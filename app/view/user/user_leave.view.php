<?php
$title = "Leave Application";
require_once views_path("partials/header");
echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

// Add Bootstrap Icons CSS
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">';

// Add Flatpickr CSS and JS
echo '<link rel="stylesheet" href="../public/assets/css/flatpickr/material_green.css">';
echo '<script src="../public/assets/js/flatpickr/flatpickr.min.js"></script>';

require_once '../app/core/database.php'; // adjust path if needed
$db = new Database();
$conn = $db->getConnection();

// Get user ID - support employees, managers, and HR
$employeeId = $_SESSION['employee_id'] ?? $_SESSION['employee_no'] ?? null;
$managerId = $_SESSION['manager_id'] ?? null;
$hrId = $_SESSION['SESSION_USER_ID'] ?? null;

// Determine user type and ID
$userType = 'employee';
$userId = $employeeId;

if ($hrId) {
    $userType = 'hr';
    $userId = $hrId;
} elseif ($managerId) {
    $userType = 'manager';
    $userId = $managerId;
}

// Function to return border color based on leave type
if (!function_exists('getBorderColorClass')) {
function getBorderColorClass($type) {
    switch ($type) {
        case 'Sick Leave': return 'border-l-blue-500';
        case 'Emergency Leave': return 'border-l-red-500';
        case 'Vacation Leave': return 'border-l-yellow-500';
        case 'Personal Leave': return 'border-l-purple-500';
        case 'Maternity/Paternity Leave': return 'border-l-green-500';
        default: return 'border-l-gray-400';
    }
}
}

// Auto-initialize leave credits if none exist using new structure
if ($userId) {
    // Build check query based on user type
    if ($userType === 'employee') {
        $check = $conn->prepare("SELECT 1 FROM leave_credits WHERE employee_id = ? AND user_type = 'employee'");
        $check->execute([$userId]);
        $insertQuery = "INSERT INTO leave_credits (employee_id, leave_type_id, taken, user_type) VALUES (?, ?, 0, 'employee')";
        $insertParams = [$userId];
    } elseif ($userType === 'manager') {
        $check = $conn->prepare("SELECT 1 FROM leave_credits WHERE manager_id = ? AND user_type = 'manager'");
        $check->execute([$userId]);
        // For managers: employee_id must be NULL, manager_id is set
        $insertQuery = "INSERT INTO leave_credits (employee_id, manager_id, leave_type_id, taken, user_type) VALUES (NULL, ?, ?, 0, 'manager')";
        $insertParams = [$userId];
    } elseif ($userType === 'hr') {
        $check = $conn->prepare("SELECT 1 FROM leave_credits WHERE hr_id = ? AND user_type = 'hr'");
        $check->execute([$userId]);
        // For HR: employee_id must be NULL, hr_id is set
        $insertQuery = "INSERT INTO leave_credits (employee_id, hr_id, leave_type_id, taken, user_type) VALUES (NULL, ?, ?, 0, 'hr')";
        $insertParams = [$userId];
    }
    
    if (!$check->fetch()) {
        // Get all leave types and create leave credits for this user
        $leaveTypesStmt = $conn->prepare("SELECT id, name, default_allowed FROM leave_types WHERE is_active = TRUE");
        $leaveTypesStmt->execute();
        $leaveTypes = $leaveTypesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $insert = $conn->prepare($insertQuery);
        foreach ($leaveTypes as $type) {
            // For employee: [userId, typeId]
            // For manager/HR: [userId, typeId] (employee_id is NULL in the query)
            $insert->execute(array_merge($insertParams, [$type['id']]));
        }
    }
}

// Fetch dynamic leave credits from DB using new structure
$leaveSummary = [];
if ($userId) {
    // Build query based on user type
    if ($userType === 'employee') {
        $query = $conn->prepare("
            SELECT 
                lt.name as leave_type,
                lt.default_allowed as allowed,
                COALESCE(lc.taken, 0) as taken
            FROM leave_types lt
            LEFT JOIN leave_credits lc ON lt.id = lc.leave_type_id AND lc.employee_id = ? AND lc.user_type = 'employee'
            WHERE lt.is_active = TRUE
            ORDER BY lt.name
        ");
        $query->execute([$userId]);
    } elseif ($userType === 'manager') {
        $query = $conn->prepare("
            SELECT 
                lt.name as leave_type,
                lt.default_allowed as allowed,
                COALESCE(lc.taken, 0) as taken
            FROM leave_types lt
            LEFT JOIN leave_credits lc ON lt.id = lc.leave_type_id AND lc.manager_id = ? AND lc.user_type = 'manager'
            WHERE lt.is_active = TRUE
            ORDER BY lt.name
        ");
        $query->execute([$userId]);
    } elseif ($userType === 'hr') {
        $query = $conn->prepare("
            SELECT 
                lt.name as leave_type,
                lt.default_allowed as allowed,
                COALESCE(lc.taken, 0) as taken
            FROM leave_types lt
            LEFT JOIN leave_credits lc ON lt.id = lc.leave_type_id AND lc.hr_id = ? AND lc.user_type = 'hr'
            WHERE lt.is_active = TRUE
            ORDER BY lt.name
        ");
        $query->execute([$userId]);
    }
    $credits = $query->fetchAll(PDO::FETCH_ASSOC);
} else {
    $credits = [];
}

foreach ($credits as $row) {
    $type = $row['leave_type'];
    $leaveSummary[$type] = [
        'allowed' => (int)$row['allowed'],
        'taken' => (int)$row['taken'],
        'color' => getBorderColorClass($type),
    ];
}



// Optional mobile check (not effective in PHP)
$isMobile = false;
?>


<style>
    #leaveTableBody tr:last-child td {
  border-bottom: none !important;
}
    table {
    margin-bottom: 0 !important;
  }
/* Responsive adjustments for leave page */
@media (max-width: 767px) {
    main#mainContent {
        margin-left: 0 !important;
        padding-top: calc(var(--mobile-navbar-height, 3.5rem) + 0.5rem) !important;
    }
    
    /* Ensure heading is visible on mobile */
    main#mainContent > div:first-of-type {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }
}

@media (min-width: 768px) {
    main#mainContent:not(.ml-\[64px\]) {
        margin-left: 256px;
    }
}

/* Make sure heading is always visible */
h1.text-xl {
    display: block !important;
    visibility: visible !important;
}



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
    filter: invert(1) brightness(2);
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
.swal2-confirm-red {
  background-color: #e74c3c !important; /* Red color */
  color: white !important;
  border: none !important;
}
.table thead th {
    background-color: #0b5125;
    color: #f3f4f6;
    font-weight: 600;
}

.table-hover tbody tr:hover {
    background-color: #f8fafc;
}

.badge {
    padding: 0.4em 0.6em;
    font-size: 0.85em;
    border-radius: 0.5rem;
}

/* Start Date - Material Green */
            .flatpickr-day.start-date {
            background-color: #4CAF50 !important; /* Material Green */
            color: white !important;
            border-radius: 50% !important;
            font-weight: bold;
            }

            /* End Date - Soft Red */
            .flatpickr-day.end-date {
            background-color: #e57373 !important; /* Soft Red */
            color: white !important;
            border-radius: 50% !important;
            font-weight: bold;
            }
            .flatpickr-calendar .flatpickr-current-month input.cur-month {
                background: transparent;
                color: white;
                font-weight: 600;
            }

            .flatpickr-calendar .flatpickr-weekday {
              color: white;
              font-weight: 600;
            }

            .flatpickr-calendar .flatpickr-prev-month,
            .flatpickr-calendar .flatpickr-next-month {
                color: white;
                border: 1px solid transparent;
                border-radius: 4px;
                width: 28px;
                height: 28px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: border-color 0.2s ease;
                margin: 7px 10px;
                box-sizing: border-box;
                background: transparent;
                padding: 0;
            }

            /* Style the arrow icons */
            .flatpickr-calendar .flatpickr-prev-month svg,
            .flatpickr-calendar .flatpickr-next-month svg {
                width: 14px;
                height: 14px;
                fill: white; /* default arrow color (light gray or as needed) */
                transition: fill 0.2s ease;
            }

            /* On hover, border and arrow turn white */
            .flatpickr-calendar .flatpickr-prev-month:hover,
            .flatpickr-calendar .flatpickr-next-month:hover {
            border-color: white;
            cursor: pointer;
            }

            .flatpickr-calendar .flatpickr-prev-month:hover svg,
            .flatpickr-calendar .flatpickr-next-month:hover svg {
            fill: white; /* hover arrow becomes white */
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
</style>



<div class="flex min-h-screen overflow-hidden bg-gray-100">    
    <main id="mainContent" class="flex-1 p-3 sm:p-4 md:p-6 bg-gray-100 transition-margin duration-300 ease-in-out md:ml-64">
  <?php require_once views_path("partials/user_sidebar"); ?>

  <div class="space-y-6 max-w-7xl mx-auto">
    
    <!-- Page Header -->
    <div class="mb-4 md:mb-6 pt-2 sm:pt-4">
      <h1 class="text-xl sm:text-2xl md:text-3xl font-bold tracking-tight text-gray-800">Leave Applications</h1>
      <p class="text-sm sm:text-base text-gray-600 mt-1 md:mt-2">View and track your leave requests and remaining days.</p>
    </div>



    <!-- 📦 Leave Credits Display -->
<div id="leaveCredits">
  <div>
      <span class="text-base sm:text-lg font-semibold text-gray-800 mb-2 block">Leave Records</span>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
          <?php foreach ($leaveSummary as $type => $data): 
              $remaining = max(0, $data['allowed'] - $data['taken']);
              $borderColor = $data['color'];
          ?>
          <div class="bg-white rounded-lg shadow-sm p-3 hover:shadow-md transition text-center border-l-4 <?= $borderColor ?>">
              <span class="text-xs sm:text-sm font-medium text-gray-600"><?= htmlspecialchars($type) ?></span>
              <p class="text-lg sm:text-base mt-2 font-bold text-green-600"><?= $data['taken'] ?></p>
              <p class="text-xs text-gray-500 mt-1">Used: <?= $data['taken'] ?> / <?= $data['allowed'] ?></p>
          </div>
          <?php endforeach; ?>
      </div>
  </div>
</div>



    <!-- Leave Application Table -->
    <div class="bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4">
                <div>
                    <span class="text-lg sm:text-xl font-semibold">Your Leave Applications</span>
                    <p class="text-sm text-gray-500">All your submitted leave records are shown here.</p>
                </div>
                <button class="btn btn-success w-full sm:w-auto" data-bs-toggle="modal" data-bs-target="#leaveModal">
                    <i class="bi bi-file-earmark-plus me-2"></i>Apply for Leave
                </button>
            </div>

            <div class="overflow-x-auto max-h-80 rounded-md border">
                <table class="table text-sm align-middle w-full">
                    <thead class="table-success sticky top-0 text-center text-xs sm:text-sm">
                        <tr>
                            <th>Type</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="leaveTableBody" class="table-hover [&>tr:last-child]:border-b-0">
                        <?php if ($leaves): ?>
                            <?php foreach ($leaves as $leave): ?>
                                <tr>
                                    <td class="py-3"><?= htmlspecialchars($leave['leave_type']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($leave['start_date']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($leave['end_date']) ?></td>
                                    <td class="text-center">
                                        <?php
                                            $hasReason = !empty($leave['reason']);
                                            $hasMedCert = !empty($leave['med_cert_path']) && $leave['leave_type'] === 'Sick Leave' && (int)$leave['duration'] >= 3;
                                            $alwaysShowModalTypes = ['Vacation Leave', 'Maternity/Paternity Leave'];

                                            $showModal = $hasReason || $hasMedCert || in_array($leave['leave_type'], $alwaysShowModalTypes);
                                        ?>

                                        <?php if ($showModal): ?>
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#leaveReasonModal<?= $leave['id'] ?>">
                                                <i class="bi bi-eye"></i> 
                                            </button>

                                            <div class="modal fade" id="leaveReasonModal<?= $leave['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered modal-lg ">
                                                    <div class="modal-content mx-auto" style="width: 90vh; max-height: 80vh; overflow-y: auto;">
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title">Leave Details</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                                                              <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>

                                                        <div class="modal-body px-3 py-3 d-flex flex-column gap-3">
                                                            <!-- Leave Reason -->
                                                            <?php if ($hasReason): ?>
                                                                <div class="text-start">
                                                                    <?= nl2br(htmlspecialchars($leave['reason'])) ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <!-- Medical Certificate -->
                                                            <?php if ($hasMedCert): ?>
                                                                <div class="text-left">
                                                                    <strong>Medical Certificate:</strong>
                                                                    <div class="d-flex justify-content-center mt-2">
                                                                        <img src="<?= htmlspecialchars($leave['med_cert_path']) ?>"
                                                                            alt="Medical Certificate"
                                                                            class="img-fluid rounded border"
                                                                            style="max-height: 280px; max-width: 100%; width: auto; ">
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>

                                                            <!-- No content fallback -->
                                                            <?php if (!$hasReason && !$hasMedCert && in_array($leave['leave_type'], $alwaysShowModalTypes)): ?>
                                                                <div class="fst-italic text-muted">No reason or certificate needed.</div>
                                                            <?php endif; ?>

                                                            <!-- Footer -->
                                                            <div class="pt-2">
                                                                <hr class="w-100 m-0">
                                                                <small class="text-muted">Leave type: <?= htmlspecialchars($leave['leave_type']) ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">N/A</span>
                                        <?php endif; ?>
                                    </td>


                                    <td class="text-center">
                                        <span class="badge <?= $leave['status'] === 'Approved' ? 'bg-success' : ($leave['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                            <?= $leave['status'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($leave['status'] === 'Pending'): ?>
                                            <button class="btn btn-sm btn-outline-danger delete-leave-btn" data-id="<?= $leave['id'] ?>" data-type="<?= htmlspecialchars($leave['leave_type']) ?>">
                                                <i class="bi bi-trash"></i> 
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No leave applications yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>




    <!-- Leave Modal -->
<!-- Leave Application Modal -->
<div class="modal fade" id="leaveModal" data-bs-backdrop="static"  tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form method="POST" action="index.php?payroll=user_leave" id="leaveForm" enctype="multipart/form-data">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="leaveModalLabel">
            <i class="bi bi-file-earmark-text me-2"></i>Leave Application
          </h5>
          <button type="button" class="btn-close btn-close-white z-[1000]" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="leave_type">Leave Type</label>
              <select name="leave_type" id="leave_type" class="block w-full px-3 py-2 cursor-pointer border border-gray-300 rounded-md focus:outline-2 focus:outline-offset-2 focus:outline-green-700 transition duration-150 ease-in-out sm:text-base"  required>
                <option value="" disabled selected>Select leave type</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Emergency Leave">Emergency Leave</option>
                <option value="Vacation Leave">Vacation Leave</option>
                <option value="Personal Leave">Personal Leave</option>
                <option value="Maternity/Paternity Leave">Maternity/Paternity Leave</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label " for="duration">Duration (days)</label>
              <input type="number" name="duration" id="duration" class="form-control py-2 pointer-events-none" readonly value="0">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="start_date">Start Date</label>
              <input type="text" placeholder="YYYY-MM-DD" name="start_date" id="start_date" class="form-control border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2"  required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="end_date">End Date</label>
              <input type="text" placeholder="YYYY-MM-DD" name="end_date" id="end_date" class="form-control border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2" required>
            </div>
          </div>

          <div class="mb-3 d-none" id="reasonContainer">
            <label class="form-label" for="reason">Reason</label>
            <textarea name="reason" id="reason" rows="3" placeholder="Please provide a detailed reason..." class="form-control border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2" style="resize: none;"></textarea>
          </div>

          <div class="mb-3 d-none" id="medCertContainer">
            <label class="form-label" for="med_cert">Upload Medical Certificate <span class="text-red-600"> *</span></label>
            <input type="file" name="med_cert" id="med_cert" class="form-control border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2" accept="image/*">
          </div>
        </div>

        <div class="modal-footer border-top-0">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-send-fill me-2"></i>Submit Application
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
</main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const typeEl = document.getElementById('leave_type');
  const durEl = document.getElementById('duration');
  const reasonC = document.getElementById('reasonContainer');
  const certC = document.getElementById('medCertContainer');
  const reasonEl = document.getElementById('reason');
  const certEl = document.getElementById('med_cert');
  const sd = document.getElementById('start_date');
  const ed = document.getElementById('end_date');
  const form = document.getElementById('leaveForm');
  const tableBody = document.getElementById('leaveTableBody');
  const leaveModalEl = document.getElementById('leaveModal');
  const durationEl = document.getElementById('duration');

  leaveModalEl.addEventListener('hidden.bs.modal', () => {
    // Reset the form
    leaveForm.reset();

    // Set duration to 0 manually
    durationEl.value = 0;

    // Hide dynamic elements again
    document.getElementById('reasonContainer').classList.add('d-none');
    document.getElementById('medCertContainer').classList.add('d-none');
  });

  let s, e;
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  // Options for leave types that allow past dates (Sick Leave)
  const sickOpts = {
    dateFormat: 'Y-m-d',
    disableMobile: true,
    disable: [d => d > today], // Disable future dates only
    onChange: sel => {
      s = sel[0];
      fpEnd.set('minDate', s);
      markDates();
      calc();
    }
  };

  const sickEndOpts = {
    dateFormat: 'Y-m-d',
    disableMobile: true,
    disable: [d => d > today], // Disable future dates only
    onChange: sel => {
      e = sel[0];
      markDates();
      calc();
    }
  };

  // Options for leave types that disable past dates (Emergency, Vacation, Personal, Maternity/Paternity)
  const futureOnlyOpts = {
    dateFormat: 'Y-m-d',
    disableMobile: true,
    minDate: 'today', // Disable past dates
    onChange: sel => {
      s = sel[0];
      fpEnd.set('minDate', s);
      markDates();
      calc();
    }
  };

  const futureOnlyEndOpts = {
    dateFormat: 'Y-m-d',
    disableMobile: true,
    minDate: 'today', // Disable past dates
    onChange: sel => {
      e = sel[0];
      markDates();
      calc();
    }
  };

  let fpStart = flatpickr(sd, futureOnlyOpts);
  let fpEnd = flatpickr(ed, futureOnlyEndOpts);

  typeEl.addEventListener('change', () => {
    fpStart.destroy();
    fpEnd.destroy();
    s = e = undefined;
    durEl.value = 0;
    sd.value = ed.value = '';
    if (typeEl.value === 'Sick Leave') {
      fpStart = flatpickr(sd, sickOpts);
      fpEnd = flatpickr(ed, sickEndOpts);
    } else {
      // Emergency, Vacation, Personal, Maternity/Paternity - disable past dates
      fpStart = flatpickr(sd, futureOnlyOpts);
      fpEnd = flatpickr(ed, futureOnlyEndOpts);
    }
    updateFields();
  });

  function markDates() {
    setTimeout(() => {
      document.querySelectorAll('.flatpickr-day').forEach(d => d.classList.remove('start-date', 'end-date'));
      if (s) {
        document.querySelectorAll('.flatpickr-day').forEach(d => {
          if (d.dateObj?.toDateString() === s.toDateString()) d.classList.add('start-date');
        });
      }
      if (e) {
        document.querySelectorAll('.flatpickr-day').forEach(d => {
          if (d.dateObj?.toDateString() === e.toDateString()) d.classList.add('end-date');
        });
      }
    }, 10);
  }

  function calc() {
    if (s && e && e >= s) {
      // Count only weekdays (Monday-Friday), excluding weekends
      let count = 0;
      const currentDate = new Date(s);
      const endDate = new Date(e);
      
      // Iterate through each day from start to end
      while (currentDate <= endDate) {
        const dayOfWeek = currentDate.getDay(); // 0 = Sunday, 6 = Saturday
        // Only count if it's not Saturday (6) or Sunday (0)
        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
          count++;
        }
        // Move to next day
        currentDate.setDate(currentDate.getDate() + 1);
      }
      
      durEl.value = count;
    } else {
      durEl.value = 0;
    }
    updateFields();
  }

  function updateFields() {
    const t = typeEl.value;
    const d = parseInt(durEl.value) || 0;
    reasonC.classList.add('d-none');
    certC.classList.add('d-none');
    if (['Personal Leave', 'Emergency Leave'].includes(t)) reasonC.classList.remove('d-none');
    if (t === 'Sick Leave') {
      if (d >= 3) certC.classList.remove('d-none');
      else if (d >= 1) reasonC.classList.remove('d-none');
    }
  }

  function fetchLeaves() {
    fetch('../app/api/user_leave-api.php')
      .then(res => res.text())
      .then(html => {
        tableBody.innerHTML = html;
        attachDeleteHandlers();
      })
      .catch(() => Swal.fire('Error', 'Failed to load leaves.', 'error'));
  }

form.addEventListener('submit', eEvt => {
  eEvt.preventDefault();

  const t = typeEl.value;
  const d = parseInt(durEl.value) || 0;

  if (d <= 0) return Swal.fire('Invalid dates', 'Check your start/end dates', 'error');
  if (t === 'Sick Leave' && d >= 3 && certEl.files.length === 0)
    return Swal.fire('Missing File', 'Upload medical certificate', 'warning');
  if ((t === 'Sick Leave' && d <= 2 || ['Personal Leave', 'Emergency Leave'].includes(t)) &&
      reasonEl.value.trim() === '')
    return Swal.fire('Missing Reason', 'Provide a reason', 'warning');

  const formData = new FormData(form);

  // ✅ Hide modal immediately
  const modalEl = document.getElementById('leaveModal');
  const modal = bootstrap.Modal.getInstance(modalEl);
  if (modal) modal.hide();

  // ✅ Delay to let modal finish closing
  setTimeout(() => {
    fetch('../app/api/user_leave-api.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(json => {
        Swal.fire({
          icon: json.status === 'success' ? 'success' : 'error',
          title: json.status === 'success' ? 'Submitted' : 'Error',
          text: json.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          if (json.status === 'success') {
            form.reset();
            durEl.value = 0;

            // ✅ Reload table
            fetch('../app/api/user_leave-api.php')
              .then(res => res.text())
              .then(html => {
                tableBody.innerHTML = html;

                // ✅ Fade-in first row
                const firstRow = tableBody.querySelector('tr');
                if (firstRow) {
                  firstRow.classList.add('fade-in');
                  requestAnimationFrame(() => {
                    firstRow.classList.add('show');
                  });
                }

                attachDeleteHandlers();

                // ✅ Prevent form resubmission warning on refresh
                history.replaceState(null, '', window.location.href);
              });
          }
        });
      })
      .catch(() => Swal.fire('Error', 'Could not connect', 'error'));
  }, 300); // Delay to ensure modal animation completes
});



    function attachDeleteHandlers() {
    document.querySelectorAll('.delete-leave-btn').forEach(btn => {
        btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const type = btn.dataset.type;
        const row = btn.closest('tr');
        const isMobile = window.innerWidth < 480;

        Swal.fire({
            title: 'Confirm Deletion',
            text: `Are you sure you want to delete your "${type}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            width: isMobile ? '90%' : undefined,
            customClass: {
            popup: isMobile ? 'text-sm' : 'text-base',
            confirmButton: isMobile ? 'bg-danger text-white px-3 py-2 rounded' : 'swal2-confirm-red text-white px-4 py-2',
            cancelButton: isMobile ? 'bg-light text-dark px-3 py-2 rounded' : 'text-dark bg-light px-4 py-2'
            }
        }).then(result => {
            if (result.isConfirmed) {
            fetch('../app/api/user_leave-api.php', {
                method: 'DELETE',
                headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ id })
            })
                .then(res => res.json())
                .then(json => {
                if (json.status === 'success') {
                    // ✅ SHOW swal first, then fade after CLOSE
                    Swal.fire({
                    icon: 'success',
                    title: 'Deleted',
                    text: json.message,
                    timer: 1200,
                    showConfirmButton: false,
                    willClose: () => {
                        // ✅ Fade the row only after swal closes
                        row.style.transition = 'opacity 0.5s ease';
                        row.style.opacity = 0;
                        setTimeout(() => {
                        row.remove();
                        if (document.querySelectorAll('#leaveTableBody tr').length === 0) {
                            document.getElementById('leaveTableBody').innerHTML =
                            `<tr><td colspan="6" class="text-center text-muted py-3">No leave applications yet.</td></tr>`;
                        }
                        // ✅ Remove DELETE trace from history
                        history.replaceState(null, '', window.location.href);
                        }, 500);
                    }
                    });
                } else {
                    Swal.fire('Failed', json.message, 'error');
                }
                })
                .catch(() => Swal.fire('Error', 'Could not connect to the server.', 'error'));
            }
        });
        });
    });
    }
    attachDeleteHandlers();
});
</script>

<!-- <script>
async function refreshLeaveData() {
  try {
    // Refresh Leave Table
    const leaveRes = await fetch('index.php?payroll=user_leave-api.php');
    const leaveHTML = await leaveRes.text();
    document.getElementById('leaveTableBody').innerHTML = leaveHTML;

    // Refresh Leave Credits
    const creditRes = await fetch('index.php?payroll=api/leave_credits-api.php');
    const creditHTML = await creditRes.text();
    document.getElementById('leaveCredits').innerHTML = creditHTML;

  } catch (err) {
    console.error('Failed to refresh leave data:', err);
  }
}
</script> -->



<?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: <?= json_encode($_SESSION['success']) ?>,
            showConfirmButton: false,
            timer: 1000,
            timerProgressBar: true
        });
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>


<?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?= $_SESSION['error'] ?>',
            showConfirmButton: false,
            timer: 1000,
            timerProgressBar: true,
        });
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
