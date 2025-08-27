<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $title ?? 'Default Title' ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="Login Form">

    <link rel="icon" type="image/png" href="../public/assets/image/logo.png">

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
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Removed Tailwind CDN to use offline build (src/output.css) -->
    <script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>
</head>
<body>
