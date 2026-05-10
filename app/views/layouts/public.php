<?php
// FILE: /app/views/layouts/public.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? e($title) . ' — ' : ''; ?>360° WIEV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <!-- Pannellum 360° viewer -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
    <style>
      /* Override Pannellum default container styles to fit our shell */
      #panorama-viewer { position:relative; }
      .pnlm-container { background:#0a0908 !important; }
      /* Hide default Pannellum UI chrome */
      .pnlm-controls-container { display:none !important; }
      .pnlm-about-msg { display:none !important; }
    </style>
</head>
<body>

<div class="viewer-shell">
    <?php echo $content; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
<script src="<?php echo asset('js/viewer.js'); ?>"></script>
</body>
</html>
