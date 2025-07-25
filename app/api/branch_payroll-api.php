<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../core/database.php';

$managerId = $_SESSION['manager_id'] ?? null;
if (!$managerId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized. Please log in as a manager.'
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

        // Get employees under the manager
        $employeesStmt = $pdo->prepare("
            SELECT id, employee_no, first_name, middle_name, last_name, position, base_salary
            FROM employees
            WHERE branch_manager = :manager_id AND approved_by_manager = 1
        ");
        $employeesStmt->execute([':manager_id' => $managerId]);
        $employees = $employeesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate date range
        $dateRange = [];
        $period = new DatePeriod(new DateTime($start_date), new DateInterval('P1D'), (new DateTime($end_date))->modify('+1 day'));
        foreach ($period as $date) {
            $dateRange[] = $date->format('Y-m-d');
        }

        // Attendance records
        $attendanceStmt = $pdo->prepare("
            SELECT a.*, e.employee_no
            FROM attendance a
            INNER JOIN employees e ON a.employee_id = e.id
            WHERE a.date BETWEEN :start AND :end
        ");
        $attendanceStmt->execute([':start' => $start_date, ':end' => $end_date]);
        $attendanceRows = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);

        $attendanceMap = [];
        foreach ($attendanceRows as $att) {
            $key = $att['employee_no'] . '_' . $att['date'];
            $attendanceMap[$key] = $att;
        }

        // Get approved leave durations
        $leaveStmt = $pdo->prepare("
            SELECT employee_id, duration
            FROM leaves
            WHERE status = 'Approved' AND start_date <= :end AND end_date >= :start
        ");
        $leaveStmt->execute([':start' => $start_date, ':end' => $end_date]);
        $leaveRows = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);

        $leaveDurations = [];
        foreach ($leaveRows as $leave) {
            $empId = $leave['employee_id'];
            if (!isset($leaveDurations[$empId])) {
                $leaveDurations[$empId] = 0;
            }
            $leaveDurations[$empId] += (int)$leave['duration'];
        }

        $result = [];

        foreach ($employees as $emp) {
            $emp_no = $emp['employee_no'];
            $emp_id = $emp['id'];
            $base_salary = $emp['base_salary'];
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

            $leave = $leaveDurations[$emp_id] ?? 0;

            if ($present === 0 && $absent === 0 && $leave === 0) continue;

            $gross = $base_salary * ($total_hours / 8);
            $sss = $gross * ($sss_rate / 100);
            $pagibig = $gross * ($pagibig_rate / 100);
            $philhealth = $gross * ($philhealth_rate / 100);
            $benefit_deduction = $sss + $pagibig + $philhealth;

            $daily_rate = $base_salary; // Adjust if you use a different divisor
            $leave_deduction = $daily_rate * $leave;

            $total_deductions = $benefit_deduction + $leave_deduction;
            $net = $gross - $total_deductions;

            // Check if payroll already processed
            $checkStmt = $pdo->prepare("
                SELECT COUNT(*) FROM payroll 
                WHERE employee_id = :employee_id AND pay_period_start = :start AND pay_period_end = :end
            ");
            $checkStmt->execute([
                ':employee_id' => $emp_id,
                ':start' => $start_date,
                ':end' => $end_date
            ]);
            $already_processed = $checkStmt->fetchColumn() > 0;

            $result[] = [
                'employee_id' => $emp_id,
                'employee_no' => $emp_no,
                'full_name' => ucwords(strtolower($emp['first_name'])) . ' ' .
                                strtoupper(substr($emp['middle_name'], 0, 1)) . '. ' .
                                ucwords(strtolower($emp['last_name'])),
                'position' => $emp['position'],
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
