<?php
// FILE: /app/core/Router.php

/**
 * Router Class
 *
 * Handles URL routing and dispatching to appropriate controllers
 */
class Router {
    private $routes = [];
    private $namedRoutes = [];

    /**
     * Add GET route
     *
     * @param string $path URL path
     * @param string $controller Controller@method
     * @param string $name Optional route name
     */
    public function get($path, $controller, $name = null) {
        $this->addRoute('GET', $path, $controller, $name);
    }

    /**
     * Add POST route
     *
     * @param string $path URL path
     * @param string $controller Controller@method
     * @param string $name Optional route name
     */
    public function post($path, $controller, $name = null) {
        $this->addRoute('POST', $path, $controller, $name);
    }

    /**
     * Add route to routes array
     *
     * @param string $method HTTP method
     * @param string $path URL path
     * @param string $controller Controller@method
     * @param string $name Optional route name
     */
    private function addRoute($method, $path, $controller, $name = null) {
        $pattern = $this->convertPathToRegex($path);

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'controller' => $controller,
            'name' => $name
        ];

        if ($name) {
            $this->namedRoutes[$name] = $path;
        }
    }

    /**
     * Convert path with parameters to regex pattern
     *
     * @param string $path URL path with {param} syntax
     * @return string Regex pattern
     */
    private function convertPathToRegex($path) {
        // Convert {id} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Dispatch request to appropriate controller
     *
     * @param string $requestUri Current URI
     * @param string $requestMethod HTTP method
     */
    public function dispatch($requestUri, $requestMethod) {
        // Remove query string
        $uri = parse_url($requestUri, PHP_URL_PATH);

        // Remove base path if exists
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return $this->executeController($route['controller'], $params);
            }
        }

        // No route found - 404
        http_response_code(404);
        echo "404 - Page Not Found";
    }

    /**
     * Execute controller method
     *
     * @param string $controllerString Controller@method
     * @param array $params Route parameters
     */
    private function executeController($controllerString, $params = []) {
        list($controllerName, $method) = explode('@', $controllerString);

        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            die("Controller not found: $controllerName");
        }

        require_once $controllerFile;

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            die("Method not found: $method in $controllerName");
        }

        // Pass parameters to method
        call_user_func_array([$controller, $method], $params);
    }

    /**
     * Generate URL for named route
     *
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string Generated URL
     */
    public function url($name, $params = []) {
        if (!isset($this->namedRoutes[$name])) {
            return '#';
        }

        $path = $this->namedRoutes[$name];

        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }

        return $path;
    }
}
