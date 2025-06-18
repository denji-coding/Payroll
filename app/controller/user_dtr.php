<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require views_path("user/user_dtr");

// $employee_id = $_SESSION['employee_id'] ?? null;

// if (!$employee_id) {
//     // If API request, return JSON error, else show message and stop
//     if (isset($_GET['id'])) {
//         header('Content-Type: application/json');
//         echo json_encode(['error' => 'Unauthorized']);
//         exit;
//     } else {
//         die("Unauthorized access.");
//     }
// }

