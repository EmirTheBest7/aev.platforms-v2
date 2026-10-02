# Testing

```bash
docker compose exec app vendor/bin/phpunit                                  # 88 tests
docker compose exec app vendor/bin/phpstan analyse --memory-limit=512M      # level 8
docker compose exec app vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes
```

| Suite | Covers |
|---|---|
| `tests/Unit` | validators, router (redirects, `{param}` routes, 405/404), path normalisation, support classes |
| `tests/Feature/SiteTest` | home (headers, SEO, no Hester/Avrora, cache-busted assets), legacy redirects, https/proxy rules, hire/contact flow (CSRF, honeypot, form age, rate limit, size, notifier failures, no leaks), Mapbox CSP only with a token |
| `CareersTest` | list/job/team, escaping, logo-name containment, bad slugs → 404, SQL injection, empty state, database failure → generic 503, legacy redirects |
| `DownloadsTest` | every configured file exists and is linked, missing documents are honest, no dead links/inline handlers |
| `AuthTest` | register → login → logout against the real MariaDB schema (skipped without `DB_HOST`): Argon2id, session regeneration, identical failure messages, CSRF, duplicates, SQLi, honeypot, server-side session expiry |
| `ApiTest` | `_api` allow-list (unknown paths 404, no CORS), domain-tool validation/rate limit, server-side Valentine notification with fixed text, no secrets in the bundle |

## Browser checks (required for UI work)

PHPUnit green does not mean a page works. `scripts/visual/` (Playwright):

```bash
cd scripts/visual && npm i
node states.mjs http://localhost:8080 / /tmp/shots                     # main page states at 4 viewports
node pages.mjs  http://localhost:8080 /tmp/shots /careers /contact /downloads /home/auth /home/_api/UI/
scripts/visual/legacy-baseline.sh up                                    # sanitised ./aev-new on :8099 for side-by-side
```

Regression proof for restructures: `node scripts/visual/baseline.mjs <dir>` before and after, then `python3 scripts/visual/compare-baseline.py <before> <after>` (HTML, requests, console errors and chrome boxes of every route at four viewports; remaining differences must be intended). Run `php scripts/build.php` first when assets changed — the phpunit bootstrap does it automatically.

Viewports: 1440×900, 820×1180, 390×844, 844×390. Reports horizontal overflow, console errors and failed requests per page. Never submit the baseline's forms.
