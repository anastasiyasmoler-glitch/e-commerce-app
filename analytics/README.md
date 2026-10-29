# Analytics Service

Event ingestion and reporting APIs.

## Overview

Consumes platform Kafka topics, stores raw events in ClickHouse, maintains daily rollups via materialized views, and exposes aggregated REST endpoints for staff users through the Auth gateway.

## Stack

| Component | Technology |
| --- | --- |
| Runtime | PHP 8.5, Laravel 13 |
| Analytics store | ClickHouse |
| Messaging | Kafka consumer |
| Framework DB | SQLite (Laravel system tables only) |
| Edge | Nginx TLS, host port **8447** |

## Getting started

```bash
docker network create microservices-net   # once
cd notification && docker compose up -d kafka
cd ../analytics
cp .env.example .env
docker compose up -d --build
```

| Endpoint | URL |
| --- | --- |
| Health | `https://localhost:8447/api/healthz`, `/api/readyz` |
| Gateway | `https://localhost/api/analytics/*` |
| OpenAPI | `/docs`, [docs/openapi.yaml](docs/openapi.yaml) |

## Documentation

| File | Description |
| --- | --- |
| [docs/openapi.yaml](docs/openapi.yaml) | Report APIs |
| [docs/events.md](docs/events.md) | Kafka topics |
| [docs/architecture.md](docs/architecture.md) | Architecture diagrams |

## Report APIs

Staff JWT via gateway (`admin`, `analyst`):

| Method | Path | Metrics |
| --- | --- | --- |
| GET | `/analytics/users` | Registrations by day |
| GET | `/analytics/orders` | Order funnel and revenue |
| GET | `/analytics/search` | Search volume and zero-result queries |

Query parameters: `from`, `to` (date range). Schemas in OpenAPI.

## ClickHouse

| Object | Role |
| --- | --- |
| `events_raw` | Staging table (one row per Kafka message) |
| `*_daily` tables | Rollups via materialized views |

SQL initialization: `docker/clickhouse/init/`.

## Docker services

| Service | Purpose |
| --- | --- |
| `clickhouse` | Analytics storage |
| `consumer` | `kafka:consume-analytics` |
| `back`, `nginx` | API |

## Testing

```bash
cd analytics
composer install
cp .env.example .env && php artisan key:generate
php vendor/bin/phpunit
```

Integration tests need ClickHouse and Kafka (CI sets `ANALYTICS_CLICKHOUSE_TESTS=1`, `KAFKA_BROKERS`). Locally:

```bash
export ANALYTICS_CLICKHOUSE_TESTS=1
export CLICKHOUSE_HOST=127.0.0.1
export CLICKHOUSE_PORT=8123
export KAFKA_BROKERS=127.0.0.1:9092
php artisan test --testsuite=Integration
```

Coverage: user/order/search rollups in ClickHouse, Kafka publish → consume → `events_raw`.

CI: `.github/workflows/analytics.yml`.
