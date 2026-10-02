# ALIEV.IO — project history and vision (technical reconstruction)

Status: **understanding phase.** This document reconstructs what ALIEV.IO was trying to be from evidence in the repository, its git history and the owner's own public texts. It is not marketing copy. Nothing in the legacy tree is deleted or reclassified as "unnecessary" here; where the repository cannot establish a purpose the entry says **`Purpose unclear — requires owner review`**.

Evidence tags used throughout:

| Tag | Source |
|---|---|
| `[git]` | commit history of `EmirTheBest7/aev.platforms` (211 commits, 2024-03-10 → 2026-07-30), read from a scratch clone |
| `[code]` | files in `aev.platforms-master` |
| `[owner]` | the owner's own written material: `README.md`, release notes (`page/updates`), planning notes (`antitup/plan.md`, `hidden.md`, `old.md`), `home/_api/Docs/mds/architecture.html`, the GitHub repository descriptions and profile README |
| `[inference]` | a reconstruction that the evidence supports but does not state |

---

## 1. What ALIEV.IO was trying to become

The owner's own one-line statements, in chronological order:

- 2024 README: *"One digital platform to rule them all. Build. Automate. Deploy. Scale."* — *"ALIEV.IO is an open platform for building modern digital systems… a single foundation that grows with your ideas."* `[owner]`
- 2024 release notes (v2.18): *"ΛΞV is more than a platform; it's a dynamic ecosystem fueled by your passion and curiosity."* `[owner]`
- 2026 GitHub description of the repository: *"ALIEV.IO – Full-Stack Enterprise Solutions Platform / A high-performance IT business ecosystem built for scalability and market expansion. Engineered with a focus on innovative 'Liquid Glass' UI/UX, robust database security architectures, and integrated social-media-driven lead generation."* `[owner]`
- 2026 profile README: *"ALIEV.IO | v2 Development — A full-stack business platform combining web development, UI/UX, application architecture, APIs, authentication, and infrastructure."* `[owner]`

Read together, the intent was never "an agency brochure". It was **an ecosystem entry point**: a polished public front door (the studio/agency face and lead generation) leading into a registered-user platform (social, messaging, media, wallet, store, creation tools), a developer layer (terminal, tools, API keys, docs, AI assistant) and hosting for users' own sites — all branded ΛΞV / "Λ L I Ξ V Platforms", under one architecture the owner calls **CVX Arch**. `[owner][inference]`

The `v2` repository's own README (this repository, written by the owner) lists the same modules as the target architecture: `apps/{social,messenger,forum,store,wallet,studio,terminal}`, `core/{auth,users,database,security,routing,permissions,…}`, `api/{v2,internal,terminal}`, `website/{home,careers,contact,legal,investors,downloads}`. That is the legacy platform re-planned module-for-module — so **v2 is the same ecosystem rebuilt, not a smaller site**. `[owner]`

## 2. The owner's architecture statement (CVX Arch 4.3.1)

`home/_api/Docs/mds/architecture.html` `[owner]`:

```
/_assets/   css/core.css, js/core.js                    shared front-end
/_inc/      cron.php (cron-based files, backups…)       background work
            functions.php (main system functions: connections, auth, etc.)
/home/      # Dynamic pages | Registered users          the platform
/page/      # Static pages                              the public site
```

This is the key to reading the tree: **`page/` is the public face; `home/` is the product behind login.** `page/main` links into `home/*` through the launcher because that is the point of the page.

## 3. How the project evolved

Git history (`[git]`) covers the **public site only**; the first commit (2024-03-10, "Initial commit" then "Project Commit") imported the already-existing platform in one piece, so the development of `home/*` before March 2024 is **not** visible in git (`Purpose/chronology unclear — requires owner review` for that period).

| Period | What git shows |
|---|---|
| **2024-03** (29 commits) | Platform imported. Main page gets the **PWA modal** (03-14), social links (03-15), **"Main Menu Redesign"** (03-25), **"AEV Notifications [Activation]"** (03-27). |
| **2024-05** (24) | Careers page redesign; **"Contact Map + Fish Added"** (05-21); Investor Relations (05-26); Downloads table + `ACS_System.pdf` (05-25/29); `page/material` experiment. |
| **2024-08/09** | `cron.php`; **HesterGPT page created** (08-29) and **"HesterGPT Integration"** into the main page (09-04); QR generator and **"Store Concept"** in the terminal; **"Ndot + Support Fix"** and **"Added Fonts"** (09-04) / SF-Pro (09-09); "Update v168.7.2" (09-26). |
| **2024-10/11** | **"Spotlight Integration"** (10-16); "Auth restore" (10-30); **"Added Crypto Marquee"** (11-08); **"App Launcher Updated"** and **"Apps Footer Panel Added"** (11-09); Services page + PragueFlow (11-15). |
| **2024-12** | HesterGPT: mic access, versions, **Avrora image generator** (12-25); 3D logo for Downloads. |
| **2025-02/03** | Terminal "WYBMV?" valentine app; HesterGPT v1.6, text animation, ad card and menu button (02-25); **Design Store concept** (03-05); **ΛWDC25** event page (03-26). |
| **2025-05/07** | HesterGPT v1.7, "1o" interface (05-06/07), PWA support for Hester (06-12); QIRIM.CZ event page (06-04…06-20); Careers fixes. |
| **2026-02/07** | "Update Patch" (02-17) touching main/services/downloads; `page/history` created; GTIN extractor tool; README rewrite (07-30). |

Active years: 2024 (intense), 2025 (HesterGPT, events, store concept), 2026 (maintenance, README, the v2 plan).

## 4. Major functional areas

### 4.1 Public layer — `page/*` (static pages; the brand and lead generation)
`main` (landing/ecosystem entry), `contact` (offices map + animated fish + Telegram-notified form), `careers` (searchable job list, job detail, team page; DB table `jobs`), `downloads` (logos, wallpapers, docs — brand kit and the "ACS System" PDF), `updates` (release notes + bug/suggestion page), `investor-relations` (shareholder page with a live Discord **News** tab), `services` (+ `pragueflow` client project), `hester` (+ `avrora`, `1o`) (AI assistant), `design_store` (store concept), `DC25` and `qirimcz` (event pages), `maps`, `history`, `legal`, `material`, `universal`, `empty`.

### 4.2 Platform layer — `home/*` (dynamic pages for registered users)
- **Account** (`auth`, `studio/editProfile`): register/login/reset; user tiers via `users.access` rendered as badges (Verified, Developer, Premium, Family, Friend, Musician, Crypto); onboarding wizard with a start-up chime; API-token management → table `api_keys`.
- **Space** (`timeline`, `profile`): posts (public/private), comments, replies, likes, reactions, friendships, "Suggestions for you", search, **dailies** (stories); `@nickname` profile URLs.
- **Studio** (`studio`): create post (with client-side **NSFW image detection**, nsfwjs/TensorFlow models), write article (DevExtreme editor), edit profile.
- **Messenger** (`messenger`; older `Pyper` in `2be_deleted`): 1:1 chat on `tl_messages`.
- **Videos** (`videos`, `aev-video.js`): channel/watch UI with a custom player — prototype.
- **Wallet / Finance** (`wallet`, `users.balance`, `users.bank`, `create_qr()`): QR-based payments UI; the ticker's AEVT/AEVD and the `AEVT` repository (a fungible token on TON "for trading and purchasing goods and services on aliev.io") show the intended economy.
- **Store** (`store`, `page/design_store`, terminal "Store Concept"): marketplace concept.

### 4.3 Developer layer — `home/_api/*`
`UI` = the **terminal / "CMD" launcher** (Dashboard, Search, Share, Devices, Settings) hosting **Pages** (4ukraine facts page with video, Antitup planning page, Docs, donate, GTIN extractor, store concept, Terminal v2, Valentine app), **Tools** (crypto prices, currency exchange calculator, domain availability, live code editor, ΛΞV Math, QR generator), **Admin** (birthday countdown, error page, resume) and **Games** (11 incl. Pac-Man, Dino, Doom, Snake). `Docs` = the **CVX docs app** ("BETA v4.3.1", Home / Learn / API, ⌘K search). Its error pages (`?0x=404`) are also the site-wide `ErrorDocument`s. `[code][owner]`

### 4.4 Hosting layer — `home/_uploads/user_<token>/`
Per-user web space: `web/` (public sites/projects), `web_hidden/` (private apps, admin, sqlite), `profile/`, `conf.json`. The owner's notes: *"Update to Mega Hosting → register new mail for users, EA Cloud access unlimited storage"*, *"Musician Studio will have his own landing page with gear, pages and some informations"*. `[owner]` This is the "platform hosts your site" idea. `[inference]` The only existing user folder is the owner's own and contains work projects and databases (private material — see `SECURITY.md`).

### 4.5 Planned but unbuilt — `home/2be_created/`
Owner-named "to be created": **music** (a Spotify-clone with songs/albums/artists/playlists tables), **blog**, **re:search** (own search engine, `search_engine` table + SQL), **settings** (dashboard UI) and `4_pages/{downloads,hiring,projects,team}`. `[code]`

### 4.6 Owner-marked for eventual removal — `home/2be_deleted/`
Owner-named "to be deleted": **Pyper** (an earlier chat app), **admin** ("WebOS 10"), **dashboard** concept, **messenger** (earlier version), **studio**, **dailies**, **mail**, **tesco** (scheduler), **backups** (Instagram-style profile layout), **zuck** (zuck.js stories library). The owner's labelling is evidence they were superseded, **not** a licence for us to delete them; they stay (see `HISTORICAL-FEATURES.md`, "POSSIBLE DEPRECATION"). One contains a second bot token (`SECRET_EXPOSURE_03`), which is a security matter, handled separately from the feature.

## 5. How the launcher fits the product

`page/main/data.json` lists 20 entries (Account, Space, Maps, Messenger, Videos, HesterGPT Lite/Pro, Finance, _API, Docs, Journal, Contacts, Jobs, Downloads, Investor Relations, Darknet, QirimTalk, Instagram, Telegram, Home) and the visible launcher panel shows 11 tiles under **"Web Apps"** with a footer (`aliev.io`, `All Apps`). Release notes call it the **"New User Panel — features apps like ΛΞV Maps and more, designed to provide a cohesive … experience"** `[owner]`. It is the **ecosystem's app switcher**, modelled on the familiar "Google apps" grid: every tile is one product of the platform, the spotlight (⌘/Ctrl+J, added 2024-10-16) searches the same list, and the profile badge beside it is the account entry. Tiles whose products are unfinished are *roadmap markers*, not mistakes: Finance, Maps, Darknet and "HesterGPT Pro" were announced ahead of their implementation. Sibling projects appear as tiles too — **QirimTalk** (community forum, separate repo) and Instagram/Telegram.

## 6. Why the main page contains what it contains

| Main-page feature | Evidence of purpose |
|---|---|
| Loading screen (logo + bar) | Brand moment; removed after ~2 s by `core.js`. `[code]` |
| Rotated wordmark rail, menu panels, accordion (Explore/Learn/Build) | "Main Menu Redesign" 2024-03-25; "Explore/Learn/Build" mirrors developer-platform nav (Services · Portfolio · Ecosystem / Learn / Quickstart · Documentation · CLI). `[git][code]` |
| 5-section scroller (Home, Works, About, Contact, Hire us) | Agency storytelling + lead generation; "Portfolio Works Cards Pagination … cyberpunk-themed cards" (release notes). `[owner]` |
| Hire/work-request form → Telegram | Lead generation: *"integrated social-media-driven lead generation"*. `[owner]` |
| Notifications (toast stack) | "AEV Notifications [Activation]" 2024-03-27; onboarding/announcement channel (welcome, community, innovations). Roadmap: *"PHP & JS Notification system, browser && inside window"*. `[git][owner]` |
| Spotlight | "Spotlight Integration" 2024-10-16: app search + web search + theme/Telegram/Docs actions. `[git][code]` |
| App launcher + profile badge | §5. |
| Settings (language, PWA, shortcuts, fullscreen, appearance, cookies, "Functional key") | "Redesigned Settings" and **"Custom Fonts Support for CVX Arch … your settings and widgets menu on the main page"** `[owner]`. The Ndot display font is used exactly there (clock readout, Settings heading). The **Shortcuts modal** was announced *"available from November"* — the Settings entry is its placeholder. The "Functional key" box with Uppercase/Lowercase/Numbers/Symbols options reads like a key/password generator; its handler is **not present** (`Purpose unclear — requires owner review`). |
| Widgets (clock, calculator) | "widgets menu" of the same update; small mini-apps (also "New Mini App for Math Solutions"). `[owner]` |
| Crypto ticker | "Added Crypto Marquee" 2024-11-08. Rows: BTC ETH **AEVT** **AEVD** USDT SOL TON DOT SUI APT. AEVT = the owner's TON token; AEVD priced from USDC. The `updates` page footer shows an earlier static ticker (`🪙 $6,43 | 💰 $67.347 | 💲 22,9`). `[git][owner]` |
| 3D globe | Hero visual of a *global* platform; also on the 3D-room "About" animation. Texture hot-linked from imgur. `[code]` |
| Promo cards (#StopTheWar, We Are Hiring, HesterGPT v1.7) | Owner's causes/products surfaced on the front door; the Ukraine card leads to the 4ukraine page. `[code]` |
| HesterGPT box + menu button + "Ask HesterGPT" | "HesterGPT Integration" 2024-09-04; "HesterGPT for Developers … accessible with just a button click. Use it with your API key". `[git][owner]` |
| Intergram chat | Visitor-support channel into the owner's Telegram ("Contact Support"). `[code]` |
| PWA modal | 2024-03-14; v2.18 "Progressive Web App: Magic in Your Pocket". `[git][owner]` |
| Seasonal favicons | Playful identity: white tile in spring/autumn, flag-bordered tile in summer/winter. `[code]` |
| Title swap on tab blur | "ΛΞV | Coding is art." / "Digital Studio." `[code]` |

## 7. AI, communication, finance, maps, media in the vision

- **AI** — HesterGPT (Telegram bot 2022 → web app 2024 → v1.7 2025; voice input; image generator **Avrora**; "1o" interface; PWA; bring-your-own-key for developers) is the platform's assistant; ad cards and the menu button keep it one click from everywhere. `[git][owner]`
- **Communication** — Space + Messenger + Discord/Telegram broadcast bridges (v2.18: *"connected our channels, so both sides can speak"*) + Intergram support chat + notifications. `[owner]`
- **Finance** — `balance`/`bank` fields, QR payments, AEVT on TON, ACS "ΛΞV Credit System" (job-benefit rewards, public PDF), Investor Relations; sibling `ArgenPayLib` (EPC QR payments, "Fintech-Dark" admin UI). `[code][owner]`
- **Maps** — "New Maps App … ✅" in release notes; `page/maps` is a stub with a "Layers" control — **INCOMPLETE**. The contact page has an interactive map of offices. `[owner][code]`
- **Media** — Videos with custom player, music streaming (Spotify-clone prototype), stories ("dailies"). `[code]`
- **Community/events** — Discord, Telegram, QirimTalk, event pages (ΛWDC25 Prague 30–31 May; QIRIM.CZ film evening 28 Jun 2025), PragueFlow. `[code]`

## 8. What appears unfinished, experimental, abandoned

- **Unfinished (announced or started):** Shortcuts modal, services page with pricing, API store, new login button, Maps app, Finance, Darknet, HesterGPT Pro, i18n (`_inc/lang/lang.en.php` is empty while Settings offers EN/CZ/UA/RU/Crimean Tatar), history and legal pages (stubs), downloads rows Whitepaper/AAEV_Keynote (shown with Action `-`), `home/2be_created/*`, Messenger polish, "Cloud".
- **Experimental / prototypes built from templates:** wallet UI (a "CareClues" clinic-wallet template skinned for ΛΞV), store UI (iMac/iPhone/iPad layout), videos/channel (placeholder titles), `material` (Polymer tabs demo), `universal` (lorem-ipsum template), `_assets/cdn/framework`, `core-gpu.js` (GPU.js matrix test, unreferenced), Games and CodePen-derived tools.
- **Owner-marked superseded:** everything under `home/2be_deleted/`.
- **Duplicates:** `_assets/js/origElectron.js` (the pixel-grid "electron" animation also lives inside `core.js`).
- **Event pages whose dates have passed:** DC25, QIRIM.CZ — historical record.

## 9. What remains relevant today

Everything above is treated as relevant until the owner says otherwise. The owner's current activity corroborates it: v2 plan (`apps/*`), **QirimTalk** and **Dreamers v2** as active social platforms, **aliev-docs** (a PHP/Markdown docs generator "with ALIEV.IO" — the natural destination of the Docs tile), **Hester**, **AEVT**, and **PIXELITE** (a separate agency brand, Pixelite.cz, whose `CLAUDE.md` also says "the original landing page is the visual source of truth").

## 10. Open questions for the owner (`Purpose unclear — requires owner review`)

1. "Functional key" settings box: intended behaviour (key generator? licence check?).
2. `AEVD`: definition and price source (ticker uses USDC); `AEVT` price source (ticker shows `0.00`).
3. `DotlineBold.ttf`: purpose (candidate for the "My own font version" idea?) and licence; `SF-Pro.ttf` was committed on purpose ("Create SF-Pro.ttf", 2024-09-09) but is not referenced — intended use?
4. `start-up.mp3`: confirmed used by the profile-setup finale; should it also play at the main-page preloader?
5. `core-gpu.js`, `origElectron.js`, `Darknet`, `HesterGPT Pro`, `page/maps` "Layers": intended scope.
6. Which `home/*` apps to bring back first, and in what order (see `MIGRATION.md`).
7. Whether a "Liquid Glass" visual layer (named in the repository description) is intended for the public site, and how it relates to the dark UI that actually shipped.
8. Pre-March-2024 history of the platform (not in git).
