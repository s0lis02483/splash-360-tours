<?php
// FILE: /app/models/TourView.php

/**
 * TourView Model
 *
 * Handles tour view tracking for analytics
 */
class TourView extends Model {
    protected $table = 'tour_views';
    protected $tenantIsolation = false; // Views are tracked globally

    /**
     * Record tour view
     *
     * @param int $tourId Tour ID
     * @param string $ipAddress Visitor IP address
     * @param string $userAgent Visitor user agent
     * @return int|false
     */
    public function recordView($tourId, $ipAddress = null, $userAgent = null) {
        $ipAddress = $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $data = [
            'tour_id' => $tourId,
            'ip_address' => $ipAddress,
            'user_agent' => substr($userAgent, 0, 255),
            'viewed_at' => date('Y-m-d H:i:s')
        ];

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
     * Get views by tour
     *
     * @param int $tourId Tour ID
     * @param int $limit Limit
     * @return array
     */
    public function getByTour($tourId, $limit = 100) {
        $sql = "SELECT * FROM {$this->table} WHERE tour_id = ? ORDER BY viewed_at DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tourId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get view count by tour
     *
     * @param int $tourId Tour ID
     * @return int
     */
    public function getCountByTour($tourId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE tour_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tourId]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Get top viewed tours for tenant
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @return array
     */
    public function getTopViewedByTenant($tenantId, $limit = 10) {
        $sql = "SELECT t.id, t.title, t.slug, COUNT(tv.id) as view_count
                FROM tours t
                LEFT JOIN {$this->table} tv ON t.id = tv.tour_id
                WHERE t.tenant_id = ?
                GROUP BY t.id
                ORDER BY view_count DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get view statistics by date range for tenant
     *
     * @param int $tenantId Tenant ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array
     */
    public function getStatsByDateRange($tenantId, $startDate, $endDate) {
        $sql = "SELECT DATE(tv.viewed_at) as date, COUNT(*) as count
                FROM {$this->table} tv
                INNER JOIN tours t ON tv.tour_id = t.id
                WHERE t.tenant_id = ? AND tv.viewed_at BETWEEN ? AND ?
                GROUP BY DATE(tv.viewed_at)
                ORDER BY date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }

    /**
     * Get total views for tenant
     *
     * @param int $tenantId Tenant ID
     * @return int
     */
    public function getTotalByTenant($tenantId) {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} tv
                INNER JOIN tours t ON tv.tour_id = t.id
                WHERE t.tenant_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }
}
