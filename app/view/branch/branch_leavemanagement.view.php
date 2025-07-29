<?php
$title = "Leave Management";
require_once views_path("partials/header");

echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $feedback = null;

    // Handle rejection or approval
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $leaveId = $_POST['leave_id'] ?? null;
        if (!$leaveId) {
            throw new Exception("Leave ID is required.");
        }

        $managerId = $_SESSION['manager_id'] ?? null;
        if (!$managerId) {
            throw new Exception("Manager ID missing from session.");
        }

        if ($_POST['action'] === 'reject') {
            $rejectionReason = trim($_POST['rejection_reason'] ?? '');
            if ($rejectionReason === '') {
                throw new Exception("Rejection reason is required.");
            }

            $conn->beginTransaction();

            $conn->prepare("INSERT INTO leave_rejections (leave_id, reason, manager_id) 
                            VALUES (:leave_id, :reason, :manager_id)")
                ->execute([
                    ':leave_id' => $leaveId,
                    ':reason' => $rejectionReason,
                    ':manager_id' => $managerId
                ]);

            $conn->prepare("UPDATE leaves 
                            SET status = 'Rejected', manager_id = :manager_id, updated_at = CURRENT_TIMESTAMP 
                            WHERE id = :leave_id")
                ->execute([
                    ':leave_id' => $leaveId,
                    ':manager_id' => $managerId
                ]);

            $conn->commit();
            $feedback = ['type' => 'success', 'message' => 'Leave request rejected successfully.'];

        } elseif ($_POST['action'] === 'approve') {
            $conn->beginTransaction();

            // ✅ Fetch leave info
            $stmt = $conn->prepare("SELECT employee_id, leave_type FROM leaves WHERE id = :leave_id");
            $stmt->execute([':leave_id' => $leaveId]);
            $leave = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$leave) {
                throw new Exception("Leave record not found.");
            }

            $empId = $leave['employee_id'];
            $type = $leave['leave_type'];

            // ✅ Check leave credits
            $creditStmt = $conn->prepare("SELECT allowed, taken FROM leave_credits 
                                          WHERE employee_id = :eid AND leave_type = :type");
            $creditStmt->execute([
                ':eid' => $empId,
                ':type' => $type
            ]);
            $credit = $creditStmt->fetch(PDO::FETCH_ASSOC);

            if (!$credit) {
                throw new Exception("Leave credit record not found.");
            }

            $available = $credit['allowed'] - $credit['taken'];
            if ($available < 1) {
                throw new Exception("The employee has insufficient leave credits for this leave type.");
            }

            // ✅ Approve leave
            $conn->prepare("UPDATE leaves 
                            SET status = 'Approved', manager_id = :manager_id, updated_at = CURRENT_TIMESTAMP 
                            WHERE id = :leave_id")
                ->execute([
                    ':leave_id' => $leaveId,
                    ':manager_id' => $managerId
                ]);

            // ✅ Deduct 1 leave credit only
            $conn->prepare("UPDATE leave_credits 
                            SET taken = taken + 1 
                            WHERE employee_id = :eid AND leave_type = :type")
                ->execute([
                    ':eid' => $empId,
                    ':type' => $type
                ]);

            $conn->commit();
            $feedback = ['type' => 'success', 'message' => 'Leave request approved.'];
        }
    }

    // Ensure manager is logged in
    $managerId = $_SESSION['manager_id'] ?? null;
    if (!$managerId) {
        throw new Exception("Manager ID is missing from session.");
    }

    // ✅ Fetch leave requests
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
        CONCAT(
            UPPER(LEFT(m.m_first_name, 1)), LOWER(SUBSTRING(m.m_first_name FROM 2)), ' ',
            IFNULL(CONCAT(UPPER(LEFT(m.m_middle_name, 1)), '. '), ''),
            UPPER(LEFT(m.m_last_name, 1)), LOWER(SUBSTRING(m.m_last_name FROM 2))
        ) AS rejected_by
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
    $feedback = ['type' => 'error', 'message' => $e->getMessage()];
}
?>


<style>
    .custom-scrollbar::-webkit-scrollbar {
  height: 8px;
  width: 8px;  /* for vertical scrollbar if needed */
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #20b879; /* Tailwind's emerald-400 */
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #16a34a;/* Tailwind's emerald-500 */
}
@keyframes fadeInSlide {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.fade-in-slide {
  animation: fadeInSlide 0.4s ease-out;
}
</style>





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
                    <svg class="absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="leaveSearch"
                        placeholder="Search employees..."
                        class="flex h-10 w-full placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
                    >
                    <button
                        id="clearSearch"
                        class="absolute right-2 top-1 text-[#478547] text-xl hidden"
                        aria-label="Clear search"
                        type="button"
                    >
                        ×
                    </button>
                </div>
            </div>

            <div class="relative w-full overflow-auto custom-scrollbar">
                    <div class="max-h-[calc(100vh-220px)] ">
                <table class="min-w-[1000px] w-full divide-y divide-gray-200 text-sm">

                <thead class="bg-emerald-600 sticky top-0 text-white text-[13.8px]">
                    <tr>
                        <th class="px-3 py-3 text-left font-semibold tracking-wide">Employee</th>
                        <th class="px-3 py-3 text-left font-semibold tracking-wide">Leave Type</th>
                        <th class="px-3 py-3 text-center font-semibold tracking-wide">Start</th>
                        <th class="px-3 py-3 text-center font-semibold tracking-wide">End</th>
                        <th class="px-3 py-3 text-left font-semibold tracking-wide">Duration</th>
                        <th class="px-3 py-3 text-left font-semibold tracking-wide">Reason</th>
                        <th class="px-3 py-3 text-left font-semibold tracking-wide ">Rejection Reason</th>
                        <th class="px-3 py-3 text-center font-semibold tracking-wide">Status</th>
                        <th class="px-3 py-3 text-center font-semibold tracking-wide">Created</th>
                        <th class="px-3 py-3 text-center font-semibold tracking-wide">Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!empty($leaveRequests)): ?>
                    <?php foreach ($leaveRequests as $leaveRequest): ?>
                        <tr class="text-sm border-b border-gray-200 last:border-b-0 hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-3 py-3 whitespace-nowrap"><?= htmlspecialchars($leaveRequest['employee_name']) ?></td>
                            <td class="px-3 py-3 "><?= htmlspecialchars($leaveRequest['leave_type']) ?></td>
                            <td class="px-3 py-3 text-center whitespace-nowrap"><?= htmlspecialchars($leaveRequest['start_date']) ?></td>
                            <td class="px-3 py-3 text-center whitespace-nowrap"><?= htmlspecialchars($leaveRequest['end_date']) ?></td>
                            <td class="px-3 py-3 text-center"><?= htmlspecialchars($leaveRequest['duration']) ?></td>
                            <!-- <td class="px-3 py-3">
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
                            </td> -->
                            <td class="text-center">
                                        <?php
                                            $hasReason = !empty($leaveRequest['reason']);
                                            $hasMedCert = !empty($leaveRequest['med_cert_path']) && $leaveRequest['leave_type'] === 'Sick Leave' && (int)$leaveRequest['duration'] >= 3;
                                            $alwaysShowModalTypes = ['Vacation Leave', 'Maternity/Paternity Leave'];

                                            $showModal = $hasReason || $hasMedCert || in_array($leaveRequest['leave_type'], $alwaysShowModalTypes);
                                        ?>

                                        <?php if ($showModal): ?>
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#reasonModal<?= $leaveRequest['id'] ?>">
                                                <i class="bi bi-eye"></i> 
                                            </button>

                                            <div class="modal fade" id="reasonModal<?= $leaveRequest['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered modal-lg ">
                                                    <div class="modal-content mx-auto" style="width: 90vh; max-height: 80vh; overflow-y: auto;">
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title">Leave Details</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <div class="modal-body px-3 py-3 d-flex flex-column gap-3">
                                                            <!-- Leave Reason -->
                                                            <?php if ($hasReason): ?>
                                                                <div class="text-start">
                                                                    <?= nl2br(htmlspecialchars($leaveRequest['reason'])) ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <!-- Medical Certificate -->
                                                            <?php if ($hasMedCert): ?>
                                                                <div class="text-left">
                                                                    <strong>Medical Certificate:</strong>
                                                                    <div class="d-flex justify-content-center mt-2">
                                                                        <img src="<?= htmlspecialchars($leaveRequest['med_cert_path']) ?>"
                                                                            alt="Medical Certificate"
                                                                            class="img-fluid rounded border"
                                                                            style="max-height: 280px; max-width: 100%; width: auto; ">
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>

                                                            <!-- No content fallback -->
                                                            <?php if (!$hasReason && !$hasMedCert && in_array($leaveRequest['leave_type'], $alwaysShowModalTypes)): ?>
                                                                <div class="fst-italic text-muted">No reason or certificate needed.</div>
                                                            <?php endif; ?>

                                                            <!-- Footer -->
                                                            <div class="pt-2">
                                                                <hr class="w-100 m-0">
                                                                <small class="text-muted">Leave type: <?= htmlspecialchars($leaveRequest['leave_type']) ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">N/A</span>
                                        <?php endif; ?>
                                    </td>

                            <td class="px-3 py-4 text-center">
                                <?php if ($leaveRequest['status'] === 'Rejected' && !empty($leaveRequest['rejection_reason'])): ?>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectReasonModal<?= $leaveRequest['id'] ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <div class="modal fade" id="rejectReasonModal<?= $leaveRequest['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="height: 70vh;">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Rejections Reason</h5>
                                                    <button type="button" class="btn-close" style="filter: brightness(0) invert(1);" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body d-flex flex-column justify-content-between text-left">
                                                    <?= nl2br(htmlspecialchars($leaveRequest['rejection_reason'])) ?>
                                                    <?php if (!empty($leaveRequest['rejected_by'])): ?>
                                                        <div>
                                                            <hr class="w-100 m-0">
                                                            <small class="text-muted">Rejected by: <?= htmlspecialchars($leaveRequest['rejected_by']) ?></small>
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

                            <td class="px-3 py-4 text-center">
                                <?php
                                $badgeClass = match($leaveRequest['status']) {
                                    'Approved' => 'bg-success',
                                    'Rejected' => 'bg-danger',
                                    default => 'bg-warning text-dark'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-1"><?= htmlspecialchars($leaveRequest['status']) ?></span>
                            </td>
                            <td class="px-3 py-4 text-center whitespace-nowrap"><?= date("M d, Y", strtotime($leaveRequest['created_at'])) ?></td>
                            <td class="px-3 py-4">
                                <?php if ($leaveRequest['status'] === 'Pending'): ?>
                                    <div class="d-flex gap-2">
                                        <form method="POST" class="approve-form">
                                            <input type="hidden" name="leave_id" value="<?= $leaveRequest['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $leaveRequest['id'] ?>">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        <div class="modal fade" id="rejectModal<?= $leaveRequest['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">Reject Leave</h5>
                                                            <button type="button" class="btn-close" style="filter: brightness(0) invert(1);" data-bs-dismiss="modal"></button>

                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="leave_id" value="<?= $leaveRequest['id'] ?>">
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
document.addEventListener('DOMContentLoaded', function () {
    const approveForms = document.querySelectorAll('.approve-form');

    approveForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Stop normal form submission

            Swal.fire({
                title: 'Approve Leave?',
                text: 'Are you sure you want to approve this leave request?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                // cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Only submit if user confirms
                }
            });
        });
    });
});
</script>


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




