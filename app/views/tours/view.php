<?php // FILE: /app/views/tours/view.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours'); ?>" style="color:var(--ink-3);">Walkthroughs</a>
    <span class="sep">/</span>
    <span class="here"><?php echo e($tour['title']); ?></span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <?php if ($tour['status'] === 'published' && $tour['is_public']): ?>
    <a href="<?php echo url('/tour/' . $tour['slug']); ?>" target="_blank" class="btn btn-outline-gold btn-sm">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Preview live
    </a>
    <?php endif; ?>
    <a href="<?php echo url('/tours/' . $tour['id'] . '/edit'); ?>" class="btn btn-sm">Edit</a>
  </div>
</header>

<div class="page-body fade-up">

  <div class="detail-grid">

    <!-- Left: main content -->
    <div>

      <!-- Scene grid -->
      <div class="section-header">
        <h2 class="section-title">Scenes <em>(<?php echo count($tour['scenes']); ?>)</em></h2>
        <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/create'); ?>" class="btn btn-primary btn-sm">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add scene
        </a>
      </div>

      <?php if (!empty($tour['scenes'])): ?>
      <div class="scene-grid">
        <?php foreach ($tour['scenes'] as $scene): ?>
        <div class="scene-card">
          <div class="scene-card__thumb">
            <?php if (!empty($scene['image_path'])): ?>
            <img src="<?php echo sceneImageUrl($scene['image_path']); ?>" alt="<?php echo e($scene['name']); ?>">
            <?php endif; ?>
          </div>
          <div class="scene-card__body">
            <div class="scene-card__title"><?php echo e($scene['name']); ?></div>
            <div class="scene-card__sub"><?php echo count($scene['hotspots']); ?> hotspots</div>
            <div class="scene-card__actions">
              <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/edit'); ?>" class="btn btn-sm">Edit</a>
              <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/create'); ?>" class="btn btn-sm btn-ghost">+ Hotspot</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="tape">
        <div class="empty">
          <div class="empty__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
          </div>
          <div class="empty__title">No scenes yet</div>
          <div class="empty__sub">Upload your first 360° panorama image</div>
          <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/create'); ?>" class="btn btn-primary" style="margin-top:8px;">Add first scene</a>
        </div>
      </div>
      <?php endif; ?>

      <!-- Delete zone -->
      <div class="hairline"></div>
      <div style="display:flex;gap:10px;align-items:center;">
        <form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/delete'); ?>">
          <?php echo CSRF::field(); ?>
          <button type="submit" class="btn btn-danger btn-sm"
                  onclick="return confirm('Permanently delete this tour and all its scenes?')">
            Delete tour
          </button>
        </form>
        <span style="font-size:12px;color:var(--ink-4);">This action cannot be undone</span>
      </div>

    </div>

    <!-- Right: metadata panel -->
    <div>
      <div class="card">
        <div class="card-header">
          <span class="card-title" style="font-size:15px;">Tour details</span>
          <?php
            $sc = match($tour['status']) { 'published' => 'chip-good', 'draft' => '', default => 'chip-danger' };
          ?>
          <span class="chip <?php echo $sc; ?>"><?php echo ucfirst($tour['status']); ?></span>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Property</div>
            <div class="detail-stat__val">
              <a href="<?php echo url('/properties/' . $property['id']); ?>" style="color:var(--ink);">
                <?php echo e($property['name'] ?? $property['title'] ?? '—'); ?>
              </a>
            </div>
          </div>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Total views</div>
            <div class="detail-stat__val" style="font-family:var(--font-mono);font-size:20px;"><?php echo number_format($view_count); ?></div>
          </div>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Scenes</div>
            <div class="detail-stat__val"><?php echo count($tour['scenes']); ?></div>
          </div>
          <?php if (!empty($tour['description'])): ?>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Description</div>
            <div class="detail-stat__val" style="color:var(--ink-2);font-size:13px;line-height:1.5;"><?php echo e($tour['description']); ?></div>
          </div>
          <?php endif; ?>
          <?php if ($tour['is_public'] && $tour['status'] === 'published'): ?>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Public link</div>
            <div class="detail-stat__val" style="font-size:12px;word-break:break-all;">
              <a href="<?php echo url('/tour/' . $tour['slug']); ?>" target="_blank" style="color:var(--gold);">
                /tour/<?php echo e($tour['slug']); ?>
              </a>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
