<?php
// FILE: /app/models/Tenant.php

/**
 * Tenant Model
 *
 * Handles tenant (agency) management
 */
class Tenant extends Model {
    protected $table = 'tenants';
    protected $tenantIsolation = false; // Tenants table doesn't need tenant isolation

    /**
     * Get tenant with subscription details
     *
     * @param int $tenantId Tenant ID
     * @return array|false
     */
    public function getWithSubscription($tenantId) {
        $sql = "SELECT t.*, s.plan_id, s.status as subscription_status,
                       s.started_at, s.expires_at, p.name as plan_name
                FROM {$this->table} t
                LEFT JOIN tenant_subscriptions s ON t.id = s.tenant_id AND s.status = 'active'
                LEFT JOIN plans p ON s.plan_id = p.id
                WHERE t.id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetch();
    }

    /**
     * Get all tenants with pagination
     *
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return array
     */
    public function getPaginated($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT t.*, COUNT(u.id) as user_count
                FROM {$this->table} t
                LEFT JOIN users u ON t.id = u.tenant_id
                GROUP BY t.id
                ORDER BY t.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perPage, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Generate unique API key for tenant
     *
     * @return string
     */
    public function generateApiKey() {
        return 'sk_' . bin2hex(random_bytes(32));
    }

    /**
     * Find tenant by API key
     *
     * @param string $apiKey API key
     * @return array|false
     */
    public function findByApiKey($apiKey) {
        $sql = "SELECT * FROM {$this->table} WHERE api_key = ? AND status = 'active' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$apiKey]);
        return $stmt->fetch();
    }

    /**
     * Update tenant status
     *
     * @param int $tenantId Tenant ID
     * @param string $status Status (active/inactive/suspended)
     * @return bool
     */
    public function updateStatus($tenantId, $status) {
        $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $tenantId]);
    }

    /**
     * Get tenant statistics
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getStatistics($tenantId) {
        // Get counts for various resources
        $db = $this->db;

        $stats = [];

        // Users count
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $stats['users'] = $stmt->fetch()['count'];

        // Properties count
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM properties WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $stats['properties'] = $stmt->fetch()['count'];

        // Tours count
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM tours WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $stats['tours'] = $stmt->fetch()['count'];

        // Scenes count
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM scenes s
                             INNER JOIN tours t ON s.tour_id = t.id
                             WHERE t.tenant_id = ?");
        $stmt->execute([$tenantId]);
        $stats['scenes'] = $stmt->fetch()['count'];

        // Total tour views
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM tour_views tv
                             INNER JOIN tours t ON tv.tour_id = t.id
                             WHERE t.tenant_id = ?");
        $stmt->execute([$tenantId]);
        $stats['total_views'] = $stmt->fetch()['count'];

        return $stats;
    }
}
