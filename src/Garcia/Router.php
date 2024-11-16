<?php

namespace Garcia;

use Garcia\Exceptions\RouterException;

class Router
{
    /**
     * @var array - Array of routes
     */
    private static array $routes = [];

    /**
     * Sets a new route.
     *
     * @param string $method - HTTP method
     * @param string $path - URL path
     * @param callable $handler - Route handler
     * @return void
     */
    public static function addRoute(string $method, string $path, callable $handler)
    {
        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => []
        ];
    }

    public function middleware(callable $middleware)
    {
        $lastRouteIndex = count(self::$routes) - 1;
        if ($lastRouteIndex >= 0) {
            self::$routes[$lastRouteIndex]['middleware'][] = $middleware;
        }
        return $this;
    }
 
    private static function matchRoute($route, $uri, &$params)
    {
        $pattern = preg_replace('/:\w+/', '(\w+)', $route);
        $pattern = str_replace('/', '\/', $pattern);
        if (preg_match('/^' . $pattern . '$/', $uri, $matches)) {
            array_shift($matches); // Remove the full match
            $paramNames = [];
            preg_match_all('/:(\w+)/', $route, $paramNames);
            $params = array_combine($paramNames[1], $matches);
            return true;
        }
        return false;
    }

    // public static function run()
    // {
    //     $method = $_SERVER['REQUEST_METHOD'];
    //     $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    //     foreach (self::$routes as $route) {
    //         if ($method === $route['method'] && self::matchRoute($route['path'], $uri, $params)) {
    //             // Execute middlewares
    //             $handler = $route['handler'];
    //             $middlewares = $route['middleware'];
    //             $middlewareChain = function ($index) use (&$middlewareChain, $middlewares, $handler, $params) {
    //                 if ($index < count($middlewares)) {
    //                     return $middlewares[$index]($params, function ($params) use ($middlewareChain, $index) {
    //                         return $middlewareChain($index + 1);
    //                     });
    //                 }
    //                 // Execute final route handler after all middlewares
    //                 return $handler($params);
    //             };

    //             //echo $middlewareChain(0);
    //             return;
    //         }
    //     }
        

    //     // 404 Not Found
    //     http_response_code(404);
    //     echo json_encode(['error' => 'Route not found']);
    // }

   /**
    * This method sets multiple routes such a Get, Post, Patch, Put, Delete and sets the corresponding callbacks
    * Based on restful controllers.
    *
    * @param string $path - URL path 
    * @param string $className - This is the name of the class that we use instantiate callbacks
    */
    public static function resource(string $path, string $className)
    {
        self::addRoute('GET', $path, fn () => (new $className)->index());
        self::addRoute('POST', $path, fn ($params) => (new $className)->store($params));
        self::addRoute('GET', "$path/:id", fn ($params) => (new $className)->show($params));
        self::addRoute('PATCH', "$path/:id", fn ($params) => (new $className)->update($params));
        self::addRoute('PUT', "$path/:id", fn ($params) => (new $className)->update($params));
        self::addRoute('DELETE', "$path/:id", fn ($params) => (new $className)->destroy($params));
    }
     
    /**
     * Sets a new route for GET requests.
     *
     * @param string $path - URL path
     * @param callable $handler - Route handler
     * @return void
     */
    public static function get(string $path, callable $handler)
    {
        self::addRoute('GET', $path, $handler);
    }

    /**
     * Sets a new route for POST requests.
     *
     * @param string $path - URL path
     * @param callable $handler - Route handler
     * @return void
     */
    public static function post(string $path, callable $handler)
    {
        self::addRoute('POST', $path, $handler);
    }

    /**
     * Sets a new route for PUT requests.
     *
     * @param string $path - URL path
     * @param callable $handler - Route handler
     * @return void
     */
    public static function put(string $path, callable $handler)
    {
        self::addRoute('PUT', $path, $handler);
    }

    /**
     * Sets a new route for DELETE requests.
     *
     * @param string $path - URL path
     * @param callable $handler - Route handler
     * @return void
     */
    public static function delete(string $path, callable $handler)
    {
        self::addRoute('DELETE', $path, $handler);
    }

    /**
     * Sets a new route for PATCH requests.
     *
     * @param string $path - URL path
     * @param callable $handler - Route handler
     * @return void
     */
    public static function patch(string $path, callable $handler)
    {
        self::addRoute('PATCH', $path, $handler);
    }

    /**
     * Sets a new route for OPTIONS requests.
     *
     * @param string $path - URL path
     * @param callable $handler - Route handler
     * @return void
     */
    public static function options(string $path, callable $handler)
    {
        self::addRoute('OPTIONS', $path, $handler);
    }

    /**
     * Sets a new route for any HTTP method.
     *
     * @param string $path - URL path
     * @param callable $handler - Route handler
     * @return void
     */
    public static function any(string $path, callable $handler)
    {
        self::addRoute('GET', $path, $handler);
        self::addRoute('POST', $path, $handler);
        self::addRoute('PUT', $path, $handler);
        self::addRoute('DELETE', $path, $handler);
        self::addRoute('PATCH', $path, $handler);
        self::addRoute('OPTIONS', $path, $handler);
    }

    /**
     * Handles the request by matching the route and calling the handler.
     *
     * @param string $method - HTTP method
     * @param string $uri - URI path
     * @return void
     * @throws RouterException - Invalid handler
     */
    public static function handleRequest(string $method, string $uri)
    {
        $found = false;
        $params = [];
        foreach (self::$routes as $route) {
            if ($route['method'] === $method && self::matchPath($route['path'], $uri, $params)) {
                if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
                    // Capture the POST data
                    $json = file_get_contents('php://input');
                    $body = json_decode($json, true);
                    $array = !empty($body) ? $body : [];
                    $_REQUEST = [...$_REQUEST, ...$array];
                    $_POST = $_REQUEST;
                    $params = array_merge($params, $_POST);
                }
                self::callHandler($route['handler'], $params);
                $found = true;
            }
        }

        if (!$found) {
            // If no route matches, handle 404
            self::handleNotFound();
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
    private static function matchPath(string $routePath, string $uri, array &$params): bool
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
     * @throws RouterException - Invalid handler
     */
    private static function callHandler(callable $handler, array $params = [])
    {
        // Assuming handlers are callable, you might need to adjust based on your use case
        if (is_callable($handler)) {
            // Capture the output of the handler function
            ob_start();
            echo json_encode(call_user_func($handler, $params));
            $output = json_decode(ob_get_clean());
            http_response_code(200);
            if (is_array($output) || is_object($output)) {
                if (property_exists($output, 'view')) {
                    // If the output is a view, render the view
                    view($output->view, $output->data ?? [], $output->path);
                } else {
                    // If the output is an array or object, convert it to JSON and echo it
                    header('Content-Type: application/json');
                    echo json_encode($output);
                }
            } else {
                // If not an array or object, simply echo the output
                echo $output;
            }
        } else {
            // Handle error: invalid handler
            throw new RouterException('Invalid handler');
        }
    }

    /**
     * Handles 404 Not Found.
     *
     * @return void
     */
    private static function handleNotFound()
    {
        header('HTTP/1.0 404 Not Found');
        view('error', ['message' => '404 Not Found']);
    }

    /**
     * Runs the router.
     *
     * @return void
     * @throws RouterException - Invalid handler
     */
    public static function run()
    {
       $method = $_SERVER['REQUEST_METHOD'];
       $uri = $_SERVER['REQUEST_URI'];

       self::handleMiddleware($method, $uri);

       // Handle the request
       self::handleRequest($method, $uri);
    }

    public static function handleMiddleware(string $method, string $uri)
    {
        $found = false;
        $params = [];
        foreach (self::$routes as $route) {
            if ($route['method'] === $method && self::matchPath($route['path'], $uri, $params)) { //&& does it have a middleware?) {
                // if it has a middleware then trigger all the middlewares assocaited to this route.
            }
        }
    }

    /**
     * Returns the array of routes.
     *
     * @return array - Array of routes
     */
    public static function getRoutes(): array
    {
        return self::$routes;
    }
}
