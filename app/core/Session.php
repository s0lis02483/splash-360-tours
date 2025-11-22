<?php
// FILE: /app/core/Session.php

/**
 * Session Class
 *
 * Handles session management
 */
class Session {
    /**
     * Constructor - start session if not started
     */
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set session value
     *
     * @param string $key Session key
     * @param mixed $value Session value
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     *
     * @param string $key Session key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     *
     * @param string $key Session key
     * @return bool
     */
    public function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     *
     * @param string $key Session key
     */
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Set flash message
     *
     * @param string $type Message type (success, error, info, warning)
     * @param string $message Message text
     */
    public function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get flash message and remove it
     *
     * @param string $type Message type
     * @return string|null
     */
    public function getFlash($type = null) {
        if ($type === null) {
            $flash = $_SESSION['flash'] ?? [];
            unset($_SESSION['flash']);
            return $flash;
        }

        $message = $_SESSION['flash'][$type] ?? null;
        if ($message) {
            unset($_SESSION['flash'][$type]);
        }
        return $message;
    }

    /**
     * Check if flash message exists
     *
     * @param string $type Message type
     * @return bool
     */
    public function hasFlash($type = null) {
        if ($type === null) {
            return !empty($_SESSION['flash']);
        }

        return isset($_SESSION['flash'][$type]);
    }

    /**
     * Destroy session
     */
    public function destroy() {
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Regenerate session ID
     */
    public function regenerate() {
        session_regenerate_id(true);
    }
}
