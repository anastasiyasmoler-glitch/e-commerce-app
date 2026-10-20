#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-interaction --prefer-dist

if ! grep -qE '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --no-interaction
fi

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache

echo "Waiting for PostgreSQL..."
until php -r "
try {
    new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_PORT') ?: '5432', getenv('DB_DATABASE')),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    exit(0);
} catch (Throwable \$e) {
    exit(1);
}
"; do
    sleep 2
done

# Only the main php-fpm container runs migrations; worker/relay containers
# share the same database and would race each other (and it) on a fresh start.
if [ "$1" = "php-fpm" ]; then
    php artisan migrate --force --isolated --no-interaction
fi

exec docker-php-entrypoint "$@"
