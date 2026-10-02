# Development

## Prerequisites

Docker with Compose v2. PHP and Composer run in the container; Node is needed only for the optional browser
tooling in `scripts/visual/`.

## First run

```bash
docker compose up --build            # http://localhost:8080   (WEB_PORT=8088 docker compose up --build to change the port)
```

Two services: `app` (PHP 8.3 + Apache, the repository bind-mounted, document root `public/`) and `db` (MariaDB 11,
not published to the host). On start the entrypoint runs `composer install` (if `vendor/` is missing) and applies
`database/migrations/*.sql` (idempotent, tracked in `schema_migrations`). `compose.yaml` sets `APP_ENV=local`, a
**development-only** `APP_KEY` and database password, `NOTIFY_DRIVER=log` and `LOG_CHANNEL=stderr`
(`docker compose logs -f app`). For persistent local settings copy `.env.example` to `.env`; generate a key with
`docker compose exec app php scripts/generate-key.php`.

`docker compose down -v` also deletes the database volume.

## Everyday commands

```bash
docker compose exec app vendor/bin/phpunit
docker compose exec app vendor/bin/phpstan analyse --memory-limit=512M
docker compose exec app vendor/bin/php-cs-fixer fix --allow-risky=yes        # apply style
docker compose exec app php scripts/migrate.php                              # re-run migrations
```

## Browser checks (required for UI work)

```bash
cd scripts/visual && npm i
node states.mjs http://localhost:8080 / /tmp/shots     # closed · menu · settings · launcher · profile at 4 viewports
```

The report lists horizontal overflow, console errors and failed requests per viewport. To compare with the
original, `scripts/visual/legacy-baseline.sh up` serves a sanitised copy of `./aev-new` on port 8099
(`node states.mjs http://localhost:8099 /page/main/ /tmp/old`). Never submit the baseline's forms.

## Adding a page

1. Route in `routes/web.php` → controller method returning a `Response`
   (`View::render('pages/x', $data, ['title' => …, 'description' => …, 'path' => '/x'], layout)`).
2. Template in `app/Views/pages/` (escape with `$e()`; no inline script/style/handlers).
3. Page-specific CSS/JS under `public/assets/`; reference first-party files through `$view->asset('/assets/…')`.
4. Historical URL (if any) in `routes/legacy.php`.
5. Feature test in `tests/Feature`, then check it in the browser.

## Adding JS behaviour

Wire behaviour with `data-*` hooks and event delegation. Start every optional feature inside its own guard so it
cannot block the page. Main-page scripts are loaded in a fixed order from `app/Views/layouts/shell.php`.

## Conventions

See `CLAUDE.md` (coding standards and security rules apply to humans too) and `docs/ARCHITECTURE.md`.
