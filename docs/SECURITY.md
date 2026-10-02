# Security

## 1. Owner actions (cannot be done in code)

| # | Action | Status |
|---|---|---|
| 1 | Rotate the legacy database password (`SECRET_EXPOSURE_01`) | Owner — "will rotate separately" |
| 2 | Revoke both legacy Telegram bot tokens in BotFather (`SECRET_EXPOSURE_02`, `_03`); create a **new** bot for production | Owner |
| 3 | Review the Mapbox public (`pk.`) token embedded in the legacy `page/contact/script.js` (`SECRET_EXPOSURE_06`, low severity — public tokens are designed to be visible, but must be URL-restricted): restrict or delete it in the Mapbox account. The new site does not embed Mapbox | Owner |
| 4 | Git-history purge (below) | Planned, needs owner go-ahead |

## 2. Git-history cleanup / purge (documented task)

The **public** legacy repository (`EmirTheBest7/aev.platforms`) contains in its history: database and bot credentials, a static admin-token list, private individuals' names and birthdays (`cron.php`, `PII_EXPOSURE_01`), and user uploads/face-recognition models (`PII_EXPOSURE_02`). Deleting files in a new commit does **not** remove them. Rotating credentials (above) is the real mitigation; the purge limits further exposure of personal data.

Checked for *this* repository (`aev.platforms-v2`): the legacy tree was never committed here (`git log --all -- aev.platforms-master` is empty; it is git-ignored). Nothing in this repo's history needs purging for that reason.

Procedure for the legacy repository (owner-run, destructive — requires explicit approval and a backup):

1. Make a private backup clone: `git clone --mirror <url> legacy-backup.git`.
2. Prefer **archiving or deleting the public repository** if it has no value as open source; otherwise rewrite history with `git filter-repo` (not `filter-branch`): remove paths `_inc/functions.php`, `cron.php`, `_inc/cron.php`, `home/_uploads/`, `home/2be_deleted/`, `home/2be_created/**/config.php`, `test.php`, `script.sh`, and any `*.sql` dumps; or use `--replace-text` for specific literals (do **not** paste the secret values into tickets or chat).
3. Force-push all branches and tags; ask GitHub Support to purge cached views/dangling commits and to disable access to old forks/PR refs (`refs/pull/*`) which still serve removed objects.
4. Contact forks' owners if forks exist; assume anything ever public may have been scraped — **treat every exposed secret as compromised regardless of the purge**.
5. Confirm the affected individuals' data (birthdays) is handled according to applicable privacy law; notify them if required.
6. Enable GitHub secret scanning + push protection on the new repository.

Nothing from the legacy history is copied into this repository or its documents (labels only).

## 3. Controls implemented

| Area | Control | Where |
|---|---|---|
| Secrets | Environment-only config; `.env.example` placeholders; `.env` git-ignored; logger redacts sensitive keys and token-shaped text | `config/*.php`, `Env`, `Logger` |
| Entry point | Only `public/index.php`; dotfiles/other `.php` blocked at nginx and Apache; docroot excludes source/config/vendor/storage | `docker/nginx/default.conf`, `public/.htaccess` |
| CSRF | `Core\Auth\Security\CsrfProtection` on every POST; token rotated after success; timing-safe compare | `ContactController` |
| Spam/abuse | Honeypot, HMAC-signed form age (3 s–2 h), file-based rate limit (3/10 min, 10/day per IP), 16 KB body cap, PHP `post_max_size=64K` | `ContactController`, `RateLimiter`, `docker/php.ini` |
| Input | Server-side validation, control-character stripping, allow-listed services; output escaped in views; notification is plain text (no markup parsing) | `ContactValidator`, `View` |
| IDs | Server-generated, random, non-sequential reference; client IDs ignored | `LeadStore::newReference` |
| Privacy | IP never stored — keyed HMAC pseudonym only; lead files 0600; logs avoid names/emails/messages | `Signer::pseudonym`, `LeadStore`, `Logger` |
| Notifications | Telegram over HTTPS only, 2 s connect/N s total timeout, no redirects, failure never breaks request, token never logged | `TelegramNotifier` |
| Proxy trust | `X-Forwarded-For/Proto` honoured only from `TRUSTED_PROXIES` | `ClientIp`, `Application` |
| Sessions | `core/auth` `SessionManager`: HttpOnly, SameSite=Lax, `Secure` when `APP_URL` is https, regenerate-on-login, destroy-on-logout; plus `use_strict_mode`, `use_only_cookies` | `Application`, `public/index.php` |
| Errors | Safe 403/404/405/410/413/429/500; no traces/paths/SQL; `display_errors=Off`; debug text only when `APP_ENV≠production` and `APP_DEBUG=true` | `ErrorController`, `Application` |
| Transport | Optional HTTPS redirect; HSTS (1 year, includeSubDomains) when `APP_URL` is https in production | `Application`, `SecurityHeaders` |
| Runtime | Container runs as `www-data`; image has no tests/dev deps; `expose_php=Off`; `server_tokens off` | `docker/` |

## 4. Headers (applied to every response)

```
Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:;
  font-src 'self'; connect-src 'self'; manifest-src 'self'; worker-src 'self'; object-src 'none';
  base-uri 'none'; form-action 'self'; frame-ancestors 'none'   [+ upgrade-insecure-requests in https production]
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()
Cross-Origin-Opener-Policy: same-origin
Strict-Transport-Security: max-age=31536000; includeSubDomains   [https production only]
```

Consequences: no inline `<script>`/`<style>`/`style=""`/`on*=`; no third-party hosts. Anything that needs an external origin (maps, analytics, embeds) requires a documented CSP exception here and owner approval. WebGL/canvas and web fonts are same-origin and need no exception.

## 5. Legacy findings and their resolution

| ID | Resolution |
|---|---|
| `SECRET_EXPOSURE_01–06` | Not migrated; env config; owner rotation (§1) |
| `LOGIC_BUG_01` (always-true host check) | `APP_ENV` from environment; no host comparison anywhere |
| `RCE_RISK_01` (`eval` include) | Removed; explicit templates only |
| `INJECTION_SQL_*`, `CRYPTO_WEAK_01` (MD5) | `home/*` not migrated; `core/auth` uses PDO + Argon2id |
| `XSS_01/02`, `CSRF_01`, `DOS_01`, `DATA_INTEGRITY_01` | Contact flow redesign (§3); spotlight rewrite in Phase 5 |
| `DB_DUMP_01`, `PII_EXPOSURE_01/02`, `UPLOAD_01` | Deleted, never migrated |
| `HEADERS_01`, `REDIRECT_01`, `ROBOTS_01`, `UA_BLOCK_01`, `THIRD_PARTY_01` | §4; explicit routes; static robots; rate limiting instead of UA blocklist; self-hosted assets |

## 6. Reporting

See the repository `SECURITY.md` for the disclosure contact.
