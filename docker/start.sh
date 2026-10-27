#!/bin/sh
# Migrate, build the Laravel caches with the runtime env, then run PHP-FPM and nginx.
set -e
export PHP_WORKERS="${PHP_WORKERS:-2}"
cd /app
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
php-fpm -D
exec nginx -g 'daemon off;'
