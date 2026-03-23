# Translations

The maintained plugins now use a shared translation workflow with:

- one active text domain per plugin
- generated POT templates
- generated PO and MO language files
- UTF-8 source and output files

## Active Text Domains

### Main plugin

- `wine-e-label`

### Receiver plugin

- `wine-e-label-receiver`

## Generate Templates

```powershell
.\scripts\export-i18n-templates.ps1
```

Generated files:

- `Wine-E-Label-v2.3.1/languages/wine-e-label.pot`
- `Wine-E-Label-Receiver-v2.3.1/wine-e-label-receiver/languages/wine-e-label-receiver.pot`

## Build Language Files

```powershell
php .\scripts\build-language-files.php
```

Generated locales:

- `de_DE`
- `de_DE_formal`
- `en_US`
- `fr_FR`
- `it_IT`

Generated file pairs:

- `.po`
- `.mo`

## Release Rule

Before packaging a release:

1. regenerate the POT templates
2. rebuild the PO and MO language files
3. run the smoke checks

## Encoding Rule

Save translation-related source files as UTF-8.

That is especially important for:

- German umlauts
- French accents
- translated preview strings
- plugin descriptions and admin guidance
