<?php

namespace Garcia;

class Router
{
    /**
     * @var array - Array of routes
     */
    private array $routes = [];

    /**
     * Sets a new route.
     *
     * @param string $method - HTTP method
     * @param string $path - URL path
     * @param callable $handler - Route handler
     * @return void
     */
    public function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    /**
     * Handles the request by matching the route and calling the handler.
     *
     * @param string $method - HTTP method
     * @param string $uri - URI path
     * @return void
     */
    public function handleRequest(string $method, string $uri): void
    {
        $found = false;
        $params = [];
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $uri, $params)) {
                $this->callHandler($route['handler'], $params);
                $found = true;
            }
        }

        if (!$found) {
            // If no route matches, handle 404
            $this->handleNotFound();
        }
    }

    /**
     * Matches the route path with the request URI.
     *
     * @param string $routePath - Route path
     * @param string $uri - Request URI
     * @param array $params - Route parameters
     * @return bool
     */
    private function matchPath(string $routePath, string $uri, array &$params): bool
    {
        $routePathSegments = explode('/', trim($routePath, '/'));
        $uriSegments = explode('/', trim($uri, '/'));

        if (count($routePathSegments) !== count($uriSegments)) {
            return false;
        }

        $params = [];
        foreach ($routePathSegments as $key => $segment) {
            if (strpos($segment, ':') === 0) {
                // This is a parameter
                $params[substr($segment, 1)] = $uriSegments[$key];
            } elseif ($segment !== $uriSegments[$key]) {
                // Non-matching segment
                return false;
            }
        }

        return true;
    }

    /**
     * Calls the route handler.
     *
     * @param callable $handler - Route handler
     * @param array $params - Route parameters
     * @return void
     */
    private function callHandler(callable $handler, array $params): void
    {
        // Assuming handlers are callable, you might need to adjust based on your use case
        if (is_callable($handler)) {
            // Capture the output of the handler function
            ob_start();
            echo json_encode(call_user_func($handler, $params));
            $output = json_decode(ob_get_clean());

            // Check if the output is an array, and convert it to JSON
            if (is_array($output) || is_object($output)) {
                header('Content-Type: application/json');
                echo json_encode($output);
            } else {
                // If not an array or object, simply echo the output
                echo json_decode($output);
            }
        } else {
            // Handle error: invalid handler
            echo "Error: Invalid handler!";
        }
    }

    /**
     * Handles 404 Not Found.
     *
     * @return void
     */
    private function handleNotFound(): void
    {
        header('HTTP/1.0 404 Not Found');
        echo '404 Not Found';
    }
}
