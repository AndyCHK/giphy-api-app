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

    // API Key de Giphy
    'api_key' => env('GIPHY_API_KEY', ''),

    // URL base de la API
    'base_url' => env('GIPHY_BASE_URL', 'https://api.giphy.com/v1'),

    // Timeout de las peticiones en segundos
    'timeout' => env('GIPHY_TIMEOUT', 5),

    // Número de reintentos para peticiones fallidas
    'retry_attempts' => env('GIPHY_RETRY_ATTEMPTS', 3),

    // Tiempo de espera entre reintentos en milisegundos
    'retry_delay' => env('GIPHY_RETRY_DELAY', 1000),

    // Usar caché para almacenar respuestas
    'use_cache' => env('GIPHY_USE_CACHE', true),

    // Usar fallback en caso de error (intentar usar datos en caché)
    'use_fallback' => env('GIPHY_USE_FALLBACK', true),

    // Umbral de errores para activar el circuit breaker
    'error_threshold' => env('GIPHY_ERROR_THRESHOLD', 5),

    // Tiempo de timeout del circuit breaker en segundos
    'circuit_breaker_timeout' => env('GIPHY_CIRCUIT_BREAKER_TIMEOUT', 60),
];
