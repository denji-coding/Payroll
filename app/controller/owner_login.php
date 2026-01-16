<?php
require_once '../app/core/session_helper.php';
require_once '../app/core/Database.php';

// If already logged in as owner, go to dashboard
if (isOwnerLoggedIn()) {
    header('Location: index.php?payroll=owner_dashboard');
    exit;
}

// Handle POST (owner login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['o_email'] ?? '');
    $password = $_POST['o_password'] ?? '';

    if ($email === '' || $password === '') {
        $_SESSION['error'] = 'Email and password are required.';
        header('Location: index.php?payroll=owner_login');
        exit;
    }

    try {
        $db = new Database();
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare('SELECT id, name, email, password, photo_path FROM owners WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($owner) {
            $hashed = $owner['password'] ?? '';
            $isValid = false;

            if (stripos($hashed, 'argon2') !== false || str_starts_with($hashed, '$2y$')) {
                $isValid = password_verify($password, $hashed);
            } else {
                // legacy plain text fallback
                $isValid = hash_equals($hashed, $password);
                if ($isValid) {
                    // rehash to Argon2id
                    $newHash = password_hash($password, PASSWORD_ARGON2ID);
                    $upd = $pdo->prepare('UPDATE owners SET password = :ph WHERE id = :id');
                    $upd->execute([':ph' => $newHash, ':id' => $owner['id']]);
                }
            }

            if ($isValid) {
                $_SESSION['owner_id'] = $owner['id'];
                $_SESSION['owner_name'] = $owner['name'];
                $_SESSION['owner_email'] = $owner['email'];
                $_SESSION['photo_path'] = $owner['photo_path'] ?? null;
                $_SESSION['user_type'] = 'owner';

                logUserActivity('Owner login success');
                $_SESSION['flash_owner_login_success'] = true;
                header('Location: index.php?payroll=owner_dashboard');
                exit;
            }
        }

        $_SESSION['error'] = 'Invalid email or password.';
        logUserActivity('Owner login failed', 'email=' . $email);
        header('Location: index.php?payroll=owner_login');
        exit;
    } catch (Throwable $e) {
        error_log('Owner login error: ' . $e->getMessage());
        $_SESSION['error'] = 'An error occurred. Please try again.';
        header('Location: index.php?payroll=owner_login');
        exit;
    }
}

// GET: render view
logUserActivity('Access owner login page');
require views_path('owner/owner_login');

?>