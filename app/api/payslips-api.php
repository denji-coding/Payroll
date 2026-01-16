<?php
// session_start();
ini_set('display_errors', 0); // Disable error display for API
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Start output buffering to catch any unexpected output
ob_start();

header('Content-Type: application/json');

// Check if database file exists
$databasePath = __DIR__ . '/../core/database.php';
if (!file_exists($databasePath)) {
    error_log("Payslips API - Database file not found: " . $databasePath);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database configuration not found']);
    exit;
}

require_once $databasePath;

// Function to properly format names with Title Case
function formatName($name) {
    if (empty($name)) return '';
    
    // Convert to lowercase first, then capitalize each word
    // This handles multiple words separated by spaces, hyphens, or apostrophes
    return preg_replace_callback('/\b\w+/u', function($matches) {
        return ucfirst(strtolower($matches[0]));
    }, $name);
}

try {
    // Check if Database class exists
    if (!class_exists('Database')) {
        throw new Exception('Database class not found');
    }
    
    $db = new Database();
    if (!$db) {
        throw new Exception('Failed to create Database instance');
    }
    
    $pdo = $db->getConnection();
    
    // Test the connection
    if (!$pdo) {
        throw new Exception('Database connection failed - no PDO object returned');
    }
    
    // Test if we can actually query the database
    $testStmt = $pdo->query('SELECT 1');
    if (!$testStmt) {
        throw new Exception('Database connection test failed');
    }
    
} catch (Exception $e) {
    // Clear any output and return error
    ob_clean();
    error_log("Payslips API - Database connection error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

try {
    // Clear any output that might have been generated
    ob_clean();
    
    // Add a test endpoint for debugging
    if (isset($_GET['test']) && $_GET['test'] === 'true') {
        echo json_encode([
            'status' => 'success',
            'message' => 'Payslips API test endpoint working',
            'timestamp' => date('Y-m-d H:i:s'),
            'params' => $_GET
        ]);
        exit;
    }
    
    $whereClause = "";
    $params = [];
    $whereParams = [];

    if (isset($_GET['employee_id']) && is_numeric($_GET['employee_id'])) {
        $whereClause = "AND pr.employee_id = ?";
        $whereParams[] = (int)$_GET['employee_id'];
    }

    // Debug: Log the query parameters
    error_log("Payslips API - Query params: " . print_r($whereParams, true));
    error_log("Payslips API - Where clause: " . $whereClause);

    // Build UNION query to include employees, managers, and HR
    $sql = "
        SELECT 
            pr.id AS payroll_id,
            pr.employee_id,
            pr.manager_id,
            pr.hr_id,
            e.employee_no,
            'employee' AS user_type,
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
        WHERE pr.employee_id IS NOT NULL " . $whereClause . "
        
        UNION ALL
        
        SELECT 
            pr.id AS payroll_id,
            pr.employee_id,
            pr.manager_id,
            pr.hr_id,
            m.m_employee_id AS employee_no,
            'manager' AS user_type,
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
            m.m_position AS position,
            m.m_base_salary AS base_salary,
            m.m_first_name AS first_name,
            m.m_middle_name AS middle_name,
            m.m_last_name AS last_name
        FROM payroll pr
        LEFT JOIN payslips ps ON pr.id = ps.payroll_id
        LEFT JOIN managers m ON pr.manager_id = m.id
        WHERE pr.manager_id IS NOT NULL
        
        UNION ALL
        
        SELECT 
            pr.id AS payroll_id,
            pr.employee_id,
            pr.manager_id,
            pr.hr_id,
            admin.hr_employee_id AS employee_no,
            'hr' AS user_type,
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
            admin.hr_position AS position,
            admin.hr_base_salary AS base_salary,
            admin.hr_first_name AS first_name,
            admin.hr_middle_name AS middle_name,
            admin.hr_last_name AS last_name
        FROM payroll pr
        LEFT JOIN payslips ps ON pr.id = ps.payroll_id
        LEFT JOIN admins admin ON pr.hr_id = admin.id
        WHERE pr.hr_id IS NOT NULL AND admin.deleted_at IS NULL
        
        ORDER BY last_name ASC, first_name ASC, pay_period_end DESC
    ";

    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare SQL statement');
    }
    
    $stmt->execute($whereParams);
    if (!$stmt) {
        throw new Exception('Failed to execute SQL statement');
    }
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Log the query result
    error_log("Payslips API - Query result count: " . count($data));

    // Format names properly for each record
    foreach ($data as &$record) {
        $firstName = formatName($record['first_name'] ?? '');
        $middleInitial = !empty($record['middle_name']) ? formatName(substr($record['middle_name'], 0, 1)) . '. ' : '';
        $lastName = formatName($record['last_name'] ?? '');
        
        $record['full_name'] = trim($firstName . ' ' . $middleInitial . $lastName);
        
        // Ensure employee_no is set (use employee_no, m_employee_id, or hr_employee_id)
        if (empty($record['employee_no'])) {
            if ($record['user_type'] === 'manager' && !empty($record['manager_id'])) {
                $record['employee_no'] = 'MGR-' . $record['manager_id'];
            } elseif ($record['user_type'] === 'hr' && !empty($record['hr_id'])) {
                $record['employee_no'] = 'HR-' . $record['hr_id'];
            } elseif ($record['user_type'] === 'employee' && !empty($record['employee_id'])) {
                $record['employee_no'] = 'EMP-' . $record['employee_id'];
            }
        }
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;

} catch (Exception $e) {
    // Clear any output and return error
    ob_clean();
    error_log("Payslips API Error: " . $e->getMessage());
    error_log($e->getMessage(), 3, __DIR__ . '/../logs/error.log');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
