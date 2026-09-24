# Auth bootstrap — Laravel Breeze + Inertia (React)

Auth package: Laravel Breeze (session). Inertia.js for the UI. JWT and roles are not in this init.

## Run

```powershell
docker compose up --build
```

Open:

- https://localhost — `/register`, `/login`
- https://localhost/spa/ — frontend stub
- http://localhost:8025 — Mailhog

Self-signed certificate: continue in the browser.

Compose: `front`, `back` (Laravel PHP 8.4-FPM), `sql`, `redis`, `nginx` (80→443), `mail`.
