FROM php:8.5-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        ca-certificates \
        libssl-dev \
        libzip-dev \
        cmake \
        $PHPIZE_DEPS \
    && pecl channel-update pecl.php.net \
    && (MAKEFLAGS="-j1" pecl install mongodb || (pecl channel-update pecl.php.net && MAKEFLAGS="-j1" pecl install mongodb)) \
    && docker-php-ext-enable mongodb \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install zip bcmath \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS cmake \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY docker/entrypoint.sh /usr/local/bin/notification-entrypoint
RUN chmod +x /usr/local/bin/notification-entrypoint

ENTRYPOINT ["notification-entrypoint"]
CMD ["php-fpm"]
