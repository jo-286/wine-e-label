<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Wine_E_Label_Migration
{
    private const MIGRATION_OPTION = 'wine_e_label_internal_migration_version';
    private const MIGRATION_VERSION = '2.3.1-internal-rename-1';

    private const OPTION_MAP = [
        'nutrition_labels_db_version' => 'wine_e_label_db_version',
        'nutrition_labels_delete_data_on_uninstall' => 'wine_e_label_delete_data_on_uninstall',
        'nutrition_labels_admin_language' => 'wine_e_label_admin_language',
        'nutrition_labels_rest_enabled' => 'wine_e_label_rest_enabled',
        'nutrition_labels_rest_base_url' => 'wine_e_label_rest_base_url',
        'nutrition_labels_rest_username' => 'wine_e_label_rest_username',
        'nutrition_labels_rest_app_password' => 'wine_e_label_rest_app_password',
        'nutrition_labels_base_url' => 'wine_e_label_base_url',
        'nutrition_labels_use_subdomain' => 'wine_e_label_use_subdomain',
        'nutrition_labels_subdomain' => 'wine_e_label_subdomain',
        'nutrition_labels_subdomain_scheme' => 'wine_e_label_subdomain_scheme',
        'nutrition_labels_design_settings' => 'wine_e_label_design_settings',
    ];

    private const META_MAP = [
        '_nutrition_labels_slug' => '_wine_e_label_slug',
        '_nutrition_labels_wine_nr' => '_wine_e_label_wine_nr',
        '_nutrition_labels_label_title' => '_wine_e_label_label_title',
        '_nutrition_labels_energy' => '_wine_e_label_energy',
        '_nutrition_labels_carbs' => '_wine_e_label_carbs',
        '_nutrition_labels_sugar' => '_wine_e_label_sugar',
        '_nutrition_labels_minor' => '_wine_e_label_minor',
        '_nutrition_labels_minor_mode' => '_wine_e_label_minor_mode',
        '_nutrition_labels_fat' => '_wine_e_label_fat',
        '_nutrition_labels_saturates' => '_wine_e_label_saturates',
        '_nutrition_labels_protein' => '_wine_e_label_protein',
        '_nutrition_labels_salt' => '_wine_e_label_salt',
        '_nutrition_labels_salt_natural' => '_wine_e_label_salt_natural',
        '_nutrition_labels_ingredients_html' => '_wine_e_label_ingredients_html',
        '_nutrition_labels_footnote' => '_wine_e_label_footnote',
        '_nutrition_labels_pretable_notice' => '_wine_e_label_pretable_notice',
        '_nutrition_labels_source_file_url' => '_wine_e_label_source_file_url',
        '_nutrition_labels_source_file_path' => '_wine_e_label_source_file_path',
        '_nutrition_labels_source_file_name' => '_wine_e_label_source_file_name',
        '_nutrition_labels_last_import' => '_wine_e_label_last_import',
        '_nutrition_labels_import_status' => '_wine_e_label_import_status',
        '_nutrition_labels_import_message' => '_wine_e_label_import_message',
        '_nutrition_labels_built_at' => '_wine_e_label_built_at',
        '_nutrition_labels_manual_config' => '_wine_e_label_manual_config',
        '_nutrition_labels_import_snapshot' => '_wine_e_label_import_snapshot',
        '_nutrition_labels_remote_page_id' => '_wine_e_label_remote_page_id',
        '_nutrition_labels_remote_page_url' => '_wine_e_label_remote_page_url',
        '_nutrition_labels_display_config' => '_wine_e_label_display_config',
    ];

    public static function run(): void
    {
        if (get_option(self::MIGRATION_OPTION) === self::MIGRATION_VERSION) {
            return;
        }

        self::migrate_options();
        self::migrate_short_url_table();
        self::migrate_import_directory();
        self::migrate_post_meta();

        update_option(self::MIGRATION_OPTION, self::MIGRATION_VERSION, false);
    }

    private static function migrate_options(): void
    {
        foreach (self::OPTION_MAP as $oldKey => $newKey) {
            $oldValue = get_option($oldKey, null);
            if ($oldValue === null) {
                continue;
            }

            $newValue = get_option($newKey, null);
            if ($newValue === null) {
                update_option($newKey, $oldValue, false);
            }

            delete_option($oldKey);
        }
    }

    private static function migrate_short_url_table(): void
    {
        global $wpdb;

        $oldTable = $wpdb->prefix . 'nutrition_short_urls';
        $newTable = $wpdb->prefix . 'wine_e_label_short_urls';

        $oldExists = self::table_exists($oldTable);
        $newExists = self::table_exists($newTable);

        if ($oldExists && !$newExists) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("RENAME TABLE {$oldTable} TO {$newTable}");
        }
    }

    private static function migrate_import_directory(): void
    {
        $uploads = wp_upload_dir();
        if (!empty($uploads['error'])) {
            return;
        }

        $oldDir = trailingslashit($uploads['basedir']) . 'nutrition-labels-imports';
        $newDir = trailingslashit($uploads['basedir']) . 'wine-e-label-imports';

        if (is_dir($oldDir) && !is_dir($newDir)) {
            @rename($oldDir, $newDir);
        }
    }

    private static function migrate_post_meta(): void
    {
        global $wpdb;

        $postmeta = $wpdb->postmeta;

        foreach (self::META_MAP as $oldKey => $newKey) {
            $insertSql = $wpdb->prepare(
                "INSERT INTO {$postmeta} (post_id, meta_key, meta_value)
                 SELECT oldmeta.post_id, %s, oldmeta.meta_value
                 FROM {$postmeta} AS oldmeta
                 LEFT JOIN {$postmeta} AS existing
                   ON existing.post_id = oldmeta.post_id
                  AND existing.meta_key = %s
                 WHERE oldmeta.meta_key = %s
                   AND existing.meta_id IS NULL",
                $newKey,
                $newKey,
                $oldKey
            );
            $wpdb->query($insertSql);

            if ($newKey === '_wine_e_label_source_file_url') {
                $updateUrlSql = $wpdb->prepare(
                    "UPDATE {$postmeta}
                        SET meta_value = REPLACE(meta_value, 'nutrition-labels-imports', 'wine-e-label-imports')
                      WHERE meta_key = %s",
                    $newKey
                );
                $wpdb->query($updateUrlSql);
            }

            if ($newKey === '_wine_e_label_source_file_path') {
                $updatePathSql = $wpdb->prepare(
                    "UPDATE {$postmeta}
                        SET meta_value = REPLACE(meta_value, 'nutrition-labels-imports', 'wine-e-label-imports')
                      WHERE meta_key = %s",
                    $newKey
                );
                $wpdb->query($updatePathSql);
            }

            $deleteSql = $wpdb->prepare(
                "DELETE FROM {$postmeta} WHERE meta_key = %s",
                $oldKey
            );
            $wpdb->query($deleteSql);
        }
    }

    private static function table_exists(string $tableName): bool
    {
        global $wpdb;

        $result = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $tableName));
        return $result === $tableName;
    }
}
