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
- Add a **complete** `@OA\` annotation block (with `operationId`, `tags`, `summary`, `security`, parameters, request body, and all applicable response codes) to every controller action — see the **OpenAPI Annotations** section below
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

## OpenAPI Annotations

> **Mandatory.** The CI `swagger.yml` workflow runs `php artisan l5-swagger:generate` and will
> **fail** if any route-mapped controller method is missing or has an incomplete `@OA\` block.

### Key Rules

1. Use **HTTP-method annotations only** on controller methods — `@OA\Get`, `@OA\Post`, `@OA\Put`,
   `@OA\Patch`, `@OA\Delete`. **Never** use `@OA\PathItem` as a wrapper on methods.
2. `operationId` must be **globally unique** — pattern: `{camelCaseResource}{Action}`
   (e.g., `vehicleIndex`, `authLogin`, `reservationStore`).
3. `tags` must be the Title-case resource name: `{"Vehicles"}`, `{"Auth"}`, etc.
4. `security={{"BearerAuth":{}}}` is **required** on every route protected by `auth:api`.
5. Omit `security` only for fully public endpoints (login, register, password reset).
6. Always document **all applicable** response codes:
   - `200`/`201` — success
   - `401` — unauthenticated (any `auth:api` route)
   - `403` — forbidden (role-restricted routes)
   - `404` — not found (routes with `{id}` path parameter)
   - `422` — validation error (routes accepting a request body)

### Minimal correct annotation examples

```php
// GET list
/**
 * @OA\Get(
 *     path="/api/v1/vehicles",
 *     operationId="vehicleIndex",
 *     tags={"Vehicles"},
 *     summary="List all vehicles",
 *     security={{"BearerAuth":{}}},
 *     @OA\Response(response=200, description="Success",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(ref="#/components/schemas/VehicleResource")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */

// POST create
/**
 * @OA\Post(
 *     path="/api/v1/vehicles",
 *     operationId="vehicleStore",
 *     tags={"Vehicles"},
 *     summary="Create a vehicle",
 *     security={{"BearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"plate","type"},
 *             @OA\Property(property="plate", type="string", example="AB-123-CD"),
 *             @OA\Property(property="type", type="string", example="car")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Created"),
 *     @OA\Response(response=401, description="Unauthenticated",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(response=422, description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 */
```

### Pre-commit checklist

- [ ] Every route-mapped method has a complete `@OA\{HttpMethod}` block
- [ ] `operationId` is unique across all controllers
- [ ] All applicable status codes documented (200/201, 401, 403, 404, 422)
- [ ] `security` present on all `auth:api`-protected routes
- [ ] `docker compose exec app php artisan l5-swagger:generate` succeeds

---

## Reference Documentation

- Master plan: `docs/implementation-plan.md`
- Architecture: `docs/architecture.md`
- Coding conventions: `docs/coding-guideline.md`
- Setup guide: `docs/getting-started.md`
- API endpoint catalogue: `docs/api-endpoints.md`
- OpenAPI annotation rules: `.claude/rules/openapi.md` / `.agents/workflows/openapi-annotations.md`
