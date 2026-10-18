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

echo "Waiting for MongoDB..."
until php -r "exit(@fsockopen(getenv('MONGODB_HOST') ?: 'mongo', (int) (getenv('MONGODB_PORT') ?: 27017), \$e, \$s, 2) ? 0 : 1);"; do
    sleep 2
done

mkdir -p database
echo "Migrating SQLite queue tables..."
php artisan migrate --database=queue --force --no-interaction \
    --path=database/migrations/0001_01_01_000002_create_jobs_table.php

exec docker-php-entrypoint "$@"
