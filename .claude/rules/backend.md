# Claude Code — Backend Conventions for bapidapi

## Architecture

```
FormRequest → Controller → Service → Repository (Interface) → Eloquent Model → PostgreSQL
                                                                             ↓
                                                                      JsonResource
```

## Mandatory at the top of every PHP file

```php
<?php

declare(strict_types=1);
```

## Constructor injection pattern

```php
public function __construct(
    private readonly UserRepositoryInterface $userRepository,
    private readonly TokenService $tokenService,
) {}
```

## Controller template

```php
class UserController extends ApiController
{
    public function __construct(private readonly UserService $userService) {}

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());
        return $this->successResponse(new UserResource($user), 'User created', 201);
    }
}
```

## Repository binding

All repository interfaces must be bound in `AppServiceProvider`:

```php
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);
```

## Available route middleware

- `auth:api` — JWT authentication required
- `throttle:60,1` — rate limiting
- `ForceJsonResponse` — applied globally

## Naming

- Controllers: `UserController` in `App\Http\Controllers\Api\V1\`
- Services: `UserService` in `App\Services\`
- Repositories: `UserRepositoryInterface` + `UserRepository` in `App\Repositories\`
- FormRequests: `CreateUserRequest`, `UpdateUserRequest` in `App\Http\Requests\`
- Resources: `UserResource`, `UserCollection` in `App\Http\Resources\`
