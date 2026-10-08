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
    && MAKEFLAGS="-j1" pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install zip bcmath \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS cmake \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/entrypoint.sh /usr/local/bin/notification-entrypoint
RUN chmod +x /usr/local/bin/notification-entrypoint

ENTRYPOINT ["notification-entrypoint"]
CMD ["php-fpm"]
