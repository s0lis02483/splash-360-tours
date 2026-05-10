<?php // FILE: /app/views/properties/create.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/properties'); ?>" style="color:var(--ink-3);">Properties</a>
    <span class="sep">/</span>
    <span class="here">New</span>
  </div>
  <div class="top__spacer"></div>
</header>

<div class="page-body fade-up">

  <h1 class="page-title">New <em>property</em></h1>
  <p class="page-subtitle">Add a listing to your portfolio</p>

  <div class="card" style="max-width:680px;">
    <div class="card-body">
      <form method="POST" action="<?php echo url('/properties/create'); ?>" enctype="multipart/form-data">
        <?php echo CSRF::field(); ?>

        <div class="form-section">
          <div class="form-section-title">Basic info</div>
          <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="field">
              <label class="field__label">Property name</label>
              <input class="input" type="text" name="title" required
                     value="<?php echo e(old('title')); ?>" placeholder="e.g. Villa Serena, Marbella">
            </div>

            <div class="form-grid">
              <div class="field">
                <label class="field__label">Type</label>
                <select class="select" name="type">
                  <?php foreach ($types as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo old('type') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label class="field__label">Status</label>
                <select class="select" name="status">
                  <?php foreach ($statuses as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo old('status') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="field">
              <label class="field__label">Price</label>
              <input class="input" type="number" step="0.01" name="price"
                     value="<?php echo e(old('price')); ?>" placeholder="0.00">
            </div>

          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title">Location</div>
          <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="field">
              <label class="field__label">Address</label>
              <input class="input" type="text" name="address"
                     value="<?php echo e(old('address')); ?>" placeholder="Street address">
            </div>
            <div class="form-grid">
              <div class="field">
                <label class="field__label">City</label>
                <input class="input" type="text" name="city"
                       value="<?php echo e(old('city')); ?>" placeholder="City">
              </div>
              <div class="field">
                <label class="field__label">Country</label>
                <input class="input" type="text" name="country"
                       value="<?php echo e(old('country')); ?>" placeholder="Country">
              </div>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title">Details</div>
          <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="field">
              <label class="field__label">Description</label>
              <textarea class="textarea" name="description" rows="4"
                        placeholder="Describe the property…"><?php echo e(old('description')); ?></textarea>
            </div>
            <div class="field">
              <label class="field__label">Main image</label>
              <input class="input" type="file" name="main_image" accept="image/*"
                     style="padding:8px;">
              <span class="field__hint">JPG, PNG or WebP — recommended 16:9</span>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Add property</button>
          <a href="<?php echo url('/properties'); ?>" class="btn btn-ghost">Cancel</a>
        </div>

      </form>
    </div>
  </div>
</div>
