jQuery(function ($) {
    const cfg = window.wineELabelAdmin || {};
    const fileInput = $('#wine_e_label_import_file');
    const fileName = $('#wine_e_label_import_file_name');
    const dropzone = $('#wine_e_label_dropzone');
    const wineNr = $('#wine_e_label_wine_nr');
    const slug = $('#wine_e_label_slug');
    const title = $('#wine_e_label_title');
    const energy = $('#wine_e_label_energy');
    const carbs = $('#wine_e_label_carbs');
    const sugar = $('#wine_e_label_sugar');
    const minor = $('#wine_e_label_minor');
    const minorMode = $('#wine_e_label_minor_mode');
    const fat = $('#wine_e_label_fat');
    const saturates = $('#wine_e_label_saturates');
    const protein = $('#wine_e_label_protein');
    const salt = $('#wine_e_label_salt');
    const saltNatural = $('#wine_e_label_salt_natural');
    const ingredients = $('#wine_e_label_ingredients_html');
    const footnote = $('#wine_e_label_footnote');
    const pretable = $('#wine_e_label_pretable_notice');
    const suggestionBtn = $('#wine_e_label_apply_suggestion');
    const preview = $('#wine_e_label_url_preview');
    const importButton = $('#wine_e_label_confirm_import');
    const createButton = $('#wine_e_label_create_label');
    const deleteImportButton = $('#wine_e_label_delete_import');
    const deleteGeneratedButton = $('#wine_e_label_delete_generated');
    const manualCreateButton = $('#wine_e_label_manual_create');
    const manualDeleteGeneratedButton = $('#nlm_delete_generated');
    const footerWineNr = $('#nlm_footer_wine_nr');
    const footerSlug = $('#nlm_footer_slug');
    const publicLinkAnchor = $('#nlm_public_link_anchor');
    const publicLinkText = $('#nlm_public_link_text');
    const linkNotice = $('#nlm_link_notice');
    const manualQrWrap = $('#nlm_manual_qr_tools_wrap');
    const manualQrToggle = $('#nlm_qr_toggle');
    const manualQrPreview = $('#nlm_manual_qr_preview');
    const manualQrImage = $('#nlm_manual_qr_img');
    const sourceBadge = $('#nlm_source_badge');
    const sourceMeta = $('#nlm_source_meta');
    const sourceList = $('#nlm_source_list');
    const clearManualButton = $('#nlm_clear_manual_data');
    const resetAllButton = $('#nlm_reset_all_data');
    const copySourceButton = $('#nlm_copy_source_apply');
    const copySourceSelect = $('#nlm_copy_source_product');
    const copySlugButton = $('#nlm_copy_slug');
    const copyLinkButton = $('#nlm_copy_link');
    const openLinkButton = $('#nlm_open_link');
    const previewLangButtons = $('#nlm_preview_lang_switch button');
    const sidebarQrWrap = $('#nl_sidebar_qr_wrap');
    const sidebarQrImg = $('#nl_sidebar_qr_img');
    const previewQrWrap = $('#nlm_preview_qr_wrap');
    const previewQrImg = $('#nlm_preview_qr_img');
    const previewQrEmpty = $('#nlm_preview_qr_empty');
    const previewQrView = $('#nlm_preview_qr_view');
    const previewQrDownload = $('#nlm_preview_qr_download');
    const previewPageView = $('#nlm_preview_page_view');
    const displayImageInput = $('#nlm_display_custom_image_url');
    const displayImageAltInput = $('#nlm_display_custom_image_alt');
    const displayWineNameInput = $('#nlm_display_wine_name');
    const displayVintageInput = $('#nlm_display_vintage');
    const displaySubtitleInput = $('#nlm_display_subtitle');
    const displayImageThumb = $('#nlm_display_image_thumb');
    const displayImageThumbImg = $('#nlm_display_image_thumb_img');
    const displayImageDefaultNote = $('#nlm_display_image_default_note');
    const displayImageSelectButton = $('#nlm_display_image_select');
    const displayImageResetButton = $('#nlm_display_image_reset');
    const previewHeaderBlock = $('#nlm_preview_header_block');
    const previewLogoWrap = $('#nlm_preview_logo_wrap');
    const previewLogoImg = $('#nlm_preview_logo_img');
    const previewProductImageWrap = $('#nlm_preview_product_image_wrap');
    const previewProductImage = $('#nlm_preview_product_image');
    const previewVintage = $('#nlm_preview_vintage');
    const previewName = $('#nlm_preview_name');
    const previewSubtitle = $('#nlm_preview_subtitle');
    const previewProducerCard = $('#nlm_preview_producer_card');
    const previewRegionLabel = $('#nlm_preview_region_label');
    const previewCountryLabel = $('#nlm_preview_country_label');
    const previewAddressLabel = $('#nlm_preview_address_label');
    const previewRegionValue = $('#nlm_preview_region_value');
    const previewCountryValue = $('#nlm_preview_country_value');
    const previewAddressValue = $('#nlm_preview_address_value');

    let currentPreviewLang = cfg.previewDefaultLang || 'de';
    let currentImportSnapshot = cfg.importSnapshot || cfg.defaultConfig || {};
    let currentRenderedManualConfig = $.extend(true, {}, cfg.initialManualConfig || cfg.defaultConfig || {});
    let currentDisplayConfig = $.extend(true, {}, cfg.initialDisplayConfig || cfg.defaultDisplayConfig || {});
    let currentSourceFileName = fileName.text().trim() || '';
    let currentLabelExists = !!cfg.hasBuiltLabel;
    let currentLastImport = $('.nl-status-row strong').filter(function(){ return $(this).text().trim() === cfg.i18n.lastImportTitle; }).parent().next().text().trim() || '';
    let displayMediaFrame = null;

    function debounce(fn, wait) {
        let timeout = null;
        return function () {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function () { fn.apply(context, args); }, wait);
        };
    }

    function getDisplayDefaults() {
        return cfg.productDisplayDefaults || {};
    }

    function normalizeDisplayConfig(input) {
        input = input || {};
        return {
            custom_image_url: $.trim(input.custom_image_url || ''),
            custom_image_alt: $.trim(input.custom_image_alt || ''),
            wine_name: $.trim(input.wine_name || ''),
            vintage: $.trim(input.vintage || ''),
            subtitle: $.trim(input.subtitle || '')
        };
    }

    function getDisplayConfigFromForm() {
        return normalizeDisplayConfig({
            custom_image_url: displayImageInput.val(),
            custom_image_alt: displayImageAltInput.val(),
            wine_name: displayWineNameInput.val(),
            vintage: displayVintageInput.val(),
            subtitle: displaySubtitleInput.val()
        });
    }

    function resolveDisplayPresentation(config) {
        const normalized = normalizeDisplayConfig(config);
        const defaults = getDisplayDefaults();
        const productImageUrl = normalized.custom_image_url || $.trim(defaults.product_image_url || '');
        const productImageAlt = normalized.custom_image_alt || $.trim(defaults.product_image_alt || '') || $.trim(normalized.wine_name || defaults.wine_name || '');

        return {
            product_image_url: productImageUrl,
            product_image_alt: productImageAlt,
            wine_name: normalized.wine_name || $.trim(defaults.wine_name || ''),
            vintage: normalized.vintage || $.trim(defaults.vintage || ''),
            subtitle: normalized.subtitle || $.trim(defaults.subtitle || ''),
            usesCustomImage: normalized.custom_image_url !== ''
        };
    }

    function setPreviewBlock($el, visible, value) {
        if (!$el.length) return;
        $el.toggleClass('nlm-hidden', !visible);
        if (typeof value !== 'undefined') {
            $el.text(value || '');
        }
    }

    function setPreviewMultiline($el, value) {
        if (!$el.length) return;
        const text = String(value || '');
        $el.html(text ? text.replace(/\n/g, '<br>') : '');
    }

    function syncDisplayThumb(presentation) {
        if (!displayImageThumb.length) return;
        const imageUrl = $.trim((presentation && presentation.product_image_url) || '');
        const imageAlt = $.trim((presentation && presentation.product_image_alt) || '');
        const usesCustom = !!(presentation && presentation.usesCustomImage);

        displayImageThumb.toggleClass('is-empty', !imageUrl);
        displayImageThumbImg.attr('src', imageUrl).attr('alt', imageAlt);
        if (displayImageDefaultNote.length) {
            displayImageDefaultNote.text(usesCustom ? (cfg.customImageOverrideHint || '') : (cfg.customImageDefaultHint || ''));
        }
    }

    function syncPresentationPreview() {
        const settings = cfg.designSettings || {};
        const presentation = resolveDisplayPresentation(getDisplayConfigFromForm());
        const producerLabels = ((cfg.producerLabels || {})[currentPreviewLang]) || ((cfg.producerLabels || {}).de) || {};
        const baseFontSize = (parseInt(settings.base_font_size || 15, 10) || 15) + 'px';
        const smallFontSize = (parseInt(settings.small_font_size || 14, 10) || 14) + 'px';
        const regionValue = $.trim(settings.producer_region || '');
        const countryValue = $.trim(settings.producer_country || '');
        const addressValue = $.trim(settings.producer_address || '');
        const logoEnabled = String(settings.logo_enabled || '0') === '1' && $.trim(settings.logo_url || '') !== '';
        const productImageEnabled = String(settings.product_image_enabled || '0') === '1' && presentation.product_image_url !== '';
        const vintageEnabled = String(settings.vintage_enabled || '0') === '1' && presentation.vintage !== '';
        const wineNameEnabled = String(settings.wine_name_enabled || '0') === '1' && presentation.wine_name !== '';
        const subtitleEnabled = String(settings.subtitle_enabled || '0') === '1' && presentation.subtitle !== '';
        const producerVisible = regionValue !== '' || countryValue !== '' || addressValue !== '';

        $('.nlm-phone-screen').css({
            'font-family': settings.font_family || '',
            'color': settings.text_color || ''
        });
        $('.nlm-preview-table').css('font-size', baseFontSize);

        if (previewLogoWrap.length) {
            previewLogoWrap.toggleClass('nlm-hidden', !logoEnabled);
            previewLogoImg.attr('src', $.trim(settings.logo_url || '')).attr('alt', $.trim(settings.logo_alt || '') || 'Logo');
            previewLogoImg.css('max-height', (parseInt(settings.logo_max_height || 110, 10) || 110) + 'px');
        }
        if (previewProductImageWrap.length) {
            previewProductImageWrap.toggleClass('nlm-hidden', !productImageEnabled);
            previewProductImage.attr('src', presentation.product_image_url).attr('alt', presentation.product_image_alt || presentation.wine_name || '');
            previewProductImage.css('max-height', (parseInt(settings.product_image_max_height || 200, 10) || 200) + 'px');
        }
        setPreviewBlock(previewVintage, vintageEnabled, presentation.vintage);
        setPreviewBlock(previewName, wineNameEnabled, presentation.wine_name);
        setPreviewBlock(previewSubtitle, subtitleEnabled, presentation.subtitle);
        previewVintage.css('font-size', (parseInt(settings.vintage_size || 17, 10) || 17) + 'px');
        previewName.css('font-size', (parseInt(settings.wine_name_size || 28, 10) || 28) + 'px');
        previewSubtitle.css('font-size', (parseInt(settings.subtitle_size || 20, 10) || 20) + 'px');
        if (previewHeaderBlock.length) {
            previewHeaderBlock.toggleClass('nlm-hidden', !(logoEnabled || productImageEnabled || vintageEnabled || wineNameEnabled || subtitleEnabled));
        }

        if (previewProducerCard.length) {
            previewProducerCard.toggleClass('nlm-hidden', !producerVisible);
            previewProducerCard.css('font-family', settings.font_family || '');
            previewProducerCard.find('.nlm-preview-producer-label,.nlm-preview-producer-value').css('font-size', smallFontSize);
            previewRegionLabel.text(producerLabels.region || 'Anbaugebiet');
            previewCountryLabel.text(producerLabels.country || 'Land');
            previewAddressLabel.text(producerLabels.address || 'Adresse');
            previewRegionValue.text(regionValue);
            previewCountryValue.text(countryValue);
            setPreviewMultiline(previewAddressValue, addressValue);
            previewRegionValue.closest('.nlm-preview-producer-item').toggleClass('nlm-hidden', regionValue === '');
            previewCountryValue.closest('.nlm-preview-producer-item').toggleClass('nlm-hidden', countryValue === '');
            previewAddressValue.closest('.nlm-preview-producer-item').toggleClass('nlm-hidden', addressValue === '');
        }

        syncDisplayThumb(presentation);
    }

    function applyDisplayConfig(config) {
        currentDisplayConfig = normalizeDisplayConfig(config || cfg.defaultDisplayConfig || {});
        if (displayImageInput.length) displayImageInput.val(currentDisplayConfig.custom_image_url);
        if (displayImageAltInput.length) displayImageAltInput.val(currentDisplayConfig.custom_image_alt);
        if (displayWineNameInput.length) displayWineNameInput.val(currentDisplayConfig.wine_name);
        if (displayVintageInput.length) displayVintageInput.val(currentDisplayConfig.vintage);
        if (displaySubtitleInput.length) displaySubtitleInput.val(currentDisplayConfig.subtitle);
        syncPresentationPreview();
    }


    function configHasMeaningfulData(config) {
        const flat = flattenConfig(config || {});
        return Object.keys(flat).some(function (key) {
            const value = String(flat[key] == null ? '' : flat[key]).trim();
            return value !== '' && value !== '0';
        });
    }

    function getCreateButtonLabel() {
        return currentLabelExists ? (cfg.i18n.updateButton || cfg.i18n.createButton) : cfg.i18n.createButton;
    }

    function refreshCreateButtons() {
        const label = getCreateButtonLabel();
        createButton.text(label);
        manualCreateButton.text(label);
    }

    function normalizeSlug(value) {
        const map = { 'ä': 'ae', 'ö': 'oe', 'ü': 'ue', 'ß': 'ss', 'Ä': 'ae', 'Ö': 'oe', 'Ü': 'ue' };
        value = (value || '').replace(/[äöüÄÖÜß]/g, c => map[c] || c).toLowerCase();
        value = value.replace(/[^a-z0-9\s\-/]+/g, '');
        value = value.replace(/[\/_]/g, '-');
        value = value.replace(/\s+/g, '-');
        value = value.replace(/-+/g, '-');
        return value.replace(/^-|-$/g, '');
    }

    function parseNum(value) {
        value = String(value || '').replace(/\s+/g, '').replace(',', '.');
        const m = value.match(/-?\d+(?:\.\d+)?/);
        return m ? parseFloat(m[0]) : 0;
    }

    function formatNum(value, decimals = 1) {
        if (!isFinite(value) || Math.abs(value) < 0.000001) return '0';
        let out = value.toFixed(decimals).replace('.', ',');
        out = out.replace(/,0+$/, '').replace(/(,\d*[1-9])0+$/, '$1');
        return out;
    }

    function withLangUrl(url, lang) {
        if (!url) return '';
        try {
            const parsed = new URL(url, window.location.origin);
            if (lang && lang !== 'de') {
                parsed.searchParams.set('lang', lang);
            } else {
                parsed.searchParams.delete('lang');
            }
            return parsed.toString();
        } catch (e) {
            return url;
        }
    }

    function currentPreviewText(currentSlug) {
        const base = cfg.baseUrl || '';
        if (!base) return cfg.i18n.baseUrlMissing;
        if (!currentSlug) return cfg.i18n.previewEmpty || '—';

        let url = base.replace(/\/$/, '') + '/' + currentSlug;
        if (cfg.isExternalReceiverMode) {
            url += '/';
        }
        return withLangUrl(url, currentPreviewLang);
    }

    function getBundle() {
        return cfg.languageBundle || {};
    }

    function translateCatalogLabel(label, lang) {
        const items = (getBundle().items || {});
        if (items[label]) return items[label][lang] || items[label].de || label;
        return label;
    }

    function getAndOrSeparator(lang) {
        const sep = (((getBundle().separators || {}).and_or) || {});
        return sep[lang] || sep.de || ' und/oder ';
    }

    function getOrganicFootnote(lang) {
        const foot = (((getBundle().footnotes || {}).organic) || {});
        return foot[lang] || foot.de || '* aus ökologischer Erzeugung';
    }

    function getGroupPrefix(groupKey, fallback, lang) {
        const prefixes = (getBundle().prefixes || {});
        if (prefixes[groupKey]) return prefixes[groupKey][lang] || prefixes[groupKey].de || fallback;
        return fallback;
    }

    function getCatalog() {
        return cfg.catalog || {};
    }

    function copyText(value, successMessage) {
        if (!value) return;
        navigator.clipboard.writeText(value).then(function () {
            window.console && console.log(successMessage || 'copied');
        }).catch(function () {});
    }

    function setSlugValue(value, source) {
        const normalized = normalizeSlug(value || '');
        if (source !== 'sidebar' && slug.length) slug.val(normalized);
        if (source !== 'footer' && footerSlug.length) footerSlug.val(normalized);
        return normalized;
    }

    function setWineNrValue(value, source) {
        const next = value || '';
        if (source !== 'sidebar' && wineNr.length) wineNr.val(next);
        if (source !== 'manual' && $('#nlm_wein_nr').length) $('#nlm_wein_nr').val(next);
        if (source !== 'footer' && footerWineNr.length) footerWineNr.val(next);
        return next;
    }

    function setManualLink(text, url, clickable) {
        if (!publicLinkAnchor.length || !publicLinkText.length) return;
        publicLinkText.text(text || '');
        if (clickable && url) {
            publicLinkAnchor.attr('href', url).removeAttr('aria-disabled tabindex').removeClass('is-disabled');
            openLinkButton.prop('disabled', false).data('href', url);
            if (previewPageView.length) previewPageView.prop('disabled', false).data('href', url);
        } else {
            publicLinkAnchor.attr('href', '#').attr('aria-disabled', 'true').attr('tabindex', '-1').addClass('is-disabled');
            openLinkButton.prop('disabled', true).removeData('href');
            if (previewPageView.length) previewPageView.prop('disabled', true).removeData('href');
        }
    }

    function setLinkNotice(message, level) {
        if (!linkNotice.length) return;
        linkNotice.removeClass('is-ok is-error is-pending').addClass('is-' + (level || 'pending')).text(message || '');
    }

    function setManualQrState(previewData, visible) {
        if (sidebarQrWrap.length) {
            if (visible && previewData) {
                sidebarQrImg.attr('src', previewData).removeClass('nl-hidden');
                sidebarQrWrap.removeClass('nl-hidden');
            } else {
                sidebarQrImg.attr('src', '').addClass('nl-hidden');
                sidebarQrWrap.addClass('nl-hidden');
            }
        }
        if (previewQrWrap.length) {
            if (visible && previewData) {
                previewQrImg.attr('src', previewData).removeClass('nlm-hidden');
                if (previewQrEmpty.length) {
                    previewQrEmpty.addClass('nlm-hidden').text(cfg.i18n.pendingQr || 'Noch kein QR-Code erzeugt.');
                }
                previewQrView.prop('disabled', false).data('href', previewData);
                previewQrDownload.prop('disabled', false);
            } else {
                previewQrImg.attr('src', '').addClass('nlm-hidden');
                if (previewQrEmpty.length) {
                    previewQrEmpty.removeClass('nlm-hidden').text(cfg.i18n.pendingQr || 'Noch kein QR-Code erzeugt.');
                }
                previewQrView.prop('disabled', true).removeData('href');
                previewQrDownload.prop('disabled', true);
            }
        }
    }

    function updatePreviewUrl(source) {
        const currentSlug = setSlugValue((source === 'footer' && footerSlug.length) ? footerSlug.val() : slug.val(), source || '');
        const text = currentPreviewText(currentSlug);
        if (preview.length) preview.text(text);
        setManualLink(text, '', false);
        updateSlugQuality(currentSlug);
        if (!currentSlug) { setManualQrState('', false); }
        return currentSlug;
    }

    function updateSuggestionButton() {
        const suggestion = normalizeSlug(wineNr.val() || footerWineNr.val() || $('#nlm_wein_nr').val());
        suggestionBtn.attr('data-suggestion', suggestion);
    }

    function flattenConfig(config, prefix, out) {
        out = out || {};
        prefix = prefix || '';
        if ($.isArray(config) || $.isPlainObject(config)) {
            $.each(config, function (key, value) {
                const next = prefix ? prefix + '.' + key : key;
                flattenConfig(value, next, out);
            });
            return out;
        }
        out[prefix] = String(config == null ? '' : config);
        return out;
    }

    function getFieldLabels() {
        return cfg.fieldLabels || {};
    }

    function isEffectivelyEmptyValue(value) {
        value = String(value == null ? '' : value);
        return value === '' || value === '0';
    }

    function shouldIgnoreDiffKey(key, currentConfig, snapshotConfig) {
        let match = key.match(/^groups\.([^\.]+)\.mode$/);
        if (match) {
            const groupKey = match[1];
            const currentEnabled = String((((currentConfig || {}).groups || {})[groupKey] || {}).enabled || '0');
            const snapshotEnabled = String((((snapshotConfig || {}).groups || {})[groupKey] || {}).enabled || '0');
            if (groupKey !== 'base' && currentEnabled !== '1' && snapshotEnabled !== '1') return true;
        }

        match = key.match(/^groups\.([^\.]+)\.items\.([^\.]+)\.(bio|enumber)$/);
        if (match) {
            const groupKey = match[1];
            const itemKey = match[2];
            const currentSelected = String((((((currentConfig || {}).groups || {})[groupKey] || {}).items || {})[itemKey] || {}).selected || '0');
            const snapshotSelected = String((((((snapshotConfig || {}).groups || {})[groupKey] || {}).items || {})[itemKey] || {}).selected || '0');
            if (currentSelected !== '1' && snapshotSelected !== '1') return true;
        }

        match = key.match(/^groups\.([^\.]+)\.custom_items\.(\d+)\.(label|e|enumber)$/);
        if (match) {
            if (String(match[3]) !== 'label') return true;
            const groupKey = match[1];
            const index = parseInt(match[2], 10);
            const currentSelected = String((((((currentConfig || {}).groups || {})[groupKey] || {}).custom_items || [])[index] || {}).selected || '0');
            const snapshotSelected = String((((((snapshotConfig || {}).groups || {})[groupKey] || {}).custom_items || [])[index] || {}).selected || '0');
            if (currentSelected !== '1' && snapshotSelected !== '1') return true;
        }

        return false;
    }

    function compareAgainstSnapshot(config) {
        const labels = getFieldLabels();
        const now = flattenConfig(config || {});
        const snap = flattenConfig(currentImportSnapshot || {});
        if (!configHasMeaningfulData(currentImportSnapshot || {})) {
            return { changed: 0, added: 0, removed: 0, names: [] };
        }
        let changed = 0;
        let added = 0;
        let removed = 0;
        const names = [];
        const keys = [...new Set(Object.keys(now).concat(Object.keys(snap)))];
        keys.forEach(function (key) {
            if (shouldIgnoreDiffKey(key, config || {}, cfg.defaultConfig || {})) return;
            const value = String(now[key] || '');
            const oldValue = String(snap[key] || '');
            if (value === oldValue) return;
            const valueEmpty = isEffectivelyEmptyValue(value);
            const oldEmpty = isEffectivelyEmptyValue(oldValue);
            if (valueEmpty && oldEmpty) {
                return;
            }
            if (!valueEmpty && oldEmpty) {
                added += 1;
            } else if (valueEmpty && !oldEmpty) {
                removed += 1;
            } else {
                changed += 1;
            }
            names.push(labels[key] || key);
        });
        return { changed, added, removed, names: [...new Set(names)] };
    }

    function compareAgainstDefaults(config) {
        const labels = getFieldLabels();
        const now = flattenConfig(config || {});
        const snap = flattenConfig(cfg.defaultConfig || {});
        let changed = 0;
        let added = 0;
        let removed = 0;
        const names = [];
        const keys = [...new Set(Object.keys(now).concat(Object.keys(snap)))];
        keys.forEach(function (key) {
            if (shouldIgnoreDiffKey(key, config || {}, cfg.defaultConfig || {})) return;
            const value = String(now[key] || '');
            const oldValue = String(snap[key] || '');
            if (value === oldValue) return;
            const valueEmpty = isEffectivelyEmptyValue(value);
            const oldEmpty = isEffectivelyEmptyValue(oldValue);
            if (valueEmpty && oldEmpty) {
                return;
            }
            if (!valueEmpty && oldEmpty) {
                added += 1;
            } else if (valueEmpty && !oldEmpty) {
                removed += 1;
            } else {
                changed += 1;
            }
            names.push(labels[key] || key);
        });
        return { changed, added, removed, names: [...new Set(names)] };
    }

    function hasManualOverrides(config) {
        config = config || getManualConfigFromForm();
        if (configHasMeaningfulData(currentImportSnapshot || {})) {
            const diff = compareAgainstSnapshot(config);
            return (diff.changed + diff.added + diff.removed) > 0;
        }
        const diff = compareAgainstDefaults(config);
        return (diff.changed + diff.added + diff.removed) > 0;
    }

    function ensureImportSnapshotBaseline() {
        if (currentSourceFileName && currentSourceFileName !== cfg.i18n.noFileChosen && !configHasMeaningfulData(currentImportSnapshot || {})) {
            currentImportSnapshot = getManualConfigFromForm();
        }
    }

    function setStatusRow(titleText, statusText, cssClass, linkHtml = '') {
        $('.nl-status-row').each(function () {
            const strong = $(this).find('strong').first().text().trim();
            if (strong === titleText) {
                const second = $(this).children().eq(1);
                second.removeClass('nl-status-ok nl-status-error nl-status-pending').addClass(cssClass).text(statusText);
                let linkWrap = $(this).find('.nl-inline-link');
                if (!linkWrap.length && linkHtml) linkWrap = $('<div class="nl-inline-link"></div>').appendTo($(this));
                if (linkWrap.length) linkHtml ? linkWrap.html(linkHtml) : linkWrap.remove();
            }
        });
    }

    function setStatusRowInBox(boxSelector, titleText, statusText, cssClass, linkHtml = '') {
        const box = $(boxSelector).first();
        if (!box.length) {
            return;
        }
        box.find('.nl-status-row').each(function () {
            const strong = $(this).find('strong').first().text().trim();
            if (strong === titleText) {
                const second = $(this).children().eq(1);
                second.removeClass('nl-status-ok nl-status-error nl-status-pending').addClass(cssClass).text(statusText);
                let linkWrap = $(this).find('.nl-inline-link');
                if (!linkWrap.length && linkHtml) linkWrap = $('<div class="nl-inline-link"></div>').appendTo($(this));
                if (linkWrap.length) linkHtml ? linkWrap.html(linkHtml) : linkWrap.remove();
            }
        });
    }

    function updateLastImport(value) {
        let found = false;
        $('.nl-status-row').each(function () {
            const strong = $(this).find('strong').first().text().trim();
            if (strong === cfg.i18n.lastImportTitle) {
                $(this).children().eq(1).text(value || '');
                found = true;
            }
        });
        if (!found && value) $('.nl-status').append('<div class="nl-status-row"><div><strong>' + cfg.i18n.lastImportTitle + '</strong></div><div>' + value + '</div></div>');
        currentLastImport = value || '';
    }

    function removeLegacyStatusQrPreviews() {
        $('.nl-status .nl-qr-preview').remove();
        $('.nl-status img[src^="data:image"]').each(function () {
            const img = $(this);
            if (img.hasClass('nl-status-qr-image')) {
                return;
            }
            if (img.closest('.nl-inline-preview').length) {
                return;
            }
            if (!img.closest('#nlm_preview_qr_wrap').length) {
                const legacyWrap = img.closest('.nl-qr-preview');
                if (legacyWrap.length) {
                    legacyWrap.remove();
                } else {
                    img.remove();
                }
            }
        });
    }

    function clearBuildStatuses() {
        setStatusRow(cfg.i18n.pageTitle, cfg.i18n.pendingPage, 'nl-status-pending');
        setStatusRow(cfg.i18n.qrTitle, cfg.i18n.pendingQr, 'nl-status-pending');
        removeLegacyStatusQrPreviews();
        setManualLink(currentPreviewText(slug.val() || footerSlug.val()), '', false);
        setManualQrState('', false);
        setLinkNotice(cfg.i18n.pendingPage, 'pending');
        setWorkflowState('page', cfg.i18n.stateOpen, 'nlm-pending');
        setWorkflowState('qr', cfg.i18n.stateOpen, 'nlm-pending');
    }

    function setWorkflowState(key, label, className) {
        $('#nlm_state_' + key).attr('class', className).text(label);
    }

    function getSlugQuality(slugValue) {
        const length = (slugValue || '').length;
        if (!slugValue) {
            return { label: cfg.i18n.slugMissingLabel, text: cfg.i18n.slugMissingText, badgeClass: 'err' };
        }
        if (length <= 16) {
            return { label: cfg.i18n.qrCompactLabel, text: cfg.i18n.qrCompactText, badgeClass: 'ok' };
        }
        if (length <= 28) {
            return { label: cfg.i18n.slugOkLabel, text: cfg.i18n.slugOkText, badgeClass: 'warn' };
        }
        return { label: cfg.i18n.slugLongLabel, text: cfg.i18n.slugLongText, badgeClass: 'err' };
    }

    function updateSlugQuality(slugValue) {
        const quality = getSlugQuality(slugValue);
        $('#nlm_slug_quality_badge').removeClass('ok warn err').addClass(quality.badgeClass).text(quality.label);
        $('#nlm_slug_quality_text').text(quality.text);
    }

    function getManualConfigFromForm() {
        const config = $.extend(true, {}, currentRenderedManualConfig || cfg.defaultConfig || {});
        config.product.bezeichnung = $('#nlm_bezeichnung').val() || '';
        config.product.wein_nr = $('#nlm_wein_nr').val() || '';
        config.product.ap_nr = $('#nlm_ap_nr').val() || '';
        config.product.kategorie = $('#nlm_kategorie').val() || '';
        config.nutrition.alkohol_gl = $('#nlm_alkohol_gl').val() || '';
        config.nutrition.restzucker_gl = $('#nlm_restzucker').val() || '';
        config.nutrition.gesamtsaeure_gl = $('#nlm_gesamtsaeure').val() || '';
        config.nutrition.glycerin_mode = $('input[name="wine_e_label_manual[nutrition][glycerin_mode]"]:checked').val() || 'standard';
        config.nutrition.glycerin_manual = $('#nlm_glycerin_manual').val() || '';
        config.nutrition.restwerte_mode = $('input[name="wine_e_label_manual[nutrition][restwerte_mode]"]:checked').val() || 'text';
        config.nutrition.fat = $('#nlm_fat').val() || '';
        config.nutrition.saturates = $('#nlm_saturates').val() || '';
        config.nutrition.protein = $('#nlm_protein').val() || '';
        config.nutrition.salt = $('#nlm_salt').val() || '';
        config.nutrition.salt_natural = $('input[name="wine_e_label_manual[nutrition][salt_natural]"]').is(':checked') ? '1' : '0';

        $('[name^="wine_e_label_manual[groups]"]').filter(':checkbox,:radio').each(function () {
            const match = this.name.match(/^wine_e_label_manual\[groups\]\[([^\]]+)\](?:\[(enabled|mode|items)\](?:\[([^\]]+)\])?(?:\[([^\]]+)\])?)?$/);
            if (!match) return;
        });

        $('[name^="wine_e_label_manual[groups]"]').each(function () {
            const name = this.name;
            const value = (this.type === 'checkbox') ? ($(this).is(':checked') ? ($(this).val() || '1') : null) : (this.type === 'radio' ? ($(this).is(':checked') ? ($(this).val() || '1') : null) : ($(this).val() || ''));
            if (value === null) return;
            const parts = [];
            name.replace(/\[([^\]]+)\]/g, function (_, key) { parts.push(key); return ''; });
            if (parts.shift() !== 'groups') return;
            let ref = config.groups;
            for (let i = 0; i < parts.length - 1; i += 1) {
                if (typeof ref[parts[i]] === 'undefined') ref[parts[i]] = {};
                ref = ref[parts[i]];
            }
            ref[parts[parts.length - 1]] = value;
        });

        return config;
    }

    function updateSourceSummary() {
        const config = getManualConfigFromForm();
        const diff = compareAgainstSnapshot(config);
        const hasImport = !!currentSourceFileName && currentSourceFileName !== cfg.i18n.noFileChosen;
        const hasManual = hasManualOverrides(config);
        let label = cfg.i18n.sourceNone;
        let badgeClass = 'err';
        if (hasImport && (diff.changed > 0 || diff.added > 0 || diff.removed > 0)) {
            label = cfg.i18n.sourceImportManual;
            badgeClass = 'warn';
        } else if (hasImport) {
            label = cfg.i18n.sourceImportOnly || 'WIPZN-Import';
            badgeClass = 'ok';
        } else if (hasManual) {
            label = cfg.i18n.sourceManual;
            badgeClass = 'ok';
        }
        sourceBadge.removeClass('ok warn err').addClass(badgeClass).text(label);
        sourceMeta.text(currentLastImport ? cfg.i18n.lastImportPrefix.replace('%s', currentLastImport) : cfg.i18n.currentFormState);
        const lines = [];
        if (hasImport) lines.push(cfg.i18n.sourceFilePrefix.replace('%s', currentSourceFileName));
        if (diff.changed > 0) lines.push(cfg.i18n.manualChangedPrefix.replace('%d', diff.changed));
        if (diff.added > 0) lines.push(cfg.i18n.manualAddedPrefix.replace('%d', diff.added));
        if (diff.removed > 0) lines.push((cfg.i18n.manualClearedPrefix || '').replace('%d', diff.removed));
        if (diff.changed > 0 || diff.added > 0 || diff.removed > 0) {
            diff.names.slice(0, 4).forEach(function (name) { lines.push(name); });
        }
        if (!lines.length) lines.push(cfg.i18n.noDifferences);
        sourceList.empty();
        lines.forEach(function (line) { sourceList.append($('<li/>').text(line)); });
    }

    function buildIngredientsPreview(config, lang) {
        config = config || getManualConfigFromForm();
        lang = lang || 'de';
        const catalog = getCatalog();
        let parts = [];
        let hasBio = false;
        $.each(catalog, function (groupKey, group) {
            const groupState = ((config.groups || {})[groupKey]) || {};
            const enabled = groupKey === 'base' || (groupState.enabled || '0') === '1';
            if (!enabled) return;
            const mode = groupState.mode || 'list';
            const items = [];
            $.each(group.items || {}, function (itemKey, item) {
                const state = ((groupState.items || {})[itemKey]) || {};
                if ((state.selected || '0') !== '1') return;
                const category = (((config.product || {}).kategorie) || '');
                if (groupKey !== 'other' && $.isArray(item.categories) && item.categories.length && (!category || item.categories.indexOf(category) === -1)) return;
                const showEnumber = (state.enumber || '0') === '1' && item.e && !item.allergen;
                let label = showEnumber ? item.e : translateCatalogLabel(item.label || '', lang);
                if (item.bio && (state.bio || '0') === '1') {
                    label += '*';
                    hasBio = true;
                }
                items.push(label);
            });
            $.each(groupState.custom_items || [], function (_, customItem) {
                if ((customItem.selected || '0') !== '1') return;
                const label = $.trim(customItem.label || '');
                const eNumber = $.trim(customItem.e || '').replace(/\s+/g, '').toUpperCase();
                const showEnumber = (customItem.enumber || '0') === '1' && eNumber;
                if (!label && !eNumber) return;
                items.push(showEnumber ? eNumber : (label || eNumber));
            });
            if (!items.length) return;
            if (group.supports_mode && mode === 'alternative') {
                const fallbackPrefix = (group.alt_prefix || ((group.label || '') + ': enthält '));
                parts.push(getGroupPrefix(groupKey, fallbackPrefix, lang) + items.join(getAndOrSeparator(lang)));
            } else {
                parts = parts.concat(items);
            }
        });
        return {
            text: parts.join(', '),
            footnote: hasBio ? getOrganicFootnote(lang) : ''
        };
    }


    function calculateManualValues() {
        const alcoholGl = parseNum($('#nlm_alkohol_gl').val());
        const restzucker = parseNum($('#nlm_restzucker').val());
        const mode = $('input[name="wine_e_label_manual[nutrition][glycerin_mode]"]:checked').val() || 'standard';
        const glycerin = mode === 'edelsuess' ? 25 : (mode === 'manual' ? parseNum($('#nlm_glycerin_manual').val()) : alcoholGl * 0.1);
        const alcoholVol = alcoholGl > 0 ? alcoholGl / 7.89 : 0;
        const sugar100 = restzucker / 10;
        const glycerin100 = glycerin / 10;
        const alcohol100 = alcoholGl / 10;
        const carbs100 = sugar100 + glycerin100;
        const kj = alcohol100 * 29 + sugar100 * 17 + glycerin100 * 10;
        const kcal = alcohol100 * 7 + sugar100 * 4 + glycerin100 * 2.4;

        $('#nlm_alkohol_vol').text(formatNum(alcoholVol));
        $('#nlm_glycerin_effective').text(formatNum(glycerin) + ' g/l');
        $('#nlm_energy_preview').text(formatNum(kj) + ' kJ / ' + formatNum(kcal) + ' kcal');
        $('#nlm_carbs_preview').text(formatNum(carbs100) + ' g');
        $('#nlm_sugar_preview').text(formatNum(sugar100) + ' g');

        energy.val(formatNum(kj) + ' kJ / ' + formatNum(kcal) + ' kcal');
        carbs.val(formatNum(carbs100) + ' g');
        sugar.val(formatNum(sugar100) + ' g');
        fat.val($('#nlm_fat').val() || '');
        saturates.val($('#nlm_saturates').val() || '');
        protein.val($('#nlm_protein').val() || '');
        salt.val($('#nlm_salt').val() || '');
        saltNatural.val($('input[name="wine_e_label_manual[nutrition][salt_natural]"]').is(':checked') ? '1' : '0');
        const restMode = $('input[name="wine_e_label_manual[nutrition][restwerte_mode]"]:checked').val() || 'text';
        minorMode.val(restMode);
        minor.val(restMode === 'text' ? 'Enthält geringfügige Mengen von: Fett, gesättigten Fettsäuren, Eiweiß und Salz' : '');

        const currentConfig = getManualConfigFromForm();
        const ingredientPreview = buildIngredientsPreview(currentConfig, 'de');
        const translatedPreview = buildIngredientsPreview(currentConfig, currentPreviewLang);
        ingredients.val(ingredientPreview.text);
        footnote.val(ingredientPreview.footnote);
        pretable.val('');
        $('#nlm_preview_value_energy').text(energy.val());
        $('#nlm_preview_value_carbs').text(carbs.val());
        $('#nlm_preview_value_sugar').text(sugar.val());
        $('#nlm_preview_ingredients_html').text(translatedPreview.text || '—');
        updateAdditionalPreviewRows();
    }

    function applyCategoryVisibility() {
        const category = $('#nlm_kategorie').val() || '';
        $('[data-categories]').each(function () {
            let allowed = $(this).data('allowedCategories');
            if (!Array.isArray(allowed)) {
                try { allowed = JSON.parse($(this).attr('data-categories') || '[]'); } catch (e) { allowed = []; }
                $(this).data('allowedCategories', allowed);
            }
            const show = !allowed.length || (category && allowed.indexOf(category) !== -1);
            $(this).toggle(show);
            if (!show) {
                $(this).find('input[type="checkbox"]').prop('checked', false);
                $(this).find('.nlm-enumber-toggle').prop('checked', false);
            }
        });
    }

    function updateGroupBodies() {
        $('.nlm-group-block').each(function () {
            const block = $(this);
            const groupKey = block.data('group');
            const enabled = groupKey === 'base' || block.find('.nlm-group-toggle').is(':checked');
            block.find('.nlm-group-body').toggleClass('nlm-hidden', !enabled);
        });
        ensureOtherCustomItemRow();
    }

    function toggleCustomItemsEmpty() {
        const list = $('#nlm_other_custom_items');
        const empty = $('#nlm_other_custom_items_empty');
        if (!list.length || !empty.length) return;
        empty.toggleClass('nlm-hidden', list.find('.nlm-custom-item-row').length > 0);
    }

    function getCustomItemDisplay(item) {
        item = item || {};
        const label = String(item.label || '').trim();
        const e = String(item.e || '').trim();
        const enumber = String(item.enumber || '0') === '1';
        return enumber && e ? e : (label || e || '');
    }

    function ensureOtherCustomItemRow() {
        const toggle = $('input[name="wine_e_label_manual[groups][other][enabled]"]');
        const list = $('#nlm_other_custom_items');
        if (!toggle.length || !list.length) return;
        if (!toggle.is(':checked')) {
            toggleCustomItemsEmpty();
            return;
        }
        if (!list.find('.nlm-custom-item-row').length) {
            list.append(buildCustomItemRow(getNextCustomItemIndex(), { label: '' }));
        }
        toggleCustomItemsEmpty();
    }

    function buildCustomItemRow(index, item) {
        item = item || {};
        const label = $('<div/>').text(getCustomItemDisplay(item)).html();
        return '' +
            '<div class="nlm-custom-item-row" data-index="' + index + '">' +
                '<input type="hidden" name="wine_e_label_manual[groups][other][custom_items][' + index + '][selected]" value="1">' +
                '<div class="nlm-field">' +
                    '<label>' + (cfg.i18n.customNameLabel || 'Stoff oder E-Nr.') + '</label>' +
                    '<input type="text" class="nlm-custom-label" name="wine_e_label_manual[groups][other][custom_items][' + index + '][label]" value="' + label + '">' +
                '</div>' +
                '<div class="nlm-field">' +
                    '<button type="button" class="button-link button-link-delete nlm-remove-custom-item">' + (cfg.i18n.removeLabel || 'Entfernen') + '</button>' +
                '</div>' +
            '</div>';
    }

    function getPreviewValueWithUnit(value) {
        const clean = String(value || '').trim();
        return clean ? (clean + ' g') : '';
    }

    function updateAdditionalPreviewRows() {
        const restMode = $('input[name="wine_e_label_manual[nutrition][restwerte_mode]"]:checked').val() || 'text';
        const t = (cfg.previewTexts && cfg.previewTexts[currentPreviewLang]) ? cfg.previewTexts[currentPreviewLang] : cfg.previewTexts.de;
        $('#nlm_preview_label_fat').text(t.fat);
        $('#nlm_preview_label_saturates').text(t.saturates);
        $('#nlm_preview_label_protein').text(t.protein);
        $('#nlm_preview_label_salt').text(t.salt);
        $('#nlm_preview_salt_natural').text(t.saltNatural);

        const values = {
            fat: $.trim($('#nlm_fat').val() || ''),
            saturates: $.trim($('#nlm_saturates').val() || ''),
            protein: $.trim($('#nlm_protein').val() || ''),
            salt: $.trim($('#nlm_salt').val() || ''),
            saltNatural: $('input[name="wine_e_label_manual[nutrition][salt_natural]"]').is(':checked')
        };

        $('#nlm_preview_value_fat').text(getPreviewValueWithUnit(values.fat));
        $('#nlm_preview_value_saturates').text(getPreviewValueWithUnit(values.saturates));
        $('#nlm_preview_value_protein').text(getPreviewValueWithUnit(values.protein));
        $('#nlm_preview_value_salt').text(getPreviewValueWithUnit(values.salt));

        const isList = restMode === 'list';
        $('#nlm_preview_row_fat').toggleClass('nlm-hidden', !(isList && values.fat));
        $('#nlm_preview_row_saturates').toggleClass('nlm-hidden', !(isList && values.saturates));
        $('#nlm_preview_row_protein').toggleClass('nlm-hidden', !(isList && values.protein));
        $('#nlm_preview_row_salt').toggleClass('nlm-hidden', !(isList && values.salt));
        $('#nlm_preview_row_salt_natural').toggleClass('nlm-hidden', !(isList && values.saltNatural));
        $('#nlm_preview_row_minor').toggleClass('nlm-hidden', isList);
        if (!isList) {
            $('#nlm_preview_minor').text(t.minor);
        } else {
            $('#nlm_preview_minor').text('');
        }
    }

    function updatePreviewLanguage(lang) {
        currentPreviewLang = lang;
        previewLangButtons.removeClass('is-active').filter('[data-lang="' + lang + '"]').addClass('is-active');
        const t = (cfg.previewTexts && cfg.previewTexts[lang]) ? cfg.previewTexts[lang] : cfg.previewTexts.de;
        $('#nlm_preview_headline').text(t.headline);
        $('#nlm_preview_label_energy').text(t.energy);
        $('#nlm_preview_label_carbs').text(t.carbs);
        $('#nlm_preview_label_sugar').text(t.sugar);
        $('#nlm_preview_label_ingredients').text(t.ingredients);
        updateAdditionalPreviewRows();
        const translatedPreview = buildIngredientsPreview(getManualConfigFromForm(), currentPreviewLang);
        $('#nlm_preview_ingredients_html').text(translatedPreview.text || '—');
        syncPresentationPreview();
        updatePreviewUrl();
        if (currentLabelExists && publicLinkAnchor.length && publicLinkAnchor.attr('href') && publicLinkAnchor.attr('href') !== '#') {
            const href = withLangUrl(publicLinkAnchor.attr('href'), currentPreviewLang);
            setManualLink(href, href, true);
        }
    }

    function validateRequiredFields() {
        const missing = [];
        const checks = [
            { key: 'bezeichnung', value: $('#nlm_bezeichnung').val(), label: 'Bezeichnung', input: $('#nlm_bezeichnung') },
            { key: 'wein_nr', value: $('#nlm_wein_nr').val(), label: 'Wein-Nr.', input: $('#nlm_wein_nr') },
            { key: 'kategorie', value: $('#nlm_kategorie').val(), label: 'Kategorie', input: $('#nlm_kategorie') },
            { key: 'alkohol_gl', value: $('#nlm_alkohol_gl').val(), label: 'Alkohol (g/l)', input: $('#nlm_alkohol_gl') },
            { key: 'slug', value: footerSlug.val() || slug.val(), label: 'Slug / URL-Teil', input: footerSlug }
        ];
        $('[data-required-field]').each(function () {
            $(this).find('input,select').removeClass('nlm-field-error');
            $(this).find('.nlm-field-hint').removeClass('is-visible');
        });
        checks.forEach(function (check) {
            if (!String(check.value || '').trim()) {
                missing.push(check.label);
                check.input.addClass('nlm-field-error');
                check.input.closest('.nlm-field').find('.nlm-field-hint').addClass('is-visible');
            }
        });
        const ok = !missing.length;
        const box = $('#nlm_validation_summary_box');
        box.toggleClass('is-error', !ok);
        $('#nlm_validation_summary_text').text(ok ? 'vollständig' : 'Es fehlen noch Pflichtfelder.');
        const list = $('#nlm_validation_summary_list');
        list.empty();
        missing.forEach(function (item) { list.append($('<li/>').text(item)); });
        list.toggleClass('nlm-hidden', !missing.length);
        setWorkflowState('validation', ok ? cfg.i18n.stateComplete : cfg.i18n.stateMissing, ok ? 'nlm-ok' : 'nlm-error');
        return { ok, missing };
    }

    function syncManualToSidebar() {
        const manualWineNr = $('#nlm_wein_nr').val() || '';
        setWineNrValue(manualWineNr, 'manual');
        if ($('#nlm_bezeichnung').val()) title.val($('#nlm_bezeichnung').val());
        updateSuggestionButton();
        if (!slug.val() && manualWineNr) setSlugValue(manualWineNr, '');
        updatePreviewUrl();
    }

    function syncCustomItemInputState() {
        toggleCustomItemsEmpty();
        calculateManualValues();
        updatePreviewLanguage(currentPreviewLang);
        updateSourceSummary();
        validateRequiredFields();
        setWorkflowState('manual', (hasManualOverrides() ? cfg.i18n.stateAvailable : cfg.i18n.stateEmpty), hasManualOverrides() ? 'nlm-ok' : 'nlm-pending');
    }

    function syncAll() {
        applyCategoryVisibility();
        updateGroupBodies();
        calculateManualValues();
        syncManualToSidebar();
        updatePreviewLanguage(currentPreviewLang);
        updateSourceSummary();
        validateRequiredFields();
        setWorkflowState('manual', (hasManualOverrides() ? cfg.i18n.stateAvailable : cfg.i18n.stateEmpty), hasManualOverrides() ? 'nlm-ok' : 'nlm-pending');
    }

    function applyManualConfig(config, labelData) {
        config = config || $.extend(true, {}, cfg.defaultConfig || {});
        currentRenderedManualConfig = $.extend(true, {}, config);
        const product = config.product || {};
        const nutrition = config.nutrition || {};
        $('#nlm_bezeichnung').val(product.bezeichnung || '');
        $('#nlm_wein_nr').val(product.wein_nr || '');
        $('#nlm_ap_nr').val(product.ap_nr || '');
        $('#nlm_kategorie').val(product.kategorie || '');
        $('#nlm_alkohol_gl').val(nutrition.alkohol_gl || '');
        $('#nlm_restzucker').val(nutrition.restzucker_gl || '');
        $('#nlm_gesamtsaeure').val(nutrition.gesamtsaeure_gl || '');
        $('#nlm_glycerin_manual').val(nutrition.glycerin_manual || '');
        $('#nlm_fat').val(nutrition.fat || '');
        $('#nlm_saturates').val(nutrition.saturates || '');
        $('#nlm_protein').val(nutrition.protein || '');
        $('#nlm_salt').val(nutrition.salt || '');
        $('input[name="wine_e_label_manual[nutrition][glycerin_mode]"][value="' + (nutrition.glycerin_mode || 'standard') + '"]').prop('checked', true);
        $('input[name="wine_e_label_manual[nutrition][restwerte_mode]"][value="' + (nutrition.restwerte_mode || 'text') + '"]').prop('checked', true);
        $('input[name="wine_e_label_manual[nutrition][salt_natural]"]').prop('checked', (nutrition.salt_natural || '0') === '1');

        $('[name^="wine_e_label_manual[groups]"]').filter(':checkbox').prop('checked', false);
        $('.nlm-enumber-toggle').prop('checked', false);
        $('#nlm_other_custom_items').empty();
        $.each(config.groups || {}, function (groupKey, group) {
            $('input[name="wine_e_label_manual[groups][' + groupKey + '][enabled]"]').prop('checked', (group.enabled || '0') === '1');
            if (group.mode) $('input[name="wine_e_label_manual[groups][' + groupKey + '][mode]"][value="' + group.mode + '"]').prop('checked', true);
            $.each(group.items || {}, function (itemKey, item) {
                const isSelected = (item.selected || '0') === '1';
                $('input[name="wine_e_label_manual[groups][' + groupKey + '][items][' + itemKey + '][selected]"]').prop('checked', isSelected);
                $('input[name="wine_e_label_manual[groups][' + groupKey + '][items][' + itemKey + '][bio]"]').prop('checked', (item.bio || '0') === '1');
                $('input[name="wine_e_label_manual[groups][' + groupKey + '][items][' + itemKey + '][enumber]"]').prop('checked', (item.enumber || '0') === '1');
            });
            if (groupKey === 'other') {
                $.each(group.custom_items || [], function (index, item) {
                    $('#nlm_other_custom_items').append(buildCustomItemRow(index, item));
                });
            }
        });

        if (labelData) {
            title.val(labelData.title || title.val() || '');
            energy.val(labelData.energy || energy.val() || '');
            carbs.val(labelData.carbs || carbs.val() || '');
            sugar.val(labelData.sugar || sugar.val() || '');
            minor.val(labelData.minor || minor.val() || '');
            minorMode.val(labelData.minor_mode || minorMode.val() || '');
            fat.val(labelData.fat || fat.val() || '');
            saturates.val(labelData.saturates || saturates.val() || '');
            protein.val(labelData.protein || protein.val() || '');
            salt.val(labelData.salt || salt.val() || '');
            saltNatural.val(labelData.salt_natural || saltNatural.val() || '0');
            ingredients.val(labelData.ingredients_html || ingredients.val() || '');
            footnote.val(labelData.footnote || footnote.val() || '');
            pretable.val(labelData.pretable_notice || pretable.val() || '');
        }

        $('#nlm_analysis_fields').toggleClass('nlm-hidden', $('input[name="wine_e_label_manual[nutrition][restwerte_mode]"]:checked').val() !== 'list');
        updateGroupBodies();
        syncAll();
    }

    function assignFiles(files) {
        if (!files || !files.length || !fileInput.length) return false;
        try {
            const dt = new DataTransfer();
            Array.from(files).forEach(file => dt.items.add(file));
            fileInput[0].files = dt.files;
        } catch (e) {
            return false;
        }
        fileName.text(files[0].name);
        fileInput.trigger('change');
        return true;
    }

    function appendManualFields(formData) {
        $('[name^="wine_e_label_manual["]').each(function () {
            if ((this.type === 'checkbox' || this.type === 'radio')) {
                if (this.checked) formData.append(this.name, $(this).val() || '1');
            } else {
                formData.append(this.name, $(this).val() || '');
            }
        });
        formData.append('manual_mode', '1');
    }

    function appendDisplayFields(formData) {
        $('[name^="wine_e_label_display["]').each(function () {
            formData.append(this.name, $(this).val() || '');
        });
    }

    function handleCreateSuccess(data) {
        if (data.display_config) {
            applyDisplayConfig(data.display_config);
        }
        if (typeof data.slug !== 'undefined') setSlugValue(data.slug, '');
        if (typeof data.wine_nr !== 'undefined') setWineNrValue(data.wine_nr, '');
        if (typeof data.title !== 'undefined') title.val(data.title);
        if (typeof data.energy !== 'undefined') energy.val(data.energy);
        if (typeof data.carbs !== 'undefined') carbs.val(data.carbs);
        if (typeof data.sugar !== 'undefined') sugar.val(data.sugar);
        if (typeof data.minor_mode !== 'undefined') minorMode.val(data.minor_mode);
        if (typeof data.fat !== 'undefined') fat.val(data.fat);
        if (typeof data.saturates !== 'undefined') saturates.val(data.saturates);
        if (typeof data.protein !== 'undefined') protein.val(data.protein);
        if (typeof data.salt !== 'undefined') salt.val(data.salt);
        if (typeof data.salt_natural !== 'undefined') saltNatural.val(data.salt_natural);
        if (typeof data.ingredients_html !== 'undefined') ingredients.val(data.ingredients_html);
        if (typeof data.footnote !== 'undefined') footnote.val(data.footnote);
        if (typeof data.pretable_notice !== 'undefined') pretable.val(data.pretable_notice);
        if (typeof data.minor !== 'undefined') minor.val(data.minor);
        currentLabelExists = true;
        refreshCreateButtons();
        updatePreviewUrl();
        if (data.url) { const finalUrl = withLangUrl(data.url, currentPreviewLang); setManualLink(finalUrl, finalUrl, true); }
        setLinkNotice(cfg.i18n.inlinePageSuccess || cfg.i18n.createSuccess, 'ok');
        setStatusRow(cfg.i18n.pageTitle, cfg.i18n.createSuccess, 'nl-status-ok', data.url ? '<a href="' + data.url + '" target="_blank">' + (cfg.i18n.openLink || 'Link öffnen') + '</a>' : '');
        const qrDownloadHtml = '<button type="button" class="button-link wine-e-label-download-qr" data-product-id="' + cfg.productId + '" data-lang="' + currentPreviewLang + '">' + (cfg.i18n.downloadLabel || 'Download') + '</button>';
        const sidebarQrHtml = qrDownloadHtml + ((data.qr_preview || '') ? '<div class="nl-inline-preview"><img src="' + data.qr_preview + '" alt="QR Code" class="nl-status-qr-image"></div>' : '');
        setStatusRow(cfg.i18n.qrTitle, cfg.i18n.qrSuccess, 'nl-status-ok', qrDownloadHtml);
        setStatusRowInBox('.nl-sidebar-box .nl-status', cfg.i18n.qrTitle, cfg.i18n.qrSuccess, 'nl-status-ok', sidebarQrHtml);
        removeLegacyStatusQrPreviews();
        setManualQrState(data.qr_preview || '', !!data.qr_preview);
        setWorkflowState('page', cfg.i18n.stateCreated, 'nlm-ok');
        setWorkflowState('qr', cfg.i18n.stateCreated, 'nlm-ok');
    }

    function createLabelRequest(formData, button) {
        button.prop('disabled', true).text(cfg.i18n.creating);
        setStatusRow(cfg.i18n.pageTitle, cfg.i18n.creating, 'nl-status-pending');
        setStatusRow(cfg.i18n.qrTitle, cfg.i18n.pendingQr, 'nl-status-pending');
        formData.append('lang_code', currentPreviewLang);
        $.ajax({
            url: cfg.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (!response || !response.success) {
                    const message = (response && response.data) ? response.data : cfg.i18n.createError;
                    setStatusRow(cfg.i18n.pageTitle, message, 'nl-status-error');
                    setLinkNotice(message, 'error');
                    setWorkflowState('page', cfg.i18n.stateError, 'nlm-error');
                    return;
                }
                handleCreateSuccess(response.data || {});
            },
            error: function () {
                setStatusRow(cfg.i18n.pageTitle, cfg.i18n.createError, 'nl-status-error');
                setLinkNotice(cfg.i18n.createError, 'error');
                setWorkflowState('page', cfg.i18n.stateError, 'nlm-error');
            },
            complete: function () {
                button.prop('disabled', false).text(getCreateButtonLabel());
            }
        });
    }


    function deleteGeneratedRequest(button) {
        button.prop('disabled', true).text(cfg.i18n.deleteGeneratedBusy || cfg.i18n.deleteGeneratedButton);
        $.post(cfg.ajaxUrl, {
            action: 'wine_e_label_delete_generated',
            nonce: cfg.deleteGeneratedNonce,
            product_id: cfg.productId
        }).done(function (response) {
            if (!response || !response.success) {
                setStatusRow(cfg.i18n.pageTitle, (response && response.data) ? response.data : cfg.i18n.deleteGeneratedError, 'nl-status-error');
                return;
            }
            currentLabelExists = false;
            refreshCreateButtons();
            clearBuildStatuses();
            const message = (response.data && response.data.message) ? response.data.message : cfg.i18n.deleteGeneratedDone;
            setStatusRow(cfg.i18n.pageTitle, message, 'nl-status-pending');
            setLinkNotice(message, 'pending');
            setStatusRow(cfg.i18n.qrTitle, cfg.i18n.pendingQr, 'nl-status-pending');
            updatePreviewUrl();
            updateSourceSummary();
        }).fail(function () {
            setStatusRow(cfg.i18n.pageTitle, cfg.i18n.deleteGeneratedError, 'nl-status-error');
            setLinkNotice(cfg.i18n.deleteGeneratedError, 'error');
        }).always(function () {
            const label = cfg.i18n.deleteGeneratedButton || 'E-Label und QR-Code löschen';
            deleteGeneratedButton.prop('disabled', false).text(label);
            manualDeleteGeneratedButton.prop('disabled', false).text(label);
        });
    }

    // Dropzone and upload
    if (dropzone.length) {
        dropzone.attr('tabindex', '0').on('click keydown', function (e) {
            if (e.type === 'click' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                fileInput.trigger('click');
            }
        });
        dropzone.on('dragenter dragover', e => { e.preventDefault(); e.stopPropagation(); dropzone.addClass('dragover'); });
        dropzone.on('dragleave dragend', e => { e.preventDefault(); e.stopPropagation(); dropzone.removeClass('dragover'); });
        dropzone.on('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.removeClass('dragover');
            const files = e.originalEvent && e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : null;
            if (!assignFiles(files)) fileName.text(cfg.i18n.noFileChosen);
        });
    }
    fileInput.on('change', function () { fileName.text(this.files && this.files.length ? this.files[0].name : cfg.i18n.noFileChosen); });

    // Sync fields
    wineNr.on('input', debounce(function () { setWineNrValue($(this).val(), 'sidebar'); updateSuggestionButton(); updatePreviewUrl(); validateRequiredFields(); }, 180));
    $('#nlm_wein_nr').on('input', debounce(syncAll, 180));
    footerWineNr.on('input', debounce(function () { setWineNrValue($(this).val(), 'footer'); updateSuggestionButton(); if (!slug.val()) setSlugValue($(this).val(), ''); syncAll(); }, 180));
    slug.on('input', debounce(function () { setSlugValue($(this).val(), 'sidebar'); updatePreviewUrl('sidebar'); validateRequiredFields(); }, 180));
    footerSlug.on('input', debounce(function () { setSlugValue($(this).val(), 'footer'); updatePreviewUrl('footer'); validateRequiredFields(); }, 180));
    suggestionBtn.on('click', function (e) { e.preventDefault(); const s = $(this).attr('data-suggestion') || normalizeSlug(wineNr.val()); if (s) { setSlugValue(s, ''); updatePreviewUrl(); validateRequiredFields(); } });
    $('#nlm_apply_slug_suggestion').on('click', function (e) { e.preventDefault(); const s = normalizeSlug($('#nlm_wein_nr').val() || footerWineNr.val() || wineNr.val()); if (s) { setSlugValue(s, ''); updatePreviewUrl(); validateRequiredFields(); } });

    // Manual form bindings
    const debouncedSync = debounce(syncAll, 180);
    $(document).on('change', '.nlm-group-toggle', function () {
        $(this).closest('.nlm-group-block').find('.nlm-group-body').toggleClass('nlm-hidden', !this.checked);
        syncAll();
    });
    $(document).on('change', 'input[name="wine_e_label_manual[nutrition][restwerte_mode]"]', function () {
        $('#nlm_analysis_fields').toggleClass('nlm-hidden', $(this).val() !== 'list');
        updateAdditionalPreviewRows();
        syncAll();
    });
    $(document).on('change', '#nlm_kategorie', syncAll);
    $(document).on('change', '.nlm-item-selected,.nlm-enumber-toggle,input[name="wine_e_label_manual[nutrition][glycerin_mode]"],input[name="wine_e_label_manual[nutrition][salt_natural]"],input[name^="wine_e_label_manual[groups]"]', syncAll);
    $(document).on('input', '#nlm_bezeichnung,#nlm_wein_nr,#nlm_ap_nr,#nlm_alkohol_gl,#nlm_restzucker,#nlm_gesamtsaeure,#nlm_glycerin_manual,#nlm_fat,#nlm_saturates,#nlm_protein,#nlm_salt', debouncedSync);
    $(document).on('input', '.nlm-custom-label', debounce(syncCustomItemInputState, 180));
    $(document).on('change', '.nlm-custom-label', syncAll);
    $(document).on('click', '.nlm-add-custom-item', function (e) {
        e.preventDefault();
        $('input[name="wine_e_label_manual[groups][other][enabled]"]').prop('checked', true);
        const index = getNextCustomItemIndex();
        $('#nlm_other_custom_items').append(buildCustomItemRow(index, { label: '' }));
        updateGroupBodies();
        const newRow = $('#nlm_other_custom_items .nlm-custom-item-row').last();
        newRow.find('.nlm-custom-label').trigger('focus');
    });
    $(document).on('click', '.nlm-remove-custom-item', function (e) {
        e.preventDefault();
        $(this).closest('.nlm-custom-item-row').remove();
        toggleCustomItemsEmpty();
        syncAll();
    });

    $(document).on('input change', '#nlm_display_custom_image_alt,#nlm_display_wine_name,#nlm_display_vintage,#nlm_display_subtitle', debounce(function () {
        currentDisplayConfig = getDisplayConfigFromForm();
        syncPresentationPreview();
    }, 120));

    displayImageSelectButton.on('click', function (e) {
        e.preventDefault();
        if (typeof wp === 'undefined' || !wp.media) {
            return;
        }
        if (!displayMediaFrame) {
            displayMediaFrame = wp.media({
                title: cfg.customImagePickerTitle || 'Produktbild waehlen',
                button: { text: cfg.customImagePickerButton || 'Dieses Bild verwenden' },
                library: { type: 'image' },
                multiple: false
            });
            displayMediaFrame.on('select', function () {
                const attachment = displayMediaFrame.state().get('selection').first();
                if (!attachment) return;
                const data = attachment.toJSON ? attachment.toJSON() : {};
                const url = data.url || '';
                const alt = $.trim(data.alt || data.title || '');
                displayImageInput.val(url);
                if (!displayImageAltInput.val()) {
                    displayImageAltInput.val(alt);
                }
                currentDisplayConfig = getDisplayConfigFromForm();
                syncPresentationPreview();
            });
        }
        displayMediaFrame.open();
    });

    displayImageResetButton.on('click', function (e) {
        e.preventDefault();
        displayImageInput.val('');
        currentDisplayConfig = getDisplayConfigFromForm();
        syncPresentationPreview();
    });

    // Preview language
    previewLangButtons.on('click', function (e) {
        e.preventDefault();
        updatePreviewLanguage($(this).data('lang'));
    });

    // Copy/open helpers
    copySlugButton.on('click', function (e) { e.preventDefault(); copyText(footerSlug.val() || slug.val(), cfg.i18n.copySlug); });
    copyLinkButton.on('click', function (e) { e.preventDefault(); copyText(publicLinkText.text(), cfg.i18n.copyLink); });
    openLinkButton.on('click', function (e) { e.preventDefault(); const href = $(this).data('href') || publicLinkAnchor.attr('href'); if (href && href !== '#') window.open(href, '_blank'); });
    previewPageView.on('click', function (e) { e.preventDefault(); const href = $(this).data('href'); if (href && href !== '#') window.open(href, '_blank'); });
    previewQrView.on('click', function (e) { e.preventDefault(); const href = $(this).data('href'); if (href) window.open(href, '_blank'); });

    // Import AJAX
    importButton.on('click', function (e) {
        e.preventDefault();
        if (!fileInput.length || !fileInput[0].files || !fileInput[0].files.length) {
            setStatusRow('Importstatus', cfg.i18n.noFile, 'nl-status-error');
            return;
        }
        const fd = new FormData();
        fd.append('action', 'wine_e_label_import_confirm');
        fd.append('nonce', cfg.importNonce);
        fd.append('product_id', cfg.productId);
        fd.append('import_file', fileInput[0].files[0]);
        importButton.prop('disabled', true).text(cfg.i18n.importing);
        setStatusRow('Importstatus', cfg.i18n.importing, 'nl-status-pending');
        $.ajax({
            url: cfg.ajaxUrl,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (response) {
                if (!response || !response.success) {
                    setStatusRow('Importstatus', (response && response.data) ? response.data : cfg.i18n.ajaxError, 'nl-status-error');
                    setWorkflowState('import', cfg.i18n.stateError, 'nlm-error');
                    return;
                }
                const d = response.data || {};
                currentImportSnapshot = d.import_snapshot || d.manual_config || cfg.defaultConfig || {};
                currentSourceFileName = d.source_file_name || '';
                if (typeof d.wine_nr !== 'undefined') setWineNrValue(d.wine_nr, '');
                if (typeof d.slug !== 'undefined') setSlugValue(d.slug, '');
                if (typeof d.title !== 'undefined') title.val(d.title);
                if (typeof d.energy !== 'undefined') energy.val(d.energy);
                if (typeof d.carbs !== 'undefined') carbs.val(d.carbs);
                if (typeof d.sugar !== 'undefined') sugar.val(d.sugar);
                if (typeof d.minor !== 'undefined') minor.val(d.minor);
                if (typeof d.minor_mode !== 'undefined') minorMode.val(d.minor_mode);
                if (typeof d.fat !== 'undefined') fat.val(d.fat);
                if (typeof d.saturates !== 'undefined') saturates.val(d.saturates);
                if (typeof d.protein !== 'undefined') protein.val(d.protein);
                if (typeof d.salt !== 'undefined') salt.val(d.salt);
                if (typeof d.salt_natural !== 'undefined') saltNatural.val(d.salt_natural);
                if (typeof d.ingredients_html !== 'undefined') ingredients.val(d.ingredients_html);
                if (typeof d.footnote !== 'undefined') footnote.val(d.footnote);
                if (typeof d.pretable_notice !== 'undefined') pretable.val(d.pretable_notice);
                if (d.display_config) applyDisplayConfig(d.display_config);
                if (d.manual_config) applyManualConfig(d.manual_config, d);
                if (d.source_file_name) fileName.text(d.source_file_name);
                ensureImportSnapshotBaseline();
                updateSuggestionButton();
                refreshCreateButtons();
                updatePreviewUrl();
                setStatusRow('Importstatus', d.import_message || cfg.i18n.importSuccess, 'nl-status-ok');
                updateLastImport(d.last_import || '');
                setWorkflowState('import', cfg.i18n.stateLoaded, 'nlm-ok');
                clearBuildStatuses();
                updateSourceSummary();
            },
            error: function () {
                setStatusRow('Importstatus', cfg.i18n.ajaxError, 'nl-status-error');
                setWorkflowState('import', cfg.i18n.stateError, 'nlm-error');
            },
            complete: function () {
                importButton.prop('disabled', false).text(cfg.i18n.importConfirmButton || 'Import bestätigen');
            }
        });
    });

    deleteImportButton.on('click', function (e) {
        e.preventDefault();
        if (fileInput.length) fileInput.val('');
        fileName.text(cfg.i18n.noFileChosen);
        const fd = new FormData();
        fd.append('action', 'wine_e_label_import_delete');
        fd.append('nonce', cfg.deleteImportNonce);
        fd.append('product_id', cfg.productId);
        deleteImportButton.prop('disabled', true).text(cfg.i18n.deleteImportBusy || cfg.i18n.deleteImportButton);
        $.ajax({
            url: cfg.ajaxUrl,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (response) {
                if (!response || !response.success) {
                    setStatusRow('Importstatus', (response && response.data) ? response.data : cfg.i18n.deleteImportError, 'nl-status-error');
                    return;
                }
                currentSourceFileName = '';
                currentImportSnapshot = cfg.defaultConfig || {};
                setStatusRow('Importstatus', (response.data && response.data.message) ? response.data.message : cfg.i18n.importDeleted, 'nl-status-pending');
                updateLastImport('');
                setWorkflowState('import', cfg.i18n.stateOpen, 'nlm-pending');
                clearBuildStatuses();
                updateSourceSummary();
            },
            error: function () {
                setStatusRow('Importstatus', cfg.i18n.deleteImportError, 'nl-status-error');
            },
            complete: function () {
                deleteImportButton.prop('disabled', false).text(cfg.i18n.deleteImportButton);
            }
        });
    });

    // Download QR
    $(document).on('click', '.wine-e-label-download-qr', function (e) {
        e.preventDefault();
        const button = $(this);
        const productId = button.data('product-id');
        if (!productId) return;
        const t = button.text();
        button.prop('disabled', true).text(cfg.i18n.generateQr);
        $.ajax({
            url: cfg.ajaxUrl,
            type: 'POST',
            data: { action: 'wine_e_label_qr_download', product_id: productId, nonce: cfg.nonce, lang_code: button.data('lang') || currentPreviewLang },
            success: function (r) {
                if (r.success) {
                    const link = document.createElement('a');
                    link.href = r.data.url;
                    link.download = r.data.filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            },
            complete: function () {
                button.prop('disabled', false).text(t);
            }
        });
    });

    // Create from sidebar / manual
    createButton.on('click', function (e) {
        e.preventDefault();
        syncAll();
        const validation = validateRequiredFields();
        const s = setSlugValue(slug.val() || footerSlug.val() || wineNr.val() || footerWineNr.val(), '');
        if (!s || !validation.ok) {
            setStatusRow(cfg.i18n.pageTitle, cfg.i18n.validationError, 'nl-status-error');
            setLinkNotice(cfg.i18n.validationError, 'error');
            return;
        }
        const fd = new FormData();
        fd.append('action', 'wine_e_label_create_label');
        fd.append('nonce', cfg.createNonce);
        fd.append('product_id', cfg.productId);
        fd.append('slug', s);
        fd.append('wine_nr', wineNr.val() || footerWineNr.val() || '');
        fd.append('title', title.val() || '');
        appendManualFields(fd);
        appendDisplayFields(fd);
        createLabelRequest(fd, createButton);
    });

    manualCreateButton.on('click', function (e) {
        e.preventDefault();
        syncAll();
        const validation = validateRequiredFields();
        const s = setSlugValue(footerSlug.val() || slug.val() || footerWineNr.val() || wineNr.val(), '');
        if (!s || !validation.ok) {
            setStatusRow(cfg.i18n.pageTitle, cfg.i18n.validationError, 'nl-status-error');
            setLinkNotice(cfg.i18n.validationError, 'error');
            return;
        }
        const fd = new FormData();
        fd.append('action', 'wine_e_label_create_label');
        fd.append('nonce', cfg.createNonce);
        fd.append('product_id', cfg.productId);
        fd.append('slug', s);
        fd.append('wine_nr', footerWineNr.val() || wineNr.val() || '');
        fd.append('title', title.val() || '');
        appendManualFields(fd);
        appendDisplayFields(fd);
        createLabelRequest(fd, manualCreateButton);
    });

    deleteGeneratedButton.on('click', function (e) {
        e.preventDefault();
        manualDeleteGeneratedButton.prop('disabled', true).text(cfg.i18n.deleteGeneratedBusy || cfg.i18n.deleteGeneratedButton);
        deleteGeneratedRequest(deleteGeneratedButton);
    });

    manualDeleteGeneratedButton.on('click', function (e) {
        e.preventDefault();
        deleteGeneratedButton.prop('disabled', true).text(cfg.i18n.deleteGeneratedBusy || cfg.i18n.deleteGeneratedButton);
        deleteGeneratedRequest(manualDeleteGeneratedButton);
    });

    // Copy product data
    copySourceButton.on('click', function (e) {
        e.preventDefault();
        const sourceId = copySourceSelect.val();
        if (!sourceId) return;
        copySourceButton.prop('disabled', true).text('Lade …');
        $.post(cfg.ajaxUrl, {
            action: 'wine_e_label_load_source_product',
            nonce: cfg.loadSourceNonce,
            product_id: sourceId
        }).done(function (response) {
            if (!response || !response.success) {
                return;
            }
            applyManualConfig(response.data.manual_config || cfg.defaultConfig, response.data.label_data || {});
            applyDisplayConfig(response.data.display_config || cfg.defaultDisplayConfig || {});
            syncAll();
        }).always(function () {
            copySourceButton.prop('disabled', false).text(cfg.i18n.applyData);
        });
    });

    // Clear manual data
    clearManualButton.on('click', function (e) {
        e.preventDefault();
        if (currentSourceFileName) {
            applyManualConfig(currentImportSnapshot || cfg.defaultConfig, {
                title: title.val(),
                energy: energy.val(),
                carbs: carbs.val(),
                sugar: sugar.val(),
                minor: minor.val(),
                minor_mode: minorMode.val(),
                fat: fat.val(),
                saturates: saturates.val(),
                protein: protein.val(),
                salt: salt.val(),
                salt_natural: saltNatural.val(),
                ingredients_html: ingredients.val(),
                footnote: footnote.val(),
                pretable_notice: pretable.val()
            });
        } else {
            applyManualConfig(cfg.defaultConfig, {
                title: '', energy: '', carbs: '', sugar: '', minor: '', minor_mode: '', fat: '', saturates: '', protein: '', salt: '', salt_natural: '0', ingredients_html: '', footnote: '', pretable_notice: ''
            });
            title.val(''); energy.val(''); carbs.val(''); sugar.val(''); minor.val(''); minorMode.val(''); fat.val(''); saturates.val(''); protein.val(''); salt.val(''); saltNatural.val('0'); ingredients.val(''); footnote.val(''); pretable.val('');
        }
        applyDisplayConfig(cfg.defaultDisplayConfig || {});
        syncAll();
    });

    // Reset all
    resetAllButton.on('click', function (e) {
        e.preventDefault();
        resetAllButton.prop('disabled', true).text(cfg.i18n.resetAll);
        $.post(cfg.ajaxUrl, {
            action: 'wine_e_label_reset_all',
            nonce: cfg.resetAllNonce,
            product_id: cfg.productId
        }).done(function (response) {
            if (!response || !response.success) return;
            currentImportSnapshot = cfg.defaultConfig || {};
            currentSourceFileName = '';
            currentLabelExists = false;
            refreshCreateButtons();
            applyManualConfig(cfg.defaultConfig || {}, {});
            applyDisplayConfig((response.data && response.data.display_config) ? response.data.display_config : (cfg.defaultDisplayConfig || {}));
            if (fileInput.length) fileInput.val('');
            fileName.text(cfg.i18n.noFileChosen);
            setWineNrValue('', '');
            setSlugValue('', '');
            title.val(''); energy.val(''); carbs.val(''); sugar.val(''); minor.val(''); minorMode.val(''); fat.val(''); saturates.val(''); protein.val(''); salt.val(''); saltNatural.val('0'); ingredients.val(''); footnote.val(''); pretable.val('');
            updateLastImport('');
            clearBuildStatuses();
            setStatusRow('Importstatus', cfg.i18n.resetAllDone, 'nl-status-pending');
            setWorkflowState('import', cfg.i18n.stateOpen, 'nlm-pending');
            setWorkflowState('manual', cfg.i18n.stateEmpty, 'nlm-pending');
            updateSourceSummary();
            validateRequiredFields();
        }).always(function () {
            resetAllButton.prop('disabled', false).text(cfg.i18n.resetAllButton || 'Alles zurücksetzen');
        });
    });

    // init
    applyCategoryVisibility();
    updateSuggestionButton();
    refreshCreateButtons();
    updatePreviewUrl();
    updatePreviewLanguage('de');
    setManualLink(publicLinkText.text(), publicLinkAnchor.attr('href') && publicLinkAnchor.attr('href') !== '#' ? publicLinkAnchor.attr('href') : '', !publicLinkAnchor.hasClass('is-disabled'));
    setLinkNotice(linkNotice.text(), linkNotice.hasClass('is-error') ? 'error' : (linkNotice.hasClass('is-ok') ? 'ok' : 'pending'));
    ensureImportSnapshotBaseline();
    removeLegacyStatusQrPreviews();
    setManualQrState(previewQrImg.attr('src') || '', !!previewQrImg.attr('src') && !!(slug.val() || footerSlug.val()));
    applyDisplayConfig(cfg.initialDisplayConfig || cfg.defaultDisplayConfig || {});
    syncAll();
});
