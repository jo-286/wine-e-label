## 2.3

- Centralized design syncing so main domain, subdomain and receiver share one styling basis.
- Added configurable logo, product image, wine name, vintage, subtitle and producer-data blocks.
- Restored the expected language-button alignment and hover/click feel for preview and receiver output.

## 2.2

- Added a dedicated `Design anpassen` admin page in the main plugin.
- Brought the receiver-style live preview into the main plugin for local and subdomain e-labels.
- Kept the new design settings strictly scoped to local/subdomain output so the external receiver domain stays separate.

## 2.1.1

- Follow-up updater test release with a higher version number than 2.1.
- Keeps the GitHub update manifest and packaged plugin ZIPs aligned for dashboard update checks.

## 2.1

- Added the shared GitHub updater foundation so future versions can update through the WordPress dashboard.
- Prepared the current build as the first updater-enabled release for update testing.

## 2.00.1

- Fixed live preview so manual fat / saturates / protein / salt values appear when analysis values are listed.
- Kept the predefined list under other ingredients visible while preserving the simplified additional substances fields.
- Removed duplicate QR preview images from status areas so only the main preview QR remains.
- Reduced the live preview phone frame thickness again.
- Added responsibility / liability notice to setup, admin box and readme.

## 2.00

- Visible dashboard/plugin name remains **Wein E-Label**.
- Version line unified to **2.00**.
- Admin language handling now follows WordPress/user locale more cleanly.
- Missing admin-language entries fall back consistently instead of producing mixed empty output.
- WIPZN HTML ingredient parsing improved for prefixed groups such as acid regulators and stabilizers.

## 1.89.5
- Removed the duplicate bottom preview link in the live preview panel.
- The QR area in the live preview now shows a clean text placeholder when no QR code has been generated yet instead of a broken image placeholder.

# Changelog

## 1.89.0
- Release preparation build.
- Added WordPress-compatible `readme.txt`.
- Added `RELEASE-CHECKLIST.md` for reproducible release testing.
- Hardened settings handling with `check_admin_referer()`, `wp_unslash()` and stricter sanitizing.
- Kept automatic rewrite flush after routing-relevant settings changes.
- Updated package metadata for release.

## 1.88.1
- Fixed false positive “Import + manual changes” state.
- Only show readable field change labels for real manual changes.

## 1.87.1
- Fixed broken URL preview when slug is empty.
- Fixed receiver-mode preview path to show `/e-label/<slug>/`.

## 1.87.0
- Added setup tab with multilingual guide.
- Fixed manual data status logic.

## 1.86.2
- Fixed routing to keep local `/l/<slug>` stable and avoid catch-all conflicts.

## 1.85.x
- Receiver integration, deletion flow, QR preview and UI fixes.
