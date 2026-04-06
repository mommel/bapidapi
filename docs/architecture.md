# bapidapi — Architecture Document

> **Document Type:** Architecture Decision Record (ADR) + System Design
> **Status:** Living Document | **Last Updated:** 2026-04-06

---

## 1. System Overview

`bapidapi` is a stateless RESTful HTTP API built on PHP 8.3 and Laravel 12. It is designed to operate as a horizontally scalable, containerized microservice. All state is external to the application process — stored in PostgreSQL (persistent data), Redis (ephemeral/session/queue state), and optionally S3-compatible object storage (files).

```
                        ┌──────────────────────────────────────────┐
                        │           Production Environment         │
    Clients             │                                          │
   (Web/Mobile/3P)      │  ┌─────────────┐   ┌──────────────────┐  │
      │                 │  │  CDN / WAF  │   │  Load Balancer   │  │
      └─────────────────┼─>│  (Cloudflare│──>│  (Nginx / LB)    │  │
                        │  │  or similar)│   └──────────────────┘  │
                        │  └─────────────┘            │            │
                        │                    ┌─────────┴────────┐  │
                        │                    │  App Container   │  │
                        │                    │  PHP-FPM 8.3     │  │
                        │                    │  Laravel 12      │  │
                        │                    └─────────┬────────┘  │
                        │                             │            │
                        │         ┌───────────────────┼──────────┐ │
                        │         │                   │          │ │
                        │   ┌─────┴─────┐      ┌──────┴──────┐   │ │
                        │   │ PostgreSQL│      │    Redis    │   │ │
                        │   │   16      │      │    7        │   │ │
                        │   └───────────┘      └─────────────┘   │ │
                        │                                        │ │
                        └──────────────────────────────────────────┘
```

---

## 2. Architectural Layers

### 2.1 Transport Layer (Nginx + PHP-FPM)

- Nginx acts as reverse proxy terminating TLS and forwarding HTTP requests to PHP-FPM via the FastCGI protocol.
- Nginx handles static asset serving, rate limiting at the network level, and security headers.
- PHP-FPM manages a pool of PHP worker processes, enabling concurrent request handling.

**Decision:** Nginx over Apache
- Superior performance under concurrent requests
- Fine-grained FastCGI configuration
- Better integration with Docker (stateless configuration)

### 2.2 Application Layer (Laravel 12)

The application follows a layered architecture inside Laravel:

```
Request -> Middleware stack -> Route -> FormRequest (validation) -> Controller
Controller -> Service -> Repository -> Eloquent Model -> Database
Controller -> JsonResource -> JSON Response
```

**Middleware stack (inbound):**
1. `TrustProxies` — correct IP detection behind load balancers
2. `SecurityHeaders` — add HSTS, CSP, etc.
3. `ForceJsonResponse` — set `Accept: application/json`
4. `ThrottleRequests` — rate limiting
5. `Authenticate` — JWT guard (protected routes only)

### 2.3 Domain Layer

- **Controllers** — thin. Delegating all logic to Services. Only responsible for parsing request, calling service, returning response.
- **Services** — contain business logic. Injected with Repositories via constructor injection.
- **Repositories** — encapsulate all database queries. Define an interface; Eloquent implementation is bound in `AppServiceProvider`.
- **Models** — Eloquent models define relationships, casts, fillable attributes. No business logic here.
- **Requests** — Laravel `FormRequest` classes handle all input validation and authorization.
- **Resources** — Laravel `JsonResource` classes transform models to JSON. All output goes through resources.

### 2.4 Persistence Layer

- **PostgreSQL 16** — primary relational datastore. Connection pooling via PgBouncer is recommended for large-scale deployments.
- **Redis 7** — used for: cache (L2 cache for expensive queries), queue workers (job queue), rate limiter storage.
- **S3-compatible object storage** (optional) — for file uploads if needed.

---

## 3. Authentication Architecture

### 3.1 JWT Flow

```
Client                              API
  |                                  |
  |--- POST /auth/login ------------>|
  |    { email, password }           |
  |                                  |--- validate credentials
  |                                  |--- generate access_token (15min)
  |                                  |--- generate refresh_token (7d)
  |<-- 200 { access_token, refresh_token } ---|
  |                                  |
  |--- GET /api/v1/resource -------->|
  |    Authorization: Bearer <token> |
  |                                  |--- verify JWT signature
  |                                  |--- check blacklist
  |                                  |--- extract user id
  |<-- 200 { data }                  |
  |                                  |
  |--- POST /auth/logout ----------->|
  |    Authorization: Bearer <token> |
  |                                  |--- add token to blacklist table
  |<-- 204 No Content               |
```

### 3.2 Token Revocation

JWTs are inherently stateless — they cannot be "deleted." To support logout and password-change revocation, a `jwt_blacklist` database table stores invalidated token JTI (JWT ID) values until their natural expiry.

**Trade-off acknowledged:** This adds one database read per authenticated request to check the blacklist. For high-traffic scenarios, this check can be cached in Redis with a TTL matching the token's remaining lifetime.

### 3.3 Password Storage

Passwords are hashed with bcrypt (Laravel default, cost factor 12). Plain-text passwords are never logged, transmitted in responses, or stored.

---

## 4. Security Architecture

Security is layered — multiple independent mechanisms must all be bypassed for an attack to succeed.

### 4.1 Application-Level

- All database queries go through Eloquent ORM (parameterized queries — SQL injection prevention)
- All input validated with FormRequests (type casting, regex rules)
- All output through JsonResource (controlled serialization — no accidental data leakage)
- Mass assignment protection via `$fillable` on all models
- HTTP method restriction (`Route::apiResource` generates only API-relevant verbs)

### 4.2 Transport-Level

- HTTPS-only enforced via HSTS header (`Strict-Transport-Security: max-age=31536000; includeSubDomains`)
- TLS 1.2+ only (configured in Nginx)
- Sensitive headers stripped from responses

### 4.3 Infrastructure-Level

- Non-root Docker container user (`www-data`)
- Internal Docker network (DB port not exposed to host)
- Secrets managed via environment variables, not baked into images
- Docker image scanned for CVEs in CI (Trivy)

---

## 5. Scalability Architecture

### 5.1 Stateless Design

All application state is externalized:

| State type | Storage |
|---|---|
| User data | PostgreSQL |
| Sessions | Not used (JWT) |
| Cache | Redis |
| Queue jobs | Redis |
| Uploaded files | S3-compatible storage |
| Logs | stdout (aggregated by Docker logging driver) |

Because no state lives in the app container, any number of identical containers can run simultaneously.

### 5.2 Horizontal Scaling

```bash
# Scale to 3 app containers
docker compose -f compose.prod.yaml up -d --scale app=3
```

Load balancer distributes requests across app containers using round-robin or least-connections.

### 5.3 Database Scaling

Read-heavy workloads: add PostgreSQL read replicas. Route read queries via `DB::connection('pgsql_read')` (configured in `config/database.php`).

Write scaling: PostgreSQL connection pooling via PgBouncer.

---

## 6. CI/CD Architecture

```
Developer push
    │
    ├─> Lint (Pint) ──────────────────────────── fail: block PR
    │
    ├─> Tests (Pest + PHPUnit + coverage) ─────  fail: block PR
    │
    ├─> Security (Enlightn + Trivy + audit) ───  fail: block PR
    │
    ├─> Swagger (auto-regenerate on change) ───  auto-commit spec
    │
    └─> Deploy (main branch only) ─────────────  SSH + docker compose pull + up
```

All CI jobs run in parallel (lint, tests, security) to minimize feedback time.

---

## 7. Decisions Made — Alternative Approaches Considered

| Decision | Chosen | Rejected | Reason |
|---|---|---|---|
| Auth mechanism | JWT (stateless) | Sanctum (token-based), Passport (OAuth2) | Explicit stateless requirement; Sanctum requires sessions for web mode; Passport is overkill |
| Database | PostgreSQL 16 | MySQL 8, SQLite | Better JSONB, GIN indexes, ACID guarantees; MySQL licensing concern in some cloud deployments |
| Test runner | Pest | PHPUnit-only | Pest's expressive syntax reduces boilerplate; both coexist in the same project |
| Container base | `php:8.3-fpm-alpine` | `php:8.3-fpm` (Debian) | Alpine is ~3x smaller; sufficient for all required PHP extensions |
| Code style | Laravel Pint (PSR-12) | PHP_CodeSniffer, PHP CS Fixer directly | Pint is Laravel-native, zero config by default, uses PHP CS Fixer under the hood |
| API docs | l5-swagger (OpenAPI 3.0) | Scribe, raw YAML | Annotation-based keeps docs with code; OpenAPI 3.0 standard; interactive UI included |
| Queue driver | Redis | Database, SQS | Local dev parity with production; Redis also handles caching — one service for two concerns |

---

## 8. Future Considerations

- **GraphQL endpoint** — Could be added alongside REST using Lighthouse PHP if needed.
- **Event Sourcing** — For audit-critical domains, consider Laravel Event Sourcing (Spatie).
- **API Gateway** — For multi-service architectures, introduce Kong or AWS API Gateway in front.
- **Observability** — Add Prometheus metrics endpoint + Grafana dashboard. Structured logging with Monolog -> Elasticsearch.
- **Multi-tenancy** — If required, the Repository pattern makes it trivial to add tenant scoping.
