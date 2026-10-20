# Search Service

Go read API over **OpenSearch**, fed asynchronously from **Kafka** catalog facts. Public `GET /search` (no JWT). Publishes `search.performed` for Analytics (later).

## Stack

| Component | Dev default |
|---|---|
| API | Go (`cmd/server`) |
| Index | OpenSearch 2.x (`opensearch` in compose) |
| Broker | Shared **Kafka** at `kafka:9092` on `microservices-net` (start Notification compose first) |
| Cache | Redis (optional, not wired yet) |
| Edge | Nginx TLS on host port **8446** |

## Kafka topics (consume)

Align with Catalog event names:

- `product.created`, `product.updated`, `product.deleted`
- `category.created`, `category.updated`, `category.deleted`
- `stock.changed`

## Kafka topics (publish)

- `search.performed` — query, filters, result count, timestamp (Analytics)

Consumer worker (`cmd/consumer`) — not implemented yet.

## Local run

```bash
docker network create microservices-net 2>/dev/null || true
# Kafka broker
cd ../notification && docker compose up -d kafka
cd ../search
cp .env.example .env
docker compose up --build -d
curl -k https://localhost:8446/healthz
curl -k https://localhost:8446/readyz
curl -k "https://localhost:8446/search?q=desk"
```

On startup the API creates the **`products`** index in OpenSearch if missing (mapping: name, price, category, attributes, availability, etc.).

OpenSearch (direct): `http://localhost:9200/_cluster/health` — `GET http://localhost:9200/products/_mapping`

## Tests

```bash
go test ./...
```

## CI

GitHub Actions: `.github/workflows/search.yml` (lint via `gofmt`, `go vet`, `go test`; image publish on `main` only).
