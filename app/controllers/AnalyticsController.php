<?php
// FILE: /app/controllers/AnalyticsController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/TourView.php';
require_once __DIR__ . '/../models/Tour.php';

/**
 * AnalyticsController
 *
 * Handles analytics and reporting
 */
class AnalyticsController extends Controller {

    /**
     * Show analytics dashboard
     */
    public function index() {
        $this->requireAuth();

        $tenantId = $this->auth->tenantId();
        $tourViewModel = new TourView();

        // Get date range from request or default to last 30 days
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        $startDate = $this->request->get('start_date', date('Y-m-d', strtotime('-30 days')));

        // Get total views
        $totalViews = $tourViewModel->getTotalByTenant($tenantId);

        // Get top viewed tours
        $topViewedTours = $tourViewModel->getTopViewedByTenant($tenantId, 10);

        // Get views by date range
        $viewsByDate = $tourViewModel->getStatsByDateRange($tenantId, $startDate, $endDate);

        $data = [
            'total_views' => $totalViews,
            'top_viewed_tours' => $topViewedTours,
            'views_by_date' => $viewsByDate,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $this->view('analytics/index', $data);
    }
}
