<?php
/**
 * Secure API Middleware
 * Protects API endpoints with authentication and CSRF protection
 */

require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/secure_session.php';

class SecureAPIMiddleware {
    
    /**
     * Apply API authentication middleware
     */
    public static function requireAuth($allowedRoles = []) {
        // Set JSON response headers
        header('Content-Type: application/json');
        
        // Validate session
        if (!validateSession()) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Session expired. Please log in again.',
                'code' => 'SESSION_EXPIRED'
            ]);
            exit;
        }
        
        $userType = getCurrentUserType();
        
        // If specific roles are required, check access
        if (!empty($allowedRoles) && !in_array($userType, $allowedRoles)) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Access denied. Insufficient permissions.',
                'code' => 'INSUFFICIENT_PERMISSIONS'
            ]);
            exit;
        }
        
        // Log API access
        logUserActivity('api_access', "API: " . $_SERVER['REQUEST_URI']);
    }
    
    /**
     * Validate CSRF token for POST/PUT/DELETE requests
     */
    public static function validateCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return true; // GET requests don't need CSRF validation
        }
        
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !verifyCSRFToken($token)) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid CSRF token.',
                'code' => 'INVALID_CSRF'
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Validate request method
     */
    public static function validateMethod($allowedMethods = ['GET', 'POST']) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (!in_array($method, $allowedMethods)) {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Method not allowed.',
                'code' => 'METHOD_NOT_ALLOWED'
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Validate required parameters
     */
    public static function validateParams($requiredParams, $source = 'POST') {
        $data = $source === 'POST' ? $_POST : $_GET;
        $missing = [];
        
        foreach ($requiredParams as $param) {
            if (!isset($data[$param]) || empty($data[$param])) {
                $missing[] = $param;
            }
        }
        
        if (!empty($missing)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing required parameters: ' . implode(', ', $missing),
                'code' => 'MISSING_PARAMETERS'
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate user can access specific resource
     */
    public static function validateResourceAccess($resourceType, $resourceId) {
        if (!canAccessResource($resourceType, $resourceId)) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Access denied to this resource.',
                'code' => 'RESOURCE_ACCESS_DENIED'
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Rate limiting check
     */
    public static function checkRateLimit($maxRequests = 100, $timeWindow = 3600) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$ip}";
        
        $requests = $_SESSION[$key] ?? 0;
        $lastReset = $_SESSION[$key . '_reset'] ?? 0;
        
        // Reset counter if time window has passed
        if (time() - $lastReset > $timeWindow) {
            $requests = 0;
            $_SESSION[$key . '_reset'] = time();
        }
        
        if ($requests >= $maxRequests) {
            http_response_code(429);
            echo json_encode([
                'status' => 'error',
                'message' => 'Rate limit exceeded. Please try again later.',
                'code' => 'RATE_LIMIT_EXCEEDED'
            ]);
            exit;
        }
        
        // Increment request counter
        $_SESSION[$key] = $requests + 1;
        
        return true;
    }
    
    /**
     * Complete API middleware with all protections
     */
    public static function protectAPI($allowedRoles = [], $allowedMethods = ['GET', 'POST'], $requiredParams = []) {
        // Check rate limiting
        self::checkRateLimit();
        
        // Validate request method
        self::validateMethod($allowedMethods);
        
        // Validate authentication
        self::requireAuth($allowedRoles);
        
        // Validate CSRF for non-GET requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            self::validateCSRF();
        }
        
        // Validate required parameters
        if (!empty($requiredParams)) {
            $source = $_SERVER['REQUEST_METHOD'] === 'GET' ? 'GET' : 'POST';
            self::validateParams($requiredParams, $source);
        }
        
        return true;
    }
}

/**
 * Auto-apply API middleware based on endpoint
 */
function applyAPIMiddleware($endpoint) {
    // Define protection requirements for each API endpoint
    $apiProtection = [
        // Admin and Manager APIs
        'employees' => ['roles' => ['admin', 'manager'], 'methods' => ['GET', 'POST', 'PUT', 'DELETE']],
        'managers_account' => ['roles' => ['admin'], 'methods' => ['GET', 'POST', 'PUT', 'DELETE']],
        'attendance' => ['roles' => ['admin'], 'methods' => ['GET', 'POST', 'PUT', 'DELETE']],
        'schedules' => ['roles' => ['admin'], 'methods' => ['GET', 'POST', 'PUT', 'DELETE']],
        'payroll' => ['roles' => ['admin'], 'methods' => ['GET', 'POST', 'PUT', 'DELETE']],
        'payslips' => ['roles' => ['admin'], 'methods' => ['GET', 'POST', 'PUT', 'DELETE']],
        'reports' => ['roles' => ['admin'], 'methods' => ['GET', 'POST']],
        'timerecords' => ['roles' => ['admin'], 'methods' => ['GET', 'POST', 'PUT', 'DELETE']],
        'leave_history' => ['roles' => ['admin'], 'methods' => ['GET', 'POST', 'PUT', 'DELETE']],
        'approvals_request' => ['roles' => ['admin'], 'methods' => ['GET', 'POST', 'PUT', 'DELETE']],
        'benefit_rates' => ['roles' => ['admin'], 'methods' => ['GET', 'POST', 'PUT', 'DELETE']],
        'delete_history' => ['roles' => ['admin'], 'methods' => ['GET', 'POST', 'DELETE']],
        
        // Manager APIs
        'branch_approvals' => ['roles' => ['manager'], 'methods' => ['GET', 'POST', 'PUT']],
        'branch_employeeslist' => ['roles' => ['manager'], 'methods' => ['GET']],
        'branch_leavemanagement' => ['roles' => ['manager'], 'methods' => ['GET', 'POST', 'PUT']],
        'branch_payroll' => ['roles' => ['manager'], 'methods' => ['GET', 'POST']],
        'branch_reports' => ['roles' => ['manager'], 'methods' => ['GET']],
        
        // Employee APIs
        'user_profile' => ['roles' => ['employee'], 'methods' => ['GET', 'POST', 'PUT']],
        'user_dtr' => ['roles' => ['employee'], 'methods' => ['GET', 'POST']],
        'user_mypayslip' => ['roles' => ['employee'], 'methods' => ['GET']],
        'user_leave' => ['roles' => ['employee'], 'methods' => ['GET', 'POST']],
        'user_change_password' => ['roles' => ['employee'], 'methods' => ['POST']],
        'user_update_profile' => ['roles' => ['employee'], 'methods' => ['POST']],
        'user_get_profile' => ['roles' => ['employee'], 'methods' => ['GET']],
        
        // Public APIs (no auth required)
        'test_session' => ['roles' => [], 'methods' => ['GET']],
    ];
    
    // Get protection requirements for this endpoint
    $protection = $apiProtection[$endpoint] ?? ['roles' => [], 'methods' => ['GET', 'POST']];
    
    // Apply middleware
    SecureAPIMiddleware::protectAPI(
        $protection['roles'],
        $protection['methods']
    );
}
?>
