<?php
/**
 * Authentication Middleware
 * Automatically protects controllers and ensures role-based access
 */

require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/secure_session.php';

class AuthMiddleware {
    
    /**
     * Apply authentication middleware to controller
     */
    public static function requireAuth($allowedRoles = []) {
        // Validate session
        if (!validateSession()) {
            secureLogout();
            redirectToLogin();
        }
        
        $userType = getCurrentUserType();
        
        // If specific roles are required, check access
        if (!empty($allowedRoles) && !in_array($userType, $allowedRoles)) {
            http_response_code(403);
            $unauthPage = __DIR__ . '/../Error/unauthorized.php';
            if (count($allowedRoles) === 1) {
                $requiredRole = $allowedRoles[0];
                if ($requiredRole === 'admin') {
                    $unauthPage = __DIR__ . '/../Error/unauthorized_admin.php';
                } elseif ($requiredRole === 'manager') {
                    $unauthPage = __DIR__ . '/../Error/unauthorized_manager.php';
                } elseif ($requiredRole === 'employee') {
                    $unauthPage = __DIR__ . '/../Error/unauthorized_employee.php';
                }
            }
            require_once $unauthPage;
            exit;
        }
        
        // Log access
        logUserActivity('page_access', "Accessed: " . $_SERVER['REQUEST_URI']);
    }
    
    /**
     * Require admin access
     */
    public static function requireAdmin() {
        self::requireAuth(['admin']);
    }
    
    /**
     * Require manager access
     */
    public static function requireManager() {
        self::requireAuth(['manager']);
    }
    
    /**
     * Require employee access
     */
    public static function requireEmployee() {
        self::requireAuth(['employee']);
    }
    
    /**
     * Require admin or manager access
     */
    public static function requireAdminOrManager() {
        self::requireAuth(['admin', 'manager']);
    }
    
    /**
     * Check if user can access specific resource
     */
    public static function canAccessResource($resourceType, $resourceId) {
        return canAccessResource($resourceType, $resourceId);
    }
    
    /**
     * Get current user data securely
     */
    public static function getCurrentUser() {
        return getCurrentUserData();
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRF($token) {
        return verifyCSRFToken($token);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRF() {
        return generateCSRFToken();
    }
}

/**
 * Auto-apply middleware based on controller name
 */
function applyAuthMiddleware($controllerName) {
    // Define role requirements for each controller
    $roleRequirements = [
        // Admin only controllers
        'dashboard1' => ['admin'],
        'employees' => ['admin'],
        'managers_account' => ['admin'],
        // Publicly accessible attendance page (no auth required)
        'attendance' => [],
        'schedules' => ['admin'],
        'payroll' => ['admin'],
        'payslips' => ['admin'],
        'reports' => ['admin'],
        'timerecords' => ['admin'],
        'leave_history' => ['admin'],
        'approvals_request' => ['admin'],
        'benefit_rates' => ['admin'],
        'delete_history' => ['admin'],
        
        // Manager only controllers
        'manager_dashboard' => ['manager'],
        'branch_profile' => ['manager'],
        'branch_employeeslist' => ['manager'],
        'branch_approvals' => ['manager'],
        'branch_leavemanagement' => ['manager'],
        'branch_payroll' => ['manager'],
        'branch_reports' => ['manager'],
        
        // Employee only controllers
        'user_dashboard' => ['employee'],
        'user_profile' => ['employee'],
        'user_dtr' => ['employee'],
        'user_mypayslip' => ['employee'],
        'user_leave' => ['employee'],
        
        // Public controllers (no auth required)
        'login1' => [],
        'login_admin' => [],
        'reset-password' => [],
        'forgot-password' => [],
        'register' => [],
        'register_manager' => [],
        'logout1' => [],
        'admin_logout' => [],
        'employee_logout' => [],
        'manager_logout' => [],
        'owner_login' => [],
        'owner_logout' => [],
    ];
    
    // Get required roles for this controller
    $requiredRoles = $roleRequirements[$controllerName] ?? [];
    
    // Apply middleware
    if (!empty($requiredRoles)) {
        AuthMiddleware::requireAuth($requiredRoles);
    }
}
?>
