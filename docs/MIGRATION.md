# Migration: legacy `aev.platforms` → v2

Principle: **move the idea and the experience; rebuild the engine.** Nothing is dropped to simplify; unfinished things stay unfinished until scheduled; anything unsafe is re-implemented, not removed.

## 1. Phases

| Phase | Content | Status |
|---|---|---|
| 0 — Understanding | Audit, history, vision, feature inventory, design extraction (`PROJECT-VISION`, `HISTORICAL-FEATURES`, `AUDIT`, `DESIGN-SYSTEM`, `MAIN-PAGE`) | **Done** (this revision) |
| 1 — Architecture & foundation | `public/` front controller, Docker, config/env, security headers, Notifier, contact flow, tests, CI | **Done** (committed, 44 tests, PHPStan 8) |
| 2 — Main page | Port `page/main` as the ecosystem entry point (`MAIN-PAGE.md`) | **Staged, not wired** — assets, ported CSS, shell partials exist in the working tree (uncommitted); behaviour modules, endpoints, tests pending |
| 3 — Public pages | contact (map+fish), careers (secure), downloads, journal, investor-relations, services/PragueFlow, hester/avrora, events, history/legal, material/universal | Proposed |
| 4 — Accounts | Rebuild `auth` on `core/auth`; profile card, tiers/badges, staff area; `AUTH_ENABLED` | Proposed |
| 5 — Space & Studio | timeline, profile, dailies, search, create post, article editor, edit-profile onboarding | Proposed |
| 6 — Messenger, Videos | chat, channel/player | Proposed |
| 7 — Wallet, Store, token | ledger design, QR payments, AEVT/AEVD integration, store | Proposed (needs owner design) |
| 8 — Developer layer | terminal (pages/tools/admin/games), docs app, API keys + `api/v2`, user hosting | Proposed |
| 9 — Planned features | music, blog, re:search, settings dashboard, Shortcuts modal, services pricing, API store | Proposed |

The order of 3–9 is a proposal; the owner decides it.

## 2. File mapping for the main page

"Copied" = byte-identical or mechanical edits only (listed). "Rewritten" = new implementation of the same behaviour.

| Original (`aev.platforms-master/…`) | New location | Treatment | Why |
|---|---|---|---|
| `page/main/index.php` (markup) | `app/Views/pages/home.php` + `app/Views/partials/shell/*` + `layouts/shell.php` | Copied then converted: URLs relative, inline `style=` → generated classes, `onclick` → `data-*`, PHP session bits → view variables, ARIA added | CSP forbids inline code; testability; accessibility. Visuals unchanged |
| `page/main/main.css` | `public/assets/css/main.css` | **Copied** with 4 mechanical edits listed in its header | Identity; `@import` of open-props (unused) and Ndot `@font-face` (licence) removed; blocking `device-notification` rules removed; image URLs rewritten |
| `_assets/css/core.css` | `public/assets/css/core.css` | **Copied**; Unicons `@import` → `<link>` | Self-hosted icons |
| `page/main/img/*` (40 files) | `public/assets/images/home/*` | **Copied byte-identical** | Brand imagery |
| `_assets/images/avatar.png` | `public/assets/images/avatar.png` | Copied | Guest avatar |
| `page/downloads/logo/*` | `public/assets/brand/*` | Copied byte-identical (`cmp`-verified) | Logo protection |
| `_assets/icon/season1,season3` | `public/assets/icons/*` | Copied | Seasonal favicons |
| `_assets/icon/index.php` | `App\Support\SeasonalIcons` | Rewritten | Same date rule; no self-HTTP request |
| `widgets/3droom/{index.php,style.css,script.js}` | `partials/widgets/room.php`, `css/widgets/3droom.css`, JS module | Markup copied, CSS copied (images self-hosted), JS rewritten | Hot-linked images |
| `widgets/clock/*` | `/widgets/clock` page + `css/widgets/clock.css` + JS | Copied | Same widget |
| `widgets/calculator/*` | `/widgets/calculator` page + css + JS | Copied; `eval` replaced by a safe evaluator | Security, same UI |
| `planet.js` | `public/assets/js/home/globe.js` | Rewritten; scene constants preserved; three r128 + texture self-hosted | Lifecycle, fallback, performance |
| `functions-min.js` (Hammer + controller) | `public/assets/js/home/sections.js` | Rewritten; thresholds/timings preserved | No bundled lib, keyboard, passive events |
| `core.js` (used parts) | `…/home/{preloader,nav,title,ripple}.js` | Rewritten | Same behaviour |
| `aev-notify.js` | `…/home/notifications.js` | Rewritten; copy and timings preserved | Quiet, aria-live |
| `spotlight.js` + `data.json` | `…/home/spotlight.js` + `config/apps.php` | Rewritten; UI/shortcut/actions preserved | XSS, encoding, offline |
| inline script #1 | `…/home/{panels,launcher,settings,hester,ticker}.js` | Rewritten | Concerns separated |
| ticker fetch | `GET /api/prices` + `ticker.js` | Rewritten | Upstream 401; cache + fallback |
| Intergram widget | `public/assets/vendor/intergram/widget.js` (pinned copy) + `intergram.js` loader | Copied + config from env | Resilience, privacy |
| `page/hester/*` | `/hester` view + assets; `POST /api/hester` | Copied UI; backend rewritten | Leaked key |
| `send_order` POST handler | `POST /hire` | Rewritten | Validation, CSRF, rate limit, server reference |
| `_inc/functions.php` `notify()` | `App\Services\Notifier` | Rewritten | Timeouts, env secrets |

**Intentionally unchanged:** logo files, colour/spacing/type values, copy text, section order, animations/timings, icon set, card layouts, the five-section scroller, the globe's look.

## 3. Decisions needed from the owner

1. Order of phases 3–9; whether `/account` (login) goes live now.
2. Fonts: Ndot (licence), DotlineBold, SF Pro (`DESIGN-SYSTEM.md`).
3. "Liquid Glass": intended where?
4. Destinations: Docs (`aliev-docs`?), Finance, Maps, YouTube/Facebook/Twitter URLs, Privacy/Legal text, project links for Avrora/Dreamers/Cerebro/Cortex/EROS, QirimTalk URL, tile "App".
5. Ticker: AEVT price source; AEVD definition; ticker provider preference.
6. Intergram: keep pointing at the personal chat id, or a dedicated bot/group?
7. "Functional key" settings intent; Language translations.
8. Credentials: revoke/rotate (`AUDIT.md` §5); history purge of the public legacy repository (`SECURITY.md`).
9. Third-party analytics (unknown origin in the legacy baseline): keep, replace, or none?
