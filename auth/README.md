# Auth Service

JWT authentication for the e-commerce training monorepo.

Start here. `docs/auth-stack.md` is a short run sheet.

## Role

Auth is checkpoint 1. Other services ask Auth `GET /api/me` to validate an access token. After registration Auth publishes `user.registered` (email, name, user_id) to `kafka:9092` on `microservices-net`. Start Notification first so that broker exists. If Kafka is down, publish is logged and registration still succeeds.

Owns:

- JWT register, login, refresh, logout, profile
- password reset API
- Google OAuth callback that issues JWT
- admin user list and analyst role
- Spatie roles `admin`, `customer`, `analyst`

There is no Breeze/Inertia UI in this service. Customer Frontend and Admin are separate apps. Catalog Filament is not here.

Product email belongs to Notification.

## Layout

Service root: `auth/` (compose, nginx, Laravel, `.github`).

## Stack

- PHP 8.4, Laravel 13, Spatie, tymon/jwt-auth, Socialite
- PostgreSQL 16, Redis 7 (cache, refresh keys, JWT blacklist)
- Nginx 443, Mailhog :8025, mock OIDC :8080

## Docker

| Service | Role |
|---|---|
| `sql` | PostgreSQL |
| `redis` | Cache, JWT blacklist/refresh |
| `mail` | Mailhog |
| `oidc` | Mock Google |
| `back` | php-fpm |
| `nginx` | 80 → 443 |

```powershell
cd auth
docker compose up --build
```

https://localhost — JSON API. Seed: `admin@example.com` / `password`.

## JWT API

- `POST /api/register`, `/api/login`, `/api/refresh`, `/api/logout`, `GET /api/me`
- `POST /api/forgot-password`, `/api/reset-password`
- `GET|PATCH /api/profile`, `PUT /api/profile/password`, `DELETE /api/profile`
- `GET /api/admin/users`, `PATCH /api/admin/users/{id}/analyst`
- `GET /auth/google`, `GET /auth/google/callback`
- Refresh cookie `refresh_token`. Access in JSON. Closed routes use `auth:api`.

## Roles

| Role | How |
|---|---|
| `admin` | seeder |
| `customer` | self-register and Google |
| `analyst` | `PATCH /api/admin/users/{id}/analyst` |

## Tests

```powershell
php vendor/bin/phpunit
```

## Out of scope

- OpenAPI
- React Frontend / Admin apps
