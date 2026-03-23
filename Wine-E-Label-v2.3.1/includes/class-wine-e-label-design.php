<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Wine_E_Label_Design
{
    public const OPTION_NAME = 'wine_e_label_design_settings';

    private static array $defaults = [
        'page_bg' => '#f3f4f6',
        'card_bg' => '#ffffff',
        'table_head_bg' => '#f3f4f6',
        'text_color' => '#111827',
        'muted_color' => '#6b7280',
        'border_color' => '#d1d5db',
        'base_font_size' => 15,
        'small_font_size' => 14,
        'button_font_size' => 13,
        'font_family' => 'system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif',
        'panel_radius' => 16,
        'outer_width' => 980,
        'label_width' => 640,
        'outer_padding_y' => 40,
        'card_padding' => 26,
        'logo_enabled' => '0',
        'logo_url' => '',
        'logo_alt' => '',
        'logo_max_height' => 110,
        'product_image_enabled' => '0',
        'product_image_max_height' => 200,
        'wine_name_enabled' => '0',
        'wine_name_size' => 28,
        'vintage_enabled' => '0',
        'vintage_size' => 17,
        'subtitle_enabled' => '0',
        'subtitle_size' => 20,
        'producer_region' => '',
        'producer_country' => '',
        'producer_address' => '',
        'custom_css' => '',
    ];

    public static function defaults(): array
    {
        return self::$defaults;
    }

    public static function get_settings(): array
    {
        return self::resolve_settings(get_option(self::OPTION_NAME, []));
    }

    public static function color_fields(): array
    {
        return [
            'page_bg',
            'card_bg',
            'table_head_bg',
            'text_color',
            'muted_color',
            'border_color',
        ];
    }

    public static function numeric_fields(): array
    {
        return [
            'base_font_size',
            'small_font_size',
            'button_font_size',
            'panel_radius',
            'outer_width',
            'label_width',
            'outer_padding_y',
            'card_padding',
            'logo_max_height',
            'product_image_max_height',
            'wine_name_size',
            'vintage_size',
            'subtitle_size',
        ];
    }

    public static function size_fields(): array
    {
        return [
            'base_font_size',
            'small_font_size',
            'button_font_size',
            'font_family',
            'panel_radius',
            'outer_width',
            'label_width',
            'outer_padding_y',
            'card_padding',
            'logo_max_height',
            'product_image_max_height',
            'wine_name_size',
            'vintage_size',
            'subtitle_size',
            'custom_css',
        ];
    }

    public static function logo_fields(): array
    {
        return [
            'logo_enabled',
            'logo_url',
            'logo_alt',
            'logo_max_height',
        ];
    }

    public static function product_block_fields(): array
    {
        return [
            'product_image_enabled',
            'product_image_max_height',
            'wine_name_enabled',
            'wine_name_size',
            'vintage_enabled',
            'vintage_size',
            'subtitle_enabled',
            'subtitle_size',
        ];
    }

    public static function producer_fields(): array
    {
        return [
            'producer_region',
            'producer_country',
            'producer_address',
        ];
    }

    public static function range_limits(): array
    {
        return [
            'base_font_size' => [10, 24],
            'small_font_size' => [9, 20],
            'button_font_size' => [10, 22],
            'panel_radius' => [0, 48],
            'outer_width' => [480, 1600],
            'label_width' => [320, 1200],
            'outer_padding_y' => [0, 120],
            'card_padding' => [0, 80],
            'logo_max_height' => [40, 240],
            'product_image_max_height' => [60, 360],
            'wine_name_size' => [14, 48],
            'vintage_size' => [12, 32],
            'subtitle_size' => [12, 36],
        ];
    }

    public static function font_options(): array
    {
        return [
            'system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif' => __('System Sans', 'wine-e-label'),
            'Arial,Helvetica,sans-serif' => __('Arial / Helvetica', 'wine-e-label'),
            'Georgia,"Times New Roman",serif' => __('Georgia', 'wine-e-label'),
            '"Times New Roman",Times,serif' => __('Times New Roman', 'wine-e-label'),
            'Verdana,Geneva,sans-serif' => __('Verdana', 'wine-e-label'),
            'Tahoma,Geneva,sans-serif' => __('Tahoma', 'wine-e-label'),
        ];
    }

    public static function sanitize_settings(array $input): array
    {
        $settings = self::get_settings();

        foreach (self::color_fields() as $field) {
            $settings[$field] = isset($input[$field])
                ? self::sanitize_hex_or_default((string) wp_unslash($input[$field]), self::$defaults[$field])
                : self::$defaults[$field];
        }

        foreach (self::range_limits() as $field => $range) {
            $value = isset($input[$field]) ? (int) wp_unslash($input[$field]) : self::$defaults[$field];
            $settings[$field] = max((int) $range[0], min((int) $range[1], $value));
        }

        $settings['font_family'] = isset($input['font_family'])
            ? self::sanitize_font_family((string) wp_unslash($input['font_family']))
            : self::$defaults['font_family'];

        foreach (['logo_enabled', 'product_image_enabled', 'wine_name_enabled', 'vintage_enabled', 'subtitle_enabled'] as $field) {
            $settings[$field] = isset($input[$field]) && (string) wp_unslash($input[$field]) === '1' ? '1' : '0';
        }

        $settings['logo_url'] = isset($input['logo_url']) ? esc_url_raw((string) wp_unslash($input['logo_url'])) : '';
        $settings['logo_alt'] = isset($input['logo_alt']) ? sanitize_text_field((string) wp_unslash($input['logo_alt'])) : '';
        $settings['producer_region'] = isset($input['producer_region']) ? sanitize_text_field((string) wp_unslash($input['producer_region'])) : '';
        $settings['producer_country'] = isset($input['producer_country']) ? sanitize_text_field((string) wp_unslash($input['producer_country'])) : '';
        $settings['producer_address'] = isset($input['producer_address']) ? sanitize_textarea_field((string) wp_unslash($input['producer_address'])) : '';
        $settings['custom_css'] = isset($input['custom_css']) ? trim((string) wp_unslash($input['custom_css'])) : '';

        return $settings;
    }

    public static function build_frontend_css(?array $settings = null): string
    {
        $s = self::optional_settings($settings);
        return self::build_base_css('wel', $s);
    }

    public static function export_remote_settings(?array $settings = null): array
    {
        $s = self::optional_settings($settings);

        return [
            'page_bg' => (string) $s['page_bg'],
            'card_bg' => (string) $s['card_bg'],
            'table_head_bg' => (string) $s['table_head_bg'],
            'text_color' => (string) $s['text_color'],
            'muted_color' => (string) $s['muted_color'],
            'border_color' => (string) $s['border_color'],
            'base_font_size' => (int) $s['base_font_size'],
            'small_font_size' => (int) $s['small_font_size'],
            'button_font_size' => (int) $s['button_font_size'],
            'font_family' => (string) $s['font_family'],
            'panel_radius' => (int) $s['panel_radius'],
            'outer_width' => (int) $s['outer_width'],
            'label_width' => (int) $s['label_width'],
            'outer_padding_y' => (int) $s['outer_padding_y'],
            'card_padding' => (int) $s['card_padding'],
            'logo_enabled' => (string) $s['logo_enabled'],
            'logo_url' => (string) $s['logo_url'],
            'logo_alt' => (string) $s['logo_alt'],
            'logo_max_height' => (int) $s['logo_max_height'],
            'product_image_enabled' => (string) $s['product_image_enabled'],
            'product_image_max_height' => (int) $s['product_image_max_height'],
            'wine_name_enabled' => (string) $s['wine_name_enabled'],
            'wine_name_size' => (int) $s['wine_name_size'],
            'vintage_enabled' => (string) $s['vintage_enabled'],
            'vintage_size' => (int) $s['vintage_size'],
            'subtitle_enabled' => (string) $s['subtitle_enabled'],
            'subtitle_size' => (int) $s['subtitle_size'],
            'producer_region' => (string) $s['producer_region'],
            'producer_country' => (string) $s['producer_country'],
            'producer_address' => (string) $s['producer_address'],
            'custom_css' => (string) $s['custom_css'],
        ];
    }

    public static function build_remote_css(?array $settings = null): string
    {
        $s = self::optional_settings($settings);
        return self::build_base_css('nler', $s);
    }

    public static function render_logo_markup(?array $settings = null, string $wrap_class = 'wel-logo-wrap', string $image_class = 'wel-logo-image'): string
    {
        $s = self::optional_settings($settings);
        if (($s['logo_enabled'] ?? '0') !== '1') {
            return '';
        }

        $url = trim((string) ($s['logo_url'] ?? ''));
        if ($url === '') {
            return '';
        }

        $alt = trim((string) ($s['logo_alt'] ?? ''));
        if ($alt === '') {
            $alt = __('Weingutslogo', 'wine-e-label');
        }

        return '<div class="' . esc_attr($wrap_class) . '"><img class="' . esc_attr($image_class) . '" src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" loading="eager" decoding="async"></div>';
    }

    public static function render_product_header_markup(array $presentation, ?array $settings = null, string $prefix = 'wel'): string
    {
        $s = self::optional_settings($settings);
        $parts = [];

        $logo_markup = self::render_logo_markup($s, $prefix . '-logo-wrap', $prefix . '-logo-image');
        if ($logo_markup !== '') {
            $parts[] = $logo_markup;
        }

        if (($s['product_image_enabled'] ?? '0') === '1' && trim((string) ($presentation['product_image_url'] ?? '')) !== '') {
            $alt = trim((string) ($presentation['product_image_alt'] ?? ''));
            if ($alt === '') {
                $alt = trim((string) ($presentation['wine_name'] ?? ''));
            }
            $parts[] = '<div class="' . esc_attr($prefix . '-product-image-wrap') . '"><img class="' . esc_attr($prefix . '-product-image') . '" src="' . esc_url((string) $presentation['product_image_url']) . '" alt="' . esc_attr($alt) . '" loading="eager" decoding="async"></div>';
        }

        if (($s['vintage_enabled'] ?? '0') === '1' && trim((string) ($presentation['vintage'] ?? '')) !== '') {
            $parts[] = '<div class="' . esc_attr($prefix . '-vintage') . '">' . esc_html((string) $presentation['vintage']) . '</div>';
        }

        if (($s['wine_name_enabled'] ?? '0') === '1' && trim((string) ($presentation['wine_name'] ?? '')) !== '') {
            $parts[] = '<div class="' . esc_attr($prefix . '-wine-name') . '">' . esc_html((string) $presentation['wine_name']) . '</div>';
        }

        if (($s['subtitle_enabled'] ?? '0') === '1' && trim((string) ($presentation['subtitle'] ?? '')) !== '') {
            $parts[] = '<div class="' . esc_attr($prefix . '-subtitle') . '">' . esc_html((string) $presentation['subtitle']) . '</div>';
        }

        if ($parts === []) {
            return '';
        }

        return '<div class="' . esc_attr($prefix . '-header-block') . '">' . implode('', $parts) . '</div>';
    }

    public static function render_producer_markup(string $lang = 'de', ?array $settings = null, string $prefix = 'wel'): string
    {
        $s = self::optional_settings($settings);
        $items = [];
        $labels = self::producer_label_map($lang);

        foreach ([
            'producer_region' => 'region',
            'producer_country' => 'country',
            'producer_address' => 'address',
        ] as $field => $label_key) {
            $value = trim((string) ($s[$field] ?? ''));
            if ($value === '') {
                continue;
            }
            $items[] = '<div class="' . esc_attr($prefix . '-producer-item') . '"><div class="' . esc_attr($prefix . '-producer-label') . '">' . esc_html($labels[$label_key]) . '</div><div class="' . esc_attr($prefix . '-producer-value') . '">' . nl2br(esc_html($value)) . '</div></div>';
        }

        if ($items === []) {
            return '';
        }

        return '<div class="' . esc_attr($prefix . '-producer-card') . '"><div class="' . esc_attr($prefix . '-producer-grid') . '">' . implode('', $items) . '</div></div>';
    }

    public static function sample_preview_fragment(): string
    {
        return '<div class="wel-page-shell wel-preview-shell">'
            . '<div class="wel-lang-switch wel-preview-lang" id="wel_preview_lang_switch">'
            . '<button type="button" class="wel-lang-button is-active" data-wel-lang="de" aria-pressed="true">DE</button>'
            . '<button type="button" class="wel-lang-button" data-wel-lang="en" aria-pressed="false">EN</button>'
            . '<button type="button" class="wel-lang-button" data-wel-lang="it" aria-pressed="false">IT</button>'
            . '<button type="button" class="wel-lang-button" data-wel-lang="fr" aria-pressed="false">FR</button>'
            . '</div>'
            . '<div class="wel-header-block" id="wel-preview-header-block">'
            . '<div class="wel-logo-wrap is-hidden" id="wel-preview-logo-wrap"><img class="wel-logo-image" id="wel-preview-logo-image" src="" alt="Logo"></div>'
            . '<div class="wel-product-image-wrap is-hidden" id="wel-preview-product-image-wrap"><img class="wel-product-image" id="wel-preview-product-image" src="' . esc_attr(self::sample_product_image_data_uri()) . '" alt="Produktbild"></div>'
            . '<div class="wel-vintage is-hidden" id="wel-preview-vintage">2024</div>'
            . '<div class="wel-wine-name is-hidden" id="wel-preview-wine-name">Riesling</div>'
            . '<div class="wel-subtitle is-hidden" id="wel-preview-subtitle-text">trocken</div>'
            . '</div>'
            . '<div class="wel-label-card wel-preview-card">'
            . '<table class="wel-label-table">'
            . '<thead><tr><th id="wel-preview-headline">Nährwertangaben je 100 ml</th></tr></thead>'
            . '<tbody>'
            . '<tr><td><div class="wel-label-row"><span id="wel-preview-energy">Brennwert</span><span>310 kJ / 75 kcal</span></div></td></tr>'
            . '<tr><td><div class="wel-label-row"><span id="wel-preview-carbs">Kohlenhydrate</span><span>1.5 g</span></div></td></tr>'
            . '<tr><td><div class="wel-label-row"><span id="wel-preview-sugars">davon Zucker</span><span>0.7 g</span></div></td></tr>'
            . '<tr class="wel-label-trace"><td id="wel-preview-trace">Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz</td></tr>'
            . '</tbody>'
            . '</table>'
            . '<div class="wel-ingredients"><strong id="wel-preview-ingredients-label">Zutaten:</strong> <span id="wel-preview-ingredients-body" class="wel-ingredients-body">Trauben*, Saccharose*, <strong>Sulfite</strong>, Gummiarabikum, unter Schutzatmosphaere abgefuellt</span></div>'
            . '<p class="wel-footnote" id="wel-preview-footnote">* aus ökologischer Erzeugung</p>'
            . '</div>'
            . '<div class="wel-producer-card is-hidden" id="wel-preview-producer-card">'
            . '<div class="wel-producer-grid">'
            . '<div class="wel-producer-item"><div class="wel-producer-label" id="wel-preview-region-label">Anbaugebiet</div><div class="wel-producer-value" id="wel-preview-region-value">Rheinhessen</div></div>'
            . '<div class="wel-producer-item"><div class="wel-producer-label" id="wel-preview-country-label">Land</div><div class="wel-producer-value" id="wel-preview-country-value">Deutschland</div></div>'
            . '<div class="wel-producer-item"><div class="wel-producer-label" id="wel-preview-address-label">Adresse des Weinguts</div><div class="wel-producer-value" id="wel-preview-address-value">Weingut Musterhof, Musterstraße 1, 55555 Musterstadt</div></div>'
            . '</div>'
            . '</div>'
            . '</div>';
    }

    public static function preview_translations(): array
    {
        $producer = self::producer_label_map();

        return [
            'de' => [
                'headline' => 'Nährwertangaben je 100 ml',
                'energy' => 'Brennwert',
                'carbs' => 'Kohlenhydrate',
                'sugars' => 'davon Zucker',
                'trace' => 'Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz',
                'ingredients_label' => 'Zutaten:',
                'ingredients_html' => 'Trauben*, Saccharose*, <strong>Sulfite</strong>, Gummiarabikum, unter Schutzatmosphaere abgefuellt',
                'footnote' => '* aus ökologischer Erzeugung',
                'region_label' => $producer['de']['region'],
                'country_label' => $producer['de']['country'],
                'address_label' => $producer['de']['address'],
            ],
            'en' => [
                'headline' => 'Nutrition declaration per 100 ml',
                'energy' => 'Energy',
                'carbs' => 'Carbohydrates',
                'sugars' => 'of which sugars',
                'trace' => 'Contains negligible amounts of fat, saturates, protein and salt',
                'ingredients_label' => 'Ingredients:',
                'ingredients_html' => 'Grapes*, sucrose*, <strong>sulphites</strong>, gum arabic, bottled under protective atmosphere',
                'footnote' => '* from organic production',
                'region_label' => $producer['en']['region'],
                'country_label' => $producer['en']['country'],
                'address_label' => $producer['en']['address'],
            ],
            'it' => [
                'headline' => 'Dichiarazione nutrizionale per 100 ml',
                'energy' => 'Energia',
                'carbs' => 'Carboidrati',
                'sugars' => 'di cui zuccheri',
                'trace' => 'Contiene quantita trascurabili di grassi, acidi grassi saturi, proteine e sale',
                'ingredients_label' => 'Ingredienti:',
                'ingredients_html' => 'Uve*, saccarosio*, <strong>solfiti</strong>, gomma arabica, imbottigliato in atmosfera protettiva',
                'footnote' => '* da produzione biologica',
                'region_label' => $producer['it']['region'],
                'country_label' => $producer['it']['country'],
                'address_label' => $producer['it']['address'],
            ],
            'fr' => [
                'headline' => 'Declaration nutritionnelle pour 100 ml',
                'energy' => 'Energie',
                'carbs' => 'Glucides',
                'sugars' => 'dont sucres',
                'trace' => 'Contient des quantites negligeables de matieres grasses, acides gras satures, proteines et sel',
                'ingredients_label' => 'Ingredients :',
                'ingredients_html' => 'Raisins*, saccharose*, <strong>sulfites</strong>, gomme arabique, mis en bouteille sous atmosphere protectrice',
                'footnote' => '* issu de l agriculture biologique',
                'region_label' => $producer['fr']['region'],
                'country_label' => $producer['fr']['country'],
                'address_label' => $producer['fr']['address'],
            ],
        ];
    }

    public static function producer_label_map(?string $lang = null): array
    {
        $labels = class_exists('Wine_E_Label_Presentation') ? Wine_E_Label_Presentation::producer_labels() : [
            'de' => ['region' => 'Anbaugebiet', 'country' => 'Land', 'address' => 'Adresse des Weinguts'],
            'en' => ['region' => 'Growing region', 'country' => 'Country', 'address' => 'Winery address'],
            'it' => ['region' => 'Zona di produzione', 'country' => 'Paese', 'address' => 'Indirizzo della cantina'],
            'fr' => ['region' => 'Region viticole', 'country' => 'Pays', 'address' => 'Adresse du domaine'],
        ];

        if ($lang === null) {
            return $labels;
        }

        return $labels[$lang] ?? $labels['de'];
    }

    private static function build_base_css(string $prefix, array $settings): string
    {
        $s = wp_parse_args($settings, self::$defaults);
        $custom_css = trim((string) $s['custom_css']);

        $css = ':root{'
            . '--' . $prefix . '-page-bg:' . $s['page_bg'] . ';'
            . '--' . $prefix . '-card-bg:' . $s['card_bg'] . ';'
            . '--' . $prefix . '-head-bg:' . $s['table_head_bg'] . ';'
            . '--' . $prefix . '-text:' . $s['text_color'] . ';'
            . '--' . $prefix . '-muted:' . $s['muted_color'] . ';'
            . '--' . $prefix . '-border:' . $s['border_color'] . ';'
            . '--' . $prefix . '-accent:#244267;'
            . '--' . $prefix . '-font:' . $s['font_family'] . ';'
            . '--' . $prefix . '-base:' . (int) $s['base_font_size'] . 'px;'
            . '--' . $prefix . '-small:' . (int) $s['small_font_size'] . 'px;'
            . '--' . $prefix . '-button:' . (int) $s['button_font_size'] . 'px;'
            . '--' . $prefix . '-radius:' . (int) $s['panel_radius'] . 'px;'
            . '--' . $prefix . '-outer-width:' . (int) $s['outer_width'] . 'px;'
            . '--' . $prefix . '-label-width:' . (int) $s['label_width'] . 'px;'
            . '--' . $prefix . '-outer-pad:' . (int) $s['outer_padding_y'] . 'px;'
            . '--' . $prefix . '-card-pad:' . (int) $s['card_padding'] . 'px;'
            . '--' . $prefix . '-logo-height:' . (int) $s['logo_max_height'] . 'px;'
            . '--' . $prefix . '-product-image-height:' . (int) $s['product_image_max_height'] . 'px;'
            . '--' . $prefix . '-wine-name-size:' . (int) $s['wine_name_size'] . 'px;'
            . '--' . $prefix . '-vintage-size:' . (int) $s['vintage_size'] . 'px;'
            . '--' . $prefix . '-subtitle-size:' . (int) $s['subtitle_size'] . 'px;'
            . '}'
            . 'html,body{margin:0 !important;padding:0 !important;background:var(--' . $prefix . '-page-bg) !important;color:var(--' . $prefix . '-text) !important;font-family:var(--' . $prefix . '-font) !important;font-size:var(--' . $prefix . '-base) !important;line-height:1.45;}'
            . 'body.' . $prefix . '-label-body{display:flex !important;justify-content:center !important;min-height:100vh !important;}'
            . '.' . $prefix . '-page-shell{box-sizing:border-box !important;width:min(var(--' . $prefix . '-outer-width),calc(100vw - 32px)) !important;max-width:none !important;margin:0 auto !important;padding:var(--' . $prefix . '-outer-pad) 16px 40px !important;}'
            . '.' . $prefix . '-lang-switch{display:flex !important;justify-content:center !important;align-items:center !important;gap:8px !important;flex-wrap:wrap !important;width:min(var(--' . $prefix . '-label-width),100%) !important;margin:0 auto 12px auto !important;}'
            . '.' . $prefix . '-lang-button{display:inline-flex !important;align-items:center !important;justify-content:center !important;text-decoration:none !important;border:1px solid #d7e0ea !important;border-radius:999px !important;background:#ffffff !important;color:#334155 !important;padding:4px 10px !important;font-size:var(--' . $prefix . '-button) !important;font-weight:600 !important;line-height:1.1 !important;box-shadow:0 1px 2px rgba(15,23,42,.03) !important;transition:all .16s ease !important;cursor:pointer !important;-webkit-appearance:none !important;appearance:none !important;}'
            . '.' . $prefix . '-lang-button:hover{border-color:#bfd0e3 !important;background:#f5f9ff !important;color:#244267 !important;}'
            . '.' . $prefix . '-lang-button.is-active,.' . $prefix . '-lang-button[aria-current="true"],.' . $prefix . '-lang-button[aria-pressed="true"]{background:#eef5ff !important;border-color:#bfd3ea !important;color:var(--' . $prefix . '-accent) !important;box-shadow:0 0 0 3px rgba(36,66,103,.06) !important;}'
            . '.' . $prefix . '-panel{display:none !important;}'
            . '.' . $prefix . '-panel.is-active{display:block !important;}'
            . '.' . $prefix . '-header-block{width:min(var(--' . $prefix . '-label-width),100%) !important;margin:0 auto 18px auto !important;display:flex !important;flex-direction:column !important;align-items:center !important;gap:12px !important;text-align:center !important;}'
            . '.' . $prefix . '-logo-wrap{display:flex !important;justify-content:center !important;align-items:center !important;width:100% !important;padding:0 12px !important;box-sizing:border-box !important;}'
            . '.' . $prefix . '-logo-wrap.is-hidden{display:none !important;}'
            . '.' . $prefix . '-logo-image{display:block !important;max-width:100% !important;max-height:var(--' . $prefix . '-logo-height) !important;width:auto !important;height:auto !important;object-fit:contain !important;}'
            . '.' . $prefix . '-vintage{font-size:var(--' . $prefix . '-vintage-size) !important;line-height:1.15 !important;font-weight:500 !important;color:var(--' . $prefix . '-text) !important;}'
            . '.' . $prefix . '-wine-name{font-size:var(--' . $prefix . '-wine-name-size) !important;line-height:1.08 !important;font-weight:700 !important;color:var(--' . $prefix . '-text) !important;}'
            . '.' . $prefix . '-subtitle{font-size:var(--' . $prefix . '-subtitle-size) !important;line-height:1.15 !important;font-weight:600 !important;color:var(--' . $prefix . '-text) !important;}'
            . '.' . $prefix . '-product-image-wrap{display:flex !important;justify-content:center !important;align-items:center !important;width:100% !important;padding:0 12px !important;box-sizing:border-box !important;}'
            . '.' . $prefix . '-product-image-wrap.is-hidden{display:none !important;}'
            . '.' . $prefix . '-product-image{display:block !important;max-width:100% !important;max-height:var(--' . $prefix . '-product-image-height) !important;width:auto !important;height:auto !important;object-fit:contain !important;}'
            . '.' . $prefix . '-label-card{box-sizing:border-box !important;width:min(var(--' . $prefix . '-label-width),100%) !important;max-width:none !important;margin:0 auto !important;padding:var(--' . $prefix . '-card-pad) !important;background:var(--' . $prefix . '-card-bg) !important;border:1px solid var(--' . $prefix . '-border) !important;border-radius:var(--' . $prefix . '-radius) !important;box-shadow:none !important;color:var(--' . $prefix . '-text) !important;}'
            . '.' . $prefix . '-label-table{width:100% !important;border-collapse:collapse !important;background:#ffffff !important;border:1px solid var(--' . $prefix . '-border) !important;font-size:var(--' . $prefix . '-base) !important;}'
            . '.' . $prefix . '-label-table th,.' . $prefix . '-label-table td{border:1px solid var(--' . $prefix . '-border) !important;padding:10px 12px !important;vertical-align:top !important;color:var(--' . $prefix . '-text) !important;font-size:var(--' . $prefix . '-base) !important;line-height:1.45 !important;}'
            . '.' . $prefix . '-label-table thead th{text-align:left !important;background:var(--' . $prefix . '-head-bg) !important;font-weight:600 !important;}'
            . '.' . $prefix . '-label-row{display:flex !important;justify-content:space-between !important;gap:16px !important;}'
            . '.' . $prefix . '-label-row span:first-child{color:var(--' . $prefix . '-text) !important;}'
            . '.' . $prefix . '-label-row span:last-child{text-align:right !important;white-space:nowrap !important;color:var(--' . $prefix . '-text) !important;font-weight:500 !important;}'
            . '.' . $prefix . '-label-trace td,.' . $prefix . '-label-pretable td,.' . $prefix . '-footnote{font-size:var(--' . $prefix . '-small) !important;color:var(--' . $prefix . '-muted) !important;}'
            . '.' . $prefix . '-ingredients,.' . $prefix . '-footnote{margin-top:12px !important;line-height:1.55 !important;}'
            . '.' . $prefix . '-ingredients{font-size:var(--' . $prefix . '-base) !important;color:var(--' . $prefix . '-text) !important;}'
            . '.' . $prefix . '-ingredients strong,.' . $prefix . '-ingredients span,.' . $prefix . '-ingredients-body{color:var(--' . $prefix . '-text) !important;}'
            . '.' . $prefix . '-footnote{font-size:var(--' . $prefix . '-small) !important;}'
            . '.' . $prefix . '-producer-card{box-sizing:border-box !important;width:min(var(--' . $prefix . '-label-width),100%) !important;max-width:none !important;margin:14px auto 0 auto !important;padding:18px var(--' . $prefix . '-card-pad) !important;background:var(--' . $prefix . '-card-bg) !important;border:1px solid var(--' . $prefix . '-border) !important;border-radius:var(--' . $prefix . '-radius) !important;}'
            . '.' . $prefix . '-producer-card.is-hidden{display:none !important;}'
            . '.' . $prefix . '-producer-grid{display:grid !important;gap:16px !important;}'
            . '.' . $prefix . '-producer-item{display:grid !important;gap:4px !important;text-align:center !important;}'
            . '.' . $prefix . '-producer-label{font-size:var(--' . $prefix . '-small) !important;color:var(--' . $prefix . '-muted) !important;font-weight:600 !important;}'
            . '.' . $prefix . '-producer-value{font-size:var(--' . $prefix . '-small) !important;color:var(--' . $prefix . '-text) !important;line-height:1.5 !important;}'
            . '@media (max-width:600px){'
            . '.' . $prefix . '-page-shell{width:calc(100vw - 20px) !important;padding:24px 10px 28px !important;}'
            . '.' . $prefix . '-label-card{padding:max(16px,calc(var(--' . $prefix . '-card-pad) * .82)) !important;}'
            . '.' . $prefix . '-producer-card{padding:16px max(16px,calc(var(--' . $prefix . '-card-pad) * .82)) !important;}'
            . '.' . $prefix . '-label-row{gap:12px !important;}'
            . '.' . $prefix . '-label-row span:last-child{white-space:normal !important;text-align:right !important;}'
            . '.' . $prefix . '-lang-switch{margin-bottom:10px !important;}'
            . '.' . $prefix . '-lang-button{padding:4px 9px !important;}}';

        if ($custom_css !== '') {
            $css .= "\n" . $custom_css;
        }

        return $css;
    }

    private static function sample_product_image_data_uri(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 260"><defs><linearGradient id="g" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#ffe48a"/><stop offset="1" stop-color="#c79000"/></linearGradient></defs><rect width="120" height="260" fill="none"/><rect x="48" y="12" width="24" height="32" rx="8" fill="#4b4b4b"/><rect x="44" y="28" width="32" height="30" rx="10" fill="#6b6b6b"/><path d="M38 54h44l10 26v114c0 16-13 29-29 29H57c-16 0-29-13-29-29V80z" fill="url(#g)" stroke="#5a4a2b" stroke-width="4"/><path d="M46 84h28v76H46z" fill="#fff3b0" opacity=".28"/><path d="M38 54h44" stroke="#2d2d2d" stroke-width="4" stroke-linecap="round"/></svg>';
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    private static function resolve_settings($settings): array
    {
        if (!is_array($settings)) {
            $settings = [];
        }

        $resolved = wp_parse_args($settings, self::$defaults);

        foreach (self::color_fields() as $field) {
            $resolved[$field] = self::sanitize_hex_or_default((string) ($resolved[$field] ?? ''), self::$defaults[$field]);
        }

        foreach (self::range_limits() as $field => $range) {
            $resolved[$field] = self::normalize_numeric_setting($resolved[$field] ?? null, self::$defaults[$field], (int) $range[0], (int) $range[1]);
        }

        $resolved['font_family'] = self::sanitize_font_family((string) ($resolved['font_family'] ?? self::$defaults['font_family']));

        foreach (['logo_enabled', 'product_image_enabled', 'wine_name_enabled', 'vintage_enabled', 'subtitle_enabled'] as $field) {
            $resolved[$field] = (string) ($resolved[$field] ?? '0') === '1' ? '1' : '0';
        }

        $resolved['logo_url'] = esc_url_raw((string) ($resolved['logo_url'] ?? ''));
        $resolved['logo_alt'] = sanitize_text_field((string) ($resolved['logo_alt'] ?? ''));
        $resolved['producer_region'] = sanitize_text_field((string) ($resolved['producer_region'] ?? ''));
        $resolved['producer_country'] = sanitize_text_field((string) ($resolved['producer_country'] ?? ''));
        $resolved['producer_address'] = sanitize_textarea_field((string) ($resolved['producer_address'] ?? ''));
        $resolved['custom_css'] = trim((string) ($resolved['custom_css'] ?? ''));

        return $resolved;
    }

    private static function optional_settings(?array $settings): array
    {
        return $settings === null
            ? self::get_settings()
            : self::resolve_settings($settings);
    }

    private static function normalize_numeric_setting($value, $default, int $min, int $max): int
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '' || !is_numeric($value)) {
            return (int) $default;
        }

        $normalized = (int) round((float) $value);

        if ($normalized < $min || $normalized > $max) {
            return (int) $default;
        }

        return $normalized;
    }

    private static function sanitize_hex_or_default(string $value, string $default): string
    {
        $value = trim($value);
        if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $value)) {
            return strtolower($value);
        }

        return $default;
    }

    private static function sanitize_font_family(string $font): string
    {
        $allowed = self::font_options();
        return isset($allowed[$font]) ? $font : self::$defaults['font_family'];
    }
}
