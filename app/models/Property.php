<?php
// FILE: /app/models/Property.php

/**
 * Property Model
 *
 * Handles real estate property management
 */
class Property extends Model {
    protected $table = 'properties';

    /**
     * Get properties with tour count
     *
     * @param array $filters Filters (status, type, search)
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getWithTourCount($filters = [], $limit = 20, $offset = 0) {
        $tenantId = $this->getTenantId();

        $sql = "SELECT p.*, COUNT(t.id) as tour_count
                FROM {$this->table} p
                LEFT JOIN tours t ON p.id = t.property_id
                WHERE p.tenant_id = ?";

        $params = [$tenantId];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND p.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name ILIKE ? OR p.address ILIKE ? OR p.city ILIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get property with tours
     *
     * @param int $propertyId Property ID
     * @return array|false
     */
    public function getWithTours($propertyId) {
        $tenantId = $this->getTenantId();

        // Get property
        $property = $this->findById($propertyId);

        if (!$property) {
            return false;
        }

        // Get tours for this property
        $sql = "SELECT * FROM tours WHERE property_id = ? AND tenant_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propertyId, $tenantId]);
        $property['tours'] = $stmt->fetchAll();

        return $property;
    }

    /**
     * Count properties with filters
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

        if (!empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (name ILIKE ? OR address ILIKE ? OR city ILIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Get property types
     *
     * @return array
     */
    public function getTypes() {
        return [
            'apartment' => 'Apartment',
            'house' => 'House',
            'villa' => 'Villa',
            'office' => 'Office',
            'land' => 'Land',
            'commercial' => 'Commercial',
            'other' => 'Other'
        ];
    }

    /**
     * Get property statuses
     *
     * @return array
     */
    public function getStatuses() {
        return [
            'available' => 'Available',
            'sold' => 'Sold',
            'rented' => 'Rented',
            'pending' => 'Pending'
        ];
    }
}
