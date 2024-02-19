<?php

use Garcia\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testAddRoute()
    {
        $router = new Router();
        $handler = function () {
            return 'Hello, World!';
        };

        $router->addRoute('GET', '/hello', $handler);
        $routes = $this->getObjectAttribute($router, 'routes');

        $this->assertCount(1, $routes);
        $this->assertEquals(['method' => 'GET', 'path' => '/hello', 'handler' => $handler], $routes[0]);
    }

    public function testHandleRequest()
    {
        $router = new Router();
        $handler = function () {
            return 'Hello, World!';
        };

        $router->addRoute('GET', '/hello', $handler);

        // Capture the output of handleRequest
        ob_start();
        $router->handleRequest('GET', '/hello');
        $output = ob_get_clean();

        $this->expectOutputString('Hello, World!');
        $this->assertEquals('Hello, World!', $output);
    }

    public function testMatchPath()
    {
        $router = new Router();
        $uri = '/users/123';

        $routePath = '/users/:id';
        $params = [];
        $result = $this->invokeMethod($router, 'matchPath', [$routePath, $uri, &$params]);

        $this->assertTrue($result);
        $this->assertEquals(['id' => '123'], $params);
    }

    public function testCallHandler()
    {
        $router = new Router();
        $handler = function ($params) {
            return ['user' => $params['id']];
        };

        // Capture the output of callHandler
        ob_start();
        $this->invokeMethod($router, 'callHandler', [$handler, ['id' => '123']]);
        $output = ob_get_clean();

        $this->expectOutputString('{"user":"123"}');
        $this->assertEquals('{"user":"123"}', $output);
    }

    public function testHandleNotFound()
    {
        $router = new Router();

        // Capture the output of handleNotFound
        ob_start();
        $this->invokeMethod($router, 'handleNotFound');
        $output = ob_get_clean();

        $this->expectOutputString('404 Not Found');
        $this->assertEquals('404 Not Found', $output);
    }

    // Helper method to invoke private/protected methods for testing
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}