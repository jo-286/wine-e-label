<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!isset($_GET['page']) || (string) $_GET['page'] !== WINE_E_LABEL_ADMIN_PAGE_DESIGN) {
    return;
}

if (isset($_POST['submit-nutrition-design']) && class_exists('Wine_E_Label_Admin_Extended')) {
    Wine_E_Label_Admin_Extended::handle_design_submission();
}

if (function_exists('wp_enqueue_media')) {
    wp_enqueue_media();
}

$settings = Wine_E_Label_Design::get_settings();
$defaults = Wine_E_Label_Design::defaults();
$color_fields = Wine_E_Label_Design::color_fields();
$size_fields = Wine_E_Label_Design::size_fields();
$logo_fields = Wine_E_Label_Design::logo_fields();
$product_block_fields = Wine_E_Label_Design::product_block_fields();
$producer_fields = Wine_E_Label_Design::producer_fields();
$numeric_fields = Wine_E_Label_Design::numeric_fields();
$font_options = Wine_E_Label_Design::font_options();
$range_limits = Wine_E_Label_Design::range_limits();
$preview_translations = Wine_E_Label_Design::preview_translations();
$current_public_base = Wine_E_Label_URL::get_public_base_url(false);
$local_base = Wine_E_Label_URL::get_local_base_url();
$rest_enabled = get_option('wine_e_label_rest_enabled', 'no') === 'yes';
$rest_base_url = trim((string) get_option('wine_e_label_rest_base_url', ''));
$synced_count = isset($_GET['wine_e_label_design_synced']) ? max(0, (int) $_GET['wine_e_label_design_synced']) : null;
$failed_count = isset($_GET['wine_e_label_design_failed']) ? max(0, (int) $_GET['wine_e_label_design_failed']) : null;
$field_labels = [
    'page_bg' => __('Seitenhintergrund', 'wine-e-label'),
    'card_bg' => __('Kartenhintergrund', 'wine-e-label'),
    'table_head_bg' => __('Tabellenkopf', 'wine-e-label'),
    'text_color' => __('Fliesstext', 'wine-e-label'),
    'muted_color' => __('Sekundaertext', 'wine-e-label'),
    'border_color' => __('Rahmen', 'wine-e-label'),
    'base_font_size' => __('Basisschriftgröße (px)', 'wine-e-label'),
    'small_font_size' => __('Kleine Schrift (px)', 'wine-e-label'),
    'button_font_size' => __('Button-Schrift (px)', 'wine-e-label'),
    'font_family' => __('Schriftart', 'wine-e-label'),
    'panel_radius' => __('Panel-Radius (px)', 'wine-e-label'),
    'outer_width' => __('Aussenbreite (px)', 'wine-e-label'),
    'label_width' => __('Label-Breite (px)', 'wine-e-label'),
    'outer_padding_y' => __('Aussenabstand oben/unten (px)', 'wine-e-label'),
    'card_padding' => __('Innenabstand Karte (px)', 'wine-e-label'),
    'logo_alt' => __('Alternativtext Logo', 'wine-e-label'),
    'logo_max_height' => __('Logo-Hoehe (px)', 'wine-e-label'),
    'product_image_enabled' => __('Produktbild im E-Label anzeigen', 'wine-e-label'),
    'product_image_max_height' => __('Produktbild-Hoehe (px)', 'wine-e-label'),
    'wine_name_enabled' => __('Weinnamen im E-Label anzeigen', 'wine-e-label'),
    'wine_name_size' => __('Weinnamen-Schrift (px)', 'wine-e-label'),
    'vintage_enabled' => __('Jahrgang im E-Label anzeigen', 'wine-e-label'),
    'vintage_size' => __('Jahrgang-Schrift (px)', 'wine-e-label'),
    'subtitle_enabled' => __('Weinstil / Untertitel im E-Label anzeigen', 'wine-e-label'),
    'subtitle_size' => __('Weinstil-Schrift (px)', 'wine-e-label'),
    'producer_region' => __('Anbaugebiet', 'wine-e-label'),
    'producer_country' => __('Land', 'wine-e-label'),
    'producer_address' => __('Adresse des Weinguts', 'wine-e-label'),
    'custom_css' => __('Zusaetzliches CSS', 'wine-e-label'),
];
?>
<div class="wrap wel-design-admin-wrap">
  <h1><?php esc_html_e('Design anpassen', 'wine-e-label'); ?></h1>
  <p class="wel-design-lead"><?php esc_html_e('Diese Einstellungen steuern das E-Label-Design zentral im Hauptplugin. Lokale, Subdomain- und bei aktiver REST-Verbindung auch Receiver-E-Labels werden von hier aus versorgt.', 'wine-e-label'); ?></p>

  <?php if (isset($_GET['wine_e_label_design_notice']) && (string) $_GET['wine_e_label_design_notice'] === 'saved') : ?>
    <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Design-Einstellungen gespeichert.', 'wine-e-label'); ?></p></div>
  <?php endif; ?>

  <?php if ($synced_count !== null || $failed_count !== null) : ?>
    <div class="notice <?php echo ($failed_count ?? 0) > 0 ? 'notice-warning' : 'notice-info'; ?> is-dismissible">
      <p>
        <?php
        echo esc_html(sprintf(
          /* translators: 1: synced count, 2: failed count */
          __('Receiver-Synchronisierung: %1$d aktualisiert, %2$d fehlgeschlagen.', 'wine-e-label'),
          (int) ($synced_count ?? 0),
          (int) ($failed_count ?? 0)
        ));
        ?>
      </p>
    </div>
  <?php endif; ?>

  <div class="wel-design-info-grid">
    <div class="wel-design-info-card">
      <strong><?php esc_html_e('Wirkt auf', 'wine-e-label'); ?></strong>
      <p>
        <?php if ($rest_enabled && $rest_base_url !== '') : ?>
          <?php
          echo esc_html(sprintf(
            /* translators: %s: receiver URL */
        __('Lokale, Subdomain- und bereits veröffentlichte Receiver-E-Labels. Beim Speichern wird das Design auch an %s übertragen.', 'wine-e-label'),
            $rest_base_url
          ));
          ?>
        <?php else : ?>
          <?php esc_html_e('Lokale und Subdomain-E-Labels aus diesem Hauptplugin. Die Vorschau unten zeigt genau diesen Ausgabebereich.', 'wine-e-label'); ?>
        <?php endif; ?>
      </p>
      <code><?php echo esc_html($current_public_base !== '' ? $current_public_base : $local_base); ?>/[slug]</code>
    </div>
    <div class="wel-design-info-card wel-design-info-card-warning">
      <strong><?php esc_html_e('Hinweis', 'wine-e-label'); ?></strong>
      <p>
        <?php if ($rest_enabled && $rest_base_url !== '') : ?>
          <?php
          echo esc_html(sprintf(
            /* translators: %s: receiver URL */
        __('Entwürfe oder noch nicht veröffentlichte Receiver-Labels auf %s werden erst bei ihrer nächsten Erstellung bzw. Aktualisierung mit dem zentralen Design versorgt.', 'wine-e-label'),
            $rest_base_url
          ));
          ?>
        <?php else : ?>
          <?php esc_html_e('Sobald eine externe Receiver-/Zweitdomain konfiguriert ist, werden auch diese E-Labels zentral aus dem Hauptplugin heraus mit dem Design versorgt.', 'wine-e-label'); ?>
        <?php endif; ?>
      </p>
    </div>
  </div>

  <form method="post" id="wel-design-form">
    <?php wp_nonce_field('wine_e_label_save_design'); ?>
    <input type="hidden" name="submit-nutrition-design" value="1">

    <div class="wel-design-viewport">
      <div class="wel-design-shell">
        <div class="wel-design-left">
          <div class="wel-card">
            <div class="wel-card-head">
              <h2><?php esc_html_e('Farben', 'wine-e-label'); ?></h2>
              <button type="button" class="wel-icon-reset wel-reset-section" data-section="colors" title="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
            </div>
            <div class="wel-color-list">
              <?php foreach ($color_fields as $field) : ?>
                <div class="wel-color-row">
                  <label for="<?php echo esc_attr($field); ?>"><?php echo esc_html($field_labels[$field] ?? $field); ?></label>
                  <div class="wel-color-controls">
                    <input type="color" id="<?php echo esc_attr($field); ?>_picker" value="<?php echo esc_attr($settings[$field]); ?>" data-sync="<?php echo esc_attr($field); ?>">
                    <input type="text" id="<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr($settings[$field]); ?>" data-default="<?php echo esc_attr($defaults[$field]); ?>" class="regular-text wel-color-text">
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="<?php echo esc_attr($field); ?>" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="wel-card">
            <div class="wel-card-head">
        <h2><?php esc_html_e('Abstände und Größen', 'wine-e-label'); ?></h2>
              <button type="button" class="wel-icon-reset wel-reset-section" data-section="sizes" title="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
            </div>
            <div class="wel-field-stack">
              <?php foreach (['base_font_size', 'small_font_size', 'button_font_size', 'font_family', 'panel_radius', 'outer_width', 'label_width', 'outer_padding_y', 'card_padding'] as $field) : ?>
                <div class="wel-field-row">
                  <label for="<?php echo esc_attr($field); ?>"><?php echo esc_html($field_labels[$field] ?? $field); ?></label>
                  <?php if ($field === 'font_family') : ?>
                    <div class="wel-input-inline">
                      <select name="font_family" id="font_family" data-default="<?php echo esc_attr($defaults['font_family']); ?>">
                        <?php foreach ($font_options as $value => $label) : ?>
                          <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['font_family'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                      </select>
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="font_family" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                    </div>
                  <?php else : ?>
                    <div class="wel-input-inline">
                      <input type="number" id="<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr((string) $settings[$field]); ?>" data-default="<?php echo esc_attr((string) $defaults[$field]); ?>" min="0">
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="<?php echo esc_attr($field); ?>" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
              <div class="wel-field-row">
                <label for="custom_css"><?php echo esc_html($field_labels['custom_css']); ?></label>
                <div class="wel-input-inline wel-input-inline-textarea">
                  <textarea id="custom_css" name="custom_css" rows="5" data-default=""><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                    <button type="button" class="wel-inline-reset wel-reset-field wel-align-start" data-field="custom_css" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                </div>
              </div>
            </div>
          </div>

          <div class="wel-card">
            <div class="wel-card-head">
              <h2><?php esc_html_e('Weingutslogo', 'wine-e-label'); ?></h2>
              <button type="button" class="wel-icon-reset wel-reset-section" data-section="logo" title="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
            </div>
            <div class="wel-field-stack">
              <label class="wel-check-toggle" for="logo_enabled">
                <input type="checkbox" id="logo_enabled" name="logo_enabled" value="1" <?php checked($settings['logo_enabled'], '1'); ?> data-default="<?php echo esc_attr((string) $defaults['logo_enabled']); ?>">
                <span><?php esc_html_e('Logo oberhalb des E-Labels anzeigen', 'wine-e-label'); ?></span>
              </label>

                <p class="description wel-logo-legal-hint"><?php esc_html_e('Hinweis: Ein Weingutslogo oberhalb des E-Labels kann rechtlich möglicherweise als Werbung bewertet werden. Bitte vor der Aktivierung selbst prüfen.', 'wine-e-label'); ?></p>

              <div class="wel-logo-picker-shell">
                <div class="wel-logo-thumb<?php echo $settings['logo_url'] === '' ? ' is-empty' : ''; ?>" id="wel-logo-thumb">
                  <img id="wel-logo-thumb-image" src="<?php echo esc_url($settings['logo_url']); ?>" alt="<?php echo esc_attr($settings['logo_alt'] !== '' ? $settings['logo_alt'] : __('Weingutslogo', 'wine-e-label')); ?>">
                    <span class="wel-logo-thumb-empty"><?php esc_html_e('Noch kein Logo ausgewählt', 'wine-e-label'); ?></span>
                </div>

                <div class="wel-logo-controls">
                  <div class="wel-field-row">
                    <label for="logo_url"><?php esc_html_e('Logo-Datei', 'wine-e-label'); ?></label>
                    <div class="wel-input-inline wel-input-inline-logo">
                      <input type="text" id="logo_url" name="logo_url" value="<?php echo esc_attr($settings['logo_url']); ?>" data-default="<?php echo esc_attr((string) $defaults['logo_url']); ?>" readonly>
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="logo_url" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                    </div>
                  </div>

                  <div class="wel-logo-button-row">
                    <button type="button" class="button" id="wel-logo-select"><?php esc_html_e('Logo wählen oder hochladen', 'wine-e-label'); ?></button>
                    <button type="button" class="button" id="wel-logo-remove"><?php esc_html_e('Logo entfernen', 'wine-e-label'); ?></button>
                  </div>
                </div>
              </div>

              <div class="wel-field-row">
                <label for="logo_alt"><?php echo esc_html($field_labels['logo_alt']); ?></label>
                <div class="wel-input-inline">
                  <input type="text" id="logo_alt" name="logo_alt" value="<?php echo esc_attr($settings['logo_alt']); ?>" data-default="<?php echo esc_attr((string) $defaults['logo_alt']); ?>">
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="logo_alt" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                </div>
              </div>

              <div class="wel-field-row">
                <label for="logo_max_height"><?php echo esc_html($field_labels['logo_max_height']); ?></label>
                <div class="wel-input-inline">
                  <input type="number" id="logo_max_height" name="logo_max_height" value="<?php echo esc_attr((string) $settings['logo_max_height']); ?>" data-default="<?php echo esc_attr((string) $defaults['logo_max_height']); ?>" min="40">
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="logo_max_height" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                </div>
              </div>
            </div>
          </div>

          <div class="wel-card">
            <div class="wel-card-head">
              <h2><?php esc_html_e('Produktkopf', 'wine-e-label'); ?></h2>
              <button type="button" class="wel-icon-reset wel-reset-section" data-section="product_blocks" title="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
            </div>
            <div class="wel-field-stack">
                <p class="description"><?php esc_html_e('Diese Schalter gelten global für alle E-Labels. Die konkreten Inhalte kommen pro Produkt aus dem WooCommerce-Produkt bzw. aus den produktspezifischen E-Label-Feldern.', 'wine-e-label'); ?></p>

              <label class="wel-check-toggle" for="product_image_enabled">
                <input type="checkbox" id="product_image_enabled" name="product_image_enabled" value="1" <?php checked($settings['product_image_enabled'], '1'); ?> data-default="<?php echo esc_attr((string) $defaults['product_image_enabled']); ?>">
                <span><?php echo esc_html($field_labels['product_image_enabled']); ?></span>
              </label>
              <div class="wel-field-row">
                <label for="product_image_max_height"><?php echo esc_html($field_labels['product_image_max_height']); ?></label>
                <div class="wel-input-inline">
                  <input type="number" id="product_image_max_height" name="product_image_max_height" value="<?php echo esc_attr((string) $settings['product_image_max_height']); ?>" data-default="<?php echo esc_attr((string) $defaults['product_image_max_height']); ?>" min="60">
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="product_image_max_height" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                </div>
              </div>

              <label class="wel-check-toggle" for="wine_name_enabled">
                <input type="checkbox" id="wine_name_enabled" name="wine_name_enabled" value="1" <?php checked($settings['wine_name_enabled'], '1'); ?> data-default="<?php echo esc_attr((string) $defaults['wine_name_enabled']); ?>">
                <span><?php echo esc_html($field_labels['wine_name_enabled']); ?></span>
              </label>
              <div class="wel-field-row">
                <label for="wine_name_size"><?php echo esc_html($field_labels['wine_name_size']); ?></label>
                <div class="wel-input-inline">
                  <input type="number" id="wine_name_size" name="wine_name_size" value="<?php echo esc_attr((string) $settings['wine_name_size']); ?>" data-default="<?php echo esc_attr((string) $defaults['wine_name_size']); ?>" min="14">
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="wine_name_size" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                </div>
              </div>

              <label class="wel-check-toggle" for="vintage_enabled">
                <input type="checkbox" id="vintage_enabled" name="vintage_enabled" value="1" <?php checked($settings['vintage_enabled'], '1'); ?> data-default="<?php echo esc_attr((string) $defaults['vintage_enabled']); ?>">
                <span><?php echo esc_html($field_labels['vintage_enabled']); ?></span>
              </label>
              <div class="wel-field-row">
                <label for="vintage_size"><?php echo esc_html($field_labels['vintage_size']); ?></label>
                <div class="wel-input-inline">
                  <input type="number" id="vintage_size" name="vintage_size" value="<?php echo esc_attr((string) $settings['vintage_size']); ?>" data-default="<?php echo esc_attr((string) $defaults['vintage_size']); ?>" min="12">
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="vintage_size" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                </div>
              </div>

              <label class="wel-check-toggle" for="subtitle_enabled">
                <input type="checkbox" id="subtitle_enabled" name="subtitle_enabled" value="1" <?php checked($settings['subtitle_enabled'], '1'); ?> data-default="<?php echo esc_attr((string) $defaults['subtitle_enabled']); ?>">
                <span><?php echo esc_html($field_labels['subtitle_enabled']); ?></span>
              </label>
              <div class="wel-field-row">
                <label for="subtitle_size"><?php echo esc_html($field_labels['subtitle_size']); ?></label>
                <div class="wel-input-inline">
                  <input type="number" id="subtitle_size" name="subtitle_size" value="<?php echo esc_attr((string) $settings['subtitle_size']); ?>" data-default="<?php echo esc_attr((string) $defaults['subtitle_size']); ?>" min="12">
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="subtitle_size" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                </div>
              </div>
            </div>
          </div>

          <div class="wel-card">
            <div class="wel-card-head">
              <h2><?php esc_html_e('Erzeugerdaten', 'wine-e-label'); ?></h2>
              <button type="button" class="wel-icon-reset wel-reset-section" data-section="producer" title="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
            </div>
            <div class="wel-field-stack">
                <p class="description"><?php esc_html_e('Diese Angaben werden global unterhalb der Nährwerttabelle angezeigt. Die Feldlabels werden im E-Label automatisch in DE/EN/IT/FR übersetzt.', 'wine-e-label'); ?></p>

              <div class="wel-field-row">
                <label for="producer_region"><?php echo esc_html($field_labels['producer_region']); ?></label>
                <div class="wel-input-inline">
                  <input type="text" id="producer_region" name="producer_region" value="<?php echo esc_attr($settings['producer_region']); ?>" data-default="<?php echo esc_attr((string) $defaults['producer_region']); ?>">
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="producer_region" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                </div>
              </div>

              <div class="wel-field-row">
                <label for="producer_country"><?php echo esc_html($field_labels['producer_country']); ?></label>
                <div class="wel-input-inline">
                  <input type="text" id="producer_country" name="producer_country" value="<?php echo esc_attr($settings['producer_country']); ?>" data-default="<?php echo esc_attr((string) $defaults['producer_country']); ?>">
                    <button type="button" class="wel-inline-reset wel-reset-field" data-field="producer_country" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                </div>
              </div>

              <div class="wel-field-row">
                <label for="producer_address"><?php echo esc_html($field_labels['producer_address']); ?></label>
                <div class="wel-input-inline wel-input-inline-textarea">
                  <textarea id="producer_address" name="producer_address" rows="4" data-default=""><?php echo esc_textarea($settings['producer_address']); ?></textarea>
                    <button type="button" class="wel-inline-reset wel-reset-field wel-align-start" data-field="producer_address" title="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Feld zurücksetzen', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
                </div>
              </div>
            </div>
          </div>

          <p class="wel-save-row">
            <button class="button button-primary button-large wel-save-button"><?php esc_html_e('Design speichern und auf alle Domains aktualisieren', 'wine-e-label'); ?></button>
          </p>
        </div>

        <div class="wel-design-right">
          <div class="wel-card wel-preview-card-admin">
            <div class="wel-card-head">
              <h2><?php esc_html_e('Live-Vorschau', 'wine-e-label'); ?></h2>
              <button type="button" class="wel-icon-reset wel-reset-preview" title="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>" aria-label="<?php esc_attr_e('Standard laden', 'wine-e-label'); ?>"><span class="dashicons dashicons-image-rotate"></span></button>
            </div>
            <p class="description wel-preview-hint"><?php esc_html_e('Die Vorschau aktualisiert sich direkt beim Ändern und bezieht sich nur auf lokale/Subdomain-E-Labels.', 'wine-e-label'); ?></p>
            <div id="wel-preview-shell">
              <style id="wel-admin-live-style"></style>
              <div id="wel-preview-stage">
                <div id="wel-preview-canvas"><?php echo Wine_E_Label_Design::sample_preview_fragment(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<style>
  .wel-design-lead{max-width:920px;margin-bottom:18px}
  .wel-design-info-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px;max-width:1180px;margin:0 0 18px}
  .wel-design-info-card{background:#fff;border:1px solid #d9e1ea;border-radius:16px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.03)}
  .wel-design-info-card strong{display:block;margin-bottom:8px;font-size:14px}
  .wel-design-info-card p{margin:0 0 10px;line-height:1.55;color:#445566}
  .wel-design-info-card code{display:block;padding:10px 12px;border:1px solid #dde5ee;border-radius:12px;background:#f7f9fb;word-break:break-all}
  .wel-design-info-card-warning{border-color:#ead7ca;background:linear-gradient(180deg,#fff9f5 0,#fff 100%)}
  .wel-design-viewport{overflow-x:auto;overflow-y:visible;padding-bottom:8px}
  .wel-design-shell{display:flex;flex-wrap:nowrap;align-items:flex-start;gap:18px;min-width:920px;max-width:1320px}
  .wel-design-left{flex:0 0 420px;width:420px;min-width:420px;display:flex;flex-direction:column;gap:14px}
  .wel-design-right{flex:0 0 860px;width:860px;min-width:860px}
  .wel-card{background:#fff;border:1px solid #d9e1ea;border-radius:16px;padding:10px 12px;box-shadow:0 1px 2px rgba(16,24,40,.03)}
  .wel-card-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin:-10px -12px 10px -12px;padding:10px 12px;background:#eef3f7;border-bottom:1px solid #dce5ee;border-top-left-radius:16px;border-top-right-radius:16px}
  .wel-card-head h2{margin:0;font-size:14px;line-height:1.2;color:#435466;font-weight:600}
  .wel-icon-reset,.wel-inline-reset{display:inline-flex;align-items:center;justify-content:center;border:1px solid #d7e0ea;background:#fff;color:#8392a3;cursor:pointer;padding:0}
  .wel-icon-reset{width:20px;height:20px;border-radius:999px}
  .wel-inline-reset{width:26px;height:26px;border-radius:8px}
  .wel-icon-reset:hover,.wel-inline-reset:hover{background:#f5f8fb;color:#556476}
  .wel-icon-reset .dashicons,.wel-inline-reset .dashicons{font-size:12px;line-height:1;width:12px;height:12px}
  .wel-color-list,.wel-field-stack{display:flex;flex-direction:column;gap:8px}
  .wel-color-row,.wel-field-row{display:flex;flex-direction:column;gap:4px}
  .wel-color-row label,.wel-field-row label{font-weight:600;font-size:11px;line-height:1.2;color:#526173}
  .wel-color-controls{display:grid;grid-template-columns:34px minmax(0,1fr) 26px;gap:6px;align-items:center}
  .wel-color-controls input[type=color]{width:34px;height:28px;padding:1px;border:1px solid #d0d7de;border-radius:6px;background:#fff}
  .wel-field-row input[type=number],.wel-field-row input[type=text],.wel-field-row select,.wel-field-row textarea,.wel-color-controls input[type=text]{width:100%;max-width:none;margin:0;min-height:30px;border-color:#cfd7df;border-radius:6px}
  .wel-field-row textarea{font-family:Consolas,Monaco,monospace;min-height:96px;padding-top:6px;padding-bottom:6px}
  .wel-input-inline{display:grid;grid-template-columns:minmax(0,1fr) 26px;gap:6px;align-items:center}
  .wel-input-inline-textarea{align-items:start}
  .wel-check-toggle{display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1px solid #dbe4ed;border-radius:12px;background:#f7f9fb;color:#233142;font-weight:600}
  .wel-check-toggle input{margin:2px 0 0}
  .wel-logo-legal-hint{margin:0;padding:12px 14px;border:1px solid #ead7ca;border-radius:12px;background:linear-gradient(180deg,#fff9f5 0,#fff 100%);color:#7a4b22;line-height:1.55}
  .wel-logo-picker-shell{display:grid;grid-template-columns:120px minmax(0,1fr);gap:14px;align-items:start}
  .wel-logo-thumb{display:flex;align-items:center;justify-content:center;min-height:96px;padding:12px;border:1px solid #dbe4ed;border-radius:14px;background:#f7f9fb}
  .wel-logo-thumb img{display:block;max-width:100%;max-height:92px;width:auto;height:auto;object-fit:contain}
  .wel-logo-thumb-empty{display:none;font-size:12px;line-height:1.45;color:#64748b;text-align:center}
  .wel-logo-thumb.is-empty img{display:none}
  .wel-logo-thumb.is-empty .wel-logo-thumb-empty{display:block}
  .wel-logo-controls{display:flex;flex-direction:column;gap:10px}
  .wel-logo-button-row{display:flex;flex-wrap:wrap;gap:8px}
  .wel-align-start{align-self:start}
  .wel-save-row{margin:0}
  .wel-save-button{display:block;width:100%;text-align:center}
  .wel-preview-card-admin .description{margin:0}
  #wel-preview-shell{background:#f7f9fb;border:1px solid #dde5ee;border-radius:14px;padding:14px;overflow:auto;min-height:760px}
  #wel-preview-stage{min-height:740px;padding:14px;border:1px solid #dce5ee;border-radius:14px;background:#fff;box-shadow:0 10px 28px rgba(16,24,40,.06)}
  #wel-preview-canvas{min-height:980px}
  @media (max-width: 960px){
    .wel-design-info-grid{grid-template-columns:1fr}
    .wel-logo-picker-shell{grid-template-columns:1fr}
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  try {
    const form = document.getElementById('wel-design-form');
    if (!form) return;

    const defaults = <?php echo wp_json_encode($defaults); ?>;
    const colorFields = <?php echo wp_json_encode($color_fields); ?>;
    const sizeFields = <?php echo wp_json_encode($size_fields); ?>;
    const logoFields = <?php echo wp_json_encode($logo_fields); ?>;
    const productBlockFields = <?php echo wp_json_encode($product_block_fields); ?>;
    const producerFields = <?php echo wp_json_encode($producer_fields); ?>;
    const numericFields = <?php echo wp_json_encode($numeric_fields); ?>;
    const fieldLimits = <?php echo wp_json_encode($range_limits); ?>;
    const previewTranslations = <?php echo wp_json_encode($preview_translations); ?>;
    const mediaTexts = {
      title: <?php echo wp_json_encode(__('Weingutslogo wählen', 'wine-e-label')); ?>,
      button: <?php echo wp_json_encode(__('Dieses Logo verwenden', 'wine-e-label')); ?>
    };
    const inputs = form.querySelectorAll('input, select, textarea');
    const previewRoot = document.getElementById('wel-preview-canvas');
    const previewStyle = document.getElementById('wel-admin-live-style');

    function val(id) {
      const el = document.getElementById(id);
      if (!el) return '';
      return el.value;
    }

    function isChecked(id) {
      const el = document.getElementById(id);
      return !!(el && el.checked);
    }

    function currentValues() {
      return {
        page_bg: val('page_bg') || defaults.page_bg,
        card_bg: val('card_bg') || defaults.card_bg,
        table_head_bg: val('table_head_bg') || defaults.table_head_bg,
        text_color: val('text_color') || defaults.text_color,
        muted_color: val('muted_color') || defaults.muted_color,
        border_color: val('border_color') || defaults.border_color,
        base_font_size: parseInt(val('base_font_size') || defaults.base_font_size, 10) || defaults.base_font_size,
        small_font_size: parseInt(val('small_font_size') || defaults.small_font_size, 10) || defaults.small_font_size,
        button_font_size: parseInt(val('button_font_size') || defaults.button_font_size, 10) || defaults.button_font_size,
        font_family: val('font_family') || defaults.font_family,
        panel_radius: parseInt(val('panel_radius') || defaults.panel_radius, 10) || defaults.panel_radius,
        outer_width: parseInt(val('outer_width') || defaults.outer_width, 10) || defaults.outer_width,
        label_width: parseInt(val('label_width') || defaults.label_width, 10) || defaults.label_width,
        outer_padding_y: parseInt(val('outer_padding_y') || defaults.outer_padding_y, 10) || defaults.outer_padding_y,
        card_padding: parseInt(val('card_padding') || defaults.card_padding, 10) || defaults.card_padding,
        logo_enabled: isChecked('logo_enabled') ? '1' : '0',
        logo_url: val('logo_url') || '',
        logo_alt: val('logo_alt') || '',
        logo_max_height: parseInt(val('logo_max_height') || defaults.logo_max_height, 10) || defaults.logo_max_height,
        product_image_enabled: isChecked('product_image_enabled') ? '1' : '0',
        product_image_max_height: parseInt(val('product_image_max_height') || defaults.product_image_max_height, 10) || defaults.product_image_max_height,
        wine_name_enabled: isChecked('wine_name_enabled') ? '1' : '0',
        wine_name_size: parseInt(val('wine_name_size') || defaults.wine_name_size, 10) || defaults.wine_name_size,
        vintage_enabled: isChecked('vintage_enabled') ? '1' : '0',
        vintage_size: parseInt(val('vintage_size') || defaults.vintage_size, 10) || defaults.vintage_size,
        subtitle_enabled: isChecked('subtitle_enabled') ? '1' : '0',
        subtitle_size: parseInt(val('subtitle_size') || defaults.subtitle_size, 10) || defaults.subtitle_size,
        producer_region: val('producer_region') || '',
        producer_country: val('producer_country') || '',
        producer_address: val('producer_address') || '',
        custom_css: val('custom_css') || ''
      };
    }

    function syncColorInputs() {
      document.querySelectorAll('input[type=color][data-sync]').forEach(function (picker) {
        const target = document.getElementById(picker.dataset.sync);
        if (target && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(target.value)) {
          picker.value = target.value;
        }
      });
    }

    function applyLimits() {
      numericFields.forEach(function (field) {
        const el = document.getElementById(field);
        if (!el || !fieldLimits[field]) return;
        let value = parseInt(el.value || defaults[field], 10);
        if (isNaN(value)) value = defaults[field];
        value = Math.max(fieldLimits[field][0], Math.min(fieldLimits[field][1], value));
        el.value = value;
      });
    }

    function buildPreviewCss(values) {
      const css = [
        '#wel-preview-canvas{background:var(--wel-page-bg) !important;color:var(--wel-text) !important;font-family:var(--wel-font) !important;font-size:var(--wel-base) !important;}',
        '#wel-preview-canvas, #wel-preview-canvas *{box-sizing:border-box;}',
        '#wel-preview-canvas .wel-page-shell{width:min(var(--wel-outer-width), 100%) !important;max-width:none !important;margin:0 auto !important;padding:var(--wel-outer-pad) 16px 36px !important;background:var(--wel-page-bg) !important;}',
        '#wel-preview-canvas .wel-lang-switch{display:flex !important;justify-content:flex-end !important;align-items:center !important;gap:8px !important;flex-wrap:wrap !important;width:min(var(--wel-label-width), 100%) !important;margin:0 auto 12px auto !important;}',
        '#wel-preview-canvas .wel-lang-button{display:inline-flex !important;align-items:center !important;justify-content:center !important;text-decoration:none !important;border:1px solid #d7e0ea !important;border-radius:999px !important;background:#ffffff !important;color:#334155 !important;padding:4px 10px !important;font-size:var(--wel-button) !important;font-weight:600 !important;line-height:1.1 !important;box-shadow:0 1px 2px rgba(15,23,42,.03) !important;transition:all .16s ease !important;cursor:pointer !important;}',
        '#wel-preview-canvas .wel-lang-button:hover{border-color:#bfd0e3 !important;background:#f5f9ff !important;color:#244267 !important;}',
        '#wel-preview-canvas .wel-lang-button.is-active, #wel-preview-canvas .wel-lang-button[aria-pressed="true"]{background:#eef5ff !important;border-color:#bfd3ea !important;color:#244267 !important;box-shadow:0 0 0 3px rgba(36,66,103,.06) !important;}',
        '#wel-preview-canvas .wel-header-block{width:min(var(--wel-label-width),100%) !important;margin:0 auto 18px auto !important;display:flex !important;flex-direction:column !important;align-items:center !important;gap:12px !important;text-align:center !important;}',
        '#wel-preview-canvas .wel-logo-wrap{display:flex !important;justify-content:center !important;align-items:center !important;width:min(var(--wel-label-width), 100%) !important;margin:0 auto 14px !important;padding:0 12px !important;box-sizing:border-box !important;}',
        '#wel-preview-canvas .wel-logo-wrap.is-hidden,#wel-preview-canvas .wel-product-image-wrap.is-hidden,#wel-preview-canvas .wel-vintage.is-hidden,#wel-preview-canvas .wel-wine-name.is-hidden,#wel-preview-canvas .wel-subtitle.is-hidden,#wel-preview-canvas .wel-producer-card.is-hidden{display:none !important;}',
        '#wel-preview-canvas .wel-logo-image{display:block !important;max-width:100% !important;max-height:var(--wel-logo-height) !important;width:auto !important;height:auto !important;object-fit:contain !important;}',
        '#wel-preview-canvas .wel-vintage{font-size:var(--wel-vintage-size) !important;line-height:1.15 !important;font-weight:500 !important;color:var(--wel-text) !important;}',
        '#wel-preview-canvas .wel-wine-name{font-size:var(--wel-wine-name-size) !important;line-height:1.08 !important;font-weight:700 !important;color:var(--wel-text) !important;}',
        '#wel-preview-canvas .wel-subtitle{font-size:var(--wel-subtitle-size) !important;line-height:1.15 !important;font-weight:600 !important;color:var(--wel-text) !important;}',
        '#wel-preview-canvas .wel-product-image-wrap{display:flex !important;justify-content:center !important;align-items:center !important;width:100% !important;padding:0 12px !important;box-sizing:border-box !important;}',
        '#wel-preview-canvas .wel-product-image{display:block !important;max-width:100% !important;max-height:var(--wel-product-image-height) !important;width:auto !important;height:auto !important;object-fit:contain !important;}',
        '#wel-preview-canvas .wel-label-card{width:min(var(--wel-label-width), 100%) !important;max-width:none !important;margin:0 auto !important;padding:var(--wel-card-pad) !important;background:var(--wel-card-bg) !important;border:1px solid var(--wel-border) !important;border-radius:var(--wel-radius) !important;color:var(--wel-text) !important;}',
        '#wel-preview-canvas .wel-label-table{width:100% !important;border-collapse:collapse !important;background:#ffffff !important;border:1px solid var(--wel-border) !important;font-size:var(--wel-base) !important;}',
        '#wel-preview-canvas .wel-label-table th, #wel-preview-canvas .wel-label-table td{border:1px solid var(--wel-border) !important;padding:10px 12px !important;vertical-align:top !important;color:var(--wel-text) !important;font-size:var(--wel-base) !important;line-height:1.45 !important;}',
        '#wel-preview-canvas .wel-label-table thead th{text-align:left !important;background:var(--wel-head-bg) !important;font-weight:600 !important;}',
        '#wel-preview-canvas .wel-label-row{display:flex !important;justify-content:space-between !important;gap:16px !important;}',
        '#wel-preview-canvas .wel-label-row span:last-child{text-align:right !important;white-space:nowrap !important;font-weight:500 !important;}',
        '#wel-preview-canvas .wel-label-trace td, #wel-preview-canvas .wel-label-pretable td, #wel-preview-canvas .wel-footnote{font-size:var(--wel-small) !important;color:var(--wel-muted) !important;}',
        '#wel-preview-canvas .wel-ingredients{margin-top:12px !important;line-height:1.55 !important;font-size:var(--wel-base) !important;color:var(--wel-text) !important;}',
        '#wel-preview-canvas .wel-ingredients strong, #wel-preview-canvas .wel-ingredients span{color:var(--wel-text) !important;}',
        '#wel-preview-canvas .wel-footnote{margin-top:12px !important;line-height:1.55 !important;}',
        '#wel-preview-canvas .wel-producer-card{box-sizing:border-box !important;width:min(var(--wel-label-width), 100%) !important;max-width:none !important;margin:14px auto 0 auto !important;padding:18px var(--wel-card-pad) !important;background:var(--wel-card-bg) !important;border:1px solid var(--wel-border) !important;border-radius:var(--wel-radius) !important;}',
        '#wel-preview-canvas .wel-producer-grid{display:grid !important;gap:16px !important;}',
        '#wel-preview-canvas .wel-producer-item{display:grid !important;gap:4px !important;text-align:center !important;}',
        '#wel-preview-canvas .wel-producer-label{font-size:var(--wel-small) !important;color:var(--wel-muted) !important;font-weight:600 !important;}',
        '#wel-preview-canvas .wel-producer-value{font-size:var(--wel-small) !important;color:var(--wel-text) !important;line-height:1.5 !important;}',
        '@media (max-width:600px){#wel-preview-canvas .wel-page-shell{width:calc(100% - 12px) !important;padding:24px 6px 28px !important;}#wel-preview-canvas .wel-label-row{gap:12px !important;}#wel-preview-canvas .wel-label-row span:last-child{white-space:normal !important;text-align:right !important;}}'
      ].join("\n");

      return values.custom_css ? css + "\n" + values.custom_css : css;
    }

    function applyPreviewVars(values) {
      if (!previewRoot) return;
      previewRoot.style.setProperty('--wel-page-bg', values.page_bg);
      previewRoot.style.setProperty('--wel-card-bg', values.card_bg);
      previewRoot.style.setProperty('--wel-head-bg', values.table_head_bg);
      previewRoot.style.setProperty('--wel-text', values.text_color);
      previewRoot.style.setProperty('--wel-muted', values.muted_color);
      previewRoot.style.setProperty('--wel-border', values.border_color);
      previewRoot.style.setProperty('--wel-font', values.font_family);
      previewRoot.style.setProperty('--wel-base', values.base_font_size + 'px');
      previewRoot.style.setProperty('--wel-small', values.small_font_size + 'px');
      previewRoot.style.setProperty('--wel-button', values.button_font_size + 'px');
      previewRoot.style.setProperty('--wel-radius', values.panel_radius + 'px');
      previewRoot.style.setProperty('--wel-outer-width', values.outer_width + 'px');
      previewRoot.style.setProperty('--wel-label-width', values.label_width + 'px');
      previewRoot.style.setProperty('--wel-outer-pad', values.outer_padding_y + 'px');
      previewRoot.style.setProperty('--wel-card-pad', values.card_padding + 'px');
      previewRoot.style.setProperty('--wel-logo-height', values.logo_max_height + 'px');
      previewRoot.style.setProperty('--wel-product-image-height', values.product_image_max_height + 'px');
      previewRoot.style.setProperty('--wel-wine-name-size', values.wine_name_size + 'px');
      previewRoot.style.setProperty('--wel-vintage-size', values.vintage_size + 'px');
      previewRoot.style.setProperty('--wel-subtitle-size', values.subtitle_size + 'px');
    }

    function syncLogoThumb(url, alt) {
      const thumb = document.getElementById('wel-logo-thumb');
      const image = document.getElementById('wel-logo-thumb-image');
      const removeButton = document.getElementById('wel-logo-remove');
      const hasLogo = !!url;

      if (thumb) {
        thumb.classList.toggle('is-empty', !hasLogo);
      }
      if (image) {
        image.src = hasLogo ? url : '';
        image.alt = alt || 'Logo';
      }
      if (removeButton) {
        removeButton.disabled = !hasLogo;
      }
    }

    function syncHeaderPreview(values) {
      if (!previewRoot) return;

      const logoWrap = previewRoot.querySelector('#wel-preview-logo-wrap');
      const logoImage = previewRoot.querySelector('#wel-preview-logo-image');
      const logoVisible = values.logo_enabled === '1' && !!values.logo_url;

      if (logoWrap) logoWrap.classList.toggle('is-hidden', !logoVisible);
      if (logoImage) {
        logoImage.src = logoVisible ? values.logo_url : '';
        logoImage.alt = values.logo_alt || 'Logo';
      }

      const vintage = previewRoot.querySelector('#wel-preview-vintage');
      if (vintage) vintage.classList.toggle('is-hidden', values.vintage_enabled !== '1');

      const wineName = previewRoot.querySelector('#wel-preview-wine-name');
      if (wineName) wineName.classList.toggle('is-hidden', values.wine_name_enabled !== '1');

      const subtitle = previewRoot.querySelector('#wel-preview-subtitle-text');
      if (subtitle) subtitle.classList.toggle('is-hidden', values.subtitle_enabled !== '1');

      const imageWrap = previewRoot.querySelector('#wel-preview-product-image-wrap');
      if (imageWrap) imageWrap.classList.toggle('is-hidden', values.product_image_enabled !== '1');

      syncLogoThumb(values.logo_url, values.logo_alt);
    }

    function syncProducerPreview(values) {
      if (!previewRoot) return;
      const card = previewRoot.querySelector('#wel-preview-producer-card');
      const hasProducer = !!(values.producer_region || values.producer_country || values.producer_address);
      if (card) {
        card.classList.toggle('is-hidden', !hasProducer);
      }

      const regionValue = previewRoot.querySelector('#wel-preview-region-value');
      const countryValue = previewRoot.querySelector('#wel-preview-country-value');
      const addressValue = previewRoot.querySelector('#wel-preview-address-value');
      if (regionValue) regionValue.textContent = values.producer_region || 'Rheinhessen';
      if (countryValue) countryValue.textContent = values.producer_country || 'Deutschland';
      if (addressValue) addressValue.textContent = values.producer_address || 'Weingut Musterhof, Musterstraße 1, 55555 Musterstadt';
    }

    function applyLivePreviewCss() {
      const values = currentValues();
      applyPreviewVars(values);
      syncHeaderPreview(values);
      syncProducerPreview(values);
      if (previewStyle) {
        previewStyle.textContent = buildPreviewCss(values);
      }
    }

    function setPreviewLanguage(lang) {
      if (!previewRoot) return;
      const tr = previewTranslations[lang] || previewTranslations.de;
      const map = {
        headline: 'wel-preview-headline',
        energy: 'wel-preview-energy',
        carbs: 'wel-preview-carbs',
        sugars: 'wel-preview-sugars',
        trace: 'wel-preview-trace',
        footnote: 'wel-preview-footnote'
      };

      Object.keys(map).forEach(function (key) {
        const el = previewRoot.querySelector('#' + map[key]);
        if (el) el.textContent = tr[key];
      });

      const label = previewRoot.querySelector('#wel-preview-ingredients-label');
      if (label) label.textContent = tr.ingredients_label;

      const body = previewRoot.querySelector('#wel-preview-ingredients-body');
      if (body) body.innerHTML = tr.ingredients_html;

      const regionLabel = previewRoot.querySelector('#wel-preview-region-label');
      const countryLabel = previewRoot.querySelector('#wel-preview-country-label');
      const addressLabel = previewRoot.querySelector('#wel-preview-address-label');
      if (regionLabel) regionLabel.textContent = tr.region_label;
      if (countryLabel) countryLabel.textContent = tr.country_label;
      if (addressLabel) addressLabel.textContent = tr.address_label;

      previewRoot.querySelectorAll('[data-wel-lang]').forEach(function (button) {
        const active = button.getAttribute('data-wel-lang') === lang;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
    }

    function wirePreviewLanguageButtons() {
      if (!previewRoot) return;
      previewRoot.querySelectorAll('[data-wel-lang]').forEach(function (button) {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          setPreviewLanguage(button.getAttribute('data-wel-lang') || 'de');
        });
      });
      setPreviewLanguage('de');
    }

    function schedulePreview() {
      applyLimits();
      applyLivePreviewCss();
    }

    document.querySelectorAll('input[type=color][data-sync]').forEach(function (picker) {
      picker.addEventListener('input', function () {
        const target = document.getElementById(this.dataset.sync);
        if (target) target.value = this.value;
        schedulePreview();
      });
    });

    document.querySelectorAll('.wel-color-text').forEach(function (input) {
      input.addEventListener('input', function () {
        const picker = document.getElementById(this.id + '_picker');
        if (picker && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(this.value)) {
          picker.value = this.value;
        }
        schedulePreview();
      });
    });

    inputs.forEach(function (el) {
      el.addEventListener('input', schedulePreview);
      el.addEventListener('change', schedulePreview);
    });

    document.querySelectorAll('.wel-reset-field').forEach(function (button) {
      button.addEventListener('click', function () {
        const field = this.dataset.field;
        const el = document.getElementById(field);
        if (!el) return;
        const fallback = defaults[field] !== undefined ? defaults[field] : '';
        const def = el.dataset.default !== undefined ? el.dataset.default : fallback;
        el.value = def;
        const picker = document.getElementById(field + '_picker');
        if (picker && typeof def === 'string' && def.charAt(0) === '#') {
          picker.value = def;
        }
        schedulePreview();
      });
    });

    document.querySelectorAll('.wel-reset-section').forEach(function (button) {
      button.addEventListener('click', function () {
        const section = this.dataset.section;
        const fields = section === 'colors'
          ? colorFields
          : (section === 'logo'
            ? logoFields
            : (section === 'product_blocks'
              ? productBlockFields
              : (section === 'producer' ? producerFields : sizeFields)));
        fields.forEach(function (field) {
          const el = document.getElementById(field);
          if (!el) return;
          const def = el.dataset.default !== undefined ? el.dataset.default : (defaults[field] !== undefined ? defaults[field] : '');
          if (el.type === 'checkbox') {
            el.checked = def === '1';
          } else {
            el.value = def;
          }
          const picker = document.getElementById(field + '_picker');
          if (picker && typeof def === 'string' && def.charAt(0) === '#') {
            picker.value = def;
          }
        });
        syncColorInputs();
        schedulePreview();
      });
    });

    const previewReset = document.querySelector('.wel-reset-preview');
    if (previewReset) {
      previewReset.addEventListener('click', function () {
        Object.keys(defaults).forEach(function (field) {
          const el = document.getElementById(field);
          if (!el) return;
          if (el.type === 'checkbox') {
            el.checked = defaults[field] === '1';
          } else {
            el.value = defaults[field];
          }
          const picker = document.getElementById(field + '_picker');
          if (picker && typeof defaults[field] === 'string' && defaults[field].charAt(0) === '#') {
            picker.value = defaults[field];
          }
        });
        syncColorInputs();
        schedulePreview();
      });
    }

    const logoSelect = document.getElementById('wel-logo-select');
    const logoRemove = document.getElementById('wel-logo-remove');
    let logoFrame = null;

    if (logoSelect) {
      logoSelect.addEventListener('click', function () {
        if (!(window.wp && wp.media)) {
          return;
        }

        if (logoFrame) {
          logoFrame.open();
          return;
        }

        logoFrame = wp.media({
          title: mediaTexts.title,
          button: { text: mediaTexts.button },
          library: { type: 'image' },
          multiple: false
        });

        logoFrame.on('select', function () {
          const attachment = logoFrame.state().get('selection').first();
          const data = attachment ? attachment.toJSON() : null;
          if (!data || !data.url) return;

          const urlField = document.getElementById('logo_url');
          const altField = document.getElementById('logo_alt');
          const enabledField = document.getElementById('logo_enabled');

          if (urlField) {
            urlField.value = data.url;
          }
          if (altField && !altField.value && data.alt) {
            altField.value = data.alt;
          }
          if (enabledField) {
            enabledField.checked = true;
          }

          schedulePreview();
        });

        logoFrame.open();
      });
    }

    if (logoRemove) {
      logoRemove.addEventListener('click', function () {
        const urlField = document.getElementById('logo_url');
        const enabledField = document.getElementById('logo_enabled');

        if (urlField) {
          urlField.value = '';
        }
        if (enabledField) {
          enabledField.checked = false;
        }

        schedulePreview();
      });
    }

    syncColorInputs();
    applyLimits();
    applyLivePreviewCss();
    wirePreviewLanguageButtons();
  } catch (err) {
    console.error('WEL design preview failed', err);
  }
});
</script>
