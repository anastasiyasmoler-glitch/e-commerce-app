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

    run_as_www_data php artisan catalog:seed-demo-if-empty --no-interaction

    run_as_www_data php artisan filament:assets --ansi --no-interaction 2>/dev/null || true

    fix_writable_dirs

    # Bind-mounted storage may still be owned by host www-data (uid 33) while FPM runs as 1000.
    if ! run_as_www_data php -r 'exit(is_writable("storage/framework/views") ? 0 : 1);'; then
        fix_writable_dirs
        if ! run_as_www_data php -r 'exit(is_writable("storage/framework/views") ? 0 : 1);'; then
            echo "catalog-entrypoint: storage/framework/views is not writable for www-data" >&2
            exit 1
        fi
    fi
fi

exec "$@"
