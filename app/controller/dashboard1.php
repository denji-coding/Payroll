<?php
require_once '../app/core/session_helper.php';

// DEBUG: Log session state before authentication check
$debugInfo = [
    'session_id' => session_id(),
    'session_name' => session_name(),
    'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'NOT ACTIVE',
    'MVC_PAYROLL_SESS_cookie' => $_COOKIE['MVC_PAYROLL_SESS'] ?? 'NOT SET',
    'PHPSESSID_cookie' => $_COOKIE['PHPSESSID'] ?? 'NOT SET',
    'SESSION_EMAIL' => $_SESSION['SESSION_EMAIL'] ?? 'NOT SET',
    'SESSION_USER_ID' => $_SESSION['SESSION_USER_ID'] ?? 'NOT SET',
    'USERNAME' => $_SESSION['USERNAME'] ?? 'NOT SET',
    'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
    'user_type' => $_SESSION['user_type'] ?? 'NOT SET',
    'isAdminLoggedIn' => isAdminLoggedIn() ? 'TRUE' : 'FALSE',
    'session_keys' => array_keys($_SESSION ?? []),
    'session_count' => count($_SESSION ?? [])
];

error_log("=== dashboard1.php BEFORE requireAdminAuth() ===");
error_log(json_encode($debugInfo, JSON_PRETTY_PRINT));

// If session is empty, try to reload from cookie
if (empty($_SESSION) && isset($_COOKIE['MVC_PAYROLL_SESS']) && !empty($_COOKIE['MVC_PAYROLL_SESS'])) {
    error_log("⚠️ Session is empty! Attempting to reload from MVC_PAYROLL_SESS cookie...");
    
    // Close current session if active
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    
    // Start session with the cookie's session ID
    session_name('MVC_PAYROLL_SESS');
    session_id($_COOKIE['MVC_PAYROLL_SESS']);
    session_start();
    
    error_log("After reload - SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
    error_log("After reload - SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
    error_log("After reload - Session keys: " . implode(', ', array_keys($_SESSION ?? [])));
}

// Check if admin is logged in
requireAdminAuth();

// Log user activity
logUserActivity('Access admin dashboard');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';

try {
    // Create database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Query to count total employees
    $sql = "SELECT COUNT(*) AS total FROM employees";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get total count or default to 0 if no results
    $totalEmployees = $result ? $result['total'] : 0;

} catch (PDOException $e) {
    $totalEmployees = 0;
    error_log("Database error: " . $e->getMessage());
}

// Make totalEmployees variable available to the view
extract(['totalEmployees' => $totalEmployees]); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = $_POST['leave_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$leave_id || !in_array($action, ['approve', 'reject'])) {
        $_SESSION['error'] = 'Invalid request.';
        header('Location: index.php?payroll=dashboard1');
        exit;
    }

    $newStatus = $action === 'approve' ? 'Approved' : 'Rejected';

    // Use PDO connection ($conn) to prepare and execute
    $sql = "UPDATE leaves SET status = :status WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status' => $newStatus,
        ':id' => $leave_id,
    ]);

    // Log the action
    logUserActivity("Leave request $action", "Leave ID: $leave_id, Status: $newStatus");

    $_SESSION['success'] = "Leave request has been $newStatus.";
    header('Location: index.php?payroll=dashboard1');
    exit;
}

// After POST handling, require your view file
require views_path("auth/dashboard1");