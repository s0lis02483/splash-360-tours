<?php // FILE: /app/views/tours/create.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours'); ?>" style="color:var(--ink-3);">Walkthroughs</a>
    <span class="sep">/</span>
    <span class="here">New</span>
  </div>
  <div class="top__spacer"></div>
</header>

<div class="page-body fade-up">

  <h1 class="page-title">New <em>walkthrough</em></h1>
  <p class="page-subtitle">Set up a 360° virtual tour for a property</p>

  <div class="card" style="max-width:640px;">
    <div class="card-body">
      <form method="POST" action="<?php echo url('/tours/create'); ?>">
        <?php echo CSRF::field(); ?>

        <div class="form-section">
          <div class="form-section-title">Details</div>
          <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="field">
              <label class="field__label" for="property_id">Property</label>
              <select class="select" name="property_id" id="property_id" required>
                <option value="">Select a property…</option>
                <?php foreach ($properties as $p): ?>
                <option value="<?php echo $p['id']; ?>" <?php echo old('property_id') == $p['id'] ? 'selected' : ''; ?>>
                  <?php echo e($p['name'] ?? $p['title'] ?? ''); ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field">
              <label class="field__label" for="title">Tour title</label>
              <input class="input" type="text" name="title" id="title" required
                     value="<?php echo e(old('title')); ?>" placeholder="e.g. Grand Suite Walkthrough">
            </div>

            <div class="field">
              <label class="field__label" for="description">Description</label>
              <textarea class="textarea" name="description" id="description" placeholder="Optional description…"><?php echo e(old('description')); ?></textarea>
            </div>

          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title">Publishing</div>
          <div style="display:flex;flex-direction:column;gap:14px;">

            <div class="field">
              <label class="field__label" for="status">Status</label>
              <select class="select" name="status" id="status">
                <option value="draft" <?php echo old('status', 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo old('status') === 'published' ? 'selected' : ''; ?>>Published</option>
              </select>
            </div>

            <label class="checkbox-row">
              <input type="checkbox" name="is_public" value="1" checked>
              <span>Publicly visible to visitors</span>
            </label>

            <label class="checkbox-row">
              <input type="checkbox" name="is_featured" value="1">
              <span>Feature this tour on the homepage</span>
            </label>

          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Create walkthrough</button>
          <a href="<?php echo url('/tours'); ?>" class="btn btn-ghost">Cancel</a>
        </div>

      </form>
    </div>
  </div>

</div>
