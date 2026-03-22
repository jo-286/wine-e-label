# Release Checklist

## Functional
- [ ] Fresh install works without PHP notices.
- [ ] Existing install upgrades without losing stored label data.
- [ ] Local `/l/<slug>/` labels render correctly.
- [ ] Receiver mode publishes and deletes correctly.
- [ ] URL preview matches the real generated link.
- [ ] Empty slug state does not generate broken preview output.
- [ ] QR generation works for PNG and SVG.
- [ ] Delete generated label removes QR + generated page without wiping import/manual data.
- [ ] Reset all removes import, manual data and generated output.
- [ ] Source summary only shows manual changes when they are real.

## Import / Data
- [ ] ZIP import works.
- [ ] JSON import works.
- [ ] HTML import works.
- [ ] Imported values can be corrected manually.
- [ ] Clearing manual data resets manual status correctly.

## Language / UI
- [ ] Setup tab is first.
- [ ] Setup page renders in DE / EN / FR / IT.
- [ ] Live preview language follows selected label language.
- [ ] Visible admin texts do not show broken mixed-language strings.

## Routing / Deployment
- [ ] Standard local mode uses the current WordPress domain automatically.
- [ ] External receiver mode does not break local `/l/<slug>` testing.
- [ ] Normal WordPress pages still resolve correctly after activating the plugin.
- [ ] Rewrite rules refresh correctly after changing routing-related settings.

## Security / Admin
- [ ] Settings save requires valid admin nonce.
- [ ] Critical AJAX actions require nonce and capability checks.
- [ ] REST credentials are not echoed back into the UI in plain text unexpectedly.

## Compliance / Real-world
- [ ] Final e-label page contains no shop, marketing or tracking content.
- [ ] Local, subdomain and receiver targets have been tested in the real environment.
