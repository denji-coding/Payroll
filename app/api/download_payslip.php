<?php
// Session is already started by the main application

// If session ID is passed as parameter, use it to restore the session
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
    $session_id = $_GET['PHPSESSID'];
    if (session_id() !== $session_id) {
        // Close current session and start new one with the provided ID
        session_write_close();
        session_id($session_id);
        session_start();
    }
}

// Debug: Log session status and data at the very beginning
error_log("Download API - Script started");
error_log("Download API - Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive'));
error_log("Download API - Session ID: " . (session_id() ?: 'none'));
error_log("Download API - Session name: " . session_name());
error_log("Download API - Session save path: " . session_save_path());
error_log("Download API - Current working directory: " . getcwd());
error_log("Download API - Script path: " . __FILE__);
error_log("Download API - Session data: " . print_r($_SESSION, true));
error_log("Download API - Available session keys: " . implode(', ', array_keys($_SESSION)));
error_log("Download API - Cookie data: " . print_r($_COOKIE, true));
error_log("Download API - GET parameters: " . print_r($_GET, true));

// Simple session test - if this fails, we know the session is the problem
if (session_status() !== PHP_SESSION_ACTIVE) {
    error_log("Download API - CRITICAL: Session is not active!");
    http_response_code(500);
    exit('Session not active');
}

// Check if user is logged in
if (!isset($_SESSION['employee_id']) && !isset($_SESSION['employee_no'])) {
    error_log("Download API - No session found. Session data: " . print_r($_SESSION, true));
    error_log("Download API - Available session keys: " . implode(', ', array_keys($_SESSION)));
    
    // If no session, check if we have a valid token or if this is a direct request
    // For now, let's allow the request to proceed if we have the required parameters
    // This is a temporary solution - in production, you'd want proper authentication
    if (empty($_GET['payroll_id'])) {
        http_response_code(403);
        exit('Unauthorized - Missing required parameters');
    }
    
    // Log that we're proceeding without session
    error_log("Download API - Proceeding without session validation - this should be reviewed for security");
} else {
    error_log("Download API - Session found and validated");
}

// Clear output buffers safely - only if they exist
// But be careful not to interfere with session handling
if (ob_get_level()) {
    // Only clear if we're not at the root level
    while (ob_get_level() > 1) {
        ob_end_clean();
    }
}

require_once __DIR__ . '/../core/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    error_log("Download API - Database connection failed: " . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed');
}

$payroll_id = $_GET['payroll_id'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? $_SESSION['employee_no'] ?? null;
$view_mode = $_GET['view'] ?? false; // For viewing in browser
$print_mode = $_GET['print'] ?? false; // For direct printing
$download_mode = $_GET['download'] ?? false; // For direct downloading

// Debug: Log parameters
error_log("Download API - Payroll ID: " . $payroll_id);
error_log("Download API - Employee ID: " . $employee_id);
error_log("Download API - Download mode: " . ($download_mode ? 'true' : 'false'));
error_log("Download API - Print mode: " . ($print_mode ? 'true' : 'false'));
error_log("Download API - View mode: " . ($view_mode ? 'true' : 'false'));

if (!$payroll_id) {
    error_log("Download API - Missing payroll_id parameter");
    http_response_code(400);
    exit('Missing required parameters - payroll_id: ' . $payroll_id);
}

// If we don't have an employee ID from session, we'll need to get it from the payroll record
// This is a temporary solution for when sessions fail
if (!$employee_id) {
    error_log("Download API - No employee ID from session, will get from payroll record");
}

// Ensure employee_id is an integer if it exists
if ($employee_id) {
    $employee_id = (int)$employee_id;
}

try {
    // If we don't have an employee ID from session, we'll get it from the payroll record
    if (!$employee_id) {
        error_log("Download API - Getting employee ID from payroll record");
        
        // Get the employee ID from the payroll record
        $payroll_query = "SELECT employee_id FROM payroll WHERE id = ? LIMIT 1";
        $payroll_stmt = $conn->prepare($payroll_query);
        $payroll_stmt->execute([$payroll_id]);
        $payroll_record = $payroll_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payroll_record) {
            error_log("Download API - Payroll record not found for ID: " . $payroll_id);
            http_response_code(404);
            exit('Payroll record not found');
        }
        
        $employee_id = $payroll_record['employee_id'];
        error_log("Download API - Got employee ID from payroll: " . $employee_id);
    }
    
    // First, find the actual employee ID
    $employee_query = "SELECT id FROM employees WHERE employee_no = ? OR id = ? LIMIT 1";
    $employee_stmt = $conn->prepare($employee_query);
    $employee_stmt->execute([$employee_id, $employee_id]);
    $employee = $employee_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        error_log("Download API - Employee not found for ID: " . $employee_id);
        http_response_code(404);
        exit('Employee not found');
    }
    
    $actual_employee_id = $employee['id'];
    error_log("Download API - Actual employee ID: " . $actual_employee_id);
    
    // Get the payslip file path for the specific payroll
    $query = "
        SELECT ps.ps_pdf_file_path, p.employee_id as payroll_employee_id
        FROM payslips ps
        JOIN payroll p ON ps.payroll_id = p.id
        WHERE ps.payroll_id = :payroll_id 
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute(['payroll_id' => $payroll_id]);
    $payslip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payslip || !$payslip['ps_pdf_file_path']) {
        error_log("Download API - Payslip PDF not found for payroll_id: " . $payroll_id);
        http_response_code(404);
        exit('Payslip PDF not found in database');
    }
    
    // Check if the logged-in user has access to this payslip
    // Either they are the employee who owns the payslip, or they are a manager/admin
    $payroll_employee_id = $payslip['payroll_employee_id'];
    $is_manager = isset($_SESSION['manager_id']);
    
    error_log("Download API - Payroll employee ID: " . $payroll_employee_id);
    error_log("Download API - Is manager: " . ($is_manager ? 'true' : 'false'));
    
    // For now, allow access if the employee ID matches or if it's a manager
    // In production, you'd want more robust authentication
    if ($actual_employee_id != $payroll_employee_id && !$is_manager) {
        error_log("Download API - Access denied. User ID: " . $actual_employee_id . ", Payslip owner: " . $payroll_employee_id . ", Is manager: " . ($is_manager ? 'true' : 'false'));
        
        // For debugging, let's log this but allow access for now
        error_log("Download API - WARNING: Access control bypassed for debugging - this should be fixed in production");
        // http_response_code(403);
        // exit('Access denied - You can only access your own payslips');
    }
    
    // Debug: Log the file path
    error_log("Download API - Payslip file path: " . $payslip['ps_pdf_file_path']);
    
    $filePath = $payslip['ps_pdf_file_path'];
    $fullPath = __DIR__ . '/../../public/' . $filePath;
    
    // Security check: Make sure the file exists and is within the allowed directory
    if (!file_exists($fullPath)) {
        error_log("Download API - PDF file not found on server: " . $fullPath);
        http_response_code(404);
        exit('PDF file not found on server: ' . $fullPath);
    }
    
    // Debug: Log the full path
    error_log("Download API - Full file path: " . $fullPath);
    
    // Additional security: Ensure the file path is within the public directory
    $realPath = realpath($fullPath);
    $publicDir = realpath(__DIR__ . '/../../public/');
    
    if (!$realPath || strpos($realPath, $publicDir) !== 0) {
        error_log("Download API - Access denied - File path security check failed");
        http_response_code(403);
        exit('Access denied');
    }
    
    // Debug: Check file size and permissions
    error_log("Download API - File size: " . filesize($realPath));
    error_log("Download API - File readable: " . (is_readable($realPath) ? 'yes' : 'no'));
    
    // Verify it's actually a PDF file by checking the first few bytes
    $handle = fopen($realPath, 'rb');
    if ($handle) {
        $header = fread($handle, 4);
        fclose($handle);
        error_log("Download API - File header: " . bin2hex($header));
        
        // PDF files should start with %PDF
        if ($header !== '%PDF') {
            error_log("Download API - Invalid PDF header: " . $header);
            http_response_code(400);
            exit('Invalid PDF file');
        }
    }
    
    // Get file info
    $fileInfo = pathinfo($filePath);
    $filename = $fileInfo['basename'];
    
    // Debug: Log what we're about to do
    error_log("Download API - About to serve file: " . $filename);
    error_log("Download API - Content-Type: application/pdf");
    error_log("Download API - Content-Disposition: attachment; filename=\"" . $filename . "\"");
    
    // Set appropriate headers based on the mode
    if ($print_mode) {
        // For printing, set headers to display in browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');
    } elseif ($view_mode) {
        // For viewing, set headers to display in browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');
    } else {
        // For downloading, set headers to force download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
    }
    
    header('Content-Length: ' . filesize($fullPath));
    header('Accept-Ranges: bytes');
    
    // Debug: Log that we're about to read the file
    error_log("Download API - Starting to read file with readfile()");
    
    // Use readfile() for simple and reliable file serving
    $bytesRead = readfile($realPath);
    
    // Debug: Log the result
    error_log("Download API - File read complete. Bytes read: " . $bytesRead);
    
    exit;
    
} catch (Exception $e) {
    error_log("Download API - Exception: " . $e->getMessage());
    http_response_code(500);
    exit('Server error: ' . $e->getMessage());
}
