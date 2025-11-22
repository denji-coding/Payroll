<?php
// File: app/api/generate_leave_form.php
// Sample code to generate leave form PDF and open it directly

require_once __DIR__ . '/../core/database.php';
require __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Sample leave data (you can get this from database or POST request)
$leaveData = [
    'form_number' => '00001',
    'employee_id' => 'EMP-001',
    'employee_name' => 'John Doe',
    'position' => 'Software Developer',
    'date' => date('Y-m-d'),
    'leave_type' => 'VACATION LEAVE', // Options: VACATION LEAVE, SICK LEAVE, EMERGENCY LEAVE, PERSONAL LEAVE, MATERNITY/PATERNITY LEAVE
    'start_date' => '2025-01-15',
    'end_date' => '2025-01-20',
    'employee_signature' => 'John Doe',
    'approval_status' => 'APPROVED', // APPROVED or REJECTED
    'note_comments' => '',
    'received_by' => 'Jane Manager',
    'received_by_title' => 'HR/MANAGER/OWNER'
];

// Get logo path
$logoPath = __DIR__ . '/../../public/assets/image/test_logo_cropted.png';
$logoExists = file_exists($logoPath);

// Convert logo to base64 if it exists
$logoImgTag = '';
if ($logoExists && $logoPath) {
    try {
        $imageData = file_get_contents($logoPath);
        if ($imageData !== false && !empty($imageData)) {
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mimeType = 'image/png';
            if ($ext === 'jpg' || $ext === 'jpeg') {
                $mimeType = 'image/jpeg';
            } elseif ($ext === 'png') {
                $mimeType = 'image/png';
            }
            $base64Data = base64_encode($imageData);
            $logoImgTag = '<img src="data:' . $mimeType . ';base64,' . $base64Data . '" alt="Company Logo" class="logo-image" />';
        }
    } catch (Exception $e) {
        error_log("Logo loading error: " . $e->getMessage());
    }
}

// Format date function
$formatDate = function($dateStr) {
    $date = new DateTime($dateStr);
    return $date->format('F j, Y');
};

// Generate HTML for leave form
$html = generateLeaveFormHTML($leaveData, $logoImgTag, $formatDate);

// Generate PDF
try {
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
    
    // Output PDF directly to browser (opens automatically)
    $dompdf->stream('leave_form_' . $leaveData['employee_id'] . '_' . date('Y-m-d') . '.pdf', [
        'Attachment' => 0 // 0 = inline (opens in browser), 1 = download
    ]);
    
} catch (Exception $e) {
    error_log("PDF generation error: " . $e->getMessage());
    die("Error generating PDF: " . $e->getMessage());
}

function generateLeaveFormHTML($data, $logoImgTag, $formatDate) {
    // Determine which leave type checkbox should be checked
    $leaveTypes = [
        'VACATION LEAVE' => $data['leave_type'] === 'VACATION LEAVE' || $data['leave_type'] === 'Vacation Leave',
        'SICK LEAVE' => $data['leave_type'] === 'SICK LEAVE' || $data['leave_type'] === 'Sick Leave',
        'EMERGENCY LEAVE' => $data['leave_type'] === 'EMERGENCY LEAVE' || $data['leave_type'] === 'Emergency Leave',
        'PERSONAL LEAVE' => $data['leave_type'] === 'PERSONAL LEAVE' || $data['leave_type'] === 'Personal Leave',
        'MATERNITY/PATERNITY LEAVE' => $data['leave_type'] === 'MATERNITY/PATERNITY LEAVE' || $data['leave_type'] === 'Maternity/Paternity Leave'
    ];
    
    // Format dates for display (mm/dd/yyyy)
    $formatDateMMDDYYYY = function($dateStr) {
        $date = new DateTime($dateStr);
        return $date->format('m/d/Y');
    };
    
    $startDateFormatted = $formatDateMMDDYYYY($data['start_date']);
    $endDateFormatted = $formatDateMMDDYYYY($data['end_date']);
    $formDateFormatted = $formatDateMMDDYYYY($data['date']);
    
    // Split dates into parts for display (mm, dd, yyyy)
    $startParts = explode('/', $startDateFormatted);
    $endParts = explode('/', $endDateFormatted);
    $dateParts = explode('/', $formDateFormatted);
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Leave Request Form</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: "DejaVu Sans", Arial, Helvetica, sans-serif; 
                margin: 25px 35px; 
                color: #000; 
                background: #fff; 
                line-height: 1.4;
                font-size: 11px;
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
                width: 70%;
            }
            .header-left-content {
                display: table;
                width: 100%;
            }
            .logo-container {
                display: table-cell;
                vertical-align: top;
                width: 80px;
                padding-right: 15px;
            }
            .logo-image {
                width: 100%;
                height: auto;
                max-width: 80px;
            }
            .company-info {
                display: table-cell;
                vertical-align: top;
            }
            .company-name { 
                font-size: 20px; 
                font-weight: 700; 
                color: #4a5568; 
                margin-bottom: 3px; 
                line-height: 1.2;
            }
            .company-address { 
                font-size: 10px; 
                color: #4a5568; 
                line-height: 1.3;
            }
            
            /* Form Title */
            .form-title {
                font-size: 16px;
                font-weight: 700;
                text-align: center;
                margin: 20px 0 25px 0;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            /* Employee Details Section */
            .employee-details {
                display: table;
                width: 100%;
                margin-bottom: 15px;
            }
            .employee-left {
                display: table-cell;
                width: 60%;
                vertical-align: top;
            }
            .employee-right {
                display: table-cell;
                width: 40%;
                vertical-align: top;
                text-align: right;
            }
            .detail-row {
                margin: 10px 0;
                display: table;
                width: 100%;
            }
            .detail-label {
                display: table-cell;
                width: 45%;
                font-weight: 600;
                font-size: 11px;
                vertical-align: middle;
                padding-right: 8px;
                white-space: nowrap;
            }
            .detail-line {
                display: table-cell;
                width: 55%;
                border-bottom: 1px solid #000;
                padding-left: 8px;
                font-size: 11px;
                min-height: 20px;
                vertical-align: bottom;
                padding-bottom: 2px;
            }
            .form-number {
                color: #dc2626;
                font-weight: 700;
                font-size: 12px;
            }
            .employee-right {
                padding-left: 20px;
            }
            .employee-right .detail-label {
                width: 30%;
            }
            .employee-right .detail-line {
                width: 70%;
            }
            
            /* Reason Box */
            .reason-box {
                border: 1px solid #000;
                padding: 15px;
                margin: 20px 0;
            }
            .reason-title {
                font-weight: 700;
                font-size: 11px;
                margin-bottom: 12px;
            }
            .checkbox-columns {
                display: table;
                width: 100%;
            }
            .checkbox-col {
                display: table-cell;
                width: 50%;
                vertical-align: top;
            }
            .checkbox-item {
                margin: 8px 0;
                font-size: 11px;
                line-height: 1.5;
            }
            .checkbox {
                display: inline-block;
                width: 12px;
                height: 12px;
                border: 1px solid #000;
                margin-right: 8px;
                vertical-align: middle;
                position: relative;
                background: #fff;
            }
            .checkbox.checked::after {
                content: "✓";
                position: absolute;
                left: 0px;
                top: -3px;
                font-size: 14px;
                font-weight: bold;
                color: #000;
            }
            
            /* Date Requested Section */
            .date-requested {
                margin: 15px 0;
            }
            .date-label {
                font-weight: 600;
                font-size: 11px;
                margin-bottom: 8px;
            }
            .date-line {
                margin: 8px 0;
                font-size: 11px;
                line-height: 1.8;
            }
            .date-blank {
                display: inline-block;
                width: 28px;
                border-bottom: 1px solid #000;
                margin: 0 4px;
                text-align: center;
                padding-bottom: 2px;
                font-size: 11px;
                min-height: 16px;
            }
            .date-separator {
                display: inline-block;
                margin: 0 3px;
                font-size: 11px;
            }
            .date-format {
                font-size: 9px;
                color: #666;
                margin-left: 10px;
                display: block;
                margin-top: 2px;
            }
            
            /* Signature Table */
            .signature-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 25px;
                border: 1px solid #000;
            }
            .signature-table tr {
                border: 1px solid #000;
            }
            .signature-table td {
                border: 1px solid #000;
                padding: 15px 12px;
                vertical-align: top;
                width: 50%;
            }
            .signature-label {
                font-weight: 600;
                font-size: 11px;
                margin-bottom: 12px;
            }
            .signature-line {
                border-bottom: 1px solid #000;
                margin-top: 55px;
                padding-bottom: 4px;
                min-height: 20px;
                font-size: 11px;
                width: 100%;
                display: block;
            }
            .checkbox-inline {
                display: inline-block;
                margin-right: 30px;
                margin-top: 12px;
                font-size: 11px;
            }
            .comments-box {
                min-height: 110px;
                border: 1px solid #000;
                padding: 10px;
                margin-top: 10px;
                font-size: 11px;
                width: 100%;
                box-sizing: border-box;
            }
            .received-by-title {
                text-align: center;
                font-size: 10px;
                margin-top: 8px;
                color: #666;
            }
            .approval-checkboxes {
                margin-top: 12px;
            }
        </style>
    </head>
    <body>
        <!-- Header -->
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
        </div>
        
        <div class="form-title">LEAVE REQUEST FORM</div>
        
        <!-- Employee Details -->
        <div class="employee-details">
            <div class="employee-left">
                <div class="detail-row">
                    <div class="detail-label">EMPLOYEE ID :</div>
                    <div class="detail-line">' . htmlspecialchars($data['employee_id']) . '</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">EMPLOYEE NAME:</div>
                    <div class="detail-line">' . htmlspecialchars($data['employee_name']) . '</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">POSITION :</div>
                    <div class="detail-line">' . htmlspecialchars($data['position']) . '</div>
                </div>
            </div>
            <div class="employee-right">
                <div class="detail-row">
                    <div class="detail-label" style="width: 30%;">NO. :</div>
                    <div class="detail-line" style="width: 70%;">
                        <span class="form-number">' . htmlspecialchars($data['form_number']) . '</span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label" style="width: 30%;">DATE:</div>
                    <div class="detail-line" style="width: 70%;">' . htmlspecialchars($formDateFormatted) . '</div>
                </div>
            </div>
        </div>
        
        <!-- Reason for Request Leave -->
        <div class="reason-box">
            <div class="reason-title">REASON FOR REQUEST LEAVE: (Please check appropriate box)</div>
            <div class="checkbox-columns">
                <div class="checkbox-col">
                    <div class="checkbox-item">
                        <span class="checkbox' . ($leaveTypes['VACATION LEAVE'] ? ' checked' : '') . '"></span> VACATION LEAVE
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox' . ($leaveTypes['SICK LEAVE'] ? ' checked' : '') . '"></span> SICK LEAVE
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox' . ($leaveTypes['EMERGENCY LEAVE'] ? ' checked' : '') . '"></span> EMERGENCY LEAVE
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox' . ($leaveTypes['PERSONAL LEAVE'] ? ' checked' : '') . '"></span> PERSONAL LEAVE
                    </div>
                </div>
                <div class="checkbox-col">
                    <div class="checkbox-item">
                        <span class="checkbox' . ($leaveTypes['MATERNITY/PATERNITY LEAVE'] ? ' checked' : '') . '"></span> MATERNITY/PATERNITY LEAVE
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Date Requested -->
        <div class="date-requested">
            <div class="date-label">DATE REQUESTED:</div>
            <div class="date-line">
                from 
                <span class="date-blank">' . str_pad($startParts[0], 2, '0', STR_PAD_LEFT) . '</span>
                <span class="date-separator">/</span>
                <span class="date-blank">' . str_pad($startParts[1], 2, '0', STR_PAD_LEFT) . '</span>
                <span class="date-separator">/</span>
                <span class="date-blank">' . $startParts[2] . '</span>
            </div>
            <div style="font-size: 9px; color: #666; margin-left: 45px; margin-top: 2px;">mm/dd/yyyy</div>
            <div class="date-line" style="margin-top: 10px;">
                to: 
                <span class="date-blank">' . str_pad($endParts[0], 2, '0', STR_PAD_LEFT) . '</span>
                <span class="date-separator">/</span>
                <span class="date-blank">' . str_pad($endParts[1], 2, '0', STR_PAD_LEFT) . '</span>
                <span class="date-separator">/</span>
                <span class="date-blank">' . $endParts[2] . '</span>
            </div>
            <div style="font-size: 9px; color: #666; margin-left: 45px; margin-top: 2px;">mm/dd/yyyy</div>
        </div>
        
        <!-- Signature and Approval Table -->
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-label">EMPLOYEE\'S SIGNITURE:</div>
                    <div class="signature-line">' . htmlspecialchars($data['employee_signature']) . '</div>
                </td>
                <td>
                    <div class="signature-label">HR/MANAGER/OWNER APPROVAL:</div>
                    <div class="approval-checkboxes">
                        <span class="checkbox-inline">
                            <span class="checkbox' . ($data['approval_status'] === 'APPROVED' ? ' checked' : '') . '"></span> APPROVED
                        </span>
                        <span class="checkbox-inline">
                            <span class="checkbox' . ($data['approval_status'] === 'REJECTED' ? ' checked' : '') . '"></span> REJECTED
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="signature-label">NOTE/COMMENTS:</div>
                    <div class="comments-box">' . nl2br(htmlspecialchars($data['note_comments'])) . '</div>
                </td>
                <td>
                    <div class="signature-label">RECEIVED BY:</div>
                    <div class="signature-line">' . htmlspecialchars($data['received_by']) . '</div>
                    <div class="received-by-title">' . htmlspecialchars($data['received_by_title']) . '</div>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    
    return $html;
}
?>

