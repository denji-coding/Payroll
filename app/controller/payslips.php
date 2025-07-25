<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/database.php';

$db = new Database();
$pdo = $db->getConnection();

$data = []; // To hold fetched payroll + payslip + employee data

try {
    $whereClause = '';
    $params = [];

    if (isset($_GET['employee_id']) && is_numeric($_GET['employee_id'])) {
        $whereClause = "WHERE pr.employee_id = ?";
        $params[] = (int)$_GET['employee_id'];
    }

    $sql = "
        SELECT 
            pr.id AS payroll_id,
            pr.employee_id,
            pr.pay_period_start,
            pr.pay_period_end,
            pr.payroll_frequency,
            pr.payroll_duration,
            pr.total_hours,
            pr.sss_deduction,
            pr.pagibig_deduction,
            pr.philhealth_deduction,
            pr.present_days,
            pr.absent_days,
            pr.leave_days,
            pr.total_deductions,
            pr.gross_pay,
            pr.net_pay,
            pr.generated_by,
            pr.generated_at,
            ps.id AS payslip_id,
            ps.ps_pdf_file_path,
            ps.date_generated,
            CONCAT_WS(' ',
              CONCAT(UCASE(LEFT(e.first_name, 1)), LCASE(SUBSTRING(e.first_name, 2))),
              IF(e.middle_name IS NOT NULL AND e.middle_name != '',
                 CONCAT(UCASE(LEFT(e.middle_name, 1)), '.'),
                 ''
              ),
              CONCAT(UCASE(LEFT(e.last_name, 1)), LCASE(SUBSTRING(e.last_name, 2)))
            ) AS full_name
        FROM payroll pr
        LEFT JOIN payslips ps ON pr.id = ps.payroll_id
        LEFT JOIN employees e ON pr.employee_id = e.id
        $whereClause
        ORDER BY e.last_name, e.first_name, pr.pay_period_end DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage(), 3, __DIR__ . '/../logs/error.log');
    $data = [];
}

// Pass $data to your view
require views_path("auth/payslips");
