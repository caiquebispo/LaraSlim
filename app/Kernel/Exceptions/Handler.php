<?php

namespace LaraSlim\Kernel\Exceptions;

use LaraSlim\Kernel\Providers\EnvLoader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Psr7\Response;
use Throwable;

/**
 * Exception Handler
 * 
 * Centralized exception handling with JSON responses and logging.
 */
class Handler
{
    private ?LoggerInterface $logger;
    private bool $debug;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->debug = EnvLoader::isDebug();
    }

    /**
     * Handle an exception and return a response
     */
    public function handle(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails = false
    ): ResponseInterface {
        // Log the exception
        $this->logException($exception, $request);

        // Convert to HttpException if possible
        $httpException = $this->convertToHttpException($exception);

        // Create response
        $response = new Response();

        // Set status code
        $statusCode = $httpException->getStatusCode();
        $response = $response->withStatus($statusCode);

        // Set headers
        foreach ($httpException->getHeaders() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        // Build response body
        $body = $this->buildResponseBody($httpException, $exception);

        // Write JSON response
        $response->getBody()->write(json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Convert any exception to HttpException
     */
    private function convertToHttpException(Throwable $exception): HttpException
    {
        // Already an HttpException
        if ($exception instanceof HttpException) {
            return $exception;
        }

        // Slim's HttpNotFoundException
        if ($exception instanceof HttpNotFoundException) {
            return HttpException::notFound('The requested resource was not found');
        }

        // Slim's HttpMethodNotAllowedException
        if ($exception instanceof HttpMethodNotAllowedException) {
            return new HttpException(405, 'Method not allowed');
        }

        // Default to 500
        return new HttpException(
            500,
            $this->debug ? $exception->getMessage() : 'Internal server error'
        );
    }

    /**
     * Build the response body array
     */
    private function buildResponseBody(HttpException $httpException, Throwable $originalException): array
    {
        $body = $httpException->toArray();

        // Add debug information in development
        if ($this->debug) {
            $body['debug'] = [
                'exception' => get_class($originalException),
                'file' => $originalException->getFile(),
                'line' => $originalException->getLine(),
                'trace' => $this->getCleanTrace($originalException),
            ];
        }

        return $body;
    }

    /**
     * Get a clean stack trace
     */
    private function getCleanTrace(Throwable $exception): array
    {
        $trace = [];
        
        foreach ($exception->getTrace() as $frame) {
            $entry = [];
            
            if (isset($frame['file'])) {
                $entry['file'] = $frame['file'];
            }
            if (isset($frame['line'])) {
                $entry['line'] = $frame['line'];
            }
            if (isset($frame['function'])) {
                $entry['function'] = $frame['function'];
            }
            if (isset($frame['class'])) {
                $entry['class'] = $frame['class'];
            }
            
            $trace[] = $entry;
        }

        // Limit trace depth
        return array_slice($trace, 0, 10);
    }

    /**
     * Log the exception
     */
    private function logException(Throwable $exception, ServerRequestInterface $request): void
    {
        if (!$this->logger) {
            return;
        }

        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'request' => [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
                'ip' => $this->getClientIp($request),
            ],
        ];

        // Determine log level based on exception type
        if ($exception instanceof ValidationException) {
            $this->logger->info('Validation failed', $context);
        } elseif ($exception instanceof HttpException && $exception->getStatusCode() < 500) {
            $this->logger->warning('HTTP error', $context);
        } else {
            $this->logger->error('Exception occurred', $context);
        }
    }

    /**
     * Get client IP from request
     */
    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        if (!empty($serverParams['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $serverParams['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        if (!empty($serverParams['HTTP_X_REAL_IP'])) {
            return $serverParams['HTTP_X_REAL_IP'];
        }

        return $serverParams['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
