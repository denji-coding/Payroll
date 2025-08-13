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
    $currentPassword = $input['currentPassword'] ?? '';
    $newPassword = $input['newPassword'] ?? '';
    $confirmPassword = $input['confirmPassword'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        throw new Exception('All fields are required');
    }
    
    // Validate password confirmation
    if ($newPassword !== $confirmPassword) {
        throw new Exception('New password and confirm password do not match');
    }
    
    // Validate password length
    if (strlen($newPassword) < 6) {
        throw new Exception('New password must be at least 6 characters long');
    }
    
    // Database connection
    require_once '../core/database.php';
    $conn = new Database();
    $pdo = $conn->getConnection();
    
    // Get current manager's password hash
    $stmt = $pdo->prepare("SELECT m_password FROM managers WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['manager_id']]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$manager) {
        throw new Exception('Manager not found');
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $manager['m_password'])) {
        throw new Exception('Current password is incorrect');
    }
    
    // Hash new password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in database
    $updateStmt = $pdo->prepare("UPDATE managers SET m_password = :password, m_updated_at = NOW() WHERE id = :id");
    $result = $updateStmt->execute([
        ':password' => $newPasswordHash,
        ':id' => $_SESSION['manager_id']
    ]);
    
    if (!$result) {
        throw new Exception('Failed to update password');
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully'
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
