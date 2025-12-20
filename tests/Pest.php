<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)->in('Feature');
pest()->extend(Tests\TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeSuccessful', function () {
    $status = $this->value->getStatusCode();
    return expect($status)->toBeGreaterThanOrEqual(200)->toBeLessThan(300);
});

expect()->extend('toBeJson', function () {
    $contentType = $this->value->getHeaderLine('Content-Type');
    return expect($contentType)->toContain('application/json');
});

expect()->extend('toHaveStatus', function (int $status) {
    return expect($this->value->getStatusCode())->toBe($status);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Get the response body as JSON
 */
function getJson(\Psr\Http\Message\ResponseInterface $response): array
{
    $response->getBody()->rewind();
    return json_decode($response->getBody()->getContents(), true) ?? [];
}

/**
 * Generate a random email for testing
 */
function fakeEmail(): string
{
    return substr(bin2hex(random_bytes(8)), 0, 8) . '@example.com';
}

/**
 * Generate a random string for testing
 */
function fakeString(int $length = 10): string
{
    return substr(bin2hex(random_bytes($length)), 0, $length);
}
