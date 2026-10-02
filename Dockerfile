# syntax=docker/dockerfile:1.7
# ALIEV.IO — one image, two targets:  dev (tools, bind-mounted source)  |  prod (default final stage, runtime only)

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress
COPY app ./app
COPY core ./core
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

FROM php:8.3-apache AS base
RUN docker-php-ext-install -j"$(nproc)" pdo_mysql opcache \
 && a2enmod rewrite headers expires \
 && a2dismod -f autoindex status >/dev/null 2>&1 || true
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-aliev.ini
COPY docker/apache/security.conf /etc/apache2/conf-available/aliev-security.conf
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/api-csp.conf /etc/apache2/aliev-api-csp.conf
RUN a2enconf aliev-security >/dev/null
COPY docker/entrypoint.sh /usr/local/bin/aliev-entrypoint
RUN chmod +x /usr/local/bin/aliev-entrypoint
WORKDIR /var/www/html
ENTRYPOINT ["aliev-entrypoint"]
CMD ["apache2-foreground"]

FROM base AS dev
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/*
# Source is bind-mounted by compose.yaml; run `composer install` inside the container.

FROM base AS prod
COPY --chown=www-data:www-data app ./app
COPY --chown=www-data:www-data core ./core
COPY --chown=www-data:www-data config ./config
COPY --chown=www-data:www-data routes ./routes
COPY --chown=www-data:www-data database ./database
COPY --chown=www-data:www-data scripts/migrate.php scripts/import-jobs.php ./scripts/
COPY --chown=www-data:www-data public ./public
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
RUN mkdir -p storage/logs storage/ratelimit storage/leads storage/cache \
 && chown -R www-data:www-data storage && chmod -R 0750 storage
# Apache's master process binds :80 as root and serves every request as www-data.
EXPOSE 80
