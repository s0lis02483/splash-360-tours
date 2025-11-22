<?php // FILE: /app/views/users/index.php ?>
<h1>Users</h1>
<a href="<?php echo url('/users/create'); ?>" class="btn btn-primary">Add User</a>
<table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td><?php echo e($u['name']); ?></td><td><?php echo e($u['email']); ?></td><td><?php echo ucfirst($u['role']); ?></td><td><?php echo ucfirst($u['status']); ?></td><td><a href="<?php echo url('/users/'.$u['id'].'/edit'); ?>" class="btn btn-sm">Edit</a></td></tr><?php endforeach; ?></tbody></table>
