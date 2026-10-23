# Recommendation Service

AI-assisted product recommendations from **Catalog** data only. This service keeps a local projection (PostgreSQL + **pgvector**) and will expose chat APIs behind the gateway.

## Stack

- Laravel (PHP 8.5)
- **Kafka** — async index updates from Catalog (same topic names as Catalog/Search/Order)
- PostgreSQL 16 + **pgvector** for embeddings
- Google Gemini (via `LlmClient` — not wired in RECOMMENDATION-1)
- Nginx HTTPS on **8448** (`recommendation-nginx` on `microservices-net`)

## Kafka topics (consumer)

| Topic | Intended action |
|---|---|
| `product.created` | Add to index |
| `product.updated` | Recompute embedding |
| `product.deleted` | Remove from index |
| `product.published` / `product.unpublished` | Include/exclude (when Catalog emits them) |
| `stock.changed` | Update availability |

RECOMMENDATION-1 ships a **stub** consumer (`kafka:consume-catalog-index`) that logs events; indexing and Gemini land in later cards.

## Local run

Prerequisites: external Docker network `microservices-net` and Kafka (`notification` compose).

```bash
cd recommendation
cp .env.example .env
docker compose up -d --build
```

- Health: `curl -k https://localhost:8448/recommendations/health`
- Via gateway (after Auth nginx reload): `curl -k https://localhost/api/recommendations/health`
- Consumer logs: `docker compose logs -f consumer`

Postgres is exposed on host port **5433** for debugging.

## Environment

See `.env.example` for `KAFKA_*`, `DB_*`, `GEMINI_*`, and `CATALOG_BASE_URL`.

## Tests

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan test
```
