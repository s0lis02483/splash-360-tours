<?php
// FILE: /app/controllers/ApiController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/SupabaseStorage.php';
require_once __DIR__ . '/../models/Tenant.php';
require_once __DIR__ . '/../models/Tour.php';

/**
 * ApiController
 *
 * Handles public API endpoints for tour embedding
 */
class ApiController extends Controller {

    /**
     * Verify API key and get tenant
     *
     * @return array|false Tenant data or false
     */
    private function verifyApiKey() {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;

        if (!$apiKey) {
            return false;
        }

        $tenantModel = new Tenant();
        return $tenantModel->findByApiKey($apiKey);
    }

    /**
     * List published tours for a tenant
     */
    public function tours() {
        $tenant = $this->verifyApiKey();

        if (!$tenant) {
            $this->json([
                'success' => false,
                'error' => 'Invalid or missing API key'
            ], 401);
            return;
        }

        $tourModel = new Tour();

        $db = Database::getInstance()->getConnection();
        $sql = "SELECT id, title, slug, description, created_at
                FROM tours
                WHERE tenant_id = ? AND status = 'published' AND is_public = 1
                ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tenant['id']]);
        $tours = $stmt->fetchAll();

        $this->json([
            'success' => true,
            'data' => $tours
        ]);
    }

    /**
     * Get single tour with scenes and hotspots
     */
    public function tour($slug) {
        $tenant = $this->verifyApiKey();

        if (!$tenant) {
            $this->json([
                'success' => false,
                'error' => 'Invalid or missing API key'
            ], 401);
            return;
        }

        $db = Database::getInstance()->getConnection();

        // Get tour
        $sql = "SELECT t.id, t.title, t.slug, t.description, t.created_at,
                       p.title as property_title, p.location, p.price
                FROM tours t
                LEFT JOIN properties p ON t.property_id = p.id
                WHERE t.slug = ? AND t.tenant_id = ? AND t.status = 'published' AND t.is_public = 1
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([$slug, $tenant['id']]);
        $tour = $stmt->fetch();

        if (!$tour) {
            $this->json([
                'success' => false,
                'error' => 'Tour not found'
            ], 404);
            return;
        }

        // Get scenes
        $sql = "SELECT id, name, image_path, initial_yaw, initial_pitch, order_index
                FROM scenes
                WHERE tour_id = ?
                ORDER BY order_index ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tour['id']]);
        $scenes = $stmt->fetchAll();

        // Get hotspots for each scene
        foreach ($scenes as &$scene) {
            $sql = "SELECT id, type, yaw, pitch, label, description, target_scene_id, external_url, icon_type
                    FROM hotspots
                    WHERE scene_id = ?
                    ORDER BY id ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute([$scene['id']]);
            $scene['hotspots'] = $stmt->fetchAll();

            // Add full image URL
            $scene['image_url'] = url('storage/uploads/scenes/' . $scene['image_path']);
        }

        $tour['scenes'] = $scenes;

        $this->json([
            'success' => true,
            'data' => $tour
        ]);
    }

    /**
     * Health check endpoint
     */
    public function health() {
        $this->json([
            'success' => true,
            'message' => 'API is running',
            'version' => '1.0.0'
        ]);
    }

    /**
     * Issue a signed Supabase upload URL so the browser can PUT a single
     * file directly to storage, bypassing Vercel's 4.5MB request cap.
     *
     * POST /api/storage/sign-upload
     *   body: { filename, folder? }   (JSON or form-urlencoded)
     *   returns: { signedUploadUrl, publicUrl, path }
     *
     * Requires the user to be logged in (no public uploads).
     */
    public function signUpload() {
        // Only POST
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            $this->json(['error' => 'Method not allowed']);
            return;
        }

        // Must be authenticated — protects the bucket from random uploads
        if (!$this->auth->check()) {
            http_response_code(401);
            $this->json(['error' => 'Login required']);
            return;
        }

        if (!SupabaseStorage::isEnabled()) {
            http_response_code(500);
            $this->json(['error' => 'Supabase Storage is not configured on this server']);
            return;
        }

        // Accept both JSON body and form-urlencoded
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) $body = $_POST;

        $filename = trim((string)($body['filename'] ?? ''));
        $folder   = trim((string)($body['folder']   ?? 'scenes'));

        if ($filename === '') {
            http_response_code(400);
            $this->json(['error' => 'filename is required']);
            return;
        }

        // Restrict folder to a small whitelist to prevent path tricks
        $allowedFolders = ['scenes', 'properties', 'gallery'];
        if (!in_array($folder, $allowedFolders, true)) {
            $folder = 'scenes';
        }

        $signed = SupabaseStorage::createSignedUploadUrl($filename, $folder);

        if (!$signed) {
            http_response_code(500);
            $this->json(['error' => 'Failed to create signed upload URL']);
            return;
        }

        $this->json([
            'signedUploadUrl' => $signed['signedUploadUrl'],
            'publicUrl'       => $signed['publicUrl'],
            'path'            => $signed['path'],
        ]);
    }
}
