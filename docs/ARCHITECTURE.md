# Architecture

## Principles

Simple, explicit, host-agnostic. No framework, no build step for PHP, no frontend framework. PHP 8.3+ behind nginx or Apache, Docker-first, `public/` as the only web-reachable directory.

## Layout

```
.
├── app/                    # application code, namespace App\
│   ├── Application.php     # composition root: config → services → Router → Response (+ error handling, HTTPS redirect, canonical paths)
│   ├── Http/               # Request, Response, Router, HttpException
│   ├── Controllers/        # PageController, ContactController, ErrorController
│   ├── Services/           # LeadStore, Notifier/{Notifier,TelegramNotifier,LogNotifier}
│   ├── Security/           # SecurityHeaders, RateLimiter, Signer, ClientIp
│   ├── Validation/         # ContactValidator
│   ├── Support/            # Env, Config, Logger, View, SeasonalIcons
│   └── Views/              # layouts/, partials/, pages/, errors/   (plain PHP templates)
├── core/auth/              # existing auth module, namespace Core\Auth\ (PDO, sessions, CSRF, roles) — reused, not duplicated
├── apps/account/           # manual test pages for core/auth (not part of the public site)
├── config/                 # app.php, security.php, notify.php  (read env, return arrays)
├── routes/                 # web.php (public routes), legacy.php (redirects + 410s)
├── public/                 # DOCUMENT ROOT
│   ├── index.php           # the only PHP entry point
│   ├── .htaccess           # Apache front-controller rules
│   ├── favicon.ico
│   └── assets/{css,js,fonts,brand,icons,images}
├── storage/                # runtime only, never committed: logs/, ratelimit/, leads/, cache/
├── docker/                 # Dockerfile (dev, prod), php.ini, nginx/default.conf
├── tests/                  # Unit/, Feature/
├── scripts/                # tooling (visual baseline, key generation)
├── docs/                   # this documentation
├── compose.yaml            # local dev: php-fpm + nginx
├── composer.json / .lock   # PSR-4: App\ → app/, Core\Auth\ → core/auth/
└── .env.example            # placeholders only
```

`aev.platforms-master/` (git-ignored) is the read-only legacy reference.

## Request lifecycle

1. nginx/Apache serve real files under `public/` directly, return 404 for dotfiles and any other `.php`, and send everything else to `public/index.php`.
2. `index.php` loads Composer's autoloader, `Application::boot()` reads `.env` (real environment variables win) and `config/*.php`.
3. `Application::handle()`:
   - canonicalises the path (`/x/` → `/x`, single-hop 301, only when a real page matched, so legacy redirects never chain);
   - optional HTTPS redirect (honours `X-Forwarded-Proto` only from `TRUSTED_PROXIES`);
   - `Router::dispatch()` — exact-match routes; `routes/legacy.php` supplies 301 redirects and 410 prefixes;
   - `HttpException` → `ErrorController` (403/404/405/410/413/429/500 pages, `noindex`, `no-store`); any other `Throwable` is logged and shown as the generic 500 (debug text only when `APP_ENV≠production` **and** `APP_DEBUG=true`);
   - `SecurityHeaders` is applied to every response.
4. `Response::send()` (HEAD requests send no body).

## Contact / lead flow (replaces the legacy order form and `notify()`)

`POST /contact`: body-size limit (16 KB) → session + **CSRF** (`Core\Auth\Security\CsrfProtection`) → **honeypot** (silent fake success) → **signed form-age token** (3 s – 2 h, HMAC with `APP_KEY`) → **rate limit** (3 per 10 min and 10 per day per client IP, file-based, hashed keys) → **validate + sanitise** (`ContactValidator`) → **server-generated reference** (`AEV-` + 10 Crockford-base32 chars; client-supplied IDs are ignored) → persist to `storage/leads/leads-YYYY-MM.jsonl` (0600, IP stored only as a keyed pseudonym) → notify (`Notifier`) → rotate CSRF token → 303 redirect (PRG). A notifier failure never loses the lead and never fails the request.

## Notifier

`Notifier::send(string): bool` — must not throw or block past its timeout. `TelegramNotifier` (HTTPS only, 2 s connect / `NOTIFY_TIMEOUT_SECONDS` total, no redirects, token only from env, errors logged without the token). `LogNotifier` is the default/dev driver. Selected by `NOTIFY_DRIVER`.

## Authentication (staff area — not yet exposed)

`core/auth` provides register/login/logout, Argon2id hashing, brute-force lockout, audit log, roles/permissions and a CSRF helper over PDO (MySQL or PostgreSQL). The public agency site does not need accounts. If a staff/admin area is required it will live under `/account` using `AuthFacade`, with role checks — **no static tokens**. This is an open owner decision.

## Data

No database is required for the public site (leads are append-only files). `LeadStore` is the seam to replace with a repository when a database is introduced.

## Frontend

Server-rendered HTML + native ES modules (`type="module"`). Styling in `public/assets/css/site.css` (tokens in `:root`). Strict CSP: only same-origin scripts, styles, fonts, images; no inline code. Progressive enhancement: with JS disabled all navigation still works.

## Decisions & rationale

| Decision | Why |
|---|---|
| No framework | Site is small; fewer moving parts to patch; portable to any PHP host |
| `public/` document root | Prevents exposure of source, config, `.env`, `vendor/`, `storage/` (the legacy site served everything) |
| File-based rate limit/leads | Works on plain hosting with no DB/Redis; swappable |
| Explicit wiring | Easy to read and type-check; no reflection/magic |
| Reuse `core/auth` | Already implements CSRF/session/brute-force correctly; avoid duplicate security code |
| Composer PSR-4 for `Core\Auth\` → `core/auth/` | The manual `core/autoload.php` maps to `core/Auth/` (capital A) which only works on case-insensitive filesystems; Composer's explicit mapping works on Linux |
