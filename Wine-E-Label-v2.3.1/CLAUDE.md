# CLAUDE.md

This file provides guidance to Claude Code when working with code in this repository.

## Project Overview

WordPress plugin (`Wine E-Label`) for winery-focused electronic labels with QR code generation, short URL routing, import workflows, and optional receiver publishing.

## Development And Deployment

- No build step required: PHP files are executed directly by WordPress.
- Tailwind CSS is precompiled to `templates/style.css` from `templates/input.css`.
- Deploy by copying the plugin folder to `wp-content/plugins/` and activating it in WordPress.
- Requires WordPress 5.0+, PHP 8.4+, and MySQL 5.6+.

## Architecture

Entry point:

- `wine-e-label.php`

Core files in `includes/`:

- `class-wine-e-label-db-extended.php`
- `class-wine-e-label-url.php`
- `class-wine-e-label-qr.php`
- `class-wine-e-label-importer.php`
- `class-wine-e-label-manual-builder.php`
- `class-wine-e-label-frontend.php`
- `class-wine-e-label-elementor.php`
- `class-wine-e-label-admin-i18n.php`
- `class-ingredients.php`

Admin UI in `admin/`:

- `class-wine-e-label-admin-extended.php`
- `working-metabox.php`
- `wine-e-label-db-management.php`
- `wine-e-label-settings-page-simple.php`

Frontend template:

- `templates/wine-e-label-secure.php`

Receiver plugin bootstrap:

- `Wine-E-Label-Receiver-v2.3/wine-e-label-receiver/wine-e-label-receiver.php`

## Database

The plugin uses the custom table `wp_nutrition_short_urls` and WordPress product meta for the label workflow.

## Security Patterns

Follow the existing WordPress patterns:

- nonce verification
- capability checks
- input sanitizing
- output escaping
