<?php // FILE: /app/views/scenes/edit.php ?>
<h1>Edit Scene: <?php echo e($scene['name']); ?></h1>
<form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/edit'); ?>" enctype="multipart/form-data">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Scene Name</label><input type="text" name="name" required value="<?php echo e($scene['name']); ?>"></div>
    <img src="<?php echo uploadUrl('scenes/' . $scene['image_path']); ?>" width="300">
    <div class="form-group"><label>Update Image</label><input type="file" name="image" accept="image/*"></div>
    <div class="form-group"><label>Initial Yaw</label><input type="number" name="initial_yaw" value="<?php echo $scene['initial_yaw']; ?>" step="0.1"></div>
    <div class="form-group"><label>Initial Pitch</label><input type="number" name="initial_pitch" value="<?php echo $scene['initial_pitch']; ?>" step="0.1"></div>
    <button type="submit" class="btn btn-primary">Update Scene</button>
</form>
<h2>Hotspots (<?php echo count($scene['hotspots']); ?>)</h2>
<a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/create'); ?>" class="btn">Add Hotspot</a>
<?php if (!empty($scene['hotspots'])): ?>
<ul><?php foreach ($scene['hotspots'] as $hs): ?>
    <li><?php echo ucfirst($hs['type']); ?>: <?php echo e($hs['label']); ?> (Yaw: <?php echo $hs['yaw']; ?>, Pitch: <?php echo $hs['pitch']; ?>)
    <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/' . $hs['id'] . '/edit'); ?>">Edit</a></li>
<?php endforeach; ?></ul>
<?php endif; ?>
