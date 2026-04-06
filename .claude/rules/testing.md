# Claude Code — Testing Conventions for bapidapi

## Test runner

Pest for feature tests. PHPUnit for unit tests. Both coexist.

## Feature test structure (Pest)

```php
<?php

declare(strict_types=1);

use App\Models\User;

it('returns 401 when not authenticated', function () {
    $this->getJson('/api/v1/users')
        ->assertStatus(401)
        ->assertJson(['success' => false]);
});

it('returns a paginated list of users', function () {
    $user = User::factory()->create();
    $token = auth('api')->login($user);

    $this->withToken($token)
        ->getJson('/api/v1/users')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [['id', 'name', 'email']],
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});
```

## Unit test structure (PHPUnit)

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\AuthService;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_login_returns_tokens_for_valid_credentials(): void
    {
        // ...
    }
}
```

## Every endpoint must have tests for

1. Unauthorized access → `assertStatus(401)`
2. Forbidden access (wrong role) → `assertStatus(403)` (if applicable)
3. Invalid input → `assertStatus(422)` with validation error structure
4. Successful operation → `assertStatus(200)` or `201` with expected structure

## Test database

- Use `RefreshDatabase` trait
- DB: PostgreSQL (`bapidapi_test`)
- Configured in `.env.testing`

## Factories

Every model must have a factory. Use `User::factory()->count(10)->create()` in tests.

## Commands

```bash
docker compose exec app ./vendor/bin/pest                    # all tests
docker compose exec app ./vendor/bin/pest --coverage --min=80 # with coverage
docker compose exec app ./vendor/bin/pest tests/Feature/      # feature only
```
