<?php // FILE: /app/views/scenes/edit.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours/' . $tour['id']); ?>" style="color:var(--ink-3);"><?php echo e($tour['title']); ?></a>
    <span class="sep">/</span>
    <span class="here"><?php echo e($scene['name']); ?></span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/create'); ?>" class="btn btn-sm btn-primary">
      + Hotspot
    </a>
  </div>
</header>

<div class="page-body fade-up">

  <div class="detail-grid">

    <!-- Left: edit form -->
    <div>
      <div class="section-header">
        <h2 class="section-title">Edit <em>scene</em></h2>
      </div>

      <div class="card" style="margin-bottom:24px;">
        <div class="card-body">
          <form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/edit'); ?>" enctype="multipart/form-data">
            <?php echo CSRF::field(); ?>

            <div style="display:flex;flex-direction:column;gap:18px;">

              <div class="field">
                <label class="field__label">Scene name</label>
                <input class="input" type="text" name="name" required value="<?php echo e($scene['name']); ?>">
              </div>

              <?php if (!empty($scene['image_path'])): ?>
              <div>
                <div class="field__label" style="margin-bottom:8px;">Current image</div>
                <img src="<?php echo sceneImageUrl($scene['image_path']); ?>"
                     style="width:100%;max-height:200px;object-fit:cover;border-radius:var(--r);border:1px solid var(--line);">
              </div>
              <?php endif; ?>

              <div class="field">
                <label class="field__label">Replace image</label>
                <input class="input" type="file" name="image" accept="image/*" style="padding:8px;">
                <span class="field__hint">Leave empty to keep current image</span>
              </div>

              <div class="form-grid">
                <div class="field">
                  <label class="field__label">Initial yaw</label>
                  <input class="input" type="number" name="initial_yaw" value="<?php echo $scene['initial_yaw']; ?>" step="0.1">
                </div>
                <div class="field">
                  <label class="field__label">Initial pitch</label>
                  <input class="input" type="number" name="initial_pitch" value="<?php echo $scene['initial_pitch']; ?>" step="0.1">
                </div>
              </div>

            </div>

            <div class="hairline"></div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Save changes</button>
              <a href="<?php echo url('/tours/' . $tour['id']); ?>" class="btn btn-ghost">Back to tour</a>
            </div>

          </form>
        </div>
      </div>

      <!-- Delete scene -->
      <form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/delete'); ?>">
        <?php echo CSRF::field(); ?>
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this scene?')">
          Delete scene
        </button>
      </form>
    </div>

    <!-- Right: hotspots -->
    <div>
      <div class="section-header">
        <h2 class="section-title">Hotspots <em>(<?php echo count($scene['hotspots']); ?>)</em></h2>
        <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/create'); ?>" class="btn btn-sm">+ Add</a>
      </div>

      <?php if (!empty($scene['hotspots'])): ?>
      <div class="tape">
        <?php foreach ($scene['hotspots'] as $i => $hs): ?>
        <div class="tape__row">
          <div class="tape__num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></div>
          <div class="tape__thumb" style="width:60px;height:40px;"></div>
          <div class="tape__info">
            <div class="tape__title"><?php echo e($hs['label'] ?? 'Hotspot'); ?></div>
            <div class="tape__sub"><?php echo ucfirst($hs['type']); ?> · Yaw <?php echo $hs['yaw']; ?>° · Pitch <?php echo $hs['pitch']; ?>°</div>
          </div>
          <div class="tape__actions">
            <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/' . $hs['id'] . '/edit'); ?>" class="btn btn-sm btn-ghost">Edit</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="tape">
        <div class="empty" style="padding:32px;">
          <div class="empty__sub">No hotspots yet — add navigation or info points to this scene</div>
        </div>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>
