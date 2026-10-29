# Frontend (Storefront)

Customer-facing single-page application.

## Overview

React and TypeScript storefront: catalog browsing, search, authentication, profile, AI assistant, cart, checkout, and order history. All API calls go through the Auth gateway with JWT access tokens held in memory.

## Stack

| Component | Technology |
| --- | --- |
| UI | React 19, TypeScript, Vite |
| Styling | Tailwind CSS, Domo design tokens |
| Components | shadcn/ui |
| Data | TanStack Query, axios |
| Forms | react-hook-form, Zod |
| Routing | React Router |

## Getting started

```bash
cd frontend
npm install
npm run dev
```

| URL | Description |
| --- | --- |
| Dev server | `http://localhost:5173` |
| API base | `VITE_AUTH_URL` (default `https://localhost`) |

Auth must allow CORS from the dev origin. Refresh token uses HttpOnly cookie on Auth.

## Documentation

| File | Description |
| --- | --- |
| [docs/architecture.md](docs/architecture.md) | Application structure |

## Main routes

| Path | Description |
| --- | --- |
| `/` | Home |
| `/categories`, `/categories/:slug` | Category listing |
| `/products/:id` | Product detail |
| `/search` | Search results |
| `/cart`, `/checkout` | Cart and checkout |
| `/orders`, `/orders/:id` | Order history |
| `/profile`, `/assistant` | Profile and chat (authenticated) |

## Project layout

| Directory | Purpose |
| --- | --- |
| `src/api/` | HTTP clients |
| `src/hooks/` | Page and data hooks |
| `src/pages/` | Route pages |
| `src/components/` | UI and layout |
| `src/lib/` | Cart, validators, utilities |

## Testing

```bash
npm run lint
npm run build
```

CI: frontend job in monorepo workflows when configured.
