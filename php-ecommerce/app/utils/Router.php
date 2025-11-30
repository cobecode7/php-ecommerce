<?php
/**
 * Router Class
 * Handles URL routing for the application with middleware support
 */

class Router
{
    private $routes = [];

    public function get($path, $callback, $middleware = [])
    {
        $this->addRoute('GET', $path, $callback, $middleware);
    }

    public function post($path, $callback, $middleware = [])
    {
        $this->addRoute('POST', $path, $callback, $middleware);
    }

    public function put($path, $callback, $middleware = [])
    {
        $this->addRoute('PUT', $path, $callback, $middleware);
    }

    public function delete($path, $callback, $middleware = [])
    {
        $this->addRoute('DELETE', $path, $callback, $middleware);
    }

    private function addRoute($method, $path, $callback, $middleware = [])
    {
        $this->routes[$method][$path] = [
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }

    public function resolve()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Handle HEAD requests by converting to GET and suppressing output
        $isHeadRequest = false;
        if ($method === 'HEAD') {
            $method = 'GET';
            $isHeadRequest = true;
        }

        // Remove the base path if the application is not in the root directory
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
        if ($basePath && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        if ($path === '') {
            $path = '/';
        }

        // Remove trailing slash to normalize routes
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        if (isset($this->routes[$method][$path])) {
            $route = $this->routes[$method][$path];
            $this->runMiddleware($route['middleware']);

            $callback = $route['callback'];
            if (is_callable($callback)) {
                if ($isHeadRequest) {
                    // For HEAD requests, suppress output but execute the callback
                    ob_start();
                    call_user_func($callback);
                    ob_end_clean();
                    return;
                }
                return call_user_func($callback);
            } elseif (is_array($callback) && count($callback) === 2) {
                $controller = new $callback[0]();
                if ($isHeadRequest) {
                    // For HEAD requests, suppress output but execute the callback
                    ob_start();
                    call_user_func([$controller, $callback[1]]);
                    ob_end_clean();
                    return;
                }
                return call_user_func([$controller, $callback[1]]);
            }
        }

        // Try to find route with parameters
        foreach ($this->routes[$method] ?? [] as $route => $config) {
            if (strpos($route, '{') !== false) {
                // Separate the route into static parts and parameter parts
                // First, extract the parameter definitions to preserve them unescaped
                preg_match_all('/\{(\w+):([^}]+)\}/', $route, $paramMatches, PREG_OFFSET_CAPTURE);

                $pattern = '';
                $lastPos = 0;

                // Process the route character by character, handling param parts specially
                for ($i = 0; $i < count($paramMatches[0]); $i++) {
                    $fullMatch = $paramMatches[0][$i][0];
                    $paramName = $paramMatches[1][$i][0];
                    $paramPattern = $paramMatches[2][$i][0];
                    $offset = $paramMatches[0][$i][1];

                    // Add the static part before this parameter
                    $staticPart = substr($route, $lastPos, $offset - $lastPos);
                    $pattern .= preg_quote($staticPart, '/');

                    // Add the named capture group for the parameter
                    $pattern .= '(?P<' . $paramName . '>' . $paramPattern . ')';

                    $lastPos = $offset + strlen($fullMatch);
                }

                // Add the remaining static part after the last parameter
                $pattern .= preg_quote(substr($route, $lastPos), '/');

                if (preg_match('/^' . $pattern . '$/', $path, $matches)) {
                    $params = [];
                    foreach ($matches as $key => $value) {
                        if (!is_numeric($key) && $key !== 'id') {
                            $params[$key] = $value;
                        }
                    }

                    $this->runMiddleware($config['middleware']);

                    $callback = $config['callback'];
                    if (is_callable($callback)) {
                        if ($isHeadRequest) {
                            // For HEAD requests, suppress output but execute the callback
                            ob_start();
                            call_user_func($callback, $matches);
                            ob_end_clean();
                            return;
                        }
                        return call_user_func($callback, $matches);
                    } elseif (is_array($callback) && count($callback) === 2) {
                        $controller = new $callback[0]();
                        if ($isHeadRequest) {
                            // For HEAD requests, suppress output but execute the callback
                            ob_start();
                            call_user_func([$controller, $callback[1]], $matches);
                            ob_end_clean();
                            return;
                        }
                        return call_user_func([$controller, $callback[1]], $matches);
                    }
                }
            }
        }

        // Return 404 if no route matches
        http_response_code(404);
        if (!$isHeadRequest) {
            include __DIR__ . '/../../views/404.html';
        }
    }

    private function runMiddleware($middleware)
    {
        foreach ($middleware as $middlewareClass) {
            $middlewareInstance = new $middlewareClass();
            $middlewareInstance->handle();
        }
    }
}