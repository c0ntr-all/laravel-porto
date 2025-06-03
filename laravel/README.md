# Home Portal

An application for accounting household tasks. Based on Laravel Porto and Quasar (Vue 3).

## Build

**Run containers**
```shell
docker-compose up -d
```

**Install php dependencies**
```shell
docker-compose exec app composer install
```

**Run migrations**
```shell
docker-compose exec app php artisan migrate
```

## Authentication
Application uses Laravel Passport as base authentication module.

**Generate keys**
```shell
php artisan passport:keys
```

**Generate client**
Generating client for password authentication.
More information - https://laravel.com/docs/11.x/passport
```shell
php artisan passport:client --password
```
Then write client id and client secret to PASSPORT_CLIENT_ID and PASSPORT_CLIENT_SECRET in .env file
