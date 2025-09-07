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
            error_log($e->getMessage());
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

