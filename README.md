
<p align="center">
  <img src="https://i.postimg.cc/qq23mpW5/Chat-GPT-Image-14-de-abr-de-2025-08-09-53.png" alt="LaraSlim Logo" width="500px" />
</p>

<p align="center">
  <a href="https://packagist.org/packages/caiquebispo/laraslim"><img src="http://poser.pugx.org/caiquebispo/laraslim/v" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/caiquebispo/laraslim"><img src="http://poser.pugx.org/caiquebispo/laraslim/downloads" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/caiquebispo/laraslim"><img src="http://poser.pugx.org/caiquebispo/laraslim/v/unstable" alt="Latest Unstable Version"></a>
  <a href="https://packagist.org/packages/caiquebispo/laraslim"><img src="http://poser.pugx.org/caiquebispo/laraslim/license" alt="License"></a>
  <a href="https://packagist.org/packages/caiquebispo/laraslim"><img src="http://poser.pugx.org/caiquebispo/laraslim/require/php" alt="PHP Version Require"></a>
</p>

## Description

**LaraSlim** is a microframework for PHP that combines the lightness of the [Slim Framework](https://www.slimframework.com/) with a structure inspired by [Laravel](https://laravel.com/). It's ideal for creating clean, modular APIs with a lightweight and fast setup.

> **Note:** This project **does not include an authentication engine** by default. You can integrate your own or use third-party libraries.

It also doesn't include a template engine, but you can integrate it with Blade (Laravel) or any other engine of your choice.

> ⚠️ This is a **beta** project: some features may be under development and bugs may occur.

---

## Features

- 🚀 **Lightweight & Fast** - Built on Slim Framework 4
- 🔒 **Security First** - CORS and Security Headers middlewares included
- 📝 **Centralized Error Handling** - PSR-3 logging, JSON error responses
- 🐳 **Docker Ready** - Multi-stage builds for dev and production
- ✅ **Testing Infrastructure** - Pest PHP with HTTP testing helpers
- 🔧 **Environment Validation** - Automatic validation of required variables

---

## Requirements

- PHP ^8.4
- Docker (optional)
- Composer

---

## Installation via Composer

```bash
composer create-project caiquebispo/laraslim example_app
```

---

## Quick Start

### Using PHP Built-in Server

```bash
cd example_app
cp .env.example .env
composer install
php -S localhost:8008 -t public
```

### Using Docker

```bash
# Development
docker compose up

# Production
docker compose --profile production up
```

---

## Docker Commands

| Command | Description |
|---------|-------------|
| `./bin/build` | Build Docker containers |
| `./bin/up` | Start Docker containers |
| `./bin/attach` | Attach to container |
| `./bin/down` | Stop Docker containers |

---

## Application Access

- **API**: `http://localhost:8003` (Docker) or `http://localhost:8008` (PHP server)
- **PHPMyAdmin**: `http://localhost:8080` (User: `root`, Password: `root`)

---

## Project Structure

```
app/
├── Contracts/          # Interfaces and contracts
├── Http/
│   ├── Controllers/    # Application controllers
│   ├── Middleware/     # CORS, Security Headers
│   └── Request/        # Form validation
├── Kernel/
│   ├── Exceptions/     # HttpException, ValidationException, Handler
│   └── Providers/      # Service providers (Logging, Database, etc.)
├── Models/             # Eloquent models
├── DTOs/               # Data Transfer Objects
└── Services/           # Business logic
config/
├── app.php             # Application config
├── cors.php            # CORS configuration
└── environments.php    # Environment validation
tests/
├── Feature/            # Feature tests
├── Unit/               # Unit tests
└── Traits/             # Test helpers (DatabaseMigrations, WithFaker)
```

---

## Environment Configuration

Copy `.env.example` to `.env` and configure:

```dotenv
# Application
APP_ENV=local
APP_DEBUG=true

# Database (MySQL)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=LaraSlim_db
DB_USERNAME=root
DB_PASSWORD=root

# Or SQLite
DB_CONNECTION=sqlite
DB_DATABASE=../storage/database.sqlite
```

---

## Artisan Commands

```bash
php artisan-slim make:migration users
php artisan-slim make:model User
php artisan-slim make:controller UserController
php artisan-slim make:request UserRequest
php artisan-slim          # List all commands
```

---

## Response Helpers

```php
use LaraSlim\Kernel\Providers\Response;

// Success response
return response()->success($data, 'Created', 201);

// Error response
return response()->error('Not found', 404);

// Validation error
return response()->validationError($errors);

// Paginated response
return response()->paginate($items, $total, $page, $perPage);
```

---

## Exception Handling

```php
use LaraSlim\Kernel\Exceptions\HttpException;

// Throw HTTP exception
throw HttpException::notFound('User not found');
throw HttpException::unauthorized();

// Or use helpers
abort(404, 'Resource not found');
abort_if($condition, 403, 'Forbidden');
```

---

## Helper Functions

| Function | Description |
|----------|-------------|
| `env($key, $default)` | Get environment variable |
| `config($key, $default)` | Get config value (dot notation) |
| `response()` | Get Response helper instance |
| `abort($code, $message)` | Throw HTTP exception |
| `logger()` | Get PSR-3 logger |
| `info($message)` | Log info message |
| `base_path($path)` | Get base path |
| `storage_path($path)` | Get storage path |

---

## Security

LaraSlim includes built-in security middlewares:

- **CORS** - Configurable via `config/cors.php` or environment variables
- **Security Headers** - X-Frame-Options, X-Content-Type-Options, HSTS (production)

---

## Testing

```bash
# Run all tests
composer test

# Or just Pest
vendor/bin/pest

# With coverage
vendor/bin/pest --coverage
```

---

## Examples

### Route Group

```php
use Slim\Routing\RouteCollectorProxy;

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/users', 'UserController:index');
    $group->post('/users', 'UserController:store');
});
```

### Controller with Response Helper

```php
<?php

namespace LaraSlim\Http\Controllers;

use LaraSlim\Services\UserServices;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController
{
    public function __construct(private UserServices $userServices) {}

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $user = $this->userServices->store($data);

        return response($response)->created($user, 'User created successfully');
    }
}
```

---

## Author

- **Caique Bispo**  
  📧 caiquebispodanet86@gmail.com

---

## License

This project is licensed under the terms of the [MIT License](LICENSE).
