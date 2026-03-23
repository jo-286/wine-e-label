# Main And Receiver Setup

This document describes the minimum production setup for the two maintained plugins in this repository:

- `Wein E-Label`
- `Wein E-Label Receiver`

The Wine Viewer is intentionally out of scope here.

## Recommended Topology

Use the plugins in this split:

1. Main WooCommerce site:
   - install `wine-e-label.zip`
   - manage products, imports, manual corrections, design, QR codes
2. Receiver site:
   - install `wine-e-label-receiver.zip`
   - receive published labels over REST
   - serve public e-label pages without the shop theme

This gives you a cleaner public label surface and keeps shop logic away from the public e-label domain.

## Main Plugin Setup

On the main site:

1. Install and activate the main plugin.
2. Open the plugin settings.
3. Decide whether labels should be served:
   - locally on the main domain
   - on a subdomain
   - on a receiver domain
4. Configure the QR defaults and publishing target.
5. Open a product and create/import the label data.
6. Generate the label and QR code.

## Receiver Setup

On the receiver site:

1. Install and activate the receiver plugin.
2. Ensure permalinks are enabled.
3. Create or use an administrator account for API access.
4. Generate a WordPress application password for that account.
5. In the main plugin, enter:
   - receiver base URL
   - username
   - application password
6. Use the built-in connection test in the main plugin.

## Production Hardening Checklist

Before go-live, confirm:

- HTTPS is enabled on both sites
- permalinks are active
- the receiver REST endpoints are reachable
- no security plugin or reverse proxy blocks authenticated REST requests
- public label pages are reachable without a shop layout
- QR codes open the expected public label target
- translated label content is visible for `DE`, `EN`, `FR`, and `IT`

## Operational Recommendation

Keep the main plugin as the editorial source of truth.

That means:

- edit data on the main site
- publish to the receiver from the main site
- avoid manual receiver-side content changes except diagnostics

This keeps synchronization predictable and makes duplicate routes easier to detect.
