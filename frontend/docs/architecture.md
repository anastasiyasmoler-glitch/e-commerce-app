# Architecture

## Context

```mermaid
flowchart TB
  FE[Frontend SPA]
  GW[Auth gateway HTTPS]
  AUTH[Auth API]
  CAT[Catalog]
  SRCH[Search]
  ORD[Order]
  REC[Recommendations]

  FE --> GW
  GW --> AUTH
  GW --> CAT
  GW --> SRCH
  GW --> ORD
  GW --> REC
```

## Client layers

```mermaid
flowchart TB
  P[Pages] --> H[hooks]
  H --> API[api/*]
  API --> HTTP[axios + JWT]
  HTTP --> GW[Gateway]
```

## Authentication

```mermaid
sequenceDiagram
  participant UI
  participant API as authApi
  participant GW as Gateway

  UI->>GW: POST /login
  GW-->>UI: access_token JSON + refresh cookie
  UI->>UI: store access in memory
  UI->>GW: API calls Authorization Bearer
```

Access token is not stored in `localStorage`. Session restore uses `/me` and `/refresh` via `authApi`.

## Cart and orders

Cart state lives in React context (`CartContext`). Checkout and orders call Order API through the gateway (`orderApi`).
