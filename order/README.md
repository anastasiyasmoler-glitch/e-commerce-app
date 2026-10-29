# Order Service

Order lifecycle, payments, and checkout orchestration.

## Overview

Manages customer orders, local product snapshots, Stripe payments, and saga steps over Kafka. Uses PostgreSQL with an outbox for reliable event publishing. Checkout UI is in the monorepo Frontend application.

## Stack

| Component | Technology |
| --- | --- |
| Runtime | PHP 8.5, Laravel 13 |
| Database | PostgreSQL 16 |
| Cache | Redis (idempotency keys) |
| Payments | Stripe (test mode) |
| Messaging | Kafka, outbox relay |
| Edge | Nginx TLS, host port **8445** |

## Getting started

```bash
docker network create microservices-net   # once
cd notification && docker compose up -d kafka
cd ../order
cp .env.example .env
docker compose up -d --build
```

| Endpoint | URL |
| --- | --- |
| API (direct) | `https://localhost:8445` |
| Gateway | `https://localhost/api/orders` |
| OpenAPI | `/docs`, [docs/openapi.yaml](docs/openapi.yaml) |
| Webhook | `POST /webhooks/stripe` (signature verified, no JWT) |

Optional: `docker compose --profile stripe up` for Stripe CLI forwarding.

## Documentation

| File | Description |
| --- | --- |
| [docs/openapi.yaml](docs/openapi.yaml) | REST API |
| [docs/events.md](docs/events.md) | Kafka topics |
| [docs/architecture.md](docs/architecture.md) | Architecture diagrams |

## REST API

| Method | Path | Description |
| --- | --- | --- |
| POST | `/orders` | Create order (`Idempotency-Key` header) |
| GET | `/orders` | List orders (own or all for staff) |
| GET | `/orders/{id}` | Order detail |
| POST | `/orders/{id}/cancel` | Cancel before shipment |
| POST | `/orders/{id}/ship` | Mark shipped (staff) |
| POST | `/orders/{id}/complete` | Mark completed (staff) |

## Docker services

| Service | Purpose |
| --- | --- |
| `sql`, `redis` | State and idempotency |
| `back` | Laravel API |
| `consumer` | Kafka consumer |
| `outbox-relay` | Publish outbox to Kafka |
| `nginx` | TLS |

## Testing

```bash
cd order
composer install
cp .env.example .env && php artisan key:generate
php vendor/bin/phpunit
```

CI: `.github/workflows/order.yml`.
