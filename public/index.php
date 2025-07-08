<?php

session_start();

// Define the root path constant for the app
define("ABSPATH", true);

// Include core initialization file
require "../app/core/init.php";

// Get the 'payroll' URL parameter (used for routing)
$url = $_GET['payroll'] ?? "home";  // Default to 'home' if not provided

// ============================
// API ROUTING (e.g. ?payroll=api/employees)
// ============================
if (strpos($url, 'api/') === 0) {
    ob_clean(); // 👈 prevent layout or whitespace

    header('Content-Type: application/json');

    $apiFile = substr($url, 4);
    $apiPath = "../app/api/" . $apiFile . "-api.php";

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
    require $controllerPath;
} else {
    http_response_code(404);
    echo "Controller Not Found!";
}
