<?php

namespace LaraSlim\Kernel;

use BladeSlim\Blade;
use LaraSlim\Kernel\Exceptions\Handler;
use LaraSlim\Kernel\Providers\AppServiceProvider;
use LaraSlim\Kernel\Providers\DatabaseServiceProvider;
use LaraSlim\Kernel\Providers\EnvLoader;
use LaraSlim\Kernel\Providers\LoggingServiceProvider;
use LaraSlim\Kernel\Providers\ValidationServiceProvider;
use Psr\Log\LoggerInterface;
use Slim\App;

class Kernel
{
    /**
     * Bootstrap the application
     */
    public function bootstrap(App $app): void
    {
        $this->loadEnvironment();
        $this->loadHelpers();
        $this->registerAppServiceProvider($app);
        $this->registerLoggingProvider($app);
        $this->registerDatabaseProvider($app);
        $this->registerValidationProvider($app);
        $this->registerErrorHandler($app);
        $this->registerMiddleware($app);
        $this->registerRoutes($app);
        $this->registerView($app);
    }

    /**
     * Load environment variables
     */
    protected function loadEnvironment(): void
    {
        EnvLoader::load(__DIR__ . '/../..');
    }

    /**
     * Load helper functions
     */
    protected function loadHelpers(): void
    {
        $helpersPath = __DIR__ . '/../../helpers.php';
        if (file_exists($helpersPath)) {
            require_once $helpersPath;
        }
    }

    /**
     * Register global middleware
     */
    protected function registerMiddleware(App $app): void
    {
        // Add custom middlewares
        $app->add(new \LaraSlim\Http\Middleware\CorsMiddleware());
        $app->add(new \LaraSlim\Http\Middleware\SecurityHeadersMiddleware());
        
        // Add Slim built-in middlewares
        $app->addRoutingMiddleware();
        $app->addBodyParsingMiddleware();
    }

    /**
     * Register application routes
     */
    protected function registerRoutes(App $app): void
    {
        $webRoutesPath = __DIR__ . '/../../routes/web.php';
        $apiRoutesPath = __DIR__ . '/../../routes/api.php';

        if (file_exists($webRoutesPath)) {
            require $webRoutesPath;
        } else {
            throw new \RuntimeException("Web routes file not found: {$webRoutesPath}");
        }

        if (file_exists($apiRoutesPath)) {
            require $apiRoutesPath;
        } else {
            throw new \RuntimeException("API routes file not found: {$apiRoutesPath}");
        }
    }

    /**
     * Register the Blade view engine
     */
    protected function registerView(App $app): void
    {
        $viewsPath = __DIR__ . '/../../resources/views';
        $cachePath = __DIR__ . '/../../storage/cache';

        if (!is_dir($viewsPath)) {
            throw new \RuntimeException("Views directory not found: {$viewsPath}");
        }

        // Create cache directory if it doesn't exist
        if (!is_dir($cachePath)) {
            if (!mkdir($cachePath, 0755, true)) {
                throw new \RuntimeException("Could not create cache directory: {$cachePath}");
            }
        }

        if (!is_writable($cachePath)) {
            throw new \RuntimeException("Cache directory is not writable: {$cachePath}");
        }

        $blade = new Blade(
            $viewsPath,
            $cachePath,
            $app->getResponseFactory()->createResponse()
        );

        $container = $app->getContainer();
        $container->set('blade', $blade);
    }

    /**
     * Register the App Service Provider
     */
    protected function registerAppServiceProvider(App $app): void
    {
        $provider = new AppServiceProvider();
        $provider->register($app->getContainer());
    }

    /**
     * Register the Database Service Provider
     */
    protected function registerDatabaseProvider(App $app): void
    {
        $provider = new DatabaseServiceProvider();
        $provider->register($app->getContainer());
    }

    /**
     * Register the Validation Service Provider
     */
    protected function registerValidationProvider(App $app): void
    {
        $provider = new ValidationServiceProvider();
        $provider->register($app->getContainer());
    }

    /**
     * Register the Logging Service Provider
     */
    protected function registerLoggingProvider(App $app): void
    {
        $provider = new LoggingServiceProvider();
        $provider->register($app->getContainer());
    }

    /**
     * Register the centralized error handler
     */
    protected function registerErrorHandler(App $app): void
    {
        $container = $app->getContainer();
        
        // Get logger if available
        $logger = null;
        if ($container->has(LoggerInterface::class)) {
            $logger = $container->get(LoggerInterface::class);
        }

        // Create the exception handler
        $handler = new Handler($logger);

        // Add error middleware with custom handler
        $errorMiddleware = $app->addErrorMiddleware(
            displayErrorDetails: EnvLoader::isDebug(),
            logErrors: true,
            logErrorDetails: true
        );

        // Set custom error handler
        $errorMiddleware->setDefaultErrorHandler(
            function ($request, $exception, $displayErrorDetails) use ($handler) {
                return $handler->handle($request, $exception, $displayErrorDetails);
            }
        );

        // Store handler in container for manual use
        $container->set(Handler::class, $handler);
        $container->set('exception.handler', $handler);
    }
}