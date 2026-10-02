# Development

## Prerequisites

Docker (Compose v2). Nothing else is required locally — PHP and Composer run in containers.

## First run

```bash
docker compose build
docker compose run --rm app composer install
docker compose up -d                 # http://localhost:8080  (WEB_PORT=8088 docker compose up -d to change the port)
```

`compose.yaml` sets `APP_ENV=local`, a **development-only** `APP_KEY` default, `NOTIFY_DRIVER=log` and `LOG_CHANNEL=stderr` (see output with `docker compose logs -f app`). For a persistent local configuration copy `.env.example` to `.env` and generate a key: `docker compose run --rm app php scripts/generate-key.php`.

## Everyday commands

```bash
docker compose run --rm app vendor/bin/phpunit
docker compose run --rm app vendor/bin/phpstan analyse --memory-limit=512M
docker compose run --rm app vendor/bin/php-cs-fixer fix --allow-risky=yes        # apply style
```

## Adding a page

1. Controller method returning `Response` (use `View::render('pages/x', $data, ['title'=>…, 'description'=>…, 'path'=>'/x'])`).
2. Template in `app/Views/pages/x.php` (escape with `$e()`; no inline script/style).
3. Route in `routes/web.php`; legacy redirect (if any) in `routes/legacy.php`.
4. Add to `docs/ROUTES.md`, `docs/URL-MIGRATION.md` and the sitemap; add a feature test.

## Adding a JS behaviour

New file in `public/assets/js/`, loaded with `<script type="module" src=…>` from the layout or page. No inline scripts (CSP). Guard every DOM lookup; clean up listeners/animation frames; honour `prefers-reduced-motion`.

## Conventions

See `CLAUDE.md` (coding standards and security rules apply to humans too).
