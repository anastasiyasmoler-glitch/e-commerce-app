# Notification Service

Sends email for the e-commerce monorepo and stores history in MongoDB.
Only this service sends product mail. Auth and Order should publish Kafka events; they must not SMTP.

Host HTTPS is **8443** (Auth uses 443). Mailhog UI: http://localhost:8026.

## Role

- Kafka **consumer** — welcome and order mail (this ticket, NOTIF-2)
- History in MongoDB `notification_logs`
- REST list for Admin/Analyst — NOTIF-3
- UI — NOTIF-4

Auth publishes `user.registered` on register. Kafka runs in this service's compose (`hostname: kafka`) and is attached to the external network `microservices-net`, so Auth can publish to `kafka:9092`. Create that network once: `docker network create microservices-net`.

## Stack

- PHP 8.5, Laravel 13, `longlang/phpkafka`, `mongodb/laravel-mongodb`
- MongoDB 7
- Apache Kafka 3.9.1 (KRaft), group `notification-service-group`
- Mailhog
- Nginx: container 443, host 8443; HTTP 8081 → HTTPS

## Kafka

| Topic | Mail |
|---|---|
| `user.registered` | Welcome |
| `order.paid` | Paid |
| `order.confirmed` | Confirmed |
| `order.cancelled` | Cancelled (`reason: out_of_stock` in payload) |
| `notifications.dlq` | After 3 failed SMTP attempts (`is_dlq` in Mongo). No replay. |

Payload JSON must include `email` or `recipient`.

`docker compose up` in this directory starts `kafka` and `consumer` (`php artisan kafka:consume-notifications`).

Retry: 3 attempts, backoff 200ms × 2^(n-1). See `config/kafka.php`.

## History document

Fields: `event`, `channel`, `recipient`, `payload`, `status`, `attempts`, `error_message`, `is_dlq`, `sent_at`.
Statuses: `pending`, `sent`, `failed`.

## Docker

| Service | Role |
|---|---|
| `mongo` | History |
| `mail` | Mailhog |
| `kafka` | Broker, also on `microservices-net` |
| `back` | php-fpm |
| `consumer` | `kafka:consume-notifications` |
| `front` | Stub SPA |
| `nginx` | TLS + FastCGI |

```powershell
cd notification
docker compose up --build
```

https://localhost:8443 (self-signed). Mailhog UI: http://localhost:8026.

## Tests

```powershell
php vendor/bin/phpunit
```

Unit: `NotificationLogTest`, `NotificationDispatchServiceTest` (успех SMTP, 3 фейла → DLQ, неизвестный топик). Живой Kafka/Mongo/Mailhog в PHPUnit нет.
