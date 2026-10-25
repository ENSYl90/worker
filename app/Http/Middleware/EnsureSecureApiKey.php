<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EnsureSecureApiKey
{
    public function handle(Request $request, Closure $next)
    {
        if (!(bool) config('api_security.enforce_api_key', false)) {
            return $next($request);
        }

        $plainKey = $request->header('X-API-Key');

        if (empty($plainKey) && $request->bearerToken()) {
            $plainKey = $request->bearerToken();
        }

        if (empty($plainKey)) {
            return $this->unauthorizedResponse();
        }

        $keyHash = ApiKey::hashKey($plainKey);
        $cacheKey = ApiKey::cacheKeyForHash($keyHash);
        $cacheMinutes = max((int) config('api_security.key_cache_minutes', 10), 1);

        $apiKeyId = Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($keyHash) {
            $apiKey = ApiKey::where('key_hash', $keyHash)
                ->where('is_active', true)
                ->first();

            if (empty($apiKey)) {
                return false;
            }

            $apiKey->forceFill([
                'last_used_at' => now(),
            ])->save();

            return $apiKey->id;
        });

        if (empty($apiKeyId)) {
            return $this->unauthorizedResponse();
        }

        return $next($request);
    }

    protected function unauthorizedResponse()
    {
        return response()->json([
            'message' => __('Invalid or missing API key'),
        ], 401);
    }
}
