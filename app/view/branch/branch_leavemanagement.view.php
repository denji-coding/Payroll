<?php

$title = "Leave Management";
require_once views_path("partials/header");

echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $feedback = null;

    // Handle rejection or approval submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $leaveId = $_POST['leave_id'] ?? null;

        if (!$leaveId) {
            throw new Exception("Leave ID is required.");
        }

        if ($_POST['action'] === 'reject') {
            $rejectionReason = trim($_POST['rejection_reason'] ?? '');
            $managerId = $_SESSION['manager_id'] ?? null;

            if ($rejectionReason === '') {
                throw new Exception("Rejection reason is required.");
            }

            if (!$managerId) {
                throw new Exception("Manager ID missing from session.");
            }

            // Begin transaction
            $conn->beginTransaction();

            // Insert rejection reason and manager
            $insertSql = "INSERT INTO leave_rejections (leave_id, reason, manager_id) 
                          VALUES (:leave_id, :reason, :manager_id)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->execute([
                ':leave_id'   => $leaveId,
                ':reason'     => $rejectionReason,
                ':manager_id' => $managerId
            ]);

            // Update leave status and manager
            $updateSql = "UPDATE leaves 
                          SET status = 'Rejected', manager_id = :manager_id, updated_at = CURRENT_TIMESTAMP 
                          WHERE id = :leave_id";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([
                ':leave_id' => $leaveId,
                ':manager_id' => $managerId
            ]);

            $conn->commit();

            $feedback = ['type' => 'success', 'message' => 'Leave request rejected successfully.'];

        } elseif ($_POST['action'] === 'approve') {
            $managerId = $_SESSION['manager_id'] ?? null;

            if (!$managerId) {
                throw new Exception("Manager ID missing from session.");
            }

            $updateSql = "UPDATE leaves 
                          SET status = 'Approved', manager_id = :manager_id, updated_at = CURRENT_TIMESTAMP 
                          WHERE id = :leave_id";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([
                ':leave_id' => $leaveId,
                ':manager_id' => $managerId
            ]);

            $feedback = ['type' => 'success', 'message' => 'Leave request approved successfully.'];
        }
    }   
   // Ensure manager is logged in
$managerId = $_SESSION['manager_id'] ?? null;
if (!$managerId) {
    throw new Exception("Manager ID is missing from session.");
}

// Fetch leave requests of employees under the logged-in manager
$sql = "SELECT 
    l.*, 
    e.first_name,
    e.middle_name, 
    e.last_name,
    CONCAT(
    UPPER(LEFT(e.first_name, 1)), LOWER(SUBSTRING(e.first_name FROM 2)), ' ',
    IFNULL(CONCAT(UPPER(LEFT(e.middle_name, 1)), '. '), ''),
    UPPER(LEFT(e.last_name, 1)), LOWER(SUBSTRING(e.last_name FROM 2))
    ) AS employee_name,
    lr.reason AS rejection_reason,
    m.name AS rejected_by
FROM leaves l
JOIN employees e ON l.employee_id = e.id
LEFT JOIN leave_rejections lr ON lr.leave_id = l.id
LEFT JOIN managers m ON m.id = lr.manager_id
WHERE e.branch_manager = :manager_id
ORDER BY l.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute(['manager_id' => $managerId]);
$leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error: " . $e->getMessage());
    $leaveRequests = [];
    $feedback = ['type' => 'error', 'message' => 'An error occurred: ' . htmlspecialchars($e->getMessage())];
}
?>



<!-- Main layout -->
<div class="flex min-h-screen overflow-hidden">
    <main id="mainContent" class="flex-1 p-6 bg-gray-100 transition-margin duration-300 ease-in-out" style="margin-left: 256px;">
        <?php require_once views_path("branch/branch_sidebar"); ?>

        <div>
            <span class="text-2xl font-bold tracking-tight">Leave Management</span>
            <p class="text-gray-600">Manage leave requests and view details below.</p>
        </div>

        <div class="mt-6 -ml-1 bg-white shadow rounded-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-gray-200 relative">
                <span class="text-lg font-semibold text-gray-800">Employee's Leave Application</span>
                <div class="relative max-w-sm w-full sm:w-auto">
                    <input
                        type="text"
                        id="leaveSearch"
                        placeholder="Search employees..."
                        class="flex h-10 w-full placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
                    >
                    <button
                        id="clearSearch"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-sm text-gray-400 hover:text-gray-600 hidden"
                        aria-label="Clear search"
                        type="button"
                    >
                        &#x2715;
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="leaveTable" class="min-w-full divide-y divide-gray-200 text-sm overflow-hidden">
                <thead class="bg-emerald-600 text-white text-center">

                <tr>
                    <th class="px-3 py-3  font-semibold tracking-wide">Employee</th>
                    <th class="px-3 py-3 text-left font-semibold tracking-wide">Leave Type</th>
                    <th class="px-3 py-3 text-left font-semibold tracking-wide">Start</th>
                    <th class="px-3 py-3 text-left font-semibold tracking-wide">End</th>
                    <th class="px-3 py-3 text-left font-semibold tracking-wide">Duration</th>
                    <th class="px-3 py-3 text-left font-semibold tracking-wide">Reason</th>
                    <th class="px-3 py-3 text-left font-semibold tracking-wide">Rejection Reason</th>
                    <th class="px-3 py-3 text-left font-semibold tracking-wide">Status</th>
                    <th class="px-3 py-3 text-left font-semibold tracking-wide">Created</th>
                    <th class="px-3 py-3 text-right font-semibold tracking-wide">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($leaveRequests)): ?>
                    <?php foreach ($leaveRequests as $leave): ?>
                        <tr class="text-sm">
                            <td class="px-3 py-3"><?= htmlspecialchars($leave['employee_name']) ?></td>
                            <td class="px-3 py-3"><?= htmlspecialchars($leave['leave_type']) ?></td>
                            <td class="px-3 py-3"><?= htmlspecialchars($leave['start_date']) ?></td>
                            <td class="px-3 py-3"><?= htmlspecialchars($leave['end_date']) ?></td>
                            <td class="px-3 py-3"><?= htmlspecialchars($leave['duration']) ?></td>
                            <td class="px-3 py-3">
                                <button class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#reasonModal<?= $leave['id'] ?>">
                                    View
                                </button>
                                <div class="modal fade" id="reasonModal<?= $leave['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content" style="height: 50vh;">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">Leave Reason</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body d-flex flex-column justify-content-between">
                                                <div>
                                                    <?= nl2br(htmlspecialchars($leave['reason'])) ?>
                                                </div>
                                                <?php if (!empty($leave['leave_type'])): ?>
                                                    <div>
                                                        <hr class="w-100 m-0">
                                                        <small class="text-muted">Leave Type: <?= htmlspecialchars($leave['leave_type']) ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-3 py-4 text-center">
                                <?php if ($leave['status'] === 'Rejected' && !empty($leave['rejection_reason'])): ?>
                                    <button class="btn btn-sm btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectReasonModal<?= $leave['id'] ?>">
                                        View
                                    </button>
                                    <div class="modal fade" id="rejectReasonModal<?= $leave['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="height: 70vh;">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Rejections Reason</h5>
                                                    <button type="button" class="btn-close" style="filter: brightness(0) invert(1);" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body d-flex flex-column justify-content-between text-left">
                                                    <?= nl2br(htmlspecialchars($leave['rejection_reason'])) ?>
                                                    <?php if (!empty($leave['rejected_by'])): ?>
                                                      <div>
                                                        <hr class="w-100 m-0">
                                                        <small class="text-muted">Rejected by: <?= htmlspecialchars($leave['rejected_by']) ?></small>
                                                      </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-3 py-4">
                                <?php
                                $badgeClass = match($leave['status']) {
                                    'Approved' => 'bg-success',
                                    'Rejected' => 'bg-danger',
                                    default => 'bg-warning text-dark'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-1"><?= htmlspecialchars($leave['status']) ?></span>
                            </td>
                            <td class="px-3 py-4"><?= htmlspecialchars($leave['created_at']) ?></td>
                            <td class="px-3 py-4">
                                <?php if ($leave['status'] === 'Pending'): ?>
                                    <div class="d-flex gap-2">
                                        <form method="POST">
                                            <input type="hidden" name="leave_id" value="<?= $leave['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $leave['id'] ?>">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        <div class="modal fade" id="rejectModal<?= $leave['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">Reject Leave</h5>
                                                            <button type="button" class="btn-close" style="filter: brightness(0) invert(1);" data-bs-dismiss="modal"></button>

                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="leave_id" value="<?= $leave['id'] ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <label class="form-label d-block text-start">Reason</label>
                                                            <textarea name="rejection_reason" class="form-control" rows="4" required style="resize: none; overflow: auto;"></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-danger btn-sm">Submit Reject</button>
                                                            <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic d-block text-center">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="noLeavesRow"><td colspan="10" class="text-center text-muted">No leave requests found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </main>
</div>

<script>
const searchInput = document.getElementById('leaveSearch');
const clearBtn = document.getElementById('clearSearch');
const table = document.getElementById('leaveTable');
const noResultsRow = document.getElementById('noLeavesRow');

if (searchInput && table) {
  searchInput.addEventListener('input', () => {
    // Only show clear button if it exists
    if (clearBtn) {
      clearBtn.style.display = searchInput.value ? 'block' : 'none';
    }

    const searchTerm = searchInput.value.toLowerCase();
    const rows = Array.from(table.tBodies[0].rows).filter(row => row.id !== 'noLeavesRow');

    let visibleCount = 0;

    rows.forEach(row => {
      const rowText = row.textContent.toLowerCase();
      const match = rowText.includes(searchTerm);
      row.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    // Show or hide the "no results" row if it exists
    if (noResultsRow) {
      noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
    }
  });
}

if (clearBtn && searchInput) {
  clearBtn.addEventListener('click', () => {
    searchInput.value = '';
    clearBtn.style.display = 'none';
    searchInput.dispatchEvent(new Event('input'));
  });
}
</script>



<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>

<?php if ($feedback): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?= $feedback['type'] === 'success' ? 'success' : 'error' ?>',
            title: '<?= addslashes($feedback['message']) ?>',
            timer: 3000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = 'index.php?payroll=branch_leavemanagement';
        });
    });
</script>
<?php endif; ?>




