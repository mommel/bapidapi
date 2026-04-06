# bapidapi — OpenAI Codex Context

## Project

A production-ready RESTful API built with PHP 8.3 and Laravel 12.

## Stack

- Language: PHP 8.3 (strict types required)
- Framework: Laravel 12
- Database: PostgreSQL 16 (via Eloquent ORM)
- Cache/Queue: Redis 7
- Auth: JWT (`php-open-source-saver/jwt-auth`)
- Docs: OpenAPI 3.0 (`darkaonline/l5-swagger`)
- Tests: Pest + PHPUnit (80%+ coverage required)
- Style: PSR-12, enforced by Laravel Pint

## Architecture

```
Controller → Service → Repository (Interface) → Eloquent Model → PostgreSQL
                  ↑ all business logic here
                        ↑ all DB queries here
```

All requests validated by `FormRequest` classes.
All responses serialized by `JsonResource` classes.

## Strict Rules

1. `declare(strict_types=1)` mandatory in every PHP file
2. `$fillable` required on all models — `$guarded = []` is forbidden
3. No raw SQL with user input — Eloquent or parameterized queries only
4. No secrets or credentials in source code files
5. Every public API endpoint must have an OpenAPI annotation
6. Every endpoint must have a Pest feature test

## Commands

```bash
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app ./vendor/bin/pest
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan l5-swagger:generate
```

## Documentation

See `docs/` folder:
- `implementation-plan.md` — master plan
- `architecture.md`  — design decisions
- `coding-guideline.md` — code conventions
- `getting-started.md` — setup steps
