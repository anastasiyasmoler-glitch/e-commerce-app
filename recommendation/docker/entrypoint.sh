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

chown -R www-data:www-data storage bootstrap/cache database || true
chmod -R ug+rwx storage bootstrap/cache database || true

echo "Waiting for PostgreSQL..."
until php -r "
\$host = getenv('DB_HOST') ?: 'postgres';
\$port = getenv('DB_PORT') ?: '5432';
\$db = getenv('DB_DATABASE') ?: 'recommendation';
\$user = getenv('DB_USERNAME') ?: 'recommendation';
\$pass = getenv('DB_PASSWORD') ?: 'recommendation';
try {
    new PDO(sprintf('pgsql:host=%s;port=%s;dbname=%s', \$host, \$port, \$db), \$user, \$pass, [PDO::ATTR_TIMEOUT => 2]);
    exit(0);
} catch (Throwable \$e) {
    exit(1);
}
"; do
    sleep 2
done

if [ "$1" = "php-fpm" ]; then
    php artisan migrate --force --isolated --no-interaction
fi

exec docker-php-entrypoint "$@"
