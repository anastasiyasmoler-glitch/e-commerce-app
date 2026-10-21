# Search load tests (k6)

Read-heavy scenarios for `GET /search`. Reports **p95/p99 latency** and error rate via k6 thresholds.

## Prerequisites

- [k6](https://k6.io/docs/get-started/installation/) installed locally
- Search API running and indexed (OpenSearch + consumer)

## Run

Against local compose (`back` on port 8080 inside network — publish or use nginx):

```bash
# From host when search nginx maps 8446 -> back
SEARCH_BASE_URL=https://localhost:8446 k6 run loadtests/k6/search.js

# Or against gateway (self-signed TLS — k6 may need insecure skip; use direct back port mapping if needed)
SEARCH_BASE_URL=http://127.0.0.1:8080 k6 run loadtests/k6/search.js
```

Tune VUs/stages in `k6/search.js`. Thresholds (`p(95)<800`, `p(99)<1500`) are dev defaults — adjust when SLA is defined.
