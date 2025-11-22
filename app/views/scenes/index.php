<?php // FILE: /app/views/scenes/index.php ?>
<h1>Scenes for: <?php echo e($tour['title']); ?></h1>
<a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/create'); ?>" class="btn">Add Scene</a>
<?php foreach ($scenes as $scene): ?>
<div class="scene-item"><img src="<?php echo uploadUrl('scenes/' . $scene['image_path']); ?>" width="200"><h4><?php echo e($scene['name']); ?></h4><a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/edit'); ?>" class="btn btn-sm">Edit</a></div>
<?php endforeach; ?>
