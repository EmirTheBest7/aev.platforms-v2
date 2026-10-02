# Migration record (legacy `aev-new` → this project)

Source of truth: the manually cleaned `./aev-new` (git-ignored); the original repository `EmirTheBest7/aev.platforms` is the archive.

| Area | Result |
|---|---|
| Main page | Ported with the original CSS/JS behaviour; HesterGPT and Avrora removed; ticker via `/api/prices`; globe/Intergram/PWA kept; menus wired by `data-*` hooks (CSP) |
| Careers | `/careers`, `/careers/{slug}`, `/careers/team`; MariaDB `jobs` (migration 003), `JobRepository`; dev seed `database/seeds/dev-jobs.sql`; JSON import `scripts/import-jobs.php` |
| Contact | Original page (map, locations, email/subject/message); real form pipeline; Mapbox token from env |
| Downloads | `config/downloads.php`; real download links; Whitepaper/Keynote shown as not available |
| Auth | Flip-card Log In / Sign Up on `core/auth`; wallet sign-in panel, availability lookups and reset-by-email dropped (reasons in `SECURITY.md`) |
| `_api` | Static bundle (UI, terminal, Docs, tools, 4ukraine, donate, valentine) + allow-listed PHP endpoints; games, store, v2 terminal, GTIN, antitup notes, birthday page and error pages removed |
| Profile panel | `aev-profile-options` restyled with the supplied SaaS Widget; behaviour unchanged |

Removed on purpose (not carried over): social/messenger/forum/store/wallet products, HesterGPT/AI, browser games, hot-linked CDN fonts/CSS (self-hosted), wallet Sign-In options, embedded credentials, dynamic `_api` dispatch, md5 password login, `eval`-style execution, raw SQL. Rationale per change: `CHANGE-LOG.md`; ecosystem-era plans: `docs/history/`.

Kept but unlisted: `public/downloads/wallpapers/LooksGood.png` (30 MB, never linked by the original page), PWA / seasonal icon sets, `resources/fonts/*`, generic launcher icons.
