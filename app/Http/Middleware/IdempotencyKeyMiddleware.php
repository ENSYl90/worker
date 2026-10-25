<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $idempotencyKey = $request->header('Idempotency-Key', $request->input('idempotency_key'));

        if (empty($idempotencyKey)) {
            return $next($request);
        }

        $payloadHash = hash('sha256', json_encode($this->normalizePayload($request->except('idempotency_key'))));
        $cacheKey = $this->cacheKey($request, $idempotencyKey);
        $lockKey = "{$cacheKey}:lock";
        $ttl = now()->addMinutes((int) setting('orderIdempotencyTTL', 1440));
        $lockSeconds = (int) setting('orderIdempotencyLockSeconds', 30);

        $cachedResponse = Cache::get($cacheKey);
        if ($cachedResponse !== null) {
            return $this->responseFromCache($cachedResponse, $payloadHash);
        }

        $lock = Cache::lock($lockKey, $lockSeconds);

        try {
            return $lock->block($lockSeconds, function () use ($request, $next, $cacheKey, $payloadHash, $ttl) {
                $cachedResponse = Cache::get($cacheKey);
                if ($cachedResponse !== null) {
                    return $this->responseFromCache($cachedResponse, $payloadHash);
                }

                $response = $next($request);

                if ($this->shouldCacheResponse($response)) {
                    Cache::put($cacheKey, [
                        'payload_hash' => $payloadHash,
                        'content' => $response->getContent(),
                        'status' => $response->getStatusCode(),
                        'headers' => [
                            'content-type' => $response->headers->get('content-type', 'application/json'),
                        ],
                    ], $ttl);

                    $response->headers->set('Idempotency-Status', 'CREATED');
                }

                return $response;
            });
        } catch (LockTimeoutException $ex) {
            return response()->json([
                'message' => __('A request with this idempotency key is still processing. Please try again shortly.'),
            ], Response::HTTP_CONFLICT);
        }
    }

    protected function cacheKey(Request $request, string $idempotencyKey): string
    {
        $userId = optional($request->user())->id ?? 'guest';

        return 'idempotency:orders:' . sha1(implode('|', [
            $userId,
            $request->method(),
            $request->path(),
            $idempotencyKey,
        ]));
    }

    protected function normalizePayload(array $payload): array
    {
        ksort($payload);

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->normalizePayload($value);
            }
        }

        return $payload;
    }

    protected function responseFromCache(array $cachedResponse, string $payloadHash)
    {
        if (($cachedResponse['payload_hash'] ?? null) !== $payloadHash) {
            return response()->json([
                'message' => __('Idempotency key has already been used with a different request payload.'),
            ], Response::HTTP_CONFLICT);
        }

        return response($cachedResponse['content'], $cachedResponse['status'])
            ->header('Content-Type', $cachedResponse['headers']['content-type'] ?? 'application/json')
            ->header('Idempotency-Status', 'REPLAYED');
    }

    protected function shouldCacheResponse($response): bool
    {
        return $response instanceof Response
            && $response->getStatusCode() >= 200
            && $response->getStatusCode() < 300
            && method_exists($response, 'getContent');
    }
}
