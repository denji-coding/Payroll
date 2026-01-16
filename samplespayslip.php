<?php
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Check if PDF download is requested
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new Dompdf($options);
    
    $html = getPayslipHTML();
    
    $dompdf->loadHtml($html);
    
    // Set paper size to 5.5" x 8.5" (Statement/Half Letter)
    $dompdf->setPaper([0, 0, 396, 612], 'portrait'); // 5.5" x 8.5" in points (72 points per inch)
    
    $dompdf->render();
    
    $dompdf->stream('payslip.pdf', ['Attachment' => true]);
    exit;
}

function getPayslipHTML() {
    return '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 11px;
      padding: 30px 40px;
    }
    .payslip {
      width: 100%;
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
      border: 1px solid #ccc;
    }
    .col-label {
      width: 55%;
    }
    .col-amount {
      width: 45%;
    }
    .total-row td {
      border-top: none;
      border-left: none;
      border-right: none;
      border-bottom: 1px solid #ccc;
    }
    .total-row .col-label {
      text-align: right;
      padding-right: 10px;
      font-weight: normal;
    }
    .spacer-row td {
      border: none;
      height: 15px;
    }
    .net-row td {
      border-top: none;
      border-left: none;
      border-right: none;
      border-bottom: 1px solid #ccc;
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
    <!-- Header Section -->
    <table class="header-table">
      <tr>
        <td class="header-left">
          <div>Employee Name:</div>
          <div>Employee ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div>
          <div>Position&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div>
        </td>
        <td class="header-right">
          <div>Pay Period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div>
          <div>Working Days:</div>
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
        <td class="col-amount"></td>
      </tr>
      <tr>
        <td class="col-label">Gross pay</td>
        <td class="col-amount"></td>
      </tr>
      <tr>
        <td class="col-label">LWP</td>
        <td class="col-amount"></td>
      </tr>
      <tr class="spacer-row"><td></td><td></td></tr>
      <tr class="total-row">
        <td class="col-label">Total Earnings</td>
        <td class="col-amount"></td>
      </tr>
    </table>

    <!-- Spacer -->
    <div style="height: 10px;"></div>

    <!-- Deductions Table -->
    <table class="main-table">
      <tr class="deductions-header">
        <td class="col-label">Deductions</td>
        <td class="col-amount"></td>
      </tr>
      <tr>
        <td class="col-label">SSS</td>
        <td class="col-amount"></td>
      </tr>
      <tr>
        <td class="col-label">Pag-Ibig</td>
        <td class="col-amount"></td>
      </tr>
      <tr>
        <td class="col-label">PhilHealth</td>
        <td class="col-amount"></td>
      </tr>
      <tr>
        <td class="col-label">Late Deduction</td>
        <td class="col-amount"></td>
      </tr>
      <tr>
        <td class="col-label">Undertime Deduction</td>
        <td class="col-amount"></td>
      </tr>
      <tr class="spacer-row"><td></td><td></td></tr>
      <tr class="total-row">
        <td class="col-label">Total Deductions</td>
        <td class="col-amount"></td>
      </tr>
      <tr class="spacer-row"><td></td><td></td></tr>
      <tr class="net-row">
        <td class="col-label">Net Pay</td>
        <td class="col-amount"></td>
      </tr>
    </table>
  </div>
</body>
</html>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payslip</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 14px;
      padding: 40px;
      background: #f5f5f5;
    }
    .btn-container {
      max-width: 600px;
      margin: 0 auto 20px;
      text-align: right;
    }
    .btn-pdf {
      background: #4CAF50;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 14px;
      cursor: pointer;
      border-radius: 5px;
      text-decoration: none;
      display: inline-block;
    }
    .btn-pdf:hover {
      background: #45a049;
    }
    .payslip {
      max-width: 600px;
      margin: 0 auto;
      background: white;
      padding: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }
    .header-left, .header-right {
      line-height: 1.8;
    }
    .header-left span, .header-right span {
      display: inline-block;
      min-width: 100px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .earnings-header {
      background: #4CAF50;
      color: white;
    }
    .earnings-header td {
      padding: 8px 10px;
      font-weight: bold;
    }
    .deductions-header {
      background: #c0392b;
      color: white;
    }
    .deductions-header td {
      padding: 8px 10px;
      font-weight: bold;
    }
    table td {
      padding: 6px 10px;
      border: 1px solid #ddd;
    }
    table td:first-child {
      width: 60%;
    }
    table td:last-child {
      width: 40%;
    }
    .total-row td {
      font-weight: bold;
      background: #f9f9f9;
    }
    .total-row td:first-child {
      text-align: right;
      padding-right: 20px;
    }
    .net-pay-row td {
      font-weight: bold;
      background: #e8f5e9;
    }
    .net-pay-row td:first-child {
      text-align: right;
      padding-right: 20px;
    }
    .spacer {
      height: 10px;
      border: none;
    }
    .spacer td {
      border: none;
      padding: 5px;
    }
  </style>
</head>
<body>
  <div class="btn-container">
    <a href="?download=pdf" class="btn-pdf">Download PDF</a>
  </div>
  <div class="payslip" id="payslip">
    <!-- Header Section -->
    <div class="header">
      <div class="header-left">
        <div>Employee Name<span>:</span></div>
        <div>Employee ID<span>:</span></div>
        <div>Position<span>:</span></div>
      </div>
      <div class="header-right">
        <div>Pay Period<span>:</span></div>
        <div>Working Days:</div>
      </div>
    </div>

    <!-- Earnings Table -->
    <table>
      <tr class="earnings-header">
        <td>Earnings</td>
        <td>Amount</td>
      </tr>
      <tr>
        <td>Basic pay</td>
        <td></td>
      </tr>
      <tr>
        <td>Gross pay</td>
        <td></td>
      </tr>
      <tr>
        <td>LWP</td>
        <td></td>
      </tr>
      <tr class="spacer"><td></td><td></td></tr>
      <tr class="total-row">
        <td>Total Earnings</td>
        <td></td>
      </tr>
    </table>

    <!-- Deductions Table -->
    <table>
      <tr class="deductions-header">
        <td>Deductions</td>
        <td></td>
      </tr>
      <tr>
        <td>SSS</td>
        <td></td>
      </tr>
      <tr>
        <td>Pag-Ibig</td>
        <td></td>
      </tr>
      <tr>
        <td>PhilHealth</td>
        <td></td>
      </tr>
      <tr>
        <td>Late Deduction</td>
        <td></td>
      </tr>
      <tr>
        <td>Undertime Deduction</td>
        <td></td>
      </tr>
      <tr class="spacer"><td></td><td></td></tr>
      <tr class="total-row">
        <td>Total Deductions</td>
        <td></td>
      </tr>
      <tr class="spacer"><td></td><td></td></tr>
      <tr class="net-pay-row">
        <td>Net Pay</td>
        <td></td>
      </tr>
    </table>
  </div>
</body>
</html>

