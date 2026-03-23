# Quality Assurance

This repository now includes a lightweight product-quality baseline for the two maintained plugins:

- `Wein E-Label`
- `Wein E-Label Receiver`

The Wine Viewer is intentionally excluded from these checks for now.

## What Is Automated

### 1. Translation template export

Run:

```powershell
.\scripts\export-i18n-templates.ps1
```

This generates:

- `Wine-E-Label-v2.3.1/languages/wine-e-label.pot`
- `Wine-E-Label-v2.3.1/languages/wine-e-label.pot`
- `Wine-E-Label-Receiver-v2.3.1/wine-e-label-receiver/languages/wine-e-label-receiver.pot`

### 2. Smoke checks

Run:

```powershell
.\scripts\run-smoke-checks.ps1
```

Current smoke checks cover:

- translation template presence
- plugin version readability
- PHP linting for all non-vendor PHP files in the main and receiver plugins

### 3. GitHub Actions quality workflow

On pushes and pull requests that touch the main or receiver plugin, GitHub Actions runs:

- translation template export
- smoke checks

Workflow file:

- `.github/workflows/quality-checks.yml`

## Manual Release Checks

Automated checks are not enough for this project. Before release, also verify manually:

1. Product editor import flow
2. Manual data correction flow
3. Label generation
4. QR code generation and download
5. Public label rendering on the main domain
6. Public label rendering on the receiver domain
7. Receiver synchronization
8. Database/E-Labels management actions
9. Translated label output in all supported languages

## Release Rule

Do not cut a release only because the smoke checks pass.

For these plugins, a release is only considered healthy when:

- smoke checks pass
- packages build successfully
- the manual release checklist has been reviewed
- one end-to-end label flow has been tested on a real or staging product
