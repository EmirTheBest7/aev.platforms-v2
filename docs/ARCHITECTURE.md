# Architecture

Status: **implemented**. The foundation and all retained pages (Main, Careers, Contact, Downloads, Auth, `_api`) are migrated; §11 lists what is deliberately left and §14 the open decisions.

## 1. What this project is

The ALIEV.IO website as a digital-studio site: the main page, Careers, Contact, Downloads, Auth, and
the retained `_api` bundle. PHP 8.3, **no framework**, Apache in Docker, MariaDB only for accounts
and job postings. It is deliberately small: a developer should be able to open the repository and
see where everything lives without reading this document.

The historical separation is kept — public website · shared core · API · assets · storage · database ·
configuration · tests · Docker · documentation — and simplified only where the agency-only scope makes a
directory unnecessary (no `apps/{social,messenger,wallet,…}`, no `packages/`, no service layer for its own sake).

## 2. Rules

1. **Minimal MVC.** `route → controller → (model | service) → view`. A layer exists only if something
   uses it today.
2. **No abstraction without a second consumer.** No container, no event bus, no repository interface
   unless two implementations exist. (`Notifier` has two — Telegram and Log. `JobRepository` has one,
   so it is a class, not an interface + class.)
3. **Explicit wiring.** `App\Application` is the only place objects are constructed.
4. **One of each security primitive.** One session manager, one CSRF implementation, one password
   hasher: those of `core/auth`. Controllers never build their own.
5. **Templates are dumb.** Every dynamic value goes through `$e()`. No inline `<script>`, `<style>`,
   `style=""` or `on*=` (the CSP forbids them).
6. **Design is not architecture.** CSS/JS ported from `aev-new` keeps its look and behaviour; only
   file locations and hooks change (`data-*` attributes instead of inline handlers).

## 3. Layout (target)

```
.
├── app/                       website application, namespace App\
│   ├── Application.php        composition root — the only place that wires objects
│   ├── Http/                  Request, Response, Router ({param} routes), HttpException
│   ├── Controllers/           Home, Hire, Careers, Contact, Downloads, Auth, Api, Widget, Error
│   ├── Models/                JobRepository (PDO, prepared statements) — the only model
│   ├── Services/              Notifier/ (Telegram, Log), LeadStore, DomainLookup, Market/ (price providers), Http/
│   ├── Security/              FormGuard, SecurityHeaders, RateLimiter, Signer, ClientIp
│   ├── Validation/            ContactValidator, HireValidator, Text
│   ├── Support/               Env, Config, Logger, View (+asset()), AppCatalog, SeasonalIcons
│   └── Views/                 plain-PHP templates
│       ├── layouts/           shell (main page), page (inner pages), widget, base (error pages)
│       ├── pages/             home, contact, downloads, careers/, auth/
│       ├── partials/          shell/, page/, contact/, widgets/, rail
│       └── errors/
├── core/auth/                 shared account core, namespace Core\Auth\ — reused, never duplicated
├── config/                    app, security, notify, database, integrations, apps, market, downloads (env → arrays / static lists)
├── routes/                    web.php (public routes), legacy.php (301s from historical URLs)
├── database/migrations/       001_auth, 002_user_roles, 003_jobs  (applied by scripts/migrate.php)
├── database/seeds/            dev-jobs.sql — fictional sample jobs, development only (SEED_DEV_DATA)
├── public/                    DOCUMENT ROOT — the only web-reachable directory
│   ├── index.php              single PHP entry point
│   ├── .htaccess              rewrite rules for hosts without the vhost file
│   ├── manifest.webmanifest favicon.ico   (robots.txt / sitemap.xml wait for the production domain)
│   ├── assets/{css,js,fonts,images,icons,brand,vendor}
│   ├── downloads/             files offered on /downloads
│   └── home/_api/{UI,Docs}/   retained static bundle (see §6)
├── resources/                 NOT served: licence-restricted fonts (SF Pro, DotlineBold)
├── storage/                   runtime only, never committed: logs/ cache/ leads/ ratelimit/
├── docker/                    apache/ (vhost, security, api-csp), php.ini, entrypoint.sh, nginx.example.conf
├── Dockerfile  compose.yaml  .dockerignore  .env.example
├── scripts/                   migrate.php, generate-key.php, import-jobs.php, import-retained.py, visual/
├── tests/                     Unit/ (pure classes)  Feature/ (whole app through Application::handle)
└── docs/                      current docs; history/ = ecosystem-era records, not current scope
```

Where does X go?

| I want to… | …put it in |
|---|---|
| add a page | route in `routes/web.php`, method on a controller, template in `app/Views/pages/<page>/` |
| change what a page shows | its template; data it needs comes from the controller |
| read/write the database | `app/Models/` (PDO via the factory in `core/auth`; prepared statements only) |
| call a third party or send a message | `app/Services/` (with a timeout; must not throw into the request) |
| protect a form | `FormGuard` in the controller — never re-implement CSRF/session |
| add a setting | env var → `.env.example` placeholder → `config/*.php` |
| add CSS/JS/image | `public/assets/…` (first-party URLs go through `$asset()` for cache-busting) |
| write a test | `tests/Unit` for a class, `tests/Feature` for a URL |

## 4. Request lifecycle

Apache serves real files under `public/` directly, answers 404 for dotfiles and any `.php` other than
`index.php`, and sends everything else to `public/index.php`. That calls `Application::handle()`:
canonical path → optional HTTPS redirect → `Router::dispatch()` → `HttpException` becomes an
ALIEV-styled error page, any other `Throwable` is logged and shown as a generic 500 (debug text only
when `APP_ENV≠production` **and** `APP_DEBUG=true`) → `SecurityHeaders` on every response.

## 5. Pages

| Page | URL (canonical) | Controller → view | Data |
|---|---|---|---|
| Main | `/` | `HomeController` → `layouts/shell` + `pages/home` | prices (`/api/prices`), `config/apps.php` |
| Hire request (main page form) | `POST /hire` | `HireController` | `LeadStore`, `Notifier` |
| Careers list / job / team | `/careers`, `/careers/{slug}`, `/careers/team` | `CareersController` → `layouts/page` | `JobRepository` (MariaDB) |
| Contact | `/contact` | `ContactController` | `LeadStore`, `Notifier` |
| Downloads | `/downloads` | `DownloadsController` | files in `public/downloads/` |
| Auth | `/home/auth`, `/home/auth/reset` (+ POST login/register/logout) | `AuthController` → `layouts/page` | `core/auth` |
| `_api` | `/api/*` (PHP, allow-list) and `/home/_api/{UI,Docs}/` (static) | `ApiController` | see §6 |
| Widgets | `/widgets/{clock,calculator}` | `WidgetController` → `layouts/widget` | none |
| Errors | any | `ErrorController` → `layouts/error` | none |

Rules for the table:

- The main page uses the **shell** layout (preloader, navigation rail, launcher, spotlight). Inner
  pages use a plain **page** layout and load only their own legacy stylesheet — the home chrome is not
  forced onto them.
- **Router** stays exact-match. The one place that needs a parameter is `/careers/{slug}`; add
  single-segment `{param}` matching (≈15 lines) when Careers is ported and nothing more (exact routes win, so `team` is reserved and never a slug). The legacy
  `page/careers/desc/?job_url=…` URL gets a 301 to it in `routes/legacy.php`.
- Auth, careers and the API are separate controllers because they have different guards (session +
  CSRF, public read-only, token/role), not because of a pattern.

## 6. URLs, trailing slashes and the `_api` bundle

- **Application routes have no trailing slash** (`/careers`, `/contact`, `/home/auth`); `Application::handle()` 301s `/x/` → `/x` in one hop.
- **`/home/_api/` is exempt**: the retained bundle is static HTML/JS with relative paths, so its URLs keep the slash. `GET /home/_api/` → 302 `/home/_api/UI/`; no alias for the bare `/home/_api`.
- **Static bundle** (`public/home/_api/`): `UI/` (windowed desktop), `UI/terminal/` (web terminal + `Tools/` currency, crypto, editor, qr, math, domain + `Page/` 4ukraine, donate, valentine), `Docs/`. Apache serves a directory that ships an `index.html` at its slash URL (`docker/apache/vhost.conf`); everything else under `public/` that is a directory (e.g. `/downloads`) belongs to the application.
- **PHP endpoints** are an explicit allow-list in `routes/web.php` → `ApiController` (`hello`, `info`, `me`, `updates`, `csrf`, `tools/domain`, `valentine/yes`). Unknown paths 404; no dynamic function dispatch, no URL credentials, no CORS, `no-store`; POSTs need the CSRF header and are rate limited. Telegram/e-mail style notifications go through the `Notifier` (environment-configured), never from the browser.
- **CSP**: the strict site policy applies to everything except the static bundle, which gets `docker/apache/api-csp.conf` (inline + the CDNs it still uses — a documented allow-list). Self-hosting those dependencies is the first `_api` modernization item.
- Historical URLs are 301 in `routes/legacy.php` (`URL-MIGRATION.md`). Removed products answer 404, never 410.

## 7. Security architecture

- **One session + CSRF instance.** `Application` builds a single `AuthConfig`/`SessionManager`/
  `CsrfProtection` and passes it to both `FormGuard` and the auth facade (today `guard()` builds its own
  inline — fixed when Auth is ported, before a second one can appear).
- **Forms** pass through `FormGuard`: body-size limit → session + CSRF → honeypot → signed form-age
  token → two-window rate limit → validation → persist → notify.
- **Database**: PDO with prepared statements, MariaDB credentials from env, `JobRepository` and
  `core/auth` are the only SQL in the codebase.
- **Headers/CSP**: `SecurityHeaders` (strict CSP, nosniff, frame-ancestors, HSTS when HTTPS in
  production). Each exception is documented in `docs/SECURITY.md`.
- **Secrets**: only from the environment (`.env.example` holds placeholders). Legacy credentials are
  treated as compromised and never copied.
- **Third parties** (Intergram, price providers) are optional: they have timeouts, fail silently for the
  visitor, and can never block page initialisation.

## 8. Data

| What | Where | Why |
|---|---|---|
| Accounts, roles, audit | MariaDB (`core/auth` tables, migrations 001–002) | needs real queries and constraints |
| Job postings | MariaDB (`jobs`, migration 003) via `JobRepository`; filled by `scripts/import-jobs.php` | public read, owner-managed |
| Leads (hire/contact) | `storage/leads/*.jsonl` (0600) | append-only; `LeadStore` is the seam if a table is wanted later |
| Rate-limit counters, price cache, logs | `storage/` files | no Redis needed |

## 9. Frontend

Server-rendered PHP templates. Home page behaviour is classic scripts under `public/assets/js/home/`
(the original jQuery-based scroller, slider, spotlight and notifications, plus `app.js` for menus,
panels, launcher, ticker, hire form), loaded with `defer` in a fixed order from `layouts/shell.php`.
Inner pages keep their own legacy script. jQuery, underscore and three.js are single self-hosted
copies in `public/assets/vendor/`. Every first-party URL is cache-busted (`?v=filemtime`) by one
`$asset()` helper (moves into `View` so layouts stop copying it). Fonts, icons and images are
self-hosted; the only third-party origin on the page is the optional Intergram frame.

## 10. Docker

- `Dockerfile` (repository root) with stages `vendor` → `base` → `dev` / `prod`; `php:8.3-apache`,
  `pdo_mysql`, `opcache`; the Apache vhost points at `public/`.
- `compose.yaml`: `app` (dev target, bind mount) + `db` (MariaDB 11, not published). `docker compose up --build`.
- The Apache master process runs as root (it must bind port 80); worker processes run as `www-data`.
  Behind a TLS-terminating proxy this is the usual arrangement; a fully non-root image would need an
  unprivileged port and is a possible later hardening.
- Entrypoint installs Composer deps in dev and runs `scripts/migrate.php` (idempotent) before Apache.
- `docker/nginx.example.conf` documents the equivalent nginx + php-fpm setup for hosts that prefer it.

## 11. What is deliberately left

Everything listed in the earlier plan is done (old `apps/`, `api/`, `website/`, `core/autoload.php`, empty `resources/views`, HesterGPT, Avrora, games, store, v2 terminal, etc. removed; see `CHANGE-LOG.md` §F). Remaining by design:

| Item | Why it stays |
|---|---|
| `public/home/_api` third-party CDNs and inline scripts | legacy pages; contained by a path-scoped CSP; to be modernized later |
| `layouts/base` + `site.css` + `nav.js` | skin of the error pages |
| PWA / seasonal icon sets, `resources/fonts`, generic launcher icons, `LooksGood.png` | brand assets kept on purpose, see `MIGRATION.md` |
| `docs/history/` | records of the original project, not current scope |
| `Dreamers` / `Cerebro` / `Cortex` / `EROS` slider cards | showcase items; owner to confirm they belong to the agency |

## 12. Testing

PHPUnit (`tests/Unit` for pure classes, `tests/Feature` through `Application::handle()` with an
injected notifier/HTTP client), PHPStan level 8, php-cs-fixer, and a Playwright pass
(`scripts/visual/states.mjs`) at 1440×900, 820×1180, 390×844, 844×390 comparing against the `aev-new`
baseline (`scripts/visual/legacy-baseline.sh`). PHPUnit green does not mean a page works: every UI
change is also checked in a browser (console clean, no failed requests, no horizontal overflow).

## 13. Build order (done)

1. Architecture foundation · 2. Docker foundation (verified from a clean clone) · 3. Careers · 4. Contact · 5. Downloads · 6. Auth · 7. `_api` · 8. cleanup and documentation. Each step shipped with tests and a browser check at four viewports.

## 14. Decisions

Taken by the owner: HesterGPT and Avrora removed (no replacement); URL structure `/`, `/careers`, `/careers/{slug}`, `/contact`, `/downloads` without trailing slashes, `/home/_api/` with one.

Open:
1. The four remaining Works-slider cards (§11).
2. **Domain**: `aliev.io` is unregistered — blocks production `APP_URL`, canonical/OG/sitemap values and the `hello@aliev.io` address.
3. **Revoke** the Telegram bot token and Mapbox token that were embedded in legacy files (removed from the tree, never committed).
4. Provide: real job postings (`scripts/import-jobs.php`), Privacy Policy text, SMTP if password reset by e-mail is wanted, a new Mapbox token, the Intergram chat ID, `AUTH_ENABLED=true` when accounts should go live.
5. Ndot-55 licence, Mapbox GL self-hosting terms.
