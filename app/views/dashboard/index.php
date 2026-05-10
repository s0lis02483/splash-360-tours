<?php
// FILE: /app/views/dashboard/index.php
?>

<!-- Top bar -->
<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <span class="here">Dashboard</span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <a href="<?php echo url('/tours/create'); ?>" class="btn btn-primary">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New walkthrough
    </a>
  </div>
</header>

<!-- Body -->
<div class="page-body fade-up">

  <!-- Metrics -->
  <div class="metrics">
    <div class="metric">
      <div class="metric__label">Active listings</div>
      <div class="metric__value"><?php echo $stats['properties_count']; ?></div>
      <div class="metric__delta">
        <a href="<?php echo url('/properties'); ?>" style="color:inherit;text-decoration:none;font-size:11px;">View all →</a>
      </div>
    </div>
    <div class="metric">
      <div class="metric__label">Walkthroughs</div>
      <div class="metric__value"><?php echo $stats['tours_count']; ?></div>
      <div class="metric__delta">
        <a href="<?php echo url('/tours'); ?>" style="color:inherit;text-decoration:none;font-size:11px;">View all →</a>
      </div>
    </div>
    <div class="metric">
      <div class="metric__label">Total views</div>
      <div class="metric__value"><?php echo number_format($stats['total_views']); ?></div>
      <div class="metric__delta">
        <a href="<?php echo url('/analytics'); ?>" style="color:inherit;text-decoration:none;font-size:11px;">Analytics →</a>
      </div>
    </div>
    <div class="metric">
      <div class="metric__label">Plan usage</div>
      <div class="metric__value" style="font-size:22px;color:var(--gold);">
        <?php
          $planName = $usage_data['subscription']['plan_name'] ?? 'Free';
          echo e($planName);
        ?>
      </div>
      <div class="metric__delta">
        <a href="<?php echo url('/subscriptions'); ?>" style="color:inherit;text-decoration:none;font-size:11px;">Manage →</a>
      </div>
    </div>
  </div>

  <!-- Recent walkthroughs -->
  <div class="section-header">
    <h2 class="section-title">Recent <em>walkthroughs</em></h2>
    <a href="<?php echo url('/tours/create'); ?>" class="btn btn-sm">+ Add</a>
  </div>

  <?php if (!empty($recent_tours)): ?>
  <div class="tape mb-6">
    <?php foreach ($recent_tours as $i => $tour): ?>
    <a href="<?php echo url('/tours/' . $tour['id']); ?>" class="tape__row">
      <div class="tape__num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></div>
      <div class="tape__thumb"></div>
      <div class="tape__info">
        <div class="tape__title"><?php echo e($tour['title']); ?></div>
        <div class="tape__sub"><?php echo e($tour['property_title'] ?? '—'); ?></div>
        <div class="tape__meta">
          <span><?php echo $tour['scene_count']; ?> scenes</span>
          <span><?php echo number_format($tour['view_count']); ?> views</span>
        </div>
      </div>
      <div class="tape__chips">
        <?php
          $statusClass = match($tour['status']) {
            'published' => 'chip-good',
            'draft'     => '',
            default     => 'chip-danger',
          };
        ?>
        <span class="chip <?php echo $statusClass; ?>"><?php echo ucfirst($tour['status']); ?></span>
        <?php if ($tour['status'] === 'published' && $tour['is_public']): ?>
          <span class="chip chip-gold">Live</span>
        <?php endif; ?>
      </div>
      <div class="tape__actions">
        <span class="btn btn-sm btn-ghost">Open →</span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="tape mb-6">
    <div class="empty">
      <div class="empty__icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        </svg>
      </div>
      <div class="empty__title">No walkthroughs yet</div>
      <div class="empty__sub">Create your first 360° tour to get started</div>
      <a href="<?php echo url('/tours/create'); ?>" class="btn btn-primary" style="margin-top:8px;">Create walkthrough</a>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($usage_data['subscription'])): ?>
  <!-- Plan usage -->
  <div class="section-header">
    <h2 class="section-title">Plan <em>limits</em></h2>
    <a href="<?php echo url('/subscriptions'); ?>" class="btn btn-sm btn-outline-gold">Upgrade</a>
  </div>
  <div class="card mb-6">
    <div class="card-body">
      <div class="usage-grid">
        <div>
          <div class="usage-item__label">
            <span class="usage-item__key">Properties</span>
            <span class="usage-item__val">
              <?php echo $usage_data['usage']['properties']; ?> /
              <?php echo $usage_data['limits']['properties'] == -1 ? '∞' : $usage_data['limits']['properties']; ?>
            </span>
          </div>
          <?php if ($usage_data['limits']['properties'] != -1 && $usage_data['limits']['properties'] > 0): ?>
          <div class="progress-bar">
            <div class="progress-fill <?php echo ($usage_data['percentages']['properties'] ?? 0) >= 90 ? 'danger' : ''; ?>"
                 style="width:<?php echo min($usage_data['percentages']['properties'] ?? 0, 100); ?>%"></div>
          </div>
          <?php endif; ?>
        </div>
        <div>
          <div class="usage-item__label">
            <span class="usage-item__key">Tours</span>
            <span class="usage-item__val">
              <?php echo $usage_data['usage']['tours']; ?> /
              <?php echo $usage_data['limits']['tours'] == -1 ? '∞' : $usage_data['limits']['tours']; ?>
            </span>
          </div>
          <?php if ($usage_data['limits']['tours'] != -1 && $usage_data['limits']['tours'] > 0): ?>
          <div class="progress-bar">
            <div class="progress-fill <?php echo ($usage_data['percentages']['tours'] ?? 0) >= 90 ? 'danger' : ''; ?>"
                 style="width:<?php echo min($usage_data['percentages']['tours'] ?? 0, 100); ?>%"></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Top viewed -->
  <?php if (!empty($top_viewed_tours)): ?>
  <div class="section-header">
    <h2 class="section-title">Top <em>viewed</em></h2>
  </div>
  <div class="card">
    <div class="card-body" style="padding:0;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Walkthrough</th>
            <th>Property</th>
            <th>Views</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($top_viewed_tours as $t): ?>
          <tr>
            <td><a href="<?php echo url('/tours/' . $t['tour_id']); ?>" style="color:var(--ink);"><?php echo e($t['title'] ?? '—'); ?></a></td>
            <td style="color:var(--ink-3);"><?php echo e($t['property_title'] ?? '—'); ?></td>
            <td style="font-family:var(--font-mono);font-size:12px;"><?php echo number_format($t['view_count']); ?></td>
            <td><span class="badge badge-published">Published</span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div>
