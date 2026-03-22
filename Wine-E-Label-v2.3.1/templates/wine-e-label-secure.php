<?php
if (!defined('ABSPATH')) {
    exit;
}

$nl_supported_languages = [
    'de' => 'Deutsch',
    'en' => 'English',
    'it' => 'Italiano',
    'fr' => 'Français',
];

$nl_lang = isset($_GET['lang']) ? strtolower(sanitize_key((string) $_GET['lang'])) : 'de';
if (!isset($nl_supported_languages[$nl_lang])) {
    $nl_lang = 'de';
}

$nl_texts = [
    'de' => [
        'nutrition_per_100ml' => 'Nährwertangaben je 100ml',
        'energy' => 'Brennwert',
        'carbohydrates' => 'Kohlenhydrate',
        'sugar' => 'davon Zucker',
        'fat' => 'Fett',
        'saturates' => 'davon gesättigte Fettsäuren',
        'protein' => 'Eiweiß',
        'salt' => 'Salz',
        'salt_natural' => 'Angegebener Salzgehalt ist ausschließlich auf die Anwesenheit natürlich vorkommenden Natriums zurückzuführen.',
        'minor_text' => 'Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz',
        'ingredients' => 'Zutaten',
    ],
    'en' => [
        'nutrition_per_100ml' => 'Nutrition declaration per 100ml',
        'energy' => 'Energy',
        'carbohydrates' => 'Carbohydrates',
        'sugar' => 'of which sugars',
        'fat' => 'Fat',
        'saturates' => 'of which saturates',
        'protein' => 'Protein',
        'salt' => 'Salt',
        'salt_natural' => 'The salt content is exclusively due to the presence of naturally occurring sodium.',
        'minor_text' => 'Contains negligible amounts of fat, saturated fat, protein and salt',
        'ingredients' => 'Ingredients',
    ],
    'it' => [
        'nutrition_per_100ml' => 'Dichiarazione nutrizionale per 100ml',
        'energy' => 'Energia',
        'carbohydrates' => 'Carboidrati',
        'sugar' => 'di cui zuccheri',
        'fat' => 'Grassi',
        'saturates' => 'di cui acidi grassi saturi',
        'protein' => 'Proteine',
        'salt' => 'Sale',
        'salt_natural' => 'Il contenuto di sale è dovuto esclusivamente alla presenza di sodio naturalmente presente.',
        'minor_text' => 'Contiene quantità trascurabili di grassi, acidi grassi saturi, proteine e sale',
        'ingredients' => 'Ingredienti',
    ],
    'fr' => [
        'nutrition_per_100ml' => 'Déclaration nutritionnelle pour 100ml',
        'energy' => 'Énergie',
        'carbohydrates' => 'Glucides',
        'sugar' => 'dont sucres',
        'fat' => 'Matières grasses',
        'saturates' => 'dont acides gras saturés',
        'protein' => 'Protéines',
        'salt' => 'Sel',
        'salt_natural' => 'La teneur en sel est exclusivement due à la présence de sodium naturellement présent.',
        'minor_text' => 'Contient des quantités négligeables de matières grasses, d’acides gras saturés, de protéines et de sel',
        'ingredients' => 'Ingrédients',
    ],
];

$nl_phrase_map = [
    'de' => [],
    'en' => [
        'Stabilisatoren: enthält ' => 'Stabilisers: contains ',
        'Säureregulatoren: enthält ' => 'Acidity regulators: contains ',
        ' und/oder ' => ' and/or ',
        '* aus ökologischer Erzeugung' => '* from organic production',
        'nur für Retsina aus Griechenland' => 'only for Retsina from Greece',
        'Trauben' => 'Grapes',
        'Fülldosage' => 'Dosage liqueur',
        'Versanddosage' => 'Expedition liqueur',
        'Saccharose' => 'Sucrose',
        'konzentrierter Traubenmost' => 'Concentrated grape must',
        'rektifiziertes Traubenmostkonzentrat (RTK)' => 'Rectified concentrated grape must (RCGM)',
        'Weinsäure' => 'Tartaric acid',
        'Äpfelsäure' => 'Malic acid',
        'Milchsäure' => 'Lactic acid',
        'Calciumsulfat' => 'Calcium sulfate',
        'Citronensäure' => 'Citric acid',
        'Sulfite' => 'Sulphites',
        'Kaliumsorbat' => 'Potassium sorbate',
        'Lysozym' => 'Lysozyme',
        'L-Ascorbinsäure' => 'L-ascorbic acid',
        'Dimethyldicarbonat (DMDC)' => 'Dimethyl dicarbonate (DMDC)',
        'Metaweinsäure' => 'Metatartaric acid',
        'Gummiarabikum' => 'Gum arabic',
        'Hefe-Mannoproteine' => 'Yeast mannoproteins',
        'Carboxymethylcellulose' => 'Carboxymethylcellulose',
        'Kaliumpolyaspartat' => 'Potassium polyaspartate',
        'Fumarsäure' => 'Fumaric acid',
        'Argon' => 'Argon',
        'Stickstoff' => 'Nitrogen',
        'Kohlendioxid' => 'Carbon dioxide',
        'unter Schutzatmosphäre abgefüllt' => 'Bottled under protective atmosphere',
        'Die Abfüllung kann unter Schutzatmosphäre erfolgt sein' => 'Bottling may have taken place under protective atmosphere',
        'Aleppokiefernharz' => 'Aleppo pine resin',
        'Karamell' => 'Caramel',
        'Aromastoffe' => 'Flavourings',
        'Aromaextrakt' => 'Flavour extract',
        'Würzkräuter' => 'Herbs',
        'Gewürze' => 'Spices',
        'Farbstoffe' => 'Colours',
        'Ethylalkohol landwirtschaftlichen Ursprungs' => 'Ethyl alcohol of agricultural origin',
        'Neutralalkohol' => 'Neutral alcohol',
        'Agraralkohol' => 'Agricultural alcohol',
        'rektifizierter Alkohol' => 'Rectified alcohol',
        'landwirtschaftlicher Alkohol' => 'Agricultural alcohol',
    ],
    'it' => [
        'Stabilisatoren: enthält ' => 'Stabilizzanti: contiene ',
        'Säureregulatoren: enthält ' => 'Correttori di acidità: contiene ',
        ' und/oder ' => ' e/o ',
        '* aus ökologischer Erzeugung' => '* da produzione biologica',
        'nur für Retsina aus Griechenland' => 'solo per Retsina dalla Grecia',
        'Trauben' => 'Uve',
        'Fülldosage' => 'Dosaggio',
        'Versanddosage' => 'Dosaggio finale',
        'Saccharose' => 'Saccarosio',
        'konzentrierter Traubenmost' => 'Mosto d’uva concentrato',
        'rektifiziertes Traubenmostkonzentrat (RTK)' => 'Mosto d’uva concentrato rettificato (MCR)',
        'Weinsäure' => 'Acido tartarico',
        'Äpfelsäure' => 'Acido malico',
        'Milchsäure' => 'Acido lattico',
        'Calciumsulfat' => 'Solfato di calcio',
        'Citronensäure' => 'Acido citrico',
        'Sulfite' => 'Solfiti',
        'Kaliumsorbat' => 'Sorbato di potassio',
        'Lysozym' => 'Lisozima',
        'L-Ascorbinsäure' => 'Acido L-ascorbico',
        'Dimethyldicarbonat (DMDC)' => 'Dimetil dicarbonato (DMDC)',
        'Metaweinsäure' => 'Acido metatartarico',
        'Gummiarabikum' => 'Gomma arabica',
        'Hefe-Mannoproteine' => 'Mannoproteine di lievito',
        'Carboxymethylcellulose' => 'Carbossimetilcellulosa',
        'Kaliumpolyaspartat' => 'Poliaspartato di potassio',
        'Fumarsäure' => 'Acido fumarico',
        'Argon' => 'Argon',
        'Stickstoff' => 'Azoto',
        'Kohlendioxid' => 'Anidride carbonica',
        'unter Schutzatmosphäre abgefüllt' => 'Imbottigliato in atmosfera protettiva',
        'Die Abfüllung kann unter Schutzatmosphäre erfolgt sein' => 'L’imbottigliamento può essere avvenuto in atmosfera protettiva',
        'Aleppokiefernharz' => 'Resina di pino d’Aleppo',
        'Karamell' => 'Caramello',
        'Aromastoffe' => 'Aromi',
        'Aromaextrakt' => 'Estratto aromatico',
        'Würzkräuter' => 'Erbe aromatiche',
        'Gewürze' => 'Spezie',
        'Farbstoffe' => 'Coloranti',
        'Ethylalkohol landwirtschaftlichen Ursprungs' => 'Alcol etilico di origine agricola',
        'Neutralalkohol' => 'Alcol neutro',
        'Agraralkohol' => 'Alcol agricolo',
        'rektifizierter Alkohol' => 'Alcol rettificato',
        'landwirtschaftlicher Alkohol' => 'Alcol agricolo',
    ],
    'fr' => [
        'Stabilisatoren: enthält ' => 'Stabilisants : contient ',
        'Säureregulatoren: enthält ' => 'Correcteurs d’acidité : contient ',
        ' und/oder ' => ' et/ou ',
        '* aus ökologischer Erzeugung' => '* issu de l’agriculture biologique',
        'nur für Retsina aus Griechenland' => 'uniquement pour le Retsina de Grèce',
        'Trauben' => 'Raisins',
        'Fülldosage' => 'Liqueur de dosage',
        'Versanddosage' => 'Liqueur d’expédition',
        'Saccharose' => 'Saccharose',
        'konzentrierter Traubenmost' => 'Moût de raisin concentré',
        'rektifiziertes Traubenmostkonzentrat (RTK)' => 'Moût de raisin concentré rectifié (MCR)',
        'Weinsäure' => 'Acide tartrique',
        'Äpfelsäure' => 'Acide malique',
        'Milchsäure' => 'Acide lactique',
        'Calciumsulfat' => 'Sulfate de calcium',
        'Citronensäure' => 'Acide citrique',
        'Sulfite' => 'Sulfites',
        'Kaliumsorbat' => 'Sorbate de potassium',
        'Lysozym' => 'Lysozyme',
        'L-Ascorbinsäure' => 'Acide L-ascorbique',
        'Dimethyldicarbonat (DMDC)' => 'Diméthyl dicarbonate (DMDC)',
        'Metaweinsäure' => 'Acide métatartrique',
        'Gummiarabikum' => 'Gomme arabique',
        'Hefe-Mannoproteine' => 'Mannoprotéines de levure',
        'Carboxymethylcellulose' => 'Carboxyméthylcellulose',
        'Kaliumpolyaspartat' => 'Polyaspartate de potassium',
        'Fumarsäure' => 'Acide fumarique',
        'Argon' => 'Argon',
        'Stickstoff' => 'Azote',
        'Kohlendioxid' => 'Dioxyde de carbone',
        'unter Schutzatmosphäre abgefüllt' => 'Mis en bouteille sous atmosphère protectrice',
        'Die Abfüllung kann unter Schutzatmosphäre erfolgt sein' => 'La mise en bouteille peut avoir eu lieu sous atmosphère protectrice',
        'Aleppokiefernharz' => 'Résine de pin d’Alep',
        'Karamell' => 'Caramel',
        'Aromastoffe' => 'Arômes',
        'Aromaextrakt' => 'Extrait aromatique',
        'Würzkräuter' => 'Herbes aromatiques',
        'Gewürze' => 'Épices',
        'Farbstoffe' => 'Colorants',
        'Ethylalkohol landwirtschaftlichen Ursprungs' => 'Alcool éthylique d’origine agricole',
        'Neutralalkohol' => 'Alcool neutre',
        'Agraralkohol' => 'Alcool agricole',
        'rektifizierter Alkohol' => 'Alcool rectifié',
        'landwirtschaftlicher Alkohol' => 'Alcool agricole',
    ],
];

$nl_translate_value = static function (string $value, string $lang_code) use ($nl_texts, $nl_phrase_map): string {
    if ($value === '') {
        return '';
    }
    if ($lang_code === 'de') {
        return $value;
    }
    $sourceMinor = $nl_texts['de']['minor_text'];
    if ($value === $sourceMinor) {
        return $nl_texts[$lang_code]['minor_text'] ?? $value;
    }
    $map = $nl_phrase_map[$lang_code] ?? [];
    if ($map !== []) {
        uksort($map, static fn($a, $b) => strlen($b) <=> strlen($a));
        return strtr($value, $map);
    }
    return $value;
};

$nl_translate_html = static function (string $html, string $lang_code) use ($nl_phrase_map): string {
    if ($html === '' || $lang_code === 'de') {
        return $html;
    }
    $map = $nl_phrase_map[$lang_code] ?? [];
    if ($map === []) {
        return $html;
    }
    uksort($map, static fn($a, $b) => strlen($b) <=> strlen($a));
    return strtr($html, $map);
};

$nl_design_css = class_exists('NutritionLabels_Design') ? NutritionLabels_Design::build_remote_css() : '';
$nl_product_id = (int) ($nutrition_data['product_id'] ?? get_the_ID());
$nl_design_settings = class_exists('NutritionLabels_Design') ? NutritionLabels_Design::get_settings() : [];
$nl_presentation = class_exists('NutritionLabels_Presentation') ? NutritionLabels_Presentation::resolve($nl_product_id, (array) ($nutrition_data['display_config'] ?? [])) : [];
$nl_header_markup = class_exists('NutritionLabels_Design') ? NutritionLabels_Design::render_product_header_markup($nl_presentation, $nl_design_settings, 'nler') : '';
$nl_default_lang = 'de';
$nl_lang_buttons = '';
$nl_lang_panels = '';

foreach ($nl_supported_languages as $code => $label) {
    $panel_text = $nl_texts[$code] ?? $nl_texts['de'];
    if (!empty($nutrition_data['manual_config']) && class_exists('NutritionLabels_Manual_Builder')) {
        [$panel_ingredient_list, $panel_ingredient_footnote] = NutritionLabels_Manual_Builder::build_ingredients_html((array) $nutrition_data['manual_config'], $code);
    } else {
        $panel_ingredient_list = $nl_translate_html((string) ($nutrition_data['ingredient_list'] ?? ''), $code);
        $panel_ingredient_footnote = $nl_translate_value((string) ($nutrition_data['ingredient_footnote'] ?? ''), $code);
    }

    $panel_minor_text = ($nutrition_data['minor_mode'] ?? '') === 'text'
        ? (string) ($panel_text['minor_text'] ?? $nl_translate_value((string) ($nutrition_data['minor_text'] ?? ''), $code))
        : $nl_translate_value((string) ($nutrition_data['minor_text'] ?? ''), $code);
    $is_default = $code === $nl_default_lang;

    $nl_lang_buttons .= '<button type="button" class="nler-lang-button' . ($is_default ? ' is-active' : '') . '" data-lang="' . esc_attr($code) . '" aria-pressed="' . ($is_default ? 'true' : 'false') . '" aria-current="' . ($is_default ? 'true' : 'false') . '">' . esc_html(strtoupper($code)) . '</button>';

    $nl_lang_panels .= '<div class="nler-panel' . ($is_default ? ' is-active' : '') . '" data-lang="' . esc_attr($code) . '">';
    $nl_lang_panels .= '<div class="nler-label-card">';
    $nl_lang_panels .= '<table class="nler-label-table"><thead><tr><th>' . esc_html($panel_text['nutrition_per_100ml']) . '</th></tr></thead><tbody>';

    if (!empty($nutrition_data['pretable_notice'])) {
        $nl_lang_panels .= '<tr class="nler-label-pretable"><td><div class="nler-label-row"><span>' . esc_html((string) $nutrition_data['pretable_notice']) . '</span><span></span></div></td></tr>';
    }

    $nl_lang_panels .= '<tr><td><div class="nler-label-row"><span>' . esc_html($panel_text['energy']) . '</span><span>' . esc_html((string) ($nutrition_data['energy'] ?? '')) . '</span></div></td></tr>';
    $nl_lang_panels .= '<tr><td><div class="nler-label-row"><span>' . esc_html($panel_text['carbohydrates']) . '</span><span>' . esc_html((string) ($nutrition_data['carbohydrates'] ?? '')) . '</span></div></td></tr>';
    $nl_lang_panels .= '<tr><td><div class="nler-label-row"><span>' . esc_html($panel_text['sugar']) . '</span><span>' . esc_html((string) ($nutrition_data['sugar'] ?? '')) . '</span></div></td></tr>';

    if (($nutrition_data['minor_mode'] ?? '') === 'list') {
        foreach ([['fat', 'fat'], ['saturates', 'saturates'], ['protein', 'protein'], ['salt', 'salt']] as $pair) {
            $value = trim((string) ($nutrition_data[$pair[0]] ?? ''));
            if ($value !== '') {
                $nl_lang_panels .= '<tr><td><div class="nler-label-row"><span>' . esc_html($panel_text[$pair[1]]) . '</span><span>' . esc_html($value . ' g') . '</span></div></td></tr>';
            }
        }
        if (!empty($nutrition_data['salt_natural'])) {
            $nl_lang_panels .= '<tr class="nler-label-trace"><td>' . esc_html($panel_text['salt_natural']) . '</td></tr>';
        }
    } elseif ($panel_minor_text !== '') {
        $nl_lang_panels .= '<tr class="nler-label-trace"><td>' . esc_html($panel_minor_text) . '</td></tr>';
    }

    $nl_lang_panels .= '</tbody></table>';

    if ($panel_ingredient_list !== '') {
        $nl_lang_panels .= '<div class="nler-ingredients"><strong>' . esc_html($panel_text['ingredients']) . ':</strong> ' . wp_kses_post($panel_ingredient_list) . '</div>';
    }

    if ($panel_ingredient_footnote !== '') {
        $nl_lang_panels .= '<div class="nler-footnote">' . esc_html($panel_ingredient_footnote) . '</div>';
    }

    $nl_lang_panels .= '</div>';

    if (class_exists('NutritionLabels_Design')) {
        $nl_lang_panels .= NutritionLabels_Design::render_producer_markup($code, $nl_design_settings, 'nler');
    }

    $nl_lang_panels .= '</div>';
}

$nl_toggle_script = '(function(){var script=document.currentScript;var root=script&&script.closest?script.closest(".nler-remote"):null;if(!root){return;}var params=new URLSearchParams(window.location.search);var current=params.get("lang")||root.getAttribute("data-default-lang")||"de";var panels=root.querySelectorAll(".nler-panel");var buttons=root.querySelectorAll(".nler-lang-button");function apply(lang){var hasMatch=false;panels.forEach(function(p){var active=p.getAttribute("data-lang")===lang;p.classList.toggle("is-active",active);p.hidden=!active;p.setAttribute("aria-hidden",active?"false":"true");if(active){p.style.display="block";hasMatch=true;}else{p.style.display="none";}});buttons.forEach(function(b){var active=b.getAttribute("data-lang")===lang;b.classList.toggle("is-active",active);b.setAttribute("aria-pressed",active?"true":"false");b.setAttribute("aria-current",active?"true":"false");});if(!hasMatch&&lang!=="de"){apply("de");}}buttons.forEach(function(btn){btn.addEventListener("click",function(){var lang=btn.getAttribute("data-lang")||"de";var url=new URL(window.location.href);url.searchParams.set("lang",lang);window.history.replaceState({},"",url.toString());apply(lang);});});apply(current);})();';
?><!DOCTYPE html>
<html lang="<?php echo esc_attr($nl_lang); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow,noarchive">
  <title><?php echo esc_html($nutrition_data['product_title']); ?></title>
  <?php if ($nl_design_css !== '') : ?>
    <style id="wel-local-design"><?php echo $nl_design_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
  <?php endif; ?>
</head>
<body class="nler-label-body">
  <div class="nler-page-shell">
    <div class="nler-remote" data-default-lang="<?php echo esc_attr($nl_default_lang); ?>">
      <div class="nler-lang-switch"><?php echo $nl_lang_buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

      <?php if ($nl_header_markup !== '') : ?>
        <?php echo $nl_header_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <?php endif; ?>

      <?php echo $nl_lang_panels; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <script><?php echo $nl_toggle_script; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
    </div>
  </div>
</body>
</html>
