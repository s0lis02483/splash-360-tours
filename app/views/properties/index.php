<?php // FILE: /app/views/properties/index.php ?>
<h1>Properties</h1>
<div class="page-actions">
    <a href="<?php echo url('/properties/create'); ?>" class="btn btn-primary">Add Property</a>
</div>
<table class="data-table">
    <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Price</th><th>Tours</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($properties as $property): ?>
        <tr>
            <td><?php echo e($property['title']); ?></td>
            <td><?php echo ucfirst($property['type']); ?></td>
            <td><?php echo ucfirst($property['status']); ?></td>
            <td><?php echo formatCurrency($property['price']); ?></td>
            <td><?php echo $property['tour_count']; ?></td>
            <td>
                <a href="<?php echo url('/properties/' . $property['id']); ?>" class="btn btn-sm">View</a>
                <a href="<?php echo url('/properties/' . $property['id'] . '/edit'); ?>" class="btn btn-sm">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
