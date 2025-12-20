<?php

namespace Tests;

use DI\Container;
use LaraSlim\Kernel\Kernel;
use LaraSlim\Kernel\Providers\EnvLoader;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Base Test Case
 * 
 * Provides a foundation for all tests with app bootstrapping,
 * database setup, and HTTP testing helpers.
 */
abstract class TestCase extends BaseTestCase
{
    protected ?App $app = null;
    protected ?Container $container = null;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set testing environment
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'true';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        
        $this->createApplication();
    }

    /**
     * Clean up after tests
     */
    protected function tearDown(): void
    {
        $this->app = null;
        $this->container = null;
        
        parent::tearDown();
    }

    /**
     * Create the application instance
     */
    protected function createApplication(): App
    {
        $this->container = new Container();
        AppFactory::setContainer($this->container);
        $this->app = AppFactory::create();

        $kernel = new Kernel();
        $kernel->bootstrap($this->app);

        return $this->app;
    }

    /**
     * Get the application instance
     */
    protected function getApp(): App
    {
        if (!$this->app) {
            $this->createApplication();
        }
        
        return $this->app;
    }

    /**
     * Get the container instance
     */
    protected function getContainer(): Container
    {
        if (!$this->container) {
            $this->createApplication();
        }
        
        return $this->container;
    }

    /**
     * Create a server request for testing
     */
    protected function createRequest(
        string $method,
        string $uri,
        array $data = [],
        array $headers = []
    ): ServerRequestInterface {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest($method, $uri);

        // Add headers
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // Add body data for POST/PUT/PATCH
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $request = $request->withParsedBody($data);
            $request = $request->withHeader('Content-Type', 'application/json');
        }

        return $request;
    }

    /**
     * Make a GET request
     */
    protected function get(string $uri, array $headers = []): ResponseInterface
    {
        return $this->request('GET', $uri, [], $headers);
    }

    /**
     * Make a POST request
     */
    protected function post(string $uri, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->request('POST', $uri, $data, $headers);
    }

    /**
     * Make a PUT request
     */
    protected function put(string $uri, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->request('PUT', $uri, $data, $headers);
    }

    /**
     * Make a PATCH request
     */
    protected function patch(string $uri, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->request('PATCH', $uri, $data, $headers);
    }

    /**
     * Make a DELETE request
     */
    protected function delete(string $uri, array $headers = []): ResponseInterface
    {
        return $this->request('DELETE', $uri, [], $headers);
    }

    /**
     * Make an HTTP request
     */
    protected function request(
        string $method,
        string $uri,
        array $data = [],
        array $headers = []
    ): ResponseInterface {
        $request = $this->createRequest($method, $uri, $data, $headers);
        return $this->getApp()->handle($request);
    }

    /**
     * Get response body as string
     */
    protected function getResponseBody(ResponseInterface $response): string
    {
        $response->getBody()->rewind();
        return $response->getBody()->getContents();
    }

    /**
     * Get response body as JSON array
     */
    protected function getJsonResponse(ResponseInterface $response): array
    {
        return json_decode($this->getResponseBody($response), true) ?? [];
    }

    /**
     * Assert that the response has a specific status code
     */
    protected function assertResponseStatus(ResponseInterface $response, int $status): void
    {
        $this->assertEquals($status, $response->getStatusCode());
    }

    /**
     * Assert that the response is JSON
     */
    protected function assertJsonResponse(ResponseInterface $response): void
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $this->assertStringContainsString('application/json', $contentType);
    }

    /**
     * Assert that the response JSON contains a key
     */
    protected function assertJsonHas(ResponseInterface $response, string $key): void
    {
        $json = $this->getJsonResponse($response);
        $this->assertArrayHasKey($key, $json);
    }

    /**
     * Assert that the response JSON has a specific value
     */
    protected function assertJsonValue(ResponseInterface $response, string $key, mixed $value): void
    {
        $json = $this->getJsonResponse($response);
        $this->assertEquals($value, $json[$key] ?? null);
    }
}
