#!/bin/sh
set -e

cd /var/www/html

# The storage/app volume may shadow the directories baked into the image,
# so recreate the writable skeleton on every start.
mkdir -p storage/framework/cache/data storage/framework/sessions \
    storage/framework/views storage/logs storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "Waiting for database at ${DB_HOST}:${DB_PORT}..."
until php -r 'exit(@fsockopen(getenv("DB_HOST"), (int) getenv("DB_PORT")) ? 0 : 1);'; do
    sleep 2
done
echo "Database is reachable."

php artisan storage:link --force || true

# Cache config/views at runtime so container env vars are picked up.
# Route caching is skipped: routes/web.php contains closure routes.
php artisan config:cache
php artisan view:cache

if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
