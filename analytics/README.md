# Analytics service

Laravel service that ingests platform facts from **Kafka** into **ClickHouse** for reporting APIs (later cards: Analyst/Admin REST).

## Stack

- PHP 8.5, Laravel 13
- ClickHouse: `events_raw` (staging) + daily rollups maintained by **materialized views**
- Kafka consumer on `microservices-net` (`kafka:9092` from Notification compose)
- Nginx HTTPS on host port **8447**

## Consumed topics

| Topic | Source (typical) |
| --- | --- |
| `user.registered` | Auth |
| `order.created` | Order |
| `order.paid` | Order |
| `order.shipped` | Order (staff ship) |
| `order.completed` | Order (staff complete) |
| `order.cancelled` | Order |
| `search.performed` | Search |

## Local run

1. Ensure external network and Kafka are up (Notification `docker compose up -d`).
2. From this directory:

```bash
docker compose up -d --build
```

3. Health (direct to service nginx):
   - `https://localhost:8447/api/healthz` — process up
   - `https://localhost:8447/api/readyz` — ClickHouse ping

   Through **Auth gateway** (same paths under `/api/analytics`, CORS handled by Auth nginx):
   - `https://localhost/api/analytics/healthz`
   - `https://localhost/api/analytics/readyz`

4. Consumer logs: `docker compose logs -f consumer`

Laravel SQLite is only for framework tables; analytics data lives in ClickHouse.

## ClickHouse schema

| Table | Role |
| --- | --- |
| `events_raw` | One row per Kafka message (`topic`, JSON `payload`, `occurred_at`) |
| `registrations_daily` | MV `mv_registrations_daily` — registrations per day |
| `orders_daily` | MV `mv_orders_daily` — created / paid / cancelled / `revenue_cents` per day |
| `search_daily` | MV `mv_search_daily` — searches and zero-result counts per day |
| `search_query_daily` | MV `mv_search_query_daily` — per query per day (top / zero lists) |

SQL: `docker/clickhouse/init/01_schema.sql`, `02_rollups.sql`.

**Existing data before rollups:** MVs only apply to new inserts. Backfill once:

```bash
docker compose exec clickhouse clickhouse-client --multiquery < docker/clickhouse/init/03_backfill_rollups.sql
```

(or run that file from the `analytics/` directory on the host with `clickhouse-client`).

Report APIs read rollup tables (`sum(...)` by day range). `currency` on orders still uses a lightweight lookup on `events_raw`.

## Artisan

```bash
php artisan kafka:consume-analytics
```

## API contract

OpenAPI: [`openapi.yaml`](openapi.yaml) (gateway base `https://localhost/api/analytics`).

## Report APIs (staff JWT via gateway)

| Gateway path | Metrics in `data` |
| --- | --- |
| `GET /api/analytics/users?from=&to=` | `total`, `by_day[]` from `user.registered` |
| `GET /api/analytics/orders?from=&to=` | funnel `created` / `paid` / `shipped` / `completed` / `cancelled`, `conversion_rate`, `completion_rate`, revenue fields from `order.*` |
| `GET /api/analytics/search?from=&to=` | `total`, `top_queries[]`, `zero_result_total`, `zero_result_queries[]` from `search.performed` (`result_count`) |

## Environment

See `.env.example` for `KAFKA_*` and `CLICKHOUSE_*`.
