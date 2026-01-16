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

        // Get all employees
        $employeesStmt = $pdo->prepare("
            SELECT id, employee_no, first_name, middle_name, last_name, position, base_salary
            FROM employees
            WHERE deleted_at IS NULL AND approved_by_manager = 1
        ");
        $employeesStmt->execute();
        $employees = $employeesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all managers
        $managersStmt = $pdo->prepare("
            SELECT id, m_employee_id, m_first_name, m_middle_name, m_last_name, m_position, m_base_salary
            FROM managers
            WHERE deleted_at IS NULL
        ");
        $managersStmt->execute();
        $managers = $managersStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all HR (admins)
        $hrStmt = $pdo->prepare("
            SELECT id, hr_employee_id, hr_first_name, hr_middle_name, hr_last_name, hr_position, hr_base_salary
            FROM admins
            WHERE deleted_at IS NULL
        ");
        $hrStmt->execute();
        $hrList = $hrStmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate date range
        $dateRange = [];
        $period = new DatePeriod(new DateTime($start_date), new DateInterval('P1D'), (new DateTime($end_date))->modify('+1 day'));
        foreach ($period as $date) {
            $dateRange[] = $date->format('Y-m-d');
        }

        // Attendance records for employees
        $empAttendanceStmt = $pdo->prepare("
            SELECT a.*, e.employee_no
            FROM attendance a
            INNER JOIN employees e ON a.employee_id = e.id
            WHERE a.date BETWEEN :start AND :end AND a.employee_id IS NOT NULL
        ");
        $empAttendanceStmt->execute([':start' => $start_date, ':end' => $end_date]);
        $empAttendanceRows = $empAttendanceStmt->fetchAll(PDO::FETCH_ASSOC);

        // Attendance records for managers
        $mgrAttendanceStmt = $pdo->prepare("
            SELECT a.*, m.m_employee_id
            FROM attendance a
            INNER JOIN managers m ON a.manager_id = m.id
            WHERE a.date BETWEEN :start AND :end AND a.manager_id IS NOT NULL
        ");
        $mgrAttendanceStmt->execute([':start' => $start_date, ':end' => $end_date]);
        $mgrAttendanceRows = $mgrAttendanceStmt->fetchAll(PDO::FETCH_ASSOC);

        // Attendance records for HR
        $hrAttendanceStmt = $pdo->prepare("
            SELECT a.*, admin.hr_employee_id
            FROM attendance a
            INNER JOIN admins admin ON a.hr_id = admin.id
            WHERE a.date BETWEEN :start AND :end AND a.hr_id IS NOT NULL
        ");
        $hrAttendanceStmt->execute([':start' => $start_date, ':end' => $end_date]);
        $hrAttendanceRows = $hrAttendanceStmt->fetchAll(PDO::FETCH_ASSOC);

        // Create attendance maps
        $empAttendanceMap = [];
        foreach ($empAttendanceRows as $att) {
            $key = $att['employee_no'] . '_' . $att['date'];
            $empAttendanceMap[$key] = $att;
        }

        $mgrAttendanceMap = [];
        foreach ($mgrAttendanceRows as $att) {
            $key = $att['m_employee_id'] . '_' . $att['date'];
            $mgrAttendanceMap[$key] = $att;
        }

        $hrAttendanceMap = [];
        foreach ($hrAttendanceRows as $att) {
            $key = $att['hr_employee_id'] . '_' . $att['date'];
            $hrAttendanceMap[$key] = $att;
        }

        // Get leave records for employees
        $leaveStmt = $pdo->prepare("
            SELECT employee_id, COUNT(*) as leave_count
            FROM leaves
            WHERE start_date <= :end AND end_date >= :start 
            AND status = 'Approved'
            AND employee_id IS NOT NULL
            GROUP BY employee_id
        ");
        $leaveStmt->execute([':start' => $start_date, ':end' => $end_date]);
        $leaveRows = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);
        $leaveMap = [];
        foreach ($leaveRows as $lv) {
            $leaveMap[$lv['employee_id']] = (int)$lv['leave_count'];
        }

        $result = [];

        // Process Employees
        foreach ($employees as $emp) {
            $emp_no = $emp['employee_no'] ?: 'N/A';
            $emp_id = $emp['id'];
            $base_salary = $emp['base_salary'] ?? 0;
            $present = $absent = $total_hours = 0;

            foreach ($dateRange as $date) {
                $key = $emp_no . '_' . $date;
                if (isset($empAttendanceMap[$key])) {
                    $present++;
                    $total_hours += 8;
                } else {
                    $absent++;
                }
            }

            $leave = $leaveMap[$emp_id] ?? 0;

            if ($present === 0 && $absent === 0 && $leave === 0) continue;

            $gross = $base_salary * ($total_hours / 8);
            $sss = $gross * ($sss_rate / 100);
            $pagibig = $gross * ($pagibig_rate / 100);
            $philhealth = $gross * ($philhealth_rate / 100);
            $benefit_deduction = $sss + $pagibig + $philhealth;

            $daily_rate = $base_salary;
            $leave_deduction = ($daily_rate / (count($dateRange) ?: 22)) * $leave;
            $total_deductions = $benefit_deduction + $leave_deduction;
            $net = $gross - $total_deductions;

            // Check if already processed
            $checkProcessed = $pdo->prepare("
                SELECT id FROM payroll 
                WHERE employee_id = :emp_id 
                AND pay_period_start = :start 
                AND pay_period_end = :end
                LIMIT 1
            ");
            $checkProcessed->execute([
                ':emp_id' => $emp_id,
                ':start' => $start_date,
                ':end' => $end_date
            ]);
            $already_processed = $checkProcessed->fetch() ? true : false;

            // Format name
            $first_name = ucwords(strtolower($emp['first_name'] ?? ''));
            $middle_initial = !empty($emp['middle_name']) ? strtoupper(substr($emp['middle_name'], 0, 1)) . '. ' : '';
            $last_name = ucwords(strtolower($emp['last_name'] ?? ''));
            $full_name = trim($first_name . ' ' . $middle_initial . $last_name);

            $result[] = [
                'employee_id' => $emp_id,
                'manager_id' => null,
                'hr_id' => null,
                'employee_no' => $emp_no,
                'full_name' => $full_name,
                'position' => $emp['position'] ?? 'Employee',
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
                'processed' => $already_processed,
                'user_type' => 'employee'
            ];
        }

        // Process Managers
        foreach ($managers as $mgr) {
            $emp_no = $mgr['m_employee_id'] ?: 'N/A';
            $mgr_id = $mgr['id'];
            $base_salary = $mgr['m_base_salary'] ?? 0;
            $present = $absent = $total_hours = 0;

            foreach ($dateRange as $date) {
                $key = $emp_no . '_' . $date;
                if (isset($mgrAttendanceMap[$key])) {
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
            $leave_deduction = 0;
            $total_deductions = $benefit_deduction + $leave_deduction;
            $net = $gross - $total_deductions;

            // Check if already processed
            $checkProcessed = $pdo->prepare("
                SELECT id FROM payroll 
                WHERE manager_id = :mgr_id 
                AND pay_period_start = :start 
                AND pay_period_end = :end
                LIMIT 1
            ");
            $checkProcessed->execute([
                ':mgr_id' => $mgr_id,
                ':start' => $start_date,
                ':end' => $end_date
            ]);
            $already_processed = $checkProcessed->fetch() ? true : false;

            // Format name
            $first_name = ucwords(strtolower($mgr['m_first_name'] ?? ''));
            $middle_initial = !empty($mgr['m_middle_name']) ? strtoupper(substr($mgr['m_middle_name'], 0, 1)) . '. ' : '';
            $last_name = ucwords(strtolower($mgr['m_last_name'] ?? ''));
            $full_name = trim($first_name . ' ' . $middle_initial . $last_name);

            $result[] = [
                'employee_id' => null,
                'manager_id' => $mgr_id,
                'hr_id' => null,
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
                'processed' => $already_processed,
                'user_type' => 'manager'
            ];
        }

        // Process HR
        foreach ($hrList as $hr) {
            $emp_no = $hr['hr_employee_id'] ?: 'N/A';
            $hr_id = $hr['id'];
            $base_salary = $hr['hr_base_salary'] ?? 0;
            $present = $absent = $total_hours = 0;

            foreach ($dateRange as $date) {
                $key = $emp_no . '_' . $date;
                if (isset($hrAttendanceMap[$key])) {
                    $present++;
                    $total_hours += 8;
                } else {
                    $absent++;
                }
            }

            $leave = 0; // HR don't have leave records in current schema

            if ($present === 0 && $absent === 0 && $leave === 0) continue;

            $gross = $base_salary * ($total_hours / 8);
            $sss = $gross * ($sss_rate / 100);
            $pagibig = $gross * ($pagibig_rate / 100);
            $philhealth = $gross * ($philhealth_rate / 100);
            $benefit_deduction = $sss + $pagibig + $philhealth;

            $daily_rate = $base_salary;
            $leave_deduction = 0;
            $total_deductions = $benefit_deduction + $leave_deduction;
            $net = $gross - $total_deductions;

            // Check if already processed
            $checkProcessed = $pdo->prepare("
                SELECT id FROM payroll 
                WHERE hr_id = :hr_id 
                AND pay_period_start = :start 
                AND pay_period_end = :end
                LIMIT 1
            ");
            $checkProcessed->execute([
                ':hr_id' => $hr_id,
                ':start' => $start_date,
                ':end' => $end_date
            ]);
            $already_processed = $checkProcessed->fetch() ? true : false;

            // Format name
            $first_name = ucwords(strtolower($hr['hr_first_name'] ?? ''));
            $middle_initial = !empty($hr['hr_middle_name']) ? strtoupper(substr($hr['hr_middle_name'], 0, 1)) . '. ' : '';
            $last_name = ucwords(strtolower($hr['hr_last_name'] ?? ''));
            $full_name = trim($first_name . ' ' . $middle_initial . $last_name);

            $result[] = [
                'employee_id' => null,
                'manager_id' => null,
                'hr_id' => $hr_id,
                'employee_no' => $emp_no,
                'full_name' => $full_name,
                'position' => $hr['hr_position'] ?? 'HR',
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
                'processed' => $already_processed,
                'user_type' => 'hr'
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

