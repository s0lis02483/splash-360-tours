<?php
// FILE: /app/controllers/DashboardController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/TourView.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/UsageTracker.php';

/**
 * DashboardController
 *
 * Handles dashboard display and statistics
 */
class DashboardController extends Controller {

    /**
     * Show dashboard
     */
    public function index() {
        $this->requireAuth();

        $user = $this->auth->user();
        $tenantId = $user['tenant_id'];

        // Get statistics
        $propertyModel = new Property();
        $tourModel = new Tour();
        $tourViewModel = new TourView();
        $usageTrackerModel = new UsageTracker();

        $stats = [
            'properties_count' => $propertyModel->count(),
            'tours_count' => $tourModel->count(),
            'total_views' => $tourViewModel->getTotalByTenant($tenantId),
        ];

        // Get recent tours
        $recentTours = $tourModel->getWithDetails([], 5, 0);

        // Get featured tours
        $featuredTours = $tourModel->getFeatured(5);

        // Get top viewed tours
        $topViewedTours = $tourViewModel->getTopViewedByTenant($tenantId, 5);

        // Get usage with limits
        $usageData = $usageTrackerModel->getUsageWithLimits($tenantId);

        $data = [
            'user' => $user,
            'stats' => $stats,
            'recent_tours' => $recentTours,
            'featured_tours' => $featuredTours,
            'top_viewed_tours' => $topViewedTours,
            'usage_data' => $usageData
        ];

        $this->view('dashboard/index', $data);
    }
}
