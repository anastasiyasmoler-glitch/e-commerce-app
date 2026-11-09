# Observability (dev)

Prometheus metrics, Grafana dashboards, and Jaeger tracing UI for local / VM full-stack runs. Aligns with project observability guidelines (metrics + distributed tracing).

## Stack

| Service | Host port | Purpose |
| --- | --- | --- |
| Prometheus | [http://localhost:9090](http://localhost:9090) | Metrics store and scraper |
| Grafana | [http://localhost:3000](http://localhost:3000) | Dashboards (default login `admin` / `admin`) |
| Jaeger UI | [http://localhost:16686](http://localhost:16686) | Trace UI (OTLP gRPC `:4317`, HTTP `:4318`) |

All containers join `microservices-net` with backend services.

## Start

**Prerequisite:** `docker network create microservices-net` (once). Search metrics need `search-back` on that network — start Search or use full stack.

### Full platform (recommended)

From repo root:

```bash
chmod +x scripts/fullstack.sh   # once
./scripts/fullstack.sh up
```

Wait until stacks are healthy (first run builds images — several minutes).

### Observability only

```bash
docker network create microservices-net 2>/dev/null || true
cd observability
cp -n .env.example .env
docker compose up -d
```

If Search is not running, the **search** scrape target stays down until `search-back` is up.

## View in the browser

| UI | URL | Login |
| --- | --- | --- |
| Grafana | http://localhost:3000 | `admin` / `admin` (override in `.env`) |
| Prometheus | http://localhost:9090 | — |
| Jaeger | http://localhost:16686 | — |

**Grafana:** open dashboard **Platform overview** (Home → Dashboards). Panels show `up` for Prometheus, Jaeger, and Search scrapes.

**Prometheus:** Status → Targets — three jobs: `prometheus`, `jaeger`, `search` (search needs Search service).

**Jaeger:** empty trace list until services send OTLP to `jaeger:4317` / `:4318` (follow-up).

**Search metrics directly:** from host (if Search nginx port mapped) or inside the network: `curl -s http://search-back:8080/metrics | head` from any container on `microservices-net`.

## Metrics from applications

Prometheus scrapes itself, Jaeger, and **Search** (`GET /metrics` on `search-back:8080`). Other Laravel services can add `/metrics` later and new `scrape_configs` in `prometheus/prometheus.yml`.

## Traces

Send OTLP to `jaeger:4317` (gRPC) or `jaeger:4318` (HTTP) from backends (OpenTelemetry SDK). Order saga tracing is a follow-up task.

## Security

Default Grafana credentials are for **development only**. Change `GRAFANA_ADMIN_PASSWORD` in `.env` on shared VMs.
