<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/session_helper.php';
require_once __DIR__ . '/../core/Database.php';

date_default_timezone_set('Asia/Manila');

function respond_json($payload, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function redirect_back($ok, $message) {
    $_SESSION['profile_flash'] = [ 'ok' => $ok, 'message' => $message ];
    header('Location: ../../public/index.php?payroll=admin_profile');
    exit;
}

// Auth guard (admin)
if (!isset($_SESSION['SESSION_EMAIL'])) {
    if (!headers_sent()) {
        header('Location: ../../public/index.php?payroll=login1&type=admin');
    }
    exit;
}

// Helper: get current admin row
function get_current_admin(PDO $pdo) {
    $adminId = $_SESSION['admin_id'] ?? null;
    $email = $_SESSION['SESSION_EMAIL'] ?? null;
    if ($adminId) {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) return $admin;
    }
    if ($email) {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

$db = new Database();
$pdo = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
          (isset($_POST['ajax']) && $_POST['ajax'] === '1');

// Ensure photo_path column exists (defensive)
try {
    $res = $pdo->query("SHOW COLUMNS FROM admins LIKE 'photo_path'");
    if ($res && $res->rowCount() === 0) {
        $pdo->exec("ALTER TABLE admins ADD COLUMN photo_path VARCHAR(255) NULL AFTER email");
    }
} catch (Throwable $e) {
    // continue
}

if ($method !== 'POST') {
    if ($isAjax) {
        respond_json(['ok' => false, 'message' => 'Method not allowed'], 405);
    } else {
        respond_json(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
}

$admin = get_current_admin($pdo);
if (!$admin) {
    if ($isAjax) {
        respond_json(['ok' => false, 'message' => 'Admin not found in session'], 403);
    } else {
        respond_json(['status' => 'error', 'message' => 'Admin not found in session'], 403);
    }
}

// Change password flow
if ($action === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        if ($isAjax) return respond_json(['ok' => false, 'message' => 'Passwords do not match.'], 400);
        redirect_back(false, 'Passwords do not match.');
    }
    if (strlen($password) < 8) {
        if ($isAjax) return respond_json(['ok' => false, 'message' => 'Password must be at least 8 characters.'], 400);
        redirect_back(false, 'Password must be at least 8 characters.');
    }
    $stored = $admin['password'] ?? '';
    $isHashed = is_string($stored) && preg_match('/^\$2y\$|^\$argon2id\$|^\$argon2i\$/', $stored);
    $valid = $isHashed ? password_verify($current, $stored) : hash_equals($stored, $current);
    if (!$valid) {
        if ($isAjax) return respond_json(['ok' => false, 'message' => 'Current password is incorrect.'], 400);
        redirect_back(false, 'Current password is incorrect.');
    }

    $hashAlgo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    $newHash = password_hash($password, $hashAlgo);
    $stmt = $pdo->prepare('UPDATE admins SET password = ? WHERE id = ?');
    $stmt->execute([$newHash, $admin['id']]);

    if ($isAjax) return respond_json(['ok' => true, 'message' => 'Password updated successfully.']);
    redirect_back(true, 'Password updated successfully.');
}

// Profile info + avatar upload
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if ($fullName === '' || $email === '') {
    if ($isAjax) return respond_json(['ok' => false, 'message' => 'Full name and email are required.'], 400);
    redirect_back(false, 'Full name and email are required.');
}

// Validate email unique (if changed)
if (strcasecmp($email, $admin['email']) !== 0) {
    $check = $pdo->prepare('SELECT id FROM admins WHERE email = ? AND id <> ? LIMIT 1');
    $check->execute([$email, $admin['id']]);
    if ($check->fetch()) {
        if ($isAjax) return respond_json(['ok' => false, 'message' => 'Email is already in use.'], 400);
        redirect_back(false, 'Email is already in use.');
    }
}

$photoPathDb = $admin['photo_path'] ?? null;

if (isset($_FILES['profile_picture']) && is_uploaded_file($_FILES['profile_picture']['tmp_name'])) {
    $file = $_FILES['profile_picture'];
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowed[$mime])) {
        if ($isAjax) return respond_json(['ok' => false, 'message' => 'Invalid image type. Allowed: JPG, PNG, WEBP.'], 400);
        redirect_back(false, 'Invalid image type. Allowed: JPG, PNG, WEBP.');
    }
    if ($file['size'] > (2 * 1024 * 1024)) { // 2MB
        if ($isAjax) return respond_json(['ok' => false, 'message' => 'Image is too large (max 2MB).'], 400);
        redirect_back(false, 'Image is too large (max 2MB).');
    }

    $ext = $allowed[$mime];
    $safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($file['name'], PATHINFO_FILENAME));
    $finalName = $safeName . '-' . uniqid('', true) . '.' . $ext;

    $uploadDir = realpath(__DIR__ . '/../../public') . DIRECTORY_SEPARATOR . 'upload';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }
    $targetFs = $uploadDir . DIRECTORY_SEPARATOR . $finalName;
    if (!move_uploaded_file($file['tmp_name'], $targetFs)) {
        if ($isAjax) return respond_json(['ok' => false, 'message' => 'Failed to upload image.'], 500);
        redirect_back(false, 'Failed to upload image.');
    }
    // Store as relative path from public root
    $photoPathDb = 'upload/' . $finalName;
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('UPDATE admins SET name = ?, email = ?, ' . ($phone !== '' ? 'phone = ?,' : '') . ' photo_path = COALESCE(?, photo_path) WHERE id = ?');
    if ($phone !== '') {
        $stmt->execute([$fullName, $email, $phone, $photoPathDb, $admin['id']]);
    } else {
        $stmt = $pdo->prepare('UPDATE admins SET name = ?, email = ?, photo_path = COALESCE(?, photo_path) WHERE id = ?');
        $stmt->execute([$fullThis = $fullName, $email, $photoPathDb, $admin['id']]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    if ($isAjax) return respond_json(['ok' => false, 'message' => 'Failed to update profile.'], 500);
    redirect_back(false, 'Failed to update profile.');
}

// Sync session data
$_SESSION['USERNAME'] = $fullName;
$_SESSION['SESSION_EMAIL'] = $email;
if (!empty($phone)) $_SESSION['phone'] = $phone;
if (!empty($photoPathDb)) $_SESSION['photo_path'] = $photoPathDb;

if ($isAjax) {
    respond_json([
        'ok' => true,
        'message' => 'Profile updated successfully.',
        'data' => [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'photo_path' => $photoPathDb,
        ],
    ]);
}

redirect_back(true, 'Profile updated successfully.');


