<?php
require_once '../app/core/session_helper.php';

// Check if admin is logged in
requireAdminAuth();

// Log user activity
logUserActivity('Access owner account page');

require_once '../app/core/database.php';

require views_path("auth/ownerController/owner_account");

?>