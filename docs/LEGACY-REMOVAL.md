# Legacy removal record

Nothing is deleted from `aev.platforms-master` (it is the read-only reference and is git-ignored). "Removed" means **not migrated** into this repository. Each entry records the evidence checked. Items marked ⏳ are decided but executed when their page/feature is migrated.

| Item | Decision | Evidence |
|---|---|---|
| `_assets/fonts/SF-Pro.ttf` (5.9 MB) | **Not migrated (unused, licence-restricted)** | Repository-wide text search (PHP, HTML, CSS, JS, JSON, XML, `.htm`, including generated/manifest files) for `sf-pro`/`sf pro`/`sfpro` found only `font-family: "SF Pro Display"` *family-name fallbacks* in `home/wallet/style.css` and `page/careers/team/style.css` (resolve to an installed system font), plus an unrelated "sf project" in a vendored changelog. **No `@font-face`, no `url(...SF-Pro...)`, no preload, no manifest entry loads the file.** The only `@font-face` in `page/` is Ndot. Apple's licence also forbids web self-hosting. The CSS stack `-apple-system, BlinkMacSystemFont, "Helvetica Neue"` (unchanged intent) renders SF on Apple devices without shipping it. |
| `_assets/fonts/Ndot-55.otf` | **Not migrated (licence)** | Embedded notice restricts use to Nothing brand materials. Used in two places only (clock widget readout, "Settings" heading). Replaced by Doto (OFL). |
| `_assets/fonts/DotlineBold.ttf` | Not migrated | Not referenced by any CSS/PHP; licence unknown. |
| `_inc/functions.php` | Not migrated; split into `Env/Config/Notifier/Logger` | Contains `SECRET_EXPOSURE_01/02/04`, always-true host check, `eval` helper. |
| `file_get_module()` / dynamic `eval` | Removed | `RCE_RISK_01`. Replaced by explicit templates. |
| `cron.php`, `_inc/cron.php`, `script.sh` | Removed | Birthday notifier with private individuals' data (`PII_EXPOSURE_01`), DB dump with `exec('rm …')` (`DB_DUMP_01`), hard-coded host path. Sitemap now static. |
| `test.php`, `.DS_Store` ×7 | Removed | Dev leftovers; `test.php` reproduces the broken host check. |
| `.htaccess` UA blocklist, `@nick` rewrite | Removed | Trivially bypassed; profiles not migrated. HTTPS/fbclid handling re-implemented (app + server config). |
| `home/` (all) | **Not revived** | Owner decision (Phase 2 brief): social-platform experiment out of scope. Auth → replaced by `core/auth`; `_uploads` (PII/biometric) never migrated; `2be_deleted` contains a second bot token. |
| `home/_api/UI` error-page look | ⏳ Rewrite as 403/404/500 templates (done for 404/410/500/…; visual parity pass in Phase 4) | Legacy error pages used `?0x=` query routing. |
| Crypto ticker (`min-api.cryptocompare.com`, CoinMarketCap images) | **Removed** (owner decision) | Third-party calls from every visitor; not agency-relevant. The marquee *style* may be reused for a clients/technology strip. |
| Intergram chat widget | **Removed** (owner decision) | Third-party script; contact form replaces it. |
| `web4ukraine` script | ⏳ Pending liveness/relevance check; will be replaced by a self-hosted static banner or removed | Third-party script at `js.web4ukraine.org`, unverified. |
| Google Analytics beacon (`region1.google-analytics.com`) | Not migrated | Observed in the baseline run; analytics will be decided separately (cookie-consent implications). |
| jQuery 3.6.0 + 3.1.0, underscore 1.8.3, normalize 5.0.0 (CDN), `open-props` import, Unicons/Material Symbols, UIkit | **Removed** | Native JS/CSS; `open-props` import has no referenced variables. |
| `functions-min.js` (40 KB minified bundle) | ⏳ Remove unless Phase 4 finds a live caller | Appears to be an embedded gesture library. |
| Calculator widget (`eval`), clock widget | ⏳ Not migrated | `WIDGETS.md`. |
| `page/hester` (HesterGPT UI) | ⏳ Replace with external link to the Telegram bot if alive, else drop | No backend call in `index.php`. |
| `page/maps`, `page/empty` | Removed → 410 | "Coming soon" stubs. |
| `page/design_store`, `page/material`, `page/universal`, `page/qirimcz` | Removed → 410 | Zero inbound references. |
| `page/DC25` | ⏳ Owner decision | Zero inbound references, 2.3 MB. |
| `device-notification` overlay | Removed | Blocked small/landscape devices; site is now responsive. |
| `_assets/icon/season2` | Not migrated | Never selected by `_assets/icon/index.php`. |
| Mapbox embed on contact page | Not migrated (⏳ replace with static office list after owner confirms details) | Third-party script + token in `page/contact/script.js`. |
