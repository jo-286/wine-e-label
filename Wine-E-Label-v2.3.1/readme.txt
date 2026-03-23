=== Wein E-Label ===
Contributors: johannesreith
Tags: wine, e-label, qr-code, woocommerce, nutrition
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.4
Stable tag: 2.3.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adds wine e-label management, local /l/ short URLs, receiver publishing and QR code generation for WooCommerce wine products.

== Description ==

Wein E-Label is a wine-focused WordPress and WooCommerce plugin system for electronic wine labels under EU e-labelling requirements.

The project started from an older nutrition-label codebase but has since been heavily reworked for wine-specific workflows. Today it focuses on winery production use cases rather than generic product nutrition labels.

Primary current development is by Johannes Reith. The historical project origin goes back to version 1.0 by Markus Hammer. The project remains distributed under GPLv3 or later.

Features:
- WIPZN ZIP / JSON / HTML import
- Manual correction and completion after import
- Local `/l/<slug>/` labels on the current WordPress site
- Optional external receiver publishing via REST API
- QR code generation (PNG / SVG)
- Database management and export
- Setup guide in DE / EN / FR / IT

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin in WordPress.
3. Open **Wein E-Label > Einstellungen**.
4. Start with the **Einrichtung / Setup** tab.

== Frequently Asked Questions ==

= Do I need a second WordPress site? =
No. The plugin can generate local labels on the same site. A separate receiver site is usually the cleaner production setup.

= Can imported values be changed manually? =
Yes. Imported values can be corrected, completed or overridden manually.

= What is the recommended production setup? =
A separate receiver site via REST API is usually the safest technical setup because the label page can stay isolated from shop, marketing and tracking content.

== Changelog ==

= 2.3.2 =
* Added real generated language files (`.po` / `.mo`) for `de_DE`, `de_DE_formal`, `en_US`, `fr_FR`, and `it_IT`.
* Hardened UTF-8 handling in the translation export workflow and validated the plugin source for mojibake markers.
* Added release-prep helper scripts for language builds, encoding repair checks, and focused main/receiver packaging.
* Completed a full PHP lint and smoke-check pass for the current release-preparation state.

= 2.3.1 =
* Unified the main-domain and receiver label layout so both render from the same nler structure and document frame.
* Added cleaner multilingual admin wording and fixed visible umlauts in labels, previews, and admin screens.
* Expanded the E-Labels overview with real target URLs, location labels, receiver-domain visibility, and target-specific QR actions.
* Improved receiver sync metadata so duplicate targets are easier to spot without generating artificial extra entries.

= 2.3 =
* Centralized the design handoff so main domain, subdomain and receiver can share the same styling basis.
* Added the new logo, product image, wine name, vintage, subtitle and producer-data design controls.
* Refined preview and receiver compatibility so language buttons feel clickable again and older labels keep working.

= 2.2 =
* Added a dedicated "Design anpassen" admin page in the main plugin for local and subdomain e-labels.
* Kept the receiver-style live preview for design changes directly in the main plugin.
* Scoped the new design controls so the external receiver domain stays untouched.

= 2.1.1 =
* Follow-up release to verify the new shared GitHub updater with a higher version number.
* Keeps the packaged GitHub update flow in sync for the main plugin and receiver.

= 2.1 =
* Added shared GitHub updater support for WordPress dashboard updates.
* Prepared the current release for testing the new update flow.

= 1.89.0 =
* Release preparation build.
* Added WordPress-compatible readme.
* Added release checklist.
* Hardened settings handling and sanitizing.

= 1.88.1 =
* Fixed false manual-change detection.
* Only show readable field names for real manual changes.

= 2.00 =
* Release-prep build with improved admin language fallback, visible plugin name kept as Wein E-Label and more robust WIPZN HTML import handling.

== Legal notice ==

This plugin is a technical aid for creating electronic wine e-labels. The user is solely responsible for the factual accuracy, completeness and legal review of all entered, imported, translated or generated data. Use of the plugin does not replace legal advice. Despite careful development, no warranty is given for the legal admissibility, completeness or error-free nature of the generated content in every individual case, to the extent permitted by law.

== Third-party dependencies ==

QR generation uses `endroid/qr-code` under the MIT License, together with its Composer dependency chain.
