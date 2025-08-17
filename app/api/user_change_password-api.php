<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/database.php';

// Start session to get user data
session_start();

// Check if user is logged in
if (!isset($_SESSION['employee_no']) && !isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

$currentPassword = $input['currentPassword'] ?? '';
$newPassword = $input['newPassword'] ?? '';
$confirmPassword = $input['confirmPassword'] ?? '';

// Validation
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'New password and confirm password do not match']);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long']);
    exit;
}

try {
    // Get employee ID
    $employeeNo = $_SESSION['employee_no'] ?? $_SESSION['employee_id'];
    
    // Get current password hash
    $stmt = $conn->prepare("SELECT password FROM employees WHERE employee_no = :employee_no OR id = :employee_id");
    $stmt->execute([':employee_no' => $employeeNo, ':employee_id' => $employeeNo]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
        exit;
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $employee['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $conn->prepare("UPDATE employees SET password = :password, updated_at = NOW() WHERE employee_no = :employee_no OR id = :employee_id");
    $result = $stmt->execute([
        ':password' => $hashedPassword,
        ':employee_no' => $employeeNo,
        ':employee_id' => $employeeNo
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
