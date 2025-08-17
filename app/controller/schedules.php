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
$scheduledEmployeeIds = $conn->query("SELECT employee_id FROM employee_schedules")->fetchAll(PDO::FETCH_COLUMN);

// Filter only employees who are not yet scheduled
$availableEmployees = array_filter($allEmployees, function($emp) use ($scheduledEmployeeIds) {
    return !in_array($emp['id'], $scheduledEmployeeIds);
});

// Get all managers
$stmt = $conn->prepare("SELECT id, m_first_name, m_middle_name, m_last_name, m_position, m_branch FROM managers WHERE deleted_at IS NULL");
$stmt->execute();
$allManagers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get scheduled manager IDs
$scheduledManagerIds = $conn->query("SELECT manager_id FROM manager_schedules")->fetchAll(PDO::FETCH_COLUMN);

// Filter only managers who are not yet scheduled
$availableManagers = array_filter($allManagers, function($manager) use ($scheduledManagerIds) {
    return !in_array($manager['id'], $scheduledManagerIds);
});

// Combine and format results
$combinedRecords = [];

// Add employees
foreach ($availableEmployees as $emp) {
    $combinedRecords[] = [
        'id' => $emp['id'],
        'name' => ucwords($emp['first_name']) . ' ' . 
                 (isset($emp['middle_name'][0]) ? strtoupper($emp['middle_name'][0]) . '. ' : '') . 
                 ucwords($emp['last_name']),
        'position' => $emp['position'],
        'type' => 'employee',
        'approved_by_manager' => $emp['approved_by_manager'] ?? 1
    ];
}

// Add managers
foreach ($availableManagers as $manager) {
    $combinedRecords[] = [
        'id' => $manager['id'],
        'name' => ucwords($manager['m_first_name']) . ' ' . 
                 (isset($manager['m_middle_name'][0]) ? strtoupper($manager['m_middle_name'][0]) . '. ' : '') . 
                 ucwords($manager['m_last_name']),
        'position' => $manager['m_position'],
        'type' => 'manager',
        'approved_by_manager' => 1 // Managers are always approved
    ];
}

// Sort by ID in descending order (newest first)
usort($combinedRecords, function($a, $b) {
    return $b['id'] <=> $a['id'];
});

// Pass data to the view
$data = [
    'employees' => $combinedRecords
];

// Load the schedules view
require views_path("auth/schedules");
