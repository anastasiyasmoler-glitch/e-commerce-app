# Order Service

Order lifecycle, local product snapshots, Stripe test payments, and Kafka saga choreography. Checkpoint 2. Laravel 13, PHP 8.5.

Broker: **Kafka** on `microservices-net` (Notification compose). Checkout UI lives in monorepo **`frontend/`** (`/cart`, `/checkout`, `/orders`).

## Run

```bash
docker network create microservices-net   # once
cd notification && docker compose up -d kafka
cd ../order
cp .env.example .env
docker compose up -d --build
```

- HTTPS: `https://localhost:8445` (direct nginx) or gateway `https://localhost/api/orders`
- Postgres host port **5434** (database `orders`)
- Workers: `consumer` (Kafka), `outbox-relay` (outbox → Kafka)
- Optional Stripe CLI profile: `docker compose --profile stripe up`

## REST (gateway identity headers or JWT via Auth)

- `POST /orders` — create (customer, `Idempotency-Key` required)
- `GET /orders`, `GET /orders/{id}`
- `POST /orders/{id}/cancel`
- `POST /webhooks/stripe` — no JWT (signature verification)

OpenAPI: `docs/openapi.yaml`, Swagger UI `/docs`.

## Kafka

**Consumes:** `product.created`, `product.updated` (snapshots); `stock.reserved`, `stock.reservation_failed`.

**Publishes:** `order.created`, `order.paid`, `order.confirmed`, `order.cancelled`, `order.payment_failed`, `stock.release_requested`, plus fulfillment events.

## Tests

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan test
```
