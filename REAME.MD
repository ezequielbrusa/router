# Valkyrae Router
A simple router for PHP, inspired by Express.js and Laravel.

## Description
This is a simple router. It is a simple router that uses the `window.location` object to determine the current route and render the appropriate component.

## Installation
```bash
composer require valkyrae/router
```

## Usage
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use JoseGarcia\valkyrae\Router;

// Example usage
$router = new Router();

$router->addRoute('GET', '/health', function () {
    return 'Hello, world!';
});

$router->addRoute('GET', '/health/:id', function ($params) {
    $id = $params['id'];
    return "User ID: $id";
});

// Define a route that returns JSON
$router->addRoute('POST', '/api/health/:id', function ($params) {
    $userId = $params['id'];

    return ['id' => $userId, 'name' => 'John Doe', 'email' => 'john@example.com'];
});

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Handle the request
$router->handleRequest($method, $uri);
```

## License
MIT
```