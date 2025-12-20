<?php

/**
 * Environment Configuration
 * 
 * Defines required variables and defaults for different environments.
 */

return [
    /**
     * Variables required in all environments
     */
    'required' => [
        'DB_CONNECTION',
    ],

    /**
     * Variables required per environment
     */
    'required_by_environment' => [
        'production' => [
            'DB_HOST',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'APP_URL',
        ],
        'staging' => [
            'DB_HOST',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
        ],
    ],

    /**
     * Default values for optional variables
     * These will be applied if the variable is not set
     */
    'defaults' => [
        'APP_NAME' => 'LaraSlim',
        'APP_ENV' => 'local',
        'APP_DEBUG' => true,
        'APP_URL' => 'http://localhost',
        'APP_TIMEZONE' => 'UTC',
        'APP_LOCALE' => 'en',
        
        'LOG_CHANNEL' => 'file',
        'LOG_LEVEL' => 'debug',
        
        'CACHE_DRIVER' => 'file',
        
        'CORS_ALLOWED_ORIGINS' => '*',
        'CORS_ALLOWED_METHODS' => 'GET,POST,PUT,PATCH,DELETE,OPTIONS',
        'CORS_ALLOWED_HEADERS' => 'Content-Type,Authorization,X-Requested-With',
        'CORS_EXPOSED_HEADERS' => '',
        'CORS_MAX_AGE' => 0,
        'CORS_SUPPORTS_CREDENTIALS' => false,
        
        'SECURITY_HEADERS_ENABLED' => true,
    ],

    /**
     * Allowed values for specific variables
     */
    'allowed_values' => [
        'DB_CONNECTION' => ['mysql', 'sqlite', 'pgsql'],
        'APP_ENV' => ['local', 'testing', 'staging', 'production'],
        'LOG_CHANNEL' => ['file', 'stderr', 'null'],
        'LOG_LEVEL' => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
        'CACHE_DRIVER' => ['file', 'array'],
    ],
];
