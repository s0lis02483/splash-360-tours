<?php
// FILE: /app/controllers/SceneController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Scene.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/UsageTracker.php';

/**
 * SceneController
 *
 * Handles scene CRUD operations
 */
class SceneController extends Controller {

    /**
     * List scenes for a tour
     */
    public function index($tourId) {
        $this->requireAuth();

        $sceneModel = new Scene();
        $tourModel = new Tour();

        $tour = $tourModel->findById($tourId);

        if (!$tour) {
            $this->session->setFlash('error', 'Tour not found');
            $this->redirect('/tours');
        }

        $scenes = $sceneModel->getByTourId($tourId);

        $data = [
            'tour' => $tour,
            'scenes' => $scenes
        ];

        $this->view('scenes/index', $data);
    }

    /**
     * Show create scene form
     */
    public function create($tourId) {
        $this->requireAuth();

        // Check usage limits
        $usageTracker = new UsageTracker();
        $tenantId = $this->auth->tenantId();
        $limitCheck = $usageTracker->checkLimit($tenantId, 'scenes');

        if (!$limitCheck['allowed']) {
            $this->session->setFlash('error', $limitCheck['message']);
            $this->redirect('/tours/' . $tourId);
        }

        $tourModel = new Tour();
        $tour = $tourModel->findById($tourId);

        if (!$tour) {
            $this->session->setFlash('error', 'Tour not found');
            $this->redirect('/tours');
        }

        $data = ['tour' => $tour];

        $this->view('scenes/create', $data);
    }

    /**
     * Store new scene
     */
    public function store($tourId) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/tours/' . $tourId . '/scenes/create');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tours/' . $tourId . '/scenes/create');
        }

        // Check usage limits
        $usageTracker = new UsageTracker();
        $tenantId = $this->auth->tenantId();
        $limitCheck = $usageTracker->checkLimit($tenantId, 'scenes');

        if (!$limitCheck['allowed']) {
            $this->session->setFlash('error', $limitCheck['message']);
            $this->redirect('/tours/' . $tourId);
        }

        $tourModel = new Tour();
        $tour = $tourModel->findById($tourId);

        if (!$tour) {
            $this->session->setFlash('error', 'Tour not found');
            $this->redirect('/tours');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('name');

        // Validate image upload
        if (!$this->request->file('image') || $this->request->file('image')['error'] !== UPLOAD_ERR_OK) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', '360° image is required');
            $this->redirect('/tours/' . $tourId . '/scenes/create');
        }

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/tours/' . $tourId . '/scenes/create');
        }

        // Handle file upload
        $upload = new Upload($this->request->file('image'));
        $upload->setAllowedTypes(config('allowed_image_types'))
               ->setMaxSize(config('max_upload_size'))
               ->setUploadPath(storagePath('uploads/scenes'));

        $imagePath = $upload->upload();

        if (!$imagePath) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Image upload failed: ' . $upload->first());
            $this->redirect('/tours/' . $tourId . '/scenes/create');
        }

        // Get next order index
        $sceneModel = new Scene();
        $orderIndex = $sceneModel->getNextOrderIndex($tourId);

        // Prepare data
        $sceneData = [
            'tour_id' => $tourId,
            'name' => $data['name'],
            'image_path' => $imagePath,
            'initial_yaw' => $data['initial_yaw'] ?? 0,
            'initial_pitch' => $data['initial_pitch'] ?? 0,
            'order_index' => $orderIndex
        ];

        $sceneId = $sceneModel->create($sceneData);

        if ($sceneId) {
            $this->session->setFlash('success', 'Scene created successfully');
            $this->redirect('/tours/' . $tourId);
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to create scene');
            $this->redirect('/tours/' . $tourId . '/scenes/create');
        }
    }

    /**
     * Show edit scene form
     */
    public function edit($tourId, $sceneId) {
        $this->requireAuth();

        $sceneModel = new Scene();
        $scene = $sceneModel->getWithHotspots($sceneId);

        if (!$scene) {
            $this->session->setFlash('error', 'Scene not found');
            $this->redirect('/tours/' . $tourId);
        }

        $tourModel = new Tour();
        $tour = $tourModel->findById($tourId);

        $data = [
            'tour' => $tour,
            'scene' => $scene
        ];

        $this->view('scenes/edit', $data);
    }

    /**
     * Update scene
     */
    public function update($tourId, $sceneId) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
        }

        $sceneModel = new Scene();
        $scene = $sceneModel->getWithHotspots($sceneId);

        if (!$scene) {
            $this->session->setFlash('error', 'Scene not found');
            $this->redirect('/tours/' . $tourId);
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('name');

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
        }

        // Handle file upload if new image provided
        $imagePath = $scene['image_path'];
        if ($this->request->file('image') && $this->request->file('image')['error'] === UPLOAD_ERR_OK) {
            $upload = new Upload($this->request->file('image'));
            $upload->setAllowedTypes(config('allowed_image_types'))
                   ->setMaxSize(config('max_upload_size'))
                   ->setUploadPath(storagePath('uploads/scenes'));

            $newImage = $upload->upload();

            if ($newImage) {
                // Delete old image
                if ($imagePath) {
                    $upload->setUploadPath(storagePath('uploads/scenes'));
                    $upload->delete($imagePath);
                }
                $imagePath = $newImage;
            }
        }

        // Prepare data
        $sceneData = [
            'name' => $data['name'],
            'image_path' => $imagePath,
            'initial_yaw' => $data['initial_yaw'] ?? 0,
            'initial_pitch' => $data['initial_pitch'] ?? 0
        ];

        if ($sceneModel->updateScene($sceneId, $sceneData)) {
            $this->session->setFlash('success', 'Scene updated successfully');
            $this->redirect('/tours/' . $tourId);
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to update scene');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
        }
    }

    /**
     * Delete scene
     */
    public function delete($tourId, $sceneId) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/tours/' . $tourId);
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tours/' . $tourId);
        }

        $sceneModel = new Scene();
        $scene = $sceneModel->getWithHotspots($sceneId);

        if (!$scene) {
            $this->session->setFlash('error', 'Scene not found');
            $this->redirect('/tours/' . $tourId);
        }

        // Delete scene image
        if ($scene['image_path']) {
            $upload = new Upload();
            $upload->setUploadPath(storagePath('uploads/scenes'));
            $upload->delete($scene['image_path']);
        }

        if ($sceneModel->deleteScene($sceneId)) {
            $this->session->setFlash('success', 'Scene deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete scene');
        }

        $this->redirect('/tours/' . $tourId);
    }
}
