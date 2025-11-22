<?php // FILE: /app/views/tenants/edit.php ?>
<h1>Edit Tenant</h1>
<form method="POST" action="<?php echo url('/tenants/'.$tenant['id'].'/edit'); ?>">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Name</label><input type="text" name="name" value="<?php echo e($tenant['name']); ?>" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo e($tenant['email']); ?>" required></div>
    <div class="form-group"><label>API Key</label><input type="text" value="<?php echo e($tenant['api_key']); ?>" readonly></div>
    <div class="form-group"><label>Status</label><select name="status"><option value="active" <?php echo $tenant['status']=='active'?'selected':''; ?>>Active</option><option value="inactive" <?php echo $tenant['status']=='inactive'?'selected':''; ?>>Inactive</option><option value="suspended" <?php echo $tenant['status']=='suspended'?'selected':''; ?>>Suspended</option></select></div>
    <button type="submit" class="btn btn-primary">Update Tenant</button>
    <a href="<?php echo url('/tenants'); ?>" class="btn">Cancel</a>
</form>
