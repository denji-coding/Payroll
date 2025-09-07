<?php
// Include security configuration first
require_once "../app/core/SecurityConfig.php";

// Session configuration is handled by secure_session.php

// Start secure session
require_once "../app/core/secure_session.php";

// Define the root path constant for the app
define("ABSPATH", true);

// Include core initialization file
require "../app/core/init.php";

// Get the 'payroll' URL parameter (used for routing)
$url = $_GET['payroll'] ?? "home";  // Default to 'home' if not provided

// ============================
// DIRECT DOWNLOAD ROUTING (bypasses API routing for better session handling)
// ============================
if ($url === 'download_payslip') {
    // Direct download route for better session handling
    require "../app/api/download_payslip.php";
    exit;
}

if ($url === 'download_payslip_admin') {
    // Direct admin download route for better session handling
    require "../app/api/download_payslip_admin.php";
    exit;
}

if ($url === 'download_payslip_user') {
    // Direct user download route for better session handling
    require "../app/api/download_payslip_user.php";
    exit;
}

// ============================
// DIRECT PRINT ROUTING (for printing payslips)
// ============================
if ($url === 'print_payslip') {
    // Direct print route for better session handling
    require "../app/api/print_payslip.php";
    exit;
}

// ============================
// SESSION TEST ENDPOINT (for debugging)
// ============================
if ($url === 'test_session') {
    // Simple session test endpoint
    header('Content-Type: application/json');
    echo json_encode([
        'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive',
        'session_id' => session_id() ?: 'none',
        'session_data' => $_SESSION,
        'cookies' => $_COOKIE,
        'request_uri' => $_SERVER['REQUEST_URI']
    ]);
    exit;
}

// ============================
// API ROUTING (e.g. ?payroll=api/employees)
// ============================
if (strpos($url, 'api/') === 0) {
    // Only clean output buffer if it exists and we're not at root level
    if (ob_get_level() > 1) {
        ob_clean(); // 👈 prevent layout or whitespace
    }

    $apiFile = substr($url, 4);
    $apiPath = "../app/api/" . $apiFile . "-api.php";

    // Special handling for download endpoints that output files directly
    if (strpos($apiFile, 'download_') === 0) {
        // For download endpoints, don't set JSON content type
        // Let the download script handle its own headers
        if (file_exists($apiPath)) {
            require $apiPath;
            exit;
        } else {
            http_response_code(404);
            echo "Download API Not Found";
            exit;
        }
    }

    // Apply API middleware for regular endpoints
    require_once "../app/core/SecureAPIMiddleware.php";
    applyAPIMiddleware($apiFile);

    if (file_exists($apiPath)) {
        require $apiPath;
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'API Not Found']);
        exit;
    }
}

// ============================
// HANDLE LOGOUT (if exists)
// ============================
if ($url === 'logout') {
    require "../app/controller/logout.php";
    exit;
}

// ============================
// WEB CONTROLLER ROUTING
// ============================
$controller = strtolower($url); // Ensure lowercase

$controllerPath = "../app/controller/" . $controller . ".php";

if (file_exists($controllerPath)) {
    // Apply authentication middleware
    require_once "../app/core/AuthMiddleware.php";
    applyAuthMiddleware($controller);
    
    require $controllerPath;
} else {
    http_response_code(404);
    echo "Controller Not Found!";
}
