<?php // FILE: /app/views/properties/create.php ?>
<h1>Create Property</h1>
<form method="POST" action="<?php echo url('/properties/create'); ?>" enctype="multipart/form-data">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Title</label><input type="text" name="title" required value="<?php echo e(old('title')); ?>"></div>
    <div class="form-group"><label>Reference</label><input type="text" name="reference" value="<?php echo e(old('reference')); ?>"></div>
    <div class="form-group"><label>Type</label><select name="type"><?php foreach($types as $k=>$v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Status</label><select name="status"><?php foreach($statuses as $k=>$v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" value="<?php echo e(old('price')); ?>"></div>
    <div class="form-group"><label>Location</label><input type="text" name="location" value="<?php echo e(old('location')); ?>"></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="4"><?php echo e(old('description')); ?></textarea></div>
    <div class="form-group"><label>Main Image</label><input type="file" name="main_image" accept="image/*"></div>
    <button type="submit" class="btn btn-primary">Create Property</button>
    <a href="<?php echo url('/properties'); ?>" class="btn">Cancel</a>
</form>
