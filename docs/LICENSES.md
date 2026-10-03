# Licences and terms of third-party material

Rule for this phase: **record, don't delete.** A licence question never causes removal; it becomes an explicit owner decision. "Verified" means checked against a primary source (the project's own repository/licence file, the font's embedded notice, or the provider's official documentation) on 2026-10-02; "Unverified" means it could not be confirmed and is flagged, not assumed.

| Material | Source | Licence / terms | Status | Production-use concern | Recommended action |
|---|---|---|---|---|---|
| **Ndot-55** (`resources/fonts/Ndot-55.otf`, served as `/build/fonts/Ndot-55.otf`) | Colophon Foundry for Nothing (`nothing.tech/Ndot55`) | Embedded notice (verified in the file): *"By using Ndot, you agree to their use solely on the Nothing brand materials and will not use them for any other purpose whatsoever."* and *"You must not send or share the Ndot font software, with any … third party who is not commissioned by, or directly associated with, Nothing."* | **Verified** (font's own name table) | **High — currently distributed.** The file is committed in this repository and published by `scripts/build.php`. Used on the main page only in the Settings panel heading (`website/home/views/partials/shell/menu.php:163`, class `u-9999d25`) and the clock date readout (`menu.php:130`, class `u-b7ae7c2`); both classes are in `website/home/assets/css/utilities.css`, the `@font-face` is in `resources/css/fonts/base.css`. The repository holds no licence or permission from Nothing, so this use is outside the stated terms | **OWNER DECISION REQUIRED before launch**: (a) obtain written permission/licence from Nothing and record it here, or (b) instruct the switch of those two spots to Doto (SIL OFL, already shipped: change `font-family` in the two classes, delete the `@font-face` and the file). Nothing has been substituted automatically |
| **DotlineBold** (`DotlineBold.ttf`) | Unknown | None embedded; no author/vendor strings | **Unverified** (no data) | Unknown provenance | Owner to state where it came from; possibly the "my own font" experiment. Kept, unreferenced |
| **SF Pro** (`SF-Pro.ttf`, 5.9 MB) | Apple San Francisco font | Embedded Apple licence (verified): for mock-ups of UIs for Apple OS software by registered Apple developers; *"You may not embed the Apple Font in any software programs or other products."* | **Verified** | **High** if served on the web or redistributed | Not shipped by v2; stays in the legacy tree as a historical asset; on Apple devices `-apple-system` renders SF without shipping it. Owner decides whether to keep it in the public legacy repo |
| **Doto** | `github.com/oliverlalan/Doto` | SIL OFL 1.1 (repo licence API: `OFL-1.1`; `OFL.txt` fetched) | **Verified** | Low; keep the licence text with the font | Used as the interim/fallback display font |
| **Roboto Thin** (calculator widget) | Google Fonts (file `fonts.gstatic.com/s/roboto/v51/…`) | Roboto 3 is OFL-1.1 (repo `googlefonts/roboto-3-classic`: `OFL-1.1`) | Partially verified (publisher repo; the served file's own notice not read) | Low | Keep; ship licence text |
| **Unicons (line) v4** | `Iconscout/unicons` | IconScout Simple License (LICENSE read): free for personal and commercial use; attribution *"strongly encouraged"* but not mandatory; no republishing as a standalone asset; no collecting to build a competing service | **Verified** | Low. Self-hosting the font in a site is use, not republishing | Keep self-hosted; add optional credit "Unicons by IconScout" on the credits/legal page |
| **three.js r128** | `mrdoob/three.js` | MIT (repo licence API) | **Verified** | None | Self-hosted pinned copy; keep copyright header (it is inside the file) |
| **jQuery 3.x** | `jquery/jquery` | MIT | **Verified** | None | Only if retained; single copy |
| **underscore 1.8.3** | `jashkenas/underscore` | MIT | **Verified** | Loaded but unused in the page | Not required by the page; kept in legacy tree |
| **Hammer.js** (bundled in `functions-min.js`) | `hammerjs/hammer.js` | MIT | **Verified** | None | Replaced by Pointer Events in v2; legacy copy kept |
| **Intergram widget** (`intergram.xyz/js/widget.js`) | `idoco/intergram` | **MPL-2.0** (repo licence API). Bundle also embeds Preact and others (not individually checked) | **Verified** (repo) | MPL-2.0 is file-level copyleft: an *unmodified* copy may be served with its licence notice; modifications to MPL files must be published. The hosted chat service at intergram.xyz is a third party relaying visitors' messages to Telegram | Serve an **unmodified pinned copy**; set `disableLoadmill: true` (supported option); keep a `NOTICE` pointing to the upstream licence; privacy statement should mention the relay |
| **3D "Room"** (CSS + 3 images) | `ricardoolivaalonso/Codepen` (GitHub) — originally a CodePen "Room" | The GitHub repo has **no licence file** (licence API 404). CodePen's documentation (verified) says *public Pens are MIT-licensed by default* — which applies only if this is that public Pen | **Unverified** (the originating Pen was not located) | Medium: reuse without a licence is not permitted by default | Owner to confirm the Pen/permission (author credit stays in the CSS header); it is shipped on the main page as in the original (not a technical blocker; a "verify" item) |
| Clock widget (`utilityClock`) | Unknown CodePen origin | Unknown | **Unverified** | Medium | Owner/author lookup; credit if found |
| Calculator widget | Unknown CodePen origin; embeds Meyer reset | Reset is public domain; widget unknown | **Unverified** | Medium | As above |
| Globe texture (`i.imgur.com/JLFp6Ws.png`) | Unknown | Unknown | **Unverified** | Medium | Owner provenance; alternatives can be compared visually if needed |
| Coin logos (BTC, ETH, USDT, SOL, TON, DOT, SUI, APT) | CoinMarketCap CDN | Trademarks of their projects; shown nominatively | **Unverified** (no per-logo terms) | Low–medium | Self-hosted copies used for the same purpose as before; owner review |
| AEVT / AEVD logos | `EmirTheBest7/AEVT` | Owner's | n/a | None | — |
| **CoinGecko API** (primary ticker provider) | `docs.coingecko.com` | Official docs (verified): a **Demo / Keyless** API exists (IP-based limits, price cache 60 s); Demo key 100 calls/min; browser calls hit CORS so must be proxied. **Terms of Service page was bot-blocked (HTTP 403) — attribution/commercial-use rules not read** | Docs verified; **ToS unverified** | Medium: possible attribution requirement; shared-IP rate limits for keyless | Server-side cache (≤ 1 call/min); owner to obtain a free Demo key (`COINGECKO_API_KEY`) and read the ToS; show "Powered by CoinGecko" if required |
| **CryptoCompare / CoinDesk Data** (original provider) | `min-api.cryptocompare.com` | Now **HTTP 401 without a key** (verified by request) | Behaviour verified; terms unverified | Needs an account key | Optional provider (`CRYPTOCOMPARE_API_KEY`) |
| Mapbox (contact map) | `mapbox.com` | Terms require attribution/logo; public `pk.` token must be URL-restricted | **Unverified** (terms not fetched) | Medium — only when `MAPBOX_TOKEN` is set; without a token the map is not loaded | Optional integration. Before enabling: read the Mapbox terms, keep the attribution control visible, URL-restrict the token to the production domain |
| Discord WidgetBot (IR News tab) | `widgetbot.io` | Third-party embed | **Unverified** | Medium (third-party iframe) | Decide with the IR-page port |
| Telegram Bot API | `core.telegram.org` | Bot terms | n/a | Low | Env-configured |
| Google Gemini API (HesterGPT) | Google | Google AI terms; usage via **server-side key** | **Unverified** | Key was exposed in legacy source | New key by the owner; server proxy; consider per-IP quotas |
| DevExtreme 18.2 (Studio article editor, via unpkg) | DevExpress | DevExtreme is a **commercial** product (DevExpress licence) — believed, not verified | **Unverified** | Likely requires a licence | Verify before porting Studio |
| nsfwjs / TensorFlow.js models, face-api (Studio, uploads demo) | respective repos | MIT-style believed; model weights' terms unchecked | **Unverified** | Medium | Verify when Studio is ported |
| Games & tools (Pac-Man, Doom, Dino, Rubik, crossy, snake, sticky, rabbit, pong, blocks…; SmartyQR, ΛΞV Math…) | Mostly CodePen pens and open-source games | CodePen default MIT per author unless stated; others unknown | **Unverified** | Medium | Audit each when the terminal is ported |
| Music samples (`2be_created/music`, `bensound-*.mp3`) | bensound.com | Bensound's free licence requires attribution and has usage limits (believed) | **Unverified** | Medium for public streaming | Verify before porting music |
| Material app (Polymer tabs demo), Universal page, other templates | Template origins | Unknown | **Unverified** | Low | Credit when ported |
| Legacy helper snippets (`cryptor.php`, `curl-get.php`, `zip-folder.php`) | `github.com/ttodua/useful-php-scripts` and others | Stated in file headers; not checked | **Unverified** | Low | Review before reuse |
| ACS paper, whitepapers, brand logos | Owner | Owner's | n/a | None | — |

## How this ledger is used

- Any new dependency or asset adds a row **before** it ships.
- "Unverified" never blocks preserving something in the legacy tree; it blocks *shipping it in production* only where the concern column says High, and only until the owner decides.

### Added during the agency rebuild (self-hosted copies; licences from the upstream projects, not yet re-verified against primary sources unless stated)

| Asset | Source | Licence | Where |
|---|---|---|---|
| SaaS Widget (profile panel look) | Jon Kantner, codepen.io/jkantner/pen/rNXoWop (layout after a Dribbble shot by Nur Praditya) | MIT — LICENSE.txt read | `public/build/home/css/profile-widget.css` (flattened Sass, attribution in the header) |
| Montserrat, Open Sans, Poppins, Roboto, Source Code Pro, Inconsolata | Google Fonts | SIL OFL 1.1 (Roboto/Source Code Pro: OFL / Apache-2.0 families) | `public/build/fonts/{careers,contact,docs,terminal}/` |
| Font Awesome Free 6.5.2 (3 brand glyphs) | fontawesome.com | Icons CC BY 4.0 · font SIL OFL 1.1 · code MIT | `public/build/vendor/fontawesome/` |
| Bootstrap Icons 1.10.3 (2 glyphs) | getbootstrap.com | MIT | `public/build/vendor/bootstrap-icons/` |
| Bootstrap 4.5.0 CSS | getbootstrap.com | MIT | `public/build/vendor/bootstrap/` |
| UIkit 3.3.6 | getuikit.com | MIT | `public/build/vendor/uikit/` |
| jQuery 3.1.0 / 2.1.3, jQuery UI 1.11.2, underscore 1.8.3 | jquery.org, jqueryui.com, underscorejs.org | MIT | `public/build/vendor/{jquery,jquery-legacy,underscore}/` |
| Mapbox GL JS 2.4.1 | mapbox.com | **Mapbox Terms of Service (not open source)** — self-hosting terms **unverified**; needs a token | `public/build/vendor/mapbox-gl/` |
| Unicons 4.0.8 font files | npm `@iconscout/unicons` via jsDelivr | IconScout Simple License (as above) | `public/build/vendor/unicons/` |
| Intergram widget (`widget.js`) | idoco/intergram (served from intergram.xyz) | MPL-2.0 (upstream; **not yet re-verified here**) | `public/build/vendor/intergram/widget.js` |
