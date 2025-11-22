<?php // FILE: /app/views/properties/edit.php ?>
<h1>Edit Property</h1>
<form method="POST" action="<?php echo url('/properties/' . $property['id'] . '/edit'); ?>" enctype="multipart/form-data">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Title</label><input type="text" name="title" required value="<?php echo e($property['title']); ?>"></div>
    <div class="form-group"><label>Reference</label><input type="text" name="reference" value="<?php echo e($property['reference']); ?>"></div>
    <div class="form-group"><label>Type</label><select name="type"><?php foreach($types as $k=>$v): ?><option value="<?php echo $k; ?>" <?php echo $property['type']==$k?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Status</label><select name="status"><?php foreach($statuses as $k=>$v): ?><option value="<?php echo $k; ?>" <?php echo $property['status']==$k?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" value="<?php echo e($property['price']); ?>"></div>
    <div class="form-group"><label>Location</label><input type="text" name="location" value="<?php echo e($property['location']); ?>"></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="4"><?php echo e($property['description']); ?></textarea></div>
    <?php if ($property['main_image']): ?><div class="current-image"><img src="<?php echo uploadUrl('properties/' . $property['main_image']); ?>" width="200"></div><?php endif; ?>
    <div class="form-group"><label>Update Image</label><input type="file" name="main_image" accept="image/*"></div>
    <button type="submit" class="btn btn-primary">Update Property</button>
    <a href="<?php echo url('/properties/' . $property['id']); ?>" class="btn">Cancel</a>
</form>
