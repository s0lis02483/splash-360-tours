<?php
// FILE: /app/views/auth/login.php
?>
<p class="auth-title">Sign <em>in</em></p>

<form method="POST" action="<?php echo url('/login'); ?>" class="auth-form-fields">
    <?php echo CSRF::field(); ?>

    <div class="field">
        <label class="field__label" for="email">Email address</label>
        <input class="input" type="email" id="email" name="email" required autofocus
               value="<?php echo e(old('email')); ?>" placeholder="you@studio.com">
    </div>

    <div class="field">
        <label class="field__label" for="password">Password</label>
        <input class="input" type="password" id="password" name="password" required placeholder="••••••••">
    </div>

    <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;">
        Sign in
    </button>
</form>

<div class="auth-footer-link">
    Don't have an account? <a href="<?php echo url('/register'); ?>">Register</a>
</div>
