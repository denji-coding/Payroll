<?php
require_once __DIR__ . '/../core/database.php';

header("Content-Type: application/json");

$db = new Database();
$pdo = $db->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Create a new benefit rate
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        !isset($data['sss_rate']) || !isset($data['pagibig_rate']) || !isset($data['philhealth_rate']) ||
        !is_numeric($data['sss_rate']) || !is_numeric($data['pagibig_rate']) || !is_numeric($data['philhealth_rate'])
    ) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Set all other benefit rates to inactive
        $pdo->prepare("UPDATE benefit_rates SET status = 'inactive' WHERE status = 'active'")->execute();

        // Insert new active rate
        $stmt = $pdo->prepare("INSERT INTO benefit_rates (sss_rate, pagibig_rate, philhealth_rate, updated_at, status)
                                VALUES (?, ?, ?, NOW(), 'active')");
        $stmt->execute([
            $data['sss_rate'],
            $data['pagibig_rate'],
            $data['philhealth_rate']
        ]);

        $pdo->commit();

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    // Delete a specific rate by ID
    parse_str($_SERVER['QUERY_STRING'], $query);
    $id = $query['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing ID.']);
        exit;
    }

    try {
        // Prevent deletion of latest rate
        $check = $pdo->prepare("SELECT id FROM benefit_rates ORDER BY updated_at DESC LIMIT 1");
        $check->execute();
        $latestId = $check->fetchColumn();

        if ($id == $latestId) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete the latest rate.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM benefit_rates WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Delete failed: ' . $e->getMessage()]);
    }
} elseif ($method === 'GET') {
    // Return the table rows HTML for refresh
    try {
        $stmt = $pdo->prepare("SELECT * FROM benefit_rates ORDER BY updated_at DESC");
        $stmt->execute();
        $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_start();
        foreach ($rates as $index => $rate) {
            $isLatest = $index === 0;
            ?>
            <tr class="transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd] fade-in-slide">
              <td class="p-3 text-center font-semibold text-[#133913]"><?= $index + 1 ?></td>
              <td class="p-3 text-center"><?= htmlspecialchars($rate['sss_rate']) ?>%</td>
              <td class="p-3 text-center"><?= htmlspecialchars($rate['pagibig_rate']) ?>%</td>
              <td class="p-3 text-center"><?= htmlspecialchars($rate['philhealth_rate']) ?>%</td>
              <td class="p-3 text-sm text-center text-gray-500"><?= date("M d, Y h:i A", strtotime($rate['updated_at'])) ?></td>
              <td class="p-3 text-center">
                <?php if ($isLatest): ?>
                  <span class="inline-block px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active Rate</span>
                <?php else: ?>
                  <span class="inline-block px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="p-3 text-center">
                <?php if (!$isLatest): ?>
                  <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $rate['id'] ?>" title="Delete Rate">
                    <i class="bi bi-trash text-center"></i>
                  </button>
                <?php else: ?>
                  <span class="text-gray-400">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php
        }
        $html = ob_get_clean();

        echo json_encode(['status' => 'success', 'html' => $html]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Fetch failed: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
}
