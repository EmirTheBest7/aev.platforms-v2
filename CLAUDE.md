# CLAUDE.md — ALIEV.IO

Permanent instructions for Claude Code sessions in this repository. Read this first, then `docs/PROJECT-VISION.md` (why the project is what it is) and `docs/ARCHITECTURE.md` (how v2 is built).

## Project identity

ALIEV.IO ("ΛΞV", "Λ L I Ξ V Platforms") is a **long-term personal project of its owner**, built over years: an **ecosystem** — a public face (agency/studio, lead generation, careers, investors, events), a registered-user platform (accounts, Space social feed, Studio, Messenger, Videos, Wallet/Store), a developer layer (terminal tools, Docs, API keys, the HesterGPT AI assistant) and user hosting. This repository (v2) **revives** it; it is not a redesign and not "an agency website". The historical reference is `./aev.platforms-master` (git-ignored, read-only; public mirror `EmirTheBest7/aev.platforms`).

Revival goal: **preserve the project and its identity; improve the engineering underneath.** Same experience, better engine.

## The seven standing rules

1. **ALIEV.IO preservation rule.** Never redesign the existing visual identity (logo, colours, typography treatment, spacing, navigation, animations, panels, launcher, globe, spotlight, ticker, chat, settings, interaction patterns) without explicit owner instruction. Do not substitute a generic agency/SaaS template, new palette, new type scale, Bootstrap/Tailwind/Material defaults.
2. **Historical preservation rule.** Do not delete features, pages, widgets, experiments, assets, fonts, icons, integrations or launcher entries because they are old, verbose, unconventional, unused-looking or "you'd do it differently". Default is **KEEP**. If something looks useless mark it `POSSIBLE DEPRECATION` with the reason and ask. Delete only if it is demonstrably accidental, a proven duplicate, malicious, contains sensitive material that must not remain, technically dangerous — or the owner explicitly approves. Record every such decision in `docs/CHANGE-LOG.md`.
3. **Functional modernization rule.** Repair or rewrite *internals* while preserving *intended behaviour*. Separate **idea** from **implementation**: an insecure login is replaced by a secure login, the account feature stays. Prefer same-behaviour/better-implementation for algorithms; document original behaviour, bugs, edge cases.
4. **Security rule.** Fix vulnerabilities **without removing the capability** behind them. Never hard-code or commit secrets; never carry legacy credentials, personal data, uploads or biometric data; no raw SQL string building; no dynamic code execution; CSRF on every state-changing request; never trust client input.
5. **Investigation rule.** Understand why a subsystem exists before modifying it: read its code, neighbours, git history (`EmirTheBest7/aev.platforms`), the owner's notes (`antitup/*.md`, release notes in `page/updates`, `home/_api/Docs/mds`), and sibling repos. Use evidence; when the purpose can't be established write **`Purpose unclear — requires owner review`** instead of guessing or deleting.
6. **Documentation rule.** Document significant changes in `docs/CHANGE-LOG.md` (original behaviour → problem → new implementation → reason → visual impact → functional impact) and update the affected doc (`ARCHITECTURE`, `ROUTES`, `URL-MIGRATION`, `SECURITY`, `DESIGN-SYSTEM`, `MAIN-PAGE`, `MIGRATION`, `HISTORICAL-FEATURES`…) in the same change.
7. **Testing rule.** Test *behaviour*, not just that a page loads: exercise controls in a real browser (Playwright, `scripts/visual/`), at desktop 1440 / tablet 820 / phone 390 / phone-landscape 844×390, compare against the legacy baseline (`docs/visual-baseline/`), check the console, failure modes (offline third party, no WebGL) and keyboard use. Every behaviour change ships with an automated test.

## Classifying problems (use these words)

`BROKEN` (exists, doesn't work → fix) · `INCOMPLETE` (started/announced → preserve, complete without changing the concept) · `LEGACY IMPLEMENTATION` (works, code should improve → rewrite internally) · `EXPERIMENTAL` (preserve, document) · `OBSOLETE` (external dependency gone → investigate alternatives before removing anything) · `DUPLICATE` (document; defer deletion unless clearly safe) · `SECURITY RISK` (fix the implementation now; keep the feature). The inventory is `docs/HISTORICAL-FEATURES.md`.

## Architecture (full detail in `docs/ARCHITECTURE.md`)

PHP **8.3+**, no framework, Composer PSR-4 (`App\` → `app/`, `Core\Auth\` → `core/auth/`). Web root is **`public/`**. `app/Application.php` is the composition root: `app/Http` (Request/Response/Router), `app/Controllers`, `app/Services` (Notifier, LeadStore), `app/Security`, `app/Validation`, `app/Support`, `app/Views` (plain-PHP templates; shell partials in `partials/shell`), `config/`, `routes/`, `storage/` (runtime, not committed), `tests/`, `docker/`. New code follows `route → controller → service → repository → view`. `core/auth` is the account system — **reuse it**, don't write a second session/CSRF implementation. The v2 README's module plan (`apps/{social,messenger,forum,store,wallet,studio,terminal}`, `api/*`) is the legacy platform re-planned: follow it as apps return.

## Coding standards

- **PHP:** `declare(strict_types=1)`; PER-CS 2.0 (`php-cs-fixer`); PHPStan level 8 clean (`app/Views` templates are excluded; they're covered by feature tests); typed code; `final` by default; constructor injection with explicit wiring in `Application`; no globals.
- **Templates:** every dynamic value through `$e()`. No inline `<script>`, `<style>`, `style=""`, `on*=` — the CSP forbids them (legacy inline styles are extracted into generated utility classes).
- **JavaScript:** native ES modules, defensive DOM access, event delegation, listener/animation cleanup, keyboard access, `prefers-reduced-motion`. jQuery is not required; if used to preserve exact legacy behaviour it must be a single self-hosted copy.
- **CSS:** reuse the legacy classes/tokens (`public/assets/css/{core,main}.css` are ported with mechanical edits only — see their headers); tokens in `site.css` for new pages.
- **HTML:** semantic landmarks, labelled controls, real `<button>`/`<a>` (never `href="#"` as a control), meaningful `alt`.
- **Naming:** `PascalCase` classes, `camelCase` methods, BEM-ish CSS, lowercase-kebab routes.

## Brand and fonts

- Logos in `public/assets/brand/` are byte-identical copies from the legacy `page/downloads/logo/`. Never redraw/recolour/re-export. Verify with `cmp`.
- Fonts: the legacy tree holds `Ndot-55.otf` (used in Settings/Widgets; **licence restricts it to Nothing's brand materials**), `DotlineBold.ttf` (unreferenced, licence unknown) and `SF-Pro.ttf` (committed on purpose; Apple licence forbids web self-hosting). **None is deleted.** v2 currently renders the display role with **Doto (SIL OFL)** as an interim; whether to keep, license or replace Ndot is the owner's decision (`docs/DESIGN-SYSTEM.md`).
- The identity is dark; the legacy home page has no light skin. Do not invent one. The repository description mentions "Liquid Glass UI/UX" — intent unclear, ask before adding any glass effect.

## Security specifics

Secrets only via environment (`.env.example` placeholders). Treat every credential in the legacy tree as compromised and never copy it (`SECRET_EXPOSURE_01…09` in `docs/AUDIT.md`). Third-party scripts only when the owner wants the integration (Intergram: self-hosted pinned copy, `disableLoadmill: true`); never load scripts that can redirect or script visitors remotely (Web4Ukraine). Strict CSP is the default; each exception is documented in `docs/SECURITY.md`.

## URL rules

Preserve public URLs. Legacy → new mapping lives in `routes/legacy.php` + `docs/URL-MIGRATION.md`. Single-hop 301 only. **Do not return 410 for work that is being preserved** — unbuilt paths return 404 until rebuilt. No trailing-slash duplicates.

## Deployment

Docker-first, host-agnostic: image from `docker/Dockerfile` (`prod` stage), nginx or Apache with **document root `public/`**, PHP 8.3+, non-root, persistent `storage/`, config via environment. `docs/DEPLOYMENT.md`.

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

See `docs/PROJECT-VISION.md` §10 and `docs/MIGRATION.md` "Decisions": which platform apps return first; fonts; Liquid Glass; destinations for Docs/Finance/Maps/social links; `AEVT`/`AEVD` price sources; Intergram target; "Functional key" intent; whether `/account` is exposed; history purge of the public legacy repo.
