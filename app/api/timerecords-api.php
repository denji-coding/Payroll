<?php
require_once __DIR__ . '/../core/secure_session.php';
startSecureSession();
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
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    $isSearchMode = !empty($searchTerm);
    
    // For search mode: no pagination, show all results
    if ($isSearchMode) {
        $page = 1;
        $perPage = 10000; // Very high limit to get all results
        $offset = 0;
    } else {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 10)));
    $offset = ($page - 1) * $perPage;
    }

    $db = new Database();
    $pdo = $db->getConnection();
    $pdo->exec("SET time_zone = '+08:00'");

    if ($isSearchMode) {
        // Search mode: query across ALL dates, no date filter
        // Convert search term to lowercase for case-insensitive matching
        $searchLower = strtolower($searchTerm);
        $searchPattern = '%' . $searchLower . '%';
        
        // Simplified count query - use CONCAT_WS to handle NULLs better
        $countStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT a.id) AS cnt 
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN managers m ON a.manager_id = m.id AND m.deleted_at IS NULL
            LEFT JOIN admins ad ON a.hr_id = ad.id AND ad.deleted_at IS NULL
            WHERE (a.employee_id IS NOT NULL OR a.manager_id IS NOT NULL OR a.hr_id IS NOT NULL)
            AND (
                LOWER(COALESCE(e.employee_no, m.m_employee_id, ad.hr_employee_id, '')) LIKE :search1
                OR LOWER(CONCAT_WS(' ', COALESCE(e.first_name, ''), COALESCE(e.middle_name, ''), COALESCE(e.last_name, ''))) LIKE :search2
                OR LOWER(CONCAT_WS(' ', COALESCE(m.m_first_name, ''), COALESCE(m.m_middle_name, ''), COALESCE(m.m_last_name, ''))) LIKE :search3
                OR LOWER(CONCAT_WS(' ', COALESCE(ad.hr_first_name, ''), COALESCE(ad.hr_middle_name, ''), COALESCE(ad.hr_last_name, ''))) LIKE :search4
            )
        ");
        $countStmt->execute([
            ':search1' => $searchPattern,
            ':search2' => $searchPattern,
            ':search3' => $searchPattern,
            ':search4' => $searchPattern
        ]);
        $total = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // Search query - no pagination, get all results
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(e.photo_path, m.m_photo_path, ad.hr_photo_path) AS photo_path,
                COALESCE(e.employee_no, m.m_employee_id, ad.hr_employee_id) AS employee_no,
                COALESCE(
                    CONCAT(e.first_name, ' ', IFNULL(CONCAT(UPPER(LEFT(IFNULL(e.middle_name,''), 1)), '. '), ''), e.last_name),
                    CONCAT(m.m_first_name, ' ', IFNULL(CONCAT(UPPER(LEFT(IFNULL(m.m_middle_name,''), 1)), '. '), ''), m.m_last_name),
                    CONCAT(ad.hr_first_name, ' ', IFNULL(CONCAT(UPPER(LEFT(IFNULL(ad.hr_middle_name,''), 1)), '. '), ''), ad.hr_last_name)
                ) AS full_name,
                COALESCE(e.position, m.m_position, ad.hr_position) AS position,
                a.date,
                a.morning_in, a.morning_out, a.afternoon_in, a.afternoon_out,
                a.status
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN managers m ON a.manager_id = m.id AND m.deleted_at IS NULL
            LEFT JOIN admins ad ON a.hr_id = ad.id AND ad.deleted_at IS NULL
            WHERE (a.employee_id IS NOT NULL OR a.manager_id IS NOT NULL OR a.hr_id IS NOT NULL)
            AND (
                LOWER(COALESCE(e.employee_no, m.m_employee_id, ad.hr_employee_id, '')) LIKE :search1
                OR LOWER(CONCAT_WS(' ', COALESCE(e.first_name, ''), COALESCE(e.middle_name, ''), COALESCE(e.last_name, ''))) LIKE :search2
                OR LOWER(CONCAT_WS(' ', COALESCE(m.m_first_name, ''), COALESCE(m.m_middle_name, ''), COALESCE(m.m_last_name, ''))) LIKE :search3
                OR LOWER(CONCAT_WS(' ', COALESCE(ad.hr_first_name, ''), COALESCE(ad.hr_middle_name, ''), COALESCE(ad.hr_last_name, ''))) LIKE :search4
            )
            ORDER BY a.date DESC, full_name
            LIMIT :limit
        ");
        // Note: execute() will be called later at the common point
    } elseif ($showAll) {
        // Total count for all - count all attendance records (employees, managers, HR)
        $countStmt = $pdo->query("SELECT COUNT(*) AS cnt FROM attendance WHERE employee_id IS NOT NULL OR manager_id IS NOT NULL OR hr_id IS NOT NULL");
        $total = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(e.photo_path, m.m_photo_path, ad.hr_photo_path) AS photo_path,
                COALESCE(e.employee_no, m.m_employee_id, ad.hr_employee_id) AS employee_no,
                COALESCE(
                    CONCAT(e.first_name, ' ', IFNULL(CONCAT(UPPER(LEFT(IFNULL(e.middle_name,''), 1)), '. '), ''), e.last_name),
                    CONCAT(m.m_first_name, ' ', IFNULL(CONCAT(UPPER(LEFT(IFNULL(m.m_middle_name,''), 1)), '. '), ''), m.m_last_name),
                    CONCAT(ad.hr_first_name, ' ', IFNULL(CONCAT(UPPER(LEFT(IFNULL(ad.hr_middle_name,''), 1)), '. '), ''), ad.hr_last_name)
                ) AS full_name,
                COALESCE(e.position, m.m_position, ad.hr_position) AS position,
                a.date,
                a.morning_in, a.morning_out, a.afternoon_in, a.afternoon_out,
                a.status
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN managers m ON a.manager_id = m.id AND m.deleted_at IS NULL
            LEFT JOIN admins ad ON a.hr_id = ad.id AND ad.deleted_at IS NULL
            WHERE a.employee_id IS NOT NULL OR a.manager_id IS NOT NULL OR a.hr_id IS NOT NULL
            ORDER BY a.date DESC, full_name
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    } else {
        // Total count for date - count all attendance records (employees, managers, HR)
        // Count records with employee_id, manager_id, or hr_id
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) AS cnt FROM attendance 
            WHERE DATE(date) = :d 
            AND (employee_id IS NOT NULL OR manager_id IS NOT NULL OR hr_id IS NOT NULL)
        ");
        $countStmt->bindParam(':d', $filterDate);
        $countStmt->execute();
        $total = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // Build query to include employees, managers, and HR
        // This ensures all attendance records are included regardless of type
        $unionQuery = "
            SELECT 
                COALESCE(e.photo_path, m.m_photo_path, ad.hr_photo_path) AS photo_path,
                COALESCE(e.employee_no, m.m_employee_id, ad.hr_employee_id) AS employee_no,
                COALESCE(
                    CONCAT(e.first_name, ' ', IFNULL(CONCAT(UPPER(LEFT(IFNULL(e.middle_name,''), 1)), '. '), ''), e.last_name),
                    CONCAT(m.m_first_name, ' ', IFNULL(CONCAT(UPPER(LEFT(IFNULL(m.m_middle_name,''), 1)), '. '), ''), m.m_last_name),
                    CONCAT(ad.hr_first_name, ' ', IFNULL(CONCAT(UPPER(LEFT(IFNULL(ad.hr_middle_name,''), 1)), '. '), ''), ad.hr_last_name)
                ) AS full_name,
                COALESCE(e.position, m.m_position, ad.hr_position) AS position,
                a.date,
                a.morning_in, a.morning_out, a.afternoon_in, a.afternoon_out,
                a.status
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN managers m ON a.manager_id = m.id AND m.deleted_at IS NULL
            LEFT JOIN admins ad ON a.hr_id = ad.id AND ad.deleted_at IS NULL
            WHERE DATE(a.date) = :filterDate
            AND (a.employee_id IS NOT NULL OR a.manager_id IS NOT NULL OR a.hr_id IS NOT NULL)
            ORDER BY full_name
        ";
        
        if ($total <= $perPage && $page === 1) {
            $stmt = $pdo->prepare($unionQuery);
            $stmt->bindParam(':filterDate', $filterDate);
        } else {
            $stmt = $pdo->prepare($unionQuery . " LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':filterDate', $filterDate);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }
    }
    
    // Execute the prepared statement with appropriate parameters
    if ($isSearchMode) {
        $stmt->execute([
            ':search1' => $searchPattern,
            ':search2' => $searchPattern,
            ':search3' => $searchPattern,
            ':search4' => $searchPattern,
            ':limit' => $perPage
        ]);
    } else {
    $stmt->execute();
    }
    
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    ob_start();
    if (!empty($rows)) {
        foreach ($rows as $index => $rec) {
            // For search mode, start numbering from 1, otherwise use offset
            $rowNumber = $isSearchMode ? ($index + 1) : ($offset + $index + 1);
            // Handle photo path - may be from employees, managers, or HR
            $photoPath = $rec['photo_path'] ?? '';
            if (!empty($photoPath)) {
                // Check if path already includes upload/ or ../
                if (strpos($photoPath, '../') === 0 || strpos($photoPath, 'upload/') === 0) {
                    $photo = $photoPath;
                } else {
                    $photo = 'upload/' . $photoPath;
                }
            } else {
                $photo = '../public/assets/image/default_user_image.svg';
            }
            
            $name  = ucwords(strtolower($rec['full_name'] ?? 'Unknown'));
            $pos   = $rec['position'] ?? '';
            $empNo = $rec['employee_no'] ?? 'N/A';
            $date  = date('d-M-Y', strtotime($rec['date']));
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
                <td class="p-2 align-middle font-medium"><?php echo $rowNumber; ?></td>
                <td class="p-2 align-middle font-medium">
                    <span class="relative flex shrink-0 overflow-hidden rounded-full h-8 w-8">
                        <img class="aspect-square h-full w-full" src="<?php echo htmlspecialchars($photo); ?>" alt="">
                    </span>
                </td>
                <td class="p-2 align-middle font-medium">
                    <div class="flex items-center space-x-2">
                        <div class="flex flex-col">
                            <span class="font-medium text-sm"><?php echo htmlspecialchars($name); ?></span>
                            <span class="text-[11px] text-gray-500"><?php echo htmlspecialchars($empNo); ?></span>
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
    
    // For search mode: always show as 1 page, no pagination
    if ($isSearchMode) {
        $totalPages = 1;
    } else {
    $totalPages = (int)ceil(($total ?? 0) / $perPage);
    }
    
    echo json_encode([
        'status' => 'success',
        'html' => $html,
        'meta' => [
            'total' => $total ?? 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'is_search_mode' => $isSearchMode,
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Server error'
    ]);
}

