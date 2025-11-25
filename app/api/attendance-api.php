<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/database.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

$db = new Database();
$pdo = $db->getConnection();
$pdo->exec("SET time_zone = '+08:00'");

$method = $_SERVER['REQUEST_METHOD'];

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Ensure attendance table supports manager logs
 */
if (!function_exists('ensureManagerAttendanceSupport')) {
    function ensureManagerAttendanceSupport(PDO $pdo): void {
        try {
            $res = $pdo->query("SHOW COLUMNS FROM attendance LIKE 'manager_id'");
            if ($res && $res->rowCount() === 0) {
                $pdo->exec("ALTER TABLE attendance ADD COLUMN manager_id INT(11) NULL AFTER employee_id");
                try {
                    $pdo->exec("CREATE INDEX idx_attendance_manager_id ON attendance(manager_id)");
                } catch (Throwable $e) {
                    // Index might already exist
                }
            }
        } catch (Throwable $e) {
            // Column might already exist
        }
    }
}

/**
 * Ensure attendance table supports HR logs
 */
if (!function_exists('ensureHrAttendanceSupport')) {
    function ensureHrAttendanceSupport(PDO $pdo): void {
        try {
            $res = $pdo->query("SHOW COLUMNS FROM attendance LIKE 'hr_id'");
            if ($res && $res->rowCount() === 0) {
                $pdo->exec("ALTER TABLE attendance ADD COLUMN hr_id INT(11) NULL AFTER manager_id");
                try {
                    $pdo->exec("CREATE INDEX idx_attendance_hr_id ON attendance(hr_id)");
                } catch (Throwable $e) {
                    // Index might already exist
                }
            }
        } catch (Throwable $e) {
            // Column might already exist
        }
    }
}

/**
 * Get default image path based on gender
 */
function getDefaultImageByGender(?string $gender): string {
    $genderLower = strtolower($gender ?? '');
    if ($genderLower === 'male' || $genderLower === 'm') {
        return 'assets/image/default_men.png';
    } elseif ($genderLower === 'female' || $genderLower === 'f') {
        return 'assets/image/default_women.png';
    }
    return 'assets/image/default_user_image.svg';
}

/**
 * Format user name based on user type
 */
function formatUserName(array $user, string $userType): string {
    if ($userType === 'employee') {
        $middle = !empty($user['middle_name']) ? strtoupper($user['middle_name'][0]) . '. ' : '';
        return $user['first_name'] . ' ' . $middle . $user['last_name'];
    } elseif ($userType === 'manager') {
        $middle = !empty($user['m_middle_name']) ? strtoupper($user['m_middle_name'][0]) . '. ' : '';
        return $user['m_first_name'] . ' ' . $middle . $user['m_last_name'];
    } else { // hr
        $middle = !empty($user['hr_middle_name']) ? strtoupper($user['hr_middle_name'][0]) . '. ' : '';
        return $user['hr_first_name'] . ' ' . $middle . $user['hr_last_name'];
    }
}

/**
 * Get user photo path and gender based on user type
 */
function getUserPhotoAndGender(array $user, string $userType): array {
    $photoPath = null;
    $gender = '';
    
    if ($userType === 'employee') {
        $photoPath = $user['photo_path'] ?? null;
        $gender = strtolower($user['sex'] ?? '');
    } elseif ($userType === 'manager') {
        $photoPath = $user['m_photo_path'] ?? null;
        $gender = strtolower($user['m_sex'] ?? '');
    } else { // hr
        $photoPath = $user['hr_photo_path'] ?? null;
        $gender = strtolower($user['hr_sex'] ?? '');
    }
    
    // Determine image path
    if (empty($photoPath)) {
        $imagePath = getDefaultImageByGender($gender);
    } else {
        $imagePath = $photoPath;
        // Add ../public/ prefix for HR and manager photos if needed
        if (($userType === 'hr' || $userType === 'manager') && 
            strpos($imagePath, '../public/') === false && 
            strpos($imagePath, 'http') === false &&
            strpos($imagePath, 'assets/image/default_') === false) {
            $imagePath = '../public/' . ltrim($imagePath, '/');
        }
    }
    
    return ['image_path' => $imagePath, 'gender' => $gender];
}

/**
 * Find user by RFID or Employee ID
 */
function findUserByIdentifier(PDO $pdo, ?string $rfid, ?string $employeeId): ?array {
    if ($rfid) {
        // Check employees
        $stmt = $pdo->prepare("SELECT *, 'employee' as user_type FROM employees WHERE rfid_number = ?");
        $stmt->execute([$rfid]);
        $user = $stmt->fetch();
        if ($user) return $user;
        
        // Check managers
        $stmt = $pdo->prepare("SELECT *, 'manager' as user_type FROM managers WHERE m_rfid_number = ? AND deleted_at IS NULL");
        $stmt->execute([$rfid]);
        $user = $stmt->fetch();
        if ($user) return $user;
        
        // Check HR/admins
        $stmt = $pdo->prepare("SELECT *, 'hr' as user_type FROM admins WHERE hr_rfid_number = ? AND deleted_at IS NULL");
        $stmt->execute([$rfid]);
        return $stmt->fetch() ?: null;
    }
    
    if ($employeeId) {
        // Check employees
        $stmt = $pdo->prepare("SELECT *, 'employee' as user_type FROM employees WHERE employee_no = ?");
        $stmt->execute([$employeeId]);
        $user = $stmt->fetch();
        if ($user) return $user;
        
        // Check managers
        $stmt = $pdo->prepare("SELECT *, 'manager' as user_type FROM managers WHERE m_employee_id = ? AND deleted_at IS NULL");
        $stmt->execute([$employeeId]);
        $user = $stmt->fetch();
        if ($user) return $user;
        
        // Check HR/admins
        $stmt = $pdo->prepare("SELECT *, 'hr' as user_type FROM admins WHERE hr_employee_id = ? AND deleted_at IS NULL");
        $stmt->execute([$employeeId]);
        return $stmt->fetch() ?: null;
    }
    
    return null;
}

/**
 * Get user schedule based on user type
 */
function getUserSchedule(PDO $pdo, int $userId, string $userType): ?array {
    if ($userType === 'employee') {
        $stmt = $pdo->prepare("
            SELECT s.* 
            FROM schedules s 
            INNER JOIN employee_schedules es ON s.id = es.schedule_id 
            WHERE es.employee_id = ?
        ");
    } elseif ($userType === 'manager') {
        $stmt = $pdo->prepare("
            SELECT s.* 
            FROM schedules s 
            INNER JOIN manager_schedules ms ON s.id = ms.schedule_id 
            WHERE ms.manager_id = ?
        ");
    } else { // hr
        $stmt = $pdo->prepare("
            SELECT s.* 
            FROM schedules s 
            INNER JOIN hr_schedules hs ON s.id = hs.schedule_id 
            WHERE hs.hr_id = ?
        ");
    }
    
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

/**
 * Get or create attendance record for today
 */
function getOrCreateAttendance(PDO $pdo, int $userId, string $userType, string $today): array {
    if ($userType === 'employee') {
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
        $stmt->execute([$userId, $today]);
        $attendance = $stmt->fetch();
        
        if (!$attendance) {
            $pdo->prepare("INSERT INTO attendance (employee_id, manager_id, hr_id, date) VALUES (?, NULL, NULL, ?)")
                ->execute([$userId, $today]);
            $stmt->execute([$userId, $today]);
            $attendance = $stmt->fetch();
        }
    } elseif ($userType === 'manager') {
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE manager_id = ? AND date = ?");
        $stmt->execute([$userId, $today]);
        $attendance = $stmt->fetch();
        
        if (!$attendance) {
            $pdo->prepare("INSERT INTO attendance (employee_id, manager_id, hr_id, date) VALUES (NULL, ?, NULL, ?)")
                ->execute([$userId, $today]);
            $stmt->execute([$userId, $today]);
            $attendance = $stmt->fetch();
        }
    } else { // hr
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE hr_id = ? AND date = ?");
        $stmt->execute([$userId, $today]);
        $attendance = $stmt->fetch();
        
        if (!$attendance) {
            $pdo->prepare("INSERT INTO attendance (employee_id, manager_id, hr_id, date) VALUES (NULL, NULL, ?, ?)")
                ->execute([$userId, $today]);
            $stmt->execute([$userId, $today]);
            $attendance = $stmt->fetch();
        }
    }
    
    return $attendance;
}

/**
 * Convert time string (HH:MM:SS) to seconds since midnight
 */
function timeToSeconds(string $time): int {
    $parts = explode(':', $time);
    $hours = (int)($parts[0] ?? 0);
    $minutes = (int)($parts[1] ?? 0);
    $seconds = (int)($parts[2] ?? 0);
    return ($hours * 3600) + ($minutes * 60) + $seconds;
}

/**
 * Determine which time slot the current time falls into
 */
function getCurrentTimeSlot(string $currentTime, array $schedule): ?string {
    // Convert times to seconds since midnight for accurate comparison
    $current = timeToSeconds($currentTime);
    $morningStart = timeToSeconds($schedule['sched_morning_in']);
    $morningEnd = timeToSeconds($schedule['sched_morning_out']);
    $afternoonStart = timeToSeconds($schedule['sched_afternoon_in']);
    $afternoonEnd = timeToSeconds($schedule['sched_afternoon_out']);
    
    $graceMinutes = $schedule['grace_period'] ?? 0;
    $morningStartWithGrace = $morningStart + ($graceMinutes * 60);
    $afternoonStartWithGrace = $afternoonStart + ($graceMinutes * 60);
    
    if ($current >= $morningStartWithGrace && $current <= $morningEnd) {
        return 'morning';
    } elseif ($current >= $afternoonStartWithGrace && $current <= $afternoonEnd) {
        return 'afternoon';
    }
    
    return null;
}

/**
 * Check if time out is within 2 minutes of time in
 */
function isTimeOutTooSoon(?string $timeIn, string $timeOut): bool {
    if (empty($timeIn)) {
        return false; // No time in recorded yet, so can't be too soon
    }
    
    $timeInTimestamp = strtotime($timeIn);
    $timeOutTimestamp = strtotime($timeOut);
    $differenceSeconds = $timeOutTimestamp - $timeInTimestamp;
    $minuteDifference = $differenceSeconds / 60;
    
    return $minuteDifference < 1;
}

/**
 * Process photo paths for records - add ../public/ prefix for HR and manager photos
 */
function processPhotoPaths(array &$records): void {
    foreach ($records as &$record) {
        if (!empty($record['photo_path'])) {
            $userType = $record['user_type'] ?? '';
            if (($userType === 'hr' || $userType === 'manager') && 
                strpos($record['photo_path'], '../public/') === false && 
                strpos($record['photo_path'], 'http') === false) {
                $record['photo_path'] = '../public/' . ltrim($record['photo_path'], '/');
            }
        }
    }
    unset($record);
}

// ============================================================================
// Initialize Database Support
// ============================================================================

ensureManagerAttendanceSupport($pdo);
ensureHrAttendanceSupport($pdo);

// ============================================================================
// GET Request - Fetch Attendance Records
// ============================================================================

if ($method === 'GET') {
    $filterDate = $_GET['date'] ?? date('Y-m-d');
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                e.photo_path,
                e.employee_no,
                CONCAT(
                    e.first_name, 
                    ' ', 
                    IF(e.middle_name IS NOT NULL AND e.middle_name != '', 
                       CONCAT(LEFT(e.middle_name, 1), '. '), 
                       ''),
                    e.last_name
                ) AS full_name,
                e.position,
                e.sex as gender,
                a.morning_in,
                a.morning_out,
                a.afternoon_in,
                a.afternoon_out,
                a.date,
                'employee' as user_type
            FROM attendance a
            INNER JOIN employees e ON a.employee_id = e.id
            WHERE DATE(a.date) = ? AND a.employee_id IS NOT NULL
            
            UNION ALL
            
            SELECT 
                m.m_photo_path as photo_path,
                m.m_employee_id as employee_no,
                CONCAT(
                    m.m_first_name, 
                    ' ', 
                    IF(m.m_middle_name IS NOT NULL AND m.m_middle_name != '', 
                       CONCAT(LEFT(m.m_middle_name, 1), '. '), 
                       ''),
                    m.m_last_name
                ) AS full_name,
                'Manager' as position,
                m.m_sex as gender,
                a2.morning_in,
                a2.morning_out,
                a2.afternoon_in,
                a2.afternoon_out,
                a2.date,
                'manager' as user_type
            FROM attendance a2
            INNER JOIN managers m ON a2.manager_id = m.id
            WHERE DATE(a2.date) = ? AND a2.manager_id IS NOT NULL
            
            UNION ALL
            
            SELECT 
                admin.hr_photo_path as photo_path,
                COALESCE(admin.hr_employee_id, '') as employee_no,
                CONCAT(
                    admin.hr_first_name, 
                    ' ', 
                    IF(admin.hr_middle_name IS NOT NULL AND admin.hr_middle_name != '', 
                       CONCAT(LEFT(admin.hr_middle_name, 1), '. '), 
                       ''),
                    admin.hr_last_name
                ) AS full_name,
                COALESCE(admin.hr_position, 'Human Resources') as position,
                admin.hr_sex as gender,
                att.morning_in,
                att.morning_out,
                att.afternoon_in,
                att.afternoon_out,
                att.date,
                'hr' as user_type
            FROM attendance att
            INNER JOIN admins admin ON att.hr_id = admin.id
            WHERE DATE(att.date) = ? AND att.hr_id IS NOT NULL AND admin.deleted_at IS NULL
            
            ORDER BY 
                GREATEST(
                    IFNULL(TIME_TO_SEC(afternoon_out), 0),
                    IFNULL(TIME_TO_SEC(afternoon_in), 0),
                    IFNULL(TIME_TO_SEC(morning_out), 0),
                    IFNULL(TIME_TO_SEC(morning_in), 0)
                ) DESC
        ");
        
        $stmt->bindValue(1, $filterDate, PDO::PARAM_STR);
        $stmt->bindValue(2, $filterDate, PDO::PARAM_STR);
        $stmt->bindValue(3, $filterDate, PDO::PARAM_STR);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        processPhotoPaths($records);
        
        ob_start();
        
        if (count($records) > 0) {
            foreach ($records as $index => $record) {
                $photoPath = $record['photo_path'] ?? null;
                $gender = strtolower($record['gender'] ?? '');
                $defaultImage = getDefaultImageByGender($gender);
                $imageSrc = !empty($photoPath) ? $photoPath : $defaultImage;
                ?>
                <tr class="fade-in-slide">
                    <td class="py-3 px-4 text-center"><?= $index + 1 ?></td>
                    <td class="py-3 px-4">
                        <img src="<?= htmlspecialchars($imageSrc) ?>" 
                             alt="Photo" 
                             class="h-10 w-10 rounded-full object-cover" 
                             onerror="this.onerror=null;this.src='<?= htmlspecialchars($defaultImage) ?>';" />
                    </td>
                    <td class="py-3 text-sm px-4"><?= htmlspecialchars(ucwords(strtolower($record['full_name']))) ?></td>
                    <td class="py-3 text-sm px-4"><?= htmlspecialchars($record['position']) ?></td>
                    <td class="py-3 text-sm text-center px-4"><?= $record['morning_in'] ? date('h:i A', strtotime($record['morning_in'])) : '--:--' ?></td>
                    <td class="py-3 text-sm text-center px-4"><?= $record['morning_out'] ? date('h:i A', strtotime($record['morning_out'])) : '--:--' ?></td>
                    <td class="py-3 text-sm text-center px-4"><?= $record['afternoon_in'] ? date('h:i A', strtotime($record['afternoon_in'])) : '--:--' ?></td>
                    <td class="py-3 text-sm text-center px-4"><?= $record['afternoon_out'] ? date('h:i A', strtotime($record['afternoon_out'])) : '--:--' ?></td>
                    <td class="py-3 px-4 text-sm text-center"><?= htmlspecialchars(date('d-M-Y', strtotime($record['date']))) ?></td>
                </tr>
                <?php
            }
        } else {
            echo '<tr><td colspan="9" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
                <i class="bi bi-calendar-x fs-5 me-3"></i>No attendance records found.
            </td></tr>';
        }
        
        $html = ob_get_clean();
        echo json_encode(['status' => 'success', 'data' => $records, 'html' => $html, 'filterDate' => $filterDate]);
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// ============================================================================
// POST Request - Log Attendance
// ============================================================================

elseif ($method === 'POST') {
    try {
        $input = $_POST ?: json_decode(file_get_contents('php://input'), true);
        $rfid = $input['rfid'] ?? null;
        $employee_id_manual = $input['employee_id'] ?? null;
        $manual_type = $input['manual_type'] ?? null;
        
        // Validate input
        if (!$rfid && !$employee_id_manual) {
            echo json_encode(["status" => "error", "message" => "RFID or Employee/Manager/HR ID is required."]);
            exit;
        }
        
        // Find user
        $user = findUserByIdentifier($pdo, $rfid, $employee_id_manual);
        
        if (!$user) {
            echo json_encode(["status" => "error", "message" => "Employee, Manager, or HR not found."]);
            exit;
        }
        
        $user_type = $user['user_type'];
        $user_id = $user['id'];
        
        // Format name and get image path
        $name = formatUserName($user, $user_type);
        $photoData = getUserPhotoAndGender($user, $user_type);
        $image_path = $photoData['image_path'];
        
        $today = date('Y-m-d');
        $now = date('H:i:s');
        
        // Get user schedule
        $schedule = getUserSchedule($pdo, $user_id, $user_type);
        
        if (!$schedule) {
            $user_type_text = $user_type === 'employee' ? 'employee' : ($user_type === 'manager' ? 'manager' : 'HR');
            echo json_encode([
                "status" => "warning", 
                "message" => "No schedule assigned to this $user_type_text. Please contact your administrator."
            ]);
            exit;
        }
        
        // Validate schedule time BEFORE creating attendance record (unless manual_type is used)
        if (!$manual_type) {
            // Determine which field to update based on current time slot
            $currentTimeSlot = getCurrentTimeSlot($now, $schedule);
            
            // Check if current time is within schedule window
            if (!$currentTimeSlot) {
                $morningStart = date('h:i A', strtotime($schedule['sched_morning_in']));
                $morningEnd = date('h:i A', strtotime($schedule['sched_morning_out']));
                $afternoonStart = date('h:i A', strtotime($schedule['sched_afternoon_in']));
                $afternoonEnd = date('h:i A', strtotime($schedule['sched_afternoon_out']));
                
                echo json_encode([
                    "status" => "warning", 
                    "message" => "You are not within your scheduled time. Your schedule: Morning ($morningStart - $morningEnd), Afternoon ($afternoonStart - $afternoonEnd). Current time: " . date('h:i A', strtotime($now))
                ]);
                exit;
            }
        }
        
        // Get or create attendance record (only after schedule validation passes)
        $attendance = getOrCreateAttendance($pdo, $user_id, $user_type, $today);
        $attendance_id = $attendance['id'];
        
        // Determine which field to update
        $field = '';
        $type = '';
        // $currentTimeSlot is already set above if not manual_type
        
        if ($manual_type) {
            $field = str_replace('-', '_', $manual_type);
            $type = $manual_type;
            
            $validFields = ['morning_in', 'morning_out', 'afternoon_in', 'afternoon_out'];
            if (!in_array($field, $validFields)) {
                echo json_encode(["status" => "error", "message" => "Invalid manual_type."]);
                exit;
            }
            
            if (!empty($attendance[$field])) {
                echo json_encode([
                    "status" => "info", 
                    "message" => ucfirst(str_replace('_', ' ', $field)) . " already logged."
                ]);
                exit;
            }
        } else {
            // Use the already determined time slot from validation above
            if ($currentTimeSlot === 'morning') {
                if (!$attendance['morning_in']) {
                    $field = 'morning_in';
                    $type = 'morning-in';
                } elseif (!$attendance['morning_out']) {
                    $field = 'morning_out';
                    $type = 'morning-out';
                } else {
                    echo json_encode(["status" => "info", "message" => "Morning attendance already complete."]);
                    exit;
                }
            } elseif ($currentTimeSlot === 'afternoon') {
                if (!$attendance['afternoon_in']) {
                    $field = 'afternoon_in';
                    $type = 'afternoon-in';
                } elseif (!$attendance['afternoon_out']) {
                    $field = 'afternoon_out';
                    $type = 'afternoon-out';
                } else {
                    echo json_encode(["status" => "info", "message" => "Afternoon attendance already complete."]);
                    exit;
                }
            }
            
            if (!$field) {
                echo json_encode([
                    "status" => "info",
                    "message" => "Attendance for today is already complete.",
                    "user_id" => $user_id,
                    "name" => $name,
                    "date" => $today
                ]);
                exit;
            }
        }
        
        // Validate field name to prevent SQL injection
        $validFields = ['morning_in', 'morning_out', 'afternoon_in', 'afternoon_out'];
        if (!in_array($field, $validFields)) {
            echo json_encode(["status" => "error", "message" => "Invalid field name."]);
            exit;
        }
        
        // Check if time out is within 2 minutes of time in
        if ($field === 'morning_out' && isTimeOutTooSoon($attendance['morning_in'], $now)) {
            echo json_encode([
                "status" => "warning", 
                "message" => "Restricted: You cannot time out within 1 minutes of time in."
            ]);
            exit;
        }
        
        if ($field === 'afternoon_out' && isTimeOutTooSoon($attendance['afternoon_in'], $now)) {
            echo json_encode([
                "status" => "warning", 
                "message" => "Restricted: You cannot time out within 1 minutes of time in."
            ]);
            exit;
        }
        
        // Update attendance
        $update = $pdo->prepare("UPDATE attendance SET `$field` = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$now, $attendance_id]);
        
        echo json_encode([
            "status" => "success",
            "message" => "Successfully logged $type.",
            "user_id" => $user_id,
            "user_type" => $user_type,
            "image_url" => $image_path,
            "name" => $name,
            "timestamp" => $now,
            "type" => $type,
            "log_status" => 'first log accepted'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}

// ============================================================================
// Method Not Allowed
// ============================================================================

else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
