# Kafka events

Configuration: `config/kafka.php`. Consumer group: `notification-service-group`.

## Consumed topics

| Topic | Handler | Email |
| --- | --- | --- |
| `user.registered` | Welcome | UserRegisteredMail |
| `order.paid` | Order paid | OrderPaidMail |
| `order.confirmed` | Order confirmed | OrderConfirmedMail |
| `order.cancelled` | Order cancelled | OrderCancelledMail |

Payload JSON must include `email` or `recipient` for routing. Additional fields are passed to Blade templates (order id, names, etc., per event).

Delivery: up to `KAFKA_MAX_ATTEMPTS` (default 3) with exponential backoff. Failed messages are recorded in MongoDB and may be published to the DLQ topic.

## Published topics

| Topic | When |
| --- | --- |
| `notifications.dlq` | After max SMTP retry attempts (`KAFKA_DLQ_TOPIC`) |

## Message format

Record values are UTF-8 JSON objects. Event-specific fields mirror the producing service’s contract (see Auth, Order documentation).

## Consumed by

This service does not expose consumed topics to other apps; it is a terminal consumer for the listed mail triggers.
