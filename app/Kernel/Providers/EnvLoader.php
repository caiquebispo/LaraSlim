<?php

namespace LaraSlim\Kernel\Providers;

use Dotenv\Dotenv;

/**
 * Environment Loader
 * 
 * Handles loading and validation of environment variables using vlucas/phpdotenv.
 * Supports environment-specific files (.env.testing, .env.production, etc.)
 */
class EnvLoader
{
    /**
     * Required environment variables that must be defined
     */
    private static array $required = [
        'DB_CONNECTION',
    ];

    /**
     * Required variables per environment
     */
    private static array $requiredByEnvironment = [
        'production' => [
            'DB_HOST',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
        ],
        'staging' => [
            'DB_HOST',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
        ],
    ];

    /**
     * Default values for optional variables
     */
    private static array $defaults = [
        'APP_NAME' => 'LaraSlim',
        'APP_ENV' => 'local',
        'APP_DEBUG' => 'true',
        'APP_URL' => 'http://localhost',
        'APP_TIMEZONE' => 'UTC',
        'APP_LOCALE' => 'en',
        'LOG_CHANNEL' => 'file',
        'LOG_LEVEL' => 'debug',
        'CACHE_DRIVER' => 'file',
        'CORS_ALLOWED_ORIGINS' => '*',
        'CORS_ALLOWED_METHODS' => 'GET,POST,PUT,PATCH,DELETE,OPTIONS',
        'CORS_ALLOWED_HEADERS' => 'Content-Type,Authorization,X-Requested-With',
        'CORS_MAX_AGE' => '0',
        'CORS_SUPPORTS_CREDENTIALS' => 'false',
        'SECURITY_HEADERS_ENABLED' => 'true',
    ];

    /**
     * Load environment variables from .env files
     * 
     * @param string $path Base path where .env files are located
     * @throws \RuntimeException If required variables are missing
     */
    public static function load(string $path): void
    {
        // Determine which .env files to load
        $envFiles = self::getEnvFiles($path);

        if (empty($envFiles)) {
            // If no .env files exist, apply defaults and return
            self::applyDefaults();
            return;
        }

        // Load environment files using Dotenv
        $dotenv = Dotenv::createImmutable($path, $envFiles);
        
        try {
            $dotenv->safeLoad();
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to load environment file: " . $e->getMessage());
        }

        // Apply default values for unset variables
        self::applyDefaults();

        // Validate required variables
        self::validateRequired($dotenv);
    }

    /**
     * Get the list of .env files to load based on environment
     * 
     * @param string $path Base path
     * @return array List of .env filenames
     */
    private static function getEnvFiles(string $path): array
    {
        $files = [];
        $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: null;

        // Check for environment-specific file first
        if ($env && file_exists($path . '/.env.' . $env)) {
            $files[] = '.env.' . $env;
        }

        // Always try to load the base .env file
        if (file_exists($path . '/.env')) {
            $files[] = '.env';
        }

        return $files;
    }

    /**
     * Apply default values for optional environment variables
     */
    private static function applyDefaults(): void
    {
        foreach (self::$defaults as $key => $value) {
            if (!isset($_ENV[$key]) && getenv($key) === false) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    /**
     * Validate that required environment variables are set
     * 
     * @param Dotenv $dotenv
     * @throws \RuntimeException If validation fails
     */
    private static function validateRequired(Dotenv $dotenv): void
    {
        // Validate base required variables
        try {
            $dotenv->required(self::$required);
        } catch (\Exception $e) {
            throw new \RuntimeException("Missing required environment variable: " . $e->getMessage());
        }

        // Validate environment-specific required variables
        $env = env('APP_ENV', 'local');
        
        if (isset(self::$requiredByEnvironment[$env])) {
            try {
                $dotenv->required(self::$requiredByEnvironment[$env]);
            } catch (\Exception $e) {
                throw new \RuntimeException(
                    "Missing required environment variable for '{$env}' environment: " . $e->getMessage()
                );
            }
        }

        // Validate DB_CONNECTION is a valid option
        try {
            $dotenv->required('DB_CONNECTION')->allowedValues(['mysql', 'sqlite', 'pgsql']);
        } catch (\Exception $e) {
            // Variable might be set but with invalid value
            $connection = env('DB_CONNECTION');
            if (!in_array($connection, ['mysql', 'sqlite', 'pgsql'])) {
                throw new \RuntimeException(
                    "Invalid DB_CONNECTION value '{$connection}'. Allowed values: mysql, sqlite, pgsql"
                );
            }
        }
    }

    /**
     * Check if the application is in a specific environment
     * 
     * @param string $env Environment name
     * @return bool
     */
    public static function isEnvironment(string $env): bool
    {
        return strtolower(env('APP_ENV', 'local')) === strtolower($env);
    }

    /**
     * Check if running in production
     */
    public static function isProduction(): bool
    {
        return self::isEnvironment('production');
    }

    /**
     * Check if running in development/local
     */
    public static function isLocal(): bool
    {
        return self::isEnvironment('local');
    }

    /**
     * Check if running in testing
     */
    public static function isTesting(): bool
    {
        return self::isEnvironment('testing');
    }

    /**
     * Check if debug mode is enabled
     */
    public static function isDebug(): bool
    {
        return filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
    }
}