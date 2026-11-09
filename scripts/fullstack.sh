#!/usr/bin/env sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

# One Compose project per service dir — avoids colliding back/sql/vendor names (ecommerce-back-1).
STACK_FILES="-f docker-compose.yml -f docker-compose.fullstack.yml"
COMPOSE_PREFIX="${COMPOSE_PROJECT_PREFIX:-ecommerce}"

cmd="${1:-up}"

ensure_network() {
  docker network inspect microservices-net >/dev/null 2>&1 \
    || docker network create microservices-net
}

compose() {
  dir=$1
  shift
  project="${COMPOSE_PREFIX}-${dir}"
  # shellcheck disable=SC2086
  (cd "$dir" && COMPOSE_PROJECT_NAME="$project" docker compose $STACK_FILES "$@")
}

status_stack() {
  for dir in notification auth catalog order search analytics recommendation observability; do
    printf '\n== %s ==\n' "$dir"
    compose "$dir" ps 2>/dev/null || true
  done
}

deploy_shared_root() {
  if [ -d "$ROOT/shared" ]; then
    printf '%s\n' "$ROOT"
    return 0
  fi
  if [ -d "$ROOT/../../shared" ]; then
    (CDPATH= cd -- "$ROOT/../.." && pwd)
    return 0
  fi
  return 1
}

materialize_deploy_env() {
  shared_root=$(deploy_shared_root) || return 0
  for dir in auth notification catalog order search analytics recommendation; do
    rel_env="$shared_root/shared/$dir/.env"
    cur_env="$ROOT/$dir/.env"
    if [ -f "$rel_env" ] && { [ -L "$cur_env" ] || [ ! -f "$cur_env" ]; }; then
      rm -f "$cur_env"
      cp "$rel_env" "$cur_env"
    fi
  done
}

start_service() {
  dir=$1
  required=$2
  echo "Starting $dir..."
  if compose "$dir" up -d --build; then
    return 0
  fi
  echo "WARNING: $dir stack failed to start" >&2
  if [ "$required" = 1 ]; then
    exit 1
  fi
  return 1
}

up_stack() {
  ensure_network
  materialize_deploy_env
  echo "Starting Kafka (notification)..."
  compose notification up -d kafka
  echo "Waiting for Kafka..."
  sleep 5

  start_service auth 1
  start_service catalog 1
  start_service order 1
  start_service notification 1
  start_service search 0
  start_service analytics 0
  start_service recommendation 0

  echo "Starting observability (Prometheus, Grafana, Jaeger)..."
  compose observability up -d || echo "WARNING: observability failed to start" >&2

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
