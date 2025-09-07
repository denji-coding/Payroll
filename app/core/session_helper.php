<?php
/**
 * Session Helper for MVC Payroll System
 * Handles session validation for different user types
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in as admin/HR
 */
function isAdminLoggedIn() {
    return isset($_SESSION['SESSION_EMAIL']) && isset($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['SESSION_EMAIL']);
}

/**
 * Check if user is logged in as employee
 */
function isEmployeeLoggedIn() {
    return (isset($_SESSION['employee_id']) && !empty($_SESSION['employee_id'])) || 
           (isset($_SESSION['employee_no']) && !empty($_SESSION['employee_no']));
}

/**
 * Check if user is logged in as manager
 */
function isManagerLoggedIn() {
    return isset($_SESSION['manager_id']) && !empty($_SESSION['manager_id']);
}

/**
 * Get current user type
 */
function getCurrentUserType() {
    // Check for admin first (most restrictive)
    if (isAdminLoggedIn()) {
        return 'admin';
    }
    
    // Check for manager
    if (isManagerLoggedIn()) {
        return 'manager';
    }
    
    // Check for employee
    if (isEmployeeLoggedIn()) {
        return 'employee';
    }
    
    // If no valid session, return guest
    return 'guest';
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    if (isAdminLoggedIn()) {
        return $_SESSION['SESSION_USER_ID'];
    } elseif (isManagerLoggedIn()) {
        return $_SESSION['manager_id'];
    } elseif (isEmployeeLoggedIn()) {
        return $_SESSION['employee_id'] ?? $_SESSION['employee_no'];
    }
    return null;
}

/**
 * Get current user name
 */
function getCurrentUserName() {
    if (isAdminLoggedIn()) {
        return $_SESSION['USERNAME'] ?? 'Admin';
    } elseif (isManagerLoggedIn()) {
        return $_SESSION['manager_name'] ?? 'Manager';
    } elseif (isEmployeeLoggedIn()) {
        return $_SESSION['name'] ?? 'Employee';
    }
    return 'Guest';
}

/**
 * Require admin authentication
 */
function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        http_response_code(403);
        require_once __DIR__ . '/../Error/unauthorized.php';
        exit;
    }
}

/**
 * Require manager authentication
 */
function requireManagerAuth() {
    if (!isManagerLoggedIn()) {
        http_response_code(403);
        require_once __DIR__ . '/../Error/unauthorized.php';
        exit;
    }
}

/**
 * Require employee authentication
 */
function requireEmployeeAuth() {
    if (!isEmployeeLoggedIn()) {
        http_response_code(403);
        require_once __DIR__ . '/../Error/unauthorized.php';
        exit;
    }
}

/**
 * Require any authenticated user (admin, manager, or employee)
 */
function requireAnyAuth() {
    if (!isAdminLoggedIn() && !isManagerLoggedIn() && !isEmployeeLoggedIn()) {
        http_response_code(403);
        require_once __DIR__ . '/../Error/unauthorized.php';
        exit;
    }
}

/**
 * Redirect to appropriate login page based on user type
 */
function redirectToLogin() {
    $userType = getCurrentUserType();
    
    switch ($userType) {
        case 'admin':
            header('Location: index.php?payroll=login_admin');
            break;
        case 'manager':
            header('Location: index.php?payroll=login1&type=manager');
            break;
        case 'employee':
            header('Location: index.php?payroll=login1&type=employee');
            break;
        default:
            header('Location: index.php?payroll=login1');
            break;
    }
    exit;
}

/**
 * Check if user has access to specific page based on user type
 */
function hasPageAccess($pageName) {
    $userType = getCurrentUserType();
    
    // Admin/HR can access all pages
    if ($userType === 'admin') {
        return true;
    }
    
    // Manager access
    if ($userType === 'manager') {
        $managerPages = [
            'manager_dashboard', 'branch_profile', 'branch_employeeslist', 
            'branch_approvals', 'branch_leavemanagement', 'branch_payroll', 'branch_reports'
        ];
        return in_array($pageName, $managerPages);
    }
    
    // Employee access
    if ($userType === 'employee') {
        $employeePages = [
            'user_dashboard', 'user_profile', 'user_dtr', 'user_mypayslip', 'user_leave'
        ];
        return in_array($pageName, $employeePages);
    }
    
    return false;
}

/**
 * Clear all session data
 */
function clearAllSessions() {
    // Clear all possible session variables
    unset($_SESSION['SESSION_EMAIL']);
    unset($_SESSION['SESSION_USER_ID']);
    unset($_SESSION['USERNAME']);
    unset($_SESSION['admin']);
    unset($_SESSION['employee']);
    unset($_SESSION['employee_id']);
    unset($_SESSION['employee_no']);
    unset($_SESSION['email']);
    unset($_SESSION['name']);
    unset($_SESSION['position']);
    unset($_SESSION['photo_path']);
    unset($_SESSION['manager_id']);
    unset($_SESSION['manager_name']);
    unset($_SESSION['manager_email']);
    unset($_SESSION['manager_branch']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_type']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    unset($_SESSION['login_time']);
    unset($_SESSION['last_activity']);
    unset($_SESSION['ip_address']);
    unset($_SESSION['user_agent']);
    unset($_SESSION['login_success']);
    
    // Don't set logged_out here - it should only be set during actual logout
}

/**
 * Log user activity
 */
function logUserActivity($action, $details = '') {
    $userId = getCurrentUserId();
    $userType = getCurrentUserType();
    $userName = getCurrentUserName();
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $logEntry = "[$timestamp] User: $userName (ID: $userId, Type: $userType) - Action: $action - IP: $ip";
    if ($details) {
        $logEntry .= " - Details: $details";
    }
    
    error_log($logEntry);
}
?>
