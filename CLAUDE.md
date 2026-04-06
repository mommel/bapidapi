# bapidapi — Claude Code Context

This file is the primary context file for Claude Code. It mirrors `AGENTS.md`.
When working on this project, Claude should ALWAYS read `docs/implementation-plan.md` first.

---

## Stack

PHP 8.3 | Laravel 12 | PostgreSQL 16 | Redis 7 | Docker | JWT Auth | Pest | OpenAPI 3.0

## Essential Commands

```bash
docker compose up -d                                              # Start dev env
docker compose exec app ./vendor/bin/pest --coverage --min=80    # Run tests
docker compose exec app ./vendor/bin/pint                         # Fix code style
docker compose exec app php artisan l5-swagger:generate           # Regen API docs
docker compose exec app php artisan migrate                        # Run migrations
```

## Architecture Pattern

Controller (thin) → Service (business logic) → Repository (DB queries) → Model

## Key Rules

- `declare(strict_types=1)` in every file
- Explicit `$fillable` on all models — never `$guarded = []`
- No raw SQL with user input
- No secrets in code
- All validation via `FormRequest`
- All output via `JsonResource`
- OpenAPI annotation on every endpoint

## When Starting a Task

1. Read `docs/implementation-plan.md` for project context
2. Check `docs/coding-guideline.md` for conventions
3. Look at existing controllers/services/repositories as patterns
4. Write tests before or alongside implementation
5. Run `./vendor/bin/pint` before committing

## References

- Full plan: `docs/implementation-plan.md`
- Architecture: `docs/architecture.md`
- Guidelines: `docs/coding-guideline.md`
- Setup: `docs/getting-started.md`
