<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';
$db = new Database();
$pdo = $db->getConnection();



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];

    if ($action === 'resend') {
        try {
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
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;

    } elseif ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("UPDATE employees SET deleted_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);

            echo json_encode($stmt->rowCount() > 0
                ? ['status' => 'success', 'message' => 'Employee deleted successfully']
                : ['status' => 'error', 'message' => 'Failed to delete employee']
            );
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    header('Content-Type: application/json');

    try {
        $stmt = $pdo->prepare("
            SELECT 
                e.*, 
                a.name AS manager_name,
                m.name AS branch_name,
                m.branch AS branch_address
            FROM employees e
            LEFT JOIN admins a ON e.branch_manager = a.id
            LEFT JOIN managers m ON e.branch_manager = m.id
            WHERE e.id = :id AND e.deleted_at IS NULL
        ");
        $stmt->execute(['id' => $id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee) {
            echo json_encode(['status' => 'success', 'data' => $employee]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// If not POST or GET with id, load employee approval list
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE approved_by_manager IN (0, -1) AND deleted_at IS NULL ORDER BY id DESC");
    $stmt->execute();
    $employeesApproval = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$stmt = $pdo->prepare("SELECT * FROM managers ORDER BY created_at DESC");
$stmt->execute();
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pass to view
require views_path("auth/approvals_request");
