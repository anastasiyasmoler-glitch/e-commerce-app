-- Apply on existing ClickHouse volumes created before shipped/completed rollups.

ALTER TABLE analytics.orders_daily
    ADD COLUMN IF NOT EXISTS shipped UInt64 DEFAULT 0;

ALTER TABLE analytics.orders_daily
    ADD COLUMN IF NOT EXISTS completed UInt64 DEFAULT 0;

DROP VIEW IF EXISTS analytics.mv_orders_daily;

CREATE MATERIALIZED VIEW analytics.mv_orders_daily
TO analytics.orders_daily
AS
SELECT
    toDate(occurred_at) AS day,
    countIf(topic = 'order.created') AS created,
    countIf(topic = 'order.paid') AS paid,
    countIf(topic = 'order.shipped') AS shipped,
    countIf(topic = 'order.completed') AS completed,
    countIf(topic = 'order.cancelled') AS cancelled,
    sumIf(JSONExtractInt(payload, 'total_cents'), topic = 'order.paid') AS revenue_cents
FROM analytics.events_raw
WHERE topic IN (
    'order.created',
    'order.paid',
    'order.shipped',
    'order.completed',
    'order.cancelled'
)
GROUP BY day;
