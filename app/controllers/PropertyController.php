<?php
// FILE: /app/controllers/PropertyController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../models/UsageTracker.php';

/**
 * PropertyController
 *
 * Handles property CRUD operations
 */
class PropertyController extends Controller {

    /**
     * List all properties
     */
    public function index() {
        $this->requireAuth();

        $propertyModel = new Property();

        // Get filters from request
        $filters = [
            'status' => $this->request->get('status'),
            'type' => $this->request->get('type'),
            'search' => $this->request->get('search')
        ];

        // Pagination
        $page = (int)$this->request->get('page', 1);
        $perPage = config('per_page', 20);
        $offset = ($page - 1) * $perPage;

        // Get properties
        $properties = $propertyModel->getWithTourCount($filters, $perPage, $offset);

        // Get total count for pagination
        $totalCount = $propertyModel->countFiltered($filters);
        $totalPages = ceil($totalCount / $perPage);

        // Get types and statuses for filters
        $types = $propertyModel->getTypes();
        $statuses = $propertyModel->getStatuses();

        $data = [
            'properties' => $properties,
            'types' => $types,
            'statuses' => $statuses,
            'filters' => $filters,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount
        ];

        $this->view('properties/index', $data);
    }

    /**
     * Show create property form
     */
    public function create() {
        $this->requireAuth();

        // Check usage limits
        $usageTracker = new UsageTracker();
        $tenantId = $this->auth->tenantId();
        $limitCheck = $usageTracker->checkLimit($tenantId, 'properties');

        if (!$limitCheck['allowed']) {
            $this->session->setFlash('error', $limitCheck['message']);
            $this->redirect('/properties');
        }

        $propertyModel = new Property();

        $data = [
            'types' => $propertyModel->getTypes(),
            'statuses' => $propertyModel->getStatuses()
        ];

        $this->view('properties/create', $data);
    }

    /**
     * Store new property
     */
    public function store() {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/properties/create');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/properties/create');
        }

        // Check usage limits
        $usageTracker = new UsageTracker();
        $tenantId = $this->auth->tenantId();
        $limitCheck = $usageTracker->checkLimit($tenantId, 'properties');

        if (!$limitCheck['allowed']) {
            $this->session->setFlash('error', $limitCheck['message']);
            $this->redirect('/properties');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('title')
                  ->required('type')
                  ->required('status');

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/properties/create');
        }

        // Handle file upload
        $mainImage = null;
        if ($this->request->file('main_image') && $this->request->file('main_image')['error'] === UPLOAD_ERR_OK) {
            $upload = new Upload($this->request->file('main_image'));
            $upload->setAllowedTypes(config('allowed_image_types'))
                   ->setMaxSize(config('max_upload_size'))
                   ->setUploadPath(storagePath('uploads/properties'));

            $mainImage = $upload->upload();

            if (!$mainImage) {
                $this->session->set('old_input', $data);
                $this->session->setFlash('error', 'Image upload failed: ' . $upload->first());
                $this->redirect('/properties/create');
            }
        }

        // Prepare data
        $propertyData = [
            'title' => $data['title'],
            'reference' => $data['reference'] ?? null,
            'type' => $data['type'],
            'status' => $data['status'],
            'price' => $data['price'] ?? null,
            'location' => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'main_image' => $mainImage
        ];

        $propertyModel = new Property();
        $propertyId = $propertyModel->insert($propertyData);

        if ($propertyId) {
            $this->session->setFlash('success', 'Property created successfully');
            $this->redirect('/properties/' . $propertyId);
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to create property');
            $this->redirect('/properties/create');
        }
    }

    /**
     * Show property details
     */
    public function show($id) {
        $this->requireAuth();

        $propertyModel = new Property();
        $property = $propertyModel->getWithTours($id);

        if (!$property) {
            $this->session->setFlash('error', 'Property not found');
            $this->redirect('/properties');
        }

        $data = ['property' => $property];

        $this->view('properties/view', $data);
    }

    /**
     * Show edit property form
     */
    public function edit($id) {
        $this->requireAuth();

        $propertyModel = new Property();
        $property = $propertyModel->findById($id);

        if (!$property) {
            $this->session->setFlash('error', 'Property not found');
            $this->redirect('/properties');
        }

        $data = [
            'property' => $property,
            'types' => $propertyModel->getTypes(),
            'statuses' => $propertyModel->getStatuses()
        ];

        $this->view('properties/edit', $data);
    }

    /**
     * Update property
     */
    public function update($id) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/properties/' . $id . '/edit');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/properties/' . $id . '/edit');
        }

        $propertyModel = new Property();
        $property = $propertyModel->findById($id);

        if (!$property) {
            $this->session->setFlash('error', 'Property not found');
            $this->redirect('/properties');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('title')
                  ->required('type')
                  ->required('status');

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/properties/' . $id . '/edit');
        }

        // Handle file upload
        $mainImage = $property['main_image'];
        if ($this->request->file('main_image') && $this->request->file('main_image')['error'] === UPLOAD_ERR_OK) {
            $upload = new Upload($this->request->file('main_image'));
            $upload->setAllowedTypes(config('allowed_image_types'))
                   ->setMaxSize(config('max_upload_size'))
                   ->setUploadPath(storagePath('uploads/properties'));

            $newImage = $upload->upload();

            if ($newImage) {
                // Delete old image
                if ($mainImage) {
                    $upload->setUploadPath(storagePath('uploads/properties'));
                    $upload->delete($mainImage);
                }
                $mainImage = $newImage;
            }
        }

        // Helper: trim, return null if blank
        $nb = function ($v) { $v = trim((string)$v); return $v === '' ? null : $v; };

        // Prepare data — write the columns that actually exist on the properties table
        $propertyData = [
            'name'              => $data['title'] ?? $data['name'] ?? null,
            'type'              => $data['type'] ?? 'residential',
            'status'            => $data['status'] ?? 'active',
            'address'           => $nb($data['address']           ?? ''),
            'city'              => $nb($data['city']              ?? ''),
            'country'           => $nb($data['country']           ?? ''),
            'description'       => $nb($data['description']       ?? ''),
            'building_type'     => $nb($data['building_type']     ?? ''),
            'floor'             => $nb($data['floor']             ?? ''),
            'rooms_total'       => $nb($data['rooms_total']       ?? ''),
            'bedrooms'          => $nb($data['bedrooms']          ?? ''),
            'bathrooms'         => $nb($data['bathrooms']         ?? ''),
            'has_parking'       => !empty($data['has_parking']) ? 't' : 'f',
            'monthly_rent'      => $nb($data['monthly_rent']      ?? ''),
            'deposit'           => $nb($data['deposit']           ?? ''),
            'monthly_utilities' => $nb($data['monthly_utilities'] ?? ''),
            'specialties'       => $nb($data['specialties']       ?? ''),
        ];

        // Drop nulls so we don't overwrite valid columns with NULL when input is blank
        $propertyData = array_filter($propertyData, function ($v) { return $v !== null; });

        if ($propertyModel->update($id, $propertyData)) {
            $this->session->setFlash('success', 'Property updated successfully');
            $this->redirect('/properties/' . $id);
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to update property');
            $this->redirect('/properties/' . $id . '/edit');
        }
    }

    /**
     * Delete property
     */
    public function delete($id) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/properties');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/properties');
        }

        $propertyModel = new Property();
        $property = $propertyModel->findById($id);

        if (!$property) {
            $this->session->setFlash('error', 'Property not found');
            $this->redirect('/properties');
        }

        // Delete property image
        if ($property['main_image']) {
            $upload = new Upload();
            $upload->setUploadPath(storagePath('uploads/properties'));
            $upload->delete($property['main_image']);
        }

        if ($propertyModel->delete($id)) {
            $this->session->setFlash('success', 'Property deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete property');
        }

        $this->redirect('/properties');
    }
}
