<?php

/**
 * Copyright (c) 2026 - Johannes Reith - https://reithwein.com
 * Based in part on earlier GPL-licensed project work originating from version 1.0 by Markus Hammer.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Extended admin class with settings backend functionality
 */

class Wine_E_Label_Admin_Extended
{

  private $db;

  public function __construct(bool $register_hooks = true)
  {
    // Only proceed if WordPress functions are available
    if (!function_exists('wp_create_nonce') || !function_exists('add_action')) {
      return;
    }

    $this->db = new Wine_E_Label_DB_Extended();

    if (!$register_hooks) {
      return;
    }

    // Register AJAX handlers
    add_action('wp_ajax_wine_e_label_search', array($this, 'ajax_search'));
    add_action('wp_ajax_wine_e_label_delete', array($this, 'ajax_delete'));
    add_action('wp_ajax_flush_rewrite_rules', array($this, 'ajax_flush_rewrite_rules'));
    add_action('wp_ajax_wine_e_label_qr_download', array($this, 'ajax_download_qr'));
    add_action('wp_ajax_wine_e_label_import_confirm', array($this, 'ajax_confirm_import'));
    add_action('wp_ajax_wine_e_label_create_label', array($this, 'ajax_create_label'));
    add_action('wp_ajax_wine_e_label_import_delete', array($this, 'ajax_delete_import'));
    add_action('wp_ajax_wine_e_label_load_source_product', array($this, 'ajax_load_source_product'));
    add_action('wp_ajax_wine_e_label_delete_generated', array($this, 'ajax_delete_generated'));
    add_action('wp_ajax_wine_e_label_reset_all', array($this, 'ajax_reset_all'));
    add_action('wp_ajax_wine_e_label_test_rest_connection', array($this, 'ajax_test_rest_connection'));

    // Register admin menu pages
    add_action('admin_menu', array($this, 'register_admin_menu_pages'));

    // Register settings
    add_action('admin_init', array($this, 'register_settings'));

    // CSV Download
    add_action('admin_init', array($this, 'elabel_export_csv'));
  }

  public function register_admin_menu_pages()
  {
    // Add top-level menu with position just below Settings
    add_menu_page(
      __('Wein E-Label Einstellungen', 'wine-e-label'),
      __('Wein E-Label', 'wine-e-label'),
      'manage_options',
      WINE_E_LABEL_ADMIN_PAGE_MAIN,
      array($this, 'render_settings_page'),
      'dashicons-food',
      80
    );

    add_submenu_page(
      WINE_E_LABEL_ADMIN_PAGE_MAIN,
      __('Wein E-Label Einstellungen', 'wine-e-label'),
      __('Einstellungen', 'wine-e-label'),
      'manage_options',
      WINE_E_LABEL_ADMIN_PAGE_MAIN,
      array($this, 'render_settings_page')
    );

    add_submenu_page(
      WINE_E_LABEL_ADMIN_PAGE_MAIN,
      __('Design anpassen', 'wine-e-label'),
      __('Design anpassen', 'wine-e-label'),
      'manage_options',
      WINE_E_LABEL_ADMIN_PAGE_DESIGN,
      array($this, 'render_design_page')
    );

    add_submenu_page(
      WINE_E_LABEL_ADMIN_PAGE_MAIN,
      __('E-Labels', 'wine-e-label'),
      __('E-Labels', 'wine-e-label'),
      'manage_options',
      WINE_E_LABEL_ADMIN_PAGE_DB,
      array($this, 'render_db_management_page')
    );
  }

  public function register_settings()
  {
    register_setting('wine_e_label_group', 'qr_size', array(
      'type' => 'string',
      'default' => '500x500',
      'sanitize_callback' => array($this, 'sanitize_qr_size')
    ));

    register_setting('wine_e_label_group', 'qr_format', array(
      'type'              => 'string',
      'default'           => 'png',
      'sanitize_callback' => fn($v) => in_array($v, ['png', 'svg'], true) ? $v : 'png',
    ));

    register_setting('wine_e_label_group', 'qr_error_correction', array(
      'type'              => 'string',
      'default'           => 'low',
      'sanitize_callback' => fn($v) => in_array($v, ['low', 'medium', 'quartile', 'high'], true) ? $v : 'low',
    ));

    register_setting('wine_e_label_group', 'wine_e_label_admin_language', array(
      'type'              => 'string',
      'default'           => 'auto',
      'sanitize_callback' => fn($v) => in_array($v, ['auto', 'de', 'en', 'fr', 'it'], true) ? $v : 'auto',
    ));

    register_setting('wine_e_label_group', 'wine_e_label_rest_enabled', array(
      'type'              => 'string',
      'default'           => 'no',
      'sanitize_callback' => fn($v) => $v === 'yes' ? 'yes' : 'no',
    ));

    register_setting('wine_e_label_group', 'wine_e_label_rest_base_url', array(
      'type'              => 'string',
      'default'           => '',
      'sanitize_callback' => array(__CLASS__, 'normalize_rest_base_url'),
    ));

    register_setting('wine_e_label_group', 'wine_e_label_rest_username', array(
      'type'              => 'string',
      'default'           => '',
      'sanitize_callback' => 'sanitize_user',
    ));

    register_setting('wine_e_label_group', 'wine_e_label_rest_app_password', array(
      'type'              => 'string',
      'default'           => '',
      'sanitize_callback' => array(__CLASS__, 'sanitize_rest_app_password'),
    ));
  }

  public static function normalize_rest_base_url($input)
  {
    $url = is_string($input) ? trim($input) : '';
    if ($url === '') {
      return '';
    }
    if (!preg_match('#^https?://#i', $url)) {
      $url = 'https://' . $url;
    }
    $url = preg_replace('#/wp-json(?:/.*)?$#i', '', $url);
    $url = preg_replace('#/wp/v2(?:/.*)?$#i', '', $url);
    $url = preg_replace('#/pages(?:/.*)?$#i', '', $url);
    return untrailingslashit(esc_url_raw($url));
  }

  public static function sanitize_rest_app_password($input)
  {
    if (!is_string($input)) {
      return '';
    }
    return trim(preg_replace('/\s+/', ' ', wp_unslash($input)));
  }

  public function sanitize_qr_size($input)
  {
    $allowed = array('300x300', '500x500', '800x800');
    return in_array($input, $allowed) ? $input : '500x500';
  }

  public function ajax_search()
  {
    check_ajax_referer('wine_e_label_search');
    if (!current_user_can('manage_options')) {
      wp_die(__('Nicht autorisiert', 'wine-e-label'));
    }

    $search = sanitize_text_field($_POST['search']);
    $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
    $per_page = 50;

    $entries = $this->db->search_entries($search, $per_page, $page);

    wp_send_json(array(
      'entries' => $entries,
      'has_more' => count($entries) === $per_page,
      'current_page' => $page
    ));
  }

  public function ajax_delete()
  {
    check_ajax_referer('wine_e_label_delete', '_wpnonce');
    if (!current_user_can('manage_options')) {
      wp_die(__('Nicht autorisiert', 'wine-e-label'));
    }

    $product_ids = array_map('absint', $_POST['product_ids']);

    if (empty($product_ids)) {
      wp_send_json_error(__('Bitte mindestens einen Eintrag auswählen.', 'wine-e-label'));
      return;
    }

    $deleted_count = 0;
    $remote_errors = [];

    foreach ($product_ids as $product_id) {
      if (get_post($product_id)) {
        $reset_result = $this->purge_label_state($product_id, true);
        if (is_wp_error($reset_result)) {
          $remote_errors[] = sprintf(
            /* translators: 1: product id, 2: error message */
            __('Produkt %1$d: %2$s', 'wine-e-label'),
            $product_id,
            $reset_result->get_error_message()
          );
        }
        $deleted_count++;
      }
    }

    $message = sprintf(_n(
      'Erfolgreich %d E-Label-Eintrag gelöscht',
      'Erfolgreich %d E-Label-Einträge gelöscht',
      $deleted_count,
      'wine-e-label'
    ), $deleted_count);

    if (!empty($remote_errors)) {
      $message .= ' ' . __('Hinweis: Auf der externen Receiver-Seite konnten nicht alle Einträge gelöscht werden.', 'wine-e-label');
    }

    wp_send_json(array(
      'success' => true,
      'deleted_count' => $deleted_count,
      'message' => $message,
      'remote_errors' => $remote_errors,
    ));
  }


  public function ajax_confirm_import()
  {
    check_ajax_referer('wine_e_label_import_confirm', 'nonce');

    $product_id = absint($_POST['product_id'] ?? 0);
    if (!$product_id) {
      wp_send_json_error(__('Ungültige Produkt-ID.', 'wine-e-label'));
    }

    if (!current_user_can('edit_post', $product_id)) {
      wp_die(__('Nicht autorisiert', 'wine-e-label'));
    }

    if (empty($_FILES['import_file'])) {
      wp_send_json_error(__('Bitte zuerst eine ZIP-, JSON- oder HTML-Datei auswählen.', 'wine-e-label'));
    }

    $existing = Wine_E_Label_Importer::get_label_data($product_id);
    $imported = Wine_E_Label_Importer::import_uploaded_file($_FILES['import_file'], $product_id);
    if (is_wp_error($imported)) {
      $existing['import_status'] = 'error';
      $existing['import_message'] = $imported->get_error_message();
      $existing['built_at'] = '';
      Wine_E_Label_Importer::save_label_data($product_id, $existing);
      $this->db->delete_label_data($product_id);
      wp_send_json_error($imported->get_error_message());
    }

    $data = array_merge($existing, $imported);
    $data['slug'] = Wine_E_Label_Importer::suggest_slug((string) ($data['wine_nr'] ?? ''), '');
    if (!empty($data['title'])) {
      $data['title'] = Wine_E_Label_Importer::format_label_title((string) $data['title']);
    }

    $data['built_at'] = '';
    Wine_E_Label_Importer::save_label_data($product_id, $data);
    $this->db->delete_label_data($product_id);

    wp_send_json_success(array(
      'wine_nr' => (string) ($data['wine_nr'] ?? ''),
      'slug' => (string) ($data['slug'] ?? ''),
      'title' => (string) ($data['title'] ?? ''),
      'manual_config' => $data['manual_config'] ?? array(),
      'import_snapshot' => $data['import_snapshot'] ?? Wine_E_Label_Manual_Builder::default_config(),
      'energy' => (string) ($data['energy'] ?? ''),
      'carbs' => (string) ($data['carbs'] ?? ''),
      'sugar' => (string) ($data['sugar'] ?? ''),
      'minor' => (string) ($data['minor'] ?? ''),
      'ingredients_html' => (string) ($data['ingredients_html'] ?? ''),
      'footnote' => (string) ($data['footnote'] ?? ''),
      'pretable_notice' => (string) ($data['pretable_notice'] ?? ''),
      'minor_mode' => (string) ($data['minor_mode'] ?? ''),
      'fat' => (string) ($data['fat'] ?? ''),
      'saturates' => (string) ($data['saturates'] ?? ''),
      'protein' => (string) ($data['protein'] ?? ''),
      'salt' => (string) ($data['salt'] ?? ''),
      'salt_natural' => (string) ($data['salt_natural'] ?? '0'),
      'source_file_name' => (string) ($data['source_file_name'] ?? ''),
      'import_message' => (string) ($data['import_message'] ?? __('Import erfolgreich', 'wine-e-label')),
      'last_import' => (string) ($data['last_import'] ?? ''),
      'base_url' => Wine_E_Label_URL::get_base_url(),
      'manual_config' => $data['manual_config'] ?? Wine_E_Label_Manual_Builder::default_config(),
    ));
  }


  public function ajax_create_label()
  {
    check_ajax_referer('wine_e_label_create_label', 'nonce');

    $product_id = absint($_POST['product_id'] ?? 0);
    if (!$product_id) {
      wp_send_json_error(__('Ungültige Produkt-ID.', 'wine-e-label'));
    }

    if (!current_user_can('edit_post', $product_id)) {
      wp_die(__('Nicht autorisiert', 'wine-e-label'));
    }

    $existing = Wine_E_Label_Importer::get_label_data($product_id);
    $display_config = class_exists('Wine_E_Label_Presentation')
      ? Wine_E_Label_Presentation::sanitize_config($_POST['wine_e_label_display'] ?? ($existing['display_config'] ?? []))
      : [];
    $previous_slug = (string) ($existing['slug'] ?? '');
    $manual_config = Wine_E_Label_Manual_Builder::sanitize_config($_POST['manual_config'] ?? ($_POST['wine_e_label_manual'] ?? ($existing['manual_config'] ?? [])));
    $manual_requested = array_key_exists('manual_mode', $_POST) || array_key_exists('manual_config', $_POST) || array_key_exists('wine_e_label_manual', $_POST);
    $lang_code = isset($_POST['lang_code']) ? sanitize_key((string) $_POST['lang_code']) : '';
    $manual_has_input = Wine_E_Label_Manual_Builder::has_meaningful_input($manual_config);
    $explicit_slug = Wine_E_Label_Importer::normalize_slug((string) ($_POST['slug'] ?? ''));
    $slug = $explicit_slug !== '' ? $explicit_slug : Wine_E_Label_Importer::normalize_slug((string) ($existing['slug'] ?? ''));
    $explicit_wine_nr = sanitize_text_field((string) ($_POST['wine_nr'] ?? ''));
    $wine_nr = $explicit_wine_nr !== '' ? $explicit_wine_nr : sanitize_text_field((string) ($existing['wine_nr'] ?? ''));
    $explicit_title = sanitize_text_field((string) ($_POST['title'] ?? ''));
    $title = $explicit_title !== '' ? $explicit_title : sanitize_text_field((string) ($existing['title'] ?? ''));
    $energy = sanitize_text_field((string) ($_POST['energy'] ?? ($existing['energy'] ?? '')));
    $carbs = sanitize_text_field((string) ($_POST['carbs'] ?? ($existing['carbs'] ?? '')));
    $sugar = sanitize_text_field((string) ($_POST['sugar'] ?? ($existing['sugar'] ?? '')));
    $minor = sanitize_text_field((string) ($_POST['minor'] ?? ($existing['minor'] ?? '')));
    $ingredients_html = wp_kses_post((string) ($_POST['ingredients_html'] ?? ($existing['ingredients_html'] ?? '')));
    $footnote = sanitize_text_field((string) ($_POST['footnote'] ?? ($existing['footnote'] ?? '')));
    $pretable_notice = sanitize_text_field((string) ($_POST['pretable_notice'] ?? ($existing['pretable_notice'] ?? '')));

    $data = array_merge($existing, [
      'slug' => $slug,
      'wine_nr' => $wine_nr,
      'title' => $title,
      'energy' => $energy,
      'carbs' => $carbs,
      'sugar' => $sugar,
      'minor' => $minor,
      'ingredients_html' => $ingredients_html,
      'footnote' => $footnote,
      'pretable_notice' => $pretable_notice,
      'manual_config' => $manual_config,
      'display_config' => $display_config,
    ]);

    if ($manual_requested && $manual_has_input) {
      $manual_label = Wine_E_Label_Manual_Builder::build_label_data($manual_config, $product_id);
      $data = array_merge($data, $manual_label);
      if ($explicit_slug !== '') {
        $data['slug'] = $explicit_slug;
      } elseif (($data['slug'] ?? '') === '' && !empty($data['wine_nr'])) {
        $data['slug'] = Wine_E_Label_Importer::suggest_slug((string) $data['wine_nr'], '');
      }
      if ($explicit_wine_nr !== '') {
        $data['wine_nr'] = $explicit_wine_nr;
      }
      if ($explicit_title !== '') {
        $data['title'] = $explicit_title;
      }
      if (trim((string) ($data['ingredients_html'] ?? '')) === '') {
        wp_send_json_error(__('Bitte mindestens eine Zutat auswählen.', 'wine-e-label'));
      }
    }

    if (($data['source_file_name'] ?? '') === '' && !$manual_has_input && trim((string) ($data['ingredients_html'] ?? '')) === '') {
      wp_send_json_error(__('Bitte zuerst eine Datei importieren oder E-Label-Daten eingeben.', 'wine-e-label'));
    }

    if ($data['slug'] === '') {
      wp_send_json_error(__('Bitte zuerst einen Slug angeben oder aus der Wein-Nr. übernehmen.', 'wine-e-label'));
    }

    if ($this->db->shortcode_belongs_to_other_product($data['slug'], $product_id)) {
      wp_send_json_error(__('Dieser Slug ist bereits vergeben.', 'wine-e-label'));
    }

    if (!empty($data['title'])) {
      $data['title'] = Wine_E_Label_Importer::format_label_title($data['title']);
    }

    $dbPayload = [
      'ingredients' => new NutritionLabelIngredientList(),
      'calories' => Wine_E_Label_Importer::extract_numeric_energy($data)['calories'],
      'kilojoules' => Wine_E_Label_Importer::extract_numeric_energy($data)['kilojoules'],
      'carbohydrates' => Wine_E_Label_Importer::extract_numeric_grams((string) $data['carbs']),
      'sugar' => Wine_E_Label_Importer::extract_numeric_grams((string) $data['sugar']),
    ];

    $this->db->save_label_data($product_id, $dbPayload);
    $this->db->upsert_shortcode($product_id, $data['slug']);

    $data['built_at'] = current_time('mysql');
    $data['import_status'] = $data['import_status'] ?: 'success';

    if (Wine_E_Label_URL::use_external_rest_domain()) {
      $remote_result = $this->publish_remote_e_label($product_id, $data);
      if (is_wp_error($remote_result)) {
        wp_send_json_error($remote_result->get_error_message());
      }
      $data['remote_page_id'] = (string) ($remote_result['id'] ?? '');
      $data['remote_page_url'] = (string) ($remote_result['url'] ?? '');
      if ($previous_slug !== '' && $previous_slug !== (string) $data['slug']) {
        $this->delete_remote_e_label($previous_slug);
      }
    } else {
      $data['remote_page_id'] = '';
      $data['remote_page_url'] = '';
    }

    Wine_E_Label_Importer::save_label_data($product_id, $data);

    $url = Wine_E_Label_URL::get_short_url($product_id, $lang_code);
    if (!$url) {
      wp_send_json_error(__('Link konnte nicht erzeugt werden.', 'wine-e-label'));
    }

    $preview = Wine_E_Label_QR::generate_qr_code_base64($url, 'png');
    if ($preview === false) {
      wp_send_json_error(__('QR-Code konnte nicht erzeugt werden.', 'wine-e-label'));
    }

    wp_send_json_success([
      'url' => $url,
      'built_at' => (string) $data['built_at'],
      'qr_preview' => $preview,
      'slug' => (string) $data['slug'],
      'title' => (string) ($data['title'] ?? ''),
      'energy' => (string) ($data['energy'] ?? ''),
      'carbs' => (string) ($data['carbs'] ?? ''),
      'sugar' => (string) ($data['sugar'] ?? ''),
      'minor' => (string) ($data['minor'] ?? ''),
      'minor_mode' => (string) ($data['minor_mode'] ?? ''),
      'fat' => (string) ($data['fat'] ?? ''),
      'saturates' => (string) ($data['saturates'] ?? ''),
      'protein' => (string) ($data['protein'] ?? ''),
      'salt' => (string) ($data['salt'] ?? ''),
      'salt_natural' => (string) ($data['salt_natural'] ?? '0'),
      'ingredients_html' => (string) ($data['ingredients_html'] ?? ''),
      'footnote' => (string) ($data['footnote'] ?? ''),
      'pretable_notice' => (string) ($data['pretable_notice'] ?? ''),
      'wine_nr' => (string) ($data['wine_nr'] ?? ''),
      'manual_config' => $data['manual_config'] ?? Wine_E_Label_Manual_Builder::default_config(),
      'display_config' => $data['display_config'] ?? [],
    ]);
  }

  public function ajax_delete_import()
  {
    check_ajax_referer('wine_e_label_import_delete', 'nonce');

    $product_id = absint($_POST['product_id'] ?? 0);
    if (!$product_id) {
      wp_send_json_error(__('Ungültige Produkt-ID.', 'wine-e-label'));
    }

    if (!current_user_can('edit_post', $product_id)) {
      wp_die(__('Nicht autorisiert', 'wine-e-label'));
    }

    $existing = Wine_E_Label_Importer::get_label_data($product_id);
    $source_path = (string) ($existing['source_file_path'] ?? '');
    if ($source_path !== '' && file_exists($source_path)) {
      @unlink($source_path);
    }

    Wine_E_Label_Importer::clear_import_state($product_id);

    wp_send_json_success([
      'message' => __('Import gelöscht.', 'wine-e-label'),
    ]);
  }



  public function ajax_load_source_product()
  {
    check_ajax_referer('wine_e_label_load_source_product', 'nonce');

    $product_id = absint($_POST['product_id'] ?? 0);
    if (!$product_id) {
      wp_send_json_error(__('Ungültige Produkt-ID.', 'wine-e-label'));
    }

    if (!current_user_can('edit_post', $product_id)) {
      wp_die(__('Nicht autorisiert', 'wine-e-label'));
    }

    $data = Wine_E_Label_Importer::get_label_data($product_id);
    $manual = Wine_E_Label_Manual_Builder::normalize_config($data['manual_config'] ?? []);
    $has_manual = Wine_E_Label_Manual_Builder::has_meaningful_input($manual);

    if (!$has_manual && !empty($data['import_snapshot'])) {
      $manual = Wine_E_Label_Manual_Builder::normalize_config($data['import_snapshot']);
      $has_manual = Wine_E_Label_Manual_Builder::has_meaningful_input($manual);
    }

    if (!$has_manual) {
      wp_send_json_error(__('Für dieses Produkt sind keine verwertbaren E-Label-Daten vorhanden.', 'wine-e-label'));
    }

    wp_send_json_success([
      'product_id' => $product_id,
      'product_title' => get_the_title($product_id),
      'manual_config' => $manual,
      'display_config' => $data['display_config'] ?? [],
      'label_data' => [
        'title' => (string) ($data['title'] ?? ''),
        'wine_nr' => (string) ($data['wine_nr'] ?? ''),
        'energy' => (string) ($data['energy'] ?? ''),
        'carbs' => (string) ($data['carbs'] ?? ''),
        'sugar' => (string) ($data['sugar'] ?? ''),
        'minor' => (string) ($data['minor'] ?? ''),
        'minor_mode' => (string) ($data['minor_mode'] ?? ''),
        'fat' => (string) ($data['fat'] ?? ''),
        'saturates' => (string) ($data['saturates'] ?? ''),
        'protein' => (string) ($data['protein'] ?? ''),
        'salt' => (string) ($data['salt'] ?? ''),
        'salt_natural' => (string) ($data['salt_natural'] ?? '0'),
        'ingredients_html' => (string) ($data['ingredients_html'] ?? ''),
        'footnote' => (string) ($data['footnote'] ?? ''),
        'pretable_notice' => (string) ($data['pretable_notice'] ?? ''),
        'display_config' => $data['display_config'] ?? [],
      ],
    ]);
  }


  public function ajax_delete_generated()
  {
    check_ajax_referer('wine_e_label_delete_generated', 'nonce');

    $product_id = absint($_POST['product_id'] ?? 0);
    if (!$product_id) {
      wp_send_json_error(__('Ungültige Produkt-ID.', 'wine-e-label'));
    }

    if (!current_user_can('edit_post', $product_id)) {
      wp_die(__('Nicht autorisiert', 'wine-e-label'));
    }

    $result = $this->purge_generated_output($product_id, true);

    $message = __('E-Label-Seite und QR-Code wurden gelöscht.', 'wine-e-label');
    if (is_wp_error($result)) {
      $message .= ' ' . sprintf(__('Hinweis zur externen Receiver-Seite: %s', 'wine-e-label'), $result->get_error_message());
    }

    wp_send_json_success([
      'message' => $message,
    ]);
  }

  public function ajax_reset_all()
  {
    check_ajax_referer('wine_e_label_reset_all', 'nonce');

    $product_id = absint($_POST['product_id'] ?? 0);
    if (!$product_id) {
      wp_send_json_error(__('Ungültige Produkt-ID.', 'wine-e-label'));
    }

    if (!current_user_can('edit_post', $product_id)) {
      wp_die(__('Nicht autorisiert', 'wine-e-label'));
    }

    $reset_result = $this->purge_label_state($product_id, true);

    $message = __('Import, manuelle Daten und erzeugtes E-Label wurden zurückgesetzt.', 'wine-e-label');
    if (is_wp_error($reset_result)) {
      $message .= ' ' . sprintf(__('Hinweis zur externen Receiver-Seite: %s', 'wine-e-label'), $reset_result->get_error_message());
    }

    wp_send_json_success([
      'message' => $message,
      'manual_config' => Wine_E_Label_Manual_Builder::default_config(),
      'display_config' => class_exists('Wine_E_Label_Presentation') ? Wine_E_Label_Presentation::defaults() : [],
    ]);
  }

  public function ajax_download_qr()
  {
    check_ajax_referer('wine_e_label_qr_download', 'nonce');

    $product_id = absint($_POST['product_id']);
    $lang_code = isset($_POST['lang_code']) ? sanitize_key((string) $_POST['lang_code']) : '';
    $target_kind = isset($_POST['target_kind']) ? sanitize_key((string) $_POST['target_kind']) : '';
    $filename_stub = isset($_POST['filename_stub']) ? sanitize_file_name((string) $_POST['filename_stub']) : '';
    if (!$product_id) {
      wp_send_json_error(__('Ungültige Produkt-ID', 'wine-e-label'));
    }

    if (!current_user_can('edit_post', $product_id)) {
      wp_die(__('Nicht autorisiert', 'wine-e-label'));
    }

    $short_url = $target_kind !== ''
      ? Wine_E_Label_URL::get_label_target_url($product_id, $target_kind, $lang_code)
      : Wine_E_Label_URL::get_short_url($product_id, $lang_code);
    if (!$short_url) {
      wp_send_json_error(__('Für dieses Produkt konnte keine E-Label-URL erzeugt werden.', 'wine-e-label'));
    }

    $format   = get_option('qr_format', 'png');
    $data_uri = Wine_E_Label_QR::generate_qr_code_base64($short_url, $format);
    if ($data_uri === false) {
      wp_send_json_error(__('QR-Code konnte nicht erzeugt werden.', 'wine-e-label'));
    }

    $product      = get_post($product_id);
    $product_name = $filename_stub !== '' ? $filename_stub : sanitize_file_name($product->post_title);
    $filename     = $product_name . ($lang_code ? '-' . $lang_code : '') . '-nutrition-qr.' . $format;

    wp_send_json_success(array(
      'url'      => $data_uri,
      'filename' => $filename,
    ));
  }

  public function elabel_export_csv()
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('Du hast keine Berechtigung für den CSV-Export.', 'wine-e-label'));
    }

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {

      if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'wine_e_label_export')) {
        wp_die(__('Invalid nonce', 'wine-e-label'));
      }

      $db = new Wine_E_Label_DB_Extended();
      $entries = $db->get_entries_for_export();

      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename="wine-e-label.csv"');
      $output = fopen('php://output', 'w');

      // Headers
      fputcsv($output, ['Product ID', 'Product Title', 'Short Code', 'Prefix', 'Calories', 'Kilojoules', 'Carbs', 'Sugar']);

      foreach ($entries as $entry) {
        fputcsv($output, [
          $entry->product_id,
          ltrim(get_the_title($entry->product_id), '=+-@'),
          $entry->short_code,
          $entry->url_prefix,
          $entry->calories,
          $entry->kilojoules,
          $entry->carbohydrates,
          $entry->sugar
        ]);
      }

      fclose($output);
      exit;
    }
  }


  public function ajax_test_rest_connection()
  {
    check_ajax_referer('wine_e_label_test_rest_connection', '_wpnonce');
    if (!current_user_can('manage_options')) {
      wp_send_json_error(array('message' => __('Nicht autorisiert', 'wine-e-label')), 403);
    }

    $base_url = self::normalize_rest_base_url($_POST['base_url'] ?? '');
    $username = sanitize_user($_POST['username'] ?? '', true);
    $app_password = self::sanitize_rest_app_password($_POST['app_password'] ?? '');
    if ($app_password === '') {
      $app_password = (string) get_option('wine_e_label_rest_app_password', '');
    }

    if ($base_url === '') {
      wp_send_json_error(array('message' => __('Bitte zuerst eine gültige REST API Ziel-URL eingeben.', 'wine-e-label')));
    }

    $creds = [
      'base_url' => $base_url,
      'username' => $username,
      'app_password' => $app_password,
    ];

    $rest_root = untrailingslashit($base_url) . '/wp-json/';
    $response = wp_remote_get($rest_root, array(
      'timeout'   => 12,
      'sslverify' => true,
      'headers'   => array('Accept' => 'application/json'),
    ));

    if (is_wp_error($response)) {
      wp_send_json_error(array('message' => sprintf(__('Verbindung fehlgeschlagen: %s', 'wine-e-label'), $response->get_error_message())));
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
      wp_send_json_error(array('message' => sprintf(__('REST API antwortet nicht wie erwartet (HTTP %d).', 'wine-e-label'), $code)));
    }

    $lines = [
      ['text' => __('REST API erreichbar.', 'wine-e-label'), 'type' => 'success'],
    ];

    $auth_ok = false;
    $auth_headers = array('Accept' => 'application/json');

    if ($username !== '' && $app_password !== '') {
      $auth_headers = $this->get_rest_auth_headers($creds);
      $auth_response = wp_remote_get(untrailingslashit($base_url) . '/wp-json/wp/v2/users/me', array(
        'timeout'   => 12,
        'sslverify' => true,
        'headers'   => $auth_headers,
      ));

      if (is_wp_error($auth_response)) {
        $lines[] = ['text' => sprintf(__('Authentifizierung konnte nicht geprüft werden: %s', 'wine-e-label'), $auth_response->get_error_message()), 'type' => 'error'];
      } else {
        $auth_code = (int) wp_remote_retrieve_response_code($auth_response);
        if ($auth_code >= 200 && $auth_code < 300) {
          $auth_ok = true;
          $lines[] = ['text' => __('Authentifizierung erfolgreich geprüft.', 'wine-e-label'), 'type' => 'success'];
        } else {
          $lines[] = ['text' => sprintf(__('Authentifizierung fehlgeschlagen (HTTP %d).', 'wine-e-label'), $auth_code), 'type' => 'error'];
        }
      }
    }

    $discovery = $this->discover_receiver_api($creds, true);
    if (is_wp_error($discovery)) {
      $error_code = $discovery->get_error_code();
      if ($error_code === 'receiver_forbidden') {
        $lines[] = ['text' => $auth_ok
          ? __('Receiver-Endpunkt antwortet, aber der API-Benutzer hat keine ausreichenden Rechte. Gib dem Benutzer auf der Zielseite mindestens Editor-Rechte.', 'wine-e-label')
          : __('Receiver-Endpunkt vorhanden, aber ohne gültige Authentifizierung nicht nutzbar.', 'wine-e-label'), 'type' => 'error'];
      } elseif ($error_code === 'receiver_request_failed') {
        $lines[] = ['text' => sprintf(__('Receiver-Endpunkt konnte nicht geprüft werden: %s', 'wine-e-label'), $discovery->get_error_message()), 'type' => 'error'];
      } else {
        $lines[] = ['text' => __('Receiver-Endpunkt nicht gefunden. Ist das Plugin Reith E-Label Receiver auf der Zielseite aktiv?', 'wine-e-label'), 'type' => 'error'];
      }
    } else {
      $source = (string) ($discovery['source'] ?? '');
      if ($source === 'info') {
        $lines[] = ['text' => __('Receiver-Endpunkt gefunden (API-Discovery).', 'wine-e-label'), 'type' => 'success'];
      } elseif ($source === 'index') {
        $lines[] = ['text' => __('Receiver-Endpunkt gefunden (REST-Index erkannt).', 'wine-e-label'), 'type' => 'success'];
      } else {
        $lines[] = ['text' => __('Receiver-Endpunkt gefunden.', 'wine-e-label'), 'type' => 'success'];
      }
    }

    wp_send_json_success(array('message' => __('Verbindung erfolgreich geprüft.', 'wine-e-label'), 'lines' => $lines));
  }



  private function get_rest_credentials(): array
  {
    return [
      'base_url' => self::normalize_rest_base_url((string) get_option('wine_e_label_rest_base_url', '')),
      'username' => sanitize_user((string) get_option('wine_e_label_rest_username', ''), true),
      'app_password' => self::sanitize_rest_app_password((string) get_option('wine_e_label_rest_app_password', '')),
    ];
  }

  private function has_rest_credentials(array $creds): bool
  {
    return (string) ($creds['base_url'] ?? '') !== ''
      && (string) ($creds['username'] ?? '') !== ''
      && (string) ($creds['app_password'] ?? '') !== '';
  }

  private function empty_label_data(): array
  {
    return [
      'slug' => '',
      'wine_nr' => '',
      'title' => '',
      'energy' => '',
      'carbs' => '',
      'sugar' => '',
      'minor' => '',
      'minor_mode' => '',
      'fat' => '',
      'saturates' => '',
      'protein' => '',
      'salt' => '',
      'salt_natural' => '0',
      'ingredients_html' => '',
      'footnote' => '',
      'pretable_notice' => '',
      'source_file_url' => '',
      'source_file_path' => '',
      'source_file_name' => '',
      'last_import' => '',
      'import_status' => '',
      'import_message' => '',
      'built_at' => '',
      'remote_page_id' => '',
      'remote_page_url' => '',
      'manual_config' => Wine_E_Label_Manual_Builder::default_config(),
      'import_snapshot' => Wine_E_Label_Manual_Builder::default_config(),
      'display_config' => class_exists('Wine_E_Label_Presentation') ? Wine_E_Label_Presentation::defaults() : [],
    ];
  }


  private function purge_generated_output(int $product_id, bool $delete_remote = true)
  {
    $data = Wine_E_Label_Importer::get_label_data($product_id);
    $remote_result = true;

    if ($delete_remote && !empty($data['slug'])) {
      $remote_result = $this->delete_remote_e_label((string) $data['slug']);
    }

    $this->db->delete_by_product_id($product_id);

    $data['built_at'] = '';
    $data['remote_page_id'] = '';
    $data['remote_page_url'] = '';

    Wine_E_Label_Importer::save_label_data($product_id, $data);

    return $remote_result;
  }

  private function purge_label_state(int $product_id, bool $delete_remote = true)
  {
    $data = Wine_E_Label_Importer::get_label_data($product_id);
    $remote_result = true;

    if ($delete_remote && !empty($data['slug'])) {
      $remote_result = $this->delete_remote_e_label((string) $data['slug']);
    }

    $source_path = (string) ($data['source_file_path'] ?? '');
    if ($source_path !== '' && file_exists($source_path)) {
      @unlink($source_path);
    }

    Wine_E_Label_Importer::save_label_data($product_id, $this->empty_label_data());
    $this->db->delete_label_data($product_id);

    return $remote_result;
  }

  private function discover_receiver_api(array $creds, bool $allow_legacy_probe = true)
  {
    if ((string) ($creds['base_url'] ?? '') === '') {
      return new WP_Error('receiver_config_missing', __('REST API Ziel-URL fehlt.', 'wine-e-label'));
    }

    $headers = ['Accept' => 'application/json'];
    if ($this->has_rest_credentials($creds)) {
      $headers = $this->get_rest_auth_headers($creds);
    }

    foreach (['reith-elabel/v2', 'reith-elabel/v1'] as $namespace) {
      $info_url = untrailingslashit($creds['base_url']) . '/wp-json/' . $namespace . '/info';
      $response = wp_remote_get($info_url, [
        'timeout' => 12,
        'sslverify' => true,
        'headers' => $headers,
      ]);
      if (is_wp_error($response)) {
        continue;
      }
      $code = (int) wp_remote_retrieve_response_code($response);
      if ($code >= 200 && $code < 300) {
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        $api = $this->normalize_receiver_info($body, (string) $creds['base_url'], $namespace, $info_url);
        if ($api !== null) {
          return $api;
        }
      } elseif ($code === 401 || $code === 403) {
        return new WP_Error('receiver_forbidden', __('Receiver-Endpunkt vorhanden, aber die Authentifizierung oder Berechtigung reicht nicht aus.', 'wine-e-label'));
      }
    }

    $index_url = untrailingslashit($creds['base_url']) . '/wp-json/';
    $index_response = wp_remote_get($index_url, [
      'timeout' => 12,
      'sslverify' => true,
      'headers' => $headers,
    ]);
    if (!is_wp_error($index_response)) {
      $index_code = (int) wp_remote_retrieve_response_code($index_response);
      if ($index_code >= 200 && $index_code < 300) {
        $index_body = json_decode((string) wp_remote_retrieve_body($index_response), true);
        $api = $this->discover_receiver_api_from_index($index_body, (string) $creds['base_url']);
        if ($api !== null) {
          return $api;
        }
      }
    }

    if (!$allow_legacy_probe) {
      return new WP_Error('receiver_not_found', __('Receiver-Endpunkt nicht gefunden.', 'wine-e-label'));
    }

    $probe_url = untrailingslashit($creds['base_url']) . '/wp-json/reith-elabel/v1/labels/connection-test-probe';
    $probe_response = wp_remote_get($probe_url, [
      'timeout' => 12,
      'sslverify' => true,
      'headers' => $headers,
    ]);

    if (is_wp_error($probe_response)) {
      return new WP_Error('receiver_request_failed', $probe_response->get_error_message());
    }

    $probe_code = (int) wp_remote_retrieve_response_code($probe_response);
    $probe_body = json_decode((string) wp_remote_retrieve_body($probe_response), true);
    $probe_error_code = is_array($probe_body) ? (string) ($probe_body['code'] ?? '') : '';

    if (($probe_code === 404 && $probe_error_code === 'relr_not_found') || ($probe_code >= 200 && $probe_code < 300)) {
      return [
        'namespace' => 'reith-elabel/v1',
        'source' => 'legacy',
        'info_endpoint' => '',
        'create_endpoint' => untrailingslashit($creds['base_url']) . '/wp-json/reith-elabel/v1/labels',
        'item_endpoint_template' => untrailingslashit($creds['base_url']) . '/wp-json/reith-elabel/v1/labels/{slug}',
      ];
    }

    if ($probe_code === 401 || $probe_code === 403) {
      return new WP_Error('receiver_forbidden', __('Receiver-Endpunkt vorhanden, aber die Authentifizierung oder Berechtigung reicht nicht aus.', 'wine-e-label'));
    }

    return new WP_Error('receiver_not_found', __('Receiver-Endpunkt nicht gefunden.', 'wine-e-label'));
  }

  private function normalize_receiver_info($body, string $base_url, string $namespace_guess, string $info_url): ?array
  {
    if (!is_array($body)) {
      return null;
    }

    $namespace = sanitize_text_field((string) ($body['namespace'] ?? $namespace_guess));
    if ($namespace === '') {
      $namespace = $namespace_guess;
    }

    $routes = is_array($body['routes'] ?? null) ? $body['routes'] : [];
    $create = (string) ($routes['create'] ?? ($body['create_endpoint'] ?? ''));
    $item = (string) ($routes['item'] ?? ($body['item_endpoint'] ?? ''));

    if ($create === '') {
      $create = '/wp-json/' . $namespace . '/labels';
    }
    if ($item === '') {
      $item = '/wp-json/' . $namespace . '/labels/{slug}';
    }

    return [
      'namespace' => $namespace,
      'source' => 'info',
      'info_endpoint' => $info_url,
      'create_endpoint' => $this->normalize_receiver_route($base_url, $create),
      'item_endpoint_template' => $this->normalize_receiver_route($base_url, $item),
    ];
  }

  private function discover_receiver_api_from_index($body, string $base_url): ?array
  {
    if (!is_array($body) || !is_array($body['routes'] ?? null)) {
      return null;
    }

    $found = [];
    foreach (array_keys($body['routes']) as $route) {
      if (!is_string($route) || strpos($route, '/reith-elabel/') !== 0) {
        continue;
      }
      if (preg_match('#^/reith-elabel/(v\d+)/labels$#', $route, $m)) {
        $version = $m[1];
        $found[$version]['create'] = $route;
      } elseif (preg_match('#^/reith-elabel/(v\d+)/labels/.+$#', $route, $m) && strpos($route, '/labels/(?P<slug>') !== false) {
        $version = $m[1];
        $found[$version]['item'] = $route;
      } elseif (preg_match('#^/reith-elabel/(v\d+)/info$#', $route, $m)) {
        $version = $m[1];
        $found[$version]['info'] = $route;
      }
    }

    if (empty($found)) {
      return null;
    }

    uksort($found, static function ($a, $b) {
      return version_compare(ltrim($b, 'v'), ltrim($a, 'v'));
    });

    foreach ($found as $version => $routes) {
      if (empty($routes['create']) || empty($routes['item'])) {
        continue;
      }
      return [
        'namespace' => 'reith-elabel/' . $version,
        'source' => 'index',
        'info_endpoint' => !empty($routes['info']) ? $this->normalize_receiver_route($base_url, $routes['info']) : '',
        'create_endpoint' => $this->normalize_receiver_route($base_url, $routes['create']),
        'item_endpoint_template' => $this->normalize_receiver_route($base_url, preg_replace('#\(\?P<slug>[^)]+\)#', '{slug}', $routes['item'])),
      ];
    }

    return null;
  }

  private function normalize_receiver_route(string $base_url, string $route): string
  {
    $route = trim($route);
    if ($route === '') {
      return '';
    }
    if (preg_match('#^https?://#i', $route)) {
      return $route;
    }
    if (strpos($route, '/wp-json/') === 0) {
      return untrailingslashit($base_url) . $route;
    }
    if ($route[0] !== '/') {
      $route = '/' . $route;
    }
    if (strpos($route, '/reith-elabel/') === 0) {
      return untrailingslashit($base_url) . '/wp-json' . $route;
    }
    return untrailingslashit($base_url) . $route;
  }

  private function receiver_item_endpoint(array $api, string $slug): string
  {
    $template = (string) ($api['item_endpoint_template'] ?? '');
    if ($template === '') {
      return '';
    }
    return str_replace('{slug}', rawurlencode(sanitize_title($slug)), $template);
  }

  private function publish_remote_e_label(int $product_id, array $data)
  {
    $creds = $this->get_rest_credentials();
    if (!$this->has_rest_credentials($creds)) {
      return new WP_Error('rest_config_missing', __('REST-API-Verbindung ist aktiviert, aber Ziel-URL, Benutzername oder Application Password fehlen.', 'wine-e-label'));
    }

    $page_content = $this->build_remote_page_content($product_id, $data);
    if ($page_content === '') {
      return new WP_Error('rest_content_empty', __('Externe E-Label-Seite konnte nicht aufgebaut werden.', 'wine-e-label'));
    }

    $api = $this->discover_receiver_api($creds, true);
    if (is_wp_error($api)) {
      return new WP_Error('rest_receiver_missing', sprintf(__('Receiver-API konnte nicht erkannt werden: %s', 'wine-e-label'), $api->get_error_message()));
    }

    $endpoint = (string) ($api['create_endpoint'] ?? '');
    if ($endpoint === '') {
      return new WP_Error('rest_publish_missing_endpoint', __('Receiver-API liefert keinen gültigen Erstell-Endpunkt.', 'wine-e-label'));
    }

    $response = wp_remote_post($endpoint, [
      'timeout' => 25,
      'sslverify' => true,
      'headers' => $this->get_rest_auth_headers($creds),
      'body' => wp_json_encode([
        'title' => (string) ($data['title'] ?? ''),
        'slug' => (string) ($data['slug'] ?? ''),
        'status' => 'publish',
        'html' => $page_content,
        'lang' => 'de',
        'design' => class_exists('Wine_E_Label_Design') ? Wine_E_Label_Design::export_remote_settings() : [],
        'source' => [
          'type' => 'main_plugin',
          'product_id' => $product_id,
          'site' => home_url('/'),
          'updated_at' => current_time('mysql'),
          'targets' => Wine_E_Label_URL::get_source_targets_for_sync($product_id),
        ],
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    if (is_wp_error($response)) {
      return new WP_Error('rest_publish_failed', sprintf(__('Externe E-Label-Seite konnte nicht erstellt werden: %s', 'wine-e-label'), $response->get_error_message()));
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode((string) wp_remote_retrieve_body($response), true);
    if ($code < 200 || $code >= 300 || empty($body['url'])) {
      $remote_message = is_array($body) ? (string) ($body['message'] ?? '') : '';
      return new WP_Error('rest_publish_invalid', sprintf(__('Externe E-Label-Seite konnte nicht erstellt werden (HTTP %1$d). %2$s', 'wine-e-label'), $code, $remote_message));
    }

    return [
      'id' => (int) ($body['id'] ?? 0),
      'url' => (string) $body['url'],
    ];
  }

  private function get_rest_auth_headers(array $creds): array
  {
    return [
      'Authorization' => 'Basic ' . base64_encode($creds['username'] . ':' . $creds['app_password']),
      'Accept' => 'application/json',
      'Content-Type' => 'application/json',
    ];
  }

  private function delete_remote_e_label(string $slug)
  {
    $slug = sanitize_title($slug);
    if ($slug === '') {
      return true;
    }

    $creds = $this->get_rest_credentials();
    if (!$this->has_rest_credentials($creds)) {
      return true;
    }

    $api = $this->discover_receiver_api($creds, true);
    if (is_wp_error($api)) {
      return $api;
    }

    $endpoint = $this->receiver_item_endpoint($api, $slug);
    if ($endpoint === '') {
      return new WP_Error('rest_delete_missing_endpoint', __('Receiver-API liefert keinen gültigen Lösch-Endpunkt.', 'wine-e-label'));
    }

    $response = wp_remote_request($endpoint, [
      'method' => 'DELETE',
      'timeout' => 20,
      'sslverify' => true,
      'headers' => $this->get_rest_auth_headers($creds),
    ]);

    if (is_wp_error($response)) {
      return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code === 404 || ($code >= 200 && $code < 300)) {
      return true;
    }

    $body = json_decode((string) wp_remote_retrieve_body($response), true);
    $remote_message = is_array($body) ? (string) ($body['message'] ?? '') : '';

    return new WP_Error('rest_delete_invalid', sprintf(__('Externe E-Label-Seite konnte nicht gelöscht werden (HTTP %1$d). %2$s', 'wine-e-label'), $code, $remote_message));
  }

  private function build_remote_page_content(int $product_id, array $data): string
  {
    $title = esc_html((string) ($data['title'] ?? ''));
    $langs = Wine_E_Label_URL::get_lang_names();
    $maps = $this->get_remote_language_maps();
    $ingredient_html = (string) ($data['ingredients_html'] ?? '');
    $footnote = esc_html((string) ($data['footnote'] ?? ''));
    $minor_mode = (string) ($data['minor_mode'] ?? 'text');
    $salt_natural = (string) ($data['salt_natural'] ?? '0') === '1';
    $lang_buttons = '';
    $lang_panels = '';
    $design_settings = class_exists('Wine_E_Label_Design') ? Wine_E_Label_Design::get_settings() : [];
    $presentation = class_exists('Wine_E_Label_Presentation')
      ? Wine_E_Label_Presentation::resolve($product_id, (array) ($data['display_config'] ?? []))
      : [];
    $header_markup = class_exists('Wine_E_Label_Design')
      ? Wine_E_Label_Design::render_product_header_markup($presentation, $design_settings, 'nler')
      : '';
    $default_lang = 'de';
    foreach ($langs as $code => $name) {
      $labels = $maps['labels'][$code] ?? $maps['labels']['de'];
      if (!empty($data['manual_config']) && class_exists('Wine_E_Label_Manual_Builder')) {
        [$translated_ingredients, $translated_footnote] = Wine_E_Label_Manual_Builder::build_ingredients_html((array) $data['manual_config'], $code);
      } else {
        $translated_ingredients = $this->translate_remote_ingredients($ingredient_html, $code, $maps['phrases']);
        $translated_footnote = $this->translate_remote_text($footnote, $code, $maps['phrases']);
      }
      $translated_minor = $minor_mode === 'text' ? ($labels['minor_text'] ?? '') : '';
      $is_default = $code === $default_lang;
      $lang_buttons .= '<button type="button" class="nler-lang-button' . ($is_default ? ' is-active' : '') . '" data-lang="' . esc_attr($code) . '" aria-pressed="' . ($is_default ? 'true' : 'false') . '" aria-current="' . ($is_default ? 'true' : 'false') . '">' . esc_html(strtoupper($code)) . '</button>';
      $panel = '<div class="nler-panel' . ($is_default ? ' is-active' : '') . '" data-lang="' . esc_attr($code) . '">';
      $panel .= '<div class="nler-label-card">';
      $panel .= '<table class="nler-label-table"><thead><tr><th>' . esc_html($labels['nutrition_per_100ml']) . '</th></tr></thead><tbody>';
      if (!empty($data['pretable_notice'])) {
        $panel .= '<tr class="nler-label-pretable"><td><div class="nler-label-row"><span>' . esc_html((string) $data['pretable_notice']) . '</span><span></span></div></td></tr>';
      }
      $panel .= '<tr><td><div class="nler-label-row"><span>' . esc_html($labels['energy']) . '</span><span>' . esc_html((string) ($data['energy'] ?? '')) . '</span></div></td></tr>';
      $panel .= '<tr><td><div class="nler-label-row"><span>' . esc_html($labels['carbohydrates']) . '</span><span>' . esc_html((string) ($data['carbs'] ?? '')) . '</span></div></td></tr>';
      $panel .= '<tr><td><div class="nler-label-row"><span>' . esc_html($labels['sugar']) . '</span><span>' . esc_html((string) ($data['sugar'] ?? '')) . '</span></div></td></tr>';
      if ($minor_mode === 'list') {
        foreach ([['fat','fat'],['saturates','saturates'],['protein','protein'],['salt','salt']] as $pair) {
          $val = trim((string) ($data[$pair[0]] ?? ''));
          if ($val !== '') {
            $panel .= '<tr><td><div class="nler-label-row"><span>' . esc_html($labels[$pair[1]]) . '</span><span>' . esc_html($val . ' g') . '</span></div></td></tr>';
          }
        }
        if ($salt_natural) {
          $panel .= '<tr class="nler-label-trace"><td>' . esc_html($labels['salt_natural']) . '</td></tr>';
        }
      } elseif ($translated_minor !== '') {
        $panel .= '<tr class="nler-label-trace"><td>' . esc_html($translated_minor) . '</td></tr>';
      }
      $panel .= '</tbody></table>';
      if ($translated_ingredients !== '') {
        $panel .= '<div class="nler-ingredients"><strong>' . esc_html($labels['ingredients']) . ':</strong> ' . $translated_ingredients . '</div>';
      }
      if ($translated_footnote !== '') {
        $panel .= '<div class="nler-footnote">' . esc_html($translated_footnote) . '</div>';
      }
      $panel .= '</div>';
      if (class_exists('Wine_E_Label_Design')) {
        $panel .= Wine_E_Label_Design::render_producer_markup($code, $design_settings, 'nler');
      }
      $panel .= '</div>';
      $lang_panels .= $panel;
    }

    $standalone_css = class_exists('Wine_E_Label_Design')
      ? Wine_E_Label_Design::build_remote_css($design_settings)
      : 'html,body{margin:0!important;padding:0!important;background:#f3f4f6!important;color:#111827!important;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif!important;font-size:15px!important;line-height:1.45}.nler-page-shell{box-sizing:border-box!important;width:min(980px,calc(100vw - 32px))!important;max-width:none!important;margin:0 auto!important;padding:40px 16px 40px!important}.nler-lang-switch{display:flex!important;justify-content:center!important;align-items:center!important;gap:8px!important;flex-wrap:wrap!important;width:min(640px,100%)!important;margin:0 auto 12px auto!important}.nler-lang-button{display:inline-flex!important;align-items:center!important;justify-content:center!important;text-decoration:none!important;border:1px solid #d7e0ea!important;border-radius:999px!important;background:#ffffff!important;color:#334155!important;padding:4px 10px!important;font-size:13px!important;font-weight:600!important;line-height:1.1!important;box-shadow:0 1px 2px rgba(15,23,42,.03)!important;cursor:pointer!important;-webkit-appearance:none!important;appearance:none!important}.nler-lang-button.is-active,.nler-lang-button[aria-current=\"true\"],.nler-lang-button[aria-pressed=\"true\"]{background:#eef5ff!important;border-color:#bfd3ea!important;color:#244267!important;box-shadow:0 0 0 3px rgba(36,66,103,.06)!important}.nler-panel{display:none!important}.nler-panel.is-active{display:block!important}.nler-header-block{width:min(640px,100%)!important;margin:0 auto 18px auto!important;display:flex!important;flex-direction:column!important;align-items:center!important;gap:12px!important;text-align:center!important}.nler-label-card{box-sizing:border-box!important;width:min(640px,100%)!important;max-width:none!important;margin:0 auto!important;padding:26px!important;background:#ffffff!important;border:1px solid #d1d5db!important;border-radius:16px!important;color:#111827!important}.nler-label-table{width:100%!important;border-collapse:collapse!important;background:#ffffff!important;border:1px solid #d1d5db!important;font-size:15px!important}.nler-label-table th,.nler-label-table td{border:1px solid #d1d5db!important;padding:10px 12px!important;vertical-align:top!important;color:#111827!important;font-size:15px!important;line-height:1.45!important}.nler-label-table thead th{text-align:left!important;background:#f3f4f6!important;font-weight:600!important}.nler-label-row{display:flex!important;justify-content:space-between!important;gap:16px!important}.nler-label-row span:last-child{text-align:right!important;white-space:nowrap!important;font-weight:500!important}.nler-label-trace td,.nler-label-pretable td,.nler-footnote{font-size:14px!important;color:#6b7280!important}.nler-ingredients,.nler-footnote{margin-top:12px!important;line-height:1.55!important}.nler-producer-card{box-sizing:border-box!important;width:min(640px,100%)!important;max-width:none!important;margin:14px auto 0 auto!important;padding:18px 26px!important;background:#ffffff!important;border:1px solid #d1d5db!important;border-radius:16px!important}@media (max-width:600px){.nler-page-shell{width:calc(100vw - 20px)!important;padding:24px 10px 28px!important}.nler-label-card{padding:16px!important}.nler-producer-card{padding:16px!important}.nler-label-row span:last-child{white-space:normal!important}}';
    $script = '(function(){var script=document.currentScript;var root=script&&script.closest?script.closest(".nler-remote"):null;if(!root){return;}var params=new URLSearchParams(window.location.search);var current=params.get("lang")||root.getAttribute("data-default-lang")||"de";var panels=root.querySelectorAll(".nler-panel");var buttons=root.querySelectorAll(".nler-lang-button");function apply(lang){var hasMatch=false;panels.forEach(function(p){var active=p.getAttribute("data-lang")===lang;p.classList.toggle("is-active",active);p.hidden=!active;p.setAttribute("aria-hidden",active?"false":"true");if(active){p.style.display="block";hasMatch=true;}else{p.style.display="none";}});buttons.forEach(function(b){var active=b.getAttribute("data-lang")===lang;b.classList.toggle("is-active",active);b.setAttribute("aria-pressed",active?"true":"false");b.setAttribute("aria-current",active?"true":"false");});if(!hasMatch&&lang!=="de"){apply("de");}}buttons.forEach(function(btn){btn.addEventListener("click",function(){var lang=btn.getAttribute("data-lang")||"de";var url=new URL(window.location.href);url.searchParams.set("lang",lang);window.history.replaceState({},"",url.toString());apply(lang);});});apply(current);})();';

    $html = '<style>' . $standalone_css . '</style>';
    $html .= '<div class="nler-page-shell"><div class="nler-remote" data-default-lang="' . esc_attr($default_lang) . '"><div class="nler-lang-switch">' . $lang_buttons . '</div>' . $header_markup . $lang_panels . '<script>' . $script . '</script></div></div>';

    return "<!-- wp:html -->
" . $html . "
<!-- /wp:html -->";
  }

  private function get_remote_language_maps(): array
  {
    return [
      'labels' => [
        'de' => ['nutrition_per_100ml'=>'Nährwertangaben je 100ml','energy'=>'Brennwert','carbohydrates'=>'Kohlenhydrate','sugar'=>'davon Zucker','fat'=>'Fett','saturates'=>'davon gesättigte Fettsäuren','protein'=>'Eiweiß','salt'=>'Salz','salt_natural'=>'Angegebener Salzgehalt ist ausschließlich auf die Anwesenheit natürlich vorkommenden Natriums zurückzuführen.','minor_text'=>'Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz','ingredients'=>'Zutaten'],
        'en' => ['nutrition_per_100ml'=>'Nutrition declaration per 100ml','energy'=>'Energy','carbohydrates'=>'Carbohydrates','sugar'=>'of which sugars','fat'=>'Fat','saturates'=>'of which saturates','protein'=>'Protein','salt'=>'Salt','salt_natural'=>'The salt content is exclusively due to the presence of naturally occurring sodium.','minor_text'=>'Contains negligible amounts of fat, saturated fat, protein and salt','ingredients'=>'Ingredients'],
        'it' => ['nutrition_per_100ml'=>'Dichiarazione nutrizionale per 100ml','energy'=>'Energia','carbohydrates'=>'Carboidrati','sugar'=>'di cui zuccheri','fat'=>'Grassi','saturates'=>'di cui acidi grassi saturi','protein'=>'Proteine','salt'=>'Sale','salt_natural'=>'Il contenuto di sale è dovuto esclusivamente alla presenza di sodio naturalmente presente.','minor_text'=>'Contiene quantità trascurabili di grassi, acidi grassi saturi, proteine e sale','ingredients'=>'Ingredienti'],
        'fr' => ['nutrition_per_100ml'=>'Déclaration nutritionnelle pour 100ml','energy'=>'Énergie','carbohydrates'=>'Glucides','sugar'=>'dont sucres','fat'=>'Matières grasses','saturates'=>'dont acides gras saturés','protein'=>'Protéines','salt'=>'Sel','salt_natural'=>'La teneur en sel est exclusivement due à la présence de sodium naturellement présent.','minor_text'=>'Contient des quantités négligeables de matières grasses, d’acides gras saturés, de protéines et de sel','ingredients'=>'Ingrédients'],
      ],
      'phrases' => [
        'en' => ['Stabilisatoren: enthält '=>'Stabilisers: contains ','Säureregulatoren: enthält '=>'Acidity regulators: contains ',' und/oder '=>' and/or ','* aus ökologischer Erzeugung'=>'* from organic production','Trauben'=>'Grapes','Fülldosage'=>'Dosage liqueur','Versanddosage'=>'Expedition liqueur','Saccharose'=>'Sucrose','konzentrierter Traubenmost'=>'Concentrated grape must','rektifiziertes Traubenmostkonzentrat (RTK)'=>'Rectified concentrated grape must (RCGM)','Weinsäure'=>'Tartaric acid','Äpfelsäure'=>'Malic acid','Milchsäure'=>'Lactic acid','Calciumsulfat'=>'Calcium sulfate','Citronensäure'=>'Citric acid','Sulfite'=>'Sulphites','Kaliumsorbat'=>'Potassium sorbate','Lysozym'=>'Lysozyme','L-Ascorbinsäure'=>'L-ascorbic acid','Dimethyldicarbonat (DMDC)'=>'Dimethyl dicarbonate (DMDC)','Metaweinsäure'=>'Metatartaric acid','Gummiarabikum'=>'Gum arabic','Hefe-Mannoproteine'=>'Yeast mannoproteins','Carboxymethylcellulose'=>'Carboxymethylcellulose','Kaliumpolyaspartat'=>'Potassium polyaspartate','Fumarsäure'=>'Fumaric acid','Argon'=>'Argon','Stickstoff'=>'Nitrogen','Kohlendioxid'=>'Carbon dioxide','unter Schutzatmosphäre abgefüllt'=>'Bottled under protective atmosphere','Die Abfüllung kann unter Schutzatmosphäre erfolgt sein'=>'Bottling may have taken place under protective atmosphere','Aleppokiefernharz'=>'Aleppo pine resin','Karamell'=>'Caramel','Aromastoffe'=>'Flavourings','Aromaextrakt'=>'Flavour extract','Würzkräuter'=>'Herbs','Gewürze'=>'Spices','Farbstoffe'=>'Colours','Ethylalkohol landwirtschaftlichen Ursprungs'=>'Ethyl alcohol of agricultural origin','Neutralalkohol'=>'Neutral alcohol','Agraralkohol'=>'Agricultural alcohol','rektifizierter Alkohol'=>'Rectified alcohol','landwirtschaftlicher Alkohol'=>'Agricultural alcohol'],
        'it' => ['Stabilisatoren: enthält '=>'Stabilizzanti: contiene ','Säureregulatoren: enthält '=>'Correttori di acidità: contiene ',' und/oder '=>' e/o ','* aus ökologischer Erzeugung'=>'* da produzione biologica','Trauben'=>'Uve','Fülldosage'=>'Dosaggio','Versanddosage'=>'Dosaggio finale','Saccharose'=>'Saccarosio','konzentrierter Traubenmost'=>'Mosto d’uva concentrato','rektifiziertes Traubenmostkonzentrat (RTK)'=>'Mosto d’uva concentrato rettificato (MCR)','Weinsäure'=>'Acido tartarico','Äpfelsäure'=>'Acido malico','Milchsäure'=>'Acido lattico','Calciumsulfat'=>'Solfato di calcio','Citronensäure'=>'Acido citrico','Sulfite'=>'Solfiti','Kaliumsorbat'=>'Sorbato di potassio','Lysozym'=>'Lisozima','L-Ascorbinsäure'=>'Acido L-ascorbico','Dimethyldicarbonat (DMDC)'=>'Dimetil dicarbonato (DMDC)','Metaweinsäure'=>'Acido metatartarico','Gummiarabikum'=>'Gomma arabica','Hefe-Mannoproteine'=>'Mannoproteine di lievito','Carboxymethylcellulose'=>'Carbossimetilcellulosa','Kaliumpolyaspartat'=>'Poliaspartato di potassio','Fumarsäure'=>'Acido fumarico','Argon'=>'Argon','Stickstoff'=>'Azoto','Kohlendioxid'=>'Anidride carbonica','unter Schutzatmosphäre abgefüllt'=>'Imbottigliato in atmosfera protettiva','Die Abfüllung kann unter Schutzatmosphäre erfolgt sein'=>'L’imbottigliamento può essere avvenuto in atmosfera protettiva','Aleppokiefernharz'=>'Resina di pino d’Aleppo','Karamell'=>'Caramello','Aromastoffe'=>'Aromi','Aromaextrakt'=>'Estratto aromatico','Würzkräuter'=>'Erbe aromatiche','Gewürze'=>'Spezie','Farbstoffe'=>'Coloranti','Ethylalkohol landwirtschaftlichen Ursprungs'=>'Alcol etilico di origine agricola','Neutralalkohol'=>'Alcol neutro','Agraralkohol'=>'Alcol agricolo','rektifizierter Alkohol'=>'Alcol rettificato','landwirtschaftlicher Alkohol'=>'Alcol agricolo'],
        'fr' => ['Stabilisatoren: enthält '=>'Stabilisants : contient ','Säureregulatoren: enthält '=>'Correcteurs d’acidité : contient ',' und/oder '=>' et/ou ','* aus ökologischer Erzeugung'=>'* issu de l’agriculture biologique','Trauben'=>'Raisins','Fülldosage'=>'Liqueur de dosage','Versanddosage'=>'Liqueur d’expédition','Saccharose'=>'Saccharose','konzentrierter Traubenmost'=>'Moût de raisin concentré','rektifiziertes Traubenmostkonzentrat (RTK)'=>'Moût de raisin concentré rectifié (MCR)','Weinsäure'=>'Acide tartrique','Äpfelsäure'=>'Acide malique','Milchsäure'=>'Acide lactique','Calciumsulfat'=>'Sulfate de calcium','Citronensäure'=>'Acide citrique','Sulfite'=>'Sulfites','Kaliumsorbat'=>'Sorbate de potassium','Lysozym'=>'Lysozyme','L-Ascorbinsäure'=>'Acide L-ascorbique','Dimethyldicarbonat (DMDC)'=>'Diméthyl dicarbonate (DMDC)','Metaweinsäure'=>'Acide métatartrique','Gummiarabikum'=>'Gomme arabique','Hefe-Mannoproteine'=>'Mannoprotéines de levure','Carboxymethylcellulose'=>'Carboxyméthylcellulose','Kaliumpolyaspartat'=>'Polyaspartate de potassium','Fumarsäure'=>'Acide fumarique','Argon'=>'Argon','Stickstoff'=>'Azote','Kohlendioxid'=>'Dioxyde de carbone','unter Schutzatmosphäre abgefüllt'=>'Mis en bouteille sous atmosphère protectrice','Die Abfüllung kann unter Schutzatmosphäre erfolgt sein'=>'La mise en bouteille peut avoir eu lieu sous atmosphère protectrice','Aleppokiefernharz'=>'Résine de pin d’Alep','Karamell'=>'Caramel','Aromastoffe'=>'Arômes','Aromaextrakt'=>'Extrait aromatique','Würzkräuter'=>'Herbes aromatiques','Gewürze'=>'Épices','Farbstoffe'=>'Colorants','Ethylalkohol landwirtschaftlichen Ursprungs'=>'Alcool éthylique d’origine agricole','Neutralalkohol'=>'Alcool neutre','Agraralkohol'=>'Alcool agricole','rektifizierter Alkohol'=>'Alcool rectifié','landwirtschaftlicher Alkohol'=>'Alcool agricole'],
      ],
    ];
  }

  private function translate_remote_ingredients(string $html, string $lang, array $phrase_maps): string
  {
    if ($html === '') {
      return '';
    }
    $translated = $html;
    foreach (($phrase_maps[$lang] ?? []) as $search => $replace) {
      $translated = str_replace($search, $replace, $translated);
    }
    return $translated;
  }

  private function translate_remote_text(string $text, string $lang, array $phrase_maps): string
  {
    if ($text === '' || $lang === 'de') {
      return $text;
    }
    foreach (($phrase_maps[$lang] ?? []) as $search => $replace) {
      $text = str_replace($search, $replace, $text);
    }
    return $text;
  }

  public function render_settings_page()
  {
    require_once WINE_E_LABEL_PLUGIN_DIR . 'admin/wine-e-label-settings-page-simple.php';
  }

  public function render_design_page()
  {
    require_once WINE_E_LABEL_PLUGIN_DIR . 'admin/wine-e-label-design-page.php';
  }

  public function render_db_management_page()
  {
    // Ensure WordPress functions are available
    if (!function_exists('wp_create_nonce')) {
      wp_die(__('WordPress-Funktionen sind nicht verfügbar. Bitte Administrator kontaktieren.', 'wine-e-label'));
    }
    require_once WINE_E_LABEL_PLUGIN_DIR . 'admin/wine-e-label-db-management.php';
  }

  public static function get_settings_nonce()
  {
    return function_exists('wp_create_nonce') ? wp_create_nonce('update-options') : '';
  }

  public function ajax_flush_rewrite_rules()
  {
    check_ajax_referer('flush_rewrite_rules', '_wpnonce_flush');
    if (!current_user_can('manage_options')) {
      wp_die(__('Nicht autorisiert', 'wine-e-label'));
    }

    flush_rewrite_rules(false);

    wp_send_json(array(
      'success' => true,
      'message' => __('Rewrite-Regeln wurden erfolgreich aktualisiert!', 'wine-e-label')
    ));
  }

  public static function handle_settings_submission()
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('Du hast keine Berechtigung, Einstellungen zu ändern.', 'wine-e-label'));
    }

    check_admin_referer('update-options');

    $posted_settings = isset($_POST['wine_e_label_settings']) && is_array($_POST['wine_e_label_settings'])
      ? wp_unslash($_POST['wine_e_label_settings'])
      : [];

    $rewrite_before = [
      'wine_e_label_base_url' => (string) get_option('wine_e_label_base_url', ''),
      'wine_e_label_use_subdomain' => (string) get_option('wine_e_label_use_subdomain', 'no'),
      'wine_e_label_subdomain' => (string) get_option('wine_e_label_subdomain', ''),
      'wine_e_label_subdomain_scheme' => (string) get_option('wine_e_label_subdomain_scheme', 'https'),
    ];

    if (isset($posted_settings['qr_size'])) {
      $allowed_qr_sizes = ['300x300', '500x500', '800x800'];
      $qr_size = sanitize_text_field((string) $posted_settings['qr_size']);
      update_option('qr_size', in_array($qr_size, $allowed_qr_sizes, true) ? $qr_size : '500x500');
    }
    if (isset($posted_settings['qr_format'])) {
      $qr_format = sanitize_text_field((string) $posted_settings['qr_format']);
      update_option('qr_format', in_array($qr_format, ['png', 'svg'], true) ? $qr_format : 'png');
    }
    if (isset($posted_settings['qr_error_correction'])) {
      $ec = sanitize_text_field((string) $posted_settings['qr_error_correction']);
      update_option('qr_error_correction', in_array($ec, ['low', 'medium', 'quartile', 'high'], true) ? $ec : 'low');
    }

    update_option(
      'wine_e_label_delete_data_on_uninstall',
      isset($posted_settings['delete_data_on_uninstall']) ? 'yes' : 'no'
    );

    if (array_key_exists('base_url', $posted_settings)) {
      $base = sanitize_text_field((string) $posted_settings['base_url']);
      $base = trim($base);
      if ($base !== '' && !preg_match('#^https?://#i', $base)) {
        $base = 'https://' . $base;
      }
      update_option('wine_e_label_base_url', untrailingslashit($base));
    }

    update_option(
      'wine_e_label_rest_enabled',
      isset($posted_settings['rest_enabled']) ? 'yes' : 'no'
    );
    if (array_key_exists('rest_base_url', $posted_settings)) {
      update_option('wine_e_label_rest_base_url', self::normalize_rest_base_url((string) $posted_settings['rest_base_url']));
    }
    if (array_key_exists('rest_username', $posted_settings)) {
      update_option('wine_e_label_rest_username', sanitize_user((string) $posted_settings['rest_username'], true));
    }
    if (array_key_exists('rest_app_password', $posted_settings)) {
      $new_password = self::sanitize_rest_app_password((string) $posted_settings['rest_app_password']);
      if ($new_password !== '') {
        update_option('wine_e_label_rest_app_password', $new_password);
      }
    }

    update_option(
      'wine_e_label_use_subdomain',
      isset($posted_settings['use_subdomain']) ? 'yes' : 'no'
    );
    if (array_key_exists('subdomain', $posted_settings)) {
      $raw = sanitize_text_field((string) $posted_settings['subdomain']);
      $raw = preg_replace('#^https?://|/.*$#', '', $raw);
      update_option('wine_e_label_subdomain', strtolower($raw));
    }
    $scheme = sanitize_text_field((string) ($posted_settings['subdomain_scheme'] ?? 'https'));
    update_option('wine_e_label_subdomain_scheme', in_array($scheme, ['https', 'http'], true) ? $scheme : 'https');

    if (array_key_exists('admin_language', $posted_settings)) {
      $admin_lang = sanitize_text_field((string) $posted_settings['admin_language']);
      update_option('wine_e_label_admin_language', in_array($admin_lang, ['auto', 'de', 'en', 'fr', 'it'], true) ? $admin_lang : 'auto');
    }

    echo '<div class="notice notice-success"><p>' . esc_html__('Einstellungen gespeichert.', 'wine-e-label') . '</p></div>';

    $rewrite_after = [
      'wine_e_label_base_url' => (string) get_option('wine_e_label_base_url', ''),
      'wine_e_label_use_subdomain' => (string) get_option('wine_e_label_use_subdomain', 'no'),
      'wine_e_label_subdomain' => (string) get_option('wine_e_label_subdomain', ''),
      'wine_e_label_subdomain_scheme' => (string) get_option('wine_e_label_subdomain_scheme', 'https'),
    ];

    if ($rewrite_before !== $rewrite_after) {
      Wine_E_Label_URL::add_rewrite_rules();
      flush_rewrite_rules(false);
    }
  }

  public static function handle_design_submission()
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('Du hast keine Berechtigung, Einstellungen zu ändern.', 'wine-e-label'));
    }

    check_admin_referer('wine_e_label_save_design');

    $settings = Wine_E_Label_Design::sanitize_settings($_POST);
    update_option(Wine_E_Label_Design::OPTION_NAME, $settings);

    $sync_summary = [
      'attempted' => 0,
      'synced' => 0,
      'failed' => 0,
    ];

    if (Wine_E_Label_URL::use_external_rest_domain()) {
      $admin = new self(false);
      $sync_summary = $admin->sync_remote_design_to_receiver();
    }

    $redirect_args = array(
      'page' => WINE_E_LABEL_ADMIN_PAGE_DESIGN,
      'wine_e_label_design_notice' => 'saved',
    );

    if (Wine_E_Label_URL::use_external_rest_domain()) {
      $redirect_args['wine_e_label_design_synced'] = (string) $sync_summary['synced'];
      $redirect_args['wine_e_label_design_failed'] = (string) $sync_summary['failed'];
    }

    wp_safe_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
    exit;
  }

  private function sync_remote_design_to_receiver(): array
  {
    $summary = [
      'attempted' => 0,
      'synced' => 0,
      'failed' => 0,
    ];

    if (!Wine_E_Label_URL::use_external_rest_domain()) {
      return $summary;
    }

    $entries = $this->db->get_entries_for_export();
    if (!is_array($entries) || $entries === []) {
      return $summary;
    }

    $seen_products = [];

    foreach ($entries as $entry) {
      $product_id = isset($entry->product_id) ? (int) $entry->product_id : 0;
      if ($product_id <= 0 || isset($seen_products[$product_id])) {
        continue;
      }

      $seen_products[$product_id] = true;
      $data = Wine_E_Label_Importer::get_label_data($product_id);

      if (trim((string) ($data['built_at'] ?? '')) === '' || trim((string) ($data['slug'] ?? '')) === '') {
        continue;
      }

      $summary['attempted']++;
      $remote_result = $this->publish_remote_e_label($product_id, $data);

      if (is_wp_error($remote_result)) {
        $summary['failed']++;
        continue;
      }

      $data['remote_page_id'] = (string) ($remote_result['id'] ?? '');
      $data['remote_page_url'] = (string) ($remote_result['url'] ?? '');
      Wine_E_Label_Importer::save_label_data($product_id, $data);
      $summary['synced']++;
    }

    return $summary;
  }
}
