<?php
// FILE: /app/models/Tour.php

/**
 * Tour Model
 *
 * Handles 360° virtual tour management
 */
class Tour extends Model {
    protected $table = 'tours';

    /**
     * Get tours with property and scene count
     *
     * @param array $filters Filters (status, property_id, search)
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getWithDetails($filters = [], $limit = 20, $offset = 0) {
        $tenantId = $this->getTenantId();

        $sql = "SELECT t.*, p.title as property_title, p.reference as property_reference,
                       COUNT(DISTINCT s.id) as scene_count,
                       COUNT(DISTINCT tv.id) as view_count
                FROM {$this->table} t
                LEFT JOIN properties p ON t.property_id = p.id
                LEFT JOIN scenes s ON t.id = s.tour_id
                LEFT JOIN tour_views tv ON t.id = tv.tour_id
                WHERE t.tenant_id = ?";

        $params = [$tenantId];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['property_id'])) {
            $sql .= " AND t.property_id = ?";
            $params[] = $filters['property_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE ? OR t.slug LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " GROUP BY t.id ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get tour by slug for public viewing
     *
     * @param string $slug Tour slug
     * @return array|false
     */
    public function getBySlug($slug) {
        $sql = "SELECT t.*, p.title as property_title, p.type as property_type,
                       p.location as property_location, p.price as property_price,
                       p.description as property_description
                FROM {$this->table} t
                LEFT JOIN properties p ON t.property_id = p.id
                WHERE t.slug = ? AND t.status = 'published' AND t.is_public = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    /**
     * Get tour with scenes and hotspots
     *
     * @param int $tourId Tour ID
     * @return array|false
     */
    public function getWithScenes($tourId) {
        $tenantId = $this->getTenantId();

        // Get tour
        $tour = $this->findById($tourId);

        if (!$tour) {
            return false;
        }

        // Get scenes
        $sql = "SELECT s.* FROM scenes s
                INNER JOIN tours t ON s.tour_id = t.id
                WHERE s.tour_id = ? AND t.tenant_id = ?
                ORDER BY s.order_index ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tourId, $tenantId]);
        $scenes = $stmt->fetchAll();

        // Get hotspots for each scene
        foreach ($scenes as &$scene) {
            $sql = "SELECT * FROM hotspots WHERE scene_id = ? ORDER BY id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$scene['id']]);
            $scene['hotspots'] = $stmt->fetchAll();
        }

        $tour['scenes'] = $scenes;

        return $tour;
    }

    /**
     * Get tour with scenes for public view (no tenant isolation)
     *
     * @param string $slug Tour slug
     * @return array|false
     */
    public function getPublicTourWithScenes($slug) {
        // Get tour
        $tour = $this->getBySlug($slug);

        if (!$tour) {
            return false;
        }

        // Get scenes
        $sql = "SELECT * FROM scenes WHERE tour_id = ? ORDER BY order_index ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tour['id']]);
        $scenes = $stmt->fetchAll();

        // Get hotspots for each scene
        foreach ($scenes as &$scene) {
            $sql = "SELECT * FROM hotspots WHERE scene_id = ? ORDER BY id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$scene['id']]);
            $scene['hotspots'] = $stmt->fetchAll();
        }

        $tour['scenes'] = $scenes;

        return $tour;
    }

    /**
     * Generate unique slug
     *
     * @param string $title Tour title
     * @param int $excludeId Tour ID to exclude (for updates)
     * @return string
     */
    public function generateSlug($title, $excludeId = null) {
        $slug = slugify($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     *
     * @param string $slug Slug to check
     * @param int $excludeId Tour ID to exclude
     * @return bool
     */
    private function slugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = ?";
        $params = [$slug];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Count tours with filters
     *
     * @param array $filters Filters
     * @return int
     */
    public function countFiltered($filters = []) {
        $tenantId = $this->getTenantId();

        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE tenant_id = ?";
        $params = [$tenantId];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['property_id'])) {
            $sql .= " AND property_id = ?";
            $params[] = $filters['property_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE ? OR slug LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Get featured tours for tenant
     *
     * @param int $limit Limit
     * @return array
     */
    public function getFeatured($limit = 5) {
        $tenantId = $this->getTenantId();

        $sql = "SELECT t.*, p.title as property_title
                FROM {$this->table} t
                LEFT JOIN properties p ON t.property_id = p.id
                WHERE t.tenant_id = ? AND t.is_featured = 1 AND t.status = 'published'
                ORDER BY t.created_at DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }
}
