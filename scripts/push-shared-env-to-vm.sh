#!/usr/bin/env bash
# Copy service .env files from this repo to Deployer shared/ on the VM and set gateway URLs.
set -euo pipefail

ROOT="$(CDPATH= cd -- "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [ -f .env.deploy ]; then
  # shellcheck disable=SC1091
  set -a && source .env.deploy && set +a
fi

HOST="${DEPLOY_HOST:?Set DEPLOY_HOST in .env.deploy or the environment}"
USER="${DEPLOY_USER:-vboxuser}"
REMOTE_ROOT="${DEPLOY_PATH:-/home/vboxuser/e-commerce-app}"
GATEWAY="${DEPLOY_GATEWAY_URL:-https://${HOST}}"

SERVICES=(auth notification catalog order search analytics recommendation)

for svc in "${SERVICES[@]}"; do
  src="$ROOT/$svc/.env"
  if [ ! -f "$src" ]; then
  echo "Missing $src — create from $svc/.env.example" >&2
    exit 1
  fi
done

echo "Creating shared dirs on $USER@$HOST ..."
ssh "$USER@$HOST" "mkdir -p $(printf '%s ' "${SERVICES[@]/#/$REMOTE_ROOT/shared/}")"

for svc in "${SERVICES[@]}"; do
  echo "scp $svc/.env -> $REMOTE_ROOT/shared/$svc/.env"
  scp "$ROOT/$svc/.env" "$USER@$HOST:$REMOTE_ROOT/shared/$svc/.env"
done

echo "Patching APP_URL / OAuth redirect for gateway $GATEWAY ..."
ssh "$USER@$HOST" "GATEWAY='$GATEWAY' REMOTE_ROOT='$REMOTE_ROOT' bash -s" <<'REMOTE'
set -euo pipefail
for f in "$REMOTE_ROOT"/shared/{auth,notification,catalog,order,analytics,recommendation}/.env; do
  [ -f "$f" ] || continue
  if grep -q '^APP_URL=' "$f"; then
    sed -i "s|^APP_URL=.*|APP_URL=${GATEWAY}|" "$f"
  fi
done
catalog_env="$REMOTE_ROOT/shared/catalog/.env"
if [ -f "$catalog_env" ] && grep -q '^AWS_URL=' "$catalog_env"; then
  sed -i "s|^AWS_URL=.*|AWS_URL=${GATEWAY}/product-images|" "$catalog_env"
fi
auth_env="$REMOTE_ROOT/shared/auth/.env"
if [ -f "$auth_env" ] && grep -q '^GOOGLE_REDIRECT_URI=' "$auth_env"; then
  sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=${GATEWAY}/auth/google/callback|" "$auth_env"
fi
REMOTE

echo ""
echo "Verify on VM:"
echo "  ssh $USER@$HOST 'grep -E \"^(APP_URL|DB_HOST|REDIS_HOST|MONGODB_HOST|JWT_SECRET)=\" $REMOTE_ROOT/shared/auth/.env $REMOTE_ROOT/shared/notification/.env'"
echo "  ssh $USER@$HOST 'ls -la $REMOTE_ROOT/current/auth/.env'"
echo ""
echo "Then restart stack:"
echo "  ssh $USER@$HOST 'cd $REMOTE_ROOT/current && COMPOSE_PROJECT_NAME=ecommerce ./scripts/fullstack.sh up'"
