# Running DTIS v2 with Docker

## Stack

| Service      | Image              | Host port (default) | Purpose                              |
|--------------|--------------------|---------------------|--------------------------------------|
| `proxy`      | `nginx:1.27-alpine`| 80, 443             | HTTPS termination for dtis.dohwv.net |
| `app`        | built from repo    | — (internal only)   | PHP 8.3 + Apache serving the Laravel app |
| `db`         | `mysql:8.0`        | 3307                | Application database                 |
| `redis`      | `redis:7-alpine`   | —                   | Cache + sessions                     |
| `phpmyadmin` | `phpmyadmin`       | 8085                | Database admin UI                    |

The `db`, `redis`, and `phpmyadmin` images are downloaded once from Docker Hub
and cached locally. The `app` image is **not** downloaded — it is built locally
from the [Dockerfile](Dockerfile) (`pull_policy: build` in the compose file) in
two stages: a Node 20 stage compiles the Vite/Tailwind assets, then a PHP 8.3
Apache image installs the required extensions (`pdo_mysql`, `gd`, `intl`,
`zip`, `exif`, `bcmath`, `opcache`, `redis`) and the production Composer
dependencies.

## First run

1. Review [.env.docker](.env.docker) — set real values for `DB_PASSWORD`,
   `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `API_BASE_URL`, and the mail
   settings. The `MYSQL_*` values must match the `DB_*` values (both are read
   from this one file). For a real deployment also regenerate `APP_KEY`.

2. (Optional but recommended on a slow connection) download the service
   images first, so a network failure is easy to spot and retry:

   ```sh
   docker compose pull
   ```

3. Build and start everything:

   ```sh
   docker compose up -d --build
   ```

   The first build takes several minutes: it downloads the base images
   (`node:20-alpine`, `php:8.3-apache`, `composer:2`), compiles PHP
   extensions, and runs `npm ci` and `composer install`. Later builds reuse
   the cache and are much faster.

4. Place the TLS certificate for `dtis.dohwv.net` in `docker/nginx/certs/`
   as `dtis.crt` and `dtis.key` (see **HTTPS** below).

5. Open https://dtis.dohwv.net (phpMyAdmin: http://localhost:8085, only from
   the server itself unless you expose it).

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

## HTTPS

The `proxy` (nginx) container terminates TLS for `https://dtis.dohwv.net`
(internal DNS points the name at the server) and forwards traffic to the app
container. It expects two files, which are **not** committed to git:

```
docker/nginx/certs/dtis.crt
docker/nginx/certs/dtis.key
```

Preferred: use a certificate issued by your organization's CA (or a wildcard
`*.dohwv.net` certificate) so browsers trust it automatically. If you have a
`.pfx` file, convert it with openssl:

```sh
openssl pkcs12 -in dtis.pfx -clcerts -nokeys -out dtis.crt
openssl pkcs12 -in dtis.pfx -nocerts -nodes -out dtis.key
```

Fallback: generate a self-signed certificate (browsers will show a warning
unless the certificate is pushed to client machines, e.g. via GPO):

```powershell
docker run --rm -v ${PWD}/docker/nginx/certs:/certs alpine/openssl req -x509 -nodes -days 825 -newkey rsa:2048 -keyout /certs/dtis.key -out /certs/dtis.crt -subj "/CN=dtis.dohwv.net" -addext "subjectAltName=DNS:dtis.dohwv.net"
```

After changing certificates: `docker compose restart proxy`.

Laravel is told to trust the proxy's `X-Forwarded-*` headers via
`TRUSTED_PROXIES=*` in `.env.docker` (safe because the app container is not
reachable from outside the Docker network). `SESSION_SECURE_COOKIE=true`
ensures session cookies are HTTPS-only.

## Troubleshooting

- **`failed to copy: httpReadSeeker ...` or `Interrupted` while pulling** —
  the download from Docker Hub broke mid-transfer (flaky network, VPN, or
  proxy). Pulls are resumable, so just run `docker compose pull` again; layers
  that already downloaded are kept. If it keeps failing, add
  `"dns": ["8.8.8.8", "1.1.1.1"]` in Docker Desktop → Settings → Docker
  Engine, then Apply & Restart.

- **`pull access denied for dtisv2-app, repository does not exist`** — the
  app image only exists locally; it is built, not pulled. The compose file
  sets `pull_policy: build` to prevent this, so if you still see it, make
  sure you are on the current `docker-compose.yml` and use
  `docker compose up -d --build` (not `pull`) for the app service.

- **`failed to connect to the docker API` / `cannot find the file
  specified`** — the Docker engine is not running. Start Docker Desktop and
  wait until the whale icon in the system tray stops animating before
  retrying.

## Changing ports

The proxy binds host ports `80` and `443`; MySQL is on `3307` and phpMyAdmin
on `8085` (overridable via `MYSQL_PORT` / `PHPMYADMIN_PORT` shell variables).
Ports 80/443 must be free on the server — stop IIS or any other web server
that holds them. Keep `APP_URL` in `.env.docker` equal to the public URL
(`https://dtis.dohwv.net`).

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
