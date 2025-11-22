<?php
// FILE: /app/controllers/HotspotController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Hotspot.php';
require_once __DIR__ . '/../models/Scene.php';
require_once __DIR__ . '/../models/Tour.php';

/**
 * HotspotController
 *
 * Handles hotspot CRUD operations
 */
class HotspotController extends Controller {

    /**
     * Show create hotspot form
     */
    public function create($tourId, $sceneId) {
        $this->requireAuth();

        $sceneModel = new Scene();
        $scene = $sceneModel->getWithHotspots($sceneId);

        if (!$scene) {
            $this->session->setFlash('error', 'Scene not found');
            $this->redirect('/tours/' . $tourId);
        }

        $tourModel = new Tour();
        $tour = $tourModel->getWithScenes($tourId);

        $hotspotModel = new Hotspot();

        $data = [
            'tour' => $tour,
            'scene' => $scene,
            'types' => $hotspotModel->getTypes(),
            'icon_types' => $hotspotModel->getIconTypes()
        ];

        $this->view('hotspots/create', $data);
    }

    /**
     * Store new hotspot
     */
    public function store($tourId, $sceneId) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/hotspots/create');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/hotspots/create');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('type')
                  ->required('yaw')->numeric('yaw')
                  ->required('pitch')->numeric('pitch');

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/hotspots/create');
        }

        // Prepare data
        $hotspotData = [
            'scene_id' => $sceneId,
            'type' => $data['type'],
            'yaw' => $data['yaw'],
            'pitch' => $data['pitch'],
            'label' => $data['label'] ?? null,
            'description' => $data['description'] ?? null,
            'target_scene_id' => $data['target_scene_id'] ?? null,
            'external_url' => $data['external_url'] ?? null,
            'icon_type' => $data['icon_type'] ?? 'arrow'
        ];

        $hotspotModel = new Hotspot();
        $hotspotId = $hotspotModel->create($hotspotData);

        if ($hotspotId) {
            $this->session->setFlash('success', 'Hotspot created successfully');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to create hotspot');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/hotspots/create');
        }
    }

    /**
     * Show edit hotspot form
     */
    public function edit($tourId, $sceneId, $hotspotId) {
        $this->requireAuth();

        $hotspotModel = new Hotspot();
        $hotspot = $hotspotModel->getById($hotspotId);

        if (!$hotspot) {
            $this->session->setFlash('error', 'Hotspot not found');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
        }

        $sceneModel = new Scene();
        $scene = $sceneModel->getWithHotspots($sceneId);

        $tourModel = new Tour();
        $tour = $tourModel->getWithScenes($tourId);

        $data = [
            'tour' => $tour,
            'scene' => $scene,
            'hotspot' => $hotspot,
            'types' => $hotspotModel->getTypes(),
            'icon_types' => $hotspotModel->getIconTypes()
        ];

        $this->view('hotspots/edit', $data);
    }

    /**
     * Update hotspot
     */
    public function update($tourId, $sceneId, $hotspotId) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/hotspots/' . $hotspotId . '/edit');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/hotspots/' . $hotspotId . '/edit');
        }

        $hotspotModel = new Hotspot();
        $hotspot = $hotspotModel->getById($hotspotId);

        if (!$hotspot) {
            $this->session->setFlash('error', 'Hotspot not found');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
        }

        $data = $this->request->post();

        // Validate input
        $validator = new Validator($data);
        $validator->required('type')
                  ->required('yaw')->numeric('yaw')
                  ->required('pitch')->numeric('pitch');

        if ($validator->fails()) {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', $validator->first());
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/hotspots/' . $hotspotId . '/edit');
        }

        // Prepare data
        $hotspotData = [
            'type' => $data['type'],
            'yaw' => $data['yaw'],
            'pitch' => $data['pitch'],
            'label' => $data['label'] ?? null,
            'description' => $data['description'] ?? null,
            'target_scene_id' => $data['target_scene_id'] ?? null,
            'external_url' => $data['external_url'] ?? null,
            'icon_type' => $data['icon_type'] ?? 'arrow'
        ];

        if ($hotspotModel->updateHotspot($hotspotId, $hotspotData)) {
            $this->session->setFlash('success', 'Hotspot updated successfully');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
        } else {
            $this->session->set('old_input', $data);
            $this->session->setFlash('error', 'Failed to update hotspot');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/hotspots/' . $hotspotId . '/edit');
        }
    }

    /**
     * Delete hotspot
     */
    public function delete($tourId, $sceneId, $hotspotId) {
        $this->requireAuth();

        if (!$this->request->isPost()) {
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
        }

        // Validate CSRF
        if (!$this->validateCSRF()) {
            $this->session->setFlash('error', 'Invalid request');
            $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
        }

        $hotspotModel = new Hotspot();

        if ($hotspotModel->deleteHotspot($hotspotId)) {
            $this->session->setFlash('success', 'Hotspot deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete hotspot');
        }

        $this->redirect('/tours/' . $tourId . '/scenes/' . $sceneId . '/edit');
    }
}
