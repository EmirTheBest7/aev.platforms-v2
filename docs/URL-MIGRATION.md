# URL migration

Principles: preserve public URLs; **single-hop 301** to the new location; **no 410** for anything being preserved (unbuilt paths answer an honest 404 until rebuilt); canonical form has no trailing slash. Implemented in `routes/legacy.php`; this table is the plan.

Status: ✅ live in v2 · 🟡 planned (page being ported) · ⏳ waiting for the product to return.

| Old URL | New URL | Action | Status | Notes |
|---|---|---|---|---|
| `/` (was 301 → `/page/main/`) | `/` | serve | ✅ | |
| `/page/main/` | `/` | 301 | ✅ | |
| `/page/contact/` | `/contact` | 301 | ✅ | map + fish pending |
| `/page/careers/`, `/page/careers/list/` | `/careers` | 301 | 🟡 | |
| `/page/careers/desc/?job_url=<token>` | `/careers/<token>` | 301 (token preserved) | 🟡 | **SQL-injection site — port with prepared statements.** Job rows live in the DB, not the repo |
| `/page/careers/team/` | `/careers/team` | 301 | 🟡 | |
| `/page/downloads/` | `/downloads` | 301 | 🟡 | |
| `/page/downloads/logo/*`, `/page/downloads/docs/*` | `/assets/brand/*`, `/downloads/docs/*` | 301 (same file names) | 🟡 | logos may be hot-linked externally |
| `/page/updates/` | `/journal` | 301 | 🟡 | launcher "Journal" |
| `/page/investor-relations/` | `/investor-relations` | 301 | 🟡 | |
| `/page/services/`, `/page/services/pragueflow/` | `/services`, `/services/pragueflow` | 301 | 🟡 | |
| `/page/hester/`, `/page/hester/avrora/`, `/page/hester/1o/` | `/hester`, `/hester/avrora`, `/hester/1o` | 301 | 🟡 | AI endpoints server-side |
| `/page/maps/` | `/maps` | 301 | ⏳ | INCOMPLETE app |
| `/page/design_store/**` | `/store-concept/**` | 301 | ⏳ | |
| `/page/DC25/`, `/page/qirimcz/` | `/events/dc25`, `/events/qirimcz` | 301 | 🟡 | archive pages |
| `/page/material/`, `/page/universal/`, `/page/history/`, `/page/legal/`, `/page/empty/` | same slugs without `/page` | 301 | 🟡 | `empty` = designed pending state |
| `/home/auth/`, `/home/auth/reset/` | `/account/login`, `/account/reset` | 301 | ⏳ | do not index |
| `/home/timeline/**` (Space) | `/space/**` | 301 | ⏳ | |
| `/home/profile/?nickname=` and `/@<nick>` | `/@<nick>` | keep | ⏳ | |
| `/home/messenger/`, `/home/videos/`, `/home/store/`, `/home/wallet/`, `/home/studio/**`, `/home/finance/` | `/messenger`, `/videos`, `/store`, `/wallet`, `/studio/**`, `/finance` | 301 | ⏳ | per `MIGRATION.md` order |
| `/home/_api/UI/**` (terminal) | `/terminal/**` | 301 | ⏳ | pages/tools/admin/games |
| `/home/_api/UI/?0x=<code>` (error pages) | rendered by the error handler | — | ✅ | |
| `/home/_api/UI/?Page=4ukraine` | `/4ukraine` | 301 | 🟡 | |
| `/home/_api/Docs/**` | `DOCS_URL` or `/docs` | 301 | ⏳ | `docs.aliev.io` does not resolve |
| `/robots.txt` | `/robots.txt` (static) | serve | 🟡 | |
| `/sitemap.xml` | generated | serve | 🟡 | exclude auth/reset |
| `/manifest.json` | `/manifest.webmanifest` | 301 | 🟡 | icons relative |
| `/.well-known/brave-rewards-verification.txt` | same | serve | 🟡 | owner's Brave verification file |
