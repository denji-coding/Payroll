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

try {
    // Get employee ID
    $employeeNo = $_SESSION['employee_no'] ?? $_SESSION['employee_id'];
    
    // Fetch latest employee data
    $stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, contact_number, position, address, dob, place_of_birth, sex, civil_status, citizenship, blood_type, photo_path, created_at, updated_at FROM employees WHERE employee_no = :employee_no OR id = :employee_id");
    $stmt->execute([':employee_no' => $employeeNo, ':employee_id' => $employeeNo]);
    $employeeData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employeeData) {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
        exit;
    }
    
    // Format the name with proper capitalization
    $firstName = ucwords(strtolower(trim($employeeData['first_name'] ?? '')));
    $middleName = $employeeData['middle_name'] ?? '';
    $lastName = ucwords(strtolower(trim($employeeData['last_name'] ?? '')));
    
    // Create both display name (with initial) and full name
    $displayName = trim($firstName . ' ' . ($middleName ? ucwords(strtolower(trim($middleName)))[0] . '. ' : '') . $lastName);
    $fullName = trim($firstName . ' ' . ($middleName ? ucwords(strtolower(trim($middleName))) . ' ' : '') . $lastName);
    
    $profileData = [
        'name' => $displayName,
        'full_name' => $fullName,
        'email' => $employeeData['email'] ?? '',
        'contact_number' => $employeeData['contact_number'] ?? '',
        'position' => $employeeData['position'] ?? '',
        'address' => $employeeData['address'] ?? '',
        'dob' => $employeeData['dob'] ?? '',
        'place_of_birth' => $employeeData['place_of_birth'] ?? '',
        'sex' => $employeeData['sex'] ?? '',
        'civil_status' => $employeeData['civil_status'] ?? '',
        'citizenship' => $employeeData['citizenship'] ?? '',
        'blood_type' => $employeeData['blood_type'] ?? '',
        'photo_path' => $employeeData['photo_path'] ?? '',
        'created_at' => $employeeData['created_at'] ?? '',
        'updated_at' => $employeeData['updated_at'] ?? ''
    ];
    
    echo json_encode(['success' => true, 'data' => $profileData]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
