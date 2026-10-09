# Catalog Service

Source of truth for stores, categories, products, images, and stock. Checkpoint 2. Laravel 13, PHP 8.5.

Kafka stays in the Notification compose. This service joins `microservices-net`. Catalog admin will be Filament here later. The storefront is `frontend/`.

## Run

```powershell
docker network create microservices-net
cd catalog
docker compose up --build
```

https://localhost:8444 (self-signed). HTTP 8082 redirects to HTTPS.

Postgres user/password/database: `marketplace` / `marketplace` / `catalog`.

Tables: `stores`, `categories` (tree via `parent_id`), `products`, `stocks` (`quantity`, `reserved`). Isolation is `store_id` on rows.

Redis cache and MinIO (`product-images`, console http://localhost:9005, login `catalog` / `catalogsecret`) are in this compose. Kafka, public REST, and Filament stay on this same CAT-1 branch.

Restart: `docker compose up --build` in `catalog/`.
