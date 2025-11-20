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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Today's Status -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600 mb-1">Today's Status</p>
                            <p class="text-2xl font-bold text-green-800">Present</p>
                            <p class="text-xs text-green-600 mt-1">Checked in at 8:30 AM</p>
                        </div>
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="bi bi-check-circle text-white text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Work Hours -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600 mb-1">Work Hours</p>
                            <p class="text-2xl font-bold text-blue-800">7.5 hrs</p>
                            <p class="text-xs text-blue-600 mt-1">This week: 37.5 hrs</p>
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
                            <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                View Details <i class="bi bi-arrow-right ml-1"></i>
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
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        <span class="text-sm text-gray-700">May 30, 2025</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-medium text-green-600">Present</span>
                                        <p class="text-xs text-gray-500">8:30 AM - 5:30 PM</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        <span class="text-sm text-gray-700">May 29, 2025</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-medium text-green-600">Present</span>
                                        <p class="text-xs text-gray-500">8:15 AM - 5:45 PM</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                        <span class="text-sm text-gray-700">May 28, 2025</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-medium text-yellow-600">Late</span>
                                        <p class="text-xs text-gray-500">9:15 AM - 6:15 PM</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Requests & Balance -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Leave Management</h2>
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Request Leave
                            </button>
                        </div>
                        
                        <!-- Leave Balance Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-blue-600 font-medium">Vacation Leave</p>
                                        <p class="text-2xl font-bold text-blue-800">6 days</p>
                                    </div>
                                    <i class="bi bi-umbrella text-blue-500 text-xl"></i>
                                </div>
                            </div>
                            
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-red-600 font-medium">Sick Leave</p>
                                        <p class="text-2xl font-bold text-red-800">4 days</p>
                                    </div>
                                    <i class="bi bi-heart-pulse text-red-500 text-xl"></i>
                                </div>
                            </div>
                            
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-green-600 font-medium">Personal Leave</p>
                                        <p class="text-2xl font-bold text-green-800">2 days</p>
                                    </div>
                                    <i class="bi bi-person text-green-500 text-xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Leave Requests -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Recent Requests</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Vacation Leave</p>
                                        <p class="text-xs text-gray-500">May 20-22, 2025 (3 days)</p>
                                    </div>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                        Approved
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Sick Leave</p>
                                        <p class="text-xs text-gray-500">May 15, 2025 (1 day)</p>
                                    </div>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full">
                                        Pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Quick Actions</h2>
                        <div class="space-y-3">
                            <button class="w-full flex items-center justify-between p-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors">
                                <div class="flex items-center space-x-3">
                                    <i class="bi bi-clock text-blue-600"></i>
                                    <span class="text-sm font-medium text-blue-900">Time In/Out</span>
                                </div>
                                <i class="bi bi-chevron-right text-blue-600"></i>
                            </button>
                            
                            <button class="w-full flex items-center justify-between p-3 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg transition-colors">
                                <div class="flex items-center space-x-3">
                                    <i class="bi bi-file-earmark-text text-green-600"></i>
                                    <span class="text-sm font-medium text-green-900">View Payslip</span>
                                </div>
                                <i class="bi bi-chevron-right text-green-600"></i>
                            </button>
                            
                            <button class="w-full flex items-center justify-between p-3 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg transition-colors">
                                <div class="flex items-center space-x-3">
                                    <i class="bi bi-calendar-plus text-purple-600"></i>
                                    <span class="text-sm font-medium text-purple-900">Request Leave</span>
                </div>
                                <i class="bi bi-chevron-right text-purple-600"></i>
                            </button>
                            
                            <button class="w-full flex items-center justify-between p-3 bg-orange-50 hover:bg-orange-100 border border-orange-200 rounded-lg transition-colors">
                                <div class="flex items-center space-x-3">
                                    <i class="bi bi-person-gear text-orange-600"></i>
                                    <span class="text-sm font-medium text-orange-900">Update Profile</span>
                </div>
                                <i class="bi bi-chevron-right text-orange-600"></i>
                            </button>
            </div>
        </div>

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
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Recent Activity</h2>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">Time in recorded</p>
                                    <p class="text-xs text-gray-500">Today, 8:30 AM</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">Payslip downloaded</p>
                                    <p class="text-xs text-gray-500">Yesterday, 2:15 PM</p>
  </div>
</div>

                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">Leave request submitted</p>
                                    <p class="text-xs text-gray-500">May 28, 2025</p>
                                </div>
</div>

                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-orange-500 rounded-full mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">Profile updated</p>
                                    <p class="text-xs text-gray-500">May 27, 2025</p>
                                </div>
                            </div>
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
    document.getElementById('currentDate').textContent = currentDate.toLocaleDateString('en-US', options);

    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Work Hours',
                data: [8, 8.5, 7.5, 8, 8, 0, 0],
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
                    max: 10,
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
</style>


