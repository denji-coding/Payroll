<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/database.php';

$db = new Database();
$pdo = $db->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $recordId = intval($_GET['id']);
            $recordType = $_GET['type'] ?? 'employee';
            
            if ($recordType === 'employee') {
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
            } else {
                $stmt = $pdo->prepare("
                    SELECT 
                        m.id, m.m_employee_id, m.m_first_name, m.m_middle_name, m.m_last_name,
                        m.m_blood_type, m.m_civil_status, m.m_dob, m.m_sex, m.m_citizenship,
                        m.m_rfid_number, m.m_position, m.m_email, m.m_contact_number,
                        m.m_place_of_birth, m.m_branch, m.m_address, m.m_base_salary,
                        m.m_sss_number, m.m_pagibig_number, m.m_philhealth_number, m.m_photo_path
                    FROM managers m
                    WHERE m.id = :id AND m.deleted_at IS NOT NULL
                    LIMIT 1
                ");
            }
            
            $stmt->execute(['id' => $recordId]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record) {
                echo json_encode($record);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Deleted record not found']);
            }
            exit;
        }

        // Fetch all soft-deleted records
        $employees = $pdo->query("SELECT *, 'employee' as record_type FROM employees WHERE deleted_at IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
        $managers = $pdo->query("SELECT *, 'manager' as record_type FROM managers WHERE deleted_at IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
        
        $allRecords = array_merge($employees, $managers);
        usort($allRecords, function($a, $b) {
            return strtotime($b['deleted_at']) - strtotime($a['deleted_at']);
        });
        
        echo json_encode($allRecords);
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
            $recordType = $input['record_type'] ?? 'employee';
            
            if ($recordType === 'employee') {
            $stmt = $pdo->prepare("UPDATE employees SET deleted_at = NULL WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE managers SET deleted_at = NULL WHERE id = ?");
            }
            
            $stmt->execute([$restoreId]);

            // Return updated HTML table
            ob_start();
            $employees = $pdo->query("SELECT *, 'employee' as record_type FROM employees WHERE deleted_at IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
            $managers = $pdo->query("SELECT *, 'manager' as record_type FROM managers WHERE deleted_at IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
            $allRecords = array_merge($employees, $managers);
            usort($allRecords, function($a, $b) {
                return strtotime($b['deleted_at']) - strtotime($a['deleted_at']);
            });
            require __DIR__ . '/../view/partials/deleted_history_rows.php';
            $html = ob_get_clean();

            echo json_encode(['status' => 'restored', 'html' => $html]);
            exit;
        }

        // Permanent delete
        if (isset($input['delete_id'])) {
            $deleteId = intval($input['delete_id']);
            $recordType = $input['record_type'] ?? 'employee';
            
            if ($recordType === 'employee') {
            $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("DELETE FROM managers WHERE id = ?");
            }
            
            $stmt->execute([$deleteId]);

            ob_start();
            $employees = $pdo->query("SELECT *, 'employee' as record_type FROM employees WHERE deleted_at IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
            $managers = $pdo->query("SELECT *, 'manager' as record_type FROM managers WHERE deleted_at IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
            $allRecords = array_merge($employees, $managers);
            usort($allRecords, function($a, $b) {
                return strtotime($b['deleted_at']) - strtotime($a['deleted_at']);
            });
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

// Optional cleanup: permanently delete soft-deleted records older than 60 days
try {
    $pdo->exec("DELETE FROM employees WHERE deleted_at IS NOT NULL AND deleted_at <= DATE_SUB(NOW(), INTERVAL 60 DAY)");
    $pdo->exec("DELETE FROM managers WHERE deleted_at IS NOT NULL AND deleted_at <= DATE_SUB(NOW(), INTERVAL 60 DAY)");
} catch (Exception $e) {
    // Fail silently
}
