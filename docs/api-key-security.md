---
group: Security
title: API Key Security
order: 10
---

# API Key Security

API key security adds an optional universal check for every route in the Laravel `api` middleware group. When enforcement is enabled, clients must send a valid generated API key with each API request.

This is separate from Laravel Sanctum user authentication. API keys protect access to the API surface; Sanctum tokens still identify authenticated users on protected endpoints.

## Admin Page

The API security settings page is available in the admin panel:

```http
GET /admin/setting/api/security
```

Use this page to:

- Enable or disable universal API key enforcement.
- Set the API key validation cache duration.
- Generate multiple API keys.
- Copy the raw key once after generation.
- Enable or disable existing keys.
- Revoke keys after confirmation.

## Environment Settings

The enforcement and cache settings are stored in `.env` and exposed through `config/api_security.php`.

```env
API_SECURITY_ENFORCE_API_KEY=false
API_SECURITY_KEY_CACHE_MINUTES=10
```

Config access:

```php
config('api_security.enforce_api_key')
config('api_security.key_cache_minutes')
```

Settings:

- `API_SECURITY_ENFORCE_API_KEY`: when `true`, every API request must provide a valid API key.
- `API_SECURITY_KEY_CACHE_MINUTES`: number of minutes to cache each API key validation result. Minimum from the admin page is `1`; maximum is `1440`.

If config is cached in production, clear or rebuild config after manual `.env` edits:

```bash
php artisan config:clear
php artisan config:cache
```

The admin page clears the config cache after saving these settings.

## Database Table

API keys are stored in the `api_keys` table.

Important columns:

- `name`: optional display name for the key.
- `key_hash`: HMAC-SHA256 hash of the raw key. The raw key is never stored.
- `key_prefix`: short preview prefix for identifying the key in the admin UI.
- `last_four`: last four characters of the raw key for admin display.
- `is_active`: whether the key can authenticate API requests.
- `last_used_at`: updated when a key is validated from the database.
- `deleted_at`: revoked keys are soft deleted.

Run migrations before using the feature:

```bash
php artisan migrate
```

## Key Generation

Raw API keys are generated with an app-name-based prefix:

```text
{app_name_slug}_sk_{random_secret}
```

Example for `APP_NAME=Glover`:

```text
glover_sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

The raw key is shown only once immediately after generation. Store it securely before leaving the page or hiding it.

Only these safe values are stored:

- A one-way hash for validation.
- A short prefix preview.
- The last four characters.

## Sending API Keys

Preferred header:

```http
X-API-Key: {raw-api-key}
```

Bearer fallback:

```http
Authorization: Bearer {raw-api-key}
```

Example:

```http
GET /api/settings
X-API-Key: glover_sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Accept: application/json
```

If the endpoint also requires user authentication, send the API key in `X-API-Key` and the Sanctum token in `Authorization`:

```http
GET /api/my/profile
X-API-Key: glover_sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Authorization: Bearer {sanctum-token}
Accept: application/json
```

## Error Response

When enforcement is enabled and the key is missing, invalid, disabled, or revoked:

```json
{
  "message": "Invalid or missing API key"
}
```

Status code: `401`

## Validation Cache

The middleware caches API key validation by the hashed provided key.

Behavior:

- First request with a key checks the `api_keys` table.
- Valid active keys are cached for `API_SECURITY_KEY_CACHE_MINUTES`.
- Invalid keys are also cached briefly to reduce repeated database hits.
- Repeated requests with the same key use cache and skip the database lookup.
- Disabling or revoking a key from the admin page clears that key's cache entry immediately.

The cache never stores the raw API key.

Note: `last_used_at` is updated when the key is validated from the database. Requests served by the validation cache do not update `last_used_at` until the cache expires or is cleared.

## Middleware Order

The check runs in the Laravel `api` middleware group:

```php
\App\Http\Middleware\EnsureSecureApiKey::class
```

It is placed before API response caching, so protected responses are not served unless the request has a valid API key when enforcement is enabled.

## Rotation And Revocation

Recommended rotation flow:

1. Generate a new API key.
2. Update clients to send the new key.
3. Confirm clients are working.
4. Revoke the old key from the admin page.

Revoking a key:

- Requires browser confirmation in the admin UI.
- Soft deletes the key record.
- Clears the validation cache for that key.
- Takes effect immediately for future requests.

## Operational Notes

- Keep API keys out of mobile apps when possible; app binaries can be inspected.
- Use HTTPS for all API traffic.
- Prefer server-to-server use cases for API keys.
- Treat generated keys like passwords.
- Do not log raw API keys.
- If a key is exposed, revoke it and generate a replacement.
