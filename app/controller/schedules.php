<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';
require_once '../app/Model/Employees.php';
// $admin_id = $_SESSION['admin_id'] ?? null;

// if (!$admin_id) {
//     // If it's an API request, return JSON
//     if (isset($_GET['id'])) {
//         header('Content-Type: application/json');
//         echo json_encode(['error' => 'Unauthorized admin access']);
//         exit;
//     } else {
//         // Show custom 403 page for browser access
//         http_response_code(403);
//          require_once '../app/Error/unauthorized.php'; // Adjust the path
//         exit;
//     }
// }

$db = new Database();
$conn = $db->getConnection();
$employeeModel = new Employees($conn);

// Get all employees and determine available ones (not scheduled yet)
$allEmployees = $employeeModel->getAllEmployees();
$scheduledEmployeeIds = $conn->query("SELECT employee_id FROM employee_schedules")->fetchAll(PDO::FETCH_COLUMN);

$availableEmployees = array_filter($allEmployees, function($emp) use ($scheduledEmployeeIds) {
    return !in_array($emp['id'], $scheduledEmployeeIds);
});
$data['employees'] = $availableEmployees;

// Handle AJAX GET Request for specific schedule
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch_schedule']) && is_numeric($_GET['fetch_schedule'])) {
    $schedule_id = (int) $_GET['fetch_schedule'];

    $stmt = $conn->prepare("SELECT * FROM schedules WHERE id = :id");
    $stmt->execute(['id' => $schedule_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($schedule 
        ? ['status' => 'success', 'data' => $schedule]
        : ['status' => 'error', 'message' => 'Schedule not found.']
    );
    exit;
}

// Handle POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete Schedule
    if (isset($_POST['action'], $_POST['schedule_id']) && $_POST['action'] === 'delete') {
        $schedule_id = filter_var($_POST['schedule_id'], FILTER_SANITIZE_NUMBER_INT);
        if (!$schedule_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid schedule ID.',
                'icon' => 'warning'
            ]);
            exit;
        }

        try {
            $conn->prepare("DELETE FROM employee_schedules WHERE schedule_id = :schedule_id")
                ->execute(['schedule_id' => $schedule_id]);

            $conn->prepare("DELETE FROM schedules WHERE id = :id")
                ->execute(['id' => $schedule_id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Schedule successfully deleted.',
                'icon' => 'success'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Database Error',
                'message' => $e->getMessage(),
                'icon' => 'error'
            ]);
        }
        exit;
    }

    // Update Schedule
    if (!isset($_POST['employee_id']) && isset($_POST['id'], $_POST['schedule_name'], $_POST['time_in'], $_POST['time_out'], $_POST['grace_period'])) {
        $requiredFields = ['id', 'schedule_name', 'time_in', 'time_out', 'grace_period'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Field '{$field}' is required.",
                    'icon' => 'warning'
                ]);
                exit;
            }
        }

        $schedule_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

        try {
            $conn->prepare("UPDATE schedules 
                SET name = :name, time_in = :time_in, time_out = :time_out, grace_period = :grace_period 
                WHERE id = :id")->execute([
                'name' => $_POST['schedule_name'],
                'time_in' => $_POST['time_in'],
                'time_out' => $_POST['time_out'],
                'grace_period' => $_POST['grace_period'],
                'id' => $schedule_id
            ]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Schedule successfully updated.',
                'icon' => 'success'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Database Error',
                'message' => $e->getMessage(),
                'icon' => 'error'
            ]);
        }
        exit;
    }

    // Add New Schedule
    if (isset($_POST['employee_id'], $_POST['time_in'], $_POST['time_out'], $_POST['grace_period'])) {
        $employee_id = filter_var($_POST['employee_id'], FILTER_SANITIZE_NUMBER_INT);
        if (!$employee_id || empty($_POST['time_in']) || empty($_POST['time_out']) || empty($_POST['grace_period'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'All fields are required.',
                'icon' => 'warning'
            ]);
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM employees WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $employee_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            $fullName = $employee
                ? ucwords($employee['first_name']) . ' ' .
                (isset($employee['middle_name'][0]) ? strtoupper($employee['middle_name'][0]) . '. ' : '') .
                ucwords($employee['last_name'])
                : 'Unknown Employee';



            $conn->prepare("INSERT INTO schedules (name, time_in, time_out, grace_period) 
                VALUES (:name, :time_in, :time_out, :grace_period)")
                ->execute([
                    'name' => $fullName,
                    'time_in' => $_POST['time_in'],
                    'time_out' => $_POST['time_out'],
                    'grace_period' => $_POST['grace_period']
                ]);

            $schedule_id = $conn->lastInsertId();

            $conn->prepare("INSERT INTO employee_schedules (employee_id, schedule_id) 
                VALUES (:employee_id, :schedule_id)")
                ->execute([
                    'employee_id' => $employee_id,
                    'schedule_id' => $schedule_id
                ]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Schedule successfully assigned to employee.',
                'icon' => 'success'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Database Error',
                'message' => $e->getMessage(),
                'icon' => 'error'
            ]);
        }
        exit;
    }
}

// Load the view
require views_path("auth/schedules");
