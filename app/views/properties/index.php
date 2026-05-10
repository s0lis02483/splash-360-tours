<?php // FILE: /app/views/properties/index.php ?>

<!-- Top bar -->
<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <span class="here">Properties</span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <a href="<?php echo url('/properties/create'); ?>" class="btn btn-primary">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add property
    </a>
  </div>
</header>

<div class="page-body fade-up">

  <div class="tape-head">
    <h1 class="tape-head__title">All <em>properties</em></h1>
    <span class="tape-head__meta"><?php echo count($properties); ?> listings</span>
  </div>

  <?php if (!empty($properties)): ?>
  <div class="tape">
    <?php foreach ($properties as $i => $prop): ?>
    <div class="tape__row">
      <div class="tape__num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></div>
      <div class="tape__thumb"></div>
      <div class="tape__info">
        <div class="tape__title"><?php echo e($prop['name'] ?? $prop['title'] ?? '—'); ?></div>
        <div class="tape__sub"><?php echo e(($prop['address'] ?? '') . (isset($prop['city']) ? ', ' . $prop['city'] : '')); ?></div>
        <div class="tape__meta">
          <span><?php echo ucfirst($prop['type'] ?? '—'); ?></span>
          <span><?php echo $prop['tour_count'] ?? 0; ?> tours</span>
          <?php if (!empty($prop['price'])): ?>
          <span><?php echo isset($prop['price']) ? '€' . number_format($prop['price']) : ''; ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="tape__chips">
        <?php
          $sClass = match($prop['status'] ?? '') {
            'active'   => 'chip-good',
            'sold'     => 'chip-danger',
            default    => '',
          };
        ?>
        <span class="chip <?php echo $sClass; ?>"><?php echo ucfirst($prop['status'] ?? '—'); ?></span>
      </div>
      <div class="tape__actions">
        <a href="<?php echo url('/properties/' . $prop['id']); ?>" class="btn btn-sm">View</a>
        <a href="<?php echo url('/properties/' . $prop['id'] . '/edit'); ?>" class="btn btn-sm btn-ghost">Edit</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
  <div class="tape">
    <div class="empty">
      <div class="empty__icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
      </div>
      <div class="empty__title">No properties yet</div>
      <div class="empty__sub">Add your first listing to get started</div>
      <a href="<?php echo url('/properties/create'); ?>" class="btn btn-primary" style="margin-top:8px;">Add property</a>
    </div>
  </div>
  <?php endif; ?>

</div>
