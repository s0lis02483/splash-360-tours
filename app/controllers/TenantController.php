<?php
// FILE: /app/controllers/TenantController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Tenant.php';

/**
 * TenantController
 *
 * Handles tenant management (for platform admins only)
 */
class TenantController extends Controller {

    /**
     * List all tenants
     */
    public function index() {
        $this->requireRole(['platform_admin']);

        $tenantModel = new Tenant();

        // Pagination
        $page = (int)$this->request->get('page', 1);
        $perPage = config('per_page', 20);

        $tenants = $tenantModel->getPaginated($page, $perPage);
        $totalCount = $tenantModel->count();
        $totalPages = ceil($totalCount / $perPage);

        $data = [
            'tenants' => $tenants,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount
        ];

        $this->view('tenants/index', $data);
    }

    /**
     * Show create tenant form
     */
    public function create() {
        $this->requireRole(['platform_admin']);

        $this->view('tenants/create');
    }

    /**
     * Store new tenant
     */
    public function store() {
        $this->requireRole(['platform_admin']);

        if (!$this->request->isPost()) {
            $this->redirect('/tenants/create');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tenants/create');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('name')
                  ->required('email')->email('email')->unique('email', 'tenants', 'email');

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/tenants/create');
        }

        $tenantModel = new Tenant();

        // Prepare data
        $tenantData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => 'active',
            'api_key' => $tenantModel->generateApiKey()
        ];

        $tenantId = $tenantModel->insert($tenantData);

        if ($tenantId) {
            $this->session->setFlash('success', 'Tenant created successfully');
            $this->redirect('/tenants');
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to create tenant');
            $this->redirect('/tenants/create');
        }
    }

    /**
     * Show edit tenant form
     */
    public function edit($id) {
        $this->requireRole(['platform_admin']);

        $tenantModel = new Tenant();
        $tenant = $tenantModel->findById($id);

        if (!$tenant) {
            $this->session->setFlash('error', 'Tenant not found');
            $this->redirect('/tenants');
        }

        $data = ['tenant' => $tenant];

        $this->view('tenants/edit', $data);
    }

    /**
     * Update tenant
     */
    public function update($id) {
        $this->requireRole(['platform_admin']);

        if (!$this->request->isPost()) {
            $this->redirect('/tenants/' . $id . '/edit');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tenants/' . $id . '/edit');
        }

        $tenantModel = new Tenant();
        $tenant = $tenantModel->findById($id);

        if (!$tenant) {
            $this->session->setFlash('error', 'Tenant not found');
            $this->redirect('/tenants');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('name')
                  ->required('email')->email('email')->unique('email', 'tenants', 'email', $id)
                  ->required('status')->in('status', ['active', 'inactive', 'suspended']);

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/tenants/' . $id . '/edit');
        }

        // Prepare data
        $tenantData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status']
        ];

        if ($tenantModel->update($id, $tenantData)) {
            $this->session->setFlash('success', 'Tenant updated successfully');
            $this->redirect('/tenants');
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to update tenant');
            $this->redirect('/tenants/' . $id . '/edit');
        }
    }
}
