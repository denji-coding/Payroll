<?php
require_once '../app/core/database.php';

// Create database connection
$db = new Database();
$conn = $db->getConnection();

// Assuming you already have a database connection in $conn
$stmt = $conn->prepare("SELECT * FROM managers ");
$stmt->execute();
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);


require views_path("auth/managers_account");