<?php
$title = "Leave Management";
require_once views_path("partials/header");
require_once views_path("owner/owner_sidebar");

echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

// try {
//     $db = new Database();
//     $conn = $db->getConnection();

//     $feedback = null;

//     // Handle rejection or approval
//     if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
//         $leaveId = $_POST['leave_id'] ?? null;
//         if (!$leaveId) {
//             throw new Exception("Leave ID is required.");
//         }

//         $ownerId = $_SESSION['owner_id'] ?? null;
//         if (!$ownerId) {
//             throw new Exception("Owner ID missing from session.");
//         }

//         if ($_POST['action'] === 'reject') {
//             $rejectionReason = trim($_POST['rejection_reason'] ?? '');
//             if ($rejectionReason === '') {
//                 throw new Exception("Rejection reason is required.");
//             }

//             $conn->beginTransaction();

//             $conn->prepare("INSERT INTO leave_rejections (leave_id, reason, owner_id) 
//                             VALUES (:leave_id, :reason, :owner_id)")
//                 ->execute([
//                     ':leave_id' => $leaveId,
//                     ':reason' => $rejectionReason,
//                     ':owner_id' => $ownerId
//                 ]);

//             $conn->prepare("UPDATE leaves 
//                             SET status = 'Rejected', owner_id = :owner_id, updated_at = CURRENT_TIMESTAMP 
//                             WHERE id = :leave_id")
//                 ->execute([
//                     ':leave_id' => $leaveId,
//                     ':owner_id' => $ownerId
//                 ]);

//             $conn->commit();
//             $feedback = ['type' => 'success', 'message' => 'Leave request rejected successfully.'];

//         } elseif ($_POST['action'] === 'approve') {
//             $conn->beginTransaction();

//             // ✅ Fetch leave info
//             $stmt = $conn->prepare("SELECT employee_id, leave_type FROM leaves WHERE id = :leave_id");
//             $stmt->execute([':leave_id' => $leaveId]);
//             $leave = $stmt->fetch(PDO::FETCH_ASSOC);

//             if (!$leave) {
//                 throw new Exception("Leave record not found.");
//             }

//             $empId = $leave['employee_id'];
//             $type = $leave['leave_type'];

//             // ✅ Get leave duration
//             $durationStmt = $conn->prepare("SELECT duration FROM leaves WHERE id = :leave_id");
//             $durationStmt->execute([':leave_id' => $leaveId]);
//             $duration = $durationStmt->fetchColumn();

//             // ✅ Map leave type to leave_type_id
//             $leaveTypeMapping = [
//                 'Sick Leave' => 1,
//                 'Emergency Leave' => 2,
//                 'Vacation Leave' => 3,
//                 'Personal Leave' => 4,
//                 'Maternity/Paternity Leave' => 5
//             ];
//             $leaveTypeId = $leaveTypeMapping[$type] ?? null;

//             if (!$leaveTypeId) {
//                 throw new Exception("Invalid leave type.");
//             }

//             // ✅ Check leave credits using new structure
//             $creditStmt = $conn->prepare("
//                 SELECT lc.taken, lt.default_allowed 
//                 FROM leave_credits lc
//                 JOIN leave_types lt ON lc.leave_type_id = lt.id
//                 WHERE lc.employee_id = :eid AND lc.leave_type_id = :type_id
//             ");
//             $creditStmt->execute([
//                 ':eid' => $empId,
//                 ':type_id' => $leaveTypeId
//             ]);
//             $credit = $creditStmt->fetch(PDO::FETCH_ASSOC);

//             if (!$credit) {
//                 // Create leave credits record if it doesn't exist
//                 $insertStmt = $conn->prepare("
//                     INSERT INTO leave_credits (employee_id, leave_type_id, taken) 
//                     VALUES (:eid, :type_id, 0)
//                 ");
//                 $insertStmt->execute([
//                     ':eid' => $empId,
//                     ':type_id' => $leaveTypeId
//                 ]);
//                 $credit = ['taken' => 0, 'default_allowed' => 0];
                
//                 // Get the default allowed from leave_types
//                 $defaultStmt = $conn->prepare("SELECT default_allowed FROM leave_types WHERE id = :type_id");
//                 $defaultStmt->execute([':type_id' => $leaveTypeId]);
//                 $credit['default_allowed'] = $defaultStmt->fetchColumn();
//             }

//             $available = $credit['default_allowed'] - $credit['taken'];
//             if ($available < $duration) {
//                 throw new Exception("The employee has insufficient leave credits. Available: {$available} days, Requested: {$duration} days.");
//             }

//             // ✅ Approve leave
//             $conn->prepare("UPDATE leaves 
//                             SET status = 'Approved', owner_id = :owner_id, updated_at = CURRENT_TIMESTAMP 
//                             WHERE id = :leave_id")
//                 ->execute([
//                     ':leave_id' => $leaveId,
//                     ':owner_id' => $ownerId
//                 ]);

//             // ✅ Deduct leave credits based on duration
//             $conn->prepare("UPDATE leave_credits 
//                             SET taken = taken + :duration, updated_at = CURRENT_TIMESTAMP
//                             WHERE employee_id = :eid AND leave_type_id = :type_id")
//                 ->execute([
//                     ':eid' => $empId,
//                     ':type_id' => $leaveTypeId,
//                     ':duration' => $duration
//                 ]);

//             $conn->commit();
//             $feedback = ['type' => 'success', 'message' => 'Leave request approved.'];
//         }
//     }

//     // Ensure owner is logged in
//     $ownerId = $_SESSION['owner_id'] ?? null;
//     if (!$ownerId) {
//         throw new Exception("Owner ID is missing from session.");
//     }

//     // ✅ Fetch leave requests from all branches
//     $sql = "SELECT 
//         l.*, 
//         e.first_name,
//         e.middle_name, 
//         e.last_name,
//         CONCAT(
//             UPPER(LEFT(e.first_name, 1)), LOWER(SUBSTRING(e.first_name FROM 2)), ' ',
//             IFNULL(CONCAT(UPPER(LEFT(e.middle_name, 1)), '. '), ''),
//             UPPER(LEFT(e.last_name, 1)), LOWER(SUBSTRING(e.last_name FROM 2))
//         ) AS employee_name,
//         b.name AS branch_name,
//         lr.reason AS rejection_reason,
//         o.name AS rejected_by
//     FROM leaves l
//     JOIN employees e ON l.employee_id = e.id
//     JOIN branches b ON e.branch_manager = b.id
//     LEFT JOIN leave_rejections lr ON lr.leave_id = l.id
//     LEFT JOIN owners o ON o.id = lr.owner_id
//     ORDER BY l.created_at DESC";

//     $stmt = $conn->prepare($sql);
//     $stmt->execute();
//     $leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// } catch (Exception $e) {
//     if (isset($conn) && $conn->inTransaction()) {
//         $conn->rollBack();
//     }
//     error_log("Error: " . $e->getMessage());
//     $leaveRequests = [];
//     $feedback = ['type' => 'error', 'message' => $e->getMessage()];
// }
// ?>

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

/* Ensure close button is visible */
.btn-close {
    background: transparent;
    border: 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.5;
    cursor: pointer;
    padding: 0;
    width: auto;
    height: auto;
}

.btn-close:hover {
    color: #000;
    text-decoration: none;
    opacity: 0.75;
}

.btn-close:focus {
    outline: none;
    box-shadow: none;
}

/* Compact table styles - no horizontal scroll */
#leaveTable {
    table-layout: fixed;
    width: 100%;
}

#leaveTable th,
#leaveTable td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

#leaveTable th:not(:has(.truncate)),
#leaveTable td:not(:has(.truncate)) {
    white-space: nowrap;
}

.btn-xs {
    padding: 0.15rem 0.35rem;
    font-size: 0.7rem;
    line-height: 1.2;
    min-width: auto;
}

.btn-xs i {
    font-size: 0.75rem;
}
</style>

<!-- Main layout -->
<div class="flex min-h-screen overflow-hidden">
    <main id="mainContent" class="flex-1 p-4 md:p-6 bg-gray-100 transition-margin duration-300 ease-in-out" style="margin-left: 256px; overflow-x: hidden;">
        <div>
            <span class="text-2xl font-bold tracking-tight">Leave Management</span>
            <p class="text-gray-600">Manage leave requests from all branches and view details below.</p>
        </div>

        <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border-b border-gray-200 gap-4">
                <span class="text-lg font-semibold text-gray-800">HR Leave Applications</span>
                <div class="relative w-full sm:max-w-sm sm:w-auto">
                    <svg class="absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="leaveSearch"
                        placeholder="Search HR..."
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

            <div class="relative w-full overflow-hidden custom-scrollbar">
                <div class="max-h-[calc(100vh-220px)] overflow-y-auto">
                <table class="w-full divide-y divide-gray-200 text-xs" id="leaveTable">

                <thead class="bg-emerald-600 sticky top-0 text-white text-[10px] sm:text-xs">
                    <tr>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-left font-semibold tracking-wide" style="max-width: 120px;">HR Name</th>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-left font-semibold tracking-wide" style="max-width: 100px;">Employee ID</th>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-left font-semibold tracking-wide hidden md:table-cell" style="max-width: 100px;">Position</th>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-left font-semibold tracking-wide" style="max-width: 110px;">Leave Type</th>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center font-semibold tracking-wide" style="max-width: 90px;">Start</th>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center font-semibold tracking-wide" style="max-width: 90px;">End</th>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center font-semibold tracking-wide" style="max-width: 70px;">Duration</th>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center font-semibold tracking-wide" style="max-width: 60px;">Reason</th>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center font-semibold tracking-wide" style="max-width: 80px;">Status</th>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center font-semibold tracking-wide hidden xl:table-cell" style="max-width: 100px;">Created At</th>
                        <th class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center font-semibold tracking-wide" style="max-width: 90px;">Actions</th>
                    </tr>
                </thead>

                <tbody id="hrLeaveTableBody">
                    <tr id="noLeavesRow"><td colspan="11" class="text-center px-6 py-4 text-muted">Loading leave applications...</td></tr>
                </tbody>
            </table>
            </div>
        </div>
    </main>
</div>

<script>
// Fetch and display HR leave applications
async function loadHrLeaves() {
    try {
        const response = await fetch('../app/api/owner_hr_leave_approval-api.php');
        const result = await response.json();
        
        const tbody = document.getElementById('hrLeaveTableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success' && result.data && result.data.length > 0) {
            result.data.forEach(leave => {
                const row = createLeaveRow(leave);
                tbody.appendChild(row);
            });
        } else {
            tbody.innerHTML = '<tr id="noLeavesRow"><td colspan="11" class="text-center px-6 py-4 text-muted">No HR leave applications found.</td></tr>';
        }
    } catch (error) {
        console.error('Error loading HR leaves:', error);
        document.getElementById('hrLeaveTableBody').innerHTML = 
            '<tr><td colspan="11" class="text-center px-6 py-4 text-danger">Error loading leave applications.</td></tr>';
    }
}

function createLeaveRow(leave) {
    const tr = document.createElement('tr');
    tr.className = 'text-sm border-b border-gray-200 last:border-b-0 hover:bg-gray-50 transition-colors duration-200 fade-in-slide';
    tr.setAttribute('data-leave-id', leave.id);
    
    const hasReason = leave.reason && leave.reason.trim() !== '';
    const hasMedCert = leave.med_cert_path && leave.leave_type === 'Sick Leave' && parseInt(leave.duration) >= 3;
    const alwaysShowModalTypes = ['Vacation Leave', 'Maternity/Paternity Leave'];
    const showModal = hasReason || hasMedCert || alwaysShowModalTypes.includes(leave.leave_type);
    
    const statusBadgeClass = leave.status === 'Approved' ? 'bg-success' : 
                            leave.status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark';
    
    const createdDate = new Date(leave.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    
    tr.innerHTML = `
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-[10px] sm:text-xs truncate" style="max-width: 120px;" title="${escapeHtml(leave.hr_name || 'N/A')}">${escapeHtml(leave.hr_name || 'N/A')}</td>
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-[10px] sm:text-xs truncate" style="max-width: 100px;" title="${escapeHtml(leave.hr_employee_id || 'N/A')}">${escapeHtml(leave.hr_employee_id || 'N/A')}</td>
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-[10px] sm:text-xs truncate hidden md:table-cell" style="max-width: 100px;" title="${escapeHtml(leave.hr_position || 'N/A')}">${escapeHtml(leave.hr_position || 'N/A')}</td>
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-[10px] sm:text-xs truncate" style="max-width: 110px;" title="${escapeHtml(leave.leave_type)}">${escapeHtml(leave.leave_type)}</td>
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center text-[10px] sm:text-xs whitespace-nowrap" style="max-width: 90px;">${escapeHtml(leave.start_date)}</td>
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center text-[10px] sm:text-xs whitespace-nowrap" style="max-width: 90px;">${escapeHtml(leave.end_date)}</td>
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center text-[10px] sm:text-xs" style="max-width: 70px;">${leave.duration}d</td>
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center" style="max-width: 60px;">
            ${showModal ? `
                <button class="btn btn-xs btn-outline-success" data-bs-toggle="modal" data-bs-target="#reasonModal${leave.id}" title="View Details">
                    <i class="bi bi-eye" style="font-size: 0.75rem;"></i>
                </button>
                <div class="modal fade" id="reasonModal${leave.id}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content mx-auto" style="width: 90vh; max-height: 80vh; overflow-y: auto;">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Leave Details</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body px-3 py-3 d-flex flex-column gap-3">
                                ${hasReason ? `<div class="text-start">${escapeHtml(leave.reason).replace(/\n/g, '<br>')}</div>` : ''}
                                ${hasMedCert ? `
                                    <div class="text-left">
                                        <strong>Medical Certificate:</strong>
                                        <div class="d-flex justify-content-center mt-2">
                                            <img src="../public/${leave.med_cert_path}" alt="Medical Certificate" class="img-fluid rounded border" style="max-height: 280px; max-width: 100%;">
                                        </div>
                                    </div>
                                ` : ''}
                                ${!hasReason && !hasMedCert && alwaysShowModalTypes.includes(leave.leave_type) ? 
                                    '<div class="fst-italic text-muted">No reason or certificate needed.</div>' : ''}
                                <div class="pt-2">
                                    <hr class="w-100 m-0">
                                    <small class="text-muted">Leave type: ${escapeHtml(leave.leave_type)}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            ` : '<span class="text-muted fst-italic">N/A</span>'}
        </td>
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center" style="max-width: 80px;">
            <span class="badge ${statusBadgeClass} rounded-pill px-1.5 py-0.5 text-[10px]">${escapeHtml(leave.status)}</span>
        </td>
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center text-[10px] sm:text-xs whitespace-nowrap hidden xl:table-cell" style="max-width: 100px;">${createdDate}</td>
        <td class="px-1.5 py-1.5 sm:px-2 sm:py-2 text-center" style="max-width: 90px;">
            ${leave.status === 'Pending' ? `
                <div class="d-flex gap-1 justify-content-center">
                    <button class="btn btn-xs btn-success approve-btn" data-leave-id="${leave.id}" title="Approve">
                        <i class="bi bi-check2-circle" style="font-size: 0.75rem;"></i>
                    </button>
                    <button class="btn btn-xs btn-danger reject-btn" data-leave-id="${leave.id}" data-bs-toggle="modal" data-bs-target="#rejectModal${leave.id}" title="Reject">
                        <i class="bi bi-x-circle" style="font-size: 0.75rem;"></i>
                    </button>
                    <div class="modal fade" id="rejectModal${leave.id}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Reject Leave</h5>
                                    <button type="button" class="btn-close" style="filter: brightness(0) invert(1);" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label d-block text-start">Reason</label>
                                    <textarea id="rejectReason${leave.id}" class="form-control" rows="4" required style="resize: none; overflow: auto;"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger btn-sm confirm-reject-btn" data-leave-id="${leave.id}">Submit Reject</button>
                                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            ` : '<span class="text-muted fst-italic d-block text-center">Done</span>'}
        </td>
    `;
    
    // Add event listeners for approve/reject buttons
    if (leave.status === 'Pending') {
        const approveBtn = tr.querySelector('.approve-btn');
        if (approveBtn) {
            approveBtn.addEventListener('click', () => handleApprove(leave.id));
        }
        
        const rejectBtn = tr.querySelector('.confirm-reject-btn');
        if (rejectBtn) {
            rejectBtn.addEventListener('click', () => {
                const reason = document.getElementById(`rejectReason${leave.id}`).value.trim();
                if (!reason) {
                    Swal.fire('Error', 'Please provide a rejection reason.', 'error');
                    return;
                }
                handleReject(leave.id, reason);
            });
        }
    }
    
    return tr;
}

async function handleApprove(leaveId) {
    const result = await Swal.fire({
        title: 'Approve Leave?',
        text: 'Are you sure you want to approve this leave request?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve it!',
    });
    
    if (result.isConfirmed) {
        try {
            const response = await fetch('../app/api/owner_hr_leave_approval-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'approve', leave_id: leaveId })
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                Swal.fire('Success', data.message, 'success').then(() => loadHrLeaves());
            } else {
                Swal.fire('Error', data.message || 'Failed to approve leave', 'error');
            }
        } catch (error) {
            console.error('Error approving leave:', error);
            Swal.fire('Error', 'Failed to approve leave. Please try again.', 'error');
        }
    }
}

async function handleReject(leaveId, reason) {
    try {
        const response = await fetch('../app/api/owner_hr_leave_approval-api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'reject', leave_id: leaveId, remarks: reason })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById(`rejectModal${leaveId}`));
            if (modal) modal.hide();
            
            Swal.fire('Success', data.message, 'success').then(() => loadHrLeaves());
        } else {
            Swal.fire('Error', data.message || 'Failed to reject leave', 'error');
        }
    } catch (error) {
        console.error('Error rejecting leave:', error);
        Swal.fire('Error', 'Failed to reject leave. Please try again.', 'error');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load leaves on page load
document.addEventListener('DOMContentLoaded', function() {
    loadHrLeaves();
});
</script>


<script>
const searchInput = document.getElementById('leaveSearch');
const clearBtn = document.getElementById('clearSearch');
const table = document.getElementById('leaveTable');
const tbody = document.getElementById('hrLeaveTableBody');

if (searchInput && table && tbody) {
  searchInput.addEventListener('input', () => {
    // Only show clear button if it exists
    if (clearBtn) {
      clearBtn.style.display = searchInput.value ? 'block' : 'none';
    }

    const searchTerm = searchInput.value.toLowerCase();
    const rows = Array.from(tbody.rows).filter(row => row.id !== 'noLeavesRow' && row.id !== 'noSearchResultsRow');

    let visibleCount = 0;

    rows.forEach(row => {
      const rowText = row.textContent.toLowerCase();
      const match = rowText.includes(searchTerm);
      row.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    // Remove existing "no search results" row if it exists
    const existingNoResultsRow = document.getElementById('noSearchResultsRow');
    if (existingNoResultsRow) {
      existingNoResultsRow.remove();
    }

    // Show "no results" message if search has a term but no matches
    if (searchTerm && visibleCount === 0) {
      const noResultsRow = document.createElement('tr');
      noResultsRow.id = 'noSearchResultsRow';
      noResultsRow.className = 'fade-in-slide';
      noResultsRow.innerHTML = '<td colspan="11" class="text-center px-6 py-4 text-muted"><i class="bi bi-search me-2"></i>No records found matching your search.</td>';
      tbody.appendChild(noResultsRow);
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

<!-- <?php if ($feedback): ?> -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?= $feedback['type'] === 'success' ? 'success' : 'error' ?>',
            title: '<?= addslashes($feedback['message']) ?>',
            timer: 3000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = 'index.php?payroll=owner_leavemanagement';
        });
    });
</script>
<?php endif; ?>

