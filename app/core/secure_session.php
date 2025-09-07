<?php
/**
 * Secure Session Management System
 * Enhanced security features for multi-user authentication
 */

// Include session helper for clearAllSessions function
require_once __DIR__ . '/session_helper.php';

// Start session with secure settings
function startSecureSession() {
    // Only configure session if it hasn't started yet
    if (session_status() === PHP_SESSION_NONE) {
        // Basic session settings for shared hosting compatibility
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 0); // Disable strict mode for shared hosting
        
        // Detect HTTPS more reliably for shared hosting
        $isHttps = false;
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $isHttps = true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $isHttps = true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
            $isHttps = true;
        } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
            $isHttps = true;
        }
        
        // Set secure cookie only if HTTPS is confirmed
        ini_set('session.cookie_secure', $isHttps ? 1 : 0);
        
        // Use Lax for SameSite for better compatibility
        ini_set('session.cookie_samesite', 'Lax');
        
        // Set session timeout (1 day)
        ini_set('session.gc_maxlifetime', 86400);
        
        // Use a custom session name to avoid old cookie conflicts
        session_name('MVC_PAYROLL_SESS');
        
        // Set cookie parameters with fallback for older PHP versions
        // Use root path to ensure session works across all pages
        $cookiePath = '/'; // Use root path for maximum compatibility
        $cookieDomain = ''; // Don't set domain to avoid subdomain issues
        
        // For InfinityFree, try to set domain to the actual host
        if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'free.nf') !== false) {
            $cookieDomain = '.' . $_SERVER['HTTP_HOST'];
        }
        
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 86400,
                'path' => $cookiePath,
                'domain' => $cookieDomain,
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            session_set_cookie_params(86400, $cookiePath, $cookieDomain);
        }
        
        session_start();
        
        // Always regenerate session ID to prevent session conflicts
        // This ensures each login gets a fresh session
        session_regenerate_id(true);
        
        // Clear any existing session data to start fresh
        $_SESSION = [];
    }
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Initialize secure session
startSecureSession();

/**
 * Session timeout check
 */
function checkSessionTimeout() {
    $timeout = 86400; // 1 day
    $lastActivity = $_SESSION['last_activity'] ?? 0;
    
    // If last_activity is not set, this is a new session, so allow it
    if ($lastActivity === 0) {
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    if (time() - $lastActivity > $timeout) {
        // Session expired
        clearAllSessions();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Force session reset for new login
 */
function resetSessionForLogin() {
    // Destroy current session completely
    session_destroy();
    
    // Start fresh session
    session_start();
    
    // Regenerate session ID
    session_regenerate_id(true);
    
    // Clear all session data
    $_SESSION = [];
    
    // Set initial session data
    $_SESSION['last_regeneration'] = time();
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Secure login function
 */
function secureLogin($userData, $userType) {
    // Clear any existing sessions
    clearAllSessions();
    
    // Set user-specific session data
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['user_type'] = $userType;
    $_SESSION['user_email'] = $userData['email'] ?? $userData['m_email'] ?? '';
    $_SESSION['user_name'] = $userData['name'] ?? $userData['m_full_name'] ?? 
                            ($userData['first_name'] . ' ' . $userData['last_name']);
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Set role-specific session variables
    switch ($userType) {
        case 'admin':
            $_SESSION['SESSION_EMAIL'] = $userData['email'];
            $_SESSION['SESSION_USER_ID'] = $userData['id'];
            $_SESSION['USERNAME'] = $userData['name'];
            break;
            
        case 'manager':
            $_SESSION['manager_id'] = $userData['id'];
            $_SESSION['manager_name'] = $userData['m_full_name'] ?? 
                trim($userData['m_first_name'] . ' ' . $userData['m_middle_name'] . ' ' . $userData['m_last_name']);
            $_SESSION['manager_email'] = $userData['m_email'];
            $_SESSION['manager_branch'] = $userData['m_branch'] ?? '';
            break;
            
        case 'employee':
            $_SESSION['employee_id'] = $userData['id'];
            $_SESSION['employee_no'] = $userData['employee_no'];
            $_SESSION['email'] = $userData['email'];
            $_SESSION['name'] = $userData['first_name'] . ' ' . $userData['last_name'];
            $_SESSION['position'] = $userData['position'];
            $_SESSION['photo_path'] = $userData['photo_path'] ?? '';
            break;
    }
    
    // Set login success flag for toast notification
    $_SESSION['login_success'] = true;
    
    // Log successful login
    logUserActivity('login', "User logged in successfully");
}

/**
 * Secure logout function
 */
function secureLogout() {
    $userType = getCurrentUserType();
    $userName = getCurrentUserName();
    
    // Log logout activity
    logUserActivity('logout', "User logged out");
    
    // Clear all sessions
    clearAllSessions();
    
    // Destroy session completely
    session_destroy();
    
    // Start new session for logout message
    session_start();
    $_SESSION['logged_out'] = true;
    $_SESSION['logout_message'] = "You have been successfully logged out.";
}

/**
 * Check if user is authenticated and session is valid
 */
function isAuthenticated() {
    // Check if session timeout
    if (!checkSessionTimeout()) {
        error_log("Authentication failed: Session timeout");
        return false;
    }
    
    // Check if user is logged in
    $isLoggedIn = isAdminLoggedIn() || isManagerLoggedIn() || isEmployeeLoggedIn();
    if (!$isLoggedIn) {
        error_log("Authentication failed: No valid login session found");
    }
    
    return $isLoggedIn;
}

/**
 * Require authentication with role check
 */
function requireAuth($allowedRoles = []) {
    if (!isAuthenticated()) {
        secureLogout();
        redirectToLogin();
    }
    
    $userType = getCurrentUserType();
    
    // If specific roles are required, check if user has access
    if (!empty($allowedRoles) && !in_array($userType, $allowedRoles)) {
        http_response_code(403);
        require_once __DIR__ . '/../Error/unauthorized.php';
        exit;
    }
}

/**
 * Get user data securely
 */
function getCurrentUserData() {
    $userType = getCurrentUserType();
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return null;
    }
    
    try {
        $db = new Database();
        
        switch ($userType) {
            case 'admin':
                $stmt = $db->query("SELECT * FROM admins WHERE id = ?", [$userId]);
                break;
            case 'manager':
                $stmt = $db->query("SELECT * FROM managers WHERE id = ?", [$userId]);
                break;
            case 'employee':
                $stmt = $db->query("SELECT * FROM employees WHERE id = ?", [$userId]);
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
 * Check if user can access specific resource
 */
function canAccessResource($resourceType, $resourceId) {
    $userType = getCurrentUserType();
    $userId = getCurrentUserId();
    
    // Admin can access everything
    if ($userType === 'admin') {
        return true;
    }
    
    // Users can only access their own resources
    if ($userType === 'employee' && $resourceType === 'employee') {
        return $userId == $resourceId;
    }
    
    if ($userType === 'manager' && $resourceType === 'manager') {
        return $userId == $resourceId;
    }
    
    return false;
}

/**
 * Validate session integrity
 */
function validateSessionIntegrity() {
    // Check if IP address changed (potential session hijacking)
    // Only check if we have a stored IP address
    if (isset($_SESSION['ip_address']) && !empty($_SESSION['ip_address'])) {
        $currentIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if ($_SESSION['ip_address'] !== $currentIP) {
            error_log("Session integrity check failed: IP mismatch. Stored: " . $_SESSION['ip_address'] . ", Current: " . $currentIP);
            secureLogout();
            return false;
        }
    }
    
    // Check if user agent changed
    // Only check if we have a stored user agent
    if (isset($_SESSION['user_agent']) && !empty($_SESSION['user_agent'])) {
        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        if ($_SESSION['user_agent'] !== $currentUserAgent) {
            error_log("Session integrity check failed: User agent mismatch. Stored: " . $_SESSION['user_agent'] . ", Current: " . $currentUserAgent);
            secureLogout();
            return false;
        }
    }
    
    return true;
}

/**
 * Enhanced session validation
 */
function validateSession() {
    if (!isAuthenticated()) {
        error_log("Session validation failed: User not authenticated");
        return false;
    }
    
    // Temporarily disable session integrity check to debug login issue
    // if (!validateSessionIntegrity()) {
    //     error_log("Session validation failed: Session integrity check failed");
    //     return false;
    // }
    
    return true;
}
?>
