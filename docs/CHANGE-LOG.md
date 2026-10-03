# Change log (rationale-level)

For every meaningful modification: original behaviour → problem → new implementation → reason → visual impact → functional impact. The short release-style list is in the root `CHANGELOG.md`. A future developer should be able to learn *why* something changed from here.

## A. Superseded instructions (how direction changed)

The project owner's direction changed during the revival. Where an earlier instruction conflicts with a later one, the later one wins; this table records it so nothing is silently reversed.

| Earlier instruction | Later instruction | Resolution |
|---|---|---|
| Rebuild only the public agency site; do **not** revive `home/*` (messenger, timeline, wallet, games, …) | "Preserve the project and its identity… the ecosystem concept remains… experimental parts remain… default to KEEP, not DELETE" | `home/*` is preserved and scheduled as phases (`MIGRATION.md`); nothing is declared gone. Routes that returned 410 now return 404 until rebuilt |
| Remove the crypto ticker and Intergram | "KEEP THE CRYPTO TICKER", "KEEP INTERGRAM" | Both kept and repaired (`MAIN-PAGE.md`) |
| Keep only launcher entries pointing at working destinations | "Do not remove an item just because it is inconvenient… adapt the destination" | All tiles kept; destinations adapted; unbuilt products show a designed status toast |
| Do not keep Ndot-55 (licence); use Doto | "Do not silently remove fonts; document licence; the owner will decide" | Ndot stays untouched in the legacy tree and is documented; v2 uses Doto as an interim stand-in; owner decides |
| SF-Pro: delete if genuinely unused | "Do not delete fonts merely because they are old" | Kept in the legacy tree; not shipped by v2; the reference check result is recorded (`DESIGN-SYSTEM.md`) |
| Keep the Web4Ukraine banner only if relevant and functional | Same intent | The script is a remote-controlled redirector (`THIRD_PARTY_RISK_01`) so it is not loaded; the cause (card + 4ukraine page) is preserved |

## B. Changes

### C-001 Contact / order flow
- **Original:** `page/main` and `page/contact` forms POSTed to themselves; the PHP built a text from raw `$_POST` and called `notify()`; order number `ddmmyy_1` (not unique, client-visible, undefined variable).
- **Problem:** `SECURITY RISK` — no CSRF, validation, rate limit, escaping; non-unique ID; notification blocked the request.
- **New:** `ContactController` (`/contact`): CSRF (`core/auth`), honeypot, HMAC-signed form age, rate limit, validation, server-generated reference, append-only lead store, Notifier, PRG redirect. The main-page Hire form will use the same hardened pipeline (`/hire`).
- **Why:** lead generation is a stated purpose; keep it, make it safe.
- **Visual impact:** none intended (contact page restyled with legacy tokens; full legacy page port pending). **Functional:** feedback and reference ID now shown.

### C-002 Notifier replaces `notify()`
- **Original:** token in URL, `file_get_contents`, no timeout/handling, chat id hard-coded.
- **New:** `Notifier` interface; `TelegramNotifier` (HTTPS only, 2 s connect / configurable total, no redirects, env secrets, scrubbed logs); `LogNotifier` default. **Functional:** same message delivery, failure-tolerant.

### C-003 Configuration and secrets
- **Original:** DB credentials and bot token in source; environment chosen by an always-true host comparison (`LOGIC_BUG_01`).
- **New:** `.env`/environment (`Env`, `config/*.php`), `APP_ENV`. **Impact:** none visible.

### C-004 Web root and entry point
- **Original:** the repository root was the web root (everything reachable).
- **New:** `public/` only; nginx/Apache rules block dotfiles and non-entry PHP; verified that `.env`, `.git`, `composer.json`, `app/`, `config/`, `vendor/`, `storage/`, `tests/` return 404.

### C-005 Autoloading of `Core\Auth\`
- **Original (v2 foundation):** `core/autoload.php` mapped `Core\Auth\X` to `core/Auth/X.php` while the directory is `core/auth/` — works on macOS, fails on Linux.
- **New:** Composer PSR-4 `Core\Auth\` → `core/auth/`.

### C-006 Security headers and CSP
- **New:** strict CSP (`default-src 'self'`, no inline script/style), HSTS in https production, `nosniff`, frame denial, referrer/permissions policies. Exceptions are added per page and documented (e.g. Intergram frame on the home page).
- **Visual impact:** forces legacy inline `style=""` into generated classes (same rendering).

### C-007 Routing and URLs
- **New:** single-hop canonicalisation (`/x/` → `/x`), legacy redirects, 405 with `Allow`.
- **Reverted decision:** the first routing commit declared `/home/*`, `/page/maps`, `/page/material`, `/page/universal`, `/page/qirimcz`, … **410 Gone**. That contradicted the preservation direction and was removed; those paths return 404 until rebuilt (`routes/legacy.php`, test updated).

### C-008 Brand assets
- Logos, icon sets copied byte-identical (`cmp`-verified for the primary files). Rail geometry and the "Hire us" slab button reproduced from measured legacy values (wordmark ≈ 90 px, rotated −90°, 1 px `#282828` edge; slab starts 23 px in, slides in 0.2 s). Effective legacy background measured as `#000` (not `#0c0c0c`).

### C-009 Display font (interim)
- **Original:** Ndot-55 for the clock date block and Settings heading.
- **New:** Doto (SIL OFL) via one `@font-face`/token, chosen over DotGothic16 after side-by-side rendering. **Visual impact:** dot shapes differ slightly in those two spots. **Reversible:** one line, if the owner approves Ndot.

### C-010 Seasonal favicon
- **Original:** PHP `include` fetching its own URL; season by date.
- **New:** `SeasonalIcons::folder()` with identical boundaries, unit-tested. **Impact:** none.

### C-011 CI and tooling
- Composer, PHPUnit 11, PHPStan 8, PHP-CS-Fixer, production Docker build in CI; `composer.lock` committed (it was git-ignored); `app/Views` excluded from PHPStan (templates).

### C-012 Understanding-phase documents
- Added `PROJECT-VISION`, `HISTORICAL-FEATURES`, rewrote `AUDIT`, `APPLICATIONS`, `WIDGETS`, `LEGACY-REMOVAL` (now a ledger: nothing deleted), `URL-MIGRATION`, `MIGRATION`, `MAIN-PAGE`, `CLAUDE.md`. **Reason:** owner's "archaeology first" instruction. No application behaviour changed except C-007's correction.

### C-013 Staged (uncommitted) main-page port
- Present in the working tree but **not committed and not wired**: `public/assets/css/{main,core,fonts}.css`, `widgets/*.css`, `images/**`, `vendor/{three,unicons}`, Roboto Thin, `app/Views/partials/shell/*`. Provenance and edits are described in `MIGRATION.md` §2 and the file headers.

## F. Agency rebuild migration (2026-10)

Each entry: original → problem → new → visual / functional impact.

- **Main page**: nothing initialised (script missing) / loader never ended → scripts ported, per-feature guards, loader fail-safe → none visible; page works.
- **Unicons fonts**: 16 of 21 files corrupt → re-fetched, URLs versioned → icons render.
- **Re:Search field**: `readonly` attribute collided with the spotlight's `[readonly]` rule → removed → field sits in the menu grid.
- **Menu on short screens**: first block shrank to 0 px → `flex-shrink:0` → landscape phones can scroll the whole menu.
- **HesterGPT / Avrora**: removed with CSS, images, entries → slider re-slotted, spacing unchanged.
- **Careers**: raw SQL on `$_GET`, `die(mysqli_error())`, raw echo → `JobRepository` (prepared, validated slugs), escaped output, generic 503 → identical look; `/careers/{slug}`.
- **Contact**: unauthenticated `notify()` of raw POST, hard-coded Mapbox token → FormGuard pipeline, env token → identical look; locations now fly the map.
- **Downloads**: `window.open` buttons, E.COM downloaded the wrong file, wallpaper-maker link pointed at a missing folder → real links, config-driven list, honest "not available" → identical look.
- **Auth**: md5 passwords, SQL string building, session fixation, wallet panel → Argon2id on `core/auth`, CSRF, limits, one failure message; hidden Sign Up face restored → flip card unchanged.
- **`_api`**: dynamic function dispatch with URL credentials, reflected XSS in the domain tool, browser-side bot token, games/store → allow-list, validated tool, server-side notification, bundle pruned → terminal/Docs look unchanged.
- **Profile panel** (`aev-profile-options`): restyled with the supplied SaaS Widget; the two identical "Sign In" links became one button.
- **Assets removed** (verified unreferenced or tied to removed products): maps/messenger/timeline/video/finance launcher icons, six never-used home images, `images/mail/unnamed.png`, `header.css`.


## G. Canonical structure and shared components (2026-10)

- **Original:** `app/` (`App\`), `public/assets/`, templates in `app/Views`, one navbar/head/guard-fields copy per page.
- **Problem:** structure did not match the ALIEV.IO canonical layout; shared UI elements were duplicated per page (navbar CSS 28 identical rules, head, honeypot/CSRF fields, ripple script, error-page chrome).
- **New:** `core/` (engine + `auth`), `website/<page>/` (controller, views, assets), `api/{internal,terminal}`, `resources/` (shared views, components, CSS, JS, images, fonts, vendor); sources published to git-ignored `public/build/` and `public/home/_api/` by `scripts/build.php`. One navbar, head, guard-fields, button, forms and ripple implementation. Empty future folders (`apps/*`, `api/v2`, `core/users|permissions|cache`, `website/legal|investors`) intentionally not created. (`website/legal` was added afterwards, see §H.)
- **Reason:** single source of truth per element; pages are self-contained folders.
- **Visual impact:** none, proven by `scripts/visual/baseline.mjs` + `compare-baseline.py` (HTML, requests, console, boxes at four viewports, 22 routes). Intended differences only: Contact icon 16→17 px, Auth logo y 56→60, error pages now use the real navbar.
- **Functional impact:** none; URLs unchanged.
- **Cleanup:** removed unused `DB_DRIVER`/`QIRIMTALK_URL` settings, empty `core/auth/Database`, `scripts/visual/shoot.mjs` (superseded by `baseline.mjs`), `.github/README.md` (duplicate of the root README).

## H. Production readiness (2026-10)

- **Privacy Policy stub → routes.** *Original:* the menu entry was a "pending" link to `/`. *New:* `GET /privacy` and `/imprint` (`website/legal`, `config/legal.php`, `LEGAL_*`), clearly marked placeholders and `noindex` until the owner supplies the content; no company or legal text invented. *Visual impact:* the menu link now navigates; the pages reuse the dark error-page look. See `docs/LEGAL.md`.
- **Works slider content → `config/works.php`.** Same four cards, byte-equivalent markup (only `'` in one tagline is now HTML-escaped); each flagged `confirmed => false` for owner review. No claims, images or dates added.
- **`.env` parsing bug.** *Original:* a value that was only an inline comment (`DOCS_URL=   # note`) was read as the text `# note`, so a `.env` copied from `.env.example` set `INTERGRAM_CHAT_ID` to comment text and loaded the chat widget with a bogus ID. *New:* such values are empty (unit test); `.env.example` rewritten with own-line comments and Required/Optional markers, new `AUTO_MIGRATE` and `LEGAL_*` entries, `TRUSTED_PROXIES` redirect-loop warning.
- **Ndot-55:** verified and documented (where used, that it is currently published, what decision is needed); untouched.
- **Unchanged on purpose:** Careers empty state (already tested), contact e-mail/phone, notifications, Mapbox/Intergram optionality, `_api` bundle and its CSP.
