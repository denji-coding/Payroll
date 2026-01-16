<?php
require_once '../app/core/session_helper.php';

// Require owner auth
requireOwnerAuth();

logUserActivity('Access owner dashboard');

require views_path('owner/owner_dashboard');
?>

