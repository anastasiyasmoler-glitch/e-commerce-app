# Recommendation Service

AI-assisted product discovery and conversational recommendations.

## Overview

Builds a local catalog projection and vector index from Kafka events, answers natural-language shopping queries using Gemini embeddings and Qdrant similarity search, with conversation history stored in PostgreSQL.

## Stack

| Component | Technology |
| --- | --- |
| Runtime | PHP 8.5, Laravel 13 |
| Projection DB | PostgreSQL 16 |
| Vector index | Qdrant |
| LLM | Google Gemini API |
| Messaging | Kafka consumer |
| Edge | Nginx TLS, host port **8448** |

## Getting started

```bash
docker network create microservices-net   # once
cd notification && docker compose up -d kafka
cd ../recommendation
cp .env.example .env
# set GEMINI_API_KEY
sh docker/nginx/generate-certs.sh
docker compose up -d --build
```

| Endpoint | URL |
| --- | --- |
| Health | `https://localhost:8448/recommendations/health` |
| Gateway | `https://localhost/api/recommendations/*` |
| OpenAPI | `/docs`, [docs/openapi.yaml](docs/openapi.yaml) |
| Qdrant | `http://localhost:6333` |

## Documentation

| File | Description |
| --- | --- |
| [docs/openapi.yaml](docs/openapi.yaml) | Chat and conversation API |
| [docs/events.md](docs/events.md) | Kafka topics |
| [docs/architecture.md](docs/architecture.md) | Architecture diagrams |

## REST API

| Method | Path | Description |
| --- | --- | --- |
| POST | `/recommendations/chat` | Chat turn with product suggestions |
| GET | `/recommendations/conversations/{id}` | Conversation history |
| DELETE | `/recommendations/conversations/{id}` | Delete conversation |
| POST | `/recommendations/reindex` | Rebuild vectors (admin) |

Rate limits and schemas: OpenAPI.

## Indexing

1. Consume catalog Kafka events → upsert `catalog_product_projections`.
2. Build embedding text → Gemini `embed` → upsert Qdrant collection `catalog_products`.
3. Chat: extract filters → vector search → verify against local projection → LLM reply.

## Docker services

| Service | Purpose |
| --- | --- |
| `postgres`, `qdrant` | Projection and vectors |
| `back`, `nginx` | API |
| `consumer` | `kafka:consume-catalog-index` |

## Testing

```bash
cd recommendation
composer install
cp .env.example .env && php artisan key:generate
php artisan test
```

Integration suite: set `RECOMMENDATION_INTEGRATION_TESTS=1` with PostgreSQL and Qdrant reachable. Kafka ingest test also needs `KAFKA_BROKERS` (CI provides both).

```bash
export RECOMMENDATION_INTEGRATION_TESTS=1
export RECOMMENDATION_TEST_DB_HOST=127.0.0.1
export RECOMMENDATION_TEST_DB_PORT=5435
export QDRANT_HOST=127.0.0.1
export QDRANT_PORT=6333
export KAFKA_BROKERS=127.0.0.1:9092
php artisan test --testsuite=Integration
```

CI: `.github/workflows/recommendation.yml`.
