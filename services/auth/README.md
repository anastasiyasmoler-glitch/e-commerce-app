# Auth Service

Authentication for the e-commerce training monorepo.

Start here. `docs/auth-stack.md` is a short run sheet.

## Role

Auth is checkpoint 1. Other services must not call Auth over HTTP for business logic. They consume events later (Kafka for Auth↔Notification). After registration Auth publishes `user.registered` (email, name, user_id). Isolated Auth compose has no Kafka: publish is logged and registration still succeeds. Use the root compose for a real broker.

Owns:

- registration and login
- staff UI (Breeze + Inertia) on **session**
- JWT API for the future SPA
- Spatie roles `admin`, `customer`, `analyst`

Product email belongs to Notification.

JWT API is on **`develop`**: access in JSON, refresh HttpOnly cookie, Redis for refresh hashes and access blacklist. Staff UI stays on session.

## Layout

Service root: `services/auth/` (compose, nginx, Laravel, frontend stub, `.github`).

## Stack

- PHP 8.4, Laravel 13, Spatie, Breeze + Inertia, tymon/jwt-auth
- PostgreSQL 16, Redis 7 (sessions, cache, refresh keys)
- Nginx 443, Mailhog :8025, mock OIDC :8080

## Docker

| Service | Role |
|---|---|
| `sql` | PostgreSQL |
| `redis` | Sessions, cache, JWT blacklist/refresh |
| `mail` | Mailhog |
| `oidc` | Mock Google |
| `back` | php-fpm |
| `front` | SPA stub |
| `nginx` | 80 → 443 |

```powershell
cd services/auth
docker compose up --build
```

https://localhost — `/register`, `/login`, `/admin`. Seed: `admin@example.com` / `password`.

## JWT API

- `POST /api/register`, `/api/login`, `/api/refresh`, `/api/logout`, `GET /api/me`
- `GET|PATCH /api/profile`, `PUT /api/profile/password`, `DELETE /api/profile`
- Refresh cookie `refresh_token`. Access in JSON. `/api/*` uses `auth:api`, not `role:admin`.

## Roles

| Role | How |
|---|---|
| `admin` | seeder |
| `customer` | self-register and Google |
| `analyst` | `/admin` |

## Tests

```powershell
php vendor/bin/phpunit
```

## Out of scope

- OpenAPI
- Replacing Inertia with the shop SPA
