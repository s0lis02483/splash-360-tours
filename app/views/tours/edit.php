<?php // FILE: /app/views/tours/edit.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours'); ?>" style="color:var(--ink-3);">Walkthroughs</a>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours/' . $tour['id']); ?>" style="color:var(--ink-3);"><?php echo e($tour['title']); ?></a>
    <span class="sep">/</span>
    <span class="here">Edit</span>
  </div>
  <div class="top__spacer"></div>
</header>

<div class="page-body fade-up">

  <h1 class="page-title">Edit <em>walkthrough</em></h1>

  <div class="card" style="max-width:640px;">
    <div class="card-body">
      <form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/edit'); ?>">
        <?php echo CSRF::field(); ?>

        <div class="form-section">
          <div class="form-section-title">Details</div>
          <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="field">
              <label class="field__label">Property</label>
              <select class="select" name="property_id" required>
                <?php foreach ($properties as $p): ?>
                <option value="<?php echo $p['id']; ?>" <?php echo $tour['property_id'] == $p['id'] ? 'selected' : ''; ?>>
                  <?php echo e($p['name'] ?? $p['title'] ?? ''); ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field">
              <label class="field__label">Title</label>
              <input class="input" type="text" name="title" required value="<?php echo e($tour['title']); ?>">
            </div>

            <div class="field">
              <label class="field__label">Description</label>
              <textarea class="textarea" name="description"><?php echo e($tour['description']); ?></textarea>
            </div>

            <div class="field">
              <label class="field__label">3D scene URL <span style="font-family:var(--font-mono);font-size:10px;color:var(--ink-4);margin-left:6px;">SuperSplat / Luma — optional</span></label>
              <input class="input" type="url" name="splat_url"
                     placeholder="https://superspl.at/scene/…   or   https://lumalabs.ai/capture/…"
                     value="<?php echo e($tour['splat_url'] ?? ''); ?>">
              <span class="field__hint">Leave blank to remove the 3D scene from the public viewer.</span>
            </div>

          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title">Publishing</div>
          <div style="display:flex;flex-direction:column;gap:14px;">

            <div class="field">
              <label class="field__label">Status</label>
              <select class="select" name="status">
                <option value="draft" <?php echo $tour['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo $tour['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="archived" <?php echo $tour['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
              </select>
            </div>

            <label class="checkbox-row">
              <input type="checkbox" name="is_public" value="1" <?php echo $tour['is_public'] ? 'checked' : ''; ?>>
              <span>Publicly visible to visitors</span>
            </label>

            <label class="checkbox-row">
              <input type="checkbox" name="is_featured" value="1" <?php echo $tour['is_featured'] ? 'checked' : ''; ?>>
              <span>Feature this tour</span>
            </label>

          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Save changes</button>
          <a href="<?php echo url('/tours/' . $tour['id']); ?>" class="btn btn-ghost">Cancel</a>
        </div>

      </form>
    </div>
  </div>
</div>
