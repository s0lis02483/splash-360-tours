<?php
// FILE: /app/controllers/AuthController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Tenant.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Plan.php';

/**
 * AuthController
 *
 * Handles user authentication (login, register, logout)
 */
class AuthController extends Controller {

    /**
     * Show login form
     */
    public function showLogin() {
        // Redirect if already logged in
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', [], 'auth');
    }

    /**
     * Process login
     */
    public function login() {
        if (!$this->request->isPost()) {
            $this->redirect('/login');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/login');
        }

        // Rate-limit by IP — block after 8 failed attempts in 15 min
        $ip = $this->getClientIp();
        if ($this->isLoginBlocked($ip)) {
            $this->session->setFlash('error', 'Too many failed attempts. Try again in 15 minutes.');
            $this->redirect('/login');
        }

        $email = $this->request->post('email');
        $password = $this->request->post('password');

        // Validate input
        $validator = new Validator($this->request->post());
        $validator->required('email')->email('email')
                  ->required('password');

        if ($validator->fails()) {
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/login');
        }

        // Verify credentials
        $userModel = new User();
        $user = $userModel->verifyCredentials($email, $password);

        if (!$user) {
            $this->recordFailedLogin($ip, $email);
            $this->session->setFlash('error', 'Invalid email or password');
            $this->redirect('/login');
        }

        // Successful auth — clear failed attempts + regenerate session ID
        $this->clearFailedLogins($ip);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        // Check if user is active
        if ($user['status'] !== 'active') {
            $this->session->setFlash('error', 'Your account is inactive');
            $this->redirect('/login');
        }

        // Login user
        $this->auth->login($user);

        $this->session->setFlash('success', 'Welcome back, ' . $user['name']);
        $this->redirect('/dashboard');
    }

    /**
     * Show registration form
     */
    public function showRegister() {
        // Redirect if already logged in
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/register', [], 'auth');
    }

    /**
     * Process registration
     */
    public function register() {
        if (!$this->request->isPost()) {
            $this->redirect('/register');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/register');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('name')
                  ->required('email')->email('email')->unique('email', 'users', 'email')
                  ->required('password')->min('password', 8)
                  ->required('password_confirm')->match('password_confirm', 'password')
                  ->required('company_name');

        // Reject obviously weak passwords
        $weakPasswords = ['password', '12345678', 'qwerty12', 'admin123', 'letmein123'];
        if (in_array(strtolower($data['password'] ?? ''), $weakPasswords, true)) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Please choose a stronger password.');
            $this->redirect('/register');
        }

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/register');
        }

        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            // Create tenant
            $tenantModel = new Tenant();
            $tenantData = [
                'name' => $data['company_name'],
                'email' => $data['email'],
                'status' => 'active',
                'api_key' => $tenantModel->generateApiKey()
            ];

            $tenantId = $tenantModel->insert($tenantData);

            if (!$tenantId) {
                throw new Exception('Failed to create tenant');
            }

            // Create user as tenant admin
            $userModel = new User();
            $userData = [
                'tenant_id' => $tenantId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'tenant_admin',
                'status' => 'active'
            ];

            $userId = $userModel->create($userData);

            if (!$userId) {
                throw new Exception('Failed to create user');
            }

            // Assign default free plan
            $subscriptionModel = new Subscription();
            $subscriptionData = [
                'tenant_id' => $tenantId,
                'plan_id' => 1, // Free plan
                'status' => 'active',
                'started_at' => date('Y-m-d H:i:s'),
                'expires_at' => null // Free plan never expires
            ];

            $subscriptionModel->create($subscriptionData);

            $db->commit();

            $this->session->setFlash('success', 'Registration successful! Please login.');
            $this->redirect('/login');

        } catch (Exception $e) {
            $db->rollback();
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Registration failed: ' . $e->getMessage());
            $this->redirect('/register');
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        $this->auth->logout();
        $this->session->setFlash('success', 'You have been logged out');
        $this->redirect('/login');
    }

    // ============================================================
    // RATE LIMITING (login brute-force protection)
    // ============================================================

    private function getClientIp() {
        // Honor Vercel/CF forwarded IP, but only the first hop (left-most).
        $candidates = [
            $_SERVER['HTTP_X_FORWARDED_FOR']  ?? '',
            $_SERVER['HTTP_X_REAL_IP']         ?? '',
            $_SERVER['REMOTE_ADDR']            ?? '',
        ];
        foreach ($candidates as $c) {
            if (empty($c)) continue;
            $first = trim(explode(',', $c)[0]);
            if (filter_var($first, FILTER_VALIDATE_IP)) return $first;
        }
        return '0.0.0.0';
    }

    private function ensureRateLimitTable() {
        try {
            $db = Database::getInstance()->getConnection();
            $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
                id SERIAL PRIMARY KEY,
                ip VARCHAR(45) NOT NULL,
                email VARCHAR(255) DEFAULT NULL,
                attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )");
            $db->exec("CREATE INDEX IF NOT EXISTS login_attempts_ip_time ON login_attempts(ip, attempted_at)");
        } catch (Throwable $e) { /* swallow — never block boot */ }
    }

    private function isLoginBlocked($ip) {
        $this->ensureRateLimitTable();
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) AS c FROM login_attempts
                                  WHERE ip = ? AND attempted_at > NOW() - INTERVAL '15 minutes'");
            $stmt->execute([$ip]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return ((int)($row['c'] ?? 0)) >= 8;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function recordFailedLogin($ip, $email) {
        $this->ensureRateLimitTable();
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO login_attempts (ip, email) VALUES (?, ?)");
            $stmt->execute([$ip, substr((string)$email, 0, 255)]);
            // GC: keep the table tidy
            $db->exec("DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL '24 hours'");
        } catch (Throwable $e) { /* swallow */ }
    }

    private function clearFailedLogins($ip) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip = ?");
            $stmt->execute([$ip]);
        } catch (Throwable $e) { /* swallow */ }
    }
}
