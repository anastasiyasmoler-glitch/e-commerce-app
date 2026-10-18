#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi


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

php artisan migrate --force --no-interaction --isolated

store_count="$(php artisan tinker --execute="echo \\App\\Models\\Store::query()->count();" 2>/dev/null | tail -n 1 | tr -d '\r')"
if [ "${store_count:-0}" = "0" ]; then
    echo "Catalog is empty — running CatalogSeeder..."
    php artisan db:seed --force --no-interaction
fi

echo "Publishing Filament assets to public/..."
php artisan filament:assets --ansi --no-interaction

exec docker-php-entrypoint "$@"
