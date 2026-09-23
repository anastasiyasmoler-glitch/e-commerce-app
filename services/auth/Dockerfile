FROM php:8.5-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
