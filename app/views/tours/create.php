<?php // FILE: /app/views/tours/create.php ?>
<h1>Create Virtual Tour</h1>
<form method="POST" action="<?php echo url('/tours/create'); ?>">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Property</label><select name="property_id" required><?php foreach($properties as $p): ?><option value="<?php echo $p['id']; ?>"><?php echo e($p['title']); ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Title</label><input type="text" name="title" required value="<?php echo e(old('title')); ?>"></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?php echo e(old('description')); ?></textarea></div>
    <div class="form-group"><label>Status</label><select name="status"><option value="draft">Draft</option><option value="published">Published</option></select></div>
    <div class="form-group"><label><input type="checkbox" name="is_public" value="1" checked> Public (visible to visitors)</label></div>
    <div class="form-group"><label><input type="checkbox" name="is_featured" value="1"> Featured</label></div>
    <button type="submit" class="btn btn-primary">Create Tour</button>
    <a href="<?php echo url('/tours'); ?>" class="btn">Cancel</a>
</form>
