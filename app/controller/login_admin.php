<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../app/core/SecureAuth.php";
require_once "../app/core/secure_session.php";

$auth = new SecureAuth();

// Check if form is submitted and login_type is admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_type']) && $_POST['login_type'] === 'admin') {
    // Reset session for fresh login attempt
    resetSessionForLogin();

    // Check if required fields are set
    if (isset($_POST['a_email']) && isset($_POST['a_password'])) {
        $email = strtolower(trim($_POST['a_email']));
        $password = $_POST['a_password'];

        try {
            $result = $auth->authenticateAdmin($email, $password);
            
            if ($result['success']) {
                // Verify session is set before redirect
                error_log("=== login_admin.php BEFORE REDIRECT ===");
                error_log("Session ID: " . session_id());
                error_log("SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
                error_log("SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
                error_log("isAdminLoggedIn(): " . (isAdminLoggedIn() ? 'TRUE' : 'FALSE'));
                error_log("Session cookie name: " . session_name());
                error_log("Session cookie value: " . (isset($_COOKIE[session_name()]) ? $_COOKIE[session_name()] : 'NOT SET'));
                error_log("Redirect URL: " . $result['redirect']);
                
                // CRITICAL: Ensure session cookie is set before redirect
                // The session data is already in $_SESSION, we just need to ensure
                // the cookie is sent with the redirect headers
                // PHP will automatically write the session and send the cookie when headers are sent
                
                // Set session cookie manually to ensure it's sent
                $cookieName = session_name();
                $cookieValue = session_id();
                $cookieLifetime = 86400; // 1 day
                $cookiePath = '/';
                $cookieDomain = '';
                $cookieSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
                $cookieHttpOnly = true;
                
                // Set the cookie using setcookie to ensure it's sent
                // Use the current session ID to ensure consistency
                $currentSessionId = session_id();
                setcookie($cookieName, $currentSessionId, time() + $cookieLifetime, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly);
                
                error_log("✅ Session cookie set manually: " . $cookieName . " = " . $currentSessionId);
                error_log("Final session state before redirect - SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
                error_log("Final session state before redirect - SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
                error_log("Session keys before redirect: " . implode(', ', array_keys($_SESSION ?? [])));
                
                // PHP will automatically write the session when the script ends
                // The session data is already in $_SESSION, and the cookie is now set
                // Redirect to admin dashboard
                header("Location: " . $result['redirect']);
                exit;
            } else {
                // Set error message in session
                $_SESSION['error'] = $result['message'];
            }
        } catch (Exception $e) {
            // Database error
            $_SESSION['error'] = 'Database error. Please try again later.';
            error_log("Login error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
    } else {
        // Missing email or password
        $_SESSION['error'] = 'Please enter both email and password.';
    }
}

// Pass error message to view
$msg = $_SESSION['error'] ?? '';
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}

require_once views_path("branch/login_admin");
?>

