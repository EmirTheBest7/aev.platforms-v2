# Changelog

## Unreleased — revival branch `revive/legacy-audit-and-rebuild`

### Added
- Phase 1: legacy audit (`docs/AUDIT.md`, `APPLICATIONS.md`, `WIDGETS.md`, `URL-MIGRATION.md`, `MIGRATION.md`).
- Phase 2: application skeleton (`app/`, `config/`, `routes/`, `public/`), secure contact/lead flow, `Notifier` service, security headers, rate limiter, structured logger, Docker (dev + prod) with nginx/Apache rules, Composer/PHPUnit/PHPStan/CS-Fixer configuration, 44 automated tests, `CLAUDE.md`, documentation set.
- Phase 3: design-system and brand-asset extraction (`DESIGN-SYSTEM.md`, `BRAND-ASSETS.md`), legacy visual baseline (`docs/visual-baseline/`, `scripts/visual/`), logos/icons copied byte-for-byte, **Doto** (OFL) replaces Ndot-55.

### Changed
- CI now builds the production image from `docker/Dockerfile`; `composer.lock` is committed (was ignored).

### Fixed
- `Core\Auth\` autoloading on case-sensitive filesystems (Composer PSR-4 `core/auth/`).

### Removed / not migrated
See `docs/LEGACY-REMOVAL.md`.
