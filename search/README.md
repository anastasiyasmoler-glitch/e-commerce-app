# Search Service

Full-text search and faceted product discovery.

## Overview

Go HTTP service indexing catalog Kafka events into OpenSearch. Serves public search queries with optional Redis caching and publishes search analytics events.

## Stack

| Component | Technology |
| --- | --- |
| Runtime | Go 1.24 |
| Search engine | OpenSearch 2.x |
| Cache | Redis |
| Messaging | Kafka consumer and producer |
| Edge | Nginx TLS, host port **8446** |

## Getting started

```bash
docker network create microservices-net   # once
cd notification && docker compose up -d kafka
cd ../catalog && docker compose up -d    # catalog outbox → Kafka
cd ../search
cp .env.example .env
docker compose up -d --build
```

| Endpoint | URL |
| --- | --- |
| Health | `https://localhost:8446/healthz`, `/readyz` |
| Search | `https://localhost:8446/search` or gateway `/api/search` |
| OpenAPI | [docs/openapi.yaml](docs/openapi.yaml) |

## Documentation

| File | Description |
| --- | --- |
| [docs/openapi.yaml](docs/openapi.yaml) | Search API |
| [docs/events.md](docs/events.md) | Kafka topics |
| [docs/architecture.md](docs/architecture.md) | Architecture diagrams |

## Search API

Public `GET /search` (no JWT). Typical query parameters:

| Parameter | Description |
| --- | --- |
| `q` | Full-text query |
| `store_id` | Required tenant scope |
| `category_id`, filters | Facets and attributes |
| `page`, `per_page`, `sort` | Pagination and sort |

## Docker services

| Service | Purpose |
| --- | --- |
| `opensearch` | Index |
| `redis` | Response cache |
| `back` | HTTP API |
| `consumer` | Kafka → OpenSearch indexer |
| `nginx` | TLS |

## Testing

```bash
cd search
go test ./...
```

Load tests: `loadtests/`. CI: `.github/workflows/search.yml`.
