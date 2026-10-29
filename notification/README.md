# Notification Service

Email delivery and notification history for the platform.

## Overview

Consumes Kafka events, sends SMTP mail (Mailhog in development), persists delivery history in MongoDB, and exposes a read API for staff users via the Auth gateway.

Kafka runs in this service’s Docker Compose (`kafka:9092` on `microservices-net`).

## Stack

| Component | Technology |
| --- | --- |
| Runtime | PHP 8.5, Laravel 13 |
| Messaging | Apache Kafka, `longlang/phpkafka` |
| History | MongoDB 7 |
| Mail | SMTP → Mailhog (dev) |
| Edge | Nginx TLS, host port **8443** |

## Getting started

```bash
docker network create microservices-net   # once
cd notification
cp .env.example .env
docker compose up -d --build
```

| Endpoint | URL |
| --- | --- |
| HTTPS | `https://localhost:8443` |
| Mailhog UI | `http://localhost:8026` |
| Kafka (internal) | `kafka:9092` |

## Documentation

| File | Description |
| --- | --- |
| [docs/openapi.yaml](docs/openapi.yaml) | REST API specification |
| [docs/events.md](docs/events.md) | Kafka topics |
| [docs/architecture.md](docs/architecture.md) | Architecture diagrams |

## REST API

Staff roles (`admin`, `analyst`) via gateway identity:

| Method | Path | Description |
| --- | --- | --- |
| GET | `/api/notifications` | List notification log entries |
| GET | `/api/notifications/{id}` | Single entry |

Identity is resolved through Auth (`GET /me` pattern); OpenAPI for request/response schemas.

## Docker services

| Service | Purpose |
| --- | --- |
| `kafka` | Message broker |
| `consumer` | `kafka:consume-notifications` |
| `mongo` | Notification history |
| `back` | Laravel API |
| `nginx` | TLS |
| `mail` | Mailhog |

## Configuration

See `.env.example` (`KAFKA_*`, MongoDB, mail, `AUTH_BASE_URL`).

## Testing

```bash
cd notification
composer install
cp .env.example .env && php artisan key:generate
php vendor/bin/phpunit
```

CI: `.github/workflows/notification.yml`.
