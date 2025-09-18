<?php
require_once __DIR__ . '/../core/secure_session.php';
require_once __DIR__ . '/../core/session_helper.php';
require_once __DIR__ . '/../core/password_reset.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!validateSession() || getCurrentUserType() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$userType = $_POST['user_type'] ?? '';
$email = trim($_POST['email'] ?? '');

if (!in_array($userType, ['employee', 'manager'], true) || $email === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid inputs']);
    exit;
}

$user = pr_find_user_by_email($userType, $email);
if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$token = pr_create_reset_token($userType, (int)$user['id'], 10);
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
$resetUrl = $base . '/reset-password.php?token=' . urlencode($token);

echo json_encode(['success' => true, 'reset_url' => $resetUrl]);
exit;
?>












