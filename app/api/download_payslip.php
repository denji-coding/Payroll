<?php
session_start();

// Optional: Add session check to restrict access
// if (!isset($_SESSION['employee_id'])) {
//     http_response_code(403);
//     exit('Unauthorized');
// }

$filename = $_GET['file'] ?? '';
$basePath = __DIR__ . '/../../public/payslips/';
$filePath = realpath($basePath . $filename);

// Security check: Make sure the file is inside the allowed folder
if (!$filename || !file_exists($filePath) || strpos($filePath, realpath($basePath)) !== 0) {
    http_response_code(404);
    exit('File not found.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream'); // This forces download
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;
