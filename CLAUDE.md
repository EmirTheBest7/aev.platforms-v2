# CLAUDE.md — ALIEV.IO

Permanent instructions for Claude Code sessions in this repository. Read this first, then `docs/ARCHITECTURE.md`. `docs/history/PROJECT-VISION.md` is background on the original project, not current scope.

## Project identity and scope

ALIEV.IO ("ΛΞV", "Λ L I Ξ V Platforms") is the owner's long-running personal project. **This repository is the fresh rebuild of it as a digital agency / studio website**, made from the manually cleaned source `./aev-new` (git-ignored, read-only; it is the visual and behavioural reference). Read `docs/ARCHITECTURE.md` first.

**In scope:** the main page (`/`), Careers, Contact, Downloads, Auth (`home/auth`) and the retained `_api` bundle (kept as an internal/API/terminal foundation, to be modernised later, not redesigned).

**Out of scope — do not reintroduce:** social network, messenger, wallet/finance, AI / HesterGPT (at most a clean extension point for a future iframe; do not invent a replacement), games as apps, music platform, search-engine experiments, abandoned apps and prototypes. `docs/history/` and the ecosystem-era docs are historical records, not current scope.

Goal: **same ALIEV.IO design and UI/UX, better functionality, security and maintainability.** Not a redesign.

## The seven standing rules

1. **ALIEV.IO preservation rule.** Never redesign the existing visual identity (logo, colours, typography treatment, spacing, navigation, animations, panels, launcher, globe, spotlight, ticker, chat, settings, interaction patterns) without explicit owner instruction. Do not substitute a generic agency/SaaS template, new palette, new type scale, Bootstrap/Tailwind/Material defaults.
2. **Historical preservation rule.** Do not delete features, pages, widgets, experiments, assets, fonts, icons, integrations or launcher entries because they are old, verbose, unconventional, unused-looking or "you'd do it differently". Default is **KEEP**. If something looks useless mark it `POSSIBLE DEPRECATION` with the reason and ask. Delete only if it is demonstrably accidental, a proven duplicate, malicious, contains sensitive material that must not remain, technically dangerous — or the owner explicitly approves. Record every such decision in `docs/CHANGE-LOG.md`.
3. **Functional modernization rule.** Repair or rewrite *internals* while preserving *intended behaviour*. Separate **idea** from **implementation**: an insecure login is replaced by a secure login, the account feature stays. Prefer same-behaviour/better-implementation for algorithms; document original behaviour, bugs, edge cases.
4. **Security rule.** Fix vulnerabilities **without removing the capability** behind them. Never hard-code or commit secrets; never carry legacy credentials, personal data, uploads or biometric data; no raw SQL string building; no dynamic code execution; CSRF on every state-changing request; never trust client input.
5. **Investigation rule.** Understand why a subsystem exists before modifying it: read its code, neighbours, git history (`EmirTheBest7/aev.platforms`), the owner's notes (`antitup/*.md`, release notes in `page/updates`, `home/_api/Docs/mds`), and sibling repos. Use evidence; when the purpose can't be established write **`Purpose unclear — requires owner review`** instead of guessing or deleting.
6. **Documentation rule.** Document significant changes in `docs/CHANGE-LOG.md` (original behaviour → problem → new implementation → reason → visual impact → functional impact) and update the affected doc (`ARCHITECTURE`, `ROUTES`, `URL-MIGRATION`, `SECURITY`, `DESIGN-SYSTEM`, `MAIN-PAGE`, `MIGRATION`, `HISTORICAL-FEATURES`…) in the same change.
7. **Testing rule.** Test *behaviour*, not just that a page loads: exercise controls in a real browser (Playwright, `scripts/visual/`), at desktop 1440 / tablet 820 / phone 390 / phone-landscape 844×390, compare against the legacy baseline (`docs/visual-baseline/`), check the console, failure modes (offline third party, no WebGL) and keyboard use. Every behaviour change ships with an automated test.

## Classifying problems (use these words)

`BROKEN` (exists, doesn't work → fix) · `INCOMPLETE` (started/announced → preserve, complete without changing the concept) · `LEGACY IMPLEMENTATION` (works, code should improve → rewrite internally) · `EXPERIMENTAL` (preserve, document) · `OBSOLETE` (external dependency gone → investigate alternatives before removing anything) · `DUPLICATE` (document; defer deletion unless clearly safe) · `SECURITY RISK` (fix the implementation now; keep the feature). The inventory is `docs/history/HISTORICAL-FEATURES.md`.

## Architecture (full detail in `docs/ARCHITECTURE.md`)

PHP **8.3+**, no framework, Composer PSR-4 (`App\` → `app/`, `Core\Auth\` → `core/auth/`). Web root is **`public/`**. Minimal MVC: `route → controller → (model | service) → view`. `app/Application.php` is the only wiring point (explicit, no container). Layers: `app/{Http,Controllers,Models,Services,Security,Validation,Support,Views}`, `config/`, `routes/`, `database/{migrations,seeds}/`, `storage/` (runtime, not committed), `resources/` (not served), `docker/`, `scripts/`, `tests/{Unit,Feature}`, `docs/`. `core/auth` is the account system — **reuse it**; one session manager and one CSRF implementation, built once in `Application`. Do not add an abstraction without a second consumer.

## Coding standards

- **PHP:** `declare(strict_types=1)`; PER-CS 2.0 (`php-cs-fixer`); PHPStan level 8 clean (`app/Views` templates are excluded; they're covered by feature tests); typed code; `final` by default; constructor injection with explicit wiring in `Application`; no globals.
- **Templates:** every dynamic value through `$e()`. No inline `<script>`, `<style>`, `style=""`, `on*=` — the CSP forbids them (legacy inline styles are extracted into generated utility classes).
- **JavaScript:** keep the original behaviour. The main page runs the original jQuery-based scripts (self-hosted, single copy) plus `assets/js/home/app.js`; wire behaviour through `data-*` hooks and delegation, never inline handlers. Every optional feature starts inside its own guard so it can never block the page. New code: defensive DOM access, keyboard access, `prefers-reduced-motion`.
- **CSS:** reuse the legacy classes/tokens (`public/assets/css/{core,main}.css` are ported with mechanical edits only — see their headers). Additions of the port live in `shell.css` (accessibility/plumbing, button resets); extracted inline styles are the generated `utilities.css`.
- **HTML:** semantic landmarks, labelled controls, real `<button>`/`<a>` (never `href="#"` as a control), meaningful `alt`.
- **Naming:** `PascalCase` classes, `camelCase` methods, BEM-ish CSS, lowercase-kebab routes.

## Brand and fonts

- Logos in `public/assets/brand/` are byte-identical copies from the legacy `page/downloads/logo/`. Never redraw/recolour/re-export. Verify with `cmp`.
- Fonts: the legacy tree holds `Ndot-55.otf` (used in Settings/Widgets; **licence restricts it to Nothing's brand materials**), `DotlineBold.ttf` (unreferenced, licence unknown) and `SF-Pro.ttf` (committed on purpose; Apple licence forbids web self-hosting). **None is deleted.** v2 currently renders the display role with **Doto (SIL OFL)** as an interim; whether to keep, license or replace Ndot is the owner's decision (`docs/DESIGN-SYSTEM.md`).
- The identity is dark; the legacy home page has no light skin. Do not invent one. The repository description mentions "Liquid Glass UI/UX" — intent unclear, ask before adding any glass effect.

## Security specifics

Secrets only via environment (`.env.example` placeholders). Treat every credential in the legacy tree as compromised and never copy it (`SECRET_EXPOSURE_01…09` in `docs/history/AUDIT.md`). Third-party scripts only when the owner wants the integration (Intergram: self-hosted pinned copy, `disableLoadmill: true`); never load scripts that can redirect or script visitors remotely (Web4Ukraine). Strict CSP is the default; each exception is documented in `docs/SECURITY.md`.

## URL rules

Preserve public URLs. Legacy → new mapping lives in `routes/legacy.php` + `docs/URL-MIGRATION.md`. App routes have no trailing slash; the static `/home/_api/` bundle keeps it. Single-hop 301 only. **Do not return 410 for work that is being preserved** — unbuilt paths return 404 until rebuilt. No trailing-slash duplicates.

## Deployment

Docker-first: `Dockerfile` at the repository root (`prod` stage), `php:8.3-apache` with **document root `public/`** (Apache master runs as root to bind the port, workers as `www-data`), MariaDB for accounts/jobs, persistent `storage/`, config via environment. `docker compose up --build`. `docs/DEPLOYMENT.md`.

## Git

Work on a branch; coherent commits (`docs:`, `refactor:`, `security:`, `fix:`, `feat:`, `test:`). Never commit `.env`, secrets, `storage/`, `vendor/`, caches, `.DS_Store`, user data, or the legacy tree. `composer.lock` is committed.

## Commands

```bash
docker compose run --rm app vendor/bin/phpunit
docker compose run --rm app vendor/bin/phpstan analyse --memory-limit=512M
docker compose run --rm app vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes
scripts/visual/legacy-baseline.sh up   # sanitised legacy copy for visual comparison (never submit its forms)
```

## Open owner decisions (do not assume)

See `docs/ARCHITECTURE.md` §14: the remaining Works-slider cards, the unregistered `aliev.io` domain, credentials to revoke, content the owner must provide. Also open: whether to keep/licence Ndot-55, the "Functional key" intent, destinations for unset social links, Intergram chat ID, SMTP for password reset, history purge of the public legacy repo.

## Verification rule

PHPUnit green is not "working". After any UI change open the page in a real browser at 1440×900, 820×1180, 390×844 and 844×390 (`scripts/visual/states.mjs`; baseline via `scripts/visual/legacy-baseline.sh`), check console, failed requests and horizontal overflow, and exercise the controls.
