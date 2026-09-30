# ALIEV.IO — Legacy Repository Audit (Phase 1)

> Source audited: `./aev.platforms-master` (local copy of `EmirTheBest7/aev.platforms`, never committed to this repository — confirmed with `git log --all -- aev.platforms-master` and `.gitignore`).
> Audit method: directory inventory, repository-wide pattern scans, reachability checks (who references what), manual reading of the entry points. Findings cite file paths; **no secret or personal value is reproduced here** — labels such as `SECRET_EXPOSURE_01` are used instead.
> Status: **Phase 1 complete. No application code has been changed.**

---

## 1. Executive summary

**What the old site is.** ALIEV.IO ("ΛΞV | Digital studio.") is a hand-written PHP/jQuery site for shared hosting (Hostinger-style `public_html` + cron). Its public face is `page/main/` — a one-page studio site with a WebGL planet, a dot-matrix (Ndot) wordmark style, dark/light theme, a Cmd/Ctrl+J "spotlight" launcher, an app-launcher grid fed by `data.json`, widgets (3D room, clock, calculator), a work-request form and a crypto-price ticker. Around it sits an unfinished "platform" (`home/`): auth, timeline/"Space", messenger, videos, wallet, store, studio, terminal/games — a social-network experiment.

**Size and shape.** 1,585 files / 234 MB. 77 % of the bytes are in `home/` (183 MB: games, music, face/NSFW ML models, uploads, QR libs, vendored minified bundles). `page/` (43 MB) and `_assets/` (8 MB) contain everything the public brand actually needs.

**What still has value.**
- The visual identity: logo SVG family, blue `#0f33ff` accent, Ndot dot-matrix headings, dark/light theming, marquee, loading-bar preloader, notification toasts, rounded app-launcher tiles, hero planet.
- Page content/structure for: home, services, contact, careers, downloads (brand assets), updates (journal), investor-relations, legal.
- The spotlight and app-launcher concepts.
- PWA icon set (`_assets/icon/pwa`).

**What is broken or dead.**
- `HEADER` constant points at `_assets/modules/header/`, which does not exist.
- 2 of 20 launcher tiles have `url: "#Empty"`; 18 of 20 use the *same* icon (`timeline.svg`); several point at `home/*` apps that are experiments, or at `page/empty` / `page/maps` which are "Coming soon" stubs.
- `page/services/` has an empty `index.php` (0 bytes) — the "Services" link on the homepage goes nowhere.
- Footer social links use `#facebook`, `#twitter`, `#instagram`; ~9 links to `#0`/`#link`/`#`.
- Root `sitemap.xml` lists auth/reset pages and job URLs with query strings; `_inc/xml/sitemap.xml` lists only `https://www.aliev.io`.
- `.htaccess` redirects `/` to `/page/main/` and `/robots.txt` to a PHP file with a 301 (crawlers may not follow this for robots).
- Third-party chat widget (intergram) and a `web4ukraine` script are loaded on the homepage with no verification that they are alive.
- Old site host `aliev.io` does not resolve from this environment, so there is no live reference to compare against; visual baselines must be rendered locally (see §12).

**What is a security risk (details §8).** Hardcoded DB and Telegram credentials (public in the old GitHub repo) → treat as compromised; a logically broken environment check (`==` with `||` on string literals, always true); `eval("?>".file_get_contents(...))`; SQL built with string interpolation, including the login query with MD5 passwords; static hard-coded admin tokens; no CSRF anywhere; unvalidated order/contact forms that pipe raw `POST` data straight into a Telegram message; private individuals' names and birthdays in `cron.php` (PII); user uploads and face-recognition models committed to the repository.

**Decision summary.**

| Bucket | What |
|---|---|
| PRESERVE (visual) | Logos, Ndot headings, blue accent, theme toggle, hero planet, marquee, preloader, toast notifications, launcher tile style, spotlight look |
| MIGRATE (content/markup) | `page/main`, `contact`, `careers`, `downloads`, `updates`, `investor-relations`, `legal`, `history`, `services` content |
| REWRITE (logic) | spotlight, launcher, order/contact flow, notification, sessions/auth glue, planet lifecycle, sitemap/robots |
| ARCHIVE (outside production tree) | `home/2be_created`, `home/studio`, `home/timeline`, `home/videos`, `home/wallet`, `home/store`, `home/messenger`, `home/profile` — not rebuilt for the agency site |
| DELETE | `.DS_Store`, `test.php`, `script.sh`, `2be_deleted/`, `_uploads/`, games/ML/model bundles, unused fonts, duplicate jQuery copies, all credentials |

---

## 2. Architecture audit (old)

| Area | Path | Responsibility | Verdict |
|---|---|---|---|
| Root infrastructure | `.htaccess`, `cron.php`, `script.sh`, `test.php`, `robots.txt`, `sitemap.xml` | HTTPS redirect, fbclid strip, `@nickname` rewrite, UA blocklist, cron entry | Rewrite as routing + config; delete `test.php`, `script.sh` (hard-coded host path) |
| Kernel | `_inc/functions.php` | constants, env detection, DB connect, `adminOnly`, `auth`, `logout`, `notify`, `file_get_module`, `random_str` | Split into `Config`, `Database`, `Notifier`, `Auth`; delete `file_get_module` (eval) |
| Helpers | `_inc/helpers/{cryptor,curl-get,text2image,zip-folder}.php` | Misc utilities | Reachability check in Phase 4; default ARCHIVE unless referenced |
| Kernel (cron) | `_inc/cron.php`, `cron.php` | Birthday notifier, DB dump, sitemap regeneration | DELETE as-is (PII + unsafe dump); sitemap becomes static/generated at build |
| SEO/PWA | `_inc/xml/*`, `_inc/.well-known/*` | manifest, robots.php, sitemap(-generator), simple_html_dom | Rewrite; drop `simple_html_dom` (vendored, used only by sitemap generator crawler) |
| Shared assets | `_assets/{css,js,fonts,icon,images,cdn,voices}` | core CSS/JS, fonts, favicons, PWA icons | See §7 |
| Public pages | `page/*` | Brand website | Migrate per §9 |
| Platform | `home/*` | Social-network experiment | Out of scope for the agency site (see §3) |

**Scope decision (default, pending user confirmation — see `MIGRATION.md`):** the revived product is the *agency website*. `home/*` is audited and documented but **not** rebuilt; launcher cards that pointed at it are removed or re-pointed. Reasoning: it is built on SQL concatenation and MD5 against a compromised database, with user data and ML models in the tree; rebuilding it is a separate product, and the brief says dead/not-ready UI must be removed.

**Current (v2) repository state.** PHP 8.3/8.4 CI matrix; `core/auth` (PDO, CSRF, sessions, brute-force guard, audit log, role/permission value objects) + `apps/account` test pages; `website/home/index.html` is **empty**; `CLAUDE.md` is empty; no `composer.json`, `phpunit.xml`, PHPStan or CS-Fixer config although CI calls them (jobs are "dormant"); deploy workflow is an explicit placeholder; README's architecture lists folders that do not exist (`apps/social`, `apps/forum`, …). `core/autoload.php` is a manual PSR-4 shim for `Core\`. These facts drive the new architecture (see `ARCHITECTURE.md` in Phase 2): reuse `core/auth` pieces (`CsrfProtection`, `PdoConnection`, `SessionManager`, `BruteForceGuard` pattern) rather than writing second copies.

---

## 3. `home/` platform inventory

| Dir | Files | Size | Finding | Decision |
|---|---|---|---|---|
| `home/auth` | 9 | 64 KB | Login is `SELECT ... WHERE email='$email' and password='".md5(...)` — SQL injection + unsalted MD5; no CSRF; no session hardening | DELETE (replaced by `core/auth`) |
| `home/timeline`, `home/profile`, `home/messenger`, `home/videos`, `home/store`, `home/wallet` | ~500 | ~15 MB | Interpolated SQL, `href="#"` everywhere, `wallet/lib` vendored phpqrcode/TCPDF | ARCHIVE (not in production tree) |
| `home/studio` | 21 | 24 MB | Upload handling (`move_uploaded_file`), nsfwjs + TF model shards | ARCHIVE |
| `home/_api` | 390 | 48 MB | `UI` (error pages + launcher), `Docs`, `terminal` (games: PacMan, Doom, Dino3D, editors, qr) | Error-page look → REWRITE as 403/404/500 templates; rest ARCHIVE |
| `home/_uploads` | 52 | 9.9 MB | **User uploads, face-api models, a hidden `_admin` page** — `PII_EXPOSURE_02` | DELETE from any tree; never migrate |
| `home/2be_created` | 131 | 85 MB | Music player w/ DB config, blog, settings — unfinished | ARCHIVE |
| `home/2be_deleted` | 101 | 1.2 MB | Name says deleted; contains a second Telegram token (`SECRET_EXPOSURE_03`) and `mysqli` code | DELETE |

---

## 4. Dependency audit

| Dependency | Version seen | Where | Purpose | Still needed? | Strategy |
|---|---|---|---|---|---|
| jQuery | 3.6.0 **and** 3.1.0 (both on `page/main`); 3.4.1 on hester | `page/main/index.php` | DOM, 92 inline `$()` calls | No | Remove; native ES modules |
| Underscore | 1.8.3 | `page/main` | Utility (probably unused) | No | Remove after usage check |
| three.js | r128 (cdnjs) | `page/main`, `planet.js` | Hero planet | Yes (brand) | Self-host pinned ES build, tree-shaken import; WebGL fallback |
| Hammer.js (inlined in `functions-min.js`, 40 KB) | unknown | `page/main` | Touch gestures | Verify use | Replace with Pointer Events or delete |
| normalize.css | 5.0.0 (cdnjs) | `page/main` | Reset | No | Fold a minimal reset into base CSS |
| Bootstrap grid | `_assets/css/bootstrap-grid.css` | `_assets/css` | Layout | Verify | Replace with CSS grid where used |
| Unicons (`uil-*`) | unknown CDN | spotlight, UI | Icons | Partly | Replace with inline SVG set already in `img/icons/` |
| Material Symbols | Google Fonts | spotlight web-search icon | 1 icon | No | Inline SVG |
| Intergram chat widget | `intergram.xyz/js/widget.js` | homepage | Chat → Telegram | Unverified; third-party script with chat id exposure | Remove; replace with contact form (+ Telegram link) |
| web4ukraine | `js.web4ukraine.org` | homepage | Solidarity banner | Unverified URL | Verify; if dead, remove; if kept, self-hosted static banner |
| cryptocompare API | `min-api.cryptocompare.com` | homepage ticker | Live BTC/ETH… prices | Not agency-relevant | Remove unless user wants ticker (rate limits, CSP, cost) |
| CoinMarketCap images | `s2.coinmarketcap.com` | ticker icons | Hotlinked logos | No | Removed with ticker |
| Bootstrap/UIkit | `uikit.min.css` | careers list | Layout | No | Replace |
| Cloudflare CDN availability | — | all | — | — | Self-host; CSP `script-src 'self'` |

All external URL liveness must be verified with network access at Phase 4 (this environment cannot resolve `aliev.io`; third-party hosts were not probed in Phase 1). Nothing is classified "alive" without a probe.

---

## 5. Frontend audit

- **CSS:** `main.css` 62 KB for one page, `core.css` 11 KB, `header.css` 7 KB. Three independent colour vocabularies (see `DESIGN-SYSTEM.md`): core/main use `#0f33ff` accent, `#282a2c/#282828/#0c0c0c` darks, `#fff/#ccc` lights; `header.css` carries a *different* teal/green palette (`#1b5955`, `#5ca084`, `#1CA3F3`) that belongs to an abandoned header module. Only one `:root` token block (`--navbar-height`, marquee sizes); no colour tokens. Dark/light via `[data-theme=dark|light]` attribute.
- **JS:** `spotlight.js` (270 lines), `aev-notify.js`, `planet.js`, `functions-min.js` (minified vendor-like bundle), 2 inline `<script>` blocks, 12 inline `onclick=`, 61 inline `style=`, jQuery throughout. Globals: `POPUP`, `STATE`, `OPTIONS`, `SEARCH`, …
- **Accessibility:** positives — spotlight uses `role="combobox"`, `aria-activedescendant`. Negatives — viewport `user-scalable=no, maximum-scale=1` (blocks zoom), `href="#0"` used as buttons, no reduced-motion handling, icon-only controls without names (to verify per element in Phase 5).
- **Responsive:** breakpoints 1180 / 900 / 767 / 600 / 359 px plus landscape-phone rules; `.device-notification` **blocks** the whole site on small/landscape devices with "please orient your device". Must be replaced with real responsive behaviour.
- **Rendering:** WebGL renderer created with `{alpha:true}`; no `setPixelRatio`, no `dispose`, no resize listener; texture hotlinked from `i.imgur.com`; page-wide `requestAnimationFrame` loop.

---

## 6. Backend audit

| Topic | Finding |
|---|---|
| PHP | Targets old-style PHP 7 (`mysqli`, globals). v2 CI is 8.3/8.4 — no compatibility work needed if rewritten. |
| DB | `mysqli_connect` via globals; errors `echo`ed to the client (`mysqli_connect_error()`); `error_reporting(0)` masks everything. |
| Sessions | Bare `session_start()`; no cookie flags, no regeneration on login, no timeout; `logout()` marked "Not Tested", triggered by GET `?action=logout` (CSRF-able). |
| Authz | `adminOnly()` checks `$_SESSION['user_id']/token_id/access` against **hard-coded token pairs** (`SECRET_EXPOSURE_04`); function also returns before its own redirect (dead code) and sets `global $isAdmin` *after* local assignment. `verified()` has duplicate `case 3`/`case 4` labels (unreachable branches). |
| Forms | 7 forms in `page/*`, **none with CSRF**; order and contact forms post to `#`/self, then call `notify()` with raw `$_POST`. Order number = `ddmmyy` + `_1` — **not unique**, and `idate("y", $timestamp)` uses `$timestamp`, which is not defined in the visible code. |
| Notifications | `notify()` → `file_get_contents()` on a Telegram URL, token embedded, chat id hard-coded, no timeout, no error handling, blocks the request. |
| Uploads | `move_uploaded_file` in `home/studio/*` — no evidence of type/size validation in the scanned snippets. |
| Routing | Filesystem routing; `.htaccess` `RewriteRule` for `@nickname` → `/home/profile/?nickname=` (301, client-visible). |
| Config | No `.env`; environment decided by `HTTP_HOST` string compare (broken, §8). |

---

## 7. Asset audit (by class)

| Asset | Finding | Class |
|---|---|---|
| `page/downloads/logo/*` (ALIEV.svg, ALIEV1/3.svg, ALIEV_original.svg, ALIEV_3D.png, A.svg, App.svg, weblogo.svg, AEV_Code/Dev.svg, Dreamers.svg) | Official logo family; `LOGO` = ALIEV.svg, `LOGO_IO` = ALIEV3.svg | **PRESERVE** (byte-identical) |
| `_assets/icon/pwa/{android,ios,windows11}` | PWA icons | PRESERVE → OPTIMIZE |
| `_assets/icon/season1-3` | Seasonal favicons | PRESERVE (verify use) |
| `_assets/fonts/Ndot-55.otf` | **Nothing brand font; embedded notice restricts use to Nothing brand materials.** Used for dot-matrix headings | **LICENSE RISK** — needs user decision (see §10) |
| `_assets/fonts/SF-Pro.ttf` (5.9 MB) | Apple font; **not referenced by any CSS** | DELETE |
| `_assets/fonts/DotlineBold.ttf` | Not referenced by CSS; license unknown | ARCHIVE pending license |
| `page/main/img/*` | Hero/work/about imagery, 16 UI icons (SVG), `logo.png` | PRESERVE/OPTIMIZE (convert large JPG/PNG → WebP/AVIF, keep originals out of web root) |
| `page/downloads/wallpapers/LooksGood.png` | 30 MB PNG | OPTIMIZE (real size reduction) |
| `_assets/voices` | 1 audio file | Verify use → likely DELETE |
| `_assets/cdn/{framework,4ukraine}` | `temp.min.css`, scratch `index.html` | DELETE |
| `.DS_Store` ×7 | junk | DELETE |

---

## 8. Security audit

| ID | Severity | Finding | Where (category) | Action |
|---|---|---|---|---|
| `SECRET_EXPOSURE_01` | Critical | DB host/user/password/name hard-coded ×2 branches | `_inc/functions.php` | Rotate; never migrate; `.env` |
| `SECRET_EXPOSURE_02` | Critical | Telegram bot token + chat id hard-coded | `_inc/functions.php` | **User must revoke via BotFather**; env only |
| `SECRET_EXPOSURE_03` | Critical | Second Telegram token in a "deleted" messenger | `home/2be_deleted/messenger/chat/hester` | Revoke; delete |
| `SECRET_EXPOSURE_04` | High | Static admin user-id→token pairs | `_inc/functions.php` | Delete; role model from `core/auth` |
| `SECRET_EXPOSURE_05` | Medium | DB connection code/config in unfinished modules (credential content not inspected in Phase 1) | `home/2be_created/**/config.php` | Delete/archive; assume compromised |
| `LOGIC_BUG_01` | High | `if (HOST == "a" \|\| "localhost" \|\| "127.0.0.1")` is always true → “production” branch never runs; dev/prod indistinguishable | `_inc/functions.php`, `test.php` | `APP_ENV` from environment |
| `RCE_RISK_01` | High | `eval("?>".file_get_contents($file))` in `file_get_module()` | `_inc/functions.php` | Delete; explicit templates |
| `INJECTION_SQL_01` | Critical | Login SQL built from `$_POST` email + `md5()` | `home/auth/index.php` | Delete; prepared statements in `core/auth` |
| `INJECTION_SQL_02..n` | High | 7 `SELECT/UPDATE` with `$_GET/$_POST`, 10 interpolated queries | `home/profile`, `home/timeline`, `home/studio`, `home/2be_*` | Archive; no migration |
| `CRYPTO_WEAK_01` | High | MD5 password hashing | `home/auth`, `2be_created/music` | `core/auth/PasswordHasher` |
| `XSS_01` | Medium | Spotlight injects option titles via `innerHTML`; web search URL unencoded `q=${value}` | `page/main/spotlight.js` | Rewrite with `textContent`, `encodeURIComponent` |
| `XSS_02` | Medium | Contact/order values concatenated into messages with no escaping (Telegram parse / HTML contexts) | `page/main`, `page/contact` | Validate + length-limit + plain text |
| `CSRF_01` | High | No CSRF token in any form; logout via GET | all forms | `core/auth/CsrfProtection` on every POST |
| `DOS_01` | Medium | Unthrottled `notify()` — anyone can flood the Telegram chat and the request thread blocks on it (no timeout) | order/contact | Rate limit + timeout + queue-less graceful fail |
| `DATA_INTEGRITY_01` | Low | Non-unique, client-visible order number; undefined `$timestamp` | `page/main` | Server ID (ULID/random) |
| `DB_DUMP_01` | High | `backDb()` dumps DB without escaping, streams to browser, uses `exec('rm …')` | `cron.php` | Delete; use managed backups |
| `PII_EXPOSURE_01` | High | Names + birthdays of private individuals (family, employees of a named retailer) in source | `cron.php` | **Never migrate / never quote; history-scrub advice in final report** |
| `PII_EXPOSURE_02` | High | User upload directory, face models, hidden `_admin` page | `home/_uploads/**` | Delete |
| `HEADERS_01` | Medium | No CSP / HSTS / Referrer / Permissions policy; viewport blocks zoom | `.htaccess` | `SECURITY.md` policy |
| `REDIRECT_01` | Low | `RewriteRule ^(.*) …?nickname=$1` for any URL containing `@`; open to odd paths | `.htaccess` | Explicit route |
| `ROBOTS_01` | Low | `robots.txt` → 301 to PHP; a second robots stanza uses a full browser UA string (meaningless) | `.htaccess`, `robots.txt` | Static `robots.txt` |
| `UA_BLOCK_01` | Info | ~80-entry scraper UA blocklist via `RewriteCond` | `.htaccess` | Obsolete, trivially bypassed; replace by rate limiting |
| `THIRD_PARTY_01` | Medium | Unpinned third-party scripts/images (intergram, web4ukraine, CMC, imgur texture) | `page/main` | Self-host or remove; CSP |
| `UPLOAD_01` | Medium | `move_uploaded_file` without evidence of validation | `home/studio` | Not migrated |

Mandatory user actions (not something code can fix): **rotate the DB password; revoke both Telegram tokens; treat the old GitHub repo history as public and compromised; decide whether to purge PII from that history.**

---

## 9. Page audit

Evidence = inbound references counted across the whole old tree (HTML/PHP/JS/JSON/XML).

| Old path | Purpose | Status | Used? (evidence) | Preserve design? | Migrate? | Replace? | Delete? |
|---|---|---|---|---|---|---|---|
| `page/main/` | Homepage / studio landing | Works (with broken links/forms) | Redirect target of `/` | **Yes** | Markup+CSS+assets | JS, form, ticker, chat | No |
| `page/contact/` | Contact form → Telegram | Works, insecure | Linked from main, sitemap | Yes | Yes | Backend flow | No |
| `page/careers/` (+`list`,`desc`,`team`) | Jobs | `index.php` redirects to `list/`; `desc` reads job by `job_url` via mysqli | Main, sitemap, launcher | Yes | Content only | Data source → static JSON/MD | No |
| `page/downloads/` | Logo/brand/wallpapers | Works; 31 MB | Main, sitemap, 12 refs | Yes | Yes (optimized) | No | No |
| `page/updates/` | Journal/updates | Works | Main, launcher | Yes | Yes | Static data | No |
| `page/investor-relations/` | IR page | Exists (3.7 KB) | Main, launcher | Yes | Review copy | — | No |
| `page/legal/` | Legal text | Exists (2.5 KB) | 0 inbound | Yes | Yes (needed) | Link from footer | No |
| `page/history/` | "Our History" | Exists (1.4 KB) | 0 inbound | Yes | Merge into About | — | No |
| `page/services/` | Services | **Empty `index.php`** (+ `pragueflow/` 1.1 MB subpage) | Linked from main | Yes | Build from homepage services block + PragueFlow case | New page | No |
| `page/hester/` | HesterGPT chatbot UI | Static chat UI; form `action="#"`; Telegram bot link `t.me/Hester_EAbot`; no backend call in `index.php` (its `script.js` not yet inspected — Phase 4) | Main, launcher | Partial | No | Link to Telegram bot externally | Embedded UI: delete |
| `page/maps/` | "Coming soon!" | Stub | Main, launcher | — | No | — | **Yes** |
| `page/empty/` | "Coming soon!" | Stub | Main (3 links) | — | No | — | **Yes** |
| `page/design_store/` | E-commerce demo | Unlinked; POST forms, no CSRF, data in JSON | 0 inbound (contact JSON only) | Partial | No | — | Archive |
| `page/DC25/` | "ΛWDC25" event page (2.3 MB) | Unlinked | 0 inbound | Maybe | Decide with user | — | Archive |
| `page/qirimcz/` | QIRIMCZ & SCOS.PRAHA event/community | Unlinked; external project | 0 inbound | No | No | — | Archive |
| `page/material/`, `page/universal/` | Template experiments | Unlinked | 0 inbound | No | No | — | Delete |

---

## 10. Design, brand and licensing flags

- **Logo:** `page/downloads/logo/ALIEV.svg` is the loading-screen/primary mark; `ALIEV3.svg` the ".IO" lockup. Files will be copied byte-for-byte — never redrawn.
- **`Ndot-55.otf` licensing:** the font's embedded notice states it is Nothing's brand material and may not be used beyond Nothing brand materials. The old design uses it for dot-matrix headlines. This is the visual signature, but redistribution/self-hosting it on a commercial agency site is a legal exposure. **Needs an explicit decision** (keep as-is / replace with a licensed or open dot-matrix face such as *Doto* (OFL) / *DotGothic16* (OFL) which keeps the look without the risk). Until decided, it is migrated *isolated* in one `@font-face` so it can be swapped in one line.
- **`SF-Pro.ttf`:** Apple license forbids web self-hosting and it is unreferenced → delete. The CSS font stack already falls back to `-apple-system, BlinkMacSystemFont`, which renders SF on Apple devices.
- Colours, spacing and motion values are extracted in `DESIGN-SYSTEM.md` (Phase 3).

---

## 11. Algorithm audit

| Algorithm | Location | Finding | Decision |
|---|---|---|---|
| Spotlight (Cmd/Ctrl+J) | `spotlight.js` | Shortcut tracked via `keyCode` state machine (deprecated API; stuck-key bugs); options built as HTML strings; arrow-key/Enter handling not yet reviewed (first ~100 lines read); global state | **Rewrite** as a module with `KeyboardEvent.key`, roving `aria-activedescendant`, `textContent`, delegated listeners |
| Search / app filter | `spotlight.js`, `data.json` | Filters launcher apps; web search unencoded | **Rewrite**, `encodeURIComponent`, search real routes instead of dead apps |
| Theme toggle | `spotlight.js` | `data-theme` from `prefers-color-scheme`; no persistence; no `color-scheme` | **Improve** (persist choice, `color-scheme`, no flash) |
| App launcher grid | `index.php` + `data.json` | 20 static tiles, 18 same icon, 2 `#Empty` | **Rewrite** as data-driven component with validated entries (see `APPLICATIONS.md`) |
| Order number | `index.php` | `ddmmyy_1`, collisions, undefined var | **Delete**, server-side random ID |
| Notification dispatch | `notify()` | Blocking, unbounded, secrets inline | **Rewrite** as `Notifier` service (timeouts, env secrets, safe logs) |
| Random ID | `random_str()` | Uses `random_int` — correct | **Keep** (port) |
| Bot blocking | `.htaccess` | UA blocklist | **Delete**, rate-limit instead |
| Nickname redirect | `.htaccess` | `@…` → profile | **Delete** (profiles not migrated) |
| Planet render loop | `planet.js` | No resize/DPR/dispose/fallback; hotlinked texture | **Rewrite** lifecycle, keep look |
| Toast notifications | `aev-notify.js` | DOM toasts | **Improve** (aria-live, reduced motion) |
| Calculator | widget | uses `eval()` on an assembled string | **Delete** `eval`; ARCHIVE widget (see `WIDGETS.md`) |
| Sitemap generator | `sitemap-generator.php` + `simple_html_dom` | Crawls the live site from cron | **Delete**; static sitemap |
| Birthday cron | `cron.php` | PII, Telegram spam | **Delete** |
| DB backup | `backDb` | Injection-prone, exec rm | **Delete** |

---

## 12. Visual baseline plan

Because `aliev.io` is unreachable from this environment and PHP is not installed locally, the baseline will be produced from the old tree in a scratchpad copy via Docker (`php:8.3-cli`, `PHP_CLI_SERVER_WORKERS=4` — the homepage does `file_get_contents(BASE_URL."_assets/icon/")`, which makes the page request itself and deadlocks a single-worker server). **Never** submit the order/contact forms and **never** run `cron.php` in that copy: `notify()` would post to a real Telegram chat using the compromised token. The copy gets a stubbed `notify()` before it is served. Screenshots: desktop 1440, tablet 820, phone 390 (portrait, landscape), dark + light, for every migrated page.

---

## 13. Open decisions (needed from the owner)

1. **Web root / deploy target.** Old host: shared PHP hosting with cron. v2 deploy workflow is an undecided placeholder. Default proposal: `public/` as document root, with a root `.htaccess` forwarding for hosts that can't change the root.
2. **Scope of `home/*`.** Default proposal: not rebuilt (see §2).
3. **Ndot font.** Keep / replace with an OFL dot-matrix (see §10).
4. **Agency content.** Services/projects copy: reuse old homepage text; anything new will be marked for review.
5. **Ticker, chat widget, web4ukraine.** Default: remove (not agency-relevant / unverified).
6. **Credentials.** Confirm DB password rotated and both Telegram tokens revoked.
