<?php
// API wrapper for download_payslip functionality
// This will be called through the main routing system to maintain session consistency

// Debug: Log session status
error_log("Download API Wrapper - Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive'));
error_log("Download API Wrapper - Session data: " . print_r($_SESSION, true));

// Get the parameters
$payroll_id = $_GET['payroll_id'] ?? '';
$view_mode = $_GET['view'] ?? false;
$print_mode = $_GET['print'] ?? false;
$download_mode = $_GET['download'] ?? false;

// Debug: Log parameters
error_log("Download API Wrapper - Parameters: payroll_id=$payroll_id, view=$view_mode, print=$print_mode, download=$download_mode");

// Validate required parameters
if (!$payroll_id) {
    error_log("Download API Wrapper - Missing payroll_id parameter");
    http_response_code(400);
    echo "Missing payroll_id parameter";
    exit;
}

// Include the actual download logic
require_once __DIR__ . '/download_payslip.php';
?>
