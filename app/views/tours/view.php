<?php // FILE: /app/views/tours/view.php ?>
<h1><?php echo e($tour['title']); ?></h1>
<div class="tour-details">
    <p><strong>Property:</strong> <a href="<?php echo url('/properties/' . $property['id']); ?>"><?php echo e($property['title']); ?></a></p>
    <p><strong>Status:</strong> <span class="badge badge-<?php echo $tour['status']; ?>"><?php echo ucfirst($tour['status']); ?></span></p>
    <p><strong>Views:</strong> <?php echo $view_count; ?></p>
    <?php if ($tour['status'] == 'published' && $tour['is_public']): ?>
    <p><strong>Public URL:</strong> <a href="<?php echo url('/tour/' . $tour['slug']); ?>" target="_blank"><?php echo url('/tour/' . $tour['slug']); ?></a></p>
    <?php endif; ?>
</div>
<div class="scenes-section">
    <h2>Scenes (<?php echo count($tour['scenes']); ?>)</h2>
    <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/create'); ?>" class="btn">Add Scene</a>
    <?php if (!empty($tour['scenes'])): ?>
    <div class="scenes-grid">
        <?php foreach ($tour['scenes'] as $scene): ?>
        <div class="scene-card">
            <img src="<?php echo uploadUrl('scenes/' . $scene['image_path']); ?>" alt="<?php echo e($scene['name']); ?>">
            <h4><?php echo e($scene['name']); ?></h4>
            <p>Hotspots: <?php echo count($scene['hotspots']); ?></p>
            <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/edit'); ?>" class="btn btn-sm">Edit</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p>No scenes yet. Add your first 360° panorama!</p>
    <?php endif; ?>
</div>
<div class="actions">
    <a href="<?php echo url('/tours/' . $tour['id'] . '/edit'); ?>" class="btn btn-primary">Edit Tour</a>
    <form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/delete'); ?>" style="display:inline;">
        <?php echo CSRF::field(); ?>
        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this tour?')">Delete</button>
    </form>
</div>
