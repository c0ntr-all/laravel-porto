#!/bin/sh
set -e

cd /var/www/laravel

# Bind-mounted storage is often root:root 775. php-fpm runs as www-data and
# creates gallery folders (storage/app/public/userfiles/...). chmod without
# chown leaves www-data unable to mkdir. Skip storage/mnt: those are host
# library mounts. 2775 keeps the group on new directories.
own_writable_tree() {
    root="$1"
    [ -d "$root" ] || return 0

    find "$root" \
        \( -path "$root/mnt" -o -path "$root/mnt/*" \) -prune \
        -o -exec chown www-data:www-data {} +

    find "$root" \
        \( -path "$root/mnt" -o -path "$root/mnt/*" \) -prune \
        -o -type d -exec chmod 2775 {} +

    find "$root" \
        \( -path "$root/mnt" -o -path "$root/mnt/*" \) -prune \
        -o -type f -exec chmod ug+rw {} +
}

own_writable_tree storage
own_writable_tree bootstrap/cache

umask 0002

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

# php-fpm stays PID 1. Reverb, the scheduler, and queue workers run as www-data
# and restart if they exit. Reverb listens on REVERB_SERVER_HOST:REVERB_SERVER_PORT (0.0.0.0:8080).
supervise_www() {
    su -s /bin/sh www-data -c "
        while true; do
            $1
            echo \"[entrypoint] exited (\$?), restart in 2s: $1\"
            sleep 2
        done
    " &
}

supervise_www "php artisan reverb:start"
supervise_www "php artisan queue:work --sleep=1 --tries=2 --timeout=120"
supervise_www "php artisan queue:work --sleep=1 --tries=2 --timeout=120"
supervise_www "php artisan queue:work --sleep=1 --tries=2 --timeout=120"
supervise_www "php artisan schedule:work"

exec docker-php-entrypoint php-fpm
