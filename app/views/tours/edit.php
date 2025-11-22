<?php // FILE: /app/views/tours/edit.php ?>
<h1>Edit Tour</h1>
<form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/edit'); ?>">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Property</label><select name="property_id" required><?php foreach($properties as $p): ?><option value="<?php echo $p['id']; ?>" <?php echo $tour['property_id']==$p['id']?'selected':''; ?>><?php echo e($p['title']); ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Title</label><input type="text" name="title" required value="<?php echo e($tour['title']); ?>"></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?php echo e($tour['description']); ?></textarea></div>
    <div class="form-group"><label>Status</label><select name="status"><option value="draft" <?php echo $tour['status']=='draft'?'selected':''; ?>>Draft</option><option value="published" <?php echo $tour['status']=='published'?'selected':''; ?>>Published</option></select></div>
    <div class="form-group"><label><input type="checkbox" name="is_public" value="1" <?php echo $tour['is_public']?'checked':''; ?>> Public</label></div>
    <div class="form-group"><label><input type="checkbox" name="is_featured" value="1" <?php echo $tour['is_featured']?'checked':''; ?>> Featured</label></div>
    <button type="submit" class="btn btn-primary">Update Tour</button>
    <a href="<?php echo url('/tours/' . $tour['id']); ?>" class="btn">Cancel</a>
</form>
