# bapidapi — GitHub Copilot Instructions

## Project Overview

This is a production-ready RESTful API built with PHP 8.3, Laravel 12, and PostgreSQL 16.

## Technology Stack

- **PHP:** 8.3 with strict types enabled
- **Framework:** Laravel 12
- **Database:** PostgreSQL 16 (Eloquent ORM — no raw SQL with user input)
- **Auth:** JWT via `php-open-source-saver/jwt-auth`
- **Queue/Cache:** Redis 7
- **Container:** Docker + Docker Compose
- **Testing:** Pest + PHPUnit (80% coverage minimum)
- **Style:** PSR-12 via Laravel Pint
- **Docs:** OpenAPI 3.0 via `darkaonline/l5-swagger`

## Architecture Pattern

All code follows a strict layered architecture:

```
FormRequest → Controller (thin) → Service (logic) → Repository → Model → PostgreSQL
                                                              ↓
                                                       JsonResource → Response
```

## Copilot Coding Guidelines

### Always do

- Begin every PHP file with `declare(strict_types=1);`
- Define explicit `$fillable` on every Eloquent model
- Use `FormRequest` classes for validation (not inline `$request->validate()`)
- Use `JsonResource` classes for all API responses
- Add `@OA\` OpenAPI annotation blocks to every controller action
- Write a Pest feature test for every new endpoint
- Use repository interfaces — inject them via constructor, not `User::where(...)` in controllers
- Use constructor property promotion for dependency injection

### Never do

- `$guarded = []` on models (mass assignment risk)
- Raw SQL with user input (SQL injection risk)
- `dd()`, `dump()`, `var_dump()` in committed code
- Return raw Eloquent models from controllers or services
- Store secrets, passwords, or API keys in source files
- Business logic in controllers or models

## Common Patterns

### Controller

```php
public function store(CreateUserRequest $request): JsonResponse
{
    $user = $this->userService->create($request->validated());
    return $this->successResponse(new UserResource($user), 'User created', 201);
}
```

### Service

```php
public function create(array $data): User
{
    return $this->userRepository->create($data);
}
```

### Repository Interface

```php
interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function findByEmail(string $email): ?User;
}
```

## Testing Pattern

```php
it('returns 422 when email is missing', function () {
    $this->postJson('/api/v1/users', [])->assertStatus(422);
});

it('creates a user with valid data', function () {
    $this->postJson('/api/v1/users', [...])
        ->assertStatus(201)
        ->assertJsonStructure(['success', 'data' => ['id', 'email']]);
});
```

## Reference Documentation

- Master plan: `docs/implementation-plan.md`
- Architecture: `docs/architecture.md`
- Coding conventions: `docs/coding-guideline.md`
- Setup guide: `docs/getting-started.md`
- API endpoint catalogue: `docs/api-endpoints.md`
