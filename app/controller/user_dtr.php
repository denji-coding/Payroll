<?php
require_once '../app/core/session_helper.php';

// Check if employee is logged in
requireEmployeeAuth();

// Log user activity
logUserActivity('Access employee DTR page');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require views_path("user/user_dtr");

