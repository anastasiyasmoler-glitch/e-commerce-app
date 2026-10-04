# Auth Service

Authentication for the e-commerce training monorepo.

Start here. `docs/auth-stack.md` is a short run sheet.

## Role

Auth is checkpoint 1. Other services must not call Auth over HTTP for business logic. They consume events later (Kafka for Auth↔Notification). **This service does not publish Kafka yet.**

Owns:

- registration and login
- staff UI (Breeze + Inertia) on **session**
- Spatie roles `admin`, `customer`, `analyst`

Product email belongs to Notification. Mailhog here is only for Auth’s own mail (password reset, etc.).

## Two clients (when JWT branch is used)

On **`develop`** there is **no** JWT API — only sessions.

On **`feature/AUTH-2-jwt`** (not merged): session stays for `/admin`; SPA uses JWT (`/api/*`, access in JSON, refresh HttpOnly cookie, Redis).

## Layout

Service root: `services/auth/` (compose, nginx, Laravel, frontend stub, `.github`).

## Stack

- PHP 8.4, Laravel 13, Spatie, Breeze + Inertia
- PostgreSQL 16, Redis 7 (sessions + cache)
- Nginx 443, Mailhog :8025
- Mock OIDC :8080 (Google-like login)

## Docker

| Service | Role |
|---|---|
| `sql` | PostgreSQL |
| `redis` | Sessions and cache |
| `mail` | Mailhog |
| `oidc` | Mock Google |
| `back` | php-fpm |
| `front` | SPA stub |
| `nginx` | 80 → 443 |

```powershell
cd services/auth
docker compose up --build
```

https://localhost — `/register`, `/login`, `/admin` (admin only). Seed: `admin@example.com` / `password`. Google: `/login` → mock at :8080.

## Roles

| Role | How |
|---|---|
| `admin` | seeder |
| `customer` | self-register and Google |
| `analyst` | checkbox on `/admin` |

`role:admin` guards `/admin`. JWT routes (other branch) currently only check `auth:api`, not admin.

## Tests

```powershell
php vendor/bin/phpunit
```

## Out of scope here (`develop`)

- JWT `/api/login` (see `feature/AUTH-2-jwt`)
- Kafka `user.registered` producer
- OpenAPI
- Replacing Inertia with the shop SPA
