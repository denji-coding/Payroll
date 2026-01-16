<?php
require_once '../app/core/secure_session.php';
require_once '../app/core/session_helper.php';

// Debug: Log session state before auth check
error_log("=== USER_DASHBOARD ACCESS DEBUG ===");
error_log("Session ID: " . session_id());
error_log("Session name: " . session_name());
error_log("Session status: " . session_status());
error_log("Session data: " . json_encode([
    'employee_id' => $_SESSION['employee_id'] ?? 'NOT SET',
    'employee_no' => $_SESSION['employee_no'] ?? 'NOT SET',
    'can_access_employee_portal' => $_SESSION['can_access_employee_portal'] ?? 'NOT SET',
    'manager_id' => $_SESSION['manager_id'] ?? 'NOT SET',
    'SESSION_USER_ID' => $_SESSION['SESSION_USER_ID'] ?? 'NOT SET',
    'session_keys' => array_keys($_SESSION ?? [])
]));
error_log("Cookies: " . json_encode($_COOKIE));

// Output debug info as HTML comment for console inspection
echo "<!-- USER_DASHBOARD_DEBUG: " . json_encode([
    'session_id' => session_id(),
    'session_name' => session_name(),
    'employee_id' => $_SESSION['employee_id'] ?? 'NOT SET',
    'employee_no' => $_SESSION['employee_no'] ?? 'NOT SET',
    'can_access_employee_portal' => $_SESSION['can_access_employee_portal'] ?? 'NOT SET'
]) . " -->\n";

// Check if employee is logged in
requireEmployeeAuth();

// Log user activity
logUserActivity('Access employee dashboard');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';

// Get user ID based on type
$employee_id = null;
$manager_id = null;
$hr_id = null;
$user_type = 'employee';

if (isset($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['SESSION_USER_ID'])) {
    $hr_id = (int)$_SESSION['SESSION_USER_ID'];
    $user_type = 'hr';
} elseif (isset($_SESSION['manager_id']) && !empty($_SESSION['manager_id'])) {
    $manager_id = (int)$_SESSION['manager_id'];
    $user_type = 'manager';
} else {
    $employee_id = $_SESSION['employee_id'] ?? $_SESSION['employee_no'];
    if ($employee_id) {
        // Get actual employee ID if employee_no was provided
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT id FROM employees WHERE employee_no = ? OR id = ? LIMIT 1");
        $stmt->execute([$employee_id, $employee_id]);
        $emp = $stmt->fetch(PDO::FETCH_ASSOC);
        $employee_id = $emp ? (int)$emp['id'] : null;
    }
}

// Fetch recent activities
$recentActivities = [];
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 1. Recent Attendance (Time In/Out)
    if ($employee_id) {
        $attendanceStmt = $conn->prepare("
            SELECT 
                'attendance' as activity_type,
                CASE 
                    WHEN morning_in IS NOT NULL THEN CONCAT('Time in recorded - ', TIME_FORMAT(morning_in, '%h:%i %p'))
                    WHEN afternoon_in IS NOT NULL THEN CONCAT('Time in recorded - ', TIME_FORMAT(afternoon_in, '%h:%i %p'))
                    ELSE 'Attendance recorded'
                END as activity_text,
                date as activity_date,
                CASE 
                    WHEN morning_in IS NOT NULL THEN CONCAT(date, ' ', morning_in)
                    WHEN afternoon_in IS NOT NULL THEN CONCAT(date, ' ', afternoon_in)
                    ELSE updated_at
                END as activity_time
            FROM attendance 
            WHERE employee_id = :user_id 
            AND (morning_in IS NOT NULL OR afternoon_in IS NOT NULL)
            ORDER BY date DESC, 
                CASE 
                    WHEN morning_in IS NOT NULL THEN morning_in
                    WHEN afternoon_in IS NOT NULL THEN afternoon_in
                    ELSE updated_at
                END DESC
            LIMIT 5
        ");
        $attendanceStmt->execute([':user_id' => $employee_id]);
    } elseif ($manager_id) {
        $attendanceStmt = $conn->prepare("
            SELECT 
                'attendance' as activity_type,
                CASE 
                    WHEN morning_in IS NOT NULL THEN CONCAT('Time in recorded - ', TIME_FORMAT(morning_in, '%h:%i %p'))
                    WHEN afternoon_in IS NOT NULL THEN CONCAT('Time in recorded - ', TIME_FORMAT(afternoon_in, '%h:%i %p'))
                    ELSE 'Attendance recorded'
                END as activity_text,
                date as activity_date,
                CASE 
                    WHEN morning_in IS NOT NULL THEN CONCAT(date, ' ', morning_in)
                    WHEN afternoon_in IS NOT NULL THEN CONCAT(date, ' ', afternoon_in)
                    ELSE updated_at
                END as activity_time
            FROM attendance 
            WHERE manager_id = :user_id 
            AND (morning_in IS NOT NULL OR afternoon_in IS NOT NULL)
            ORDER BY date DESC, 
                CASE 
                    WHEN morning_in IS NOT NULL THEN morning_in
                    WHEN afternoon_in IS NOT NULL THEN afternoon_in
                    ELSE updated_at
                END DESC
            LIMIT 5
        ");
        $attendanceStmt->execute([':user_id' => $manager_id]);
    } elseif ($hr_id) {
        $attendanceStmt = $conn->prepare("
            SELECT 
                'attendance' as activity_type,
                CASE 
                    WHEN morning_in IS NOT NULL THEN CONCAT('Time in recorded - ', TIME_FORMAT(morning_in, '%h:%i %p'))
                    WHEN afternoon_in IS NOT NULL THEN CONCAT('Time in recorded - ', TIME_FORMAT(afternoon_in, '%h:%i %p'))
                    ELSE 'Attendance recorded'
                END as activity_text,
                date as activity_date,
                CASE 
                    WHEN morning_in IS NOT NULL THEN CONCAT(date, ' ', morning_in)
                    WHEN afternoon_in IS NOT NULL THEN CONCAT(date, ' ', afternoon_in)
                    ELSE updated_at
                END as activity_time
            FROM attendance 
            WHERE hr_id = :user_id 
            AND (morning_in IS NOT NULL OR afternoon_in IS NOT NULL)
            ORDER BY date DESC, 
                CASE 
                    WHEN morning_in IS NOT NULL THEN morning_in
                    WHEN afternoon_in IS NOT NULL THEN afternoon_in
                    ELSE updated_at
                END DESC
            LIMIT 5
        ");
        $attendanceStmt->execute([':user_id' => $hr_id]);
    }
    
    if (isset($attendanceStmt)) {
        $attendanceActivities = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($attendanceActivities as $activity) {
            $recentActivities[] = [
                'type' => 'attendance',
                'icon' => 'blue',
                'text' => $activity['activity_text'],
                'date' => $activity['activity_date'],
                'time' => $activity['activity_time']
            ];
        }
    }
    
    // 2. Recent Leave Requests
    if ($employee_id) {
        $leaveStmt = $conn->prepare("
            SELECT 
                'leave' as activity_type,
                CONCAT(leave_type, ' request submitted') as activity_text,
                CONCAT(start_date, ' - ', end_date, ' (', duration, ' days)') as details,
                created_at as activity_time,
                status
            FROM leaves 
            WHERE employee_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $leaveStmt->execute([':user_id' => $employee_id]);
    } elseif ($manager_id) {
        $leaveStmt = $conn->prepare("
            SELECT 
                'leave' as activity_type,
                CONCAT(leave_type, ' request submitted') as activity_text,
                CONCAT(start_date, ' - ', end_date, ' (', duration, ' days)') as details,
                created_at as activity_time,
                status
            FROM leaves 
            WHERE applicant_manager_id = :user_id AND applicant_type = 'manager'
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $leaveStmt->execute([':user_id' => $manager_id]);
    } elseif ($hr_id) {
        $leaveStmt = $conn->prepare("
            SELECT 
                'leave' as activity_type,
                CONCAT(leave_type, ' request submitted') as activity_text,
                CONCAT(start_date, ' - ', end_date, ' (', duration, ' days)') as details,
                created_at as activity_time,
                status
            FROM leaves 
            WHERE applicant_hr_id = :user_id AND applicant_type = 'hr'
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $leaveStmt->execute([':user_id' => $hr_id]);
    }
    
    if (isset($leaveStmt)) {
        $leaveActivities = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($leaveActivities as $activity) {
            $recentActivities[] = [
                'type' => 'leave',
                'icon' => 'purple',
                'text' => $activity['activity_text'],
                'details' => $activity['details'],
                'status' => $activity['status'],
                'time' => $activity['activity_time']
            ];
        }
    }
    
    // 3. Recent Payslips (based on date_generated)
    if ($employee_id) {
        $payslipStmt = $conn->prepare("
            SELECT 
                'payslip' as activity_type,
                'Payslip generated' as activity_text,
                CONCAT('Period: ', DATE_FORMAT(p.pay_period_start, '%M %d'), ' - ', DATE_FORMAT(p.pay_period_end, '%M %d, %Y')) as details,
                ps.date_generated as activity_time
            FROM payslips ps
            JOIN payroll p ON ps.payroll_id = p.id
            WHERE p.employee_id = :user_id 
            ORDER BY ps.date_generated DESC 
            LIMIT 5
        ");
        $payslipStmt->execute([':user_id' => $employee_id]);
    } elseif ($manager_id) {
        $payslipStmt = $conn->prepare("
            SELECT 
                'payslip' as activity_type,
                'Payslip generated' as activity_text,
                CONCAT('Period: ', DATE_FORMAT(p.pay_period_start, '%M %d'), ' - ', DATE_FORMAT(p.pay_period_end, '%M %d, %Y')) as details,
                ps.date_generated as activity_time
            FROM payslips ps
            JOIN payroll p ON ps.payroll_id = p.id
            WHERE p.manager_id = :user_id 
            ORDER BY ps.date_generated DESC 
            LIMIT 5
        ");
        $payslipStmt->execute([':user_id' => $manager_id]);
    } elseif ($hr_id) {
        $payslipStmt = $conn->prepare("
            SELECT 
                'payslip' as activity_type,
                'Payslip generated' as activity_text,
                CONCAT('Period: ', DATE_FORMAT(p.pay_period_start, '%M %d'), ' - ', DATE_FORMAT(p.pay_period_end, '%M %d, %Y')) as details,
                ps.date_generated as activity_time
            FROM payslips ps
            JOIN payroll p ON ps.payroll_id = p.id
            WHERE p.hr_id = :user_id 
            ORDER BY ps.date_generated DESC 
            LIMIT 5
        ");
        $payslipStmt->execute([':user_id' => $hr_id]);
    }
    
    if (isset($payslipStmt)) {
        $payslipActivities = $payslipStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($payslipActivities as $activity) {
            $recentActivities[] = [
                'type' => 'payslip',
                'icon' => 'green',
                'text' => $activity['activity_text'],
                'details' => $activity['details'],
                'time' => $activity['activity_time']
            ];
        }
    }
    
    // Sort all activities by time (most recent first)
    usort($recentActivities, function($a, $b) {
        $timeA = strtotime($a['time'] ?? '1970-01-01');
        $timeB = strtotime($b['time'] ?? '1970-01-01');
        return $timeB - $timeA;
    });
    
    // Limit to 5 most recent
    $recentActivities = array_slice($recentActivities, 0, 5);
    
} catch (Exception $e) {
    error_log("Error fetching recent activities: " . $e->getMessage());
    $recentActivities = [];
}

// Fetch attendance data for dashboard
$todayStatus = 'Absent';
$todayCheckIn = null;
$todayWorkHours = 0;
$weekWorkHours = 0;
$weeklyChartData = [0, 0, 0, 0, 0, 0, 0]; // Default: 7 days
$weeklyChartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; // Default labels
$recentAttendance = [];
$detailedAttendance = []; // For modal display

try {
    $db = new Database();
    $conn = $db->getConnection();
    $today = date('Y-m-d');
    
    // Get user ID for attendance queries
    $attendanceUserId = $employee_id ?? $manager_id ?? $hr_id;
    $attendanceUserField = $employee_id ? 'employee_id' : ($manager_id ? 'manager_id' : 'hr_id');
    
    if ($attendanceUserId) {
        // 1. Today's attendance status
        $todayStmt = $conn->prepare("
            SELECT 
                morning_in,
                morning_out,
                afternoon_in,
                afternoon_out,
                date
            FROM attendance 
            WHERE $attendanceUserField = :user_id 
            AND date = :today
            LIMIT 1
        ");
        $todayStmt->execute([':user_id' => $attendanceUserId, ':today' => $today]);
        $todayRecord = $todayStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($todayRecord) {
            // Calculate today's work hours
            $morningHours = 0;
            $afternoonHours = 0;
            
            if ($todayRecord['morning_in'] && $todayRecord['morning_out']) {
                $morningHours = (strtotime($todayRecord['morning_out']) - strtotime($todayRecord['morning_in'])) / 3600;
            }
            if ($todayRecord['afternoon_in'] && $todayRecord['afternoon_out']) {
                $afternoonHours = (strtotime($todayRecord['afternoon_out']) - strtotime($todayRecord['afternoon_in'])) / 3600;
            }
            
            $todayWorkHours = round($morningHours + $afternoonHours, 2);
            
            // Determine status
            if ($todayRecord['morning_in'] || $todayRecord['afternoon_in']) {
                $todayStatus = 'Present';
                $todayCheckIn = $todayRecord['morning_in'] ? $todayRecord['morning_in'] : $todayRecord['afternoon_in'];
            }
        }
        
        // 2. Weekly attendance data for chart (last 7 days)
        $weekStart = date('Y-m-d', strtotime('-6 days'));
        $weeklyStmt = $conn->prepare("
            SELECT 
                date,
                morning_in,
                morning_out,
                afternoon_in,
                afternoon_out
            FROM attendance 
            WHERE $attendanceUserField = :user_id 
            AND date >= :week_start
            AND date <= :today
            ORDER BY date ASC
        ");
        $weeklyStmt->execute([
            ':user_id' => $attendanceUserId,
            ':week_start' => $weekStart,
            ':today' => $today
        ]);
        $weeklyRecords = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create a map of dates to hours
        $weeklyHoursMap = [];
        foreach ($weeklyRecords as $record) {
            $morningHours = 0;
            $afternoonHours = 0;
            
            if ($record['morning_in'] && $record['morning_out']) {
                $morningHours = (strtotime($record['morning_out']) - strtotime($record['morning_in'])) / 3600;
            }
            if ($record['afternoon_in'] && $record['afternoon_out']) {
                $afternoonHours = (strtotime($record['afternoon_out']) - strtotime($record['afternoon_in'])) / 3600;
            }
            
            $weeklyHoursMap[$record['date']] = round($morningHours + $afternoonHours, 2);
        }
        
        // Generate chart data for last 7 days
        $weeklyChartLabels = [];
        $weeklyChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D', strtotime($date));
            $weeklyChartLabels[] = $dayName;
            $weeklyChartData[] = $weeklyHoursMap[$date] ?? 0;
        }
        
        // Calculate week total
        $weekWorkHours = round(array_sum($weeklyChartData), 2);
        
        // 3. Recent attendance records (last 5) for dashboard
        $recentStmt = $conn->prepare("
            SELECT 
                date,
                morning_in,
                morning_out,
                afternoon_in,
                afternoon_out
            FROM attendance 
            WHERE $attendanceUserField = :user_id 
            AND (morning_in IS NOT NULL OR afternoon_in IS NOT NULL)
            ORDER BY date DESC, updated_at DESC
            LIMIT 5
        ");
        $recentStmt->execute([':user_id' => $attendanceUserId]);
        $recentRecords = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($recentRecords as $record) {
            // Determine status
            $status = 'Absent';
            $statusColor = 'gray';
            $timeIn = null;
            $timeOut = null;
            
            if ($record['morning_in'] || $record['afternoon_in']) {
                $status = 'Present';
                $statusColor = 'green';
                $timeIn = $record['morning_in'] ? $record['morning_in'] : $record['afternoon_in'];
            }
            
            // Get time out
            if ($record['afternoon_out']) {
                $timeOut = $record['afternoon_out'];
            } elseif ($record['morning_out']) {
                $timeOut = $record['morning_out'];
            }
            
            $recentAttendance[] = [
                'date' => $record['date'],
                'status' => $status,
                'statusColor' => $statusColor,
                'timeIn' => $timeIn,
                'timeOut' => $timeOut
            ];
        }
        
        // 4. Detailed attendance records for modal (last 30 days)
        $detailedAttendance = [];
        $monthStart = date('Y-m-d', strtotime('-30 days'));
        $detailedStmt = $conn->prepare("
            SELECT 
                date,
                morning_in,
                morning_out,
                afternoon_in,
                afternoon_out,
                updated_at
            FROM attendance 
            WHERE $attendanceUserField = :user_id 
            AND date >= :month_start
            ORDER BY date DESC
        ");
        $detailedStmt->execute([
            ':user_id' => $attendanceUserId,
            ':month_start' => $monthStart
        ]);
        $detailedRecords = $detailedStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($detailedRecords as $record) {
            // Calculate work hours
            $morningHours = 0;
            $afternoonHours = 0;
            
            if ($record['morning_in'] && $record['morning_out']) {
                $morningHours = (strtotime($record['morning_out']) - strtotime($record['morning_in'])) / 3600;
            }
            if ($record['afternoon_in'] && $record['afternoon_out']) {
                $afternoonHours = (strtotime($record['afternoon_out']) - strtotime($record['afternoon_in'])) / 3600;
            }
            
            $totalHours = round($morningHours + $afternoonHours, 2);
            
            // Determine status
            $status = 'Absent';
            $statusColor = 'gray';
            if ($record['morning_in'] || $record['afternoon_in']) {
                $status = 'Present';
                $statusColor = 'green';
            }
            
            $detailedAttendance[] = [
                'date' => $record['date'],
                'status' => $status,
                'statusColor' => $statusColor,
                'morning_in' => $record['morning_in'],
                'morning_out' => $record['morning_out'],
                'afternoon_in' => $record['afternoon_in'],
                'afternoon_out' => $record['afternoon_out'],
                'total_hours' => $totalHours,
                'morning_hours' => round($morningHours, 2),
                'afternoon_hours' => round($afternoonHours, 2)
            ];
        }
    }
    
} catch (Exception $e) {
    error_log("Error fetching attendance data: " . $e->getMessage());
}

require views_path("user/user_dashboard");




