# Main page (`/`)

Template: `app/Views/layouts/shell.php` (chrome) + `pages/home.php` (five sections). Controller: `HomeController`. Behaviour: classic scripts under `public/assets/js/home/`, loaded with `defer` in this order — jQuery 3.1.0, underscore, three.js r128, `planet.js` (globe), `sections.js` (Hammer + section scroller/slider, byte-identical to the original `functions-min.js`), `notify.js` (notifications), `spotlight.js`, `room.js` (3D room tilt), `app.js` (everything else). Styles: `main.css`, `core.css`, `widgets/3droom.css`, `utilities.css` (generated from the original inline `style=""`), `shell.css`, `profile-widget.css`.

| Part | Behaviour | Source of truth |
|---|---|---|
| Preloader | shown ≥1.2 s, released on `load`, hard fail-safe at 5 s (no optional feature can block it) | `app.js` |
| Sections | wheel / swipe / keys / side-nav / "Hire us" buttons | `sections.js` |
| Navigation rail | hamburger menu, Settings / Widgets panels (`data-panel`), accordion, Esc closes | `app.js` |
| Settings | language list (non-English shows an honest notice), PWA install modal, shortcuts, fullscreen, functional-key (pending) | `app.js` |
| Launcher & profile | toggles on the two top-right buttons; outside click / Esc close; profile panel = SaaS Widget look, data from `HomeController` (guest or `AuthFacade::currentUser`) | `app.js`, `user-panel.php` |
| Spotlight | Ctrl/⌘+J or the menu search; actions + directory from `config/apps.php` (`#aev-directory`) | `spotlight.js` |
| Notifications | welcome sequence | `notify.js` |
| Ticker | `/api/prices` every 60 s; AEVT has no verified source and shows "—" | `app.js`, `PriceService` |
| Hire form | `POST /hire` over fetch (JSON) with the same guard as Contact | `app.js`, `HireController` |
| Intergram | loaded only when `INTERGRAM_CHAT_ID` is set; the widget script is a self-hosted copy (`/assets/vendor/intergram/widget.js`, MPL-2.0) and the CSP gains `frame-src` for the chat frame only on the main page | `app.js` |
| Works slider | Community, Dreamers, Cerebro Blockchain, Cortex Browser, EROS | `home.php` |

Controls whose destination does not exist yet keep their place and answer with the designed notification (`data-soon`). Removed: HesterGPT (box, menu entry, card, launcher/directory entries, CSS, images), Avrora card, Store / Investor Relations / Journal menu entries.

Open: the `Dreamers` / `Cerebro` / `Cortex` / `EROS` slider cards are showcase items whose products may not belong to the agency scope — owner to confirm.
