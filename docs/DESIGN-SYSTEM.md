# Design system (extracted from the legacy site)

Source of truth: legacy `_assets/css/core.css`, `page/main/main.css`, `page/main/index.php`, plus rendered baselines in `docs/visual-baseline/`. Nothing here is invented; where the legacy CSS contradicted itself the **rendered** result was measured and recorded. Implemented tokens: `public/assets/css/site.css`.

Status legend: ✅ implemented in `site.css` · 🔜 extracted, ported with its page in Phase 4/5 · ❌ intentionally dropped.

## Theme

**Dark only.** `main.css` contains no `[data-theme]` rules; the legacy "Toggle theme" spotlight action switched an attribute that styled nothing on the homepage. A light skin would be a new design, so it is not shipped (`color-scheme: dark`). If the owner wants light mode it must be designed and approved first.

## Colour

| Token | Value | Legacy source | Use | |
|---|---|---|---|---|
| `--c-bg` | `#000` | measured; `core.css body` (overrides `main.css #0c0c0c`) | page background | ✅ |
| `--c-bg-rail` | `#000` | `.Navbar` | rail / top bar | ✅ |
| rail edge | `#282828` 1 px | measured (right edge of rail) | rail separator | ✅ |
| `--c-surface` | `#1b1b1b` | `.apps ul` | launcher panel | 🔜 |
| `--c-surface-edge` | `#282a2c` | `.apps` border/hover tile | panel border, tile hover | ✅ |
| `--c-card` / `--c-card-edge` | `#18181b` / `#29292c` | `.noticard` | toast + notices | ✅ |
| `--c-text` | `#fff` | body | text | ✅ |
| `--c-text-muted` | `#99999d` | `.notidesc` | secondary text | ✅ |
| `--c-text-dim` | `#666` | `.Navbar-menu-minor` | tertiary text | ✅ |
| `--c-hairline` | `#555` @ 35 % | `.l-side-nav::before` | side-nav line | 🔜 |
| `--c-accent` | `#0f33ff` | button slab, full stops, submit | primary brand blue | ✅ |
| `--c-info` | `#32a6ff` | `.notititle` | toast title | ✅ |
| `--c-focus` | `#2eadff` | gradient start | focus ring (new: legacy had none) | ✅ |
| `--grad-accent` | `#2eadff → #3d83ff → #7e61ff` | `.noticard:after` | toast bar | ✅ |
| `--c-ok` / `--c-error` | `#62f754` / `#f44336` | legacy status colours in main.css | form states | ✅ |

Not carried over: the teal/green palette in `_assets/css/header.css` (`#1b5955`, `#5ca084`, …) — belongs to an abandoned header module and appears nowhere on the rendered pages. ❌

## Typography

| Role | Value | Notes |
|---|---|---|
| Body/UI | `"Helvetica Neue", Helvetica, Arial, sans-serif` | Legacy declared `Montserrat`/`Roboto`/`Inter` but **never loaded** them (no `@font-face`, no font links), so visitors saw the platform sans-serif. The stack reproduces what was actually rendered. No webfont is shipped for body text. ✅ |
| Display (dot-matrix) | **Doto** (variable, wght 100–900), fallback `"Courier New", monospace` | Replaces **Ndot-55**. ✅ |
| Base size | `html{font-size:62.5%}` → `1rem = 10px`; `body 14px/1.6` | ✅ |
| Hero H1 | 68 px / 900 / line-height 1 (55 px ≤1180/900, 44 px ≤767) | ✅ |
| Buttons | 14 px / 700 / uppercase | ✅ |
| Rail menu | 14 px / 700 / uppercase / letter-spacing 0.28 rem | ✅ |

### Font decision: Ndot-55 → Doto

Ndot-55's embedded notice limits it to Nothing's own brand materials, so it is **not** shipped. The legacy used it in only two places: the clock widget's time readout and the "Settings" panel heading. Candidates were compared against the original render (screenshot in the Phase 3 notes): **Doto** (round dots on a grid — closest match; weight ~600–700 matches Ndot's dot size) vs **DotGothic16** (square pixels — visibly different). Doto was chosen; DotGothic16 was not needed.

- Font: Doto — © 2024 The Doto Project Authors (<https://github.com/oliverlalan/Doto>), **SIL Open Font License 1.1**. Licence text shipped at `public/assets/fonts/Doto-OFL.txt`.
- Shipped file: `Doto-latin.woff2` (5.3 KB, latin subset), `font-display: swap`, preloaded.
- Swap point: the single `--font-dot` token + one `@font-face` in `site.css`.

## Layout & spacing

| Item | Value |
|---|---|
| Rail width (≥544 px) | `--rail-w: 6rem` (60 px), full height, fixed |
| Top bar (<544 px) | 5.6 rem (legacy 38 px on very small screens via `--navbar-height`; 56 px keeps a 44 px tap target) |
| Menu panel | slides from the rail, 26 rem wide (full width on phones); item padding `1.6rem 14%`; hover `rgba(255,255,255,.06)`, radius 8 px |
| Breakpoints (legacy) | 1180, 900, 767, 600, 359 px; plus landscape-phone rules; nav switches at **544 px** |
| Content column | legacy homepage content starts ≈ x=300 px at 1440 (container) — ported with the homepage 🔜 |

## Shape

Radius: card/toast `1rem` (16 px), launcher panel/tiles `28px` / `8px`, round buttons `50%`. Borders: 1 px hairlines. Shadows: launcher `0 2px 10px rgba(0,0,0,.2)` 🔜.

## Components

| Component | Spec | |
|---|---|---|
| **Rail** | black, 1 px `#282828` right edge; hamburger (3 × 2 px lines, 20 px) at top; wordmark `ALIEV.svg` rotated −90°, 90 px long, starting below the toggle; social icons bottom 🔜 | ✅ rail/menu, 🔜 social |
| **Primary button** | 14/700 uppercase text, padding `5px 17px 5px 12px`; blue `#0f33ff` slab begins 23 px from the left so the first letters overhang it; on hover/focus the slab slides to `left:0` in `.2s ease-in-out`; 15 px arrow icon | ✅ |
| **Notice / toast** | `#18181b` card inside a 1 px `#29292c` edge, radius 1 rem, 4 px gradient bar at left, title `#32a6ff` 12 px/500, body `#99999d`; hover nudges title +0.15 rem and body +0.25 rem (300 ms); enter/exit 500 ms | ✅ static, 🔜 animated toast stack (`.aev-notifications`, 288 px wide, top-right) |
| **Form fields** | transparent, 1 px white border, no radius, white text, `#666` placeholder; submit = primary blue | ✅ |
| **Launcher panel** | `#1b1b1b`, 10 px `#282a2c` border, radius 28 px, tiles 86 × 98, 64 px icons, 12/16 labels; hover `#282a2c` radius 8 | 🔜 |
| **Cards (homepage promos)** | rounded image cards with overlay text and outlined action button (yellow/blue) | 🔜 extract with homepage |
| **Spotlight** | Cmd/Ctrl+J popover with combobox | 🔜 |
| **Preloader** | logo + loading bar (`bluebar` keyframes) | 🔜 |
| **Globe** | WebGL dot-matrix planet, top-right of hero | 🔜 |

## Motion

Transitions: UI `0.2s ease-in-out` (button slab), `0.35s` (menu open), `0.4s ease` (legacy cards), `cubic-bezier(.333,0,0,1)` for launcher tile transforms. Keyframes in the legacy CSS: `notiOut`, `notiCardIn/Out`, `bluebar`, `pulse`, `stroke-draw`, `menu`, `icon3d`, `scrolling` (marquee). **New:** all transitions/animations collapse under `prefers-reduced-motion: reduce`.

## Responsive behaviour

≥544 px: vertical rail. <544 px: top bar (hamburger left, horizontal wordmark right). Cards stack on phones; landscape phones keep the rail. The legacy `.device-notification` ("please rotate your device") overlay is **not** carried over — the site must work on every size. ❌

## Accessibility additions (no visual change)

Skip link; `:focus-visible` ring (`#2eadff`); real `<button>` for the menu with `aria-expanded`/`aria-controls`; `aria-current` on the active link; zoom is not blocked (legacy viewport used `user-scalable=no`); reduced-motion support; labelled form controls.

## Dropped legacy design artefacts

Unused teal header palette; `device-notification` overlay; `open-props` CSS import from unpkg (no variable from it is referenced); `normalize.css` CDN (replaced by a minimal reset); Material Symbols / Unicons icon fonts (to be replaced with inline SVG from the legacy `page/main/img/icons/` set).
