<?php
require_once __DIR__ . '/../core/secure_session.php';
require_once __DIR__ . '/../core/password_reset.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? '';

if ($password === '' || $password !== $confirm || strlen($password) < 8) {
    http_response_code(400);
    echo 'Invalid password';
    exit;
}

$row = pr_find_token($token);
if (!$row) {
    http_response_code(400);
    echo 'Invalid or expired token';
    exit;
}

if (!pr_is_verified($token)) {
    http_response_code(400);
    echo 'Token not verified';
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
if (!pr_update_user_password($row['user_type'], (int)$row['user_id'], $hash)) {
    http_response_code(500);
    echo 'Failed to update password';
    exit;
}

pr_consume_token($token);

// Redirect based on user type with success message
$userType = $_POST['user_type'] ?? 'employee';
if ($userType === 'manager') {
    header('Location: /mvcPayroll/public/index.php?payroll=login1&type=manager&reset_success=1');
} else {
    header('Location: /mvcPayroll/public/index.php?payroll=login1&type=employee&reset_success=1');
}
exit;
?>












