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

CAT-1 is still in progress: Store/Category/Product/Stock models are not migrated yet. Redis, MinIO, Kafka inventory, public REST, and Filament are later cards.
