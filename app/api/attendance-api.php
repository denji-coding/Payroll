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

// Ensure attendance table supports manager logs as well
if (!function_exists('ensureManagerAttendanceSupport')) {
    function ensureManagerAttendanceSupport(PDO $pdo): void {
        try {
            $res = $pdo->query("SHOW COLUMNS FROM attendance LIKE 'manager_id'");
            if ($res && $res->rowCount() === 0) {
                $pdo->exec("ALTER TABLE attendance ADD COLUMN manager_id INT(11) NULL AFTER employee_id");
                // Index creation may not support IF NOT EXISTS across all MySQL versions; wrap in try
                try { $pdo->exec("CREATE INDEX idx_attendance_manager_id ON attendance(manager_id)"); } catch (Throwable $e) {}
            }
        } catch (Throwable $e) {
            // ignore if cannot alter; manager attendance will be disabled silently
        }
    }
}
ensureManagerAttendanceSupport($pdo);

if ($method === 'GET') {
    $filterDate = $_GET['date'] ?? date('Y-m-d');

    try {
        $stmt = $pdo->prepare("
            SELECT 
                e.photo_path,
                e.employee_no,
                CONCAT(e.first_name, ' ', LEFT(e.middle_name, 1), '. ', e.last_name) AS full_name,
                e.position,
                a.morning_in,
                a.morning_out,
                a.afternoon_in,
                a.afternoon_out,
                a.date
            FROM attendance a
            INNER JOIN employees e ON a.employee_id = e.id
            WHERE DATE(a.date) = :filterDate
            ORDER BY 
                GREATEST(
                    IFNULL(TIME_TO_SEC(a.afternoon_out), 0),
                    IFNULL(TIME_TO_SEC(a.afternoon_in), 0),
                    IFNULL(TIME_TO_SEC(a.morning_out), 0),
                    IFNULL(TIME_TO_SEC(a.morning_in), 0)
                ) DESC
        ");
        $stmt->bindParam(':filterDate', $filterDate);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_start();

        if (count($records)) {
            foreach ($records as $index => $record) {
                ?>
                <tr class="fade-in-slide">
                    <td class="py-3 px-4 text-center"><?= $index + 1?></td>
                    <td class="py-3 px-4">
                        <img src="<?= htmlspecialchars($record['photo_path'] ?: 'assets/image/default_user_image.svg') ?>" alt="Photo" class="h-10 w-10 rounded-full object-cover" />
                    </td>
                    <td class="py-3 text-sm px-4"><?= htmlspecialchars($record['employee_no']) ?></td>
                    <td class="py-3 text-sm px-4"><?= htmlspecialchars(ucwords(strtolower($record['full_name']))) ?></td>
                    <td class="py-3 text-sm px-4"><?= htmlspecialchars($record['position']) ?></td>
                    <td class="py-3 text-sm text-center px-4"><?= $record['morning_in'] ? date('h:i A', strtotime($record['morning_in'])) : '-' ?></td>
                    <td class="py-3 text-sm text-center px-4"><?= $record['morning_out'] ? date('h:i A', strtotime($record['morning_out'])) : '-' ?></td>
                    <td class="py-3 text-sm text-center px-4"><?= $record['afternoon_in'] ? date('h:i A', strtotime($record['afternoon_in'])) : '-' ?></td>
                    <td class="py-3 text-sm text-center px-4"><?= $record['afternoon_out'] ? date('h:i A', strtotime($record['afternoon_out'])) : '-' ?></td>
                    <td class="py-3 px-4 text-sm text-center"><?= htmlspecialchars(date('F j, Y', strtotime($record['date']))) ?></td>
                </tr>
                <?php
            }
        } else {
            echo '<tr><td colspan="10" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
            <i class="bi bi-calendar-x fs-5 me-3"></i>No attendance records found.
           </td></tr>';
        }

        $html = ob_get_clean();

        echo json_encode(['status' => 'success', 'data' => $records, 'html' => $html, 'filterDate' => $filterDate]);


    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} elseif ($method === 'POST') {
    try {
        $input = $_POST ?: json_decode(file_get_contents('php://input'), true);
        $rfid = $input['rfid'] ?? null;
        $employee_id_manual = $input['employee_id'] ?? null;
        $manual_type = $input['manual_type'] ?? null;

        // Find employee
        if ($rfid) {
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE rfid_number = ?");
            $stmt->execute([$rfid]);
        } elseif ($employee_id_manual) {
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_no = ?");
            $stmt->execute([$employee_id_manual]);
        } else {
            echo json_encode(["status" => "error", "message" => "RFID or Employee ID is required."]);
            exit;
        }

        $employee = $stmt->fetch();
        if (!$employee) {
            echo json_encode(["status" => "error", "message" => "Employee not found."]);
            exit;
        }

        $employee_id = $employee['id'];
        $middle_initial = !empty($employee['middle_name']) ? strtoupper($employee['middle_name'][0]) . '. ' : '';
        $name = $employee['first_name'] . ' ' . $middle_initial . $employee['last_name'];
        $image_path = $employee['photo_path'] ?? 'assets/image/default_user_image.svg';
        $today = date('Y-m-d');
        $now = date('H:i:s');

        // Check if employee has a schedule
        $stmt = $pdo->prepare("
            SELECT s.* 
            FROM schedules s 
            INNER JOIN employee_schedules es ON s.id = es.schedule_id 
            WHERE es.employee_id = ?
        ");
        $stmt->execute([$employee_id]);
        $schedule = $stmt->fetch();

        if (!$schedule) {
            echo json_encode(["status" => "error", "message" => "No schedule assigned to this employee. Please contact your administrator."]);
            exit;
        }

        // Function to check if current time is within schedule window
        function isWithinScheduleWindow($currentTime, $startTime, $endTime, $gracePeriod = 0) {
            $current = strtotime($currentTime);
            $start = strtotime($startTime);
            $end = strtotime($endTime);
            
            // Add grace period to start time
            $startWithGrace = $start + ($gracePeriod * 60);
            
            return ($current >= $startWithGrace && $current <= $end);
        }

        // Function to determine which time slot the current time falls into
        function getCurrentTimeSlot($currentTime, $schedule) {
            $current = strtotime($currentTime);
            $morningStart = strtotime($schedule['sched_morning_in']);
            $morningEnd = strtotime($schedule['sched_morning_out']);
            $afternoonStart = strtotime($schedule['sched_afternoon_in']);
            $afternoonEnd = strtotime($schedule['sched_afternoon_out']);
            
            // Add grace period
            $graceMinutes = $schedule['grace_period'];
            $morningStartWithGrace = $morningStart + ($graceMinutes * 60);
            $afternoonStartWithGrace = $afternoonStart + ($graceMinutes * 60);
            
            if ($current >= $morningStartWithGrace && $current <= $morningEnd) {
                return 'morning';
            } elseif ($current >= $afternoonStartWithGrace && $current <= $afternoonEnd) {
                return 'afternoon';
            }
            
            return null;
        }

        // Check if current time is within any schedule window
        $currentTimeSlot = getCurrentTimeSlot($now, $schedule);
        
        if (!$currentTimeSlot) {
            $morningStart = date('h:i A', strtotime($schedule['sched_morning_in']));
            $morningEnd = date('h:i A', strtotime($schedule['sched_morning_out']));
            $afternoonStart = date('h:i A', strtotime($schedule['sched_afternoon_in']));
            $afternoonEnd = date('h:i A', strtotime($schedule['sched_afternoon_out']));
            
            echo json_encode([
                "status" => "error", 
                "message" => "You are not within your scheduled time. Your schedule: Morning ($morningStart - $morningEnd), Afternoon ($afternoonStart - $afternoonEnd). Current time: " . date('h:i A', strtotime($now))
            ]);
            exit;
        }

        // Check or create today's attendance
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
        $stmt->execute([$employee_id, $today]);
        $attendance = $stmt->fetch();

        if (!$attendance) {
            $pdo->prepare("INSERT INTO attendance (employee_id, date) VALUES (?, ?)")->execute([$employee_id, $today]);
            $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
            $stmt->execute([$employee_id, $today]);
            $attendance = $stmt->fetch();
        }

        $attendance_id = $attendance['id'];
        $field = '';
        $type = '';

        if ($manual_type) {
            $field = str_replace('-', '_', $manual_type);
            $type = $manual_type;

            $validFields = ['morning_in', 'morning_out', 'afternoon_in', 'afternoon_out'];
            if (!in_array($field, $validFields)) {
                echo json_encode(["status" => "error", "message" => "Invalid manual_type."]);
                exit;
            }

            if (!empty($attendance[$field])) {
                echo json_encode(["status" => "info", "message" => ucfirst(str_replace('_', ' ', $field)) . " already logged."]);
                exit;
            }
        } else {
            // Determine which field to update based on current time slot
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
                    "employee_id" => $employee_id,
                    "name" => $name,
                    "date" => $today
                ]);
                exit;
            }
        }

        $update = $pdo->prepare("UPDATE attendance SET `$field` = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$now, $attendance_id]);

        echo json_encode([
            "status" => "success",
            "message" => "Successfully logged $type.",
            "employee_id" => $employee_id,
            "image_url" => $image_path,
            "name" => $name,
            "timestamp" => $now,
            "type" => $type,
            "log_status" => 'first log accepted'
        ]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
