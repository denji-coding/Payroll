<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
require_once __DIR__ . '/../core/database.php';
$db = new Database();
$conn = $db->getConnection();

// Support both employee_id (PK) and employee_no fallback if used elsewhere
$employee_id = $_SESSION['employee_id'] ?? ($_SESSION['employee_no'] ?? null);

if (!$employee_id) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ✅ GET: Return HTML leave summary for frontend (normalized schema)
if ($method === 'GET') {
    $query = $conn->prepare(
        "SELECT 
            lt.name AS leave_type,
            lt.default_allowed AS allowed,
            COALESCE(lc.taken, 0) AS taken
         FROM leave_types lt
         LEFT JOIN leave_credits lc
           ON lc.leave_type_id = lt.id AND lc.employee_id = ?
         WHERE lt.is_active = TRUE
         ORDER BY lt.name"
    );
    $query->execute([$employee_id]);
    $credits = $query->fetchAll(PDO::FETCH_ASSOC);

    function getBorderColorClass($type) {
        return match ($type) {
            'Sick Leave' => 'border-l-blue-500',
            'Emergency Leave' => 'border-l-red-500',
            'Vacation Leave' => 'border-l-yellow-500',
            'Personal Leave' => 'border-l-purple-500',
            'Maternity/Paternity Leave' => 'border-l-green-500',
            default => 'border-l-gray-400',
        };
    }

    ob_start();
    if ($credits):
        foreach ($credits as $row):
            $type = htmlspecialchars($row['leave_type']);
            $allowed = (int)$row['allowed'];
            $taken = (int)$row['taken'];
            $remaining = max(0, $allowed - $taken);
            $borderColor = getBorderColorClass($type);
            ?>
            <div class="bg-white rounded-lg shadow-sm p-3 hover:shadow-md transition text-center border-l-4 <?= $borderColor ?>">
                <span class="text-xs sm:text-sm font-medium text-gray-600"><?= $type ?></span>
                <p class="text-lg sm:text-base mt-2 font-bold text-green-600"><?= $remaining ?></p>
            </div>
        <?php
        endforeach;
    else:
        echo '<div class="text-muted text-sm">No leave credit records found.</div>';
    endif;

    echo ob_get_clean();
    exit;
}

// ✅ POST: Initialize leave credits (admin use) for all active leave types
if ($method === 'POST') {
    $empIdForInit = $_POST['employee_id'] ?? null;
    if (!$empIdForInit) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Employee ID is required']);
        exit;
    }

    // Insert missing leave_credits rows for each active leave type
    $typesStmt = $conn->prepare("SELECT id FROM leave_types WHERE is_active = TRUE");
    $typesStmt->execute();
    $types = $typesStmt->fetchAll(PDO::FETCH_COLUMN, 0);

    $ok = true;
    foreach ($types as $typeId) {
        $existsStmt = $conn->prepare("SELECT 1 FROM leave_credits WHERE employee_id = ? AND leave_type_id = ?");
        $existsStmt->execute([$empIdForInit, $typeId]);
        if (!$existsStmt->fetchColumn()) {
            $ins = $conn->prepare("INSERT INTO leave_credits (employee_id, leave_type_id, taken) VALUES (?, ?, 0)");
            $ok = $ok && $ins->execute([$empIdForInit, $typeId]);
        }
    }

    echo json_encode([
        'status' => $ok ? 'success' : 'error',
        'message' => $ok ? 'Leave credits initialized.' : 'Some inserts failed.'
    ]);
    exit;
}

// ✅ PATCH: Deduct leave credits (supports leave_type name or leave_type_id)
if ($method === 'PATCH') {
    parse_str(file_get_contents("php://input"), $data);
    $emp = $data['employee_id'] ?? null;
    $leaveTypeName = $data['leave_type'] ?? null;
    $leaveTypeId = $data['leave_type_id'] ?? null;
    $duration = isset($data['duration']) ? max(0, (int)$data['duration']) : 1;

    if (!$emp || (!$leaveTypeName && !$leaveTypeId) || $duration <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
        exit;
    }

    // Resolve leave_type_id
    if (!$leaveTypeId && $leaveTypeName) {
        $ltStmt = $conn->prepare("SELECT id FROM leave_types WHERE name = ? AND is_active = TRUE");
        $ltStmt->execute([$leaveTypeName]);
        $leaveTypeId = $ltStmt->fetchColumn();
        if (!$leaveTypeId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid leave type']);
            exit;
        }
    }

    // Ensure a leave_credits row exists
    $existsStmt = $conn->prepare("SELECT taken FROM leave_credits WHERE employee_id = ? AND leave_type_id = ?");
    $existsStmt->execute([$emp, $leaveTypeId]);
    $credit = $existsStmt->fetch(PDO::FETCH_ASSOC);
    if (!$credit) {
        $ins = $conn->prepare("INSERT INTO leave_credits (employee_id, leave_type_id, taken) VALUES (?, ?, 0)");
        $ins->execute([$emp, $leaveTypeId]);
        $credit = ['taken' => 0];
    }

    // Get default allowed for the type
    $allowedStmt = $conn->prepare("SELECT default_allowed FROM leave_types WHERE id = ?");
    $allowedStmt->execute([$leaveTypeId]);
    $defaultAllowed = (int)$allowedStmt->fetchColumn();

    $available = $defaultAllowed - (int)$credit['taken'];
    if ($available < $duration) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Insufficient leave credits. Available: {$available}, Requested: {$duration}"]);
        exit;
    }

    $upd = $conn->prepare("UPDATE leave_credits SET taken = taken + ? WHERE employee_id = ? AND leave_type_id = ?");
    $ok = $upd->execute([$duration, $emp, $leaveTypeId]);

    echo json_encode([
        'status' => $ok ? 'success' : 'error',
        'message' => $ok ? 'Leave credit deducted' : 'Failed to deduct credit'
    ]);
    exit;
}

// ❌ Unsupported method
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
