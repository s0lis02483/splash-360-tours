<?php
// FILE: /app/controllers/AuthController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Tenant.php';

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
            $this->session->setFlash('error', 'Invalid email or password');
            $this->redirect('/login');
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
                  ->required('password')->min('password', 6)
                  ->required('password_confirm')->match('password_confirm', 'password')
                  ->required('company_name');

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
}
