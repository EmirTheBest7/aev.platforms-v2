# Widgets audit

| Widget | Path | Purpose | Dependencies | Design relevance | Security / performance | Decision |
|---|---|---|---|---|---|---|
| Hero planet | `page/main/planet.js` | WebGL globe behind hero | three.js r128 (CDN), hotlinked `imgur` texture, mouse tracking | **High** — signature visual | No resize / DPR cap / dispose / WebGL fallback; third-party texture | **REWRITE** lifecycle, keep look; self-host texture + pinned three; static fallback image |
| 3D room | `page/main/widgets/3droom/` (`index.php`, `script.js`, `style.css`) | Decorative CSS/JS 3D scene, styled into homepage | none external found | Medium (stylesheet is loaded by homepage) | `index.php` is PHP — ports to static markup | **REWRITE** to static component if referenced by homepage markup (verify in Phase 4); otherwise ARCHIVE |
| Clock | `.../clock/` | Decorative clock | none | Low | — | **ARCHIVE** unless a homepage section embeds it |
| Calculator | `.../calculator/` | Toy calculator | none | Low | `eval(equation.join(''))` — code execution of a built string | **DELETE** (`eval`); not agency-relevant |
| Crypto ticker | inline in `page/main/index.php` | BTC/ETH… prices | cryptocompare API, CoinMarketCap hotlinked logos | Medium (marquee look) | Unthrottled third-party fetch from every visitor | **DELETE**; marquee *style* is reused for a clients/tech-stack strip |
| Intergram chat | `intergram.xyz/js/widget.js` | Visitor chat → Telegram | third-party script | — | Unverified third-party script, extra tracking | **DELETE**; contact form replaces it |
| web4ukraine | `js.web4ukraine.org` | Solidarity banner | third-party script | Medium (owner's stance) | Unverified | Verify; KEEP as self-hosted static banner if owner wants, else DELETE |
| Toast notifications | `page/main/aev-notify.js` | UI toasts | jQuery? (verify) | High (used for form feedback) | — | **KEEP concept / REWRITE** w/ `aria-live`, reduced motion |
| Spotlight | `page/main/spotlight.js` | Cmd/Ctrl+J command palette | Unicons, Material Symbols | High | `innerHTML`, unencoded search | **REWRITE** |
| Preloader | `.loading-screen` in homepage | Logo + loading bar | CSS only | High | Blocks content until JS? (verify) | **KEEP**, make non-blocking |
| Hammer/touch bundle | `functions-min.js` | Gestures | embedded lib | Unknown | 40 KB | **DELETE** unless Phase 4 usage check finds live callers |
