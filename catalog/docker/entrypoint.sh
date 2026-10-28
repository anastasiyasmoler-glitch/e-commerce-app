#!/bin/sh
set -e

cd /var/www/html

fix_writable_dirs() {
    mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/testing \
        storage/logs \
        bootstrap/cache

    if [ "$(id -u)" = "0" ]; then
        chown -R www-data:www-data storage bootstrap/cache
        chmod -R ug+rwx storage bootstrap/cache
    fi
}

run_as_www_data() {
    if [ "$(id -u)" = "0" ]; then
        runuser -u www-data -- "$@"
    else
        "$@"
    fi
}

fix_writable_dirs

if [ "$1" = "php-fpm" ]; then
    until php -r "exit(@fsockopen(getenv('DB_HOST') ?: 'sql', (int) (getenv('DB_PORT') ?: 5432)) ? 0 : 1);" 2>/dev/null; do
        sleep 1
    done

    run_as_www_data php artisan migrate --force --no-interaction

    if ! run_as_www_data php artisan db:seed --class=Database\\Seeders\\CatalogSeeder --force --no-interaction 2>/dev/null; then
        true
    fi

    run_as_www_data php artisan filament:assets --ansi --no-interaction 2>/dev/null || true

    fix_writable_dirs
fi

exec "$@"
