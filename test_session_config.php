<?php
// Test session configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing session configuration...\n";

// Test 1: Check if session is not started
echo "Session status before: " . session_status() . "\n";

// Test 2: Include security config
require_once "app/core/SecurityConfig.php";
echo "SecurityConfig loaded\n";

// Test 3: Include secure session
require_once "app/core/secure_session.php";
echo "Secure session loaded\n";

// Test 4: Check session status after
echo "Session status after: " . session_status() . "\n";
echo "Session ID: " . (session_id() ?: 'none') . "\n";

// Test 5: Set some session data
$_SESSION['test'] = 'Hello World';
echo "Session data set\n";

echo "Test completed successfully!\n";
?>
