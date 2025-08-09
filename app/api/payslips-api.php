<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../core/database.php';

// Function to properly format names with Title Case
function formatName($name) {
    if (empty($name)) return '';
    
    // Convert to lowercase first, then capitalize each word
    // This handles multiple words separated by spaces, hyphens, or apostrophes
    return preg_replace_callback('/\b\w+/u', function($matches) {
        return ucfirst(strtolower($matches[0]));
    }, $name);
}

$db = new Database();
$pdo = $db->getConnection();

try {
    $whereClause = "";
    $params = [];

    if (isset($_GET['employee_id']) && is_numeric($_GET['employee_id'])) {
        $whereClause = "WHERE pr.employee_id = ?";
        $params[] = (int)$_GET['employee_id'];
    }

    $sql = "
        SELECT 
            pr.id AS payroll_id,
            pr.employee_id,
            e.employee_no,
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
            e.position,
            e.base_salary,
            e.first_name,
            e.middle_name,
            e.last_name
        FROM payroll pr
        LEFT JOIN payslips ps ON pr.id = ps.payroll_id
        LEFT JOIN employees e ON pr.employee_id = e.id
        $whereClause
        ORDER BY e.last_name, e.first_name, pr.pay_period_end DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format names properly for each record
    foreach ($data as &$record) {
        $firstName = formatName($record['first_name'] ?? '');
        $middleInitial = !empty($record['middle_name']) ? formatName(substr($record['middle_name'], 0, 1)) . '.' : '';
        $lastName = formatName($record['last_name'] ?? '');
        
        $record['full_name'] = trim($firstName . ' ' . $middleInitial . ' ' . $lastName);
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage(), 3, __DIR__ . '/../logs/error.log');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
    exit;
}
