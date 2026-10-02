
<p align="center">
  <img src="https://github.com/user-attachments/assets/e18ad66f-8af5-4b0b-af63-995c4494b4b2"  alt="ALIEV.IO" width="120"> v2

</p>

<p align="center">
  <strong>// One digital platform to rule them all. //</strong>
</p>

<p align="center">
  Build. Automate. Deploy. Scale.
</p>

<p align="center">
  <a href="https://docs.aliev.io"><strong>Documentation</strong></a>
  ·
  <a href="https://aliev.io">Website</a>
  ·
  <a href="https://github.com/EmirTheBest7/aliev.io/discussions">Community</a>
  ·
  <a href="https://github.com/EmirTheBest7/aliev.io/issues">Issues</a>
</p>

---


## What this is

ALIEV.IO is a long-running personal project: an **ecosystem** with a public face (studio, lead generation, careers, investors, events), a registered-user platform (accounts, Space, Studio, Messenger, Videos, Wallet, Store), a developer layer (terminal tools, Docs, API keys, the HesterGPT assistant) and user hosting. This repository (v2) is its **revival**: the original experience and identity are preserved and the engineering underneath is rebuilt securely.

Start with [`docs/PROJECT-VISION.md`](docs/PROJECT-VISION.md) (why the project is what it is) and [`docs/HISTORICAL-FEATURES.md`](docs/HISTORICAL-FEATURES.md) (everything it had). The original is kept, read-only, as `aev.platforms-master` (git-ignored; public mirror `EmirTheBest7/aev.platforms`).

## Current state

| Area | State |
|---|---|
| Foundation | PHP 8.3 front controller in `public/`, routing, security headers (strict CSP), Notifier, structured logging, secure contact flow, Docker (dev + prod), CI — **done, 44 tests, PHPStan level 8** |
| Account system | `core/auth` (PDO, Argon2id, CSRF, sessions, lockout, roles) + `apps/account` test pages |
| Main page (`page/main`) | Audited and mapped (`docs/MAIN-PAGE.md`); assets/CSS staged; **port in progress** |
| Other public pages, platform apps, terminal, docs, hosting | Inventoried (`docs/HISTORICAL-FEATURES.md`); scheduled in `docs/MIGRATION.md` |

## Run locally

```bash
docker compose build
docker compose run --rm app composer install
docker compose up -d          # http://localhost:8080  (WEB_PORT=8088 docker compose up -d to change the port)
```

Configuration is environment-based; see `.env.example` (placeholders only — never commit `.env`). Generate a key with `php scripts/generate-key.php`.

## Quality

```bash
docker compose run --rm app vendor/bin/phpunit
docker compose run --rm app vendor/bin/phpstan analyse --memory-limit=512M
docker compose run --rm app vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes
```

Visual comparison with the original: `scripts/visual/legacy-baseline.sh` serves a sanitised copy of the legacy site (never submit its forms) and `scripts/visual/shoot.mjs` captures screenshots; reference images are in `docs/visual-baseline/`.

## Deployment

Docker-first and host-agnostic: build `docker/Dockerfile` (final stage `prod`), serve with nginx or Apache using **`public/` as the document root**, PHP 8.3+, persistent `storage/`, secrets via environment. See [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

## Documentation

| Topic | Document |
|---|---|
| History, vision, inventory | `PROJECT-VISION`, `HISTORICAL-FEATURES`, `AUDIT` |
| Main page | `MAIN-PAGE`, `APPLICATIONS`, `WIDGETS` |
| Design | `DESIGN-SYSTEM`, `BRAND-ASSETS` |
| Engineering | `ARCHITECTURE`, `ROUTES`, `URL-MIGRATION`, `DEVELOPMENT`, `TESTING`, `OPERATIONS`, `DEPLOYMENT` |
| Security | `SECURITY` (incl. owner actions and the history-purge task) |
| Migration | `MIGRATION`, `LEGACY-REMOVAL` (ledger — nothing is deleted), `CHANGE-LOG` |

## Contributing

Read [`CLAUDE.md`](CLAUDE.md): the standing rules apply to humans too — **preserve the identity, don't delete things because they are old, fix security without removing capability, understand before changing, document, test behaviour.**

## Target architecture (owner's plan)

```
aliev.io/

├── apps/                       # Main user-facing platform modules
│   ├── social/                 # Profiles, timeline, posts, communities
│   ├── messenger/              # Private messages and communication
│   ├── forum/                  # Discussions and communities
│   ├── store/                  # Marketplace and products
│   ├── wallet/                 # Payments and user finances
│   ├── studio/                 # User content creation
│   └── terminal/               # Developer playground and mini-app launcher
│
├── core/                       # Shared platform engine
│   ├── auth/                   # Authentication
│   ├── users/                  # User management
│   ├── database/               # Database layer
│   ├── security/               # Security services
│   ├── routing/                # Application routing
│   ├── permissions/            # Roles and access control
│   ├── validation/             # Input validation
│   ├── cache/                  # Cache system
│   ├── logging/                # Logs
│   └── helpers/                # Shared utilities
│
├── api/                        # Backend communication layer
│   ├── v2/                     # Public API version 2
│   ├── internal/               # Internal services
│   └── terminal/               # Terminal app API
│
├── website/                    # Public company pages
│   ├── home/                   # Landing page
│   ├── careers/                # Jobs
│   ├── contact/                # Contact pages
│   ├── legal/                  # Legal documents
│   ├── investors/              # Investor information
│   └── downloads/              # Public downloads
│
├── resources/                  # Shared frontend resources
│   ├── views/                  # Templates
│   ├── css/                    # Styles
│   ├── js/                     # JavaScript
│   ├── images/                 # Shared images
│   ├── icons/                  # Icons
│   ├── fonts/                  # Fonts
│   └── languages/              # Translations
│
├── public/                     # Public web root
│   └── build/                  # Compiled frontend files
│
├── storage/                    # Runtime data (not committed)
│   ├── uploads/                # User files
│   ├── cache/
│   ├── sessions/
│   ├── logs/
│   └── temporary/
│
├── database/
│   ├── migrations/
│   ├── seeds/
│   └── schema/
│
├── config/                     # Configuration files
│
├── scripts/                    # Automation
│   ├── cron/
│   ├── maintenance/
│   └── deployment/
│
├── tests/                      # Automated testing
│
├── docker/                     # Development environment
│
├── docs/                       # Documentation
│   ├── architecture.md
│   ├── database.md
│   ├── security.md
│   ├── api.md
│   └── decisions/
│
├── .github/                    # GitHub automation
│   └── workflows/
│
├── README.md
├── CLAUDE.md
├── CONTRIBUTING.md
├── SECURITY.md
├── CHANGELOG.md
└── .gitignore
```
