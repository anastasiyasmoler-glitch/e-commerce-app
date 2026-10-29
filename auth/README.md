# Auth Service

Identity, JWT session management, and HTTP gateway for the e-commerce platform.

## Overview

Auth issues and validates JSON Web Tokens, stores users and roles in PostgreSQL, and keeps token state in Redis. Nginx terminates TLS on port 443 and proxies authenticated requests to backend services. New accounts trigger a `user.registered` event on Kafka.

Customer and admin UIs are separate applications in the monorepo; they call Auth over HTTPS.

## Stack

| Component | Technology |
| --- | --- |
| Runtime | PHP 8.5, Laravel 13 |
| API auth | `tymon/jwt-auth`, Spatie Permission |
| OAuth | Laravel Socialite (Google / mock OIDC in dev) |
| Database | PostgreSQL 16 |
| Cache / tokens | Redis 7 (sessions, JWT blacklist, refresh rotation) |
| Edge | Nginx (HTTP→HTTPS redirect, self-signed TLS) |
| Mail (dev) | Mailhog |
| Messaging | Kafka producer (`longlang/phpkafka`) |

## Getting started

**Prerequisites:** Docker, external network `microservices-net`, Kafka reachable at `kafka:9092` (Notification stack).

```bash
docker network create microservices-net   # once
cd notification && docker compose up -d kafka
cd ../auth
cp .env.example .env
docker compose up -d --build
```

| Endpoint | URL |
| --- | --- |
| API / gateway | `https://localhost` |
| OpenAPI (Swagger UI) | `https://localhost/docs` |
| Mailhog | `http://localhost:8025` |
| Mock OIDC | `http://localhost:8080` |

Default admin (seeder): `admin@example.com` / `password`.

## Documentation

| File | Description |
| --- | --- |
| [docs/openapi.yaml](docs/openapi.yaml) | REST API specification |
| [docs/events.md](docs/events.md) | Kafka topics and message payloads |
| [docs/architecture.md](docs/architecture.md) | Architecture diagrams |

## Authentication

### Tokens

- **Access token** — JWT in JSON (`access_token`), ~15 minutes TTL, claim `token_use=access`, includes `roles`.
- **Refresh token** — JWT in HttpOnly cookie `refresh_token`, ~7 days TTL, claim `token_use=refresh`, stored in Redis for rotation and revocation.

Send access tokens as `Authorization: Bearer <token>` on protected routes.

### Public endpoints

`POST /register`, `POST /login`, `POST /refresh` — rate limited per client IP.

### Protected endpoints

Require valid access JWT (`auth:api`, `auth.access` middleware): `/logout`, `/me`, `/profile/*`, `/admin/*`.

### OAuth

`GET /auth/google` → `GET /auth/google/callback` — same token response as password login.

## Authorization

| Role | Assignment |
| --- | --- |
| `customer` | Registration or first OAuth signup |
| `analyst` | Admin via `PATCH /admin/users/{id}/analyst` |
| `admin` | Database seeder |

Policies enforce admin-only admin routes and self-service profile updates.

## API summary

| Method | Path | Description |
| --- | --- | --- |
| POST | `/register` | Create account |
| POST | `/login` | Issue tokens |
| POST | `/refresh` | Rotate tokens (cookie) |
| POST | `/logout` | Revoke tokens |
| GET | `/me` | Current user |
| GET/PATCH | `/profile` | View / update profile |
| PUT | `/profile/password` | Change password |
| DELETE | `/profile` | Soft-delete account |
| GET | `/admin/users` | List users (admin) |
| PATCH | `/admin/users/{id}/analyst` | Grant or revoke analyst (admin) |

Request and response schemas: OpenAPI.

## Gateway

Nginx validates JWTs via internal subrequests to Laravel (`/_gateway/customer`, `/_gateway/staff`, `/_gateway/admin`) and forwards `X-Auth-User-*` headers to upstream services. Details: [docs/architecture.md](docs/architecture.md).

## Docker services

| Service | Purpose |
| --- | --- |
| `nginx` | TLS, routing, CORS |
| `back` | Laravel application |
| `sql` | PostgreSQL |
| `redis` | Token and session storage |
| `mail` | Mailhog |
| `oidc` | Mock identity provider |

## Configuration

Environment variables: see `.env.example` (`JWT_*`, `REDIS_*`, `KAFKA_*`, `CORS_ALLOWED_ORIGINS`, database, OAuth).

## Testing

```bash
cd auth
composer install
cp .env.example .env && php artisan key:generate
php vendor/bin/phpunit
```

Integration tests against Redis:

```bash
docker compose up -d redis sql
docker compose run --rm back php vendor/bin/phpunit --filter RedisJwtAuthTest
```

CI: `.github/workflows/auth.yml`.
