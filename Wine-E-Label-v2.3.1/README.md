Johannes Reith, Markus Hammer

# Wein E-Label

## Release Preparation

This repository contains the release-prepared plugin package including changelog and release checklist. Use `RELEASE-CHECKLIST.md` before distributing a build.

## Overview

Wein E-Label is a WordPress and WooCommerce plugin system for electronic wine labels under EU e-labelling requirements.

It is designed for real winery workflows:

- maintain wine e-label data directly in the product editor
- import existing data from WIPZN ZIP, JSON, or HTML files
- manually correct and complete imported values
- generate a dedicated e-label page per product
- create QR codes that point directly to the label page
- provide multilingual output in `DE`, `EN`, `FR`, and `IT`
- publish labels either locally on the same site or to a separate receiver instance

This is not a generic food nutrition plugin. The current project is focused specifically on wine e-label production and the day-to-day workflow of a winery.

## Project Origin

This project started from an earlier open-source nutrition-label codebase:

- [mmrtxd/nutritionlabels](https://github.com/mmrtxd/nutritionlabels)

However, the current codebase has been heavily reworked and expanded for wine-specific e-label use cases. The original project should be understood as a historical starting point, not as an accurate description of the current feature set or product direction.

In practical terms, this plugin now differs substantially in:

- domain focus: wine e-labels instead of general product nutrition labels
- backend workflow: product-editor-driven label creation instead of a generic nutrition entry flow
- import architecture: WIPZN and file import workflows
- output architecture: local label pages and optional receiver-based publishing
- multilingual handling across admin, content, preview, and label output
- winery-specific manual correction and status workflow

## Authorship And Licensing

The current Wein E-Label project is primarily developed and maintained by Johannes Reith.

At the same time, this repository conservatively acknowledges that the project originated from an earlier GPL-licensed codebase by Markus Hammer and may still contain code derived from that original version in modified form.

The intended attribution line for the current project is therefore:

- current project direction and main ongoing development: Johannes Reith
- historical origin: version 1.0 by Markus Hammer
- license: GNU General Public License v3 or later

This repository does not claim that Markus Hammer is responsible for the current feature set or later development stages beyond the original starting point. It does, however, preserve the origin history and GPL continuity of the codebase.

## What The Plugin Does

Wein E-Label provides a structured workflow for creating and maintaining electronic wine labels:

1. Open a WooCommerce product.
2. Define or confirm the slug.
3. Import a ZIP, JSON, or HTML source file if available.
4. Review and edit imported data in WordPress.
5. Check the live preview and resulting label URL.
6. Generate the e-label page.
7. Generate and download the QR code.

The goal is to keep imported data editable, keep the workflow understandable for non-technical users, and keep the final label page as clean and reduced as possible.

## Main Features

- **Right-side product editor metabox** as the main operational UI
- **Slug management** with suggestions based on wine number
- **WIPZN ZIP / JSON / HTML import**
- **Persistent editable imported data**
- **Manual completion and correction after import**
- **Wine-specific structured ingredient and substance handling**
- **Dedicated e-label page generation**
- **QR code generation and download** in `PNG` or `SVG`
- **Live preview in the product editor**
- **Multilingual label output** in `DE`, `EN`, `FR`, and `IT`
- **Local label mode** on the same WordPress site
- **Optional receiver mode** via REST API to a separate WordPress instance
- **Database management and CSV export**
- **Subdomain-based label URLs** for clearer public label access

## Local And Receiver Modes

The plugin supports two publishing modes and is intentionally not limited to only one of them.

### Local Mode

Generate the e-label directly on the same WordPress installation.

Typical URL shape:

```text
https://example.com/l/abc12
https://example.com/l/abc12-de
```

### Receiver Mode

Publish the label content to a separate WordPress receiver instance via REST API.

This mode is useful when the public e-label page should be separated from the main shop website and its theme, marketing, or tracking environment.

Receiver setup requires:

- receiver base URL
- API user
- application password
- active receiver plugin on the target site

## Data Sources

The plugin can combine multiple input sources:

- manual entry in the product editor
- imported WIPZN ZIP files
- imported JSON files
- imported HTML files
- existing product-related values

Imported data is not meant to be a black box. After import, values remain visible and editable in WordPress.

## Multilingual Output

Multilingual output is a core feature, not an optional add-on.

Supported languages:

- German
- English
- French
- Italian

Language affects:

- e-label output
- ingredient and substance naming
- category labels
- preview content
- generated URLs with language suffixes
- relevant admin texts and setup guidance

## QR Code Generation

Each product can generate a QR code that links directly to its e-label page.

- download formats: `PNG` and `SVG`
- QR generation runs locally on the server
- no external QR service is required

QR generation uses the [endroid/qr-code](https://github.com/endroid/qr-code) library.

## Requirements

- WordPress 6.0 or higher
- WooCommerce product workflow, or another compatible `product` post-type workflow
- PHP 8.4 or higher
- MySQL 5.6 or higher

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin in WordPress.
3. Open **Wein E-Label > Einstellungen**.
4. Start with the **Einrichtung / Setup** tab.

## Recommended Workflow

- open a wine product
- set or confirm the slug
- import available source data
- review import state and manual data state
- correct or complete values
- verify preview and final URL
- generate label page and QR code
- download the QR code for print usage

## Legal Notice

This plugin is a technical aid for creating electronic wine e-labels. The user is solely responsible for the factual accuracy, completeness and legal review of all entered, imported, translated or generated data.

Use of the plugin does not replace legal advice. Despite careful development, no warranty is given for the legal admissibility, completeness or error-free nature of the generated content in every individual case, to the extent permitted by law.

## License

Primary ongoing development and maintenance:

- Copyright (c) 2026 Johannes Reith - https://reithwein.com

Historical project origin:

- Based on earlier GPL-licensed work originating from version 1.0 by Markus Hammer - https://github.com/mmrtxd/

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.

## Third-Party Dependencies

This project includes or depends on third-party components with their own licenses. In particular:

- [endroid/qr-code](https://github.com/endroid/qr-code) - MIT License
- `bacon/bacon-qr-code` - transitive dependency used via Composer
- `dasprid/enum` - transitive dependency used via Composer
- Tailwind CSS build output in `assets/css/style.css` retains its upstream MIT notice

The QR code functionality is not original to this project alone. It relies on the `endroid/qr-code` library and its dependency chain, which remain subject to their respective upstream licenses and notices.

## Support

For issues and feature requests, please open a GitHub issue.
