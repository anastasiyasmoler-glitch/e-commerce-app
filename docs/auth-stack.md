# Auth — Laravel Breeze + Inertia (React)

Session auth (Breeze). Roles: Spatie (`admin`, `customer`, `analyst`). JWT is not in this branch.

## Run

```powershell
docker compose up --build
```

Open https://localhost — `/register`, `/login`.

Self-signed certificate: continue in the browser.

Seeded admin: `admin@example.com` / `password` (роль `admin`, отдельной админ-страницы нет). Self-register gets role `customer`.

Compose: `front`, `back`, `sql`, `redis`, `nginx` (80→443), `mail` (http://localhost:8025).
