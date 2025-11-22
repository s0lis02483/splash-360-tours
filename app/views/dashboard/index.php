<?php
// FILE: /app/views/dashboard/index.php
?>
<div class="dashboard">
    <h1>Dashboard</h1>
    <p>Welcome back, <?php echo e($user['name']); ?>!</p>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Properties</h3>
            <p class="stat-number"><?php echo $stats['properties_count']; ?></p>
            <a href="<?php echo url('/properties'); ?>" class="btn btn-sm">View All</a>
        </div>

        <div class="stat-card">
            <h3>Tours</h3>
            <p class="stat-number"><?php echo $stats['tours_count']; ?></p>
            <a href="<?php echo url('/tours'); ?>" class="btn btn-sm">View All</a>
        </div>

        <div class="stat-card">
            <h3>Total Views</h3>
            <p class="stat-number"><?php echo $stats['total_views']; ?></p>
            <a href="<?php echo url('/analytics'); ?>" class="btn btn-sm">View Analytics</a>
        </div>
    </div>

    <!-- Usage Limits -->
    <?php if (isset($usage_data['subscription'])): ?>
    <div class="usage-section">
        <h2>Plan Usage</h2>
        <p>Current Plan: <strong><?php echo e($usage_data['subscription']['plan_name']); ?></strong></p>

        <div class="usage-bars">
            <div class="usage-item">
                <label>Properties: <?php echo $usage_data['usage']['properties']; ?> / <?php echo $usage_data['limits']['properties'] == -1 ? 'Unlimited' : $usage_data['limits']['properties']; ?></label>
                <?php if ($usage_data['limits']['properties'] != -1): ?>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo min($usage_data['percentages']['properties'], 100); ?>%"></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="usage-item">
                <label>Tours: <?php echo $usage_data['usage']['tours']; ?> / <?php echo $usage_data['limits']['tours'] == -1 ? 'Unlimited' : $usage_data['limits']['tours']; ?></label>
                <?php if ($usage_data['limits']['tours'] != -1): ?>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo min($usage_data['percentages']['tours'], 100); ?>%"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <a href="<?php echo url('/subscriptions'); ?>" class="btn">Manage Subscription</a>
    </div>
    <?php endif; ?>

    <!-- Recent Tours -->
    <div class="recent-section">
        <h2>Recent Tours</h2>
        <?php if (!empty($recent_tours)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Property</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_tours as $tour): ?>
                <tr>
                    <td><?php echo e($tour['title']); ?></td>
                    <td><?php echo e($tour['property_title']); ?></td>
                    <td><span class="badge badge-<?php echo $tour['status']; ?>"><?php echo ucfirst($tour['status']); ?></span></td>
                    <td><?php echo $tour['view_count']; ?></td>
                    <td>
                        <a href="<?php echo url('/tours/' . $tour['id']); ?>" class="btn btn-sm">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No tours yet. <a href="<?php echo url('/tours/create'); ?>">Create your first tour</a></p>
        <?php endif; ?>
    </div>
</div>
