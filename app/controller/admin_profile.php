<?php
require_once '../app/core/session_helper.php';
// Database not needed here

// Enforce admin authentication
if (function_exists('requireAdminAuth')) {
    requireAdminAuth();
} else {
    if (!isset($_SESSION['SESSION_EMAIL'])) {
        header('Location: index.php?payroll=login1&type=admin');
        exit();
    }
}

// Optional: activity log
if (function_exists('logUserActivity')) {
    logUserActivity('Access admin profile settings');
}

date_default_timezone_set('Asia/Manila');

// No DB operations needed in this controller

$title = 'Admin Profile Settings';

require views_path('auth/admin_profile');
?>

