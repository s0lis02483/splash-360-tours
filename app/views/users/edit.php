<?php // FILE: /app/views/users/edit.php ?>
<h1>Edit User</h1>
<form method="POST" action="<?php echo url('/users/'.$user['id'].'/edit'); ?>">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Name</label><input type="text" name="name" value="<?php echo e($user['name']); ?>" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo e($user['email']); ?>" required></div>
    <div class="form-group"><label>Password (leave blank to keep current)</label><input type="password" name="password"></div>
    <div class="form-group"><label>Role</label><select name="role"><option value="user" <?php echo $user['role']=='user'?'selected':''; ?>>User</option><option value="tenant_admin" <?php echo $user['role']=='tenant_admin'?'selected':''; ?>>Tenant Admin</option></select></div>
    <div class="form-group"><label>Status</label><select name="status"><option value="active" <?php echo $user['status']=='active'?'selected':''; ?>>Active</option><option value="inactive" <?php echo $user['status']=='inactive'?'selected':''; ?>>Inactive</option></select></div>
    <button type="submit" class="btn btn-primary">Update User</button>
    <form method="POST" action="<?php echo url('/users/'.$user['id'].'/delete'); ?>" style="display:inline;"><?php echo CSRF::field(); ?><button type="submit" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</button></form>
</form>
