<?php
// FILE: /app/core/Controller.php

/**
 * Base Controller Class
 *
 * All controllers extend this base class
 * Provides common functionality like view rendering, redirects, etc.
 */
class Controller {
    protected $auth;
    protected $request;
    protected $session;

    /**
     * Constructor - initialize common services
     */
    public function __construct() {
        $this->auth = new Auth();
        $this->request = new Request();
        $this->session = new Session();
    }

    /**
     * Render a view
     *
     * @param string $view View file path (without .php)
     * @param array $data Data to pass to view
     * @param string $layout Layout file to use
     */
    protected function view($view, $data = [], $layout = 'main') {
        $viewObj = new View();
        $viewObj->render($view, $data, $layout);
    }

    /**
     * Render JSON response
     *
     * @param mixed $data Data to encode
     * @param int $statusCode HTTP status code
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to URL
     *
     * @param string $url URL to redirect to
     */
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }

    /**
     * Redirect back with message
     *
     * @param string $message Flash message
     * @param string $type Message type (success, error, info)
     */
    protected function back($message = '', $type = 'info') {
        if ($message) {
            $this->session->setFlash($type, $message);
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    /**
     * Check if user is authenticated, redirect if not
     */
    protected function requireAuth() {
        if (!$this->auth->check()) {
            $this->session->setFlash('error', 'Please login to continue');
            $this->redirect('/login');
        }
    }

    /**
     * Check if user has specific role
     *
     * @param string|array $roles Role(s) to check
     * @return bool
     */
    protected function requireRole($roles) {
        if (!$this->auth->check()) {
            $this->session->setFlash('error', 'Please login to continue');
            $this->redirect('/login');
        }

        $userRole = $this->auth->user()['role'];

        $roles = is_array($roles) ? $roles : [$roles];

        if (!in_array($userRole, $roles)) {
            $this->session->setFlash('error', 'You do not have permission to access this page');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Validate CSRF token
     *
     * @return bool
     */
    protected function validateCSRF() {
        $token = $this->request->post('csrf_token');
        return CSRF::validate($token);
    }
}
