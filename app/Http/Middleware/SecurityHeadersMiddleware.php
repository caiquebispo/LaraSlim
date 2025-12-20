<?php

namespace LaraSlim\Http\Middleware;

use LaraSlim\Kernel\Providers\EnvLoader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Security Headers Middleware
 * 
 * Adds security-related HTTP headers to responses.
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    private bool $enabled;

    public function __construct()
    {
        $this->enabled = filter_var(
            env('SECURITY_HEADERS_ENABLED', true),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$this->enabled) {
            return $response;
        }

        // Prevent MIME-type sniffing
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking
        $response = $response->withHeader('X-Frame-Options', 'DENY');

        // Enable XSS filter in browsers
        $response = $response->withHeader('X-XSS-Protection', '1; mode=block');

        // Remove server version information
        $response = $response->withoutHeader('X-Powered-By');

        // Referrer policy
        $response = $response->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy (basic)
        $response = $response->withHeader(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';"
        );

        // Add HSTS in production
        if (EnvLoader::isProduction()) {
            $response = $response->withHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // Permissions Policy (formerly Feature-Policy)
        $response = $response->withHeader(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=()'
        );

        return $response;
    }
}
