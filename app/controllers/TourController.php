<?php
// FILE: /app/controllers/TourController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../models/Scene.php';
require_once __DIR__ . '/../models/Hotspot.php';
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
     * Show create tour form (simplified: place name + multi-image)
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

        $this->view('tours/create', []);
    }

    /**
     * Store new tour — supports both:
     *   (a) Simple flow: place_name + images[] upload (auto-creates property, scenes, hotspots)
     *   (b) Legacy flow: title + property_id (old form, no images)
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

        $post = $this->request->post();

        // ---- Determine flow ----
        $isSimpleFlow = !empty($post['place_name']);

        if ($isSimpleFlow) {
            $placeName = trim($post['place_name']);
            if (empty($placeName)) {
                $this->session->setFlash('error', 'Place name is required');
                $this->redirect('/tours/create');
            }

            // Collect uploaded images
            $uploadedFiles = $this->collectMultipleFiles('images');

            if (empty($uploadedFiles)) {
                $this->session->set('old_input', $post);
                $this->session->setFlash('error', 'Please upload at least one 360° photo');
                $this->redirect('/tours/create');
            }

            // 1. Auto-create a Property for this place — capture all listing details
            $propertyModel = new Property();

            // Helper: empty string → null; otherwise cast appropriately
            $nullIfBlank = function ($v) {
                if ($v === null) return null;
                $v = trim((string)$v);
                return $v === '' ? null : $v;
            };

            $propertyData = [
                'name'              => $placeName,
                'type'              => 'residential',
                'status'            => 'active',
                'address'           => $nullIfBlank($post['address'] ?? null),
                'building_type'     => $nullIfBlank($post['building_type'] ?? null),
                'floor'             => $nullIfBlank($post['floor'] ?? null),
                'rooms_total'       => $nullIfBlank($post['rooms_total'] ?? null),
                'bedrooms'          => $nullIfBlank($post['bedrooms'] ?? null),
                'bathrooms'         => $nullIfBlank($post['bathrooms'] ?? null),
                'has_parking'       => !empty($post['has_parking']) ? 't' : 'f',
                'monthly_rent'      => $nullIfBlank($post['monthly_rent'] ?? null),
                'deposit'           => $nullIfBlank($post['deposit'] ?? null),
                'monthly_utilities' => $nullIfBlank($post['monthly_utilities'] ?? null),
                'specialties'       => $nullIfBlank($post['specialties'] ?? null),
            ];

            // Strip nulls so we don't insert NULL into NOT NULL columns elsewhere
            $propertyData = array_filter($propertyData, function ($v) { return $v !== null; });

            $propertyId = $propertyModel->insert($propertyData);

            if (!$propertyId) {
                $this->session->setFlash('error', 'Failed to create property record');
                $this->redirect('/tours/create');
            }

            // 2. Create the Tour
            $tourModel = new Tour();
            $slug = $tourModel->generateSlug($placeName);
            $tourId = $tourModel->insert([
                'property_id' => $propertyId,
                'title'       => $placeName,
                'slug'        => $slug,
                'description' => null,
                'status'      => 'published',
                'is_public'   => 1,
                'is_featured' => 0,
            ]);

            if (!$tourId) {
                $this->session->setFlash('error', 'Failed to create tour');
                $this->redirect('/tours/create');
            }

            // 3. Create a Scene for each uploaded image
            $sceneModel = new Scene();
            $createdSceneIds = [];
            $sortOrder = 1;

            foreach ($uploadedFiles as $idx => $fileArr) {
                $imagePath = null;

                // Prefer Supabase Storage when configured (persistent, CDN-served)
                if (SupabaseStorage::isEnabled()) {
                    $imagePath = SupabaseStorage::uploadFile($fileArr, 'scenes');
                }

                // Fall back to local filesystem upload
                if (!$imagePath) {
                    $upload = new Upload($fileArr);
                    $upload->setAllowedTypes(config('allowed_image_types', ['jpg', 'jpeg', 'png', 'webp', 'image/jpeg', 'image/png', 'image/webp']))
                           ->setMaxSize(config('max_upload_size', 52428800))
                           ->setUploadPath(storagePath('uploads/scenes'));
                    $imagePath = $upload->upload();
                }

                if (!$imagePath) {
                    // Skip failed uploads but continue with the rest
                    continue;
                }

                // Generate scene name from filename or ordinal
                $baseName = pathinfo($fileArr['name'], PATHINFO_FILENAME);
                $baseName = preg_replace('/[_\-]+/', ' ', $baseName);
                $baseName = trim($baseName);
                $sceneName = $baseName ?: ($placeName . ' — Room ' . $sortOrder);

                $sceneId = $sceneModel->create([
                    'tour_id'       => $tourId,
                    'name'          => $sceneName,
                    'image_path'    => $imagePath,
                    'initial_yaw'   => 0,
                    'initial_pitch' => 0,
                    'sort_order'    => $sortOrder,
                ]);

                if ($sceneId) {
                    $createdSceneIds[] = $sceneId;
                    $sortOrder++;
                }
            }

            if (empty($createdSceneIds)) {
                $this->session->setFlash('error', 'Image upload failed. Please try again with smaller files.');
                $this->redirect('/tours/create');
            }

            // 4. Auto-create navigation hotspots between consecutive scenes
            if (count($createdSceneIds) > 1) {
                $this->createNavigationHotspots($createdSceneIds);
            }

            $this->session->setFlash('success', 'Walkthrough created with ' . count($createdSceneIds) . ' scenes!');
            $this->redirect('/tours/' . $tourId);

        } else {
            // ---- Legacy flow (old form with title + property_id) ----
            $validator = new Validator($post);
            $validator->required('title')
                      ->required('property_id')
                      ->required('status');

            if ($validator->fails()) {
                $this->session->set('old_input', $post);
                $this->session->setFlash('error', $validator->first());
                $this->redirect('/tours/create');
            }

            $tourModel = new Tour();
            $slug = $tourModel->generateSlug($post['title']);

            $tourData = [
                'property_id' => $post['property_id'],
                'title'       => $post['title'],
                'slug'        => $slug,
                'description' => $post['description'] ?? null,
                'status'      => $post['status'],
                'is_public'   => isset($post['is_public']) ? 1 : 0,
                'is_featured' => isset($post['is_featured']) ? 1 : 0
            ];

            $tourId = $tourModel->insert($tourData);

            if ($tourId) {
                $this->session->setFlash('success', 'Tour created successfully');
                $this->redirect('/tours/' . $tourId);
            } else {
                $this->session->set('old_input', $post);
                $this->session->setFlash('error', 'Failed to create tour');
                $this->redirect('/tours/create');
            }
        }
    }

    /**
     * Reconstruct $_FILES multi-upload into array of individual file arrays
     *
     * @param string $fieldName The name attribute used for the file input
     * @return array Array of individual $_FILES-style arrays
     */
    private function collectMultipleFiles($fieldName) {
        if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName]['name'])) {
            // Single file fallback
            if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
                return [$_FILES[$fieldName]];
            }
            return [];
        }

        $result = [];
        $count = count($_FILES[$fieldName]['name']);

        for ($i = 0; $i < $count; $i++) {
            if ($_FILES[$fieldName]['error'][$i] === UPLOAD_ERR_OK) {
                $result[] = [
                    'name'     => $_FILES[$fieldName]['name'][$i],
                    'type'     => $_FILES[$fieldName]['type'][$i],
                    'tmp_name' => $_FILES[$fieldName]['tmp_name'][$i],
                    'error'    => $_FILES[$fieldName]['error'][$i],
                    'size'     => $_FILES[$fieldName]['size'][$i],
                ];
            }
        }

        return $result;
    }

    /**
     * Auto-create forward/back navigation hotspots between an ordered list of scene IDs
     *
     * @param array $sceneIds Ordered array of scene IDs
     */
    private function createNavigationHotspots(array $sceneIds) {
        $hotspotModel = new Hotspot();
        $total = count($sceneIds);

        for ($i = 0; $i < $total; $i++) {
            $currentId = $sceneIds[$i];

            // Forward hotspot → next scene (yaw 0° = straight ahead)
            if ($i < $total - 1) {
                $nextId = $sceneIds[$i + 1];
                $hotspotModel->create([
                    'scene_id'        => $currentId,
                    'type'            => 'navigation',
                    'yaw'             => 0,
                    'pitch'           => -5,
                    'label'           => 'Next room',
                    'description'     => null,
                    'target_scene_id' => $nextId,
                    'external_url'    => null,
                    'icon_type'       => 'arrow',
                ]);
            }

            // Backward hotspot → previous scene (yaw 180° = behind)
            if ($i > 0) {
                $prevId = $sceneIds[$i - 1];
                $hotspotModel->create([
                    'scene_id'        => $currentId,
                    'type'            => 'navigation',
                    'yaw'             => 180,
                    'pitch'           => -5,
                    'label'           => 'Previous room',
                    'description'     => null,
                    'target_scene_id' => $prevId,
                    'external_url'    => null,
                    'icon_type'       => 'arrow',
                ]);
            }
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
            'property_id' => $data['property_id'] ?? $tour['property_id'],
            'title'       => $data['title'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'status'      => $data['status'],
            'is_public'   => isset($data['is_public']) ? 1 : 0,
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
     * Show public tour viewer
     */
    public function viewPublic($slug) {
        $tourModel = new Tour();
        $tour = $tourModel->getPublicTourWithScenes($slug);

        if (!$tour) {
            http_response_code(404);
            echo "Tour not found";
            exit;
        }

        // Enrich scenes with full image URLs for the viewer.
        // If image_path is already a full URL (Supabase), use as-is.
        foreach ($tour['scenes'] as &$scene) {
            if (strncmp($scene['image_path'], 'http', 4) === 0) {
                $scene['image_url'] = $scene['image_path'];
            } else {
                $scene['image_url'] = url('storage/uploads/scenes/' . ltrim($scene['image_path'], '/'));
            }
        }
        unset($scene);

        // Record view
        $tourViewModel = new TourView();
        $tourViewModel->recordView($tour['id']);

        $data = ['tour' => $tour];

        $this->view('public/tour', $data, 'public');
    }
}
