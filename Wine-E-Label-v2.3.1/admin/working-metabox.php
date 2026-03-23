<?php

if (!defined('ABSPATH')) {
    exit;
}

class Working_Wine_E_Label_MetaBox
{
    private Wine_E_Label_DB_Extended $db;

    public function __construct(Wine_E_Label_DB_Extended $db)
    {
        $this->db = $db;

        add_action('add_meta_boxes', [$this, 'register_metaboxes']);
        add_action('save_post', [$this, 'save_data'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('post_edit_form_tag', [$this, 'enable_file_upload']);
    }

    public function register_metaboxes(): void
    {
        add_meta_box(
            'wine_e_label_sidebar',
            __('E-Label Import & QR', 'wine-e-label'),
            [$this, 'render_sidebar_metabox'],
            'product',
            'side',
            'high'
        );

        add_meta_box(
            'wine_e_label_data',
            __('E-Label Daten', 'wine-e-label'),
            [$this, 'render_data_metabox'],
            'product',
            'normal',
            'default'
        );
    }

    public function enable_file_upload(): void
    {
        echo ' enctype="multipart/form-data"';
    }

    public function render_sidebar_metabox(WP_Post $post): void
    {
        $product_id = (int) $post->ID;
        $data = Wine_E_Label_Importer::get_label_data($product_id);
        $saved_slug = (string) ($data['slug'] ?? '');
        $slug_suggestion = Wine_E_Label_Importer::suggest_slug((string) ($data['wine_nr'] ?? ''), '');
        $base_url = Wine_E_Label_URL::get_public_base_url();
        $preview_base_url = Wine_E_Label_URL::get_preview_base_url();
        $public_url = $saved_slug !== '' ? Wine_E_Label_URL::get_short_url($product_id) : false;
        $page_status = $this->get_page_status($product_id, $public_url);
        $qr_status = $this->get_qr_status($product_id, $public_url);
        $create_button_label = !empty($data['built_at']) ? __('E-Label und QR-Code aktualisieren', 'wine-e-label') : __('E-Label und QR-Code erstellen', 'wine-e-label');

        wp_nonce_field('wine_e_label_save', 'wine_e_label_nonce');
        ?>
        <style>
            .nl-sidebar-box{display:flex;flex-direction:column;gap:10px}.nl-help{font-size:12px;color:#646970;line-height:1.4}.nl-field label{display:block;font-weight:600;margin-bottom:4px}.nl-field input[type=text],.nl-field textarea{width:100%}.nl-base-url{background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;padding:8px 10px;word-break:break-all;font-size:12px}.nl-url-preview{font-size:12px;color:#1d2327;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;padding:8px 10px;word-break:break-all}.nl-dropzone{border:2px dashed #8c8f94;border-radius:6px;padding:18px 12px;text-align:center;background:#fcfcfc;cursor:pointer;transition:.15s}.nl-dropzone.dragover{border-color:#2271b1;background:#f0f6fc}.nl-dropzone strong{display:block;margin-bottom:4px}.nl-hidden{display:none}.nl-status{border:1px solid #dcdcde;border-radius:6px;padding:10px;background:#fff}.nl-status-row{padding:8px 0;border-bottom:1px solid #f0f0f1;font-size:12px}.nl-status-row:last-child{border-bottom:none}.nl-status-ok{color:#008a20;font-weight:600}.nl-status-error{color:#b32d2e;font-weight:600}.nl-status-pending{color:#646970;font-weight:600}.nl-qr-preview img{max-width:100%;height:auto;border:1px solid #dcdcde;background:#fff}.nl-status .nl-qr-preview{display:none!important}.nl-inline-preview{margin-top:10px;display:flex;justify-content:center}.nl-inline-preview img{display:block;max-width:132px;max-height:132px;width:auto;height:auto;border:1px solid #dcdcde;background:#fff;border-radius:8px;padding:6px}.nl-actions .button{width:100%;margin-top:6px;text-align:center;justify-content:center}.nl-file-name{font-size:12px;color:#1d2327}.nl-inline-link{font-size:12px}.nl-small-button{display:inline-block;margin-top:4px;font-size:12px}
        </style>
        <div class="nl-sidebar-box">
            <div class="nl-help"><?php esc_html_e('WIPZN-Import importieren, E-Label-Link prüfen und QR-Code direkt erzeugen.', 'wine-e-label'); ?></div>
            <div class="nl-help"><?php esc_html_e('Zum Aktualisieren der Nährwerte einfach neue Datei einfügen.', 'wine-e-label'); ?></div>

            <div class="nl-field">
                <label for="wine_e_label_wine_nr"><?php esc_html_e('Wein-Nr. (optional)', 'wine-e-label'); ?></label>
                <input type="text" name="wine_e_label_wine_nr" id="wine_e_label_wine_nr" value="<?php echo esc_attr((string) ($data['wine_nr'] ?? '')); ?>" placeholder="">
            </div>

            <div class="nl-field">
                <label><?php esc_html_e('Basis-URL', 'wine-e-label'); ?></label>
                <div class="nl-base-url" id="wine_e_label_base_url_display"><?php echo esc_html($base_url !== '' ? preg_replace('#^https?://#', '', $base_url) : __('Bitte zuerst im Plugin-Admin setzen', 'wine-e-label')); ?></div>
            </div>

            <div class="nl-field">
                <label for="wine_e_label_slug"><?php esc_html_e('Slug / URL-Teil', 'wine-e-label'); ?> <span style="color:#b32d2e">*</span></label>
                <input type="text" name="wine_e_label_slug" id="wine_e_label_slug" value="<?php echo esc_attr($saved_slug); ?>" placeholder="<?php echo esc_attr($slug_suggestion); ?>">
                <button type="button" class="button-link nl-small-button" id="wine_e_label_apply_suggestion" data-suggestion="<?php echo esc_attr($slug_suggestion); ?>"><?php esc_html_e('Vorschlag aus Wein-Nr. übernehmen', 'wine-e-label'); ?></button>
            </div>

            <div class="nl-field">
                <label><?php esc_html_e('URL-Vorschau', 'wine-e-label'); ?></label>
                <div class="nl-url-preview" id="wine_e_label_url_preview"><?php echo esc_html($this->compose_preview_url($preview_base_url, $saved_slug !== '' ? $saved_slug : '')); ?></div>
            </div>

            <div class="nl-field">
                <label><?php esc_html_e('WIPZN-Import', 'wine-e-label'); ?></label>
                <div class="nl-dropzone" id="wine_e_label_dropzone">
                    <strong><?php esc_html_e('ZIP, JSON oder HTML hier ablegen', 'wine-e-label'); ?></strong>
                    <span><?php esc_html_e('oder klicken, um eine Datei auszuwählen', 'wine-e-label'); ?></span>
                </div>
                <input type="file" class="nl-hidden" name="wine_e_label_import_file" id="wine_e_label_import_file" accept=".zip,.json,.html,.htm">
                <div class="nl-file-name" id="wine_e_label_import_file_name">
                    <?php echo esc_html($data['source_file_name'] !== '' ? $data['source_file_name'] : __('Keine Datei ausgewählt', 'wine-e-label')); ?>
                </div>
                <button type="button" class="button" name="wine_e_label_confirm_import" value="1" id="wine_e_label_confirm_import" style="width:100%;margin-top:8px;" formnovalidate><?php esc_html_e('Import bestätigen', 'wine-e-label'); ?></button>
                <button type="button" class="button" id="wine_e_label_delete_import" style="width:100%;margin-top:8px;" formnovalidate><?php esc_html_e('Import löschen', 'wine-e-label'); ?></button>
                <button type="button" class="button button-primary" name="wine_e_label_create_label" value="1" id="wine_e_label_create_label" style="width:100%;margin-top:8px;"><?php echo esc_html($create_button_label); ?></button>
                <button type="button" class="button" id="wine_e_label_delete_generated" style="width:100%;margin-top:8px;" formnovalidate><?php esc_html_e('E-Label und QR-Code löschen', 'wine-e-label'); ?></button>
            </div>

            <div class="nl-status">
                <div class="nl-status-row">
                    <div><strong><?php esc_html_e('Importstatus', 'wine-e-label'); ?></strong></div>
                    <?php if (($data['import_status'] ?? '') === 'success') : ?>
                        <div class="nl-status-ok"><?php echo esc_html($data['import_message'] ?: __('Import erfolgreich', 'wine-e-label')); ?></div>
                    <?php elseif (($data['import_status'] ?? '') === 'error') : ?>
                        <div class="nl-status-error"><?php echo esc_html($data['import_message'] ?: __('Import fehlgeschlagen', 'wine-e-label')); ?></div>
                    <?php else : ?>
                        <div class="nl-status-pending"><?php esc_html_e('Noch kein Import durchgeführt', 'wine-e-label'); ?></div>
                    <?php endif; ?>
                </div>
                <div class="nl-status-row">
                    <div><strong><?php esc_html_e('E-Label-Seite', 'wine-e-label'); ?></strong></div>
                    <?php if ($page_status['ok']) : ?>
                        <div class="nl-status-ok"><?php esc_html_e('E-Label-Seite erfolgreich erstellt', 'wine-e-label'); ?></div>
                        <div class="nl-inline-link"><a href="<?php echo esc_url($page_status['url']); ?>" target="_blank"><?php esc_html_e('Link öffnen', 'wine-e-label'); ?></a></div>
                    <?php else : ?>
                        <div class="nl-status-<?php echo esc_attr($page_status['level']); ?>"><?php echo esc_html($page_status['message']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="nl-status-row">
                    <div><strong><?php esc_html_e('QR-Code', 'wine-e-label'); ?></strong></div>
                    <?php if ($qr_status['ok']) : ?>
                        <div class="nl-status-ok"><?php esc_html_e('QR-Code erfolgreich erstellt', 'wine-e-label'); ?></div>
                        <div class="nl-inline-link">
                            <button type="button" id="download_qr_code" class="button-link wine-e-label-download-qr" data-product-id="<?php echo esc_attr($product_id); ?>"><?php esc_html_e('Download', 'wine-e-label'); ?></button>
                            <?php if (!empty($qr_status['preview'])) : ?>
                                <div class="nl-inline-preview"><img src="<?php echo esc_attr($qr_status['preview']); ?>" alt="QR Code" class="nl-status-qr-image"></div>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <div class="nl-status-<?php echo esc_attr($qr_status['level']); ?>"><?php echo esc_html($qr_status['message']); ?></div>
                    <?php endif; ?>
                </div>
                <?php if (($data['last_import'] ?? '') !== '') : ?>
                    <div class="nl-status-row">
                        <div><strong><?php esc_html_e('Letzter Import', 'wine-e-label'); ?></strong></div>
                        <div><?php echo esc_html((string) $data['last_import']); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="nl-actions">
                <?php if ($page_status['ok']) : ?>
                    <a class="button button-primary" href="<?php echo esc_url($page_status['url']); ?>" target="_blank"><?php esc_html_e('E-Label öffnen', 'wine-e-label'); ?></a>
                <?php endif; ?>
                <?php if (($data['source_file_url'] ?? '') !== '') : ?>
                    <a class="button" href="<?php echo esc_url((string) $data['source_file_url']); ?>" target="_blank"><?php esc_html_e('Quelldatei herunterladen', 'wine-e-label'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function render_data_metabox(WP_Post $post): void
    {
        $product_id = (int) $post->ID;
        $data = Wine_E_Label_Importer::get_label_data($product_id);
        $manual = Wine_E_Label_Manual_Builder::normalize_config($data['manual_config'] ?? []);
        $derived = Wine_E_Label_Manual_Builder::derive_values($manual);
        $catalog = Wine_E_Label_Manual_Builder::get_catalog();
        $admin_lang_for_categories = class_exists('Wine_E_Label_Admin_I18n') ? Wine_E_Label_Admin_I18n::get_current_language() : 'de';
        $categories = Wine_E_Label_Manual_Builder::get_translated_category_options($admin_lang_for_categories);
        $base_url = Wine_E_Label_URL::get_public_base_url();
        $preview_base_url = Wine_E_Label_URL::get_preview_base_url();
        $current_slug = (string) ($data['slug'] ?? '');
        $public_url = $current_slug !== '' ? Wine_E_Label_URL::get_short_url($product_id) : false;
        $page_status = $this->get_page_status($product_id, $public_url);
        $qr_status = $this->get_qr_status($product_id, $public_url);
        $link_preview = $public_url ?: $this->compose_preview_url($preview_base_url, $current_slug);
        $workflow = $this->get_workflow_flags($product_id, $data, $manual, $page_status, $qr_status);
        $create_button_label = !empty($data['built_at']) ? __('E-Label und QR-Code aktualisieren', 'wine-e-label') : __('E-Label und QR-Code erstellen', 'wine-e-label');
        $source_summary = $this->get_source_summary($data, $manual);
        $copy_products = $this->get_copy_source_products($product_id);
        $slug_quality = $this->get_slug_scan_quality($current_slug ?: Wine_E_Label_Importer::suggest_slug((string) ($manual['product']['wein_nr'] ?? ''), ''));
        $display_config = class_exists('Wine_E_Label_Presentation') ? Wine_E_Label_Presentation::normalize_config($data['display_config'] ?? []) : [];
        $display_defaults = class_exists('Wine_E_Label_Presentation') ? Wine_E_Label_Presentation::product_defaults($product_id) : [];
        $resolved_display = class_exists('Wine_E_Label_Presentation') ? Wine_E_Label_Presentation::resolve($product_id, $display_config) : $display_defaults;
        $design_settings = class_exists('Wine_E_Label_Design') ? Wine_E_Label_Design::get_settings() : [];
        $producer_labels = class_exists('Wine_E_Label_Design') ? Wine_E_Label_Design::producer_label_map('de') : ['region' => __('Anbaugebiet', 'wine-e-label'), 'country' => __('Land', 'wine-e-label'), 'address' => __('Adresse des Weinguts', 'wine-e-label')];
        $preview_logo_url = trim((string) ($design_settings['logo_url'] ?? ''));
        $preview_logo_alt = trim((string) ($design_settings['logo_alt'] ?? ''));
        if ($preview_logo_alt === '') {
            $preview_logo_alt = __('Weingutslogo', 'wine-e-label');
        }
        $preview_header_visible = (
            (($design_settings['logo_enabled'] ?? '0') === '1' && $preview_logo_url !== '')
            || (($design_settings['product_image_enabled'] ?? '0') === '1' && trim((string) ($resolved_display['product_image_url'] ?? '')) !== '')
            || (($design_settings['vintage_enabled'] ?? '0') === '1' && trim((string) ($resolved_display['vintage'] ?? '')) !== '')
            || (($design_settings['wine_name_enabled'] ?? '0') === '1' && trim((string) ($resolved_display['wine_name'] ?? '')) !== '')
            || (($design_settings['subtitle_enabled'] ?? '0') === '1' && trim((string) ($resolved_display['subtitle'] ?? '')) !== '')
        );
        $preview_producer_visible = trim((string) ($design_settings['producer_region'] ?? '')) !== ''
            || trim((string) ($design_settings['producer_country'] ?? '')) !== ''
            || trim((string) ($design_settings['producer_address'] ?? '')) !== '';
        ?>
        <style>
            .nlm-wrap{width:100%;max-width:none}.nlm-note{margin:0 0 14px;color:#646970}.nlm-top-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:16px;margin-bottom:16px}.nlm-main-grid{display:grid;grid-template-columns:minmax(360px,1fr) minmax(420px,1.05fr);gap:16px;align-items:start}.nlm-left-stack,.nlm-right-stack{display:grid;gap:16px}@media (max-width:1200px){.nlm-top-grid,.nlm-main-grid{grid-template-columns:1fr}}.nlm-card{background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:18px 18px 14px;box-shadow:0 1px 2px rgba(0,0,0,.04)}.nlm-card h3{margin:0 0 14px;font-size:16px}.nlm-field{margin-bottom:12px}.nlm-field label{display:block;font-weight:600;margin-bottom:4px}.nlm-field input[type=text],.nlm-field select{width:100%}.nlm-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}.nlm-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}@media (max-width:782px){.nlm-row,.nlm-row-3{grid-template-columns:1fr}}.nlm-inline-note,.nlm-help-inline{font-size:12px;color:#646970;margin-top:4px;line-height:1.4}.nlm-required{color:#b32d2e;margin-left:2px}.nlm-required-note{margin-top:8px;font-size:12px;color:#646970}.nlm-radio-group{display:flex;flex-direction:column;gap:8px}.nlm-muted{color:#646970}.nlm-derived{background:#f6f7f7;border:1px solid #dcdcde;border-radius:6px;padding:8px 10px;min-height:38px;display:flex;align-items:center}.nlm-group-block{margin:14px 0 0;padding:12px;border:1px solid #e2e4e7;border-radius:8px;background:#fafafa}.nlm-items{margin-top:10px}.nlm-item,.nlm-head{display:grid;grid-template-columns:28px 1fr 170px;gap:10px;align-items:start;padding:6px 0}.nlm-head{font-weight:600}.nlm-item{border-top:1px solid #ececec}.nlm-item:first-child{border-top:none}.nlm-item.no-third,.nlm-head.no-third{grid-template-columns:28px 1fr}.nlm-hidden{display:none}.nlm-display-choice{display:flex;flex-direction:column;gap:6px;font-size:12px}.nlm-display-choice label{display:flex;align-items:center;gap:6px;font-weight:400}.nlm-ingredients-card .nlm-group-block:first-child{margin-top:0}.nlm-custom-items{margin-top:14px;padding-top:10px;border-top:1px solid #ececec}.nlm-custom-items-head{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:8px}.nlm-custom-items-list{display:grid;gap:8px}.nlm-custom-item-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;align-items:end}.nlm-custom-item-row .nlm-field{margin-bottom:0}.nlm-custom-item-row .button-link-delete{color:#b32d2e;text-decoration:none}.nlm-custom-items-empty{font-size:12px;color:#646970}.nlm-qr-tools,.nlm-copy-tools,.nlm-link-tools{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:6px}.nlm-qr-thumb{display:inline-flex;align-items:center;justify-content:center;width:124px;height:124px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:8px;margin:0 auto;text-align:center}.nlm-qr-thumb img{max-width:100%;max-height:100%;height:auto;width:auto}.nlm-qr-thumb img[src=""]{display:none!important}.nlm-qr-empty{font-size:12px;line-height:1.35;color:#646970;padding:8px}.nlm-qr-empty.nlm-hidden,.nlm-qr-thumb img.nlm-hidden{display:none}.nlm-pill-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}@media (max-width:782px){.nlm-pill-grid{grid-template-columns:1fr 1fr}}.nlm-pill{border:1px solid #dcdcde;border-radius:10px;padding:10px;background:#fafafa}.nlm-pill strong{display:block;font-size:12px;margin-bottom:4px}.nlm-pill span{font-size:13px;font-weight:600}.nlm-ok{color:#008a20}.nlm-error{color:#b32d2e}.nlm-pending{color:#646970}.nl-status .nl-qr-preview{display:none!important}.nlm-source-list{margin:8px 0 0 18px}.nlm-source-list li{margin:4px 0}.nlm-link-box{word-break:break-all}.nlm-link-anchor{color:#2271b1;text-decoration:none;word-break:break-all}.nlm-link-anchor.is-disabled{color:#50575e;pointer-events:none;text-decoration:none;cursor:default}.nlm-inline-notice{margin-top:8px;padding:8px 10px;border-radius:6px;font-size:12px;line-height:1.4;border:1px solid #dcdcde;background:#fcfcfc}.nlm-inline-notice.is-ok{border-color:#b8e6bf;background:#edfaef;color:#0a7a24}.nlm-inline-notice.is-error{border-color:#f1b7b9;background:#fff5f5;color:#b32d2e}.nlm-inline-notice.is-pending{color:#646970}.nlm-inline-notice a{color:inherit;text-decoration:underline}.nlm-button-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;align-items:stretch}.nlm-button-row .button{width:100%;min-width:0;margin:0;text-align:center;padding-left:10px;padding-right:10px;line-height:1.3;white-space:normal}@media (max-width:780px){.nlm-button-row{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:640px){.nlm-button-row{grid-template-columns:1fr}}.nlm-display-thumb{display:flex;align-items:center;justify-content:center;min-height:110px;padding:12px;border:1px solid #dbe4ed;border-radius:14px;background:#f7f9fb}.nlm-display-thumb img{display:block;max-width:100%;max-height:120px;width:auto;height:auto;object-fit:contain}.nlm-display-thumb.is-empty img{display:none}.nlm-display-thumb-empty{font-size:12px;color:#64748b;text-align:center}.nlm-display-thumb:not(.is-empty) .nlm-display-thumb-empty{display:none}.nlm-display-button-row{display:flex;flex-wrap:wrap;gap:8px}.nlm-preview-wrap{display:grid;gap:12px}.nlm-preview-lang{display:flex;gap:8px;flex-wrap:wrap}.nlm-preview-lang button{border:1px solid #dcdcde;background:#fff;border-radius:999px;padding:4px 10px;cursor:pointer}.nlm-preview-lang button.is-active{background:#2271b1;border-color:#2271b1;color:#fff}.nlm-phone{max-width:380px;background:#1d2327;border-radius:22px;padding:7px 6px 8px;margin:0 auto}.nlm-phone-screen{background:#fff;border-radius:15px;padding:12px}.nlm-preview-title{font-weight:700;font-size:15px;margin-bottom:12px}.nlm-preview-header{display:grid;gap:10px;justify-items:center;margin-bottom:12px;text-align:center}.nlm-preview-logo-wrap,.nlm-preview-product-image-wrap{display:flex;justify-content:center;align-items:center;width:100%}.nlm-preview-logo-wrap img{max-width:100%;max-height:110px;width:auto;height:auto;object-fit:contain}.nlm-preview-product-image-wrap img{max-width:100%;max-height:180px;width:auto;height:auto;object-fit:contain}.nlm-preview-vintage{font-size:17px;font-weight:500;line-height:1.15}.nlm-preview-name{font-size:28px;font-weight:700;line-height:1.08}.nlm-preview-subtitle{font-size:20px;font-weight:600;line-height:1.15}.nlm-preview-table{width:100%;border-collapse:collapse;font-size:12px}.nlm-preview-table td,.nlm-preview-table th{border:1px solid #dcdcde;padding:6px 8px;vertical-align:top}.nlm-preview-table th{text-align:left;background:#f6f7f7}.nlm-preview-ingredients{font-size:12px;margin-top:12px;line-height:1.45}.nlm-preview-producer{margin-top:12px;border:1px solid #dcdcde;border-radius:12px;padding:12px 14px;background:#fff}.nlm-preview-producer-grid{display:grid;gap:12px}.nlm-preview-producer-item{text-align:center}.nlm-preview-producer-label{font-size:12px;color:#646970;font-weight:600}.nlm-preview-producer-value{font-size:12px;line-height:1.45;color:#1d2327}.nlm-scan-box{display:grid;grid-template-columns:1fr 1fr;gap:12px}@media (max-width:782px){.nlm-scan-box{grid-template-columns:1fr}}.nlm-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:600}.nlm-badge.ok{background:#edfaef;color:#0a7a24}.nlm-badge.warn{background:#fff8e5;color:#946200}.nlm-badge.err{background:#fdeceb;color:#b32d2e}.nlm-validation-box{border:1px solid #dcdcde;border-radius:8px;padding:10px 12px;background:#fcfcfc}.nlm-validation-box.is-error{border-color:#d63638;background:#fff5f5}.nlm-validation-list{margin:8px 0 0 18px}.nlm-field-error{border-color:#d63638!important;box-shadow:0 0 0 1px #d63638}.nlm-field-hint{font-size:12px;color:#b32d2e;margin-top:4px;display:none}.nlm-field-hint.is-visible{display:block}.nlm-secondary-note{font-size:12px;color:#50575e}.nlm-copy-select-row{display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end}@media (max-width:782px){.nlm-copy-select-row{grid-template-columns:1fr}}.nlm-status-mini{font-size:12px;color:#646970;margin-top:6px}.nlm-preview-grid{display:grid;grid-template-columns:140px minmax(0,1fr);gap:18px;align-items:start;margin-top:14px}.nlm-preview-side{display:grid;gap:10px;align-content:start}.nlm-preview-main{display:grid;gap:8px;justify-items:center}.nlm-preview-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.nlm-liability-box{margin-top:14px}.nlm-secondary-note + .nlm-validation-box{display:none}@media (max-width:900px){.nlm-preview-grid{grid-template-columns:1fr}.nlm-preview-main{justify-items:start}.nlm-phone{margin:0}}
        </style>
        <div class="nlm-wrap">
            <p class="nlm-note"><?php esc_html_e('Importierte Daten und manuelle Eingaben bleiben gemeinsam bearbeitbar. Manuelle Änderungen haben beim Erstellen Vorrang.', 'wine-e-label'); ?></p>
            <input type="hidden" name="wine_e_label_title" id="wine_e_label_title" value="<?php echo esc_attr((string) ($data['title'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_energy" id="wine_e_label_energy" value="<?php echo esc_attr((string) ($data['energy'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_carbs" id="wine_e_label_carbs" value="<?php echo esc_attr((string) ($data['carbs'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_sugar" id="wine_e_label_sugar" value="<?php echo esc_attr((string) ($data['sugar'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_minor" id="wine_e_label_minor" value="<?php echo esc_attr((string) ($data['minor'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_minor_mode" id="wine_e_label_minor_mode" value="<?php echo esc_attr((string) ($data['minor_mode'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_fat" id="wine_e_label_fat" value="<?php echo esc_attr((string) ($data['fat'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_saturates" id="wine_e_label_saturates" value="<?php echo esc_attr((string) ($data['saturates'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_protein" id="wine_e_label_protein" value="<?php echo esc_attr((string) ($data['protein'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_salt" id="wine_e_label_salt" value="<?php echo esc_attr((string) ($data['salt'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_salt_natural" id="wine_e_label_salt_natural" value="<?php echo esc_attr((string) ($data['salt_natural'] ?? '0')); ?>">
            <input type="hidden" name="wine_e_label_ingredients_html" id="wine_e_label_ingredients_html" value="<?php echo esc_attr((string) ($data['ingredients_html'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_footnote" id="wine_e_label_footnote" value="<?php echo esc_attr((string) ($data['footnote'] ?? '')); ?>">
            <input type="hidden" name="wine_e_label_pretable_notice" id="wine_e_label_pretable_notice" value="<?php echo esc_attr((string) ($data['pretable_notice'] ?? '')); ?>">

            <div class="nlm-top-grid">
                <div class="nlm-card">
                    <h3><?php esc_html_e('Ablaufstatus', 'wine-e-label'); ?></h3>
                    <div class="nlm-pill-grid" id="nlm_workflow_grid">
                        <div class="nlm-pill"><strong><?php esc_html_e('Import', 'wine-e-label'); ?></strong><span id="nlm_state_import" class="<?php echo esc_attr($workflow['import']['class']); ?>"><?php echo esc_html($workflow['import']['label']); ?></span></div>
                        <div class="nlm-pill"><strong><?php esc_html_e('Manuelle Daten', 'wine-e-label'); ?></strong><span id="nlm_state_manual" class="<?php echo esc_attr($workflow['manual']['class']); ?>"><?php echo esc_html($workflow['manual']['label']); ?></span></div>
                        <div class="nlm-pill"><strong><?php esc_html_e('E-Label-Seite', 'wine-e-label'); ?></strong><span id="nlm_state_page" class="<?php echo esc_attr($workflow['page']['class']); ?>"><?php echo esc_html($workflow['page']['label']); ?></span></div>
                        <div class="nlm-pill"><strong><?php esc_html_e('QR-Code', 'wine-e-label'); ?></strong><span id="nlm_state_qr" class="<?php echo esc_attr($workflow['qr']['class']); ?>"><?php echo esc_html($workflow['qr']['label']); ?></span></div>
                        <div class="nlm-pill"><strong><?php esc_html_e('Pflichtfelder', 'wine-e-label'); ?></strong><span id="nlm_state_validation" class="<?php echo esc_attr($workflow['validation']['class']); ?>"><?php echo esc_html($workflow['validation']['label']); ?></span></div>
                    </div>
                </div>
                <div class="nlm-card">
                    <h3><?php esc_html_e('Datenquelle', 'wine-e-label'); ?></h3>
                    <div><span class="nlm-badge <?php echo esc_attr($source_summary['badge_class']); ?>" id="nlm_source_badge"><?php echo esc_html($source_summary['label']); ?></span></div>
                    <div class="nlm-status-mini" id="nlm_source_meta"><?php echo esc_html($source_summary['meta']); ?></div>
                    <ul class="nlm-source-list" id="nlm_source_list">
                        <?php foreach ($source_summary['lines'] as $line) : ?><li><?php echo esc_html($line); ?></li><?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="nlm-main-grid">
                <div class="nlm-left-stack">
                    <div class="nlm-card">
                        <h3><?php esc_html_e('Produktinformationen', 'wine-e-label'); ?></h3>
                        <div class="nlm-field" data-required-field="bezeichnung">
                            <label for="nlm_bezeichnung"><?php esc_html_e('Bezeichnung', 'wine-e-label'); ?><span class="nlm-required">*</span></label>
                            <input type="text" id="nlm_bezeichnung" name="wine_e_label_manual[product][bezeichnung]" value="<?php echo esc_attr($manual['product']['bezeichnung']); ?>">
                            <div class="nlm-field-hint"><?php esc_html_e('Pflichtfeld', 'wine-e-label'); ?></div>
                        </div>
                        <div class="nlm-row">
                            <div class="nlm-field" data-required-field="wein_nr">
                                <label for="nlm_wein_nr"><?php esc_html_e('Wein-Nr.', 'wine-e-label'); ?><span class="nlm-required">*</span></label>
                                <input type="text" id="nlm_wein_nr" name="wine_e_label_manual[product][wein_nr]" value="<?php echo esc_attr($manual['product']['wein_nr']); ?>">
                                <div class="nlm-field-hint"><?php esc_html_e('Pflichtfeld', 'wine-e-label'); ?></div>
                            </div>
                            <div class="nlm-field">
                                <label for="nlm_ap_nr"><?php esc_html_e('AP-Nr.', 'wine-e-label'); ?></label>
                                <input type="text" id="nlm_ap_nr" name="wine_e_label_manual[product][ap_nr]" value="<?php echo esc_attr($manual['product']['ap_nr']); ?>">
                            </div>
                        </div>
                        <div class="nlm-field" data-required-field="kategorie">
                            <label for="nlm_kategorie"><?php esc_html_e('Kategorie', 'wine-e-label'); ?><span class="nlm-required">*</span></label>
                            <select id="nlm_kategorie" name="wine_e_label_manual[product][kategorie]">
                                <option value=""><?php esc_html_e('Kategorie wählen', 'wine-e-label'); ?></option>
                                <?php foreach ($categories as $categoryValue => $categoryLabel) : ?><option value="<?php echo esc_attr($categoryValue); ?>" <?php selected($manual['product']['kategorie'], $categoryValue); ?>><?php echo esc_html($categoryLabel); ?></option><?php endforeach; ?>
                            </select>
                            <div class="nlm-field-hint"><?php esc_html_e('Pflichtfeld', 'wine-e-label'); ?></div>
                        </div>
                        <div class="nlm-required-note"><?php esc_html_e('Pflichtfelder sind mit * markiert.', 'wine-e-label'); ?></div>
                    </div>

                    <div class="nlm-card">
                        <h3><?php esc_html_e('Nährwertangaben', 'wine-e-label'); ?></h3>
                        <div class="nlm-row">
                            <div class="nlm-field" data-required-field="alkohol_gl">
                                <label for="nlm_alkohol_gl"><?php esc_html_e('Alkohol', 'wine-e-label'); ?><span class="nlm-required">*</span></label>
                                <input type="text" id="nlm_alkohol_gl" name="wine_e_label_manual[nutrition][alkohol_gl]" value="<?php echo esc_attr($manual['nutrition']['alkohol_gl']); ?>">
                                <div class="nlm-inline-note">g/l</div>
                                <div class="nlm-field-hint"><?php esc_html_e('Pflichtfeld', 'wine-e-label'); ?></div>
                            </div>
                            <div class="nlm-field">
                                <label><?php esc_html_e('Alkohol in vol%', 'wine-e-label'); ?></label>
                                <div class="nlm-derived" id="nlm_alkohol_vol"><?php echo esc_html($derived['alcohol_vol']); ?></div>
                                <div class="nlm-inline-note"><?php esc_html_e('automatisch aus g/l berechnet', 'wine-e-label'); ?></div>
                            </div>
                        </div>
                        <div class="nlm-row">
                            <div class="nlm-field">
                                <label for="nlm_restzucker"><?php esc_html_e('Restzucker', 'wine-e-label'); ?></label>
                                <input type="text" id="nlm_restzucker" name="wine_e_label_manual[nutrition][restzucker_gl]" value="<?php echo esc_attr($manual['nutrition']['restzucker_gl']); ?>">
                                <div class="nlm-inline-note">g/l</div>
                            </div>
                            <div class="nlm-field">
                                <label for="nlm_gesamtsaeure"><?php esc_html_e('Gesamtsäure', 'wine-e-label'); ?></label>
                                <input type="text" id="nlm_gesamtsaeure" name="wine_e_label_manual[nutrition][gesamtsaeure_gl]" value="<?php echo esc_attr($manual['nutrition']['gesamtsaeure_gl']); ?>">
                                <div class="nlm-inline-note">g/l</div>
                            </div>
                        </div>
                        <div class="nlm-field">
                            <label><?php esc_html_e('Glycerin', 'wine-e-label'); ?></label>
                            <div class="nlm-radio-group">
                                <label><input type="radio" name="wine_e_label_manual[nutrition][glycerin_mode]" value="standard" <?php checked($manual['nutrition']['glycerin_mode'], 'standard'); ?>> <?php esc_html_e('Standardwert', 'wine-e-label'); ?></label>
                                <label><input type="radio" name="wine_e_label_manual[nutrition][glycerin_mode]" value="edelsuess" <?php checked($manual['nutrition']['glycerin_mode'], 'edelsuess'); ?>> <?php esc_html_e('Standardwert edelsüß', 'wine-e-label'); ?></label>
                                <label><input type="radio" name="wine_e_label_manual[nutrition][glycerin_mode]" value="manual" <?php checked($manual['nutrition']['glycerin_mode'], 'manual'); ?>> <?php esc_html_e('manueller Analysewert', 'wine-e-label'); ?></label>
                            </div>
                            <div class="nlm-row" style="margin-top:8px;">
                                <div class="nlm-field">
                                    <input type="text" id="nlm_glycerin_manual" name="wine_e_label_manual[nutrition][glycerin_manual]" value="<?php echo esc_attr($manual['nutrition']['glycerin_manual']); ?>">
                                    <div class="nlm-inline-note">g/l</div>
                                </div>
                                <div class="nlm-field">
                                    <div class="nlm-derived" id="nlm_glycerin_effective"><?php echo esc_html($derived['glycerin']); ?> g/l</div>
                                    <div class="nlm-inline-note"><?php esc_html_e('wirksamer Wert', 'wine-e-label'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="nlm-group-block">
                            <strong><?php esc_html_e('Berechnete Werte (pro 100ml)', 'wine-e-label'); ?></strong>
                            <div class="nlm-row" style="margin-top:10px;">
                                <div class="nlm-field"><label><?php esc_html_e('Brennwert', 'wine-e-label'); ?></label><div class="nlm-derived" id="nlm_energy_preview"><?php echo esc_html($derived['energy']); ?></div></div>
                                <div class="nlm-field"><label><?php esc_html_e('Kohlenhydrate', 'wine-e-label'); ?></label><div class="nlm-derived" id="nlm_carbs_preview"><?php echo esc_html($derived['carbs']); ?> g</div></div>
                            </div>
                            <div class="nlm-field"><label><?php esc_html_e('davon Zucker', 'wine-e-label'); ?></label><div class="nlm-derived" id="nlm_sugar_preview"><?php echo esc_html($derived['sugar']); ?> g</div></div>
                        </div>
                        <div class="nlm-group-block">
                            <strong><?php esc_html_e('Weitere Nährwertangaben', 'wine-e-label'); ?></strong>
                            <div class="nlm-radio-group" style="margin-top:10px;">
                                <label><input type="radio" name="wine_e_label_manual[nutrition][restwerte_mode]" value="text" <?php checked($manual['nutrition']['restwerte_mode'], 'text'); ?>> <?php esc_html_e('Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz', 'wine-e-label'); ?></label>
                                <label><input type="radio" name="wine_e_label_manual[nutrition][restwerte_mode]" value="list" <?php checked($manual['nutrition']['restwerte_mode'], 'list'); ?>> <?php esc_html_e('Analysewerte auflisten', 'wine-e-label'); ?></label>
                            </div>
                            <div id="nlm_analysis_fields" class="<?php echo $manual['nutrition']['restwerte_mode'] === 'list' ? '' : 'nlm-hidden'; ?>" style="margin-top:10px;">
                                <div class="nlm-row">
                                    <div class="nlm-field"><label for="nlm_fat"><?php esc_html_e('Fett', 'wine-e-label'); ?></label><input type="text" id="nlm_fat" name="wine_e_label_manual[nutrition][fat]" value="<?php echo esc_attr($manual['nutrition']['fat']); ?>"></div>
                                    <div class="nlm-field"><label for="nlm_saturates"><?php esc_html_e('davon gesättigte Fettsäuren', 'wine-e-label'); ?></label><input type="text" id="nlm_saturates" name="wine_e_label_manual[nutrition][saturates]" value="<?php echo esc_attr($manual['nutrition']['saturates']); ?>"></div>
                                </div>
                                <div class="nlm-row">
                                    <div class="nlm-field"><label for="nlm_protein"><?php esc_html_e('Eiweiß', 'wine-e-label'); ?></label><input type="text" id="nlm_protein" name="wine_e_label_manual[nutrition][protein]" value="<?php echo esc_attr($manual['nutrition']['protein']); ?>"></div>
                                    <div class="nlm-field"><label for="nlm_salt"><?php esc_html_e('Salz', 'wine-e-label'); ?></label><input type="text" id="nlm_salt" name="wine_e_label_manual[nutrition][salt]" value="<?php echo esc_attr($manual['nutrition']['salt']); ?>"></div>
                                </div>
                                <label><input type="checkbox" name="wine_e_label_manual[nutrition][salt_natural]" value="1" <?php checked($manual['nutrition']['salt_natural'], '1'); ?>> <?php esc_html_e('Angegebener Salzgehalt ist ausschließlich auf die Anwesenheit natürlich vorkommenden Natriums zurückzuführen.', 'wine-e-label'); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="nlm-card">
                        <h3><?php esc_html_e('Kopfbereich und Zusatzangaben', 'wine-e-label'); ?></h3>
                <div class="nlm-help-inline"><?php esc_html_e('Hier pflegst du die produktspezifischen Inhalte für Logo-Bereich und Zusatzangaben. Sichtbarkeit und Größen steuerst du global über die Design-Seite.', 'wine-e-label'); ?></div>

                        <div class="nlm-group-block">
                            <strong><?php esc_html_e('Produktbild', 'wine-e-label'); ?></strong>
                    <div class="nlm-help-inline" style="margin-top:8px;"><?php esc_html_e('Standardmäßig wird das WooCommerce-Produktbild verwendet. Optional kannst du hier ein eigenes Bild für dieses E-Label hinterlegen.', 'wine-e-label'); ?></div>
                            <div style="display:grid;grid-template-columns:120px minmax(0,1fr);gap:12px;align-items:start;margin-top:10px;">
                                <div class="nlm-display-thumb<?php echo trim((string) ($display_config['custom_image_url'] ?? '')) === '' ? ' is-empty' : ''; ?>" id="nlm_display_image_thumb">
                                    <img id="nlm_display_image_thumb_img" src="<?php echo esc_url((string) ($resolved_display['product_image_url'] ?? '')); ?>" alt="<?php echo esc_attr((string) ($resolved_display['product_image_alt'] ?? '')); ?>">
                            <span class="nlm-display-thumb-empty"><?php esc_html_e('Kein Produktbild verfügbar', 'wine-e-label'); ?></span>
                                </div>
                                <div style="display:flex;flex-direction:column;gap:10px;">
                                    <div class="nlm-field">
                                        <label for="nlm_display_custom_image_url"><?php esc_html_e('Eigenes Produktbild', 'wine-e-label'); ?></label>
                                        <input type="text" id="nlm_display_custom_image_url" name="wine_e_label_display[custom_image_url]" value="<?php echo esc_attr((string) ($display_config['custom_image_url'] ?? '')); ?>" readonly>
                                        <div class="nlm-help-inline" id="nlm_display_image_default_note"><?php echo esc_html(trim((string) ($display_config['custom_image_url'] ?? '')) === '' ? __('Aktuell wird das WooCommerce-Produktbild verwendet.', 'wine-e-label') : __('Aktuell ist ein eigenes Produktbild hinterlegt.', 'wine-e-label')); ?></div>
                                    </div>
                                    <div class="nlm-display-button-row">
                        <button type="button" class="button" id="nlm_display_image_select"><?php esc_html_e('Eigenes Produktbild wählen oder hochladen', 'wine-e-label'); ?></button>
                        <button type="button" class="button" id="nlm_display_image_reset"><?php esc_html_e('Auf Produktbild zurücksetzen', 'wine-e-label'); ?></button>
                                    </div>
                                    <div class="nlm-field">
                                        <label for="nlm_display_custom_image_alt"><?php esc_html_e('Alternativtext Produktbild', 'wine-e-label'); ?></label>
                                        <input type="text" id="nlm_display_custom_image_alt" name="wine_e_label_display[custom_image_alt]" value="<?php echo esc_attr((string) ($display_config['custom_image_alt'] ?? '')); ?>" placeholder="<?php echo esc_attr((string) ($display_defaults['product_image_alt'] ?? '')); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="nlm-group-block">
                            <strong><?php esc_html_e('Weinname, Jahrgang und Weinstil', 'wine-e-label'); ?></strong>
                    <div class="nlm-help-inline" style="margin-top:8px;"><?php esc_html_e('Leer gelassene Felder werden automatisch aus dem Produkt übernommen, sofern dort passende Daten vorhanden sind.', 'wine-e-label'); ?></div>
                            <div class="nlm-row" style="margin-top:10px;">
                                <div class="nlm-field">
                                    <label for="nlm_display_wine_name"><?php esc_html_e('Weinname', 'wine-e-label'); ?></label>
                                    <input type="text" id="nlm_display_wine_name" name="wine_e_label_display[wine_name]" value="<?php echo esc_attr((string) ($display_config['wine_name'] ?? '')); ?>" placeholder="<?php echo esc_attr((string) ($display_defaults['wine_name'] ?? '')); ?>">
                                    <div class="nlm-help-inline"><?php esc_html_e('Leer = WooCommerce-Produkttitel verwenden.', 'wine-e-label'); ?></div>
                                </div>
                                <div class="nlm-field">
                                    <label for="nlm_display_vintage"><?php esc_html_e('Jahrgang', 'wine-e-label'); ?></label>
                                    <input type="text" id="nlm_display_vintage" name="wine_e_label_display[vintage]" value="<?php echo esc_attr((string) ($display_config['vintage'] ?? '')); ?>" placeholder="<?php echo esc_attr((string) ($display_defaults['vintage'] ?? '')); ?>">
                                    <div class="nlm-help-inline"><?php esc_html_e('Leer = erkannte Produktdaten verwenden.', 'wine-e-label'); ?></div>
                                </div>
                            </div>
                            <div class="nlm-field">
                                <label for="nlm_display_subtitle"><?php esc_html_e('Weinstil / Untertitel', 'wine-e-label'); ?></label>
                                <input type="text" id="nlm_display_subtitle" name="wine_e_label_display[subtitle]" value="<?php echo esc_attr((string) ($display_config['subtitle'] ?? '')); ?>" placeholder="<?php echo esc_attr((string) ($display_defaults['subtitle'] ?? '')); ?>">
                                <div class="nlm-help-inline"><?php esc_html_e('Leer = erkannte Produktdaten verwenden.', 'wine-e-label'); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="nlm-card">
                        <h3><?php esc_html_e('Erstellung', 'wine-e-label'); ?></h3>
                        <div class="nlm-field">
                            <label for="nlm_copy_source_product"><?php esc_html_e('Daten aus bestehendem Produkt übernehmen', 'wine-e-label'); ?></label>
                            <div class="nlm-copy-select-row">
                                <select id="nlm_copy_source_product">
                                    <option value=""><?php esc_html_e('Produkt auswählen …', 'wine-e-label'); ?></option>
                                    <?php foreach ($copy_products as $copy_product) : ?>
                                        <option value="<?php echo esc_attr((string) $copy_product['id']); ?>"><?php echo esc_html($copy_product['label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="button" id="nlm_copy_source_apply"><?php esc_html_e('Daten übernehmen', 'wine-e-label'); ?></button>
                            </div>
                            <div class="nlm-help-inline"><?php esc_html_e('Gut für Vorjahresprodukte: Formularwerte werden übernommen, Slug und erzeugte Seite bleiben unberührt.', 'wine-e-label'); ?></div>
                        </div>
                        <div class="nlm-validation-box <?php echo $workflow['validation']['class'] === 'nlm-error' ? 'is-error' : ''; ?>" id="nlm_validation_summary_box">
                            <strong><?php esc_html_e('Prüfung vor dem Erstellen', 'wine-e-label'); ?></strong>
                            <div id="nlm_validation_summary_text"><?php echo esc_html($workflow['validation']['label']); ?></div>
                            <ul class="nlm-validation-list <?php echo empty($workflow['validation']['missing']) ? 'nlm-hidden' : ''; ?>" id="nlm_validation_summary_list">
                                <?php foreach ($workflow['validation']['missing'] as $missing) : ?><li><?php echo esc_html($missing); ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="nlm-row" style="margin-top:12px;">
                            <div class="nlm-field">
                                <label for="nlm_footer_wine_nr"><?php esc_html_e('Wein-Nr. für Link', 'wine-e-label'); ?><span class="nlm-required">*</span></label>
                                <input type="text" id="nlm_footer_wine_nr" value="<?php echo esc_attr((string) ($manual['product']['wein_nr'] ?? ($data['wine_nr'] ?? ''))); ?>">
                                <div class="nlm-field-hint"><?php esc_html_e('Pflichtfeld', 'wine-e-label'); ?></div>
                            </div>
                            <div class="nlm-field" data-required-field="slug">
                                <label for="nlm_footer_slug"><?php esc_html_e('Slug / URL-Teil', 'wine-e-label'); ?><span class="nlm-required">*</span></label>
                                <input type="text" id="nlm_footer_slug" value="<?php echo esc_attr($current_slug); ?>" placeholder="<?php echo esc_attr(Wine_E_Label_Importer::suggest_slug((string) ($manual['product']['wein_nr'] ?? ''), '')); ?>">
                                <div class="nlm-field-hint"><?php esc_html_e('Pflichtfeld', 'wine-e-label'); ?></div>
                            </div>
                        </div>
                        <div class="nlm-link-tools" style="margin-top:-2px;margin-bottom:10px;">
                            <button type="button" class="button-link" id="nlm_copy_slug"><?php esc_html_e('Slug kopieren', 'wine-e-label'); ?></button>
                            <button type="button" class="button-link" id="nlm_apply_slug_suggestion"><?php esc_html_e('Vorschlag aus Wein-Nr. übernehmen', 'wine-e-label'); ?></button>
                        </div>
                        <div class="nlm-field">
                            <label><?php esc_html_e('E-Label-Link', 'wine-e-label'); ?></label>
                            <div class="nlm-derived nlm-link-box"><a id="nlm_public_link_anchor" class="nlm-link-anchor <?php echo $page_status['ok'] ? '' : 'is-disabled'; ?>" href="<?php echo $page_status['ok'] ? esc_url($page_status['url']) : '#'; ?>" target="_blank" <?php echo $page_status['ok'] ? '' : 'aria-disabled="true" tabindex="-1"'; ?>><span id="nlm_public_link_text"><?php echo esc_html($link_preview); ?></span></a></div>
                            <div class="nlm-link-tools">
                                <button type="button" class="button-link" id="nlm_copy_link"><?php esc_html_e('Link kopieren', 'wine-e-label'); ?></button>
                                <button type="button" class="button-link" id="nlm_open_link" <?php echo $page_status['ok'] ? '' : 'disabled'; ?>><?php esc_html_e('Link öffnen', 'wine-e-label'); ?></button>
                            </div>
                            <div class="nlm-inline-notice is-<?php echo esc_attr($page_status['ok'] ? 'ok' : $page_status['level']); ?>" id="nlm_link_notice"><?php echo esc_html($page_status['ok'] ? __('E-Label-Seite erfolgreich erstellt', 'wine-e-label') : $page_status['message']); ?></div>
                        </div>
<div class="nlm-button-row">
                            <button type="button" class="button button-primary" id="wine_e_label_manual_create"><?php echo esc_html($create_button_label); ?></button>
                            <button type="button" class="button" id="nlm_delete_generated"><?php esc_html_e('E-Label und QR-Code löschen', 'wine-e-label'); ?></button>
                            <button type="button" class="button" id="nlm_clear_manual_data"><?php esc_html_e('Manuelle Daten leeren', 'wine-e-label'); ?></button>
                            <button type="button" class="button" id="nlm_reset_all_data"><?php esc_html_e('Alles zurücksetzen', 'wine-e-label'); ?></button>
                        </div>
                        <div class="nlm-secondary-note"><?php esc_html_e('Import löschen entfernt nur die Importquelle. Manuelle Daten leeren setzt nur das Formular zurück. Alles zurücksetzen entfernt beides und die erzeugte E-Label-Seite.', 'wine-e-label'); ?></div>
                        <div class="nlm-validation-box" style="margin-top:10px;">
                            <strong><?php esc_html_e('Hinweis zu Verantwortung und Haftung', 'wine-e-label'); ?></strong>
                            <div class="nlm-status-mini" style="margin-top:6px;"><?php esc_html_e('Dieses Plugin ist eine technische Hilfe. Für die inhaltliche Richtigkeit, Vollständigkeit und rechtliche Prüfung aller eingegebenen, importierten, übersetzten oder ausgegebenen Daten ist ausschließlich der Nutzer verantwortlich. Die Nutzung ersetzt keine Rechtsberatung. Trotz sorgfältiger Entwicklung wird keine Gewähr für die rechtliche Zulässigkeit oder Fehlerfreiheit in jedem Einzelfall übernommen, soweit gesetzlich zulässig.', 'wine-e-label'); ?></div>
                        </div>
                        <div class="nl-status" style="margin-top:12px;" id="nlm_left_status_box">
                            <div class="nl-status-row">
                                <div><strong><?php esc_html_e('Importstatus', 'wine-e-label'); ?></strong></div>
                                <?php if (($data['import_status'] ?? '') === 'success') : ?>
                                    <div class="nl-status-ok"><?php echo esc_html($data['import_message'] ?: __('Import erfolgreich', 'wine-e-label')); ?></div>
                                <?php elseif (($data['import_status'] ?? '') === 'error') : ?>
                                    <div class="nl-status-error"><?php echo esc_html($data['import_message'] ?: __('Import fehlgeschlagen', 'wine-e-label')); ?></div>
                                <?php else : ?>
                                    <div class="nl-status-pending"><?php esc_html_e('Noch kein Import durchgeführt', 'wine-e-label'); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="nl-status-row">
                                <div><strong><?php esc_html_e('E-Label-Seite', 'wine-e-label'); ?></strong></div>
                                <?php if ($page_status['ok']) : ?>
                                    <div class="nl-status-ok"><?php esc_html_e('E-Label-Seite erfolgreich erstellt', 'wine-e-label'); ?></div>
                                    <div class="nl-inline-link"><a href="<?php echo esc_url($page_status['url']); ?>" target="_blank"><?php esc_html_e('Link öffnen', 'wine-e-label'); ?></a></div>
                                <?php else : ?>
                                    <div class="nl-status-<?php echo esc_attr($page_status['level']); ?>"><?php echo esc_html($page_status['message']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="nl-status-row">
                                <div><strong><?php esc_html_e('QR-Code', 'wine-e-label'); ?></strong></div>
                                <?php if ($qr_status['ok']) : ?>
                                    <div class="nl-status-ok"><?php esc_html_e('QR-Code erfolgreich erstellt', 'wine-e-label'); ?></div>
                                    <div class="nl-inline-link"><button type="button" class="button-link wine-e-label-download-qr" data-product-id="<?php echo esc_attr($product_id); ?>"><?php esc_html_e('Download', 'wine-e-label'); ?></button></div>
                                <?php else : ?>
                                    <div class="nl-status-<?php echo esc_attr($qr_status['level']); ?>"><?php echo esc_html($qr_status['message']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="nlm-right-stack">
                    <div class="nlm-card nlm-ingredients-card">
                        <h3><?php esc_html_e('Zutaten', 'wine-e-label'); ?></h3>
                        <?php foreach ($catalog as $groupKey => $group) : $groupState = $manual['groups'][$groupKey] ?? []; ?>
                            <div class="nlm-group-block" data-group="<?php echo esc_attr($groupKey); ?>">
                                <?php if ($groupKey !== 'base') : ?><label><input class="nlm-group-toggle" type="checkbox" name="wine_e_label_manual[groups][<?php echo esc_attr($groupKey); ?>][enabled]" value="1" <?php checked($groupState['enabled'] ?? '0', '1'); ?>> <?php echo esc_html($group['toggle_label'] ?? $group['label']); ?></label><?php endif; ?>
                                <div class="nlm-group-body <?php echo ($groupKey === 'base' || ($groupState['enabled'] ?? '0') === '1') ? '' : 'nlm-hidden'; ?>" style="margin-top:10px;">
                                    <?php if (!empty($group['supports_mode'])) : ?><div class="nlm-radio-group" style="margin-bottom:10px;"><label><input type="radio" name="wine_e_label_manual[groups][<?php echo esc_attr($groupKey); ?>][mode]" value="list" <?php checked($groupState['mode'] ?? 'list', 'list'); ?>> <?php esc_html_e('Zutaten aufzählen', 'wine-e-label'); ?></label><label><input type="radio" name="wine_e_label_manual[groups][<?php echo esc_attr($groupKey); ?>][mode]" value="alternative" <?php checked($groupState['mode'] ?? 'list', 'alternative'); ?>> <?php esc_html_e('Alternativauswahl zur Angabe von bis zu 3 Stoffen, die wahlweise eingesetzt werden können', 'wine-e-label'); ?></label></div><?php endif; ?>
                                    <div class="nlm-items"><div class="nlm-head <?php echo ($groupKey === 'gases') ? 'no-third' : ''; ?>"><div></div><div><?php esc_html_e('Name', 'wine-e-label'); ?></div><?php if ($groupKey !== 'gases') : ?><div><?php echo ($groupKey === 'base' || $groupKey === 'enrichment') ? esc_html__('Bio', 'wine-e-label') : esc_html__('Darstellung', 'wine-e-label'); ?></div><?php endif; ?></div>
                                    <?php foreach ($group['items'] as $itemKey => $item) : $itemState = $groupState['items'][$itemKey] ?? []; $showThird = $groupKey !== 'gases'; ?>
                                        <div class="nlm-item <?php echo $showThird ? '' : 'no-third'; ?>" data-categories="<?php echo esc_attr(($groupKey === 'other') ? '[]' : (isset($item['categories']) ? wp_json_encode($item['categories']) : '[]')); ?>">
                                            <div><input class="nlm-item-selected" type="checkbox" name="wine_e_label_manual[groups][<?php echo esc_attr($groupKey); ?>][items][<?php echo esc_attr($itemKey); ?>][selected]" value="1" <?php checked($itemState['selected'] ?? '0', '1'); ?>></div>
                                            <div><?php echo esc_html($item['label']); ?><?php if (!empty($item['note'])) : ?> <span class="nlm-muted"><?php echo esc_html('(' . $item['note'] . ')'); ?></span><?php endif; ?></div>
                                            <?php if ($showThird) : ?><div>
                                                <?php if ($groupKey === 'base' || $groupKey === 'enrichment') : ?>
                                                    <?php if (!empty($item['bio'])) : ?><label><input type="checkbox" name="wine_e_label_manual[groups][<?php echo esc_attr($groupKey); ?>][items][<?php echo esc_attr($itemKey); ?>][bio]" value="1" <?php checked($itemState['bio'] ?? '0', '1'); ?>> <?php esc_html_e('biologisch', 'wine-e-label'); ?></label><?php endif; ?>
                                                <?php elseif (!empty($item['e'])) : ?>
                                                    <div class="nlm-display-choice">
                                                        <label><input type="checkbox" class="nlm-enumber-toggle" data-enumber="<?php echo esc_attr($item['e']); ?>" name="wine_e_label_manual[groups][<?php echo esc_attr($groupKey); ?>][items][<?php echo esc_attr($itemKey); ?>][enumber]" value="1" <?php checked($itemState['enumber'] ?? '0', '1'); ?>> <?php printf(esc_html__('statt Name %s anzeigen', 'wine-e-label'), esc_html($item['e'])); ?></label>
                                                    </div>
                                                <?php endif; ?>
                                            </div><?php endif; ?>
                                        </div>
                                    <?php endforeach; ?></div>
                                    <?php if ($groupKey === 'other') :
                                        $customItems = is_array($groupState['custom_items'] ?? null) ? array_values($groupState['custom_items']) : [];
                                        $renderCustomItems = $customItems;
                                        if (empty($renderCustomItems) && (($groupState['enabled'] ?? '0') === '1')) {
                                            $renderCustomItems[] = ['label' => '', 'selected' => '1'];
                                        }
                                    ?>
                                        <div class="nlm-custom-items">
                                            <div class="nlm-custom-items-head">
                                                <strong><?php esc_html_e('Zusätzliche Stoffe', 'wine-e-label'); ?></strong>
                                                <button type="button" class="button button-secondary nlm-add-custom-item" data-group="other"><?php esc_html_e('Zusätzlichen Stoff hinzufügen', 'wine-e-label'); ?></button>
                                            </div>
                                            <div class="nlm-custom-items-list" id="nlm_other_custom_items">
                                                <?php foreach ($renderCustomItems as $customIndex => $customItem) :
                                                    $customDisplay = trim((string) (($customItem['label'] ?? '') !== '' ? $customItem['label'] : (((string) ($customItem['enumber'] ?? '0') === '1' && !empty($customItem['e'])) ? (string) $customItem['e'] : (string) ($customItem['e'] ?? ''))));
                                                ?>
                                                    <div class="nlm-custom-item-row" data-index="<?php echo esc_attr((string) $customIndex); ?>">
                                                        <input type="hidden" name="wine_e_label_manual[groups][other][custom_items][<?php echo esc_attr((string) $customIndex); ?>][selected]" value="1">
                                                        <div class="nlm-field">
                                                            <label><?php esc_html_e('Stoff oder E-Nr.', 'wine-e-label'); ?></label>
                                                            <input type="text" class="nlm-custom-label" name="wine_e_label_manual[groups][other][custom_items][<?php echo esc_attr((string) $customIndex); ?>][label]" value="<?php echo esc_attr($customDisplay); ?>">
                                                        </div>
                                                        <div class="nlm-field">
                                                            <button type="button" class="button-link button-link-delete nlm-remove-custom-item"><?php esc_html_e('Entfernen', 'wine-e-label'); ?></button>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="nlm-custom-items-empty <?php echo !empty($renderCustomItems) ? 'nlm-hidden' : ''; ?>" id="nlm_other_custom_items_empty"><?php esc_html_e('Noch keine zusätzlichen Stoffe angelegt.', 'wine-e-label'); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="nlm-card">
                        <h3><?php esc_html_e('Live-Vorschau', 'wine-e-label'); ?></h3>
                        <div class="nlm-preview-wrap">
                            <div class="nlm-preview-lang" id="nlm_preview_lang_switch">
                                <button type="button" class="is-active" data-lang="de">DE</button>
                                <button type="button" data-lang="en">EN</button>
                                <button type="button" data-lang="it">IT</button>
                                <button type="button" data-lang="fr">FR</button>
                            </div>
                            <div class="nlm-scan-box">
                                <div><span class="nlm-badge <?php echo esc_attr($slug_quality['badge_class']); ?>" id="nlm_slug_quality_badge"><?php echo esc_html($slug_quality['label']); ?></span><div class="nlm-status-mini" id="nlm_slug_quality_text"><?php echo esc_html($slug_quality['text']); ?></div></div>
                                <div><span class="nlm-badge <?php echo esc_attr($workflow['page']['class'] === 'nlm-ok' ? 'ok' : 'warn'); ?>" id="nlm_mobile_status_badge"><?php echo esc_html($workflow['page']['class'] === 'nlm-ok' ? __('Seite geprüft', 'wine-e-label') : __('Vorschau lokal', 'wine-e-label')); ?></span><div class="nlm-status-mini"><?php esc_html_e('Kompakte Vorschau für Handy-Layout und QR-Scanbarkeit.', 'wine-e-label'); ?></div></div>
                            </div>
                            <div class="nlm-preview-grid">
                                <div class="nlm-preview-side">
                                    <div class="nlm-qr-thumb" id="nlm_preview_qr_wrap">
                                        <img id="nlm_preview_qr_img" src="<?php echo !empty($qr_status['preview']) ? esc_attr($qr_status['preview']) : ''; ?>" alt="QR Code" class="<?php echo !empty($qr_status['preview']) ? '' : 'nlm-hidden'; ?>">
                                        <div id="nlm_preview_qr_empty" class="nlm-qr-empty <?php echo !empty($qr_status['preview']) ? 'nlm-hidden' : ''; ?>"><?php esc_html_e('Noch kein QR-Code erzeugt.', 'wine-e-label'); ?></div>
                                    </div>
                                    <div class="nlm-preview-actions">
                                        <button type="button" class="button-link" id="nlm_preview_qr_view" <?php echo !empty($qr_status['preview']) ? '' : 'disabled'; ?>><?php esc_html_e('QR-Code ansehen', 'wine-e-label'); ?></button>
                                        <button type="button" class="button-link wine-e-label-download-qr" id="nlm_preview_qr_download" data-product-id="<?php echo esc_attr($product_id); ?>" <?php echo $qr_status['ok'] ? '' : 'disabled'; ?>><?php esc_html_e('QR-Code herunterladen', 'wine-e-label'); ?></button>
                                    </div>
                                </div>
                                <div class="nlm-preview-main">
                                    <div class="nlm-phone">
                                        <div class="nlm-phone-screen">
                                            <div class="nlm-preview-header <?php echo $preview_header_visible ? '' : 'nlm-hidden'; ?>" id="nlm_preview_header_block">
                                                <div class="nlm-preview-logo-wrap <?php echo (($design_settings['logo_enabled'] ?? '0') === '1' && $preview_logo_url !== '') ? '' : 'nlm-hidden'; ?>" id="nlm_preview_logo_wrap">
                                                    <img id="nlm_preview_logo_img" src="<?php echo esc_url($preview_logo_url); ?>" alt="<?php echo esc_attr($preview_logo_alt); ?>">
                                                </div>
                                                <div class="nlm-preview-product-image-wrap <?php echo (($design_settings['product_image_enabled'] ?? '0') === '1' && trim((string) ($resolved_display['product_image_url'] ?? '')) !== '') ? '' : 'nlm-hidden'; ?>" id="nlm_preview_product_image_wrap">
                                                    <img id="nlm_preview_product_image" src="<?php echo esc_url((string) ($resolved_display['product_image_url'] ?? '')); ?>" alt="<?php echo esc_attr((string) ($resolved_display['product_image_alt'] ?? '')); ?>">
                                                </div>
                                                <div class="nlm-preview-vintage <?php echo (($design_settings['vintage_enabled'] ?? '0') === '1' && trim((string) ($resolved_display['vintage'] ?? '')) !== '') ? '' : 'nlm-hidden'; ?>" id="nlm_preview_vintage"><?php echo esc_html((string) ($resolved_display['vintage'] ?? '')); ?></div>
                                                <div class="nlm-preview-name <?php echo (($design_settings['wine_name_enabled'] ?? '0') === '1' && trim((string) ($resolved_display['wine_name'] ?? '')) !== '') ? '' : 'nlm-hidden'; ?>" id="nlm_preview_name"><?php echo esc_html((string) ($resolved_display['wine_name'] ?? '')); ?></div>
                                                <div class="nlm-preview-subtitle <?php echo (($design_settings['subtitle_enabled'] ?? '0') === '1' && trim((string) ($resolved_display['subtitle'] ?? '')) !== '') ? '' : 'nlm-hidden'; ?>" id="nlm_preview_subtitle"><?php echo esc_html((string) ($resolved_display['subtitle'] ?? '')); ?></div>
                                            </div>
                                            <table class="nlm-preview-table">
                                                <thead><tr><th id="nlm_preview_headline"><?php esc_html_e('Nährwertangaben je 100ml', 'wine-e-label'); ?></th></tr></thead>
                                                <tbody>
                                                    <tr><td><div style="display:flex;justify-content:space-between;gap:12px"><span id="nlm_preview_label_energy"><?php esc_html_e('Brennwert', 'wine-e-label'); ?></span><span id="nlm_preview_value_energy"><?php echo esc_html($derived['energy']); ?></span></div></td></tr>
                                                    <tr><td><div style="display:flex;justify-content:space-between;gap:12px"><span id="nlm_preview_label_carbs"><?php esc_html_e('Kohlenhydrate', 'wine-e-label'); ?></span><span id="nlm_preview_value_carbs"><?php echo esc_html($derived['carbs']); ?> g</span></div></td></tr>
                                                    <tr><td><div style="display:flex;justify-content:space-between;gap:12px"><span id="nlm_preview_label_sugar"><?php esc_html_e('davon Zucker', 'wine-e-label'); ?></span><span id="nlm_preview_value_sugar"><?php echo esc_html($derived['sugar']); ?> g</span></div></td></tr>
                                                    <tr id="nlm_preview_row_fat" class="<?php echo $manual['nutrition']['restwerte_mode'] === 'list' && $manual['nutrition']['fat'] !== '' ? '' : 'nlm-hidden'; ?>"><td><div style="display:flex;justify-content:space-between;gap:12px"><span id="nlm_preview_label_fat"><?php esc_html_e('Fett', 'wine-e-label'); ?></span><span id="nlm_preview_value_fat"><?php echo esc_html($manual['nutrition']['fat']); ?><?php echo $manual['nutrition']['fat'] !== '' ? ' g' : ''; ?></span></div></td></tr>
                                                    <tr id="nlm_preview_row_saturates" class="<?php echo $manual['nutrition']['restwerte_mode'] === 'list' && $manual['nutrition']['saturates'] !== '' ? '' : 'nlm-hidden'; ?>"><td><div style="display:flex;justify-content:space-between;gap:12px"><span id="nlm_preview_label_saturates"><?php esc_html_e('davon gesättigte Fettsäuren', 'wine-e-label'); ?></span><span id="nlm_preview_value_saturates"><?php echo esc_html($manual['nutrition']['saturates']); ?><?php echo $manual['nutrition']['saturates'] !== '' ? ' g' : ''; ?></span></div></td></tr>
                                                    <tr id="nlm_preview_row_protein" class="<?php echo $manual['nutrition']['restwerte_mode'] === 'list' && $manual['nutrition']['protein'] !== '' ? '' : 'nlm-hidden'; ?>"><td><div style="display:flex;justify-content:space-between;gap:12px"><span id="nlm_preview_label_protein"><?php esc_html_e('Eiweiß', 'wine-e-label'); ?></span><span id="nlm_preview_value_protein"><?php echo esc_html($manual['nutrition']['protein']); ?><?php echo $manual['nutrition']['protein'] !== '' ? ' g' : ''; ?></span></div></td></tr>
                                                    <tr id="nlm_preview_row_salt" class="<?php echo $manual['nutrition']['restwerte_mode'] === 'list' && $manual['nutrition']['salt'] !== '' ? '' : 'nlm-hidden'; ?>"><td><div style="display:flex;justify-content:space-between;gap:12px"><span id="nlm_preview_label_salt"><?php esc_html_e('Salz', 'wine-e-label'); ?></span><span id="nlm_preview_value_salt"><?php echo esc_html($manual['nutrition']['salt']); ?><?php echo $manual['nutrition']['salt'] !== '' ? ' g' : ''; ?></span></div></td></tr>
                                                    <tr id="nlm_preview_row_salt_natural" class="<?php echo $manual['nutrition']['restwerte_mode'] === 'list' && $manual['nutrition']['salt_natural'] === '1' ? '' : 'nlm-hidden'; ?>"><td id="nlm_preview_salt_natural"><?php esc_html_e('Angegebener Salzgehalt ist ausschließlich auf die Anwesenheit natürlich vorkommenden Natriums zurückzuführen.', 'wine-e-label'); ?></td></tr>
                                                    <tr id="nlm_preview_row_minor" class="<?php echo $manual['nutrition']['restwerte_mode'] === 'list' ? 'nlm-hidden' : ''; ?>"><td id="nlm_preview_minor"><?php echo esc_html($manual['nutrition']['restwerte_mode'] === 'list' ? '' : __('Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz', 'wine-e-label')); ?></td></tr>
                                                </tbody>
                                            </table>
                                            <div class="nlm-preview-ingredients"><strong id="nlm_preview_label_ingredients"><?php esc_html_e('Zutaten', 'wine-e-label'); ?></strong>: <span id="nlm_preview_ingredients_html"><?php echo wp_kses_post((string) ($data['ingredients_html'] ?? '')); ?></span></div>
                                            <div class="nlm-preview-producer <?php echo $preview_producer_visible ? '' : 'nlm-hidden'; ?>" id="nlm_preview_producer_card">
                                                <div class="nlm-preview-producer-grid">
                                                    <div class="nlm-preview-producer-item">
                                                        <div class="nlm-preview-producer-label" id="nlm_preview_region_label"><?php echo esc_html($producer_labels['region']); ?></div>
                                                        <div class="nlm-preview-producer-value" id="nlm_preview_region_value"><?php echo nl2br(esc_html((string) ($design_settings['producer_region'] ?? ''))); ?></div>
                                                    </div>
                                                    <div class="nlm-preview-producer-item">
                                                        <div class="nlm-preview-producer-label" id="nlm_preview_country_label"><?php echo esc_html($producer_labels['country']); ?></div>
                                                        <div class="nlm-preview-producer-value" id="nlm_preview_country_value"><?php echo nl2br(esc_html((string) ($design_settings['producer_country'] ?? ''))); ?></div>
                                                    </div>
                                                    <div class="nlm-preview-producer-item">
                                                        <div class="nlm-preview-producer-label" id="nlm_preview_address_label"><?php echo esc_html($producer_labels['address']); ?></div>
                                                        <div class="nlm-preview-producer-value" id="nlm_preview_address_value"><?php echo nl2br(esc_html((string) ($design_settings['producer_address'] ?? ''))); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="nlm-validation-box nlm-liability-box">
                                <strong><?php esc_html_e('Hinweis zu Verantwortung und Haftung', 'wine-e-label'); ?></strong>
                                <div class="nlm-status-mini" style="margin-top:6px;"><?php esc_html_e('Dieses Plugin ist eine technische Hilfe. Für die inhaltliche Richtigkeit, Vollständigkeit und rechtliche Prüfung aller eingegebenen, importierten, übersetzten oder ausgegebenen Daten ist ausschließlich der Nutzer verantwortlich. Die Nutzung ersetzt keine Rechtsberatung. Trotz sorgfältiger Entwicklung wird keine Gewähr für die rechtliche Zulässigkeit oder Fehlerfreiheit in jedem Einzelfall übernommen, soweit gesetzlich zulässig.', 'wine-e-label'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_workflow_flags(int $product_id, array $data, array $manual, array $page_status, array $qr_status): array
    {
        $validation = $this->validate_required_fields($data, $manual);
        $hasImport = !empty($data['source_file_name']);
        $hasManualChanges = $this->has_manual_changes($manual, $data);
        return [
            'import' => [
                'label' => $hasImport ? __('geladen', 'wine-e-label') : __('offen', 'wine-e-label'),
                'class' => $hasImport ? 'nlm-ok' : 'nlm-pending',
            ],
            'manual' => [
                'label' => $hasManualChanges ? __('vorhanden', 'wine-e-label') : __('leer', 'wine-e-label'),
                'class' => $hasManualChanges ? 'nlm-ok' : 'nlm-pending',
            ],
            'page' => [
                'label' => $page_status['ok'] ? __('erstellt', 'wine-e-label') : __('offen', 'wine-e-label'),
                'class' => $page_status['ok'] ? 'nlm-ok' : ($page_status['level'] === 'error' ? 'nlm-error' : 'nlm-pending'),
            ],
            'qr' => [
                'label' => $qr_status['ok'] ? __('erstellt', 'wine-e-label') : __('offen', 'wine-e-label'),
                'class' => $qr_status['ok'] ? 'nlm-ok' : ($qr_status['level'] === 'error' ? 'nlm-error' : 'nlm-pending'),
            ],
            'validation' => [
                'label' => empty($validation['missing']) ? __('vollständig', 'wine-e-label') : __('fehlt', 'wine-e-label'),
                'class' => empty($validation['missing']) ? 'nlm-ok' : 'nlm-error',
                'missing' => $validation['missing'],
            ],
        ];
    }

    private function validate_required_fields(array $data, array $manual): array
    {
        $missing = [];
        $slug = trim((string) ($data['slug'] ?? ''));
        if (trim((string) ($manual['product']['bezeichnung'] ?? '')) === '') {
            $missing[] = __('Bezeichnung', 'wine-e-label');
        }
        if (trim((string) ($manual['product']['wein_nr'] ?? ($data['wine_nr'] ?? ''))) === '') {
            $missing[] = __('Wein-Nr.', 'wine-e-label');
        }
        if (trim((string) ($manual['product']['kategorie'] ?? '')) === '') {
            $missing[] = __('Kategorie', 'wine-e-label');
        }
        if (trim((string) ($manual['nutrition']['alkohol_gl'] ?? '')) === '') {
            $missing[] = __('Alkohol (g/l)', 'wine-e-label');
        }
        if ($slug === '') {
            $missing[] = __('Slug / URL-Teil', 'wine-e-label');
        }
        return ['missing' => $missing];
    }

    private function get_source_summary(array $data, array $manual): array
    {
        $snapshot = $this->get_manual_reference_snapshot($data);
        $diff = $this->compare_manual_configs($manual, $snapshot);
        $has_import = !empty($data['source_file_name']);
        $has_manual = $this->has_manual_changes($manual, $data);

        if ($has_import && ($diff['changed_count'] > 0 || $diff['added_count'] > 0 || $diff['removed_count'] > 0)) {
            $label = __('Import + manuelle Änderungen', 'wine-e-label');
            $badge = 'warn';
        } elseif ($has_import) {
            $label = __('WIPZN-Import', 'wine-e-label');
            $badge = 'ok';
        } elseif ($has_manual) {
            $label = __('Nur manuelle Eingabe', 'wine-e-label');
            $badge = 'ok';
        } else {
            $label = __('Noch keine Daten', 'wine-e-label');
            $badge = 'err';
        }

        $lines = [];
        if ($has_import) {
            $lines[] = sprintf(__('Quelldatei: %s', 'wine-e-label'), (string) $data['source_file_name']);
        }
        if ($diff['changed_count'] > 0) {
            $lines[] = sprintf(__('Manuell geändert: %d Felder', 'wine-e-label'), $diff['changed_count']);
        }
        if ($diff['added_count'] > 0) {
            $lines[] = sprintf(__('Manuell ergänzt: %d Felder', 'wine-e-label'), $diff['added_count']);
        }
        if ($diff['removed_count'] > 0) {
            $lines[] = sprintf(__('Manuell geleert: %d Felder', 'wine-e-label'), $diff['removed_count']);
        }
        foreach (array_slice($diff['field_labels'], 0, 4) as $field_label) {
            $lines[] = $field_label;
        }
        if ($lines === []) {
            $lines[] = __('Keine Abweichungen zwischen Import und aktueller Eingabe.', 'wine-e-label');
        }

        return [
            'label' => $label,
            'badge_class' => $badge,
            'meta' => $has_import && !empty($data['last_import']) ? sprintf(__('Letzter Import: %s', 'wine-e-label'), (string) $data['last_import']) : __('Aktueller Formularstand', 'wine-e-label'),
            'lines' => $lines,
        ];
    }

    private function compare_manual_configs(array $manual, array $snapshot): array
    {
        $manual_flat = $this->flatten_manual_config($manual);
        $snapshot_flat = $this->flatten_manual_config($snapshot);
        $labels = $this->get_manual_field_labels();
        $changed = 0;
        $added = 0;
        $removed = 0;
        $field_labels = [];

        $keys = array_values(array_unique(array_merge(array_keys($manual_flat), array_keys($snapshot_flat))));
        foreach ($keys as $key) {
            if ($this->should_ignore_manual_diff_key($key, $manual, $snapshot)) {
                continue;
            }
            $value = (string) ($manual_flat[$key] ?? '');
            $snapshot_value = (string) ($snapshot_flat[$key] ?? '');
            if ($value === $snapshot_value) {
                continue;
            }
            $value_empty = $this->is_effectively_empty_value($value);
            $snapshot_empty = $this->is_effectively_empty_value($snapshot_value);
            if ($value_empty && $snapshot_empty) {
                continue;
            }
            if (!$value_empty && $snapshot_empty) {
                $added++;
            } elseif ($value_empty && !$snapshot_empty) {
                $removed++;
            } else {
                $changed++;
            }
            $field_labels[] = $labels[$key] ?? $this->get_manual_field_label($key);
        }

        return [
            'changed_count' => $changed,
            'added_count' => $added,
            'removed_count' => $removed,
            'field_labels' => array_values(array_unique($field_labels)),
        ];
    }

    private function should_ignore_manual_diff_key(string $key, array $manual, array $snapshot): bool
    {
        if (preg_match('/^groups\.([^\.]+)\.mode$/', $key, $m)) {
            $group_key = (string) $m[1];
            $manual_enabled = (string) ($manual['groups'][$group_key]['enabled'] ?? '0');
            $snapshot_enabled = (string) ($snapshot['groups'][$group_key]['enabled'] ?? '0');
            if ($group_key !== 'base' && $manual_enabled !== '1' && $snapshot_enabled !== '1') {
                return true;
            }
        }

        if (preg_match('/^groups\.([^\.]+)\.items\.([^\.]+)\.(bio|enumber)$/', $key, $m)) {
            $group_key = (string) $m[1];
            $item_key = (string) $m[2];
            $manual_selected = (string) ($manual['groups'][$group_key]['items'][$item_key]['selected'] ?? '0');
            $snapshot_selected = (string) ($snapshot['groups'][$group_key]['items'][$item_key]['selected'] ?? '0');
            if ($manual_selected !== '1' && $snapshot_selected !== '1') {
                return true;
            }
        }

        if (preg_match('/^groups\.([^\.]+)\.custom_items\.(\d+)\.(label|e|enumber)$/', $key, $m)) {
            if ((string) $m[3] !== 'label') {
                return true;
            }
            $group_key = (string) $m[1];
            $index = (int) $m[2];
            $manual_selected = (string) ($manual['groups'][$group_key]['custom_items'][$index]['selected'] ?? '0');
            $snapshot_selected = (string) ($snapshot['groups'][$group_key]['custom_items'][$index]['selected'] ?? '0');
            if ($manual_selected !== '1' && $snapshot_selected !== '1') {
                return true;
            }
        }

        return false;
    }

    private function is_effectively_empty_value(string $value): bool
    {
        return $value === '' || $value === '0';
    }

    private function get_manual_reference_snapshot(array $data): array
    {
        $snapshot = Wine_E_Label_Manual_Builder::sanitize_config($data['import_snapshot'] ?? []);
        if (!empty($data['source_file_name']) && Wine_E_Label_Manual_Builder::has_meaningful_input($snapshot)) {
            return $snapshot;
        }
        return Wine_E_Label_Manual_Builder::default_config();
    }

    private function has_manual_changes(array $manual, array $data): bool
    {
        $snapshot = $this->get_manual_reference_snapshot($data);
        $diff = $this->compare_manual_configs($manual, $snapshot);
        return ($diff['changed_count'] + $diff['added_count'] + $diff['removed_count']) > 0;
    }

    private function flatten_manual_config(array $config): array
    {
        $flat = [];
        $iterator = function ($value, string $prefix = '') use (&$flat, &$iterator) {
            if (is_array($value)) {
                foreach ($value as $key => $child) {
                    $iterator($child, $prefix === '' ? (string) $key : $prefix . '.' . (string) $key);
                }
                return;
            }
            $flat[$prefix] = (string) $value;
        };
        $iterator($config);
        return $flat;
    }

    private function get_manual_field_labels(): array
    {
        $labels = [
            'product.bezeichnung' => __('Bezeichnung', 'wine-e-label'),
            'product.wein_nr' => __('Wein-Nr.', 'wine-e-label'),
            'product.ap_nr' => __('AP-Nr.', 'wine-e-label'),
            'product.kategorie' => __('Kategorie', 'wine-e-label'),
            'nutrition.alkohol_gl' => __('Alkohol', 'wine-e-label'),
            'nutrition.restzucker_gl' => __('Restzucker', 'wine-e-label'),
            'nutrition.gesamtsaeure_gl' => __('Gesamtsäure', 'wine-e-label'),
            'nutrition.glycerin_mode' => __('Glycerin-Modus', 'wine-e-label'),
            'nutrition.glycerin_manual' => __('Glycerin', 'wine-e-label'),
            'nutrition.restwerte_mode' => __('Weitere Nährwerte', 'wine-e-label'),
            'nutrition.fat' => __('Fett', 'wine-e-label'),
            'nutrition.saturates' => __('gesättigte Fettsäuren', 'wine-e-label'),
            'nutrition.protein' => __('Eiweiß', 'wine-e-label'),
            'nutrition.salt' => __('Salz', 'wine-e-label'),
            'nutrition.salt_natural' => __('Salz-Hinweis', 'wine-e-label'),
        ];

        $catalog = Wine_E_Label_Manual_Builder::get_catalog();
        foreach ($catalog as $group_key => $group) {
            $labels['groups.' . $group_key . '.enabled'] = sprintf(__('Gruppe: %s', 'wine-e-label'), $this->get_group_label((string) $group_key));
            if (!empty($group['supports_mode'])) {
                $labels['groups.' . $group_key . '.mode'] = sprintf(__('%s: Modus', 'wine-e-label'), $this->get_group_label((string) $group_key));
            }
            foreach (($group['items'] ?? []) as $item_key => $item) {
                $item_label = $this->get_catalog_item_label((string) $item_key);
                $labels['groups.' . $group_key . '.items.' . $item_key . '.selected'] = sprintf(__('%1$s: %2$s', 'wine-e-label'), $item_label, __('Auswahl', 'wine-e-label'));
                $labels['groups.' . $group_key . '.items.' . $item_key . '.bio'] = sprintf(__('%1$s: %2$s', 'wine-e-label'), $item_label, __('Bio', 'wine-e-label'));
                $labels['groups.' . $group_key . '.items.' . $item_key . '.enumber'] = sprintf(__('%1$s: %2$s', 'wine-e-label'), $item_label, __('E-Nummer-Anzeige', 'wine-e-label'));
            }
        }

        return $labels;
    }

    private function get_manual_field_label(string $key): string
    {
        $labels = $this->get_manual_field_labels();
        if (isset($labels[$key])) {
            return $labels[$key];
        }

        if (preg_match('/^groups\.([^\.]+)\.enabled$/', $key, $m)) {
            return sprintf(__('Gruppe: %s', 'wine-e-label'), $this->get_group_label((string) $m[1]));
        }
        if (preg_match('/^groups\.([^\.]+)\.mode$/', $key, $m)) {
            return sprintf(__('%s: Modus', 'wine-e-label'), $this->get_group_label((string) $m[1]));
        }
        if (preg_match('/^groups\.([^\.]+)\.items\.([^\.]+)\.(selected|bio|enumber)$/', $key, $m)) {
            $item_label = $this->get_catalog_item_label((string) $m[2]);
            $suffix_map = [
                'selected' => __('Auswahl', 'wine-e-label'),
                'bio' => __('Bio', 'wine-e-label'),
                'enumber' => __('E-Nummer-Anzeige', 'wine-e-label'),
            ];
            return sprintf(__('%1$s: %2$s', 'wine-e-label'), $item_label, $suffix_map[$m[3]] ?? $m[3]);
        }
        if (preg_match('/^groups\.([^\.]+)\.custom_items\.(\d+)\.(label|e|selected|enumber)$/', $key, $m)) {
            $index = ((int) $m[2]) + 1;
            $suffix_map = [
                'label' => __('Stoff oder E-Nr.', 'wine-e-label'),
                'e' => __('Interner E-Nummer-Wert', 'wine-e-label'),
                'selected' => __('Auswahl', 'wine-e-label'),
                'enumber' => __('Interne Anzeigeoption', 'wine-e-label'),
            ];
            return sprintf(__('Zusätzlicher Stoff %1$d: %2$s', 'wine-e-label'), $index, $suffix_map[$m[3]] ?? $m[3]);
        }

        return $key;
    }

    private function get_group_label(string $group_key): string
    {
        $catalog = Wine_E_Label_Manual_Builder::get_catalog();
        $lang = class_exists('Wine_E_Label_Admin_I18n') ? Wine_E_Label_Admin_I18n::get_current_language() : 'de';
        if (!isset($catalog[$group_key]['label'])) {
            return $group_key;
        }
        return Wine_E_Label_Manual_Builder::translate_catalog_label((string) $catalog[$group_key]['label'], $lang);
    }

    private function get_catalog_item_label(string $item_key): string
    {
        $catalog = Wine_E_Label_Manual_Builder::get_catalog();
        $lang = class_exists('Wine_E_Label_Admin_I18n') ? Wine_E_Label_Admin_I18n::get_current_language() : 'de';
        foreach ($catalog as $group) {
            if (!empty($group['items'][$item_key]['label'])) {
                return Wine_E_Label_Manual_Builder::translate_catalog_label((string) $group['items'][$item_key]['label'], $lang);
            }
        }
        return $item_key;
    }

    private function get_copy_source_products(int $current_product_id): array
    {
        $query = new WP_Query([
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 150,
            'post__not_in' => [$current_product_id],
            'orderby' => 'modified',
            'order' => 'DESC',
            'fields' => 'ids',
        ]);

        $items = [];
        foreach ($query->posts as $product_id) {
            $label_data = Wine_E_Label_Importer::get_label_data((int) $product_id);
            $manual = Wine_E_Label_Manual_Builder::normalize_config($label_data['manual_config'] ?? []);
            if (!Wine_E_Label_Manual_Builder::has_meaningful_input($manual) && trim((string) ($label_data['ingredients_html'] ?? '')) === '') {
                continue;
            }
            $items[] = [
                'id' => (int) $product_id,
                'label' => sprintf('%s (#%d)', get_the_title((int) $product_id), (int) $product_id),
            ];
        }
        wp_reset_postdata();

        return $items;
    }

    private function get_slug_scan_quality(string $slug): array
    {
        $slug = trim($slug);
        $length = strlen($slug);
        if ($slug === '') {
            return ['label' => __('Slug fehlt', 'wine-e-label'), 'text' => __('Für einen sauberen QR-Code muss ein Slug gesetzt sein.', 'wine-e-label'), 'badge_class' => 'err'];
        }
        if ($length <= 16) {
            return ['label' => __('QR-kompakt', 'wine-e-label'), 'text' => __('Kurzer Slug, gut lesbar und in der Regel sehr gut scanbar.', 'wine-e-label'), 'badge_class' => 'ok'];
        }
        if ($length <= 28) {
            return ['label' => __('noch gut', 'wine-e-label'), 'text' => __('Scannbar, aber ein kürzerer Slug wäre auf Etiketten sauberer.', 'wine-e-label'), 'badge_class' => 'warn'];
        }
        return ['label' => __('recht lang', 'wine-e-label'), 'text' => __('Für kleine Etiketten besser kürzen, damit der QR-Code ruhiger bleibt.', 'wine-e-label'), 'badge_class' => 'err'];
    }

    public function save_data($post_id, $post): void
    {
        if ($post->post_type !== 'product') {
            return;
        }
        if (!isset($_POST['wine_e_label_nonce']) || !wp_verify_nonce($_POST['wine_e_label_nonce'], 'wine_e_label_save')) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $existing = Wine_E_Label_Importer::get_label_data($post_id);
        $manualConfig = Wine_E_Label_Manual_Builder::sanitize_config($_POST['wine_e_label_manual'] ?? ($existing['manual_config'] ?? []));
        $displayConfig = class_exists('Wine_E_Label_Presentation')
            ? Wine_E_Label_Presentation::sanitize_config($_POST['wine_e_label_display'] ?? ($existing['display_config'] ?? []))
            : [];
        $existingSlug = (string) ($existing['slug'] ?? '');
        $didImport = false;
        $didCreate = false;
        $isImportAction = isset($_POST['wine_e_label_confirm_import']);
        $isCreateAction = isset($_POST['wine_e_label_create_label']);
        $explicitSlug = Wine_E_Label_Importer::normalize_slug((string) ($_POST['wine_e_label_slug'] ?? ''));
        $explicitWineNr = sanitize_text_field((string) ($_POST['wine_e_label_wine_nr'] ?? ''));
        $explicitTitle = sanitize_text_field((string) ($_POST['wine_e_label_title'] ?? ''));

        $data = [
            'slug' => $explicitSlug,
            'wine_nr' => $explicitWineNr !== '' ? $explicitWineNr : sanitize_text_field((string) ($existing['wine_nr'] ?? '')),
            'title' => $explicitTitle !== '' ? $explicitTitle : sanitize_text_field((string) ($existing['title'] ?? '')),
            'energy' => sanitize_text_field((string) ($_POST['wine_e_label_energy'] ?? ($existing['energy'] ?? ''))),
            'carbs' => sanitize_text_field((string) ($_POST['wine_e_label_carbs'] ?? ($existing['carbs'] ?? ''))),
            'sugar' => sanitize_text_field((string) ($_POST['wine_e_label_sugar'] ?? ($existing['sugar'] ?? ''))),
            'minor' => sanitize_text_field((string) ($_POST['wine_e_label_minor'] ?? ($existing['minor'] ?? ''))),
            'ingredients_html' => wp_kses_post((string) ($_POST['wine_e_label_ingredients_html'] ?? ($existing['ingredients_html'] ?? ''))),
            'footnote' => sanitize_text_field((string) ($_POST['wine_e_label_footnote'] ?? ($existing['footnote'] ?? ''))),
            'pretable_notice' => sanitize_text_field((string) ($_POST['wine_e_label_pretable_notice'] ?? ($existing['pretable_notice'] ?? ''))),
            'source_file_url' => (string) ($existing['source_file_url'] ?? ''),
            'source_file_path' => (string) ($existing['source_file_path'] ?? ''),
            'source_file_name' => (string) ($existing['source_file_name'] ?? ''),
            'last_import' => (string) ($existing['last_import'] ?? ''),
            'import_status' => (string) ($existing['import_status'] ?? ''),
            'import_message' => (string) ($existing['import_message'] ?? ''),
            'built_at' => (string) ($existing['built_at'] ?? ''),
            'minor_mode' => (string) ($_POST['wine_e_label_minor_mode'] ?? ($existing['minor_mode'] ?? '')),
            'fat' => sanitize_text_field((string) ($_POST['wine_e_label_fat'] ?? ($existing['fat'] ?? ''))),
            'saturates' => sanitize_text_field((string) ($_POST['wine_e_label_saturates'] ?? ($existing['saturates'] ?? ''))),
            'protein' => sanitize_text_field((string) ($_POST['wine_e_label_protein'] ?? ($existing['protein'] ?? ''))),
            'salt' => sanitize_text_field((string) ($_POST['wine_e_label_salt'] ?? ($existing['salt'] ?? ''))),
            'salt_natural' => sanitize_text_field((string) ($_POST['wine_e_label_salt_natural'] ?? ($existing['salt_natural'] ?? '0'))),
            'manual_config' => $manualConfig,
            'display_config' => $displayConfig,
        ];

        if ($isImportAction) {
            if (!empty($_FILES['wine_e_label_import_file']['name'])) {
                $imported = Wine_E_Label_Importer::import_uploaded_file($_FILES['wine_e_label_import_file'], (int) $post_id);
                if (is_wp_error($imported)) {
                    $data['import_status'] = 'error';
                    $data['import_message'] = $imported->get_error_message();
                    $data['built_at'] = '';
                } else {
                    $didImport = true;
                    $oldSlug = (string) ($data['slug'] ?? '');
                    $oldWineNr = (string) ($data['wine_nr'] ?? '');
                    $data = array_merge($data, $imported);
                    $newSuggestion = Wine_E_Label_Importer::suggest_slug((string) ($data['wine_nr'] ?? ''), '');
                    $oldSuggestion = Wine_E_Label_Importer::suggest_slug($oldWineNr, '');
                    if ($oldSlug === '' || $oldSlug === $oldSuggestion) {
                        $data['slug'] = $newSuggestion;
                    }
                    $data['built_at'] = '';
                    $this->db->delete_label_data($post_id);
                }
            } else {
                $data['import_status'] = 'error';
                $data['import_message'] = __('Bitte zuerst eine ZIP-, JSON- oder HTML-Datei auswählen.', 'wine-e-label');
                $data['built_at'] = '';
            }
        }

        if (!empty($data['title'])) {
            $data['title'] = Wine_E_Label_Importer::format_label_title($data['title']);
        }

        if ($isCreateAction) {
            $hasManualInput = Wine_E_Label_Manual_Builder::has_meaningful_input($manualConfig);
            if ($hasManualInput) {
                $manualLabel = Wine_E_Label_Manual_Builder::build_label_data($manualConfig, (int) $post_id);
                $data = array_merge($data, $manualLabel);
                if ($explicitSlug !== '') {
                    $data['slug'] = $explicitSlug;
                } elseif (($data['slug'] ?? '') === '' && !empty($data['wine_nr'])) {
                    $data['slug'] = Wine_E_Label_Importer::suggest_slug((string) $data['wine_nr'], '');
                }
                if ($explicitWineNr !== '') {
                    $data['wine_nr'] = $explicitWineNr;
                }
                if ($explicitTitle !== '') {
                    $data['title'] = $explicitTitle;
                }
            }
            if (($data['source_file_name'] ?? '') === '' && ($existing['source_file_name'] ?? '') === '' && !$hasManualInput) {
                $data['import_status'] = 'error';
                $data['import_message'] = __('Bitte zuerst eine Datei importieren oder E-Label-Daten eingeben.', 'wine-e-label');
                $data['built_at'] = '';
                $this->db->delete_label_data($post_id);
            } elseif ($hasManualInput && trim((string) ($data['ingredients_html'] ?? '')) === '') {
                $data['import_status'] = 'error';
                $data['import_message'] = __('Bitte mindestens eine Zutat auswählen.', 'wine-e-label');
                $data['built_at'] = '';
                $this->db->delete_label_data($post_id);
            } elseif ($data['slug'] === '') {
                $data['import_status'] = $data['import_status'] ?: 'error';
                $data['import_message'] = $data['import_message'] ?: __('Bitte einen Slug angeben oder den Vorschlag aus der Wein-Nr. übernehmen.', 'wine-e-label');
                $data['built_at'] = '';
                $this->db->delete_label_data($post_id);
            } elseif ($this->db->shortcode_belongs_to_other_product($data['slug'], (int) $post_id)) {
                $data['import_status'] = 'error';
                $data['import_message'] = __('Dieser Slug ist bereits vergeben.', 'wine-e-label');
                $data['built_at'] = '';
                $this->db->delete_label_data($post_id);
            } else {
                $didCreate = true;
                $data['built_at'] = current_time('mysql');
                $this->sync_db_record($post_id, $data);
            }
        }

        Wine_E_Label_Importer::save_label_data($post_id, $data);

        if (!$isCreateAction && !$didImport && $data['slug'] !== '' && $data['slug'] !== $existingSlug && !empty($existing['built_at'])) {
            // existing built label, slug edited manually via normal product update
            $this->sync_db_record($post_id, $data);
            $data['built_at'] = current_time('mysql');
            Wine_E_Label_Importer::save_label_data($post_id, $data);
        }
    }

    private function sync_db_record(int $post_id, array $data): void
    {
        $dbPayload = [
            'ingredients' => new NutritionLabelIngredientList(),
            'calories' => Wine_E_Label_Importer::extract_numeric_energy($data)['calories'],
            'kilojoules' => Wine_E_Label_Importer::extract_numeric_energy($data)['kilojoules'],
            'carbohydrates' => Wine_E_Label_Importer::extract_numeric_grams((string) $data['carbs']),
            'sugar' => Wine_E_Label_Importer::extract_numeric_grams((string) $data['sugar']),
        ];

        $this->db->save_label_data($post_id, $dbPayload);

        if (!empty($data['slug'])) {
            $this->db->upsert_shortcode($post_id, $data['slug']);
        }
    }

    private function compose_preview_url(string $baseUrl, string $slug): string
    {
        if ($baseUrl === '') {
            return __('Bitte zuerst die Basis-URL im Plugin-Admin setzen.', 'wine-e-label');
        }

        $slug = trim($slug);
        if ($slug === '') {
            return '—';
        }

        $url = Wine_E_Label_URL::compose_public_url($slug, '', true);
        if ($url) {
            return $url;
        }

        return Wine_E_Label_URL::use_external_rest_domain()
            ? trailingslashit(rtrim($baseUrl, '/') . '/' . ltrim($slug, '/'))
            : rtrim($baseUrl, '/') . '/' . ltrim($slug, '/');
    }

    private function get_page_status(int $product_id, string|false $url): array
    {
        $data = Wine_E_Label_Importer::get_label_data($product_id);
        if (($data['slug'] ?? '') !== '' && $this->db->shortcode_belongs_to_other_product((string) $data['slug'], $product_id)) {
            return ['ok' => false, 'level' => 'error', 'message' => __('Dieser Slug ist bereits vergeben.', 'wine-e-label')];
        }

        if (($data['built_at'] ?? '') === '') {
            return ['ok' => false, 'level' => 'pending', 'message' => __('Noch nicht erstellt. Erst importieren, dann E-Label und QR-Code erstellen.', 'wine-e-label')];
        }

        if (!$url) {
            return ['ok' => false, 'level' => 'error', 'message' => __('Kein gültiger Slug oder keine Basis-URL vorhanden.', 'wine-e-label')];
        }

        if (Wine_E_Label_URL::use_external_rest_domain()) {
            $remote_url = (string) ($data['remote_page_url'] ?? '');
            $ok = $remote_url !== '';
            return [
                'ok' => $ok,
                'level' => $ok ? 'ok' : 'error',
                'url' => $ok ? $remote_url : $url,
                'message' => $ok ? '' : __('Externe E-Label-Seite konnte nicht erzeugt werden.', 'wine-e-label'),
            ];
        }

        $html = Wine_E_Label_URL::render_label_html($product_id);
        $ok = $html !== '' && strlen(trim(wp_strip_all_tags($html))) > 50 && (str_contains($html, 'Brennwert') || str_contains($html, 'Nährwertangaben'));

        return [
            'ok' => $ok,
            'level' => $ok ? 'ok' : 'error',
            'url' => $url,
            'message' => $ok ? '' : __('E-Label-Seite konnte nicht mit Inhalt erzeugt werden.', 'wine-e-label'),
        ];
    }

    private function get_qr_status(int $product_id, string|false $url): array
    {
        $data = Wine_E_Label_Importer::get_label_data($product_id);
        if (($data['built_at'] ?? '') === '') {
            return ['ok' => false, 'level' => 'pending', 'message' => __('Noch kein QR-Code erzeugt.', 'wine-e-label')];
        }

        if (!$url) {
            return ['ok' => false, 'level' => 'error', 'message' => __('QR-Code kann ohne gültigen Link nicht erzeugt werden.', 'wine-e-label')];
        }

        $preview = Wine_E_Label_QR::generate_qr_code_base64($url, 'png');
        if ($preview === false) {
            return ['ok' => false, 'level' => 'error', 'message' => __('QR-Code konnte nicht erzeugt werden.', 'wine-e-label')];
        }

        return [
            'ok' => true,
            'level' => 'ok',
            'message' => '',
            'preview' => $preview,
        ];
    }

    public function enqueue_scripts($hook): void
    {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'product') {
            return;
        }

        $product_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        $data = $product_id > 0 ? Wine_E_Label_Importer::get_label_data($product_id) : [];
        $admin_lang_for_categories = class_exists('Wine_E_Label_Admin_I18n') ? Wine_E_Label_Admin_I18n::get_current_language() : 'de';
        $display_defaults = class_exists('Wine_E_Label_Presentation') ? Wine_E_Label_Presentation::product_defaults($product_id) : [];
        $display_config = class_exists('Wine_E_Label_Presentation') ? Wine_E_Label_Presentation::normalize_config($data['display_config'] ?? []) : [];
        $design_settings = class_exists('Wine_E_Label_Design') ? Wine_E_Label_Design::export_remote_settings() : [];
        $producer_labels = class_exists('Wine_E_Label_Design') ? Wine_E_Label_Design::producer_label_map() : [];

        $admin_script_path = WINE_E_LABEL_PLUGIN_DIR . 'assets/js/admin.js';
        $admin_script_version = file_exists($admin_script_path) ? (string) filemtime($admin_script_path) : WINE_E_LABEL_VERSION;

        wp_enqueue_media();
        wp_enqueue_script(
            'wine-e-label-admin',
            WINE_E_LABEL_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            $admin_script_version,
            true
        );
        wp_localize_script('wine-e-label-admin', 'wineELabelAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wine_e_label_qr_download'),
            'baseUrl' => Wine_E_Label_URL::get_preview_base_url(),
            'isExternalReceiverMode' => Wine_E_Label_URL::use_external_rest_domain(),
            'importNonce' => wp_create_nonce('wine_e_label_import_confirm'),
            'createNonce' => wp_create_nonce('wine_e_label_create_label'),
            'deleteImportNonce' => wp_create_nonce('wine_e_label_import_delete'),
            'deleteGeneratedNonce' => wp_create_nonce('wine_e_label_delete_generated'),
            'loadSourceNonce' => wp_create_nonce('wine_e_label_load_source_product'),
            'resetAllNonce' => wp_create_nonce('wine_e_label_reset_all'),
            'productId' => $product_id,
            'hasBuiltLabel' => !empty($data['built_at']),
            'defaultConfig' => Wine_E_Label_Manual_Builder::default_config(),
            'importSnapshot' => Wine_E_Label_Manual_Builder::sanitize_config(Wine_E_Label_Importer::get_label_data($product_id)['import_snapshot'] ?? Wine_E_Label_Manual_Builder::default_config()),
            'initialManualConfig' => Wine_E_Label_Importer::get_label_data($product_id)['manual_config'] ?? Wine_E_Label_Manual_Builder::default_config(),
            'defaultDisplayConfig' => class_exists('Wine_E_Label_Presentation') ? Wine_E_Label_Presentation::defaults() : [],
            'initialDisplayConfig' => $display_config,
            'productDisplayDefaults' => $display_defaults,
            'designSettings' => $design_settings,
            'producerLabels' => $producer_labels,
            'previewTexts' => Wine_E_Label_Manual_Builder::get_preview_texts(),
            'languageBundle' => Wine_E_Label_Manual_Builder::get_language_bundle(),
            'catalog' => Wine_E_Label_Manual_Builder::get_catalog(),
            'previewDefaultLang' => in_array($admin_lang_for_categories, array_keys(Wine_E_Label_URL::get_lang_names()), true) ? $admin_lang_for_categories : 'de',
            'i18n' => [
                'noFile' => __('Bitte zuerst eine ZIP-, JSON- oder HTML-Datei auswählen.', 'wine-e-label'),
                'importing' => __('Import läuft …', 'wine-e-label'),
                'importSuccess' => __('Import erfolgreich', 'wine-e-label'),
                'pendingPage' => __('Noch nicht erstellt. Erst importieren, dann E-Label und QR-Code erstellen.', 'wine-e-label'),
                'pendingQr' => __('Noch kein QR-Code erzeugt.', 'wine-e-label'),
                'ajaxError' => __('Import fehlgeschlagen.', 'wine-e-label'),
                'creating' => __('E-Label und QR-Code werden erstellt …', 'wine-e-label'),
                'createError' => __('Erstellung fehlgeschlagen.', 'wine-e-label'),
                'createSuccess' => __('E-Label-Seite erfolgreich erstellt', 'wine-e-label'),
                'qrSuccess' => __('QR-Code erfolgreich erstellt', 'wine-e-label'),
                'importDeleted' => __('Import gelöscht.', 'wine-e-label'),
                'deleteImportError' => __('Import konnte nicht gelöscht werden.', 'wine-e-label'),
                'deleteGeneratedBusy' => __('E-Label und QR-Code werden gelöscht …', 'wine-e-label'),
                'deleteGeneratedDone' => __('E-Label-Seite und QR-Code wurden gelöscht.', 'wine-e-label'),
                'deleteGeneratedError' => __('E-Label-Seite und QR-Code konnten nicht gelöscht werden.', 'wine-e-label'),
                'manualCleared' => __('Manuelle Daten wurden geleert.', 'wine-e-label'),
                'resetAll' => __('Alles wird zurückgesetzt …', 'wine-e-label'),
                'resetAllDone' => __('Alle E-Label-Daten wurden zurückgesetzt.', 'wine-e-label'),
                'copyLoaded' => __('Daten wurden aus dem ausgewählten Produkt übernommen.', 'wine-e-label'),
                'copyError' => __('Daten konnten nicht übernommen werden.', 'wine-e-label'),
                'copySlug' => __('Slug kopiert.', 'wine-e-label'),
                'copyLink' => __('Link kopiert.', 'wine-e-label'),
                'lastImportTitle' => __('Letzter Import', 'wine-e-label'),
                'pageTitle' => __('E-Label-Seite', 'wine-e-label'),
                'qrTitle' => __('QR-Code', 'wine-e-label'),
                'validationError' => __('Bitte zuerst alle Pflichtfelder füllen.', 'wine-e-label'),
                'baseUrlMissing' => __('Bitte zuerst die Basis-URL im Plugin-Admin setzen.', 'wine-e-label'),
                'previewEmpty' => '—',
                'viewQr' => __('QR-Code ansehen', 'wine-e-label'),
                'hideQr' => __('QR-Code verbergen', 'wine-e-label'),
                'stateOpen' => __('offen', 'wine-e-label'),
                'stateLoaded' => __('geladen', 'wine-e-label'),
                'stateAvailable' => __('vorhanden', 'wine-e-label'),
                'stateEmpty' => __('leer', 'wine-e-label'),
                'stateCreated' => __('erstellt', 'wine-e-label'),
                'stateComplete' => __('vollständig', 'wine-e-label'),
                'stateMissing' => __('fehlt', 'wine-e-label'),
                'stateError' => __('Fehler', 'wine-e-label'),
                'slugMissingLabel' => __('Slug fehlt', 'wine-e-label'),
                'slugMissingText' => __('Für einen sauberen QR-Code muss ein Slug gesetzt sein.', 'wine-e-label'),
                'qrCompactLabel' => __('QR-kompakt', 'wine-e-label'),
                'qrCompactText' => __('Kurzer Slug, gut lesbar und in der Regel sehr gut scanbar.', 'wine-e-label'),
                'slugOkLabel' => __('noch gut', 'wine-e-label'),
                'slugOkText' => __('Scannbar, aber ein kürzerer Slug wäre auf Etiketten sauberer.', 'wine-e-label'),
                'slugLongLabel' => __('recht lang', 'wine-e-label'),
                'slugLongText' => __('Für kleine Etiketten besser kürzen, damit der QR-Code ruhiger bleibt.', 'wine-e-label'),
                'sourceImportManual' => __('Import + manuelle Änderungen', 'wine-e-label'),
                'sourceManual' => __('Nur manuelle Eingabe', 'wine-e-label'),
                'sourceImportOnly' => __('WIPZN-Import', 'wine-e-label'),
                'sourceNone' => __('Noch keine Daten', 'wine-e-label'),
                'lastImportPrefix' => __('Letzter Import: %s', 'wine-e-label'),
                'currentFormState' => __('Aktueller Formularstand', 'wine-e-label'),
                'sourceFilePrefix' => __('Quelldatei: %s', 'wine-e-label'),
                'manualChangedPrefix' => __('Manuell geändert: %d Felder', 'wine-e-label'),
                'manualAddedPrefix' => __('Manuell ergänzt: %d Felder', 'wine-e-label'),
                'manualClearedPrefix' => __('Manuell geleert: %d Felder', 'wine-e-label'),
                'noDifferences' => __('Keine Abweichungen zwischen Import und aktueller Eingabe.', 'wine-e-label'),
                'loadingData' => __('Lade Daten …', 'wine-e-label'),
                'applyData' => __('Daten übernehmen', 'wine-e-label'),
                'deleteImportButton' => __('Import löschen', 'wine-e-label'),
                'createButton' => __('E-Label und QR-Code erstellen', 'wine-e-label'),
                'updateButton' => __('E-Label und QR-Code aktualisieren', 'wine-e-label'),
                'generateQr' => __('QR-Code wird erzeugt …', 'wine-e-label'),
                'noFileChosen' => __('Keine Datei ausgewählt', 'wine-e-label'),
                'openLink' => __('Link öffnen', 'wine-e-label'),
                'inlinePageSuccess' => __('E-Label-Seite erfolgreich erstellt', 'wine-e-label'),
                'downloadLabel' => __('Download', 'wine-e-label'),
                'importConfirmButton' => __('Import bestätigen', 'wine-e-label'),
                'deleteImportBusy' => __('Import wird gelöscht …', 'wine-e-label'),
                'resetAllButton' => __('Alles zurücksetzen', 'wine-e-label'),
                'deleteGeneratedButton' => __('E-Label und QR-Code löschen', 'wine-e-label'),
                'addSubstance' => __('Zusätzlichen Stoff hinzufügen', 'wine-e-label'),
                'removeLabel' => __('Entfernen', 'wine-e-label'),
                'emptyCustomSubstances' => __('Noch keine zusätzlichen Stoffe angelegt.', 'wine-e-label'),
                'customNameLabel' => __('Stoff oder E-Nr.', 'wine-e-label'),
            ],
    'customImagePickerTitle' => __('Produktbild wählen', 'wine-e-label'),
            'customImagePickerButton' => __('Dieses Bild verwenden', 'wine-e-label'),
            'customImageDefaultHint' => __('Aktuell wird das WooCommerce-Produktbild verwendet.', 'wine-e-label'),
            'customImageOverrideHint' => __('Aktuell ist ein eigenes Produktbild hinterlegt.', 'wine-e-label'),
    'customImageMissing' => __('Kein Produktbild verfügbar', 'wine-e-label'),
            'fieldLabels' => $this->get_manual_field_labels(),
        ]);
    }
}
