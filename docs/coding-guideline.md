# bapidapi — Coding Guidelines

> **Mandatory for all contributors and AI agents.**
> These guidelines are enforced automatically in CI via Laravel Pint.
> **Last Updated:** 2026-04-06

---

## 1. PHP & PSR Standards

- **PHP version:** 8.3+ minimum. Use modern PHP features (match expressions, named arguments, readonly properties, enums, fibers).
- **Standard:** PSR-12 (Extended Coding Style Guide). Enforced by Laravel Pint.
- **Type declarations:** All function parameters, return types, and property types MUST be explicitly typed. `mixed` is forbidden unless unavoidable.
- **Strict types:** Every PHP file MUST begin with `declare(strict_types=1);`

```php
<?php

declare(strict_types=1);

namespace App\Services;
```

---

## 2. Naming Conventions

| Element | Convention | Example |
|---|---|---|
| Class | PascalCase | `UserService`, `AuthController` |
| Interface | PascalCase + `Interface` suffix | `UserRepositoryInterface` |
| Trait | PascalCase + `Trait` suffix | `HasTimestampsTrait` |
| Enum | PascalCase | `UserStatus` |
| Method | camelCase | `findByEmail()`, `createToken()` |
| Property | camelCase | `$refreshToken`, `$expiresAt` |
| Variable | camelCase | `$accessToken`, `$userId` |
| Constant | SCREAMING_SNAKE_CASE | `TOKEN_EXPIRY_MINUTES` |
| Database table | snake_case, plural | `users`, `jwt_blacklists` |
| Migration | `YYYY_MM_DD_HHMMSS_description` | `2026_04_06_120000_create_users_table` |
| Route name | kebab-case, dot notation | `auth.login`, `users.show` |
| Env variable | SCREAMING_SNAKE_CASE | `JWT_SECRET`, `DB_USERNAME` |

---

## 3. File & Class Structure

### Controller conventions

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->successResponse($result, 'Login successful');
    }
}
```

**Controller rules:**
- Controllers MUST be thin. No business logic. Delegate to Services.
- Always type-hint injected dependencies.
- Always use `FormRequest` classes for validation — never `$request->validate()` inside a controller.
- Return type must be `JsonResponse`.
- Use `$this->successResponse()` / `$this->errorResponse()` helpers from `ApiController`.

### Service conventions

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepositoryInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function login(array $credentials): array
    {
        // Business logic here
    }
}
```

**Service rules:**
- Services contain ALL business logic.
- Services MUST NOT write database queries directly — use repositories.
- Services MUST NOT return Eloquent models — return arrays or DTOs.
- Services MUST be injected into controllers via the constructor.

### Repository conventions

```php
<?php

declare(strict_types=1);

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
}
```

```php
<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }
}
```

**Repository rules:**
- Always define an interface first.
- Bind the implementation in `AppServiceProvider`.
- No raw SQL with user input — use Eloquent or parameterized query builder.
- Never return raw query builder instances — always finalize (`->get()`, `->first()`, etc.).

### Model conventions

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // REQUIRED: explicit fillable list (never use $guarded = [])
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // REQUIRED: explicit casts
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // REQUIRED: always hide sensitive fields
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
```

**Model rules:**
- `$fillable` MUST be defined. `$guarded = []` is banned.
- `$hidden` MUST include `password` and any sensitive fields.
- `$casts` MUST be defined for dates, booleans, and encrypted fields.
- Business logic does NOT belong in models.
- Relationships defined as methods using type hints.

---

## 4. API Response Format

All API responses must use the standardized helpers from `ApiController`:

```php
// Success
return $this->successResponse($data, 'Created successfully', 201);

// Error
return $this->errorResponse('VALIDATION_ERROR', 'Invalid input', $errors, 422);

// Paginated
return $this->paginatedResponse($resource, 'Users retrieved');
```

**Response envelope format:**

Success:
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

Error:
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

## 5. Validation & Requests

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth guard handles authorization at route level
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Please provide a valid email address.',
        ];
    }
}
```

**Validation rules:**
- Use `FormRequest` classes — never inline validation in controllers.
- Always define both `authorize()` and `rules()`.
- Use Laravel validation rule objects or rule arrays (not rule strings alone for complex rules).
- Sanitize and cast inputs via `prepareForValidation()` where needed.

---

## 6. Security Rules (Non-Negotiable)

1. **No raw SQL with user input.** Always use Eloquent or `DB::select()` with `?` bindings.
2. **No `$guarded = []`.** Always define `$fillable` explicitly.
3. **No secrets in code.** All configuration via `.env` only.
4. **No `dd()`, `dump()`, `var_dump()` in production code.** Use `Log::debug()` instead.
5. **No passwords in logs.** Mask sensitive fields in log contexts.
6. **No `APP_DEBUG=true` in production.** Enforced by Enlightn in CI.
7. **Input validation before any database operation.**
8. **Output through JsonResource always** — never return Eloquent models directly.

---

## 7. Testing Conventions

```php
# Feature test — Pest style
it('returns 401 when not authenticated', function () {
    $response = $this->getJson('/api/v1/users');

    $response->assertStatus(401)
        ->assertJson(['success' => false]);
});

it('returns a list of users when authenticated', function () {
    $user = User::factory()->create();
    $token = auth('api')->login($user);

    $response = $this->withToken($token)->getJson('/api/v1/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [['id', 'name', 'email']],
            'meta' => ['version', 'timestamp'],
        ]);
});
```

**Testing rules:**
- Test file names must match the class they test: `UserService` -> `UserServiceTest.php`
- Every endpoint must have a test for: unauthorized (401), forbidden (403), invalid input (422), and success (200/201)
- Use factories to generate test data — never hardcode values
- Use `RefreshDatabase` trait to reset state between tests
- Mock external services (email, payment, etc.) in unit tests

---

## 8. Git & Commit Conventions

### Branch naming

```
feature/user-authentication
bugfix/jwt-token-expiry
hotfix/xss-sanitization
docs/update-api-spec
chore/upgrade-laravel-12
```

### Commit message format (Conventional Commits)

```
type(scope): short description

[Optional body explaining WHY, not WHAT]

[Optional footer: closes #issue]
```

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `ci`, `perf`, `security`

Examples:
```
feat(auth): add JWT refresh token endpoint
fix(middleware): correct CORS allowed origins
security(model): add fillable list to prevent mass assignment
docs(swagger): add OpenAPI annotation to UserController
test(auth): add rate limiting test for login endpoint
```

---

## 9. Code Style Enforcement

Laravel Pint is configured with `pint.json` at the project root.

```bash
# Check style (CI mode — fails if issues found)
./vendor/bin/pint --test

# Auto-fix style issues
./vendor/bin/pint
```

Pre-commit hook (optional, recommended):
```bash
#!/bin/sh
./vendor/bin/pint --test
if [ $? -ne 0 ]; then
    echo "Code style violations found. Run './vendor/bin/pint' to fix."
    exit 1
fi
```

---

## 10. OpenAPI Annotation Standard

Every controller action MUST have complete OpenAPI annotations:

```php
/**
 * @OA\Post(
 *     path="/api/v1/auth/login",
 *     operationId="authLogin",
 *     tags={"Authentication"},
 *     summary="Authenticate user and issue JWT tokens",
 *     description="Validates credentials and returns a short-lived access token and a long-lived refresh token.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
 *     ),
 *     @OA\Response(response=200, description="Login successful", @OA\JsonContent(ref="#/components/schemas/AuthTokenResponse")),
 *     @OA\Response(response=401, description="Invalid credentials"),
 *     @OA\Response(response=422, description="Validation error"),
 *     @OA\Response(response=429, description="Too many requests")
 * )
 */
public function login(LoginRequest $request): JsonResponse
```

**Annotation rules:**
- `operationId` must be unique across the entire API.
- `tags` groups related endpoints (match controller name without "Controller").
- Document ALL possible HTTP response codes (200, 201, 400, 401, 403, 404, 422, 429, 500).
- All request bodies and responses must reference `$ref` schemas, not inline definitions.
- Schemas defined in `app/docs/ApiSchemas.php` or distributed near the models they describe.
