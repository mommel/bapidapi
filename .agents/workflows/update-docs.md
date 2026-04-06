---
description: How to update OpenAPI documentation when endpoints change
---

# Update API Documentation

// turbo-all

## Steps

// turbo
1. Ensure the development environment is running
```bash
docker compose up -d
```

2. Add or update OpenAPI annotations on the changed controller methods

   Each action needs a complete `@OA\` annotation block, for example:
   ```php
   /**
    * @OA\Get(
    *     path="/api/v1/users",
    *     operationId="usersList",
    *     tags={"Users"},
    *     summary="List all users",
    *     security={{"BearerAuth":{}}},
    *     @OA\Response(response=200, description="Success"),
    *     @OA\Response(response=401, description="Unauthorized")
    * )
    */
   ```

// turbo
3. Regenerate the OpenAPI specification
```bash
docker compose exec app php artisan l5-swagger:generate
```

4. Verify the documentation in the browser
```
Open: http://localhost:8080/api/docs
Check: all new/changed endpoints appear with correct schemas and response codes
```

5. Update `docs/api-endpoints.md` with the new/changed endpoint details

6. Commit changes (annotation + generated spec + docs update)
```bash
git add app/Http/Controllers/ storage/api-docs/ docs/api-endpoints.md
git commit -m "docs(swagger): update OpenAPI spec for [endpoint description]"
```

## Notes

- The `storage/api-docs/api-docs.json` file is auto-generated — do NOT manually edit it
- In CI, the swagger workflow automatically regenerates the spec when controllers change
- Set `L5_SWAGGER_GENERATE_ALWAYS=true` in `.env` to auto-regenerate on every page load (dev only)
- All endpoints must be documented — PRs without annotations will not pass code review
