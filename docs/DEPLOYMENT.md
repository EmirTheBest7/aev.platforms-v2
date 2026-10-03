# Deployment

## What runs

One image (`Dockerfile`, target `prod`): `php:8.3-apache` with `pdo_mysql` and `opcache`, the Apache vhost from
`docker/apache/`, document root `public/`. Plus MariaDB (or any MySQL-compatible server) for accounts and job
postings. Apache's master process runs as root so it can bind the port; the worker processes run as `www-data`.
Terminate TLS in front of it (reverse proxy / load balancer) and set `TRUSTED_PROXIES` to that proxy's address.

## Build and run

```bash
docker build --target prod -t aliev-site:$(git rev-parse --short HEAD) .
docker run -d --name aliev -p 8080:80 --env-file production.env -v aliev-storage:/var/www/html/storage aliev-site:<tag>
```

`production.env` (not in the repository) holds the variables from `.env.example`. Mount `storage/` on a persistent
volume. Apply migrations once per release with `docker exec aliev php scripts/migrate.php`
(or set `AUTO_MIGRATE=true` for the container).

`docker/nginx.example.conf` documents the equivalent nginx + php-fpm setup for hosts that prefer it; it is not used by
the provided image.

## Plain Apache/PHP hosting (no Docker)

`public/build/` and `public/home/_api/` are generated: run `php scripts/build.php` on the build machine (the prod image and the container entrypoint do it for you).

Point the vhost document root at `public/` (preferred). `public/.htaccess` supplies the front controller and blocks dotfiles/other `.php`. Run `composer install --no-dev --optimize-autoloader` on the build machine and upload the result. If the host cannot change the document root, **do not** upload the repository root as-is; deploy only the contents of `public/` as the root and place the rest one level above it, adjusting the path in `public/index.php`.

## Configuration (environment)

See `.env.example`. Required in production: `APP_ENV=production`, `APP_URL` (https), `APP_KEY` (≥ 32 random characters — `php scripts/generate-key.php`), and the database settings `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Required before launch: `LEGAL_OPERATOR_NAME`, `LEGAL_OPERATOR_ADDRESS`, `LEGAL_REGISTRATION_ID` (Imprint, `docs/LEGAL.md`). Required when TLS is terminated by a proxy: `TRUSTED_PROXIES` (otherwise `FORCE_HTTPS` redirects forever). Optional: `TRUSTED_PROXIES`, `NOTIFY_DRIVER=telegram` with `TELEGRAM_BOT_TOKEN`/`TELEGRAM_CHAT_ID`, `LOG_CHANNEL`, `LOG_LEVEL`. Secrets live in the platform's secret store, never in the image or repository. **Use freshly created Telegram credentials; the legacy ones are compromised.**

## Release checklist

1. CI green (lint, composer validate, PHPUnit, PHPStan, CS, Docker build).
2. `APP_ENV=production`, `APP_DEBUG` unset/false, HTTPS in place (HSTS is emitted automatically for https `APP_URL` in production).
3. `storage/` writable by the PHP user and on a persistent volume; not web-reachable.
4. Smoke tests: `GET /` 200, `GET /contact` 200 and form submission end-to-end, `/nope` 404, `/.env` 404, `/does-not-exist` 404 (unbuilt legacy paths are never 410), security headers present.
5. Rotate/restrict credentials if anything was exposed.

## Rollback

Redeploy the previous image tag. `storage/` is append-only data and is not affected by a code rollback.
