<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';
$db = new Database();
$pdo = $db->getConnection();

// --- Fetch soft-deleted employees and managers for listing ---
$employees = $pdo->query("SELECT *, 'employee' as record_type FROM employees WHERE deleted_at IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
$managers = $pdo->query("SELECT *, 'manager' as record_type FROM managers WHERE deleted_at IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);

// Combine and sort by deletion date (most recent first)
$allRecords = array_merge($employees, $managers);
usort($allRecords, function($a, $b) {
    return strtotime($b['deleted_at']) - strtotime($a['deleted_at']);
});

require views_path("auth/delete_history");
?>
