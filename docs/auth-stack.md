# Auth Service layout

Native PHP 8.5 Auth service (PSR-4, PHPUnit, PostgreSQL, Redis, JWT, Mailhog, Nginx TLS).

## Docker Compose

| Service | Role |
| --- | --- |
| `nginx` | Ports 80 → 443, self-signed TLS |
| `front` | Frontend placeholder |
| `back` | PHP-FPM 8.5 (`services/auth`) |
| `sql` | PostgreSQL |
| `redis` | Sessions, refresh tokens, token blacklist (to be implemented) |
| `mail` | Mailhog (SMTP 1025, UI 8025) |

App URL: `https://localhost` (browser warning on self-signed cert is expected).

## Packages

- `lcobucci/jwt` — access/refresh JWT
- PHPUnit — unit and integration tests

## Not implemented yet

Registration, profile, RBAC/ABAC, `/refresh`, `/logout`. This commit is structure only.
