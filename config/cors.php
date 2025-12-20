<?php

/**
 * CORS Configuration
 * 
 * Configure Cross-Origin Resource Sharing settings.
 * These can also be set via environment variables.
 */

return [
    /**
     * Allowed origins
     * Use '*' to allow all origins, or list specific domains
     */
    'allowed_origins' => env('CORS_ALLOWED_ORIGINS', '*'),

    /**
     * Allowed HTTP methods
     */
    'allowed_methods' => [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ],

    /**
     * Allowed request headers
     */
    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
        'Origin',
    ],

    /**
     * Headers exposed to the client
     */
    'exposed_headers' => [],

    /**
     * Preflight max age in seconds
     * 0 = no caching
     */
    'max_age' => 0,

    /**
     * Whether to support credentials (cookies, authorization headers)
     * Note: Cannot be used with 'allowed_origins' => '*'
     */
    'supports_credentials' => false,
];
