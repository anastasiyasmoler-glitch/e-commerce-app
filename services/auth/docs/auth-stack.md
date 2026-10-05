# Auth — Laravel Breeze + Inertia (React)

Session auth (Breeze) for Inertia. JWT API for `/api/*` (access in JSON, refresh in HttpOnly cookie, Redis). Roles: Spatie (`admin`, `customer`, `analyst`). After register (web and JWT) Auth publishes Kafka `user.registered` when `KAFKA_BROKERS` is reachable.

Корень сервиса — `services/auth` (здесь `docker-compose.yml`, Laravel, nginx, `frontend`, `.github`).

## Run

```powershell
cd services/auth
docker compose up --build
```

Open https://localhost — `/register`, `/login`.

Self-signed certificate: continue in the browser.

Seeded admin: `admin@example.com` / `password` (роль `admin`). Staff UI: `/admin` (Users, роль `analyst`). Self-register gets role `customer`.

Compose: `front` (образ из `./frontend`), `back`, `sql`, `redis`, `nginx` (80→443), `mail` (http://localhost:8025), `oidc` (http://localhost:8080, мок Google). Isolated compose has no Kafka.

To publish `user.registered` and see mail, use the repo-root `docker-compose.yml` plus Notification consumer.

Google login: `/login` → Sign in with Google. В моке любое имя + JSON с `email`.
