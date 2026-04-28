---
description: OpenAPI annotation rules — must follow when creating or modifying any API endpoint
---

# OpenAPI Annotation Rules for bapidapi

> **Mandatory for all AI agents.** The CI swagger workflow (`swagger.yml`) will fail if any
> controller action is missing or has an incomplete annotation block.
> Every time you create or modify a controller action you MUST add or update the annotation.

> **Format:** This project uses **swagger-php v4 PHP Attributes** (OpenAPI 3.x, docblock v10 style).
> Do **NOT** use the old `/** @OA\... */` docblock syntax. All annotations MUST use `#[OA\...]` PHP 8 attributes.

---

## Core Requirement

Every controller method that maps to a route MUST have a complete `#[OA\...]` PHP attribute annotation
placed immediately before the method signature. No exceptions.

Add `use OpenApi\Attributes as OA;` to every controller that contains annotations.

---

## Annotation Templates

### GET (list / index)

```php
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/{resource}',
    operationId: '{resource}Index',
    summary: 'List all {resources}',
    description: 'Returns a paginated list.',
    security: [['BearerAuth' => []]],
    tags: ['{Resource}'],
    parameters: [
        new OA\Parameter(
            name: 'pageSize',
            in: 'query',
            required: false,
            description: 'Items per page (default 20)',
            schema: new OA\Schema(type: 'integer', example: 20)
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Success',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/{Resource}Resource')
                    ),
                    new OA\Property(
                        property: 'meta',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'current_page', type: 'integer'),
                            new OA\Property(property: 'per_page', type: 'integer'),
                            new OA\Property(property: 'total', type: 'integer'),
                        ]
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
    ]
)]
```

### GET (show single)

```php
#[OA\Get(
    path: '/api/v1/{resource}/{id}',
    operationId: '{resource}Show',
    summary: 'Get a single {resource}',
    security: [['BearerAuth' => []]],
    tags: ['{Resource}'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: '{Resource} UUID',
            schema: new OA\Schema(type: 'string', format: 'uuid')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Success',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data', ref: '#/components/schemas/{Resource}Resource'),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 404,
            description: 'Not found',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
    ]
)]
```

### POST (store / create)

```php
#[OA\Post(
    path: '/api/v1/{resource}',
    operationId: '{resource}Store',
    summary: 'Create a new {resource}',
    security: [['BearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['field_one'],
            properties: [
                new OA\Property(property: 'field_one', type: 'string', example: 'value'),
            ]
        )
    ),
    tags: ['{Resource}'],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Created',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data', ref: '#/components/schemas/{Resource}Resource'),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
        ),
    ]
)]
```

### PUT/PATCH (update)

```php
#[OA\Patch(
    path: '/api/v1/{resource}/{id}',
    operationId: '{resource}Update',
    summary: 'Update a {resource}',
    security: [['BearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'field_one', type: 'string', example: 'updated value'),
            ]
        )
    ),
    tags: ['{Resource}'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: '{Resource} UUID',
            schema: new OA\Schema(type: 'string', format: 'uuid')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Success',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data', ref: '#/components/schemas/{Resource}Resource'),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 404,
            description: 'Not found',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
        ),
    ]
)]
```

### DELETE (destroy)

```php
#[OA\Delete(
    path: '/api/v1/{resource}/{id}',
    operationId: '{resource}Destroy',
    summary: 'Delete a {resource}',
    security: [['BearerAuth' => []]],
    tags: ['{Resource}'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: '{Resource} UUID',
            schema: new OA\Schema(type: 'string', format: 'uuid')
        ),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Deleted successfully'),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 404,
            description: 'Not found',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
    ]
)]
```

---

## Global API Config (`app/Docs/OpenApi.php`)

The global `#[OA\Info]`, `#[OA\Server]`, `#[OA\SecurityScheme]`, and all `#[OA\Schema]` definitions
live in `app/Docs/OpenApi.php`. Use PHP Attributes on the class:

```php
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'My API',
    description: 'API description',
    contact: new OA\Contact(email: 'contact@example.com')
)]
#[OA\SecurityScheme(
    securityScheme: 'BearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Schema(
    schema: 'MySchema',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    ]
)]
class OpenApi {}
```

---

## Naming Rules

| Field | Convention | Example |
|---|---|---|
| `operationId` | `{camelCaseResource}{Action}` | `vehicleIndex`, `authLogin` |
| `tags` | Title-case resource name | `['Vehicles']`, `['Auth']` |
| `path` | Matches route in `routes/api.php` | `/api/v1/vehicles/{id}` |

## Security Rules

- **Protected route** → `security: [['BearerAuth' => []]]` is **required**
- **Public routes** (login, register, forgot-password, reset-password) → omit `security`

## Required Response Codes

| Scenario | Code | Required when |
|---|---|---|
| Success | 200 / 201 | Always |
| Unauthenticated | 401 | Route uses `auth:api` middleware |
| Forbidden | 403 | Route is role-restricted |
| Not found | 404 | Route has path parameter `{id}` |
| Validation failed | 422 | Route accepts a request body |

## Key Prohibitions

> ❌ **Do NOT use `/** @OA\... */` docblock annotations.** The project has migrated to PHP 8 Attributes.
> Use only `#[OA\...]` attribute syntax directly above each method.

> ❌ **Do NOT use `@OA\PathItem` on controller methods.**
> Use only HTTP-method attributes (`#[OA\Get]`, `#[OA\Post]`, `#[OA\Put]`, `#[OA\Patch]`, `#[OA\Delete]`)
> directly on methods.

---

## Verification Step

After writing or updating any annotation, run:

```bash
docker compose exec app php artisan l5-swagger:generate
```

If this command fails, fix the annotation before committing.

---

## Pre-commit Checklist

- [ ] `use OpenApi\Attributes as OA;` is imported in the controller
- [ ] Every route-mapped method has `#[OA\{HttpMethod}(...)]` attribute (NOT a docblock)
- [ ] `operationId` is unique across ALL controllers
- [ ] All applicable status codes (200/201, 401, 403, 404, 422) are documented
- [ ] `security` is present on protected routes
- [ ] `php artisan l5-swagger:generate` runs without errors
