<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $title ?? 'Default Title' ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="Login Form">

    <link rel="icon" type="image/png" href="../public/assets/image/test_logo2.png">

    <!-- Fonts and Animations -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../public/assets/css/fontawesome/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Bootstrap Icons (Offline) -->
    <link rel="stylesheet" href="../public/icons/bootstrap-icons.css">

    <!-- Load AOS styles -->
    <link rel="stylesheet" href="../public/assets/css/aos/aos.css">

    <!-- ✅ Conditional CSS -->
    <?php if (isset($url) && $url === 'register'): ?>
        <link rel="stylesheet" href="../public/assets/css/register.css">
    <?php endif; ?>

    <?php if (isset($url) && $url === 'dashboard1'): ?>
        <link rel="stylesheet" href="../public/assets/css/dashboard.css">
    <?php endif; ?>

    <?php if (isset($url) && $url === 'attendance'): ?>
        <link rel="stylesheet" href="../public/assets/css/attendance.css">
    <?php endif; ?>

    <?php if (isset($url) && $url === 'login1'): ?>
        <link rel="stylesheet" href="../public/assets/css/login1.css">
    <?php endif; ?>

    <!-- ✅ Always loaded (shared across pages) -->
    <link rel="stylesheet" href="../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../public/assets/css/nav.css">
    <!-- <link rel="stylesheet" href="../src/output.css"> -->
    <link rel="stylesheet" href="../public/assets/css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../public/assets/css/flatpickr/material_green.css">
    <link rel="stylesheet" href="../public/assets/css/flatpickr/monthSelect/style.css">
    <!-- <link rel="stylesheet" href="../public/assets/css/flatpickr/flatpickr.css"> -->

    <!-- ✅ JS -->
    <script src="../public/assets/js/flatpickr/flatpickr.min.js"></script>
    <script src="../public/assets/js/flatpickr/monthSelect/index.js"></script>
    
    <!-- Suppress console warnings before loading Tailwind -->
    <script>
        (function() {
            const originalWarn = console.warn;
            const originalLog = console.log;
            const originalError = console.error;
            
            console.warn = function(...args) {
                const message = args.join(' ');
                // Suppress Tailwind CDN production warning
                if (message.includes('cdn.tailwindcss.com') && message.includes('should not be used in production')) {
                    return;
                }
                originalWarn.apply(console, args);
            };
            
            console.log = function(...args) {
                const message = args.join(' ');
                // Suppress content-script.js messages
                if (message.includes('content-script.js') || 
                    message.includes('Document already loaded') ||
                    message.includes('Attempting to initialize') ||
                    message.includes('AdUnit initialized')) {
                    return;
                }
                originalLog.apply(console, args);
            };
            
            console.error = function(...args) {
                const message = args.join(' ');
                // Suppress content-script.js errors
                if (message.includes('content-script.js')) {
                    return;
                }
                originalError.apply(console, args);
            };
        })();
    </script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Removed Tailwind CDN to use offline build (src/output.css) -->
    <script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="../node_modules/flowbite/dist/flowbite.min.js"></script>
    
    <!-- Global CSRF Token -->
    <script>
        window.csrfToken = '<?php echo generateCSRFToken(); ?>';
    </script>
</head>
<body>
