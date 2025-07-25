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
        } elseif ($employee_id_manual && $manual_type) {
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_no = ?");
            $stmt->execute([$employee_id_manual]);
        } else {
            echo json_encode(["status" => "error", "message" => "RFID or Employee ID with manual type is required."]);
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
            foreach (['morning_in', 'morning_out', 'afternoon_in', 'afternoon_out'] as $f) {
                if (!$attendance[$f]) {
                    $field = $f;
                    $type = str_replace('_', '-', $f);
                    break;
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
            "image_url" => 'http://localhost/mvcPayroll/public/' . $image_path,
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
