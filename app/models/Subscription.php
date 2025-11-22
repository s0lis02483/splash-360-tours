<?php
// FILE: /app/models/Subscription.php

/**
 * Subscription Model
 *
 * Handles tenant subscription management
 */
class Subscription extends Model {
    protected $table = 'tenant_subscriptions';
    protected $tenantIsolation = false; // We handle tenant context manually

    /**
     * Get active subscription for tenant
     *
     * @param int $tenantId Tenant ID
     * @return array|false
     */
    public function getActiveBytenant($tenantId) {
        $sql = "SELECT s.*, p.name as plan_name, p.max_properties, p.max_tours,
                       p.max_scenes, p.max_storage_size
                FROM {$this->table} s
                INNER JOIN plans p ON s.plan_id = p.id
                WHERE s.tenant_id = ? AND s.status = 'active'
                ORDER BY s.created_at DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetch();
    }

    /**
     * Get subscription history for tenant
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getHistory($tenantId) {
        $sql = "SELECT s.*, p.name as plan_name
                FROM {$this->table} s
                INNER JOIN plans p ON s.plan_id = p.id
                WHERE s.tenant_id = ?
                ORDER BY s.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Create new subscription
     *
     * @param array $data Subscription data
     * @return int|false
     */
    public function create($data) {
        // Deactivate any existing active subscriptions for this tenant
        if (isset($data['tenant_id'])) {
            $sql = "UPDATE {$this->table} SET status = 'expired' WHERE tenant_id = ? AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['tenant_id']]);
        }

        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $fields = array_keys($data);
        $values = array_values($data);

        $fieldsList = implode(', ', $fields);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO {$this->table} ($fieldsList) VALUES ($placeholders)";

        $stmt = $this->db->prepare($sql);

        if ($stmt->execute($values)) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Check if subscription is expired
     *
     * @param int $tenantId Tenant ID
     * @return bool
     */
    public function isExpired($tenantId) {
        $subscription = $this->getActiveByTenant($tenantId);

        if (!$subscription) {
            return true;
        }

        if ($subscription['expires_at'] && strtotime($subscription['expires_at']) < time()) {
            // Update status to expired
            $sql = "UPDATE {$this->table} SET status = 'expired' WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$subscription['id']]);

            return true;
        }

        return false;
    }

    /**
     * Renew subscription
     *
     * @param int $subscriptionId Subscription ID
     * @param string $expiresAt New expiry date
     * @return bool
     */
    public function renew($subscriptionId, $expiresAt) {
        $sql = "UPDATE {$this->table} SET expires_at = ?, status = 'active', updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$expiresAt, $subscriptionId]);
    }
}
