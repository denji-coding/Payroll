<?php
// Load required files
require_once '../app/core/database.php';

// Create database connection
$db = new Database();
$conn = $db->getConnection();

// Check if admin is logged in
// You can enable this later if you want to block unauthorized access
// session_start();
// if (!isset($_SESSION['admin_id'])) {
//     http_response_code(403);
//     require_once '../app/Error/unauthorized.php';
//     exit;
// }

// Fetch all active (not deleted) employees
$sql = "SELECT * FROM employees WHERE deleted_at IS NULL AND approved_by_manager = 1 ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all managers (for dropdowns or assignments)
$sql = "SELECT * FROM managers ORDER BY m_created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get last manager (optional)
$lastManager = null;
$lastManagerQuery = $conn->query("SELECT * FROM managers ORDER BY m_created_at DESC LIMIT 1");
if ($lastManagerQuery && $lastManagerQuery->rowCount() > 0) {
    $lastManager = $lastManagerQuery->fetch(PDO::FETCH_ASSOC);
}

// Pass data to the view
$data = [
    'employees' => $employees,
    'managers' => $managers,
    'lastManager' => $lastManager
];

// Render the employee management page
require views_path("auth/employees");
