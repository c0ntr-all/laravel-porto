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
```shell
docker compose exec app php artisan passport:keys --force
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

More information — https://laravel.com/docs/11.x/passport
