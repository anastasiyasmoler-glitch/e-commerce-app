# Auth Service

JWT authentication for the e-commerce training monorepo.

## Role

Auth is checkpoint 1. Other services ask Auth `GET /me` to validate an access token. After registration Auth publishes `user.registered` (email, name, user_id) to `kafka:9092` on `microservices-net`. Start Notification first so that broker exists. If Kafka is down, publish is logged and registration still succeeds.

Owns:

- JWT register, login, refresh, logout, profile
- Google OAuth callback that issues JWT
- admin user list and analyst role
- Spatie roles `admin`, `customer`, `analyst`

There is no Breeze/Inertia UI in this service. Customer Frontend and Admin are separate apps. Catalog Filament is not here.

Product email belongs to Notification.

## Layout

Service root: `auth/` (compose, nginx, Laravel, `.github`).

## Stack

- PHP 8.5, Laravel 13, Spatie, tymon/jwt-auth, Socialite
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

Swagger UI: https://localhost/docs  
OpenAPI file: `docs/openapi.yaml`.

## JWT API

- `POST /register`, `/login`, `/refresh`, `/logout`, `GET /me`
- `GET|PATCH /profile`, `PUT /profile/password`, `DELETE /profile`
- `GET /admin/users`, `PATCH /admin/users/{id}/analyst`
- `GET /auth/google`, `GET /auth/google/callback`
- Access and refresh are both JWTs (`token_use` claim). Refresh is HttpOnly cookie `refresh_token` plus Redis. Closed routes use `auth:api`.

## Roles

| Role | How |
|---|---|
| `admin` | seeder |
| `customer` | self-register and Google |
| `analyst` | `PATCH /admin/users/{id}/analyst` |

## Tests

```powershell
php vendor/bin/phpunit
```

## Out of scope

- React Frontend / Admin apps
