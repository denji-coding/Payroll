<?php
require_once '../app/core/session_helper.php';

// If this is an AJAX request, return JSON on auth failure instead of HTML error page
$isAjax = isset($_GET['ajax']);
if ($isAjax) {
    // Suppress on-screen notices/warnings for JSON cleanliness
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    if (!isAdminLoggedIn()) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
} else {
    // Full page access requires admin
    requireAdminAuth();
}

// Log user activity
logUserActivity('Access timerecords page');

if (!$isAjax) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

require_once '../app/core/database.php';
date_default_timezone_set('Asia/Manila');

// Establish DB connection
$db = new Database();
$pdo = $db->getConnection();
$pdo->exec("SET time_zone = '+08:00'");

// Determine filter date (default: today)
$filterDate = $_GET['date'] ?? date('Y-m-d');

// Fetch attendance records with employee info for the date
$attendanceRecords = [];
try {
    $stmt = $pdo->prepare("\n        SELECT \n            e.photo_path,\n            e.employee_no,\n            CONCAT(\n                e.first_name, ' ',\n                IFNULL(CONCAT(UPPER(LEFT(IFNULL(e.middle_name,''), 1)), '. '), ''),\n                e.last_name\n            ) AS full_name,\n            e.position,\n            a.date,\n            a.morning_in,\n            a.morning_out,\n            a.afternoon_in,\n            a.afternoon_out,\n            a.status\n        FROM attendance a\n        INNER JOIN employees e ON a.employee_id = e.id\n        WHERE DATE(a.date) = :filterDate\n        ORDER BY e.last_name, e.first_name\n    ");
    $stmt->bindParam(':filterDate', $filterDate);
    $stmt->execute();
    $attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log('TimeRecords fetch error: ' . $e->getMessage());
}

// AJAX: return rendered rows for the requested date without full page reload
if ($isAjax) {
    header('Content-Type: application/json');
    ob_start();
    if (!empty($attendanceRecords)) {
        foreach ($attendanceRecords as $index => $rec) {
            $photo = !empty($rec['photo_path']) ? $rec['photo_path'] : '../public/assets/image/default_user_image.svg';
            $name  = ucwords(strtolower($rec['full_name']));
            $pos   = $rec['position'] ?? '';
            $date  = date('Y-m-d', strtotime($rec['date']));
            $min   = $rec['morning_in']   ? date('h:i A', strtotime($rec['morning_in']))   : '-';
            $mout  = $rec['morning_out']  ? date('h:i A', strtotime($rec['morning_out']))  : '-';
            $ain   = $rec['afternoon_in'] ? date('h:i A', strtotime($rec['afternoon_in'])) : '-';
            $aout  = $rec['afternoon_out']? date('h:i A', strtotime($rec['afternoon_out'])): '-';
            $status= $rec['status'] ?? 'Present';
            $statusClass = 'bg-green-100 text-green-800 border-green-200';
            if ($status === 'Late') $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
            if ($status === 'Absent') $statusClass = 'bg-red-100 text-red-800 border-red-200';
            ?>
            <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                <td class="p-4 align-middle font-medium"><?php echo $index + 1; ?></td>
                <td class="p-4 align-middle font-medium">
                    <span class="relative flex shrink-0 overflow-hidden rounded-full h-8 w-8">
                        <img class="aspect-square h-full w-full" src="<?php echo htmlspecialchars($photo); ?>" alt="">
                    </span>
                </td>
                <td class="p-4 align-middle font-medium">
                    <div class="flex items-center space-x-2">
                        <div class="flex flex-col">
                            <span class="font-medium"><?php echo htmlspecialchars($name); ?></span>
                            <span class="text-xs text-gray-500"><?php echo htmlspecialchars($rec['employee_no']); ?></span>
                        </div>
                    </div>
                </td>
                <td class="p-4 align-middle">
                    <span class="text-sm"><?php echo htmlspecialchars($pos); ?></span>
                </td>
                <td class="p-4 align-middle"><?php echo htmlspecialchars($date); ?></td>
                <td class="p-4 align-middle text-center"><?php echo $min; ?></td>
                <td class="p-4 align-middle text-center"><?php echo $mout; ?></td>
                <td class="p-4 align-middle text-center"><?php echo $ain; ?></td>
                <td class="p-4 align-middle text-center"><?php echo $aout; ?></td>
                <td class="p-4 align-middle">
                    <div class="inline-flex items-center rounded-full border <?php echo $statusClass; ?> px-2.5 py-0.5 text-xs font-semibold"><?php echo htmlspecialchars($status); ?></div>
                </td>
            </tr>
            <?php
        }
    } else { ?>
        <tr>
            <td colspan="10" class="px-4 py-6 text-center text-secondary fst-italic bg-light">
                <i class="bi bi-calendar-x me-2"></i>No attendance records found.
            </td>
        </tr>
    <?php }
    $html = ob_get_clean();
    echo json_encode(['status' => 'success', 'html' => $html]);
    exit;
}

require views_path("auth/timerecords");