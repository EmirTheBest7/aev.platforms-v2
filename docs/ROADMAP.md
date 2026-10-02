# Revival roadmap — classification of every system

**These labels are scheduling and risk classifications. None is a deletion decision.** Every system stays part of the documented scope of ALIEV.IO. The owner sets the order beyond the first target.

| Label | Meaning |
|---|---|
| **CURRENT** | Being revived now |
| **FUTURE** | Planned revival; needs the account foundation or an owner design |
| **EXPERIMENTAL** | Deliberate experiment/prototype; preserved and documented, revived only if the owner wants it as a product |
| **ARCHIVAL** | Superseded or historical reference; preserved as knowledge, kept out of the live path |
| **SECURITY** | The legacy implementation is unsafe; the feature is rebuilt (never reproduced as-is). Combines with any label above |

## Priority order (agreed)

1. Historical archaeology and documentation — **done**
2. Architecture and security foundation — **done for the public layer; extending with what the main page needs (market data, Hester proxy, lead endpoints, CSP exceptions)**
3. Design-system and brand extraction — **done; refined while porting**
4. Revive `page/main`
5. Repair every main-page feature
6. Visual regression and browser testing
7. Staged revival of the wider ecosystem (below)

## Systems

| System | Evidence of intent | Class | Depends on | Notes |
|---|---|---|---|---|
| **`page/main` — ecosystem entry point** | 50 commits, owner's centre of gravity | **CURRENT** + SECURITY (form, ticker proxy, Hester key) | foundation | First revival target; `MAIN-PAGE.md` |
| Crypto ticker | `7c6692a` 2024-11 | **CURRENT** (repair: provider abstraction) | server price proxy | AEVT: honest "unavailable" |
| Intergram | support chat | **CURRENT** (keep, harden) | — | `disableLoadmill`, id from env |
| HesterGPT (`/hester`, panel, menu, cards) | 45 commits | **CURRENT** + SECURITY (key) | server proxy | Voice input, versions, Avrora UI kept |
| Spotlight, launcher, profile panel, settings, notifications, PWA, globe, widgets | main-page features | **CURRENT** | — | |
| `page/contact` (map, fish, form) | `eeb7a29` | **CURRENT-adjacent** (next page after main) + SECURITY (form) | Notifier | v2 contact flow already exists |
| `page/downloads`, `page/updates` (Journal), `page/investor-relations`, `page/history`, `page/legal` | menu/launcher targets | FUTURE (small, static) | shell layout | Porting these removes menu "pending" toasts |
| `page/careers` (list/desc/team) | search, ACS benefits | FUTURE + **SECURITY** (SQL injection, error leak) | jobs data source | Needs the `jobs` data (not in repo) |
| `page/services` + PragueFlow | `d139ada` | FUTURE (INCOMPLETE: pricing page announced) | content from owner | |
| `page/hester/avrora`, `1o` | Dec 2024 / May 2025 | FUTURE (Avrora backend unclear) | Hester proxy | |
| `page/maps` | "New Maps App" | FUTURE (INCOMPLETE) | owner scope | |
| Events: `DC25`, `qirimcz` | 2025-03, 2025-06 | ARCHIVAL page (preserved as event record) | — | Past events |
| `page/design_store` | `2e95865` | EXPERIMENTAL | accounts | Steam-style store concept |
| `page/material`, `page/universal` | 2024-03/05 | EXPERIMENTAL (Material / Liquid-Glass lineage, inferred) | — | Owner to say how they relate to "Liquid Glass" |
| `home/auth` + account | gate to the platform | FUTURE + **SECURITY** (SQLi, MD5, CSRF) | `core/auth` (exists) | First of the platform apps |
| `home/profile`, tiers/badges | identity | FUTURE + SECURITY | accounts | `@nick` routes |
| `home/timeline` (Space) | social core | FUTURE + SECURITY | accounts, profile | Fix "likes only first 3" bug |
| `home/studio` (post, article, profile onboarding, API keys) | creation tools | FUTURE + SECURITY (uploads) | Space | NSFW client-side detector (EXPERIMENTAL) |
| `home/messenger` | communication | FUTURE + SECURITY | accounts | Pyper (older) is ARCHIVAL reference |
| `home/videos` | media | EXPERIMENTAL | accounts | Custom player kept |
| `home/wallet` / Finance, AEVT/AEVD | token economy | FUTURE (needs owner design) / EXPERIMENTAL | accounts, owner design | `users.balance`, QR payments |
| `home/store` | marketplace | EXPERIMENTAL | accounts | |
| `home/_api/UI` terminal (Pages, Tools, Admin, Games) | developer playground | EXPERIMENTAL → FUTURE | shell | Games/tools licences audit first |
| `home/_api/Docs` | CVX docs | FUTURE (content), see `aliev-docs` | owner | |
| API keys / `api/v2` | developer platform | FUTURE + SECURITY | accounts | |
| `home/_uploads` user hosting | "Mega Hosting" idea | FUTURE (concept) + SECURITY; **data is private, never committed** | accounts, storage design | |
| `home/2be_created/*` (music, blog, re:search, settings, pages) | planned | EXPERIMENTAL / FUTURE | accounts | Spotify-clone sample audio needs licence check |
| `home/2be_deleted/*` | owner-superseded | ARCHIVAL (one file also SECURITY: secret) | — | Kept; owner may deprecate later |
| Shortcuts modal, API store, services pricing, new login button | announced 2024-09 | FUTURE (INCOMPLETE; Shortcuts is part of main-page settings) | — | |
| Cron features (birthdays for consenting users, backups, sitemap) | `cron.php` | FUTURE + SECURITY/PII | accounts | Private-person list is never carried |
| Custom fonts (Ndot, DotlineBold, SF Pro) | v168.7.2 "Custom Fonts Support" | licensing decision | — | `LICENSES.md` |
| Companion repos (QirimTalk, Dreamers, aliev-docs, Hester, AEVT, PIXELITE) | siblings | outside this repo; links preserved | — | |

## Staged rollout (proposal for phase 7)

1. Static public pages (downloads, journal, investor-relations, history, legal, DC25/QIRIM.CZ archive pages, contact map+fish).
2. Accounts on `core/auth` (+ profile card, badges).
3. Space + profile + Studio.
4. Messenger.
5. Careers (needs data), services.
6. Terminal + Docs, Hester extras (Avrora/1o).
7. Wallet/Finance/Store (needs owner design), Videos, music/search experiments.
8. User hosting and API platform.
