<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';
$db = new Database();
$pdo = $db->getConnection();

// --- Handle manual permanent delete request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->execute([$deleteId]);
    
    header("Location: index.php?payroll=delete_history&status=deleted");
    exit;
}

// --- Cleanup: Permanently delete employees soft deleted more than 60 days ago --- 10 MINUTE
$pdo->prepare("DELETE FROM employees WHERE deleted_at IS NOT NULL AND deleted_at <= DATE_SUB(NOW(), INTERVAL 60 DAY)")->execute();

// --- JSON endpoint for fetching a single deleted employee details ---
if (isset($_GET['payroll']) && $_GET['payroll'] === 'delete_history' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $employeeId = intval($_GET['id']);

    $stmt = $pdo->prepare("
        SELECT 
            e.id,
            e.employee_no,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.blood_type,
            e.civil_status,
            e.dob,
            e.sex,
            e.citizenship,
            e.rfid_number,
            e.position,
            e.email,
            e.contact_number,
            e.place_of_birth,
            e.branch_manager,
            e.address,
            e.base_salary,
            e.sss_number,
            e.pagibig_number,
            e.philhealth_number,
            e.photo_path,
            m.name AS manager_name,
            m.branch AS manager_address
        FROM employees e
        LEFT JOIN managers m ON e.branch_manager = m.id
        WHERE e.id = :id AND e.deleted_at IS NOT NULL
        LIMIT 1;
    ");
    $stmt->execute(['id' => $employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        echo json_encode($employee);
    } else {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'Deleted employee not found']);
    }
    exit;
}

// --- Handle restore request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_id'])) {
    $restoreId = $_POST['restore_id'];

    $stmt = $pdo->prepare("UPDATE employees SET deleted_at = NULL WHERE id = ?");
    $stmt->execute([$restoreId]);

    header("Location: index.php?payroll=delete_history&status=restored");
    exit;
}

// --- Fetch soft-deleted employees for listing ---
$sql = "SELECT * FROM employees WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$deletedEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $sql = "SELECT * FROM managers ORDER BY created_at DESC";
// $stmt = $pdo->prepare($sql);
// $stmt->execute();
// $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);


require views_path("auth/delete_history");
?>
