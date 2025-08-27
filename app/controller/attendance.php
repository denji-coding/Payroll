<?php
require_once '../app/core/session_helper.php';

// Public access: Allow viewing attendance without requiring admin/HR login
// (If you want to re-enable protection, restore: requireAdminAuth();)

// Log user activity
logUserActivity('Access attendance page');

require_once '../app/core/database.php';
date_default_timezone_set('Asia/Manila');

$db = new Database();
$pdo = $db->getConnection();
$pdo->exec("SET time_zone = '+08:00'");

$attendanceRecords = []; // ✅ Initialize to avoid undefined error
$filterDate = $_GET['date'] ?? date('Y-m-d');

try {
    $stmt = $pdo->prepare("
        SELECT 
            e.photo_path,
            e.employee_no,
            CONCAT(e.first_name, ' ', LEFT(e.middle_name, 1), '. ', e.last_name) AS full_name,
            e.position,
            a.morning_in,
            a.morning_out,
            a.afternoon_in,
            a.afternoon_out,
            a.date
        FROM attendance a
        INNER JOIN employees e ON a.employee_id = e.id
        WHERE DATE(a.date) = :filterDate
        ORDER BY 
            GREATEST(
                IFNULL(TIME_TO_SEC(a.afternoon_out), 0),
                IFNULL(TIME_TO_SEC(a.afternoon_in), 0),
                IFNULL(TIME_TO_SEC(a.morning_out), 0),
                IFNULL(TIME_TO_SEC(a.morning_in), 0)
            ) DESC
    ");
    $stmt->bindParam(':filterDate', $filterDate);
    $stmt->execute();
    $attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}
$current_time = date("h:i:s A");
$current_date = date("F j, Y");
require views_path("auth/attendance");