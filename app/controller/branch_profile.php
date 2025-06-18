<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Controller or main PHP file
$managerId = $_SESSION['manager_id'] ?? null;

if (!$managerId) {
    die("Manager not logged in.");
}

require_once "../app/core/database.php";



$db = new Database();
$result = $db->query("SELECT * FROM managers WHERE id = ?", [$managerId]);
$managers = $result[0] ?? null;

if (!$managers) {
    die("Manager not found.");
}

// Now include the view, $manager is accessible inside
require views_path("branch/branch_profile");
