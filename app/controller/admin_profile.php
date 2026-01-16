<?php
require_once '../app/core/session_helper.php';
// Database not needed here

// Enforce admin authentication
if (function_exists('requireAdminAuth')) {
    requireAdminAuth();
} else {
    if (!isset($_SESSION['SESSION_EMAIL'])) {
        header('Location: index.php?payroll=login1&type=admin');
        exit();
    }
}

// Optional: activity log
if (function_exists('logUserActivity')) {
    logUserActivity('Access admin profile settings');
}

date_default_timezone_set('Asia/Manila');

// Hydrate session profile data from DB if missing or stale
try {
    require_once '../app/core/Database.php';
    $db = new Database();
    $pdo = $db->getConnection();

    // Ensure photo_path column exists (defensive)
    $res = $pdo->query("SHOW COLUMNS FROM admins LIKE 'photo_path'");
    if ($res && $res->rowCount() === 0) {
        $pdo->exec("ALTER TABLE admins ADD COLUMN photo_path VARCHAR(255) NULL AFTER email");
    }

    $email = $_SESSION['SESSION_EMAIL'] ?? null;
    if ($email) {
        $stmt = $pdo->prepare('SELECT id, name, email, phone, photo_path FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['USERNAME'] = $row['name'] ?: ($_SESSION['USERNAME'] ?? '');
            $_SESSION['SESSION_EMAIL'] = $row['email'];
            if (!empty($row['phone'])) {
                $_SESSION['phone'] = $row['phone'];
            }
            if (!empty($row['photo_path'])) {
                $_SESSION['photo_path'] = $row['photo_path'];
            }
        }
    }
} catch (Throwable $e) {
    // ignore and proceed; view will fallback to defaults
}

$title = 'Admin Profile Settings';

require views_path('auth/admin_profile');
?>

