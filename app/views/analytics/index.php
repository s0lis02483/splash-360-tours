<?php // FILE: /app/views/analytics/index.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Analytics</span>
    <span class="sep">/</span>
    <span class="here">Overview</span>
  </div>
  <div class="top__spacer"></div>
</header>

<div class="page-body fade-up">

  <div class="metrics" style="grid-template-columns: repeat(2, 1fr);">
    <div class="metric">
      <div class="metric__label">Total tour views</div>
      <div class="metric__value"><?php echo number_format($total_views); ?></div>
    </div>
    <div class="metric">
      <div class="metric__label">Top tours tracked</div>
      <div class="metric__value"><?php echo count($top_viewed_tours); ?></div>
    </div>
  </div>

  <div class="section-header">
    <h2 class="section-title">Top <em>walkthroughs</em></h2>
  </div>

  <div class="card mb-6">
    <div class="card-body" style="padding:0;">
      <?php if (!empty($top_viewed_tours)): ?>
      <table class="data-table">
        <thead>
          <tr><th>#</th><th>Walkthrough</th><th>Views</th></tr>
        </thead>
        <tbody>
          <?php foreach ($top_viewed_tours as $i => $t): ?>
          <tr>
            <td style="font-family:var(--font-mono);font-size:11px;color:var(--ink-4);"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></td>
            <td>
              <a href="<?php echo url('/tours/' . $t['tour_id']); ?>" style="color:var(--ink);"><?php echo e($t['title'] ?? '—'); ?></a>
            </td>
            <td style="font-family:var(--font-mono);font-size:13px;"><?php echo number_format($t['view_count']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="empty">
        <div class="empty__title">No view data yet</div>
        <div class="empty__sub">Share your walkthroughs to start tracking views</div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($views_by_date)): ?>
  <div class="section-header">
    <h2 class="section-title">Views <em>over time</em></h2>
  </div>
  <div class="card">
    <div class="card-body" style="padding:0;">
      <table class="data-table">
        <thead>
          <tr><th>Date</th><th>Views</th></tr>
        </thead>
        <tbody>
          <?php foreach ($views_by_date as $v): ?>
          <tr>
            <td style="font-family:var(--font-mono);font-size:12px;"><?php echo e($v['date']); ?></td>
            <td style="font-family:var(--font-mono);font-size:13px;"><?php echo number_format($v['count']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div>
