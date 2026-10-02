# CLAUDE.md — ALIEV.IO

Permanent instructions for Claude Code sessions in this repository. Read this first; then `docs/ARCHITECTURE.md`.

## Project identity

ALIEV.IO ("ΛΞV | Digital studio") is a digital agency / technology-studio website: home, services, projects, about, contact, careers, journal, brand assets, legal. It is a **revival** of the legacy site kept (git-ignored, read-only reference) in `./aev.platforms-master`. The legacy `home/*` social-platform experiment (messenger, timeline, wallet, studio, games, ML) is **out of scope and must not be revived** without an explicit request.

## Critical design rule

**Never redesign the visual identity.** The target is *same ALIEV.IO identity + clean architecture + secure implementation*. Do not introduce generic agency/SaaS styling, new colour palettes, gradients, glassmorphism, card systems, Tailwind/Bootstrap/Material defaults, or a new type scale. If a change would be visible, it needs explicit approval from the owner. The extracted system is in `docs/DESIGN-SYSTEM.md`; the tokens live in `public/assets/css/site.css`. The identity is **dark-only** (the legacy site never shipped a light skin — do not invent one).

## Brand rules

- Logos in `public/assets/brand/` are copied **byte-for-byte** from the legacy `page/downloads/logo/`. Never redraw, recolour, simplify, re-export or "optimise" them. Verify with `cmp` against the legacy file.
- Favicons/PWA icons come from the legacy `season1`/`season3` sets (`SeasonalIcons` keeps the legacy date rule).
- Display font: **Doto** (SIL OFL 1.1, `public/assets/fonts/`). It replaces Ndot-55, whose licence forbids reuse. Never add Ndot, SF Pro, or any font without a verified open licence recorded in `docs/DESIGN-SYSTEM.md`.
- Details: `docs/BRAND-ASSETS.md`.

## Architecture (one paragraph; full detail in `docs/ARCHITECTURE.md`)

PHP **8.3+**, no framework, Composer PSR-4 (`App\` → `app/`, `Core\Auth\` → `core/auth/`). Web root is **`public/`** (only `public/index.php` is an entry point). `app/Application.php` is the composition root; `app/Http` (Request/Response/Router), `app/Controllers`, `app/Services` (Notifier, LeadStore), `app/Security` (headers, rate limiter, signer, client IP), `app/Validation`, `app/Support` (Env, Config, Logger, View), `app/Views` (plain PHP templates), `config/`, `routes/`, `storage/` (runtime, not committed), `tests/`, `docker/`. `core/auth` is the existing authentication module — **reuse** it (`SessionManager`, `CsrfProtection`, `TokenGenerator`, roles/permissions); do not write a second session/CSRF implementation.

## Coding standards

- **PHP:** `declare(strict_types=1)` everywhere; PER-CS 2.0 (`composer cs:fix`); PHPStan level 8 must stay clean (`composer stan`); typed properties/returns; `final` classes by default; constructor injection, explicit wiring in `Application` (no service locator/magic container); no globals; no `@` suppression except around filesystem calls that have a handled failure path.
- **Templates:** every dynamic value goes through `$e()`. No logic beyond loops/conditionals. No inline `<script>`, `<style>`, `style=""`, or `on*=` attributes — the CSP forbids them.
- **JavaScript:** native ES modules, `type="module"`, strict by default, no jQuery, no CDN scripts, defensive DOM access, event delegation, cleanup of listeners/animation loops, keyboard-accessible, `prefers-reduced-motion` respected. Small focused files in `public/assets/js/`.
- **CSS:** tokens as custom properties in `:root`; reuse existing tokens; rem-based (html is 62.5%); legacy breakpoints 1180/900/767/600/543; no `!important` unless overriding third-party code.
- **HTML:** semantic landmarks, one `<h1>` per page, labelled controls, real `<button>`/`<a>` (never `href="#"` as a button), meaningful `alt`.
- **Naming:** classes `PascalCase`, methods `camelCase`, CSS BEM-ish (`block__element--modifier`), routes lowercase-kebab, config keys snake_case.

## Security rules (non-negotiable)

1. **Never hardcode secrets.** Configuration comes from the environment (`Env`/`config/*.php`). `.env.example` holds placeholders only. Never print or log secrets; the `Logger` redacts, but do not rely on it.
2. **Never bypass CSRF.** Every state-changing request validates `CsrfProtection`. Forms also use the honeypot, signed form-age token, and rate limiting (see `ContactController`).
3. **No dynamic PHP execution** (`eval`, `assert` with strings, `include` of user/DB/remote content, `create_function`, `preg_replace /e`). **No `shell_exec/exec/system/passthru/proc_open`** without an explicit, reviewed need.
4. **No raw SQL string building.** Use PDO prepared statements through a repository (`Core\Auth\Database\PdoConnection`).
5. **Never trust client input**, including hidden fields, headers (`Host`, `X-Forwarded-*` only via `TRUSTED_PROXIES`), identifiers and totals. IDs are generated server-side.
6. **No third-party scripts/styles/fonts at runtime.** Self-host. The CSP is `default-src 'self'`; widening it needs a documented reason in `docs/SECURITY.md`.
7. **Errors:** never show stack traces, SQL, paths or credentials to users. Throw `HttpException(status)` for expected failures; unexpected ones are logged and rendered as the generic 500.
8. Treat all credentials found in the legacy repo as **compromised** and never copy them, their values, or personal data (PII, uploads, biometric/face data) into this repo or its docs. Refer to them by label (`SECRET_EXPOSURE_01`, `PII_EXPOSURE_01`…).

## Legacy rules

- `aev.platforms-master/` is reference only: read it, never import from it, never commit it (it is git-ignored).
- Do not revive deleted legacy systems (Messenger, Space/timeline, Wallet/Finance, Videos, Studio, `_api` terminal/games, HesterGPT embed, crypto ticker, Intergram chat, Mapbox embed, `eval` calculator widget) without a written justification and owner approval.
- Removals and their evidence are recorded in `docs/LEGACY-REMOVAL.md`; the audit is `docs/AUDIT.md`.

## URL rules

Preserve important public URLs. Legacy → new mapping lives in `routes/legacy.php` and `docs/URL-MIGRATION.md`; keep them in sync. Single-hop 301 only (no chains), 410 for removed products, no trailing-slash duplicates (`/x/` → `/x`). New routes go in `routes/web.php` and `docs/ROUTES.md`; update `public/sitemap.xml` (when it exists) and canonical URLs.

## Testing rules

Every behaviour change ships with a test. Before finishing any task run, inside Docker:

```bash
docker compose run --rm app vendor/bin/phpunit          # tests
docker compose run --rm app vendor/bin/phpstan analyse  # level 8
docker compose run --rm app vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes
```

Verify UI changes in a real browser (Playwright scripts in `scripts/visual/`) at desktop 1440, tablet 820, phone 390 and phone-landscape 844×390, and compare against the legacy baseline. See `docs/TESTING.md`.

## Documentation rules

Update the relevant doc in the same change whenever architecture, routes, config, security policy, design tokens or deployment change. Index: `docs/` (ARCHITECTURE, ROUTES, URL-MIGRATION, SECURITY, DESIGN-SYSTEM, BRAND-ASSETS, DEPLOYMENT, DEVELOPMENT, TESTING, OPERATIONS, LEGACY-REMOVAL, MIGRATION, CHANGELOG).

## Design preservation rules

Visual change = approval required. Fidelity checks compare against the legacy rendering (screenshots in `docs/visual-baseline/`, reproducible via `scripts/visual/`). Accessibility, performance and responsive improvements are welcome **as long as the look stays recognisably the same**.

## Deployment rules

Docker-first, host-agnostic. Production = image built from `docker/Dockerfile` (default final stage `prod`), nginx or Apache in front, **document root `public/`**, PHP 8.3+, non-root user, `storage/` writable and persistent, configuration via environment. Do not hard-code a hosting provider. Details: `docs/DEPLOYMENT.md`.

## Git rules

Work on a branch, commit coherently (`docs:`, `refactor:`, `security:`, `fix:`, `cleanup:`, `test:`). **Never commit:** `.env`, secrets, credentials, anything from `storage/`, `vendor/`, caches, `.DS_Store`, machine-specific files, user data, or the legacy tree. `composer.lock` **is** committed. Commits end with the co-author trailer configured for the session.

## Open owner decisions (do not assume)

Hosting provider; whether `/account` (staff login on `core/auth`) is exposed; company contact details (phone/CIN/offices appear on the legacy contact page — confirm before publishing); legal-page text; Git-history purge (see `docs/SECURITY.md`).
