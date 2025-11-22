<?php
/**
 * Security Configuration
 * Centralized security settings for the application
 */

// Session Security Settings
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('SESSION_REGENERATION_INTERVAL', 300); // 5 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_DURATION', 900); // 15 minutes

// Password Security Settings
define('MIN_PASSWORD_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SPECIAL_CHARS', true);

// CSRF Protection Settings
define('CSRF_TOKEN_LENGTH', 32);
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// Rate Limiting Settings
define('API_RATE_LIMIT_MAX_REQUESTS', 100);
define('API_RATE_LIMIT_TIME_WINDOW', 3600); // 1 hour

// Database Security Settings
define('DB_PREPARED_STATEMENTS', true);
define('DB_ESCAPE_OUTPUT', true);

// File Upload Security
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
define('UPLOAD_PATH', '../uploads/');

// Security Headers
define('SECURITY_HEADERS', [
    'X-Frame-Options' => 'DENY',
    'X-Content-Type-Options' => 'nosniff',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Content-Security-Policy' => "default-src 'self'; img-src 'self' data: blob:; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com https://cdnjs.cloudflare.com; connect-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://cdn.tailwindcss.com https://cdnjs.cloudflare.com;"
]);

// Role Definitions
define('ROLES', [
    'ADMIN' => 'admin',
    'MANAGER' => 'manager',
    'EMPLOYEE' => 'employee'
]);

// Page Access Control
define('PAGE_ACCESS', [
    'admin' => [
        'dashboard1', 'employees', 'managers_account', 'attendance', 'schedules',
        'payroll', 'payslips', 'reports', 'timerecords', 'leave_history',
        'approvals_request', 'benefit_rates', 'delete_history'
    ],
    'manager' => [
        'manager_dashboard', 'branch_profile', 'branch_employeeslist',
        'branch_approvals', 'branch_leavemanagement', 'branch_payroll', 'branch_reports'
    ],
    'employee' => [
        'user_dashboard', 'user_profile', 'user_dtr', 'user_mypayslip', 'user_leave'
    ]
]);

// API Access Control
define('API_ACCESS', [
    'admin' => [
        'employees', 'managers_account', 'attendance', 'schedules', 'payroll',
        'payslips', 'reports', 'timerecords', 'leave_history', 'approvals_request',
        'benefit_rates', 'delete_history'
    ],
    'manager' => [
        'branch_approvals', 'branch_employeeslist', 'branch_leavemanagement',
        'branch_payroll', 'branch_reports'
    ],
    'employee' => [
        'user_profile', 'user_dtr', 'user_mypayslip', 'user_leave',
        'user_change_password', 'user_update_profile', 'user_get_profile'
    ]
]);

// Security Functions
class SecurityConfig {
    
    /**
     * Apply security headers
     */
    public static function applySecurityHeaders() {
        foreach (SECURITY_HEADERS as $header => $value) {
            header("$header: $value");
        }
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file) {
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['valid' => false, 'message' => 'File size exceeds limit.'];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_FILE_TYPES)) {
            return ['valid' => false, 'message' => 'File type not allowed.'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Generate secure filename
     */
    public static function generateSecureFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Sanitize output
     */
    public static function sanitizeOutput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeOutput'], $data);
        }
        
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Check if user has page access
     */
    public static function hasPageAccess($userType, $pageName) {
        return in_array($pageName, PAGE_ACCESS[$userType] ?? []);
    }
    
    /**
     * Check if user has API access
     */
    public static function hasAPIAccess($userType, $apiName) {
        return in_array($apiName, API_ACCESS[$userType] ?? []);
    }
    
    /**
     * Log security event
     */
    public static function logSecurityEvent($event, $details = '', $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = getCurrentUserId() ?? 'guest';
        $userType = getCurrentUserType() ?? 'guest';
        
        $logEntry = "[$timestamp] [$level] [$ip] [$userType:$userId] $event";
        if ($details) {
            $logEntry .= " - $details";
        }
        $logEntry .= " - User-Agent: $userAgent";
        
        error_log($logEntry);
    }
    
    /**
     * Validate session security
     */
    public static function validateSessionSecurity() {
        // Check if session is active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        // Check session timeout
        $lastActivity = $_SESSION['last_activity'] ?? 0;
        if (time() - $lastActivity > SESSION_TIMEOUT) {
            return false;
        }
        
        // Check IP address
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
            self::logSecurityEvent('SESSION_HIJACKING_ATTEMPT', 'IP address changed', 'WARNING');
            return false;
        }
        
        // Check user agent
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown')) {
            self::logSecurityEvent('SESSION_HIJACKING_ATTEMPT', 'User agent changed', 'WARNING');
            return false;
        }
        
        return true;
    }
}

// Apply security headers automatically
SecurityConfig::applySecurityHeaders();
?>
