# Deployment

Docker-first, provider-agnostic. Nothing here assumes a specific host.

## Requirements

PHP **8.3+** (`curl`, `mbstring`, `json`, `opcache`); nginx or Apache; HTTPS terminated at the web server or a reverse proxy; persistent writable `storage/`.

## Build the production image

```bash
docker build -f docker/Dockerfile -t aliev-platform:$(git rev-parse --short HEAD) .
```

The last stage (`prod`) contains only `app/ core/ config/ routes/ public/ vendor/` (no tests, no dev dependencies, no docs), runs as `www-data`, and creates `storage/{logs,ratelimit,leads,cache}`. Mount a persistent volume at `/var/www/html/storage`.

## Run (php-fpm + nginx)

Use `docker/nginx/default.conf` as the nginx site (document root `public/`, dotfile and non-entry `.php` blocking, 64 KB body limit). Example compose fragment:

```yaml
services:
  app:
    image: aliev-platform:<tag>
    environment: { APP_ENV: production, APP_URL: https://aliev.io, APP_KEY: "${APP_KEY}", TRUSTED_PROXIES: "10.0.0.0/8", NOTIFY_DRIVER: telegram, TELEGRAM_BOT_TOKEN: "${TELEGRAM_BOT_TOKEN}", TELEGRAM_CHAT_ID: "${TELEGRAM_CHAT_ID}", LOG_CHANNEL: stderr }
    volumes: [ "aliev-storage:/var/www/html/storage" ]
  web:
    image: nginx:1.27-alpine
    volumes: [ "./public:/var/www/html/public:ro", "./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro" ]
    ports: [ "80:80" ]
volumes: { aliev-storage: {} }
```

## Plain Apache/PHP hosting (no Docker)

Point the vhost document root at `public/` (preferred). `public/.htaccess` supplies the front controller and blocks dotfiles/other `.php`. Run `composer install --no-dev --optimize-autoloader` on the build machine and upload the result. If the host cannot change the document root, **do not** upload the repository root as-is; deploy only the contents of `public/` as the root and place the rest one level above it, adjusting the path in `public/index.php`.

## Configuration (environment)

See `.env.example`. Required in production: `APP_ENV=production`, `APP_URL` (https), `APP_KEY` (≥ 32 random characters — `php scripts/generate-key.php`). Optional: `TRUSTED_PROXIES`, `NOTIFY_DRIVER=telegram` with `TELEGRAM_BOT_TOKEN`/`TELEGRAM_CHAT_ID`, `LOG_CHANNEL`, `LOG_LEVEL`. Secrets live in the platform's secret store, never in the image or repository. **Use freshly created Telegram credentials; the legacy ones are compromised.**

## Release checklist

1. CI green (lint, composer validate, PHPUnit, PHPStan, CS, Docker build).
2. `APP_ENV=production`, `APP_DEBUG` unset/false, HTTPS in place (HSTS is emitted automatically for https `APP_URL` in production).
3. `storage/` writable by the PHP user and on a persistent volume; not web-reachable.
4. Smoke tests: `GET /` 200, `GET /contact` 200 and form submission end-to-end, `/nope` 404, `/.env` 404, `/home/auth` 404 (unbuilt legacy paths are never 410), security headers present.
5. Rotate/restrict credentials if anything was exposed.

## Rollback

Redeploy the previous image tag. `storage/` is append-only data and is not affected by a code rollback.
