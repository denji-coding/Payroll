<?php

// $admin_id = $_SESSION['admin_id'] ?? null;

// if (!$admin_id) {
//     // If it's an API request, return JSON
//     if (isset($_GET['id'])) {
//         header('Content-Type: application/json');
//         echo json_encode(['error' => 'Unauthorized admin access']);
//         exit;
//     } else {
//         // Show custom 403 page for browser access
//         http_response_code(403);
//          require_once '../app/Error/unauthorized.php'; // Adjust the path
//         exit;
//     }
// }
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';

if (!isset($_SESSION['SESSION_EMAIL'])) {
    header("Location: index.php?payroll=login1&type=admin");
    exit();
}

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

    // Save the modal ID to open after redirect
    // $_SESSION['open_modal'] = $leave_id;

    $_SESSION['success'] = "Leave request has been $newStatus.";
    header('Location: index.php?payroll=dashboard1');
    exit;
}

// After POST handling, require your view file
require views_path("auth/dashboard1");
