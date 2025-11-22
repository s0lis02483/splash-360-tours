<?php
// FILE: /app/core/CSRF.php

/**
 * CSRF Protection Class
 *
 * Generates and validates CSRF tokens
 */
class CSRF {
    /**
     * Generate CSRF token
     *
     * @return string
     */
    public static function generate() {
        $session = new Session();

        if (!$session->has('csrf_token')) {
            $token = bin2hex(random_bytes(32));
            $session->set('csrf_token', $token);
        }

        return $session->get('csrf_token');
    }

    /**
     * Validate CSRF token
     *
     * @param string $token Token to validate
     * @return bool
     */
    public static function validate($token) {
        $session = new Session();
        $sessionToken = $session->get('csrf_token');

        if (!$sessionToken || !$token) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Get CSRF token field HTML
     *
     * @return string
     */
    public static function field() {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
