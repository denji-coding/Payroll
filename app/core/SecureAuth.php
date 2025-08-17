<?php
/**
 * Secure Authentication Class
 * Handles secure login, password hashing, and validation
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/secure_session.php';

class SecureAuth {
    private $db;
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 900; // 15 minutes
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Hash password securely
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password needs rehashing
     */
    public function passwordNeedsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Check login attempts
     */
    private function checkLoginAttempts($email, $userType) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $attempts = $_SESSION['login_attempts'][$ip][$email] ?? 0;
        $lastAttempt = $_SESSION['login_attempts'][$ip][$email . '_time'] ?? 0;
        
        // Reset if lockout period has passed
        if (time() - $lastAttempt > $this->lockoutDuration) {
            unset($_SESSION['login_attempts'][$ip][$email]);
            unset($_SESSION['login_attempts'][$ip][$email . '_time']);
            return true;
        }
        
        return $attempts < $this->maxLoginAttempts;
    }
    
    /**
     * Record login attempt
     */
    private function recordLoginAttempt($email, $userType, $success = false) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if (!$success) {
            $_SESSION['login_attempts'][$ip][$email] = ($_SESSION['login_attempts'][$ip][$email] ?? 0) + 1;
            $_SESSION['login_attempts'][$ip][$email . '_time'] = time();
        } else {
            // Clear attempts on successful login
            unset($_SESSION['login_attempts'][$ip][$email]);
            unset($_SESSION['login_attempts'][$ip][$email . '_time']);
        }
    }
    
    /**
     * Authenticate admin
     */
    public function authenticateAdmin($email, $password) {
        // Check login attempts
        if (!$this->checkLoginAttempts($email, 'admin')) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }
        
        try {
            $stmt = $this->db->query("SELECT * FROM admins WHERE email = ?", [$email]);
            $admin = $stmt ? $stmt[0] : null;
            
            if (!$admin) {
                $this->recordLoginAttempt($email, 'admin', false);
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }
            
            if (!$this->verifyPassword($password, $admin['password'])) {
                $this->recordLoginAttempt($email, 'admin', false);
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }
            
            // Check if password needs rehashing
            if ($this->passwordNeedsRehash($admin['password'])) {
                $newHash = $this->hashPassword($password);
                $this->db->query("UPDATE admins SET password = ? WHERE id = ?", [$newHash, $admin['id']]);
            }
            
            $this->recordLoginAttempt($email, 'admin', true);
            secureLogin($admin, 'admin');
            
            return ['success' => true, 'user' => $admin, 'redirect' => 'index.php?payroll=dashboard1'];
            
        } catch (Exception $e) {
            error_log("Admin authentication error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Authentication error. Please try again.'];
        }
    }
    
    /**
     * Authenticate manager
     */
    public function authenticateManager($email, $password) {
        // Check login attempts
        if (!$this->checkLoginAttempts($email, 'manager')) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }
        
        try {
            $stmt = $this->db->query("SELECT * FROM managers WHERE m_email = ? AND deleted_at IS NULL", [$email]);
            $manager = $stmt ? $stmt[0] : null;
            
            if (!$manager) {
                $this->recordLoginAttempt($email, 'manager', false);
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }
            
            if (!$this->verifyPassword($password, $manager['m_password'])) {
                $this->recordLoginAttempt($email, 'manager', false);
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }
            
            // Check if password needs rehashing
            if ($this->passwordNeedsRehash($manager['m_password'])) {
                $newHash = $this->hashPassword($password);
                $this->db->query("UPDATE managers SET m_password = ? WHERE id = ?", [$newHash, $manager['id']]);
            }
            
            $this->recordLoginAttempt($email, 'manager', true);
            secureLogin($manager, 'manager');
            
            return ['success' => true, 'user' => $manager, 'redirect' => 'index.php?payroll=manager_dashboard'];
            
        } catch (Exception $e) {
            error_log("Manager authentication error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Authentication error. Please try again.'];
        }
    }
    
    /**
     * Authenticate employee
     */
    public function authenticateEmployee($email, $employeeNo) {
        // Check login attempts
        if (!$this->checkLoginAttempts($email, 'employee')) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }
        
        try {
            $stmt = $this->db->query("SELECT * FROM employees WHERE email = ? AND employee_no = ?", [$email, $employeeNo]);
            $employee = $stmt ? $stmt[0] : null;
            
            if (!$employee) {
                $this->recordLoginAttempt($email, 'employee', false);
                return ['success' => false, 'message' => 'Invalid email or employee number.'];
            }
            
            $this->recordLoginAttempt($email, 'employee', true);
            secureLogin($employee, 'employee');
            
            return ['success' => true, 'user' => $employee, 'redirect' => 'index.php?payroll=user_dashboard'];
            
        } catch (Exception $e) {
            error_log("Employee authentication error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Authentication error. Please try again.'];
        }
    }
    
    /**
     * Change password securely
     */
    public function changePassword($userId, $userType, $currentPassword, $newPassword) {
        try {
            // Get user data
            $userData = $this->getUserData($userId, $userType);
            if (!$userData) {
                return ['success' => false, 'message' => 'User not found.'];
            }
            
            // Verify current password
            $currentHash = $userData['password'] ?? $userData['m_password'] ?? '';
            if (!$this->verifyPassword($currentPassword, $currentHash)) {
                return ['success' => false, 'message' => 'Current password is incorrect.'];
            }
            
            // Hash new password
            $newHash = $this->hashPassword($newPassword);
            
            // Update password in database
            switch ($userType) {
                case 'admin':
                    $this->db->query("UPDATE admins SET password = ? WHERE id = ?", [$newHash, $userId]);
                    break;
                case 'manager':
                    $this->db->query("UPDATE managers SET m_password = ? WHERE id = ?", [$newHash, $userId]);
                    break;
                case 'employee':
                    // For employees, we might need to implement a different approach
                    // since they currently use employee_no as password
                    return ['success' => false, 'message' => 'Password change not implemented for employees.'];
            }
            
            return ['success' => true, 'message' => 'Password changed successfully.'];
            
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error changing password. Please try again.'];
        }
    }
    
    /**
     * Get user data
     */
    private function getUserData($userId, $userType) {
        try {
            switch ($userType) {
                case 'admin':
                    $stmt = $this->db->query("SELECT * FROM admins WHERE id = ?", [$userId]);
                    break;
                case 'manager':
                    $stmt = $this->db->query("SELECT * FROM managers WHERE id = ? AND deleted_at IS NULL", [$userId]);
                    break;
                case 'employee':
                    $stmt = $this->db->query("SELECT * FROM employees WHERE id = ?", [$userId]);
                    break;
                default:
                    return null;
            }
            
            return $stmt ? $stmt[0] : null;
        } catch (Exception $e) {
            error_log("Error fetching user data: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate password strength
     */
    public function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }
        
        return $errors;
    }
    
    /**
     * Generate secure random password
     */
    public function generateSecurePassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $password = '';
        
        // Ensure at least one of each required character type
        $password .= chr(rand(65, 90)); // Uppercase
        $password .= chr(rand(97, 122)); // Lowercase
        $password .= chr(rand(48, 57)); // Number
        $password .= '!@#$%^&*'[rand(0, 7)]; // Special character
        
        // Fill the rest randomly
        for ($i = 4; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        // Shuffle the password
        return str_shuffle($password);
    }
}
?>
