#!/bin/sh
set -e

cd /var/www/laravel

for dir in storage bootstrap/cache; do
    if [ -d "$dir" ]; then
        chmod -R ug+rwx "$dir" 2>/dev/null || chmod -R 777 "$dir" 2>/dev/null || true
    fi
done

if [ -f storage/logs/laravel.log ]; then
    chmod 666 storage/logs/laravel.log 2>/dev/null || true
fi

for key in storage/oauth-private.key storage/oauth-public.key; do
    if [ -f "$key" ]; then
        chmod 644 "$key" 2>/dev/null || true
    fi
done

exec docker-php-entrypoint php-fpm
