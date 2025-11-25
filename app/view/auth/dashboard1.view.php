<?php
// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");

$title = "Dashboard"; 
require_once views_path("partials/header");

require_once '../app/core/Database.php';

// Session handling
$loginSuccess = isset($_SESSION['login_success']) && $_SESSION['login_success'] === true;
if ($loginSuccess) {
    unset($_SESSION['login_success']);
}

// Authentication check (should be outside of any loops)
if (!isset($_SESSION['SESSION_EMAIL'])) {
    header("Location: index.php?payroll=login1&type=admin");
    exit();
}

// Get username and email from session
$username = $_SESSION['USERNAME'] ?? 'Unknown User';
$email = $_SESSION['SESSION_EMAIL'] ?? 'no-email@example.com';

// Get user initials
$initials = '';
foreach (explode(' ', $username) as $part) {
    $initials .= strtoupper(substr($part, 0, 1));
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Fetch leave requests with employee full name formatted properly
    $sql = "SELECT l.*, 
            CONCAT(
                UPPER(LEFT(e.first_name,1)), LOWER(SUBSTRING(e.first_name,2)), ' ',
                IFNULL(CONCAT(UPPER(LEFT(e.middle_name,1)), LOWER(SUBSTRING(e.middle_name,2)), '. '), ''),
                UPPER(LEFT(e.last_name,1)), LOWER(SUBSTRING(e.last_name,2))
            ) AS employee_name
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            ORDER BY l.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Aggregate counts
    // Total Employees (fallback safe)
    try {
        $totalEmployees = (int)$conn->query("SELECT COUNT(*) FROM employees WHERE deleted_at IS NULL AND approved_by_manager = 1")->fetchColumn();
    } catch (Throwable $e) {
        $totalEmployees = 0;
    }

    // Total Managers: try managers table; fallback to employees with position like manager
    try {
        $totalManagers = (int)$conn->query("SELECT COUNT(*) FROM managers WHERE deleted_at IS NULL")->fetchColumn();
    } catch (Throwable $e) {
        try {
            $stmtMgr = $conn->prepare("SELECT COUNT(*) FROM employees WHERE LOWER(position) LIKE '%manager%' AND deleted_at IS NULL");
            $stmtMgr->execute();
            $totalManagers = (int)$stmtMgr->fetchColumn();
        } catch (Throwable $e2) {
            $totalManagers = 0;
        }
    }
    
    // Total HR/Admins
    try {
        $totalHR = (int)$conn->query("SELECT COUNT(*) FROM admins WHERE deleted_at IS NULL")->fetchColumn();
    } catch (Throwable $e) {
        $totalHR = 0;
    }
    
    // Total active users (employees + managers + HR)
    $totalActiveUsers = $totalEmployees + $totalManagers + $totalHR;
    
    // Today's date
    $today = date('Y-m-d');
    
    // Get today's attendance statistics
    $presentToday = 0;
    $lateToday = 0;
    $absentToday = 0;
    
    try {
        // Count Present: Has morning_in for today (or status = 'Present')
        $presentStmt = $conn->prepare("
            SELECT COUNT(DISTINCT 
                CASE 
                    WHEN employee_id IS NOT NULL THEN CONCAT('emp_', employee_id)
                    WHEN manager_id IS NOT NULL THEN CONCAT('mgr_', manager_id)
                    WHEN hr_id IS NOT NULL THEN CONCAT('hr_', hr_id)
                END
            ) as present_count
            FROM attendance 
            WHERE date = :today 
            AND (morning_in IS NOT NULL OR status = 'Present')
        ");
        $presentStmt->execute([':today' => $today]);
        $presentToday = (int)$presentStmt->fetchColumn();
        
        // Count Late: Status = 'Late' OR check if morning_in is late based on schedule
        // For now, we'll use status = 'Late' or morning_in > 08:00:00 as a general threshold
        // (In a more sophisticated system, we'd join with schedules table to get per-user scheduled times)
        $lateStmt = $conn->prepare("
            SELECT COUNT(DISTINCT 
                CASE 
                    WHEN employee_id IS NOT NULL THEN CONCAT('emp_', employee_id)
                    WHEN manager_id IS NOT NULL THEN CONCAT('mgr_', manager_id)
                    WHEN hr_id IS NOT NULL THEN CONCAT('hr_', hr_id)
                END
            ) as late_count
            FROM attendance 
            WHERE date = :today 
            AND (
                status = 'Late' 
                OR (morning_in IS NOT NULL AND morning_in > '08:15:00')
            )
        ");
        $lateStmt->execute([':today' => $today]);
        $lateToday = (int)$lateStmt->fetchColumn();
        
        // Calculate Absent: Total active users - Present
        $absentToday = max(0, $totalActiveUsers - $presentToday);
        
        // Calculate percentage
        $presentPercentage = $totalActiveUsers > 0 ? round(($presentToday / $totalActiveUsers) * 100) : 0;
        $latePercentage = $totalActiveUsers > 0 ? round(($lateToday / $totalActiveUsers) * 100) : 0;
        $absentPercentage = $totalActiveUsers > 0 ? round(($absentToday / $totalActiveUsers) * 100) : 0;
        
    } catch (Throwable $e) {
        error_log("Attendance stats error: " . $e->getMessage());
        $presentToday = 0;
        $lateToday = 0;
        $absentToday = 0;
        $presentPercentage = 0;
        $latePercentage = 0;
        $absentPercentage = 0;
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $leaveRequests = [];
    $totalEmployees = $totalEmployees ?? 0;
    $totalManagers = $totalManagers ?? 0;
}
?>

<div class="flex bg-[#f8fbf8]">
    <?php require_once views_path("partials/sidebar"); ?>
    <?php require_once views_path("partials/nav"); ?>

    <div class="flex w-full pt-12">
        <main class="ml-[260px] w-full px-3 mt-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <span class="text-2xl font-bold tracking-tight">Dashboard</span>
                    <p class="text-[#478547]">Overview of employee records and system activity</p>
                </div>

                <span class="bg-white border-2 border-green-200 text-green-700 px-4 py-1 rounded-full text-sm">
                    <span id="dateString"></span>
                </span>
            </div>

            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-2 -mt-2 mb-6 w-full" >
                <div class="bg-white border-2 border-green-200 rounded-lg p-3 w-full h-24"
                        data-aos="fade-in" 
                        data-aos-delay="<?= $index * 50 ?>"
                        data-aos-duration="500">
                    <div class="-mt-3">
                        <span class="text-sm -mt-12 font-medium text-[#396A39]">Total Employees</span>
                        <p class="text-2xl font-bold mt-2"><?php echo $totalEmployees; ?></p>
                        <span class="text-xs text-gray-500">Active staff members</span>
                    </div>
                    <i class="bi bi-people absolute top-2 right-3 text-green-600"></i>                
                </div>
                
                <div class="bg-white border-2 border-green-200 rounded-lg p-3 w-full h-24"
                    data-aos="fade-in" 
                    data-aos-delay="<?= $index * 50 ?>"
                    data-aos-duration="500">
                <div class="-mt-3">
                    <span class="text-sm -mt-12 font-medium text-[#396A39]">Total Managers</span>
                    <p class="text-2xl font-bold mt-2"><?php echo $totalManagers; ?></p>
                    <span class="text-xs text-gray-500">Active managers</span>
                </div>
                <i class="bi bi-person-gear absolute top-2 right-3 text-green-600"></i>                
            </div>


                <div class="bg-white border-2 border-green-200 rounded-lg p-3 w-full h-24 relative"
                    data-aos="fade-in" 
                    data-aos-delay="<?= $index * 40 ?>"
                    data-aos-duration="500">
                <div class="-mt-3">
                    <span class="text-sm -mt-2 font-medium text-[#396A39]">Present Today</span>
                    <p class="text-2xl font-bold mt-2"><?= $presentToday ?></p>
                    <span class="text-xs text-gray-500"><?= $presentPercentage ?>% of active users</span>
                </div>
                    <i class="bi bi-person-check absolute top-2 right-3 text-green-600"></i>
                </div>

                <div class="bg-white border-2 border-green-200 rounded-lg p-3 w-full h-24 relative"
                    data-aos="fade-in" 
                    data-aos-delay="<?= $index * 30 ?>"
                    data-aos-duration="500">
                <div class="-mt-3">
                    <span class="text-sm -mt-2 font-medium text-[#396A39]">Late Today</span>
                    <p class="text-2xl font-bold mt-2"><?= $lateToday ?></p>
                    <span class="text-xs text-gray-500"><?= $latePercentage ?>% of active users</span>
                </div>
                    <i class="bi bi-clock absolute top-2 right-3 text-green-600"></i>
                </div>

                <div class="bg-white border-2 border-green-200 rounded-lg p-3 w-full h-24 relative"
                    data-aos="fade-in" 
                    data-aos-delay="<?= $index * 20 ?>"
                    data-aos-duration="500">
                <div class="-mt-3">
                    <span class="text-sm -mt-2 font-medium text-[#396A39]">Absent Today</span>
                    <p class="text-2xl font-bold mt-2"><?= $absentToday ?></p>
                    <span class="text-xs text-gray-500"><?= $absentPercentage ?>% of active users</span>
                </div>
                    <i class="bi bi-person-x absolute top-2 right-3 text-green-600"></i>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="flex mb-6">
                <div class="bg-white -mt-2 border-2 border-green-200 rounded-lg p-6" 
                    data-aos="fade-up" 
                    data-aos-delay="<?= $index * 10 ?>"
                    data-aos-duration="400" style="width: 76%;">
                    <div class="flex items-center justify-between mb-4">
                        <button id="prevMonth" class="month-nav-btn">
                            <i class="bi bi-chevron-left text-[#396A39]"></i>
                        </button>
                        <span id="calendarMonth" class="font-bold text-[#396A39]">Month Year</span>
                        <button id="nextMonth" class="month-nav-btn">
                            <i class="bi bi-chevron-right text-[#396A39]"></i>
                        </button>
                    </div>
                    <hr>
                    <div class="grid grid-cols-7 gap-2 text-center text-sm mt-3 font-medium text-[#396A39] mb-2">
                        <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                    </div>
                    <div id="calendarDays" class="grid grid-cols-7 gap-2 text-sm text-[#396A39] text-center"></div>
                </div>

                <!-- Quick Access -->
                <div class="bg-white ml-2 -mt-2 h-1/4 border-2 border-green-200 rounded-lg p-6"data-aos="fade-left" 
                    delay="<?= $index * 10 ?>"
                    data-aos-duration="400" style="width: 24.80%;">
                    <div class="font-semibold text-center text-[#396A39] mb-4 -mt-2">Quick Access</div>
                    <div class="flex flex-col gap-3">
                        <a href="#" class="border-2 text-black px-4 py-1 rounded-lg border-green-200 hover:bg-green-100 transition-all flex items-center gap-2">
                            <i class="bi bi-calendar-check text-green-600"></i> <span class="hidden md:inline text-sm md:text-base">Attendance</span>
                        </a>
                        <button 
                            type="button" 
                            class="border-2 text-black px-4 py-1 rounded-lg border-green-200 hover:bg-green-100 transition-all flex items-center gap-2" 
                            data-bs-toggle="modal" 
                            data-bs-target="#leaveModal">
                            <i class="bi bi-people text-green-600"></i> 
                            <span class="hidden md:inline text-sm md:text-base">Employees</span>
                        </button>

                        <!-- <a href="#" class="border-2 text-black px-4 py-1 rounded-lg border-green-200 hover:bg-green-100 transition-all flex items-center gap-2">
                            <i class="bi bi-currency-dollar text-green-600"></i> <span class="hidden md:inline text-sm md:text-base">Payroll</span>
                        </a> -->
                    </div>
                </div>
            </div>

            <!-- <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveRequestsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content shadow-lg rounded-4 border-0">
                    <div class="modal-header border-bottom-0 bg-light py-3 rounded-top-4">
                        <h5 class="modal-title fw-bold text-primary" id="leaveRequestsModalLabel">Employee Leave Requests</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3 bg-white">
                        <div class="table-responsive rounded-3 shadow-sm border">

                        
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase text-secondary small">
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration (days)</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Date Created</th> 
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($leaveRequests)): ?>
                                        <?php foreach ($leaveRequests as $leave): ?>
                                            <tr>
                                                <td class="fw-semibold"><?= htmlspecialchars($leave['employee_name']) ?></td>
                                                <td><?= htmlspecialchars($leave['leave_type']) ?></td>
                                                <td><?= htmlspecialchars($leave['start_date']) ?></td>
                                                <td><?= htmlspecialchars($leave['end_date']) ?></td>
                                                <td><?= htmlspecialchars($leave['duration']) ?></td>
                                                <td class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($leave['reason']) ?>">
                                                    <?= htmlspecialchars(strlen($leave['reason']) > 50 ? substr($leave['reason'], 0, 47) . '...' : $leave['reason']) ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status = $leave['status'] ?? 'Pending';
                                                    $badgeClass = match($status) {
                                                        'Approved' => 'bg-success',
                                                        'Rejected' => 'bg-danger',
                                                        default => 'bg-warning text-dark'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-1"><?= htmlspecialchars($status) ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($leave['created_at']) ?></td>
                                                <td>
                                                    <?php if ($status === 'Pending'): ?>
                                                    <div class="d-flex gap-2">
                                                        <form method="POST" action="index.php?payroll=dashboard1" class="d-inline">
                                                            <input type="hidden" name="leave_id" value="<?= $leave['id'] ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <button type="submit" class="btn btn-sm btn-success" title="Approve" data-bs-toggle="modal" data-bs-target="#leaveModal">
                                                                <i class="bi bi-check2-circle"></i> Approve
                                                            </button>
                                                        </form>

                                                        <form method="POST" action="index.php?payroll=dashboard1" class="d-inline">
                                                            <input type="hidden" name="leave_id" value="<?= $leave['id'] ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#leaveModal">
                                                                <i class="bi bi-x-circle"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">Done</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No leave requests found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 bg-light py-3 rounded-bottom-4">
                            <button type="button" class="btn btn-outline-primary fw-semibold" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div> -->


            <!-- Calendar Cell Modal -->
            <div id="calendarCellModalOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 opacity-0 transition-opacity duration-500" onclick="closeCalendarModal()"></div>
            <div id="calendarCellModal" class="fixed inset-0 z-50 hidden transition-all duration-300 scale-75 opacity-0 flex items-center justify-center">
            
                <div class="bg-white p-6 rounded-lg w-full max-w-2xl shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-[#396A39]" id="modalDateTitle">Attendance Records</h3>
                            <p class="text-sm text-gray-500" id="modalHolidayText"></p>
                        </div>
                        <button id="closeCalendarModal" class="text-gray-500 hover:text-gray-700">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    
                    <div id="attendanceStats" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-[#396A39] mb-2">Time In</h4>
                            <p class="text-2xl font-bold" id="timeInCount">0</p>
                            <p class="text-xs text-gray-500">Employees</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-yellow-700 mb-2">Time Out</h4>
                            <p class="text-2xl font-bold" id="timeOutCount">0</p>
                            <p class="text-xs text-gray-500">Employees</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-red-700 mb-2">Late</h4>
                            <p class="text-2xl font-bold" id="lateCount">0</p>
                            <p class="text-xs text-gray-500">Employees</p>
                        </div>
                    </div>

                    <div id="attendanceDetailsSection" class="border-t pt-4">
                        <h4 class="text-sm font-medium text-[#396A39] mb-3">Employee Attendance Details</h4>
                        <div class="overflow-x-auto">
                            <div class="max-h-[300px] overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="py-2 px-4 text-left">Employee</th>
                                            <th class="py-2 px-4 text-left">Time In</th>
                                            <th class="py-2 px-4 text-left">Time Out</th>
                                            <th class="py-2 px-4 text-left">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendanceDetails" class="divide-y divide-gray-200">
                                        <!-- Attendance details will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="holidayMessage" class="hidden text-center py-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
                            <i class="bi bi-calendar-x text-3xl text-red-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-red-600 mb-2">Holiday</h3>
                        <p class="text-gray-600" id="holidayDescription"></p>
                    </div>

                    <div id="noRecordsMessage" class="hidden text-center py-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <i class="bi bi-calendar-check text-3xl text-gray-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Records</h3>
                        <p class="text-gray-500">No attendance records found for this date.</p>
                    </div>
                </div>
            </div>
            </div>
        </main>
    </div>

    <!-- <?php if (isset($_SESSION['open_modal'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var leaveId = <?= json_encode($_SESSION['open_modal']) ?>;
        // Assuming you have Bootstrap 5 modal with id="modal-leave-{leaveId}"
        var modal = new bootstrap.Modal(document.getElementById('modal-leave-' + leaveId));
        modal.show();
    });
</script>
<?php 
    unset($_SESSION['open_modal']); // clear after use
endif; 
?> -->


    <?php // Removed login success toast ?>

    <style>
        /* Add these new animation classes */
        .modal-enter {
            animation: modalEnter 0.3s ease-out forwards;
        }

        .modal-exit {
            animation: modalExit 0.3s ease-out forwards;
        }

        .overlay-enter {
            animation: overlayEnter 0.3s ease-out forwards;
        }

        .overlay-exit {
            animation: overlayExit 0.3s ease-out forwards;
        }

        @keyframes modalEnter {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes modalExit {
            from {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
            to {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
        }

        @keyframes overlayEnter {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes overlayExit {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        /* Update the modal classes */
        #calendarCellModal {
            transition: all 0.3s ease-out;
        }

        #calendarCellModalOverlay {
            transition: all 0.3s ease-out;
        }
    </style>

    <script>
        // Calendar Modal Functions
        function openCalendarModal(cell) {
            const modal = document.getElementById('calendarCellModal');
            const overlay = document.getElementById('calendarCellModalOverlay');
            const date = cell.getAttribute('data-date');
            const isHoliday = cell.classList.contains('holiday');
            const holidayText = cell.getAttribute('data-holiday') || '';
            
            const formattedDate = new Date(date).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            document.getElementById('modalDateTitle').textContent = `Attendance Records - ${formattedDate}`;
            document.getElementById('modalHolidayText').textContent = holidayText;

            if (isHoliday) {
                document.getElementById('holidayMessage').classList.remove('hidden');
                document.getElementById('attendanceStats').classList.add('hidden');
                document.getElementById('attendanceDetailsSection').classList.add('hidden');
                document.getElementById('noRecordsMessage').classList.add('hidden');
                document.getElementById('holidayDescription').textContent = holidayText;
            } else {
                document.getElementById('holidayMessage').classList.add('hidden');
                document.getElementById('attendanceStats').classList.remove('hidden');
                document.getElementById('attendanceDetailsSection').classList.remove('hidden');
                document.getElementById('noRecordsMessage').classList.add('hidden');

                const attendanceData = {
                    timeIn: 5,
                    timeOut: 3,
                    late: 2,
                    details: [
                        { employee: 'John Doe', timeIn: '08:00 AM', timeOut: '05:00 PM', status: 'On Time' },
                        { employee: 'Jane Smith', timeIn: '08:30 AM', timeOut: '05:00 PM', status: 'Late' },
                        { employee: 'Mike Johnson', timeIn: '08:00 AM', timeOut: '05:00 PM', status: 'On Time' }
                    ]
                };

                document.getElementById('timeInCount').textContent = attendanceData.timeIn;
                document.getElementById('timeOutCount').textContent = attendanceData.timeOut;
                document.getElementById('lateCount').textContent = attendanceData.late;

                const tbody = document.getElementById('attendanceDetails');
                tbody.innerHTML = '';
                
                if (attendanceData.details.length > 0) {
                    attendanceData.details.forEach(detail => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="py-2 px-4">${detail.employee}</td>
                            <td class="py-2 px-4">${detail.timeIn}</td>
                            <td class="py-2 px-4">${detail.timeOut}</td>
                            <td class="py-2 px-4">
                                <span class="px-2 py-1 rounded-full text-xs ${
                                    detail.status === 'On Time' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                }">
                                    ${detail.status}
                                </span>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    document.getElementById('attendanceStats').classList.add('hidden');
                    document.getElementById('attendanceDetailsSection').classList.add('hidden');
                    document.getElementById('noRecordsMessage').classList.remove('hidden');
                }
            }

            // Remove any existing animation classes
            modal.classList.remove('modal-exit');
            overlay.classList.remove('overlay-exit');
            
            // Show the modal and overlay
            modal.classList.remove('hidden');
            overlay.classList.remove('hidden');
            
            // Trigger reflow to ensure the animation works
            void modal.offsetWidth;
            void overlay.offsetWidth;
            
            // Add animation classes
            modal.classList.add('modal-enter');
            overlay.classList.add('overlay-enter');
        }

        function closeCalendarModal() {
            const modal = document.getElementById('calendarCellModal');
            const overlay = document.getElementById('calendarCellModalOverlay');
            
            // Remove enter animations and add exit animations
            modal.classList.remove('modal-enter');
            overlay.classList.remove('overlay-enter');
            modal.classList.add('modal-exit');
            overlay.classList.add('overlay-exit');
            
            // Wait for animation to complete before hiding
            setTimeout(() => {
                modal.classList.add('hidden');
                overlay.classList.add('hidden');
                // Remove exit animations
                modal.classList.remove('modal-exit');
                overlay.classList.remove('overlay-exit');
            }, 300);
        }

        // Add click event to calendar cells
        document.addEventListener('DOMContentLoaded', function() {
            const calendarDays = document.getElementById('calendarDays');
            calendarDays.addEventListener('click', function(e) {
                const cell = e.target.closest('.calendar-day');
                if (cell) {
                    openCalendarModal(cell);
                }
            });

            // Close modal with escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeCalendarModal();
                }
            });

            // Close modal with close button
            document.getElementById('closeCalendarModal').addEventListener('click', closeCalendarModal);
            
            // Login Success Notification
            <?php if ($loginSuccess): ?>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Login Successfully!',
                text: 'Welcome back, <?php echo addslashes(htmlspecialchars($username)); ?>',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            <?php endif; ?>
        });
    </script>

    <?php require_once views_path("partials/footer"); ?>
