<?php
// FILE: /app/views/layouts/main.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? e($title) . ' - ' : ''; ?>Splash360 Tours</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo url('/dashboard'); ?>" class="logo">Splash360 Tours</a>
            <ul class="nav-menu">
                <li><a href="<?php echo url('/dashboard'); ?>" class="<?php echo activeRoute('/dashboard'); ?>">Dashboard</a></li>
                <li><a href="<?php echo url('/properties'); ?>" class="<?php echo activeRoute('/properties'); ?>">Properties</a></li>
                <li><a href="<?php echo url('/tours'); ?>" class="<?php echo activeRoute('/tours'); ?>">Tours</a></li>
                <li><a href="<?php echo url('/analytics'); ?>" class="<?php echo activeRoute('/analytics'); ?>">Analytics</a></li>
                <li><a href="<?php echo url('/subscriptions'); ?>" class="<?php echo activeRoute('/subscriptions'); ?>">Subscription</a></li>

                <?php if (isAuth() && auth()['role'] === 'tenant_admin'): ?>
                <li><a href="<?php echo url('/users'); ?>" class="<?php echo activeRoute('/users'); ?>">Users</a></li>
                <?php endif; ?>

                <?php if (isAuth() && auth()['role'] === 'platform_admin'): ?>
                <li><a href="<?php echo url('/tenants'); ?>" class="<?php echo activeRoute('/tenants'); ?>">Tenants</a></li>
                <?php endif; ?>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle"><?php echo e(auth()['name']); ?></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo url('/logout'); ?>">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (hasFlash()): ?>
        <div class="container">
            <?php if (hasFlash('success')): ?>
                <div class="alert alert-success"><?php echo e(flash('success')); ?></div>
            <?php endif; ?>

            <?php if (hasFlash('error')): ?>
                <div class="alert alert-error"><?php echo e(flash('error')); ?></div>
            <?php endif; ?>

            <?php if (hasFlash('info')): ?>
                <div class="alert alert-info"><?php echo e(flash('info')); ?></div>
            <?php endif; ?>

            <?php if (hasFlash('warning')): ?>
                <div class="alert alert-warning"><?php echo e(flash('warning')); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php echo $content; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Splash360 Tours. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?php echo asset('js/app.js'); ?>"></script>
</body>
</html>
