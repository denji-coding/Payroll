<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/secure_session.php';
startSecureSession();
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/SecureAuth.php';

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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

$currentPassword = trim($input['currentPassword'] ?? '');
$newPassword = trim($input['newPassword'] ?? '');
$confirmPassword = trim($input['confirmPassword'] ?? '');

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
    $db = new Database();
    $conn = $db->getConnection();
    $auth = new SecureAuth();
    
    // Determine user type and get user data
    $user = null;
    $storedPassword = null;
    $result = false;
    
    if ($isHR) {
        $hrId = $_SESSION['SESSION_USER_ID'];
        
        $stmt = $conn->prepare("SELECT hr_password FROM admins WHERE id = :user_id AND deleted_at IS NULL");
        $stmt->execute([':user_id' => $hrId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $storedPassword = $user['hr_password'];
            
            // Verify current password using SecureAuth (handles both hashed and plain text)
            if (!$auth->verifyPassword($currentPassword, $storedPassword)) {
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                exit;
            }
            
            // Hash new password using SecureAuth (always hash, never store plain text)
            $hashedPassword = $auth->hashPassword($newPassword);
            
            // Fallback: If SecureAuth hashPassword fails, use PHP's password_hash with bcrypt
            if (empty($hashedPassword) || (!str_starts_with($hashedPassword, '$argon2id$') && !str_starts_with($hashedPassword, '$2y$') && !str_starts_with($hashedPassword, '$2a$'))) {
                // Fallback to bcrypt if Argon2ID is not available
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            }
            
            // Final verification: Ensure password is hashed (never store plain text)
            if (empty($hashedPassword) || (!str_starts_with($hashedPassword, '$argon2id$') && !str_starts_with($hashedPassword, '$2y$') && !str_starts_with($hashedPassword, '$2a$'))) {
                echo json_encode(['success' => false, 'message' => 'Password hashing failed. Please try again.']);
                exit;
            }
            
            // Update password (always hashed)
            $stmt = $conn->prepare("UPDATE admins SET hr_password = :password, hr_updated_at = NOW() WHERE id = :user_id AND deleted_at IS NULL");
            $result = $stmt->execute([
                ':password' => $hashedPassword,
                ':user_id' => $hrId
            ]);
        }
    } elseif ($isManager) {
        $managerId = $_SESSION['manager_id'];
        
        $stmt = $conn->prepare("SELECT m_password FROM managers WHERE id = :user_id AND deleted_at IS NULL");
        $stmt->execute([':user_id' => $managerId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $storedPassword = $user['m_password'];
            
            // Verify current password using SecureAuth (handles both hashed and plain text)
            if (!$auth->verifyPassword($currentPassword, $storedPassword)) {
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                exit;
            }
            
            // Hash new password using SecureAuth (always hash, never store plain text)
            $hashedPassword = $auth->hashPassword($newPassword);
            
            // Fallback: If SecureAuth hashPassword fails, use PHP's password_hash with bcrypt
            if (empty($hashedPassword) || (!str_starts_with($hashedPassword, '$argon2id$') && !str_starts_with($hashedPassword, '$2y$') && !str_starts_with($hashedPassword, '$2a$'))) {
                // Fallback to bcrypt if Argon2ID is not available
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            }
            
            // Final verification: Ensure password is hashed (never store plain text)
            if (empty($hashedPassword) || (!str_starts_with($hashedPassword, '$argon2id$') && !str_starts_with($hashedPassword, '$2y$') && !str_starts_with($hashedPassword, '$2a$'))) {
                echo json_encode(['success' => false, 'message' => 'Password hashing failed. Please try again.']);
                exit;
            }
            
            // Update password (always hashed)
            $stmt = $conn->prepare("UPDATE managers SET m_password = :password, m_updated_at = NOW() WHERE id = :user_id AND deleted_at IS NULL");
            $result = $stmt->execute([
                ':password' => $hashedPassword,
                ':user_id' => $managerId
            ]);
        }
    } else {
        // Employee
        $employeeNo = $_SESSION['employee_no'] ?? $_SESSION['employee_id'];
        
        $stmt = $conn->prepare("SELECT password, employee_no FROM employees WHERE employee_no = :employee_no OR id = :employee_id");
        $stmt->execute([':employee_no' => $employeeNo, ':employee_id' => $employeeNo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $storedPassword = $user['password'];
            
            // If password is NULL or empty, check if current password matches employee_no (legacy authentication)
            if (empty($storedPassword) || $storedPassword === null) {
                // Legacy: employee_no was used as password
                if ($currentPassword !== $user['employee_no']) {
                    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                    exit;
                }
            } else {
                // Verify current password using SecureAuth (handles both hashed and plain text)
                // SecureAuth::verifyPassword handles:
                // - Hashed passwords (argon2id, bcrypt)
                // - Plain text passwords (for backward compatibility)
                $isValid = $auth->verifyPassword($currentPassword, $storedPassword);
                if (!$isValid) {
                    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                    exit;
                }
            }
            
            // Hash new password using SecureAuth (always hash, never store plain text)
            $hashedPassword = $auth->hashPassword($newPassword);
            
            // Fallback: If SecureAuth hashPassword fails, use PHP's password_hash with bcrypt
            if (empty($hashedPassword) || (!str_starts_with($hashedPassword, '$argon2id$') && !str_starts_with($hashedPassword, '$2y$') && !str_starts_with($hashedPassword, '$2a$'))) {
                // Fallback to bcrypt if Argon2ID is not available
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            }
            
            // Final verification: Ensure password is hashed (never store plain text)
            if (empty($hashedPassword) || (!str_starts_with($hashedPassword, '$argon2id$') && !str_starts_with($hashedPassword, '$2y$') && !str_starts_with($hashedPassword, '$2a$'))) {
                echo json_encode(['success' => false, 'message' => 'Password hashing failed. Please try again.']);
                exit;
            }
            
            // Update password (always hashed - never store plain text)
            $stmt = $conn->prepare("UPDATE employees SET password = :password, updated_at = NOW() WHERE employee_no = :employee_no OR id = :employee_id");
            $result = $stmt->execute([
                ':password' => $hashedPassword,
                ':employee_no' => $employeeNo,
                ':employee_id' => $employeeNo
            ]);
        }
    }
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
