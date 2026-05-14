<?php
// FILE: /app/views/layouts/public.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- viewport-fit=cover lets the viewer extend under iOS notch/home indicator -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0a0908">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <title><?php echo isset($title) ? e($title) . ' — ' : ''; ?>360° WIEV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <!-- Pannellum 360° viewer -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
    <style>
      /* Reset for mobile-first viewer */
      html, body {
        margin: 0; padding: 0;
        overflow: hidden;
        height: 100%;
        /* iOS Safari address-bar-aware viewport. Falls back to 100vh. */
        height: 100dvh;
        overscroll-behavior: none;       /* no rubber-band scroll */
        -webkit-tap-highlight-color: transparent;
        -webkit-touch-callout: none;     /* no long-press menu on photos */
        background: #0a0908;
      }
      .viewer-shell {
        height: 100dvh;
        height: 100vh; /* fallback */
        height: 100dvh;
        display: flex;
        flex-direction: column;
        position: relative;
      }
      /* Override Pannellum default container styles to fit our shell */
      #panorama-viewer {
        position: relative;
        touch-action: none; /* let Pannellum own all touches inside */
      }
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
