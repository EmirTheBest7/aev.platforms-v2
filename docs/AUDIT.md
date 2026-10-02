# ALIEV.IO — legacy repository audit

> Source audited: `./aev.platforms-master` (git-ignored local copy of `EmirTheBest7/aev.platforms`; never committed here). History read from a scratch clone of the public repository (211 commits).
> Method: directory inventory, repository-wide pattern scans, reachability (who references what), manual reading of entry points, git history, the owner's own notes and sibling repositories.
> **Revision 2 — "archaeology first".** Revision 1 of this audit proposed deleting or archiving large parts of the tree. That was the wrong default and has been withdrawn: the audit now classifies, it does not prune. See `PROJECT-VISION.md` (why things exist), `HISTORICAL-FEATURES.md` (feature inventory), `CHANGE-LOG.md` (what changed and why).
> Secrets and personal data are referenced by label only (`SECRET_EXPOSURE_nn`, `PII_EXPOSURE_nn`); no value is reproduced.

---

## 1. Executive summary

**What the old site is.** ALIEV.IO ("ΛΞV", "Λ L I Ξ V Platforms") is a PHP/jQuery site built over years as an **ecosystem entry point**: a polished public layer (`page/`) in front of a registered-user platform (`home/`: auth, Space social feed, profiles, Studio, Messenger, Videos, Wallet, Store), a developer layer (terminal tools/games, Docs, API keys, HesterGPT AI) and per-user hosting. The owner's architecture note ("CVX Arch 4.3.1") calls `/home` "dynamic pages | registered users" and `/page` "static pages".

**Size/shape.** 1,585 files / 234 MB. `home/` is 183 MB (largest: Pac-Man game, music samples, NSFW/face ML models, a 21 MB video). `page/` 43 MB. `_assets/` 8 MB.

**What has value.** All of it is treated as valuable until the owner says otherwise. The *identity* (logo family, dark UI, blue `#0f33ff`, rail navigation, dot-matrix globe, toasts, launcher, spotlight) is the first thing to protect; the *ideas* (ecosystem launcher, AI assistant, token economy, social platform, developer playground, hosting) are the product.

**What is broken or incomplete** (details in §6 and `HISTORICAL-FEATURES.md`): the crypto ticker's data source now returns HTTP 401; `docs.aliev.io` and `aliev.io` do not resolve; several launcher destinations never existed (Finance) or are stubs (Maps); Settings controls with no handlers (Shortcuts, Language, "Functional key"); `{{ }}` template placeholders left in the menu; mobile social icons reference SVG symbols that don't exist; services/history/legal pages empty.

**What is insecure** (§5): five exposed credentials in source, two SQL-injection sites, MD5 passwords, `eval` helper and calculator, no CSRF anywhere, a remotely-controlled redirect script, a hidden affiliate-tracker iframe, private individuals' data in `cron.php`, user data and biometric-model demos in `_uploads`. **These are implementation faults; the features behind them are kept and rebuilt securely.**

**Default decision for every subsystem: PRESERVE.** Counts of work by class are in `HISTORICAL-FEATURES.md`.

---

## 2. Relationship between this repository (v2) and the legacy one

| Question | Answer (evidence) |
|---|---|
| Is v2 "a smaller agency site"? | No. v2's own README plans `apps/{social,messenger,forum,store,wallet,studio,terminal}`, `core/*`, `api/*`, `website/*` — the legacy platform re-planned module by module. |
| What exists in v2 today? | `core/auth` (PDO, Argon2id, CSRF, sessions, brute-force guard, roles/permissions, audit log), `apps/account` test pages, `api/terminal` skeleton, `app/` front-controller + Notifier + security headers + contact flow (this revival), Docker, CI. |
| What must be (re)built? | Everything the legacy platform did, on the new architecture, in an order the owner chooses (`MIGRATION.md`). |
| Why was the legacy tree kept out of git here? | It contains credentials and personal data; it is a **read-only historical reference** (git-ignored), not a place to commit from. |

## 3. Architecture audit (legacy)

| Area | Path | Responsibility | Verdict |
|---|---|---|---|
| Public layer | `page/*` | Brand site, lead generation, events, docs, careers | Preserve; port page by page |
| Platform | `home/*` | Accounts + social + creation + messaging + media + wallet/store (some prototypes) | Preserve; rebuild securely on `core/auth` in phases |
| Developer layer | `home/_api/{UI,Docs}` | Terminal (pages/tools/admin/games), docs app, site error pages | Preserve |
| Hosting | `home/_uploads/user_*` | Per-user web space (`web/`, `web_hidden/`) | Preserve the concept; data is private (never committed) |
| Planned | `home/2be_created/*` | Music, blog, re:search, settings, hiring/projects/team | Preserve; INCOMPLETE |
| Owner-superseded | `home/2be_deleted/*` | Pyper, WebOS admin, dashboard concept, old messenger/studio… | **POSSIBLE DEPRECATION** (owner-labelled); keep; one file has a secret |
| Kernel | `_inc/functions.php`, `_inc/helpers/*`, `cron.php` | Constants, DB, auth helpers, Telegram `notify()`, QR, cron | Capabilities preserved, implementation replaced |
| Shared front-end | `_assets/*` | `core.css/js`, fonts, icons (season1–3, PWA sets), media, video player, experiments | Preserve all |
| Root | `.htaccess`, `robots.txt`, `sitemap.xml`, `test.php`, `script.sh` | Rewrites, SEO, dev leftovers | Behaviours preserved in v2 routing/config |

## 4. Dependency audit

| Dependency | Where | Purpose | Status (probed) | Strategy |
|---|---|---|---|---|
| jQuery 3.6.0 **and** 3.1.0 (+ 3.4.1 on Hester), underscore 1.8.3 | `page/main`, `page/hester` | DOM/animations | alive (CDN) | Load once, self-hosted, or rewrite to native — behaviour unchanged |
| Hammer.js (bundled in `functions-min.js`) | `page/main` | swipe for section scroller | bundled | replace by Pointer Events or keep |
| three.js r128 | `page/main` | Globe | alive (cdnjs) | Self-host pinned |
| Globe texture | `i.imgur.com/JLFp6Ws.png` | Earth map overlay | alive | Self-hosted copy made |
| Unicons v4 (`uil-*`) line font | `core.css` | Icons everywhere | alive (CDN, but Cloudflare-challenged in headless) | Self-hosted woff2 (Apache-2.0) |
| `open-props` CSS | `main.css` @import | none referenced | alive | Not needed (no variables used) |
| normalize.css 5.0.0 | `page/main` | reset | alive | Self-host or minimal reset |
| CryptoCompare `pricemulti` | ticker | prices | **HTTP 401** (needs API key now) | Replace source (CoinGecko keyless works; see MAIN-PAGE.md) |
| CoinMarketCap images, GitHub raw (AEVT/AEVD logos) | ticker icons | logos | alive | Self-hosted copies made |
| Intergram widget (`intergram.xyz/js/widget.js`) | chat | support chat | **alive** | Keep; pinned self-hosted copy; `disableLoadmill` |
| Web4Ukraine (`js.web4ukraine.org`) | `page/main` | solidarity | alive | **Security**: remote redirect — do not load (§5) |
| Google Gemini API | `page/hester/script.js` | HesterGPT | alive; key exposed | Server-side proxy |
| Mapbox (`api.tiles.mapbox.com`) | `page/contact` | map | alive | Keep concept; restrict token or alternative |
| Discord WidgetBot | `investor-relations` | News tab | alive | Keep; restricted embed |
| GitHub-hosted room images (githack) | 3D room | decoration | alive | Self-hosted copies made |
| `docs.aliev.io` / `aliev.io` / `qirimtalk.com` | launcher targets | docs/site | **NXDOMAIN** | Owner supplies destinations (`DOCS_URL` etc.) |
| PHP 7-style `mysqli`, globals | everywhere | DB | n/a | PDO repositories |

## 5. Security audit

| ID | Sev. | Finding | Where | Action (feature is kept) |
|---|---|---|---|---|
| `SECRET_EXPOSURE_01` | Critical | DB host/user/password/name hard-coded ×2 | `_inc/functions.php` | Owner rotates; env config |
| `SECRET_EXPOSURE_02` | Critical | Telegram bot token + chat id | `_inc/functions.php` | Owner revokes; env config |
| `SECRET_EXPOSURE_03` | Critical | Second bot token | `home/2be_deleted/messenger/chat/hester` | Owner revokes |
| `SECRET_EXPOSURE_04` | High | Static admin user-id→token pairs | `_inc/functions.php` | Replace with roles |
| `SECRET_EXPOSURE_05` | Medium | DB connection code in prototypes | `home/2be_created/**/config.php` | Assume compromised |
| `SECRET_EXPOSURE_06` | Low | Mapbox public (`pk.`) token | `page/contact/script.js` | Restrict by URL |
| `SECRET_EXPOSURE_07` | **Critical** | **Google Gemini API key** in client JS | `page/hester/script.js` | **Owner revokes**; server-side proxy keeps HesterGPT working |
| `SECRET_EXPOSURE_08` | **Critical** | Bot token + chat id | `home/_api/UI/terminal/Page/valentine/yes_page.php` | **Owner revokes** (may equal 02/03; assume not) |
| `SECRET_EXPOSURE_09` | Info | Intergram chat id = the owner's personal Telegram id, public by design of Intergram | `page/main/index.php` | Keep widget; value from env, not source; owner may prefer a dedicated bot/group |
| `INJECTION_SQL_01` | Critical | Login SQL from `$_POST` + MD5 | `home/auth/index.php` | Rebuild on `core/auth` |
| `INJECTION_SQL_02` | **Critical** | **`$_GET['job_url']` interpolated into SELECT**; URL pattern is in the old sitemap | `page/careers/desc/index.php` | Prepared statement |
| `INJECTION_SQL_03…` | High | ~16 further string-built queries | `home/profile`, `timeline`, `studio`, `2be_*` | Repositories when each app returns |
| `ERROR_DISCLOSURE_01` | Medium | `or die(mysqli_error())` printed to visitors | careers list/desc, `connect()` | Generic errors |
| `CRYPTO_WEAK_01` | High | MD5 passwords | `home/auth`, music prototype | Argon2id (`core/auth`) |
| `RCE_RISK_01` | High | `eval("?>".file_get_contents())` helper | `_inc/functions.php` | Explicit templates |
| `RCE_RISK_02` | Medium | `eval()` of the calculator expression | `widgets/calculator/script.js` | Safe arithmetic parser |
| `THIRD_PARTY_RISK_01` | **High** | **Web4Ukraine script POSTs the page URL to a remote server and, if the answer contains a URL, calls `top.location.replace()`** — the vendor can redirect any aliev.io visitor anywhere (bots excluded) | `page/main/index.php` | Do not load it. Keep the cause (#StopTheWar card, 4ukraine page) self-hosted |
| `THIRD_PARTY_RISK_02` | Medium | Intergram widget injects a hidden affiliate-tracker iframe (`loadmill`, `sandbox allow-scripts allow-same-origin`) unless `disableLoadmill` is set | Intergram `widget.js` | Self-hosted pinned copy + `disableLoadmill: true` |
| `XSS_01/02`, `CSRF_01`, `DOS_01`, `DATA_INTEGRITY_01` | Med/High | Spotlight `innerHTML` + unencoded `q=`; unvalidated order/contact POST → Telegram; no CSRF; no rate limit; non-unique order numbers | `page/main`, `page/contact` | v2 contact flow (done) + spotlight rewrite |
| `DB_DUMP_01` | High | Dump streams to browser, `exec('rm …')` | `cron.php` | Backup concept kept, safe implementation |
| `PII_EXPOSURE_01` | High | Names + birthdays of private individuals hard-coded | `cron.php` | Never carried; birthday *feature* kept for registered, consenting users |
| `PII_EXPOSURE_02` | High | User folder with SQLite DBs, face-API demo, hidden admin page | `home/_uploads/**` | Never committed; hosting concept kept |
| `HEADERS_01`, `REDIRECT_01`, `ROBOTS_01`, `UA_BLOCK_01` | Low/Med | No CSP/HSTS; `@` rewrite for any URL containing `@`; robots via 301; UA blocklist | `.htaccess` | v2 config (done / planned) |
| `UPLOAD_01` | Medium | `move_uploaded_file` without evident validation | `home/studio/*` | Validate when Studio returns |

**Owner-only actions** (code can't do them): rotate DB password; **revoke** the Telegram tokens (02, 03, 08) and the Gemini key (07); restrict the Mapbox token; decide on history purge of the public repository (`SECURITY.md`).

## 6. Frontend audit (main page)

- **CSS:** `main.css` 62 KB, `core.css` 11 KB, `header.css` 7 KB (abandoned teal header theme, unreferenced by rendered pages — kept in the tree). Only one `:root` block; dark-only (no `[data-theme]` rule in `main.css`).
- **JS:** `spotlight.js` (270 l), `aev-notify.js`, `planet.js`, `functions-min.js` (Hammer + the scroller/slider controller), `core.js` (title swap, nav toggle, preloader, ripple button, cookies, canvas grid), two inline script blocks, 12 inline `onclick`, 61 inline `style=`, jQuery throughout.
- **Defects found:** `.ripple-button` listener attached only to the first element; `onclick="#"`; `onclick` double-navigation on promo cards; viewport meta disables zoom; `device-notification` overlay blocks landscape phones/<360 px; `$('#navbarCollapse')` toggle on an `<a href="#">`; unencoded web-search; globe has no resize/DPR/dispose/WebGL fallback; notification code logs on every mouse move.
- **Accessibility:** spotlight has combobox roles (good); icon-only controls unnamed; no reduced-motion handling.
- **Responsive:** breakpoints 1180/900/767/600/359 + landscape; rail ≥544 px, top bar below.

## 7. Asset audit — all PRESERVE

| Asset | Finding | Action |
|---|---|---|
| `page/downloads/logo/*` | Official logo family | Keep byte-identical; copied to `public/assets/brand/` |
| `_assets/icon/season1-3`, `pwa/*` | Icon sets; season2 never selected by code (purpose unclear — owner review) | Keep all; season1/3 + PWA used in v2 |
| `_assets/fonts/Ndot-55.otf` | Display font used in settings/widgets ("Custom Fonts Support", v168.7.2). **Licence restricts use to Nothing's brand materials.** | Keep in historical tree; owner decides; v2 uses Doto (OFL) meanwhile |
| `_assets/fonts/DotlineBold.ttf` | Unreferenced; licence unknown; possible link to the "my own font" idea | Keep; owner review |
| `_assets/fonts/SF-Pro.ttf` | Committed on purpose ("Create SF-Pro.ttf", 2024-09-09); unreferenced by CSS; Apple licence forbids web self-hosting | Keep in historical tree; not shipped by v2; owner review |
| `page/main/img/*` | Hero/work/about images, line icons, flag | Copied unchanged to `public/assets/images/home/` |
| `page/downloads/wallpapers/LooksGood.png` (30 MB) | Brand wallpaper | Keep original; an optimised derivative may be *added* |
| `_assets/voices/start-up.mp3` | Used by profile-setup finale | Keep |
| `core-gpu.js`, `origElectron.js`, `aev-video.*`, `cdn/*` | Experiments/utilities | Keep |
| `.DS_Store` ×7 | macOS metadata | Only item that is plainly accidental; harmless to omit from v2 (never committed there) |

## 8. Page audit

| Old path | Purpose | Class | Evidence of use | Preserve design | Migrate |
|---|---|---|---|---|---|
| `page/main/` | Landing / ecosystem entry | works with defects | `/` redirect target; sitemap | **Yes** | Yes — in progress (`MAIN-PAGE.md`) |
| `page/contact/` | Offices, animated fish, form | SEC-RISK (form) | menu, sitemap | Yes | Yes |
| `page/careers/` (list, desc, team) | Jobs + team | SEC-RISK (SQLi, errors) | menu, sitemap, cards | Yes | Yes |
| `page/downloads/` | Brand kit + docs | works | menu, sitemap | Yes | Yes |
| `page/updates/` | Release notes | works | launcher "Journal" | Yes | Yes |
| `page/investor-relations/` | IR + Discord news | works | menu, launcher | Yes | Yes |
| `page/services/` (+`pragueflow`) | Services + client showcase | INCOMPLETE | menu cards | Yes | Yes |
| `page/hester/` (+`avrora`,`1o`) | AI assistant | SEC-RISK (key) | launcher, cards, iframe | Yes | Yes |
| `page/maps/` | Maps app | INCOMPLETE | launcher | Yes | Complete |
| `page/design_store/` | Store concept | EXPERIMENTAL | none inbound | Yes | Later |
| `page/DC25/`, `page/qirimcz/` | Event records | past events | none inbound | Yes | As archive pages |
| `page/material/`, `page/universal/` | UI experiments | EXPERIMENTAL | none inbound | Yes | Later |
| `page/history/`, `page/legal/` | Stubs | INCOMPLETE | none inbound | Yes | Content by owner |
| `page/empty/` | "Coming soon" state | designed state | 3 menu links | Yes | Reuse as the designed pending state |

## 9. Algorithm audit — preserve behaviour, replace implementation

| Algorithm | Original behaviour | Faults | Plan |
|---|---|---|---|
| Spotlight | ⌘/Ctrl+J popover; filter built-in actions + apps; Enter runs; web-search fallback | `keyCode` state machine, `innerHTML`, unencoded query | Same UX, `KeyboardEvent.key`, text nodes, `encodeURIComponent` |
| Section scroller | wheel (±50 delta, 800 ms lock), ↑/↓, swipe; wrap around; CTA → last | jQuery, passive-wheel issue | Same thresholds/transitions, native |
| Works slider | rotates left/center/right classes with 400 ms fade; wraps | jQuery | Same |
| Preloader | wait 2 s then fade | strands without JS | Same timing + fail-safe |
| Title swap | random title on blur | brittle path split | Same |
| Notification stack | create/auto-dismiss/hover glow | console spam | Same, quiet |
| Order number | `ddmmyy_1` | not unique | Server-side random reference |
| `random_str` | CSPRNG string | none | Port as-is |
| Birthday cron | notify on `users.birth` match + private list | PII list | Keep for opted-in users |
| `verified()` | tier → icon | duplicate `case`s | Unique tier map |
| Ticker | fetch → text; icons | 401, no fallback | Server cache + fallback |
| Seasonal icon | by date | none | Ported (`SeasonalIcons`) |

## 10. SEO audit

Old sitemap lists `/page/main/`, careers list, downloads, contact, `home/auth/`, three `desc/?job_url=…` and `auth/reset/`; `_inc/xml/sitemap.xml` lists only `https://www.aliev.io`; `robots.txt` is reached by a 301 to PHP; meta description/OG exist on `page/main` ("We design and develop beautiful and effective digital products… Based in Prague."). Auth/reset URLs should not be indexed. Handled in v2 (`SEO.md` when pages land).

## 11. Open items requiring the owner

See `PROJECT-VISION.md` §10 and `MIGRATION.md` "Decisions". Highlights: revoke the credentials in §5; which platform apps return first; font decisions (Ndot, DotlineBold, SF-Pro); Liquid-Glass intent; destinations for Docs/Finance/Maps/YouTube/Facebook/Twitter; `AEVD`/`AEVT` price sources; Intergram chat target; Web4Ukraine replacement.
