<?php

if (!defined('ABSPATH')) {
    exit;
}

class NutritionLabels_URL
{
    private static ?NutritionLabels_DB_Extended $db = null;

    public static function init(): void
    {
        if (!function_exists('add_action') || !function_exists('add_filter')) {
            return;
        }

        self::$db = new NutritionLabels_DB_Extended();
        self::add_rewrite_rules();
        add_filter('query_vars', [__CLASS__, 'add_query_vars']);
        add_action('template_redirect', [__CLASS__, 'enforce_subdomain_restriction'], 1);
        add_action('template_redirect', [__CLASS__, 'handle_short_url']);
    }

    public static function get_db(): NutritionLabels_DB_Extended
    {
        if (!self::$db) {
            self::$db = new NutritionLabels_DB_Extended();
        }
        return self::$db;
    }

    public static function get_local_base_url(): string
    {
        return self::normalize_base_url(home_url('/' . NUTRITION_LABELS_URL_PREFIX));
    }

    public static function get_manual_public_base_url(): string
    {
        $configured = trim((string) get_option('nutrition_labels_base_url', ''));
        return $configured !== '' ? self::normalize_base_url($configured) : '';
    }

    public static function get_base_url(): string
    {
        return self::get_local_base_url();
    }

    public static function get_public_base_url(bool $prefer_external = true): string
    {
        if ($prefer_external && self::use_external_rest_domain()) {
            $external = self::get_external_rest_base_url();
            if ($external !== '') {
                return $external;
            }
        }

        $manual = self::get_manual_public_base_url();
        if ($manual !== '') {
            return $manual;
        }

        return self::get_local_base_url();
    }

    public static function get_external_receiver_path_prefix(): string
    {
        return 'e-label';
    }

    public static function get_external_receiver_label_base_url(): string
    {
        $external = self::get_external_rest_base_url();
        if ($external === '') {
            return '';
        }

        return self::normalize_base_url($external . '/' . self::get_external_receiver_path_prefix());
    }

    public static function get_preview_base_url(bool $prefer_external = true): string
    {
        if ($prefer_external && self::use_external_rest_domain()) {
            $receiver = self::get_external_receiver_label_base_url();
            if ($receiver !== '') {
                return $receiver;
            }
        }

        return self::get_public_base_url(false);
    }

    public static function normalize_base_url(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        return rtrim($url, '/');
    }



    public static function use_external_rest_domain(): bool
    {
        return get_option('nutrition_labels_rest_enabled', 'no') === 'yes' && trim((string) get_option('nutrition_labels_rest_base_url', '')) !== '';
    }

    public static function get_external_rest_base_url(): string
    {
        $configured = trim((string) get_option('nutrition_labels_rest_base_url', ''));
        return $configured !== '' ? self::normalize_base_url($configured) : '';
    }

    public static function get_lang_names(): array
    {
        return [
            'de' => 'Deutsch',
            'en' => 'English',
            'it' => 'Italiano',
            'fr' => 'Français',
        ];
    }

    public static function get_local_route_base_path(): string
    {
        $prefix = trim((string) NUTRITION_LABELS_URL_PREFIX, '/');
        $prefix = strtolower(sanitize_title_with_dashes($prefix));
        return $prefix !== '' ? $prefix : 'l';
    }

    public static function get_route_base_path(): string
    {
        return self::get_local_route_base_path();
    }

    public static function add_rewrite_rules(): void
    {
        $basePath = self::get_local_route_base_path();
        $pattern = '^' . preg_quote($basePath, '#') . '/([a-z0-9-]{1,120})/?$';
        add_rewrite_rule($pattern, 'index.php?nutrition_shortcode=$matches[1]', 'top');
    }

    public static function add_query_vars($query_vars)
    {
        $query_vars[] = 'nutrition_shortcode';
        return $query_vars;
    }

    public static function enforce_subdomain_restriction(): void
    {
        if (is_admin() || !self::should_handle_dedicated_host_request()) {
            return;
        }

        $shortcode = self::get_requested_shortcode();
        if ($shortcode === '') {
            wp_die(esc_html__('Not found', 'nutrition-labels'), esc_html__('Not found', 'nutrition-labels'), ['response' => 404]);
        }
    }

    public static function handle_short_url(): void
    {
        if (is_admin()) {
            return;
        }

        $shortcode = self::get_requested_shortcode();
        if ($shortcode === '') {
            return;
        }

        $product_id = self::get_db()->get_product_id_by_shortcode($shortcode);
        if ($product_id) {
            self::display_nutrition_label((int) $product_id);
        }
    }

    private static function get_requested_shortcode(): string
    {
        $shortcode = (string) get_query_var('nutrition_shortcode');
        if ($shortcode !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $shortcode)) {
            return $shortcode;
        }

        $dedicatedHostShortcode = self::get_shortcode_from_dedicated_host_request();
        if ($dedicatedHostShortcode !== '') {
            return $dedicatedHostShortcode;
        }

        return '';
    }

    private static function get_shortcode_from_dedicated_host_request(): string
    {
        if (!self::should_handle_dedicated_host_request()) {
            return '';
        }

        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $requestPath = trim((string) parse_url($requestUri, PHP_URL_PATH), '/');
        if ($requestPath === '' || str_contains($requestPath, '/')) {
            return '';
        }

        return preg_match('/^[a-z0-9-]{1,120}$/', $requestPath) ? $requestPath : '';
    }

    private static function should_handle_dedicated_host_request(): bool
    {
        if (get_option('nutrition_labels_use_subdomain', 'no') !== 'yes') {
            return false;
        }

        $manualBaseUrl = self::get_manual_public_base_url();
        if ($manualBaseUrl === '') {
            return false;
        }

        $manualHost = strtolower((string) parse_url($manualBaseUrl, PHP_URL_HOST));
        $siteHost = strtolower((string) parse_url(home_url(), PHP_URL_HOST));
        $requestHost = strtolower((string) preg_replace('/:\d+$/', '', sanitize_text_field($_SERVER['HTTP_HOST'] ?? '')));

        return $manualHost !== '' && $manualHost !== $siteHost && $requestHost === $manualHost;
    }

    public static function compose_public_url(string $slug, string $lang_code = '', bool $prefer_external = true): string|false
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return false;
        }

        $slug = ltrim($slug, '/');
        $lang_code = strtolower(sanitize_key($lang_code));

        if ($prefer_external && self::use_external_rest_domain()) {
            $base = self::get_external_receiver_label_base_url();
            if ($base !== '') {
                $url = trailingslashit($base . '/' . $slug);
                if ($lang_code !== '' && array_key_exists($lang_code, self::get_lang_names())) {
                    $url = add_query_arg('lang', $lang_code, $url);
                }
                return $url;
            }
        }

        $base = self::get_public_base_url(false);
        if ($base === '') {
            return false;
        }

        $url = rtrim($base, '/') . '/' . $slug;
        if ($lang_code !== '' && array_key_exists($lang_code, self::get_lang_names())) {
            $url = add_query_arg('lang', $lang_code, $url);
        }

        return $url;
    }

    public static function get_short_url(int $product_id, string $lang_code = ""): string|false
    {
        $label_data = NutritionLabels_Importer::get_label_data($product_id);
        $lang_code = strtolower(sanitize_key($lang_code));

        if (self::use_external_rest_domain() && !empty($label_data['remote_page_url'])) {
            $url = (string) $label_data['remote_page_url'];
            if ($lang_code !== '' && array_key_exists($lang_code, self::get_lang_names())) {
                $url = add_query_arg('lang', $lang_code, $url);
            }
            return $url;
        }

        $slug = (string) get_post_meta($product_id, NutritionLabels_Importer::META_SLUG, true);
        if ($slug === '') {
            $slug = (string) self::get_db()->get_shortcode_by_product_id($product_id);
        }
        if ($slug === '') {
            return false;
        }

        return self::compose_public_url($slug, $lang_code, true);
    }

    public static function get_label_target_url(int $product_id, string $target_kind = '', string $lang_code = ''): string|false
    {
        $target_kind = sanitize_key($target_kind);
        $targets = self::get_label_targets($product_id, $lang_code);

        if ($target_kind !== '') {
            foreach ($targets as $target) {
                if (($target['kind'] ?? '') === $target_kind && !empty($target['url'])) {
                    return (string) $target['url'];
                }
            }
        }

        return self::get_short_url($product_id, $lang_code);
    }

    public static function get_label_targets(int $product_id, string $lang_code = ''): array
    {
        $label_data = NutritionLabels_Importer::get_label_data($product_id);
        $lang_code = strtolower(sanitize_key($lang_code));
        $slug = (string) get_post_meta($product_id, NutritionLabels_Importer::META_SLUG, true);
        if ($slug === '') {
            $slug = (string) self::get_db()->get_shortcode_by_product_id($product_id);
        }

        $preferred_url = self::get_short_url($product_id, $lang_code);
        $targets = [];
        $seen = [];

        if ($slug !== '') {
            self::append_target(
                $targets,
                $seen,
                'main',
                self::compose_target_url(self::get_local_base_url(), $slug, $lang_code),
                'Main Domain',
                $preferred_url !== false && self::normalize_base_url((string) $preferred_url) === self::normalize_base_url((string) self::compose_target_url(self::get_local_base_url(), $slug, $lang_code))
            );

            $manual_base = self::get_manual_public_base_url();
            if (get_option('nutrition_labels_use_subdomain', 'no') === 'yes' && $manual_base !== '' && self::normalize_base_url($manual_base) !== self::normalize_base_url(self::get_local_base_url())) {
                $manual_url = self::compose_target_url($manual_base, $slug, $lang_code);
                self::append_target(
                    $targets,
                    $seen,
                    'subdomain',
                    $manual_url,
                    'Subdomain',
                    $preferred_url !== false && self::normalize_base_url((string) $preferred_url) === self::normalize_base_url((string) $manual_url)
                );
            }
        }

        if (self::use_external_rest_domain() && !empty($label_data['remote_page_url'])) {
            $receiver_url = self::append_lang_to_url((string) $label_data['remote_page_url'], $lang_code);
            self::append_target(
                $targets,
                $seen,
                'receiver',
                $receiver_url,
                'Receiver Domain',
                $preferred_url !== false && self::normalize_base_url((string) $preferred_url) === self::normalize_base_url((string) $receiver_url)
            );
        }

        if (empty($targets) && $preferred_url !== false) {
            self::append_target($targets, $seen, 'main', (string) $preferred_url, 'Main Domain', true);
        }

        return $targets;
    }

    public static function get_source_targets_for_sync(int $product_id): array
    {
        $targets = self::get_label_targets($product_id);
        $source_targets = [];

        foreach ($targets as $target) {
            if (($target['kind'] ?? '') === 'receiver') {
                continue;
            }

            $source_targets[] = [
                'kind' => (string) ($target['kind'] ?? ''),
                'location_label' => (string) ($target['location_label'] ?? ''),
                'host' => (string) ($target['host'] ?? ''),
                'display_name' => (string) ($target['display_name'] ?? ''),
                'url' => (string) ($target['url'] ?? ''),
                'is_primary' => !empty($target['is_primary']),
            ];
        }

        return $source_targets;
    }

    private static function append_target(array &$targets, array &$seen, string $kind, string|false $url, string $location_label, bool $is_primary): void
    {
        if (!is_string($url) || trim($url) === '') {
            return;
        }

        $url = trim($url);
        if (isset($seen[$url])) {
            if ($is_primary) {
                $targets[$seen[$url]]['is_primary'] = true;
            }
            return;
        }

        $host = (string) wp_parse_url($url, PHP_URL_HOST);
        $display_name = $location_label . ($host !== '' ? ' - ' . $host : '');

        $seen[$url] = count($targets);
        $targets[] = [
            'kind' => $kind,
            'location_label' => $location_label,
            'host' => $host,
            'display_name' => $display_name,
            'url' => $url,
            'is_primary' => $is_primary,
        ];
    }

    private static function compose_target_url(string $base, string $slug, string $lang_code = ''): string|false
    {
        $base = self::normalize_base_url($base);
        $slug = trim((string) $slug);
        if ($base === '' || $slug === '') {
            return false;
        }

        return self::append_lang_to_url($base . '/' . ltrim($slug, '/'), $lang_code);
    }

    private static function append_lang_to_url(string $url, string $lang_code = ''): string
    {
        $lang_code = strtolower(sanitize_key($lang_code));
        if ($lang_code === '' || !array_key_exists($lang_code, self::get_lang_names())) {
            return $url;
        }

        return add_query_arg('lang', $lang_code, $url);
    }

    public static function render_label_html(int $product_id): string
    {
        $label = NutritionLabels_Importer::get_label_data($product_id);
        if (trim($label['energy']) === '' && trim($label['ingredients_html']) === '') {
            return '';
        }

        $nutrition_data = [
            'product_id' => $product_id,
            'product_title' => $label['title'] ?: NutritionLabels_Importer::format_label_title(get_the_title($product_id)),
            'ingredient_list' => $label['ingredients_html'],
            'ingredient_footnote' => $label['footnote'],
            'energy' => $label['energy'],
            'carbohydrates' => $label['carbs'],
            'sugar' => $label['sugar'],
            'minor_text' => $label['minor'],
            'minor_mode' => $label['minor_mode'] ?: (!empty($label['minor']) ? 'text' : ''),
            'fat' => $label['fat'],
            'saturates' => $label['saturates'],
            'protein' => $label['protein'],
            'salt' => $label['salt'],
            'salt_natural' => $label['salt_natural'],
            'pretable_notice' => $label['pretable_notice'],
            'manual_config' => $label['manual_config'] ?? [],
            'display_config' => $label['display_config'] ?? [],
        ];

        $template = locate_template('wine-e-label/wine-e-label-secure.php');
        if (empty($template)) {
            $template = NUTRITION_LABELS_PLUGIN_DIR . 'templates/wine-e-label-secure.php';
        }

        ob_start();
        include $template;
        return (string) ob_get_clean();
    }

    private static function display_nutrition_label(int $product_id): void
    {
        $html = self::render_label_html($product_id);
        if ($html === '') {
            wp_die(__('E-Label not found', 'nutrition-labels'));
        }
        echo $html;
        exit;
    }
}
