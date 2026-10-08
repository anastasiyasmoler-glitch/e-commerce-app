# Auth — JWT API

JWT for `/api/*` (access in JSON, refresh in HttpOnly cookie, Redis). Roles: Spatie (`admin`, `customer`, `analyst`). After register (JWT and Google) Auth publishes Kafka `user.registered` when `KAFKA_BROKERS` is reachable. There is no Breeze/Inertia UI.

Service root is `auth/` (`docker-compose.yml`, Laravel, nginx, `.github`).

## Run

```powershell
cd auth
docker compose up --build
```

API: https://localhost/api/login

Self-signed certificate: continue in the browser.

Seeded admin: `admin@example.com` / `password` (role `admin`). Self-register gets role `customer`.

Compose: `back`, `sql`, `redis`, `nginx` (80→443), `mail` (http://localhost:8025), `oidc` (http://localhost:8080, mock Google). `back` and `nginx` join external network `microservices-net`. Auth publishes to `kafka:9092`.

To see the welcome mail, create the network once (`docker network create microservices-net`), start `notification` (Kafka and `consumer`), then this compose.

Google login: `GET /auth/google`. The mock accepts any name plus JSON with `email`. The callback returns JWT JSON, not a session.
