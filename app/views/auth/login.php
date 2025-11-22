<?php
// FILE: /app/views/auth/login.php
?>
<div class="auth-form">
    <h2>Login to Your Account</h2>

    <form method="POST" action="<?php echo url('/login'); ?>">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required autofocus value="<?php echo e(old('email')); ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </div>
    </form>

    <div class="auth-links">
        <p>Don't have an account? <a href="<?php echo url('/register'); ?>">Register here</a></p>
    </div>

    <div class="demo-credentials">
        <h4>Demo Credentials:</h4>
        <p><strong>Luxury Realty:</strong> john@luxuryrealty.com / password</p>
        <p><strong>Coastal Props:</strong> emma@coastalprops.com / password</p>
        <p><strong>Platform Admin:</strong> admin@splash360.com / password</p>
    </div>
</div>
