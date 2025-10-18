<?php
require_once '../app/core/session_helper.php';

// Log action before clearing
logUserActivity('Owner requested logout');

// Set a short-lived cookie flash for logout success (survives session destroy)
setcookie('owner_logged_out', '1', time() + 300, '/');

// Clear all sessions to avoid lingering auth from other roles
clearAllSessions();

// Destroy PHP session entirely
if (session_status() === PHP_SESSION_ACTIVE) {
    // Unset all session variables
    $_SESSION = [];

    // Delete the session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

// Redirect to owner login
header('Location: index.php?payroll=owner_login');
exit;
?>


