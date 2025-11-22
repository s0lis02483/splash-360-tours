<?php // FILE: /app/views/tours/index.php ?>
<h1>Virtual Tours</h1>
<a href="<?php echo url('/tours/create'); ?>" class="btn btn-primary">Create Tour</a>
<table class="data-table">
    <thead><tr><th>Title</th><th>Property</th><th>Status</th><th>Scenes</th><th>Views</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($tours as $tour): ?>
        <tr>
            <td><?php echo e($tour['title']); ?></td>
            <td><?php echo e($tour['property_title']); ?></td>
            <td><span class="badge badge-<?php echo $tour['status']; ?>"><?php echo ucfirst($tour['status']); ?></span></td>
            <td><?php echo $tour['scene_count']; ?></td>
            <td><?php echo $tour['view_count']; ?></td>
            <td>
                <a href="<?php echo url('/tours/' . $tour['id']); ?>" class="btn btn-sm">View</a>
                <?php if ($tour['status'] == 'published' && $tour['is_public']): ?>
                <a href="<?php echo url('/tour/' . $tour['slug']); ?>" target="_blank" class="btn btn-sm">Preview</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
