# The main page (`page/main`) — audit, dependency map and integration plan

Source of truth for UI/UX: legacy `page/main` (`aev.platforms-master/page/main`, public mirror `EmirTheBest7/aev.platforms/tree/master/page/main`). Goal: **the same page, with an engine that works**. Visual baseline: `docs/visual-baseline/legacy-home-*.webp` (rendered from a sanitised copy; third-party regions excluded).

Status legend: ✅ done · 🟡 staged in the working tree, not yet wired/tested · ⬜ not started · ❓ owner decision needed.

---

## 1. Original architecture

`page/main/index.php` (1,136 lines) renders in this order: preloader → *(device-notification overlay)* → notifications container → spotlight popover → PWA modal → HesterGPT floating box → user panel (apps button, profile badge, profile card, app launcher) → `<nav class="Navbar">` (toggle, brand, three switchable panels: menu / widgets / settings; quick links) → `<main>` with five sections → scripts.

Server-side PHP in the file: (a) `POST send_order` → builds a text and calls `notify()`; (b) `$_SESSION['token_id']` / `user_photo` to choose avatar and "Dashboard/Log In" labels; (c) `file_get_contents(BASE_URL."_assets/icon/")` for the seasonal favicon `<head>` block; (d) `include 3droom/index.php`.

## 2. Dependency map

```
index.php
├─ _inc/functions.php ─ LOGO/LOGO_IO/BASE_URL, notify()  ──► Telegram Bot API
├─ _assets/icon/index.php ─ seasonal <head> (favicons, meta, OG, canonical)  ──► icon/season1|3/*
├─ CSS  normalize 5.0.0 (CDN) · main.css (62 KB) · _assets/css/core.css (+ @import Unicons line.css CDN) · widgets/3droom/style.css
│     main.css ─ @import open-props (unused) · @font-face Ndot ─► _assets/fonts/Ndot-55.otf · img/*
│     3droom/style.css ─► 3 images (githack CDN)
├─ JS (load order)
│   three.js r128 (CDN) → jQuery 3.6.0 (CDN) → planet.js ──► i.imgur.com texture
│   js.web4ukraine.org (async)  ← redirector, see AUDIT §5
│   jQuery 3.1.0 (CDN, 2nd copy!) → underscore 1.8.3 (CDN, unused)
│   functions-min.js = Hammer.js + section/slider/form controller
│   _assets/js/core.js = title swap · #toggle · preloader · ripple button · cookies · canvas grid
│   aev-notify.js = toast stack + demo schedule
│   spotlight.js = popover search ──► fetch https://aliev.io/page/main/data.json (20 apps)
│   inline #1: panel switching, clock text, HesterGPT toggle, crypto ticker, launcher/profile toggles,
│              custom <select> dropdown, notification fade, fullscreen, accordion
│   inline #2: window.intergramId/Customizations
│   widgets/3droom/script.js = pointer-tilt
│   intergram.xyz/js/widget.js ──► iframe https://www.intergram.xyz/chat.html  (+ hidden loadmill iframe)
├─ iframes  widgets/clock/ · widgets/calculator/ (jQuery-free, eval) · https://aliev.io/page/hester/
├─ APIs     min-api.cryptocompare.com (401 now) · coinmarketcap/github images
└─ navigation targets  /home/{profile,timeline,messenger,videos,finance,_api/UI,_api/Docs} · /page/{maps,hester,updates,careers,downloads,investor-relations,contact,empty,services}
```

`page/main/data.json` (spotlight apps) is **git-ignored in the legacy repo** yet present in the tree.

## 3. Feature trace and checklist

"Works?" is measured against the legacy site rendered locally (Chromium) and by reading the code.

| # | Feature | Trigger | Depends on | Works? | What breaks it | v2 plan | Status |
|---|---|---|---|---|---|---|---|
| 1 | **Loading screen** | on load; hidden after 2 s | `core.js` jQuery fade, logo | Yes | JS failure leaves it forever; fade is jQuery | Same look/timing; CSS-class fade; fail-safe so it can't strand; reduced-motion | 🟡 markup+CSS staged |
| 2 | **Main navigation** (rail, hamburger, menu) | click toggle | `core.js` `#toggle` | Yes | `<a href="#">` not keyboard-friendly | Same visuals; real `<button>`, `aria-expanded`, Esc/outside click | 🟡 |
| 3 | **Mobile navigation** | <544 px top bar | `core.css` media | Yes | — | Same CSS | 🟡 |
| 4 | **Panel switching** (Menu ↔ Widgets ↔ Settings) | Settings/Widgets/Back buttons | inline fn + jQuery fade | Yes | global functions | `data-panel` buttons, same slide/fade | ⬜ |
| 5 | **Explore/Learn/Build accordion** | click | inline jQuery `slideToggle` | Yes | div, not keyboard; targets dead | Same animation; fix destinations (§5) | ⬜ |
| 6 | **Section scroller + side-nav + CTA** | wheel/↑↓/swipe/click | `functions-min.js` | Yes | passive wheel; Hammer bundled | Same thresholds (±50, 800 ms lock) and transitions | ⬜ |
| 7 | **Works slider** | prev/next | `functions-min.js` | Yes | — | Same rotation logic | ⬜ |
| 8 | **Floating-label form** | focusout | `functions-min.js` | Yes | `window.scrollTo(0,0)` on every blur | Keep label behaviour; drop forced scroll | ⬜ |
| 9 | **User/profile panel** | click badge | inline jQuery | Open/close yes; buttons dead | no handlers | Guest/auth states; buttons → account (flag) | 🟡 |
| 10 | **App launcher** | click apps button | inline jQuery | Opens; many tiles dead | destinations (§5) | Every tile kept; per-tile destination decision | 🟡 |
| 11 | **Spotlight** | Ctrl/⌘+J; click menu input | `spotlight.js`, Popover API | Mostly | `keyCode` combo, `innerHTML`, `q=` unencoded, fetches production JSON, popover-only | Same UI; `key`-based; safe DOM; inline JSON of apps; fallback if no Popover API | ⬜ |
| 12 | **Settings** | panel | — | Partly | see §4 | Implement every control (§4) | ⬜ |
| 13 | **Theme** | spotlight "Toggle theme" | `data-theme` attr | Attribute toggles; **no visual change** | no light skin exists in `main.css` | Keep action + persistence; dark-only look; ❓ | ❓ |
| 14 | **Crypto ticker** | on load | CryptoCompare (401), CMC/GitHub images | **No** (`$0.00`) | upstream needs an API key | Server cache `/api/prices` (CoinGecko keyless; CryptoCompare if key set); self-hosted logos; keep last-known on failure | ⬜ (assets 🟡) |
| 15 | **3D globe** | on load; drag | three r128, imgur texture, jQuery | Yes | no resize/DPR/dispose/WebGL fallback; hot-linked texture | Same scene constants; self-hosted three+texture; resize; pause when hidden; static fallback image | ⬜ (assets 🟡) |
| 16 | **Notifications** | timers 4 s | `aev-notify.js` | Yes | console log on mousemove | Same UI/copy/timing | ⬜ |
| 17 | **Intergram** | floating button | widget.js (alive) | Yes | third-party; hidden tracker iframe | Pinned self-hosted widget, `disableLoadmill`, id from env, `frame-src` | ⬜ |
| 18 | **HesterGPT box** | click circle | iframe `/page/hester/` | Opens | embedded app uses a leaked key | `/hester` page + `/api/hester` proxy; lazy-load iframe on first open | ⬜ |
| 19 | **PWA modal** | Settings → PWA install (`#RccSCT7-open`) | CSS `:target` | Opens | no install action | Keep modal; add real install button where `beforeinstallprompt` exists; manifest | 🟡 |
| 20 | **Widgets** clock/calculator | Widgets panel | iframes | Clock yes; calc `eval` | `eval` | Same UIs; safe evaluator | 🟡 CSS staged |
| 21 | **Hire form** | submit | `notify()` | Sends unvalidated text | no CSRF/validation/rate limit | `/hire` secure flow + toast feedback | ⬜ |
| 22 | **Footer/social links** | click | — | Partly | placeholders (§5) | Real URLs; owner supplies missing | ⬜ |
| 23 | Title swap on blur/focus | window events | `core.js` | Yes | pathname split | Same | ⬜ |
| 24 | Seasonal favicons | on load | `icon/index.php` | Yes | self-fetch of own URL | `SeasonalIcons` | ✅ |
| 25 | Ripple button | click | `core.js` | Only the first button | `querySelector` | All `.ripple-button` | ⬜ |

## 4. Settings — what each control was meant to do

| Control | Original wiring | Intended behaviour (evidence) | v2 decision |
|---|---|---|---|
| Functional key box + **Check** | none (`{{ ... }}` text, no handler) | Reads as a key/password generator driven by the four options below `[inference]` | ❓ Implement generator (CSPRNG, selected sets, copy on click). **Owner to confirm intent.** |
| Uppercase / Lowercase / Numbers / Symbols | checkboxes, no handler | Character sets of the generator | Used by the generator |
| Language (EN/CZ/UA/RU/Crimean Tatar) | custom dropdown with search, no effect | Planned i18n (`lang.en.php` empty) | Persist choice, set `<html lang>`, say translations aren't available yet; ❓ translations |
| PWA Install | `window.location='#RccSCT7-open'` | Open install instructions | Works; adds real install prompt |
| Shortcuts | `onclick="#"` (JS error) | The announced **Shortcuts modal** `[owner]` | Implement modal listing real shortcuts |
| Fullscreen | `toggleFullscreen()` | Toggle fullscreen | Works |
| Dark Mode, Animations (disabled, checked) | disabled by design | State display | Keep disabled; Animations additionally honours `prefers-reduced-motion` |
| Cookies: Functional (disabled, checked), Statistics, Marketing (disabled) | disabled | Consent display | Keep; consent flow when analytics is decided |

## 5. Launcher, menu and link destinations

Every control is preserved. Where the original destination no longer exists, the destination below is the closest valid behaviour; items with no valid destination keep their tile and open a **designed status toast** in the existing notification UI (the owner's own "Coming soon" convention) — no `#` links.

| Control | Original | Now | Basis |
|---|---|---|---|
| Account | `/home/profile/` | `/account` when auth is enabled, else status toast | `core/auth` + `apps/account` exist |
| Space / Messenger / Videos | `/home/{timeline,messenger,videos}/` | status toast until those apps return | apps are to be rebuilt (`MIGRATION.md`) |
| Maps | `/page/maps/` | status toast | page is an INCOMPLETE stub |
| HesterGPT | `/page/hester/` | opens HesterGPT panel; link to `/hester` | ported |
| Finance | `/home/finance/` (never existed) | status toast | roadmap marker |
| _API | `/home/_api/UI/` | status toast until terminal is ported | |
| Docs | `/home/_api/Docs/` | `DOCS_URL` env (e.g. an `aliev-docs` deployment) else toast | `docs.aliev.io` is NXDOMAIN ❓ |
| Journal | `/page/updates/` | `/journal` (ported updates) | |
| App | `#` | status toast | purpose unclear — owner review |
| Footer: aliev.io / All Apps | dead | `/` / opens Spotlight | closest valid |
| Explore → Services | `/page/empty/` | `/#hire` | services checkboxes live there |
| Explore → Portfolio | `/page/empty/` | `/#works` | "Selected work" |
| Explore → Ecosystem | `/page/empty/` | opens the app launcher | the launcher *is* the ecosystem |
| Learn `{{}}` | placeholder → `_api/UI/?0x=404` | "Journal" → `/journal` | placeholder replaced |
| Build → Quickstart/Docs/CLI | `_api/Docs/`, `_api/UI/` | `DOCS_URL` else toast; CLI toast | |
| Store | `#link` | status toast | `design_store` concept |
| Privacy Policy | `#link` | `/legal` | owner supplies text ❓ |
| Careers / Downloads / Investor Relations / Contact | `../…/` | `/careers` `/downloads` `/investor-relations` `/contact` | pages being ported |
| Log In / Dashboard | `/home/` | `/account` if enabled else toast | |
| YouTube / Facebook / Twitter | `#link` | rendered only when `SOCIAL_*` URL configured ❓ | owner supplies |
| Works cards with `#0` (Avrora, Dreamers, Cerebro, Cortex, EROS) | dead | stay cards; link when owner supplies project URLs ❓ | |
| #StopTheWar card / Donate | `/home/_api/UI/?Page=4ukraine` | `/4ukraine` (terminal page ported) | |
| We Are Hiring / Join Us | `/page/careers/list/` | `/careers` | |
| Philosophy / History (About) | `#0` | History → `/history`; Philosophy ❓ | |

## 6. Integration design (v2)

- **Layout `shell`**: preloader, notifications, spotlight, PWA modal, HesterGPT box, user panel, navbar. `pages/home` = the five sections. Other pages reuse the shell (legacy `core.css` + `main.css` are the shared chrome).
- **Assets** (staged): `public/assets/css/{main,core,fonts}.css` (mechanical edits only — header lists them), `widgets/*.css`, `images/home/*` (byte-identical), `images/{avatar.png,crypto/*,globe/earth-map.png,room/*}`, `vendor/three/three.r128.min.js`, `vendor/unicons/*` (Apache-2.0), `fonts/Roboto-Thin-latin.woff2`.
- **Inline styles → classes** (`utilities.css`, generated): the CSP forbids `style=""`.
- **JS** as native ES modules under `public/assets/js/home/`, one per concern.
- **Endpoints**: `POST /hire`, `GET /api/prices` (cached, same-origin), `POST /api/hester`, `GET /hester`, `GET /widgets/{clock,calculator}`.
- **CSP additions (home only)**: `frame-src 'self' https://www.intergram.xyz`; `/hester` and `/widgets/*` sent with `frame-ancestors 'self'`.
- **Config/env**: `INTERGRAM_CHAT_ID`, `GEMINI_API_KEY`, `CRYPTOCOMPARE_API_KEY` (optional), `DOCS_URL`, `SOCIAL_*`, `AUTH_ENABLED`.

## 7. Known limitations (current)

- The page is **not yet functional in v2**: assets, CSS and shell partials are staged; behaviour modules, endpoints and tests are pending. Nothing here is claimed working until it has been exercised in a browser.
- No light skin exists in the original; none is invented.
- AEVT has no price source; `0.00` is shown as in the original.
- Authenticated profile state needs the database-backed account area.
