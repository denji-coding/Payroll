<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
require_once __DIR__ . '/../core/database.php';
$db = new Database();
// Fix session variable handling
$employee_id = $_SESSION['employee_id'] ?? $_SESSION['employee_no'] ?? null;

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

// ✅ GET: Return HTML leave rows
if ($method === 'GET') {
    $sql = "SELECT * FROM leaves WHERE employee_id = :eid ORDER BY created_at DESC";
    $leaves = $db->query($sql, [':eid' => $employee_id]) ?: [];

    ob_start(); // capture HTML
    if ($leaves):
        foreach ($leaves as $leave):
            $id = $leave['id'];
            $type = htmlspecialchars($leave['leave_type']);
            $start = htmlspecialchars($leave['start_date']);
            $end = htmlspecialchars($leave['end_date']);
            $reason = $leave['reason'];
            $medCert = $leave['med_cert_path'];
            $duration = (int)$leave['duration'];
            $status = $leave['status'];

            $hasReason = !empty($reason);
            $hasMedCert = !empty($medCert) && $type === 'Sick Leave' && $duration >= 3;
            $alwaysShowModalTypes = ['Vacation Leave', 'Maternity/Paternity Leave'];
            $showModal = $hasReason || $hasMedCert || in_array($type, $alwaysShowModalTypes);
            ?>
            <tr>
                <td><?= $type ?></td>
                <td><?= $start ?></td>
                <td><?= $end ?></td>
                <td class="text-center">
                    <?php if ($showModal): ?>
                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#leaveReasonModal<?= $id ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="modal fade" id="leaveReasonModal<?= $id ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content mx-auto" style="width: 90vh; max-height: 80vh; overflow-y: auto;">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title">Leave Details</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body px-3 py-3 d-flex flex-column gap-3">
                                        <?php if ($hasReason): ?>
                                            <div class="text-start"><?= nl2br(htmlspecialchars($reason)) ?></div>
                                        <?php endif; ?>
                                        <?php if ($hasMedCert): ?>
                                            <div class="text-left">
                                                <strong>Medical Certificate:</strong>
                                                <div class="d-flex justify-content-center mt-2">
                                                    <img src="<?= htmlspecialchars($medCert) ?>" alt="Medical Certificate" class="img-fluid rounded border" style="max-height: 280px; max-width: 100%;">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!$hasReason && !$hasMedCert && in_array($type, $alwaysShowModalTypes)): ?>
                                            <div class="fst-italic text-muted">No reason or certificate provided.</div>
                                        <?php endif; ?>
                                        <div class="pt-2">
                                            <hr class="w-100 m-0">
                                            <small class="text-muted">Leave type: <?= $type ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="text-muted fst-italic">N/A</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <span class="badge <?= $status === 'Approved' ? 'bg-success' : ($status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                        <?= $status ?>
                    </span>
                </td>
                <td class="text-center">
                    <?php if ($status === 'Pending'): ?>
                        <button class="btn btn-sm btn-outline-danger delete-leave-btn" data-id="<?= $id ?>" data-type="<?= $type ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach;
    else:
        echo '<tr><td colspan="6" class="text-center text-muted py-3">No leave applications yet.</td></tr>';
    endif;

    echo ob_get_clean();
    exit;
}

// ✅ POST: Submit leave
if ($method === 'POST') {
    $leave_type = $_POST['leave_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    $med_cert_path = null;

    $valid = ['Sick Leave', 'Emergency Leave', 'Vacation Leave', 'Personal Leave', 'Maternity/Paternity Leave'];
    if (!in_array($leave_type, $valid)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid leave type']);
        exit;
    }
    if (!$start_date || !$end_date || $end_date < $start_date) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid date range']);
        exit;
    }

    $duration = (new DateTime($start_date))->diff(new DateTime($end_date))->days + 1;

    // 🩺 Medical Certificate Required
    if ($leave_type === 'Sick Leave' && $duration >= 3) {
        if (!isset($_FILES['med_cert']) || $_FILES['med_cert']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Medical certificate required for 3+ days']);
            exit;
        }

        $file = $_FILES['med_cert'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];

        if (!in_array($ext, $allowed)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
            exit;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'File too large (max 2MB)']);
            exit;
        }

        $newName = uniqid('med_', true) . '.' . $ext;
        $uploadDir = dirname(__DIR__, 2) . '/public/upload/med_cert/';
        $target = $uploadDir . $newName;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save medical certificate']);
            exit;
        }

        $med_cert_path = 'upload/med_cert/' . $newName;
        $reason = '';
    }

    // ✏️ Reason Required
    if (in_array($leave_type, ['Emergency Leave', 'Personal Leave']) || ($leave_type === 'Sick Leave' && $duration <= 2)) {
        if (empty($reason)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Reason is required']);
            exit;
        }
    }

    $sql = "INSERT INTO leaves (employee_id, leave_type, start_date, end_date, duration, reason, med_cert_path)
            VALUES (:eid, :lt, :sd, :ed, :dur, :rs, :mp)";
    $params = [
        ':eid' => $employee_id,
        ':lt' => $leave_type,
        ':sd' => $start_date,
        ':ed' => $end_date,
        ':dur' => $duration,
        ':rs' => $reason,
        ':mp' => $med_cert_path
    ];

    $ok = $db->query($sql, $params);
    echo json_encode([
        'status' => $ok ? 'success' : 'error',
        'message' => $ok ? 'Leave submitted.' : 'Failed to submit leave.'
    ]);
    exit;
}

// ✅ DELETE: Remove pending leave
if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $input);
    $id = intval($input['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID required']);
        exit;
    }

    $check = $db->query("SELECT 1 FROM leaves WHERE id = :id AND employee_id = :eid AND status = 'Pending'", [
        ':id' => $id, ':eid' => $employee_id
    ]);

    if (!$check) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Cannot delete this leave']);
        exit;
    }

    $delete = $db->query("DELETE FROM leaves WHERE id = :id AND employee_id = :eid", [
        ':id' => $id, ':eid' => $employee_id
    ]);

    echo json_encode([
        'status' => $delete ? 'success' : 'error',
        'message' => $delete ? 'Deleted.' : 'Delete failed.'
    ]);
    exit;
}

// ❌ If method not allowed
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
