<?php // FILE: /app/views/tenants/create.php ?>
<h1>Create Tenant</h1>
<form method="POST" action="<?php echo url('/tenants/create'); ?>">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
    <button type="submit" class="btn btn-primary">Create Tenant</button>
    <a href="<?php echo url('/tenants'); ?>" class="btn">Cancel</a>
</form>
