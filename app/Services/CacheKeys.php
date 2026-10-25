<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheKeys
{
    // Global toggle key
    public const API_CACHE_ENABLED = 'api_cache_enabled';

    /**
     * Get a standardized tag for a given model.
     *
     * @param  string|object  $model
     * @return string
     */
    public static function getTagForModel($model)
    {
        $className = is_object($model) ? class_basename($model) : class_basename((string) $model);

        return strtolower($className).'_cache';
    }

    /**
     * Generate a unique request key for an API call based on URL and query params.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public static function getRequestKey($request)
    {
        // Sort query parameters to ensure key consistency
        $query = $request->query();
        ksort($query);
        $url = $request->url().'?'.http_build_query($query);

        // Include user ID in key if authenticated to avoid cross-user cache hits
        $userId = $request->user() ? $request->user()->id : 'guest';

        return 'api_res_'.md5($url.'_'.$userId);
    }

    /**
     * Helper to clear cache tags for a model.
     *
     * @param  string|object  $model
     * @return void
     */
    public static function flushForModel($model)
    {
        $enableLogging = env('APP_ENV') === 'local';
        $className = is_object($model) ? class_basename($model) : class_basename((string) $model);
        if ($enableLogging) {
            logger('Cache flushing for model: '.$className);
        }
        try {
            $tag = self::getTagForModel($model);

            // Fix: Check if tagging is supported before calling tags()
            if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
                Cache::tags([$tag])->flush();
                if ($enableLogging) {
                    logger('Cache flushed for model: '.$className.' with tag: '.$tag);
                }
            } else {
                if ($enableLogging) {
                    logger('Cache flush by model skipped (tags not supported by current driver)');
                }
            }
        } catch (\Exception $ex) {
            if ($enableLogging) {
                logger('Cache flush failed for model: '.$className.' - Error: '.$ex->getMessage());
            }
        }
    }
}
