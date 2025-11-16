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

// Ensure attendance table supports HR logs
try {
    $res = $pdo->query("SHOW COLUMNS FROM attendance LIKE 'hr_id'");
    if ($res && $res->rowCount() === 0) {
        $pdo->exec("ALTER TABLE attendance ADD COLUMN hr_id INT(11) NULL AFTER manager_id");
        try { $pdo->exec("CREATE INDEX idx_attendance_hr_id ON attendance(hr_id)"); } catch (Throwable $e) {}
    }
} catch (Throwable $e) {
    // ignore if cannot alter
}

$attendanceRecords = []; // ✅ Initialize to avoid undefined error
$filterDate = $_GET['date'] ?? date('Y-m-d');

try {
    $stmt = $pdo->prepare("
        SELECT 
            e.photo_path,
            e.employee_no,
            CONCAT(
                e.first_name, 
                ' ', 
                IF(e.middle_name IS NOT NULL AND e.middle_name != '', 
                   CONCAT(LEFT(e.middle_name, 1), '. '), 
                   ''),
                e.last_name
            ) AS full_name,
            e.position,
            e.sex as gender,
            a.morning_in,
            a.morning_out,
            a.afternoon_in,
            a.afternoon_out,
            a.date,
            'employee' as user_type
        FROM attendance a
        INNER JOIN employees e ON a.employee_id = e.id
            WHERE DATE(a.date) = ? AND a.employee_id IS NOT NULL
            
            UNION ALL
            
            SELECT 
            m.m_photo_path as photo_path,
            m.m_employee_id as employee_no,
            CONCAT(
                m.m_first_name, 
                ' ', 
                IF(m.m_middle_name IS NOT NULL AND m.m_middle_name != '', 
                   CONCAT(LEFT(m.m_middle_name, 1), '. '), 
                   ''),
                m.m_last_name
            ) AS full_name,
            'Manager' as position,
            m.m_sex as gender,
            a2.morning_in,
            a2.morning_out,
            a2.afternoon_in,
            a2.afternoon_out,
            a2.date,
            'manager' as user_type
        FROM attendance a2
        INNER JOIN managers m ON a2.manager_id = m.id
        WHERE DATE(a2.date) = ? AND a2.manager_id IS NOT NULL
        
        UNION ALL
        
        SELECT 
            admin.hr_photo_path as photo_path,
            COALESCE(admin.hr_employee_id, '') as employee_no,
            CONCAT(
                admin.hr_first_name, 
                ' ', 
                IF(admin.hr_middle_name IS NOT NULL AND admin.hr_middle_name != '', 
                   CONCAT(LEFT(admin.hr_middle_name, 1), '. '), 
                   ''),
                admin.hr_last_name
            ) AS full_name,
            COALESCE(admin.hr_position, 'Human Resources') as position,
            admin.hr_sex as gender,
            att.morning_in,
            att.morning_out,
            att.afternoon_in,
            att.afternoon_out,
            att.date,
            'hr' as user_type
        FROM attendance att
        INNER JOIN admins admin ON att.hr_id = admin.id
        WHERE DATE(att.date) = ? AND att.hr_id IS NOT NULL AND admin.deleted_at IS NULL
        
        ORDER BY 
            GREATEST(
                IFNULL(TIME_TO_SEC(afternoon_out), 0),
                IFNULL(TIME_TO_SEC(afternoon_in), 0),
                IFNULL(TIME_TO_SEC(morning_out), 0),
                IFNULL(TIME_TO_SEC(morning_in), 0)
            ) DESC
    ");
    // Bind the date parameter three times (once for each UNION part)
    $stmt->bindValue(1, $filterDate, PDO::PARAM_STR);
    $stmt->bindValue(2, $filterDate, PDO::PARAM_STR);
    $stmt->bindValue(3, $filterDate, PDO::PARAM_STR);
    $stmt->execute();
    $attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process photo paths - add ../public/ prefix for HR and manager photos if needed
    foreach ($attendanceRecords as &$record) {
        if (!empty($record['photo_path'])) {
            $userType = $record['user_type'] ?? '';
            // Only add prefix for HR and manager photos (employee photos might already have correct path)
            if (($userType === 'hr' || $userType === 'manager') && 
                strpos($record['photo_path'], '../public/') === false && 
                strpos($record['photo_path'], 'http') === false) {
                // Add prefix for relative paths (HR and manager photos are stored as relative paths)
                $record['photo_path'] = '../public/' . ltrim($record['photo_path'], '/');
            }
        }
    }
    unset($record); // Unset reference
} catch (PDOException $e) {
    error_log("Database error in attendance controller: " . $e->getMessage());
    $attendanceRecords = [];
}
$current_time = date("h:i:s A");
$current_date = date("F j, Y");
require views_path("auth/attendance");