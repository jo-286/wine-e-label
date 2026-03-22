# Wine E-Label

Wine E-Label is a WordPress and WooCommerce plugin system for electronic wine labels under EU e-label requirements.

It is built for real winery workflows, not as a generic food nutrition plugin. The goal is to let wineries manage e-label data directly inside WordPress, generate multilingual label pages, and print one QR code on the bottle that points to a dedicated label page.

## For Most Users

For most users, the important folder in this repository is:

- [Plugin Installation Files](./Plugin%20Installation%20Files)

That folder contains the files most people actually need:

- `wine-e-label.zip`
- `wine-e-label-receiver.zip`
- `Testdata for Zip Import.zip`
- `README.txt`

The current local source folders are maintained as:

- `Wine-E-Label-v2.3.1`
- `Wine-E-Label-Receiver-v2.3.1`

Important:

- Do not use GitHub's default `Download ZIP` button for WordPress installation.
- `Testdata for Zip Import.zip` is not a plugin. It is sample data for testing imports and setup.

## Main Capabilities

Wine E-Label supports a much broader workflow than just generating a QR code.

- product-based e-label management directly inside the WooCommerce product editor
- right-side import and QR workflow in the product editor
- manual entry and manual correction of label data
- import of ZIP, JSON, and HTML source data
- editable imported data inside WordPress after import
- multilingual e-label output in `DE`, `EN`, `FR`, and `IT`
- one QR code per product that leads to a label page with translated content
- local label pages on the same WordPress domain
- label pages on a dedicated subdomain
- remote publishing to a separate WordPress receiver site via REST API
- live preview of the label page in the product editor
- QR code generation and QR download
- configurable QR code size, format, and error correction
- slug management with suggested short URLs
- short label URLs for practical QR printing
- language-aware label links through one public label target
- structured ingredient and substance handling for wine
- grouped ingredient logic with selectable display variants
- organic markers and related footnotes where applicable
- allergen-aware ingredient output
- category translation and translated output texts
- optional Elementor integration for frontend embedding
- database management inside the WordPress admin
- CSV export of stored label data
- QR export from the management area
- receiver connection testing from the admin
- downloadable label assets and installable test data for setup checks
- optional cleanup on uninstall
- subdomain-based label routing with dedicated label targets
- subdomain restriction so label hosts can stay limited to label pages
- separate receiver plugin for a reduced public label output

## What You Manage In The Product Editor

The main plugin is designed around the product editor workflow rather than a separate back-office system.

From the WooCommerce product screen you can work with:

- the product-specific e-label slug
- imported ZIP, JSON, or HTML source files
- manual product data such as product title, wine number, category, and related values
- ingredients, substances, allergens, and E-number related display choices
- nutrition values per 100 ml
- live label preview
- generated public label link
- generated QR code
- import status, page status, and QR status

This is important for wineries because the label workflow stays directly attached to the product that will later carry the printed QR code.

## Why This Matters

The intended workflow is simple for the winery:

1. Open the product in WooCommerce.
2. Import data or enter the values manually.
3. Correct or complete the data directly in WordPress.
4. Generate the label page.
5. Generate the QR code.
6. Print the QR code on the product.

The printed QR code can then point to a label page that serves the required information in different languages. This means you do not need a different QR code for each language version in normal use.

## Publishing Options

Wine E-Label can generate and publish labels in multiple ways.

### 1. Same Domain

You can generate QR codes and label pages directly on the same WordPress or WooCommerce site where the products are managed.

Typical use:

- simple setup
- no second website required
- useful for testing and for compact installations

### 2. Subdomain

You can publish the label page on a dedicated subdomain while still keeping it within the same domain family.

Typical use:

- cleaner separation from the main shop
- better public label URL structure
- still managed close to the main website

### 3. External Receiver Site

You can publish the label page to a second WordPress installation via the Receiver plugin and REST API.

Typical use:

- strict separation between shop and public label output
- clean reduced label pages
- better control over legal and technical separation
- central management of received labels on the receiver instance
- independent receiver design and language settings

## Receiver Connection And Publishing

When you use the Receiver plugin, the connection is set up from the main plugin with:

- the receiver base URL
- a WordPress username on the receiver site
- a WordPress application password
- a built-in connection test in the admin

The receiver plugin then accepts labels via REST, stores them on the receiver site, and serves them without the normal shop theme layout. This makes it possible to keep the shop and the public label destination clearly separated while still managing the source data from one place.

## Manual Input And Import

This system is not import-only.

You can:

- import existing data from ZIP, JSON, or HTML files
- work fully manually without any import file
- import first and then continue editing manually
- correct missing or incomplete imported values inside WordPress
- keep imported data persistent so it stays editable later
- test the workflow with the included sample import ZIP

That is important because real wine data is often incomplete, varies by source, and needs manual refinement before release.

## Multilingual Label Pages With One QR Code

One of the core goals of the project is multilingual output without making packaging unnecessarily complex.

The plugin can generate label pages that:

- show translated label texts
- translate categories and ingredient names
- switch language on the label page itself
- keep the QR workflow practical for real products

In practice, this means one QR code can lead to one label page that serves the information in multiple languages on the destination page.

## Label Routing And Public URLs

The system supports multiple public routing patterns depending on setup.

- local labels can be served from the main WordPress site
- labels can be exposed on a dedicated subdomain for cleaner public presentation
- external receiver labels can be served from a separate WordPress installation
- local short routes stay centered around `/l/<slug>`
- receiver-based public labels use the receiver path prefix
- translated label targets can be requested through the label URL itself instead of printing a separate QR code per language

This routing flexibility is one of the practical strengths of the project because wineries can start simple and later move to a cleaner public setup without replacing the whole workflow.

## Elementor Integration

The repository also includes Elementor support for frontend display scenarios.

This is useful when:

- a winery wants to embed label-related output inside an Elementor-based site
- specific frontend presentations should remain possible without giving up the main e-label workflow

The Elementor widget can be used with:

- the current product context
- a manually selected product ID
- optional title output
- optional expanded nutrition details
- optional ingredient display
- optional link back to the generated e-label page

## Ingredient And Substance Handling

The project includes structured handling for ingredients and wine-related substances.

This includes:

- grouped ingredient selection
- wine-specific substance handling
- translated ingredient names
- selectable display styles where relevant
- E-number display support
- organic markers and organic footnotes where applicable

This matters because wine labels often need more than a plain text field. The plugin is designed to keep ingredient output structured, editable, and easier to maintain over time.

## Admin And Export Tools

The repository is not limited to product-level editing only. It also includes admin tools for managing stored data across products.

These tools include:

- database management from the WordPress admin
- search and filtering of entries
- QR export from the management view
- CSV export of stored data
- settings pages for publishing, QR behavior, language, and setup
- receiver connection testing and diagnostics
- reset and cleanup actions for generated labels and imported data

## Configuration Options

The plugin also supports operational settings beyond the product editor workflow.

Examples include:

- QR code size
- QR code format
- QR error correction level
- local or external publishing mode
- subdomain-based label delivery
- receiver connection settings
- receiver URL normalization and connection testing
- application password based receiver authentication
- admin language and multilingual setup guidance
- optional deletion of stored data on uninstall

## Requirements

The current project is intended for modern WordPress environments.

- WordPress 5.0 or higher
- PHP 8.4 or higher
- MySQL 5.6 or higher
- WooCommerce workflow or another compatible `product` post type workflow
- Composer-based QR generation through `endroid/qr-code`

## Permissions

The project separates product editing from system-level administration.

- product-level label editing and generation use WordPress post editing capabilities
- QR downloads require editor-level product access
- settings, CSV export, REST connection testing, and database management require `manage_options`

This keeps day-to-day label work separate from higher-risk configuration tasks.

## Theme, Template And Developer Integration

The project also keeps room for theme-level and developer-level customization.

- the rendered label template can be overridden from the active theme at `wine-e-label/wine-e-label-secure.php`
- the plugin exposes WordPress hooks such as `nutrition_labels_saved` and `nutrition_labels_deleted`
- shortcode generation can be influenced through the `nutrition_labels_shortcode` filter
- Elementor support is loaded only when Elementor itself is available

This means the plugin can still be adapted for custom WordPress stacks without forcing all changes into the core plugin files.

## Data Storage And Routing Notes

The main plugin stores short URL data in its own database table and combines that with product meta and generated label data.

- custom short URL table: `wp_nutrition_short_urls`
- local route base: `/l/<slug>`
- theme override target: `wine-e-label/wine-e-label-secure.php`
- receiver publishing is based on REST endpoints plus application-password authentication
- uninstall can optionally remove stored label data when that setting is enabled

## Clean Uninstall Behavior

Deactivating the plugin does not automatically remove your label data.

The project supports controlled cleanup behavior:

- plugin options are removed on uninstall
- stored label data can optionally be deleted on uninstall
- uninstall behavior is configurable so wineries do not accidentally lose their work

## Typical Setup

### Minimal Setup

1. Install `wine-e-label.zip` on the main WordPress or WooCommerce site.
2. Import or enter product data.
3. Generate the label page and QR code.

### Extended Setup With Receiver

1. Install `wine-e-label.zip` on the main WordPress or WooCommerce site.
2. Install `wine-e-label-receiver.zip` on a separate WordPress site.
3. Connect both systems with the REST API settings.
4. Test the connection.
5. Publish labels to the receiver site.

### Test Setup

1. Install the main plugin.
2. Use `Testdata for Zip Import.zip` inside the plugin.
3. Verify import, preview, label creation, and QR workflow.

## Repository Structure

- [Plugin Installation Files](./Plugin%20Installation%20Files)
  Ready-to-install plugin ZIP files and test data
- [Wine-E-Label-v2.3.1](./Wine-E-Label-v2.3.1)
  Main WordPress/WooCommerce plugin
- [Wine-E-Label-Receiver-v2.3.1](./Wine-E-Label-Receiver-v2.3.1)
  Receiver plugin for a separate WordPress site
- [scripts](./scripts)
  Release packaging scripts

## Documentation

- Main plugin documentation: [Wine-E-Label-v2.3.1/README.md](./Wine-E-Label-v2.3.1/README.md)
- Install folder guide: [Plugin Installation Files/README.txt](./Plugin%20Installation%20Files/README.txt)
- Release notes: [RELEASING.md](./RELEASING.md)

## Project Origin

This repository started from an earlier GPL-licensed nutrition-label codebase:

- [mmrtxd/nutritionlabels](https://github.com/mmrtxd/nutritionlabels)

The current repository has since been heavily reworked and expanded into a wine-specific e-label system for real winery workflows.

Current project direction and main ongoing development:

- Johannes Reith

Historical origin:

- version 1.0 by Markus Hammer

## License

This repository is distributed under the GNU General Public License v3.0 or later.

See [LICENSE](./LICENSE).

## Third-Party Dependencies

This repository includes or depends on third-party components with their own licenses, including:

- [endroid/qr-code](https://github.com/endroid/qr-code) - MIT License
- `bacon/bacon-qr-code`
- `dasprid/enum`

Their respective notices and licenses remain applicable.
