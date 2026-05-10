<?php // FILE: /app/views/tours/index.php ?>

<!-- Top bar -->
<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <span class="here">Walkthroughs</span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <a href="<?php echo url('/tours/create'); ?>" class="btn btn-primary">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New walkthrough
    </a>
  </div>
</header>

<div class="page-body fade-up">

  <div class="tape-head">
    <h1 class="tape-head__title">All <em>walkthroughs</em></h1>
    <span class="tape-head__meta"><?php echo count($tours); ?> total</span>
  </div>

  <?php if (!empty($tours)): ?>
  <div class="tape">
    <?php foreach ($tours as $i => $tour): ?>
    <div class="tape__row">
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
          <span class="chip chip-gold">Public</span>
        <?php endif; ?>
      </div>
      <div class="tape__actions">
        <a href="<?php echo url('/tours/' . $tour['id']); ?>" class="btn btn-sm">Open</a>
        <?php if ($tour['status'] === 'published' && $tour['is_public']): ?>
        <a href="<?php echo url('/tour/' . $tour['slug']); ?>" target="_blank" class="btn btn-sm btn-outline-gold">Preview</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
  <div class="tape">
    <div class="empty">
      <div class="empty__icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        </svg>
      </div>
      <div class="empty__title">No walkthroughs</div>
      <div class="empty__sub">Create your first 360° tour</div>
      <a href="<?php echo url('/tours/create'); ?>" class="btn btn-primary" style="margin-top:8px;">Create walkthrough</a>
    </div>
  </div>
  <?php endif; ?>

</div>
