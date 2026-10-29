# Architecture

## Context

```mermaid
flowchart TB
  AD[Admin SPA]
  GW[Auth gateway]
  AUTH[Auth]
  AN[Analytics]
  NOT[Notification]
  ORD[Order]
  REC[Recommendation]
  FIL[Catalog Filament]

  AD --> GW
  GW --> AUTH
  GW --> AN
  GW --> NOT
  GW --> ORD
  GW --> REC
  AD -->|link| FIL
```

## Role-based UI

```mermaid
flowchart LR
  R[RequireAdmin / RequireRole]
  R --> P[Pages]
  P --> A[analyticsApi]
  P --> N[notificationApi]
  P --> O[orders API]
  P --> U[Auth admin users API]
```

## Typical data flow

```mermaid
sequenceDiagram
  participant Page
  participant Hook
  participant API
  participant Gateway

  Page->>Hook: useQuery
  Hook->>API: GET /api/analytics/...
  API->>Gateway: Bearer JWT
  Gateway-->>Page: JSON
```

JWT access token is kept in memory (`src/api/http.ts`). Catalog administration opens Filament with gateway URL and optional token query parameter.
