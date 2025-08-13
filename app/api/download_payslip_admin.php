<?php
// Admin Payslip Download API
// This endpoint is specifically for admin users downloading payslips from payslips.view.php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Start output buffering
ob_start();

require_once __DIR__ . '/../core/database.php';

// Check if user is logged in as admin
session_start();
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['employee_id'])) {
    error_log("Admin Download API - No admin session found");
    http_response_code(403);
    exit('Unauthorized - Admin access required');
}

// Clear output buffers safely
if (ob_get_level()) {
    while (ob_get_level() > 1) {
        ob_end_clean();
    }
}

try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    error_log("Admin Download API - Database connection failed: " . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed');
}

$payroll_id = $_GET['payroll_id'] ?? '';
$download_mode = $_GET['download'] ?? 'download'; // download, view, or print

// Debug logging
error_log("Admin Download API - Payroll ID: " . $payroll_id);
error_log("Admin Download API - Download mode: " . $download_mode);
error_log("Admin Download API - Session data: " . print_r($_SESSION, true));

if (!$payroll_id) {
    error_log("Admin Download API - Missing payroll_id parameter");
    http_response_code(400);
    exit('Missing required parameters - payroll_id');
}

try {
    // Get payslip information - admin can access any payslip
    $query = "
        SELECT 
            ps.ps_pdf_file_path, 
            p.employee_id as payroll_employee_id,
            e.employee_no,
            e.first_name,
            e.last_name,
            p.pay_period_start,
            p.pay_period_end
        FROM payslips ps
        JOIN payroll p ON ps.payroll_id = p.id
        JOIN employees e ON p.employee_id = e.id
        WHERE ps.payroll_id = :payroll_id
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute(['payroll_id' => $payroll_id]);
    $payslip = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payslip || !$payslip['ps_pdf_file_path']) {
        error_log("Admin Download API - Payslip not found or no PDF path for payroll_id: " . $payroll_id);
        http_response_code(404);
        exit('Payslip not found or PDF not available');
    }

    // Debug: Log the file path
    error_log("Admin Download API - Payslip file path: " . $payslip['ps_pdf_file_path']);
    
    $filePath = $payslip['ps_pdf_file_path'];
    
    // Try different path constructions for admin access
    $possiblePaths = [
        __DIR__ . '/../../public/' . $filePath,  // public/payslips/filename.pdf
        __DIR__ . '/../../public/payslips/' . basename($filePath), // public/payslips specifically
        __DIR__ . '/../../' . $filePath,  // relative to project root
        $filePath, // absolute path if already absolute
    ];
    
    $fullPath = null;
    foreach ($possiblePaths as $path) {
        error_log("Admin Download API - Trying path: " . $path);
        if (file_exists($path)) {
            $fullPath = $path;
            error_log("Admin Download API - Found file at: " . $fullPath);
            break;
        }
    }
    
    if (!$fullPath) {
        error_log("Admin Download API - PDF file not found in any attempted paths");
        error_log("Admin Download API - Database file path: " . $filePath);
        http_response_code(404);
        exit('PDF file not found on server');
    }
    
    // Security check: Ensure the file path is within allowed directories
    $realPath = realpath($fullPath);
    $allowedDirs = [
        realpath(__DIR__ . '/../../public/'),
        realpath(__DIR__ . '/../../')
    ];
    
    $isAllowed = false;
    foreach ($allowedDirs as $allowedDir) {
        if ($allowedDir && strpos($realPath, $allowedDir) === 0) {
            $isAllowed = true;
            error_log("Admin Download API - File is within allowed directory: " . $allowedDir);
            break;
        }
    }
    
    if (!$realPath || !$isAllowed) {
        error_log("Admin Download API - Access denied - File path security check failed");
        http_response_code(403);
        exit('Access denied - File not in allowed directory');
    }
    
    // Check file size and permissions
    error_log("Admin Download API - File size: " . filesize($fullPath) . " bytes");
    error_log("Admin Download API - File readable: " . (is_readable($fullPath) ? 'yes' : 'no'));
    
    // Set appropriate headers based on mode
    if ($download_mode === 'download') {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="payslip_' . $payslip['employee_no'] . '_' . date('Y-m-d', strtotime($payslip['pay_period_start'])) . '_' . date('Y-m-d', strtotime($payslip['pay_period_end'])) . '.pdf"');
    } elseif ($download_mode === 'view') {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="payslip_' . $payslip['employee_no'] . '_' . date('Y-m-d', strtotime($payslip['pay_period_start'])) . '_' . date('Y-m-d', strtotime($payslip['pay_period_end'])) . '.pdf"');
    } else {
        // Default to download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="payslip_' . $payslip['employee_no'] . '_' . date('Y-m-d', strtotime($payslip['pay_period_start'])) . '_' . date('Y-m-d', strtotime($payslip['pay_period_end'])) . '.pdf"');
    }
    
    // Additional headers for better compatibility
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Length: ' . filesize($fullPath));
    header('Accept-Ranges: bytes');
    
    // Output the PDF file
    readfile($fullPath);
    
    error_log("Admin Download API - PDF file served successfully");
    exit;
    
} catch (Exception $e) {
    error_log("Admin Download API - Error: " . $e->getMessage());
    http_response_code(500);
    exit('Internal server error: ' . $e->getMessage());
}
?>
