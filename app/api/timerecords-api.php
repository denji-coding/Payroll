<?php
require_once __DIR__ . '/../core/session_helper.php';
require_once __DIR__ . '/../core/database.php';

header('Content-Type: application/json');

// Auth: only admin can fetch
if (!isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    date_default_timezone_set('Asia/Manila');
    $showAll = isset($_GET['all']) && (int)$_GET['all'] === 1;
    $filterDate = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : date('Y-m-d');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 10)));
    $offset = ($page - 1) * $perPage;

    $db = new Database();
    $pdo = $db->getConnection();
    $pdo->exec("SET time_zone = '+08:00'");

    if ($showAll) {
        // Total count for all
        $countStmt = $pdo->query("SELECT COUNT(*) AS cnt FROM attendance");
        $total = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        $stmt = $pdo->prepare("\n            SELECT \n                e.photo_path,\n                e.employee_no,\n                CONCAT(\n                    e.first_name, ' ',\n                    IFNULL(CONCAT(UPPER(LEFT(IFNULL(e.middle_name,''), 1)), '. '), ''),\n                    e.last_name\n                ) AS full_name,\n                e.position,\n                a.date,\n                a.morning_in, a.morning_out, a.afternoon_in, a.afternoon_out,\n                a.status\n            FROM attendance a\n            INNER JOIN employees e ON a.employee_id = e.id\n            ORDER BY a.date DESC, e.last_name, e.first_name\n            LIMIT :limit OFFSET :offset\n        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    } else {
        // Total count for date
        $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM attendance WHERE DATE(date) = :d");
        $countStmt->bindParam(':d', $filterDate);
        $countStmt->execute();
        $total = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        $stmt = $pdo->prepare("\n            SELECT \n                e.photo_path,\n                e.employee_no,\n                CONCAT(\n                    e.first_name, ' ',\n                    IFNULL(CONCAT(UPPER(LEFT(IFNULL(e.middle_name,''), 1)), '. '), ''),\n                    e.last_name\n                ) AS full_name,\n                e.position,\n                a.date,\n                a.morning_in, a.morning_out, a.afternoon_in, a.afternoon_out,\n                a.status\n            FROM attendance a\n            INNER JOIN employees e ON a.employee_id = e.id\n            WHERE DATE(a.date) = :filterDate\n            ORDER BY e.last_name, e.first_name\n            LIMIT :limit OFFSET :offset\n        ");
        $stmt->bindParam(':filterDate', $filterDate);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    ob_start();
    if (!empty($rows)) {
        foreach ($rows as $index => $rec) {
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
                <td class="p-2 align-middle font-medium"><?php echo ($offset + $index + 1); ?></td>
                <td class="p-2 align-middle font-medium">
                    <span class="relative flex shrink-0 overflow-hidden rounded-full h-8 w-8">
                        <img class="aspect-square h-full w-full" src="<?php echo htmlspecialchars($photo); ?>" alt="">
                    </span>
                </td>
                <td class="p-2 align-middle font-medium">
                    <div class="flex items-center space-x-2">
                        <div class="flex flex-col">
                            <span class="font-medium text-sm"><?php echo htmlspecialchars($name); ?></span>
                            <span class="text-[11px] text-gray-500"><?php echo htmlspecialchars($rec['employee_no']); ?></span>
                        </div>
                    </div>
                </td>
                <td class="p-2 align-middle">
                    <span class="text-sm"><?php echo htmlspecialchars($pos); ?></span>
                </td>
                <td class="p-2 align-middle"><?php echo htmlspecialchars($date); ?></td>
                <td class="p-2 align-middle text-center"><?php echo $min; ?></td>
                <td class="p-2 align-middle text-center"><?php echo $mout; ?></td>
                <td class="p-2 align-middle text-center"><?php echo $ain; ?></td>
                <td class="p-2 align-middle text-center"><?php echo $aout; ?></td>
                <td class="p-2 align-middle">
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
    $totalPages = (int)ceil(($total ?? 0) / $perPage);
    echo json_encode([
        'status' => 'success',
        'html' => $html,
        'meta' => [
            'total' => $total ?? 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}

