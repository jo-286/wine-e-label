# Release Notes 2.3.1

## Main plugin

- E-Labels management now expands only real target URLs instead of creating artificial duplicate rows.
- Each target row now shows a direct public link, a location label, and a target-specific QR action.
- Receiver targets are visible in the overview as `Receiver Domain`.
- Admin action buttons in the overview are aligned side by side.
- Visible admin wording and label preview text were cleaned up with proper umlauts.

## Receiver plugin

- Central `nler` labels now render with the same document frame and body classes as the main-domain output.
- Mirrored main-domain and subdomain source targets are visible in receiver admin views to spot duplicates faster.
- Receiver admin location labels were aligned with the main plugin wording.

## Shared improvements

- Main domain and receiver domain now share the same label structure more consistently.
- Design syncing across local, subdomain, and receiver output was hardened.
- Package output, update manifest paths, and repository docs were updated for the renamed `Plugin Installation Files` folder.
