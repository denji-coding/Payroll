<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to properly format names with Title Case
function formatName($name) {
    if (empty($name)) return '';
    
    // Convert to lowercase first, then capitalize each word
    // This handles multiple words separated by spaces, hyphens, or apostrophes
    return preg_replace_callback('/\b\w+/u', function($matches) {
        return ucfirst(strtolower($matches[0]));
    }, $name);
}

// Check for either admin or manager authentication
$managerId = $_SESSION['manager_id'] ?? null;
$adminId = $_SESSION['SESSION_USER_ID'] ?? null;

if (!$managerId && !$adminId) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Please login as admin or manager']);
    exit;
}

// For database constraints, we need a manager ID. If admin is processing, 
// we'll use a default manager or handle this differently
if ($managerId) {
    $userId = $managerId;
} else {
    // Admin is processing - we need to handle this case
    // Option 1: Use the first available manager as a placeholder
    // Option 2: Create a special admin-manager record
    // For now, let's use the first manager in the system
    $pdo = (new Database())->getConnection();
    $managerStmt = $pdo->query("SELECT id FROM managers LIMIT 1");
    $managerRow = $managerStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$managerRow) {
        echo json_encode(['status' => 'error', 'message' => 'No managers found in system. Please create a manager first.']);
        exit;
    }
    
    $userId = $managerRow['id'];
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['payrolls'], $input['start_date'], $input['end_date'], $input['payroll_type'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// Use existing PDO connection if it was created above, otherwise create new one
if (!isset($pdo)) {
    $pdo = (new Database())->getConnection();
}

try {
    $pdo->beginTransaction();

    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) FROM payroll 
        WHERE employee_id = :employee_id 
        AND pay_period_start = :start 
        AND pay_period_end = :end
    ");

    $insertStmt = $pdo->prepare("
        INSERT INTO payroll (
            employee_id, pay_period_start, pay_period_end, payroll_frequency,
            payroll_duration, total_hours, sss_deduction, pagibig_deduction,
            philhealth_deduction, present_days, absent_days, leave_days,
            total_deductions, gross_pay, net_pay, generated_by
        ) VALUES (
            :employee_id, :start, :end, :type, :duration,
            :hours, :sss, :pagibig, :philhealth,
            :present, :absent, :leave, :total_deductions, :gross, :net, :manager_id
        )
    ");

    $payslipInsertStmt = $pdo->prepare("
        INSERT INTO payslips (
            payroll_id, employee_id, generated_by, ps_pdf_file_path
        ) VALUES (
            :payroll_id, :employee_id, :generated_by, :file_path
        )
    ");

    $processedEmployees = [];

    $empStmt = $pdo->prepare("SELECT id, first_name, middle_name, last_name, employee_no, position, base_salary, email FROM employees WHERE employee_no = ?");

    foreach ($input['payrolls'] as $p) {
        $empStmt->execute([$p['employee_no']]);
        $empRow = $empStmt->fetch(PDO::FETCH_ASSOC);
        if (!$empRow) continue;

        $employee_id = $empRow['id'];

        $checkStmt->execute([
            ':employee_id' => $employee_id,
            ':start' => $input['start_date'],
            ':end' => $input['end_date']
        ]);
        if ($checkStmt->fetchColumn() > 0) continue;

        // BACKUP CALCULATION: If total_deductions is 0 or missing, calculate it manually
        $calculatedTotal = floatval($p['sss_deduction'] ?? 0) + floatval($p['pagibig_deduction'] ?? 0) + floatval($p['philhealth_deduction'] ?? 0);
        $receivedTotal = floatval($p['total_deductions'] ?? 0);
        
        // Use calculated total if received total is 0
        $finalTotal = ($receivedTotal > 0) ? $receivedTotal : $calculatedTotal;
        
        $executeData = [
            ':employee_id' => $employee_id,
            ':start' => $input['start_date'],
            ':end' => $input['end_date'],
            ':type' => $input['payroll_type'],
            ':duration' => $p['present_days'] + $p['absent_days'] + $p['leave_days'],
            ':hours' => $p['total_hours'],
            ':sss' => floatval($p['sss_deduction'] ?? 0),
            ':pagibig' => floatval($p['pagibig_deduction'] ?? 0),
            ':philhealth' => floatval($p['philhealth_deduction'] ?? 0),
            ':present' => $p['present_days'],
            ':absent' => $p['absent_days'],
            ':leave' => $p['leave_days'],
            ':total_deductions' => $finalTotal,
            ':gross' => floatval($p['gross']),
            ':net' => floatval($p['net']),
            ':manager_id' => $userId
        ];
        

        
        $insertStmt->execute($executeData);

        $payrollId = $pdo->lastInsertId();

        // Generate Payslip PDF
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $pdf = new Dompdf($options);

        $html = '
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { display: flex; justify-content: space-between; }
        .section { margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; }
        .subtitle { color: #478547; }
        .payslip-label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px; vertical-align: top; }
        .border-b { border-bottom: 1px solid #ccc; }
        .text-right { text-align: right; }
        .highlight { background-color: #f2f8f2; padding: 10px; border-radius: 6px; }
        .net { font-size: 18px; font-weight: bold; color: #478547; }
        .row-flex { display: flex; justify-content: space-between; gap: 20px; }
        .col-half { width: 49%; }
        ul { list-style: none; padding: 0; margin: 0; }
    </style>

    <div class="section header">
        <div>
            <div class="title">Migrants Venture Corporation</div>
            <div class="subtitle">Lapu-Lapu St. Tagum City, Davao Del Norte</div>
        </div>
        <div style="text-align: right;">
            <div class="payslip-label">PAY PERIOD</div>
            <div>' . htmlspecialchars($input['start_date']) . ' - ' . htmlspecialchars($input['end_date']) . '</div>
        </div>
    </div>

    <!-- Employee & Payroll Details (One Row with Two Columns using Table) -->
    <div class="section">
        <table style="width: 100%;">
            <tr>
                <!-- Left Column: Employee Info -->
                <td style="width: 50%; vertical-align: top;">
                    
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li><strong>Name:</strong> ' . formatName($empRow['first_name']) . ' ' . 
                        ($empRow['middle_name'] ? formatName(substr($empRow['middle_name'], 0, 1)) . '. ' : '') . 
                        formatName($empRow['last_name']) . '</li>

                        <li><strong>ID:</strong> ' . $empRow['employee_no'] . '</li>
                        <li><strong>Position:</strong> ' . $empRow['position'] . '</li>
                        <li><strong>Basic Salary:</strong> ₱' . $empRow['base_salary'] . ' / day</li>
                    </ul>
                </td>

                <!-- Right Column: Payroll Info -->
                <td style="width: 50%; vertical-align: top;">
                    
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li><strong>Total Hours:</strong> ' . $p['total_hours'] . '</li>
                        <li><strong>Present Days:</strong> ' . $p['present_days'] . '</li>
                        <li><strong>Absent:</strong> ' . $p['absent_days'] . '</li>
                        <li><strong>Leave:</strong> ' . $p['leave_days'] . '</li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>

    <div class="border-b border-gray-300 my-2"></div>



    <!-- Earnings and Deductions -->
    <div class="section">
        <table>
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr><td colspan="2"><strong>Earnings</strong></td></tr>
                        <tr class="border-b"><td>Basic Pay</td><td class="text-right">₱' . number_format($p['gross'], 2) . '</td></tr>
                        <tr class="border-b"><td class="payslip-label">Total Earnings</td><td class="text-right">₱' . number_format($p['gross'], 2) . '</td></tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr><td colspan="2"><strong>Deductions</strong></td></tr>
                        <tr class="border-b"><td>SSS</td><td class="text-right">₱' . number_format($p['sss_deduction'], 2) . '</td></tr>
                        <tr class="border-b"><td>PhilHealth</td><td class="text-right">₱' . number_format($p['philhealth_deduction'], 2) . '</td></tr>
                        <tr class="border-b"><td>Pag-IBIG</td><td class="text-right">₱' . number_format($p['pagibig_deduction'], 2) . '</td></tr>
                        <tr class="border-b"><td>Leave Deduction</td><td class="text-right">₱' . number_format($p['leave_deduction'] ?? 0, 2) . '</td></tr>
                        <tr class="border-b"><td class="payslip-label">Total Deductions</td><td class="text-right">₱' . number_format($p['total_deductions'], 2) . '</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Net Pay -->
    <div class="highlight section">
        <table>
            <tr><td><strong>Net Pay</strong></td><td class="text-right net">₱' . number_format($p['net'], 2) . '</td></tr>
        </table>
        <p style="font-size: 10px;">Total earnings minus total deductions</p>
    </div>
';


        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $folderPath = __DIR__ . '/../../public/payslips/';
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        $filename = "payslip_{$empRow['employee_no']}_{$input['start_date']}_{$input['end_date']}.pdf";
        $pdfPath = $folderPath . $filename;
        file_put_contents($pdfPath, $pdf->output());

        $payslipInsertStmt->execute([
            ':payroll_id' => $payrollId,
            ':employee_id' => $employee_id,
            ':generated_by' => $userId,
            ':file_path' => 'payslips/' . $filename
        ]);

        // Send Email with Attachment
        // $mail = new PHPMailer(true);
        // try {
        //     $firstName = ucfirst(strtolower($empRow['first_name']));

        //     $mail->isSMTP();
        //     $mail->SMTPAuth   = true;
        //     $mail->Host       = 'mail.smtp2go.com';
        //     $mail->Username   = 'nabesis.roy@dnsc.edu.ph';
        //     $mail->Password   = 'pGdu8SqFpeLnVp2Y';
        //     $mail->SMTPSecure = 'tls';
        //     $mail->Port       = 587;

        //     $mail->setFrom('noreply@migrantsventurecorp.ip-ddns.com', 'Migrants Venture Corporation');
        //     $mail->addReplyTo('support@migrantsventurecorp.ip-ddns.com', 'Support Team');

        //     $mail->addAddress($empRow['email'], $empRow['first_name'] . ' ' . $empRow['last_name']);
        //     $mail->Subject = 'Your Payslip [' . $input['start_date'] . ' - ' . $input['end_date'] . ']';

        //     $mail->Body = <<<EOD
        //         Dear {$firstName},

        //             Attached is your payslip for the period covering {$input['start_date']} to {$input['end_date']}.

        //             You may also view this payslip anytime by logging into the employee portal. If you have any questions or notice any discrepancies, feel free to reach out.

        //             Best regards,  
        //             Migrants Venture Corporation  
        //             HR Department  
        //             support@migrantsventurecorp.ip-ddns.com
        //         EOD;

        //     $mail->addAttachment($pdfPath, $filename);
        //     $mail->send();
        // } catch (Exception $e) {
        //     error_log("Email send failed for {$empRow['email']}: " . $mail->ErrorInfo);
        // }

                 $processedEmployees[] = $empRow['employee_no'];
    }

    $pdo->commit();
    echo json_encode([
        'status' => 'success',
        'processed_employees' => $processedEmployees
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'PDF generation or Email error: ' . $e->getMessage()]);
}
