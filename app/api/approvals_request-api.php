<?php
// approvals_request-api.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/database.php';
header('Content-Type: application/json');

$db = new Database();
$pdo = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true) ?? [];
parse_str($_SERVER['QUERY_STRING'] ?? '', $query);

$id = $_GET['id'] ?? $input['id'] ?? null;
$action = $_GET['action'] ?? $input['action'] ?? null;

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Fetch single employee
                $stmt = $pdo->prepare("
    SELECT 
        e.*, 
        CASE 
            WHEN m.id IS NOT NULL THEN CONCAT(m.m_first_name, ' ', UPPER(LEFT(m.m_middle_name, 1)), '. ', m.m_last_name)
            WHEN a.id IS NOT NULL THEN CONCAT(a.hr_first_name, ' ', UPPER(LEFT(a.hr_middle_name, 1)), '. ', a.hr_last_name)
            ELSE NULL
        END AS manager_name,
        CASE 
            WHEN m.id IS NOT NULL THEN CONCAT(m.m_first_name, ' ', UPPER(LEFT(m.m_middle_name, 1)), '. ', m.m_last_name)
            WHEN a.id IS NOT NULL THEN CONCAT(a.hr_first_name, ' ', UPPER(LEFT(a.hr_middle_name, 1)), '. ', a.hr_last_name)
            ELSE NULL
        END AS branch_name,
        m.m_branch AS branch_address
    FROM employees e
    LEFT JOIN managers m ON e.branch_manager = m.id AND m.deleted_at IS NULL
    LEFT JOIN admins a ON e.branch_manager = a.id AND a.deleted_at IS NULL
    WHERE e.id = :id AND e.deleted_at IS NULL
");

                $stmt->execute([':id' => $id]);
                $employee = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($employee) {
                    echo json_encode(['status' => 'success', 'data' => $employee]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
                }
            } else {
                // Fetch all employees pending or rejected
                $stmt = $pdo->prepare("
                    SELECT 
                        e.*, 
                        CONCAT(m.m_first_name, ' ', UPPER(LEFT(m.m_middle_name, 1)), '. ', m.m_last_name) AS manager_name,
                        m.m_branch AS branch_address
                    FROM employees e
                    LEFT JOIN managers m ON e.branch_manager = m.id
                    WHERE e.approved_by_manager IN (0, -1) AND e.deleted_at IS NULL
                    ORDER BY e.id DESC
                ");
                $stmt->execute();
                $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

                ob_start();
                $index = 1;
                foreach ($employees as $emp) {
                    include realpath(__DIR__ . '/../..') . '/app/view/partials/approval_table_row.php';
                }
                $tbodyHtml = ob_get_clean();

                echo json_encode(['status' => 'success', 'tbody' => $tbodyHtml]);
            }
            break;

        case 'POST':
            // Handle actions via JSON body
            if ($action === 'resend' && $id) {
                $checkStmt = $pdo->prepare("SELECT approved_by_manager FROM employees WHERE id = :id AND deleted_at IS NULL");
                $checkStmt->execute(['id' => $id]);
                $emp = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($emp && (int)$emp['approved_by_manager'] === -1) {
                    $updateStmt = $pdo->prepare("UPDATE employees SET approved_by_manager = 0 WHERE id = :id");
                    $updateStmt->execute(['id' => $id]);

                    echo json_encode($updateStmt->rowCount() > 0
                        ? ['status' => 'success', 'message' => 'Approval request resent successfully']
                        : ['status' => 'error', 'message' => 'Failed to resend approval']
                    );
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Cannot resend approval for this employee']);
                }

            } elseif ($action === 'delete' && $id) {
                $stmt = $pdo->prepare("UPDATE employees SET deleted_at = NOW() WHERE id = :id");
                $stmt->execute(['id' => $id]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Employee marked as deleted']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to delete employee']);
                }

            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid POST request']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
