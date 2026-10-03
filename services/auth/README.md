# Auth Service

Authentication and authorization service for the e-commerce training monorepo.

This README is the place to start. `docs/auth-stack.md` still exists; do not treat it as the source of truth.

## Role in the system

Auth is checkpoint 1. Other services (Notification, Catalog, Order, and so on) must not call Auth over synchronous HTTP for business logic. They consume events later, through a broker.

This service owns:

- user registration and login
- web session for the staff UI (Laravel Breeze + Inertia)
- roles (`admin`, `customer`, `analyst`) via Spatie
- the only place that creates the first user records

It does **not** send email to end users (Mailhog is only for local mail capture). Product email belongs to Notification.

JWT API is on this branch: access token in JSON, refresh token in an HttpOnly cookie, Redis store for refresh hashes and access blacklist.

## Layout

Monorepo root (`e-commerce-app`):

| Path | What it is |
|---|---|
| `services/auth/` | Laravel app, `docker-compose.yml`, nginx, frontend stub |
| `services/auth/docs/auth-stack.md` | Extra run notes |

Compose builds the PHP image from `services/auth`. Nginx serves `services/auth/public` and proxies `/spa/` to `front`.

## Stack

- PHP 8.4, Laravel, Composer, PSR-4
- **Breeze + Inertia (React)** — cookie session, CSRF, Blade/Inertia pages (`/register`, `/login`, `/dashboard`, `/profile`)
- **Spatie Permission** — roles `admin`, `customer`, `analyst`
- **PostgreSQL 16** — users and role tables
- **Redis 7** — session and cache in Docker
- **Nginx** — port 443 only for the app; HTTP 80 redirects to HTTPS; self-signed cert for local use
- **Mailhog** — SMTP catcher, UI at http://localhost:8025
- PHPUnit for tests

The Inertia UI is a **staff tool inside Auth**, not the future shop SPA. The `front` container is a stub behind `/spa/`.

## Request path

1. Browser opens `https://localhost`.
2. Nginx terminates TLS (`docker/nginx`).
3. Static files and PHP go to `public/` → php-fpm on `back:9000`.
4. Laravel uses the `web` guard (session) for Inertia and the `api` guard (JWT) for `/api/*`.
5. After register, Spatie assigns `customer`. The seeded account has `admin`.

Self-signed certificate: the browser will warn once; continue to the site.

## Docker services

| Service | Role |
|---|---|
| `sql` | PostgreSQL, database `auth` |
| `redis` | Sessions and cache |
| `mail` | Mailhog (SMTP 1025 inside the network, UI 8025 on the host) |
| `back` | php-fpm, Laravel, volume-mounted `services/auth` |
| `front` | Placeholder nginx SPA |
| `nginx` | 80 → 443, FastCGI to `back`, `/spa/` to `front` |
| `oidc` | Mock Google OIDC (host 8080) |

`back` waits until `sql` and `redis` are healthy. `nginx` waits until `back` is healthy.

## Roles

| Role | How you get it |
|---|---|
| `admin` | Database seeder only (`admin@example.com` / `password`) |
| `customer` | Default on self-registration |
| `analyst` | Admin toggles it on `/admin` |

Admin UI: `/admin` (role `admin`).

## Run

From **`services/auth`**:

```powershell
cd services/auth
docker compose up --build
```

Then:

- App: https://localhost — `/register`, `/login`
- Mailhog: http://localhost:8025
- Seeded admin: `admin@example.com` / `password`

Stop with `docker compose down`.

## Tests

From the Auth app directory (or the `back` container):

```powershell
php vendor/bin/phpunit
```

## JWT API

- `POST /api/register`, `/api/login`, `/api/refresh`, `/api/logout`, `GET /api/me`
- `GET|PATCH /api/profile`, `PUT /api/profile/password`, `DELETE /api/profile`
- Refresh cookie: `refresh_token` (HttpOnly). Access token is in the JSON body.

## Out of scope here

- Gateway in front of Auth (each service will validate JWT itself later)
- Notification emails and Kafka consumers
- Replacing the Inertia staff UI with the shared shop frontend
