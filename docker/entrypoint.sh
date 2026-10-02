#!/bin/sh
# Container entrypoint.
#  - non-production: installs Composer dependencies on first start (source is bind-mounted)
#  - runs database migrations when APP_ENV=local, or when AUTO_MIGRATE=true (opt-in for other environments)
set -e
cd /var/www/html

if [ "${APP_ENV:-production}" != "production" ] && [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --no-progress
fi

if [ "${APP_ENV:-production}" = "local" ] || [ "${AUTO_MIGRATE:-}" = "true" ]; then
    php scripts/migrate.php || echo "WARNING: migrations did not complete" >&2
fi

php scripts/build.php

mkdir -p storage/logs storage/ratelimit storage/leads storage/cache
chown -R www-data:www-data storage 2>/dev/null || true

exec "$@"
