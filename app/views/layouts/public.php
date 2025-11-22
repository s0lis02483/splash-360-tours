<?php
// FILE: /app/views/layouts/public.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? e($title) . ' - ' : ''; ?>360° Virtual Tour</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/viewer.css'); ?>">
</head>
<body class="public-tour">
    <?php echo $content; ?>

    <script src="<?php echo asset('js/viewer.js'); ?>"></script>
</body>
</html>
