<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Wine_E_Label_Presentation
{
    private static array $defaults = [
        'custom_image_url' => '',
        'custom_image_alt' => '',
        'wine_name' => '',
        'vintage' => '',
        'subtitle' => '',
    ];

    public static function defaults(): array
    {
        return self::$defaults;
    }

    public static function normalize_config($config): array
    {
        if (is_string($config) && $config !== '') {
            $decoded = json_decode($config, true);
            if (is_array($decoded)) {
                $config = $decoded;
            }
        }

        if (!is_array($config)) {
            $config = [];
        }

        return self::sanitize_config($config);
    }

    public static function sanitize_config(array $input): array
    {
        return [
            'custom_image_url' => isset($input['custom_image_url']) ? esc_url_raw((string) wp_unslash($input['custom_image_url'])) : '',
            'custom_image_alt' => isset($input['custom_image_alt']) ? sanitize_text_field((string) wp_unslash($input['custom_image_alt'])) : '',
            'wine_name' => isset($input['wine_name']) ? sanitize_text_field((string) wp_unslash($input['wine_name'])) : '',
            'vintage' => isset($input['vintage']) ? sanitize_text_field((string) wp_unslash($input['vintage'])) : '',
            'subtitle' => isset($input['subtitle']) ? sanitize_text_field((string) wp_unslash($input['subtitle'])) : '',
        ];
    }

    public static function product_defaults(int $product_id): array
    {
        $title = trim((string) get_the_title($product_id));
        $image_url = '';
        $image_alt = '';

        if (function_exists('get_the_post_thumbnail_url')) {
            $image_url = (string) get_the_post_thumbnail_url($product_id, 'large');
        }

        $thumbnail_id = (int) get_post_thumbnail_id($product_id);
        if ($thumbnail_id > 0) {
            $image_alt = trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true));
        }

        if ($image_alt === '') {
            $image_alt = $title;
        }

        return [
            'wine_name' => $title,
            'vintage' => self::detect_vintage($product_id, $title),
            'subtitle' => self::detect_subtitle($product_id, $title),
            'product_image_url' => $image_url,
            'product_image_alt' => $image_alt,
        ];
    }

    public static function resolve(int $product_id, array $config = []): array
    {
        $config = self::sanitize_config($config);
        $defaults = self::product_defaults($product_id);

        $image_url = trim((string) ($config['custom_image_url'] ?? ''));
        if ($image_url === '') {
            $image_url = (string) ($defaults['product_image_url'] ?? '');
        }

        $image_alt = trim((string) ($config['custom_image_alt'] ?? ''));
        if ($image_alt === '') {
            $image_alt = (string) ($defaults['product_image_alt'] ?? '');
        }

        return [
            'wine_name' => trim((string) ($config['wine_name'] !== '' ? $config['wine_name'] : ($defaults['wine_name'] ?? ''))),
            'vintage' => trim((string) ($config['vintage'] !== '' ? $config['vintage'] : ($defaults['vintage'] ?? ''))),
            'subtitle' => trim((string) ($config['subtitle'] !== '' ? $config['subtitle'] : ($defaults['subtitle'] ?? ''))),
            'product_image_url' => $image_url,
            'product_image_alt' => $image_alt,
            'defaults' => $defaults,
            'config' => $config,
        ];
    }

    public static function producer_labels(): array
    {
        return [
            'de' => [
                'region' => __('Anbaugebiet', 'wine-e-label'),
                'country' => __('Land', 'wine-e-label'),
                'address' => __('Adresse des Weinguts', 'wine-e-label'),
            ],
            'en' => [
                'region' => __('Growing region', 'wine-e-label'),
                'country' => __('Country', 'wine-e-label'),
                'address' => __('Winery address', 'wine-e-label'),
            ],
            'it' => [
                'region' => __('Zona di produzione', 'wine-e-label'),
                'country' => __('Paese', 'wine-e-label'),
                'address' => __('Indirizzo della cantina', 'wine-e-label'),
            ],
            'fr' => [
                'region' => __('Region viticole', 'wine-e-label'),
                'country' => __('Pays', 'wine-e-label'),
                'address' => __('Adresse du domaine', 'wine-e-label'),
            ],
        ];
    }

    private static function detect_vintage(int $product_id, string $title): string
    {
        if (preg_match('/\b(19|20)\d{2}\b/', $title, $matches)) {
            return (string) $matches[0];
        }

        $attribute_value = self::find_product_attribute_value($product_id, ['jahrgang', 'vintage', 'year']);
        if ($attribute_value !== '') {
            return $attribute_value;
        }

        return '';
    }

    private static function detect_subtitle(int $product_id, string $title): string
    {
        $attribute_value = self::find_product_attribute_value($product_id, [
            'geschmack',
            'weinstil',
            'stil',
            'sweetness',
            'suesse',
            'susse',
            'suss',
            'taste',
        ]);
        if ($attribute_value !== '') {
            return $attribute_value;
        }

        if (preg_match('/\b(trocken|halbtrocken|feinherb|lieblich|suess|suss|brut|extra brut|mild|sec|dry)\b/i', $title, $matches)) {
            return sanitize_text_field((string) $matches[0]);
        }

        return '';
    }

    private static function find_product_attribute_value(int $product_id, array $needles): string
    {
        if (!function_exists('wc_get_product')) {
            return '';
        }

        $product = wc_get_product($product_id);
        if (!$product || !method_exists($product, 'get_attributes')) {
            return '';
        }

        foreach ($product->get_attributes() as $attribute) {
            if (!is_object($attribute) || !method_exists($attribute, 'get_name')) {
                continue;
            }

            $name = (string) $attribute->get_name();
            $label = function_exists('wc_attribute_label') ? (string) wc_attribute_label($name, $product) : $name;
            $haystack = self::normalize_key($label . ' ' . $name);

            $matched = false;
            foreach ($needles as $needle) {
                if (str_contains($haystack, self::normalize_key($needle))) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                continue;
            }

            if (method_exists($attribute, 'is_taxonomy') && $attribute->is_taxonomy()) {
                $terms = wc_get_product_terms($product_id, $name, ['fields' => 'names']);
                if (!empty($terms)) {
                    return sanitize_text_field(implode(', ', array_map('strval', $terms)));
                }
            } elseif (method_exists($attribute, 'get_options')) {
                $options = array_filter(array_map('trim', array_map('strval', (array) $attribute->get_options())));
                if (!empty($options)) {
                    return sanitize_text_field(implode(', ', $options));
                }
            }
        }

        return '';
    }

    private static function normalize_key(string $value): string
    {
        $value = function_exists('remove_accents') ? remove_accents($value) : $value;
        $value = strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? $value));
        return $value;
    }
}
