<?php
session_start();

// Check user type
if (isset($_SESSION['SESSION_EMAIL'])) {
    // Admin logout
    unset($_SESSION['SESSION_EMAIL']);
    unset($_SESSION['SESSION_USER_ID']);
    unset($_SESSION['USERNAME']);
    $_SESSION['logged_out'] = true;
    header("Location: index.php?payroll=login_admin");
    exit;

} elseif (isset($_SESSION['employee_id'])) {
    // Employee logout
    unset($_SESSION['employee']);
    unset($_SESSION['employee_id']);
    unset($_SESSION['employee_no']);
    unset($_SESSION['email']);
    unset($_SESSION['name']);
    unset($_SESSION['position']);
    unset($_SESSION['photo_path']);
    $_SESSION['logged_out'] = true;
    header("Location: index.php?payroll=login1&type=employee");
    exit;

} elseif (isset($_SESSION['manager_id'])) {
    // Manager logout
    unset($_SESSION['manager_id']);
    unset($_SESSION['manager_name']);
    $_SESSION['logged_out'] = true;
    header("Location: index.php?payroll=login1&type=manager");
    exit;

} else {
    // Fallback
    header("Location: index.php?payroll=login1");
    exit;
}


