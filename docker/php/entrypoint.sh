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

# passport:keys (run as root) leaves keys root:root 600/660, which php-fpm
# (www-data) cannot read. 640 + group www-data is readable and accepted by
# league/oauth2-server. Do not chmod 644: Laravel turns that notice into an exception.
for key in storage/oauth-private.key storage/oauth-public.key; do
    if [ -f "$key" ]; then
        chgrp www-data "$key" 2>/dev/null || true
        chmod 640 "$key" 2>/dev/null || true
    fi
done

exec docker-php-entrypoint php-fpm
