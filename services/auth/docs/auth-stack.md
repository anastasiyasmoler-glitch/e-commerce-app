# Auth — Laravel Breeze + Inertia (React)

Session auth (Breeze). Roles: Spatie (`admin`, `customer`, `analyst`). JWT is not in this branch.

Корень сервиса — `services/auth` (здесь `docker-compose.yml`, Laravel, nginx, `frontend`, `.github`).

## Run

```powershell
cd services/auth
docker compose up --build
```

Open https://localhost — `/register`, `/login`.

Self-signed certificate: continue in the browser.

Seeded admin: `admin@example.com` / `password` (роль `admin`). Staff UI: `/admin` (Users, роль `analyst`). Self-register gets role `customer`.

Compose: `front` (образ из `./frontend`), `back`, `sql`, `redis`, `nginx` (80→443), `mail` (http://localhost:8025), `oidc` (http://localhost:8080, мок Google).

Google login: `/login` → Sign in with Google. В моке любое имя + JSON с `email`.
