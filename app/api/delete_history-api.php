<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/database.php';

$db = new Database();
$pdo = $db->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $employeeId = intval($_GET['id']);
            $stmt = $pdo->prepare("
                SELECT 
                    e.id, e.employee_no, e.first_name, e.middle_name, e.last_name,
                    e.blood_type, e.civil_status, e.dob, e.sex, e.citizenship,
                    e.rfid_number, e.position, e.email, e.contact_number,
                    e.place_of_birth, e.branch_manager, e.address, e.base_salary,
                    e.sss_number, e.pagibig_number, e.philhealth_number, e.photo_path,
                    CONCAT(
                            UPPER(LEFT(m.m_first_name, 1)), LOWER(SUBSTRING(m.m_first_name, 2)), ' ',
                            UPPER(LEFT(m.m_middle_name, 1)), '. ',
                            UPPER(LEFT(m.m_last_name, 1)), LOWER(SUBSTRING(m.m_last_name, 2))
                            ) AS manager_name, m.m_branch AS manager_address
                FROM employees e
                LEFT JOIN managers m ON e.branch_manager = m.id
                WHERE e.id = :id AND e.deleted_at IS NOT NULL
                LIMIT 1
            ");
            $stmt->execute(['id' => $employeeId]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                echo json_encode($employee);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Deleted employee not found']);
            }
            exit;
        }

        // Fetch all soft-deleted employees (optional if needed)
        $stmt = $pdo->query("SELECT * FROM employees WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON body']);
            exit;
        }

        // Restore
        if (isset($input['restore_id'])) {
            $restoreId = intval($input['restore_id']);
            $stmt = $pdo->prepare("UPDATE employees SET deleted_at = NULL WHERE id = ?");
            $stmt->execute([$restoreId]);

            // Return updated HTML table
            ob_start();
            $stmt = $pdo->query("SELECT * FROM employees WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
            $deletedEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require __DIR__ . '/../view/partials/deleted_history_rows.php';
            $html = ob_get_clean();

            echo json_encode(['status' => 'restored', 'html' => $html]);
            exit;
        }

        // Permanent delete
        if (isset($input['delete_id'])) {
            $deleteId = intval($input['delete_id']);
            $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$deleteId]);

            ob_start();
            $stmt = $pdo->query("SELECT * FROM employees WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
            $deletedEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require __DIR__ . '/../view/partials/deleted_history_rows.php';
            $html = ob_get_clean();

            echo json_encode(['status' => 'deleted', 'html' => $html]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['error' => 'Missing restore_id or delete_id']);
        exit;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
}

// Optional cleanup: permanently delete soft-deleted employees older than 60 days
try {
    $pdo->prepare("
        DELETE FROM employees 
        WHERE deleted_at IS NOT NULL 
        AND deleted_at <= DATE_SUB(NOW(), INTERVAL 60 DAY)
    ")->execute();
} catch (Exception $e) {
    // Fail silently
}
