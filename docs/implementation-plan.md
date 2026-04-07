# bapidapi — Comprehensive Implementation Plan

> **Living Document** — This file is the single source of truth for the project. It must be updated whenever the project changes. AI agents and human developers alike should consult this document at the start of every session.
>
> **Last updated:** 2026-04-07 | **Status:** 🟢 Execution (Phase 4 — Core API Endpoints)

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technology Stack & Decisions](#2-technology-stack--decisions)
3. [Directory Structure](#3-directory-structure)
4. [Phase 0 — Repository & Tooling Bootstrap](#phase-0--repository--tooling-bootstrap)
5. [Phase 1 — Docker Development Environment](#phase-1--docker-development-environment)
6. [Phase 2 — Laravel Application Scaffold](#phase-2--laravel-application-scaffold)
7. [Phase 3 — Authentication (JWT)](#phase-3--authentication-jwt)
8. [Phase 4 — Core API Endpoints](#phase-4--core-api-endpoints)
9. [Phase 5 — Security Hardening](#phase-5--security-hardening)
10. [Phase 6 — OpenAPI / Swagger Documentation](#phase-6--openapi--swagger-documentation)
11. [Phase 7 — Testing (PHPUnit + Pest)](#phase-7--testing-phpunit--pest)
12. [Phase 8 — CI/CD Pipeline (GitHub Actions)](#phase-8--cicd-pipeline-github-actions)
13. [Phase 9 — Production Deployment](#phase-9--production-deployment)
14. [Phase 10 — AI Agent Configuration](#phase-10--ai-agent-configuration)
15. [Verification Checklist](#verification-checklist)
16. [Open Questions & Decisions Log](#open-questions--decisions-log)

---

## 1. Project Overview

**Project name:** `bapidapi`
**Purpose:** A production-ready, stateless RESTful API built with Laravel 12 and PostgreSQL, protected by JWT authentication and hardened against common web vulnerabilities. The API is designed to scale horizontally in a containerized environment.

### Core Requirements Summary

| Requirement | Solution |
|---|---|
| Language / Framework | PHP 8.3 + Laravel 12 |
| Database | PostgreSQL 16 |
| Authentication | JWT (`php-open-source-saver/jwt-auth`) |
| Containerization | Docker + Docker Compose (dev & prod variants) |
| API Documentation | OpenAPI 3.0 via `darkaonline/l5-swagger` at `/api/docs` |
| Testing | Pest + PHPUnit, 80%+ coverage enforced in CI |
| CI/CD | GitHub Actions |
| Code Style | PSR-12, Laravel Pint |
| AI Agent Context | Claude (`CLAUDE.md`), Gemini/Antigravity (`.agents/`), Codex (`CODEX.md`), Copilot (`.github/copilot-instructions.md`) |

---

## 2. Technology Stack & Decisions

### 2.1 Framework — Laravel 12

**Decision:** Laravel 12 (released February 2025, PHP 8.2–8.4 support, security patches until February 2027).

**Alternatives considered:**
- *Symfony* — More flexibility but steeper learning curve and less opinionated structure.
- *Slim / Lumen* — Lightweight but lacks the ecosystem (queues, ORM, auth scaffolding) required here.
- *Laravel 11* — Superseded; 12 is the latest stable with no breaking changes from 11.

### 2.2 Authentication — JWT

**Decision:** `php-open-source-saver/jwt-auth` — the actively maintained fork of the abandoned `tymon/jwt-auth`.

**Rationale:** The project explicitly requires JWT for stateless authentication. The API must operate across potentially different clients (mobile, third-party services) without server-side session state.

**Alternatives considered:**
- *Laravel Sanctum* — Official first-party package, excellent for SPAs and mobile apps using cookie/token-based auth. Rejected here because JWT with cross-domain, stateless semantics was an explicit requirement.
- *Laravel Passport* — OAuth 2.0 full implementation. Overkill for a pure API; adds complexity without benefit.

### 2.3 Database — PostgreSQL 16

**Decision:** PostgreSQL 16 on Alpine Linux base image (smaller footprint).

**Rationale:** Stronger ACID compliance, superior JSON (JSONB) support, and better index types (partial, expression, GIN) vs MySQL. Widely supported in cloud-managed database offerings (RDS, Cloud SQL, Supabase).

### 2.4 API Documentation — l5-swagger (OpenAPI 3.0)

**Decision:** `darkaonline/l5-swagger` (wrapper around `swagger-php` + Swagger UI).

**Rationale:** Native annotation-driven approach keeps docs co-located with controller code, drastically reducing documentation drift. CI action regenerates and validates the spec on every PR.

**Alternatives considered:**
- *Scribe* — Auto-detection from code without annotations. Easier to start but less precise.
- *Raw OpenAPI YAML files* — Maximum flexibility, but requires manual maintenance.

### 2.5 Testing — Pest

**Decision:** Pest as the primary test runner (sits on top of PHPUnit). PHPUnit used directly for lower-level unit tests where Pest's expressive DSL adds no benefit.

**Coverage driver:** PCOV (faster than Xdebug for coverage-only runs; Xdebug available for step debugging).

### 2.6 CI/CD — GitHub Actions

**Decision:** GitHub Actions with a service container strategy for PostgreSQL.

**Rationale:** Tightly integrated with GitHub, free for public repositories, matrix-testing support, native secret management, and first-class Docker support.

### 2.7 Code Style — PSR-12 + Laravel Pint

**Decision:** Laravel Pint (built on PHP CS Fixer) enforced in CI. Configuration defined in `pint.json`.

---

## 3. Directory Structure

```
bapidapi/
├── .agents/                        # Gemini / Antigravity agent config
│   └── workflows/
│       ├── deploy.md
│       ├── run-tests.md
│       └── update-docs.md
├── .claude/                        # Claude Code agent config
│   ├── rules/
│   │   ├── backend.md
│   │   ├── testing.md
│   │   └── security.md
│   └── settings.json
├── .github/
│   ├── copilot-instructions.md     # GitHub Copilot agent instructions
│   └── workflows/
│       ├── tests.yml               # PHPUnit + Pest + coverage
│       ├── lint.yml                # Pint code style
│       ├── security.yml            # Dependency audit + SAST
│       ├── swagger.yml             # Auto-regenerate OpenAPI spec
│       └── deploy.yml              # Production deployment
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       └── ...
│   │   ├── Middleware/
│   │   │   ├── ForceJsonResponse.php
│   │   │   ├── SecurityHeaders.php
│   │   │   └── ...
│   │   └── Requests/               # FormRequest validation
│   ├── Models/
│   ├── Repositories/               # Repository pattern
│   ├── Services/                   # Business logic layer
│   └── Exceptions/
│       └── Handler.php
├── config/
│   ├── auth.php
│   ├── jwt.php
│   └── l5-swagger.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docker/
│   ├── nginx/
│   │   ├── dev.conf
│   │   └── prod.conf
│   ├── php/
│   │   ├── php-dev.ini
│   │   └── php-prod.ini
│   └── supervisor/
│       └── supervisord.conf
├── docs/
│   ├── implementation-plan.md      <- THIS FILE
│   ├── architecture.md
│   ├── coding-guideline.md
│   ├── getting-started.md          # Step-by-step verified setup guide
│   └── api-endpoints.md            # Human-readable endpoint catalogue
├── routes/
│   └── api.php
├── storage/
│   └── api-docs/                   # Generated OpenAPI JSON
├── tests/
│   ├── Feature/
│   │   └── Api/
│   └── Unit/
├── .dockerignore
├── .env.example
├── AGENTS.md                       # Cross-tool agent primary config
├── CLAUDE.md                       # Symlink or copy of AGENTS.md
├── CODEX.md                        # OpenAI Codex instructions
├── Dockerfile                      # Multi-stage (dev target + prod target)
├── compose.yaml                    # Dev Docker Compose
├── compose.prod.yaml               # Production Docker Compose
├── pint.json                       # Laravel Pint code style config
├── phpunit.xml
└── README.md
```

---

## Phase 0 — Repository & Tooling Bootstrap

**Goal:** Clean, initialized repository with AI agent context files and docs skeleton.

### Tasks

- [x] Initialize git repository with `.gitignore` (Laravel template)
- [x] Create `docs/` skeleton (this file + placeholder docs)
- [x] Create root AI agent context files: `AGENTS.md`, `CLAUDE.md`, `CODEX.md`, `.github/copilot-instructions.md`
- [x] Create `.claude/` directory with rules
- [x] Create `.agents/workflows/` directory for Gemini/Antigravity
- [x] Create `README.md` with project overview and quick-start
- [x] Create `.env.example` template

**Acceptance criteria:** `git status` shows a clean commit with all scaffolding files present. ✅ Completed 2026-04-06.

---

## Phase 1 — Docker Development Environment

**Goal:** A reproducible local development environment with one command: `docker compose up`.

### Services

| Service | Image | Port |
|---|---|---|
| `app` | `php:8.3-fpm-alpine` (custom) | — |
| `nginx` | `nginx:1.27-alpine` | 8080 -> 80 |
| `db` | `postgres:16-alpine` | 5432 |
| `redis` | `redis:7-alpine` | 6379 |
| `mailpit` | `axllent/mailpit` | 8025 (UI), 1025 (SMTP) |

### Tasks

- [x] Write `Dockerfile` with `dev` and `prod` build targets (multi-stage)
  - Dev target: includes Composer, Xdebug, dev PHP extensions
  - Prod target: no dev tools, runs as `www-data`, OPcache enabled
- [x] Write `compose.yaml` (dev) — bind-mounts source code for hot reload
  - Added MinIO (S3-compatible) service for local object storage
- [x] Write `compose.prod.yaml` — no bind mounts, read-only FS where possible
- [x] Write `docker/nginx/dev.conf` — proxy pass to php-fpm
- [x] Write `docker/php/php-dev.ini` — xdebug config, memory limits
- [x] Write `docker/supervisor/supervisord.conf` — queue worker
- [x] Write `.dockerignore` — exclude `.git`, `tests`, `node_modules`, `storage/logs`
- [x] Verify: `docker compose up --build -d && docker compose exec app php artisan --version` prints `Laravel Framework 12.56.0` ✅
- [x] Document exact steps in `docs/getting-started.md`

**Completed:** 2026-04-06. Xdebug version pinning issue resolved by using latest compatible version.

### Key docker-compose health checks

```yaml
db:
  healthcheck:
    test: ["CMD-SHELL", "pg_isready -U ${DB_USERNAME} -d ${DB_DATABASE}"]
    interval: 10s
    timeout: 5s
    retries: 5
```

---

## Phase 2 — Laravel Application Scaffold

**Goal:** Fresh Laravel 12 installation wired to PostgreSQL inside Docker, migrations running, API routes registered.

### Tasks

- [x] Install Laravel 12 inside Docker container (v12.56.0)
- [x] Configure `config/database.php` default connection to `pgsql`
- [x] Set `.env` PostgreSQL credentials (matching compose service names)
- [x] Run initial migrations: `php artisan migrate`
- [x] Install `darkaonline/l5-swagger` v11.0
- [x] Install `php-open-source-saver/jwt-auth` v2.9
- [x] Install `Laravel Pint` (dev) — bundled with Laravel 12
- [ ] Install `Pest` (dev) — dependency conflict with PHPUnit 11, deferred
- [ ] Install `PCOV` PHP extension in Docker image (coverage driver)
- [x] Create `ForceJsonResponse` middleware — `app/Http/Middleware/ForceJsonResponse.php`
- [x] Create `SecurityHeaders` middleware — `app/Http/Middleware/SecurityHeaders.php` (HSTS, X-Frame-Options, X-Content-Type-Options, CSP, Referrer-Policy)
- [x] Register middlewares in `bootstrap/app.php` (Laravel 12 style)
- [x] Create `routes/api.php` skeleton with versioned prefix `/api/v1`
- [x] Verify: `GET /api/v1/health` returns `{"success":true,"data":{"status":"ok"},"meta":{"version":"1.0.0"}}` ✅

**Completed:** 2026-04-06.

### API Response Structure (standard)

All API responses follow a consistent envelope:

```json
{
  "success": true,
  "data": {},
  "message": "OK",
  "meta": {
    "version": "1.0.0",
    "timestamp": "2026-04-06T20:00:00Z"
  }
}
```

Error responses:

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The email field is required.",
    "details": {}
  }
}
```

---

## Phase 3 — Authentication (JWT)

**Goal:** Fully working JWT authentication with register, login, logout, refresh-token, and profile endpoints.

### Tasks

- [x] Publish JWT config: `php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"`
- [x] Generate JWT secret: `php artisan jwt:secret` (writes to `.env`)
- [x] Update `User` model to implement `JWTSubject` interface — `app/Models/User.php`
- [x] Update `config/auth.php` to set `api` guard driver to `jwt`
- [x] Create `AuthController` (`app/Http/Controllers/Api/AuthController.php`) with:
  - `POST /api/v1/auth/register` — create user, return tokens ✅
  - `POST /api/v1/auth/login` — validate credentials, return JWT access + refresh tokens ✅
  - `POST /api/v1/auth/logout` — blacklist current token ✅
  - `POST /api/v1/auth/refresh` — rotate access token using refresh token ✅
  - `GET  /api/v1/auth/me` — return authenticated user profile ✅
- [ ] Implement token blacklist using database table (for revocation on logout)
- [x] Add `auth:api` middleware to protected routes
- [x] Short-lived access tokens (15 min default, configurable via `JWT_TTL` env)
- [x] Longer-lived refresh tokens (7 days default, configurable via `JWT_REFRESH_TTL` env)
- [ ] Add rate limiting: `throttle:6,1` on auth routes
- [ ] Add `POST /api/v1/auth/password/forgot` and `POST /api/v1/auth/password/reset`
- [x] Verify: full auth flow (register → login → token → authenticated request) works ✅

**Completed (core):** 2026-04-06. Token blacklist, rate limiting, and password reset still pending.

---

## Phase 4 — Core API Endpoints

**Goal:** Implement the domain-specific API endpoints. Endpoints are defined per business requirements in `docs/api-endpoints.md`.

### Architecture Patterns

- **Repository Pattern** — All database queries go through repository interfaces. Concrete implementations are injected via the service container. This enables mocking in tests.
- **Service Layer** — Business logic lives in `app/Services/`, not in controllers.
- **FormRequests** — All input validation is handled by `FormRequest` classes, not inline in controllers.
- **API Resources** — All data output is transformed by `JsonResource` classes (never raw Eloquent models).

### Tasks

- [x] Define endpoint catalogue — `docs/api2integradte.openapi.yml` (source OpenAPI spec)
- [ ] Create base `ApiController` extending `Controller` with shared response helpers
- [x] Implement repositories with interface + Eloquent implementation:
  - `app/Repositories/DriverRepositoryInterface.php` → `Eloquent/EloquentDriverRepository.php`
  - `app/Repositories/VehicleRepositoryInterface.php` → `Eloquent/EloquentVehicleRepository.php`
  - `app/Repositories/ParkingLotRepositoryInterface.php` → `Eloquent/EloquentParkingLotRepository.php`
  - `app/Repositories/ReservationRepositoryInterface.php` → `Eloquent/EloquentReservationRepository.php`
- [x] Implement services calling repositories:
  - `app/Services/DriverService.php`, `VehicleService.php`, `ParkingLotService.php`, `ReservationService.php`
  - ParkingLotService includes availability check (Haversine geo-search, overlapping reservation counting)
  - ReservationService includes cancellation business rules
- [x] Implement controllers calling services with `FormRequest` validation:
  - `app/Http/Controllers/Api/DriverController.php` — GET list, POST create, GET show, PATCH update
  - `app/Http/Controllers/Api/VehicleController.php` — GET list, POST create, GET show, PATCH update
  - `app/Http/Controllers/Api/ParkingLotController.php` — GET list, GET show, GET availability
  - `app/Http/Controllers/Api/ReservationController.php` — GET list, POST create, GET show, DELETE cancel
- [x] Implement FormRequests: `StoreDriverRequest`, `UpdateDriverRequest`, `StoreVehicleRequest`, `UpdateVehicleRequest`, `StoreReservationRequest`
- [x] Implement API Resources: `DriverResource`, `VehicleResource`, `ParkingLotResource`, `ReservationResource`
- [x] Bind repository interfaces in `AppServiceProvider`
- [ ] Add OpenAPI annotations to each controller method (see Phase 6)
- [x] Paginate all list endpoints (`?page=1&pageSize=20`)
- [x] Implement filtering: geo-radius search (ParkingLot), text search (Driver, Vehicle), status/date filters (Reservation)
- [x] Database migrations for all 4 domain tables (UUID primary keys, foreign keys with cascade/set null)
- [ ] Write Pest feature tests for all endpoints (401, 422, success, pagination)
- [ ] Write model factories for seeding and testing

**Status:** In progress. Core CRUD endpoints are functional (21 routes registered, verified via test script). Tests, OpenAPI annotations, and factories are next.

---

## Phase 5 — Security Hardening

**Goal:** API hardened against OWASP Top 10 and common PHP/Laravel-specific vulnerabilities.

### Security Measures

| Threat | Countermeasure |
|---|---|
| SQL Injection | Eloquent ORM + parameterized queries only; no raw SQL with user input |
| XSS | `htmlspecialchars()` on output; Content-Security-Policy header |
| CSRF | Stateless API — no cookies/sessions, so CSRF not applicable; JWT in Authorization header |
| Broken Authentication | JWT with short expiry + blacklist; bcrypt password hashing; rate limiting |
| Broken Access Control | Policy-based authorization (`Gate`, `Policy` classes) |
| Security Misconfiguration | `.env` never committed; `APP_DEBUG=false` in prod; `.dockerignore` excludes secrets |
| Sensitive Data Exposure | HTTPS enforced (HSTS header); no passwords/tokens in logs |
| XXE | Not applicable (no XML parsing) |
| Mass Assignment | `$fillable` defined on all models; `$guarded = []` banned by Pint rule |
| Injection (general) | Input validated via FormRequests; output escaped via Resources |
| SSRF | No user-controlled URL fetching without allowlist |
| Rate Limiting | Laravel throttle middleware on all public endpoints |

### Tasks

- [ ] Create `SecurityHeaders` middleware (HSTS, X-Content-Type-Options, X-Frame-Options, CSP, Referrer-Policy)
- [ ] Create `ForceJsonResponse` middleware
- [ ] Enable `APP_DEBUG=false` enforcement check in CI
- [ ] Install `enlightn/enlightn`: `composer require --dev enlightn/enlightn` — runs security checks
- [ ] Add `php artisan enlightn` to CI pipeline
- [ ] Configure `config/cors.php` with explicit allowed origins (env-configurable)
- [ ] Ensure all models have explicit `$fillable`
- [ ] Validate Content-Type header on POST/PUT/PATCH routes
- [ ] Write `docs/security.md` describing the security model

---

## Phase 6 — OpenAPI / Swagger Documentation

**Goal:** Interactive API documentation auto-generated from code annotations, available at `/api/docs`, auto-updated in CI.

### Tasks

- [ ] Publish l5-swagger config: `php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"`
- [ ] Configure `config/l5-swagger.php`:
  - `documentationPath` -> `/api/docs`
  - `scanPaths` -> `['app/Http/Controllers']`
  - `generate_always` -> `true` in dev, `false` in prod
- [ ] Add global `@OA\Info` annotation to base `Controller.php`
- [ ] Add `@OA\SecurityScheme` for `BearerAuth` JWT
- [ ] Add OpenAPI annotations to every controller method
- [ ] Create `@OA\Schema` definitions for all API Resources
- [ ] Verify interactive UI works at `http://localhost:8080/api/docs`
- [ ] GitHub Action auto-regenerates spec when controllers/routes change

### Swagger GitHub Action

```yaml
# .github/workflows/swagger.yml
on:
  push:
    paths:
      - 'app/Http/Controllers/**'
      - 'routes/api.php'
jobs:
  regenerate-swagger:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.3' }
      - run: composer install --no-dev
      - run: php artisan l5-swagger:generate
      - uses: stefanzweifel/git-auto-commit-action@v5
        with:
          commit_message: "docs(swagger): auto-regenerate OpenAPI spec [skip ci]"
          file_pattern: "storage/api-docs/*"
```

---

## Phase 7 — Testing (PHPUnit + Pest)

**Goal:** Comprehensive test suite covering all endpoints and business logic, minimum 80% line coverage enforced in CI.

### Test Categories

| Type | Location | Tool | Example |
|---|---|---|---|
| Unit — Services | `tests/Unit/Services/` | PHPUnit | `AuthServiceTest.php` |
| Unit — Repositories | `tests/Unit/Repositories/` | PHPUnit | `UserRepositoryTest.php` |
| Feature — Auth | `tests/Feature/Api/Auth/` | Pest | `LoginTest.php` |
| Feature — Endpoints | `tests/Feature/Api/` | Pest | `UsersTest.php` |
| Security | `tests/Feature/Security/` | Pest | `InjectionTest.php` |

### Tasks

- [ ] Configure `phpunit.xml` for PostgreSQL test DB (`DB_CONNECTION=pgsql`, `DB_DATABASE=bapidapi_test`)
- [ ] Create `DatabaseTestCase` using `RefreshDatabase` trait
- [ ] Write `AuthTest` covering register, login, logout, refresh, me (happy path + error cases)
- [ ] Write feature tests for every CRUD endpoint (401, 422, happy path, pagination)
- [ ] Write unit tests for all Services
- [ ] Write unit tests for all Repositories
- [ ] Write security tests (SQL injection, XSS, rate limiting)
- [ ] Configure Pest coverage threshold: `--coverage --min=80`
- [ ] Create `tests/Pest.php` with helper utilities
- [ ] Factory definitions for all models

---

## Phase 8 — CI/CD Pipeline (GitHub Actions)

**Goal:** Every push to `main` and every PR runs linting, security checks, tests with coverage, and optionally deploys to production.

### Workflows

| File | Trigger | Purpose |
|---|---|---|
| `.github/workflows/lint.yml` | push, PR | Laravel Pint style check |
| `.github/workflows/tests.yml` | push, PR | Pest + PHPUnit + coverage (80%+ min) |
| `.github/workflows/security.yml` | push, weekly | `composer audit` + Enlightn + Trivy image scan |
| `.github/workflows/swagger.yml` | controller/route changes | Regenerate + auto-commit OpenAPI spec |
| `.github/workflows/deploy.yml` | push to `main` | SSH deploy to production server |

### tests.yml structure

```yaml
services:
  postgres:
    image: postgres:16
    env:
      POSTGRES_DB: testing
      POSTGRES_USER: root
      POSTGRES_PASSWORD: password
    ports: ['5432:5432']
    options: --health-cmd pg_isready --health-interval 10s --health-timeout 5s --health-retries 5
steps:
  - uses: shivammathur/setup-php@v2
    with:
      php-version: '8.3'
      extensions: mbstring, xml, pdo_pgsql, pgsql
      coverage: pcov
  - run: composer install --prefer-dist --no-interaction
  - run: cp .env.example .env && php artisan key:generate
  - run: php artisan migrate --force
  - run: ./vendor/bin/pest --coverage --min=80
```

### Branch Strategy

```
main          — production deployments (protected)
develop       — integration branch
feature/*     — feature branches (PRs to develop)
hotfix/*      — urgent production fixes (PRs to main)
```

---

## Phase 9 — Production Deployment

**Goal:** Reproducible, automated production deployment on any Docker-capable server.

### Production Architecture

```
Internet -> CDN / Load Balancer -> Nginx (TLS termination) -> PHP-FPM App Containers
                                                           -> PostgreSQL (managed)
                                                           -> Redis (managed)
```

### Tasks

- [ ] Write `compose.prod.yaml` (no bind mounts, `restart: unless-stopped`, internal network only)
- [ ] Write multi-stage `Dockerfile` prod target:
  - `composer install --no-dev --optimize-autoloader`
  - Config/route/view cache baked in
  - OPcache enabled
  - Runs as `www-data` non-root user
- [ ] Write `docker/nginx/prod.conf` (security headers, no `server_tokens`, rate limiting)
- [ ] Create `deploy.sh` deployment script for server-side execution
- [ ] Document zero-downtime deployment strategy (rolling restart)
- [ ] Configure environment secrets via `.env` on server (not baked into image)

### Horizontal Scaling

The app containers are stateless (queue jobs in Redis, file storage in S3-compatible object storage). Scale with:

```bash
docker compose -f compose.prod.yaml up -d --scale app=3
```

---

## Phase 10 — AI Agent Configuration

**Goal:** Correct context files for Claude, Gemini/Antigravity, OpenAI Codex, and GitHub Copilot.

### Files to Create

| File | Tool | Purpose |
|---|---|---|
| `AGENTS.md` | Cross-tool standard | Primary machine-readable project context |
| `CLAUDE.md` | Claude Code | Symlink or copy of `AGENTS.md` |
| `.claude/settings.json` | Claude Code | Permission and behavior overrides |
| `.claude/rules/backend.md` | Claude Code | Laravel/PHP conventions |
| `.claude/rules/testing.md` | Claude Code | Test patterns |
| `.claude/rules/security.md` | Claude Code | Security constraints |
| `.agents/workflows/deploy.md` | Gemini/Antigravity | Deploy workflow steps |
| `.agents/workflows/run-tests.md` | Gemini/Antigravity | Test runner workflow |
| `.agents/workflows/update-docs.md` | Gemini/Antigravity | Docs update workflow |
| `CODEX.md` | OpenAI Codex | Codex-compatible instructions |
| `.github/copilot-instructions.md` | GitHub Copilot | Copilot workspace instructions |

### Content Requirements for `AGENTS.md`

The file must include:
- Tech stack summary (PHP 8.3, Laravel 12, PostgreSQL 16, Redis, Docker)
- Essential commands: `docker compose up`, `php artisan test`, `./vendor/bin/pint`
- Architecture: Repository pattern, Service layer, FormRequests, JsonResources
- Coding conventions reference (`docs/coding-guideline.md`)
- Security constraints (no `$guarded = []`, no raw SQL with user input, no secrets in code)
- References to: endpoint catalogue (`docs/api-endpoints.md`), architecture doc (`docs/architecture.md`)

---

## Verification Checklist

After completing all phases, verify each item:

- [ ] `docker compose up --build -d` starts all services without errors
- [ ] `GET http://localhost:8080/api/v1/health` returns `{"status":"ok"}`
- [ ] Full JWT auth flow: register -> login -> authenticated request -> logout
- [ ] `GET http://localhost:8080/api/docs` shows interactive Swagger UI
- [ ] `docker compose exec app ./vendor/bin/pest --coverage --min=80` passes with 80%+
- [ ] `docker compose exec app php artisan enlightn` reports no critical issues
- [ ] All 5 GitHub Actions workflows pass on `main` branch
- [ ] `compose.prod.yaml` starts without errors on a clean server
- [ ] All AI agent context files are present and readable

---

## Open Questions & Decisions Log

| # | Question | Status | Decision |
|---|---|---|---|
| 1 | What domain-specific entities will the API manage beyond Users? | Resolved | Order parking lots |
| 2 | Will file uploads be required? Local storage or S3-compatible? | Resolved | Both should be supported (S3 compatible available in Docker too via MinIO/similar) |
| 3 | What is the target production hosting environment? | Resolved | VPS Docker and Azure Container App |
| 4 | Should the API support versioning beyond `/v1`? | Resolved | `/v1` for now, `/v2` possible in the future |
| 5 | Email provider for password reset? | Resolved | Mailpit for now |
| 6 | Custom domain and TLS cert for `/api/docs`? | Open | TBD |
| 7 | Codecov or self-hosted coverage reporting? | Recommended | Codecov (free for public repos) |

---

*This document is a living specification. Commit it to version control. Review it at the start of every development session. All AI agents must treat this as their primary context when contributing to this project.*
