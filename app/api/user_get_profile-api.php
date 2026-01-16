<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/secure_session.php';

// Start secure session to get user data
startSecureSession();

// Check if user is logged in (employee, manager, or HR)
$isEmployee = isset($_SESSION['employee_no']) || isset($_SESSION['employee_id']);
$isManager = isset($_SESSION['manager_id']) && !empty($_SESSION['manager_id']);
$isHR = isset($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['SESSION_USER_ID']);

if (!$isEmployee && !$isManager && !$isHR) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $profileData = null;
    
    // Fetch HR/Admin data
    if ($isHR) {
        $hrId = $_SESSION['SESSION_USER_ID'];
        $stmt = $conn->prepare("SELECT hr_first_name, hr_middle_name, hr_last_name, hr_email, hr_contact_number, hr_position, hr_address, hr_dob, hr_place_of_birth, hr_sex, hr_civil_status, hr_citizenship, hr_blood_type, hr_photo_path, hr_created_at, hr_updated_at FROM admins WHERE id = :hr_id AND deleted_at IS NULL");
        $stmt->execute([':hr_id' => $hrId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            $firstName = ucwords(strtolower(trim($userData['hr_first_name'] ?? '')));
            $middleName = $userData['hr_middle_name'] ?? '';
            $lastName = ucwords(strtolower(trim($userData['hr_last_name'] ?? '')));
            
            $displayName = trim($firstName . ' ' . ($middleName ? ucwords(strtolower(trim($middleName)))[0] . '. ' : '') . $lastName);
            $fullName = trim($firstName . ' ' . ($middleName ? ucwords(strtolower(trim($middleName))) . ' ' : '') . $lastName);
            
            $profileData = [
                'name' => $displayName,
                'full_name' => $fullName,
                'email' => $userData['hr_email'] ?? '',
                'contact_number' => $userData['hr_contact_number'] ?? '',
                'position' => $userData['hr_position'] ?? '',
                'address' => $userData['hr_address'] ?? '',
                'dob' => $userData['hr_dob'] ?? '',
                'place_of_birth' => $userData['hr_place_of_birth'] ?? '',
                'sex' => $userData['hr_sex'] ?? '',
                'civil_status' => $userData['hr_civil_status'] ?? '',
                'citizenship' => $userData['hr_citizenship'] ?? '',
                'blood_type' => $userData['hr_blood_type'] ?? '',
                'photo_path' => $userData['hr_photo_path'] ?? '',
                'created_at' => $userData['hr_created_at'] ?? '',
                'updated_at' => $userData['hr_updated_at'] ?? '',
                'user_type' => 'hr'
            ];
        }
    }
    // Fetch Manager data
    elseif ($isManager) {
        $managerId = $_SESSION['manager_id'];
        $stmt = $conn->prepare("SELECT m_first_name, m_middle_name, m_last_name, m_email, m_contact_number, m_position, m_address, m_dob, m_place_of_birth, m_sex, m_civil_status, m_citizenship, m_blood_type, m_photo_path, m_created_at, m_updated_at FROM managers WHERE id = :manager_id AND deleted_at IS NULL");
        $stmt->execute([':manager_id' => $managerId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            $firstName = ucwords(strtolower(trim($userData['m_first_name'] ?? '')));
            $middleName = $userData['m_middle_name'] ?? '';
            $lastName = ucwords(strtolower(trim($userData['m_last_name'] ?? '')));
            
            $displayName = trim($firstName . ' ' . ($middleName ? ucwords(strtolower(trim($middleName)))[0] . '. ' : '') . $lastName);
            $fullName = trim($firstName . ' ' . ($middleName ? ucwords(strtolower(trim($middleName))) . ' ' : '') . $lastName);
            
            $profileData = [
                'name' => $displayName,
                'full_name' => $fullName,
                'email' => $userData['m_email'] ?? '',
                'contact_number' => $userData['m_contact_number'] ?? '',
                'position' => $userData['m_position'] ?? '',
                'address' => $userData['m_address'] ?? '',
                'dob' => $userData['m_dob'] ?? '',
                'place_of_birth' => $userData['m_place_of_birth'] ?? '',
                'sex' => $userData['m_sex'] ?? '',
                'civil_status' => $userData['m_civil_status'] ?? '',
                'citizenship' => $userData['m_citizenship'] ?? '',
                'blood_type' => $userData['m_blood_type'] ?? '',
                'photo_path' => $userData['m_photo_path'] ?? '',
                'created_at' => $userData['m_created_at'] ?? '',
                'updated_at' => $userData['m_updated_at'] ?? '',
                'user_type' => 'manager'
            ];
        }
    }
    // Fetch Employee data
    else {
        $employeeNo = $_SESSION['employee_no'] ?? $_SESSION['employee_id'];
        $stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, contact_number, position, address, dob, place_of_birth, sex, civil_status, citizenship, blood_type, photo_path, created_at, updated_at FROM employees WHERE employee_no = :employee_no1 OR id = :employee_id");
        $stmt->execute([':employee_no1' => $employeeNo, ':employee_id' => $employeeNo]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            $firstName = ucwords(strtolower(trim($userData['first_name'] ?? '')));
            $middleName = $userData['middle_name'] ?? '';
            $lastName = ucwords(strtolower(trim($userData['last_name'] ?? '')));
            
            $displayName = trim($firstName . ' ' . ($middleName ? ucwords(strtolower(trim($middleName)))[0] . '. ' : '') . $lastName);
            $fullName = trim($firstName . ' ' . ($middleName ? ucwords(strtolower(trim($middleName))) . ' ' : '') . $lastName);
            
            $profileData = [
                'name' => $displayName,
                'full_name' => $fullName,
                'email' => $userData['email'] ?? '',
                'contact_number' => $userData['contact_number'] ?? '',
                'position' => $userData['position'] ?? '',
                'address' => $userData['address'] ?? '',
                'dob' => $userData['dob'] ?? '',
                'place_of_birth' => $userData['place_of_birth'] ?? '',
                'sex' => $userData['sex'] ?? '',
                'civil_status' => $userData['civil_status'] ?? '',
                'citizenship' => $userData['citizenship'] ?? '',
                'blood_type' => $userData['blood_type'] ?? '',
                'photo_path' => $userData['photo_path'] ?? '',
                'created_at' => $userData['created_at'] ?? '',
                'updated_at' => $userData['updated_at'] ?? '',
                'user_type' => 'employee'
            ];
        }
    }
    
    if (!$profileData) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'data' => $profileData]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
