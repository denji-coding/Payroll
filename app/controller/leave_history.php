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

require_once '../app/core/database.php'; // Update with your DB connection file path

$db = new Database();
$pdo = $db->getConnection();

// Get leave applications with employee and manager info
$sql = "
    SELECT 
        e.photo_path,
        e.employee_no,
        CONCAT(
            UPPER(LEFT(e.first_name, 1)), LOWER(SUBSTRING(e.first_name, 2)), ' ',
            UPPER(LEFT(e.middle_name, 1)), '. ',
            UPPER(LEFT(e.last_name, 1)), LOWER(SUBSTRING(e.last_name, 2))
        ) AS employee_name,
        e.sex,
        l.id AS leave_id,
        l.leave_type,
        l.start_date,
        l.end_date,
        l.status,
        m.name AS manager_name,
        lr.reason AS rejection_reason
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    LEFT JOIN managers m ON l.manager_id = m.id
    LEFT JOIN leave_rejections lr ON l.id = lr.leave_id
    ORDER BY l.created_at DESC
";


$stmt = $pdo->prepare($sql);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);


require views_path("auth/leave_history");