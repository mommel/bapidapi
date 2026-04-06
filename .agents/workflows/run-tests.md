---
description: How to run the test suite with coverage reports
---

# Run Tests

// turbo-all

## Steps

// turbo
1. Ensure the development environment is running
```bash
docker compose up -d
docker compose exec app php artisan migrate --env=testing
```

// turbo
2. Run all tests with coverage
```bash
docker compose exec app ./vendor/bin/pest --coverage --min=80
```

// turbo
3. Run only feature tests
```bash
docker compose exec app ./vendor/bin/pest tests/Feature/
```

// turbo
4. Run only unit tests
```bash
docker compose exec app ./vendor/bin/pest tests/Unit/
```

5. Run a specific test file
```bash
docker compose exec app ./vendor/bin/pest tests/Feature/Api/Auth/LoginTest.php
```

6. Run tests matching a description pattern
```bash
docker compose exec app ./vendor/bin/pest --filter "returns 401"
```

// turbo
7. Generate HTML coverage report
```bash
docker compose exec app ./vendor/bin/pest --coverage --coverage-html coverage/
```

## Test Database

Tests use a separate PostgreSQL database: `bapidapi_test`

The `.env.testing` file configures test-specific database settings. The `RefreshDatabase` trait resets state between tests.

## Coverage Requirements

- Minimum: 80% line coverage enforced in CI
- If coverage drops below 80%, the CI pipeline fails and blocks the PR
- New endpoints MUST include: 401, 403, 422, and success case tests

## Checking Failed Tests

If tests fail, check:
1. Is the Docker environment running? (`docker compose ps`)
2. Are migrations up to date? (`docker compose exec app php artisan migrate`)
3. Is `.env.testing` configured correctly?
