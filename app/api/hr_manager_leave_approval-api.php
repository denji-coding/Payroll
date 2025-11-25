<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

require_once __DIR__ . '/../core/secure_session.php';
startSecureSession();
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/session_helper.php';

// Check if user is HR/Admin
$hrId = $_SESSION['SESSION_USER_ID'] ?? null;

if (!$hrId) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. HR access required.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

// GET: Fetch manager leave applications (all statuses for display)
if ($method === 'GET') {
    try {
        // Optional filter for status (default: show all)
        $statusFilter = $_GET['status'] ?? null;
        
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
                l.updated_at,
                l.applicant_manager_id,
                l.approver_hr_id,
                l.approver_type,
                l.approver_name,
                CONCAT(
                    UPPER(LEFT(m.m_first_name, 1)), LOWER(SUBSTRING(m.m_first_name, 2)), ' ',
                    IFNULL(CONCAT(UPPER(LEFT(m.m_middle_name, 1)), '. '), ''),
                    UPPER(LEFT(m.m_last_name, 1)), LOWER(SUBSTRING(m.m_last_name, 2))
                ) as manager_name,
                m.m_email as manager_email,
                m.m_position as manager_position,
                m.m_employee_id as manager_employee_id,
                m.m_branch as manager_branch
            FROM leaves l
            INNER JOIN managers m ON l.applicant_manager_id = m.id
            WHERE l.applicant_type = 'manager' 
            AND m.deleted_at IS NULL
        ";
        
        $params = [];
        
        // Add status filter if provided
        if ($statusFilter && in_array($statusFilter, ['Pending', 'Approved', 'Rejected'])) {
            $sql .= " AND l.status = :status";
            $params[':status'] = $statusFilter;
        }
        
        $sql .= " ORDER BY l.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $leaves,
            'count' => count($leaves)
        ]);
    } catch (Exception $e) {
        error_log("HR Manager Leave Approval API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch leave applications: ' . $e->getMessage()
        ]);
    }
    exit;
}

// POST: Approve or reject manager leave
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
                m.m_first_name,
                m.m_middle_name,
                m.m_last_name
            FROM leaves l
            JOIN managers m ON l.applicant_manager_id = m.id
            WHERE l.id = ? AND l.applicant_type = 'manager' AND l.status = 'Pending'
        ");
        $leaveStmt->execute([$leaveId]);
        $leave = $leaveStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$leave) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Leave application not found or already processed']);
            exit;
        }
        
        // Get HR name for approver_name
        $hrStmt = $pdo->prepare("
            SELECT CONCAT(hr_first_name, ' ', IFNULL(hr_middle_name, ''), ' ', hr_last_name) as hr_name
            FROM admins
            WHERE id = ? AND deleted_at IS NULL
        ");
        $hrStmt->execute([$hrId]);
        $hr = $hrStmt->fetch(PDO::FETCH_ASSOC);
        $approverName = $hr['hr_name'] ?? 'HR';
        
        // Update leave status
        $status = $action === 'approve' ? 'Approved' : 'Rejected';
        $updateStmt = $pdo->prepare("
            UPDATE leaves 
            SET status = ?,
                approver_hr_id = ?,
                approver_type = 'hr',
                approver_name = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $updateStmt->execute([$status, $hrId, $approverName, $leaveId]);
        
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
                    WHERE manager_id = ? AND leave_type_id = ? AND user_type = 'manager'
                ");
                $restoreStmt->execute([$leave['applicant_manager_id'], $leaveTypeId]);
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
        error_log("HR Manager Leave Approval API Error: " . $e->getMessage());
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

