# Auth stack run sheet

Quick local checklist for the Auth gateway and JWT API.

## Prerequisites

- Docker network `microservices-net` exists.
- For Kafka publish on register: Notification compose up (`kafka` on `microservices-net`).

## Start Auth

```bash
cd auth
cp .env.example .env
docker compose up -d --build
```

- API (via gateway): `https://localhost` (nginx 443 on Auth compose; in full stack, browser uses shared gateway).
- Swagger UI: `https://localhost/docs` (when hitting Auth nginx directly: Auth compose publishes 443).
- Seed user: `admin@example.com` / `password`.

## Gateway identity subrequests

Nginx `auth_request` calls these with the client `Authorization` header:

| Route | Roles allowed |
|---|---|
| `GET /_gateway/customer` | any valid access JWT |
| `GET /_gateway/staff` | `admin` or `analyst` |
| `GET /_gateway/admin` | `admin` only |

Success: `204 No Content` plus `X-Auth-User-Id`, `X-Auth-User-Email`, `X-Auth-User-Name`, `X-Auth-User-Roles`.

## Tests

```bash
cd auth
composer install
cp .env.example .env && php artisan key:generate
php vendor/bin/phpunit
```

## Docs

- REST contract: `docs/openapi.yaml`
- Service overview: `README.md`
