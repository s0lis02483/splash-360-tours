<?php // FILE: /app/views/scenes/create.php ?>
<h1>Add Scene to Tour: <?php echo e($tour['title']); ?></h1>
<form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/scenes/create'); ?>" enctype="multipart/form-data">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Scene Name</label><input type="text" name="name" required></div>
    <div class="form-group"><label>360° Image</label><input type="file" name="image" required accept="image/*"></div>
    <div class="form-group"><label>Initial Yaw</label><input type="number" name="initial_yaw" value="0" step="0.1"></div>
    <div class="form-group"><label>Initial Pitch</label><input type="number" name="initial_pitch" value="0" step="0.1"></div>
    <button type="submit" class="btn btn-primary">Add Scene</button>
    <a href="<?php echo url('/tours/' . $tour['id']); ?>" class="btn">Cancel</a>
</form>
