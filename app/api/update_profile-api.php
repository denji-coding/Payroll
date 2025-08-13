<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in as manager
if (!isset($_SESSION['manager_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Validate required fields
    $field = $input['field'] ?? '';
    $value = $input['value'] ?? '';
    
    if (empty($field) || empty($value)) {
        throw new Exception('Field and value are required');
    }
    
    // Validate field type
    if (!in_array($field, ['fullName', 'email'])) {
        throw new Exception('Invalid field type');
    }
    
    // Database connection
    require_once '../core/database.php';
    $conn = new Database();
    $pdo = $conn->getConnection();
    
    if ($field === 'fullName') {
        // Parse full name into components
        $nameParts = explode(' ', trim($value));
        
        if (count($nameParts) < 2) {
            throw new Exception('Full name must contain at least first and last name');
        }
        
        $firstName = $nameParts[0];
        $lastName = end($nameParts);
        $middleName = '';
        
        // If there are more than 2 parts, middle name is everything in between
        if (count($nameParts) > 2) {
            $middleParts = array_slice($nameParts, 1, -1);
            $middleName = implode(' ', $middleParts);
        }
        
        // Update first, middle, and last names
        $stmt = $pdo->prepare("UPDATE managers SET m_first_name = :first_name, m_middle_name = :middle_name, m_last_name = :last_name, m_updated_at = NOW() WHERE id = :id");
        $result = $stmt->execute([
            ':first_name' => $firstName,
            ':middle_name' => $middleName,
            ':last_name' => $lastName,
            ':id' => $_SESSION['manager_id']
        ]);
        
        if (!$result) {
            throw new Exception('Failed to update name');
        }
        
        $message = 'Name updated successfully';
        
    } elseif ($field === 'email') {
        // Validate email format
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Check if email already exists for another manager
        $stmt = $pdo->prepare("SELECT id FROM managers WHERE m_email = :email AND id != :id");
        $stmt->execute([':email' => $value, ':id' => $_SESSION['manager_id']]);
        
        if ($stmt->fetch()) {
            throw new Exception('Email already exists for another manager');
        }
        
        // Update email
        $stmt = $pdo->prepare("UPDATE managers SET m_email = :email, m_updated_at = NOW() WHERE id = :id");
        $result = $stmt->execute([
            ':email' => $value,
            ':id' => $_SESSION['manager_id']
        ]);
        
        if (!$result) {
            throw new Exception('Failed to update email');
        }
        
        $message = 'Email updated successfully';
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
