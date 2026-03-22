<?php

if (!defined('ABSPATH')) {
    exit;
}

class NutritionLabels_Frontend
{
    public static function init(): void
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
        add_shortcode('wine_elabel_nutrition_table', [__CLASS__, 'shortcode']);
    }

    public static function register_assets(): void
    {
        wp_register_style(
            'nutrition-labels-frontend-widget',
            NUTRITION_LABELS_PLUGIN_URL . 'assets/css/frontend-widget.css',
            [],
            NUTRITION_LABELS_VERSION
        );
    }

    public static function shortcode($atts = []): string
    {
        $atts = shortcode_atts([
            'product_id' => 0,
            'show_title' => 'yes',
            'show_minor' => 'yes',
            'show_ingredients' => 'no',
            'show_link' => 'no',
            'display_mode' => 'static',
            'accordion_open' => 'no',
            'accordion_heading' => 'NÄHRWERTANGABEN',
        ], $atts, 'wine_elabel_nutrition_table');

        $product_id = self::resolve_product_id((int) $atts['product_id']);

        return self::render_table($product_id, [
            'show_title' => $atts['show_title'] === 'yes',
            'show_minor' => $atts['show_minor'] === 'yes',
            'show_ingredients' => $atts['show_ingredients'] === 'yes',
            'show_link' => $atts['show_link'] === 'yes',
            'display_mode' => $atts['display_mode'] === 'accordion' ? 'accordion' : 'static',
            'accordion_open' => $atts['accordion_open'] === 'yes',
            'accordion_heading' => sanitize_text_field((string) $atts['accordion_heading']),
            'context' => 'shortcode',
        ]);
    }

    public static function resolve_product_id(int $manual_product_id = 0): int
    {
        if ($manual_product_id > 0) {
            return $manual_product_id;
        }

        if (is_singular('product')) {
            $queried_id = (int) get_queried_object_id();
            if ($queried_id > 0) {
                return $queried_id;
            }
        }

        global $product, $post;

        if (is_object($product) && method_exists($product, 'get_id')) {
            $product_id = (int) $product->get_id();
            if ($product_id > 0) {
                return $product_id;
            }
        }

        if (is_object($post) && isset($post->ID) && get_post_type($post->ID) === 'product') {
            return (int) $post->ID;
        }

        $current_id = (int) get_the_ID();
        if ($current_id > 0 && get_post_type($current_id) === 'product') {
            return $current_id;
        }

        return 0;
    }

    public static function get_label_payload(int $product_id): array
    {
        if ($product_id <= 0) {
            return [];
        }

        $label = NutritionLabels_Importer::get_label_data($product_id);
        if (!is_array($label)) {
            return [];
        }

        $has_core_values = trim((string) ($label['energy'] ?? '')) !== ''
            || trim((string) ($label['carbs'] ?? '')) !== ''
            || trim((string) ($label['sugar'] ?? '')) !== ''
            || trim((string) ($label['minor'] ?? '')) !== ''
            || trim((string) ($label['ingredients_html'] ?? '')) !== '';

        if (!$has_core_values) {
            return [];
        }

        return [
            'product_id' => $product_id,
            'product_title' => $label['title'] ?: NutritionLabels_Importer::format_label_title(get_the_title($product_id)),
            'energy' => (string) ($label['energy'] ?? ''),
            'carbohydrates' => (string) ($label['carbs'] ?? ''),
            'sugar' => (string) ($label['sugar'] ?? ''),
            'minor_text' => (string) ($label['minor'] ?? ''),
            'minor_mode' => (string) (($label['minor_mode'] ?? '') ?: (!empty($label['minor']) ? 'text' : '')),
            'fat' => (string) ($label['fat'] ?? ''),
            'saturates' => (string) ($label['saturates'] ?? ''),
            'protein' => (string) ($label['protein'] ?? ''),
            'salt' => (string) ($label['salt'] ?? ''),
            'salt_natural' => (string) ($label['salt_natural'] ?? ''),
            'ingredients_html' => (string) ($label['ingredients_html'] ?? ''),
            'footnote' => (string) ($label['footnote'] ?? ''),
            'pretable_notice' => (string) ($label['pretable_notice'] ?? ''),
            'public_url' => NutritionLabels_URL::get_short_url($product_id),
        ];
    }

    public static function render_table(int $product_id, array $args = []): string
    {
        $args = wp_parse_args($args, [
            'show_title' => true,
            'show_minor' => true,
            'show_ingredients' => false,
            'show_link' => false,
            'display_mode' => 'static',
            'accordion_open' => false,
            'accordion_heading' => 'NÄHRWERTANGABEN',
            'context' => 'frontend',
        ]);

        $data = self::get_label_payload($product_id);

        if ($data === []) {
            if (current_user_can('edit_posts')) {
                return '<div class="nlw-widget-empty">Für dieses Produkt sind noch keine E-Label-Daten gespeichert.</div>';
            }
            return '';
        }

        wp_enqueue_style('nutrition-labels-frontend-widget');

        ob_start();
        $content_open = (bool) ($args['accordion_open'] ?? false);
        $heading = trim((string) ($args['accordion_heading'] ?? 'NÄHRWERTANGABEN'));
        ?>
        <div class="nlw-widget-wrap <?php echo ($args['display_mode'] ?? 'static') === 'accordion' ? 'is-accordion' : 'is-static'; ?>">
            <?php if (($args['display_mode'] ?? 'static') === 'accordion') : ?>
                <details class="nlw-accordion" <?php echo $content_open ? 'open' : ''; ?>>
                    <summary class="nlw-accordion-summary">
                        <span class="nlw-accordion-title"><?php echo esc_html($heading !== '' ? $heading : 'NÄHRWERTANGABEN'); ?></span>
                        <span class="nlw-accordion-icon" aria-hidden="true"></span>
                    </summary>
                    <div class="nlw-accordion-panel">
            <?php endif; ?>

            <?php if (!empty($args['show_title'])) : ?>
                <div class="nlw-widget-title"><?php echo esc_html($data['product_title']); ?></div>
            <?php endif; ?>

            <table class="nlw-widget-table">
                <thead>
                    <tr>
                        <th>
                            <div class="nlw-head-row"><span><?php echo esc_html__('Nährwertangaben', 'nutrition-labels'); ?></span><span><?php echo esc_html__('je 100 ml', 'nutrition-labels'); ?></span></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($data['pretable_notice'] !== '') : ?>
                        <tr><td><div class="nlw-row"><span><?php echo esc_html($data['pretable_notice']); ?></span><span></span></div></td></tr>
                    <?php endif; ?>
                    <tr><td><div class="nlw-row"><span><?php echo esc_html__('Brennwert', 'nutrition-labels'); ?></span><span><?php echo esc_html($data['energy']); ?></span></div></td></tr>
                    <tr><td><div class="nlw-row"><span><?php echo esc_html__('Kohlenhydrate', 'nutrition-labels'); ?></span><span><?php echo esc_html($data['carbohydrates']); ?></span></div></td></tr>
                    <tr><td><div class="nlw-row nlw-indent"><span><?php echo esc_html__('davon Zucker', 'nutrition-labels'); ?></span><span><?php echo esc_html($data['sugar']); ?></span></div></td></tr>

                    <?php if (!empty($args['show_minor'])) : ?>
                        <?php if (($data['minor_mode'] ?? '') === 'list') : ?>
                            <?php if ($data['fat'] !== '') : ?><tr><td><div class="nlw-row"><span><?php echo esc_html__('Fett', 'nutrition-labels'); ?></span><span><?php echo esc_html($data['fat']); ?> g</span></div></td></tr><?php endif; ?>
                            <?php if ($data['saturates'] !== '') : ?><tr><td><div class="nlw-row"><span><?php echo esc_html__('davon gesättigte Fettsäuren', 'nutrition-labels'); ?></span><span><?php echo esc_html($data['saturates']); ?> g</span></div></td></tr><?php endif; ?>
                            <?php if ($data['protein'] !== '') : ?><tr><td><div class="nlw-row"><span><?php echo esc_html__('Eiweiß', 'nutrition-labels'); ?></span><span><?php echo esc_html($data['protein']); ?> g</span></div></td></tr><?php endif; ?>
                            <?php if ($data['salt'] !== '') : ?><tr><td><div class="nlw-row"><span><?php echo esc_html__('Salz', 'nutrition-labels'); ?></span><span><?php echo esc_html($data['salt']); ?> g</span></div></td></tr><?php endif; ?>
                            <?php if (!empty($data['salt_natural'])) : ?><tr><td class="nlw-smalltext"><?php echo esc_html__('Angegebener Salzgehalt ist ausschließlich auf die Anwesenheit natürlich vorkommenden Natriums zurückzuführen.', 'nutrition-labels'); ?></td></tr><?php endif; ?>
                        <?php elseif ($data['minor_text'] !== '') : ?>
                            <tr><td class="nlw-smalltext"><?php echo esc_html($data['minor_text']); ?></td></tr>
                        <?php endif; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (!empty($args['show_ingredients']) && $data['ingredients_html'] !== '') : ?>
                <div class="nlw-ingredients"><span class="nlw-ingredients-label"><?php echo esc_html__('Zutaten', 'nutrition-labels'); ?>:</span> <?php echo wp_kses_post($data['ingredients_html']); ?></div>
                <?php if ($data['footnote'] !== '') : ?>
                    <div class="nlw-footnote"><?php echo esc_html($data['footnote']); ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($args['show_link']) && !empty($data['public_url'])) : ?>
                <div class="nlw-link-wrap">
                    <a class="nlw-link" href="<?php echo esc_url($data['public_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__('E-Label öffnen', 'nutrition-labels'); ?></a>
                </div>
            <?php endif; ?>

            <?php if (($args['display_mode'] ?? 'static') === 'accordion') : ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
