<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

require_once __DIR__ . '/../core/secure_session.php';
startSecureSession();
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/session_helper.php';

// Check if user is Owner
$ownerId = $_SESSION['owner_id'] ?? null;

if (!$ownerId) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Owner access required.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

// GET: Fetch HR leave applications (all statuses for display)
if ($method === 'GET') {
    try {
        $sql = "
            SELECT 
                l.id,
                l.leave_type,
                l.start_date,
                l.end_date,
                l.duration,
                l.reason,
                l.med_cert_path,
                l.status,
                l.created_at,
                l.applicant_hr_id,
                l.approver_owner_id,
                l.approver_type,
                l.approver_name,
                CONCAT(
                    UPPER(LEFT(admin.hr_first_name, 1)), LOWER(SUBSTRING(admin.hr_first_name, 2)), ' ',
                    IFNULL(CONCAT(UPPER(LEFT(admin.hr_middle_name, 1)), '. '), ''),
                    UPPER(LEFT(admin.hr_last_name, 1)), LOWER(SUBSTRING(admin.hr_last_name, 2))
                ) as hr_name,
                admin.hr_email as hr_email,
                admin.hr_position as hr_position,
                admin.hr_employee_id as hr_employee_id
            FROM leaves l
            LEFT JOIN admins admin ON l.applicant_hr_id = admin.id AND admin.deleted_at IS NULL
            WHERE l.applicant_type = 'hr' 
            AND l.applicant_hr_id IS NOT NULL
            ORDER BY l.created_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log query results
        error_log("Owner HR Leave Approval API: Found " . count($leaves) . " HR leave applications");
        
        echo json_encode([
            'status' => 'success',
            'data' => $leaves,
            'count' => count($leaves)
        ]);
    } catch (Exception $e) {
        error_log("Owner HR Leave Approval API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch leave applications: ' . $e->getMessage()
        ]);
    }
    exit;
}

// POST: Approve or reject HR leave
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['action']) || !isset($input['leave_id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }
    
    $action = $input['action']; // 'approve' or 'reject'
    $leaveId = intval($input['leave_id']);
    $remarks = $input['remarks'] ?? '';
    
    if (!in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get leave details
        $leaveStmt = $pdo->prepare("
            SELECT 
                l.*,
                admin.hr_first_name,
                admin.hr_middle_name,
                admin.hr_last_name
            FROM leaves l
            LEFT JOIN admins admin ON l.applicant_hr_id = admin.id AND admin.deleted_at IS NULL
            WHERE l.id = ? AND l.applicant_type = 'hr' AND l.status = 'Pending'
        ");
        $leaveStmt->execute([$leaveId]);
        $leave = $leaveStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$leave) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Leave application not found or already processed']);
            exit;
        }
        
        // Get Owner name for approver_name
        $ownerStmt = $pdo->prepare("
            SELECT name as owner_name
            FROM owners
            WHERE id = ?
        ");
        $ownerStmt->execute([$ownerId]);
        $owner = $ownerStmt->fetch(PDO::FETCH_ASSOC);
        $approverName = $owner['owner_name'] ?? 'Owner';
        
        // Update leave status
        $status = $action === 'approve' ? 'Approved' : 'Rejected';
        $updateStmt = $pdo->prepare("
            UPDATE leaves 
            SET status = ?,
                approver_owner_id = ?,
                approver_type = 'owner',
                approver_name = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $updateStmt->execute([$status, $ownerId, $approverName, $leaveId]);
        
        // Handle credit restoration on rejection
        if ($action === 'reject') {
            $leaveTypeMapping = [
                'Sick Leave' => 1,
                'Emergency Leave' => 2,
                'Vacation Leave' => 3,
                'Personal Leave' => 4,
                'Maternity/Paternity Leave' => 5
            ];
            $leaveTypeId = $leaveTypeMapping[$leave['leave_type']] ?? null;
            
            if ($leaveTypeId) {
                // Restore 1 credit when rejecting a leave (credits are deducted per application, not per day)
                $restoreStmt = $pdo->prepare("
                    UPDATE leave_credits 
                    SET taken = GREATEST(0, taken - 1), updated_at = CURRENT_TIMESTAMP
                    WHERE hr_id = ? AND leave_type_id = ? AND user_type = 'hr'
                ");
                $restoreStmt->execute([$leave['applicant_hr_id'], $leaveTypeId]);
            }
        }
        // Note: Credits are already deducted when the leave application was submitted
        // No need to deduct again on approval
        
        $pdo->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Leave application ' . strtolower($status) . ' successfully'
        ]);
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Owner HR Leave Approval API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to process leave application: ' . $e->getMessage()
        ]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);

