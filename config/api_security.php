<?php

return [
    'enforce_api_key' => env('API_SECURITY_ENFORCE_API_KEY', false),
    'key_cache_minutes' => (int) env('API_SECURITY_KEY_CACHE_MINUTES', 10),
];
