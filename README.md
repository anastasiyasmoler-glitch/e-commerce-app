# E-commerce monorepo

Microservices e-commerce platform for local development. Nine applications: **Auth**, **Notification**, **Catalog**, **Order**, **Recommendation**, **Search**, **Analytics**, customer **Frontend**, and shared **Admin**. Catalog also ships **Filament** admin UI inside the Catalog service (`/catalog-admin` via gateway), not the Admin SPA.

## Architecture

| Channel | Flow |
| --- | --- |
| **HTTP (people)** | Frontend or Admin → **Auth gateway** (`https://localhost`, JWT) → backend service |
| **Events** | **Kafka** on `microservices-net` (broker in Notification compose). No request/response over the broker. |

There is **no root `docker-compose.yml`**. Each backend service has its own compose under `<service>/docker-compose.yml`. Create the shared network once:

```bash
docker network create microservices-net
```

Start **Kafka** before consumers that need it:

```bash
cd notification && docker compose up -d kafka
```

Then bring up the stacks you need (Auth first if you use the gateway). Example:

```bash
cd auth && cp .env.example .env && docker compose up -d --build
cd ../catalog && docker compose up -d --build
cd ../order && docker compose up -d --build
# … notification, search, analytics, recommendation
```

Notification `consumer` runs `php artisan kafka:consume-notifications`. Other services run their own Kafka consumers (see each service README).

## Services

| Service | Role | Direct HTTPS (dev) | Gateway (typical) | README |
| --- | --- | --- | --- | --- |
| Auth | JWT, routing gateway | `https://localhost` | — | [auth/README.md](auth/README.md) |
| Notification | Email, history, Kafka | `https://localhost:8443` | `/api/notifications/*` | [notification/README.md](notification/README.md) |
| Catalog | Products, stock, Filament | `https://localhost:8444` | `/api/catalog/*`, `/catalog-admin` | [catalog/README.md](catalog/README.md) |
| Order | Orders, payments, saga | `https://localhost:8445` | `/api/orders/*` | [order/README.md](order/README.md) |
| Search | Full-text search (Go) | `https://localhost:8446` | `/api/search` | [search/README.md](search/README.md) |
| Analytics | ClickHouse reports | `https://localhost:8447` | `/api/analytics/*` | [analytics/README.md](analytics/README.md) |
| Recommendation | AI chat, vectors | `https://localhost:8448` | `/api/recommendations/*` | [recommendation/README.md](recommendation/README.md) |
| Frontend | Storefront SPA | `npm run dev` → `:5173` | uses gateway | [frontend/README.md](frontend/README.md) |
| Admin | Staff SPA | `npm run dev` → `:5174` | uses gateway | [admin/README.md](admin/README.md) |

Self-signed TLS on service nginx (443 mapped to host ports above). Trust or skip verification in clients during dev.

## API documentation (OpenAPI + Swagger UI)

Each backend service ships `docs/openapi.yaml` and serves Swagger UI at `/docs` (YAML at `/docs/openapi.yaml`). Through the **Auth gateway** (after `auth` compose is up), use the paths below; each UI loads the spec from the same URL prefix (works on gateway and on direct ports).

| Service | Gateway Swagger UI | Direct Swagger UI |
| --- | --- | --- |
| Auth | `https://localhost/docs` | same |
| Catalog | `https://localhost/api/catalog/docs` | `https://localhost:8444/docs` |
| Notification | `https://localhost/api/notifications/docs` | `https://localhost:8443/docs` |
| Order | `https://localhost/api/orders/docs` | `https://localhost:8445/docs` |
| Search | `https://localhost/api/search/docs` | `https://localhost:8446/docs` |
| Analytics | `https://localhost/api/analytics/docs` | `https://localhost:8447/docs` |
| Recommendation | `https://localhost/api/recommendations/docs` | `https://localhost:8448/docs` |

Kafka event payloads are documented in each service `docs/events.md`, not in OpenAPI.

## Dev tooling

| Tool | URL |
| --- | --- |
| Auth Mailhog | `http://localhost:8025` |
| Notification Mailhog | `http://localhost:8026` |
| Mock OIDC (Auth) | `http://localhost:8080` |
| Search load tests (k6) | [search/loadtests/](search/loadtests/) |

## Tests and CI

Each service runs its own pipeline under `.github/workflows/<service>.yml` (and often a copy under `<service>/.github/workflows/`). From a service directory:

```bash
# Laravel example
composer install && cp .env.example .env && php artisan key:generate && php artisan test

# Search (Go)
cd search && go test ./...
```

Integration tests may require Docker services (PostgreSQL, ClickHouse, Qdrant, Kafka); see the service README.

## Language and conventions

Application code, API messages, and UI copy are **English**. Commits follow Conventional Commits with service `scope` (e.g. `feat(order): …`).
