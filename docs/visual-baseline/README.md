# Legacy visual baseline

Screenshots of the legacy ALIEV.IO site (sanitised copy, see `scripts/visual/legacy-baseline.sh`), taken with Chromium headless at 1440×900, 820×1180, 390×844 and 844×390, colour scheme dark. Used as the reference for visual-continuity checks (Phase 7). Third-party regions (maps, crypto prices, Google Analytics, chat widgets, Cloudflare challenge) are not reproducible and are not part of the comparison.

Findings recorded from the baseline:
- The homepage is **dark-only**: `main.css` has no `data-theme` rules; effective body background is `#000` (core.css overrides main.css `#0c0c0c`).
- ≥544 px: 60 px black rail (1 px `#282828` right edge), hamburger at top, wordmark rotated −90° (~90 px long) below it, social icons at the bottom. <544 px: top bar, hamburger left, horizontal wordmark right.
- Hero H1 68 px / 900 / line-height 1, with a blue `#0f33ff` full stop; "HIRE US" button with a blue slab starting 23 px in.
