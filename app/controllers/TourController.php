<?php
// FILE: /app/controllers/TourController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../models/TourView.php';
require_once __DIR__ . '/../models/UsageTracker.php';

/**
 * TourController
 *
 * Handles tour CRUD operations
 */
class TourController extends Controller {

    /**
     * List all tours
     */
    public function index() {
        $this->requireAuth();

        $tourModel = new Tour();

        // Get filters from request
        $filters = [
            'status' => $this->request->get('status'),
            'property_id' => $this->request->get('property_id'),
            'search' => $this->request->get('search')
        ];

        // Pagination
        $page = (int)$this->request->get('page', 1);
        $perPage = config('per_page', 20);
        $offset = ($page - 1) * $perPage;

        // Get tours
        $tours = $tourModel->getWithDetails($filters, $perPage, $offset);

        // Get total count for pagination
        $totalCount = $tourModel->countFiltered($filters);
        $totalPages = ceil($totalCount / $perPage);

        // Get properties for filter
        $propertyModel = new Property();
        $properties = $propertyModel->findAll();

        $data = [
            'tours' => $tours,
            'properties' => $properties,
            'filters' => $filters,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount
        ];

        $this->view('tours/index', $data);
    }

    /**
     * Show create tour form
     */
    public function create() {
        $this->requireAuth();

        // Check usage limits
        $usageTracker = new UsageTracker();
        $tenantId = $this->auth->tenantId();
        $limitCheck = $usageTracker->checkLimit($tenantId, 'tours');

        if (!$limitCheck['allowed']) {
            $this->session->setFlash('error', $limitCheck['message']);
            $this->redirect('/tours');
        }

        $propertyModel = new Property();
        $properties = $propertyModel->findAll();

        $data = ['properties' => $properties];

        $this->view('tours/create', $data);
    }

    /**
     * Store new tour
     */
    public function store() {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/tours/create');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tours/create');
        }

        // Check usage limits
        $usageTracker = new UsageTracker();
        $tenantId = $this->auth->tenantId();
        $limitCheck = $usageTracker->checkLimit($tenantId, 'tours');

        if (!$limitCheck['allowed']) {
            $this->session->setFlash('error', $limitCheck['message']);
            $this->redirect('/tours');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('title')
                  ->required('property_id')
                  ->required('status');

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/tours/create');
        }

        // Generate unique slug
        $tourModel = new Tour();
        $slug = $tourModel->generateSlug($data['title']);

        // Prepare data
        $tourData = [
            'property_id' => $data['property_id'],
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'is_public' => isset($data['is_public']) ? 1 : 0,
            'is_featured' => isset($data['is_featured']) ? 1 : 0
        ];

        $tourId = $tourModel->insert($tourData);

        if ($tourId) {
            $this->session->setFlash('success', 'Tour created successfully');
            $this->redirect('/tours/' . $tourId);
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to create tour');
            $this->redirect('/tours/create');
        }
    }

    /**
     * Show tour details
     */
    public function show($id) {
        $this->requireAuth();

        $tourModel = new Tour();
        $tour = $tourModel->getWithScenes($id);

        if (!$tour) {
            $this->session->setFlash('error', 'Tour not found');
            $this->redirect('/tours');
        }

        // Get property details
        $propertyModel = new Property();
        $property = $propertyModel->findById($tour['property_id']);

        // Get view count
        $tourViewModel = new TourView();
        $viewCount = $tourViewModel->getCountByTour($id);

        $data = [
            'tour' => $tour,
            'property' => $property,
            'view_count' => $viewCount
        ];

        $this->view('tours/view', $data);
    }

    /**
     * Show edit tour form
     */
    public function edit($id) {
        $this->requireAuth();

        $tourModel = new Tour();
        $tour = $tourModel->findById($id);

        if (!$tour) {
            $this->session->setFlash('error', 'Tour not found');
            $this->redirect('/tours');
        }

        $propertyModel = new Property();
        $properties = $propertyModel->findAll();

        $data = [
            'tour' => $tour,
            'properties' => $properties
        ];

        $this->view('tours/edit', $data);
    }

    /**
     * Update tour
     */
    public function update($id) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/tours/' . $id . '/edit');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tours/' . $id . '/edit');
        }

        $tourModel = new Tour();
        $tour = $tourModel->findById($id);

        if (!$tour) {
            $this->session->setFlash('error', 'Tour not found');
            $this->redirect('/tours');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('title')
                  ->required('property_id')
                  ->required('status');

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/tours/' . $id . '/edit');
        }

        // Generate slug if title changed
        $slug = $tour['slug'];
        if ($data['title'] !== $tour['title']) {
            $slug = $tourModel->generateSlug($data['title'], $id);
        }

        // Prepare data
        $tourData = [
            'property_id' => $data['property_id'],
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'is_public' => isset($data['is_public']) ? 1 : 0,
            'is_featured' => isset($data['is_featured']) ? 1 : 0
        ];

        if ($tourModel->update($id, $tourData)) {
            $this->session->setFlash('success', 'Tour updated successfully');
            $this->redirect('/tours/' . $id);
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to update tour');
            $this->redirect('/tours/' . $id . '/edit');
        }
    }

    /**
     * Delete tour
     */
    public function delete($id) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/tours');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tours');
        }

        $tourModel = new Tour();
        $tour = $tourModel->findById($id);

        if (!$tour) {
            $this->session->setFlash('error', 'Tour not found');
            $this->redirect('/tours');
        }

        if ($tourModel->delete($id)) {
            $this->session->setFlash('success', 'Tour deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete tour');
        }

        $this->redirect('/tours');
    }

    /**
     * Show public tour
     */
    public function viewPublic($slug) {
        $tourModel = new Tour();
        $tour = $tourModel->getPublicTourWithScenes($slug);

        if (!$tour) {
            http_response_code(404);
            echo "Tour not found";
            exit;
        }

        // Record view
        $tourViewModel = new TourView();
        $tourViewModel->recordView($tour['id']);

        $data = ['tour' => $tour];

        $this->view('public/tour', $data, 'public');
    }
}
