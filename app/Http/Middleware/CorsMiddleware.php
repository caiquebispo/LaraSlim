<?php

namespace LaraSlim\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CORS Middleware
 * 
 * Handles Cross-Origin Resource Sharing (CORS) headers.
 * Configurable via environment variables.
 */
class CorsMiddleware implements MiddlewareInterface
{
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? $this->getDefaultConfig();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Slim\Psr7\Response();
            return $this->addCorsHeaders($response, $request);
        }

        // Handle actual request
        $response = $handler->handle($request);
        return $this->addCorsHeaders($response, $request);
    }

    /**
     * Add CORS headers to response
     */
    private function addCorsHeaders(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $origin = $this->getOrigin($request);
        
        if ($origin && $this->isAllowedOrigin($origin)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
            
            if ($this->config['supports_credentials']) {
                $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            }
        } elseif ($this->config['allowed_origins'] === ['*']) {
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        }

        // Add allowed methods
        $response = $response->withHeader(
            'Access-Control-Allow-Methods',
            implode(', ', $this->config['allowed_methods'])
        );

        // Add allowed headers
        $response = $response->withHeader(
            'Access-Control-Allow-Headers',
            implode(', ', $this->config['allowed_headers'])
        );

        // Add exposed headers
        if (!empty($this->config['exposed_headers'])) {
            $response = $response->withHeader(
                'Access-Control-Expose-Headers',
                implode(', ', $this->config['exposed_headers'])
            );
        }

        // Add max age for preflight caching
        if ($this->config['max_age'] > 0) {
            $response = $response->withHeader(
                'Access-Control-Max-Age',
                (string) $this->config['max_age']
            );
        }

        return $response;
    }

    /**
     * Get request origin
     */
    private function getOrigin(ServerRequestInterface $request): ?string
    {
        $origin = $request->getHeaderLine('Origin');
        return $origin ?: null;
    }

    /**
     * Check if origin is allowed
     */
    private function isAllowedOrigin(string $origin): bool
    {
        // Allow all origins
        if (in_array('*', $this->config['allowed_origins'])) {
            return true;
        }

        // Check specific origins
        return in_array($origin, $this->config['allowed_origins']);
    }

    /**
     * Get default configuration from environment
     */
    private function getDefaultConfig(): array
    {
        $allowedOrigins = env('CORS_ALLOWED_ORIGINS', '*');
        $allowedMethods = env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,PATCH,DELETE,OPTIONS');
        $allowedHeaders = env('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization,X-Requested-With');
        $exposedHeaders = env('CORS_EXPOSED_HEADERS', '');

        return [
            'allowed_origins' => $this->parseCommaSeparated($allowedOrigins),
            'allowed_methods' => $this->parseCommaSeparated($allowedMethods),
            'allowed_headers' => $this->parseCommaSeparated($allowedHeaders),
            'exposed_headers' => $this->parseCommaSeparated($exposedHeaders),
            'max_age' => (int) env('CORS_MAX_AGE', 0),
            'supports_credentials' => filter_var(
                env('CORS_SUPPORTS_CREDENTIALS', false),
                FILTER_VALIDATE_BOOLEAN
            ),
        ];
    }

    /**
     * Parse comma-separated string to array
     */
    private function parseCommaSeparated(string $value): array
    {
        if (empty($value)) {
            return [];
        }

        return array_map('trim', explode(',', $value));
    }
}
