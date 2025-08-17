<?php
require_once '../app/core/session_helper.php';

// Check if admin is logged in
requireAdminAuth();

// Log user activity
logUserActivity('Access payroll page');

// Load the payroll view
require views_path("auth/payroll");
