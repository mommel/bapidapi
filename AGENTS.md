# bapidapi — Agent Context

> This is the primary machine-readable context file for all AI coding agents.
> Claude: this file is also available as `CLAUDE.md`.
> Gemini/Antigravity: see `.agents/workflows/` for executable workflows.
> Copilot: see `.github/copilot-instructions.md`.

---

## Project Identity

- **Name:** bapidapi
- **Type:** RESTful API (stateless, horizontally scalable)
- **Language:** PHP 8.3
- **Framework:** Laravel 12
- **Database:** PostgreSQL 16
- **Cache/Queue:** Redis 7
- **Container:** Docker + Docker Compose

---

## Quick Commands

```bash
# Start development environment
docker compose up -d

# Run all tests
docker compose exec app ./vendor/bin/pest

# Run tests with coverage (80% minimum enforced)
docker compose exec app ./vendor/bin/pest --coverage --min=80

# Fix code style
docker compose exec app ./vendor/bin/pint

# Check code style (CI mode)
docker compose exec app ./vendor/bin/pint --test

# Regenerate OpenAPI documentation
docker compose exec app php artisan l5-swagger:generate

# Run artisan commands
docker compose exec app php artisan <command>

# Open a shell in the app container
docker compose exec app bash

# View logs
docker compose logs -f app
```

---

## Key URLs (local development)

| URL | Purpose |
|---|---|
| http://localhost:8080/api/v1 | API base URL |
| http://localhost:8080/api/v1/health | Health check |
| http://localhost:8080/api/docs | Interactive Swagger UI |
| http://localhost:8025 | Mailpit (email testing) |

---

## Architecture Overview

```
Request → Nginx → PHP-FPM (Laravel)
              ├── Middleware (auth, security headers, JSON enforce)
              ├── FormRequest (validation)
              ├── Controller (thin, delegates to Service)
              ├── Service (all business logic)
              ├── Repository (all DB queries, interface + Eloquent impl)
              ├── Model (Eloquent, relationships, NO business logic)
              └── JsonResource (output transformation)
```

**Key patterns:**
- Repository Pattern: `app/Repositories/` (interface) + `app/Repositories/Eloquent/` (implementation)
- Service Layer: `app/Services/` — holds all business logic
- FormRequests: `app/Http/Requests/` — all validation here, not in controllers
- API Resources: `app/Http/Resources/` — all output transformation here

---

## Authentication

- JWT (stateless) via `php-open-source-saver/jwt-auth`
- Access token: 15 minutes (configurable via `JWT_TTL` env)
- Refresh token: 7 days (configurable via `JWT_REFRESH_TTL` env)
- Token revocation: database blacklist table `jwt_blacklists`
- Guards: `api` guard uses `jwt` driver

**Auth endpoints:**
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/refresh`
- `GET  /api/v1/auth/me`
- `POST /api/v1/auth/password/forgot`
- `POST /api/v1/auth/password/reset`

---

## Security Constraints (Mandatory — Never Violate)

1. **No raw SQL with user input** — always use Eloquent or parameterized queries
2. **No `$guarded = []`** — all models must have explicit `$fillable`
3. **No secrets in code** — all config via `.env` only
4. **No `dd()`, `dump()`, `var_dump()` in committed code**
5. **No passwords or tokens in logs**
6. **All input validated via FormRequest before any operation**
7. **All output via JsonResource** — never return Eloquent models directly
8. **Every controller action must have a complete `@OA\` annotation block** — the CI `swagger.yml` will fail without it. See `.agents/workflows/openapi-annotations.md` for templates and rules.

---

## Code Style

- PSR-12 standard enforced by Laravel Pint
- `declare(strict_types=1);` in every PHP file
- All parameters, return types, and properties must be explicitly typed
- `$fillable` required on all models
- Commit messages follow Conventional Commits format

---

## Documentation References

| Document | Path |
|---|---|
| This plan | `docs/implementation-plan.md` |
| Architecture decisions | `docs/architecture.md` |
| Coding guidelines | `docs/coding-guideline.md` |
| Setup guide | `docs/getting-started.md` |
| API endpoints | `docs/api-endpoints.md` |
| Live API docs | `/api/docs` (when running) |
| **OpenAPI annotation rules** | `.agents/workflows/openapi-annotations.md` |

---

## Testing

- Test runner: Pest (feature tests) + PHPUnit (unit tests)
- Coverage driver: PCOV
- Minimum coverage: 80% enforced in CI
- Test database: PostgreSQL (same engine as production)
- All endpoints must have tests for: 401, 403, 422, and success cases

```bash
# Run a specific test
docker compose exec app ./vendor/bin/pest tests/Feature/Api/Auth/LoginTest.php

# Run tests matching a description
docker compose exec app ./vendor/bin/pest --filter "returns 401"
```

---

## CI/CD

GitHub Actions workflows in `.github/workflows/`:
- `lint.yml` — Pint style check (blocks PR on failure)
- `tests.yml` — Pest + PHPUnit + coverage 80%+ (blocks PR on failure)
- `security.yml` — composer audit + Laravel production config check + Trivy scan; runs Enlightn when supported by the current Laravel version
- `swagger.yml` — auto-regenerate OpenAPI spec on controller/route changes
- `deploy.yml` — deploy to production on push to `main`

---

## Environment Files

- `.env.example` — committed, template for all variables (no secrets)
- `.env` — NOT committed, generated locally and on servers
- `.env.testing` — used by phpunit for test database configuration

---

## When Making Changes

1. **New endpoint:** Add controller → service → repository → FormRequest → JsonResource → OpenAPI annotation → feature test
2. **New model:** Add migration → model (with `$fillable`, `$casts`, `$hidden`) → factory → repository interface + implementation → bind in `AppServiceProvider`
3. **Security fix:** Check if test covers the vulnerability → fix → add regression test
4. **Config change:** Update `.env.example` → update `docs/getting-started.md`
