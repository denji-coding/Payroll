<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// $admin_id = $_SESSION['admin_id'] ?? null;

// if (!$admin_id) {
//     // If it's an API request, return JSON
//     if (isset($_GET['id'])) {
//         header('Content-Type: application/json');
//         echo json_encode(['error' => 'Unauthorized admin access']);
//         exit;
//     } else {
//         // Show custom 403 page for browser access
//         http_response_code(403);
//          require_once '../app/Error/unauthorized.php'; // Adjust the path
//         exit;
//     }
// }

require views_path("auth/reports");