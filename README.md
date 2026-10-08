# E-commerce monorepo (dev)

Nine apps: Auth, Notification, Catalog, Order, Recommendation, Search, Analytics, customer Frontend, and Admin. Catalog also has its own Filament admin inside the Catalog service (not the shared Admin SPA).

Human requests go Frontend or Admin → service → Auth (`GET /api/me` and JWT). Service-to-service facts go through Kafka, not through Auth.

Auth: https://localhost  
Notification: https://localhost:8443  
Auth Mailhog: http://localhost:8025  
Notification Mailhog: http://localhost:8026  
OIDC mock: http://localhost:8080  

There is no root compose file. Auth and Notification each have `docker-compose.yml`. They share one Docker network, `microservices-net`. Kafka runs inside the Notification compose. Auth reaches it as `kafka:9092`. Notification calls Auth at `https://auth-nginx/api/me`.

Create the network once:

```powershell
docker network create microservices-net
```

Start Notification first (it owns Kafka), then Auth:

```powershell
cd services/notification
docker compose up --build
```

```powershell
cd services/auth
docker compose up --build
```

`consumer` in the Notification compose runs `php artisan kafka:consume-notifications`.
