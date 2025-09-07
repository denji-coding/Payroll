<?php
require_once "../app/core/secure_session.php";

secureLogout();

// Redirect to admin login
header("Location: index.php?payroll=login_admin");
exit;
