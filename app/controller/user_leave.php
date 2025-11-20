
<?php
require_once '../app/core/session_helper.php';

// Check if employee is logged in
requireEmployeeAuth();

// Log user activity
logUserActivity('Access employee leave page');

require_once '../app/core/database.php';

// Get user ID - support employees, managers, and HR
$employee_id = $_SESSION['employee_id'] ?? $_SESSION['employee_no'] ?? null;
$manager_id = $_SESSION['manager_id'] ?? null;
$hr_id = $_SESSION['SESSION_USER_ID'] ?? null;

// Determine user type and ID
$user_type = 'employee';
$user_id = $employee_id;

if ($hr_id) {
    $user_type = 'hr';
    $user_id = $hr_id;
} elseif ($manager_id) {
    $user_type = 'manager';
    $user_id = $manager_id;
}

$db = new Database();
$conn = $db->getConnection();

// Fetch leaves based on user type
$leaves = [];
if ($user_id) {
    if ($user_type === 'employee') {
        $sql = "SELECT * FROM leaves WHERE employee_id = :user_id AND applicant_type = 'employee' ORDER BY created_at DESC";
        $leaves = $db->query($sql, [':user_id' => $user_id]);
    } elseif ($user_type === 'manager') {
        $sql = "SELECT * FROM leaves WHERE applicant_manager_id = :user_id AND applicant_type = 'manager' ORDER BY created_at DESC";
        $leaves = $db->query($sql, [':user_id' => $user_id]);
    } elseif ($user_type === 'hr') {
        $sql = "SELECT * FROM leaves WHERE applicant_hr_id = :user_id AND applicant_type = 'hr' ORDER BY created_at DESC";
        $leaves = $db->query($sql, [':user_id' => $user_id]);
    }
}

// Render the page layout
require views_path("user/user_leave");
