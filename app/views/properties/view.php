<?php // FILE: /app/views/properties/view.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/properties'); ?>" style="color:var(--ink-3);">Properties</a>
    <span class="sep">/</span>
    <span class="here"><?php echo e($property['name'] ?? $property['title'] ?? 'Property'); ?></span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <a href="<?php echo url('/properties/' . $property['id'] . '/edit'); ?>" class="btn btn-sm">Edit</a>
  </div>
</header>

<div class="page-body fade-up">

  <div class="detail-grid">

    <!-- Left -->
    <div>
      <!-- Tours -->
      <div class="section-header">
        <h2 class="section-title">Walkthroughs <em>(<?php echo count($property['tours'] ?? []); ?>)</em></h2>
        <a href="<?php echo url('/tours/create?property_id=' . $property['id']); ?>" class="btn btn-primary btn-sm">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New tour
        </a>
      </div>

      <?php if (!empty($property['tours'])): ?>
      <div class="tape">
        <?php foreach ($property['tours'] as $i => $t): ?>
        <a href="<?php echo url('/tours/' . $t['id']); ?>" class="tape__row">
          <div class="tape__num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></div>
          <div class="tape__thumb"></div>
          <div class="tape__info">
            <div class="tape__title"><?php echo e($t['title']); ?></div>
            <div class="tape__sub"><?php echo isset($t['scene_count']) ? $t['scene_count'] . ' scenes' : ''; ?></div>
          </div>
          <div class="tape__chips">
            <?php $sc = match($t['status']) { 'published' => 'chip-good', 'draft' => '', default => '' }; ?>
            <span class="chip <?php echo $sc; ?>"><?php echo ucfirst($t['status']); ?></span>
          </div>
          <div class="tape__actions">
            <span class="btn btn-sm btn-ghost">Open →</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="tape">
        <div class="empty">
          <div class="empty__title">No tours yet</div>
          <div class="empty__sub">Create a 360° walkthrough for this property</div>
          <a href="<?php echo url('/tours/create?property_id=' . $property['id']); ?>" class="btn btn-primary" style="margin-top:8px;">Create tour</a>
        </div>
      </div>
      <?php endif; ?>

      <div class="hairline"></div>
      <div style="display:flex;gap:10px;align-items:center;">
        <form method="POST" action="<?php echo url('/properties/' . $property['id'] . '/delete'); ?>">
          <?php echo CSRF::field(); ?>
          <button type="submit" class="btn btn-danger btn-sm"
                  onclick="return confirm('Delete this property? Associated tours may be affected.')">
            Delete property
          </button>
        </form>
        <span style="font-size:12px;color:var(--ink-4);">This action cannot be undone</span>
      </div>
    </div>

    <!-- Right: info panel -->
    <div>
      <div class="card">
        <div class="card-header">
          <span class="card-title" style="font-size:15px;">Listing details</span>
          <?php $sc = match($property['status'] ?? '') { 'active' => 'chip-good', 'sold' => 'chip-danger', default => '' }; ?>
          <span class="chip <?php echo $sc; ?>"><?php echo ucfirst($property['status'] ?? '—'); ?></span>
        </div>
        <div class="card-body" style="padding:0;">
          <?php
            $fields = [
              'Type'        => ucfirst($property['type'] ?? '—'),
              'Price'       => !empty($property['price']) ? '€' . number_format($property['price']) : '—',
              'Address'     => !empty($property['address']) ? $property['address'] : null,
              'City'        => !empty($property['city']) ? $property['city'] : null,
              'Country'     => !empty($property['country']) ? $property['country'] : null,
            ];
            foreach ($fields as $k => $v):
              if ($v === null) continue;
          ?>
          <div class="detail-stat" style="padding:12px 20px;">
            <div class="detail-stat__key"><?php echo $k; ?></div>
            <div class="detail-stat__val"><?php echo e($v); ?></div>
          </div>
          <?php endforeach; ?>
          <?php if (!empty($property['description'])): ?>
          <div class="detail-stat" style="padding:12px 20px;">
            <div class="detail-stat__key">Description</div>
            <div class="detail-stat__val" style="color:var(--ink-2);font-size:13px;line-height:1.6;"><?php echo e($property['description']); ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
