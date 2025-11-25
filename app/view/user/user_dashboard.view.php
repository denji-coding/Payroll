<?php
$title = "Employee Dashboard";
require_once views_path("partials/header");

// Add Bootstrap Icons CSS
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">';

// Scripts
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

// Fix session variable handling - support employees, managers, and HR
$loginSuccess = $_SESSION['login_success'] ?? false;

// Get username based on user type
if (isset($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['SESSION_USER_ID'])) {
    // HR/Admin
    $username = $_SESSION['USERNAME'] ?? $_SESSION['user_name'] ?? 'HR User';
} elseif (isset($_SESSION['manager_id']) && !empty($_SESSION['manager_id'])) {
    // Manager
    $username = $_SESSION['manager_name'] ?? 'Manager';
} else {
    // Regular Employee
$username = $_SESSION['name'] ?? $_SESSION['username'] ?? $_SESSION['first_name'] ?? 'Employee';
}

if ($loginSuccess) {
    unset($_SESSION['login_success']); // So it only shows once
    unset($_SESSION['username']);
}

$isMobile = '<script>document.write(window.innerWidth < 768 ? "true" : "false");</script>';
?>

<script>
// Debug logging functions (same as login page)
function debugLog(message, type = 'info') {
    const timestamp = new Date().toLocaleTimeString();
    const logEntry = {
        time: timestamp,
        message: message,
        type: type
    };
    
    // Store in localStorage for persistence
    let logs = JSON.parse(localStorage.getItem('employeeLoginDebugLogs') || '[]');
    logs.push(logEntry);
    if (logs.length > 100) {
        logs = logs.slice(-100);
    }
    localStorage.setItem('employeeLoginDebugLogs', JSON.stringify(logs));
    
    const consoleMethod = type === 'error' ? 'error' : type === 'warning' ? 'warn' : 'log';
    console[consoleMethod](`[${timestamp}] ${message}`);
}

// Debug: Check session state when dashboard loads
document.addEventListener('DOMContentLoaded', function() {
    debugLog('=== EMPLOYEE DASHBOARD LOAD ===', 'success');
    debugLog('Current URL: ' + window.location.href, 'info');
    
    // Check for debug comment from PHP
    if (document.body.innerHTML.includes('USER_DASHBOARD_DEBUG')) {
        debugLog('✅ Found PHP debug output', 'success');
        const htmlContent = document.body.innerHTML;
        const match = htmlContent.match(/USER_DASHBOARD_DEBUG:\s*({[^}]+})/);
        if (match) {
            try {
                const debugData = JSON.parse(match[1]);
                debugLog('Session debug data: ' + JSON.stringify(debugData), 'info');
            } catch (e) {
                debugLog('Could not parse debug data', 'warning');
            }
        }
    } else {
        debugLog('⚠️ No PHP debug output found', 'warning');
    }
    
    // Check cookies
    const cookies = document.cookie.split(';').reduce((acc, cookie) => {
        const [key, value] = cookie.trim().split('=');
        acc[key] = value;
        return acc;
    }, {});
    debugLog('Cookies: ' + JSON.stringify(cookies), 'info');
    debugLog('MVC_PAYROLL_SESS cookie: ' + (cookies['MVC_PAYROLL_SESS'] || 'NOT SET'), cookies['MVC_PAYROLL_SESS'] ? 'success' : 'error');
    debugLog('PHPSESSID cookie: ' + (cookies['PHPSESSID'] || 'NOT SET'), cookies['PHPSESSID'] ? 'warning' : 'info');
    
    // Check if we're actually on the dashboard
    if (window.location.href.includes('user_dashboard')) {
        debugLog('✅ Successfully loaded employee dashboard', 'success');
    } else {
        debugLog('❌ Not on dashboard - redirected to: ' + window.location.href, 'error');
    }
    
    // Check for error messages
    const errorElements = document.querySelectorAll('.alert-danger, [class*="error"], [class*="unauthorized"]');
    if (errorElements.length > 0) {
        debugLog('❌ Error elements found: ' + errorElements.length, 'error');
        errorElements.forEach((el, idx) => {
            debugLog(`Error ${idx + 1}: ` + el.textContent.trim(), 'error');
        });
    }
});
</script>

<div class="flex min-h-screen bg-gray-50">
    <main id="mainContent" class="flex-1 transition-all duration-300 ease-in-out">
        <?php require_once views_path("partials/user_sidebar"); ?>

        <!-- Main Content Area -->
        <div class="p-6 lg:p-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">
                            Welcome back, <?php echo htmlspecialchars($username); ?>! 👋
                        </h1>
                        <p class="text-gray-600">Here's what's happening with your work today</p>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <div class="flex items-center space-x-2 text-sm text-gray-500">
                            <i class="bi bi-calendar3"></i>
                            <span id="currentDate"><?php echo date('l, F j, Y'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Today's Status -->
                <?php
                $statusColor = $todayStatus === 'Present' ? 'green' : ($todayStatus === 'Late' ? 'yellow' : 'red');
                $statusBg = $todayStatus === 'Present' ? 'from-green-50 to-green-100 border-green-200' : ($todayStatus === 'Late' ? 'from-yellow-50 to-yellow-100 border-yellow-200' : 'from-red-50 to-red-100 border-red-200');
                $statusTextColor = $todayStatus === 'Present' ? 'text-green-600' : ($todayStatus === 'Late' ? 'text-yellow-600' : 'text-red-600');
                $statusTextColorDark = $todayStatus === 'Present' ? 'text-green-800' : ($todayStatus === 'Late' ? 'text-yellow-800' : 'text-red-800');
                $statusIconBg = $todayStatus === 'Present' ? 'bg-green-500' : ($todayStatus === 'Late' ? 'bg-yellow-500' : 'bg-red-500');
                $checkInText = $todayCheckIn ? 'Checked in at ' . date('g:i A', strtotime($todayCheckIn)) : 'Not checked in';
                ?>
                <div class="bg-gradient-to-br <?= $statusBg ?> border rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium <?= $statusTextColor ?> mb-1">Today's Status</p>
                            <p class="text-2xl font-bold <?= $statusTextColorDark ?>"><?= htmlspecialchars($todayStatus) ?></p>
                            <p class="text-xs <?= $statusTextColor ?> mt-1"><?= htmlspecialchars($checkInText) ?></p>
                        </div>
                        <div class="w-12 h-12 <?= $statusIconBg ?> rounded-lg flex items-center justify-center">
                            <i class="bi bi-check-circle text-white text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Work Hours -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600 mb-1">Work Hours</p>
                            <p class="text-2xl font-bold text-blue-800"><?= number_format($todayWorkHours, 1) ?> hrs</p>
                            <p class="text-xs text-blue-600 mt-1">This week: <?= number_format($weekWorkHours, 1) ?> hrs</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="bi bi-clock text-white text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Leave Balance -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-yellow-600 mb-1">Leave Balance</p>
                            <p class="text-2xl font-bold text-yellow-800">12 days</p>
                            <p class="text-xs text-yellow-600 mt-1">Vacation & Sick Leave</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                            <i class="bi bi-calendar-check text-white text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Latest Pay -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-purple-600 mb-1">Latest Pay</p>
                            <p class="text-2xl font-bold text-purple-800">₱18,500</p>
                            <p class="text-xs text-purple-600 mt-1">Released May 28, 2025</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                            <i class="bi bi-cash-stack text-white text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Attendance Overview -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Attendance Overview</h2>
                            <button onclick="openAttendanceModal()" class="inline-flex items-center text-blue-600 hover:text-blue-700 text-sm font-medium group transition-all duration-200 hover:underline cursor-pointer">
                                View Details 
                                <i class="bi bi-arrow-right ml-1 group-hover:translate-x-1 transition-transform duration-200"></i>
                            </button>
                        </div>
                        
                        <!-- Weekly Attendance Chart -->
                        <div class="mb-6">
                            <canvas id="attendanceChart" height="100"></canvas>
                        </div>
                        
                        <!-- Recent Attendance -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Recent Attendance</h3>
                            <div class="space-y-2">
                                <?php if (!empty($recentAttendance)): ?>
                                    <?php foreach ($recentAttendance as $attendance): ?>
                                        <?php
                                        $statusColor = match($attendance['statusColor']) {
                                            'green' => 'bg-green-500',
                                            'yellow' => 'bg-yellow-500',
                                            'red' => 'bg-red-500',
                                            default => 'bg-gray-500'
                                        };
                                        $statusTextColor = match($attendance['statusColor']) {
                                            'green' => 'text-green-600',
                                            'yellow' => 'text-yellow-600',
                                            'red' => 'text-red-600',
                                            default => 'text-gray-600'
                                        };
                                        $dateFormatted = date('M j, Y', strtotime($attendance['date']));
                                        $timeRange = '';
                                        if ($attendance['timeIn']) {
                                            $timeInFormatted = date('g:i A', strtotime($attendance['timeIn']));
                                            if ($attendance['timeOut']) {
                                                $timeOutFormatted = date('g:i A', strtotime($attendance['timeOut']));
                                                $timeRange = "$timeInFormatted - $timeOutFormatted";
                                            } else {
                                                $timeRange = "$timeInFormatted - --";
                                            }
                                        }
                                        ?>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-2 h-2 <?= $statusColor ?> rounded-full"></div>
                                                <span class="text-sm text-gray-700"><?= htmlspecialchars($dateFormatted) ?></span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-sm font-medium <?= $statusTextColor ?>"><?= htmlspecialchars($attendance['status']) ?></span>
                                                <?php if ($timeRange): ?>
                                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($timeRange) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <p class="text-sm text-gray-500">No recent attendance records</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    <!-- Today's Schedule -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Today's Schedule</h2>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Work Hours</p>
                                    <p class="text-xs text-gray-500">8:00 AM - 5:00 PM</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Lunch Break</p>
                                    <p class="text-xs text-gray-500">12:00 PM - 1:00 PM</p>
                                </div>
            </div>
                            
                            <div class="flex items-center space-x-3 p-3 bg-yellow-50 rounded-lg">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Team Meeting</p>
                                    <p class="text-xs text-gray-500">3:00 PM - 4:00 PM</p>
            </div>
            </div>
        </div>
    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Recent Activity</h2>
                        </div>
                        <div id="activityContainer" class="space-y-4">
                            <?php if (!empty($recentActivities)): ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <?php
                                    $iconColor = match($activity['icon'] ?? 'blue') {
                                        'blue' => 'bg-blue-500',
                                        'green' => 'bg-green-500',
                                        'purple' => 'bg-purple-500',
                                        'orange' => 'bg-orange-500',
                                        default => 'bg-gray-500'
                                    };
                                    
                                    $time = $activity['time'] ?? '';
                                    $formattedDate = '';
                                    $formattedTime = '';
                                    $timestamp = 0;
                                    
                                    if ($time) {
                                        // Try to parse the time string
                                        $timestamp = strtotime($time);
                                        
                                        // If strtotime fails, try alternative parsing
                                        if ($timestamp === false || $timestamp <= 0) {
                                            // Try parsing as datetime string
                                            $timestamp = strtotime($time);
                                        }
                                        
                                        if ($timestamp && $timestamp > 0) {
                                            $now = time();
                                            $diff = $now - $timestamp;
                                            
                                            if ($diff < 3600) { // Less than 1 hour
                                                $minutes = floor($diff / 60);
                                                $formattedDate = $minutes <= 1 ? 'Just now' : "$minutes minutes ago";
                                            } elseif ($diff < 86400) { // Less than 24 hours
                                                $hours = floor($diff / 3600);
                                                $formattedDate = $hours == 1 ? '1 hour ago' : "$hours hours ago";
                                            } elseif ($diff < 604800) { // Less than 7 days
                                                $days = floor($diff / 86400);
                                                if ($days == 1) {
                                                    $formattedDate = 'Yesterday';
                                                    $formattedTime = date('g:i A', $timestamp);
                                                } else {
                                                    $formattedDate = "$days days ago";
                                                }
                                            } else {
                                                $formattedDate = date('M j, Y', $timestamp);
                                                $formattedTime = date('g:i A', $timestamp);
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="flex items-start space-x-3 activity-item">
                                        <div class="w-2 h-2 <?= $iconColor ?> rounded-full mt-2"></div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-900"><?= htmlspecialchars($activity['text']) ?></p>
                                            <?php if (!empty($activity['details'])): ?>
                                                <p class="text-xs text-gray-500"><?= htmlspecialchars($activity['details']) ?></p>
                                            <?php endif; ?>
                                            <p class="text-xs text-gray-500 activity-time" data-timestamp="<?= $timestamp ?>" data-time-string="<?= htmlspecialchars($time) ?>">
                                                <?= $formattedDate ?>
                                                <?php if ($formattedTime): ?>
                                                    , <?= $formattedTime ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-sm text-gray-500">No recent activity</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update current date
    const currentDate = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateElement = document.getElementById('currentDate');
    if (dateElement) {
        dateElement.textContent = currentDate.toLocaleDateString('en-US', options);
    }

    // Attendance Chart
    const attendanceChart = document.getElementById('attendanceChart');
    if (attendanceChart) {
        const attendanceCtx = attendanceChart.getContext('2d');
        
        // Get chart data from PHP
        const chartLabels = <?= json_encode($weeklyChartLabels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) ?>;
        const chartData = <?= json_encode($weeklyChartData ?? [0, 0, 0, 0, 0, 0, 0]) ?>;
        
        // Find max value for y-axis (round up to nearest 2)
        const maxValue = Math.max(...chartData, 8);
        const yAxisMax = Math.ceil(maxValue / 2) * 2;
        
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Work Hours',
                    data: chartData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: yAxisMax,
                        ticks: {
                            stepSize: 2
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 6
                    }
                }
            }
        });
    }
});
</script>
<?php // Removed login success toast ?>

<style>
/* Custom scrollbar for activity log */
.custom-scroll::-webkit-scrollbar {
    width: 4px;
}

.custom-scroll::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 2px;
}

.custom-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Smooth transitions */
.transition-all {
    transition: all 0.3s ease;
}

/* Hover effects */
.hover\:shadow-lg:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Activity slide-in animation */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.activity-item {
    animation: slideIn 0.3s ease-out;
}

/* Modal Styles */
.modal-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.75) !important;
    backdrop-filter: blur(2px);
    z-index: 99999;
    animation: fadeIn 0.2s ease-out;
}

.modal-backdrop.show {
    display: flex !important;
    align-items: center;
    justify-content: center;
    opacity: 1 !important;
}

.attendance-modal {
    background: #ffffff !important;
    border-radius: 16px;
    max-width: 900px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(0, 0, 0, 0.1) !important;
    animation: slideUp 0.3s ease-out;
    position: relative;
    z-index: 100000;
    opacity: 1 !important;
    -webkit-opacity: 1 !important;
    -moz-opacity: 1 !important;
    filter: opacity(1) !important;
    border: 2px solid rgba(255, 255, 255, 1) !important;
}

/* Force full opacity - override any inherited opacity */
.attendance-modal.show,
.attendance-modal[style*="opacity"] {
    opacity: 1 !important;
    -webkit-opacity: 1 !important;
    -moz-opacity: 1 !important;
    filter: opacity(1) !important;
}

/* Ensure all modal content has solid backgrounds */
.attendance-modal > div {
    background: #ffffff !important;
}

.attendance-modal .p-6,
.attendance-modal div.p-6 {
    background: #ffffff !important;
}

.attendance-modal table {
    background: #ffffff !important;
}

.attendance-modal table thead {
    background: #f9fafb !important;
}

.attendance-modal table tbody {
    background: #ffffff !important;
}

.attendance-modal table tr {
    background: #ffffff !important;
}

.attendance-modal table td,
.attendance-modal table th {
    background: inherit !important;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1 !important; }
}

/* Force full opacity after animation */
.modal-backdrop.show,
.modal-backdrop.show * {
    animation-fill-mode: forwards;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1 !important;
        transform: translateY(0);
    }
}

/* Force full opacity - ensure modal is never transparent */
.attendance-modal,
.attendance-modal *:not(button):not(.bg-blue-50):not(.bg-green-50):not(.bg-purple-50):not(.bg-gray-50):not(.bg-yellow-50):not(.bg-red-50) {
    opacity: 1 !important;
    -webkit-opacity: 1 !important;
    -moz-opacity: 1 !important;
}
</style>

<!-- Attendance Details Modal -->
<div id="attendanceModal" class="modal-backdrop" data-debug="modal-backdrop">
    <div class="attendance-modal" style="background: #ffffff !important; opacity: 1 !important; position: relative; z-index: 100000 !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important; display: block !important; visibility: visible !important;" data-debug="modal-content">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10" style="background: #ffffff !important; opacity: 1 !important;">
            <h2 class="text-xl font-semibold text-gray-900" style="background: transparent !important;">Attendance Details</h2>
            <button onclick="closeAttendanceModal()" class="text-gray-400 hover:text-gray-600 transition-colors" style="background: transparent !important;">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <div class="p-6" style="background: #ffffff !important; opacity: 1 !important;">
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <p class="text-sm text-blue-600 font-medium mb-1">Total Days</p>
                    <p class="text-2xl font-bold text-blue-800"><?= count($detailedAttendance ?? []) ?></p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <p class="text-sm text-green-600 font-medium mb-1">Present Days</p>
                    <p class="text-2xl font-bold text-green-800">
                        <?= count(array_filter($detailedAttendance ?? [], function($a) { return $a['status'] === 'Present'; })) ?>
                    </p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <p class="text-sm text-purple-600 font-medium mb-1">Total Hours</p>
                    <p class="text-2xl font-bold text-purple-800">
                        <?= number_format(array_sum(array_column($detailedAttendance ?? [], 'total_hours')), 1) ?>
                    </p>
                </div>
            </div>
            
            <!-- Attendance Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Morning</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Afternoon</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" style="background: #ffffff !important; opacity: 1 !important;">
                        <?php if (!empty($detailedAttendance)): ?>
                            <?php foreach ($detailedAttendance as $attendance): ?>
                                <?php
                                $statusColor = match($attendance['statusColor']) {
                                    'green' => 'bg-green-100 text-green-800',
                                    'yellow' => 'bg-yellow-100 text-yellow-800',
                                    'red' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                $dayName = date('D', strtotime($attendance['date']));
                                $dateFormatted = date('M j, Y', strtotime($attendance['date']));
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors" style="background: #ffffff !important;">
                                    <td class="px-4 py-3 whitespace-nowrap" style="background: #ffffff !important;">
                                        <div class="text-sm font-medium text-gray-900"><?= $dayName ?></div>
                                        <div class="text-xs text-gray-500"><?= $dateFormatted ?></div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap" style="background: #ffffff !important;">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusColor ?>">
                                            <?= htmlspecialchars($attendance['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700" style="background: #ffffff !important;">
                                        <?php if ($attendance['morning_in']): ?>
                                            <div><?= date('g:i A', strtotime($attendance['morning_in'])) ?></div>
                                        <?php else: ?>
                                            <span class="text-gray-400">--</span>
                                        <?php endif; ?>
                                        <?php if ($attendance['morning_out']): ?>
                                            <div class="text-gray-500">→ <?= date('g:i A', strtotime($attendance['morning_out'])) ?></div>
                                            <?php if ($attendance['morning_hours'] > 0): ?>
                                                <div class="text-xs text-blue-600">(<?= number_format($attendance['morning_hours'], 1) ?>h)</div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700" style="background: #ffffff !important;">
                                        <?php if ($attendance['afternoon_in']): ?>
                                            <div><?= date('g:i A', strtotime($attendance['afternoon_in'])) ?></div>
                                        <?php else: ?>
                                            <span class="text-gray-400">--</span>
                                        <?php endif; ?>
                                        <?php if ($attendance['afternoon_out']): ?>
                                            <div class="text-gray-500">→ <?= date('g:i A', strtotime($attendance['afternoon_out'])) ?></div>
                                            <?php if ($attendance['afternoon_hours'] > 0): ?>
                                                <div class="text-xs text-blue-600">(<?= number_format($attendance['afternoon_hours'], 1) ?>h)</div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap" style="background: #ffffff !important;">
                                        <span class="text-sm font-semibold text-gray-900">
                                            <?= number_format($attendance['total_hours'], 1) ?> hrs
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    No attendance records found for the last 30 days
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Footer Actions -->
            <div class="mt-6 flex justify-end gap-3">
                <a href="index.php?payroll=user_dtr" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    View Full DTR
                </a>
                <button onclick="closeAttendanceModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openAttendanceModal() {
    console.log('=== MODAL DEBUG: Opening Attendance Modal ===');
    
    const modal = document.getElementById('attendanceModal');
    const modalContent = modal?.querySelector('.attendance-modal');
    
    console.log('Modal element found:', modal);
    console.log('Modal content found:', modalContent);
    
    if (!modal) {
        console.error('❌ ERROR: Modal element not found!');
        alert('Modal element not found. Check console for details.');
        return;
    }
    
    // Check initial state
    console.log('Initial modal display:', window.getComputedStyle(modal).display);
    console.log('Initial modal opacity:', window.getComputedStyle(modal).opacity);
    console.log('Initial modal z-index:', window.getComputedStyle(modal).zIndex);
    console.log('Initial modal background:', window.getComputedStyle(modal).backgroundColor);
    
    if (modalContent) {
        console.log('Initial content display:', window.getComputedStyle(modalContent).display);
        console.log('Initial content opacity:', window.getComputedStyle(modalContent).opacity);
        console.log('Initial content z-index:', window.getComputedStyle(modalContent).zIndex);
        console.log('Initial content background:', window.getComputedStyle(modalContent).backgroundColor);
        console.log('Initial content visibility:', window.getComputedStyle(modalContent).visibility);
    }
    
    // Add show class
    modal.classList.add('show');
    console.log('✅ Added "show" class to modal');
    
    // Force backdrop to be darker and more visible
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.75)';
    modal.style.zIndex = '99999';
    modal.style.setProperty('display', 'flex', 'important');
    modal.style.setProperty('opacity', '1', 'important');
    console.log('✅ Set backdrop styles:', {
        backgroundColor: modal.style.backgroundColor,
        zIndex: modal.style.zIndex,
        display: modal.style.display
    });
    
    if (modalContent) {
        modalContent.classList.add('show');
        // Force modal content to be fully opaque and visible
        modalContent.style.opacity = '1';
        modalContent.style.background = '#ffffff';
        modalContent.style.zIndex = '100000';
        modalContent.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.5)';
        modalContent.style.border = '2px solid rgba(255, 255, 255, 1)';
        modalContent.style.display = 'block';
        modalContent.style.visibility = 'visible';
        // Remove any filters that might cause transparency
        modalContent.style.filter = 'none';
        modalContent.style.webkitFilter = 'none';
        modalContent.style.mozFilter = 'none';
        
        console.log('✅ Set modal content styles:', {
            opacity: modalContent.style.opacity,
            background: modalContent.style.background,
            zIndex: modalContent.style.zIndex,
            display: modalContent.style.display,
            visibility: modalContent.style.visibility
        });
        
        // Check computed styles after setting
        setTimeout(() => {
            const computed = window.getComputedStyle(modalContent);
            console.log('📊 Computed styles after setting:', {
                display: computed.display,
                opacity: computed.opacity,
                zIndex: computed.zIndex,
                backgroundColor: computed.backgroundColor,
                visibility: computed.visibility,
                position: computed.position,
                transform: computed.transform
            });
        }, 100);
    } else {
        console.error('❌ ERROR: Modal content element not found!');
    }
    
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Final check
    setTimeout(() => {
        const finalComputed = window.getComputedStyle(modal);
        console.log('📊 Final modal computed styles:', {
            display: finalComputed.display,
            opacity: finalComputed.opacity,
            zIndex: finalComputed.zIndex,
            backgroundColor: finalComputed.backgroundColor,
            visibility: finalComputed.visibility
        });
        
        if (finalComputed.display === 'none') {
            console.error('❌ PROBLEM: Modal display is still "none"!');
            console.log('Attempting to force display...');
            modal.style.setProperty('display', 'flex', 'important');
        }
        
            // ALWAYS force opacity to 1, regardless of computed value
            console.log('🔧 Forcing modal opacity to 1 (current:', finalComputed.opacity, ')...');
            modal.style.setProperty('opacity', '1', 'important');
            modal.style.setProperty('-webkit-opacity', '1', 'important');
            modal.style.setProperty('-moz-opacity', '1', 'important');
            // Remove animation that might be affecting opacity
            modal.style.removeProperty('animation');
            modal.style.removeProperty('-webkit-animation');
            modal.style.removeProperty('-moz-animation');
            
            // Also ALWAYS force modal content opacity to 1
            if (modalContent) {
                const contentComputed = window.getComputedStyle(modalContent);
                console.log('🔧 Forcing modal content opacity to 1 (current:', contentComputed.opacity, ')...');
                modalContent.style.setProperty('opacity', '1', 'important');
                modalContent.style.setProperty('-webkit-opacity', '1', 'important');
                modalContent.style.setProperty('-moz-opacity', '1', 'important');
                // Remove animation
                modalContent.style.removeProperty('animation');
                modalContent.style.removeProperty('-webkit-animation');
                modalContent.style.removeProperty('-moz-animation');
                
                // Verify after fix
                setTimeout(() => {
                    const verifyComputed = window.getComputedStyle(modalContent);
                    console.log('✅ Verified modal content opacity after fix:', verifyComputed.opacity);
                    if (parseFloat(verifyComputed.opacity) < 1) {
                        console.error('❌ Still not 1! Trying alternative fix...');
                        modalContent.style.cssText += 'opacity: 1 !important;';
                    }
                }, 50);
            }
            
            // Verify modal opacity after fix
            setTimeout(() => {
                const verifyModal = window.getComputedStyle(modal);
                console.log('✅ Verified modal opacity after fix:', verifyModal.opacity);
                if (parseFloat(verifyModal.opacity) < 1) {
                    console.error('❌ Still not 1! Trying alternative fix...');
                    modal.style.cssText += 'opacity: 1 !important;';
                }
            }, 50);
        
        // Visual debug indicator
        if (modalContent) {
            const rect = modalContent.getBoundingClientRect();
            console.log('📐 Modal content position and size:', {
                top: rect.top,
                left: rect.left,
                width: rect.width,
                height: rect.height,
                visible: rect.width > 0 && rect.height > 0
            });
            
            if (rect.width === 0 || rect.height === 0) {
                console.error('❌ PROBLEM: Modal content has zero dimensions!');
            }
        }
        
        console.log('=== MODAL DEBUG END ===');
    }, 200);
}

function closeAttendanceModal() {
    console.log('=== MODAL DEBUG: Closing Attendance Modal ===');
    const modal = document.getElementById('attendanceModal');
    const modalContent = modal?.querySelector('.attendance-modal');
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        if (modalContent) {
            modalContent.classList.remove('show');
        }
        document.body.style.overflow = ''; // Restore scrolling
        console.log('✅ Modal closed');
    }
}

// Close modal when clicking outside
document.getElementById('attendanceModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAttendanceModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAttendanceModal();
    }
});

// Real-time activity time updates
(function() {
    let updateInterval = null;
    
    function updateActivityTimes() {
        const activityTimeElements = document.querySelectorAll('.activity-time');
        
        if (activityTimeElements.length === 0) {
            console.log('No activity time elements found');
            return;
        }
        
        activityTimeElements.forEach(function(element) {
            let timestampStr = element.getAttribute('data-timestamp');
            const timeString = element.getAttribute('data-time-string');
            
            // If timestamp is 0 or empty, try to parse from time string
            if (!timestampStr || timestampStr === '0' || timestampStr === '' || timestampStr === null) {
                if (timeString) {
                    try {
                        const parsedDate = new Date(timeString);
                        if (!isNaN(parsedDate.getTime())) {
                            timestampStr = Math.floor(parsedDate.getTime() / 1000).toString();
                            element.setAttribute('data-timestamp', timestampStr);
                        } else {
                            return; // Can't parse time
                        }
                    } catch (e) {
                        return; // Error parsing
                    }
                } else {
                    return; // No timestamp or time string
                }
            }
            
            const timestamp = parseInt(timestampStr);
            if (!timestamp || isNaN(timestamp) || timestamp <= 0) {
                return; // Invalid timestamp
            }
            
            const now = Math.floor(Date.now() / 1000);
            const diff = now - timestamp;
            
            if (diff < 0) {
                return; // Future time, skip
            }
            
            let formattedText = '';
            let formattedTime = '';
            
            if (diff < 60) { // Less than 1 minute
                formattedText = 'Just now';
            } else if (diff < 3600) { // Less than 1 hour
                const minutes = Math.floor(diff / 60);
                formattedText = minutes === 1 ? '1 minute ago' : `${minutes} minutes ago`;
            } else if (diff < 86400) { // Less than 24 hours
                const hours = Math.floor(diff / 3600);
                formattedText = hours === 1 ? '1 hour ago' : `${hours} hours ago`;
            } else if (diff < 604800) { // Less than 7 days
                const days = Math.floor(diff / 86400);
                if (days === 1) {
                    formattedText = 'Yesterday';
                    formattedTime = new Date(timestamp * 1000).toLocaleTimeString('en-US', { 
                        hour: 'numeric', 
                        minute: '2-digit',
                        hour12: true 
                    });
                } else {
                    formattedText = `${days} days ago`;
                }
            } else {
                const date = new Date(timestamp * 1000);
                formattedText = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                formattedTime = date.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
            }
            
            // Update the text content
            if (formattedTime) {
                element.textContent = `${formattedText}, ${formattedTime}`;
            } else {
                element.textContent = formattedText;
            }
        });
    }
    
    // Initialize and start updates
    function initActivityTimeUpdates() {
        // Clear any existing interval
        if (updateInterval) {
            clearInterval(updateInterval);
        }
        
        // Update immediately
        updateActivityTimes();
        
        // Update every 30 seconds for more responsive updates
        updateInterval = setInterval(updateActivityTimes, 30000);
    }
    
    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initActivityTimeUpdates);
    } else {
        // DOM is already loaded
        initActivityTimeUpdates();
    }
})();
</script>


