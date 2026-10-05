# E-commerce monorepo (dev)

JWT Auth: https://localhost  
Notification: https://localhost:8443  
Mailhog (shared): http://localhost:8025  
OIDC mock: http://localhost:8080  

## Joint stack (one Kafka)

From the repo root:

```powershell
docker compose up --build
```

Shared broker: service `kafka` on the `events` network. Auth and Notification PHP both use `KAFKA_BROKERS=kafka:9092`.

Consumer is not started by compose:

```powershell
docker compose exec notification-back php artisan kafka:consume-notifications
```

No API Gateway here. Auth binds host 443, Notification 8443.

## One service only

```powershell
cd services/auth
docker compose up --build
```

```powershell
cd services/notification
docker compose up --build
```

Those files do **not** start Kafka (and Auth/Notification Mailhog still clash if both isolated stacks run). Do not run a service compose and the root compose at the same time (port clash). Kafka exists only in this root file.

Auth publishes `user.registered` on API and web register. Isolated Auth compose has no Kafka; use this root stack plus `kafka:consume-notifications` to see Mailhog mail.
