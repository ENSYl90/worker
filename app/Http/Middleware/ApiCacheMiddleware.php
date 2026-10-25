<?php

namespace App\Http\Middleware;

use App\Services\CacheKeys;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiCacheMiddleware
{
    /**
     * Define which endpoints should be cached, their tags, and TTL in minutes.
     * Use "*" as a wildcard for IDs or sub-paths.
     *
     * @var array
     */
    protected $cacheableEndpoints = [
        // Static information (Long TTL: 24h)
        'settings' => ['setting', 1440],
        'onboarding' => ['onboarding', 1440],
        'faqs' => ['faq', 1440],
        'cancellation/reasons' => ['cancellationreason', 1440],
        'payment/methods' => ['paymentmethod', 1440],
        'vehicle/types' => ['vehicletype', 1440],
        'package/types' => ['packagetype', 1440],
        'property-types' => ['propertytype', 1440],

        // Core Catalog (Mid TTL: 1h)
        'tags' => ['tag', 60],
        'categories' => ['category', 60],
        'banners' => ['banner', 60],
        'vendor/types' => ['vendortype', 60],
        'search/data' => ['category|vendor|tag', 60],

        // Dynamic Product/Vendor Data (Short TTL: 30m)
        'products' => ['product', 30],
        'products/*' => ['product', 30],
        'vendors' => ['vendor', 30],
        'vendors/*' => ['vendor', 30],
        'services' => ['service', 30],
        'services/*' => ['service', 30],
        'flash/sales' => ['flashsale', 30],
        'coupons' => ['coupon', 30],
        'properties' => ['property', 30],
        'properties/*' => ['property', 30],

        // User Personal Data (Very Short TTL: 5m)
        'my/profile' => ['user', 5],
        'wallet/balance' => ['wallet', 5],
        'loyalty/point/my' => ['loyaltypoint', 5],
        'delivery/addresses' => ['deliveryaddress', 30],
        'orders' => ['order', 2],
        'orders/*' => ['order', 2],
    ];

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Basic Filters
        $isCachingEnabled = env('API_CACHE_ENABLED', true);
        if (! $isCachingEnabled || ! $request->isMethod('GET')) {
            return $next($request);
        }

        // 2. Identify if this route is cacheable
        $cacheInfo = $this->getCacheInfo($request);
        if (! $cacheInfo) {
            return $next($request);
        }

        [$tags, $ttl] = $cacheInfo;
        $tagList = explode('|', $tags);

        // Ensure all tags have the correct suffix
        $formattedTags = array_map(function ($tag) {
            return str_ends_with($tag, '_cache') ? $tag : $tag.'_cache';
        }, $tagList);

        $cacheKey = CacheKeys::getRequestKey($request);
        
        // Check if the current driver supports tagging (e.g. Redis, Memcached)
        $supportsTags = Cache::getStore() instanceof \Illuminate\Cache\TaggableStore;
        $cache = $supportsTags ? Cache::tags($formattedTags) : Cache::driver();

        // 3. Serve from Cache
        $cachedData = $cache->get($cacheKey);
        if ($cachedData !== null) {
            return response()->json($cachedData['content'], 200)
                ->header('Content-Type', 'application/json')
                ->header('X-Cache-API', 'HIT');
        }

        // 4. Process Request
        $response = $next($request);

        // 5. Cache the Result (if successful)
        if ($response->getStatusCode() === 200) {
            $content = json_decode($response->getContent(), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                // Store with tags if supported, otherwise store globally
                $cache->put($cacheKey, [
                    'content' => $content,
                    'cached_at' => now()->toDateTimeString(),
                ], now()->addMinutes($ttl));

                $response->header('X-Cache-API', 'MISS');
            }
        }

        return $response;
    }

    /**
     * Matches the current request URI against the cacheable list.
     * e.g. "api/products/12" matches "products/*"
     */
    protected function getCacheInfo(Request $request)
    {
        $uri = ltrim(str_replace('api/', '', $request->path()), '/');

        // Direct matching
        if (isset($this->cacheableEndpoints[$uri])) {
            return $this->cacheableEndpoints[$uri];
        }

        // Wildcard matching (e.g. products/* matches products/12)
        foreach ($this->cacheableEndpoints as $pattern => $info) {
            if (strpos($pattern, '*') !== false) {
                $regex = str_replace(['/', '*'], ['\/', '[a-zA-Z0-9\-_]+'], $pattern);
                if (preg_match('/^'.$regex.'$/', $uri)) {
                    return $info;
                }
            }
        }

        return null;
    }
}
