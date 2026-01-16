<?php
require_once '../app/core/session_helper.php';

// Check if admin is logged in
requireAdminAuth();

// Log user activity
logUserActivity('Access approvals request page');

// controller/approval_request.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';

$db = new Database();
$pdo = $db->getConnection();

// Fetch employees pending or rejected
try {
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
    $employeesApproval = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Employee fetch error: " . $e->getMessage());
}

// Fetch all managers for dropdown/filtering if needed
try {
    $stmt = $pdo->prepare("SELECT * FROM managers ORDER BY m_created_at DESC");
    $stmt->execute();
    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Manager fetch error: " . $e->getMessage());
}

// Send to view
require views_path("auth/approvals_request");
