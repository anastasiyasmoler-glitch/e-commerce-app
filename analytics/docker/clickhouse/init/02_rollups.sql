-- Daily rollups fed automatically on each INSERT into events_raw.

CREATE TABLE IF NOT EXISTS analytics.registrations_daily
(
    day Date,
    registrations UInt64
)
ENGINE = SummingMergeTree
ORDER BY (day);

CREATE MATERIALIZED VIEW IF NOT EXISTS analytics.mv_registrations_daily
TO analytics.registrations_daily
AS
SELECT
    toDate(occurred_at) AS day,
    count() AS registrations
FROM analytics.events_raw
WHERE topic = 'user.registered'
GROUP BY day;

CREATE TABLE IF NOT EXISTS analytics.orders_daily
(
    day Date,
    created UInt64,
    paid UInt64,
    cancelled UInt64,
    revenue_cents Int64
)
ENGINE = SummingMergeTree
ORDER BY (day);

CREATE MATERIALIZED VIEW IF NOT EXISTS analytics.mv_orders_daily
TO analytics.orders_daily
AS
SELECT
    toDate(occurred_at) AS day,
    countIf(topic = 'order.created') AS created,
    countIf(topic = 'order.paid') AS paid,
    countIf(topic = 'order.cancelled') AS cancelled,
    sumIf(JSONExtractInt(payload, 'total_cents'), topic = 'order.paid') AS revenue_cents
FROM analytics.events_raw
WHERE topic IN ('order.created', 'order.paid', 'order.cancelled')
GROUP BY day;

CREATE TABLE IF NOT EXISTS analytics.search_daily
(
    day Date,
    searches UInt64,
    zero_results UInt64
)
ENGINE = SummingMergeTree
ORDER BY (day);

CREATE MATERIALIZED VIEW IF NOT EXISTS analytics.mv_search_daily
TO analytics.search_daily
AS
SELECT
    toDate(occurred_at) AS day,
    count() AS searches,
    countIf(JSONExtractInt(payload, 'result_count') = 0) AS zero_results
FROM analytics.events_raw
WHERE topic = 'search.performed'
GROUP BY day;

CREATE TABLE IF NOT EXISTS analytics.search_query_daily
(
    day Date,
    query String,
    searches UInt64,
    zero_results UInt64
)
ENGINE = SummingMergeTree
ORDER BY (day, query);

CREATE MATERIALIZED VIEW IF NOT EXISTS analytics.mv_search_query_daily
TO analytics.search_query_daily
AS
SELECT
    toDate(occurred_at) AS day,
    JSONExtractString(payload, 'query') AS query,
    count() AS searches,
    countIf(JSONExtractInt(payload, 'result_count') = 0) AS zero_results
FROM analytics.events_raw
WHERE topic = 'search.performed'
  AND JSONExtractString(payload, 'query') != ''
GROUP BY day, query;
