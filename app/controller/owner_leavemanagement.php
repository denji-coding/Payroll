<?php
require_once '../app/core/session_helper.php';

// Require owner authentication
requireOwnerAuth();

logUserActivity('Access owner leave management');

require views_path('owner/owner_leavemanagement');
?>



