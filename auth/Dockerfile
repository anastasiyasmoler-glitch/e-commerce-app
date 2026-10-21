FROM php:8.5-fpm-bookworm

# Match container www-data uid/gid to the host user (1000) so bind-mounted
# storage/ and bootstrap/cache files keep host-writable ownership.
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        ca-certificates \
        libpq-dev \
        libzip-dev \
        libssl-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql zip bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY docker/entrypoint.sh /usr/local/bin/auth-entrypoint
RUN chmod +x /usr/local/bin/auth-entrypoint

ENTRYPOINT ["auth-entrypoint"]
CMD ["php-fpm"]
