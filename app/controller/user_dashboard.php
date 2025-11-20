<?php
require_once '../app/core/secure_session.php';
require_once '../app/core/session_helper.php';

// Debug: Log session state before auth check
error_log("=== USER_DASHBOARD ACCESS DEBUG ===");
error_log("Session ID: " . session_id());
error_log("Session name: " . session_name());
error_log("Session status: " . session_status());
error_log("Session data: " . json_encode([
    'employee_id' => $_SESSION['employee_id'] ?? 'NOT SET',
    'employee_no' => $_SESSION['employee_no'] ?? 'NOT SET',
    'can_access_employee_portal' => $_SESSION['can_access_employee_portal'] ?? 'NOT SET',
    'manager_id' => $_SESSION['manager_id'] ?? 'NOT SET',
    'SESSION_USER_ID' => $_SESSION['SESSION_USER_ID'] ?? 'NOT SET',
    'session_keys' => array_keys($_SESSION ?? [])
]));
error_log("Cookies: " . json_encode($_COOKIE));

// Output debug info as HTML comment for console inspection
echo "<!-- USER_DASHBOARD_DEBUG: " . json_encode([
    'session_id' => session_id(),
    'session_name' => session_name(),
    'employee_id' => $_SESSION['employee_id'] ?? 'NOT SET',
    'employee_no' => $_SESSION['employee_no'] ?? 'NOT SET',
    'can_access_employee_portal' => $_SESSION['can_access_employee_portal'] ?? 'NOT SET'
]) . " -->\n";

// Check if employee is logged in
requireEmployeeAuth();

// Log user activity
logUserActivity('Access employee dashboard');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require views_path("user/user_dashboard");




