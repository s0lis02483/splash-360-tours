<?php
// FILE: /app/models/Scene.php

/**
 * Scene Model
 *
 * Handles 360° panoramic scene management
 */
class Scene extends Model {
    protected $table = 'scenes';
    protected $tenantIsolation = false; // Scenes are linked to tours which handle tenant isolation

    /**
     * Get scenes by tour ID
     *
     * @param int $tourId Tour ID
     * @return array
     */
    public function getByTourId($tourId) {
        // First verify tour belongs to current tenant
        $auth = new Auth();
        $tenantId = $auth->tenantId();

        $sql = "SELECT s.* FROM {$this->table} s
                INNER JOIN tours t ON s.tour_id = t.id
                WHERE s.tour_id = ? AND t.tenant_id = ?
                ORDER BY s.order_index ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tourId, $tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Get scene with hotspots
     *
     * @param int $sceneId Scene ID
     * @return array|false
     */
    public function getWithHotspots($sceneId) {
        $auth = new Auth();
        $tenantId = $auth->tenantId();

        // Get scene with tenant verification
        $sql = "SELECT s.* FROM {$this->table} s
                INNER JOIN tours t ON s.tour_id = t.id
                WHERE s.id = ? AND t.tenant_id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sceneId, $tenantId]);
        $scene = $stmt->fetch();

        if (!$scene) {
            return false;
        }

        // Get hotspots
        $sql = "SELECT * FROM hotspots WHERE scene_id = ? ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sceneId]);
        $scene['hotspots'] = $stmt->fetchAll();

        return $scene;
    }

    /**
     * Get next order index for tour
     *
     * @param int $tourId Tour ID
     * @return int
     */
    public function getNextOrderIndex($tourId) {
        $sql = "SELECT MAX(order_index) as max_order FROM {$this->table} WHERE tour_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tourId]);
        $result = $stmt->fetch();

        return ($result['max_order'] ?? 0) + 1;
    }

    /**
     * Reorder scenes
     *
     * @param array $sceneIds Array of scene IDs in new order
     * @return bool
     */
    public function reorder($sceneIds) {
        $this->db->beginTransaction();

        try {
            $orderIndex = 1;
            foreach ($sceneIds as $sceneId) {
                $sql = "UPDATE {$this->table} SET order_index = ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$orderIndex, $sceneId]);
                $orderIndex++;
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Insert scene without tenant isolation
     *
     * @param array $data Scene data
     * @return int|false
     */
    public function create($data) {
        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $fields = array_keys($data);
        $values = array_values($data);

        $fieldsList = implode(', ', $fields);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO {$this->table} ($fieldsList) VALUES ($placeholders) RETURNING id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : false;
    }

    /**
     * Update scene by ID
     *
     * @param int $id Scene ID
     * @param array $data Data to update
     * @return bool
     */
    public function updateScene($id, $data) {
        // Add updated_at timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete scene by ID with tenant verification
     *
     * @param int $sceneId Scene ID
     * @return bool
     */
    public function deleteScene($sceneId) {
        $auth = new Auth();
        $tenantId = $auth->tenantId();

        // Delete scene with tenant verification
        $sql = "DELETE s FROM {$this->table} s
                INNER JOIN tours t ON s.tour_id = t.id
                WHERE s.id = ? AND t.tenant_id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$sceneId, $tenantId]);
    }
}
