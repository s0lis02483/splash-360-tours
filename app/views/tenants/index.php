<?php // FILE: /app/views/tenants/index.php ?>
<h1>Tenants</h1>
<a href="<?php echo url('/tenants/create'); ?>" class="btn btn-primary">Add Tenant</a>
<table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Users</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($tenants as $t): ?><tr><td><?php echo e($t['name']); ?></td><td><?php echo e($t['email']); ?></td><td><?php echo $t['user_count']; ?></td><td><?php echo ucfirst($t['status']); ?></td><td><a href="<?php echo url('/tenants/'.$t['id'].'/edit'); ?>" class="btn btn-sm">Edit</a></td></tr><?php endforeach; ?></tbody></table>
