<?php
$title = "Manager Dashboard";
require_once views_path("partials/header");

// Scripts
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

$username = isset($_SESSION['manager_name']) ? $_SESSION['manager_name'] : 'Guest';
?>

<div class="flex min-h-screen overflow-hidden">
    <main id="mainContent" class="flex-1 p-6 bg-gray-100 transition-margin duration-300 ease-in-out" style="margin-left: 256px;">
        <?php require_once views_path("branch/branch_sidebar"); ?>

       
        <div>
            <span class="text-2xl font-bold tracking-tight">My Payslips & Analytics</span>
            <p class="text-gray-600 mb-4">A detailed summary of your salary, deductions, and net pay for the selected period.</p>
        </div>

        
        <!-- <div class="text-center mt-4 mb-6">
    <div class="inline-flex rounded-lg overflow-hidden border-1 border-green-600 mx-auto">
        <button
            id="payslipBtn"
            class="px-6 py-2 text-sm font-semibold transition-all duration-300 bg-green-600 text-white hover:bg-green-700 focus:outline-none"
            onclick="showAnalytics('payslip')"
        >
            Payslip Analytics
        </button>
        <button
            id="leaveBtn"
            class="px-6 py-2 text-sm font-semibold transition-all duration-300 bg-white text-green-600 hover:bg-green-100 focus:outline-none"
            onclick="showAnalytics('leave')"
        >
            Leave Analytics
        </button>
    </div>
</div> -->

        <!-- Payslip Analytics Section -->
        <div id="payslipSection">
            <!-- Payslip Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
               <div class="bg-white p-4 rounded-2xl shadow-md" style="border-left: 5px solid #22c55e;">
                    <span class="text-lg font-semibold text-gray-700">Total Leave Requests</span>
                    <p class="text-lg font-bold text-gray-700">12</p>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-md" style="border-left: 5px solid #22c55e;">
                    <span class="text-lg font-semibold text-gray-700">Approved Leaves</span>
                    <p class="text-lg font-bold text-gray-700">9</p>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-md" style="border-left: 5px solid #22c55e;">
                    <span class="text-lg font-semibold text-gray-700">Rejected Leaves</span>
                    <p class="text-lg font-bold text-gray-700">3</p>
                </div>

            </div>

            <!-- Payslip Chart -->
            <div class="bg-white p-6 rounded-2xl shadow-md mb-10">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 h-[550px]"> <!-- Equal height here -->

    <!-- Calendar Column -->
    <div class="flex flex-col h-full">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Calendar</h3>
      <div class="bg-gray-50 rounded-xl p-4 shadow-md border border-gray-200 flex-grow flex flex-col">
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

    <!-- Activity Logs Column -->
    <div class="flex flex-col h-full">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Activity Logs</h3>
      <div class="bg-gray-50 rounded-xl p-4 shadow-md border border-gray-200 flex-grow overflow-y-auto space-y-5">

        <!-- Each log item -->
        <div class="flex items-start gap-3">
          <div class="w-2 h-2 bg-emerald-500 rounded-full mt-1"></div>
          <div>
            <p class="text-sm text-gray-800"><span class="font-bold text-emerald-600">Manager Sophia Cruz</span> logged in</p>
            <span class="text-xs text-gray-500">2025-05-30 08:00 AM</span>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <div class="w-2 h-2 bg-blue-500 rounded-full mt-1"></div>
          <div>
            <p class="text-sm text-gray-800"><span class="font-bold text-blue-600">Manager Sophia Cruz</span> approved leave for <span class="font-medium">Anna Santos</span></p>
            <span class="text-xs text-gray-500">2025-05-29 03:15 PM</span>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <div class="w-2 h-2 bg-red-500 rounded-full mt-1"></div>
          <div>
            <p class="text-sm text-gray-800"><span class="font-bold text-red-600">Manager John Lim</span> rejected leave for <span class="font-medium">Carl Dela Cruz</span></p>
            <span class="text-xs text-gray-500">2025-05-29 01:40 PM</span>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <div class="w-2 h-2 bg-yellow-500 rounded-full mt-1"></div>
          <div>
            <p class="text-sm text-gray-800"><span class="font-bold text-yellow-600">Manager Sophia Cruz</span> processed payroll for <span class="font-medium">May 2025</span></p>
            <span class="text-xs text-gray-500">2025-05-28 05:30 PM</span>
          </div>
        </div>

        <div class="text-center text-sm text-gray-400 italic mt-auto">End of logs</div>
      </div>
    </div>

  </div>
</div>



        </div>

        <!-- Leave Analytics Section -->
        <div id="leaveSection" class="hidden">
            

            <!-- Leave Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
               <div class="bg-white p-4 rounded-2xl shadow-md" style="border-left: 5px solid #8b5cf6; border-right: 5px solid #8b5cf6;">
                    <span class="text-lg font-semibold text-gray-700">Total Leave Requests</span>
                    <p class="text-lg font-bold text-purple-600">12</p>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-md" style="border-left: 5px solid #22c55e; border-right: 5px solid #22c55e;">
                    <span class="text-lg font-semibold text-gray-700">Approved Leaves</span>
                    <p class="text-lg font-bold text-green-500">9</p>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-md" style="border-left: 5px solid #ef4444; border-right: 5px solid #ef4444;">
                    <span class="text-lg font-semibold text-gray-700">Rejected Leaves</span>
                    <p class="text-lg font-bold text-red-500">3</p>
                </div>

            </div>

            <!-- Leave Chart -->
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Monthly Leave Request Status</h3>
                <canvas id="leaveChart" height="100"></canvas>
            </div>
        </div>
    </main>
</div>

<!-- Chart.js Scripts -->
<script>
    // // Charts Initialization
    // const payslipCtx = document.getElementById('payslipChart').getContext('2d');
    // const leaveCtx = document.getElementById('leaveChart').getContext('2d');

    // const payslipChart = new Chart(payslipCtx, {
    //     type: 'bar',
    //     data: {
    //         labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    //         datasets: [
    //             {
    //                 label: 'Gross Pay',
    //                 data: [14000, 14500, 15000, 15500, 16000, 17000],
    //                 backgroundColor: 'rgba(16, 185, 129, 0.6)', // green
    //             },
    //             {
    //                 label: 'Deductions',
    //                 data: [2000, 2100, 2200, 2300, 2400, 2500],
    //                 backgroundColor: 'rgba(239, 68, 68, 0.6)', // red
    //             },
    //             {
    //                 label: 'Net Pay',
    //                 data: [12000, 12400, 12800, 13200, 13600, 14500],
    //                 backgroundColor: 'rgba(59, 130, 246, 0.6)', // blue
    //             }
    //         ]
    //     },
    //     options: {
    //         responsive: true,
    //         plugins: {
    //             legend: { position: 'top' }
    //         },
    //         scales: {
    //             y: {
    //                 beginAtZero: true,
    //                 ticks: { callback: value => '₱' + value }
    //             }
    //         }
    //     }
    // });

    // const leaveChart = new Chart(leaveCtx, {
    //     type: 'line',
    //     data: {
    //         labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    //         datasets: [
    //             {
    //                 label: 'Approved',
    //                 data: [2, 1, 3, 1, 1, 1],
    //                 backgroundColor: 'rgba(34, 197, 94, 0.2)',
    //                 borderColor: 'rgba(34, 197, 94, 1)',
    //                 tension: 0.4,
    //                 fill: true
    //             },
    //             {
    //                 label: 'Rejected',
    //                 data: [0, 1, 0, 1, 0, 1],
    //                 backgroundColor: 'rgba(239, 68, 68, 0.2)',
    //                 borderColor: 'rgba(239, 68, 68, 1)',
    //                 tension: 0.4,
    //                 fill: true
    //             }
    //         ]
    //     },
    //     options: {
    //         responsive: true,
    //         plugins: {
    //             legend: { position: 'top' }
    //         },
    //         scales: {
    //             y: {
    //                 beginAtZero: true,
    //                 ticks: { stepSize: 1 }
    //             }
    //         }
    //     }
    // });

    // Toggle function for Analytics sections and buttons
    function showAnalytics(type) {
    const payslipSection = document.getElementById('payslipSection');
    const leaveSection = document.getElementById('leaveSection');
    const payslipBtn = document.getElementById('payslipBtn');
    const leaveBtn = document.getElementById('leaveBtn');

    if (type === 'payslip') {
        payslipSection.classList.remove('hidden');
        leaveSection.classList.add('hidden');

        payslipBtn.classList.add('bg-green-600', 'text-white', 'hover:bg-green-700', 'focus:outline-none');
        payslipBtn.classList.remove('bg-white', 'text-green-600', 'hover:bg-green-100');

        leaveBtn.classList.add('bg-white', 'text-green-600', 'hover:bg-green-100', 'focus:outline-none');
        leaveBtn.classList.remove('bg-green-600', 'text-white', 'hover:bg-green-700');
    } else {
        payslipSection.classList.add('hidden');
        leaveSection.classList.remove('hidden');

        leaveBtn.classList.add('bg-green-600', 'text-white', 'hover:bg-green-700', 'focus:outline-none');
        leaveBtn.classList.remove('bg-white', 'text-green-600', 'hover:bg-green-100');

        payslipBtn.classList.add('bg-white', 'text-green-600', 'hover:bg-green-100', 'focus:outline-none');
        payslipBtn.classList.remove('bg-green-600', 'text-white', 'hover:bg-green-700');
    }
}


    // Default show payslip analytics on load
    // document.addEventListener('DOMContentLoaded', () => {
    //     showAnalytics('payslip');
    // });
</script>
