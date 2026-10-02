# Operations

## Logging

Structured JSON lines via `Core\Logging\Logger` (`LOG_CHANNEL=stderr|file|null`, `LOG_LEVEL`). In containers use `stderr` and collect with the platform's log driver; `file` writes `storage/logs/app-YYYY-MM-DD.log` (dir 0750).

Never logged: passwords, tokens, session/CSRF values, authorization/cookie headers, e-mail addresses, message bodies, raw IPs (a keyed 16-hex pseudonym `who` is used to correlate abuse). Context keys matching `pass|secret|token|authorization|cookie|api_key|csrf|session|email|message` are redacted automatically, and bot-token-shaped strings in free text are scrubbed. Exceptions are logged as class + scrubbed message + `file:line` (no trace).

Notable events: `contact.accepted` (reference, notified flag), `contact.csrf_rejected`, `contact.rate_limited`, `contact.honeypot`, `contact.persist_failed` (error), `notify.telegram.failed` (HTTP status / curl errno only), `request.unhandled`.

## Data in `storage/`

| Path | Content | Retention |
|---|---|---|
| `leads/leads-YYYY-MM.jsonl` | contact requests (name, email, company, services, message, reference, pseudonym) — **personal data** | Define a retention period (suggested 24 months) and delete/rotate older files; back up; restrict access (0600) |
| `ratelimit/*.json` | hashed rate-limit counters | safe to delete any time; prune files older than 2 days |
| `logs/` | application logs | rotate (logrotate/Docker) |
| `cache/` | reserved | — |

Subject-access/erasure requests: search leads by e-mail and remove the matching lines.

## Health & smoke checks

`GET /` → 200; `GET /contact` → 200 with `Cache-Control: no-store`; `GET /.env` → 404. There is deliberately no public status/debug endpoint.

## Incident basics

Suspected credential leak: rotate `APP_KEY` (invalidates signed form tokens only), the Telegram token, and DB credentials; check `contact.*` logs for spikes. Spam wave: lower limits in `ContactController`, or block at the proxy.
