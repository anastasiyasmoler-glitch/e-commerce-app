FROM php:8.5-fpm-bookworm

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        ca-certificates \
        libzip-dev \
        libpq-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install zip bcmath pdo_pgsql \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/entrypoint.sh /usr/local/bin/recommendation-entrypoint
RUN chmod +x /usr/local/bin/recommendation-entrypoint

ENTRYPOINT ["recommendation-entrypoint"]
CMD ["php-fpm"]
