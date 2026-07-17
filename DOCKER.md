# Running DTIS v2 with Docker

## Stack

| Service      | Image             | Host port (default) | Purpose                              |
|--------------|-------------------|---------------------|--------------------------------------|
| `app`        | built from repo   | 8095                | PHP 8.3 + Apache serving the Laravel app |
| `db`         | `mysql:8.0`       | 3307                | Application database                 |
| `redis`      | `redis:7-alpine`  | —                   | Cache + sessions                     |
| `phpmyadmin` | `phpmyadmin`      | 8085                | Database admin UI                    |

The app image is built in two stages: a Node 20 stage compiles the Vite/Tailwind
assets, then a PHP 8.3 Apache image installs the required extensions
(`pdo_mysql`, `gd`, `intl`, `zip`, `exif`, `bcmath`, `opcache`, `redis`) and the
production Composer dependencies.

## First run

1. Review [.env.docker](.env.docker) — set real values for `DB_PASSWORD`,
   `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `API_BASE_URL`, and the mail
   settings. The `MYSQL_*` values must match the `DB_*` values (both are read
   from this one file). For a real deployment also regenerate `APP_KEY`.

2. Build and start everything:

   ```sh
   docker compose up -d --build
   ```

3. Open http://localhost:8095 (phpMyAdmin: http://localhost:8085).

On startup the app container waits for MySQL, creates the `storage` symlink,
caches config/views, and runs `php artisan migrate --force` (disable by setting
`AUTO_MIGRATE=false` in `.env.docker`). If the database is empty and you need
seed data, run:

```sh
docker compose exec app php artisan db:seed --force
```

## Everyday commands

```sh
docker compose up -d          # start
docker compose down           # stop (data volumes are kept)
docker compose logs -f app    # tail application logs
docker compose exec app php artisan tinker
docker compose up -d --build app   # rebuild after code changes
```

## Changing ports

The host ports have defaults baked into `docker-compose.yml`
(`8095`, `3307`, `8085`). Override them via shell environment variables when
starting, e.g. in PowerShell:

```powershell
$env:APACHE_PORT = "8195"; docker compose up -d
```

Remember to keep `APP_URL` in `.env.docker` in sync with the app port.

## Notes

- Uploaded files live in the named volume `app-storage`
  (`storage/app` inside the container); database data lives in `db-data`.
  `docker compose down -v` deletes both — only use it when you mean to.
- Config is cached at startup, so after editing `.env.docker` you must
  `docker compose up -d` again (recreates the container) — a plain restart of
  Apache is not enough.
- `API_BASE_URL` defaults to `host.docker.internal:8081`, which points at the
  machine running Docker. If the offices API runs elsewhere on the network,
  use its real address (e.g. `http://192.168.100.162:8081/`).
- Route caching is intentionally skipped because `routes/web.php` registers a
  closure route (`/logout`); convert it to a controller action if you want
  `php artisan route:cache`.
- The local dev setup (Herd + Vite) is unaffected: `.env` is still used for
  local development, `.env.docker` only feeds the containers.
