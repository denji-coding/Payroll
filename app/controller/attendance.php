<?php
require_once '../app/core/database.php';
// Required for SweetAlert2 session-based popups

date_default_timezone_set('Asia/Manila');

$db = new Database();
$pdo = $db->getConnection();
$pdo->exec("SET time_zone = '+08:00'");

if (isset($_GET['payroll']) && $_GET['payroll'] === 'attendance') {
    try {
        $filterDate = $_GET['date'] ?? date('Y-m-d');
        // $formattedDate = date('F j, Y', strtotime($filterDate));
        $query = "
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
";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':filterDate', $filterDate);
        $stmt->execute();
        $attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $attendanceRecords = [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        $rfid = $_POST['rfid'] ?? null;
        $employee_id_manual = $_POST['employee_id'] ?? null;
        $manual_type = $_POST['manual_type'] ?? null;

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
        $type = $manual_type ?? 'auto';
        $field = '';
        $log_status = 'on time';
        $logged = false;

        if ($manual_type || $employee_id_manual) {
        // Smart logic just like RFID: determine next available field
        if (!$attendance['morning_in']) {
            $field = 'morning_in';
            $type = 'morning-in';
        } elseif (!$attendance['morning_out']) {
            $field = 'morning_out';
            $type = 'morning-out';
        } elseif (!$attendance['afternoon_in']) {
            $field = 'afternoon_in';
            $type = 'afternoon-in';
        } elseif (!$attendance['afternoon_out']) {
            $field = 'afternoon_out';
            $type = 'afternoon-out';
        } else {
            echo json_encode([
                "status" => "info",
                "message" => "Attendance for today is already complete.",
                "employee_id" => $employee_id,
                "name" => $name,
                "date" => $today
            ]);
            exit;
        }

        if ($attendance[$field]) {
            echo json_encode([
                "status" => "info",
                "message" => "You have already logged your " . str_replace('_', ' ', $field) . " today.",
                "employee_id" => $employee_id,
                "name" => $name,
                "date" => $today
            ]);
            exit;
        }

        $update = $pdo->prepare("UPDATE attendance SET `$field` = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$now, $attendance_id]);
        $logged = true;
    }elseif ($rfid) {
            if (!$attendance['morning_in']) {
                $field = 'morning_in';
                $type = 'morning-in';
                $logged = true;
            } elseif (!$attendance['morning_out']) {
                $field = 'morning_out';
                $type = 'morning-out';
                $logged = true;
            } elseif (!$attendance['afternoon_in']) {
                $field = 'afternoon_in';
                $type = 'afternoon-in';
                $logged = true;
            } elseif (!$attendance['afternoon_out']) {
                $field = 'afternoon_out';
                $type = 'afternoon-out';
                $logged = true;
            } else {
                echo json_encode([
                    "status" => "info",
                    "message" => "Attendance for today is already complete.",
                    "employee_id" => $employee_id,
                    "name" => $name,
                    "date" => $today
                ]);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE attendance SET `$field` = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$now, $attendance_id]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid attendance request."]);
            exit;
        }

        if ($logged) {
            $_SESSION['attendance_popup'] = [
                'alert_message' => ucfirst(str_replace('-', ' ', $type)) . " successfully recorded for $name.",
                'attendance_message' => "You logged at " . date("h:i A", strtotime($now)) . ".",
                'alert_type' => 'success',
                'image_url' => $image_path,
                'attendance_action' => strtoupper($type),
                'popup_border_color' => "rgb(76, 180, 76)"
            ];
        }

        echo json_encode([
            "status" => "success",
            "message" => "Successfully logged $type.",
            "employee_id" => $employee_id,
            "image_url" => 'http://localhost/mvcPayroll/public/' . $image_path,
            "name" => $name,
            "timestamp" => $now,
            "type" => $type,
            "log_status" => $log_status
        ]);
        exit;

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        exit;
    }
}

// For GET requests: Render the attendance view
$current_time = date("h:i:s A");
$current_date = date("F j, Y");
require views_path("auth/attendance");
