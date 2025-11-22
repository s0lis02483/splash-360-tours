<?php // FILE: /app/views/properties/view.php ?>
<div class="property-detail">
    <h1><?php echo e($property['title']); ?></h1>
    <?php if ($property['main_image']): ?>
    <img src="<?php echo uploadUrl('properties/' . $property['main_image']); ?>" alt="<?php echo e($property['title']); ?>" class="property-image">
    <?php endif; ?>
    <div class="property-info">
        <p><strong>Reference:</strong> <?php echo e($property['reference']); ?></p>
        <p><strong>Type:</strong> <?php echo ucfirst($property['type']); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($property['status']); ?></p>
        <p><strong>Price:</strong> <?php echo formatCurrency($property['price']); ?></p>
        <p><strong>Location:</strong> <?php echo e($property['location']); ?></p>
        <p><strong>Description:</strong> <?php echo e($property['description']); ?></p>
    </div>
    <div class="tours-section">
        <h2>Tours (<?php echo count($property['tours']); ?>)</h2>
        <?php if (!empty($property['tours'])): ?>
        <ul><?php foreach ($property['tours'] as $tour): ?>
            <li><a href="<?php echo url('/tours/' . $tour['id']); ?>"><?php echo e($tour['title']); ?></a> - <?php echo ucfirst($tour['status']); ?></li>
        <?php endforeach; ?></ul>
        <?php else: ?>
        <p>No tours yet.</p>
        <?php endif; ?>
        <a href="<?php echo url('/tours/create?property_id=' . $property['id']); ?>" class="btn">Create Tour</a>
    </div>
    <div class="actions">
        <a href="<?php echo url('/properties/' . $property['id'] . '/edit'); ?>" class="btn btn-primary">Edit</a>
        <form method="POST" action="<?php echo url('/properties/' . $property['id'] . '/delete'); ?>" style="display:inline;">
            <?php echo CSRF::field(); ?>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this property?')">Delete</button>
        </form>
    </div>
</div>
