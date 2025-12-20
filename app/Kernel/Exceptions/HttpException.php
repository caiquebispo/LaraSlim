<?php

namespace LaraSlim\Kernel\Exceptions;

use Exception;

/**
 * HTTP Exception
 * 
 * Base exception for HTTP errors with status codes.
 */
class HttpException extends Exception
{
    protected int $statusCode;
    protected array $headers;
    protected array $errors;

    public function __construct(
        int $statusCode = 500,
        string $message = '',
        array $errors = [],
        array $headers = [],
        ?Exception $previous = null
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->errors = $errors;

        if (empty($message)) {
            $message = $this->getDefaultMessage($statusCode);
        }

        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the JSON-serializable representation
     */
    public function toArray(): array
    {
        $data = [
            'status' => 'error',
            'code' => $this->statusCode,
            'message' => $this->getMessage(),
        ];

        if (!empty($this->errors)) {
            $data['errors'] = $this->errors;
        }

        return $data;
    }

    /**
     * Get default message for common HTTP status codes
     */
    protected function getDefaultMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            default => 'Error',
        };
    }

    /**
     * Static factory methods for common errors
     */
    public static function notFound(string $message = 'Resource not found'): self
    {
        return new self(404, $message);
    }

    public static function badRequest(string $message = 'Bad request', array $errors = []): self
    {
        return new self(400, $message, $errors);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self(401, $message);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self(403, $message);
    }

    public static function serverError(string $message = 'Internal server error'): self
    {
        return new self(500, $message);
    }
}
