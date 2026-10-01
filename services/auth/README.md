# Auth Service

Сервис аутентификации и ролей учебного e-commerce монорепо.

Корень сервиса — эта папка: Laravel-приложение, `docker-compose.yml`, nginx, заглушка `frontend/`.

## Запуск

```powershell
cd services/auth
docker compose up --build
```

Открыть https://localhost (self-signed сертификат — продолжить в браузере).

- Регистрация: `/register`
- Вход: `/login`
- Админ (сид): `admin@example.com` / `password`

Подробнее: [docs/auth-stack.md](docs/auth-stack.md).
