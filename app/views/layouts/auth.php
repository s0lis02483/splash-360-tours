<?php
// FILE: /app/views/layouts/auth.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? e($title) . ' - ' : ''; ?>Splash360 Tours</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body class="auth-page">
    <!-- Flash Messages -->
    <?php if (hasFlash()): ?>
        <div class="flash-container">
            <?php if (hasFlash('success')): ?>
                <div class="alert alert-success"><?php echo e(flash('success')); ?></div>
            <?php endif; ?>

            <?php if (hasFlash('error')): ?>
                <div class="alert alert-error"><?php echo e(flash('error')); ?></div>
            <?php endif; ?>

            <?php if (hasFlash('info')): ?>
                <div class="alert alert-info"><?php echo e(flash('info')); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Auth Content -->
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Splash360 Tours</h1>
                <p>360° Virtual Tours for Real Estate</p>
            </div>
            <?php echo $content; ?>
        </div>
    </div>

    <footer class="auth-footer">
        <p>&copy; <?php echo date('Y'); ?> Splash360 Tours. All rights reserved.</p>
    </footer>

    <script src="<?php echo asset('js/app.js'); ?>"></script>
</body>
</html>
