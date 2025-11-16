<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

session_start();
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

// GET: Fetch pending HR leave applications
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
                CONCAT(admin.hr_first_name, ' ', IFNULL(admin.hr_middle_name, ''), ' ', admin.hr_last_name) as hr_name,
                admin.hr_email as hr_email,
                admin.hr_position as hr_position,
                admin.hr_employee_id as hr_employee_id
            FROM leaves l
            JOIN admins admin ON l.applicant_hr_id = admin.id
            WHERE l.applicant_type = 'hr' 
            AND l.status = 'Pending'
            AND admin.deleted_at IS NULL
            ORDER BY l.created_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $leaves
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
            JOIN admins admin ON l.applicant_hr_id = admin.id
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
            SELECT CONCAT(owner_first_name, ' ', IFNULL(owner_middle_name, ''), ' ', owner_last_name) as owner_name
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
        
        // If approved, deduct from HR's leave credits
        if ($action === 'approve') {
            $leaveTypeMapping = [
                'Sick Leave' => 1,
                'Emergency Leave' => 2,
                'Vacation Leave' => 3,
                'Personal Leave' => 4,
                'Maternity/Paternity Leave' => 5
            ];
            $leaveTypeId = $leaveTypeMapping[$leave['leave_type']] ?? null;
            
            if ($leaveTypeId) {
                // Get or create leave credits for HR
                $creditStmt = $pdo->prepare("
                    SELECT id, taken FROM leave_credits 
                    WHERE hr_id = ? AND leave_type_id = ? AND user_type = 'hr'
                ");
                $creditStmt->execute([$leave['applicant_hr_id'], $leaveTypeId]);
                $credit = $creditStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($credit) {
                    // Update existing credit
                    $updateCreditStmt = $pdo->prepare("
                        UPDATE leave_credits 
                        SET taken = taken + ? 
                        WHERE id = ?
                    ");
                    $updateCreditStmt->execute([$leave['duration'], $credit['id']]);
                } else {
                    // Create new credit record
                    $insertCreditStmt = $pdo->prepare("
                        INSERT INTO leave_credits (hr_id, leave_type_id, taken, user_type)
                        VALUES (?, ?, ?, 'hr')
                    ");
                    $insertCreditStmt->execute([$leave['applicant_hr_id'], $leaveTypeId, $leave['duration']]);
                }
            }
        }
        
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

