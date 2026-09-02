#!/bin/sh
set -e

PORT="${PORT:-8080}"

# Render (and most PaaS hosts) assign the port at runtime via $PORT; Apache's
# config is static, so point it at the right port before starting.
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

php artisan migrate --force
php artisan migrate --force --seed
# Safe to (re)build on every boot: these caches are derived purely from the
# code and the real runtime env vars, which are only available now, not at
# image build time.
php artisan config:cache
php artisan route:cache
# php artisan view:cache

exec apache2-foreground
