# Testing

## Automated (PHPUnit 11, PHP 8.3 — run in Docker)

```bash
docker compose run --rm app vendor/bin/phpunit
```

| Suite | File | Covers |
|---|---|---|
| unit | `tests/Unit/SupportTest.php` | `.env` parsing (real env wins), typed getters, HMAC signer (tamper/short key), rate limiter (limit, isolation, hashed keys), client-IP resolution (spoofing, CIDR, forwarded chain), seasonal icon boundaries, logger redaction (keys + token-shaped text), lead reference format/uniqueness, lead file mode 0600 |
| unit | `tests/Unit/ValidationAndRoutingTest.php` | contact validation/normalisation/limits, router (match, HEAD, redirect, 410 prefix boundary, 405, 404), path normalisation |
| feature | `tests/Feature/SiteTest.php` | home (headers, SEO, no inline script/style), no dead `#` links, safe 404, legacy 301/410, 405 + `Allow`, HTTPS redirect trust model + HSTS, trailing-slash canonicalisation (single hop), contact: form tokens, valid submission end-to-end, client cannot choose reference, CSRF failure/missing, honeypot, too-fast/stale/forged timestamp, validation errors escaped, rate limit (429), oversize (413), notifier failure keeps lead, exception never leaks detail |

Static analysis: `vendor/bin/phpstan analyse` (level 8, must be clean). Style: `vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes`. CI (`.github/workflows/ci.yml`) runs lint (8.3, 8.4), composer validate, PHPUnit, PHPStan, CS and a production Docker build.

## Stack/E2E checks performed (Phase 2)

Against nginx + php-fpm from `compose.yaml`: status codes for public paths; **every private path returns 404** (`/.env`, `/.git/config`, `/composer.json`, `/app/…`, `/config/…`, `/vendor/…`, `/storage/…`, `/tests/…`, `/docker/…`, traversal); security headers; HEAD sends no body. Playwright/Chromium: menu toggle + Escape, "Hire us" navigation, full contact submission (server reference shown, lead file mode 0600, pseudonymised `who`), validation-error rendering, flash cleared on reload, 404 page, first Tab stop = "Skip to content".

## Visual regression (Phase 3 baseline → Phase 7)

`scripts/visual/` rebuilds a **sanitised** copy of the legacy site (stub `functions.php`: no credentials, `notify()` is a no-op, absolute production URLs rewritten to localhost), serves it with `php -S` (6 workers — the legacy homepage requests itself), and screenshots pages at desktop 1440, tablet 820, phone 390, phone-landscape 844×390 in light/dark colour schemes. Curated baselines are in `docs/visual-baseline/`. Never submit forms or run `cron.php` in the legacy copy.

Known baseline limits: third-party hosts (Cloudflare challenge, Google Analytics, widgetbot, Mapbox/crypto APIs) are not reachable/consistent, so those regions differ; animated regions (globe, toasts, marquee) are compared structurally, not pixel-for-pixel.

## Pending

Spotlight, launcher, globe (WebGL fallback), PWA, sitemap/robots — tests are added as each ships (Phases 4–5).
