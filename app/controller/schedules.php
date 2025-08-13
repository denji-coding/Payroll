<?php
require_once '../app/core/session_helper.php';

// Check if admin is logged in
requireAdminAuth();

// Log user activity
logUserActivity('Access schedules page');

// schedules.php (Controller)

require_once '../app/core/database.php';
require_once '../app/Model/Employees.php';

$db = new Database();
$conn = $db->getConnection();
$employeeModel = new Employees($conn);

// Get all employees
$allEmployees = $employeeModel->getAllEmployees();

// Get scheduled employee IDs
$scheduledIds = $conn->query("SELECT employee_id FROM employee_schedules")->fetchAll(PDO::FETCH_COLUMN);

// Filter only employees who are not yet scheduled
$availableEmployees = array_filter($allEmployees, function($emp) use ($scheduledIds) {
    return !in_array($emp['id'], $scheduledIds);
});

// 🔽 Sort available employees by ID in descending order (newest first)
usort($availableEmployees, function($a, $b) {
    return $b['id'] <=> $a['id'];
});

// Pass data to the view
$data = [
    'employees' => array_values($availableEmployees) // reset array keys
];

// Load the schedules view
require views_path("auth/schedules");
