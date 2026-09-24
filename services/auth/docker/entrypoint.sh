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

npm install --legacy-peer-deps
npm run build

chown -R www-data:www-data storage bootstrap/cache public/build || true
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

php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

exec docker-php-entrypoint php-fpm
