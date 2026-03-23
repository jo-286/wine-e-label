<?php

if (!defined('ABSPATH')) {
    exit;
}

class Wine_E_Label_Manual_Builder
{
    public static function get_category_options(): array
    {
        return [
            'Wein',
            'Schaumwein',
            'Perlwein',
            'Likörwein',
            'aromatischer Qualitätsschaumwein',
            'aromatisiertes weinhaltiges Getränk',
            'Federweißer / teilweise gegorener Traubenmost',
            'Traubensaft',
        ];
    }


    public static function get_translated_category_options(string $lang = 'de'): array
    {
        $options = [];
        foreach (self::get_category_options() as $category) {
            $options[$category] = self::get_category_label($category, $lang);
        }
        return $options;
    }

    public static function get_category_label(string $category, string $lang = 'de'): string
    {
        $bundle = self::get_language_bundle();
        return (string) ($bundle['categories'][$category][$lang] ?? $bundle['categories'][$category]['de'] ?? $category);
    }

    public static function get_language_bundle(): array
    {
        static $bundle = null;
        if ($bundle !== null) {
            return $bundle;
        }

        $bundle = [
            'categories' => [
                'Wein' => ['de' => 'Wein', 'en' => 'Wine', 'fr' => 'Vin', 'it' => 'Vino'],
                'Schaumwein' => ['de' => 'Schaumwein', 'en' => 'Sparkling wine', 'fr' => 'Vin mousseux', 'it' => 'Vino spumante'],
                'Perlwein' => ['de' => 'Perlwein', 'en' => 'Semi-sparkling wine', 'fr' => 'Vin pétillant', 'it' => 'Vino frizzante'],
                'Likörwein' => ['de' => 'Likörwein', 'en' => 'Liqueur wine', 'fr' => 'Vin de liqueur', 'it' => 'Vino liquoroso'],
                'aromatischer Qualitätsschaumwein' => ['de' => 'aromatischer Qualitätsschaumwein', 'en' => 'Aromatic quality sparkling wine', 'fr' => 'Vin mousseux de qualité aromatique', 'it' => 'Vino spumante di qualità aromatico'],
                'aromatisiertes weinhaltiges Getränk' => ['de' => 'aromatisiertes weinhaltiges Getränk', 'en' => 'Aromatised wine-based drink', 'fr' => 'Boisson aromatisée à base de vin', 'it' => 'Bevanda aromatizzata a base di vino'],
                'Federweißer / teilweise gegorener Traubenmost' => ['de' => 'Federweißer / teilweise gegorener Traubenmost', 'en' => 'Partially fermented grape must', 'fr' => 'Moût de raisin partiellement fermenté', 'it' => 'Mosto d’uva parzialmente fermentato'],
                'Traubensaft' => ['de' => 'Traubensaft', 'en' => 'Grape juice', 'fr' => 'Jus de raisin', 'it' => 'Succo d’uva'],
            ],
            'items' => [
                'Trauben' => ['de' => 'Trauben', 'en' => 'Grapes', 'fr' => 'Raisins', 'it' => 'Uve'],
                'Fülldosage' => ['de' => 'Fülldosage', 'en' => 'Dosage liqueur', 'fr' => 'Liqueur de dosage', 'it' => 'Liquore di dosaggio'],
                'Versanddosage' => ['de' => 'Versanddosage', 'en' => 'Expedition liqueur', 'fr' => 'Liqueur d’expédition', 'it' => 'Liquore di spedizione'],
                'Saccharose' => ['de' => 'Saccharose', 'en' => 'Sucrose', 'fr' => 'Saccharose', 'it' => 'Saccarosio'],
                'konzentrierter Traubenmost' => ['de' => 'konzentrierter Traubenmost', 'en' => 'Concentrated grape must', 'fr' => 'Moût de raisin concentré', 'it' => 'Mosto d’uva concentrato'],
                'rektifiziertes Traubenmostkonzentrat (RTK)' => ['de' => 'rektifiziertes Traubenmostkonzentrat (RTK)', 'en' => 'Rectified concentrated grape must (RCGM)', 'fr' => 'Moût de raisin concentré rectifié (MCR)', 'it' => 'Mosto d’uva concentrato rettificato (MCR)'],
                'Weinsäure' => ['de' => 'Weinsäure', 'en' => 'Tartaric acid', 'fr' => 'Acide tartrique', 'it' => 'Acido tartarico'],
                'Äpfelsäure' => ['de' => 'Äpfelsäure', 'en' => 'Malic acid', 'fr' => 'Acide malique', 'it' => 'Acido malico'],
                'Milchsäure' => ['de' => 'Milchsäure', 'en' => 'Lactic acid', 'fr' => 'Acide lactique', 'it' => 'Acido lattico'],
                'Calciumsulfat' => ['de' => 'Calciumsulfat', 'en' => 'Calcium sulfate', 'fr' => 'Sulfate de calcium', 'it' => 'Solfato di calcio'],
                'Citronensäure' => ['de' => 'Citronensäure', 'en' => 'Citric acid', 'fr' => 'Acide citrique', 'it' => 'Acido citrico'],
                'Sulfite' => ['de' => 'Sulfite', 'en' => 'Sulphites', 'fr' => 'Sulfites', 'it' => 'Solfiti'],
                'Kaliumsorbat' => ['de' => 'Kaliumsorbat', 'en' => 'Potassium sorbate', 'fr' => 'Sorbate de potassium', 'it' => 'Sorbato di potassio'],
                'Lysozym' => ['de' => 'Lysozym', 'en' => 'Lysozyme', 'fr' => 'Lysozyme', 'it' => 'Lisozima'],
                'L-Ascorbinsäure' => ['de' => 'L-Ascorbinsäure', 'en' => 'L-ascorbic acid', 'fr' => 'Acide L-ascorbique', 'it' => 'Acido L-ascorbico'],
                'Dimethyldicarbonat (DMDC)' => ['de' => 'Dimethyldicarbonat (DMDC)', 'en' => 'Dimethyl dicarbonate (DMDC)', 'fr' => 'Diméthyl dicarbonate (DMDC)', 'it' => 'Dimetil dicarbonato (DMDC)'],
                'Metaweinsäure' => ['de' => 'Metaweinsäure', 'en' => 'Metatartaric acid', 'fr' => 'Acide métatartrique', 'it' => 'Acido metatartarico'],
                'Gummiarabikum' => ['de' => 'Gummiarabikum', 'en' => 'Gum arabic', 'fr' => 'Gomme arabique', 'it' => 'Gomma arabica'],
                'Hefe-Mannoproteine' => ['de' => 'Hefe-Mannoproteine', 'en' => 'Yeast mannoproteins', 'fr' => 'Mannoprotéines de levure', 'it' => 'Mannoproteine di lievito'],
                'Carboxymethylcellulose' => ['de' => 'Carboxymethylcellulose', 'en' => 'Carboxymethylcellulose', 'fr' => 'Carboxyméthylcellulose', 'it' => 'Carbossimetilcellulosa'],
                'Kaliumpolyaspartat' => ['de' => 'Kaliumpolyaspartat', 'en' => 'Potassium polyaspartate', 'fr' => 'Polyaspartate de potassium', 'it' => 'Poliaspartato di potassio'],
                'Fumarsäure' => ['de' => 'Fumarsäure', 'en' => 'Fumaric acid', 'fr' => 'Acide fumarique', 'it' => 'Acido fumarico'],
                'Argon' => ['de' => 'Argon', 'en' => 'Argon', 'fr' => 'Argon', 'it' => 'Argon'],
                'Stickstoff' => ['de' => 'Stickstoff', 'en' => 'Nitrogen', 'fr' => 'Azote', 'it' => 'Azoto'],
                'Kohlendioxid' => ['de' => 'Kohlendioxid', 'en' => 'Carbon dioxide', 'fr' => 'Dioxyde de carbone', 'it' => 'Anidride carbonica'],
                'unter Schutzatmosphäre abgefüllt' => ['de' => 'unter Schutzatmosphäre abgefüllt', 'en' => 'Bottled under protective atmosphere', 'fr' => 'Mis en bouteille sous atmosphère protectrice', 'it' => 'Imbottigliato in atmosfera protettiva'],
                'Die Abfüllung kann unter Schutzatmosphäre erfolgt sein' => ['de' => 'Die Abfüllung kann unter Schutzatmosphäre erfolgt sein', 'en' => 'Bottling may have taken place under protective atmosphere', 'fr' => 'La mise en bouteille peut avoir eu lieu sous atmosphère protectrice', 'it' => 'L’imbottigliamento può essere avvenuto in atmosfera protettiva'],
                'Aleppokiefernharz' => ['de' => 'Aleppokiefernharz', 'en' => 'Aleppo pine resin', 'fr' => 'Résine de pin d’Alep', 'it' => 'Resina di pino d’Aleppo'],
                'Karamell' => ['de' => 'Karamell', 'en' => 'Caramel', 'fr' => 'Caramel', 'it' => 'Caramello'],
                'Aromastoffe' => ['de' => 'Aromastoffe', 'en' => 'Flavourings', 'fr' => 'Arômes', 'it' => 'Aromi'],
                'Aromaextrakt' => ['de' => 'Aromaextrakt', 'en' => 'Flavour extract', 'fr' => 'Extrait aromatique', 'it' => 'Estratto aromatico'],
                'Würzkräuter' => ['de' => 'Würzkräuter', 'en' => 'Herbs', 'fr' => 'Herbes aromatiques', 'it' => 'Erbe aromatiche'],
                'Gewürze' => ['de' => 'Gewürze', 'en' => 'Spices', 'fr' => 'Épices', 'it' => 'Spezie'],
                'Farbstoffe' => ['de' => 'Farbstoffe', 'en' => 'Colours', 'fr' => 'Colorants', 'it' => 'Coloranti'],
                'Ethylalkohol landwirtschaftlichen Ursprungs' => ['de' => 'Ethylalkohol landwirtschaftlichen Ursprungs', 'en' => 'Ethyl alcohol of agricultural origin', 'fr' => 'Alcool éthylique d’origine agricole', 'it' => 'Alcol etilico di origine agricola'],
                'Neutralalkohol' => ['de' => 'Neutralalkohol', 'en' => 'Neutral alcohol', 'fr' => 'Alcool neutre', 'it' => 'Alcol neutro'],
                'Agraralkohol' => ['de' => 'Agraralkohol', 'en' => 'Agricultural alcohol', 'fr' => 'Alcool agricole', 'it' => 'Alcol agricolo'],
                'rektifizierter Alkohol' => ['de' => 'rektifizierter Alkohol', 'en' => 'Rectified alcohol', 'fr' => 'Alcool rectifié', 'it' => 'Alcol rettificato'],
                'landwirtschaftlicher Alkohol' => ['de' => 'landwirtschaftlicher Alkohol', 'en' => 'Agricultural alcohol', 'fr' => 'Alcool agricole', 'it' => 'Alcol agricolo'],
            ],
            'prefixes' => [
                'acid' => ['de' => 'Säureregulatoren: enthält ', 'en' => 'Acidity regulators: contains ', 'fr' => 'Correcteurs d’acidité : contient ', 'it' => 'Correttori di acidità: contiene '],
                'stabilizers' => ['de' => 'Stabilisatoren: enthält ', 'en' => 'Stabilisers: contains ', 'fr' => 'Stabilisants : contient ', 'it' => 'Stabilizzanti: contiene '],
            ],
            'footnotes' => [
                'organic' => ['de' => '* aus ökologischer Erzeugung', 'en' => '* from organic production', 'fr' => '* issu de l’agriculture biologique', 'it' => '* da produzione biologica'],
                'retsina' => ['de' => 'nur für Retsina aus Griechenland', 'en' => 'only for Retsina from Greece', 'fr' => 'uniquement pour le Retsina de Grèce', 'it' => 'solo per il Retsina della Grecia'],
            ],
            'separators' => [
                'and_or' => ['de' => ' und/oder ', 'en' => ' and/or ', 'fr' => ' et/ou ', 'it' => ' e/o '],
            ],
            'ui' => [
                'ingredients' => ['de' => 'Zutaten', 'en' => 'Ingredients', 'fr' => 'Ingrédients', 'it' => 'Ingredienti'],
                'minor_text' => [
                    'de' => 'Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz',
                    'en' => 'Contains negligible amounts of fat, saturated fat, protein and salt',
                    'fr' => 'Contient des quantités négligeables de matières grasses, d’acides gras saturés, de protéines et de sel',
                    'it' => 'Contiene quantità trascurabili di grassi, acidi grassi saturi, proteine e sale',
                ],
            ],
        ];

        return $bundle;
    }

    public static function get_preview_texts(): array
    {
        $bundle = self::get_language_bundle();
        return [
            'de' => ['headline' => 'Nährwertangaben je 100ml', 'energy' => 'Brennwert', 'carbs' => 'Kohlenhydrate', 'sugar' => 'davon Zucker', 'fat' => 'Fett', 'saturates' => 'davon gesättigte Fettsäuren', 'protein' => 'Eiweiß', 'salt' => 'Salz', 'saltNatural' => 'Angegebener Salzgehalt ist ausschließlich auf die Anwesenheit natürlich vorkommenden Natriums zurückzuführen.', 'ingredients' => $bundle['ui']['ingredients']['de'], 'minor' => $bundle['ui']['minor_text']['de']],
            'en' => ['headline' => 'Nutrition declaration per 100ml', 'energy' => 'Energy', 'carbs' => 'Carbohydrates', 'sugar' => 'of which sugars', 'fat' => 'Fat', 'saturates' => 'of which saturates', 'protein' => 'Protein', 'salt' => 'Salt', 'saltNatural' => 'The stated salt content is exclusively due to naturally occurring sodium.', 'ingredients' => $bundle['ui']['ingredients']['en'], 'minor' => $bundle['ui']['minor_text']['en']],
            'fr' => ['headline' => 'Déclaration nutritionnelle pour 100ml', 'energy' => 'Énergie', 'carbs' => 'Glucides', 'sugar' => 'dont sucres', 'fat' => 'Matières grasses', 'saturates' => 'dont acides gras saturés', 'protein' => 'Protéines', 'salt' => 'Sel', 'saltNatural' => 'La teneur en sel indiquée est exclusivement due à la présence de sodium naturellement présent.', 'ingredients' => $bundle['ui']['ingredients']['fr'], 'minor' => $bundle['ui']['minor_text']['fr']],
            'it' => ['headline' => 'Dichiarazione nutrizionale per 100ml', 'energy' => 'Energia', 'carbs' => 'Carboidrati', 'sugar' => 'di cui zuccheri', 'fat' => 'Grassi', 'saturates' => 'di cui acidi grassi saturi', 'protein' => 'Proteine', 'salt' => 'Sale', 'saltNatural' => 'Il contenuto di sale indicato è dovuto esclusivamente alla presenza di sodio naturalmente presente.', 'ingredients' => $bundle['ui']['ingredients']['it'], 'minor' => $bundle['ui']['minor_text']['it']],
        ];
    }

    public static function translate_catalog_label(string $label, string $lang = 'de'): string
    {
        $bundle = self::get_language_bundle();
        return (string) ($bundle['items'][$label][$lang] ?? $bundle['items'][$label]['de'] ?? $label);
    }

    private static function translate_group_prefix(string $groupKey, string $fallback, string $lang = 'de'): string
    {
        $bundle = self::get_language_bundle();
        return (string) ($bundle['prefixes'][$groupKey][$lang] ?? $bundle['prefixes'][$groupKey]['de'] ?? $fallback);
    }

    private static function translate_note(string $note, string $lang = 'de'): string
    {
        $bundle = self::get_language_bundle();
        if ($note === 'nur für Retsina aus Griechenland') {
            return (string) ($bundle['footnotes']['retsina'][$lang] ?? $bundle['footnotes']['retsina']['de']);
        }
        return $note;
    }

    private static function get_and_or_separator(string $lang = 'de'): string
    {
        $bundle = self::get_language_bundle();
        return (string) ($bundle['separators']['and_or'][$lang] ?? $bundle['separators']['and_or']['de']);
    }

    private static function get_organic_footnote(string $lang = 'de'): string
    {
        $bundle = self::get_language_bundle();
        return (string) ($bundle['footnotes']['organic'][$lang] ?? $bundle['footnotes']['organic']['de']);
    }

    public static function get_catalog(): array
    {
        return [
            'base' => [
                'label' => 'Grundzutaten',
                'items' => [
                    'trauben' => ['label' => 'Trauben', 'bio' => true, 'default' => true],
                    'fuelldosage' => ['label' => 'Fülldosage', 'bio' => true, 'categories' => ['Schaumwein', 'aromatischer Qualitätsschaumwein']],
                    'versanddosage' => ['label' => 'Versanddosage', 'bio' => true, 'categories' => ['Schaumwein', 'aromatischer Qualitätsschaumwein']],
                ],
            ],
            'enrichment' => [
                'label' => 'Anreicherung',
                'toggle_label' => 'Wurde das Produkt angereichert?',
                'items' => [
                    'saccharose' => ['label' => 'Saccharose', 'bio' => true],
                    'ktm' => ['label' => 'konzentrierter Traubenmost', 'bio' => true],
                    'rtk' => ['label' => 'rektifiziertes Traubenmostkonzentrat (RTK)', 'bio' => true],
                ],
            ],
            'acid' => [
                'label' => 'Säureregulatoren',
                'toggle_label' => 'Wurde das Produkt gesäuert?',
                'supports_mode' => true,
                'alt_prefix' => 'Säureregulatoren: enthält ',
                'items' => [
                    'weinsaeure' => ['label' => 'Weinsäure', 'e' => 'E334'],
                    'aepfelsaeure' => ['label' => 'Äpfelsäure', 'e' => 'E296'],
                    'milchsaeure' => ['label' => 'Milchsäure', 'e' => 'E270'],
                    'calciumsulfat' => ['label' => 'Calciumsulfat', 'e' => 'E516', 'categories' => ['Likörwein']],
                    'citronensaeure' => ['label' => 'Citronensäure', 'e' => 'E330'],
                ],
            ],
            'conservants' => [
                'label' => 'Konservierungsstoffe und Antioxidationsmittel',
                'toggle_label' => 'Wurden Konservierungsstoffe (Sulfite etc.) verwendet?',
                'items' => [
                    'sulfite' => ['label' => 'Sulfite', 'allergen' => true],
                    'kaliumsorbat' => ['label' => 'Kaliumsorbat', 'e' => 'E202'],
                    'lysozym' => ['label' => 'Lysozym', 'e' => 'E1105'],
                    'ascorbinsaeure' => ['label' => 'L-Ascorbinsäure', 'e' => 'E300'],
                    'dmdc' => ['label' => 'Dimethyldicarbonat (DMDC)', 'e' => 'E242'],
                ],
            ],
            'stabilizers' => [
                'label' => 'Stabilisatoren',
                'toggle_label' => 'Wurden Stabilisatoren verwendet?',
                'supports_mode' => true,
                'alt_prefix' => 'Stabilisatoren: enthält ',
                'items' => [
                    'citronensaeure' => ['label' => 'Citronensäure', 'e' => 'E330'],
                    'metaweinsaeure' => ['label' => 'Metaweinsäure', 'e' => 'E353'],
                    'gummiarabikum' => ['label' => 'Gummiarabikum', 'e' => 'E414'],
                    'hefe_mannoproteine' => ['label' => 'Hefe-Mannoproteine'],
                    'carboxymethylcellulose' => ['label' => 'Carboxymethylcellulose', 'e' => 'E466'],
                    'kaliumpolyaspartat' => ['label' => 'Kaliumpolyaspartat', 'e' => 'E456'],
                    'fumarsaeure' => ['label' => 'Fumarsäure', 'e' => 'E297'],
                ],
            ],
            'gases' => [
                'label' => 'Gase und Packgase',
                'toggle_label' => 'Wurden Gase / Packgase verwendet?',
                'items' => [
                    'argon' => ['label' => 'Argon'],
                    'stickstoff' => ['label' => 'Stickstoff'],
                    'kohlendioxid' => ['label' => 'Kohlendioxid'],
                    'schutzatmosphaere' => ['label' => 'unter Schutzatmosphäre abgefüllt'],
                    'schutzatmosphaere_kann' => ['label' => 'Die Abfüllung kann unter Schutzatmosphäre erfolgt sein'],
                ],
            ],
            'other' => [
                'label' => 'sonstige Verfahren',
                'toggle_label' => 'Wurden sonstige Zutaten verwendet?',
                'items' => [
                    'aleppokiefernharz' => ['label' => 'Aleppokiefernharz', 'categories' => ['Wein'], 'note' => 'nur für Retsina aus Griechenland'],
                    'karamell' => ['label' => 'Karamell', 'e' => 'E150', 'categories' => ['Likörwein']],
                    'aromastoffe' => ['label' => 'Aromastoffe', 'categories' => ['aromatisiertes weinhaltiges Getränk']],
                    'aromaextrakt' => ['label' => 'Aromaextrakt', 'categories' => ['aromatisiertes weinhaltiges Getränk']],
                    'wuerzkraeuter' => ['label' => 'Würzkräuter', 'categories' => ['aromatisiertes weinhaltiges Getränk']],
                    'gewuerze' => ['label' => 'Gewürze', 'categories' => ['aromatisiertes weinhaltiges Getränk']],
                    'farbstoffe' => ['label' => 'Farbstoffe', 'categories' => ['aromatisiertes weinhaltiges Getränk']],
                    'ethylalkohol_landw' => ['label' => 'Ethylalkohol landwirtschaftlichen Ursprungs', 'categories' => ['Likörwein']],
                    'neutralalkohol' => ['label' => 'Neutralalkohol', 'categories' => ['Likörwein']],
                    'agraralkohol' => ['label' => 'Agraralkohol', 'categories' => ['Likörwein']],
                    'rektifizierter_alkohol' => ['label' => 'rektifizierter Alkohol', 'categories' => ['Likörwein']],
                    'landwirtschaftlicher_alkohol' => ['label' => 'landwirtschaftlicher Alkohol', 'categories' => ['Likörwein']],
                ],
            ],
        ];
    }

    public static function default_config(): array
    {
        $catalog = self::get_catalog();
        $config = [
            'product' => [
                'bezeichnung' => '',
                'wein_nr' => '',
                'ap_nr' => '',
                'kategorie' => '',
            ],
            'nutrition' => [
                'alkohol_gl' => '',
                'restzucker_gl' => '',
                'gesamtsaeure_gl' => '',
                'glycerin_mode' => 'standard',
                'glycerin_manual' => '',
                'restwerte_mode' => 'text',
                'fat' => '',
                'saturates' => '',
                'protein' => '',
                'salt' => '',
                'salt_natural' => '0',
            ],
            'groups' => [],
        ];

        foreach ($catalog as $groupKey => $group) {
            $config['groups'][$groupKey] = [
                'enabled' => $groupKey === 'base' ? '1' : '0',
                'mode' => !empty($group['supports_mode']) ? 'list' : '',
                'items' => [],
                'custom_items' => [],
            ];

            foreach ($group['items'] as $itemKey => $item) {
                $config['groups'][$groupKey]['items'][$itemKey] = [
                    'selected' => !empty($item['default']) ? '1' : '0',
                    'bio' => !empty($item['bio']) ? '0' : '',
                    'enumber' => !empty($item['e']) ? '0' : '',
                ];
            }
        }

        return $config;
    }

    public static function normalize_config($raw): array
    {
        $defaults = self::default_config();

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        if (!is_array($raw)) {
            return $defaults;
        }

        $config = $defaults;

        foreach (['bezeichnung', 'wein_nr', 'ap_nr', 'kategorie'] as $key) {
            if (isset($raw['product'][$key])) {
                $config['product'][$key] = (string) $raw['product'][$key];
            }
        }

        foreach (['alkohol_gl', 'restzucker_gl', 'gesamtsaeure_gl', 'glycerin_mode', 'glycerin_manual', 'restwerte_mode', 'fat', 'saturates', 'protein', 'salt', 'salt_natural'] as $key) {
            if (isset($raw['nutrition'][$key])) {
                $config['nutrition'][$key] = (string) $raw['nutrition'][$key];
            }
        }

        $catalog = self::get_catalog();
        foreach ($catalog as $groupKey => $group) {
            if (isset($raw['groups'][$groupKey]['enabled'])) {
                $config['groups'][$groupKey]['enabled'] = (string) $raw['groups'][$groupKey]['enabled'];
            }
            if (!empty($group['supports_mode']) && isset($raw['groups'][$groupKey]['mode'])) {
                $config['groups'][$groupKey]['mode'] = (string) $raw['groups'][$groupKey]['mode'];
            }
            foreach ($group['items'] as $itemKey => $item) {
                foreach (['selected', 'bio', 'enumber'] as $flag) {
                    if (isset($raw['groups'][$groupKey]['items'][$itemKey][$flag])) {
                        $config['groups'][$groupKey]['items'][$itemKey][$flag] = (string) $raw['groups'][$groupKey]['items'][$itemKey][$flag];
                    }
                }
                if (isset($raw['groups'][$groupKey]['items'][$itemKey]['mode'])) {
                    $mode = (string) $raw['groups'][$groupKey]['items'][$itemKey]['mode'];
                    if ($mode === 'name') {
                        $config['groups'][$groupKey]['items'][$itemKey]['selected'] = '1';
                        $config['groups'][$groupKey]['items'][$itemKey]['enumber'] = '0';
                    } elseif ($mode === 'e') {
                        $config['groups'][$groupKey]['items'][$itemKey]['selected'] = '1';
                        $config['groups'][$groupKey]['items'][$itemKey]['enumber'] = '1';
                    } elseif ($mode === 'off') {
                        $config['groups'][$groupKey]['items'][$itemKey]['selected'] = '0';
                        $config['groups'][$groupKey]['items'][$itemKey]['enumber'] = '0';
                    }
                }
            }

            if (!empty($raw['groups'][$groupKey]['custom_items']) && is_array($raw['groups'][$groupKey]['custom_items'])) {
                foreach ($raw['groups'][$groupKey]['custom_items'] as $customItem) {
                    if (!is_array($customItem)) {
                        continue;
                    }
                    $config['groups'][$groupKey]['custom_items'][] = [
                        'label' => self::resolve_custom_item_display($customItem),
                        'e' => '',
                        'selected' => (string) ($customItem['selected'] ?? '1'),
                        'enumber' => '0',
                    ];
                }
            }
        }

        return $config;
    }

    public static function sanitize_config($raw): array
    {
        $config = self::normalize_config($raw);
        $categories = self::get_category_options();

        $config['product']['bezeichnung'] = sanitize_text_field($config['product']['bezeichnung']);
        $config['product']['wein_nr'] = sanitize_text_field($config['product']['wein_nr']);
        $config['product']['ap_nr'] = sanitize_text_field($config['product']['ap_nr']);
        $config['product']['kategorie'] = in_array($config['product']['kategorie'], $categories, true) ? $config['product']['kategorie'] : '';

        foreach (['alkohol_gl', 'restzucker_gl', 'gesamtsaeure_gl', 'glycerin_manual', 'fat', 'saturates', 'protein', 'salt'] as $key) {
            $config['nutrition'][$key] = self::sanitize_number_string((string) $config['nutrition'][$key]);
        }

        $config['nutrition']['glycerin_mode'] = in_array($config['nutrition']['glycerin_mode'], ['standard', 'edelsuess', 'manual'], true)
            ? $config['nutrition']['glycerin_mode']
            : 'standard';
        $config['nutrition']['restwerte_mode'] = in_array($config['nutrition']['restwerte_mode'], ['text', 'list'], true)
            ? $config['nutrition']['restwerte_mode']
            : 'text';
        $config['nutrition']['salt_natural'] = $config['nutrition']['salt_natural'] === '1' ? '1' : '0';

        $catalog = self::get_catalog();
        foreach ($catalog as $groupKey => $group) {
            $config['groups'][$groupKey]['enabled'] = $config['groups'][$groupKey]['enabled'] === '1' ? '1' : '0';
            if (!empty($group['supports_mode'])) {
                $config['groups'][$groupKey]['mode'] = in_array($config['groups'][$groupKey]['mode'], ['list', 'alternative'], true)
                    ? $config['groups'][$groupKey]['mode']
                    : 'list';
            }
            foreach ($group['items'] as $itemKey => $item) {
                foreach (['selected', 'bio', 'enumber'] as $flag) {
                    if (array_key_exists($flag, $config['groups'][$groupKey]['items'][$itemKey])) {
                        $config['groups'][$groupKey]['items'][$itemKey][$flag] = $config['groups'][$groupKey]['items'][$itemKey][$flag] === '1' ? '1' : '0';
                    }
                }
            }

            $sanitizedCustomItems = [];
            foreach ((array) ($config['groups'][$groupKey]['custom_items'] ?? []) as $customItem) {
                if (!is_array($customItem)) {
                    continue;
                }
                $display = self::resolve_custom_item_display($customItem);
                if ($display === '') {
                    continue;
                }
                $sanitizedCustomItems[] = [
                    'label' => $display,
                    'e' => '',
                    'selected' => !empty($customItem['selected']) && (string) $customItem['selected'] !== '0' ? '1' : '0',
                    'enumber' => '0',
                ];
            }
            $config['groups'][$groupKey]['custom_items'] = $sanitizedCustomItems;
        }

        return $config;
    }

    public static function has_meaningful_input($raw): bool
    {
        $config = self::normalize_config($raw);
        $defaults = self::default_config();

        foreach (['bezeichnung', 'wein_nr', 'ap_nr', 'kategorie'] as $key) {
            if ((string) ($config['product'][$key] ?? '') !== (string) ($defaults['product'][$key] ?? '')) {
                return true;
            }
        }

        foreach (['alkohol_gl', 'restzucker_gl', 'gesamtsaeure_gl', 'glycerin_mode', 'glycerin_manual', 'restwerte_mode', 'fat', 'saturates', 'protein', 'salt', 'salt_natural'] as $key) {
            if ((string) ($config['nutrition'][$key] ?? '') !== (string) ($defaults['nutrition'][$key] ?? '')) {
                return true;
            }
        }

        foreach (($config['groups'] ?? []) as $groupKey => $group) {
            $defaultGroup = $defaults['groups'][$groupKey] ?? ['enabled' => '0', 'mode' => '', 'items' => [], 'custom_items' => []];
            if ((string) ($group['enabled'] ?? '0') !== (string) ($defaultGroup['enabled'] ?? '0')) {
                return true;
            }
            if ((string) ($group['mode'] ?? '') !== (string) ($defaultGroup['mode'] ?? '')) {
                return true;
            }
            foreach (($group['items'] ?? []) as $itemKey => $item) {
                $defaultItem = $defaultGroup['items'][$itemKey] ?? ['selected' => '0', 'bio' => '', 'enumber' => '0'];
                foreach (['selected', 'bio', 'enumber'] as $flag) {
                    if ((string) ($item[$flag] ?? '') !== (string) ($defaultItem[$flag] ?? '')) {
                        return true;
                    }
                }
            }
            foreach (($group['custom_items'] ?? []) as $customItem) {
                if (trim(self::resolve_custom_item_display((array) $customItem)) !== '') {
                    return true;
                }
            }
        }

        return false;
    }

    public static function derive_values(array $config): array
    {
        $config = self::sanitize_config($config);
        $alcoholGl = self::parse_number((string) $config['nutrition']['alkohol_gl']);
        $restzuckerGl = self::parse_number((string) $config['nutrition']['restzucker_gl']);
        $glycerinGl = self::determine_glycerin_gl($config);

        $sugar100 = $restzuckerGl / 10;
        $glycerin100 = $glycerinGl / 10;
        $alcohol100 = $alcoholGl / 10;
        $carbs100 = $sugar100 + $glycerin100;
        $kj = ($alcohol100 * 29) + ($sugar100 * 17) + ($glycerin100 * 10);
        $kcal = ($alcohol100 * 7) + ($sugar100 * 4) + ($glycerin100 * 2.4);
        $alcoholVol = $alcoholGl > 0 ? ($alcoholGl / 7.89) : 0;

        return [
            'alcohol_vol' => self::format_decimal($alcoholVol),
            'glycerin_gl' => self::format_decimal($glycerinGl),
            'energy' => self::format_energy($kj, $kcal),
            'carbs' => self::format_decimal($carbs100),
            'sugar' => self::format_decimal($sugar100),
        ];
    }

    public static function build_label_data(array $config, int $product_id = 0): array
    {
        $config = self::sanitize_config($config);
        $derived = self::derive_values($config);
        [$ingredientsHtml, $footnote] = self::build_ingredients_html($config);

        $title = trim((string) $config['product']['bezeichnung']);
        if ($title === '' && $product_id > 0) {
            $title = (string) get_the_title($product_id);
        }

        return [
            'manual_config' => $config,
            'slug' => Wine_E_Label_Importer::suggest_slug((string) $config['product']['wein_nr'], ''),
            'wine_nr' => (string) $config['product']['wein_nr'],
            'title' => Wine_E_Label_Importer::format_label_title($title),
            'energy' => $derived['energy'],
            'carbs' => $derived['carbs'] . ' g',
            'sugar' => $derived['sugar'] . ' g',
            'minor' => ($config['nutrition']['restwerte_mode'] ?? 'text') === 'text'
                ? (self::get_language_bundle()['ui']['minor_text']['de'] ?? 'Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz')
                : '',
            'minor_mode' => (string) ($config['nutrition']['restwerte_mode'] ?? 'text'),
            'fat' => (string) $config['nutrition']['fat'],
            'saturates' => (string) $config['nutrition']['saturates'],
            'protein' => (string) $config['nutrition']['protein'],
            'salt' => (string) $config['nutrition']['salt'],
            'salt_natural' => (string) $config['nutrition']['salt_natural'],
            'ingredients_html' => $ingredientsHtml,
            'footnote' => $footnote,
            'pretable_notice' => '',
        ];
    }

    public static function build_config_from_wip_json(array $json): array
    {
        $config = self::default_config();

        $config['product']['bezeichnung'] = sanitize_text_field((string) ($json['bezeichnung'] ?? ''));
        $config['product']['wein_nr'] = sanitize_text_field((string) ($json['weinNr'] ?? ''));
        $config['product']['ap_nr'] = sanitize_text_field((string) ($json['apNr'] ?? ''));
        $config['product']['kategorie'] = sanitize_text_field((string) ($json['kategorie'] ?? ''));

        $nutrition = is_array($json['naehrwert'] ?? null) ? $json['naehrwert'] : [];
        $config['nutrition']['alkohol_gl'] = self::sanitize_number_string((string) ($nutrition['alkohol'] ?? ''));
        $config['nutrition']['restzucker_gl'] = self::sanitize_number_string((string) ($nutrition['restzucker'] ?? ''));
        $config['nutrition']['gesamtsaeure_gl'] = self::sanitize_number_string((string) ($nutrition['gesamtsaeure'] ?? ''));

        $glycerinMode = self::normalize_lookup((string) ($nutrition['glycerinWert'] ?? 'standard'));
        if (in_array($glycerinMode, ['edelsuess', 'edelsuss'], true)) {
            $config['nutrition']['glycerin_mode'] = 'edelsuess';
        } elseif ($glycerinMode === 'manual') {
            $config['nutrition']['glycerin_mode'] = 'manual';
        } else {
            $config['nutrition']['glycerin_mode'] = 'standard';
        }
        $config['nutrition']['glycerin_manual'] = self::sanitize_number_string((string) ($nutrition['glycerin'] ?? ''));

        $restMode = self::normalize_lookup((string) ($nutrition['restWerte'] ?? 'text'));
        $config['nutrition']['restwerte_mode'] = $restMode === 'list' ? 'list' : 'text';
        $config['nutrition']['fat'] = self::sanitize_number_string((string) ($nutrition['fett'] ?? ''));
        $config['nutrition']['saturates'] = self::sanitize_number_string((string) ($nutrition['fettsaeuren'] ?? ''));
        $config['nutrition']['protein'] = self::sanitize_number_string((string) ($nutrition['eiweiss'] ?? ''));
        $config['nutrition']['salt'] = self::sanitize_number_string((string) ($nutrition['salz'] ?? ''));
        $config['nutrition']['salt_natural'] = !empty($nutrition['salzHinweis']) ? '1' : '0';

        $groupMap = [
            'grundzutaten' => 'base',
            'anreicherung' => 'enrichment',
            'saureregulatoren' => 'acid',
            'saeureregulatoren' => 'acid',
            'konservierungsstoffeundantioxidationsmittel' => 'conservants',
            'stabilisatoren' => 'stabilizers',
            'gaseundpackgase' => 'gases',
            'sonstigeverfahren' => 'other',
        ];

        $catalog = self::get_catalog();
        $ingredients = is_array($json['zutatenverzeichnis'] ?? null) ? $json['zutatenverzeichnis'] : [];
        foreach ($ingredients as $groupLabel => $groupData) {
            $normalizedGroup = self::normalize_lookup((string) $groupLabel);
            $groupKey = $groupMap[$normalizedGroup] ?? null;
            if (!$groupKey || !isset($catalog[$groupKey]) || !is_array($groupData)) {
                continue;
            }

            if ($groupKey !== 'base') {
                $config['groups'][$groupKey]['enabled'] = !empty($groupData['zutaten']) ? '1' : '0';
            }

            if (!empty($catalog[$groupKey]['supports_mode'])) {
                $showMode = self::normalize_lookup((string) ($groupData['show'] ?? 'list'));
                $config['groups'][$groupKey]['mode'] = $showMode === 'alternative' ? 'alternative' : 'list';
            }

            foreach ((array) ($groupData['zutaten'] ?? []) as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $entryLabel = (string) (($entry['zutat']['bezeichnung'] ?? '') ?: '');
                $entryENumber = (string) (($entry['zutat']['eNummer'] ?? '') ?: '');
                $itemKey = self::find_catalog_item_key($catalog[$groupKey]['items'], $entryLabel, $entryENumber);
                if ($itemKey === null) {
                    if ($groupKey === 'other') {
                        $config['groups'][$groupKey]['custom_items'][] = [
                            'label' => $entryLabel,
                            'e' => $entryENumber,
                            'selected' => '1',
                            'enumber' => !empty($entry['useENummer']) ? '1' : '0',
                        ];
                    }
                    continue;
                }

                $config['groups'][$groupKey]['items'][$itemKey]['selected'] = '1';
                if (array_key_exists('bio', $config['groups'][$groupKey]['items'][$itemKey])) {
                    $config['groups'][$groupKey]['items'][$itemKey]['bio'] = !empty($entry['bio']) ? '1' : '0';
                }
                if (array_key_exists('enumber', $config['groups'][$groupKey]['items'][$itemKey])) {
                    $config['groups'][$groupKey]['items'][$itemKey]['enumber'] = !empty($entry['useENummer']) ? '1' : '0';
                }
            }
        }

        return self::sanitize_config($config);
    }

    private static function determine_glycerin_gl(array $config): float
    {
        return match ((string) ($config['nutrition']['glycerin_mode'] ?? 'standard')) {
            'edelsuess' => 25.0,
            'manual' => self::parse_number((string) ($config['nutrition']['glycerin_manual'] ?? '')),
            default => self::parse_number((string) ($config['nutrition']['alkohol_gl'] ?? '')) * 0.1,
        };
    }

    public static function build_ingredients_html(array $config, string $lang = 'de'): array
    {
        $catalog = self::get_catalog();
        $category = (string) ($config['product']['kategorie'] ?? '');
        $parts = [];
        $hasBio = false;

        foreach ($catalog as $groupKey => $group) {
            if ($groupKey !== 'base' && (($config['groups'][$groupKey]['enabled'] ?? '0') !== '1')) {
                continue;
            }

            $selected = [];
            foreach ($group['items'] as $itemKey => $item) {
                $state = $config['groups'][$groupKey]['items'][$itemKey] ?? [];
                if (($state['selected'] ?? '0') !== '1') {
                    continue;
                }
                if (!empty($item['categories']) && ($category === '' || !in_array($category, $item['categories'], true))) {
                    continue;
                }
                $selected[] = self::format_piece($item, $state, $hasBio, $lang);
            }

            foreach ((array) ($config['groups'][$groupKey]['custom_items'] ?? []) as $customItem) {
                if (($customItem['selected'] ?? '0') !== '1') {
                    continue;
                }
                $formatted = self::format_custom_piece($customItem);
                if ($formatted !== '') {
                    $selected[] = $formatted;
                }
            }

            if ($selected === []) {
                continue;
            }

            $mode = (string) ($config['groups'][$groupKey]['mode'] ?? 'list');
            if (!empty($group['supports_mode']) && $mode === 'alternative') {
                $prefix = self::translate_group_prefix($groupKey, (string) ($group['alt_prefix'] ?? ($group['label'] . ': enthält ')), $lang);
                $parts[] = '<span>' . esc_html($prefix) . implode(self::get_and_or_separator($lang), $selected) . '</span>';
                continue;
            }

            foreach ($selected as $piece) {
                $parts[] = '<span>' . $piece . '</span>';
            }
        }

        return [implode(', ', $parts), $hasBio ? self::get_organic_footnote($lang) : ''];
    }

    private static function format_piece(array $item, array $state, bool &$hasBio, string $lang = 'de'): string
    {
        $showEnumber = !empty($state['enumber']) && !empty($item['e']) && empty($item['allergen']);
        $baseLabel = self::translate_catalog_label((string) $item['label'], $lang);
        $visibleLabel = $showEnumber ? (string) $item['e'] : $baseLabel;

        if (!empty($item['allergen'])) {
            $label = '<b>' . esc_html($baseLabel) . '</b>';
        } else {
            $label = esc_html($visibleLabel);
        }

        if (!empty($item['bio']) && !empty($state['bio'])) {
            $label .= '*';
            $hasBio = true;
        }

        return $label;
    }

    private static function format_custom_piece(array $item): string
    {
        $display = self::resolve_custom_item_display($item);
        if ($display === '') {
            return '';
        }
        return esc_html($display);
    }


    private static function resolve_custom_item_display(array $item): string
    {
        $label = self::normalize_custom_display_value((string) ($item['label'] ?? ''));
        $e = self::normalize_custom_display_value((string) ($item['e'] ?? ''));
        $showEnumber = !empty($item['enumber']) && $e !== '';

        if ($showEnumber) {
            return $e;
        }
        if ($label !== '') {
            return $label;
        }
        return $e;
    }

    private static function normalize_custom_display_value(string $value): string
    {
        $value = trim(sanitize_text_field($value));
        if ($value === '') {
            return '';
        }

        $compact = strtoupper(preg_replace('/[^A-Z0-9]/', '', $value) ?? '');
        if ($compact !== '') {
            if (preg_match('/^\d+[A-Z]*$/', $compact)) {
                return 'E' . $compact;
            }
            if (preg_match('/^E\d+[A-Z]*$/', $compact)) {
                return $compact;
            }
        }

        return $value;
    }

    public static function build_config_from_ingredients_html(string $ingredientsHtml, string $footnote = '', array $seed = []): array
    {
        $config = self::default_config();
        if (!empty($seed['product']) && is_array($seed['product'])) {
            foreach (['bezeichnung', 'wein_nr', 'ap_nr', 'kategorie'] as $key) {
                if (isset($seed['product'][$key])) {
                    $config['product'][$key] = (string) $seed['product'][$key];
                }
            }
        }

        $category = (string) ($config['product']['kategorie'] ?? '');
        $catalog = self::get_catalog();
        $chunks = preg_split('/\s*,\s*/u', trim(wp_strip_all_tags(str_replace(['</span>', '<br>', '<br/>', '<br />'], [', ', ', ', ', ', ', '], $ingredientsHtml)))) ?: [];
        $hasOrganicFootnote = stripos($footnote, 'ökolog') !== false || stripos($footnote, 'organic') !== false;

        foreach ($chunks as $chunk) {
            $chunk = trim((string) $chunk);
            if ($chunk === '') {
                continue;
            }
            $chunk = preg_replace('/^Zutaten:\s*/iu', '', $chunk) ?? $chunk;
            $groupKey = null;
            $groupPrefixes = [
                'acid' => ['Säureregulatoren: enthält ', 'Säureregulatoren:', 'Acid regulators: contains ', 'Acid regulators:', "Correcteurs d'acidité : ", "Correcteurs d'acidité: ", 'Correttori di acidità: '],
                'stabilizers' => ['Stabilisatoren: enthält ', 'Stabilisatoren:', 'Stabilizers: contains ', 'Stabilizers:', 'Stabilisants : ', 'Stabilisants:', 'Stabilizzanti: '],
            ];
            foreach ($groupPrefixes as $scanGroupKey => $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (stripos($chunk, $prefix) === 0) {
                        $groupKey = $scanGroupKey;
                        $chunk = trim(substr($chunk, strlen($prefix)));
                        break 2;
                    }
                }
            }

            $subparts = $groupKey ? preg_split('/\s+und\/oder\s+|\s+and\/or\s+|\s+et\/ou\s+|\s+e\/o\s+/u', $chunk) : [$chunk];
            foreach ($subparts as $part) {
                $part = trim((string) $part);
                if ($part === '') {
                    continue;
                }
                $bio = str_contains($part, '*') && $hasOrganicFootnote;
                $part = str_replace('*', '', $part);
                $label = trim(preg_replace('/\s*\(E\s*\d+[A-Z]?\)\s*$/iu', '', $part) ?? $part);
                preg_match('/\((E\s*\d+[A-Z]?)\)\s*$/iu', $part, $m);
                $e = isset($m[1]) ? strtoupper(preg_replace('/\s+/', '', $m[1])) : '';

                $matched = false;
                foreach ($catalog as $scanGroupKey => $group) {
                    if ($groupKey !== null && $scanGroupKey !== $groupKey) {
                        continue;
                    }
                    $itemKey = self::find_catalog_item_key($group['items'], $label, $e);
                    if ($itemKey === null) {
                        continue;
                    }
                    if (!empty($group['items'][$itemKey]['categories']) && ($category === '' || !in_array($category, $group['items'][$itemKey]['categories'], true))) {
                        continue;
                    }
                    if ($scanGroupKey !== 'base') {
                        $config['groups'][$scanGroupKey]['enabled'] = '1';
                    }
                    $config['groups'][$scanGroupKey]['items'][$itemKey]['selected'] = '1';
                    if (array_key_exists('bio', $config['groups'][$scanGroupKey]['items'][$itemKey])) {
                        $config['groups'][$scanGroupKey]['items'][$itemKey]['bio'] = $bio ? '1' : '0';
                    }
                    if (array_key_exists('enumber', $config['groups'][$scanGroupKey]['items'][$itemKey])) {
                        $config['groups'][$scanGroupKey]['items'][$itemKey]['enumber'] = $e !== '' ? '1' : '0';
                    }
                    $matched = true;
                    break;
                }

                if (!$matched) {
                    $config['groups']['other']['enabled'] = '1';
                    $config['groups']['other']['custom_items'][] = [
                        'label' => $e !== '' ? $e : $label,
                        'e' => '',
                        'selected' => '1',
                        'enumber' => '0',
                    ];
                }
            }
        }

        return self::sanitize_config($config);
    }

    private static function find_catalog_item_key(array $items, string $label, string $eNumber): ?string
    {
        $needleLabel = self::normalize_lookup($label);
        $needleENumber = strtoupper(trim($eNumber));

        foreach ($items as $itemKey => $item) {
            if ($needleLabel !== '' && self::normalize_lookup((string) ($item['label'] ?? '')) === $needleLabel) {
                return $itemKey;
            }
            if ($needleENumber !== '' && strtoupper((string) ($item['e'] ?? '')) === $needleENumber) {
                return $itemKey;
            }
        }

        return null;
    }

    private static function normalize_lookup(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (function_exists('remove_accents')) {
            $value = remove_accents($value);
        }
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/u', '', $value) ?? '';
        return trim($value);
    }

    private static function sanitize_number_string(string $value): string
    {
        $value = str_replace(["\u{00A0}", ' '], '', $value);
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value) ?? '';

        if (substr_count($value, '.') > 1) {
            $firstDot = strpos($value, '.');
            $value = substr($value, 0, $firstDot + 1) . str_replace('.', '', substr($value, $firstDot + 1));
        }

        return $value;
    }

    private static function parse_number(string $value): float
    {
        $value = self::sanitize_number_string($value);
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private static function format_decimal(float $value, int $decimals = 1): string
    {
        if (!is_finite($value) || abs($value) < 0.000001) {
            return '0';
        }

        $formatted = number_format($value, $decimals, ',', '');
        $formatted = rtrim($formatted, '0');
        return rtrim($formatted, ',');
    }

    private static function format_energy(float $kj, float $kcal): string
    {
        return self::format_decimal($kj) . ' kJ / ' . self::format_decimal($kcal) . ' kcal';
    }
}
