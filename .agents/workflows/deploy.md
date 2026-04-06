---
description: How to deploy the application to production
---

# Deploy to Production

// turbo-all

## Prerequisites

Ensure the following GitHub Secrets are set in the repository:
- `DEPLOY_HOST` — production server IP or hostname
- `DEPLOY_USER` — SSH username
- `DEPLOY_SSH_KEY` — private SSH key (PEM format)
- `DEPLOY_PORT` — SSH port (default: 22)

## Steps

1. Verify all CI checks pass on `main` branch
```bash
# Check GitHub Actions status
# All 3 workflows (lint, tests, security) must be green before deploying
```

// turbo
2. SSH into the production server and navigate to the project directory
```bash
ssh -p ${DEPLOY_PORT} ${DEPLOY_USER}@${DEPLOY_HOST}
cd /var/www/bapidapi
```

// turbo
3. Pull the latest images and restart containers
```bash
docker compose -f compose.prod.yaml pull
docker compose -f compose.prod.yaml up -d --remove-orphans
```

// turbo
4. Run database migrations (non-destructive, forward only)
```bash
docker compose -f compose.prod.yaml exec app php artisan migrate --force
```

// turbo
5. Restart queue workers to pick up any code changes
```bash
docker compose -f compose.prod.yaml exec app php artisan queue:restart
```

// turbo
6. Clear and warm application caches
```bash
docker compose -f compose.prod.yaml exec app php artisan config:cache
docker compose -f compose.prod.yaml exec app php artisan route:cache
docker compose -f compose.prod.yaml exec app php artisan view:cache
```

7. Verify deployment
```bash
curl https://api.yourdomain.com/api/v1/health
# Expected: {"success":true,"data":{"status":"ok"}}
```

8. Check logs for errors
```bash
docker compose -f compose.prod.yaml logs --tail=50 app
```

## Rollback

If the deployment fails, rollback to the previous image tag:

```bash
docker compose -f compose.prod.yaml down
# Edit compose.prod.yaml to pin previous image tag
docker compose -f compose.prod.yaml up -d
docker compose -f compose.prod.yaml exec app php artisan migrate:rollback --force
```
