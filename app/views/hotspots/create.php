<?php // FILE: /app/views/hotspots/create.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours/' . $tour['id']); ?>" style="color:var(--ink-3);"><?php echo e($tour['title']); ?></a>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/edit'); ?>" style="color:var(--ink-3);"><?php echo e($scene['name']); ?></a>
    <span class="sep">/</span>
    <span class="here">Add hotspot</span>
  </div>
  <div class="top__spacer"></div>
</header>

<div class="page-body fade-up">

  <h1 class="page-title">New <em>hotspot</em></h1>
  <p class="page-subtitle">Add an interactive point to <?php echo e($scene['name']); ?></p>

  <div class="card" style="max-width:560px;">
    <div class="card-body">
      <form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/create'); ?>">
        <?php echo CSRF::field(); ?>

        <div style="display:flex;flex-direction:column;gap:16px;">

          <div class="field">
            <label class="field__label">Type</label>
            <select class="select" name="type" id="hotspot-type" required>
              <?php foreach ($types as $k => $v): ?>
              <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-grid">
            <div class="field">
              <label class="field__label">Yaw (°)</label>
              <input class="input" type="number" name="yaw" required value="0" step="0.1" placeholder="0">
            </div>
            <div class="field">
              <label class="field__label">Pitch (°)</label>
              <input class="input" type="number" name="pitch" required value="0" step="0.1" placeholder="0">
            </div>
          </div>

          <div class="field">
            <label class="field__label">Label</label>
            <input class="input" type="text" name="label" placeholder="Hotspot label">
          </div>

          <div class="field">
            <label class="field__label">Description</label>
            <textarea class="textarea" name="description" placeholder="Optional description…"></textarea>
          </div>

          <div class="field" id="target-scene-field">
            <label class="field__label">Target scene</label>
            <select class="select" name="target_scene_id">
              <option value="">Select scene…</option>
              <?php foreach ($tour['scenes'] as $s): ?>
              <?php if ($s['id'] != $scene['id']): ?>
              <option value="<?php echo $s['id']; ?>"><?php echo e($s['name']); ?></option>
              <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field" id="external-url-field" style="display:none;">
            <label class="field__label">External URL</label>
            <input class="input" type="url" name="external_url" placeholder="https://…">
          </div>

          <div class="field">
            <label class="field__label">Icon style</label>
            <select class="select" name="icon_type">
              <?php foreach ($icon_types as $k => $v): ?>
              <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>

        <div class="hairline"></div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Add hotspot</button>
          <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/edit'); ?>" class="btn btn-ghost">Cancel</a>
        </div>

      </form>
    </div>
  </div>

</div>

<script>
document.getElementById('hotspot-type').addEventListener('change', function() {
    document.getElementById('target-scene-field').style.display = this.value === 'navigation' ? 'flex' : 'none';
    document.getElementById('external-url-field').style.display = this.value === 'link' ? 'flex' : 'none';
});
</script>
