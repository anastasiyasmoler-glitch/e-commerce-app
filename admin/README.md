# Admin (Shared admin SPA)

Staff dashboard for operations and analytics.

## Overview

React and TypeScript admin UI for roles `admin` and `analyst`: analytics dashboards, notification history, order views, user management hooks, and links to Catalog Filament. Communicates with backend services through the Auth gateway.

## Stack

| Component | Technology |
| --- | --- |
| UI | React, TypeScript, Vite |
| Styling | Tailwind CSS, Domo design tokens |
| Data | TanStack Query, axios |
| Routing | React Router |

## Getting started

```bash
cd admin
npm install
npm run dev
```

| URL | Description |
| --- | --- |
| Dev server | `http://localhost:5174` |
| API base | `VITE_AUTH_URL` (default `https://localhost`) |

## Documentation

| File | Description |
| --- | --- |
| [docs/architecture.md](docs/architecture.md) | Application structure |

## Main routes

| Path | Description |
| --- | --- |
| `/login` | Staff login |
| `/analytics` | Platform metrics |
| `/notifications` | Notification log |
| `/orders` | Order list |
| `/users` | User administration |
| `/recommendations` | Vector reindex tool |

Catalog CRUD uses Filament at gateway `/catalog-admin` (separate app in Catalog service).

## Project layout

| Directory | Purpose |
| --- | --- |
| `src/api/` | HTTP clients |
| `src/hooks/` | Page hooks |
| `src/pages/` | Route pages |

## Testing

```bash
npm run lint
npm run build
```
