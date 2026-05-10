<?php
// FILE: /app/views/layouts/auth.php
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

<div class="auth-shell">

  <?php if (hasFlash()): ?>
  <div style="position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:999;width:400px;max-width:90vw;">
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

  <div class="auth-box fade-up">
    <div class="auth-brand">
      <div class="auth-brand__mark">360<span class="deg">°</span></div>
      <div class="auth-brand__sub">WIEV · STUDIO</div>
    </div>
    <div class="auth-card">
      <?php echo $content; ?>
    </div>
  </div>

</div>

<script src="<?php echo asset('js/app.js'); ?>"></script>
</body>
</html>
