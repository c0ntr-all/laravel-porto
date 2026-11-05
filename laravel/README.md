# Home Portal

An application for accounting household tasks. Based on Laravel Porto and Quasar (Vue 3).

## Build

**Run containers**
```shell
docker compose up -d
```

**Install php dependencies**
```shell
docker compose exec app composer install
```

**Run migrations**
```shell
docker compose exec app php artisan migrate
```

## Authentication

Application uses Laravel Passport as base authentication module.

**Generate application key**
```shell
docker compose exec app php artisan key:generate
```

**Generate Passport keys**

Run as root. `docker compose exec -u www-data` fails with permission denied: `storage/` and existing keys belong to root.
```shell
docker compose exec app php artisan passport:keys --force
docker compose exec app sh -c 'chgrp www-data storage/oauth-private.key storage/oauth-public.key && chmod 640 storage/oauth-private.key storage/oauth-public.key'
```

**Generate password grant client**
```shell
docker compose exec app php artisan passport:client --password --no-interaction --name="Home Portal Password Grant"
```

Then write **Client ID** and **Client secret** to `PASSPORT_PERSONAL_ACCESS_CLIENT_ID` and `PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET` in `.env`.

Restart app container after key generation:
```shell
docker compose restart app
```

***MovieSection:***
import kinopoisk images:
```shell
docker exec php-app php artisan movie:sync-kinopoisk-images --sync
```


More information — https://laravel.com/docs/11.x/passport
