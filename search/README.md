# Search Service

Go read API over **OpenSearch**, fed asynchronously from **Kafka** catalog facts. Public `GET /search` (no JWT). Publishes `search.performed` for Analytics (later).

## Stack

| Component | Dev default |
|---|---|
| API | Go (`cmd/server`) |
| Index | OpenSearch 2.x (`opensearch` in compose) |
| Broker | Shared **Kafka** at `kafka:9092` on `microservices-net` (start Notification compose first) |
| Cache | Redis — cached `GET /search` responses (`CACHE_ENABLED`, `CACHE_TTL_SECONDS`) |
| Edge | Nginx TLS on host port **8446** |

## Kafka topics (consume)

Align with Catalog event names:

- `product.created`, `product.updated`, `product.deleted`
- `category.created`, `category.updated`, `category.deleted`
- `stock.changed`

## Kafka topics (publish)

- `search.performed` — query, filters, result count, timestamp (Analytics)

Consumer worker: `cmd/consumer` (compose service `consumer`) — indexes catalog Kafka events into OpenSearch.

## Indexing (fill OpenSearch from Catalog)

Search does **not** read Catalog over HTTP. Documents appear when **Search `consumer`** processes Kafka facts published by **Catalog**.

1. **Network + Kafka** (once per machine):

```bash
docker network create microservices-net 2>/dev/null || true
cd notification && docker compose up -d kafka
```

2. **Catalog** (SQL + outbox → Kafka):

```bash
cd catalog && docker compose up -d
# optional demo data
docker compose exec back php artisan db:seed --force
docker compose exec back php artisan cache:clear
```

Ensure **`outbox`** and **`consumer`** services are running in Catalog compose so `product.*` / `category.*` reach Kafka.

3. **Search** (API + indexing consumer + OpenSearch):

```bash
cd search
cp .env.example .env
docker compose up --build -d
```

4. **Verify index** (replace `10` with a real `store_id` from `GET /api/catalog/stores`):

```bash
curl -k "https://localhost/api/search?q=chair&store_id=10&per_page=5"
curl -s "http://localhost:9200/products/_count"
```

If count is `0`, check `docker compose logs consumer` in `search/` and Catalog Kafka/outbox logs. Re-saving a product in Filament re-emits `product.updated`.

## Local run

```bash
docker network create microservices-net 2>/dev/null || true
cd ../notification && docker compose up -d kafka
cd ../search
cp .env.example .env
docker compose up --build -d
curl -k https://localhost:8446/healthz
curl -k https://localhost:8446/readyz
curl -k "https://localhost:8446/search?q=desk&store_id=1&page=1&per_page=20&sort=relevance"
curl -k "https://localhost:8446/search?q=sofa&category_id=2&available=true&price_min=1000&attr.color=natural"
```

On startup the API creates **`products`** and **`categories`** indexes in OpenSearch if missing. Products hold searchable fields; **`categories`** is filled from `category.*` Kafka events (id, name, slug, store) and used to attach human-readable **`label`** on category facets in `GET /search` (`value` stays category id for filters). Catalog stores attribute definitions in SQL (`attributes`, `product_attribute_values`); Kafka still sends `attributes` as a key-value object on product events.

OpenSearch (direct): `http://localhost:9200/_cluster/health` — `GET http://localhost:9200/products/_mapping`

**Typo-tolerant search:** set `FUZZY_SEARCH=true` (default) on the API container. OpenSearch `multi_match` then uses `fuzziness: AUTO` on `name` and `description` (e.g. `soffa` → `sofa`). Disable with `FUZZY_SEARCH=false` for exact token matching only. Not autocomplete — only the `q` you send on `GET /search`.

## API contract

OpenAPI 3: [`openapi.yaml`](openapi.yaml) (`GET /search`, health probes).

Swagger UI (after `docker compose up --build back`):

```text
https://localhost:8446/docs
https://localhost/api/search/docs
```

OpenAPI YAML is served next to the UI (`.../docs/openapi.yaml`). Rebuild Search `back` after changing `search/openapi.yaml`.

## Tests

```bash
go test ./...

# Integration (OpenSearch + Kafka); set env or use defaults for local compose
OPENSEARCH_URL=http://localhost:9200 KAFKA_BROKERS=localhost:9092 \
  go test -tags=integration ./test/integration/... -count=1
```

## Load tests

k6 scenarios: [`loadtests/README.md`](loadtests/README.md) (p95/p99 thresholds in `loadtests/k6/search.js`).

## CI

GitHub Actions: `.github/workflows/search.yml` (lint via `gofmt`, `go vet`, `go test`; image publish on `main` only).
