<?php
// FILE: /app/core/Request.php

/**
 * Request Class
 *
 * Handles HTTP request data
 */
class Request {
    /**
     * Get value from GET request
     *
     * @param string $key Parameter key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    /**
     * Get value from POST request
     *
     * @param string $key Parameter key
     * @param mixed $default Default value
     * @return mixed
     */
    public function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }

    /**
     * Get value from REQUEST (GET or POST)
     *
     * @param string $key Parameter key
     * @param mixed $default Default value
     * @return mixed
     */
    public function input($key = null, $default = null) {
        if ($key === null) {
            return $_REQUEST;
        }

        return $_REQUEST[$key] ?? $default;
    }

    /**
     * Get uploaded file
     *
     * @param string $key File input name
     * @return array|null
     */
    public function file($key) {
        return $_FILES[$key] ?? null;
    }

    /**
     * Check if request is POST
     *
     * @return bool
     */
    public function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     *
     * @return bool
     */
    public function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Check if request is AJAX
     *
     * @return bool
     */
    public function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get request URI
     *
     * @return string
     */
    public function uri() {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Get all input data (GET + POST)
     *
     * @return array
     */
    public function all() {
        return array_merge($_GET, $_POST);
    }

    /**
     * Sanitize input string
     *
     * @param string $value Input value
     * @return string
     */
    public function sanitize($value) {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}
