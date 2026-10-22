CREATE DATABASE IF NOT EXISTS analytics;

CREATE TABLE IF NOT EXISTS analytics.events_raw
(
    event_id UUID DEFAULT generateUUIDv4(),
    topic String,
    payload String,
    occurred_at DateTime64(3, 'UTC') DEFAULT now64(3)
)
ENGINE = MergeTree
ORDER BY (topic, occurred_at);
