> **Historical record — not current scope.** Written for the original ALIEV.IO ecosystem / earlier phases of the rebuild. The current project is the digital-studio site described in [`../ARCHITECTURE.md`](../ARCHITECTURE.md). Kept for context; do not treat anything here as a requirement.

# ALIEV.IO — project timeline (factual, from repository evidence)

Sources: git history of `EmirTheBest7/aev.platforms` (branch `master`, **211 commits, 2024-03-10 → 2026-07-30**, read from a scratch clone; short hashes are real), the files at `HEAD`, the owner's release notes (`page/updates`), planning notes (`home/_api/UI/terminal/Page/antitup/{plan,hidden,old}.md`) and the owner's other public repositories. Statements marked **(inferred)** are reconstructions the evidence supports but does not state. Where git cannot answer, this says so.

## 0. What git can and cannot tell us

- **One author** (210 commits) plus one single-line contribution by another account (`b1a29b4`, 2024-03-10, "Update index.php"). **No tags, no releases, one branch.**
- The first commit (`5564ba1`, "Initial commit", 2024-03-10) is an empty README; the second (`b6c39b0`, "Project Commit", same day) adds **1,433 files / +332,144 lines in one step** — the existing platform (`home/*`, `page/*`, `_assets`, `_inc`). **Everything before 2024-03-10 — the development of the platform itself — is not in git.** Evidence of an earlier life: the platform's own version scheme ("v168.7.2"), the `2be_created` / `2be_deleted` staging folders, the `Pyper`/`WebOS`/`dashboard` generations, and sibling repositories dated 2021–2022 (`exam-pub` 2021-09, `ChunkyPip` 2022-03, `Hester` 2022-10).
- In the entire history there are **only 12 file deletions** (e.g. `home/auth/index.copy.php` in "Auth restore", `page/hester/intro/*` merged into the Hester page, `page/careers/desc/index.original.php`, `page/main/img/IMG_7780.jpg`). **The project grew almost purely by addition; the owner almost never removed things.** That is itself a statement of intent.
- Commit subjects are mostly generic ("Update index.php"); 100+ of the 211 are, so features are dated by the few descriptive commits and by the owner's release notes.

## 1. Activity by month

| Month | Commits | Month | Commits |
|---|---|---|---|
| 2024-03 | 29 | 2025-02 | 15 |
| 2024-05 | 24 | 2025-03 | 13 |
| 2024-06 | 2 | 2025-05 | 5 |
| 2024-08 | 17 | 2025-06 | 38 |
| 2024-09 | 16 | 2025-07 | 4 |
| 2024-10 | 7 | 2026-02 | 4 |
| 2024-11 | 17 | 2026-03 | 1 |
| 2024-12 | 16 | 2026-04 | 1 |
| 2025-01 | 1 | 2026-07 | 1 |

Gaps: 2024-07, 2025-04, 2025-08 → 2026-01 (nothing), mid-2026 only README work.

## 2. Eras

| Era | Dates | Character |
|---|---|---|
| **Import & identity** | 2024-03 | The platform enters git; logo changed (`1e1457c`, 03-14); PWA modal; main-menu redesign; notifications go live; universal-page template. |
| **Public face** | 2024-05 → 06 | Careers, contact map + fish, downloads table, investor relations, ACS paper, Material experiment, a personal web tool in the hosting layer. |
| **HesterGPT & the platform release** | 2024-08 → 10 | Hester page and main-page integration, QR tool, store concept, fonts, **v168.7.2** (45 files), Spotlight. |
| **Ecosystem entry point** | 2024-11 → 12 | Crypto marquee, launcher + footer panel, Services/PragueFlow, Avrora image generator, HesterGPT versions and mic input. |
| **Concepts & events** | 2025-02 → 06 | Valentine app, Hester v1.6/1.7/1o, Design Store concept, ΛWDC25, QIRIM.CZ event, Hester PWA. |
| **Quiet maintenance** | 2025-07 → 2026-07 | Careers fixes, "Update Patch" (history/services pages), GTIN tool, README rewrites; v2 begins as a separate repository. |

## 3. Chronological log

### 2024
| Date | Commit | Event | Subsystem |
|---|---|---|---|
| 03-10 | `b6c39b0` | **Project import** (1,433 files) | all |
| 03-12 | `4270d49` | "Small changes" (7 files, +5,005) | `_assets`, downloads |
| 03-12 | `7b53f5d` | **Universal Page Added** (template page + SVG) | `page/universal` |
| 03-13 | `56f77ce`, `62ad8f3` | Universal header; "Visually better" | `page/universal` |
| 03-14 | `cc3ebf7` | **PWA Modal Added** to main page | main |
| 03-14 | `1e1457c` | **"Logo Changed"** (the commit itself touches only `page/universal/index.php`; the new logo files must already have been part of the tree — **which logo changed from which is not recoverable from this commit**, requires owner review) | brand |
| 03-14 | `96c56a8` | Contacts updated | main |
| 03-15 | `52b13c4` | Social links added | main |
| 03-25 | `173eb31`, `20b28bb` | **Main Menu Redesign** | main |
| 03-27 | `8da36df` | **AEV Notifications [Activation]** | main |
| 05-20 | `539bfba` | **Material App** (replaced an empty first attempt, `80b31b1`) | `page/material` |
| 05-20 | `9fe88a9` | Careers page updated (+1,124 lines) | careers |
| 05-21 | `eeb7a29` | **Contact Map + Fish Added** (Mapbox dark map, animated fish) | contact |
| 05-25 | `e858112`, `ff44c29` | **Downloads table** | downloads |
| 05-26 | `f7bbd53`, `42ba658` | **Investor Relations**; News feed (Discord WidgetBot) | investor-relations |
| 05-29 | `e3a5d36` | `ACS_System.pdf` added | downloads |
| 06-11 | `689e6bc` | "New Personal Web Tool" — a work-related tool in the user-hosting area (with SQLite DB) | `_uploads` |
| 08-10 | `994a691`, `550924e` | Careers listing for a partner; **ΛΞV Community on main page** | careers, main |
| 08-20 | `a6653b6` | **QR Generator** (terminal tool, later also in downloads) | terminal |
| 08-29 | `2e80c3c`, `929d101`, `5262f56` | **Hester page created**, intro added then merged | hester |
| 08-29 | `0fb0573` | **Store Concept** in terminal (+5,536 lines) | terminal |
| 09-04 | `17c843d` | **HesterGPT Integration** into main page | main |
| 09-04 | `26fed18`, `e2277ea` | **"Added Fonts"**; **"Ndot + Support Fix"** | fonts |
| 09-09 | — | "Create SF-Pro.ttf" | fonts |
| 09-26 | `33e8ecd` | **Update v168.7.2** — 45 files: Docs app rewrite, terminal Math tool, team photos, careers, maps/updates pages, main page | release |
| 10-01 | `8d5aef3` | "Described Updates" — release notes written | updates |
| 10-16 | `4c22763` | **Spotlight Integration** | main |
| 10-21 | `f2adb02` | "Fixed CSS" (auth, +376/−374) | auth |
| 10-30 | `22a368f` | **"Auth restore"** (+387/−391; removed `auth/index.copy.php`) | auth |
| 11-08 | `7c6692a` | **Added Crypto Marquee** (AEVT/AEVD logos uploaded to the AEVT repo the same day) | main |
| 11-09 | `3cf03ea`, `b2c0b67` | **Apps Footer Panel**; **App Launcher Updated** | main |
| 11-15 | `d139ada`, `9d87ed2` | **Services page + PragueFlow** | services |
| 11-26 | `962db8b` | QR updated (7 files) | terminal |
| 12-05 | `8a91145`, `f3c94fe` | **Mic access** and **HesterGPT versions** (select) | hester |
| 12-24 | `3b4c829` | **3D logo for Downloads** | downloads |
| 12-25 | `dbfa58d`, `c9be035` | **Avrora image generator** added and announced on main | hester, main |

### 2025
| Date | Commit | Event |
|---|---|---|
| 02-01 | `4fca2f1`, `95701ee` | **Terminal "WYBMV?" (Valentine) app** |
| 02-20 | `c1cf39b` | **HesterGPT v1.6** |
| 02-25 | `003e039`, `2af63be`, `b8d5f22` | Hester text animation; **Hester ad card** on main; **menu button "Ask HesterGPT"** |
| 03-05 | `2e95865` | **Design Store Concept** (14 files) |
| 03-06 | `50075a6` | Store filter functionality |
| 03-26 | `749fb7d`, `8b57307` | **ΛWDC25 event page**; link on main |
| 03-30 | `7681d22` | "Test App" (scheduler) in the hosting area |
| 05-06/07 | `c8956b5`, `f0eb5dd`, `22f786b` | **HesterGPT v1.7**; **1o interface** added; a commit then "downgrades to HesterGPT 1.6 stable" (`22f786b`); at `HEAD` the select shows v1.7 selected and 1o disabled |
| 06-04 → 06-20 | `61fb20c` … (22 commits) | **QIRIM.CZ & SCOS.PRAHA event page** (film evening, 28 June) |
| 06-05 | `269aba8` | **Preloader animation for the store** |
| 06-12 | `49b2a7a`, `689f973` | **HesterGPT PWA support** (17 files) |
| 06-30 → 07-02 | `55d1d20`, `949347f` | Careers rework/bug fixes (replaced `index.original.php`) |

### 2026
| Date | Commit | Event |
|---|---|---|
| 02-17 | `2ddaba1` | **"Update Patch"** (14 files): `page/history` and `page/services` index created, wallpaper "create" tool, main/downloads tweaks |
| 02-17/18 | `cbdfb81`, `839e6d3` | **GTIN extractor** terminal tool |
| 03-28, 04-24, 07-30 | README commits | New title/description; project overview; final README |

## 4. Evolution threads

- **Main page:** import (03-10) → PWA modal (03-14) → social links (03-15) → menu redesign (03-25) → notifications (03-27) → Investor/community links (05–08) → Hester integration (09-04) → v168.7.2 (09-26) → Spotlight (10-16) → crypto marquee (11-08) → launcher footer + update (11-09) → Avrora announcement (12-25) → Hester ad + menu button (02-25) → ΛWDC25 (03-26) → patches (06-07, 07-06, 2026-02-17). **50 commits — the most-edited area.**
- **HesterGPT (45 commits):** page created (2024-08-29) → main-page integration (09-04) → versions/mic (12-05) → Avrora (12-25) → v1.6 (2025-02-20) → v1.7 and 1o (05-06/07) → PWA (06-12). Origin: a 2022 Python Telegram bot (`Hester` repository).
- **Launcher/Spotlight/Ticker (the "ecosystem entry point"):** all added **after** HesterGPT and the v168.7.2 release, 2024-10 → 11: Spotlight → marquee → launcher footer. The owner's release notes frame the "New User Panel" as the home for apps "like ΛΞV Maps and more".
- **Fonts/branding:** logo changed 2024-03-14; fonts added 2024-09-04 (Ndot + "Support Fix"), SF-Pro 09-09; "Custom Fonts Support for CVX Arch" listed as shipped in v168.7.2 (09-26); 3D logo 2024-12-24; seasonal icons exist from import.
- **Public pages:** careers (12 commits), contact (10), downloads (13), IR (2), services (9), updates (2), history (1, 2026).
- **Terminal (`_api`, 18 commits):** QR (2024-08), store concept (08), Math tool (09-26), valentine (2025-02), GTIN (2026-02).
- **Concepts:** design store (2025-03/06), Material (2024-05), Universal (2024-03), Materials/Liquid-Glass lineage **(inferred)**.
- **Events:** ΛWDC25 (2025-03), QIRIM.CZ (2025-06) — community-driven pages made quickly and kept.
- **Auth:** only 4 commits, incl. "Auth restore" (2024-10-30) — a rollback/recovery.

## 5. Relationships between applications (from code and notes)

```
page/main (entry point)
 ├─ launcher ─► Account(home/auth,profile) · Space(timeline) · Messenger · Videos · Maps · Finance(roadmap)
 │              HesterGPT(page/hester ─► avrora, 1o) · _API(terminal) · Docs · Journal(page/updates)
 │              Contacts · Jobs · Downloads · Investor Relations · QirimTalk(external) · social
 ├─ spotlight ─► same app list (data.json) + web search
 ├─ hire form / Intergram ─► owner's Telegram
 └─ promo cards ─► 4ukraine(terminal Page) · careers · Hester
home/ (registered users): auth ─► users(access tiers, balance, bank, birth…) ─► profile ─► timeline ─► studio(post/article/profile)
                            messenger(tl_messages) · videos · wallet(QR) · store · api_keys(editProfile "API")
home/_api: UI terminal (Pages · Tools · Admin · Games) · Docs · site error pages (0x codes)
home/_uploads/user_*: per-user hosting (web/, web_hidden/)
cron.php: birthdays (users.birth), DB dump, sitemap crawl ─► notify() ─► Telegram
```

## 6. Unfinished / abandoned / experimental (as the owner's own records show)

- **Announced, not delivered** (release notes, "available from November"): Shortcuts modal, API store, new login button, services pricing page, cyberpunk portfolio-cards pagination (the Works slider exists).
- **Unfinished:** Maps ("Layers"), history/legal pages, Docs content (template text), i18n (`lang.en.php` empty), music/blog/re:search/settings (`2be_created`), `page/main` Functional-key box (never wired; present since import).
- **Owner-superseded:** `2be_deleted/*` (Pyper, WebOS admin, dashboard concept, older messenger/studio).
- **Experiments:** Material, Universal, Design Store, 3D room, fish, Games, GPU.js matrix test, NSFW detector, face-API demo.
- **Ideas recorded only in notes:** Telegram bot commands, musician studio site, web player, email subscription, hosting with mail, "my own font", notification system, group chat rooms, "Cloud".

## 7. Open chronology questions (`requires owner review`)

Platform development before 2024-03-10; what "Auth restore" restored from; the reason for the 2025-04 and 2025-08 → 2026-01 gaps; when the domain `aliev.io` stopped being registered (it is **not registered** as of 2026-10-02 per the `.io` registry WHOIS, and neither is `qirimtalk.com` per Verisign).
