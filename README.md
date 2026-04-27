# bapidapi

[![CodeQL Advanced](https://github.com/mommel/bapidapi/actions/workflows/codeql.yml/badge.svg?branch=main)](https://github.com/mommel/bapidapi/actions/workflows/codeql.yml)
[![PHPMD](https://github.com/mommel/bapidapi/actions/workflows/phpmd.yml/badge.svg?branch=main)](https://github.com/mommel/bapidapi/actions/workflows/phpmd.yml)
[![Security Audit](https://github.com/mommel/bapidapi/actions/workflows/security.yml/badge.svg?branch=main)](https://github.com/mommel/bapidapi/actions/workflows/security.yml)
[![Tests](https://github.com/mommel/bapidapi/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/mommel/bapidapi/actions/workflows/tests.yml)
[![Lint](https://github.com/mommel/bapidapi/actions/workflows/lint.yml/badge.svg?branch=main)](https://github.com/mommel/bapidapi/actions/workflows/lint.yml)
[![Swagger Auto-Regenerate](https://github.com/mommel/bapidapi/actions/workflows/swagger.yml/badge.svg?branch=main)](https://github.com/mommel/bapidapi/actions/workflows/swagger.yml)


This is just a demoporject don't use it



A production-ready, stateless RESTful API built with **PHP 8.3 + Laravel 12**, **PostgreSQL 16**, JWT authentication, and full CI/CD via GitHub Actions.

---

## Quick Start

```bash
git clone https://github.com/YOUR_ORG/bapidapi.git && cd bapidapi
cp .env.example .env
docker compose up --build -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate
docker compose exec app php artisan l5-swagger:generate
```

**Verify:** `curl http://localhost:8080/api/v1/health` → `{"success":true,...}`

---

## Key URLs

| URL | Purpose |
|---|---|
| http://localhost:8080/api/v1 | REST API |
| http://localhost:8080/api/docs | Interactive Swagger UI |
| http://localhost:8025 | Mailpit (email testing) |

---

## Technology Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.3 (strict types) |
| Framework | Laravel 12 |
| Database | PostgreSQL 16 |
| Cache / Queue | Redis 7 |
| Authentication | JWT (`php-open-source-saver/jwt-auth`) |
| API Docs | OpenAPI 3.0 (`darkaonline/l5-swagger`) |
| Testing | Pest + PHPUnit (80%+ coverage) |
| Code Style | PSR-12 (Laravel Pint) |
| Container | Docker + Docker Compose |
| CI/CD | GitHub Actions |

---

## Documentation

| Document | Description |
|---|---|
| [Implementation Plan](docs/implementation-plan.md) | Master plan and phase checklist |
| [Architecture](docs/architecture.md) | System design and ADRs |
| [Getting Started](docs/getting-started.md) | Step-by-step setup guide |
| [Coding Guidelines](docs/coding-guideline.md) | Conventions and rules |
| [API Endpoints](docs/api-endpoints.md) | Endpoint catalogue |

---

## Common Commands

```bash
# Start dev environment
docker compose up -d

# Run tests with coverage (80% minimum)
docker compose exec app ./vendor/bin/pest --coverage --min=80

# Fix code style
docker compose exec app ./vendor/bin/pint

# Regenerate API docs
docker compose exec app php artisan l5-swagger:generate

# Open shell in app container
docker compose exec app bash
```

---

## CI/CD Status

| Workflow | Status |
|---|---|
| Lint | ![lint](https://github.com/YOUR_ORG/bapidapi/actions/workflows/lint.yml/badge.svg) |
| Tests | ![tests](https://github.com/YOUR_ORG/bapidapi/actions/workflows/tests.yml/badge.svg) |
| Security | ![security](https://github.com/YOUR_ORG/bapidapi/actions/workflows/security.yml/badge.svg) |

---

## AI Agent Context

Context files for AI coding assistants:

| Tool | File |
|---|---|
| Claude | `CLAUDE.md` + `.claude/rules/` |
| Gemini / Antigravity | `.agents/workflows/` |
| OpenAI Codex | `CODEX.md` |
| GitHub Copilot | `.github/copilot-instructions.md` |

---

## License

MIT
