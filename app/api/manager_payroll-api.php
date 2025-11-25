<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/secure_session.php';
require_once __DIR__ . '/../core/database.php';

// Start secure session
startSecureSession();

// Allow only admin/HR access
$adminId = $_SESSION['SESSION_USER_ID'] ?? null;

if (!$adminId || empty($adminId)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized. Please log in as admin.'
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$db = new Database();
$pdo = $db->getConnection();

if ($method === 'GET') {
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;

    if (!$start_date || !$end_date) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Start date and end date are required.'
        ]);
        exit;
    }

    try {
        // Get benefit rates
        $rateStmt = $pdo->query("SELECT * FROM benefit_rates WHERE status = 'active' LIMIT 1");
        $rateRow = $rateStmt->fetch(PDO::FETCH_ASSOC);
        $sss_rate = $rateRow['sss_rate'] ?? 0;
        $pagibig_rate = $rateRow['pagibig_rate'] ?? 0;
        $philhealth_rate = $rateRow['philhealth_rate'] ?? 0;
        $total_benefit_rate = ($sss_rate + $pagibig_rate + $philhealth_rate) / 100;

        // Get all managers (not just under a specific manager)
        $managersStmt = $pdo->prepare("
            SELECT id, m_employee_id, m_first_name, m_middle_name, m_last_name, m_position, m_base_salary
            FROM managers
            WHERE deleted_at IS NULL
        ");
        $managersStmt->execute();
        $managers = $managersStmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate date range
        $dateRange = [];
        $period = new DatePeriod(new DateTime($start_date), new DateInterval('P1D'), (new DateTime($end_date))->modify('+1 day'));
        foreach ($period as $date) {
            $dateRange[] = $date->format('Y-m-d');
        }

        // Attendance records for managers
        $attendanceStmt = $pdo->prepare("
            SELECT a.*, m.m_employee_id
            FROM attendance a
            INNER JOIN managers m ON a.manager_id = m.id
            WHERE a.date BETWEEN :start AND :end AND a.manager_id IS NOT NULL
        ");
        $attendanceStmt->execute([':start' => $start_date, ':end' => $end_date]);
        $attendanceRows = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);

        $attendanceMap = [];
        foreach ($attendanceRows as $att) {
            $key = $att['m_employee_id'] . '_' . $att['date'];
            $attendanceMap[$key] = $att;
        }

        // Note: Managers don't have leave records in the leaves table (only employees do)
        // If you need leave tracking for managers, you'll need to add that functionality

        $result = [];

        foreach ($managers as $mgr) {
            $emp_no = $mgr['m_employee_id'] ?: 'N/A';
            $mgr_id = $mgr['id'];
            $base_salary = $mgr['m_base_salary'] ?? 0;
            $present = $absent = $total_hours = 0;

            foreach ($dateRange as $date) {
                $key = $emp_no . '_' . $date;
                if (isset($attendanceMap[$key])) {
                    $present++;
                    $total_hours += 8;
                } else {
                    $absent++;
                }
            }

            $leave = 0; // Managers don't have leave records in current schema

            if ($present === 0 && $absent === 0 && $leave === 0) continue;

            $gross = $base_salary * ($total_hours / 8);
            $sss = $gross * ($sss_rate / 100);
            $pagibig = $gross * ($pagibig_rate / 100);
            $philhealth = $gross * ($philhealth_rate / 100);
            $benefit_deduction = $sss + $pagibig + $philhealth;

            $daily_rate = $base_salary;
            $leave_deduction = $daily_rate * $leave;

            $total_deductions = $benefit_deduction + $leave_deduction;
            $net = $gross - $total_deductions;

            // Check if payroll already processed
            $checkStmt = $pdo->prepare("
                SELECT COUNT(*) FROM payroll 
                WHERE manager_id = :manager_id AND pay_period_start = :start AND pay_period_end = :end
            ");
            $checkStmt->execute([
                ':manager_id' => $mgr_id,
                ':start' => $start_date,
                ':end' => $end_date
            ]);
            $already_processed = $checkStmt->fetchColumn() > 0;

            // Format name
            $first_name = ucwords(strtolower($mgr['m_first_name'] ?? ''));
            $middle_initial = !empty($mgr['m_middle_name']) ? strtoupper(substr($mgr['m_middle_name'], 0, 1)) . '. ' : '';
            $last_name = ucwords(strtolower($mgr['m_last_name'] ?? ''));
            $full_name = trim($first_name . ' ' . $middle_initial . $last_name);

            $result[] = [
                'manager_id' => $mgr_id,
                'employee_no' => $emp_no,
                'full_name' => $full_name,
                'position' => $mgr['m_position'] ?? 'Manager',
                'base_salary' => (float)$base_salary,
                'total_hours' => $total_hours,
                'present_days' => $present,
                'absent_days' => $absent,
                'leave_days' => $leave,
                'gross' => round($gross, 2),
                'sss_deduction' => round($sss, 2),
                'pagibig_deduction' => round($pagibig, 2),
                'philhealth_deduction' => round($philhealth, 2),
                'benefit_deduction' => round($benefit_deduction, 2),
                'leave_deduction' => round($leave_deduction, 2),
                'total_deductions' => round($total_deductions, 2),
                'net' => round($net, 2),
                'processed' => $already_processed
            ];
        }

        echo json_encode([
            'status' => 'success',
            'start_date' => $start_date,
            'end_date' => $end_date,
            'benefit_rates' => [
                'sss' => $sss_rate,
                'pagibig' => $pagibig_rate,
                'philhealth' => $philhealth_rate,
                'total' => $total_benefit_rate * 100
            ],
            'data' => $result,
            'day_count' => count($dateRange)
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

