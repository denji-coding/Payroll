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

    if (isset($_GET['employee_id']) && is_numeric($_GET['employee_id'])) {
        $whereClause = "WHERE pr.employee_id = ?";
        $params[] = (int)$_GET['employee_id'];
    }

    // Debug: Log the query parameters
    error_log("Payslips API - Query params: " . print_r($params, true));
    error_log("Payslips API - Where clause: " . $whereClause);

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
    if (!$stmt) {
        throw new Exception('Failed to prepare SQL statement');
    }
    
    $stmt->execute($params);
    if (!$stmt) {
        throw new Exception('Failed to execute SQL statement');
    }
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Log the query result
    error_log("Payslips API - Query result count: " . count($data));

    // Format names properly for each record
    foreach ($data as &$record) {
        $firstName = formatName($record['first_name'] ?? '');
        $middleInitial = !empty($record['middle_name']) ? formatName(substr($record['middle_name'], 0, 1)) . '.' : '';
        $lastName = formatName($record['last_name'] ?? '');
        
        $record['full_name'] = trim($firstName . ' ' . $middleInitial . ' ' . $lastName);
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
