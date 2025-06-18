<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../app/core/database.php"; // Your Database class



$employee_id = $_SESSION['employee_id'] ?? null;

if (!$employee_id) {
    // If API request, return JSON error, else show message and stop
    if (isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    } else {
        die("Unauthorized access.");
    }
}

$db = new Database();
$conn = $db->getConnection();

// If a single payslip ID is requested, return JSON data (API mode)
if (isset($_GET['id'])) {
    $payroll_id = (int)$_GET['id'];

    $query = "
        SELECT 
            p.*,
            e.employee_no,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.position,
            e.base_salary
        FROM payroll p
        JOIN employees e ON p.employee_id = e.id
        WHERE p.id = :payroll_id AND p.employee_id = :employee_id
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute(['payroll_id' => $payroll_id, 'employee_id' => $employee_id]);
    $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    if ($payroll) {
        echo json_encode(['payroll' => $payroll]);
    } else {
        echo json_encode(['error' => 'Payslip not found or access denied']);
    }
    exit;
}

// Otherwise load all payslips and render HTML page

$query = "
    SELECT 
        payroll.id,
        payroll.pay_period_start,
        payroll.pay_period_end,
        payroll.gross_pay,
        payroll.deductions,
        payroll.net_pay,
        employees.first_name,
        employees.last_name,
        employees.position
    FROM payroll
    JOIN employees ON payroll.employee_id = employees.id
    WHERE payroll.employee_id = :employee_id
    ORDER BY payroll.pay_period_start DESC
";

$stmt = $conn->prepare($query);
$stmt->execute(['employee_id' => $employee_id]);
$payslips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pass the $payslips to your view
require views_path("user/user_mypayslip");
