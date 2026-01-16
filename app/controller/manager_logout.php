<?php
require_once "../app/core/secure_session.php";

secureLogout();

// Redirect to manager login
header("Location: index.php?payroll=login1&type=manager");
exit;
