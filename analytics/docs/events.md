# Kafka events

Consumer group: `analytics-service-group`. Command: `php artisan kafka:consume-analytics`.

## Consumed topics

Each message is stored in ClickHouse `events_raw` with topic name, JSON payload, and timestamp.

| Topic | Typical source |
| --- | --- |
| `user.registered` | Auth |
| `order.created` | Order |
| `order.paid` | Order |
| `order.shipped` | Order |
| `order.completed` | Order |
| `order.cancelled` | Order |
| `search.performed` | Search |

Payload shape follows the producing service (full JSON document preserved in `events_raw`).

## Published topics

Analytics does not publish Kafka events.

## Rollups

Materialized views aggregate `events_raw` into daily tables (`registrations_daily`, `orders_daily`, `search_daily`, etc.). Report APIs read rollup tables, not raw Kafka payloads directly.

## Message format

UTF-8 JSON record values. Ingestion is at-least-once; consumers use Kafka consumer group offsets.
