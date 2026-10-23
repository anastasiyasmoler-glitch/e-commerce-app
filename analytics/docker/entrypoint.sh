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

if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    chown www-data:www-data database/database.sqlite
fi

echo "Waiting for ClickHouse..."
until php -r "
\$host = getenv('CLICKHOUSE_HOST') ?: 'clickhouse';
\$port = getenv('CLICKHOUSE_PORT') ?: '8123';
\$url = sprintf('http://%s:%s/ping', \$host, \$port);
\$ctx = stream_context_create(['http' => ['timeout' => 2]]);
\$body = @file_get_contents(\$url, false, \$ctx);
exit(trim((string) \$body) === 'Ok.' ? 0 : 1);
"; do
    sleep 2
done

if [ "$1" = "php-fpm" ]; then
    php artisan migrate --force --isolated --no-interaction
fi

exec docker-php-entrypoint "$@"
