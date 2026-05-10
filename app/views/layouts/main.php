<?php
// FILE: /app/views/layouts/main.php

// Determine active nav item
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function isActive($path) {
    global $currentPath;
    if ($path === '/dashboard') return $currentPath === '/dashboard' ? 'active' : '';
    return strpos($currentPath, $path) === 0 ? 'active' : '';
}

// Get user initial for avatar
$userInitial = isset($user) ? strtoupper(substr($user['name'], 0, 1)) : (isAuth() ? strtoupper(substr(auth()['name'], 0, 1)) : 'U');
$userName = isset($user) ? $user['name'] : (isAuth() ? auth()['name'] : '');
$userRole = isAuth() ? auth()['role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? e($title) . ' — ' : ''; ?>360° WIEV · Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body>

<div class="app">

  <!-- ── Sidebar ─────────────────────────────────────────────────── -->
  <aside class="side">

    <div class="side__brand">
      <div class="side__brand-mark">
        360<span class="deg">°</span>
      </div>
      <div class="side__brand-sub">WIEV · STUDIO</div>
    </div>

    <nav class="side__nav">

      <div class="side__group-label">Workspace</div>

      <a href="<?php echo url('/dashboard'); ?>" class="side__item <?php echo isActive('/dashboard'); ?>">
        <span class="side__icon">
          <svg viewBox="0 0 24 24" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
          </svg>
        </span>
        <span>Dashboard</span>
      </a>

      <a href="<?php echo url('/properties'); ?>" class="side__item <?php echo isActive('/properties'); ?>">
        <span class="side__icon">
          <svg viewBox="0 0 24 24" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
        </span>
        <span>Properties</span>
      </a>

      <a href="<?php echo url('/tours'); ?>" class="side__item <?php echo isActive('/tours'); ?>">
        <span class="side__icon">
          <svg viewBox="0 0 24 24" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
          </svg>
        </span>
        <span>Walkthroughs</span>
      </a>

      <div class="side__group-label">Analytics</div>

      <a href="<?php echo url('/analytics'); ?>" class="side__item <?php echo isActive('/analytics'); ?>">
        <span class="side__icon">
          <svg viewBox="0 0 24 24" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
        </span>
        <span>Analytics</span>
      </a>

      <div class="side__group-label">Account</div>

      <a href="<?php echo url('/subscriptions'); ?>" class="side__item <?php echo isActive('/subscriptions'); ?>">
        <span class="side__icon">
          <svg viewBox="0 0 24 24" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="5" width="20" height="14" rx="2"/>
            <line x1="2" y1="10" x2="22" y2="10"/>
          </svg>
        </span>
        <span>Subscription</span>
      </a>

      <?php if ($userRole === 'tenant_admin'): ?>
      <a href="<?php echo url('/users'); ?>" class="side__item <?php echo isActive('/users'); ?>">
        <span class="side__icon">
          <svg viewBox="0 0 24 24" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </span>
        <span>Team</span>
      </a>
      <?php endif; ?>

      <?php if ($userRole === 'platform_admin'): ?>
      <a href="<?php echo url('/tenants'); ?>" class="side__item <?php echo isActive('/tenants'); ?>">
        <span class="side__icon">
          <svg viewBox="0 0 24 24" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          </svg>
        </span>
        <span>Tenants</span>
      </a>
      <?php endif; ?>

    </nav>

    <div class="side__foot">
      <div class="side__avatar"><?php echo $userInitial; ?></div>
      <div class="side__user">
        <div class="side__user-name"><?php echo e($userName); ?></div>
        <div class="side__user-plan">STUDIO</div>
      </div>
      <a href="<?php echo url('/logout'); ?>" class="side__logout">Out</a>
    </div>

  </aside>

  <!-- ── Main area ─────────────────────────────────────────────── -->
  <div class="main">

    <?php if (hasFlash()): ?>
    <div class="flash-stack">
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

    <?php echo $content; ?>

  </div><!-- /.main -->

</div><!-- /.app -->

<script src="<?php echo asset('js/app.js'); ?>"></script>
</body>
</html>
