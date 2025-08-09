<?php
$title = "User Dashboard";
require_once views_path("partials/header");

// Add Bootstrap Icons CSS
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">';

// Scripts
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

// Fix session variable handling
$loginSuccess = $_SESSION['login_success'] ?? false;
$username = $_SESSION['name'] ?? $_SESSION['username'] ?? $_SESSION['first_name'] ?? 'John';

if ($loginSuccess) {
    unset($_SESSION['login_success']); // So it only shows once
    unset($_SESSION['username']);
}

$isMobile = '<script>document.write(window.innerWidth < 768 ? "true" : "false");</script>';
?>

<div class="flex min-h-screen overflow-hidden <?= $isMobile ? 'bg-gray-100' : '' ?>">    

    <main id="mainContent" class="flex-1 p-6 bg-gray-100 transition-all duration-300 ease-in-out ">
        <?php require_once views_path("partials/user_sidebar"); ?>

        <!-- Page Title -->
        <h1 class="text-3xl font-bold text-gray-800 mt-6 mb-6">Welcome Back, <?php echo htmlspecialchars($username); ?>!</h1>

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

            <!-- Attendance Overview -->
            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Attendance Overview</h2>
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 4h10M5 11h14M5 15h14M5 19h14"></path>
                    </svg>
                </div>
                <p class="text-gray-600 mb-2">Today’s Status: <span class="font-bold text-green-600">Present</span></p>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li>May 30, 2025 - Present</li>
                    <li>May 29, 2025 - Present</li>
                    <li>May 28, 2025 - Late</li>
                    <li>May 27, 2025 - Absent</li>
                    <li>May 26, 2025 - Present</li>
                </ul>
            </div>

            <!-- Leave Balance & Requests -->
            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Leave Balance</h2>
                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a5 5 0 00-10 0v2M5 13h14v9H5v-9z"></path>
                    </svg>
                </div>
                <div class="space-y-1 text-gray-600">
                    <p>Sick Leave: <span class="font-bold text-gray-800">4 days</span></p>
                    <p>Vacation Leave: <span class="font-bold text-gray-800">6 days</span></p>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-800 mb-1">Recent Requests:</p>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li class="flex justify-between">
                            <span>May 20 - Vacation</span>
                            <span class="text-green-600 font-semibold">Approved</span>
                        </li>
                        <li class="flex justify-between">
                            <span>May 15 - Sick Leave</span>
                            <span class="text-yellow-600 font-semibold">Pending</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Payslip Summary -->
            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Payslip Summary</h2>
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.38 0-2.5 1.12-2.5 2.5S10.62 13 12 13s2.5-1.12 2.5-2.5S13.38 8 12 8zm0 0v8"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 18h16"></path>
                    </svg>
                </div>
                <div class="space-y-2 text-gray-700">
                    <p>Latest Net Pay: <span class="font-bold text-gray-800">₱18,500</span></p>
                    <p>Release Date: <span class="font-bold text-gray-800">May 28, 2025</span></p>
                </div>
                <div class="mt-4 space-x-2">
                    <a href="view_payslip.php" class="inline-block bg-blue-500 text-white px-4 py-1 rounded-full hover:bg-blue-600 text-sm transition">View Payslip</a>
                    <a href="download_payslip.php" class="inline-block bg-green-500 text-white px-4 py-1 rounded-full hover:bg-green-600 text-sm transition">Download</a>
                </div>
            </div>
        </div>

        <!-- Calendar & Activity Log -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Calendar Section -->
    <div class="flex flex-col h-full">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Calendar</h3>
        <div class="bg-white rounded-xl p-4 shadow-md border border-gray-200 flex-grow flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <button class="text-sm text-gray-600 hover:text-black hover:underline">&lt; Prev</button>
                <h5 class="text-lg font-semibold text-gray-700">May 2025</h5>
                <button class="text-sm text-gray-600 hover:text-black hover:underline">Next &gt;</button>
            </div>
            <div class="grid grid-cols-7 text-center text-gray-600 text-sm font-semibold border-b pb-2">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>
            <div class="grid grid-cols-7 text-center text-sm text-gray-700 gap-2 mt-2 flex-grow">
                <!-- Calendar Dates -->
                <div></div><div></div><div></div>
                <!-- Dates 1-31 -->
                <!-- Highlight May 30 -->
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">1</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">2</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">3</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">4</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">5</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">6</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">7</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">8</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">9</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">10</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">11</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">12</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">13</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">14</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">15</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">16</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">17</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">18</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">19</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">20</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">21</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">22</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">23</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">24</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">25</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">26</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">27</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">28</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">29</div>
                <div class="rounded-lg bg-green-200 font-bold p-2">30</div>
                <div class="rounded-lg hover:bg-green-100 cursor-pointer p-2">31</div>
            </div>
        </div>
    </div>

    <!-- Activity Log Section -->
<div class="flex flex-col h-full">
  <h3 class="text-lg font-semibold text-gray-800 mb-4">Activity Log</h3>
  <div class="bg-white rounded-xl shadow-md p-4 flex-grow overflow-hidden flex flex-col">
    <ul class="text-sm text-gray-700 space-y-3 overflow-y-auto pr-2 custom-scroll">
      <li class="flex items-start gap-3 border-b pb-2 hover:bg-gray-50 rounded-lg p-2 transition">
        <span class="text-xs bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full font-medium">09:10 AM</span>
        <span>Logged in</span>
      </li>
      <li class="flex items-start gap-3 border-b pb-2 hover:bg-gray-50 rounded-lg p-2 transition">
        <span class="text-xs bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full font-medium">10:00 AM</span>
        <span>Checked payslip</span>
      </li>
      <li class="flex items-start gap-3 border-b pb-2 hover:bg-gray-50 rounded-lg p-2 transition">
        <span class="text-xs bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full font-medium">12:00 PM</span>
        <span>Lunch break</span>
      </li>
      <li class="flex items-start gap-3 border-b pb-2 hover:bg-gray-50 rounded-lg p-2 transition">
        <span class="text-xs bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full font-medium">01:15 PM</span>
        <span>Back to work</span>
      </li>
      <li class="flex items-start gap-3 border-b pb-2 hover:bg-gray-50 rounded-lg p-2 transition">
        <span class="text-xs bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full font-medium">03:30 PM</span>
        <span>Viewed attendance</span>
      </li>
      <li class="flex items-start gap-3 hover:bg-gray-50 rounded-lg p-2 transition">
        <span class="text-xs bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full font-medium">05:00 PM</span>
        <span>Logged out</span>
      </li>
    </ul>
  </div>
</div>

</div>

    </main>
</div>


 <?php if ($loginSuccess): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
        });
    </script>
    <?php endif; ?>


<!-- 
<script>
    function showAnalytics(type) {
    const sections = {
        payslip: document.getElementById('payslipSection'),
        payroll: document.getElementById('payrollSection'),
        attendance: document.getElementById('attendanceSection'),
        leave: document.getElementById('leaveSection'),
    };

    const buttons = {
        payslip: document.getElementById('payslipBtn'),
        payroll: document.getElementById('payrollBtn'),
        attendance: document.getElementById('attendanceBtn'),
        leave: document.getElementById('leaveBtn'),
    };

    // Reset all sections and buttons
    for (const key in sections) {
        // Hide all sections
        sections[key].classList.add('hidden');

        // Reset button styles
        buttons[key].classList.remove('bg-green-600', 'text-white', 'hover:bg-green-700');
        buttons[key].classList.add('bg-white', 'text-green-600', 'hover:bg-green-100', 'focus:outline-none');
    }

    // Show the selected section
    sections[type].classList.remove('hidden');

    // Highlight the active button
    buttons[type].classList.remove('bg-white', 'text-green-600', 'hover:bg-green-100');
    buttons[type].classList.add('bg-green-600', 'text-white', 'hover:bg-green-700', 'focus:outline-none');
}


    // Show default section on load
    showAnalytics('payslip');

    // Payslip Chart
    const payslipCtx = document.getElementById('payslipChart').getContext('2d');
    new Chart(payslipCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Gross Pay',
                    data: [14000, 14500, 15000, 15500, 16000, 17000],
                    backgroundColor: 'rgba(16, 185, 129, 0.6)', // green
                },
                {
                    label: 'Deductions',
                    data: [2000, 2100, 2200, 2300, 2400, 2500],
                    backgroundColor: 'rgba(239, 68, 68, 0.6)', // red
                },
                {
                    label: 'Net Pay',
                    data: [12000, 12400, 12800, 13200, 13600, 14500],
                    backgroundColor: 'rgba(59, 130, 246, 0.6)', // blue
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => '₱' + value
                    }
                }
            }
        }
    });

    // Payroll Chart (sample line chart)
    const payrollCtx = document.getElementById('payrollChart').getContext('2d');
    new Chart(payrollCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Payroll Amount',
                data: [13000, 13500, 14000, 14500, 15000, 16000],
                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                borderColor: 'rgba(34, 197, 94, 1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => '₱' + value
                    }
                }
            }
        }
    });

    // Attendance Chart (Pie Chart)
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'pie',
        data: {
            labels: ['Present', 'Absent', 'Late'],
            datasets: [{
                data: [20, 5, 3],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.7)',    // green
                    'rgba(239, 68, 68, 0.7)',    // red
                    'rgba(234, 179, 8, 0.7)'     // yellow
                ],
                borderColor: [
                    'rgba(34, 197, 94, 1)',
                    'rgba(239, 68, 68, 1)',
                    'rgba(234, 179, 8, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Leave Chart
    const leaveCtx = document.getElementById('leaveChart').getContext('2d');
    new Chart(leaveCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Approved Leaves',
                    data: [2, 1, 3, 4, 2, 3],
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Rejected Leaves',
                    data: [1, 0, 1, 0, 2, 1],
                    borderColor: 'rgba(239, 68, 68, 1)',
                    backgroundColor: 'rgba(239, 68, 68, 0.2)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    stepSize: 1
                }
            }
        }
    });
</script>

<style>
    /* Adjust the pie chart canvas size */
    #attendanceChart {
        width: 300px !important;
        height: 300px !important;
    }
</style> -->


