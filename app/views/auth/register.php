<?php
// FILE: /app/views/auth/register.php
?>
<div class="auth-form">
    <h2>Create Your Account</h2>

    <form method="POST" action="<?php echo url('/register'); ?>">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="company_name">Company Name</label>
            <input type="text" id="company_name" name="company_name" required value="<?php echo e(old('company_name')); ?>">
        </div>

        <div class="form-group">
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" required value="<?php echo e(old('name')); ?>">
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required value="<?php echo e(old('email')); ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <small>Minimum 6 characters</small>
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </div>
    </form>

    <div class="auth-links">
        <p>Already have an account? <a href="<?php echo url('/login'); ?>">Login here</a></p>
    </div>
</div>
