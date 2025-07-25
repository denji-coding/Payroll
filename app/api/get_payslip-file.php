<?php
require_once __DIR__ . '/../core/database.php';

$employee_id = $_GET['employee_id'] ?? null;

if (!$employee_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Employee ID is required']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT ps_pdf_file_path FROM payslips WHERE employee_id = :id ORDER BY id DESC LIMIT 1");
$stmt->execute([':id' => $employee_id]);
$payslip = $stmt->fetch(PDO::FETCH_ASSOC);

if ($payslip && file_exists('../../public/' . $payslip['ps_pdf_file_path'])) {
    echo json_encode(['file' => $payslip['ps_pdf_file_path']]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Payslip not found']);
}
