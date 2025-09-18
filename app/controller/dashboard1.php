<?php
require_once '../app/core/session_helper.php';

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