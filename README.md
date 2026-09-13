### Start

1. Copy `.env.example` to `.env` and adjust paths for your machine.
2. Add to `hosts` (only domain, without `http://` and without `/`):
   `127.0.0.1 docker-porto.loc`
   Then run: `ipconfig /flushdns`
   Use **http://docker-porto.loc** or **http://127.0.0.1** (not `https://` and not bare `localhost` if IPv6 breaks routing on Windows).
3. In `laravel/.env` set:
   - `APP_URL=http://docker-porto.loc`
   - `API_URL=http://docker-porto.loc`
   - `API_PREFIX=api`
4. Start containers:

```bash
docker compose up -d --build
```

5. Backend setup (inside `php-app` container):

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan storage:link
docker compose exec app php artisan migrate
docker compose exec -u www-data app php artisan queue:work
docker compose exec -u www-data app php artisan reverb:start
```

6. Laravel Passport (fix `Invalid key supplied` on login/register):

```bash
docker compose exec app php artisan passport:keys --force
docker compose exec app php artisan passport:client --password --no-interaction --name="Home Portal Password Grant"
```

Copy **Client ID** and **Client secret** from the command output into `laravel/.env`:

```env
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=<Client ID>
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=<Client secret>
```

Keys are stored in `laravel/storage/oauth-private.key` and `laravel/storage/oauth-public.key`.
After generation, restart the app container so entrypoint fixes file permissions for `www-data`:

```bash
docker compose restart app
```

7. Open app: http://docker-porto.loc

Routing via nginx:

- `/` — Quasar frontend (dev server with HMR)
- `/api` — Laravel API
- `/storage` — Laravel public static files
- `/app` — Laravel Reverb (WebSocket)
