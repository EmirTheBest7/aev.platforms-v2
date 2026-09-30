# URL migration (draft — finalized in Phase 4)

New URLs are proposals; the router and `ROUTES.md` are created in Phase 2/4. Redirects are single-hop 301s (no chains). Query-string job URLs are not preserved (the job data source changes).

| Old URL | New URL | Action | HTTP | Notes |
|---|---|---|---|---|
| `/` | `/` | Serve home directly | 200 | Old: 302/301 to `/page/main/` |
| `/page/main/` | `/` | Redirect | 301 | Also listed in old sitemap |
| `/page/contact/` | `/contact/` | Redirect | 301 | In old sitemap |
| `/page/careers/` | `/careers/` | Redirect | 301 | Old index redirected to `list/` |
| `/page/careers/list/` | `/careers/` | Redirect | 301 | In old sitemap |
| `/page/careers/desc/?job_url=*` | `/careers/` | Redirect | 301 | IDs were DB-backed; unrecoverable |
| `/page/downloads/` | `/brand/` | Redirect | 301 | Brand assets page |
| `/page/downloads/logo/*` | `/assets/brand/*` | Redirect (same filenames) | 301 | Keep stable logo URLs — may be hotlinked externally |
| `/page/updates/` | `/journal/` | Redirect | 301 | |
| `/page/investor-relations/` | `/investors/` | Redirect (if kept) | 301 | Owner decision |
| `/page/legal/` | `/legal/` | Redirect | 301 | |
| `/page/history/` | `/about/` | Redirect | 301 | Merged into About |
| `/page/services/` | `/services/` | Redirect | 301 | |
| `/page/services/pragueflow/` | `/projects/pragueflow/` | Redirect | 301 | |
| `/page/hester/` | `/` (or bot link) | Redirect | 301 | Depends on bot liveness |
| `/page/maps/`, `/page/empty/` | `/` | Gone | 410 | Coming-soon stubs |
| `/page/design_store/**`, `/page/DC25/`, `/page/qirimcz/`, `/page/material/`, `/page/universal/` | — | Gone (or redirect to `/` for DC25 if kept) | 410 | No inbound references |
| `/home/auth/` | `/account/login` (staff only) or gone | Redirect/410 | 301/410 | Owner decision |
| `/home/auth/reset/` | gone | 410 | 410 | Was in sitemap — remove |
| `/home/timeline/`, `/home/messenger/`, `/home/videos/`, `/home/profile/`, `/home/store/`, `/home/wallet/`, `/home/studio/**`, `/home/finance/`, `/home/_api/**` | — | Gone | 410 | Archived/unrebuilt platform |
| `/@<nick>` | — | Gone | 410 | Profiles not migrated |
| `/robots.txt` | `/robots.txt` | Static file | 200 | Was a 301 to PHP |
| `/sitemap.xml` | `/sitemap.xml` | Generated static | 200 | |
| `/manifest.json` | `/manifest.webmanifest` | Redirect | 301 | Icons use relative paths |
