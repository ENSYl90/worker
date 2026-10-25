<?php

use Illuminate\Support\Facades\Cache;

if (!function_exists('cached_setting')) {
    function cached_setting(string $key, $default = null, int $minutes = 60)
    {
        return Cache::remember("cached_setting_{$key}", $minutes * 60, function () use ($key, $default) {
            return setting($key, $default);
        });
    }
}

if (!function_exists('clear_cached_setting')) {
    function clear_cached_setting(string $key): void
    {
        Cache::forget("cached_setting_{$key}");
    }
}

if (!function_exists('remember_result')) {
    function remember_result(string $key, \Closure $callback, int $minutes = 60)
    {
        return Cache::remember($key, $minutes * 60, $callback);
    }
}
