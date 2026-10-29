# Architecture

## Context

```mermaid
flowchart TB
  subgraph clients
    FE[Frontend]
    AD[Admin]
  end

  subgraph auth_svc[Auth]
    NGX[Nginx]
    APP[Laravel]
    DB[(PostgreSQL)]
    RD[(Redis)]
    NGX --> APP
    APP --> DB
    APP --> RD
  end

  KF[(Kafka)]

  subgraph services[Backend services]
    SVC[Catalog, Order, Notification, …]
  end

  FE --> NGX
  AD --> NGX
  NGX --> SVC
  APP -->|user.registered| KF
```

## Gateway request flow

```mermaid
sequenceDiagram
  participant Client
  participant Nginx
  participant Auth as Auth API
  participant Upstream

  Client->>Nginx: HTTPS + Bearer JWT
  Nginx->>Auth: auth_request /_gateway/*
  Auth->>Auth: Validate access JWT
  Auth-->>Nginx: 204 + X-Auth-User-*
  Nginx->>Upstream: Proxy request
  Upstream-->>Nginx: Response
  Nginx-->>Client: Response
```

| Subrequest | Allowed roles |
| --- | --- |
| `GET /_gateway/customer` | Authenticated user |
| `GET /_gateway/staff` | `admin`, `analyst` |
| `GET /_gateway/admin` | `admin` |

## Token lifecycle

```mermaid
stateDiagram-v2
  [*] --> Login: register / login
  Login --> Active: access + refresh issued
  Active --> Active: refresh (cookie)
  Active --> Revoked: logout
  Revoked --> [*]
```

- Access JWT: short TTL, sent in `Authorization` header.
- Refresh JWT: long TTL, HttpOnly cookie, Redis-backed rotation.
- Logout: blacklist access JTI, remove refresh keys from Redis.

## Registration event

```mermaid
flowchart LR
  A[Register / OAuth signup] --> B[Registered]
  B --> C[Publish to Kafka]
  C --> D[user.registered]
```

Payload: [events.md](events.md).

## Runtime (Docker)

```mermaid
flowchart TB
  nginx[Nginx :443] --> back[Laravel]
  back --> sql[PostgreSQL]
  back --> redis[Redis]
  back --> kafka[Kafka cluster]
  back --> mail[Mailhog]
```

Auth connects to Kafka over `microservices-net` (`kafka:9092`).
