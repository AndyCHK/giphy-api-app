<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Giphy API Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí se definen los parámetros de configuración para la API de Giphy.
    |
    */

    'api_key' => env('GIPHY_API_KEY', ''),

    'base_url' => env('GIPHY_BASE_URL', 'https://api.giphy.com/v1'),

    'timeout' => env('GIPHY_TIMEOUT', 5),

    'retry_attempts' => env('GIPHY_RETRY_ATTEMPTS', 3),

    'retry_delay' => env('GIPHY_RETRY_DELAY', 1000),

    'use_cache' => env('GIPHY_USE_CACHE', true),

    'use_fallback' => env('GIPHY_USE_FALLBACK', true),

    'error_threshold' => env('GIPHY_ERROR_THRESHOLD', 5),

    'circuit_breaker_timeout' => env('GIPHY_CIRCUIT_BREAKER_TIMEOUT', 60),
];
