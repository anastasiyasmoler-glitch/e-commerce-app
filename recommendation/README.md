# Recommendation Service

AI-assisted product recommendations from **Catalog** data only.

## Stack

- Laravel (PHP 8.5)
- **Kafka** — catalog product facts
- **PostgreSQL** — local product projection (`catalog_product_projections`, JSON metadata)
- **Qdrant** — vector index (Gemini embeddings)
- **Google Gemini** — `LlmClient` (`embed` + `generate` for future chat)
- Nginx HTTPS on **8448** (`recommendation-nginx` on `microservices-net`)

## Indexing flow

1. `product.created` / `product.updated` → upsert row in Postgres.
2. Build text from payload → `GeminiLlmClient::embed()`.
3. Upsert point in Qdrant (cosine, collection `catalog_products`).
4. `product.deleted` → delete from Postgres and Qdrant.

If Gemini or Qdrant fails, the Postgres row is still saved; check logs for `catalog_vector_index_failed`.

Additional topics: `stock.changed`, `product.published`, `product.unpublished` (metadata-only updates when applicable).

## Chat verification (read-model)

Before the LLM answer, candidate products are verified against the **local** `catalog_product_projections` read model (synced from Catalog Kafka). There is **no** synchronous Catalog HTTP call on the chat path.

`CatalogReadModelProductVerifier` drops rows that are unpublished, out of stock (when `in_stock_only` is set), missing a name, or whose `product_id` / `store_id` do not match the projection payload.

## Local run

Prerequisites: `microservices-net`, Kafka (`notification` compose).

```bash
cd recommendation
cp .env.example .env
# Set GEMINI_API_KEY in .env (not committed)
docker compose up -d --build
```

- Health: `curl -k https://localhost:8448/recommendations/health` (`database`, `qdrant`, `catalog_projection_table`)
- Qdrant UI/API: `http://localhost:6333`
- Postgres: host port **5433**

If you previously used pgvector in Postgres, run migrations after pull; for a clean slate: `docker compose down -v` in `recommendation/`.

## Tests

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan test
```

CI uses SQLite; Qdrant health is `skipped` in `testing` env.
