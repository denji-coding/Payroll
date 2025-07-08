<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
require_once __DIR__ . '/../core/database.php';
$db = new Database();
$conn = $db->getConnection();

$employee_id = $_SESSION['employee_id'] ?? null;

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

// ✅ GET: Return HTML leave summary for frontend
if ($method === 'GET') {
    $query = $conn->prepare("SELECT leave_type, allowed, taken FROM leave_credits WHERE employee_id = ?");
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

// ✅ POST: Initialize leave credits (admin use)
if ($method === 'POST') {
    $employee_id = $_POST['employee_id'] ?? null;
    if (!$employee_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Employee ID is required']);
        exit;
    }

    $defaults = [
        ['Sick Leave', 10],
        ['Emergency Leave', 5],
        ['Vacation Leave', 5],
        ['Personal Leave', 5],
        ['Maternity/Paternity Leave', 8],
    ];

    $ok = true;
    foreach ($defaults as [$type, $allowed]) {
        $exists = $db->query("SELECT 1 FROM leave_credits WHERE employee_id = :eid AND leave_type = :lt", [
            ':eid' => $employee_id,
            ':lt' => $type
        ]);

        if (!$exists) {
            $ok = $ok && $db->query(
                "INSERT INTO leave_credits (employee_id, leave_type, allowed, taken) VALUES (:eid, :lt, :alw, 0)",
                [
                    ':eid' => $employee_id,
                    ':lt' => $type,
                    ':alw' => $allowed
                ]
            );
        }
    }

    echo json_encode([
        'status' => $ok ? 'success' : 'error',
        'message' => $ok ? 'Leave credits initialized.' : 'Some inserts failed.'
    ]);
    exit;
}

// ✅ PATCH: Deduct 1 leave credit per leave application
if ($method === 'PATCH') {
    parse_str(file_get_contents("php://input"), $data);
    $employee_id = $data['employee_id'] ?? null;
    $type = $data['leave_type'] ?? null;

    if (!$employee_id || !$type) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
        exit;
    }

    $duration = 1; // ✅ Always deduct only 1 credit per leave application

    $credit = $db->query("SELECT allowed, taken FROM leave_credits WHERE employee_id = :eid AND leave_type = :lt", [
        ':eid' => $employee_id,
        ':lt' => $type
    ]);

    if (!$credit || $credit[0]['allowed'] - $credit[0]['taken'] < $duration) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Insufficient leave credits']);
        exit;
    }

    $update = $db->query("UPDATE leave_credits SET taken = taken + :dur WHERE employee_id = :eid AND leave_type = :lt", [
        ':dur' => $duration,
        ':eid' => $employee_id,
        ':lt' => $type
    ]);

    echo json_encode([
        'status' => $update ? 'success' : 'error',
        'message' => $update ? 'Leave credit deducted' : 'Failed to deduct credit'
    ]);
    exit;
}

// ❌ Unsupported method
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
