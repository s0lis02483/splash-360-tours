<?php
// FILE: /app/core/Auth.php

/**
 * Auth Class
 *
 * Handles user authentication
 */
class Auth {
    private $session;

    /**
     * Constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Login user
     *
     * @param array $user User data
     */
    public function login($user) {
        $this->session->set('user_id', $user['id']);
        $this->session->set('user', $user);
        $this->session->regenerate();
    }

    /**
     * Logout user
     */
    public function logout() {
        $this->session->remove('user_id');
        $this->session->remove('user');
        $this->session->destroy();
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public function check() {
        return $this->session->has('user_id');
    }

    /**
     * Get authenticated user
     *
     * @return array|null
     */
    public function user() {
        return $this->session->get('user');
    }

    /**
     * Get authenticated user ID
     *
     * @return int|null
     */
    public function id() {
        return $this->session->get('user_id');
    }

    /**
     * Check if user has role
     *
     * @param string|array $roles Role(s) to check
     * @return bool
     */
    public function hasRole($roles) {
        if (!$this->check()) {
            return false;
        }

        $user = $this->user();
        $userRole = $user['role'] ?? null;

        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($userRole, $roles);
    }

    /**
     * Get user tenant ID
     *
     * @return int|null
     */
    public function tenantId() {
        if (!$this->check()) {
            return null;
        }

        $user = $this->user();
        return $user['tenant_id'] ?? null;
    }

    /**
     * Check if user is platform admin
     *
     * @return bool
     */
    public function isPlatformAdmin() {
        return $this->hasRole('platform_admin');
    }

    /**
     * Check if user is tenant admin
     *
     * @return bool
     */
    public function isTenantAdmin() {
        return $this->hasRole('tenant_admin');
    }
}
