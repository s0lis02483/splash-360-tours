<?php
// FILE: /app/views/auth/register.php
?>
<p class="auth-title">Create <em>account</em></p>

<form method="POST" action="<?php echo url('/register'); ?>" class="auth-form-fields">
    <?php echo CSRF::field(); ?>

    <div class="field">
        <label class="field__label" for="company_name">Studio / Company name</label>
        <input class="input" type="text" id="company_name" name="company_name" required
               value="<?php echo e(old('company_name')); ?>" placeholder="Atelier Properties">
    </div>

    <div class="field">
        <label class="field__label" for="name">Your name</label>
        <input class="input" type="text" id="name" name="name" required
               value="<?php echo e(old('name')); ?>" placeholder="Sofia M.">
    </div>

    <div class="field">
        <label class="field__label" for="email">Email address</label>
        <input class="input" type="email" id="email" name="email" required
               value="<?php echo e(old('email')); ?>" placeholder="you@studio.com">
    </div>

    <div class="field">
        <label class="field__label" for="password">Password</label>
        <input class="input" type="password" id="password" name="password" required placeholder="••••••••">
        <span class="field__hint">Minimum 6 characters</span>
    </div>

    <div class="field">
        <label class="field__label" for="password_confirm">Confirm password</label>
        <input class="input" type="password" id="password_confirm" name="password_confirm" required placeholder="••••••••">
    </div>

    <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;">
        Create account
    </button>
</form>

<div class="auth-footer-link">
    Already have an account? <a href="<?php echo url('/login'); ?>">Sign in</a>
</div>
