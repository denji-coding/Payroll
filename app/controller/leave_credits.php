<?php
require_once __DIR__ . '/../core/AuthMiddleware.php';
require_once __DIR__ . '/../core/database.php';

// Apply authentication middleware
requireAuth();

// Check if user is admin or manager
if (!isAdminLoggedIn() && !isManagerLoggedIn()) {
    header("Location: index.php?payroll=unauthorized");
    exit();
}

$db = new Database();
$pdo = $db->getConnection();

// Handle AJAX refresh request
if (isset($_GET['action']) && $_GET['action'] === 'refresh_data' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    
    try {
        // Fetch updated data
        $employeesStmt = $pdo->prepare("SELECT id, employee_no, first_name, last_name FROM employees WHERE deleted_at IS NULL ORDER BY first_name, last_name");
        $employeesStmt->execute();
        $employees = $employeesStmt->fetchAll(PDO::FETCH_ASSOC);

        $leaveTypesStmt = $pdo->prepare("SELECT * FROM leave_types WHERE is_active = TRUE ORDER BY name");
        $leaveTypesStmt->execute();
        $leaveTypes = $leaveTypesStmt->fetchAll(PDO::FETCH_ASSOC);

        $creditsStmt = $pdo->prepare("
            SELECT 
                lc.*, 
                e.first_name, 
                e.last_name, 
                e.employee_no,
                lt.name as leave_type,
                lt.default_allowed
            FROM leave_credits lc 
            JOIN employees e ON lc.employee_id = e.id 
            JOIN leave_types lt ON lc.leave_type_id = lt.id
            ORDER BY e.first_name, e.last_name, lt.name
        ");
        $creditsStmt->execute();
        $leaveCredits = $creditsStmt->fetchAll(PDO::FETCH_ASSOC);

        $leaveTypeStatsStmt = $pdo->prepare("
            SELECT 
                lt.id,
                lt.name as leave_type,
                lt.default_allowed,
                lt.description,
                COUNT(DISTINCT lc.employee_id) as employee_count,
                SUM(lt.default_allowed) as total_allowed,
                SUM(COALESCE(lc.taken, 0)) as total_taken,
                AVG(lt.default_allowed) as avg_allowed,
                MIN(lt.default_allowed) as min_allowed,
                MAX(lt.default_allowed) as max_allowed
            FROM leave_types lt
            LEFT JOIN leave_credits lc ON lt.id = lc.leave_type_id
            WHERE lt.is_active = TRUE
            GROUP BY lt.id, lt.name, lt.default_allowed, lt.description
            ORDER BY lt.name
        ");
        $leaveTypeStatsStmt->execute();
        $leaveTypeStats = $leaveTypeStatsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate statistics
        $stats = [
            'activeLeaveTypes' => count($leaveTypeStats),
            'totalEmployees' => count($employees),
            'totalRecords' => count($leaveCredits),
            'totalAllowedDays' => array_sum(array_column($leaveTypeStats, 'total_allowed'))
        ];

        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'leaveTypeStats' => $leaveTypeStats,
            'leaveCredits' => $leaveCredits
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_leave_type':
                $leaveTypeName = trim($_POST['leave_type_name'] ?? '');
                $defaultAllowed = (int)($_POST['default_allowed'] ?? 0);
                $description = trim($_POST['description'] ?? '');
                $applyToAllEmployees = isset($_POST['apply_to_all_employees']) ? true : false;
                
                if (empty($leaveTypeName) || $defaultAllowed < 0) {
                    throw new Exception("Please fill in all required fields with valid values.");
                }
                
                // Check if leave type name already exists
                $checkStmt = $pdo->prepare("SELECT id FROM leave_types WHERE name = ?");
                $checkStmt->execute([$leaveTypeName]);
                
                if ($checkStmt->fetch()) {
                    throw new Exception("Leave type with this name already exists.");
                }
                
                // Start transaction
                $pdo->beginTransaction();
                
                try {
                    // Insert the new leave type
                    $stmt = $pdo->prepare("INSERT INTO leave_types (name, default_allowed, description, is_active) VALUES (?, ?, ?, 1)");
                    $stmt->execute([$leaveTypeName, $defaultAllowed, $description]);
                    $newLeaveTypeId = $pdo->lastInsertId();
                    
                    // If apply to all employees is checked, create leave credits for all employees
                    if ($applyToAllEmployees) {
                        // Get all active employees
                        $employeesStmt = $pdo->prepare("SELECT id FROM employees WHERE deleted_at IS NULL");
                        $employeesStmt->execute();
                        $employees = $employeesStmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        // Create leave credits for each employee
                        $insertCreditsStmt = $pdo->prepare("INSERT INTO leave_credits (employee_id, leave_type_id, taken) VALUES (?, ?, 0)");
                        foreach ($employees as $employeeId) {
                            $insertCreditsStmt->execute([$employeeId, $newLeaveTypeId]);
                        }
                    }
                    
                    $pdo->commit();
                    $message = "Leave type added successfully!";
                    if ($applyToAllEmployees) {
                        $message .= " Leave credits created for all existing employees.";
                    }
                    $_SESSION['success_message'] = $message;
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
                break;
                
            case 'add_employee_credits':
                $employeeId = $_POST['employee_id'] ?? '';
                $leaveTypeId = $_POST['leave_type_id'] ?? '';
                $taken = (int)($_POST['taken'] ?? 0);
                
                if (empty($employeeId) || empty($leaveTypeId) || $taken < 0) {
                    throw new Exception("Please fill in all required fields with valid values.");
                }
                
                // Check if record already exists
                $checkStmt = $pdo->prepare("SELECT id FROM leave_credits WHERE employee_id = ? AND leave_type_id = ?");
                $checkStmt->execute([$employeeId, $leaveTypeId]);
                
                if ($checkStmt->fetch()) {
                    throw new Exception("Leave credits for this employee and leave type already exist.");
                }
                
                $stmt = $pdo->prepare("INSERT INTO leave_credits (employee_id, leave_type_id, taken) VALUES (?, ?, ?)");
                $stmt->execute([$employeeId, $leaveTypeId, $taken]);
                
                $_SESSION['success_message'] = "Leave credits added successfully!";
                break;
                
            case 'update_credits':
                $id = $_POST['id'] ?? '';
                $taken = (int)($_POST['taken'] ?? 0);
                
                if (empty($id) || $taken < 0) {
                    throw new Exception("Please provide valid values.");
                }
                
                $stmt = $pdo->prepare("UPDATE leave_credits SET taken = ? WHERE id = ?");
                $stmt->execute([$taken, $id]);
                
                $_SESSION['success_message'] = "Leave credits updated successfully!";
                break;
                
            case 'update_leave_type':
                $leaveTypeId = $_POST['leave_type_id'] ?? '';
                $defaultAllowed = (int)($_POST['default_allowed'] ?? 0);
                $description = $_POST['description'] ?? '';
                $applyToAll = isset($_POST['apply_to_all']) ? true : false;
                
                if (empty($leaveTypeId) || $defaultAllowed < 0) {
                    throw new Exception("Please provide valid values.");
                }
                
                // Start transaction
                $pdo->beginTransaction();
                
                try {
                    // Update leave type defaults
                    $stmt = $pdo->prepare("UPDATE leave_types SET default_allowed = ?, description = ? WHERE id = ?");
                    $stmt->execute([$defaultAllowed, $description, $leaveTypeId]);
                    
                    // If apply to all employees is checked, update all existing leave credits
                    if ($applyToAll) {
                        // Get current taken days for each employee for this leave type
                        $currentCreditsStmt = $pdo->prepare("
                            SELECT employee_id, taken 
                            FROM leave_credits 
                            WHERE leave_type_id = ?
                        ");
                        $currentCreditsStmt->execute([$leaveTypeId]);
                        $currentCredits = $currentCreditsStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Update each employee's leave credits with new default
                        foreach ($currentCredits as $credit) {
                            $newAllowed = $defaultAllowed;
                            $taken = $credit['taken'];
                            
                            // If taken days exceed new default, cap taken days to new default
                            if ($taken > $newAllowed) {
                                $taken = $newAllowed;
                            }
                            
                            // Update the leave credit record
                            $updateStmt = $pdo->prepare("
                                UPDATE leave_credits 
                                SET taken = ? 
                                WHERE employee_id = ? AND leave_type_id = ?
                            ");
                            $updateStmt->execute([$taken, $credit['employee_id'], $leaveTypeId]);
                        }
                    }
                    
                    $pdo->commit();
                    $_SESSION['success_message'] = "Leave type updated successfully!" . ($applyToAll ? " Changes applied to all employees." : "");
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
                break;
                
            case 'bulk_update_leave_types':
                $leaveTypes = $_POST['leave_types'] ?? [];
                $applyToAll = isset($_POST['apply_to_all']) ? true : false;
                
                if (empty($leaveTypes)) {
                    throw new Exception("No leave types provided for update.");
                }
                
                // Start transaction
                $pdo->beginTransaction();
                
                try {
                    foreach ($leaveTypes as $leaveTypeId => $data) {
                        $defaultAllowed = (int)($data['default_allowed'] ?? 0);
                        $description = $data['description'] ?? '';
                        
                        if ($defaultAllowed < 0) {
                            throw new Exception("Invalid default allowed value for leave type ID: {$leaveTypeId}");
                        }
                        
                        // Update leave type defaults
                        $stmt = $pdo->prepare("UPDATE leave_types SET default_allowed = ?, description = ? WHERE id = ?");
                        $stmt->execute([$defaultAllowed, $description, $leaveTypeId]);
                        
                        // If apply to all employees is checked, update all existing leave credits
                        if ($applyToAll) {
                            // Get current taken days for each employee for this leave type
                            $currentCreditsStmt = $pdo->prepare("
                                SELECT employee_id, taken 
                                FROM leave_credits 
                                WHERE leave_type_id = ?
                            ");
                            $currentCreditsStmt->execute([$leaveTypeId]);
                            $currentCredits = $currentCreditsStmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            // Update each employee's leave credits with new default
                            foreach ($currentCredits as $credit) {
                                $newAllowed = $defaultAllowed;
                                $taken = $credit['taken'];
                                
                                // If taken days exceed new default, cap taken days to new default
                                if ($taken > $newAllowed) {
                                    $taken = $newAllowed;
                                }
                                
                                // Update the leave credit record
                                $updateStmt = $pdo->prepare("
                                    UPDATE leave_credits 
                                    SET taken = ? 
                                    WHERE employee_id = ? AND leave_type_id = ?
                                ");
                                $updateStmt->execute([$taken, $credit['employee_id'], $leaveTypeId]);
                            }
                        }
                    }
                    
                    $pdo->commit();
                    $_SESSION['success_message'] = "All leave types updated successfully!" . ($applyToAll ? " Changes applied to all employees." : "");
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
                break;
                
            case 'delete_leave_type':
                $leaveTypeId = $_POST['leave_type_id'] ?? '';
                
                if (empty($leaveTypeId)) {
                    throw new Exception("Invalid leave type ID.");
                }
                
                // Start transaction
                $pdo->beginTransaction();
                
                try {
                    // First, delete all leave credits associated with this leave type
                    $deleteCreditsStmt = $pdo->prepare("DELETE FROM leave_credits WHERE leave_type_id = ?");
                    $deleteCreditsStmt->execute([$leaveTypeId]);
                    
                    // Then delete the leave type itself
                    $deleteTypeStmt = $pdo->prepare("DELETE FROM leave_types WHERE id = ?");
                    $deleteTypeStmt->execute([$leaveTypeId]);
                    
                    $pdo->commit();
                    $_SESSION['success_message'] = "Leave type and all associated credits deleted successfully!";
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
                break;
                
            case 'delete_credits':
                $id = $_POST['id'] ?? '';
                
                if (empty($id)) {
                    throw new Exception("Invalid record ID.");
                }
                
                $stmt = $pdo->prepare("DELETE FROM leave_credits WHERE id = ?");
                $stmt->execute([$id]);
                
                $_SESSION['success_message'] = "Leave credits deleted successfully!";
                break;
                
            default:
                throw new Exception("Invalid action.");
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    // Redirect to prevent form resubmission
    header("Location: index.php?payroll=leave_credits");
    exit();
}

// Fetch all employees for dropdown
$employeesStmt = $pdo->prepare("SELECT id, employee_no, first_name, last_name FROM employees WHERE deleted_at IS NULL ORDER BY first_name, last_name");
$employeesStmt->execute();
$employees = $employeesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all leave types
$leaveTypesStmt = $pdo->prepare("SELECT * FROM leave_types WHERE is_active = TRUE ORDER BY name");
$leaveTypesStmt->execute();
$leaveTypes = $leaveTypesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all leave credits with employee names and leave type info
$creditsStmt = $pdo->prepare("
    SELECT 
        lc.*, 
        e.first_name, 
        e.last_name, 
        e.employee_no,
        lt.name as leave_type,
        lt.default_allowed
    FROM leave_credits lc 
    JOIN employees e ON lc.employee_id = e.id 
    JOIN leave_types lt ON lc.leave_type_id = lt.id
    ORDER BY e.first_name, e.last_name, lt.name
");
$creditsStmt->execute();
$leaveCredits = $creditsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch leave type statistics for the main table
$leaveTypeStatsStmt = $pdo->prepare("
    SELECT 
        lt.id,
        lt.name as leave_type,
        lt.default_allowed,
        lt.description,
        COUNT(DISTINCT lc.employee_id) as employee_count,
        SUM(lt.default_allowed) as total_allowed,
        SUM(COALESCE(lc.taken, 0)) as total_taken,
        AVG(lt.default_allowed) as avg_allowed,
        MIN(lt.default_allowed) as min_allowed,
        MAX(lt.default_allowed) as max_allowed
    FROM leave_types lt
    LEFT JOIN leave_credits lc ON lt.id = lc.leave_type_id
    WHERE lt.is_active = TRUE
    GROUP BY lt.id, lt.name, lt.default_allowed, lt.description
    ORDER BY lt.name
");
$leaveTypeStatsStmt->execute();
$leaveTypeStats = $leaveTypeStatsStmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to get leave type mapping
function getLeaveTypeMapping() {
    return [
        'Sick Leave' => 1,
        'Emergency Leave' => 2,
        'Vacation Leave' => 3,
        'Personal Leave' => 4,
        'Maternity/Paternity Leave' => 5
    ];
}

// Helper function to update leave credits when leave is approved/rejected
function updateLeaveCreditsOnApproval($pdo, $leaveId, $action) {
    try {
        // Get leave details
        $leaveStmt = $pdo->prepare("
            SELECT 
                l.id,
                l.employee_id,
                l.leave_type,
                l.duration,
                l.status
            FROM leaves l
            WHERE l.id = ?
        ");
        $leaveStmt->execute([$leaveId]);
        $leave = $leaveStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$leave) {
            throw new Exception('Leave request not found');
        }
        
        $leaveTypeMapping = getLeaveTypeMapping();
        $leaveTypeId = $leaveTypeMapping[$leave['leave_type']] ?? null;
        
        if (!$leaveTypeId) {
            throw new Exception('Invalid leave type');
        }
        
        if ($action === 'approve') {
            // Check if leave credits exist for this employee and leave type
            $creditsStmt = $pdo->prepare("
                SELECT id, taken 
                FROM leave_credits 
                WHERE employee_id = ? AND leave_type_id = ?
            ");
            $creditsStmt->execute([$leave['employee_id'], $leaveTypeId]);
            $credits = $creditsStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($credits) {
                // Update taken days
                $newTaken = $credits['taken'] + $leave['duration'];
                
                // Check if employee has enough leave credits
                $leaveTypeStmt = $pdo->prepare("SELECT default_allowed FROM leave_types WHERE id = ?");
                $leaveTypeStmt->execute([$leaveTypeId]);
                $leaveType = $leaveTypeStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($newTaken > $leaveType['default_allowed']) {
                    throw new Exception('Employee does not have enough leave credits');
                }
                
                $updateStmt = $pdo->prepare("
                    UPDATE leave_credits 
                    SET taken = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $updateStmt->execute([$newTaken, $credits['id']]);
                
            } else {
                // Create new leave credits record
                $insertStmt = $pdo->prepare("
                    INSERT INTO leave_credits (employee_id, leave_type_id, taken) 
                    VALUES (?, ?, ?)
                ");
                $insertStmt->execute([$leave['employee_id'], $leaveTypeId, $leave['duration']]);
            }
            
        } elseif ($action === 'reject') {
            // If rejecting a previously approved leave, decrease taken days
            if ($leave['status'] === 'Approved') {
                $creditsStmt = $pdo->prepare("
                    SELECT id, taken 
                    FROM leave_credits 
                    WHERE employee_id = ? AND leave_type_id = ?
                ");
                $creditsStmt->execute([$leave['employee_id'], $leaveTypeId]);
                $credits = $creditsStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($credits) {
                    $newTaken = max(0, $credits['taken'] - $leave['duration']);
                    
                    $updateStmt = $pdo->prepare("
                        UPDATE leave_credits 
                        SET taken = ?, updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$newTaken, $credits['id']]);
                }
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error updating leave credits: " . $e->getMessage());
        throw $e;
    }
}

// Include the view
require_once __DIR__ . '/../view/auth/leave_credits.view.php';
?>


















