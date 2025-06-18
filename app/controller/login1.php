<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../app/core/database.php";

$db = new Database();
$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginType = $_POST['login_type'] ?? 'admin'; // get login type

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Email and password are required.';
        header("Location: index.php?payroll=login1&type=$loginType"); // preserve login type
        exit;
    }

    try {
        if ($loginType === 'admin') {
            // Admin login logic
            $stmt = $db->query("SELECT * FROM admins WHERE email = ?", [$email]);
            $user = $stmt ? $stmt[0] : null;

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['SESSION_EMAIL'] = $email;
                $_SESSION['SESSION_USER_ID'] = $user['id'];
                $_SESSION['USERNAME'] = $user['name'];
                $_SESSION['login_success'] = true;

                header("Location: index.php?payroll=dashboard1");
                exit;
            } else {
                $_SESSION['error'] = 'Invalid admin email or password.';
            }

        } elseif ($loginType === 'employee') {
            // Employee login logic
            $stmt = $db->query("SELECT * FROM employees WHERE email = ? AND employee_no = ?", [$email, $password]);
            $employee = $stmt ? $stmt[0] : null;

            if ($employee) {
                $_SESSION['employee_id'] = $employee['id'];
                $_SESSION['employee_no'] = $employee['employee_no'];
                $_SESSION['email'] = $employee['email'];
                $_SESSION['name'] = $employee['first_name'] . ' ' . $employee['last_name'];
                $_SESSION['position'] = $employee['position'];
                $_SESSION['photo_path'] = $employee['photo_path'];
                $_SESSION['login_success'] = true;

                header('Location: index.php?payroll=user_dashboard');
                exit;
            } else {
                $_SESSION['error'] = 'Invalid employee email or password.';
            }

        } else {
            $_SESSION['error'] = 'Invalid login type.';
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }

    // Redirect back with error and correct login type
    header("Location: index.php?payroll=login1&type=$loginType");
    exit;
}

// Show logout message if set
$loggedOut = false;
if (!empty($_SESSION['logged_out'])) {
    $loggedOut = true;
    unset($_SESSION['logged_out']);
}

// Determine active form type for the view
$loginType = $_GET['type'] ?? 'admin'; // used in view to show correct form

// $_SESSION['login_success'] = true;
// $_SESSION['username'] = $employee['first_name'] . ' ' . $employee['last_name'];

// header('Location: index.php?payroll=user_dashboard');
// exit;


require views_path("auth/login1");
