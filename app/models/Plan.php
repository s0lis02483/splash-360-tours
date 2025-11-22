<?php
// FILE: /app/models/Plan.php

/**
 * Plan Model
 *
 * Handles subscription plan management
 */
class Plan extends Model {
    protected $table = 'plans';
    protected $tenantIsolation = false; // Plans are global

    /**
     * Get all active plans
     *
     * @return array
     */
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY price ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get plan by ID
     *
     * @param int $planId Plan ID
     * @return array|false
     */
    public function getById($planId) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$planId]);
        return $stmt->fetch();
    }

    /**
     * Get plan limits
     *
     * @param int $planId Plan ID
     * @return array
     */
    public function getLimits($planId) {
        $plan = $this->getById($planId);

        if (!$plan) {
            return [];
        }

        return [
            'max_properties' => $plan['max_properties'],
            'max_tours' => $plan['max_tours'],
            'max_scenes' => $plan['max_scenes'],
            'max_storage_size' => $plan['max_storage_size']
        ];
    }
}
