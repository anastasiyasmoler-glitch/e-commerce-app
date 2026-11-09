#!/usr/bin/env sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

export COMPOSE_PROJECT_NAME="${COMPOSE_PROJECT_NAME:-ecommerce}"

STACK_FILES="-f docker-compose.yml -f docker-compose.fullstack.yml"

cmd="${1:-up}"

ensure_network() {
  docker network inspect microservices-net >/dev/null 2>&1 \
    || docker network create microservices-net
}

compose() {
  dir=$1
  shift
  # shellcheck disable=SC2086
  (cd "$dir" && docker compose $STACK_FILES "$@")
}

status_stack() {
  for dir in notification auth catalog order search analytics recommendation observability; do
    printf '\n== %s ==\n' "$dir"
    compose "$dir" ps 2>/dev/null || true
  done
}

up_stack() {
  ensure_network
  echo "Starting Kafka (notification)..."
  compose notification up -d kafka
  echo "Waiting for Kafka..."
  sleep 5

  for dir in auth catalog order notification search analytics recommendation; do
    echo "Starting $dir..."
    compose "$dir" up -d --build
  done

  echo "Starting observability (Prometheus, Grafana, Jaeger)..."
  compose observability up -d

  echo ""
  echo "Full stack is up. API: https://localhost (gateway only on host :443)."
  echo "Observability: Grafana http://localhost:3000  Prometheus http://localhost:9090  Jaeger http://localhost:16686"
  echo "Dev mail: Auth Mailhog http://localhost:8025, Notification http://localhost:8026"
  echo "SPAs: cd frontend && npm run dev  |  cd admin && npm run dev"
}

down_stack() {
  echo "Stopping observability..."
  compose observability down || true

  for dir in recommendation analytics search notification order catalog auth; do
    echo "Stopping $dir..."
    compose "$dir" down || true
  done
  echo "Stacks stopped. Network microservices-net is left in place (shared with partial runs)."
}

case "$cmd" in
  up) up_stack ;;
  down) down_stack ;;
  status) status_stack ;;
  *)
    echo "Usage: $0 {up|down|status}" >&2
    exit 1
    ;;
esac
