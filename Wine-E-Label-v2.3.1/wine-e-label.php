<?php

/**
 * Plugin Name: Wein E-Label
 * Plugin URI:  https://github.com/jo-286/wine-e-label
 * Description: Importiert WIPZN-Importe sowie ZIP-, JSON- und HTML-Dateien, erzeugt E-Label-Seiten und QR-Codes für WooCommerce-Produkte.
 * Version: 2.3.1
 * Author:      Johannes Reith, Markus Hammer
 * Author URI:  https://reithwein.com
 * Text Domain: wine-e-label
 * Domain Path: /languages
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP: 8.4
 * Requires at least: 5.0
 * Update URI:  https://github.com/jo-286/wine-e-label#wine-e-label
 */

if (!defined('ABSPATH')) {
  exit;
}

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

define('NUTRITION_LABELS_VERSION', '2.3.1');
define('NUTRITION_LABELS_DB_VERSION', '1.71');
define('NUTRITION_LABELS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NUTRITION_LABELS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WINE_E_LABEL_TEXT_DOMAIN', 'wine-e-label');
define('WINE_E_LABEL_LEGACY_TEXT_DOMAIN', 'nutrition-labels');
define('WINE_E_LABEL_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WINE_E_LABEL_GITHUB_REPO_URL', 'https://github.com/jo-286/wine-e-label');
define('WINE_E_LABEL_GITHUB_MANIFEST_URL', 'https://raw.githubusercontent.com/jo-286/wine-e-label/main/updates/plugin-updates.json');
define('WINE_E_LABEL_ADMIN_PAGE_MAIN', 'wine_e_label_main');
define('WINE_E_LABEL_ADMIN_PAGE_DESIGN', 'wine_e_label_design');
define('WINE_E_LABEL_ADMIN_PAGE_DB', 'wine_e_label_db_management');

if (!defined('NUTRITION_LABELS_URL_PREFIX')) {
  define('NUTRITION_LABELS_URL_PREFIX', 'l');
}
if (!defined('NUTRITION_LABELS_SHORTCODE_LENGTH')) {
  define('NUTRITION_LABELS_SHORTCODE_LENGTH', 5);
}
if (!defined('NUTRITION_LABELS_CHARACTER_SET')) {
  define('NUTRITION_LABELS_CHARACTER_SET', 'alphanumeric');
}

require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-ingredients.php';
require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-db-extended.php';
require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-importer.php';
require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-manual-builder.php';
require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-design.php';
require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-presentation.php';
require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-url.php';
require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-frontend.php';
require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-elementor.php';
require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-admin-i18n.php';
require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-github-updater.php';

class Wine_E_Label_Plugin
{
  public function __construct()
  {
    new Wine_E_Label_GitHub_Updater(array(
      'plugin_file'     => __FILE__,
      'plugin_basename' => WINE_E_LABEL_PLUGIN_BASENAME,
      'plugin_slug'     => 'wine-e-label',
      'plugin_name'     => 'Wein E-Label',
      'plugin_version'  => NUTRITION_LABELS_VERSION,
      'manifest_key'    => 'wine-e-label',
      'manifest_url'    => WINE_E_LABEL_GITHUB_MANIFEST_URL,
      'homepage'        => WINE_E_LABEL_GITHUB_REPO_URL,
      'cache_key'       => 'wine_e_label_github_manifest',
    ));

    NutritionLabels_Admin_I18n::init();
    add_action('plugins_loaded', [$this, 'load_textdomain']);
    add_action('init', [$this, 'init']);
    add_action('admin_init', [$this, 'migrate_database']);
    add_action('admin_init', [$this, 'maybe_cleanup_duplicate_directories']);
  }

  public function load_textdomain()
  {
    load_plugin_textdomain(WINE_E_LABEL_TEXT_DOMAIN, false, dirname(WINE_E_LABEL_PLUGIN_BASENAME) . '/languages');
    load_plugin_textdomain(WINE_E_LABEL_LEGACY_TEXT_DOMAIN, false, dirname(WINE_E_LABEL_PLUGIN_BASENAME) . '/languages');
  }

  public static function activate()
  {
    $db = new NutritionLabels_DB_Extended();
    $db->create_tables();
    NutritionLabels_URL::add_rewrite_rules();
    flush_rewrite_rules();
  }

  public static function deactivate()
  {
    flush_rewrite_rules();
  }

  public function init()
  {
    NutritionLabels_URL::init();
    NutritionLabels_Frontend::init();
    NutritionLabels_Elementor::init();

    if (is_admin()) {
      require_once NUTRITION_LABELS_PLUGIN_DIR . 'admin/working-metabox.php';
      require_once NUTRITION_LABELS_PLUGIN_DIR . 'admin/class-wine-e-label-admin-extended.php';
      require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/class-wine-e-label-qr.php';
      $db = new NutritionLabels_DB_Extended();
      new Working_NutritionLabels_MetaBox($db);
      new NutritionLabels_Admin_Extended();
    }
  }

  public static function migrate_database()
  {
    global $wpdb;
    $table = $wpdb->prefix . 'nutrition_short_urls';
    $installed_db_version = get_option('nutrition_labels_db_version', '0.0.0');

    if (version_compare($installed_db_version, '1.2.0', '<')) {
      $wpdb->query("ALTER TABLE {$table} MODIFY short_code VARCHAR(120) NOT NULL");
      update_option('nutrition_labels_db_version', '1.2.0');
    }
  }

  public function maybe_cleanup_duplicate_directories()
  {
    if (!is_admin() || !current_user_can('activate_plugins')) {
      return;
    }

    if (empty($_GET['wel_cleanup_plugins'])) {
      return;
    }

    if (!empty($_GET['_wpnonce'])) {
      check_admin_referer('wel_cleanup_plugins');
    }

    $raw_dirs = isset($_GET['dirs']) ? (string) wp_unslash($_GET['dirs']) : '';
    $dirs = array_filter(array_map(static function ($dir) {
      return sanitize_title($dir);
    }, explode(',', $raw_dirs)));

    if (empty($dirs)) {
      wp_safe_redirect(admin_url('plugins.php?plugin_status=all'));
      exit;
    }

    $current_dir = basename(NUTRITION_LABELS_PLUGIN_DIR);
    foreach (array_unique($dirs) as $dir) {
      if ($dir === $current_dir || strpos($dir, 'wine-e-label') !== 0) {
        continue;
      }

      $target = trailingslashit(WP_PLUGIN_DIR) . $dir;
      if (!is_dir($target)) {
        continue;
      }

      self::delete_directory_recursively($target);
    }

    wp_safe_redirect(admin_url('plugins.php?plugin_status=all'));
    exit;
  }

  private static function delete_directory_recursively($directory)
  {
    $directory = wp_normalize_path((string) $directory);
    $plugins_dir = wp_normalize_path(WP_PLUGIN_DIR);

    if ($directory === '' || $directory === $plugins_dir || strpos($directory, $plugins_dir . '/') !== 0 || !is_dir($directory)) {
      return;
    }

    $items = scandir($directory);
    if (!is_array($items)) {
      return;
    }

    foreach ($items as $item) {
      if ($item === '.' || $item === '..') {
        continue;
      }

      $path = $directory . '/' . $item;
      if (is_dir($path)) {
        self::delete_directory_recursively($path);
      } elseif (file_exists($path)) {
        @unlink($path);
      }
    }

    @rmdir($directory);
  }
}

class_alias('Wine_E_Label_Plugin', 'NutritionLabels');

register_activation_hook(__FILE__, ['Wine_E_Label_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['Wine_E_Label_Plugin', 'deactivate']);
new Wine_E_Label_Plugin();
