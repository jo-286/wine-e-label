# Wein E-Label Receiver 2.3.2

Receiver plugin for a second WordPress instance that accepts wine e-label pages via REST, manages them centrally, and serves them without theme layout.

## New in 2.3.2

- ships with generated `.po` and `.mo` language files for `de_DE`, `de_DE_formal`, `en_US`, `fr_FR`, and `it_IT`
- participates in the stricter release-prep smoke-check and PHP-lint pass
- keeps the current receiver package aligned with the latest local release-preparation build

## Also included in 2.3.1

- renders central labels with the same document frame and body classes as the main plugin output
- keeps mirrored source targets visible in the receiver admin so duplicate routes are easier to detect
- aligns the receiver wording and admin/location labels with the current main plugin behavior

## Also included in 2.3

- aligns the receiver release with the new 2.3 packaging and shared updater manifest
- keeps centralized design syncing compatible across receiver output, preview styling and older stored labels

## Also included in 2.1.1

- follow-up release to validate the shared GitHub updater with a higher version number than 2.1
- keeps the packaged receiver ZIP and shared update manifest in sync

## Also included in 2.1

- shared GitHub updater support added so future receiver updates can be offered in the WordPress dashboard
- release notes and branding references cleaned up for the current visible plugin name
- setup guide translations revised again for **DE / EN / FR / IT**
- old hidden design ballast removed where it was no longer used in the UI
- REST error messages aligned for API usage
- sender example updated to the current discovery-based receiver flow

## Installation

1. Upload the ZIP in the WordPress admin under Plugins.
2. Activate the plugin.
3. Save permalinks once if needed.
4. Read the REST connection notes under **Setup**.
5. Enter receiver URL, username and application password in the main plugin.

## Translations And QA

The receiver now participates in the shared product-quality baseline:

- translation template export via `scripts/export-i18n-templates.ps1`
- smoke checks via `scripts/run-smoke-checks.ps1`
- repository docs for setup and QA in the root `docs/` folder

Current receiver translation template:

- `languages/wine-e-label-receiver.pot`

## REST endpoints

- `GET /wp-json/reith-elabel/v2/info`
- `GET /wp-json/reith-elabel/v1/info`
- `POST /wp-json/reith-elabel/v2/labels`
- `GET /wp-json/reith-elabel/v2/labels/<slug>`
- `DELETE /wp-json/reith-elabel/v2/labels/<slug>`

The `v1` label routes remain available for compatibility.

## Note

The admin UI language is switchable inside the plugin for de/en/fr/it. This is separate from the language of the actual e-label pages.
