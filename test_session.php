<?php
// Simple session test for InfinityFree
echo "<h1>Session Test</h1>";

// Check if sessions are enabled
if (!function_exists('session_start')) {
    die("Sessions are not available");
}

// Start session
session_start();

// Test session write
$_SESSION['test'] = 'Hello from ' . date('Y-m-d H:i:s');

echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session data: " . $_SESSION['test'] . "</p>";

// Test session read
if (isset($_SESSION['test'])) {
    echo "<p style='color: green;'>✓ Session write/read works!</p>";
} else {
    echo "<p style='color: red;'>✗ Session write/read failed!</p>";
}

// Show all session data
echo "<h2>All Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check session directory
$sessionPath = ini_get('session.save_path');
if (empty($sessionPath)) {
    $sessionPath = sys_get_temp_dir();
}
echo "<p>Session save path: $sessionPath</p>";
echo "<p>Session save path writable: " . (is_writable($sessionPath) ? 'Yes' : 'No') . "</p>";

// Test form to maintain session across requests
echo "<h2>Test Session Persistence:</h2>";
echo "<form method='post'>";
echo "<input type='submit' name='test' value='Refresh Page'>";
echo "</form>";

if (isset($_POST['test'])) {
    echo "<p style='color: green;'>✓ Session persisted across request!</p>";
}
?>


