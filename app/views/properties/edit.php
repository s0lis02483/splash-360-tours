<?php // FILE: /app/views/properties/edit.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/properties'); ?>" style="color:var(--ink-3);">Properties</a>
    <span class="sep">/</span>
    <a href="<?php echo url('/properties/' . $property['id']); ?>" style="color:var(--ink-3);"><?php echo e($property['name'] ?? $property['title'] ?? 'Property'); ?></a>
    <span class="sep">/</span>
    <span class="here">Edit</span>
  </div>
  <div class="top__spacer"></div>
</header>

<div class="page-body fade-up">

  <h1 class="page-title">Edit <em>property</em></h1>

  <div class="card" style="max-width:680px;">
    <div class="card-body">
      <form method="POST" action="<?php echo url('/properties/' . $property['id'] . '/edit'); ?>" enctype="multipart/form-data">
        <?php echo CSRF::field(); ?>

        <div class="form-section">
          <div class="form-section-title">Basic info</div>
          <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="field">
              <label class="field__label">Property name</label>
              <input class="input" type="text" name="title" required value="<?php echo e($property['name'] ?? $property['title'] ?? ''); ?>">
            </div>

            <div class="form-grid">
              <div class="field">
                <label class="field__label">Type</label>
                <select class="select" name="type">
                  <?php foreach ($types as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo ($property['type'] ?? '') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label class="field__label">Status</label>
                <select class="select" name="status">
                  <?php foreach ($statuses as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo ($property['status'] ?? '') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="field">
              <label class="field__label">Price</label>
              <input class="input" type="number" step="0.01" name="price" value="<?php echo e($property['price'] ?? ''); ?>">
            </div>

          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title">Location</div>
          <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="field">
              <label class="field__label">Address</label>
              <input class="input" type="text" name="address" value="<?php echo e($property['address'] ?? ''); ?>">
            </div>
            <div class="form-grid">
              <div class="field">
                <label class="field__label">City</label>
                <input class="input" type="text" name="city" value="<?php echo e($property['city'] ?? ''); ?>">
              </div>
              <div class="field">
                <label class="field__label">Country</label>
                <input class="input" type="text" name="country" value="<?php echo e($property['country'] ?? ''); ?>">
              </div>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title">Details</div>
          <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="field">
              <label class="field__label">Description</label>
              <textarea class="textarea" name="description"><?php echo e($property['description'] ?? ''); ?></textarea>
            </div>
            <?php if (!empty($property['main_image'])): ?>
            <div>
              <div class="field__label" style="margin-bottom:8px;">Current image</div>
              <img src="<?php echo uploadUrl('properties/' . $property['main_image']); ?>"
                   style="width:100%;max-height:180px;object-fit:cover;border-radius:var(--r);border:1px solid var(--line);">
            </div>
            <?php endif; ?>
            <div class="field">
              <label class="field__label">Update image</label>
              <input class="input" type="file" name="main_image" accept="image/*" style="padding:8px;">
              <span class="field__hint">Leave empty to keep current image</span>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Save changes</button>
          <a href="<?php echo url('/properties/' . $property['id']); ?>" class="btn btn-ghost">Cancel</a>
        </div>

      </form>
    </div>
  </div>
</div>
