# Catalog Service

Source of truth for stores, categories, products, images, and stock. Checkpoint 2. Laravel 13, PHP 8.5.

Kafka stays in the Notification compose (`kafka:9092`). This service joins `microservices-net`. Store admin UI is Filament inside this service (not started). The storefront is `frontend/`. Shared `admin/` SPA does not manage catalog.

## Run

Bring Notification up first so Kafka exists, then Catalog.

```powershell
docker network create microservices-net
cd notification
docker compose up -d kafka
cd ../catalog
docker compose up --build
```

https://localhost:8444 (self-signed). HTTP 8082 redirects to HTTPS.

Postgres user/password/database: `marketplace` / `marketplace` / `catalog`.

Compose services: `sql`, `redis`, `minio` (host 9004/9005), `minio-init`, `back`, `consumer`, `nginx`.

Tables: `stores`, `categories` (tree via `parent_id`), `products`, `stocks` (`quantity`, `reserved`). Isolation is `store_id` on rows.

## Public REST (no JWT)

- `GET /categories?store_id=&parent_id=`
- `GET /products?store_id=&category_id=&page=`
- `GET /products/{id}?store_id=`

Full-text and facets belong in Search, not here.

## Kafka

Group: `catalog-service-group`. Consumer: `php artisan kafka:consume-catalog`.

Publishes: `product.created`, `product.updated`, `product.deleted`, `category.created`, `category.updated`, `category.deleted`, `stock.changed`, `stock.reserved`, `stock.reservation_failed`.

Consumes: `order.created`, `stock.release_requested`.

`order.created` payload: `{ "order_id", "items": [{ "product_id", "store_id", "quantity" }] }`. Available stock is `quantity - reserved`. Tests use `ArrayKafkaPublisher`; they do not need a live broker.

## Tests

```powershell
php vendor/bin/phpunit
```

CI: `.github/workflows/catalog.yml` (Pint, PHPUnit, image publish on `main`).
