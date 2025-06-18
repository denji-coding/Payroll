<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php'; 
$db = new Database();

// Ensure the user is logged in
$employee_id = $_SESSION['employee_id'] ?? null;

if (!$employee_id) {
    // If API request (e.g. ?id=...), return JSON error
    if (isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    } else {
        // For normal page request, show custom 403 page
        http_response_code(403);  // Set HTTP status 403 Forbidden
        require_once '../app/Error/unauthorized.php';  // adjust path accordingly
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = $_POST['leave_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    $valid_leave_types = ['Sick Leave', 'Emergency Leave', 'Vacation Leave', 'Personal Leave', 'Maternity/Paternity Leave'];

    if (!in_array($leave_type, $valid_leave_types)) {
        die('Invalid leave type.');
    }
    if (!$start_date || !$end_date || !$reason) {
        die('Please fill in all required fields.');
    }
    if ($end_date < $start_date) {
        die('End date cannot be before start date.');
    }

    $start_date_obj = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);
    $duration = $start_date_obj->diff($end_date_obj)->days + 1;

    $sql = "INSERT INTO leaves (employee_id, leave_type, start_date, end_date, duration, reason) 
            VALUES (:employee_id, :leave_type, :start_date, :end_date, :duration, :reason)";

    $params = [
        ':employee_id' => $employee_id,
        ':leave_type' => $leave_type,
        ':start_date' => $start_date,
        ':end_date' => $end_date,
        ':duration' => $duration,
        ':reason' => $reason,
    ];

    try {
        $inserted = $db->query($sql, $params);
        $_SESSION['success'] = $inserted ? 'Leave application submitted.' : 'Failed to submit leave.';
    } catch (Exception $e) {
        error_log("Leave application error: " . $e->getMessage());
        $_SESSION['error'] = 'An error occurred.';
    }

    header('Location: index.php?payroll=user_leave');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {

    $leaveId = $_GET['id'];

    // Optional: Check if the leave is still pending before deleting
    $sql = "SELECT * FROM leaves WHERE id = :id AND status = 'Pending' AND employee_id = :employee_id";
    $leaveCheck = $db->query($sql, [':id' => $leaveId, ':employee_id' => $employee_id]);

    if ($leaveCheck && count($leaveCheck) > 0) {
        // Delete leave
        $sqlDelete = "DELETE FROM leaves WHERE id = :id AND employee_id = :employee_id";
        $deleted = $db->query($sqlDelete, [':id' => $leaveId, ':employee_id' => $employee_id]);

        if ($deleted) {
            $_SESSION['success'] = "Leave application deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete leave application.";
        }
    } else {
        $_SESSION['error'] = "Only pending leave applications can be deleted.";
    }

    header("Location: index.php?payroll=user_leave");
    exit;
}

// ✅ Fetch only current employee’s leaves
$sql = "SELECT * FROM leaves WHERE employee_id = :employee_id ORDER BY created_at DESC";
$leaves = $db->query($sql, [':employee_id' => $employee_id]);




// ✅ Load the view after processing
require views_path("user/user_leave");
