# Claude Code — Security Rules for bapidapi

## Non-negotiable security constraints

These rules MUST be followed without exception:

### 1. SQL Injection Prevention

NEVER write raw SQL with user input:

```php
// FORBIDDEN
DB::select("SELECT * FROM users WHERE email = '$email'");

// REQUIRED
DB::select("SELECT * FROM users WHERE email = ?", [$email]);

// PREFERRED — always use Eloquent
User::where('email', $email)->first();
```

### 2. Mass Assignment Protection

NEVER use `$guarded = []`:

```php
// FORBIDDEN
class User extends Model
{
    protected $guarded = [];
}

// REQUIRED
class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
}
```

### 3. Secrets Management

NEVER hardcode secrets:

```php
// FORBIDDEN
$apiKey = 'sk-prod-abc123...';

// REQUIRED
$apiKey = config('services.stripe.key');
// config/services.php reads from env('STRIPE_KEY')
```

### 4. Debug Prevention

NEVER commit debug statements:

```php
// FORBIDDEN
dd($user);
dump($request->all());
var_dump($token);

// USE INSTEAD (for debugging — remove before commit)
Log::debug('User data', ['user' => $user->id]);
```

### 5. Password & Token Logging

NEVER log sensitive data:

```php
// FORBIDDEN
Log::info('Login attempt', ['password' => $request->password]);

// REQUIRED
Log::info('Login attempt', ['email' => $request->email]);
```

### 6. Input Validation

ALWAYS validate before any database operation. Use `FormRequest` — not inline validation.

### 7. Output Sanitization

NEVER return raw Eloquent models from controllers. Always use `JsonResource`.

### 8. Security Headers

These headers are added by `SecurityHeaders` middleware (applied globally):
- `Strict-Transport-Security: max-age=31536000; includeSubDomains`
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy: default-src 'self'`

### 9. CORS

Allowed origins are configured via `CORS_ALLOWED_ORIGINS` env variable. Do not use wildcard `*` in production.

### 10. Rate Limiting

Auth routes: 6 requests/minute
Authenticated API: 60 requests/minute
Public API: 30 requests/minute
