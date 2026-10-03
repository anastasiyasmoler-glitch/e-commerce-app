# Notification Service

Sends email for the e-commerce monorepo and stores notification history in MongoDB.
This service is the only one allowed to send mail. Auth and Order publish events; they do not SMTP.

Host HTTPS is **8443** so it does not collide with Auth on 443.

## Role

- Consumer of Kafka events (welcome mail, order mail) — next ticket
- History documents in MongoDB — this ticket
- REST history API for Admin/Analyst — later

## Layout

Service root is `services/notification/` (Compose, nginx, Laravel, frontend stub).

## Stack

- PHP 8.5, Laravel 13, PSR-4
- MongoDB 7 — collection `notification_logs`
- Apache Kafka (broker in Compose; consumer not wired yet)
- Mailhog (UI http://localhost:8026)
- Nginx: container 443, host **8443**; HTTP host **8081** redirects to HTTPS
- `front` — SPA stub at `/spa/`

## History document

Fields: `event`, `channel`, `recipient`, `payload`, `status`, `attempts`, `error_message`, `is_dlq`, `sent_at`.
Statuses: `pending`, `sent`, `failed`.
Access: `NotificationLogRepositoryInterface` → `MongoNotificationLogRepository`.

## Docker

| Service | Role |
|---|---|
| `mongo` | History store |
| `kafka` | Broker (9092) |
| `mail` | Mailhog |
| `back` | php-fpm |
| `front` | Stub SPA |
| `nginx` | TLS + FastCGI |

## Run

```powershell
cd services/notification
docker compose up --build
```

Open https://localhost:8443 (self-signed cert). Mailhog: http://localhost:8026.

## Tests

```powershell
php vendor/bin/phpunit
```

No Kafka consumer, welcome mail, or REST list in this ticket.
