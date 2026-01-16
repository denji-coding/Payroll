<?php
require_once '../app/core/session_helper.php';

// Check if manager is logged in
requireManagerAuth();

// Log user activity
logUserActivity('Access manager dashboard');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require views_path("branch/manager_dashboard");