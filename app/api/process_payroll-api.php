<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/secure_session.php';
require_once __DIR__ . '/../core/database.php';
require __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

// Start secure session
startSecureSession();

// Check authentication - allow admin, manager, or owner
$adminId = $_SESSION['SESSION_USER_ID'] ?? null;
$managerId = $_SESSION['manager_id'] ?? null;
$ownerId = $_SESSION['owner_id'] ?? null;

if ((!$adminId || empty($adminId)) && (!$managerId || empty($managerId)) && (!$ownerId || empty($ownerId))) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized. Please log in.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['payrolls'], $input['start_date'], $input['end_date'], $input['payroll_type'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

// Only use manager_id for generated_by due to foreign key constraint
// If user is admin/owner, we need to get a default manager ID for payslips table
// (payslips.generated_by is NOT NULL, but payroll.generated_by can be NULL)
$generatedBy = null;
if ($managerId) {
    $generatedBy = $managerId;
} else {
    // For admin/HR/owner, get the first available manager ID as fallback
    // This is needed because payslips.generated_by cannot be NULL
    $defaultManagerStmt = $pdo->prepare("SELECT id FROM managers WHERE deleted_at IS NULL LIMIT 1");
    $defaultManagerStmt->execute();
    $defaultManager = $defaultManagerStmt->fetch(PDO::FETCH_ASSOC);
    if ($defaultManager) {
        $generatedBy = $defaultManager['id'];
    } else {
        // If no manager exists, we cannot proceed (payslips requires generated_by)
        echo json_encode([
            'status' => 'error',
            'message' => 'Cannot process payroll: No manager found in the system. Please add a manager first.'
        ]);
        exit;
    }
}

try {
    $pdo->beginTransaction();
    
    $startDate = $input['start_date'];
    $endDate = $input['end_date'];
    $payrollType = $input['payroll_type'];
    $payrolls = $input['payrolls'];
    
    // Calculate duration (only weekdays, excluding Saturday and Sunday)
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $duration = 0;
    $current = clone $start;
    while ($current <= $end) {
        $dayOfWeek = (int)$current->format('w'); // 0 = Sunday, 6 = Saturday
        if ($dayOfWeek !== 0 && $dayOfWeek !== 6) {
            $duration++;
        }
        $current->modify('+1 day');
    }
    
    $processedEmployees = [];
    $errors = [];
    
    foreach ($payrolls as $payroll) {
        try {
            // Check if hr_id is provided in payload (for HR payroll)
            $currentHrId = $payroll['hr_id'] ?? null;
            // Check if manager_id is provided in payload (for manager payroll)
            $currentManagerId = $payroll['manager_id'] ?? null;
            
            if ($currentHrId) {
                // This is HR payroll
                $hrStmt = $pdo->prepare("SELECT id, hr_email, hr_first_name, hr_last_name, hr_employee_id FROM admins WHERE id = ? AND deleted_at IS NULL LIMIT 1");
                $hrStmt->execute([$currentHrId]);
                $hr = $hrStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$hr) {
                    $errors[] = "HR not found for ID: " . $currentHrId;
                    continue;
                }
                
                $employeeId = null; // For HR, employee_id is NULL
                $currentManagerId = null; // For HR, manager_id is NULL
                // Use hr_employee_id directly from database (e.g., "EMP-033873")
                $hrEmployeeId = trim($hr['hr_employee_id'] ?? '');
                if (!empty($hrEmployeeId) && $hrEmployeeId !== '') {
                    $employeeNo = $hrEmployeeId; // Use hr_employee_id as-is (e.g., "EMP-033873")
                    error_log("Using hr_employee_id from database in main loop: " . $employeeNo);
                } else {
                    // Fallback to HR ID if hr_employee_id is empty
                    error_log("Warning: hr_employee_id is empty for HR ID: " . $currentHrId . ", using fallback");
                    $employeeNo = 'HR-' . $currentHrId;
                }
            } elseif ($currentManagerId) {
                // This is manager payroll
                $managerStmt = $pdo->prepare("SELECT id, m_email, m_first_name, m_last_name, m_employee_id FROM managers WHERE id = ? AND deleted_at IS NULL LIMIT 1");
                $managerStmt->execute([$currentManagerId]);
                $manager = $managerStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$manager) {
                    $errors[] = "Manager not found for ID: " . $currentManagerId;
                    continue;
                }
                
                $employeeId = null; // For managers, employee_id is NULL
                // Use m_employee_id directly from database (e.g., "EMP-033873")
                $mEmployeeId = trim($manager['m_employee_id'] ?? '');
                if (!empty($mEmployeeId) && $mEmployeeId !== '') {
                    $employeeNo = $mEmployeeId; // Use m_employee_id as-is (e.g., "EMP-033873")
                    error_log("Using m_employee_id from database in main loop: " . $employeeNo);
                } else {
                    // Fallback to manager ID if m_employee_id is empty
                    error_log("Warning: m_employee_id is empty for manager ID: " . $currentManagerId . ", using fallback");
                    $employeeNo = 'MGR-' . $currentManagerId;
                }
            } else {
                // This is employee payroll - check if employee_id is provided, otherwise get by employee_no
                $currentEmployeeId = $payroll['employee_id'] ?? null;
                
                if ($currentEmployeeId) {
                    // Use provided employee_id
                    $employeeStmt = $pdo->prepare("SELECT id, email, first_name, last_name, employee_no FROM employees WHERE id = ? LIMIT 1");
                    $employeeStmt->execute([$currentEmployeeId]);
                } else {
                    // Fallback to employee_no lookup
                    $employeeStmt = $pdo->prepare("SELECT id, email, first_name, last_name, employee_no FROM employees WHERE employee_no = ? LIMIT 1");
                    $employeeStmt->execute([$payroll['employee_no']]);
                }
                
                $employee = $employeeStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$employee) {
                    $errors[] = "Employee not found for ID: " . ($currentEmployeeId ?? $payroll['employee_no']);
                    continue;
                }
                
                $employeeId = $employee['id'];
                $currentManagerId = null;
                $currentHrId = null;
                // Use employee_no directly from database
                $employeeNo = $employee['employee_no'] ?? '';
                if (empty($employeeNo)) {
                    // Fallback to employee ID if employee_no is empty
                    $employeeNo = 'EMP-' . $employeeId;
                }
            }
            
            // Check if payroll already exists
            if ($currentHrId) {
                $checkStmt = $pdo->prepare("SELECT id FROM payroll WHERE hr_id = ? AND pay_period_start = ? AND pay_period_end = ?");
                $checkStmt->execute([$currentHrId, $startDate, $endDate]);
            } elseif ($currentManagerId) {
                $checkStmt = $pdo->prepare("SELECT id FROM payroll WHERE manager_id = ? AND pay_period_start = ? AND pay_period_end = ?");
                $checkStmt->execute([$currentManagerId, $startDate, $endDate]);
            } else {
                $checkStmt = $pdo->prepare("SELECT id FROM payroll WHERE employee_id = ? AND pay_period_start = ? AND pay_period_end = ?");
                $checkStmt->execute([$employeeId, $startDate, $endDate]);
            }
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                $userType = $currentHrId ? "HR" : ($currentManagerId ? "manager" : "employee");
                $errors[] = "Payroll already processed for " . $userType . ": " . $employeeNo;
                continue;
            }
            
            // Insert payroll record
            // Both employee_id and manager_id can be NULL based on the schema
            $insertStmt = $pdo->prepare("
                INSERT INTO payroll (
                    employee_id, manager_id, hr_id,
                    pay_period_start, pay_period_end, payroll_frequency, payroll_duration,
                    total_hours, sss_deduction, pagibig_deduction, philhealth_deduction,
                    present_days, absent_days, leave_days,
                    total_deductions, gross_pay, net_pay, generated_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // For payroll table, generated_by can be NULL (for admin/HR/owner)
            // For payslips table, generated_by must be a valid manager ID (NOT NULL constraint)
            $payrollGeneratedBy = $managerId ? $managerId : null; // NULL for payroll table when admin/HR/owner
            $payslipGeneratedBy = $generatedBy; // Must be a valid manager ID for payslips table
            
            $insertStmt->execute([
                $employeeId, // NULL for managers/HR, actual ID for employees
                $currentManagerId, // NULL for employees/HR, actual ID for managers
                $currentHrId, // NULL for employees/managers, actual ID for HR
                $startDate,
                $endDate,
                $payrollType,
                $duration,
                $payroll['total_hours'] ?? 0,
                $payroll['sss_deduction'] ?? 0,
                $payroll['pagibig_deduction'] ?? 0,
                $payroll['philhealth_deduction'] ?? 0,
                $payroll['present_days'] ?? 0,
                $payroll['absent_days'] ?? 0,
                $payroll['leave_days'] ?? 0,
                $payroll['total_deductions'] ?? 0,
                $payroll['gross'] ?? 0,
                $payroll['net'] ?? 0,
                $payrollGeneratedBy // NULL for admin/HR/owner, manager_id for managers
            ]);
            
            $payrollId = $pdo->lastInsertId();
            
            // Generate payslip PDF - pass employeeNo directly to avoid re-querying
            $pdfPath = generatePayslipPDF($pdo, $payrollId, $currentManagerId, $employeeId, $currentHrId, $payroll, $startDate, $endDate, $employeeNo);
            
            // Insert payslip record
            // Both employee_id and manager_id can be NULL based on the schema
            $payslipStmt = $pdo->prepare("
                INSERT INTO payslips (
                    payroll_id, employee_id, manager_id, hr_id, generated_by, ps_pdf_file_path
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $payslipStmt->execute([
                $payrollId,
                $employeeId, // NULL for managers/HR, actual ID for employees
                $currentManagerId, // NULL for employees/HR, actual ID for managers
                $currentHrId, // NULL for employees/managers, actual ID for HR
                $payslipGeneratedBy, // Must be a valid manager ID (NOT NULL constraint)
                $pdfPath
            ]);
            
            // Send email with payslip
            if ($currentHrId) {
                sendPayslipEmail($hr['hr_email'], $hr['hr_first_name'] . ' ' . $hr['hr_last_name'], $pdfPath, $startDate, $endDate);
            } elseif ($currentManagerId) {
                sendPayslipEmail($manager['m_email'], $manager['m_first_name'] . ' ' . $manager['m_last_name'], $pdfPath, $startDate, $endDate);
            } else {
                sendPayslipEmail($employee['email'], $employee['first_name'] . ' ' . $employee['last_name'], $pdfPath, $startDate, $endDate);
            }
            
            $processedEmployees[] = $employeeNo;
            
        } catch (Exception $e) {
            $errors[] = "Error processing payroll for " . ($payroll['employee_no'] ?? 'unknown') . ": " . $e->getMessage();
            error_log("Payroll processing error: " . $e->getMessage());
        }
    }
    
    if (count($processedEmployees) === 0 && count($errors) > 0) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to process any payroll: ' . implode(', ', array_slice($errors, 0, 3))
        ]);
        exit;
    }
    
    $pdo->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Payroll processed successfully',
        'processed_employees' => $processedEmployees,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Payroll API Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

function generatePayslipPDF($pdo, $payrollId, $managerId, $employeeId, $hrId, $payrollData, $startDate, $endDate, $employeeNo = null) {
    // Get user details
    if ($hrId) {
        $userStmt = $pdo->prepare("
            SELECT hr_employee_id, hr_first_name, hr_middle_name, hr_last_name, hr_position, hr_base_salary, hr_email
            FROM admins WHERE id = ?
        ");
        $userStmt->execute([$hrId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        // Use employeeNo passed from main loop if available, otherwise calculate from database
        if ($employeeNo === null || empty($employeeNo)) {
            // Get hr_employee_id - handle both NULL and empty string
            $hrEmployeeId = isset($user['hr_employee_id']) ? trim($user['hr_employee_id']) : '';
            
            // Use hr_employee_id directly from database (e.g., "EMP-033873")
            // Check if it's not empty and not just whitespace
            if (!empty($hrEmployeeId) && strlen($hrEmployeeId) > 0) {
                $employeeNo = $hrEmployeeId; // Use hr_employee_id as-is (e.g., "EMP-033873")
                error_log("SUCCESS: Using hr_employee_id from database: " . $employeeNo);
            } else {
                // Fallback to HR ID if hr_employee_id is empty
                error_log("WARNING: hr_employee_id is empty/null for HR ID: " . $hrId . ", hr_employee_id value: '" . $hrEmployeeId . "', using fallback HR-" . $hrId);
                $employeeNo = 'HR-' . $hrId;
            }
        } else {
            // Use the employeeNo passed from main loop
            error_log("Using employeeNo passed from main loop: " . $employeeNo);
        }
        $baseSalary = $user['hr_base_salary'] ?? 0;
        $firstName = ucwords(strtolower($user['hr_first_name'] ?? ''));
        $middleName = !empty($user['hr_middle_name']) ? ucwords(strtolower(substr($user['hr_middle_name'], 0, 1))) . '. ' : '';
        $lastName = ucwords(strtolower($user['hr_last_name'] ?? ''));
        $fullName = trim($firstName . ' ' . $middleName . $lastName);
        $position = $user['hr_position'] ?? '';
    } elseif ($managerId) {
        $userStmt = $pdo->prepare("
            SELECT m_employee_id, m_first_name, m_middle_name, m_last_name, m_position, m_base_salary, m_email
            FROM managers WHERE id = ?
        ");
        $userStmt->execute([$managerId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        // Use employeeNo passed from main loop if available, otherwise calculate from database
        if ($employeeNo === null || empty($employeeNo)) {
            // Get m_employee_id - handle both NULL and empty string
            $mEmployeeId = isset($user['m_employee_id']) ? trim($user['m_employee_id']) : '';
            
            // Use m_employee_id directly from database (e.g., "EMP-033873")
            // Check if it's not empty and not just whitespace
            if (!empty($mEmployeeId) && strlen($mEmployeeId) > 0) {
                $employeeNo = $mEmployeeId; // Use m_employee_id as-is (e.g., "EMP-033873")
                error_log("SUCCESS: Using m_employee_id from database: " . $employeeNo);
            } else {
                // Fallback to manager ID if m_employee_id is empty
                error_log("WARNING: m_employee_id is empty/null for manager ID: " . $managerId . ", m_employee_id value: '" . $mEmployeeId . "', using fallback MGR-" . $managerId);
                $employeeNo = 'MGR-' . $managerId;
            }
        } else {
            // Use the employeeNo passed from main loop
            error_log("Using employeeNo passed from main loop: " . $employeeNo);
        }
        $baseSalary = $user['m_base_salary'] ?? 0;
        $firstName = ucwords(strtolower($user['m_first_name'] ?? ''));
        $middleName = !empty($user['m_middle_name']) ? ucwords(strtolower(substr($user['m_middle_name'], 0, 1))) . '. ' : '';
        $lastName = ucwords(strtolower($user['m_last_name'] ?? ''));
        $fullName = trim($firstName . ' ' . $middleName . $lastName);
        $position = $user['m_position'] ?? '';
    } else {
        $userStmt = $pdo->prepare("
            SELECT employee_no, first_name, middle_name, last_name, position, base_salary, email
            FROM employees WHERE id = ?
        ");
        $userStmt->execute([$employeeId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        // Use employeeNo passed from main loop if available, otherwise calculate from database
        if ($employeeNo === null || empty($employeeNo)) {
            $employeeNo = trim($user['employee_no'] ?? '');
            // Fallback to employee ID if employee_no is empty
            if (empty($employeeNo) || $employeeNo === '') {
                error_log("Warning: employee_no is empty for employee ID: " . $employeeId . ", using fallback");
                $employeeNo = 'EMP-' . $employeeId;
            } else {
                error_log("Using employee_no from database: " . $employeeNo);
            }
        } else {
            // Use the employeeNo passed from main loop
            error_log("Using employeeNo passed from main loop: " . $employeeNo);
        }
        $baseSalary = $user['base_salary'] ?? 0;
        $firstName = ucwords(strtolower($user['first_name'] ?? ''));
        $middleName = !empty($user['middle_name']) ? ucwords(strtolower(substr($user['middle_name'], 0, 1))) . '. ' : '';
        $lastName = ucwords(strtolower($user['last_name'] ?? ''));
        $fullName = trim($firstName . ' ' . $middleName . $lastName);
        $position = $user['position'] ?? '';
    }
    
    if (!$user) {
        throw new Exception("User not found");
    }
    
    // Create payslips directory if it doesn't exist
    $payslipDir = __DIR__ . '/../../public/payslips';
    if (!is_dir($payslipDir)) {
        mkdir($payslipDir, 0755, true);
    }
    
    // Generate PDF filename: payslip_{employee_no}_{startDate}_{endDate}.pdf
    // Ensure employeeNo is not empty
    if (empty($employeeNo)) {
        error_log("Warning: employeeNo is empty in generatePayslipPDF. HrId: " . ($hrId ?? 'null') . ", ManagerId: " . ($managerId ?? 'null') . ", EmployeeId: " . ($employeeId ?? 'null'));
        // Final fallback - use ID
        if ($hrId) {
            $employeeNo = 'HR-' . $hrId;
        } elseif ($managerId) {
            $employeeNo = 'MGR-' . $managerId;
        } else {
            $employeeNo = 'EMP-' . $employeeId;
        }
    }
    $filename = 'payslip_' . $employeeNo . '_' . $startDate . '_' . $endDate . '.pdf';
    $filepath = 'payslips/' . $filename;
    $fullPath = $payslipDir . '/' . $filename;
    
    // Get logo path for PDF
    $logoPath = __DIR__ . '/../../public/assets/image/test_logo_cropted.png';
    $logoExists = file_exists($logoPath);
    
    // Create HTML content for payslip
    $html = generatePayslipHTML($user, $payrollData, $startDate, $endDate, $fullName, $position, $employeeNo, $baseSalary, $logoPath, $logoExists);
    
    try {
        // Use DomPDF to generate PDF with proper configuration
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isFontSubsettingEnabled', true);
        $options->set('chroot', realpath(__DIR__ . '/../../'));
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        // Set paper size to 5.5" x 8.5" (Statement/Half Letter) - 396 x 612 points
        $dompdf->setPaper([0, 0, 396, 612], 'portrait');
        $dompdf->render();
        
        // Save PDF to file
        $output = $dompdf->output();
        file_put_contents($fullPath, $output);
        
        error_log("PDF generated successfully: " . $fullPath);
    } catch (Exception $e) {
        error_log("PDF generation error: " . $e->getMessage());
        error_log("PDF generation stack trace: " . $e->getTraceAsString());
        // Create placeholder file
        file_put_contents($fullPath, '');
        throw $e;
    }
    
    return $filepath;
}

function generatePayslipHTML($user, $payrollData, $startDate, $endDate, $fullName, $position, $employeeNo, $baseSalary, $logoPath = '', $logoExists = false) {
    $grossPay = $payrollData['gross'] ?? 0;
    $sss = $payrollData['sss_deduction'] ?? 0;
    $pagibig = $payrollData['pagibig_deduction'] ?? 0;
    $philhealth = $payrollData['philhealth_deduction'] ?? 0;
    $lateDeduction = $payrollData['late_deduction'] ?? 0;
    $undertimeDeduction = $payrollData['undertime_deduction'] ?? 0;
    $totalDeductions = $payrollData['total_deductions'] ?? 0;
    $netPay = $payrollData['net'] ?? 0;
    $presentDays = $payrollData['present_days'] ?? 0;
    
    // LWP (Leave Without Pay) calculation
    $lwp = $payrollData['leave_days'] ?? 0;
    
    // Calculate working days from date range
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $workingDays = $start->diff($end)->days + 1;
    
    // Format numbers with commas
    $formatNumber = function($num) {
        return number_format($num, 2);
    };
    
    // Format date for display (e.g., "Jul 1 - Jul 15, 2025")
    $formatDateRange = function($startStr, $endStr) {
        $start = new DateTime($startStr);
        $end = new DateTime($endStr);
        return $start->format('M j') . ' - ' . $end->format('M j, Y');
    };
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Payslip</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: DejaVu Sans, Arial, sans-serif; 
                font-size: 11px;
                padding: 30px 40px;
                color: #000;
            }
            .payslip {
                width: 100%;
            }
            .company-header {
                text-align: center;
                margin-bottom: 15px;
            }
            .company-name {
                font-size: 14px;
                font-weight: bold;
            }
            .company-address {
                font-size: 10px;
                color: #333;
            }
            .payslip-title {
                font-size: 12px;
                font-weight: bold;
                margin-top: 8px;
                text-decoration: underline;
            }
            .header-table {
                width: 100%;
                border: none;
                margin-bottom: 20px;
            }
            .header-table td {
                border: none;
                padding: 2px 0;
                vertical-align: top;
                line-height: 1.6;
            }
            .header-left {
                width: 50%;
            }
            .header-right {
                width: 50%;
            }
            table.main-table {
                width: 100%;
                border-collapse: collapse;
            }
            .earnings-header td {
                background-color: #4CAF50;
                color: white;
                padding: 5px 8px;
                font-weight: bold;
                border: 1px solid #4CAF50;
            }
            .deductions-header td {
                background-color: #c0392b;
                color: white;
                padding: 5px 8px;
                font-weight: bold;
                border: 1px solid #c0392b;
            }
            table.main-table td {
                padding: 4px 8px;
                border: 1px solid #000;
            }
            .col-label {
                width: 55%;
            }
            .col-amount {
                width: 45%;
                text-align: right;
            }
            .text-right {
                text-align: right;
            }
            .font-bold {
                font-weight: bold;
            }
            .total-row td {
                border-top: none;
                border-left: none;
                border-right: none;
                border-bottom: 1px solid #000;
                font-weight: bold;
            }
            .total-row .col-label {
                text-align: right;
                padding-right: 10px;
            }
            .spacer-row td {
                border: none;
                height: 15px;
            }
            .net-row td {
                border-top: none;
                border-left: none;
                border-right: none;
                border-bottom: 1px solid #000;
                font-weight: bold;
            }
            .net-row .col-label {
                text-align: right;
                padding-right: 10px;
                font-weight: normal;
            }
        </style>
    </head>
    <body>
        <div class="payslip">
            <!-- Company Header -->
            <div class="company-header">
                <div class="company-name">Migrants Venture Corporation</div>
                <div class="company-address">Lapu-Lapu St. Tagum City, Davao Del Norte</div>
                <div class="payslip-title">PAYSLIP</div>
            </div>

            <!-- Employee Info Section -->
            <table class="header-table">
                <tr>
                    <td class="header-left">
                        <div>Employee Name: ' . htmlspecialchars($fullName) . '</div>
                        <div>Employee ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ' . htmlspecialchars($employeeNo) . '</div>
                        <div>Position&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  : ' . htmlspecialchars($position) . '</div>
                    </td>
                    <td class="header-right">
                        <div>Pay Period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ' . $formatDateRange($startDate, $endDate) . '</div>
                        <div>Working Days: ' . $presentDays . '</div>
                    </td>
                </tr>
            </table>

            <!-- Earnings Table -->
            <table class="main-table">
                <tr class="earnings-header">
                    <td class="col-label">Earnings</td>
                    <td class="col-amount">Amount</td>
                </tr>
                <tr>
                    <td class="col-label">Basic pay</td>
                    <td class="col-amount text-right">&#8369;' . $formatNumber($baseSalary) . '</td>
                </tr>
                <tr>
                    <td class="col-label">Gross pay</td>
                    <td class="col-amount text-right">&#8369;' . $formatNumber($grossPay) . '</td>
                </tr>
                <tr>
                    <td class="col-label">LWP</td>
                    <td class="col-amount text-right">' . $lwp . '</td>
                </tr>
                <tr class="spacer-row"><td></td><td></td></tr>
                <tr class="total-row">
                    <td class="col-label font-bold">Total Earnings</td>
                    <td class="col-amount text-right">&#8369;' . $formatNumber($grossPay) . '</td>
                </tr>
                <tr class="spacer-row"><td></td><td></td></tr>
                <tr class="deductions-header">
                    <td class="col-label">Deductions</td>
                    <td class="col-amount"></td>
                </tr>
                <tr>
                    <td class="col-label">SSS</td>
                    <td class="col-amount text-right">-&#8369;' . $formatNumber($sss) . '</td>
                </tr>
                <tr>
                    <td class="col-label">Pag-Ibig</td>
                    <td class="col-amount text-right">-&#8369;' . $formatNumber($pagibig) . '</td>
                </tr>
                <tr>
                    <td class="col-label">PhilHealth</td>
                    <td class="col-amount text-right">-&#8369;' . $formatNumber($philhealth) . '</td>
                </tr>
                <tr>
                    <td class="col-label">Late Deduction</td>
                    <td class="col-amount text-right">-&#8369;' . $formatNumber($lateDeduction) . '</td>
                </tr>
                <tr>
                    <td class="col-label">Undertime Deduction</td>
                    <td class="col-amount text-right">-&#8369;' . $formatNumber($undertimeDeduction) . '</td>
                </tr>
                <tr class="spacer-row"><td></td><td></td></tr>
                <tr class="total-row">
                    <td class="col-label font-bold">Total Deductions</td>
                    <td class="col-amount text-right">-&#8369;' . $formatNumber($totalDeductions) . '</td>
                </tr>
                <tr class="spacer-row"><td></td><td></td></tr>
                <tr class="net-row">
                    <td class="col-label font-bold"><strong>Net Pay</strong></td>
                    <td class="col-amount text-right font-bold">&#8369;' . $formatNumber($netPay) . '</td>
                </tr>
            </table>
        </div>
    </body>
    </html>';
    
    return $html;
}

function sendPayslipEmail($email, $name, $pdfPath, $startDate, $endDate) {
    try {
        $mail = new PHPMailer(true);
        
        // Gmail SMTP Configuration
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = 'migrantsventurecorporation@gmail.com';
        $mail->Password = 'tfop acec ukat dosw'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('migrantsventurecorporation@gmail.com', 'Migrants Venture Corporation');
        $mail->addReplyTo('support@migrantsventurecorp.ip-ddns.com', 'Support Team');
        $mail->addAddress($email, $name);
        
        // Attach PDF
        $fullPdfPath = __DIR__ . '/../../public/' . $pdfPath;
        if (file_exists($fullPdfPath)) {
            $mail->addAttachment($fullPdfPath, 'payslip_' . $startDate . '_' . $endDate . '.pdf');
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Payslip - ' . $startDate . ' to ' . $endDate;
        
        $mail->Body = "
        <p>Dear " . htmlspecialchars($name) . ",</p>
        <p>Please find attached your payslip for the period <strong>" . htmlspecialchars($startDate) . " to " . htmlspecialchars($endDate) . "</strong>.</p>
        <p>If you have any questions, please contact the HR department.</p>
        <br>
        <p>Best regards,<br>Migrants Venture Corporation</p>
        ";
        
        $mail->AltBody = "Dear $name,\n\nPlease find attached your payslip for the period $startDate to $endDate.\n\nBest regards,\nMigrants Venture Corporation";
        
        $mail->send();
        error_log("Payslip email sent successfully to: " . $email);
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        // Don't throw exception to avoid breaking the main flow
    }
}
?>
