# Legal pages and content the owner must provide

The application ships the **structure** for the Privacy Policy and the Imprint. It contains no company details and no legal text — those are the owner's to provide (and, for the policy, to have reviewed). Nothing here is legal advice.

## What exists

| URL | Template | Content source |
|---|---|---|
| `GET /privacy` | `website/legal/views/privacy.php` | `config/legal.php` → `privacy.sections` (paragraphs of plain text per section) |
| `GET /imprint` | `website/legal/views/imprint.php` | environment `LEGAL_*` → `config/legal.php` → `operator`; e-mail from `integrations.destinations.email` |

The main-menu "Privacy Policy" link points to `/privacy`. The two pages link to each other and to `/contact`; the menu has no Imprint entry (adding one changes the menu design — owner decision).

While anything required is empty the page says **"Placeholder … the owner must provide"**, shows "To be provided by the owner." per missing item, and sends `noindex,nofollow`. When all required items are filled in, the notice disappears and the page becomes indexable. A test (`tests/Feature/LegalTest.php`) covers both states.

## What the owner must provide

1. **Environment** (deployment secrets/config, never the repository): `LEGAL_OPERATOR_NAME`, `LEGAL_OPERATOR_ADDRESS`, `LEGAL_REGISTRATION_ID` (required); `LEGAL_REPRESENTATIVE`, `LEGAL_REGISTER_ENTRY`, `LEGAL_VAT_ID` (optional, shown when set).
2. **Policy text** in `config/legal.php` → `privacy.sections[*].body` (and `privacy.updated`, a date string). Section headings are generic and may be changed.
3. A decision whether the main menu also links to the Imprint (and whether the contact form should link to the policy).

## Factual inventory of what the site processes (for whoever writes the policy)

Taken from the code on `main`; verify against the deployment.

| Area | Data | Where it goes | Notes |
|---|---|---|---|
| Contact form (`/contact`) | e-mail, subject, message | `storage/leads/leads-YYYY-MM.jsonl` (+ Telegram message if `NOTIFY_DRIVER=telegram`) | Personal data. Retention period to be defined (docs/OPERATIONS.md suggests 24 months). Erasure = delete the matching line. |
| Hire form (main page) | name, e-mail, selected services (`website/home/HireValidator.php`) | same leads file / Telegram | as above |
| Accounts (`/home/auth`, only when `AUTH_ENABLED=true`) | username, e-mail, Argon2id password hash, role | MariaDB `users` | Off by default. |
| Session cookie `aliev_session` | random session id (CSRF protection; login state when accounts are on) | browser cookie, `HttpOnly`, `SameSite=Lax`, `Secure` on https | Set on pages that contain a form. Strictly necessary. |
| Abuse protection | keyed hash of the client IP (no raw IP stored), form-age and honeypot checks | `storage/ratelimit/*.json`, logs (pseudonym `who`) | |
| Logs | structured JSON; no passwords, tokens, e-mails or message bodies | stderr / `storage/logs` | |
| Intergram chat (only if `INTERGRAM_CHAT_ID` is set) | whatever the visitor types; connects to `intergram.xyz`, relayed to Telegram | third party | Needs disclosure and a processor decision. |
| Mapbox map (only if `MAPBOX_TOKEN` is set, contact page) | visitor IP and map requests to `api.mapbox.com` / `events.mapbox.com` | third party | |
| Crypto ticker (`/api/prices`) | none from visitors; the server calls CoinGecko/Coinbase | — | |
| `/home/_api/` legacy bundle | loads Google Fonts, cdnjs, jsDelivr and other CDNs in the visitor's browser (see `docker/apache/api-csp.conf`) | third parties | Visitor IPs reach those CDNs when these pages are opened. |
| Analytics / ad cookies | none | — | None are implemented. |

## Other launch content (not legal, same owner-provided status)

See `docs/ARCHITECTURE.md` §9 and `config/works.php` (Works slider), `config/downloads.php` (Whitepaper, Keynote), the `jobs` table (Careers).
