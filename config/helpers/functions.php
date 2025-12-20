<?php

use LaraSlim\Kernel\Exceptions\HttpException;
use LaraSlim\Kernel\Providers\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Get the application container or a service from it
 */
if (!function_exists('app')) {
    function app(?string $abstract = null): mixed
    {
        global $container;

        if ($abstract) {
            return $container->get($abstract);
        }

        return $container;
    }
}

/**
 * Get an environment variable
 */
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        // Handle boolean strings
        switch (strtolower((string) $value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}

/**
 * Get a configuration value using dot notation
 */
if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $config = null;

        if ($config === null) {
            $configPath = dirname(__DIR__) . '/config/app.php';
            if (file_exists($configPath)) {
                $config = require $configPath;
            } else {
                $config = [];
            }
        }

        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

/**
 * Get the base path of the application
 */
if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $basePath = dirname(__DIR__);
        return $basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }
}

/**
 * Get the app path
 */
if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

/**
 * Get the storage path
 */
if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

/**
 * Get the config path
 */
if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

/**
 * Get the database path
 */
if (!function_exists('database_path')) {
    function database_path(string $path = ''): string
    {
        return base_path('database' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

/**
 * Get the resources path
 */
if (!function_exists('resource_path')) {
    function resource_path(string $path = ''): string
    {
        return base_path('resources' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

/**
 * Create a response helper
 */
if (!function_exists('response')) {
    function response(?ResponseInterface $response = null): Response
    {
        return new Response($response);
    }
}

/**
 * Abort with an HTTP exception
 */
if (!function_exists('abort')) {
    /**
     * @throws HttpException
     */
    function abort(int $code, string $message = ''): never
    {
        throw new HttpException($code, $message);
    }
}

/**
 * Abort if condition is true
 */
if (!function_exists('abort_if')) {
    /**
     * @throws HttpException
     */
    function abort_if(bool $condition, int $code, string $message = ''): void
    {
        if ($condition) {
            abort($code, $message);
        }
    }
}

/**
 * Abort unless condition is true
 */
if (!function_exists('abort_unless')) {
    /**
     * @throws HttpException
     */
    function abort_unless(bool $condition, int $code, string $message = ''): void
    {
        if (!$condition) {
            abort($code, $message);
        }
    }
}

/**
 * Log a message
 */
if (!function_exists('logger')) {
    function logger(): ?LoggerInterface
    {
        try {
            return app(LoggerInterface::class);
        } catch (\Exception $e) {
            return null;
        }
    }
}

/**
 * Log an info message
 */
if (!function_exists('info')) {
    function info(string $message, array $context = []): void
    {
        $logger = logger();
        if ($logger) {
            $logger->info($message, $context);
        }
    }
}

/**
 * Render a view
 */
if (!function_exists('view')) {
    function view(string $view, array $data = []): ResponseInterface
    {
        $blade = app('blade');
        return $blade->render($view, $data);
    }
}

/**
 * Dump and die - for debugging only
 */
if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        exit(1);
    }
}
