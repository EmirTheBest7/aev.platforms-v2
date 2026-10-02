# Security

Rules: secrets only from the environment · no raw SQL strings · no dynamic code execution · CSRF on every state change · nothing from the client is trusted · errors never show internals. (`CLAUDE.md` §Security.)

## Secrets
- `.env.example` has placeholders only; `.env` is git-ignored. Production values come from the deployment environment.
- Legacy credentials are treated as compromised and were never copied. Two credentials found embedded in retained files (a Telegram bot token in the Valentine page, a Mapbox token in `contact.js`) were **removed**, not migrated; revoke them at the providers. The repository tree and its full history were scanned for tokens/keys/private keys: clean.
- Telegram is server-side only (`TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`); the Mapbox token comes from `MAPBOX_TOKEN` and is rendered into the Contact page only.

## Request pipeline
`public/index.php` → `Application::handle` → router → controller. Apache serves only `public/`; dotfiles and every `.php` except `index.php` answer 404; directories without `index.html` belong to the application; SVG documents are served with a sandboxing CSP.

## Headers & CSP (`SecurityHeaders`)
`default-src 'self'`, no inline script/style/handlers, `frame-ancestors 'none'`, `base-uri 'none'`, nosniff, strict referrer policy, HSTS in production over HTTPS. Documented exceptions (per response / path):
| Where | Widening | Why |
|---|---|---|
| `/` with `INTERGRAM_CHAT_ID` | `frame-src` Intergram | support chat (widget script self-hosted) |
| `/contact` with `MAPBOX_TOKEN` | Mapbox hosts, `blob:` workers/images | map |
| `/widgets/*` | `frame-ancestors 'self'` | framed by the main page |
| `/home/_api/` static bundle | `docker/apache/api-csp.conf` (inline + listed CDNs) | legacy pages; first modernization item is self-hosting them and dropping `unsafe-inline` |

## Forms (`FormGuard`)
Body-size limit → session + CSRF (the one `core/auth` instance) → honeypot → HMAC-signed form age (3 s–2 h) → two-window per-IP rate limit (file based, hashed keys). Leads store a keyed pseudonym of the IP, never the address.

## Accounts (`core/auth`, enabled with `AUTH_ENABLED=true`)
Argon2id; policy ≥10 chars with upper/lower/digit; per-account lockout + per-IP limits; one failure message for unknown email / wrong password / lockout and a decoy hash on unknown emails (no timing oracle); one "not available" message for duplicate email/nickname; no availability lookups; `session.use_strict_mode`, `use_only_cookies`, HttpOnly + SameSite=Lax (+Secure on https) cookie, id regenerated on login, server-side lifetime enforcement, session emptied and cookie expired on logout; audit log table. Password reset by e-mail is not offered (no mail server) and says so.

## Data access
All SQL is in `core/auth` repositories and `Website\Careers\JobRepository`: prepared statements, slugs validated before any query, only published rows, database errors → logged + generic 503. Job text is plain text and always escaped.

## `_api`
Explicit allow-list (no function dispatch from the URL, no credentials in URLs, no CORS, `no-store`); POST needs the CSRF header; rate limits on the domain tool and the notification; the terminal inserts everything as text and reads same-origin paths only; the domain tool validates host names and returns only a boolean.

## Open items
`aliev.io` unregistered (HSTS/canonical/OG/e-mail); Mapbox GL self-hosting terms unverified; the legacy `_api` pages still use inline scripts and third-party CDNs under their relaxed CSP; history purge of the public legacy repository is a separate task.
