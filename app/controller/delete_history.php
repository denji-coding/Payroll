<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';
$db = new Database();
$pdo = $db->getConnection();


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
