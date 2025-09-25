<?php
$title = "Leave Credits Management";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");
?>

<style>
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

.leave-credits-card {
    background: #ffffff;
    border: 2px solid #bbf7d0; /* green-200 */
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-left: 11px;
    margin-right: 11px;
}

.credits-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.credits-table th {
    background: #ffffff;
    color: #478547;
    font-weight: 700;
    text-transform: none;
    font-size: 0.9rem;
    letter-spacing: 0;
}

/* Ensure sticky headers stay fixed within scroll containers */
/* .credits-table {
    border-collapse: separate;
    border-spacing: 0;
} */
.credits-table thead th {
    position: sticky;
    top: 0;
    z-index: 12;
    background: #ffffff;
}

/* Custom checkbox styling for Apply to all existing employees */
input#applyToAllEmployees.form-check-input {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid #16a249; /* green */
    border-radius: 4px;
    background: #ffffff;
    display: inline-block;
    position: relative;
    cursor: pointer;
    transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}
input#applyToAllEmployees.form-check-input:focus {
    outline: none;
    box-shadow: 0 0 0 0.15rem rgba(22, 162, 73, 0.25);
}
input#applyToAllEmployees.form-check-input:checked {
    background-color: #16a249;
    border-color: #16a249;
}
input#applyToAllEmployees.form-check-input:checked::after {
    content: '\2713'; /* check mark */
    position: absolute;
    top: -2px;
    left: 2px;
    font-size: 16px;
    color: #ffffff;
    line-height: 18px;
}

/* Same custom checkbox styling for Bulk Update modal */
input#applyToAll.form-check-input {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid #16a249; /* green */
    border-radius: 4px;
    background: #ffffff;
    display: inline-block;
    position: relative;
    cursor: pointer;
    transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}
input#applyToAll.form-check-input:focus {
    outline: none;
    box-shadow: 0 0 0 0.15rem rgba(22, 162, 73, 0.25);
}
input#applyToAll.form-check-input:checked {
    background-color: #16a249;
    border-color: #16a249;
}
input#applyToAll.form-check-input:checked::after {
    content: '\2713'; /* check mark */
    position: absolute;
    top: -2px;
    left: 2px;
    font-size: 16px;
    color: #ffffff;
    line-height: 18px;
}

#leaveTypesTable,
#employeeDetailsTable {
    position: relative;
}

.credits-table td {
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.credits-table tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.badge-credits {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 50px;
    font-weight: 600;
}

.badge-allowed {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.badge-taken {
    background: linear-gradient(135deg, #dc3545, #e74c3c);
    color: white;
}

.badge-remaining {
    background: linear-gradient(135deg, #17a2b8, #6f42c1);
    color: white;
}

.btn-action {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-edit {
    background: linear-gradient(135deg, #ffc107, #ffb300);
    color: #212529;
    border: none;
}

.btn-edit:hover {
    background: linear-gradient(135deg, #ffb300, #ff8f00);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
}

.btn-delete {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    border: none;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #c82333, #bd2130);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.modal-header {
    background: linear-gradient(135deg, #206037 0%, #1a5a2e 100%);
    color: white;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
}

.modal-content {
    border: none;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.form-control:focus {
    border-color: #206037;
    box-shadow: 0 0 0 0.2rem rgba(32, 96, 55, 0.25);
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stats-card h3 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

.stats-card p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.875rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Mobile Backdrop */
.mobile-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 35;
    display: none;
}

.mobile-backdrop.show {
    display: block;
}

/* Responsive Layout */
@media (max-width: 768px) {
    .main-content-wrapper {
        margin-left: 0 !important;
        margin-top: 56px !important;
    }
    
    #adminSidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        z-index: 40;
    }
    
    #adminSidebar.show {
        transform: translateX(0);
    }
}

/* Ensure proper spacing */
.main-content-wrapper {
    background-color: #f8f9fa;
    min-height: calc(100vh - 56px);
}

/* Fix any overflow issues */
body {
    overflow-x: hidden;
}
</style>

<!-- Mobile Backdrop -->
<div id="mobileBackdrop" class="mobile-backdrop"></div>

<!-- Mobile Menu Toggle Button -->
<button id="mobileMenuToggle" class="d-md-none fixed top-4 left-4 z-50 btn btn-success btn-sm">
    <i class="bi bi-list"></i>
</button>

<!-- Main Content Wrapper -->
<div class="main-content-wrapper" style="margin-left: 256px; margin-top: 56px; min-height: calc(100vh - 56px); transition: margin-left 0.3s ease;">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-2xl ml-2 font-bold tracking-tight text-[#133913]">Leave Credits Management</span>
                <p class="text-[#478547] ml-2">Manage employee leave credits and allocations.</p>
            </div>
                <button type="button" class="btn btn-success d-inline-flex align-items-center h-10 px-4 py-2 " data-bs-toggle="modal" data-bs-target="#addLeaveTypeModal">
                    <i class="bi bi-plus-circle me-2"></i>
                    <span class="font-semibold">Add Leave Type</span>
                </button>
                <!-- <button id="showAddEmployeeModal" 
                            class="btn btn-success d-inline-flex align-items-center h-10 px-4 py-2 " 
                            data-bs-toggle="modal" 
                            data-bs-target="#addEmployeeModal">
                    <i class="fas fa-plus me-2"></i>
                    <span class="font-semibold">Add Employee</span>
                    </button> -->
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <!-- <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <h3><?= count($leaveTypeStats) ?></h3>
                <p>Active Leave Types</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3><?= count($employees) ?></h3>
                <p>Total Employees</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3><?= count($leaveCredits) ?></h3>
                <p>Total Records</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <h3><?= array_sum(array_column($leaveTypeStats, 'total_allowed')) ?></h3>
                <p>Total Allowed Days</p>
            </div>
        </div>
    </div> -->

    <!-- Leave Credits Table -->
    <div class="row">
        <div class="col-12">
            <div class="leave-credits-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-dark fw-bold">
                        <!-- <i class="bi bi-table me-2"></i> -->
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success btn-sm" onclick="toggleView()">
                            <i class="bi bi-eye me-1"></i>
                            <span id="viewToggleText">Show Details</span>
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="bulkUpdateLeaveTypes()">
                            <i class="bi bi-gear me-1"></i>
                            Bulk Update Leave Types
                        </button>
                        <!-- <button class="btn btn-outline-success btn-sm" onclick="refreshTable()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button> -->
                    </div>
                </div>

                <?php if (empty($leaveTypeStats)): ?>
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <h4>No Leave Types Found</h4>
                        <p>Start by adding leave types to manage employee leave credits.</p>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addLeaveTypeModal">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add First Leave Type
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Leave Types Overview Table -->
                    <div class="table-responsive overflow-y-auto max-h-[357px] transition-all duration-300 ease-in-out" id="leaveTypesTable">
                        <table class="min-w-full table-auto md:table-fixed credits-table mb-0">
                            <thead class="sticky top-0 z-10">
                                <tr class="border-b hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                    <th class="h-12 px-2 md:px-4 text-left text-sm font-bold text-[#478547] bg-white">Leave Type</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Employees</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Total Allowed</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Total Taken</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Total Remaining</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Average per Employee</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Range (Min-Max)</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 text-sm">
                                <?php foreach ($leaveTypeStats as $stat): 
                                    $totalRemaining = $stat['total_allowed'] - $stat['total_taken'];
                                ?>
                                    <tr class="fade-in-slide border-b-0 hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-calendar-check text-white"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($stat['leave_type']) ?></div>
                                                    <!-- <small class="text-muted">Leave Type</small> -->
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?= $stat['employee_count'] ?> employees</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-credits badge-allowed" id="total-allowed-<?= $stat['leave_type'] ?>"><?= $stat['total_allowed'] ?> days</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-credits badge-taken" id="total-taken-<?= $stat['leave_type'] ?>"><?= $stat['total_taken'] ?> days</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-credits badge-remaining"><?= $totalRemaining ?> days</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?= round($stat['avg_allowed'], 1) ?> days</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark"><?= $stat['min_allowed'] ?>-<?= $stat['max_allowed'] ?> days</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group gap-2" role="group">

                                                <button type="button" class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                                                        data-action="view-leave-type"
                                                        data-type='<?= htmlspecialchars($stat['leave_type'], ENT_QUOTES) ?>'
                                                        title="View Employee Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>

                                                <button type="button" class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-red-600 hover:text-white"
                                                        data-action="delete-leave-type"
                                                        data-id="<?= (int)$stat['id'] ?>"
                                                        data-type='<?= htmlspecialchars($stat['leave_type'], ENT_QUOTES) ?>'
                                                        title="Delete Leave Type">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Employee Details Table (Hidden by default) -->
                    <div class="table-responsive overflow-y-auto max-h-[357px] transition-all duration-300 ease-in-out d-none" id="employeeDetailsTable">
                        <table class="min-w-full table-auto md:table-fixed credits-table mb-0">
                            <thead class="sticky top-0 z-10">
                                <tr class="border-b hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                    <th class="h-12 px-2 md:px-4 text-left text-sm font-bold text-[#478547] bg-white">Employee</th>
                                    <th class="h-12 px-2 md:px-4 text-left text-sm font-bold text-[#478547] bg-white">Leave Type</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Allowed</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Taken</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Remaining</th>
                                    <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 text-sm">
                                <?php foreach ($leaveCredits as $credit): 
                                    $remaining = $credit['default_allowed'] - $credit['taken'];
                                ?>
                                    <tr class="fade-in-slide border-b-0 hover:bg-[#f2f8f2] even:bg-[#cde4cd]" data-default-allowed="<?= $credit['default_allowed'] ?>">
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person text-white"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars(ucwords(strtolower($credit['first_name'] . ' ' . $credit['last_name']))) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($credit['employee_no']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-primary"><?= htmlspecialchars($credit['leave_type']) ?></span>
                                        </td>
                                        <td class="text-center px-4 py-3">
                                            <span class="badge badge-credits badge-allowed"><?= $credit['default_allowed'] ?> days</span>
                                            <!-- <small class="text-muted d-block">(from leave type)</small> -->
                                        </td>
                                        <td class="text-center px-4 py-3">
                                            <span class="badge badge-credits badge-taken" id="taken-<?= $credit['id'] ?>"><?= $credit['taken'] ?> days</span>
                                        </td>
                                        <td class="text-center px-4 py-3">
                                            <span class="badge badge-credits badge-remaining"><?= $remaining ?> days</span>
                                        </td>
                                        <td class="text-center px-4 py-3">
                                            <div class="btn-group gap-2" role="group">
                                                <button type="button" class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white" 
                                                        onclick='editCredits(<?= json_encode($credit, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'
                                                        title="Edit Leave Credits">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-red-600 hover:text-white" 
                                                        onclick='deleteCredits(<?= (int)$credit['id'] ?>, <?= json_encode($credit['first_name'] . ' ' . $credit['last_name']) ?>)'
                                                        title="Delete Leave Credits">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Leave Type Modal -->
<div class="modal fade" id="addLeaveTypeModal" tabindex="-1" aria-labelledby="addLeaveTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLeaveTypeModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Leave Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="index.php?payroll=leave_credits">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_leave_type">
                    
                    <div class="mb-3">
                        <label for="leave_type_name" class="form-label">Leave Type Name</label>
                        <input type="text" class="form-control" id="leave_type_name" name="leave_type_name" 
                               required placeholder="e.g., Sick Leave, Vacation Leave">
                        <div class="form-text">Enter a descriptive name for the leave type</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="default_allowed" class="form-label">Default Allowed Days</label>
                        <input type="number" class="form-control" id="default_allowed" name="default_allowed" 
                               min="0" max="365" required placeholder="Enter default days">
                        <div class="form-text">Number of days allowed by default for new employees</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="3" placeholder="Enter a description for this leave type"></textarea>
                        <div class="form-text">Optional description explaining when this leave type should be used</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="apply_to_all_employees" id="applyToAllEmployees" checked>
                        <label class="form-check-label" for="applyToAllEmployees">
                            <strong>Apply to all existing employees</strong>
                            <br>
                            <small class="text-muted">
                                If checked, all existing employees will automatically get leave credits for this new leave type with 0 taken days.
                            </small>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>
                        Add Leave Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Leave Credits Modal -->
<div class="modal fade" id="editCreditsModal" tabindex="-1" aria-labelledby="editCreditsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCreditsModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>
                    Edit Leave Credits
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="index.php?payroll=leave_credits">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_credits">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" id="edit_employee_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Leave Type</label>
                        <input type="text" class="form-control" id="edit_leave_type" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Default Allowed Days</label>
                        <input type="text" class="form-control" id="edit_default_allowed" readonly>
                        <div class="form-text">This is set by the leave type configuration</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_taken" class="form-label">Taken Days</label>
                        <input type="number" class="form-control" id="edit_taken" name="taken" 
                               min="0" max="365" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-2"></i>
                        Update Credits
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Update Leave Types Modal -->
<div class="modal fade" id="bulkUpdateLeaveTypesModal" tabindex="-1" aria-labelledby="bulkUpdateLeaveTypesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkUpdateLeaveTypesModalLabel">
                    <i class="bi bi-gear me-2"></i>
                    Bulk Update Leave Types
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="index.php?payroll=leave_credits" id="bulkUpdateForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="bulk_update_leave_types">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Bulk Update:</strong> Update default allowed days for all leave types at once. 
                        You can choose to apply changes to all existing employees or just update the defaults for new employees.
                    </div>
                    
                    <div class="row">
                        <?php foreach ($leaveTypes as $type): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="bi bi-calendar-check me-2"></i>
                                        <?= htmlspecialchars($type['name']) ?>
                                    </h6>
                                    
                                    <div class="mb-2">
                                        <label class="form-label small">Default Allowed Days</label>
                                        <input type="number" 
                                               class="form-control form-control-sm" 
                                               name="leave_types[<?= $type['id'] ?>][default_allowed]" 
                                               value="<?= $type['default_allowed'] ?>" 
                                               min="0" max="365" required>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <label class="form-label small">Description</label>
                                        <textarea class="form-control form-control-sm" 
                                                  name="leave_types[<?= $type['id'] ?>][description]" 
                                                  rows="2" 
                                                  placeholder="Leave type description"><?= htmlspecialchars($type['description']) ?></textarea>
                                    </div>
                                    
                                    <small class="text-muted">
                                        Current: <?= $type['default_allowed'] ?> days
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="apply_to_all" id="applyToAll" checked>
                        <label class="form-check-label" for="applyToAll">
                            <strong>Apply changes to all existing employees</strong>
                            <br>
                            <small class="text-muted">
                                If checked, all employees will have their leave credits updated with the new defaults. 
                                If an employee has taken more days than the new default, their taken days will be capped to the new default.
                            </small>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-2"></i>
                        Update All Leave Types
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit credits function
function editCredits(credit) {
    document.getElementById('edit_id').value = credit.id;
    document.getElementById('edit_employee_name').value = credit.first_name + ' ' + credit.last_name;
    document.getElementById('edit_leave_type').value = credit.leave_type;
    document.getElementById('edit_default_allowed').value = credit.default_allowed + ' days (from leave type)';
    document.getElementById('edit_taken').value = credit.taken;
    
    const editModal = new bootstrap.Modal(document.getElementById('editCreditsModal'));
    editModal.show();
}

// Delete credits function
function deleteCredits(id, employeeName) {
    Swal.fire({
        title: 'Are you sure?',
        text: `This will permanently delete leave credits for ${employeeName}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?payroll=leave_credits';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_credits';
            form.appendChild(actionInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Refresh table function
function refreshTable() {
    // Show loading state
    const refreshBtn = document.querySelector('button[onclick="refreshTable()"]');
    const originalContent = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refreshing...';
    refreshBtn.disabled = true;
    
    // Fetch updated data via AJAX
    fetch('index.php?payroll=leave_credits&action=refresh_data', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update statistics cards if they exist
            if (data.stats) {
                updateStatisticsCards(data.stats);
            }
            
            // Update leave types table
            if (data.leaveTypeStats) {
                updateLeaveTypesTable(data.leaveTypeStats);
            }
            
            // Update employee details table
            if (data.leaveCredits) {
                updateEmployeeDetailsTable(data.leaveCredits);
            }
            
            // Table refreshed successfully - no popup needed
        } else {
            throw new Error(data.message || 'Failed to refresh data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Failed to refresh table data.',
            icon: 'error'
        });
    })
    .finally(() => {
        // Restore button state
        refreshBtn.innerHTML = originalContent;
        refreshBtn.disabled = false;
    });
}

// Update statistics cards
function updateStatisticsCards(stats) {
    const cards = document.querySelectorAll('.stats-card h3');
    if (cards.length >= 4) {
        cards[0].textContent = stats.activeLeaveTypes || 0;
        cards[1].textContent = stats.totalEmployees || 0;
        cards[2].textContent = stats.totalRecords || 0;
        cards[3].textContent = stats.totalAllowedDays || 0;
    }
}

// Update leave types table
function updateLeaveTypesTable(leaveTypeStats) {
    const tbody = document.querySelector('#leaveTypesTable tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    leaveTypeStats.forEach(stat => {
        const totalRemaining = stat.total_allowed - stat.total_taken;
        const row = document.createElement('tr');
        row.className = 'fade-in-slide';
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <i class="bi bi-calendar-check text-white"></i>
                    </div>
                    <div>
                        <div class="fw-semibold text-dark">${stat.leave_type}</div>
                        <small class="text-muted">Leave Type</small>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <span class="badge bg-info">${stat.employee_count} employees</span>
            </td>
            <td class="text-center">
                <span class="badge badge-credits badge-allowed" id="total-allowed-${stat.leave_type}">${stat.total_allowed} days</span>
            </td>
            <td class="text-center">
                <span class="badge badge-credits badge-taken" id="total-taken-${stat.leave_type}">${stat.total_taken} days</span>
            </td>
            <td class="text-center">
                <span class="badge badge-credits badge-remaining">${totalRemaining} days</span>
            </td>
            <td class="text-center">
                <span class="badge bg-secondary">${Math.round(stat.avg_allowed * 10) / 10} days</span>
            </td>
            <td class="text-center">
                <span class="badge bg-warning text-dark">${stat.min_allowed}-${stat.max_allowed} days</span>
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-action btn-delete btn-sm" 
                            onclick="deleteLeaveType(${stat.id}, '${stat.leave_type}')"
                            title="Delete Leave Type">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button type="button" class="btn btn-action btn-info btn-sm" 
                            onclick="viewLeaveTypeDetails('${stat.leave_type}')"
                            title="View Employee Details">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Update employee details table
function updateEmployeeDetailsTable(leaveCredits) {
    const tbody = document.querySelector('#employeeDetailsTable tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    leaveCredits.forEach(credit => {
        const remaining = credit.default_allowed - credit.taken;
        const row = document.createElement('tr');
        row.className = 'fade-in-slide';
        row.setAttribute('data-default-allowed', credit.default_allowed);
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <i class="bi bi-person text-white"></i>
                    </div>
                    <div>
                        <div class="fw-semibold text-dark">${credit.first_name} ${credit.last_name}</div>
                        <small class="text-muted">${credit.employee_no}</small>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-primary">${credit.leave_type}</span>
            </td>
            <td class="text-center">
                <span class="badge badge-credits badge-allowed">${credit.default_allowed} days</span>
                <small class="text-muted d-block">(from leave type)</small>
            </td>
            <td class="text-center">
                <span class="badge badge-credits badge-taken" id="taken-${credit.id}">${credit.taken} days</span>
            </td>
            <td class="text-center">
                <span class="badge badge-credits badge-remaining">${remaining} days</span>
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-action btn-edit btn-sm" 
                            onclick="editCredits(${JSON.stringify(credit).replace(/"/g, '&quot;')})"
                            title="Edit Leave Credits">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-action btn-delete btn-sm" 
                            onclick="deleteCredits(${credit.id}, '${credit.first_name} ${credit.last_name}')"
                            title="Delete Leave Credits">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}



// Toggle between leave types view and employee details view
function toggleView() {
    const leaveTypesTable = document.getElementById('leaveTypesTable');
    const employeeDetailsTable = document.getElementById('employeeDetailsTable');
    const toggleText = document.getElementById('viewToggleText');
    
    if (leaveTypesTable.classList.contains('d-none')) {
        // Show leave types view
        leaveTypesTable.classList.remove('d-none');
        employeeDetailsTable.classList.add('d-none');
        toggleText.textContent = 'Show Details';
    } else {
        // Show employee details view
        leaveTypesTable.classList.add('d-none');
        employeeDetailsTable.classList.remove('d-none');
        toggleText.textContent = 'Show Overview';
    }
}


// Bulk update leave types function
function bulkUpdateLeaveTypes() {
    const modal = new bootstrap.Modal(document.getElementById('bulkUpdateLeaveTypesModal'));
    modal.show();
}

// Delete leave type function
function deleteLeaveType(leaveTypeId, leaveTypeName) {
    Swal.fire({
        title: 'Are you sure?',
        html: `
            <div class="text-start">
                <p class="mb-3">You are about to delete the leave type:</p>
                <div class="alert alert-warning">
                    <strong>${leaveTypeName}</strong>
                </div>
                <p class="mb-3"><strong>Warning:</strong> This action will:</p>
                <ul class="text-start">
                    <li>Delete the leave type permanently</li>
                    <li>Remove all associated leave credits for all employees</li>
                    <li>Affect any pending leave requests of this type</li>
                </ul>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?payroll=leave_credits';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_leave_type';
            form.appendChild(actionInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'leave_type_id';
            idInput.value = leaveTypeId;
            form.appendChild(idInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// View leave type details (filtered employee list)
function viewLeaveTypeDetails(leaveType) {
    // Filter the employee details table to show only this leave type
    const employeeDetailsTable = document.getElementById('employeeDetailsTable');
    const rows = employeeDetailsTable.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const leaveTypeCell = row.querySelector('td:nth-child(2) .badge');
        if (leaveTypeCell && leaveTypeCell.textContent.trim() === leaveType) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Switch to employee details view
    document.getElementById('leaveTypesTable').classList.add('d-none');
    employeeDetailsTable.classList.remove('d-none');
    document.getElementById('viewToggleText').textContent = 'Show Overview';
    
    // Show filter message
    // Swal.fire({
    //     title: 'Filtered View',
    //     text: `Showing employees with ${leaveType} leave credits`,
    //     icon: 'info',
    //     timer: 2000,
    //     showConfirmButton: false
    // });
}

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('mobileBackdrop');
    const mainContent = document.querySelector('.main-content-wrapper');
    
    function toggleSidebar() {
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('show');
    }
    
    function closeSidebar() {
        sidebar.classList.remove('show');
        backdrop.classList.remove('show');
    }
    
    if (mobileMenuToggle && sidebar && backdrop) {
        mobileMenuToggle.addEventListener('click', toggleSidebar);
        backdrop.addEventListener('click', closeSidebar);
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !mobileMenuToggle.contains(e.target)) {
                closeSidebar();
            }
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
});

// Show success/error messages
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['success_message'])): ?>
        Swal.fire({
            title: 'Success!',
            text: '<?= addslashes($_SESSION['success_message']) ?>',
            icon: 'success',
            timer: 1500,
            timerProgressBar: true,
            showConfirmButton: false
        });
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        Swal.fire({
            title: 'Error!',
            text: '<?= addslashes($_SESSION['error_message']) ?>',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    // Delegate clicks for leave type actions (view/delete)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        if (action === 'view-leave-type') {
            const type = btn.getAttribute('data-type');
            if (type) {
                viewLeaveTypeDetails(type);
            }
        } else if (action === 'delete-leave-type') {
            const id = parseInt(btn.getAttribute('data-id')) || 0;
            const type = btn.getAttribute('data-type') || '';
            if (id) {
                deleteLeaveType(id, type);
            }
        }
    });
});
</script>

    </div> <!-- End container-fluid -->
</div> <!-- End main-content-wrapper -->

<?php require_once views_path("partials/footer"); ?>










