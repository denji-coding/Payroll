<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../app/core/SecureAuth.php";
require_once "../app/core/secure_session.php";

$auth = new SecureAuth();
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
            $result = $auth->authenticateAdmin($email, $password);
            
            if ($result['success']) {
                header("Location: " . $result['redirect']);
                exit;
            } else {
                $_SESSION['error'] = $result['message'];
            }

        } elseif ($loginType === 'employee') {
            // Employee login logic
            $result = $auth->authenticateEmployee($email, $password);
            
            if ($result['success']) {
                header("Location: " . $result['redirect']);
                exit;
            } else {
                $_SESSION['error'] = $result['message'];
            }

        } else {
            $_SESSION['error'] = 'Invalid login type.';
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = 'Authentication error. Please try again.';
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
