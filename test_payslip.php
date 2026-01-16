<?php

require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// Sample data
$fullName = "Giovan Kier V. Cardenio";
$employeeNo = "EMP-517859";
$position = "Staff";
$baseSalary = 600.00;
$startDate = "Jul 1, 2025";
$endDate = "Jul 18, 2025";
$totalHours = 16;
$presentDays = 2;
$absentDays = 6;
$leaveDays = 10;
$grossPay = 1200.00;
$sss = 60.00;
$philhealth = 60.00;
$pagibig = 60.00;
$lateDeduction = 0.00;
$leaveDeduction = 6000.00;
$totalDeductions = 333.33;
$netPay = 686.67;

// Format numbers with commas
$formatNumber = function($num) {
    return number_format($num, 2);
};

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payslip Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            color: #333; 
            background: #fff; 
            line-height: 1.4;
        }
        
        /* Header Section */
        .header-section { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            margin-bottom: 20px; 
        }
        .company-name { 
            font-size: 24px; 
            font-weight: 700; 
            color: #2d5016; 
            margin-bottom: 5px; 
        }
        .company-address { 
            font-size: 14px; 
            color: #478547; 
        }
        .pay-period-label { 
            font-weight: 700; 
            font-size: 12px; 
            margin-bottom: 3px; 
            color: #333;
        }
        .pay-period-date { 
            font-size: 12px; 
            color: #333;
        }
        
        /* Horizontal Line */
        hr { 
            border: none; 
            border-top: 1px solid #dee2e6; 
            margin: 15px 0; 
        }
        
        /* Two Column Layout */
        .row { 
            display: flex; 
            flex-wrap: wrap; 
            margin: 0 -10px; 
        }
        .col-md-6 { 
            flex: 0 0 50%; 
            max-width: 50%; 
            padding: 0 10px; 
        }
        
        /* Section Titles */
        .section-title { 
            font-weight: 700; 
            font-size: 13px; 
            margin-bottom: 10px; 
            display: block; 
            color: #333;
        }
        
        /* Info Text */
        .info-text { 
            font-size: 13px; 
            margin: 0; 
        }
        .info-text p { 
            margin: 5px 0; 
        }
        .info-text strong { 
            font-weight: 700; 
            color: #333;
        }
        
        /* Earnings and Deductions Section */
        .earnings-deductions-section {
            margin-top: 20px;
        }
        .earnings-title, .deductions-title { 
            font-size: 13px; 
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
            padding: 8px 5px; 
            border-bottom: 1px solid #dee2e6; 
            font-size: 13px;
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
            background-color: #f8fbf8;
        }
        .deductions-table td {
            color: #dc3545;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .net-pay-label { 
            font-weight: 700; 
            font-size: 16px; 
            color: #2d5016;
        }
        .net-pay-desc { 
            font-size: 13px; 
            color: #333; 
            margin-top: 5px; 
            font-weight: normal;
        }
        .net-pay-amount { 
            font-weight: 700; 
            font-size: 28px; 
            color: #2d5016; 
            text-align: right; 
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div>
            <div class="company-name">Migrants Venture Corporation</div>
            <div class="company-address">Lapu-Lapu St. Tagum City, Davao Del Norte</div>
        </div>
        <div style="text-align: right;">
            <div class="pay-period-label">PAY PERIOD</div>
            <div class="pay-period-date">' . htmlspecialchars($startDate) . ' - ' . htmlspecialchars($endDate) . '</div>
        </div>
    </div>

    <hr>

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

        <hr style="border-top: 1px solid #28a745;">

        <!-- Net Pay Section -->
        <div class="net-pay-box">
            <div>
                <div class="net-pay-label">Net Pay</div>
                <div class="net-pay-desc">Total earnings minus total deductions</div>
            </div>
            <div class="net-pay-amount">₱' . $formatNumber($netPay) . '</div>
        </div>
    </div>
</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the PDF
$dompdf->stream("payslip_test.pdf", ["Attachment" => false]);

?>

