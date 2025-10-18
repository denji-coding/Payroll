<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/session_helper.php';

header('Content-Type: application/json');

try {
    // Auth: Allow GET for owner or admin; write operations restricted to owner
    $method = $_SERVER['REQUEST_METHOD'];
    $isReadOnly = ($method === 'GET');
    if (!isOwnerLoggedIn() && !($isReadOnly && function_exists('isAdminLoggedIn') && isAdminLoggedIn())) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $db = new Database();
    $pdo = $db->getConnection();

    // Ensure branches table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS branches (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        address VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_branches_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    // $method already defined above

    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT id, name, address, created_at FROM branches ORDER BY id DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $rows]);
        exit;
    }

    if ($method === 'PATCH' || ($method === 'POST' && (($_GET['method'] ?? '') === 'PATCH'))) {
        // Support method override via query param
        parse_str(file_get_contents('php://input'), $payload);
        if (empty($payload)) { $payload = $_POST; }
        $id = (int)($payload['id'] ?? 0);
        $name = trim($payload['name'] ?? '');
        $address = trim($payload['address'] ?? '');
        if ($id <= 0 || $name === '') {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
            exit;
        }
        $stmt = $pdo->prepare('UPDATE branches SET name = :n, address = :a WHERE id = :id');
        $stmt->execute([':n' => $name, ':a' => $address, ':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Branch updated']);
        exit;
    }

    if ($method === 'DELETE' || ($method === 'POST' && (($_GET['method'] ?? '') === 'DELETE'))) {
        // Support method override via query param
        parse_str(file_get_contents('php://input'), $payload);
        if (empty($payload)) { $payload = $_POST; }
        $id = (int)($payload['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Invalid branch id']);
            exit;
        }
        $stmt = $pdo->prepare('DELETE FROM branches WHERE id = :id');
        $stmt->execute([':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Branch deleted']);
        exit;
    }

    if ($method === 'POST' && empty($_GET['method'])) {
        $input = $_POST;
        $name = trim($input['name'] ?? '');
        $address = trim($input['address'] ?? '');
        if ($name === '') {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Branch name is required']);
            exit;
        }
        $stmt = $pdo->prepare('INSERT INTO branches (name, address) VALUES (:n, :a)');
        $stmt->execute([':n' => $name, ':a' => $address]);
        echo json_encode(['status' => 'success', 'message' => 'Branch added', 'id' => $pdo->lastInsertId()]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error', 'detail' => $e->getMessage()]);
}
?>

