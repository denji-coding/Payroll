
<?php
require_once '../app/core/session_helper.php';

// Check if employee is logged in
requireEmployeeAuth();

// Log user activity
logUserActivity('Access employee leave page');

require_once '../app/core/database.php';

// Get user ID - support employees, managers, and HR
// Note: Leave system currently only supports employees
$employee_id = $_SESSION['employee_id'] ?? $_SESSION['employee_no'] ?? null;

$db = new Database();
$conn = $db->getConnection();

// Fetch leaves - only if employee_id is available
$leaves = [];
if ($employee_id) {
    $sql = "SELECT * FROM leaves WHERE employee_id = :employee_id ORDER BY created_at DESC";
    $leaves = $db->query($sql, [':employee_id' => $employee_id]);
}

// require views_path("user/user_leave");

// Render the page layout
require views_path("user/user_leave");
