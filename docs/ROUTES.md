# Routes

Canonical form: lowercase, **no trailing slash** (`/contact/` → 301 `/contact`). Defined in `routes/web.php`; legacy handling in `routes/legacy.php`.

## Public (implemented)

| Method | Path | Handler | Notes |
|---|---|---|---|
| GET, HEAD | `/` | `PageController::home` | Home |
| GET, HEAD | `/contact` | `ContactController::show` | Form with CSRF token, signed timestamp, honeypot; `Cache-Control: no-store` |
| POST | `/contact` | `ContactController::submit` | See flow in `ARCHITECTURE.md`; 303 → `/contact#sent` or `/contact#form`; 403 (CSRF), 413, 429 |

## Planned (Phase 4)

`/services`, `/projects`, `/about`, `/careers`, `/journal`, `/brand`, `/legal`, `/investors` (owner decision), `/sitemap.xml`, `/robots.txt`, `/manifest.webmanifest`. Each gets a controller/view, a sitemap entry, canonical + OG metadata, and a test.

## Legacy redirects (implemented, 301)

| Old | New |
|---|---|
| `/page/main` | `/` |
| `/page/contact` | `/contact` |

## Gone (410)

`/home/**` (social-platform experiment), `/page/maps`, `/page/empty`, `/page/design_store`, `/page/material`, `/page/universal`, `/page/qirimcz`.

## Error pages

403, 404, 405 (with `Allow`), 410, 413, 429, 500 — `noindex`, `no-store`, no internal detail.

## Not routes (blocked at the web server)

Dotfiles, any `.php` other than `index.php`, and everything outside `public/` (`app/`, `config/`, `vendor/`, `storage/`, `tests/`, `docker/`, `docs/`). Verified by request against the running stack (all 404).
