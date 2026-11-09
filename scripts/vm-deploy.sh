#!/usr/bin/env sh
# Run on the VM inside the repo root after git clone. Updates code and restarts stacks.
set -eu

ROOT="$(CDPATH= cd -- "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

BRANCH="${DEPLOY_BRANCH:-main}"

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is not installed. See docs/deploy-vm.md" >&2
  exit 1
fi

if [ -d .git ]; then
  echo "Fetching $BRANCH..."
  git fetch origin "$BRANCH"
  git checkout "$BRANCH"
  git pull --ff-only origin "$BRANCH" || git pull origin "$BRANCH"
else
  echo "Not a git repository — skipping pull." >&2
fi

missing=0
for d in auth notification catalog order search analytics recommendation; do
  if [ ! -f "$d/.env" ]; then
    echo "Missing $d/.env — copy from .env.example and configure." >&2
    missing=1
  fi
done

if [ "$missing" -ne 0 ]; then
  exit 1
fi

chmod +x scripts/fullstack.sh
./scripts/fullstack.sh up

echo ""
echo "Deploy finished. API: https://$(hostname -I 2>/dev/null | awk '{print $1}' || echo 'localhost')"
echo "Check: curl -k https://127.0.0.1/docs"
