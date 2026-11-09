# E-commerce monorepo

Microservices e-commerce platform for local development. Nine applications: **Auth**, **Notification**, **Catalog**, **Order**, **Recommendation**, **Search**, **Analytics**, customer **Frontend**, and shared **Admin**. Catalog also ships **Filament** admin UI inside the Catalog service (`/catalog-admin` via gateway), not the Admin SPA.

## Architecture

| Channel | Flow |
| --- | --- |
| **HTTP (people)** | Frontend or Admin → **Auth gateway** (`https://localhost`, JWT) → backend service |
| **Events** | **Kafka** on `microservices-net` (broker in Notification compose). No request/response over the broker. |

Each backend service has its own compose under `<service>/docker-compose.yml`. For **full-stack local deploy (platform spec)**, use the repo root entry point — only **Auth gateway :443** is published on the host; service nginx and DB ports stay on the Docker network (`*/docker-compose.fullstack.yml` overlays).

```bash
chmod +x scripts/fullstack.sh scripts/vm-deploy.sh   # once
./scripts/fullstack.sh up       # all backends
./scripts/fullstack.sh down
./scripts/fullstack.sh status
```

**VM deploy (VirtualBox / VPS):** [Deployer](https://deployer.org/) — `composer install`, copy `.env.deploy.example` → `.env.deploy`, `vendor/bin/dep deploy production` (root `deploy.php`). Fallback on the VM: `./scripts/vm-deploy.sh`.

Single-service development (direct ports `8444`–`8448`, local Postgres on host, etc.) — drop the fullstack overlay:

```bash
cd catalog && docker compose up -d --build
```

Create the shared network once (the fullstack script also creates it):

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

### Recommended startup order

Bring stacks up in this order so the gateway, broker, and Kafka consumers exist before you use the storefront or run sync commands.

| Step | What | Why |
| --- | --- | --- |
| 1 | `docker network create microservices-net` | Shared network (once) |
| 2 | `cd notification && docker compose up -d kafka` | Kafka broker for all publishers/consumers |
| 3 | `cd auth && cp .env.example .env && docker compose up -d --build` | Gateway + JWT; seeds users (see below) |
| 4 | `cd catalog && cp .env.example .env && docker compose up -d --build` | Products, stock, Filament; demo seed on empty DB |
| 5 | `cd order && cp .env.example .env && docker compose up -d --build` | Orders saga (**`consumer`** runs `kafka:consume-orders`) |
| 6 | Optional backends | `notification`, `search`, `recommendation`, `analytics` — each `cp .env.example .env` then `docker compose up -d --build` |
| 7 | Stripe webhooks (checkout) | `cd order && docker compose --profile stripe up -d stripe-cli` — copy `whsec_…` from logs into `order/.env`, restart `back` |
| 8 | SPAs | `cd frontend && npm install && npm run dev` (and `admin` on `:5174` if needed) |

One-shot copy-paste (minimal full path through catalog + order):

```bash
docker network create microservices-net 2>/dev/null || true
cd notification && docker compose up -d kafka
cd ../auth && cp -n .env.example .env && docker compose up -d --build
cd ../catalog && cp -n .env.example .env && docker compose up -d --build
cd ../order && cp -n .env.example .env && docker compose up -d --build
```

Then start Search / Recommendation if you use search or the assistant, and the frontend dev server.

### Seeders and demo data

| Service | When data appears | Notes |
| --- | --- | --- |
| **Auth** | Every `back` container start | `php artisan db:seed` in [auth/docker/entrypoint.sh](auth/docker/entrypoint.sh). Default admin: **`admin@example.com`** / **`password`** ([auth/README.md](auth/README.md)). |
| **Catalog** | First start on an **empty** database | `catalog:seed-demo-if-empty` in [catalog/docker/entrypoint.sh](catalog/docker/entrypoint.sh) runs demo stores/products once. |
| **Catalog (manual reset)** | When you want ids from 1 again | `docker compose exec back php artisan catalog:reset-demo-catalog` — wipes and reruns [CatalogSeeder](catalog/database/seeders/CatalogSeeder.php). Add `--republish` to emit Kafka events in the same step. |
| **Order / Search / Recommendation** | No product catalog seeder | They learn products from Kafka (`product.created` / `product.updated`). Start their **consumers** (and Catalog **outbox** relay) so events are applied. |

Other Laravel services run **`migrate`** on start; only Auth always seeds, Catalog seeds only when the DB has no catalog data.

### Refresh downstream after catalog changes

Order keeps a **local product snapshot** for checkout; Search and Recommendation keep their own projections. If you reseeded Catalog, recreated volumes, or see **“product is not available for checkout”** / empty search while Catalog API looks fine, republish catalog facts to Kafka:

```bash
cd catalog && docker compose exec back php artisan catalog:republish-product-updates
```

This touches every product so **`product.updated`** is written to the outbox. Before running it, ensure **Catalog `outbox`** (relay to Kafka) is up and downstream **consumers** are running (at least **Order** for checkout, **Search** for `/api/search`, **Recommendation** for vectors/chat).

After a full demo reset you can combine reseed + republish:

```bash
cd catalog && docker compose exec back php artisan catalog:reset-demo-catalog --republish
```

Equivalent to reset, then `catalog:republish-product-updates`.

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
| **Grafana** | `http://localhost:3000` (`admin` / `admin` — change in `observability/.env`) |
| **Prometheus** | `http://localhost:9090` |
| **Jaeger UI** | `http://localhost:16686` |
| Auth Mailhog | `http://localhost:8025` |
| Notification Mailhog | `http://localhost:8026` |
| Mock OIDC (Auth) | `http://localhost:8080` |
| Search load tests (k6) | [search/loadtests/](search/loadtests/) |

Observability compose: [observability/README.md](observability/README.md). Started automatically by `./scripts/fullstack.sh up`.

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
