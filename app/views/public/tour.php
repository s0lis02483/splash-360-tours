<?php
// FILE: /app/views/public/tour.php
?>
<div class="tour-viewer">
    <!-- Property Info Header -->
    <div class="tour-header">
        <h1><?php echo e($tour['property_title']); ?></h1>
        <div class="property-info">
            <?php if ($tour['property_location']): ?>
                <span class="location"><?php echo e($tour['property_location']); ?></span>
            <?php endif; ?>
            <?php if ($tour['property_price']): ?>
                <span class="price"><?php echo formatCurrency($tour['property_price']); ?></span>
            <?php endif; ?>
        </div>
        <p class="tour-title"><?php echo e($tour['title']); ?></p>
    </div>

    <!-- Main Viewer Area -->
    <div class="viewer-container">
        <div id="panorama-viewer" data-tour='<?php echo htmlspecialchars(json_encode($tour), ENT_QUOTES, 'UTF-8'); ?>'></div>

        <!-- Viewer Controls -->
        <div class="viewer-controls">
            <button id="zoom-in" class="control-btn" title="Zoom In">+</button>
            <button id="zoom-out" class="control-btn" title="Zoom Out">-</button>
            <button id="fullscreen" class="control-btn" title="Fullscreen">⛶</button>
        </div>
    </div>

    <!-- Scene Navigator -->
    <div class="scene-navigator">
        <h3>Scenes</h3>
        <div class="scene-list">
            <?php foreach ($tour['scenes'] as $index => $scene): ?>
            <div class="scene-item" data-scene-id="<?php echo $scene['id']; ?>" data-scene-index="<?php echo $index; ?>">
                <div class="scene-thumbnail" style="background-image: url('<?php echo uploadUrl('scenes/' . $scene['image_path']); ?>')"></div>
                <span class="scene-name"><?php echo e($scene['name']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Info Panel (for info hotspots) -->
    <div id="info-panel" class="info-panel" style="display: none;">
        <div class="info-content">
            <h3 id="info-title"></h3>
            <p id="info-description"></p>
            <button id="close-info" class="btn">Close</button>
        </div>
    </div>
</div>

<script>
// Initialize viewer when page loads
document.addEventListener('DOMContentLoaded', function() {
    const viewer = new Splash360Viewer('panorama-viewer');
    viewer.init();
});
</script>
