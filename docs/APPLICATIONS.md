# Applications (launcher) audit

Source: `page/main/data.json` (20 entries). Every entry currently uses the same icon (`timeline.svg`) — a copy/paste artefact; the launcher will get per-destination icons from the existing `page/main/img/icons/*.svg` set.
Liveness of external targets has **not** been probed in Phase 1 (no network path to `aliev.io`); "external" entries are re-verified in Phase 5.

| # | Name | Old URL | Class | Decision |
|---|------|---------|-------|----------|
| 1 | ΛΞV Account | `/home/auth/` | dead (SQLi + MD5 login) | **Remove** from launcher. If staff login is required, expose `apps/account` (built on `core/auth`) behind `/account/` and keep it out of the public launcher/sitemap. |
| 2 | ΛΞV Space | `/home/timeline/` | archived experiment | Remove |
| 3 | ΛΞV Maps | `/page/maps/` | stub ("Coming soon!") | Remove |
| 4 | ΛΞV Messenger | `/home/messenger/` | archived experiment | Remove |
| 5 | ΛΞV Videos | `/home/videos/` | archived experiment | Remove |
| 6 | HesterGPT Lite | `/page/hester/` | static UI, no verified backend; Telegram bot link exists | Replace with external link to the Telegram bot *if the bot is alive* (verify), else remove |
| 7 | HesterGPT Pro | `#Empty` | dead | Remove |
| 8 | ΛΞV Finance | `/home/finance/` | **directory does not exist** in old tree | Remove |
| 9 | ΛΞV _API | `/home/_api/UI/` | terminal/games playground | Remove (archive) |
| 10 | ΛΞV Docs | `/home/_api/Docs/` | old docs viewer | Replace with the real docs destination (README links `docs.aliev.io` — verify) or remove |
| 11 | ΛΞV Journal | `/page/updates` | active page | **Keep** → `/journal/` (redirect from old) |
| 12 | ΛΞV Contacts | `/page/contact` | active page | **Keep** → `/contact/` |
| 13 | ΛΞV Jobs | `/page/careers/list/` | active page | **Keep** → `/careers/` |
| 14 | ΛΞV Downloads | `/page/downloads/` | active page | **Keep** → `/brand/` (brand assets) |
| 15 | ΛΞV Investor Relations | `/page/investor-relations/` | active page | Keep if owner confirms it should be public; otherwise remove |
| 16 | Darknet | `#Empty` | dead | Remove |
| 17 | QirimTalk | `https://qirimtalk.com/` | external project | Verify; keep only if alive and owner-related |
| 18 | Instagram | `instagram.com/aev.platforms/` | external social | Keep (footer/launcher) after verification |
| 19 | Telegram | `t.me/s/aev_platforms` | external social | Keep |
| 20 | Home Page | `https://www.aliev.io` | self link | Remove (redundant) |

Result: launcher shrinks from 20 tiles to roughly 7–9 working destinations. No fake tiles remain.
