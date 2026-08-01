# `.github/` — CI/CD & Repository Automation

This directory contains all GitHub Actions workflows, templates, and
repository automation for **ALIEV.IO V2**. It's designed to scale from an
early-stage project (no tests yet, no Docker yet) into a mature, production
deployment pipeline **without restructuring** — new capabilities are enabled
by adding config files the workflows already know how to detect, not by
rewriting YAML.

## How the CI pipeline is organized

```
workflows/
├── ci.yml                      # Orchestrator — the only workflow triggered by push/PR
├── release.yml                 # Tag push → GitHub Release (+ Docker publish)
├── codeql.yml                  # Security static analysis (scheduled + PR/push)
├── dependency-review.yml       # PR-time dependency vulnerability gate
├── labeler.yml                 # Auto-labels PRs by changed path
├── stale.yml                   # Closes inactive issues/PRs
│
└── _reusable-*.yml             # One file per concern, called via `workflow_call`
    ├── _reusable-php-lint.yml      # php -l syntax check (matrix: 8.3, 8.4)
    ├── _reusable-composer.yml      # composer validate + cached install
    ├── _reusable-phpunit.yml       # dormant until phpunit.xml(.dist) exists
    ├── _reusable-phpstan.yml       # dormant until phpstan.neon(.dist) exists
    ├── _reusable-cs-fixer.yml      # dormant until .php-cs-fixer(.dist).php exists
    ├── _reusable-docker-build.yml  # dormant until Dockerfile exists
    └── _reusable-deploy.yml        # placeholder pipeline, environment-gated
```

### Why reusable workflows instead of one big file?

Each `_reusable-*.yml` file owns exactly one concern (lint, tests, static
analysis, style, Docker, deploy). `ci.yml` and `release.yml` don't contain
any tooling logic themselves — they just call these reusable workflows with
`uses: ./.github/workflows/_reusable-X.yml`. This means:

- Adding a new check = add one job block to `ci.yml`, not touch a monolith.
- Changing how PHPUnit runs = edit `_reusable-phpunit.yml` once; both `ci.yml`
  and any future workflow that needs tests automatically get the update.
- Each file is small enough to read and understand in isolation.

### "Dormant" jobs — the auto-detection pattern

`_reusable-phpunit.yml`, `_reusable-phpstan.yml`, `_reusable-cs-fixer.yml`,
and `_reusable-docker-build.yml` are **already wired into `ci.yml`**, but
each one starts with a detection step (e.g. "does `phpunit.xml.dist`
exist?"). If the relevant config file isn't present yet, the job logs a
notice and exits successfully — it does not fail CI. This means:

- CI is green from day one, even before tests/static analysis/Docker exist.
- The moment you add `phpstan.neon.dist` to the repo root, for example,
  PHPStan starts running on the very next push — no workflow edits needed.

Each reusable workflow file has an **"Activation checklist"** comment block
at the top explaining exactly what to add to turn it on.

## Branch protection recommendation

Point your branch protection rule's "Require status checks to pass" at the
**`CI Summary`** job in `ci.yml`, not at individual jobs. `CI Summary`
aggregates every job's result (including dormant/skipped ones) into a single
required check, so adding or renaming jobs later never breaks branch
protection settings.

## Repository settings this directory assumes

A few things are *repository settings*, not workflow files — enable them
under **Settings → Code security**:

- **Secret scanning** + **Push protection** (blocks committed credentials)
- **Dependabot alerts** (works together with `dependabot.yml` here, which
  handles the *update* side; alerts are the *detection* side)
- **CodeQL** is already wired via `workflows/codeql.yml`, but confirm
  "Code scanning" shows results after the first run

## Deployment

`release.yml` creates GitHub Releases and (once a `Dockerfile` exists)
publishes images to GHCR automatically on every version tag. Actual
deployment (`_reusable-deploy.yml`) is intentionally left as a placeholder
— see its activation checklist for what to fill in once a hosting target
(SSH, Kubernetes, PaaS, etc.) is decided. It's pre-wired to use GitHub
Environments for approval gates, so production deploys can require manual
sign-off without any extra plumbing later.

## Other files in this directory

| File | Purpose |
|---|---|
| `CODEOWNERS` | Auto-requests review based on changed paths |
| `dependabot.yml` | Automated dependency + Action + Docker base image updates |
| `SECURITY.md` | Vulnerability disclosure policy |
| `PULL_REQUEST_TEMPLATE.md` | Structured PR description template |
| `ISSUE_TEMPLATE/*.yml` | Structured issue forms (bug/feature/question) |
| `labeler.yml` | Path-based auto-labeling rules (used by `workflows/labeler.yml`) |
| `FUNDING.yml` | Sponsor/funding links (placeholder, edit or remove) |
