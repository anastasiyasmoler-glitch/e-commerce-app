# Deploy on a Ubuntu VM (VirtualBox / VMware / cloud VPS)

No domain required: use the VM IP and self-signed TLS from service nginx (or replace certs on Auth gateway later).

## 1. Create the VM

| Setting | Recommendation |
| --- | --- |
| OS | Ubuntu Server **24.04 LTS** |
| RAM | **8 GB** (4 GB minimum — drop Search/Recommendation if tight) |
| Disk | 50 GB |
| Network | **Bridged** (VM gets `192.168.x.x`) or NAT + port forward 22, 443, 3000 |

Install **OpenSSH server** during setup.

From your laptop:

```bash
ssh YOUR_USER@VM_IP
```

## 2. One-time VM setup

```bash
sudo apt update && sudo apt install -y git ca-certificates curl
# Docker Engine + Compose plugin — follow https://docs.docker.com/engine/install/ubuntu/
sudo usermod -aG docker $USER
```

Log out and back in so `docker` group applies.

```bash
docker network create microservices-net 2>/dev/null || true
```

## 3. Clone and configure env

```bash
git clone https://github.com/YOUR_ORG/e-commerce-app.git
cd e-commerce-app
chmod +x scripts/fullstack.sh scripts/vm-deploy.sh
```

Copy env for each backend you run:

```bash
for d in auth notification catalog order search analytics recommendation; do
  cp -n "$d/.env.example" "$d/.env"
done
```

Edit at least:

| File | Set |
| --- | --- |
| `auth/.env` | `APP_URL=https://VM_IP`, `JWT_SECRET`, `CORS_ALLOWED_ORIGINS=http://VM_IP:5173,http://VM_IP:5174,https://VM_IP` |
| `order/.env` | `STRIPE_*`, real webhook secret (not stripe-cli on prod) |
| `recommendation/.env` | `GEMINI_API_KEY` |

Do **not** commit `.env`.

## 4. First deploy

```bash
./scripts/fullstack.sh up
```

Wait for builds (first run is slow). Check:

```bash
./scripts/fullstack.sh status
curl -k https://VM_IP/docs
```

Observability (optional, included in fullstack):

- Grafana http://VM_IP:3000 (`admin` / `admin`)
- Prometheus http://VM_IP:9090

### Catalog downstream

If checkout/search/chat are empty after fresh DB:

```bash
cd catalog && docker compose exec back php artisan catalog:republish-product-updates
```

## 5. Storefront and Admin (on VM or laptop)

On build machine with Node 20+:

```bash
cd frontend
echo "VITE_AUTH_URL=https://VM_IP" > .env
npm ci && npm run dev -- --host 0.0.0.0
```

Admin: port `5174`, same `VITE_AUTH_URL`.

For production-like static hosting, `npm run build` and serve `dist/` via nginx (not included in backend compose).

## 6. Deployer (recommended updates)

From your **laptop** (SSH key to the VM, no password):

```bash
cd e-commerce-app
composer install
cp .env.deploy.example .env.deploy
# edit .env.deploy — DEPLOY_HOST, DEPLOY_USER, DEPLOY_PATH, DEPLOY_REPOSITORY (git SSH URL)
set -a && source .env.deploy && set +a
vendor/bin/dep deploy:setup production   # once: creates shared/, releases/, current/
```

Put real secrets on the VM once (they stay in `shared/` across releases):

```bash
ssh YOUR_USER@VM_IP
mkdir -p ~/e-commerce-app/shared/{auth,notification,catalog,order,search,analytics,recommendation}
for d in auth notification catalog order search analytics recommendation; do
  cp e-commerce-app/current/$d/.env.example ~/e-commerce-app/shared/$d/.env
  nano ~/e-commerce-app/shared/$d/.env
done
```

(If you have not deployed yet, copy from a local `.env.example` into those paths instead.)

Every release:

```bash
set -a && source .env.deploy && set +a
vendor/bin/dep deploy production
# or: composer run deploy
```

Deployer clones into `releases/N`, symlinks `current`, runs `COMPOSE_PROJECT_NAME=ecommerce ./scripts/fullstack.sh up` over SSH.

## 7. Manual update on the VM (without Deployer)

```bash
cd e-commerce-app   # flat clone, not release layout
./scripts/vm-deploy.sh
```

## 8. GitHub Actions → VM (optional)

On `push` to `main`, add a workflow that runs `ssh user@host ./e-commerce-app/scripts/vm-deploy.sh` using repository secrets `VM_HOST`, `VM_USER`, `VM_SSH_KEY`.

CI already builds images to GHCR on `main`; this repo deploy path uses **git pull + compose build** on the VM unless you switch compose to `image: ghcr.io/...`.

## 9. Stripe on a VM without a domain

- **Test checkout locally** with stripe-cli, or
- Use Stripe Dashboard webhook to a **tunnel** (ngrok / Cloudflare Tunnel) to `https://tunnel/order/webhooks/stripe`, or
- Skip live payment demo on the VM until you have a public HTTPS URL.

## 10. Firewall

```bash
sudo ufw allow 22,80,443/tcp
# optional: 3000,9090,16686 for observability; 5173,5174 for dev SPA
sudo ufw enable
```

Cloud provider: open the same ports in the security group.
