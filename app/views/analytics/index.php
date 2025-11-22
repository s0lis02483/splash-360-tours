<?php // FILE: /app/views/analytics/index.php ?>
<h1>Analytics</h1>
<div class="stat-card"><h3>Total Views</h3><p class="stat-number"><?php echo $total_views; ?></p></div>
<h2>Top Viewed Tours</h2>
<table class="data-table">
    <thead><tr><th>Tour</th><th>Views</th></tr></thead>
    <tbody><?php foreach ($top_viewed_tours as $t): ?><tr><td><?php echo e($t['title']); ?></td><td><?php echo $t['view_count']; ?></td></tr><?php endforeach; ?></tbody>
</table>
<h2>Views Over Time</h2>
<?php foreach ($views_by_date as $v): ?><p><?php echo $v['date']; ?>: <?php echo $v['count']; ?> views</p><?php endforeach; ?>
