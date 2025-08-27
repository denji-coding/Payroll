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

    // Set headers for HTML download
    $filename = "attendance_report_{$year}_{$month}.html";
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Pragma: no-cache');
    
    // Create HTML content with Excel-compatible styling
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attendance Report - ' . $year . '/' . $month . '</title>
         <style>
         body { font-family: Arial, sans-serif; margin: 20px; }
         table { border-collapse: collapse; width: 100%; margin-top: 20px; table-layout: auto; }
         th { 
             background-color: #16A249; 
             color: white; 
             font-weight: bold; 
             text-align: center; 
             padding: 12px 8px; 
             border: 1px solid #16A249;
             font-size: 12px;
             white-space: nowrap;
         }
         td { 
             padding: 8px; 
             border: 1px solid #ddd; 
             text-align: left;
             vertical-align: middle;
             word-wrap: break-word;
             max-width: 200px;
         }
         tr:nth-child(even) { background-color: #f8fbf8; }
         tr:hover { background-color: #f2f8f2; }
         .header { 
             text-align: center; 
             margin-bottom: 20px; 
             color: #16A249; 
             font-size: 18px; 
             font-weight: bold; 
         }
         .subheader { 
             text-align: center; 
             margin-bottom: 20px; 
             color: #666; 
             font-size: 14px; 
         }
         .employee-name { 
             font-weight: 500; 
             color: #333; 
         }
         .employee-id { 
             font-size: 11px; 
             color: #666; 
             margin-top: 2px; 
         }
         .status-present { color: #16A249; font-weight: 500; }
         .status-late { color: #f59e0b; font-weight: 500; }
         .status-absent { color: #ef4444; font-weight: 500; }
         .status-halfday { color: #3b82f6; font-weight: 500; }
         @media print {
             body { margin: 10px; }
             table { font-size: 10px; }
             th, td { padding: 6px 4px; }
         }
     </style>
</head>
<body>
    <div class="header">HRM & Payroll System</div>
    <div class="subheader">Attendance Report - ' . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . '</div>
    <table>
        <thead>
            <tr>
                <th>Employee No.</th>
                <th>Employee Name</th>
                <th>Position</th>
                <th>Date</th>
                <th>Morning In</th>
                <th>Morning Out</th>
                <th>Afternoon In</th>
                <th>Afternoon Out</th>
                <th>Status</th>
                <th>Total Hours</th>
            </tr>
        </thead>
        <tbody>';
    
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
        
                 // Determine status class for styling
         $statusClass = 'status-present';
         switch(strtolower($row['status'])) {
             case 'late': $statusClass = 'status-late'; break;
             case 'absent': $statusClass = 'status-absent'; break;
             case 'half day': $statusClass = 'status-halfday'; break;
             default: $statusClass = 'status-present';
         }
         
         echo '<tr>
                 <td><strong>' . htmlspecialchars($row['employee_no']) . '</strong></td>
                 <td>
                     <div class="employee-name">' . htmlspecialchars(ucwords(strtolower($row['full_name']))) . '</div>
                 </td>
                 <td>' . htmlspecialchars($row['position']) . '</td>
                 <td>' . htmlspecialchars(date('M d, Y', strtotime($row['date']))) . '</td>
                 <td>' . htmlspecialchars($morningIn) . '</td>
                 <td>' . htmlspecialchars($morningOut) . '</td>
                 <td>' . htmlspecialchars($afternoonIn) . '</td>
                 <td>' . htmlspecialchars($afternoonOut) . '</td>
                 <td class="' . $statusClass . '">' . htmlspecialchars($row['status']) . '</td>
                 <td><strong>' . htmlspecialchars($totalHours . ' hrs') . '</strong></td>
               </tr>';
    }
    
    echo '</tbody>
    </table>
    <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
        Generated on ' . date('F j, Y \a\t g:i A') . '
    </div>
</body>
</html>';
    
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Export failed: ' . $e->getMessage()]);
}
?>

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

    // Set headers for HTML download
    $filename = "attendance_report_{$year}_{$month}.html";
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Pragma: no-cache');
    
    // Create HTML content with Excel-compatible styling
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attendance Report - ' . $year . '/' . $month . '</title>
         <style>
         body { font-family: Arial, sans-serif; margin: 20px; }
         table { border-collapse: collapse; width: 100%; margin-top: 20px; table-layout: auto; }
         th { 
             background-color: #16A249; 
             color: white; 
             font-weight: bold; 
             text-align: center; 
             padding: 12px 8px; 
             border: 1px solid #16A249;
             font-size: 12px;
             white-space: nowrap;
         }
         td { 
             padding: 8px; 
             border: 1px solid #ddd; 
             text-align: left;
             vertical-align: middle;
             word-wrap: break-word;
             max-width: 200px;
         }
         tr:nth-child(even) { background-color: #f8fbf8; }
         tr:hover { background-color: #f2f8f2; }
         .header { 
             text-align: center; 
             margin-bottom: 20px; 
             color: #16A249; 
             font-size: 18px; 
             font-weight: bold; 
         }
         .subheader { 
             text-align: center; 
             margin-bottom: 20px; 
             color: #666; 
             font-size: 14px; 
         }
         .employee-name { 
             font-weight: 500; 
             color: #333; 
         }
         .employee-id { 
             font-size: 11px; 
             color: #666; 
             margin-top: 2px; 
         }
         .status-present { color: #16A249; font-weight: 500; }
         .status-late { color: #f59e0b; font-weight: 500; }
         .status-absent { color: #ef4444; font-weight: 500; }
         .status-halfday { color: #3b82f6; font-weight: 500; }
         @media print {
             body { margin: 10px; }
             table { font-size: 10px; }
             th, td { padding: 6px 4px; }
         }
     </style>
</head>
<body>
    <div class="header">HRM & Payroll System</div>
    <div class="subheader">Attendance Report - ' . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . '</div>
    <table>
        <thead>
            <tr>
                <th>Employee No.</th>
                <th>Employee Name</th>
                <th>Position</th>
                <th>Date</th>
                <th>Morning In</th>
                <th>Morning Out</th>
                <th>Afternoon In</th>
                <th>Afternoon Out</th>
                <th>Status</th>
                <th>Total Hours</th>
            </tr>
        </thead>
        <tbody>';
    
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
        
                 // Determine status class for styling
         $statusClass = 'status-present';
         switch(strtolower($row['status'])) {
             case 'late': $statusClass = 'status-late'; break;
             case 'absent': $statusClass = 'status-absent'; break;
             case 'half day': $statusClass = 'status-halfday'; break;
             default: $statusClass = 'status-present';
         }
         
         echo '<tr>
                 <td><strong>' . htmlspecialchars($row['employee_no']) . '</strong></td>
                 <td>
                     <div class="employee-name">' . htmlspecialchars(ucwords(strtolower($row['full_name']))) . '</div>
                 </td>
                 <td>' . htmlspecialchars($row['position']) . '</td>
                 <td>' . htmlspecialchars(date('M d, Y', strtotime($row['date']))) . '</td>
                 <td>' . htmlspecialchars($morningIn) . '</td>
                 <td>' . htmlspecialchars($morningOut) . '</td>
                 <td>' . htmlspecialchars($afternoonIn) . '</td>
                 <td>' . htmlspecialchars($afternoonOut) . '</td>
                 <td class="' . $statusClass . '">' . htmlspecialchars($row['status']) . '</td>
                 <td><strong>' . htmlspecialchars($totalHours . ' hrs') . '</strong></td>
               </tr>';
    }
    
    echo '</tbody>
    </table>
    <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
        Generated on ' . date('F j, Y \a\t g:i A') . '
    </div>
</body>
</html>';
    
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Export failed: ' . $e->getMessage()]);
}
?>
