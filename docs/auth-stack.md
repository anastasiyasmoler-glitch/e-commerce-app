# Auth Service layout

Bootstrap for Auth: native PHP 8.5, Docker Compose, JWT + RBAC roles, Inertia.js + React.

## Authentication and authorization

| Layer | Choice | Why |
| --- | --- | --- |
| Authentication | **JWT** (`lcobucci/jwt`) | Spec: access (15–30 min) + refresh (7–30 days), user id and roles in the access token. Not Laravel Sanctum (Auth is not Laravel). |
| Token store | **Redis** | Blacklist, sessions, refresh rotation, separate TTLs |
| Authorization | **RBAC + ABAC** | Roles: **Customer** (self-register), **Analyst** (Admin assigns), **Admin** (seed only). RBAC = role permissions; ABAC = extra checks on attributes. Validated inside Auth, not in a gateway. |

Login/register/refresh are not implemented yet — packages and layout only.

## Inertia.js

`@inertiajs/react` in `frontend/`. Auth PHP renders Inertia pages (`Welcome`) without Laravel. Nginx: `/` → PHP, `/assets/` → Vite build, `/api/` → JSON health.

## Docker Compose

`nginx`, `front`, `back`, `sql`, `redis`, `mail`

```powershell
docker compose up --build
```

- https://localhost — Inertia Welcome page (accept self-signed cert)
- https://localhost/api/ — `{"service":"auth","status":"ok"}`
- http://localhost:8025 — Mailhog
