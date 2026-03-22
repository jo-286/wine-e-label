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

if (!current_user_can('manage_options')) {
  wp_die(__('Du hast keine Berechtigung, auf diese Seite zuzugreifen.', 'nutrition-labels'));
}

$search = isset($_GET['search']) ? sanitize_text_field((string) $_GET['search']) : '';
$page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
$per_page = 50;
$status_filter = isset($_GET['status_filter']) ? sanitize_text_field((string) $_GET['status_filter']) : '';
$year_filter = isset($_GET['year_filter']) ? sanitize_text_field((string) $_GET['year_filter']) : '';

$db = new NutritionLabels_DB_Extended();
$entries_raw = $search !== '' ? $db->search_entries($search, 5000, 1) : $db->get_all_entries(5000, 1);
$filtered_entries = [];
$available_years = [];

$format_target_url = static function (string $url): string {
  $host = (string) wp_parse_url($url, PHP_URL_HOST);
  $path = (string) wp_parse_url($url, PHP_URL_PATH);
  $query = (string) wp_parse_url($url, PHP_URL_QUERY);
  $display = $host . $path;
  if ($query !== '') {
    $display .= '?' . $query;
  }
  return $display !== '' ? $display : $url;
};

$build_filename_stub = static function (string $product_title, array $target): string {
  $kind = sanitize_key((string) ($target['kind'] ?? 'label'));
  $suffix = $kind !== '' ? '-' . $kind : '';
  return sanitize_file_name($product_title . $suffix);
};

foreach ($entries_raw as $entry) {
  $product_id = (int) $entry->product_id;
  $label_data = NutritionLabels_Importer::get_label_data($product_id);
  $built = !empty($label_data['built_at']);
  $has_import = !empty($label_data['source_file_name']);
  $manual = NutritionLabels_Manual_Builder::normalize_config($label_data['manual_config'] ?? []);
  $has_manual = NutritionLabels_Manual_Builder::has_meaningful_input($manual);
  $status_key = $built ? 'built' : ($has_import ? 'import' : ($has_manual ? 'manual' : 'draft'));
  $year_match_source = trim((string) ($label_data['title'] ?: ($manual['product']['bezeichnung'] ?? '') ?: get_the_title($product_id)));
  preg_match('/(19|20)\d{2}/', $year_match_source, $year_match);
  $year_value = $year_match[0] ?? '';

  if ($year_value !== '') {
    $available_years[$year_value] = $year_value;
  }

  if ($status_filter !== '' && $status_filter !== $status_key) {
    continue;
  }

  if ($year_filter !== '' && $year_filter !== $year_value) {
    continue;
  }

  $targets = NutritionLabels_URL::get_label_targets($product_id);
  if (empty($targets)) {
    $fallback_url = NutritionLabels_URL::get_short_url($product_id);
    if ($fallback_url) {
      $targets[] = [
        'kind' => 'main',
        'location_label' => 'Main Domain',
        'host' => (string) wp_parse_url((string) $fallback_url, PHP_URL_HOST),
        'display_name' => 'Main Domain - ' . (string) wp_parse_url((string) $fallback_url, PHP_URL_HOST),
        'url' => (string) $fallback_url,
        'is_primary' => true,
      ];
    }
  }

  foreach ($targets as $target) {
    if (empty($target['url'])) {
      continue;
    }

    $row = clone $entry;
    $row->label_status = $status_key;
    $row->label_year = $year_value;
    $row->target = $target;
    $row->target_pretty_url = $format_target_url((string) $target['url']);
    $row->filename_stub = $build_filename_stub((string) get_the_title($product_id), $target);
    $filtered_entries[] = $row;
  }
}

krsort($available_years);
$total = count($filtered_entries);
$offset = ($page - 1) * $per_page;
$entries = array_slice($filtered_entries, $offset, $per_page);

$status_labels = [
  'built' => __('E-Label erstellt', 'nutrition-labels'),
  'import' => __('Nur Import vorhanden', 'nutrition-labels'),
  'manual' => __('Nur manuelle Daten', 'nutrition-labels'),
  'draft' => __('Unvollstaendig', 'nutrition-labels'),
];
?>

<div class="wrap">
  <h1><?php esc_html_e('Wein E-Label - E-Labels', 'nutrition-labels'); ?></h1>

  <div class="nutrition-labels-toolbar">
    <form method="get" action="">
      <input type="hidden" name="page" value="<?php echo esc_attr(WINE_E_LABEL_ADMIN_PAGE_DB); ?>">
      <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Produktname oder Slug suchen ...', 'nutrition-labels'); ?>">
      <select name="status_filter">
        <option value=""><?php esc_html_e('Alle Status', 'nutrition-labels'); ?></option>
        <option value="built" <?php selected($status_filter, 'built'); ?>><?php esc_html_e('E-Label erstellt', 'nutrition-labels'); ?></option>
        <option value="import" <?php selected($status_filter, 'import'); ?>><?php esc_html_e('Nur Import vorhanden', 'nutrition-labels'); ?></option>
        <option value="manual" <?php selected($status_filter, 'manual'); ?>><?php esc_html_e('Nur manuelle Daten', 'nutrition-labels'); ?></option>
        <option value="draft" <?php selected($status_filter, 'draft'); ?>><?php esc_html_e('Unvollstaendig', 'nutrition-labels'); ?></option>
      </select>
      <select name="year_filter">
        <option value=""><?php esc_html_e('Alle Jahrgaenge', 'nutrition-labels'); ?></option>
        <?php foreach ($available_years as $year_value) : ?>
          <option value="<?php echo esc_attr($year_value); ?>" <?php selected($year_filter, $year_value); ?>><?php echo esc_html($year_value); ?></option>
        <?php endforeach; ?>
      </select>
      <input type="submit" value="<?php esc_attr_e('Filtern', 'nutrition-labels'); ?>" class="button button-primary">
      <a href="<?php echo esc_url(admin_url('admin.php?page=' . WINE_E_LABEL_ADMIN_PAGE_DB)); ?>" class="button"><?php esc_html_e('Zurücksetzen', 'nutrition-labels'); ?></a>
    </form>
  </div>

  <?php if (empty($entries)) : ?>
    <div class="notice notice-warning">
      <p><?php esc_html_e('Keine E-Label-Eintraege gefunden.', 'nutrition-labels'); ?></p>
    </div>
  <?php else : ?>
    <div class="nutrition-labels-table-wrapper">
      <p><?php printf(esc_html__('Zeige %1$d von %2$d Eintraegen', 'nutrition-labels'), count($entries), $total); ?></p>

      <form method="post" action="">
        <?php wp_nonce_field('nutrition_delete', '_wpnonce'); ?>
        <table class="wp-list-table widefat striped">
          <thead>
            <tr>
              <td class="manage-column column-cb check-column">
                <input type="checkbox" id="cb-select-all-1" onclick="toggleAllCheckboxes(this)">
          <label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e('Alle auswählen', 'nutrition-labels'); ?></label>
              </td>
              <th class="manage-column column-product"><?php esc_html_e('Produkt', 'nutrition-labels'); ?></th>
              <th class="column-link"><?php esc_html_e('Direkter Link', 'nutrition-labels'); ?></th>
              <th class="column-location"><?php esc_html_e('Ort', 'nutrition-labels'); ?></th>
              <th class="column-slug"><?php esc_html_e('Slug', 'nutrition-labels'); ?></th>
              <th class="column-year"><?php esc_html_e('Jahrgang', 'nutrition-labels'); ?></th>
              <th class="column-status"><?php esc_html_e('Status', 'nutrition-labels'); ?></th>
              <th class="column-created"><?php esc_html_e('Erstellt', 'nutrition-labels'); ?></th>
              <th class="column-actions"><?php esc_html_e('Aktionen', 'nutrition-labels'); ?></th>
              <th class="column-export"><?php esc_html_e('QR-Export', 'nutrition-labels'); ?></th>
              <th class="column-delete">&nbsp;</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($entries as $entry) : ?>
              <?php
              $target = is_array($entry->target ?? null) ? $entry->target : [];
              $target_kind = (string) ($target['kind'] ?? '');
              $target_url = (string) ($target['url'] ?? '');
              $target_host = (string) ($target['host'] ?? '');
              $target_location = (string) ($target['location_label'] ?? '');
              ?>
              <tr>
                <td class="check-column">
                  <input
                    id="cb-select-<?php echo esc_attr($entry->product_id . '-' . $target_kind); ?>"
                    type="checkbox"
                    name="product_ids[]"
                    value="<?php echo esc_attr((string) $entry->product_id); ?>"
                    data-product-id="<?php echo esc_attr((string) $entry->product_id); ?>"
                    data-target-kind="<?php echo esc_attr($target_kind); ?>"
                    data-filename-stub="<?php echo esc_attr((string) $entry->filename_stub); ?>">
                  <label class="screen-reader-text" for="cb-select-<?php echo esc_attr($entry->product_id . '-' . $target_kind); ?>"><?php esc_html_e('Auswählen', 'nutrition-labels'); ?></label>
                </td>
                <td class="column-product">
                  <strong><?php echo esc_html(get_the_title((int) $entry->product_id)); ?></strong>
                  <br>
                  <small>ID: <?php echo esc_html((string) $entry->product_id); ?></small>
                  <?php if (!empty($target['is_primary'])) : ?>
                    <div class="wel-target-badge"><?php esc_html_e('Primaeres Ziel', 'nutrition-labels'); ?></div>
                  <?php endif; ?>
                </td>
                <td class="column-link">
                  <?php if ($target_url !== '') : ?>
                    <a href="<?php echo esc_url($target_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html((string) $entry->target_pretty_url); ?></a>
                  <?php else : ?>
                    <span class="description">&mdash;</span>
                  <?php endif; ?>
                </td>
                <td class="column-location">
                  <div><?php echo esc_html($target_location !== '' ? $target_location : 'Main Domain'); ?></div>
                  <?php if ($target_host !== '') : ?>
                    <small><?php echo esc_html($target_host); ?></small>
                  <?php endif; ?>
                </td>
                <td class="column-slug">
                  <code>/<?php echo esc_html((string) $entry->short_code); ?></code>
                </td>
                <td class="column-year">
                  <?php echo esc_html((string) ($entry->label_year ?: '—')); ?>
                </td>
                <td class="column-status">
                  <?php echo esc_html($status_labels[$entry->label_status] ?? (string) $entry->label_status); ?>
                </td>
                <td class="column-created">
                  <?php echo esc_html(date('Y-m-d H:i', strtotime((string) $entry->created_at))); ?>
                </td>
                <td class="column-actions">
                  <div class="wel-db-actions">
                    <button type="button" class="button button-small" onclick="viewNutritionLabel('<?php echo esc_js($target_url); ?>')">
                        <?php esc_html_e('E-Label öffnen', 'nutrition-labels'); ?>
                    </button>
                    <button
                      type="button"
                      class="button button-small"
                      onclick="downloadQrCode(<?php echo esc_attr((string) $entry->product_id); ?>, '<?php echo esc_js($target_kind); ?>', this, '', '<?php echo esc_js((string) $entry->filename_stub); ?>')">
                      <?php esc_html_e('QR herunterladen', 'nutrition-labels'); ?>
                    </button>
                  </div>
                </td>
                <td class="column-export">
                  <select onchange="exportQrCode(<?php echo esc_attr((string) $entry->product_id); ?>, '<?php echo esc_js($target_kind); ?>', '<?php echo esc_js((string) $entry->filename_stub); ?>', this)">
                          <option value="">— <?php esc_html_e('Auswählen', 'nutrition-labels'); ?> —</option>
                    <?php foreach (NutritionLabels_URL::get_lang_names() as $code => $name) : ?>
                      <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td class="column-delete">
                  <button type="button" class="button button-small" onclick="deleteEntry(<?php echo esc_attr((string) $entry->product_id); ?>)">
                            <?php esc_html_e('Löschen', 'nutrition-labels'); ?>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="nutrition-labels-bulk-actions">
      <button type="button" id="bulk_delete" class="button button-primary"><?php esc_html_e('Auswahl löschen', 'nutrition-labels'); ?></button>
          <label for="bulk_qr_lang" class="screen-reader-text"><?php esc_html_e('QR-Export', 'nutrition-labels'); ?></label>
          <select id="bulk_qr_lang">
            <option value=""><?php esc_html_e('Standardsprache', 'nutrition-labels'); ?></option>
            <?php foreach (NutritionLabels_URL::get_lang_names() as $code => $name) : ?>
              <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
            <?php endforeach; ?>
          </select>
      <button type="button" id="bulk_download_qr" class="button"><?php esc_html_e('QR für Auswahl herunterladen', 'nutrition-labels'); ?></button>
          <a
            href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=' . WINE_E_LABEL_ADMIN_PAGE_DB . '&export=csv'), 'nutrition_labels_export')); ?>"
            class="button">
            <?php esc_html_e('Als CSV exportieren', 'nutrition-labels'); ?>
          </a>
        </div>
      </form>
    </div>

    <?php if ($total > $per_page) : ?>
      <div class="tablenav">
        <?php
        $current_url = add_query_arg([
          'search' => $search,
          'status_filter' => $status_filter,
          'year_filter' => $year_filter,
          'paged' => $page,
        ]);

        echo paginate_links([
          'base' => add_query_arg('paged', '%#%', $current_url),
          'format' => '',
      'prev_text' => __('&laquo; Zurück', 'nutrition-labels'),
          'next_text' => __('Weiter &raquo;', 'nutrition-labels'),
          'total' => max(1, (int) ceil($total / $per_page)),
          'current' => $page,
        ]);
        ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <script>
    if (typeof ajaxurl === 'undefined') {
      var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }

    function toggleAllCheckboxes(source) {
      var checkboxes = document.getElementsByName('product_ids[]');
      for (var i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
      }
    }

    function viewNutritionLabel(url) {
      if (!url) {
        return;
      }
      window.open(url);
    }

    function deleteEntry(productId) {
        if (confirm('<?php echo esc_js(__('Diesen E-Label-Eintrag löschen?\n\nDas Produkt selbst wird nicht gelöscht - nur die E-Label-Daten werden entfernt.\n\nDieser Vorgang kann nicht rückgängig gemacht werden.', 'nutrition-labels')); ?>')) {
        jQuery.ajax({
          url: ajaxurl,
          type: 'POST',
          data: {
            action: 'nutrition_delete',
            product_ids: [productId],
            _wpnonce: jQuery('input[name="_wpnonce"]').val()
          },
          success: function(response) {
            if (response.success) {
              alert(response.message || response.data.message);
              location.reload();
            } else {
              alert('Fehler: ' + (response.data || response.message));
            }
          },
          error: function() {
            alert('<?php echo esc_js(__('Fehler: Eintrag konnte nicht gelöscht werden.', 'nutrition-labels')); ?>');
            location.reload();
          }
        });
      }
    }

    var nutritionQrNonce = '<?php echo esc_js(wp_create_nonce('nutrition_qr_download')); ?>';

    function downloadQrCode(productId, targetKind, button, langCode, filenameStub) {
      langCode = langCode || '';
      targetKind = targetKind || '';
      filenameStub = filenameStub || '';
      var originalText = button ? button.textContent : '';
      if (button) {
        button.disabled = true;
        button.textContent = '<?php echo esc_js(esc_html__('Erzeuge...', 'nutrition-labels')); ?>';
      }

      jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'nutrition_qr_download',
          product_id: productId,
          target_kind: targetKind,
          lang_code: langCode,
          filename_stub: filenameStub,
          nonce: nutritionQrNonce
        },
        success: function(response) {
          if (response.success) {
            var link = document.createElement('a');
            link.href = response.data.url;
            link.download = response.data.filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert('<?php echo esc_js(esc_html__('Fehler:', 'nutrition-labels')); ?> ' + (response.data || '<?php echo esc_js(esc_html__('QR-Code konnte nicht erzeugt werden', 'nutrition-labels')); ?>'));
          }
        },
        error: function() {
          alert('<?php echo esc_js(esc_html__('Fehler: QR-Code konnte nicht erzeugt werden.', 'nutrition-labels')); ?>');
        },
        complete: function() {
          if (button) {
            button.disabled = false;
            button.textContent = originalText;
          }
        }
      });
    }

    function exportQrCode(productId, targetKind, filenameStub, select) {
      var langCode = select.value;
      if (!langCode) {
        return;
      }
      select.value = '';
      downloadQrCode(productId, targetKind, null, langCode, filenameStub);
    }

    jQuery(document).ready(function($) {
      $('#bulk_delete').click(function() {
        var seen = {};
        var selectedIds = $('input[name="product_ids[]"]:checked').map(function() {
          return $(this).data('product-id') || $(this).val();
        }).get().filter(function(productId) {
          if (!productId || seen[productId]) {
            return false;
          }
          seen[productId] = true;
          return true;
        });

        if (selectedIds.length === 0) {
      alert('<?php echo esc_js(__('Bitte mindestens einen Eintrag zum Löschen auswählen', 'nutrition-labels')); ?>');
          return;
        }

    if (confirm('<?php echo esc_js(__('Lösche ', 'nutrition-labels')); ?>' + selectedIds.length + '<?php echo esc_js(__(' E-Label-Einträge?\n\nDie Produkte selbst werden nicht gelöscht - nur die E-Label-Daten werden entfernt.\n\nDieser Vorgang kann nicht rückgängig gemacht werden.', 'nutrition-labels')); ?>')) {
          $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
              action: 'nutrition_delete',
              product_ids: selectedIds,
              _wpnonce: $('input[name="_wpnonce"]').val()
            },
            success: function(response) {
              if (response.success) {
                alert(response.message || response.data.message);
                location.reload();
              } else {
                alert('Fehler: ' + (response.data || response.message));
              }
            },
            error: function() {
          alert('<?php echo esc_js(__('Fehler: Einträge konnten nicht gelöscht werden.', 'nutrition-labels')); ?>');
              location.reload();
            }
          });
        }
      });

      $('#bulk_download_qr').click(function() {
        var selectedTargets = [];
        var seen = {};

        $('input[name="product_ids[]"]:checked').each(function() {
          var productId = String($(this).data('product-id') || $(this).val() || '');
          var targetKind = String($(this).data('target-kind') || '');
          var filenameStub = String($(this).data('filename-stub') || '');
          var key = productId + '::' + targetKind;
          if (!productId || seen[key]) {
            return;
          }
          seen[key] = true;
          selectedTargets.push({
            productId: productId,
            targetKind: targetKind,
            filenameStub: filenameStub
          });
        });

        if (selectedTargets.length === 0) {
      alert('<?php echo esc_js(__('Bitte mindestens einen Eintrag für den QR-Download auswählen', 'nutrition-labels')); ?>');
          return;
        }

        var langCode = $('#bulk_qr_lang').val() || '';
        var delay = 0;
        selectedTargets.forEach(function(target) {
          setTimeout(function() {
            downloadQrCode(target.productId, target.targetKind, null, langCode, target.filenameStub);
          }, delay);
          delay += 350;
        });
      });
    });
  </script>
</div>

<style>
  .nutrition-labels-toolbar {
    margin: 20px 0;
    padding: 15px;
    background: #fff;
    border: 1px solid #ccd;
    border-radius: 4px;
  }

  .nutrition-labels-toolbar form {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .nutrition-labels-toolbar input[type="text"] {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 220px;
  }

  .nutrition-labels-table-wrapper {
    background: #fff;
    padding: 15px;
    border: 1px solid #ccd;
    border-radius: 4px;
    margin-top: 20px;
  }

  .nutrition-labels-table-wrapper table code {
    background: #f9f9f9;
    padding: 2px 4px;
    border-radius: 3px;
  }

  .nutrition-labels-table-wrapper table.wp-list-table {
    table-layout: auto;
  }

  .nutrition-labels-bulk-actions {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
  }

  .wel-db-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .wel-target-badge {
    margin-top: 8px;
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 999px;
    background: #eef5ff;
    color: #244267;
    font-size: 12px;
    font-weight: 600;
  }

  #bulk_qr_lang,
  .column-export select {
    min-width: 170px;
  }

  th.column-product,
  td.column-product {
    width: 210px;
  }

  th.column-link,
  td.column-link {
    width: 270px;
  }

  th.column-location,
  td.column-location {
    width: 150px;
  }

  th.column-slug,
  td.column-slug {
    width: 110px;
  }

  th.column-year,
  td.column-year {
    width: 90px;
  }

  th.column-status,
  td.column-status {
    width: 130px;
  }

  th.column-created,
  td.column-created {
    width: 125px;
  }

  th.column-actions,
  td.column-actions {
    width: 220px;
  }

  th.column-export,
  td.column-export {
    width: 170px;
  }

  th.column-delete,
  td.column-delete {
    width: 90px;
  }

  td.column-location small {
    color: #646970;
    display: block;
    margin-top: 4px;
  }
</style>
