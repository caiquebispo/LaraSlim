<?php

namespace LaraSlim\Kernel\Providers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response as SlimResponse;

/**
 * Response Helper
 * 
 * Provides standardized JSON response methods.
 */
class Response
{
    private ResponseInterface $response;

    public function __construct(?ResponseInterface $response = null)
    {
        $this->response = $response ?? new SlimResponse();
    }

    /**
     * Create a JSON response
     */
    public function json(mixed $data, int $status = 200, array $headers = []): ResponseInterface
    {
        $response = $this->response;
        
        // Set status
        $response = $response->withStatus($status);
        
        // Set content type
        $response = $response->withHeader('Content-Type', 'application/json');
        
        // Set custom headers
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        
        // Write body
        $response->getBody()->write(
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        return $response;
    }

    /**
     * Create a success response
     */
    public function success(mixed $data = null, string $message = 'Success', int $status = 200): ResponseInterface
    {
        $payload = [
            'status' => 'success',
            'message' => $message,
        ];
        
        if ($data !== null) {
            $payload['data'] = $data;
        }
        
        return $this->json($payload, $status);
    }

    /**
     * Create an error response
     */
    public function error(string $message = 'Error', int $status = 400, array $errors = []): ResponseInterface
    {
        $payload = [
            'status' => 'error',
            'message' => $message,
        ];
        
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }
        
        return $this->json($payload, $status);
    }

    /**
     * Create a validation error response
     */
    public function validationError(array $errors, string $message = 'Validation failed'): ResponseInterface
    {
        return $this->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    /**
     * Create a not found response
     */
    public function notFound(string $message = 'Resource not found'): ResponseInterface
    {
        return $this->error($message, 404);
    }

    /**
     * Create an unauthorized response
     */
    public function unauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return $this->error($message, 401);
    }

    /**
     * Create a forbidden response
     */
    public function forbidden(string $message = 'Forbidden'): ResponseInterface
    {
        return $this->error($message, 403);
    }

    /**
     * Create a server error response
     */
    public function serverError(string $message = 'Internal server error'): ResponseInterface
    {
        return $this->error($message, 500);
    }

    /**
     * Create a created response
     */
    public function created(mixed $data = null, string $message = 'Created successfully'): ResponseInterface
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Create a no content response
     */
    public function noContent(): ResponseInterface
    {
        return $this->response->withStatus(204);
    }

    /**
     * Create a paginated response
     */
    public function paginate(
        array $items,
        int $total,
        int $page = 1,
        int $perPage = 15,
        string $message = 'Success'
    ): ResponseInterface {
        $lastPage = max(1, (int) ceil($total / $perPage));
        
        return $this->json([
            'status' => 'success',
            'message' => $message,
            'data' => $items,
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total),
            ],
        ]);
    }
}
