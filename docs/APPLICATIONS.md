# Applications (the launcher)

The launcher is the **ecosystem's app switcher** (see `PROJECT-VISION.md` §5). Two legacy lists exist and both are preserved:

- the **visible grid** ("Web Apps", 11 tiles + footer `aliev.io` / `All Apps`) in `page/main/index.php`;
- the **spotlight / "All Apps" list**, `page/main/data.json` (20 entries, fetched by Spotlight search).

Rule: **no tile or entry is removed.** An entry whose product is not rebuilt yet keeps its tile and opens a designed status toast (the owner's "Coming soon" convention, in the existing notification UI). Real destinations replace broken ones as soon as they exist. The single source of truth in v2 will be `config/apps.php`; Spotlight and the grid both read it.

Liveness was probed where network access allowed (`aliev.io`, `docs.aliev.io`, `qirimtalk.com` do not resolve from the test environment; Telegram, Instagram and the Hester bot respond).

## Visible grid (11)

| # | Tile (icon) | Original target | What it is | Class | Target in v2 now | Owner input |
|---|---|---|---|---|---|---|
| 1 | Account (avatar) | `/home/profile/` | Profile/account of the registered user | rebuild on `core/auth` | `/account` when `AUTH_ENABLED`, else toast | Expose staff/user login? |
| 2 | Space (`timeline.svg`) | `/home/timeline/` | Social feed | INCOMPLETE→rebuild (SEC-RISK impl.) | toast until `apps/social` | Order of return |
| 3 | Maps (`maps.svg`) | `/page/maps/` | Maps app ("Layers") | INCOMPLETE | toast | Scope (Mapbox/OSM) |
| 4 | Messenger (`messenger.svg`) | `/home/messenger/` | Chat | rebuild | toast until `apps/messenger` | Order |
| 5 | Videos (`video.svg`) | `/home/videos/` | Video channel/player | EXPERIMENTAL | toast | Order |
| 6 | HesterGPT (`hester.svg`) | `/page/hester/` | AI assistant | SEC-RISK impl. → ported | opens panel / `/hester` | `GEMINI_API_KEY` |
| 7 | Finance (`Plant.svg`) | `/home/finance/` (never existed) | Wallet/AEVT finance (roadmap) | INCOMPLETE | toast | Scope; token integration |
| 8 | _API (`Cloudshot.svg`) | `/home/_api/UI/` | Terminal/CMD playground | EXPERIMENTAL | toast until terminal port | Order |
| 9 | Docs (`Book.svg`) | `/home/_api/Docs/` | CVX docs app | INCOMPLETE | `DOCS_URL` else toast | Where do docs live? (`aliev-docs`?) |
| 10 | Journal (`Paste.svg`) | `/page/updates/` | Release notes | works | `/journal` | — |
| 11 | App (`Folder.svg`) | `#` | `Purpose unclear — requires owner review` | — | toast | Intended target |
| — | Footer `aliev.io` | none | home | — | `/` | — |
| — | Footer `All Apps` | none | full app list | — | opens Spotlight (lists all 20) | — |

## Spotlight / `data.json` (20)

| # | Name | Original URL | Class | v2 target | Notes |
|---|---|---|---|---|---|
| 1 | ΛΞV Account | `/home/auth/` | rebuild | `/account` or toast | |
| 2 | ΛΞV Space | `/home/timeline/` | rebuild | toast | |
| 3 | ΛΞV Maps | `/page/maps/` | INCOMPLETE | toast | |
| 4 | ΛΞV Messenger | `/home/messenger/` | rebuild | toast | |
| 5 | ΛΞV Videos | `/home/videos/` | EXPERIMENTAL | toast | |
| 6 | HesterGPT Lite | `/page/hester/` | ported | `/hester` | |
| 7 | HesterGPT Pro | `#Empty` | announced (roadmap) | toast | `Purpose unclear` |
| 8 | ΛΞV Finance | `/home/finance/` | roadmap | toast | |
| 9 | ΛΞV _API | `/home/_api/UI/` | EXPERIMENTAL | toast → terminal | |
| 10 | ΛΞV Docs | `/home/_api/Docs/` | INCOMPLETE | `DOCS_URL` | |
| 11 | ΛΞV Journal | `/page/updates` | works | `/journal` | |
| 12 | ΛΞV Contacts | `/page/contact` | works | `/contact` | |
| 13 | ΛΞV Jobs | `/page/careers/list/` | SEC-RISK impl. | `/careers` | |
| 14 | ΛΞV Downloads | `/page/downloads/` | works | `/downloads` | |
| 15 | ΛΞV Investor Relations | `/page/investor-relations/` | works | `/investor-relations` | |
| 16 | Darknet | `#Empty` | roadmap marker | toast | `Purpose unclear — requires owner review` |
| 17 | QirimTalk | `https://qirimtalk.com/` | sibling project (forum/social for Crimeans in Europe) | external link | domain did not resolve in the test environment — owner to confirm URL |
| 18 | Instagram | `instagram.com/aev.platforms/` | social | external link | responds |
| 19 | Telegram | `t.me/s/aev_platforms` | social/news | external link | responds |
| 20 | Home Page | `https://www.aliev.io` | self | `/` | |

All 20 icon fields in `data.json` point at the same `timeline.svg` — a copy/paste artefact. The grid already uses distinct icons (`page/main/img/icons/*.svg`); the spotlight list will use them too. Both lists stay; neither is shortened.
