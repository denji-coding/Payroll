<?php
require_once '../app/core/database.php';

// Create database connection
$db = new Database();
$conn = $db->getConnection();

// Fetch non-deleted managers ordered by ID descending (most recent first)
try {
    // Check if deleted_at column exists
    $stmt = $conn->prepare("SELECT * FROM managers WHERE deleted_at IS NULL ORDER BY id DESC");
    $stmt->execute();
    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if deleted_at column doesn't exist
    $stmt = $conn->prepare("SELECT * FROM managers ORDER BY id DESC");
$stmt->execute();
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require views_path("auth/managers_account");
?>