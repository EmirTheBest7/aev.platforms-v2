# Security Policy

## Supported Versions

While ALIEV.IO V2 is under active initial development, security fixes are
applied to the `main` branch and the most recent tagged release only.

| Version        | Supported          |
| -------------- | ------------------- |
| Latest release | :white_check_mark: |
| Older releases | :x:                 |

This table will be expanded with a formal support matrix once the project
reaches a stable 1.0 release.

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub
issues.**

Instead, please use GitHub's private vulnerability reporting feature:

1. Go to the **Security** tab of this repository.
2. Click **Report a vulnerability**.
3. Provide as much detail as possible:
   - A description of the vulnerability and its potential impact.
   - Steps to reproduce, or a proof-of-concept if available.
   - Affected version(s) or commit SHA.

Alternatively, if private reporting is unavailable to you, contact the
maintainers directly at: **security@aliev.io** *(placeholder — update once
a real security contact address exists)*.

## What to Expect

- **Acknowledgment:** We aim to acknowledge new reports within 72 hours.
- **Assessment:** We will investigate and aim to provide an initial
  assessment (severity, affected components) within 7 days.
- **Fix & Disclosure:** Once a fix is available, we will coordinate a
  disclosure timeline with the reporter. Credit will be given unless
  anonymity is requested.

## Scope

This policy covers the ALIEV.IO V2 codebase in this repository, including:

- `core/` — internal platform engine (including `core/auth/`)
- `api/` — API communication layer
- `apps/` — platform applications
- `database/` — database structure and migrations
- `.github/` — CI/CD pipelines and automation

Vulnerabilities in third-party dependencies should ideally be reported
upstream, but feel free to flag them here as well so we can track and
patch via Dependabot.
