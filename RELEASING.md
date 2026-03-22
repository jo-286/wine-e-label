# Releasing

This repository contains two WordPress plugins in one codebase:

- `Wine-E-Label-v2.3.1`
- `Wine-E-Label-Receiver-v2.3.1/wine-e-label-receiver`

GitHub's normal "Download ZIP" button always downloads the whole repository, which is not suitable for direct WordPress plugin installation.

Use the release packaging script instead:

```powershell
.\scripts\build-release-packages.ps1
```

This creates separate WordPress-ready ZIP files in `dist/`:

- `wine-e-label-<version>.zip`
- `wine-e-label-receiver-<version>.zip`

It also refreshes the stable install files in `Plugin Installation Files/`:

- `wine-e-label.zip`
- `wine-e-label-receiver.zip`

And it refreshes the shared GitHub update manifest in `updates/`:

- `plugin-updates.json`

Each ZIP contains exactly one plugin folder with a stable installable folder name:

- `wine-e-label`
- `wine-e-label-receiver`

Both plugins read that same manifest from the shared repository and can therefore receive WordPress dashboard updates from one GitHub repo, while still installing as two separate plugins.

For GitHub Releases:

- create a GitHub Release, or
- run the `Build Release Packages` workflow manually

The workflow uploads the separate ZIP files as artifacts and, for GitHub Releases, also attaches them as release assets.
