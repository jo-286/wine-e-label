<?php

if (!defined('ABSPATH')) {
    exit;
}

class NutritionLabels_Importer
{
    public const META_SLUG = '_nutrition_labels_slug';
    public const META_WINE_NR = '_nutrition_labels_wine_nr';
    public const META_TITLE = '_nutrition_labels_label_title';
    public const META_ENERGY = '_nutrition_labels_energy';
    public const META_CARBS = '_nutrition_labels_carbs';
    public const META_SUGAR = '_nutrition_labels_sugar';
    public const META_MINOR = '_nutrition_labels_minor';
    public const META_MINOR_MODE = '_nutrition_labels_minor_mode';
    public const META_FAT = '_nutrition_labels_fat';
    public const META_SATURATES = '_nutrition_labels_saturates';
    public const META_PROTEIN = '_nutrition_labels_protein';
    public const META_SALT = '_nutrition_labels_salt';
    public const META_SALT_NATURAL = '_nutrition_labels_salt_natural';
    public const META_INGREDIENTS_HTML = '_nutrition_labels_ingredients_html';
    public const META_FOOTNOTE = '_nutrition_labels_footnote';
    public const META_PRETABLE = '_nutrition_labels_pretable_notice';
    public const META_SOURCE_URL = '_nutrition_labels_source_file_url';
    public const META_SOURCE_PATH = '_nutrition_labels_source_file_path';
    public const META_SOURCE_NAME = '_nutrition_labels_source_file_name';
    public const META_LAST_IMPORT = '_nutrition_labels_last_import';
    public const META_IMPORT_STATUS = '_nutrition_labels_import_status';
    public const META_IMPORT_MESSAGE = '_nutrition_labels_import_message';
    public const META_BUILT_AT = '_nutrition_labels_built_at';
    public const META_MANUAL_CONFIG = '_nutrition_labels_manual_config';
    public const META_IMPORT_SNAPSHOT = '_nutrition_labels_import_snapshot';
    public const META_REMOTE_PAGE_ID = '_nutrition_labels_remote_page_id';
    public const META_REMOTE_PAGE_URL = '_nutrition_labels_remote_page_url';
    public const META_DISPLAY_CONFIG = '_nutrition_labels_display_config';

    public static function normalize_slug(string $value): string
    {
        $value = trim(wp_strip_all_tags($value));
        if ($value === '') {
            return '';
        }

        $map = [
            'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
        ];
        $value = strtr($value, $map);
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9\s\-\/]+/', '', $value) ?? $value;
        $value = str_replace(['/', '_'], '-', $value);
        $value = preg_replace('/\s+/', '-', $value) ?? $value;
        $value = preg_replace('/-+/', '-', $value) ?? $value;
        return trim($value, '-');
    }

    public static function suggest_slug(string $wineNr = '', string $fallback = ''): string
    {
        $source = trim($wineNr) !== '' ? $wineNr : $fallback;
        return self::normalize_slug($source);
    }

    public static function get_label_data(int $product_id): array
    {
        $product_title = get_the_title($product_id);

        return [
            'slug' => (string) get_post_meta($product_id, self::META_SLUG, true),
            'wine_nr' => (string) get_post_meta($product_id, self::META_WINE_NR, true),
            'title' => (string) get_post_meta($product_id, self::META_TITLE, true),
            'energy' => (string) get_post_meta($product_id, self::META_ENERGY, true),
            'carbs' => (string) get_post_meta($product_id, self::META_CARBS, true),
            'sugar' => (string) get_post_meta($product_id, self::META_SUGAR, true),
            'minor' => (string) get_post_meta($product_id, self::META_MINOR, true),
            'minor_mode' => (string) get_post_meta($product_id, self::META_MINOR_MODE, true),
            'fat' => (string) get_post_meta($product_id, self::META_FAT, true),
            'saturates' => (string) get_post_meta($product_id, self::META_SATURATES, true),
            'protein' => (string) get_post_meta($product_id, self::META_PROTEIN, true),
            'salt' => (string) get_post_meta($product_id, self::META_SALT, true),
            'salt_natural' => (string) get_post_meta($product_id, self::META_SALT_NATURAL, true),
            'ingredients_html' => (string) get_post_meta($product_id, self::META_INGREDIENTS_HTML, true),
            'footnote' => (string) get_post_meta($product_id, self::META_FOOTNOTE, true),
            'pretable_notice' => (string) get_post_meta($product_id, self::META_PRETABLE, true),
            'source_file_url' => (string) get_post_meta($product_id, self::META_SOURCE_URL, true),
            'source_file_path' => (string) get_post_meta($product_id, self::META_SOURCE_PATH, true),
            'source_file_name' => (string) get_post_meta($product_id, self::META_SOURCE_NAME, true),
            'last_import' => (string) get_post_meta($product_id, self::META_LAST_IMPORT, true),
            'import_status' => (string) get_post_meta($product_id, self::META_IMPORT_STATUS, true),
            'import_message' => (string) get_post_meta($product_id, self::META_IMPORT_MESSAGE, true),
            'built_at' => (string) get_post_meta($product_id, self::META_BUILT_AT, true),
            'manual_config' => NutritionLabels_Manual_Builder::normalize_config((string) get_post_meta($product_id, self::META_MANUAL_CONFIG, true)),
            'import_snapshot' => NutritionLabels_Manual_Builder::normalize_config((string) get_post_meta($product_id, self::META_IMPORT_SNAPSHOT, true)),
            'remote_page_id' => (string) get_post_meta($product_id, self::META_REMOTE_PAGE_ID, true),
            'remote_page_url' => (string) get_post_meta($product_id, self::META_REMOTE_PAGE_URL, true),
            'display_config' => class_exists('NutritionLabels_Presentation') ? NutritionLabels_Presentation::normalize_config((string) get_post_meta($product_id, self::META_DISPLAY_CONFIG, true)) : [],
        ];
    }

    public static function save_label_data(int $product_id, array $data): void
    {
        $map = [
            self::META_SLUG => $data['slug'] ?? '',
            self::META_WINE_NR => $data['wine_nr'] ?? '',
            self::META_TITLE => $data['title'] ?? '',
            self::META_ENERGY => $data['energy'] ?? '',
            self::META_CARBS => $data['carbs'] ?? '',
            self::META_SUGAR => $data['sugar'] ?? '',
            self::META_MINOR => $data['minor'] ?? '',
            self::META_MINOR_MODE => $data['minor_mode'] ?? '',
            self::META_FAT => $data['fat'] ?? '',
            self::META_SATURATES => $data['saturates'] ?? '',
            self::META_PROTEIN => $data['protein'] ?? '',
            self::META_SALT => $data['salt'] ?? '',
            self::META_SALT_NATURAL => $data['salt_natural'] ?? '',
            self::META_INGREDIENTS_HTML => $data['ingredients_html'] ?? '',
            self::META_FOOTNOTE => $data['footnote'] ?? '',
            self::META_PRETABLE => $data['pretable_notice'] ?? '',
            self::META_SOURCE_URL => $data['source_file_url'] ?? '',
            self::META_SOURCE_PATH => $data['source_file_path'] ?? '',
            self::META_SOURCE_NAME => $data['source_file_name'] ?? '',
            self::META_LAST_IMPORT => $data['last_import'] ?? '',
            self::META_IMPORT_STATUS => $data['import_status'] ?? '',
            self::META_IMPORT_MESSAGE => $data['import_message'] ?? '',
            self::META_BUILT_AT => $data['built_at'] ?? '',
            self::META_MANUAL_CONFIG => wp_json_encode(NutritionLabels_Manual_Builder::sanitize_config($data['manual_config'] ?? []), JSON_UNESCAPED_UNICODE),
            self::META_IMPORT_SNAPSHOT => wp_json_encode(NutritionLabels_Manual_Builder::sanitize_config($data['import_snapshot'] ?? []), JSON_UNESCAPED_UNICODE),
            self::META_REMOTE_PAGE_ID => $data['remote_page_id'] ?? '',
            self::META_REMOTE_PAGE_URL => $data['remote_page_url'] ?? '',
            self::META_DISPLAY_CONFIG => wp_json_encode(class_exists('NutritionLabels_Presentation') ? NutritionLabels_Presentation::sanitize_config($data['display_config'] ?? []) : [], JSON_UNESCAPED_UNICODE),
        ];

        foreach ($map as $key => $value) {
            update_post_meta($product_id, $key, $value);
        }
    }

    public static function import_uploaded_file(array $file, int $product_id): array|WP_Error
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return new WP_Error('upload_missing', __('No import file uploaded.', 'nutrition-labels'));
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['html', 'htm', 'json', 'zip'], true)) {
            return new WP_Error('upload_type', __('Nur ZIP-, JSON- oder HTML-Dateien sind erlaubt.', 'nutrition-labels'));
        }

        $upload_dir = wp_upload_dir();
        if (!empty($upload_dir['error'])) {
            return new WP_Error('upload_dir', $upload_dir['error']);
        }

        $target_dir = trailingslashit($upload_dir['basedir']) . 'nutrition-labels-imports';
        if (!wp_mkdir_p($target_dir)) {
            return new WP_Error('upload_dir_create', __('Import-Ordner konnte nicht erstellt werden.', 'nutrition-labels'));
        }

        $filename = wp_unique_filename($target_dir, sanitize_file_name($file['name']));
        $target_path = trailingslashit($target_dir) . $filename;

        if (!@move_uploaded_file($file['tmp_name'], $target_path)) {
            return new WP_Error('upload_move', __('Hochgeladene Datei konnte nicht verschoben werden.', 'nutrition-labels'));
        }

        $parse = self::parse_import_file($target_path, $filename);
        if (is_wp_error($parse)) {
            return $parse;
        }

        $parse['source_file_path'] = $target_path;
        $parse['source_file_url'] = trailingslashit($upload_dir['baseurl']) . 'nutrition-labels-imports/' . rawurlencode($filename);
        $parse['source_file_name'] = $filename;
        $parse['last_import'] = current_time('mysql');
        $parse['import_status'] = 'success';
        $source = (string) ($parse['import_source'] ?? 'html');
        $parse['import_message'] = match ($source) {
            'json' => __('Import erfolgreich (Quelle: JSON).', 'nutrition-labels'),
            'zip' => __('Import erfolgreich (Quelle: ZIP).', 'nutrition-labels'),
            default => __('Import erfolgreich (Quelle: HTML).', 'nutrition-labels'),
        };

        if (!empty($parse['title'])) {
            $parse['title'] = self::format_label_title($parse['title']);
        }

        if (empty($parse['title'])) {
            $parse['title'] = self::format_label_title(get_the_title($product_id));
        }

        $parse['built_at'] = '';
        $parse['minor_mode'] = (string) ($parse['minor_mode'] ?? ($parse['minor'] !== '' ? 'text' : ''));
        $parse['fat'] = (string) ($parse['fat'] ?? '');
        $parse['saturates'] = (string) ($parse['saturates'] ?? '');
        $parse['protein'] = (string) ($parse['protein'] ?? '');
        $parse['salt'] = (string) ($parse['salt'] ?? '');
        $parse['salt_natural'] = (string) ($parse['salt_natural'] ?? '0');
        $parse['import_snapshot'] = NutritionLabels_Manual_Builder::sanitize_config($parse['manual_config'] ?? []);

        return $parse;
    }


    public static function clear_import_state(int $product_id): void
    {
        $data = self::get_label_data($product_id);
        $data['source_file_path'] = '';
        $data['source_file_url'] = '';
        $data['source_file_name'] = '';
        $data['last_import'] = '';
        $data['import_status'] = '';
        $data['import_message'] = '';
        $data['import_snapshot'] = NutritionLabels_Manual_Builder::default_config();
        self::save_label_data($product_id, $data);
    }


    public static function format_label_title(string $title): string
    {
        $title = trim(sanitize_text_field($title));
        if ($title === '') {
            return '';
        }

        if (!preg_match('/\be-?label\b/i', $title)) {
            $title .= ' E-Label';
        }

        return trim($title);
    }

    public static function parse_import_file(string $path, string $originalName = ''): array|WP_Error
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $html = '';
        $json = '';

        if ($extension === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($path) !== true) {
                return new WP_Error('zip_open', __('ZIP file could not be opened.', 'nutrition-labels'));
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if ($html === '' && preg_match('/\.html?$/i', $name)) {
                    $html = (string) $zip->getFromIndex($i);
                }
                if ($json === '' && preg_match('/\.json$/i', $name)) {
                    $json = (string) $zip->getFromIndex($i);
                }
            }
            $zip->close();
        } elseif ($extension === 'json') {
            $json = (string) file_get_contents($path);
        } else {
            $html = (string) file_get_contents($path);
        }

        $htmlParsed = null;
        if ($html !== '') {
            $htmlParsed = self::parse_html($html);
            if (is_wp_error($htmlParsed)) {
                $htmlParsed = null;
            }
        }

        $jsonParsed = null;
        if ($json !== '') {
            $jsonData = json_decode($json, true);
            if (is_array($jsonData) && self::is_plausible_wip_json($jsonData)) {
                $jsonParsed = self::parse_wip_json($jsonData);
                if (is_wp_error($jsonParsed)) {
                    $jsonParsed = null;
                }
            }
        }

        if (is_array($jsonParsed)) {
            $parsed = $jsonParsed;
            if (is_array($htmlParsed)) {
                $parsed = array_merge($parsed, array_filter([
                    'energy' => (string) ($htmlParsed['energy'] ?? ''),
                    'carbs' => (string) ($htmlParsed['carbs'] ?? ''),
                    'sugar' => (string) ($htmlParsed['sugar'] ?? ''),
                    'minor' => (string) ($htmlParsed['minor'] ?? ''),
                    'ingredients_html' => (string) ($htmlParsed['ingredients_html'] ?? ''),
                    'footnote' => (string) ($htmlParsed['footnote'] ?? ''),
                    'pretable_notice' => (string) ($htmlParsed['pretable_notice'] ?? ''),
                ], static fn($value) => $value !== ''));

                if (empty($parsed['title']) && !empty($htmlParsed['title'])) {
                    $parsed['title'] = (string) $htmlParsed['title'];
                }
            }

            return $parsed;
        }

        if (is_array($htmlParsed)) {
            return $htmlParsed;
        }

        if ($json !== '') {
            return new WP_Error('import_json_invalid', __('Die JSON-Daten im ZIP konnten nicht verarbeitet werden.', 'nutrition-labels'));
        }

        return new WP_Error('import_content_missing', __('Es konnten weder brauchbare JSON- noch HTML-Daten im Import gefunden werden.', 'nutrition-labels'));
    }

    private static function is_plausible_wip_json(array $jsonData): bool
    {
        return isset($jsonData['bezeichnung']) || isset($jsonData['weinNr']) || isset($jsonData['naehrwert']) || isset($jsonData['zutatenverzeichnis']);
    }

    private static function parse_wip_json(array $jsonData): array|WP_Error
    {
        $manualConfig = NutritionLabels_Manual_Builder::build_config_from_wip_json($jsonData);
        if (!NutritionLabels_Manual_Builder::has_meaningful_input($manualConfig)) {
            return new WP_Error('import_json_empty', __('Die JSON-Daten enthalten keine verwertbaren Produktinformationen.', 'nutrition-labels'));
        }

        $parsed = NutritionLabels_Manual_Builder::build_label_data($manualConfig);
        $parsed['manual_config'] = $manualConfig;

        if (!empty($jsonData['bezeichnung'])) {
            $parsed['title'] = sanitize_text_field((string) $jsonData['bezeichnung']);
        }
        if (!empty($jsonData['weinNr'])) {
            $parsed['wine_nr'] = sanitize_text_field((string) $jsonData['weinNr']);
        }

        $parsed['import_source'] = 'json';

        return $parsed;
    }

    public static function parse_html(string $html): array|WP_Error
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        if (!$loaded) {
            return new WP_Error('import_parse', __('HTML could not be parsed.', 'nutrition-labels'));
        }

        $xpath = new DOMXPath($dom);
        $tableRows = $xpath->query('//table//tbody/tr');

        $energy = '';
        $carbs = '';
        $sugar = '';
        $minor = '';
        $pretable = '';

        $energyLabels = ['brennwert', 'energie', 'energy', 'valore energetico', 'valeur énergétique'];
        $carbLabels = ['kohlenhydrate', 'carboidrati', 'carbohydrates', 'glucides'];
        $sugarLabels = ['davon zucker', 'di cui zuccheri', 'of which sugars', 'dont sucres'];
        $minorLabels = ['enthält geringfügige mengen', 'contains negligible amounts', 'contiene quantità trascurabili', 'contient des quantités négligeables'];

        if ($tableRows instanceof DOMNodeList) {
            $rowIndex = 0;
            foreach ($tableRows as $row) {
                $text = trim(preg_replace('/\s+/u', ' ', $row->textContent ?? ''));
                if ($text === '') {
                    continue;
                }
                $normalized = self::normalize_import_label($text);
                $rowIndex++;

                if ($energy === '' && self::starts_with_any($normalized, $energyLabels)) {
                    $energy = self::strip_any_prefix($text, $energyLabels);
                    continue;
                }
                if ($carbs === '' && self::starts_with_any($normalized, $carbLabels)) {
                    $carbs = self::strip_any_prefix($text, $carbLabels);
                    continue;
                }
                if ($sugar === '' && self::starts_with_any($normalized, $sugarLabels)) {
                    $sugar = self::strip_any_prefix($text, $sugarLabels);
                    continue;
                }
                if ($minor === '' && self::starts_with_any($normalized, $minorLabels)) {
                    $minor = $text;
                    continue;
                }

                if ($pretable === '' && !preg_match('/kJ|kcal|g/u', $text)) {
                    $pretable = $text;
                }

                // Fallback for HTML exports where labels were not matched cleanly.
                if ($rowIndex === 1 && $energy === '' && preg_match('/kJ|kcal/u', $text)) {
                    $energy = $text;
                } elseif ($rowIndex === 2 && $carbs === '' && preg_match('/g/u', $text)) {
                    $carbs = $text;
                } elseif ($rowIndex === 3 && $sugar === '' && preg_match('/g/u', $text)) {
                    $sugar = $text;
                }
            }
        }

        $ingredientNeedles = ['zutaten:', 'ingredients:', 'ingredienti:', 'ingrédients:', 'ingredients'];
        $ingredientsWrap = null;
        $candidateDivs = $xpath->query('//div');
        if ($candidateDivs instanceof DOMNodeList) {
            foreach ($candidateDivs as $div) {
                $text = trim(preg_replace('/\s+/u', ' ', $div->textContent ?? ''));
                if ($text === '') {
                    continue;
                }
                $normalized = self::normalize_import_label($text);
                foreach ($ingredientNeedles as $needle) {
                    if (str_contains($normalized, self::normalize_import_label($needle))) {
                        $ingredientsWrap = $div;
                        break 2;
                    }
                }
            }
        }

        if (!$ingredientsWrap) {
            return new WP_Error('import_content', __('No ingredient block could be found in the import.', 'nutrition-labels'));
        }

        $ingredientsHtml = '';
        $footnote = '';
        foreach ($ingredientsWrap->childNodes as $child) {
            if ($child instanceof DOMElement && strpos($child->getAttribute('class'), 'mb-4') !== false) {
                $footnote = trim(preg_replace('/\s+/u', ' ', $child->textContent ?? ''));
                continue;
            }
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'span') {
                $text = trim($child->textContent ?? '');
                if (self::starts_with_any(self::normalize_import_label($text), $ingredientNeedles)) {
                    continue;
                }
                $ingredientsHtml .= trim(self::inner_html($child));
            } elseif ($child instanceof DOMText) {
                $text = trim($child->textContent ?? '');
                if ($text !== '') {
                    $ingredientsHtml .= esc_html($text);
                }
            }
        }

        $ingredientsHtml = trim($ingredientsHtml);
        if ($ingredientsHtml === '') {
            $full = trim(self::inner_html($ingredientsWrap));
            foreach ($ingredientNeedles as $needle) {
                $full = preg_replace('/^<span>\s*' . preg_quote(rtrim($needle, ':'), '/') . ':?\s*<\/span>/iu', '', $full) ?? $full;
            }
            $ingredientsHtml = trim($full);
        }

        $htmlTitle = '';
        $h1 = $xpath->query('//h1')->item(0);
        if ($h1) {
            $htmlTitle = trim(preg_replace('/\s+/u', ' ', $h1->textContent ?? ''));
        }
        if ($htmlTitle === '') {
            $titleNode = $xpath->query('//title')->item(0);
            if ($titleNode) {
                $htmlTitle = trim(preg_replace('/\s+/u', ' ', $titleNode->textContent ?? ''));
                $htmlTitle = preg_replace('/\s*-\s*(Nährwertkennzeichnung|Nutrition declaration|Valori nutrizionali|Déclaration nutritionnelle)$/iu', '', $htmlTitle) ?? $htmlTitle;
                if (in_array(self::normalize_import_label($htmlTitle), ['nahrwerte und zutaten', 'nutritional values and ingredients', 'valori nutrizionali e ingredienti', 'valeurs nutritionnelles et ingrédients'], true)) {
                    $htmlTitle = '';
                }
            }
        }

        $manualConfig = NutritionLabels_Manual_Builder::build_config_from_ingredients_html(
            $ingredientsHtml,
            $footnote,
            ['product' => ['bezeichnung' => sanitize_text_field($htmlTitle)]]
        );

        return [
            'import_source' => 'html',
            'title' => sanitize_text_field($htmlTitle),
            'wine_nr' => '',
            'energy' => self::clean_inline_value($energy),
            'carbs' => self::clean_inline_value($carbs),
            'sugar' => self::clean_inline_value($sugar),
            'minor' => $minor,
            'ingredients_html' => wp_kses_post($ingredientsHtml),
            'footnote' => $footnote,
            'pretable_notice' => $pretable,
            'manual_config' => $manualConfig,
        ];
    }

    private static function normalize_import_label(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = remove_accents($value);
        $value = strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? $value));
        return $value;
    }

    private static function starts_with_any(string $text, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($text, self::normalize_import_label($prefix))) {
                return true;
            }
        }
        return false;
    }

    private static function strip_any_prefix(string $text, array $prefixes): string
    {
        foreach ($prefixes as $prefix) {
            $pattern = '/^' . preg_quote($prefix, '/') . '\s*/iu';
            $stripped = preg_replace($pattern, '', $text);
            if (is_string($stripped) && $stripped !== $text) {
                return trim($stripped);
            }
        }
        return trim($text);
    }

    public static function inner_html(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }
        return $html;
    }

    public static function clean_inline_value(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        return $value;
    }

    public static function extract_numeric_energy(array $labelData): array
    {
        $energy = (string) ($labelData['energy'] ?? '');
        preg_match('/(\d+)\s*kJ/i', $energy, $kj);
        preg_match('/(\d+)\s*kcal/i', $energy, $kcal);
        return [
            'kilojoules' => isset($kj[1]) ? (int) $kj[1] : 0,
            'calories' => isset($kcal[1]) ? (int) $kcal[1] : 0,
        ];
    }

    public static function extract_numeric_grams(string $value): float
    {
        $value = str_replace(',', '.', html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (preg_match('/(\d+(?:\.\d+)?)/', $value, $m)) {
            return (float) $m[1];
        }
        return 0.0;
    }
}
