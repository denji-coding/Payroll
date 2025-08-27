<?php
require_once '../core/database.php';
require_once '../core/function.php';
require_once '../core/secure_session.php';

// Check if user is logged in as admin
if (!isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Admin access required']);
    exit;
}

// Set timezone
date_default_timezone_set('Asia/Manila');

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $pdo->exec("SET time_zone = '+08:00'");

    // Get month and year from request
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    
    // Validate month and year
    if (!is_numeric($month) || $month < 1 || $month > 12) {
        $month = date('m');
    }
    if (!is_numeric($year) || $year < 2020 || $year > 2030) {
        $year = date('Y');
    }

    // Query to get attendance data for the specified month
    $stmt = $pdo->prepare("
        SELECT 
            e.employee_no,
            CONCAT(
                e.first_name, ' ',
                IFNULL(CONCAT(UPPER(LEFT(IFNULL(e.middle_name,''), 1)), '. '), ''),
                e.last_name
            ) AS full_name,
            e.position,
            a.date,
            a.morning_in,
            a.morning_out,
            a.afternoon_in,
            a.afternoon_out,
            a.status,
            TIMESTAMPDIFF(MINUTE, a.morning_in, a.morning_out) + 
            TIMESTAMPDIFF(MINUTE, a.afternoon_in, a.afternoon_out) AS total_minutes
        FROM attendance a
        INNER JOIN employees e ON a.employee_id = e.id
        WHERE MONTH(a.date) = :month AND YEAR(a.date) = :year
        ORDER BY e.last_name, e.first_name, a.date
    ");
    
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    
    $attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for CSV download with enhanced formatting
    $filename = "attendance_report_{$year}_{$month}.csv";
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Pragma: no-cache');
    
    // Create CSV content
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add report header information
    fprintf($output, "HRM & Payroll System - Attendance Report\n");
    fprintf($output, "Period: " . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . "\n");
    fprintf($output, "Generated: " . date('F j, Y \a\t g:i A') . "\n");
    fprintf($output, "\n");
    
    // Add CSV headers
    fputcsv($output, [
        'Employee No.',
        'Employee Name',
        'Position',
        'Date',
        'Morning In',
        'Morning Out',
        'Afternoon In',
        'Afternoon Out',
        'Status',
        'Total Hours'
    ]);
    
    // Data rows with enhanced formatting
    foreach ($attendanceData as $row) {
        $morningIn = $row['morning_in'] ? date('h:i A', strtotime($row['morning_in'])) : '-';
        $morningOut = $row['morning_out'] ? date('h:i A', strtotime($row['morning_out'])) : '-';
        $afternoonIn = $row['afternoon_in'] ? date('h:i A', strtotime($row['afternoon_in'])) : '-';
        $afternoonOut = $row['afternoon_out'] ? date('h:i A', strtotime($row['afternoon_out'])) : '-';
        
        // Calculate total hours
        $totalHours = 0;
        if ($row['total_minutes'] > 0) {
            $totalHours = round($row['total_minutes'] / 60, 2);
        }
        
        // Write data row with proper formatting
        fputcsv($output, [
            $row['employee_no'],
            ucwords(strtolower($row['full_name'])),
            $row['position'],
            date('Y-m-d', strtotime($row['date'])),
            $morningIn,
            $morningOut,
            $afternoonIn,
            $afternoonOut,
            $row['status'],
            $totalHours . ' hrs'
        ]);
    }
    
    // Add footer information
    fprintf($output, "\n");
    fprintf($output, "Total Records: " . count($attendanceData) . "\n");
    fprintf($output, "Generated on: " . date('F j, Y \a\t g:i A') . "\n");
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Export failed: ' . $e->getMessage()]);
}
?>



