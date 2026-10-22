# Analytics service

Laravel service that ingests platform facts from **Kafka** into **ClickHouse** for reporting APIs (later cards: Analyst/Admin REST).

## Stack

- PHP 8.5, Laravel 13
- ClickHouse (`events_raw` staging table)
- Kafka consumer on `microservices-net` (`kafka:9092` from Notification compose)
- Nginx HTTPS on host port **8447**

## Consumed topics

| Topic | Source (typical) |
| --- | --- |
| `user.registered` | Auth |
| `order.created` | Order |
| `order.paid` | Order |
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

## Artisan

```bash
php artisan kafka:consume-analytics
```

## Report APIs (staff JWT via gateway)

| Gateway path | Metrics in `data` |
| --- | --- |
| `GET /api/analytics/users?from=&to=` | `total`, `by_day[]` from `user.registered` |
| `GET /api/analytics/orders?from=&to=` | funnel `created` / `paid` / `cancelled`, `conversion_rate`, `revenue_cents`, `average_order_value_cents`, `currency`, `revenue_by_day[]` from `order.*` (`total_cents` on `order.paid`) |
| `GET /api/analytics/search?from=&to=` | `total`, `top_queries[]`, `zero_result_total`, `zero_result_queries[]` from `search.performed` (`result_count`) |

## Environment

See `.env.example` for `KAFKA_*` and `CLICKHOUSE_*`.
