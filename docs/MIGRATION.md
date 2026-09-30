# Migration plan

Source of truth for the old design: `./aev.platforms-master` (git-ignored, never copied into the production tree).
Work branch: `revive/legacy-audit-and-rebuild`.

| Phase | Output | Status |
|---|---|---|
| 1 Discovery | `AUDIT.md`, `APPLICATIONS.md`, `WIDGETS.md`, `URL-MIGRATION.md` (draft) | **Done** |
| 2 Architecture | `ARCHITECTURE.md`, `CLAUDE.md`, `.env.example`, skeleton, `composer.json` + CI configs | Blocked on decisions below |
| 3 Brand & design extraction | `DESIGN-SYSTEM.md`, `BRAND-ASSETS.md`, visual baselines | Needs Docker/PHP baseline run |
| 4 Safe migration | homepage + pages on new stack, contact flow, notifier | |
| 5 Functional repair | spotlight, launcher, globe, forms, PWA | |
| 6 Legacy cleanup | `LEGACY-REMOVAL.md` | |
| 7 QA | PHPUnit + browser checks + visual regression | |
| 8 Docs | `FINAL-AUDIT-REPORT.md`, `MIGRATION-SUMMARY.md` | |

## Decisions required before Phase 2

1. **Web root & hosting.** Proposed: `public/` document root (PHP 8.3+, Apache `.htaccess` fallback rewrite for hosts that can't change the root). Confirm the real host and deploy method (FTP/SSH/rsync/CI).
2. **Scope.** Proposed: agency website only; `home/*` platform not rebuilt.
3. **Ndot font** (license). Keep vs. replace with an OFL dot-matrix face.
4. **Launcher.** Approve the reduction in `APPLICATIONS.md`.
5. **Ticker / chat widget / web4ukraine** removal.
6. **Credential rotation** confirmed (DB, both Telegram tokens).
7. **Whether the old GitHub repo history should be purged** of PII and credentials (owner-only action).
