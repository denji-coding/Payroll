<?php
// Debug session and routing issues
require_once "app/core/secure_session.php";

echo "<h1>Session Debug</h1>";
echo "<pre>";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Data:\n";
print_r($_SESSION);
echo "\nCookies:\n";
print_r($_COOKIE);
echo "</pre>";

// Test if we're being redirected correctly
if (isset($_GET['payroll'])) {
    echo "<p>Payroll parameter: " . $_GET['payroll'] . "</p>";
}

// Check if logged_out is set
if (isset($_SESSION['logged_out'])) {
    echo "<p style='color: red;'>WARNING: logged_out session variable is set!</p>";
    echo "<p>Value: " . $_SESSION['logged_out'] . "</p>";
}

// Check if login_success is set
if (isset($_SESSION['login_success'])) {
    echo "<p style='color: green;'>SUCCESS: login_success session variable is set!</p>";
    echo "<p>Value: " . $_SESSION['login_success'] . "</p>";
}

// Check user authentication
if (isset($_SESSION['user_type'])) {
    echo "<p style='color: blue;'>User Type: " . $_SESSION['user_type'] . "</p>";
}

if (isset($_SESSION['user_id'])) {
    echo "<p style='color: blue;'>User ID: " . $_SESSION['user_id'] . "</p>";
}

// Test authentication functions
echo "<h2>Authentication Status</h2>";
require_once "app/core/session_helper.php";

echo "<p>isAdminLoggedIn(): " . (isAdminLoggedIn() ? 'true' : 'false') . "</p>";
echo "<p>isManagerLoggedIn(): " . (isManagerLoggedIn() ? 'true' : 'false') . "</p>";
echo "<p>isEmployeeLoggedIn(): " . (isEmployeeLoggedIn() ? 'true' : 'false') . "</p>";
echo "<p>getCurrentUserType(): " . getCurrentUserType() . "</p>";

// Test session validation
echo "<h2>Session Validation</h2>";
echo "<p>isAuthenticated(): " . (isAuthenticated() ? 'true' : 'false') . "</p>";
echo "<p>validateSession(): " . (validateSession() ? 'true' : 'false') . "</p>";

// Check session timeout
echo "<h2>Session Timeout Info</h2>";
echo "<p>Current time: " . time() . "</p>";
echo "<p>Last activity: " . ($_SESSION['last_activity'] ?? 'not set') . "</p>";
if (isset($_SESSION['last_activity'])) {
    echo "<p>Time since last activity: " . (time() - $_SESSION['last_activity']) . " seconds</p>";
}
?>

