<?php
// FILE: /app/models/Hotspot.php

/**
 * Hotspot Model
 *
 * Handles interactive hotspot management for 360° scenes
 */
class Hotspot extends Model {
    protected $table = 'hotspots';
    protected $tenantIsolation = false; // Hotspots are linked to scenes/tours which handle tenant isolation

    /**
     * Get hotspots by scene ID
     *
     * @param int $sceneId Scene ID
     * @return array
     */
    public function getBySceneId($sceneId) {
        $sql = "SELECT * FROM {$this->table} WHERE scene_id = ? ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sceneId]);
        return $stmt->fetchAll();
    }

    /**
     * Create hotspot without tenant isolation
     *
     * @param array $data Hotspot data
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
     * Update hotspot by ID
     *
     * @param int $id Hotspot ID
     * @param array $data Data to update
     * @return bool
     */
    public function updateHotspot($id, $data) {
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
     * Delete hotspot by ID with tenant verification
     *
     * @param int $hotspotId Hotspot ID
     * @return bool
     */
    public function deleteHotspot($hotspotId) {
        $auth = new Auth();
        $tenantId = $auth->tenantId();

        $sql = "DELETE FROM {$this->table}
                WHERE id = ?
                AND scene_id IN (
                    SELECT s.id FROM scenes s
                    INNER JOIN tours t ON s.tour_id = t.id
                    WHERE t.tenant_id = ?
                )";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$hotspotId, $tenantId]);
    }

    /**
     * Get hotspot by ID with tenant verification
     *
     * @param int $hotspotId Hotspot ID
     * @return array|false
     */
    public function getById($hotspotId) {
        $auth = new Auth();
        $tenantId = $auth->tenantId();

        $sql = "SELECT h.* FROM {$this->table} h
                INNER JOIN scenes s ON h.scene_id = s.id
                INNER JOIN tours t ON s.tour_id = t.id
                WHERE h.id = ? AND t.tenant_id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hotspotId, $tenantId]);
        return $stmt->fetch();
    }

    /**
     * Get hotspot types
     *
     * @return array
     */
    public function getTypes() {
        return [
            'navigation' => 'Navigation (Jump to scene)',
            'info' => 'Info (Show text)',
            'link' => 'Link (External URL)'
        ];
    }

    /**
     * Get hotspot icon types
     *
     * @return array
     */
    public function getIconTypes() {
        return [
            'arrow' => 'Arrow',
            'info' => 'Info',
            'link' => 'Link',
            'custom' => 'Custom'
        ];
    }
}
