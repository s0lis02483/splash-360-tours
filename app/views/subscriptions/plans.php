<?php // FILE: /app/views/subscriptions/plans.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Account</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/subscriptions'); ?>" style="color:var(--ink-3);">Subscription</a>
    <span class="sep">/</span>
    <span class="here">Plans</span>
  </div>
  <div class="top__spacer"></div>
</header>

<div class="page-body fade-up">

  <h1 class="page-title">Choose a <em>plan</em></h1>
  <p class="page-subtitle">Scale your virtual tour studio</p>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;max-width:900px;">
    <?php foreach ($plans as $plan): ?>
    <div class="card" style="transition:border-color 0.18s;" onmouseenter="this.style.borderColor='var(--gold-line)'" onmouseleave="this.style.borderColor='var(--line)'">
      <div class="card-header" style="flex-direction:column;align-items:flex-start;gap:6px;">
        <div style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:var(--ink-3);">
          <?php echo e($plan['name']); ?>
        </div>
        <div style="font-family:var(--font-display);font-size:32px;line-height:1;color:var(--ink);">
          <?php echo !empty($plan['price']) ? '€' . number_format($plan['price'], 0) : 'Free'; ?>
          <?php if (!empty($plan['price'])): ?>
          <span style="font-size:14px;color:var(--ink-3);font-family:var(--font-sans);">/<?php echo $plan['billing_period'] ?? 'mo'; ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="detail-stat" style="padding:10px 20px;">
          <div class="detail-stat__key">Properties</div>
          <div class="detail-stat__val"><?php echo $plan['max_properties'] == -1 ? 'Unlimited' : $plan['max_properties']; ?></div>
        </div>
        <div class="detail-stat" style="padding:10px 20px;">
          <div class="detail-stat__key">Tours</div>
          <div class="detail-stat__val"><?php echo $plan['max_tours'] == -1 ? 'Unlimited' : $plan['max_tours']; ?></div>
        </div>
        <div class="detail-stat" style="padding:10px 20px;">
          <div class="detail-stat__key">Scenes per tour</div>
          <div class="detail-stat__val"><?php echo ($plan['max_scenes_per_tour'] ?? $plan['max_scenes'] ?? 0) == -1 ? 'Unlimited' : ($plan['max_scenes_per_tour'] ?? $plan['max_scenes'] ?? '—'); ?></div>
        </div>
        <div class="detail-stat" style="padding:10px 20px;">
          <div class="detail-stat__key">Storage</div>
          <div class="detail-stat__val"><?php echo isset($plan['max_storage_gb']) ? $plan['max_storage_gb'] . ' GB' : '—'; ?></div>
        </div>
        <div style="padding:16px 20px;">
          <form method="POST" action="<?php echo url('/subscriptions/change-plan'); ?>">
            <?php echo CSRF::field(); ?>
            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
            <button type="submit" class="btn btn-primary btn-block">Select plan</button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

</div>
