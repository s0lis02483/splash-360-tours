<?php
// FILE: /app/models/UsageTracker.php

/**
 * UsageTracker Model
 *
 * Tracks tenant usage against subscription limits
 */
class UsageTracker extends Model {
    protected $tenantIsolation = false;

    /**
     * Get current usage for tenant
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getCurrentUsage($tenantId) {
        $usage = [];

        // Count properties
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM properties WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $usage['properties'] = $stmt->fetch()['count'];

        // Count tours
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM tours WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $usage['tours'] = $stmt->fetch()['count'];

        // Count scenes
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM scenes s
                                    INNER JOIN tours t ON s.tour_id = t.id
                                    WHERE t.tenant_id = ?");
        $stmt->execute([$tenantId]);
        $usage['scenes'] = $stmt->fetch()['count'];

        // Calculate storage size (approximate sum of file sizes)
        $usage['storage'] = $this->calculateStorageUsage($tenantId);

        return $usage;
    }

    /**
     * Calculate storage usage for tenant
     *
     * @param int $tenantId Tenant ID
     * @return int Storage size in bytes
     */
    private function calculateStorageUsage($tenantId) {
        // Storage tracking not available in serverless environment
        return 0;
    }

    /**
     * Check if tenant has reached limit
     *
     * @param int $tenantId Tenant ID
     * @param string $resource Resource type (properties, tours, scenes, storage)
     * @return array Result with allowed status and details
     */
    public function checkLimit($tenantId, $resource) {
        // Get tenant subscription and plan limits
        $subscriptionModel = new Subscription();
        $subscription = $subscriptionModel->getActiveByTenant($tenantId);

        if (!$subscription) {
            return [
                'allowed' => false,
                'message' => 'No active subscription found'
            ];
        }

        // Get current usage
        $usage = $this->getCurrentUsage($tenantId);

        // Check specific resource limit
        $limitKey = 'max_' . $resource;
        $limit = $subscription[$limitKey] ?? null;

        if ($limit === null || $limit === -1) {
            // Unlimited
            return ['allowed' => true];
        }

        $current = $usage[$resource] ?? 0;

        if ($resource === 'storage') {
            // Storage is in bytes
            $currentMB = $current / 1048576; // Convert to MB
            $allowed = $currentMB < $limit;

            return [
                'allowed' => $allowed,
                'current' => $currentMB,
                'limit' => $limit,
                'message' => $allowed ? '' : "Storage limit reached. Current: " . round($currentMB, 2) . "MB, Limit: {$limit}MB"
            ];
        }

        $allowed = $current < $limit;

        return [
            'allowed' => $allowed,
            'current' => $current,
            'limit' => $limit,
            'message' => $allowed ? '' : ucfirst($resource) . " limit reached. Current: $current, Limit: $limit"
        ];
    }

    /**
     * Get usage with limits for tenant
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getUsageWithLimits($tenantId) {
        $subscriptionModel = new Subscription();
        $subscription = $subscriptionModel->getActiveByTenant($tenantId);

        $usage = $this->getCurrentUsage($tenantId);

        if (!$subscription) {
            return [
                'usage' => $usage,
                'limits' => [],
                'subscription' => null
            ];
        }

        $limits = [
            'properties' => $subscription['max_properties'] ?? 0,
            'tours' => $subscription['max_tours'] ?? 0,
            'scenes' => $subscription['max_scenes_per_tour'] ?? 0,
            'storage' => $subscription['max_storage_gb'] ?? 0
        ];

        // Calculate percentages
        $percentages = [];
        foreach ($limits as $key => $limit) {
            if ($limit == -1) {
                $percentages[$key] = 0; // Unlimited
            } else if ($limit > 0) {
                $current = $usage[$key] ?? 0;
                if ($key === 'storage') {
                    $current = $current / 1048576; // Convert to MB
                }
                $percentages[$key] = round(($current / $limit) * 100, 2);
            } else {
                $percentages[$key] = 0;
            }
        }

        return [
            'usage' => $usage,
            'limits' => $limits,
            'percentages' => $percentages,
            'subscription' => $subscription
        ];
    }
}
