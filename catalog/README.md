# Catalog Service

Product catalog, inventory, and store administration.

## Overview

Authoritative data for stores, categories, products, stock, and media. Publishes catalog and inventory facts to Kafka via an outbox relay. Consumes order events for stock reservation. Store administration uses Filament at `/catalog-admin` through the Auth gateway.

## Stack

| Component | Technology |
| --- | --- |
| Runtime | PHP 8.5, Laravel 13 |
| Database | PostgreSQL 16 |
| Cache | Redis |
| Object storage | MinIO |
| Admin UI | Filament |
| Messaging | Kafka (`longlang/phpkafka`), outbox pattern |
| Edge | Nginx TLS, host port **8444** |

## Getting started

```bash
docker network create microservices-net   # once
cd notification && docker compose up -d kafka
cd ../catalog
cp .env.example .env
docker compose up -d --build
```

| Endpoint | URL |
| --- | --- |
| Public API | `https://localhost:8444` or gateway `/api/catalog/*` |
| Filament admin | Gateway `/catalog-admin` |
| OpenAPI | `/docs`, [docs/openapi.yaml](docs/openapi.yaml) |

Postgres: database `catalog`, user/password `marketplace`.

## Documentation

| File | Description |
| --- | --- |
| [docs/openapi.yaml](docs/openapi.yaml) | Public REST API |
| [docs/events.md](docs/events.md) | Kafka topics |
| [docs/architecture.md](docs/architecture.md) | Architecture diagrams |

## Public REST API

No JWT required; `store_id` scopes data.

| Method | Path | Description |
| --- | --- | --- |
| GET | `/stores` | List stores |
| GET | `/categories` | Category tree |
| GET | `/products` | Product listing |
| GET | `/products/{id}` | Product detail |

Search and facets are provided by the Search service.

## Docker services

| Service | Purpose |
| --- | --- |
| `sql`, `redis`, `minio` | Data and assets |
| `back` | Laravel + Filament |
| `consumer` | Kafka inventory consumer |
| `outbox` | Outbox → Kafka relay |
| `nginx` | TLS |

## Testing

```bash
cd catalog
composer install
cp .env.example .env && php artisan key:generate
php vendor/bin/phpunit
```

CI: `.github/workflows/catalog.yml`.
