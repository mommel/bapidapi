---
description: OpenAPI annotation rules — must follow when creating or modifying any API endpoint
---

# OpenAPI Annotation Rules for bapidapi

> **Mandatory for all AI agents.** The CI swagger workflow (`swagger.yml`) will fail if any
> controller action is missing or has an incomplete `@OA\` annotation block.
> Every time you create or modify a controller action you MUST add or update the annotation.

---

## Core Requirement

Every controller method that maps to a route MUST have a complete `@OA\` docblock annotation
placed immediately before the method signature. No exceptions.

---

## Annotation Templates

### GET (list / index)

```php
/**
 * @OA\Get(
 *     path="/api/v1/{resource}",
 *     operationId="{resource}Index",
 *     tags={"{Resource}"},
 *     summary="List all {resources}",
 *     description="Returns a paginated list.",
 *     security={{"BearerAuth":{}}},
 *     @OA\Parameter(
 *         name="pageSize", in="query", required=false,
 *         description="Items per page (default 20)",
 *         @OA\Schema(type="integer", example=20)
 *     ),
 *     @OA\Response(
 *         response=200, description="Success",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(ref="#/components/schemas/{Resource}Resource")
 *             ),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="current_page", type="integer"),
 *                 @OA\Property(property="per_page", type="integer"),
 *                 @OA\Property(property="total", type="integer")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
```

### GET (show single)

```php
/**
 * @OA\Get(
 *     path="/api/v1/{resource}/{id}",
 *     operationId="{resource}Show",
 *     tags={"{Resource}"},
 *     summary="Get a single {resource}",
 *     security={{"BearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id", in="path", required=true,
 *         description="{Resource} UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(
 *         response=200, description="Success",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/{Resource}Resource")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(response=404, description="Not found",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
```

### POST (store / create)

```php
/**
 * @OA\Post(
 *     path="/api/v1/{resource}",
 *     operationId="{resource}Store",
 *     tags={"{Resource}"},
 *     summary="Create a new {resource}",
 *     security={{"BearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"field_one"},
 *             @OA\Property(property="field_one", type="string", example="value")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201, description="Created",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/{Resource}Resource")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(response=422, description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 */
```

### PUT/PATCH (update)

```php
/**
 * @OA\Put(
 *     path="/api/v1/{resource}/{id}",
 *     operationId="{resource}Update",
 *     tags={"{Resource}"},
 *     summary="Update a {resource}",
 *     security={{"BearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id", in="path", required=true,
 *         description="{Resource} UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="field_one", type="string", example="updated value")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200, description="Success",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/{Resource}Resource")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(response=404, description="Not found",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(response=422, description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 */
```

### DELETE (destroy)

```php
/**
 * @OA\Delete(
 *     path="/api/v1/{resource}/{id}",
 *     operationId="{resource}Destroy",
 *     tags={"{Resource}"},
 *     summary="Delete a {resource}",
 *     security={{"BearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id", in="path", required=true,
 *         description="{Resource} UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(response=204, description="Deleted successfully"),
 *     @OA\Response(response=401, description="Unauthenticated",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(response=404, description="Not found",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
```

---

## Naming Rules

| Field | Convention | Example |
|---|---|---|
| `operationId` | `{camelCaseResource}{Action}` | `vehicleIndex`, `authLogin` |
| `tags` | Title-case resource name | `{"Vehicles"}`, `{"Auth"}` |
| `path` | Matches route in `routes/api.php` | `/api/v1/vehicles/{id}` |

## Security Rules

- **Protected route** → `security={{"BearerAuth":{}}}` is **required**
- **Public routes** (login, register, forgot-password, reset-password) → omit `security`

## Required Response Codes

| Scenario | Code | Required when |
|---|---|---|
| Success | 200 / 201 | Always |
| Unauthenticated | 401 | Route uses `auth:api` middleware |
| Forbidden | 403 | Route is role-restricted |
| Not found | 404 | Route has path parameter `{id}` |
| Validation failed | 422 | Route accepts a request body |

## Key Prohibition

> ❌ **Do NOT use `@OA\PathItem` on controller methods.**
> Use only HTTP-method annotations (`@OA\Get`, `@OA\Post`, `@OA\Put`, `@OA\Patch`, `@OA\Delete`)
> directly on method docblocks. `@OA\PathItem` is only valid as a standalone class-level
> annotation used to group path-level parameters — it is NOT a wrapper for HTTP methods.

---

## Verification Step

After writing or updating any annotation, run:

```bash
docker compose exec app php artisan l5-swagger:generate
```

If this command fails, fix the annotation before committing.

---

## Pre-commit Checklist

- [ ] Every route-mapped method has `@OA\{HttpMethod}(...)` annotation
- [ ] `operationId` is unique across ALL controllers
- [ ] All applicable status codes (200/201, 401, 403, 404, 422) are documented
- [ ] `security` is present on protected routes
- [ ] `php artisan l5-swagger:generate` runs without errors
