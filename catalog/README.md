# Catalog Service

Source of truth for stores, categories, products, images, and stock. Checkpoint 2. Laravel 13, PHP 8.5.

Kafka stays in the Notification compose (`kafka:9092`). This service joins `microservices-net`. Store admin UI is **Filament** at `/catalog-admin` (via gateway). The storefront is `frontend/`. Shared `admin/` SPA links to Filament; it does not manage catalog CRUD.

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

Compose services: `sql`, `redis`, `minio` (host 9004/9005), `minio-init`, `back`, `consumer`, `outbox`, `nginx`.

Tables: `stores`, `categories` (tree via `parent_id`), `products`, `stocks` (`quantity`, `reserved`). Isolation is `store_id` on rows.

## Public REST (no JWT)

- `GET /stores`
- `GET /categories?store_id=&parent_id=`
- `GET /products?store_id=&category_id=&page=`
- `GET /products/{id}?store_id=`

Full-text and facets belong in Search, not here.

## Kafka

Group: `catalog-service-group`. Consumer: `php artisan kafka:consume-catalog`.

Publishes: `product.created`, `product.updated`, `product.deleted`, `product.published`, `product.unpublished`, `category.created`, `category.updated`, `category.deleted`, `stock.changed`, `stock.reserved`, `stock.reservation_failed`.

Product events include `is_published` and `in_stock` (from available stock). New products default to unpublished until Filament toggle is on.

Consumes: `order.created`, `stock.release_requested`.

`order.created` payload: `{ "order_id", "items": [{ "product_id", "store_id", "quantity" }] }`. Available stock is `quantity - reserved`. Tests use `ArrayKafkaPublisher`; they do not need a live broker.

## Tests

```powershell
php vendor/bin/phpunit
```

CI: `.github/workflows/catalog.yml` (Pint, PHPUnit, image publish on `main`).
