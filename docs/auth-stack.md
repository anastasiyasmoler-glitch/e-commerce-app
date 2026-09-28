# Auth — Laravel Breeze + JWT API

Session auth (Breeze / Inertia) for the Auth UI. JSON JWT API for the storefront SPA.

JWT uses `tymon/jwt-auth` ^2.3 (Laravel 13). Access tokens are signed JWTs; blacklist and refresh records live in Redis (`CACHE_STORE=redis`, keys `auth:refresh:{userId}:{tokenHash}`).

## Run

```powershell
docker compose up --build
```

Open https://localhost — `/register`, `/login`.

API (send `Accept: application/json`):

- `POST /api/register`
- `POST /api/login`
- `POST /api/refresh` — body `{ "refresh_token": "..." }`
- `POST /api/logout` — `Authorization: Bearer` + optional `refresh_token`
- `GET /api/me` — `Authorization: Bearer`

Self-signed certificate: continue in the browser.

Seeded admin: `admin@example.com` / `password` (role `admin`, no admin UI). Self-register gets role `customer`.

Compose: `front`, `back`, `sql`, `redis`, `nginx` (80→443), `mail` (http://localhost:8025).
