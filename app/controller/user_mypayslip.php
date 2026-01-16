<?php
require_once '../app/core/session_helper.php';

// Check if employee is logged in
requireEmployeeAuth();

// Log user activity
logUserActivity('Access employee payslips page');

ini_set('display_errors', 0); // Disable error display for API
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Start output buffering to catch any unexpected output
ob_start();

require_once "../app/core/database.php"; // Your Database class

// Get user ID - support employees, managers, and HR
$employee_id = null;
$manager_id = null;
$hr_id = null;
$user_type = 'employee';

// Check if user is HR/Admin
if (isset($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['SESSION_USER_ID'])) {
    $hr_id = (int)$_SESSION['SESSION_USER_ID'];
    $user_type = 'hr';
}
// Check if user is Manager
elseif (isset($_SESSION['manager_id']) && !empty($_SESSION['manager_id'])) {
    $manager_id = (int)$_SESSION['manager_id'];
    $user_type = 'manager';
}
// Regular Employee
elseif (isset($_SESSION['employee_id']) || isset($_SESSION['employee_no'])) {
    $employee_id = $_SESSION['employee_id'] ?? $_SESSION['employee_no'];
    if ($employee_id) {
        $employee_id = (int)$employee_id;
    }
    $user_type = 'employee';
}

// For testing purposes, if no session, use a default employee
if (!$employee_id && !$manager_id && !$hr_id) {
    // Check if this is a test request
    if (isset($_GET['test']) && $_GET['test'] === 'true') {
        $employee_id = 115; // Use existing employee ID from database
        $user_type = 'employee';
    }
}

// Debug: Log the user ID for troubleshooting
if (isset($_GET['id'])) {
    error_log("API Request - User Type: " . $user_type);
    error_log("Employee ID: " . ($employee_id ?? 'null'));
    error_log("Manager ID: " . ($manager_id ?? 'null'));
    error_log("HR ID: " . ($hr_id ?? 'null'));
    error_log("Session data: " . print_r($_SESSION, true));
}

if (!$employee_id && !$manager_id && !$hr_id) {
    // If API request, return JSON error, else show message and stop
    if (isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    } else {
        die("Unauthorized access.");
    }
}

try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    if (isset($_GET['id'])) {
        // API mode - return JSON error
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    } else {
        // Page mode - show error
        ini_set('display_errors', 1);
        die("Database connection failed: " . $e->getMessage());
    }
}

// If a single payslip ID is requested, return JSON data (API mode)
if (isset($_GET['id'])) {
    // Add a test endpoint for debugging
    if ($_GET['id'] === 'test') {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'employee_id' => $employee_id,
            'session_data' => $_SESSION,
            'test' => 'success'
        ]);
        exit;
    }
    try {
        // Clear any output that might have been generated
        ob_clean();
        
        $payroll_id = (int)$_GET['id'];

        // Build query based on user type
        if ($hr_id) {
            // HR/Admin payslip
            $query = "
                SELECT 
                    p.*,
                    admin.hr_employee_id AS employee_no,
                    admin.hr_first_name AS first_name,
                    admin.hr_middle_name AS middle_name,
                    admin.hr_last_name AS last_name,
                    admin.hr_position AS position,
                    admin.hr_base_salary AS base_salary,
                    ps.ps_pdf_file_path
                FROM payroll p
                JOIN admins admin ON p.hr_id = admin.id
                LEFT JOIN payslips ps ON p.id = ps.payroll_id
                WHERE p.id = :payroll_id AND p.hr_id = :hr_id AND admin.deleted_at IS NULL
                LIMIT 1
            ";
            $stmt = $conn->prepare($query);
            $stmt->execute(['payroll_id' => $payroll_id, 'hr_id' => $hr_id]);
        } elseif ($manager_id) {
            // Manager payslip
            $query = "
                SELECT 
                    p.*,
                    m.m_employee_id AS employee_no,
                    m.m_first_name AS first_name,
                    m.m_middle_name AS middle_name,
                    m.m_last_name AS last_name,
                    m.m_position AS position,
                    m.m_base_salary AS base_salary,
                    ps.ps_pdf_file_path
                FROM payroll p
                JOIN managers m ON p.manager_id = m.id
                LEFT JOIN payslips ps ON p.id = ps.payroll_id
                WHERE p.id = :payroll_id AND p.manager_id = :manager_id AND m.deleted_at IS NULL
                LIMIT 1
            ";
            $stmt = $conn->prepare($query);
            $stmt->execute(['payroll_id' => $payroll_id, 'manager_id' => $manager_id]);
        } else {
            // Employee payslip
            // First, let's try to find the employee by employee_no if employee_id is not working
            $employee_query = "SELECT id FROM employees WHERE employee_no = ? OR id = ? LIMIT 1";
            $employee_stmt = $conn->prepare($employee_query);
            $employee_stmt->execute([$employee_id, $employee_id]);
            $employee = $employee_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$employee) {
                // If we can't find the employee, return error
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Employee not found']);
                exit;
            }
            
            $actual_employee_id = $employee['id'];
            
            $query = "
                SELECT 
                    p.*,
                    e.employee_no,
                    e.first_name,
                    e.middle_name,
                    e.last_name,
                    e.position,
                    e.base_salary,
                    ps.ps_pdf_file_path
                FROM payroll p
                JOIN employees e ON p.employee_id = e.id
                LEFT JOIN payslips ps ON p.id = ps.payroll_id
                WHERE p.id = :payroll_id AND p.employee_id = :employee_id
                LIMIT 1
            ";
            $stmt = $conn->prepare($query);
            $stmt->execute(['payroll_id' => $payroll_id, 'employee_id' => $actual_employee_id]);
        }
        
        $payroll = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug: Log the query parameters
        error_log("Query params - payroll_id: $payroll_id, user_type: $user_type");
        if ($hr_id) {
            error_log("HR ID: $hr_id");
        } elseif ($manager_id) {
            error_log("Manager ID: $manager_id");
        } else {
            error_log("Employee ID: $employee_id");
        }
        error_log("Query result: " . ($payroll ? 'found' : 'not found'));
        if ($payroll) {
            error_log("Payroll data: " . print_r($payroll, true));
        } else {
            error_log("No payroll data found for payroll_id: $payroll_id");
        }

        header('Content-Type: application/json');
        if ($payroll) {
            echo json_encode(['payroll' => $payroll]);
        } else {
            echo json_encode(['error' => 'Payslip not found or access denied']);
        }
    } catch (Exception $e) {
        // Clear any output and return error
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Otherwise load all payslips and render HTML page
try {
    // Build query based on user type
    if ($hr_id) {
        // HR/Admin payslips
        $query = "
            SELECT 
                payroll.id,
                payroll.pay_period_start,
                payroll.pay_period_end,
                payroll.gross_pay,
                payroll.total_deductions,
                payroll.net_pay,
                admin.hr_first_name AS first_name,
                admin.hr_last_name AS last_name,
                admin.hr_position AS position
            FROM payroll
            JOIN admins admin ON payroll.hr_id = admin.id
            WHERE payroll.hr_id = :hr_id AND admin.deleted_at IS NULL
            ORDER BY payroll.pay_period_start DESC
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute(['hr_id' => $hr_id]);
    } elseif ($manager_id) {
        // Manager payslips
        $query = "
            SELECT 
                payroll.id,
                payroll.pay_period_start,
                payroll.pay_period_end,
                payroll.gross_pay,
                payroll.total_deductions,
                payroll.net_pay,
                m.m_first_name AS first_name,
                m.m_last_name AS last_name,
                m.m_position AS position
            FROM payroll
            JOIN managers m ON payroll.manager_id = m.id
            WHERE payroll.manager_id = :manager_id AND m.deleted_at IS NULL
            ORDER BY payroll.pay_period_start DESC
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute(['manager_id' => $manager_id]);
    } else {
        // Employee payslips
        // First, let's try to find the employee by employee_no if employee_id is not working
        $employee_query = "SELECT id FROM employees WHERE employee_no = ? OR id = ? LIMIT 1";
        $employee_stmt = $conn->prepare($employee_query);
        $employee_stmt->execute([$employee_id, $employee_id]);
        $employee = $employee_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$employee) {
            die("Employee not found. Please contact administrator.");
        }
        
        $actual_employee_id = $employee['id'];
        
        $query = "
            SELECT 
                payroll.id,
                payroll.pay_period_start,
                payroll.pay_period_end,
                payroll.gross_pay,
                payroll.total_deductions,
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
        $stmt->execute(['employee_id' => $actual_employee_id]);
    }
    
    $payslips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pass the $payslips to your view
    require views_path("user/user_mypayslip");
} catch (Exception $e) {
    // For the main page, we can show the error
    ini_set('display_errors', 1);
    die("Error loading payslips: " . $e->getMessage());
}

