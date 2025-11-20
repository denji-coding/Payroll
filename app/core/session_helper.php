<?php
/**
 * Session Helper for MVC Payroll System
 * Handles session validation for different user types
 */

// DO NOT start session here - let secure_session.php handle it
// This ensures the session name is set correctly before session_start()
// The session will be started by secure_session.php via startSecureSession()

/**
 * Check if user is logged in as admin/HR
 */
function isAdminLoggedIn() {
    $hasEmail = isset($_SESSION['SESSION_EMAIL']) && !empty($_SESSION['SESSION_EMAIL']);
    $hasUserId = isset($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['SESSION_USER_ID']);
    
    // Debug logging for troubleshooting
    if (!$hasEmail || !$hasUserId) {
        error_log("isAdminLoggedIn() check failed:");
        error_log("  SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
        error_log("  SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
        error_log("  Session ID: " . session_id());
    }
    
    return $hasEmail && $hasUserId;
}

/**
 * Get user role from database
 */
function getUserRole($userId, $userType) {
    require_once __DIR__ . '/database.php';
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $table = '';
        $idColumn = '';
        
        switch ($userType) {
            case 'employee':
                $table = 'employees';
                $idColumn = 'id';
                break;
            case 'manager':
                $table = 'managers';
                $idColumn = 'id';
                break;
            case 'admin':
                $table = 'admins';
                $idColumn = 'id';
                break;
            default:
                return null;
        }
        
        $stmt = $pdo->prepare("SELECT role_id FROM $table WHERE $idColumn = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['role_id'] ?? null;
    } catch (Exception $e) {
        error_log("Error getting user role: " . $e->getMessage());
        return null;
    }
}

/**
 * Get role permissions
 */
function getRolePermissions($roleId) {
    if (!$roleId) return null;
    
    require_once __DIR__ . '/database.php';
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ? AND is_active = 1");
        $stmt->execute([$roleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting role permissions: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if user can access employee portal
 */
function canAccessEmployeePortal($userId = null, $userType = null) {
    error_log("=== canAccessEmployeePortal() DEBUG ===");
    error_log("Parameters: userId=" . ($userId ?? 'NULL') . ", userType=" . ($userType ?? 'NULL'));
    
    // If already in session, use session data
    if (isset($_SESSION['can_access_employee_portal'])) {
        $result = $_SESSION['can_access_employee_portal'] == 1;
        error_log("Using session data: " . ($result ? 'TRUE' : 'FALSE'));
        return $result;
    }
    
    error_log("Session data not available, checking database...");
    // Otherwise check from database
    if ($userId && $userType) {
        $roleId = getUserRole($userId, $userType);
        if ($roleId) {
            $role = getRolePermissions($roleId);
            return $role && $role['can_access_employee_portal'] == 1;
        }
    }
    
    // Fallback: check current session
    $currentUserType = getCurrentUserType();
    $currentUserId = getCurrentUserId();
    
    if ($currentUserId) {
        $roleId = getUserRole($currentUserId, $currentUserType);
        if ($roleId) {
            $role = getRolePermissions($roleId);
            return $role && $role['can_access_employee_portal'] == 1;
        }
    }
    
    return false;
}

/**
 * Check if user is logged in as employee
 */
function isEmployeeLoggedIn() {
    // Ensure session is started with correct name
    if (session_status() === PHP_SESSION_NONE) {
        if (session_name() !== 'MVC_PAYROLL_SESS') {
            session_name('MVC_PAYROLL_SESS');
        }
        session_start();
    }
    
    error_log("=== isEmployeeLoggedIn() DEBUG ===");
    error_log("Session status: " . session_status());
    error_log("Session ID: " . session_id());
    
    // Regular employees
    if ((isset($_SESSION['employee_id']) && !empty($_SESSION['employee_id'])) || 
        (isset($_SESSION['employee_no']) && !empty($_SESSION['employee_no']))) {
        error_log("isEmployeeLoggedIn: TRUE (regular employee)");
        return true;
    }
    
    // Managers with employee portal access
    // SIMPLIFIED CHECK: If manager_id exists AND can_access_employee_portal is truthy, allow access
    if (isset($_SESSION['manager_id']) && !empty($_SESSION['manager_id'])) {
        error_log("Manager ID found: " . $_SESSION['manager_id']);
        
        // Simple truthy check - if can_access_employee_portal exists and is truthy (1, '1', true), allow access
        if (isset($_SESSION['can_access_employee_portal']) && $_SESSION['can_access_employee_portal']) {
            // Convert to int to ensure we're checking the numeric value
            $portalAccess = intval($_SESSION['can_access_employee_portal']);
            error_log("can_access_employee_portal value (as int): " . $portalAccess);
            
            if ($portalAccess == 1) {
                error_log("SUCCESS: Manager has employee portal access - returning TRUE");
                return true;
            } else {
                error_log("WARNING: can_access_employee_portal is not 1. Value: " . var_export($_SESSION['can_access_employee_portal'], true));
            }
        } else {
            error_log("can_access_employee_portal NOT SET or is falsy");
        }
        
        // Fallback to database check
        error_log("Falling back to database check...");
        $canAccess = canAccessEmployeePortal($_SESSION['manager_id'], 'manager');
        error_log("isEmployeeLoggedIn: Manager check using database - " . ($canAccess ? 'TRUE' : 'FALSE'));
        return $canAccess;
    }
    
    // HR/Admin with employee portal access
    // SIMPLIFIED CHECK: If SESSION_USER_ID exists AND can_access_employee_portal is truthy, allow access
    if (isset($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['SESSION_USER_ID'])) {
        error_log("HR/Admin ID found: " . $_SESSION['SESSION_USER_ID']);
        
        // Simple truthy check - if can_access_employee_portal exists and is truthy (1, '1', true), allow access
        if (isset($_SESSION['can_access_employee_portal']) && $_SESSION['can_access_employee_portal']) {
            // Convert to int to ensure we're checking the numeric value
            $portalAccess = intval($_SESSION['can_access_employee_portal']);
            error_log("can_access_employee_portal value (as int): " . $portalAccess);
            
            if ($portalAccess == 1) {
                error_log("SUCCESS: HR/Admin has employee portal access - returning TRUE");
                return true;
            } else {
                error_log("WARNING: can_access_employee_portal is not 1. Value: " . var_export($_SESSION['can_access_employee_portal'], true));
            }
        } else {
            error_log("can_access_employee_portal NOT SET or is falsy");
        }
        
        // Fallback to database check
        error_log("Falling back to database check...");
        $canAccess = canAccessEmployeePortal($_SESSION['SESSION_USER_ID'], 'admin');
        error_log("isEmployeeLoggedIn: HR/Admin check using database - " . ($canAccess ? 'TRUE' : 'FALSE'));
        return $canAccess;
    }
    
    error_log("isEmployeeLoggedIn: FALSE (no matching condition)");
    error_log("Available session keys: " . implode(', ', array_keys($_SESSION ?? [])));
    return false;
}

/**
 * Check if user is logged in as manager
 */
function isManagerLoggedIn() {
    return isset($_SESSION['manager_id']) && !empty($_SESSION['manager_id']);
}

/**
 * Check if user is logged in as owner
 */
function isOwnerLoggedIn() {
    return isset($_SESSION['owner_id']) && !empty($_SESSION['owner_id']);
}

/**
 * Get current user type
 * Returns the user type based on their role and portal access permissions
 */
function getCurrentUserType() {
    // Check for owner first (highest level)
    if (isOwnerLoggedIn()) {
        return 'owner';
    }
    
    // Check for admin (HR) BEFORE checking employee portal access
    // This ensures HR users are identified as 'admin' for admin-only pages
    if (isAdminLoggedIn()) {
        return 'admin';
    }
    
    // Check for manager BEFORE checking employee portal access
    // This ensures managers are identified as 'manager' for manager-only pages
    if (isManagerLoggedIn()) {
        return 'manager';
    }
    
    // Check if user is accessing employee portal (has employee portal access)
    // This allows managers/HR with employee portal access to access employee pages
    // But only if they haven't been identified as admin/manager above
    if (isEmployeeLoggedIn()) {
        // If user has employee portal access, return 'employee'
        // This allows managers/HR with employee portal access to access employee pages
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
    } elseif (isOwnerLoggedIn()) {
        return $_SESSION['owner_id'];
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
    } elseif (isOwnerLoggedIn()) {
        return $_SESSION['owner_name'] ?? 'Owner';
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
    // Ensure session is started with correct name
    // Use startSecureSession() to ensure proper session initialization
    if (session_status() === PHP_SESSION_NONE) {
        // Check if secure_session.php is loaded
        if (function_exists('startSecureSession')) {
            startSecureSession();
        } else {
            // Fallback to manual session start
            if (session_name() !== 'MVC_PAYROLL_SESS') {
                session_name('MVC_PAYROLL_SESS');
            }
            // Check if session cookie exists before starting
            if (isset($_COOKIE['MVC_PAYROLL_SESS'])) {
                session_id($_COOKIE['MVC_PAYROLL_SESS']);
            }
            session_start();
        }
    }
    
    error_log("=== requireEmployeeAuth() CALLED ===");
    error_log("Session ID: " . session_id());
    error_log("Session status: " . session_status());
    
    // ULTRA-SIMPLE DIRECT CHECK: Just check if the values exist and are truthy
    // Check for regular employees first
    if ((isset($_SESSION['employee_id']) && !empty($_SESSION['employee_id'])) || 
        (isset($_SESSION['employee_no']) && !empty($_SESSION['employee_no']))) {
        error_log("✅ requireEmployeeAuth: Regular employee - ACCESS GRANTED");
        return;
    }
    
    // Check for managers with employee portal access
    if (isset($_SESSION['manager_id']) && !empty($_SESSION['manager_id'])) {
        // Check can_access_employee_portal - handle both int and string values
        $portalAccess = $_SESSION['can_access_employee_portal'] ?? null;
        $portalAccessInt = isset($portalAccess) ? intval($portalAccess) : 0;
        
        if ($portalAccessInt == 1) {
            error_log("✅ requireEmployeeAuth: Manager with employee portal access - ACCESS GRANTED");
            error_log("  manager_id: " . $_SESSION['manager_id']);
            error_log("  can_access_employee_portal: " . $portalAccessInt);
            return;
        } else {
            error_log("❌ Manager Portal access check FAILED - value is: " . var_export($portalAccess, true) . " (as int: " . $portalAccessInt . ")");
        }
    }
    
    // Check for HR/Admin with employee portal access
    if (isset($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['SESSION_USER_ID'])) {
        error_log("HR/Admin ID check: PASSED - SESSION_USER_ID = " . $_SESSION['SESSION_USER_ID']);
        
        // Check can_access_employee_portal - handle both int and string values
        $portalAccess = $_SESSION['can_access_employee_portal'] ?? null;
        error_log("  can_access_employee_portal raw value: " . var_export($portalAccess, true));
        error_log("  can_access_employee_portal type: " . (isset($portalAccess) ? gettype($portalAccess) : 'NOT SET'));
        
        // Convert to int for comparison (handles '1', 1, true, etc.)
        $portalAccessInt = isset($portalAccess) ? intval($portalAccess) : 0;
        error_log("  can_access_employee_portal as int: " . $portalAccessInt);
        
        if ($portalAccessInt == 1) {
            error_log("✅ requireEmployeeAuth: HR/Admin with employee portal access - ACCESS GRANTED");
            error_log("  SESSION_USER_ID: " . $_SESSION['SESSION_USER_ID']);
            error_log("  can_access_employee_portal: " . $portalAccessInt);
            return;
        } else {
            error_log("❌ HR Portal access check FAILED - value is: " . var_export($portalAccess, true) . " (as int: " . $portalAccessInt . ")");
        }
    } else {
        error_log("❌ HR/Admin ID check FAILED - SESSION_USER_ID is: " . var_export($_SESSION['SESSION_USER_ID'] ?? 'NOT SET', true));
    }
    
    // If we get here, the direct checks failed - log why and use fallback
    error_log("❌ requireEmployeeAuth: Direct checks failed");
    error_log("Session data available:");
    error_log("  employee_id: " . ($_SESSION['employee_id'] ?? 'NOT SET'));
    error_log("  employee_no: " . ($_SESSION['employee_no'] ?? 'NOT SET'));
    error_log("  manager_id: " . ($_SESSION['manager_id'] ?? 'NOT SET'));
    error_log("  SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
    error_log("  can_access_employee_portal: " . ($_SESSION['can_access_employee_portal'] ?? 'NOT SET'));
    error_log("  can_access_employee_portal type: " . (isset($_SESSION['can_access_employee_portal']) ? gettype($_SESSION['can_access_employee_portal']) : 'NOT SET'));
    
    // Fallback: use isEmployeeLoggedIn()
    error_log("⚠️ Using isEmployeeLoggedIn() as fallback...");
    $isLoggedIn = isEmployeeLoggedIn();
    error_log("requireEmployeeAuth: isEmployeeLoggedIn() = " . ($isLoggedIn ? 'TRUE' : 'FALSE'));
    
    if (!$isLoggedIn) {
        error_log("❌ requireEmployeeAuth: All checks failed - Redirecting to unauthorized_employee.php");
        http_response_code(403);
        require_once __DIR__ . '/../Error/unauthorized_employee.php';
        exit;
    }
    error_log("✅ requireEmployeeAuth: Access granted via isEmployeeLoggedIn()");
}

/**
 * Require owner authentication
 */
function requireOwnerAuth() {
    if (!isOwnerLoggedIn()) {
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
        case 'owner':
            header('Location: index.php?payroll=owner_login');
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
    unset($_SESSION['role_id']);
    unset($_SESSION['role_name']);
    unset($_SESSION['can_access_employee_portal']);
    unset($_SESSION['can_access_manager_portal']);
    unset($_SESSION['can_access_hr_portal']);
    unset($_SESSION['can_access_owner_portal']);
    
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
