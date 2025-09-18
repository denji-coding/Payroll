<?php
require_once __DIR__ . '/database.php';

function pr_get_pdo() {
    static $pdo = null;
    if ($pdo === null) {
        $db = new Database();
        $pdo = $db->getConnection();
    }
    return $pdo;
}

function pr_ensure_table() {
    $pdo = pr_get_pdo();
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_type ENUM('employee','manager') NOT NULL,
        user_id INT NOT NULL,
        token VARCHAR(128) NOT NULL UNIQUE,
        verification_code VARCHAR(10) DEFAULT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME DEFAULT NULL,
        verified_at DATETIME DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX(user_type, user_id),
        INDEX(token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
}

function pr_generate_token(): string {
    return bin2hex(random_bytes(32));
}

function pr_generate_code(): string {
    return (string)random_int(100000, 999999);
}

function pr_create_reset_token(string $userType, int $userId, int $ttlMinutes = 10): string {
    pr_ensure_table();
    $pdo = pr_get_pdo();
    $token = pr_generate_token();
    $code = pr_generate_code();
    $expires = (new DateTime('+'.((int)$ttlMinutes).' minutes'))->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO password_resets (user_type, user_id, token, verification_code, expires_at) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userType, $userId, $token, $code, $expires]);
    return $token;
}

function pr_find_token(string $token) {
    pr_ensure_table();
    $pdo = pr_get_pdo();
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ? LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return null;
    if (!empty($row['used_at'])) return null;
    if (new DateTime($row['expires_at']) < new DateTime()) return null;
    return $row;
}

function pr_consume_token(string $token): void {
    $pdo = pr_get_pdo();
    $stmt = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE token = ?');
    $stmt->execute([$token]);
}

function pr_get_verification_code(string $token): ?string {
    $pdo = pr_get_pdo();
    $stmt = $pdo->prepare('SELECT verification_code FROM password_resets WHERE token = ? AND used_at IS NULL LIMIT 1');
    $stmt->execute([$token]);
    $code = $stmt->fetchColumn();
    return $code !== false ? $code : null;
}

function pr_mark_verified(string $token): bool {
    $pdo = pr_get_pdo();
    $stmt = $pdo->prepare('UPDATE password_resets SET verified_at = NOW() WHERE token = ? AND used_at IS NULL');
    return $stmt->execute([$token]);
}

function pr_is_verified(string $token): bool {
    $pdo = pr_get_pdo();
    $stmt = $pdo->prepare('SELECT verified_at FROM password_resets WHERE token = ? AND used_at IS NULL LIMIT 1');
    $stmt->execute([$token]);
    $ts = $stmt->fetchColumn();
    return !empty($ts);
}

function pr_update_user_password(string $userType, int $userId, string $newPasswordHash): bool {
    $pdo = pr_get_pdo();
    if ($userType === 'employee') {
        // For employees, we need to add a password column first or use employee_no
        // Let's add the password column if it doesn't exist
        try {
            $pdo->exec("ALTER TABLE employees ADD COLUMN password VARCHAR(255) DEFAULT NULL");
        } catch (PDOException $e) {
            // Column might already exist, ignore error
        }
        $stmt = $pdo->prepare('UPDATE employees SET password = ? WHERE id = ?');
    } elseif ($userType === 'manager') {
        $stmt = $pdo->prepare('UPDATE managers SET m_password = ? WHERE id = ?');
    } else {
        return false;
    }
    return $stmt->execute([$newPasswordHash, $userId]);
}

function pr_find_user_by_email(string $userType, string $email) {
    $pdo = pr_get_pdo();
    if ($userType === 'employee') {
        $stmt = $pdo->prepare('SELECT id, email FROM employees WHERE email = ? LIMIT 1');
    } else {
        $stmt = $pdo->prepare('SELECT id, email FROM managers WHERE email = ? LIMIT 1');
    }
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

?>












