<?php
// FILE: /app/controllers/UserController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

/**
 * UserController
 *
 * Handles user management (for tenant admins)
 */
class UserController extends Controller {

    /**
     * List all users in tenant
     */
    public function index() {
        $this->requireRole(['tenant_admin']);

        $tenantId = $this->auth->tenantId();

        $userModel = new User();
        $users = $userModel->getByTenant($tenantId);

        $data = ['users' => $users];

        $this->view('users/index', $data);
    }

    /**
     * Show create user form
     */
    public function create() {
        $this->requireRole(['tenant_admin']);

        $this->view('users/create');
    }

    /**
     * Store new user
     */
    public function store() {
        $this->requireRole(['tenant_admin']);

        if (!$this->request->isPost()) {
            $this->redirect('/users/create');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/users/create');
        }

        $data = $this->request->post();
        $tenantId = $this->auth->tenantId();

        // Validate input
        $validator = new Validator($data);
        $validator->required('name')
                  ->required('email')->email('email')->unique('email', 'users', 'email')
                  ->required('password')->min('password', 6)
                  ->required('role')->in('role', ['tenant_admin', 'user']);

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/users/create');
        }

        // Prepare data
        $userData = [
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'status' => 'active'
        ];

        $userModel = new User();
        $userId = $userModel->create($userData);

        if ($userId) {
            $this->session->setFlash('success', 'User created successfully');
            $this->redirect('/users');
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to create user');
            $this->redirect('/users/create');
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id) {
        $this->requireRole(['tenant_admin']);

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            $this->session->setFlash('error', 'User not found');
            $this->redirect('/users');
        }

        $data = ['user' => $user];

        $this->view('users/edit', $data);
    }

    /**
     * Update user
     */
    public function update($id) {
        $this->requireRole(['tenant_admin']);

        if (!$this->request->isPost()) {
            $this->redirect('/users/' . $id . '/edit');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/users/' . $id . '/edit');
        }

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            $this->session->setFlash('error', 'User not found');
            $this->redirect('/users');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('name')
                  ->required('email')->email('email')->unique('email', 'users', 'email', $id)
                  ->required('role')->in('role', ['tenant_admin', 'user'])
                  ->required('status')->in('status', ['active', 'inactive']);

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/users/' . $id . '/edit');
        }

        // Prepare data
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status']
        ];

        // Update password if provided
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $this->session->setFlash('error', 'Password must be at least 6 characters');
                $this->redirect('/users/' . $id . '/edit');
            }
            $userModel->updatePassword($id, $data['password']);
        }

        if ($userModel->update($id, $userData)) {
            $this->session->setFlash('success', 'User updated successfully');
            $this->redirect('/users');
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to update user');
            $this->redirect('/users/' . $id . '/edit');
        }
    }

    /**
     * Delete user
     */
    public function delete($id) {
        $this->requireRole(['tenant_admin']);

        if (!$this->request->isPost()) {
            $this->redirect('/users');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/users');
        }

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            $this->session->setFlash('error', 'User not found');
            $this->redirect('/users');
        }

        // Prevent deleting own account
        if ($user['id'] == $this->auth->id()) {
            $this->session->setFlash('error', 'You cannot delete your own account');
            $this->redirect('/users');
        }

        if ($userModel->delete($id)) {
            $this->session->setFlash('success', 'User deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete user');
        }

        $this->redirect('/users');
    }
}
