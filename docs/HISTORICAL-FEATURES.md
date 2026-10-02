# What we had — historical feature inventory

Default answer for every row is **PRESERVE**. Nothing here is slated for deletion. Rows tagged **POSSIBLE DEPRECATION** are only *flagged for the owner's decision* with the reason; they stay in the legacy tree (`aev.platforms-master`, the historical reference) and are not blocked from being ported.

**Class** (in "Current state"): `BROKEN` intended feature exists but doesn't work · `INCOMPLETE` started/announced, unfinished · `LEGACY IMPL.` works conceptually, code should be rewritten · `EXPERIMENTAL` prototype/experiment · `OBSOLETE` an external dependency no longer exists (alternatives investigated first) · `DUPLICATE` genuinely exists elsewhere · `SECURITY RISK` implementation unsafe — **fix the implementation, keep the feature**.

Evidence and reasoning behind "why it appears to exist" are in `PROJECT-VISION.md` (tags `[git] [code] [owner] [inference]`). "Repair"/"Modernize" are the work to do *when the feature is brought into v2*; "Preserve" says what must survive unchanged (look, behaviour, concept).

---

## A. Main landing page — `page/main` (the ecosystem entry point)

| Feature | What it did | Why it appears to exist | Current state | Preserve | Repair | Modernize |
|---|---|---|---|---|---|---|
| Loading screen | Full-screen black, wordmark + animated blue bar, fades out ~2 s after DOM ready | Brand moment `[code]` | Works. `LEGACY IMPL.` (jQuery fade; can strand if JS fails) | Look, timing | Non-blocking fail-safe | Native JS |
| Rail navigation + mobile top bar | 60 px black rail, hamburger, rotated wordmark, quick social icons; becomes top bar on phones | "Main Menu Redesign" 2024-03 `[git]` | Works. `LEGACY IMPL.` (`<a href="#">` toggle) | All of it | Keyboard/aria | Native JS |
| Menu panels (Menu → Settings → Widgets) | Slide-switching panels inside the menu (`settings_in()/register()/login()`) | "Redesigned Settings", widgets menu `[owner]` | Works. `LEGACY IMPL.` (inline onclick, global functions) | Slide animation | — | Event delegation |
| Explore / Learn / Build accordion | Services·Portfolio·Ecosystem, Learn, Quickstart·Documentation·CLI | Developer-platform style nav `[inference]` | `INCOMPLETE`: Services/Portfolio/Ecosystem → "Coming soon" page; Learn contains literal `{{}}` placeholder | Structure + icons | Point to real destinations (in-page sections, journal, docs) | Accessible accordion |
| Section scroller + side-nav (01–05) | Wheel/keys/swipe move Home→Works→About→Contact→Hire us with 3D-ish transitions; CTA jumps to last | Agency story + lead capture `[inference]` | Works. `LEGACY IMPL.` (jQuery + bundled Hammer.js) | Behaviour + transitions | `preventDefault` on passive wheel; focus/keyboard | Pointer Events, no deps |
| Hero + "Hire Us" CTA | H1 "Build your digital success with us." + slab button | Front-door message | Works | Exactly | — | — |
| Crypto ticker (marquee) | BTC ETH AEVT AEVD USDT SOL TON DOT SUI APT with logos | "Added Crypto Marquee" 2024-11 `[git]`; shows the owner's AEVT/AEVD tokens next to majors | **`OBSOLETE`/`BROKEN`**: CryptoCompare now returns **401** without an API key, so every price stays `0.00`. AEVT price was never wired (static `0.00`). | Look, position, refresh role | Replace data source (see MAIN-PAGE.md); graceful failure | Server-side cached price proxy; no per-visitor third-party call |
| 3D globe | three.js r128 dot-matrix planet, orbiting arcs, drag-to-rotate | Hero visual; "global ecosystem" | Works while imgur texture and CDN exist. `LEGACY IMPL.` (no resize/DPR/dispose/WebGL fallback) | Entire look | WebGL fallback, resize, texture hosting | Self-host three + texture; cap DPR; pause off-screen |
| Promo cards (#StopTheWar · We Are Hiring · HesterGPT v1.7) | Three image cards with outlined action buttons | Owner's causes/products on the front door | Works; button inside `<a>` navigates twice. Destinations moved | Cards + copy | Valid destinations, single navigation | — |
| Works slider ("Selected work") | 3-up carousel: HesterGPT, ΛΞV Community, Avrora [SOON], Dreamers, Cerebro Blockchain, Cortex Browser, EROS | "Portfolio Works Cards Pagination" `[owner]`; portfolio of the ecosystem's own products | Works. 5 of 7 cards link `#0` — projects without pages | All 7 cards and copy | Card links to real project URLs when owner supplies them | Native JS |
| About + CSS 3D room | Interactive isometric room that tilts with the pointer; Our Team / Philosophy / History hover panels | About storytelling | Works (images hot-linked from githack). Philosophy/History/Team targets: team = careers/team; history = stub page; philosophy = none | Whole thing | Self-host images; wire destinations | — |
| Contact section | Location, e-mail, Telegram, Instagram, profile link | Reach-out | Works; profile link `@emirthebest7` targets the unported profile system | Layout | Profile link once Space returns | — |
| Hire / work-request form | Service checkboxes + name + e-mail → Telegram message | Lead generation `[owner]` | `SECURITY RISK`: unvalidated POST → Telegram, non-unique order number, no CSRF/rate limit | Fields, look, flow | Validate, CSRF, rate-limit, server ID | Notifier service |
| Notifications (toast stack) | Welcome/Collaboration/Innovations/Explore cards, glow follows pointer | "AEV Notifications [Activation]" 2024-03 `[git]` | Works. Logs to console on every mouse move | Look, copy, timing | Remove debug logging, aria-live | — |
| Spotlight (Ctrl/⌘+J) | Popover search: apps, theme toggle, Telegram, Docs, web search | "Spotlight Integration" 2024-10 `[git]` | `LEGACY IMPL.`/`SECURITY RISK`: `keyCode` shortcuts, `innerHTML`, unencoded `q=`; fetches `data.json` from production URL | Look, shortcut, actions | XSS-safe, URL-encoding, arrow/Enter/Esc | Native, popover fallback |
| App launcher (Web Apps grid) | 11 tiles + footer (aliev.io / All Apps) | Ecosystem app switcher (§5 of vision) | Many destinations don't exist; footer buttons dead; all-same icons in `data.json` | **All tiles**, icons, labels | Destinations; real footer actions | Data-driven |
| Profile/account panel | Avatar badge → card "Hi, User!" with Sign In / Dashboard / Logout | Entry to the account system | `INCOMPLETE`: buttons have no handlers | Look, states | Wire to `core/auth` when enabled | — |
| Settings panel | Functional key, Language, PWA, Shortcuts, Fullscreen, Appearance, Generator options, Cookies | "Redesigned Settings", "Shortcuts Modal (announced)" `[owner]` | Mixed. **Working:** Fullscreen, PWA modal link. **Dead:** Shortcuts (`onclick="#"` throws), Language (no effect), Check button + generator checkboxes (no handler). Disabled by design: Dark Mode, Animations, Functional cookies | Design + every control | Implement each intended behaviour (see MAIN-PAGE.md) | Native dropdown with search |
| Widgets: Clock, Calculator | Iframe mini-apps in the menu; date block in display font | widgets menu `[owner]` | Clock works. Calculator uses `eval()` — `SECURITY RISK` | Both | Replace `eval` with a safe parser | — |
| HesterGPT floating box | Button opens a chat panel (iframe of `/page/hester`) | "HesterGPT Integration" `[git]` | Opens; the embedded app calls Google's Gemini with a **hardcoded API key** (`SECRET_EXPOSURE_07`) | Look, flow | Server-side proxy, key in env | Own endpoint, rate limit |
| Intergram chat | Floating "Contact Support" chat → owner's Telegram | Visitor support | Works while intergram.xyz is up. Bundles a hidden **affiliate tracker iframe** (opt-out exists) | Look, role | Self-hosted pinned widget, `disableLoadmill`, chat id from env | — |
| PWA install modal | Share → Add to Home Screen instructions | 2024-03 `[git]` | Opens via `:target`. No install button, no SW | UI | Real install prompt where supported | Manifest + icons |
| Social quick-links | Instagram, YouTube, Telegram | Community | YouTube `#link`; mobile Facebook/Twitter use SVG `<use>` of symbols that don't exist (blank icons) | Placement | Real URLs (owner to supply) | Icon font |
| Tab-title swap | Random "Coding is art." / "Digital Studio." on blur | Personality `[code]` | Works (`pathname.split('/')[2]` fragile) | Behaviour | Robust | — |
| Seasonal favicons | season1/season3 by date | Personality | Works | Rule | — | Implemented (`SeasonalIcons`) |
| Web4Ukraine script | Third-party script; POSTs the page URL to a remote server and **may redirect `top.location`** | #StopTheWar stance `[inference]` | `SECURITY RISK` (remote-controlled navigation of every visitor) | The *stance* (card + 4ukraine page) | Do not load third-party redirector; keep cause content self-hosted | — |
| Analytics beacon (Google Analytics seen in baseline) | Unknown source (not in `page/main`) | Measurement `[inference]` | `Purpose unclear — requires owner review` | — | Decide with owner (consent) | — |

## B. Other `page/*`

| Feature | What it did | Why it appears to exist | Current state | Preserve | Repair | Modernize |
|---|---|---|---|---|---|---|
| `contact` | Mapbox dark map of offices (Prague · Dubai · Kiev · London), animated **fish**, contact form → Telegram | "Contact Map + Fish Added" 2024-05; "digital ocean" animation `[git][owner]` | Works; `SECURITY RISK` (form), `OBSOLETE`-ish third-party map (token to restrict) | Map concept, fish, office list | Secure form; self-host or restricted map | Contact flow already built in v2 |
| `careers` (list · desc · team) | Searchable jobs (table `jobs`), detail pages, team/culture page | Hiring + ACS rewards `[owner]` | `SECURITY RISK`: **SQL injection** in `desc` (`$_GET['job_url']` interpolated into `SELECT … WHERE job_url = '…'`; that URL pattern is in the old sitemap) and raw `mysqli_error()` shown to visitors in list+desc. Data lives in the DB (not in the tree) | Pages, search, job cards, ACS benefits copy | Prepared statements; generic errors | Repository; jobs as DB or content files |
| `downloads` | Logos, wallpapers, programs, docs table (ACS_System.pdf downloadable; Whitepaper/Keynote rows "-") | Brand kit + "Docs tab" `[owner]` | Works; `LooksGood.png` 30 MB | All | Optimise the heavy file *without deleting the original* | Responsive table |
| `updates` | Release notes (v168.7.2, v2.18) + bugs/suggestions page | Transparency `[owner]` | Works | All text | Link to contact flow | — |
| `investor-relations` | Shareholder page, stock-price/news tabs, Discord WidgetBot **News** | Startup-investor concept `[owner]` | Works while WidgetBot exists; copy states stock isn't for sale | All | Restrict/replace third-party embed | — |
| `services` / `pragueflow` | PragueFlow: web front for a Telegram community (Prague, Russian-speaking) | Client/side project listed as a service `[git]` | Works as a static showcase; `services/index.php` empty | Showcase | Services page with pricing (announced) | — |
| `hester` (+ `avrora`, `1o`) | HesterGPT chat (v1.7 / 1o disabled), Avrora image generator UI, voice input | AI assistant `[owner]` | `SECURITY RISK` (hard-coded key); Avrora backend unclear | All incl. suggestions, typing animation, theme | Server proxy; Avrora endpoint | PWA already started |
| `maps` | "Layers" control, nothing else | "New Maps App ✅" `[owner]` | `INCOMPLETE` | Concept | Complete (Mapbox/OSM) — owner scope | — |
| `design_store` | Steam-style store: login, messages, alerts, admin CP, light mode, early access/top sellers/new/upcoming, filter | "Design Store Concept" 2025-03 `[git]` | `EXPERIMENTAL`, POST forms without CSRF | Concept + UI | Secure forms | — |
| `DC25` | ΛWDC25 event page (Prague, May 30–31, RSVP) | WWDC-style keynote culture (`antitup/plan.md` "KeyNote") | Past event; record | As archive page | — | — |
| `qirimcz` | QIRIM.CZ & SCOS.PRAHA film evening 2025-06-28 (UA/EN, registration) | Community events, QirimTalk | Past event; record | As archive page | — | — |
| `material` | Polymer-style tabs demo | UI experiment (Liquid-Glass/Material lineage `[inference]`) | `EXPERIMENTAL` | Yes | — | — |
| `universal` | Lorem-ipsum landing template ("Our Team", "Meta Center") | Early page skeleton `[inference]` | `EXPERIMENTAL` | Yes | — | — |
| `history`, `legal` | Stubs ("Our History… We are"; copy of studio nav) | Planned pages | `INCOMPLETE` | Concept | Write content (owner) | — |
| `empty` | "Coming soon" template | Placeholder target for unbuilt features | Used by menu cards | As a designed state | — | — |

## C. Platform — `home/*`

| Feature | What it did | Why it appears to exist | Current state | Preserve | Repair | Modernize |
|---|---|---|---|---|---|---|
| Authentication (`auth`) | Login/register/forgot/reset, Terms & Privacy links, wallet-network blurb | Account = gate to the platform | **`SECURITY RISK`**: SQL injection, MD5, no CSRF, sessions unhardened. Feature itself valid | Pages' look, flows (incl. password-strength idea from `hidden.md`) | Rebuild on `core/auth` (Argon2id, CSRF, lockout) | `route→controller→service→repository` |
| User tiers / badges | `users.access` → Verified, Developer, Premium, Family, Friend, Musician, Crypto badges | Status system | `LEGACY IMPL.`: duplicate `case` values make some tiers unreachable | Concept + icons | Unique tier map | Roles/permissions (`core/auth` Role/Permission) |
| Space (`timeline`) | Feed, public/private posts, comments, replies, likes, reactions, suggestions, search, dailies | Social network of the ecosystem | `LEGACY IMPL.`/`SECURITY RISK` (string-built SQL); known bug "Likes unavailable… only first 3 works" | All of it | Fix likes, prepared statements | Repository layer |
| Profile (`profile`) | `@nickname` pages, friend graph, social links, verified badge | Identity | Same SQL issues | All | — | Router for `@nick` |
| Studio — Create post | Post upload + client-side NSFW detection (nsfwjs) | Self-moderating creation tool | `EXPERIMENTAL`; models ~24 MB; upload validation unclear | Idea (AI-assisted moderation) | Server-side validation/limits | Keep model client-side |
| Studio — Write article | Rich HTML editor (DevExtreme) | Long-form publishing | `INCOMPLETE` (1.9 KB) | Concept | Complete | — |
| Studio — Edit profile | Onboarding wizard, tabs Profile/Preferences/Security/Privacy/API/Delete, start-up chime | Account setup | Works; SQL concat | Wizard, chime | Prepared statements | — |
| API keys (`api_keys`) | Per-user API tokens with scopes/validity | Developer platform | `INCOMPLETE` (no API consumes them) | Concept | Build `api/v2` | Hashed tokens |
| Messenger (`messenger`) | Chat list + conversation (`tl_messages`) | Communication | `LEGACY IMPL.`; earlier **Pyper** in `2be_deleted` | Yes | Prepared statements | Websocket/SSE optional |
| Videos | Channel + watch UI, custom player (`aev-video.js`) | Media | `EXPERIMENTAL` (placeholder titles) | Yes | Real data | — |
| Wallet / Finance | Dashboard UI from a "CareClues" template, QR generator, `balance`/`bank` fields | AEVT economy, payments | `EXPERIMENTAL`/`INCOMPLETE` | Concept + UI skin | Real ledger design | Token integration (owner) |
| Store | Apple-store style product layout | Marketplace | `EXPERIMENTAL` | Concept | — | — |
| Terminal / `_api/UI` | "CMD" launcher: Dashboard·Search·Share·Devices·Settings; also site error pages | Developer playground + system pages | Works as pages | Look + purpose | Error pages reused by v2 | — |
| Terminal Pages | 4ukraine (facts+video+music), antitup (planning notes + page), docs, donate, GTIN extractor, store concept, v2.1, Valentine ("Will you be my Valentine?" → Telegram message on "yes") | Causes, tooling, fun | `EXPERIMENTAL`; **Valentine hard-codes a Telegram bot token + chat id in `yes_page.php`** (`SECRET_EXPOSURE_08`) | All (the Valentine page is a charming personal easter egg) | Send through the Notifier, secrets from env | — |
| Terminal Tools | Crypto prices, currency exchange, domain availability, live editor, ΛΞV Math, QR generator (SmartyQR) | Useful utilities; "Math app" in release notes | `LEGACY IMPL.` | All | — | — |
| Terminal Admin | Birthday countdown, error page, résumé | Personal/admin | `EXPERIMENTAL` | Yes | — | — |
| Terminal Games | 11 browser games (Pac-Man 24 MB, Dino, Doom…) | Easter eggs / showcase | `EXPERIMENTAL`; many third-party/CodePen origin (licences to verify) | Yes | Licence audit | — |
| Docs app (`_api/Docs`) | CVX docs UI (Home/Learn/API, ⌘K, Overview, Community, Blog, Legals, Getting Started, CLI) | Documentation of the platform | `INCOMPLETE`; text is React-docs prose with names replaced | Concept | Real content; the owner's `aliev-docs` generator is the likely home | — |
| User web hosting (`_uploads/user_*`) | `web/` public sites, `web_hidden/` private apps, `profile/`, `conf.json` | "Mega Hosting", musician landing pages `[owner]` | Contains the owner's projects incl. SQLite DBs and face-api demo — **private material** | The *hosting concept* | Define storage layout in v2 (never commit user data) | — |
| `2be_created/music` | Spotify-clone: songs/albums/artists/playlists, player | Planned "Web Player… freelance musicians" `[owner]` | `INCOMPLETE`/`EXPERIMENTAL`; 87 MB of sample MP3s (licences: bensound) | Concept | Licence check | — |
| `2be_created/blog`, `settings`, `re:search`, `4_pages/*` | Blog, dashboard UI, own search engine (SQL), hiring/projects/team/downloads pages | Planned features | `INCOMPLETE` | Concept | Complete when scheduled | — |
| `2be_deleted/*` | Pyper chat, WebOS admin, dashboard concept, old messenger/studio/dailies/mail, tesco, backups, zuck | Superseded by owner | **POSSIBLE DEPRECATION** (owner-labelled). One file holds a second bot token → `SECURITY RISK` for that file only | Keep as reference | Secret must not be carried | — |

## D. Kernel, infrastructure, assets

| Feature | What it did | Why it appears to exist | Current state | Preserve | Repair | Modernize |
|---|---|---|---|---|---|---|
| `functions.php` kernel | Constants, DB connect, `verified()`, `adminOnly()`, `auth()`, `logout()`, `notify()`, `create_qr()`, `random_str()` | Shared engine (CVX Arch) | `SECURITY RISK` (credentials, always-true host test, token admin, `eval` helper) | The *capabilities* | Env config, roles, Notifier, no `eval` | `Config/Env/Notifier/Auth` (done in v2) |
| `cron.php` | Birthday notifications for users (+ a hard-coded list of private individuals), DB dump, sitemap crawl | Community care, backups, SEO | Concept valid; `SECURITY RISK`/**PII** in the hard-coded list and unsafe dump | Birthday notification *for registered users who opt in*; backups; sitemap | Remove the private list from code; safe backups | Scheduled command |
| `.htaccess` | HTTPS redirect, fbclid strip, `@nick` rewrite, error pages, UA blocklist | Hygiene | `LEGACY IMPL.` | Behaviours (https, nick, errors) | — | Router + server config |
| PWA manifest + icons | Installable app identity; icon sets (android/ios/windows11) | PWA | Icons present; manifest icons absolute URLs | Yes | Relative URLs | `manifest.webmanifest` |
| Fonts | `Ndot-55.otf` (settings/widgets), `DotlineBold.ttf`, `SF-Pro.ttf` | "Custom Fonts Support for CVX Arch" (v168.7.2) `[owner]` | Ndot licence restricts use to Nothing's brand materials; DotlineBold/SF-Pro unreferenced, licences unknown/Apple | **Keep all three in the historical tree**; document | Licence decisions are the owner's | Doto (OFL) used in v2 meanwhile |
| `lang/lang.en.php` | Empty translations file | i18n plan (Settings lists 5 languages) | `INCOMPLETE` | Concept | Implement | — |
| `_inc/helpers/*` | cryptor (2-way encrypt), curl-get, text2image, zip-folder | Utilities (third-party snippets) | Unreferenced | Keep as library | Review before use | — |
| `_assets/js/core-gpu.js`, `origElectron.js` | GPU.js matrix benchmark; pixel-grid "electron" animation (also inside `core.js`) | Experiments | `EXPERIMENTAL`; origElectron = **DUPLICATE** of code in core.js | Keep | — | — |
| `_assets/js/aev-video.*` | Custom video player | Videos app | Used by videos + 4ukraine | Keep | — | — |
| `_assets/voices/start-up.mp3` | Start-up chime | Onboarding finale (used by `editProfile/script.js`) | Used | Keep | — | — |
| `_assets/cdn/framework`, `cdn/4ukraine` | Scratch template; 4ukraine script | Experiments | Unreferenced scratch | Keep | — | — |

## E. Companion projects referenced by the ecosystem (outside this tree)

`QirimTalk` (community forum; launcher tile) · `Dreamers` / Dreamers v2 (social app; slider card, logo in brand set) · `aliev-docs` (docs generator) · `Hester` (Telegram bot origin of HesterGPT) · `AEVT` (token README, coin logos) · `PragueFlow` · `PIXELITE` (sibling agency brand). Links from the launcher/slider to these are *intended* and should be restored.

---

## Summary of classes

| Class | Count (approx.) | Typical action |
|---|---|---|
| SECURITY RISK | 13 areas | Rebuild implementation, keep feature |
| LEGACY IMPL. | 14 | Rewrite internals, same behaviour |
| INCOMPLETE | 16 | Preserve concept; complete when scheduled |
| EXPERIMENTAL | 13 | Preserve; document |
| OBSOLETE/BROKEN | 3 (ticker data source, Google-hosted dependencies, maps) | Investigate alternatives, fix |
| DUPLICATE | 1 (`origElectron.js`) | Document, defer |
| POSSIBLE DEPRECATION | `2be_deleted/*` (owner-labelled) | Owner decision |
