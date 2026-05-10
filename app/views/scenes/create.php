<?php // FILE: /app/views/scenes/create.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours'); ?>" style="color:var(--ink-3);">Walkthroughs</a>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours/' . $tour['id']); ?>" style="color:var(--ink-3);"><?php echo e($tour['title']); ?></a>
    <span class="sep">/</span>
    <span class="here">Add scene</span>
  </div>
  <div class="top__spacer"></div>
</header>

<div class="page-body fade-up">

  <h1 class="page-title">New <em>scene</em></h1>
  <p class="page-subtitle">Upload a 360° panorama for <strong style="color:var(--ink);"><?php echo e($tour['title']); ?></strong></p>

  <div class="card" style="max-width:600px;">
    <div class="card-body">
      <form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/scenes/create'); ?>" enctype="multipart/form-data">
        <?php echo CSRF::field(); ?>

        <div style="display:flex;flex-direction:column;gap:18px;">

          <div class="field">
            <label class="field__label">Scene name</label>
            <input class="input" type="text" name="name" required
                   value="<?php echo e(old('name')); ?>" placeholder="e.g. Living Room, Master Suite">
          </div>

          <div class="field">
            <label class="field__label">360° panorama image</label>
            <input class="input" type="file" name="image" required accept="image/*" style="padding:8px;">
            <span class="field__hint">Equirectangular JPG or PNG — minimum 4096 × 2048 px recommended</span>
          </div>

          <div class="form-grid">
            <div class="field">
              <label class="field__label">Initial yaw</label>
              <input class="input" type="number" name="initial_yaw" value="<?php echo e(old('initial_yaw', 0)); ?>" step="0.1">
              <span class="field__hint">Horizontal starting angle (°)</span>
            </div>
            <div class="field">
              <label class="field__label">Initial pitch</label>
              <input class="input" type="number" name="initial_pitch" value="<?php echo e(old('initial_pitch', 0)); ?>" step="0.1">
              <span class="field__hint">Vertical starting angle (°)</span>
            </div>
          </div>

        </div>

        <div class="hairline"></div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Upload scene</button>
          <a href="<?php echo url('/tours/' . $tour['id']); ?>" class="btn btn-ghost">Cancel</a>
        </div>

      </form>
    </div>
  </div>

</div>
