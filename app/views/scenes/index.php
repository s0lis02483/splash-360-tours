<?php // FILE: /app/views/scenes/index.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours'); ?>" style="color:var(--ink-3);">Walkthroughs</a>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours/' . $tour['id']); ?>" style="color:var(--ink-3);"><?php echo e($tour['title']); ?></a>
    <span class="sep">/</span>
    <span class="here">Scenes</span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/create'); ?>" class="btn btn-primary btn-sm">+ Add scene</a>
  </div>
</header>

<div class="page-body fade-up">

  <div class="tape-head">
    <h1 class="tape-head__title">Scenes <em>(<?php echo count($scenes); ?>)</em></h1>
  </div>

  <?php if (!empty($scenes)): ?>
  <div class="scene-grid">
    <?php foreach ($scenes as $scene): ?>
    <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/edit'); ?>" class="scene-card">
      <div class="scene-card__thumb">
        <?php if (!empty($scene['image_path'])): ?>
        <img src="<?php echo uploadUrl('scenes/' . $scene['image_path']); ?>" alt="<?php echo e($scene['name']); ?>">
        <?php endif; ?>
      </div>
      <div class="scene-card__body">
        <div class="scene-card__title"><?php echo e($scene['name']); ?></div>
        <div class="scene-card__sub">Order: <?php echo $scene['sort_order'] ?? '—'; ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="tape">
    <div class="empty">
      <div class="empty__title">No scenes</div>
      <div class="empty__sub">Upload your first 360° panorama</div>
      <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/create'); ?>" class="btn btn-primary" style="margin-top:8px;">Add scene</a>
    </div>
  </div>
  <?php endif; ?>

</div>
