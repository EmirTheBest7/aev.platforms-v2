> **Historical record — not current scope.** Audit of the original main-page widgets; current notes are in [`../MAIN-PAGE.md`](../MAIN-PAGE.md).

# Widgets and page components

Every widget and component of the legacy main page is **KEPT**. "Repair" and "Modernize" describe internal work only; the visual result stays.

| Widget | Where | What it does | Dependencies | Design relevance | Security / performance | Decision |
|---|---|---|---|---|---|---|
| Globe | `planet.js`, `#globeCanvas` | three.js r128 dot-matrix planet, orbiting arcs, drag to rotate | three r128, jQuery (events), imgur texture | **Signature hero visual** | No resize/DPR cap/dispose/WebGL fallback; hot-linked texture | **KEEP · REPAIR** (self-host, fallback, resize, pause off-screen) |
| Crypto ticker | inline script + `.marquee` | Scrolling prices with logos (BTC ETH AEVT AEVD USDT SOL TON DOT SUI APT) | CryptoCompare (now 401), CMC/GitHub images | Owner's token presence on the front door | Upstream broken; per-visitor third-party call | **KEEP · REPAIR** (server cache, new source, local logos, fail-soft) |
| Intergram chat | widget.js + inline config | Floating support chat to Telegram | intergram.xyz iframe; hidden loadmill tracker | Visual/functional role on every visit | Third-party script, tracker | **KEEP · REPAIR** (pinned copy, `disableLoadmill`, id from env) |
| HesterGPT box | `.ai-box` + `/page/hester` | Slide-up AI chat panel | Gemini API (key leaked) | Ecosystem AI entry | Key exposure | **KEEP · REPAIR** (server proxy) |
| Notifications | `aev-notify.js` | Stacked toast cards with hover glow | none | Distinct UI element | Console spam | **KEEP · LEGACY IMPL.** |
| Spotlight | `spotlight.js` | Ctrl/⌘+J popover search | Popover API, Unicons | Distinct UI element | XSS/URL-encoding | **KEEP · REWRITE** |
| App launcher / profile panel | inline | Apps grid, avatar card | — | Ecosystem switcher | dead tiles | **KEEP · REPAIR** (`APPLICATIONS.md`) |
| Preloader | `.loading-screen` | Wordmark + bar | — | Brand moment | can strand without JS | **KEEP** |
| Settings panel | `#settings1` | Key generator? / language / PWA / shortcuts / fullscreen / appearance / cookies | custom dropdown (jQuery) | Part of "Custom Fonts support" update | dead controls | **KEEP · INCOMPLETE→complete** (`MAIN-PAGE.md` §4) |
| Widgets panel | `#register1` | Date block + clock + calculator iframes | — | "widgets menu" | calculator `eval` | **KEEP · REPAIR** |
| Clock widget | `widgets/clock` | Analog utility clock | none | Visual | — | **KEEP** |
| Calculator widget | `widgets/calculator` | iOS-style calculator | `eval`, Google Font, reset CDN | Visual | `eval` | **KEEP · REPAIR** (safe parser; local font/reset) |
| 3D room | `widgets/3droom` | Pointer-tilt isometric room | CSS 3D, 3 images | About-section centrepiece | hot-linked images | **KEEP** (images self-hosted) |
| Section scroller / slider | `functions-min.js` | Full-screen sections + 3-up carousel | Hammer.js | Core interaction | passive-wheel | **KEEP · LEGACY IMPL.** |
| Floating-label form | `.work-request` | Name/email/services lead form | — | Look | unvalidated POST | **KEEP · REPAIR** |
| PWA modal | `.RccSCT7` | Install instructions | `:target` | Look | no install action | **KEEP · REPAIR** |
| Seasonal favicons | `icon/index.php` | Date-based icon set | — | Personality | — | **KEEP** (done) |
| Title swap | `core.js` | Random title on tab blur | — | Personality | — | **KEEP** |
| Web4Ukraine script | `<script async>` | Third-party solidarity script | remote server | The cause is the owner's | **remote-controlled redirect** | The *cause content* is kept (card + 4ukraine page); **the third-party script is not loaded** (security) — owner may supply a self-hosted banner |
| Ripple button | `core.js` | Click ripple on buttons | — | Look | only first button wired | **KEEP · REPAIR** |
| Contact fish | `page/contact` | Animated SVG fish | — | Personality | — | **KEEP** |
| Terminal tools & games | `home/_api/UI/terminal/*` | Utilities, 11 games | various, third-party | Playground | licences to verify | **KEEP** (port later) |
