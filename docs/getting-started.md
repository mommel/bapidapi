# bapidapi — Getting Started Guide

> **Step-by-step verified setup guide.** Every command in this document has been tested.
> **Prerequisites:** Docker Desktop, Git, a terminal (bash/zsh/PowerShell)
> **Last Updated:** 2026-04-06

---

## Prerequisites

Before starting, ensure these are installed on your machine:

| Tool | Version | Verify |
|---|---|---|
| Docker Desktop | 4.x+ | `docker --version` |
| Docker Compose | V2 (built into Docker) | `docker compose version` |
| Git | 2.x+ | `git --version` |

> **Note:** You do NOT need PHP, Composer, or Node.js installed locally. Everything runs inside Docker.

---

## 1. Clone the Repository

```bash
git clone https://github.com/YOUR_ORG/bapidapi.git
cd bapidapi
```

Verify the repository structure:

```bash
ls -la
# Expected output includes: Dockerfile, compose.yaml, docs/, .agents/, .claude/, .github/
```

---

## 2. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

The default `.env.example` is pre-configured for the Docker development environment. No changes are required to get started locally, but review these variables:

```env
APP_NAME=bapidapi
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=pgsql
DB_HOST=db                  # Docker service name — do not change for local dev
DB_PORT=5432
DB_DATABASE=bapidapi
DB_USERNAME=bapidapi
DB_PASSWORD=secret

REDIS_HOST=redis            # Docker service name
REDIS_PORT=6379

JWT_SECRET=                 # Will be generated in step 5
JWT_TTL=15                  # Access token lifetime in minutes
JWT_REFRESH_TTL=10080       # Refresh token lifetime in minutes (7 days)
```

---

## 3. Build and Start Docker Services

Build all images and start all services:

```bash
docker compose up --build -d
```

Expected output (all services should show `Started` or `Running`):

```
[+] Running 5/5
 ✔ Container bapidapi-db-1       Started
 ✔ Container bapidapi-redis-1    Started
 ✔ Container bapidapi-app-1      Started
 ✔ Container bapidapi-nginx-1    Started
 ✔ Container bapidapi-mailpit-1  Started
```

Check service health:

```bash
docker compose ps
```

All services should show `healthy` or `running`.

---

## 4. Install PHP Dependencies

```bash
docker compose exec app composer install
```

> This runs inside the container and installs all packages defined in `composer.json` into the `vendor/` directory, which is bind-mounted to your host.

---

## 5. Generate Application Keys

```bash
# Laravel application encryption key
docker compose exec app php artisan key:generate

# JWT secret
docker compose exec app php artisan jwt:secret
```

Both commands write values into your `.env` file. Verify with:

```bash
grep -E "APP_KEY|JWT_SECRET" .env
```

Both lines should have non-empty values.

---

## 6. Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

Expected output:

```
  INFO  Running migrations.

  2014_10_12_000000_create_users_table ............... 71ms DONE
  2019_08_19_000000_create_failed_jobs_table ......... 35ms DONE
  ...
```

Optionally seed the database with test data:

```bash
docker compose exec app php artisan db:seed
```

---

## 7. Generate API Documentation

```bash
docker compose exec app php artisan l5-swagger:generate
```

---

## 8. Verify the Setup

### 8.1 Check Application Health

```bash
curl http://localhost:8080/api/v1/health
```

Expected response:

```json
{
    "success": true,
    "data": {
        "status": "ok",
        "version": "1.0.0",
        "environment": "local"
    },
    "message": "API is running",
    "meta": {
        "version": "1.0.0",
        "timestamp": "2026-04-06T20:00:00Z"
    }
}
```

### 8.2 Check Swagger UI

Open in browser: **http://localhost:8080/api/docs**

You should see the interactive Swagger UI with all available endpoints.

### 8.3 Test Authentication Flow

Register a new user:

```bash
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "SecurePassw0rd!",
    "password_confirmation": "SecurePassw0rd!"
  }'
```

Login and get tokens:

```bash
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "SecurePassw0rd!"
  }'
```

Save the `access_token` from the response and test an authenticated endpoint:

```bash
curl http://localhost:8080/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### 8.4 Check Mail UI (Mailpit)

Open in browser: **http://localhost:8025**

Password reset emails sent during local development will appear here.

---

## 9. Running Tests

```bash
# Run all tests
docker compose exec app ./vendor/bin/pest

# Run with coverage report
docker compose exec app ./vendor/bin/pest --coverage

# Run with coverage and enforce 80% minimum
docker compose exec app ./vendor/bin/pest --coverage --min=80

# Run specific test file
docker compose exec app ./vendor/bin/pest tests/Feature/Api/Auth/LoginTest.php

# Run only unit tests
docker compose exec app ./vendor/bin/pest --group=unit
```

---

## 10. Code Style

Check code style:

```bash
docker compose exec app ./vendor/bin/pint --test
```

Auto-fix code style issues:

```bash
docker compose exec app ./vendor/bin/pint
```

---

## 11. Useful Docker Commands

```bash
# View logs for all services
docker compose logs -f

# View logs for specific service
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f db

# Enter a container shell
docker compose exec app bash
docker compose exec db psql -U bapidapi -d bapidapi

# Stop all services
docker compose down

# Stop and remove volumes (DELETES DATABASE DATA)
docker compose down -v

# Rebuild a specific service
docker compose up --build app -d

# Run one-off artisan commands
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:list
```

---

## 12. Service URLs Summary

| Service | URL | Description |
|---|---|---|
| API | http://localhost:8080/api/v1 | Main API |
| API Health | http://localhost:8080/api/v1/health | Health check |
| Swagger UI | http://localhost:8080/api/docs | Interactive API docs |
| Mailpit UI | http://localhost:8025 | Email testing |
| PostgreSQL | localhost:5432 | DB (use any PostgreSQL client) |
| Redis | localhost:6379 | Redis CLI: `docker compose exec redis redis-cli` |

---

## 13. Troubleshooting

### Port conflicts

If port 8080 or 5432 is already in use:

```bash
# Check what is using the port
netstat -ano | findstr :8080   # Windows
lsof -i :8080                  # Mac/Linux

# Change the port in compose.yaml
ports:
  - "9090:80"  # change 8080 to any free port
```

### Database connection refused

```bash
# Check if db container is healthy
docker compose ps db

# Check db logs
docker compose logs db

# Wait for container health check to pass (can take 30s on first run)
docker compose up -d --wait
```

### Migrations failing

```bash
# Reset and re-run migrations
docker compose exec app php artisan migrate:fresh

# Check database connection
docker compose exec app php artisan tinker
DB::connection()->getPdo();  # Should not throw
```

### Composer permission errors

```bash
docker compose exec -u root app chown -R www-data:www-data /var/www/vendor
```

---

## 14. Environment-Specific Configuration

### Local Development

`.env` — present locally, not committed to Git.

### CI/CD (GitHub Actions)

Environment variables are set directly in the workflow file's `env:` block and via GitHub Secrets. See `.github/workflows/tests.yml` for exact configuration.

### Production

`.env` file on the production server, managed separately from the codebase. Never committed to Git.

---

*This guide is a living document. If a step fails, update it after finding the fix.*
