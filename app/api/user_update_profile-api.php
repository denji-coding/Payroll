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

// Determine user type and ID
$userType = 'employee';
$userId = null;
$userTable = 'employees';
$userField = 'employee_no';

if ($isHR) {
    $userType = 'hr';
    $userId = $_SESSION['SESSION_USER_ID'];
    $userTable = 'admins';
    $userField = 'id';
} elseif ($isManager) {
    $userType = 'manager';
    $userId = $_SESSION['manager_id'];
    $userTable = 'managers';
    $userField = 'id';
} else {
    // For employees, get the actual ID from database if we only have employee_no
    $employeeNo = $_SESSION['employee_no'] ?? $_SESSION['employee_id'];
    if (isset($_SESSION['employee_id']) && is_numeric($_SESSION['employee_id'])) {
        $userId = (int)$_SESSION['employee_id'];
    } else {
        // Get ID from employee_no - initialize DB connection temporarily
        $tempDb = new Database();
        $tempConn = $tempDb->getConnection();
        $stmt = $tempConn->prepare("SELECT id FROM employees WHERE employee_no = ? OR id = ? LIMIT 1");
        $stmt->execute([$employeeNo, $employeeNo]);
        $emp = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $emp ? (int)$emp['id'] : $employeeNo;
    }
    $userTable = 'employees';
    $userField = 'employee_no';
}

// Initialize database connection
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
    // Map field names to database columns based on user type
    $fieldMap = [
        'employee' => [
            'fullName' => ['first_name', 'middle_name', 'last_name'],
            'email' => 'email',
            'contactNumber' => 'contact_number'
        ],
        'manager' => [
            'fullName' => ['m_first_name', 'm_middle_name', 'm_last_name'],
            'email' => 'm_email',
            'contactNumber' => 'm_contact_number'
        ],
        'hr' => [
            'fullName' => ['hr_first_name', 'hr_middle_name', 'hr_last_name'],
            'email' => 'hr_email',
            'contactNumber' => 'hr_contact_number'
        ]
    ];
    
    $userFieldMap = $fieldMap[$userType] ?? $fieldMap['employee'];
    
    if ($field === 'fullName') {
        // Handle name update - split into first, middle, last names
        $nameParts = explode(' ', trim($value));
        $firstName = $nameParts[0];
        $lastName = end($nameParts);
        $middleName = '';
        
        if (count($nameParts) > 2) {
            $middleName = implode(' ', array_slice($nameParts, 1, -1));
        }
        
        // Get column names for this user type
        $nameFields = $userFieldMap['fullName'];
        $firstNameCol = $nameFields[0];
        $middleNameCol = $nameFields[1];
        $lastNameCol = $nameFields[2];
        
        // Build WHERE clause based on user type
        if ($userType === 'employee') {
            $whereClause = "employee_no = :user_id1 OR id = :user_id2";
        } else {
            $whereClause = "id = :user_id";
        }
        
        // Update name fields
        $stmt = $conn->prepare("UPDATE $userTable SET $firstNameCol = :first_name, $middleNameCol = :middle_name, $lastNameCol = :last_name, " . 
                              ($userType === 'employee' ? 'updated_at' : ($userType === 'manager' ? 'm_updated_at' : 'hr_updated_at')) . " = NOW() WHERE $whereClause");
        
        $params = [
            ':first_name' => $firstName,
            ':middle_name' => $middleName,
            ':last_name' => $lastName
        ];
        
        if ($userType === 'employee') {
            $params[':user_id1'] = $userId;
            $params[':user_id2'] = $userId;
        } else {
            $params[':user_id'] = $userId;
        }
        
        $result = $stmt->execute($params);
    } else {
        // Get column name for this field and user type
        $dbField = $userFieldMap[$field] ?? '';
        
        if (empty($dbField)) {
            echo json_encode(['success' => false, 'message' => 'Invalid field']);
            exit;
        }
        
        // Check for duplicate email if updating email
        if ($field === 'email') {
            $emailCol = $userFieldMap['email'];
            if ($userType === 'employee') {
                $stmt = $conn->prepare("SELECT id FROM $userTable WHERE $emailCol = :email AND (employee_no != :user_id1 AND id != :user_id2)");
                $stmt->execute([':email' => $value, ':user_id1' => $userId, ':user_id2' => $userId]);
            } else {
                $stmt = $conn->prepare("SELECT id FROM $userTable WHERE $emailCol = :email AND id != :user_id" . ($userType === 'hr' ? " AND deleted_at IS NULL" : " AND deleted_at IS NULL"));
                $stmt->execute([':email' => $value, ':user_id' => $userId]);
            }
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                exit;
            }
        }
        
        // Build WHERE clause and update field
        if ($userType === 'employee') {
            $whereClause = "employee_no = :user_id1 OR id = :user_id2";
            $updateField = "updated_at";
        } else {
            $whereClause = "id = :user_id";
            $updateField = $userType === 'manager' ? 'm_updated_at' : 'hr_updated_at';
        }
        
        // Update field
        $stmt = $conn->prepare("UPDATE $userTable SET $dbField = :value, $updateField = NOW() WHERE $whereClause");
        
        if ($userType === 'employee') {
            $result = $stmt->execute([
                ':value' => $value,
                ':user_id1' => $userId,
                ':user_id2' => $userId
            ]);
        } else {
            $result = $stmt->execute([
                ':value' => $value,
                ':user_id' => $userId
            ]);
        }
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
