<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../app/core/database.php";
$db = new Database();
$msg = "";

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Use null coalescing operator to safely access POST data
    $name = trim($_POST['name'] ?? '');  // Changed to $_POST['name']
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm-password'] ?? '');

    // Validate form inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $msg = "<div class='alert alert-danger text-center'>All fields are required.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "<div class='alert alert-danger text-center'>Invalid email format.</div>";
    } elseif ($password !== $confirm_password) {
        $msg = "<div class='alert alert-danger text-center'>Passwords do not match.</div>";
    } else {
        // Check if email already exists in the database
        $existing = $db->query("SELECT * FROM admins WHERE email = ?", [$email]);

        if ($existing && count($existing) > 0) {
            $msg = "<div class='alert alert-danger text-center'>The email '{$email}' is already registered.</div>";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new admin into the database
            $insert = $db->query("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)", [$name, $email, $hashed_password]);

            if ($insert) {
                $msg = "<div class='alert alert-success text-center'>Registration successful! Redirecting to login...</div>";
                header("Refresh: 2; url=index.php?payroll=login1");  // Redirect after 2 seconds
                exit();
            } else {
                $msg = "<div class='alert alert-danger text-center'>Something went wrong. Please try again.</div>";
            }
        }
    }
}

// Include the registration view
require views_path("auth/register");
?>
