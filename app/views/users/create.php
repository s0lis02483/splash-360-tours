<?php // FILE: /app/views/users/create.php ?>
<h1>Create User</h1>
<form method="POST" action="<?php echo url('/users/create'); ?>">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
    <div class="form-group"><label>Role</label><select name="role"><option value="user">User</option><option value="tenant_admin">Tenant Admin</option></select></div>
    <button type="submit" class="btn btn-primary">Create User</button>
    <a href="<?php echo url('/users'); ?>" class="btn">Cancel</a>
</form>
