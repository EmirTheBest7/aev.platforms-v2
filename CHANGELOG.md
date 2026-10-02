# Changelog

## Unreleased — revival branch `revive/legacy-audit-and-rebuild`

Rationale for each change: `docs/CHANGE-LOG.md`.

### Structure
- Canonical layout (`core/`, `website/`, `api/`, `resources/`), build step to `public/build`, shared navbar/head/form components; see `docs/ARCHITECTURE.md` and `docs/CHANGE-LOG.md` §G.

### Understanding phase (archaeology first)
- `docs/PROJECT-VISION.md`, `docs/HISTORICAL-FEATURES.md` added; `AUDIT`, `APPLICATIONS`, `WIDGETS`, `LEGACY-REMOVAL` (now a ledger — nothing deleted), `URL-MIGRATION`, `MIGRATION`, `DESIGN-SYSTEM`, `SECURITY`, `CLAUDE.md`, `README.md` rewritten around "preserve the project; fix the engineering"; `docs/MAIN-PAGE.md` added (dependency map, feature trace, integration plan).
- New security findings recorded: `SECRET_EXPOSURE_07` (Gemini key), `_08` (Valentine page bot token), `_09`, SQL injection in `careers/desc`, Web4Ukraine remote redirector, Intergram hidden tracker.
- Ticker data source found broken (CryptoCompare HTTP 401).

### Changed
- Legacy routes no longer answer 410 for preserved work; unbuilt paths return 404.

### Added
- Phase 1: legacy audit (`docs/AUDIT.md`, `APPLICATIONS.md`, `WIDGETS.md`, `URL-MIGRATION.md`, `MIGRATION.md`).
- Phase 2: application skeleton (`config/`, `routes/`, `public/`), secure contact/lead flow, `Notifier` service, security headers, rate limiter, structured logger, Docker (dev + prod) with nginx/Apache rules, Composer/PHPUnit/PHPStan/CS-Fixer configuration, 44 automated tests, `CLAUDE.md`, documentation set.
- Phase 3: design-system and brand-asset extraction (`DESIGN-SYSTEM.md`, `BRAND-ASSETS.md`), legacy visual baseline (`docs/visual-baseline/`, `scripts/visual/`), logos/icons copied byte-for-byte, **Doto** (OFL) used as an interim stand-in for Ndot-55 (Ndot stays in the legacy tree; the owner decides).

### Changed
- CI now builds the production image from `docker/Dockerfile`; `composer.lock` is committed (was ignored).

### Fixed
- `Core\Auth\` autoloading on case-sensitive filesystems (Composer PSR-4 `core/auth/`).

### Removed / not migrated
See `docs/LEGACY-REMOVAL.md`.
