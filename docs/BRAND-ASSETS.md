# Brand assets

All files below were copied **byte-for-byte** from the legacy repository (verified with `cmp` for `A.svg`, `ALIEV.svg`, `ALIEV3.svg`; the rest by identical `cp`). Never edit, redraw, recolour or re-export them. All logo SVGs use a **white** fill and are designed for dark backgrounds.

## Logos — `public/build/images/brand/`

| File | viewBox | Role | Referenced by legacy |
|---|---|---|---|
| `ALIEV.svg` | 740.7 × 124.1 | **Primary wordmark ΛLIΞV** (legacy `LOGO`) | every page (rail, preloader) |
| `ALIEV3.svg` | 1000.4 × 124.1 | Wordmark with ".IO" (legacy `LOGO_IO`) | `_inc/functions.php` constant |
| `A.svg` | 115.7 × 122.5 | **Λ monogram** | offered on the downloads page |
| `Dreamers.svg` | 322.4 × 51.5 | "Dreamers.com" lockup | downloads page |
| `weblogo.svg` | 1103.8 × 841.9 | Large web logo artwork | downloads page |
| `ALIEV_3D.png` | 200 KB | 3D render of the mark | downloads page (open/download button) |
| `ALIEV_original.svg` | 740.7 × 124.1 | Original Illustrator export of the wordmark | not referenced — kept as brand archive |
| `ALIEV1.svg` | 740.7 × 124 | Wordmark variant | not referenced — brand archive |
| `AEV_Code.svg` | 1134.2 × 128.05 | "ΛΞV Code" lockup (role inferred from name) | not referenced — brand archive |
| `AEV_Dev.svg` | 648.7 × 120.7 | "ΛΞV Dev" lockup (role inferred from name) | not referenced — brand archive |
| `App.svg` | 156.35 × 156.35 | App-icon mark | not referenced — brand archive |

Unreferenced variants are shipped under `/build/images/brand/` only so they remain downloadable from the brand page; the owner may prune them. Logo URLs are kept identical to the legacy filenames so external hot-links keep working via the `/page/downloads/logo/*` redirect (Phase 4).

Monochrome note: every SVG is white. A dark-on-light variant does not exist in the legacy set and must **not** be created without approval (a favicon in white would also be invisible on light browser tabs — hence the favicon PNG/ICO sets below).

## Favicons & app icons — `public/build/icons/`, `public/favicon.ico`

| Set | Look | Legacy rule (preserved by `SeasonalIcons`) |
|---|---|---|
| `season1/` | white rounded tile, black Λ | Mar 20 – Jun 20 and Sep 22 – Dec 21 |
| `season3/` | black tile, white Λ, blue/yellow border | Jun 20 – Sep 22 and Dec 21 – Mar 20 |
| `season2/` | teal-gradient Λ on dark | **never selected by legacy code → not migrated** |

Each set contains `favicon.ico` and `apple-touch-icon` 57 – 180 px. The legacy PWA icons (`_assets/icon/pwa/{android,ios,windows11}`) are of the season3 look and are migrated with the PWA manifest in Phase 5 (only if install behaviour is verified to work).

## Fonts — `public/build/fonts/` and the legacy tree

| File | Licence | Where | Status |
|---|---|---|---|
| `Doto-latin.woff2` (v2) | SIL OFL 1.1 (`Doto-OFL.txt`) | Display/dot-matrix role | **Interim** stand-in for Ndot-55 |
| `Ndot-55.otf` (`resources/fonts/`, published as `/build/fonts/Ndot-55.otf`) | Nothing brand notice — restricted | Settings heading, clock date readout | **Currently committed and served.** Licence unresolved — owner decides (permission / replace with Doto); see `docs/LICENSES.md` |
| `DotlineBold.ttf` (legacy) | Unknown | Unreferenced — possible link to the "my own font" idea | Kept; `Purpose unclear — requires owner review` |
| `SF-Pro.ttf` (legacy) | Apple — web self-hosting not permitted | Committed deliberately (2024-09-09); only `font-family` fallback names reference it | Kept in the legacy tree; not shipped by v2; owner review |
| `Roboto-Thin-latin.woff2` (v2, staged) | per publisher (Apache-2.0 or OFL; not independently verified) | Calculator widget (the legacy widget loaded it from Google Fonts) | Staged for the widget port |

Nothing was deleted. See `DESIGN-SYSTEM.md` ("Fonts — status") for the evidence trail.

## Other brand imagery (pending Phase 4)

Homepage visuals in legacy `page/main/img/` (hero/work/about photos, 16 line icons `icons/*.svg`, `ukr_flag.svg`) are migrated with the pages that use them, converted to WebP/AVIF with originals kept out of the web root. Ownership/licence of stock photography (`IMG_*.jpg`, `work-*.jpg`) must be confirmed by the owner before publishing. `page/downloads/wallpapers/LooksGood.png` (30 MB) is optimised or dropped.
