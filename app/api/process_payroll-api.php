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

$generatedBy = $adminId ?? $managerId ?? $ownerId;

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

try {
    $pdo->beginTransaction();
    
    $startDate = $input['start_date'];
    $endDate = $input['end_date'];
    $payrollType = $input['payroll_type'];
    $payrolls = $input['payrolls'];
    
    // Calculate duration
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $duration = $start->diff($end)->days + 1;
    
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
                // This is employee payroll - get employee by employee_no
                $employeeStmt = $pdo->prepare("SELECT id, email, first_name, last_name, employee_no FROM employees WHERE employee_no = ? LIMIT 1");
                $employeeStmt->execute([$payroll['employee_no']]);
                $employee = $employeeStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$employee) {
                    $errors[] = "Employee not found for ID: " . $payroll['employee_no'];
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
                $generatedBy
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
                $generatedBy,
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
        $dompdf->setPaper('A4', 'portrait');
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
    $leaveDeduction = ($payrollData['total_deductions'] ?? 0) - ($sss + $pagibig + $philhealth + $lateDeduction);
    $totalDeductions = $payrollData['total_deductions'] ?? 0;
    $netPay = $payrollData['net'] ?? 0;
    $totalHours = $payrollData['total_hours'] ?? 0;
    $absentDays = $payrollData['absent_days'] ?? 0;
    $leaveDays = $payrollData['leave_days'] ?? 0;
    $presentDays = $payrollData['present_days'] ?? 0;
    
    // Format numbers with commas
    $formatNumber = function($num) {
        return number_format($num, 2);
    };
    
    // Format date for display (e.g., "Jul 1, 2025")
    $formatDate = function($dateStr) {
        $date = new DateTime($dateStr);
        return $date->format('M j, Y');
    };
    
    // Convert logo to base64 if it exists - but skip if GD is not available to avoid errors
    // Note: Even with base64, Dompdf may still require GD for PNG processing in some cases
    $logoImgTag = '';
    $gdLoaded = extension_loaded('gd');
    
    if ($logoExists && $logoPath && file_exists($logoPath)) {
        // Only include logo if GD is loaded (required by Dompdf for PNG images)
        if ($gdLoaded) {
            try {
                $imageData = file_get_contents($logoPath);
                if ($imageData !== false && !empty($imageData)) {
                    // Determine MIME type from file extension
                    $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                    $mimeType = 'image/png'; // Default to PNG
                    
                    if ($ext === 'jpg' || $ext === 'jpeg') {
                        $mimeType = 'image/jpeg';
                    } elseif ($ext === 'gif') {
                        $mimeType = 'image/gif';
                    } elseif ($ext === 'png') {
                        $mimeType = 'image/png';
                    } elseif ($ext === 'svg') {
                        $mimeType = 'image/svg+xml';
                    }
                    
                    // Encode to base64 for embedding
                    $base64Data = base64_encode($imageData);
                    $logoImgTag = '<img src="data:' . $mimeType . ';base64,' . $base64Data . '" alt="Company Logo" class="logo-image" />';
                    
                    error_log("Logo converted to base64 successfully. MIME type: " . $mimeType . ", GD loaded: " . ($gdLoaded ? 'yes' : 'no'));
                } else {
                    error_log("Logo file could not be read: " . $logoPath);
                }
            } catch (Exception $e) {
                // If logo loading fails, just skip it (don't break the PDF generation)
                error_log("Logo loading error: " . $e->getMessage());
                $logoImgTag = '';
            }
        } else {
            // GD not loaded - skip logo to avoid PDF generation errors
            error_log("GD extension not loaded - skipping logo. Please enable GD extension in php.ini and restart Apache.");
            $logoImgTag = '';
        }
    } else {
        error_log("Logo file not found: " . ($logoPath ?? 'null'));
    }
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Payslip</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: "DejaVu Sans", Arial, Helvetica, sans-serif; 
                margin: 55px 45px; 
                color: #333; 
                background: #fff; 
                line-height: 1.2;
                font-size: 13px;
            }
            
            /* Header Section */
            .header-section { 
                width: 100%;
                margin-bottom: 20px; 
            }
            .header-top {
                display: table;
                width: 100%;
            }
            .header-left {
                display: table-cell;
                vertical-align: top;
                width: 60%;
            }
            .header-left-content {
                display: table;
                width: 100%;
            }
            .logo-container {
                display: table-cell;
                vertical-align: top;
                width: 70px;
                padding-right: 20px;
                
                
            }
            .logo-image {
                width: 100%;
                height: auto;
            }
            .company-info {
                display: table-cell;
                vertical-align: top;
                
            }
            .header-right {
                display: table-cell;
                vertical-align: top;
                text-align: right;
                width: 40%;
            }
            .header-pay-period {
                text-align: right;
                margin-top: 10px;
            }
            .company-name { 
                font-size: 22px; 
                font-weight: 700; 
                color: #000; 
                
                margin-bottom: 2px; 
                line-height: 1.2;
                
            }
            .company-address { 
                font-size: 11px; 
                color: #478547; 
                line-height: 1.2;
            }
            .pay-period-label { 
                font-weight: 700; 
                font-size: 11px; 
                margin-bottom: 2px; 
                color: #000000;
                line-height: 1.2;
            }
            .pay-period-date { 
                font-size: 11px; 
                color: #000000;
                line-height: 1.2;
                font-weight: normal;
            }
            
            /* Horizontal Line */
            hr { 
                border: none; 
                border-top: 1px solid #dee2e6; 
                margin: 15px 0; 
            }
            
            /* Two Column Layout */
            .row { 
                display: table;
                width: 100%;
                margin: 0;
            }
            .col-md-6 { 
                display: table-cell;
                width: 50%;
                vertical-align: top;
                padding: 0 10px; 
            }
            
            /* Section Titles */
            .section-title { 
                font-weight: 700; 
                font-size: 12px; 
                margin-bottom: 10px; 
                display: block; 
                color: #333;
            }
            
            /* Info Text */
            .info-text { 
                font-size: 12px; 
                margin: 0; 
            }
            .info-text p { 
                margin: 3px 0; 
                line-height: 1.1;
            }
            .info-text strong { 
                font-weight: 700; 
                color: #000;
            }
            
            /* Earnings and Deductions Section */
            .earnings-deductions-section {
                margin-top: 20px;
            }
            .earnings-title, .deductions-title { 
                font-size: 12px; 
                font-weight: 700; 
                margin-bottom: 8px; 
                display: block; 
                color: #333;
            }
            
            /* Tables */
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 5px;
            }
            table td { 
                padding: 6px 5px; 
                border-bottom: 1px solid #dee2e6; 
                font-size: 12px;
                line-height: 1.2;
            }
            table td:first-child {
                text-align: left;
            }
            table td:last-child {
                text-align: right;
            }
            .text-end { 
                text-align: right; 
            }
            
            /* Earnings Table */
            .earnings-table td {
                color: #333;
            }
            .total-earnings {
                font-weight: 700;
                color: #2d5016;
            }
            .total-earnings td {
                color: #2d5016;
            }
            
            /* Deductions Table */
            .deductions-table {
                background-color: #fff;
            }
            .deductions-table td {
                color: #333;
            }
            .total-deductions {
                font-weight: 700;
                color: #dc3545;
            }
            .total-deductions td {
                color: #dc3545;
            }
            
            /* Net Pay Section */
            .net-pay-box { 
                background-color: #f2f8f2; 
                border-radius: 8px; 
                padding: 20px; 
                margin-top: 20px; 
                display: table;
                width: 100%;
            }
            .net-pay-left {
                display: table-cell;
                vertical-align: middle;
                width: 60%;
            }
            .net-pay-right {
                display: table-cell;
                vertical-align: middle;
                text-align: right;
                width: 40%;
            }
            .net-pay-label { 
                font-weight: 700; 
                font-size: 14px; 
                color: #2d5016;
            }
            .net-pay-desc { 
                font-size: 12px; 
                color: #333; 
                margin-top: 5px; 
                font-weight: normal;
            }
            .net-pay-amount { 
                font-weight: 700; 
                font-size: 26px; 
                color: #2d5016; 
                text-align: right; 
            }
        </style>
    </head>
    <body>
        <!-- Header Section -->
        <div class="header-section">
            <div class="header-top">
                <div class="header-left">
                    <div class="header-left-content">
                        <div class="logo-container">
                            ' . $logoImgTag . '
                        </div>
                        <div class="company-info">
                            <div class="company-name">Migrants Venture Corporation</div>
                            <div class="company-address">Lapu-Lapu St. Tagum City, Davao Del Norte</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-pay-period">
                <div class="pay-period-label">PAY PERIOD</div>
                <div class="pay-period-date">' . $formatDate($startDate) . ' - ' . $formatDate($endDate) . '</div>
            </div>
        </div>

        

        <!-- Employee and Payment Details Section -->
        <div class="row">
            <div class="col-md-6">
                <span class="section-title">Employee Information</span>
                <div class="info-text"> 
                    <p><strong>Name:</strong> ' . htmlspecialchars($fullName) . '</p>
                    <p><strong>ID:</strong> ' . htmlspecialchars($employeeNo) . '</p>
                    <p><strong>Position:</strong> ' . htmlspecialchars($position) . '</p>
                </div>
            </div>
            <div class="col-md-6">
                <span class="section-title">Payment Details</span>
                <div class="info-text">
                    <p><strong>Basic Salary:</strong> ₱' . $formatNumber($baseSalary) . ' /day</p>
                    <p><strong>Total Hours:</strong> ' . $totalHours . '</p>
                    <p><strong>Present Days:</strong> ' . $presentDays . '</p>
                    <p><strong>Absent:</strong> ' . $absentDays . '</p>
                    <p><strong>Leave:</strong> ' . $leaveDays . '</p>
                </div>
            </div>
        </div>

        <hr>
        
        <!-- Earnings and Deductions Section -->
        <div class="earnings-deductions-section">
            <span class="section-title">Earnings & Deductions</span>
            <div class="row">
                <div class="col-md-6">
                    <span class="earnings-title">Earnings</span>
                    <table class="earnings-table">
                        <tbody>
                            <tr>
                                <td>Gross Pay</td>
                                <td class="text-end">₱' . $formatNumber($grossPay) . '</td>
                            </tr>
                            <tr class="total-earnings">
                                <td>Total Earnings</td>
                                <td class="text-end">₱' . $formatNumber($grossPay) . '</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <span class="deductions-title">Deductions</span>
                    <table class="deductions-table">
                        <tbody>
                            <tr>
                                <td>SSS</td>                        
                                <td class="text-end">₱' . $formatNumber($sss) . '</td>
                            </tr>
                            <tr>
                                <td>PhilHealth</td>                        
                                <td class="text-end">₱' . $formatNumber($philhealth) . '</td>
                            </tr>
                            <tr>
                                <td>Pag-IBIG</td>                        
                                <td class="text-end">₱' . $formatNumber($pagibig) . '</td>
                            </tr>
                            <tr>
                                <td>Late Deduction</td>                        
                                <td class="text-end">₱' . $formatNumber($lateDeduction) . '</td>
                            </tr>
                            <tr>
                                <td>Leave Deduction</td>                        
                                <td class="text-end">₱' . $formatNumber($leaveDeduction) . '</td>
                            </tr>
                            <tr class="total-deductions">
                                <td>Total Deductions</td>                        
                                <td class="text-end">₱' . $formatNumber($totalDeductions) . '</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            

            <!-- Net Pay Section -->
            <div class="net-pay-box">
                <div class="net-pay-left">
                    <div class="net-pay-label">Net Pay</div>
                    <div class="net-pay-desc">Total earnings minus total deductions</div>
                </div>
                <div class="net-pay-right">
                    <div class="net-pay-amount">₱' . $formatNumber($netPay) . '</div>
                </div>
            </div>
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
