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

$field = $input['field'] ?? '';
$value = $input['value'] ?? '';

// Validation
if (empty($field) || empty($value)) {
    echo json_encode(['success' => false, 'message' => 'Field and value are required']);
    exit;
}

// Validate field names
$allowedFields = ['fullName', 'email', 'contactNumber'];
if (!in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'message' => 'Invalid field']);
    exit;
}

// Validate email format
if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate full name
if ($field === 'fullName') {
    $nameParts = explode(' ', trim($value));
    if (count($nameParts) < 2) {
        echo json_encode(['success' => false, 'message' => 'Full name must contain at least first and last name']);
        exit;
    }
}

try {
    // Get employee ID
    $employeeNo = $_SESSION['employee_no'] ?? $_SESSION['employee_id'];
    
    // Map field names to database columns
    $fieldMap = [
        'fullName' => 'name_update', // Special handling for name
        'email' => 'email',
        'contactNumber' => 'contact_number'
    ];
    
    $dbField = $fieldMap[$field] ?? '';
    
    if ($field === 'fullName') {
        // Handle name update - split into first, middle, last names
        $nameParts = explode(' ', trim($value));
        $firstName = $nameParts[0];
        $lastName = end($nameParts);
        $middleName = '';
        
        if (count($nameParts) > 2) {
            $middleName = implode(' ', array_slice($nameParts, 1, -1));
        }
        
        // Check for duplicate email if updating email
        if ($field === 'email') {
            $stmt = $conn->prepare("SELECT id FROM employees WHERE email = :email AND (employee_no != :employee_no AND id != :employee_id)");
            $stmt->execute([':email' => $value, ':employee_no' => $employeeNo, ':employee_id' => $employeeNo]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                exit;
            }
        }
        
        // Update name fields
        $stmt = $conn->prepare("UPDATE employees SET first_name = :first_name, middle_name = :middle_name, last_name = :last_name, updated_at = NOW() WHERE employee_no = :employee_no OR id = :employee_id");
        $result = $stmt->execute([
            ':first_name' => $firstName,
            ':middle_name' => $middleName,
            ':last_name' => $lastName,
            ':employee_no' => $employeeNo,
            ':employee_id' => $employeeNo
        ]);
    } else {
        // Check for duplicate email if updating email
        if ($field === 'email') {
            $stmt = $conn->prepare("SELECT id FROM employees WHERE email = :email AND (employee_no != :employee_no AND id != :employee_id)");
            $stmt->execute([':email' => $value, ':employee_no' => $employeeNo, ':employee_id' => $employeeNo]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                exit;
            }
        }
        
        // Update other fields
        $stmt = $conn->prepare("UPDATE employees SET $dbField = :value, updated_at = NOW() WHERE employee_no = :employee_no OR id = :employee_id");
        $result = $stmt->execute([
            ':value' => $value,
            ':employee_no' => $employeeNo,
            ':employee_id' => $employeeNo
        ]);
    }
    
    if ($result) {
        $message = ucfirst($field) . ' updated successfully';
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
