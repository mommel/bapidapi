# bapidapi — API Endpoint Catalogue

> This document is the canonical reference for all API endpoints.
> It drives the implementation plan and Swagger annotations.
> **Last Updated:** 2026-04-06 | **API Version:** v1

---

## Base URL

```
http://localhost:8080/api/v1     # Local development
https://api.yourdomain.com/v1   # Production
```

## Authentication

All protected endpoints require a JWT Bearer token in the `Authorization` header:

```
Authorization: Bearer <access_token>
```

---

## Standard Response Envelopes

### Success

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

### Error

```json
{
    "success": false,
    "error": {
        "code": "ERROR_CODE",
        "message": "Human readable message",
        "details": {}
    }
}
```

### Paginated List

```json
{
    "success": true,
    "data": [],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7,
        "version": "1.0.0",
        "timestamp": "2026-04-06T20:00:00Z"
    }
}
```

---

## Endpoints

### Health

| Method | Path | Auth | Description |
|---|---|---|---|
| GET | `/api/v1/health` | None | Application health check |

---

### Authentication (`/api/v1/auth`)

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/auth/register` | None | Register a new user |
| POST | `/api/v1/auth/login` | None | Login and get JWT tokens |
| POST | `/api/v1/auth/logout` | Required | Invalidate current access token |
| POST | `/api/v1/auth/refresh` | Required | Rotate access token using refresh token |
| GET | `/api/v1/auth/me` | Required | Get authenticated user profile |
| POST | `/api/v1/auth/password/forgot` | None | Send password reset email |
| POST | `/api/v1/auth/password/reset` | None | Reset password with token |

#### POST `/api/v1/auth/register`

Request body:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePassw0rd!",
    "password_confirmation": "SecurePassw0rd!"
}
```

Responses:
- `201` — User created, returns access_token and refresh_token
- `422` — Validation errors (email already taken, password too weak)
- `429` — Too many requests

#### POST `/api/v1/auth/login`

Request body:
```json
{
    "email": "john@example.com",
    "password": "SecurePassw0rd!"
}
```

Responses:
- `200` — Returns `access_token`, `refresh_token`, `token_type`, `expires_in`
- `401` — Invalid credentials
- `422` — Validation error
- `429` — Too many requests (6 attempts per minute)

#### POST `/api/v1/auth/logout`

Headers: `Authorization: Bearer <token>`

Responses:
- `204` — Token blacklisted, logout successful
- `401` — Token missing or invalid

#### POST `/api/v1/auth/refresh`

Headers: `Authorization: Bearer <refresh_token>`

Responses:
- `200` — New `access_token` issued
- `401` — Refresh token expired or blacklisted

#### GET `/api/v1/auth/me`

Headers: `Authorization: Bearer <token>`

Response `200`:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": "2026-04-06T20:00:00Z",
        "created_at": "2026-04-06T20:00:00Z"
    }
}
```

---

### Users (`/api/v1/users`)

> **Note:** Additional domain endpoints will be added here as product requirements are defined.

| Method | Path | Auth | Description |
|---|---|---|---|
| GET | `/api/v1/users` | Admin | List all users (paginated) |
| GET | `/api/v1/users/{id}` | Required | Get a specific user |
| PUT | `/api/v1/users/{id}` | Required | Update user profile |
| DELETE | `/api/v1/users/{id}` | Admin | Soft-delete user |

---

## HTTP Status Codes Used

| Code | Meaning | When |
|---|---|---|
| 200 | OK | Successful GET, PUT |
| 201 | Created | Successful POST (resource created) |
| 204 | No Content | Successful DELETE, logout |
| 400 | Bad Request | Malformed request |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Authenticated but not authorized |
| 404 | Not Found | Resource does not exist |
| 409 | Conflict | Resource already exists |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Unexpected server error |

---

## Rate Limiting

| Route group | Limit |
|---|---|
| Auth routes (`/auth/register`, `/auth/login`) | 6 requests per minute |
| Password reset | 3 requests per minute |
| Authenticated API | 60 requests per minute |
| Public API | 30 requests per minute |

Rate limit headers returned on every response:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
Retry-After: 60  (only on 429)
```

---

## Pagination

All list endpoints support pagination query parameters:

| Parameter | Default | Description |
|---|---|---|
| `page` | 1 | Page number |
| `per_page` | 15 | Results per page (max: 100) |

---

## Filtering & Sorting

Endpoints that return lists support:

| Parameter | Example | Description |
|---|---|---|
| `sort` | `sort=created_at` | Field to sort by |
| `order` | `order=desc` | Sort direction (`asc` or `desc`) |
| `search` | `search=john` | Full-text search (where implemented) |

---

*Add new endpoints to this document before or alongside implementation.*
