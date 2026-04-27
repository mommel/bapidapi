# Claude Code — OpenAPI Annotation Rules for bapidapi

## Mandatory: Every controller action MUST have complete OpenAPI annotations

This is a non-negotiable requirement. The CI pipeline (`swagger.yml`) will fail if any
documented endpoint is missing its `@OA\` block. **Never create or modify a controller
action without adding or updating the corresponding annotation.**

---

## Required annotation structure

### Standard HTTP method (GET, POST, PUT, PATCH, DELETE)

```php
/**
 * @OA\Get(
 *     path="/api/v1/resource",
 *     operationId="resourceIndex",
 *     tags={"Resource"},
 *     summary="List all resources",
 *     description="Returns a paginated list of resources for the authenticated user.",
 *     security={{"BearerAuth":{}}},
 *     @OA\Parameter(
 *         name="pageSize",
 *         in="query",
 *         required=false,
 *         description="Number of items per page (default 20)",
 *         @OA\Schema(type="integer", example=20)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Paginated list of resources",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(ref="#/components/schemas/ResourceResource")
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
public function index(Request $request): AnonymousResourceCollection
```

### POST (create) — include request body

```php
/**
 * @OA\Post(
 *     path="/api/v1/resource",
 *     operationId="resourceStore",
 *     tags={"Resource"},
 *     summary="Create a new resource",
 *     security={{"BearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"field_one","field_two"},
 *             @OA\Property(property="field_one", type="string", example="value"),
 *             @OA\Property(property="field_two", type="string", example="value")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Resource created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/ResourceResource")
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
public function store(StoreResourceRequest $request): JsonResponse
```

### Path parameters (show, update, destroy)

```php
/**
 * @OA\Get(
 *     path="/api/v1/resource/{id}",
 *     operationId="resourceShow",
 *     tags={"Resource"},
 *     summary="Get a single resource",
 *     security={{"BearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Resource UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Resource details",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/ResourceResource")
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
public function show(string $id): JsonResponse
```

---

## Rules

1. **`operationId` must be unique** across all controllers. Use the pattern `{resource}{Action}`:
   - `vehicleIndex`, `vehicleStore`, `vehicleShow`, `vehicleUpdate`, `vehicleDestroy`
   - `authLogin`, `authRegister`, `authLogout`, `authRefresh`, `authMe`

2. **`tags`** must match the resource domain: `{"Auth"}`, `{"Vehicles"}`, `{"ParkingLots"}`,
   `{"Reservations"}`, `{"Drivers"}`.

3. **`security`** is required on all protected routes: `security={{"BearerAuth":{}}}`.
   Omit `security` only for public endpoints (register, login, forgot-password, reset-password).

4. **Always document these response codes** (where applicable):
   - `200` / `201` — success
   - `401` — unauthenticated
   - `403` — forbidden (if role-based)
   - `404` — not found (for endpoints with path parameters)
   - `422` — validation failed (for endpoints with a request body)

5. **`@OA\Schema` components** for models go on the corresponding `JsonResource` class, not the
   controller. Use the `ref="#/components/schemas/..."` reference in controller annotations.

6. **Do NOT use `@OA\PathItem`** at the method level — it belongs only on the class or in a
   separate annotation file. Use the HTTP-method annotations (`@OA\Get`, `@OA\Post`, etc.)
   directly on controller methods.

7. **After any annotation change**, regenerate the spec and verify:
   ```bash
   docker compose exec app php artisan l5-swagger:generate
   ```

---

## Checklist before committing a controller change

- [ ] Every public/protected method has a complete `@OA\` block
- [ ] `operationId` is unique project-wide
- [ ] All expected HTTP status codes are documented
- [ ] Path and query parameters are fully described
- [ ] Request body is documented for POST/PUT/PATCH
- [ ] `security` is present on auth-protected routes
- [ ] `php artisan l5-swagger:generate` runs without errors
