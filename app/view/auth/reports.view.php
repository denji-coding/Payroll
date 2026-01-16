<?php
$title = "Reports";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");

// Data for Pie Chart
$dataPoints = array( 
  array("label" => "Manager", "y" => 5),
  array("label" => "Human Resources", "y" => 5),
  array("label" => "Staff", "y" => 30),
  array("label" => "Driver", "y" => 10)
);

// Extract labels and values for Chart.js
$labels = array_column($dataPoints, 'label');
$values = array_column($dataPoints, 'y');
$reports = [
    [
        'id' => 1,
        'type' => 'Attendance',
        'from_date' => '2025-05-01',
        'to_date' => '2025-05-15',
        'details' => '15 records processed',
        'generated_at' => '2025-05-29 10:45:00'
    ],
    [
        'id' => 2,
        'type' => 'Leave Summary',
        'from_date' => '2025-04-01',
        'to_date' => '2025-04-30',
        'details' => '10 employees on leave',
        'generated_at' => '2025-05-29 11:10:00'
    ]
];


?>


<style>
    .tooltip-container {
  position: relative;
  display: inline-block;
  cursor: pointer;
}

.tooltip-container .tooltip-text {
  visibility: hidden;
  width: 80px;
  background-color: #333;
  color: #fff;
  text-align: center;
  border-radius: 4px;
  padding: 5px 7px;
  position: absolute;
  z-index: 10;
  bottom: 125%; /* position above the button */
  left: 50%;
  transform: translateX(-50%);
  opacity: 0;
  transition: opacity 0.3s;
  font-size: 12px;
  pointer-events: none;
}

.tooltip-container:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
}

</style>

<!-- Tailwind and Chart.js -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<main class="ml-64 mt-12 p-6  bg-gray-100 min-h-screen">
  <div class="space-y-4">
    <div>
      <span class="text-2xl font-bold text-[#133913]">Reports</span>
      <p class="text-[#478547]">View and generate analytical reports for management decisions</p>
    </div>

    <div class="mt-6 bg-white shadow-md border-2 border-green-200 rounded-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-gray-200 relative">
                <span class="text-lg font-semibold text-gray-800"></span>
                <div class="relative max-w-sm w-full sm:w-auto">
                  <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="employeeSearch"
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

            <div class="p-4 bg-white -mt-4 rounded shadow-sm max-w-full">
                <form id="reportForm" method="GET" action="" class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col">
                            <label for="reportType" class="font-semibold text-gray-700 mb-1">Report Type</label>
                            <select id="reportType" name="report_type" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-600" required>
                                <option value="" disabled selected>Select report type</option>
                                <option value="Attendance">Attendance Report</option>
                                <option value="Leave Summary">Leave Report</option>
                                <option value="Payroll">Payroll Summary Report</option>
                                <option value="Payroll">Tax Deuductions Report</option>
                            </select>
                        </div>

                        <div class="flex flex-col">
                            <label for="fromDate" class="font-semibold text-gray-700 mb-1">From:</label>
                            <input type="date" id="fromDate" name="from_date" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-600" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="toDate" class="font-semibold text-gray-700 mb-1">To:</label>
                            <input type="date" id="toDate" name="to_date" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-600" required>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <label class="opacity-0 mb-1 select-none">Hidden label</label> <!-- invisible label to align button with inputs -->
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-5 rounded">Generate</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table id="reportsTable" class="min-w-full divide-y divide-gray-200 text-sm overflow-hidden">
                    <thead class="bg-emerald-600 text-white text-center">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">ID</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">Report Type</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">From</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">To</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">Generated At</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr class="hover:bg-gray-50 text-center">
                            <td class="px-6 py-4"><?php echo htmlspecialchars($report['id']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($report['type']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($report['from_date']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($report['to_date']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($report['generated_at']); ?></td>
                            <td class="px-6 py-4 text-center">
                                <div class="tooltip-container">
                                    <button
                                        class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-emerald-600 hover:text-white"
                                        onclick='viewReportDemo(<?php echo json_encode($report); ?>)'>
                                        <i class="bi bi-eye text-lg"></i>
                                    </button>
                                    <span class="tooltip-text">View Report Details</span>
                                </div>
                            </td>


                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    </div>


    <script>
(() => {
    const form = document.getElementById('reportForm');
    const reportsTableBody = document.querySelector('#reportsTable tbody');
    const reportTypeSelect = document.getElementById('reportType');
    const fromDateInput = document.getElementById('fromDate');
    const toDateInput = document.getElementById('toDate');

    let maxId = <?php echo count($reports) > 0 ? max(array_column($reports, 'id')) : 0; ?>;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const type = reportTypeSelect.value;
        const from = fromDateInput.value;
        const to = toDateInput.value;

        if (!type || !from || !to) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please fill in all fields before generating the report!'
            });
            return;
        }

        maxId++;

        const generatedAt = new Date().toISOString().slice(0, 19).replace('T', ' ');

        const newRow = document.createElement('tr');
        newRow.classList.add('hover:bg-gray-50');
        newRow.innerHTML = `
            <td class="px-6 py-4 text-center">${maxId}</td>
            <td class="px-6 py-4 text-center">${type}</td>
            <td class="px-6 py-4 text-center">${from}</td>
            <td class="px-6 py-4 text-center">${to}</td>
            <td class="px-6 py-4 text-center">${generatedAt}</td>
            <td class="px-6 py-4 text-center">
    <div class="tooltip-container">
        <button
            class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-emerald-600 hover:text-white"
            onclick='viewReportDemo(<?php echo json_encode($report); ?>)'>
            <i class="bi bi-eye text-lg"></i>
        </button>
        <span class="tooltip-text">View Report Details</span>
    </div>
</td>

        `;

        reportsTableBody.appendChild(newRow);

        form.reset();

        Swal.fire({
            icon: 'success',
            title: 'Report Generated',
            text: `Your ${type} report from ${from} to ${to} was successfully generated!`,
            timer: 2500,
            timerProgressBar: true,
            showConfirmButton: false
        });
    });
})();

function viewReportDemo(report) {
  let reportText = '';

  switch(report.type.toLowerCase()) {
    case 'attendance':
      reportText = `
Report Type: Attendance Report
From: ${report.from_date}
To: ${report.to_date}

Details:
- Summary: ${report.details}

Generated At: ${report.generated_at}
      `;
      break;

    case 'leave summary':
    case 'leave':
      reportText = `
Report Type: Leave Report
From: ${report.from_date}
To: ${report.to_date}

Details:
- Summary: ${report.details}

Generated At: ${report.generated_at}
      `;
      break;

    case 'payroll':
      reportText = `
Report Type: Payroll Report
From: ${report.from_date}
To: ${report.to_date}

Details:
- Summary: ${report.details}

Generated At: ${report.generated_at}
      `;
      break;

    default:
      reportText = `
Report Type: ${report.type}
From: ${report.from_date}
To: ${report.to_date}

Details:
- ${report.details}

Generated At: ${report.generated_at}
      `;
  }

  Swal.fire({
    title: 'Report Preview',
    html: `<pre style="text-align:left; font-family: monospace; background:#f9f9f9; padding: 15px; border-radius: 8px;">${reportText}</pre>`,
    width: 450,
    confirmButtonText: 'Close',
    background: '#f0fdf4',
    iconColor: '#10b981'
  });
}

</script>

  
    <!-- <div id="content-dashboard" class="tab-content">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
   
        <div class="bg-white border-2 border-green-200 shadow-md rounded-lg p-6 flex flex-col items-center">
          <h2 class="text-xl font-semibold mb-4">2025 Attendance Overview</h2>
          <canvas id="attendanceChart" class="w-full h-80"></canvas>
        </div>

       
        <div class="bg-white border-2 border-green-200 shadow-md rounded-lg p-6 flex flex-col items-center">
          <h2 class="text-xl font-semibold mb-4">Employee Distribution</h2>
          <div class="relative w-full max-w-xs h-64">
            <canvas id="pieChart"></canvas>
          </div>
        </div>
      </div>
    </div>

  
    <div id="content-attendance" class="tab-content hidden">
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-2">Attendance Report</h2>
        <p class="text-gray-500">Coming soon...</p>
      </div>
    </div>


 <div id="content-payroll" class="tab-content hidden border-2 border-green-200 shadow-sm rounded-lg">
  <div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold text-gray-800">Monthly Payroll Summary</h2>
      <span class="text-sm text-gray-500">2025 Fiscal Year</span>
    </div>
    

    <div class="relative h-96">
      <canvas id="payrollChart"></canvas>
    </div>
    

    <div class="grid grid-cols-3 gap-3 mt-4">
      <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
        <p class="text-xs text-blue-600 font-medium">Annual Total</p>
        <p class="text-xl font-bold text-blue-800">₱1,758,000</p>
      </div>
      <div class="bg-green-50 p-3 rounded-lg border border-green-100">
        <p class="text-xs text-green-600 font-medium">Monthly Average</p>
        <p class="text-xl font-bold text-green-800">₱146,500</p>
      </div>
      <div class="bg-purple-50 p-3 rounded-lg border border-purple-100">
        <p class="text-xs text-purple-600 font-medium">Highest Month</p>
        <p class="text-xl font-bold text-purple-800">₱160,000 <span class="text-sm">(Dec)</span></p>
      </div>
    </div>
  </div>
</div>


  <div class="bg-white border-2 border-green-200 rounded-xl p-6 mt-6 shadow-sm">
    <h3 class="text-lg font-semibold mb-4 text-gray-700">Available Reports</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <button class="bg-green-700 hover:bg-green-800 text-white font-medium py-3 px-4 rounded-lg shadow-sm">
        Employee Attendance Report
      </button>
      <button class="bg-green-700 hover:bg-green-800 text-white font-medium py-3 px-4 rounded-lg shadow-sm">
        Payroll Summary Report
      </button>
      <button class="bg-green-700 hover:bg-green-800 text-white font-medium py-3 px-4 rounded-lg shadow-sm">
        Employee Headcount Report
      </button>
      <button class="bg-green-700 hover:bg-green-800 text-white font-medium py-3 px-4 rounded-lg shadow-sm">
        Tax Deductions Report
      </button>
      <button class="bg-green-700 hover:bg-green-800 text-white font-medium py-3 px-4 rounded-lg shadow-sm">
        Benefits Report
      </button>
      <button class="bg-green-700 hover:bg-green-800 text-white font-medium py-3 px-4 rounded-lg shadow-sm">
        Employee Performance Report
      </button>
    </div>
  </div>
</main> -->

<!-- Chart Logic -->
<script>
  // Tab logic
  let attendanceChart = null;
  let pieChart = null;
  let payrollChart = null;

  function showTab(tab) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Reset all tab buttons to inactive state
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('bg-emerald-600', 'text-white');
        el.classList.add('bg-white', 'text-gray-600', 'hover:bg-emerald-600', 'hover:text-black');
    });

    // Show the selected tab content
    document.getElementById(`content-${tab}`).classList.remove('hidden');
    
    // Set the active tab button
    const activeTab = document.getElementById(`tab-${tab}`);
    if (activeTab) {
        activeTab.classList.remove('bg-white', 'text-gray-600', 'hover:bg-emerald-600', 'hover:text-black');
        activeTab.classList.add('bg-emerald-600', 'text-white');
    }

    // Initialize charts only when their tab is shown
    if (tab === 'dashboard') {
        initDashboardCharts();
    } else if (tab === 'payroll') {
        initPayrollChart();
    }
}

  function initDashboardCharts() {
    // Destroy existing charts if they exist
    if (attendanceChart) attendanceChart.destroy();
    if (pieChart) pieChart.destroy();

    // Bar Chart (Attendance)
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    attendanceChart = new Chart(attendanceCtx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
          {
            label: 'Present',
            data: [60, 55, 70, 65, 58, 62],
            backgroundColor: '#10B981',
            borderRadius: 6
          },
          {
            label: 'Late',
            data: [15, 12, 10, 14, 10, 11],
            backgroundColor: '#F59E0B',
            borderRadius: 6
          },
          {
            label: 'Absent',
            data: [10, 11, 12, 9, 7, 7],
            backgroundColor: '#EF4444',
            borderRadius: 6
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              stepSize: 25
            }
          }
        },
        plugins: {
          legend: {
            position: 'top',
            labels: {
              boxWidth: 14,
              padding: 10
            }
          }
        }
      }
    });

    // Pie Chart (Employee Distribution)
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    pieChart = new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
          label: 'Employee Distribution',
          data: <?php echo json_encode($values, JSON_NUMERIC_CHECK); ?>,
          backgroundColor: ['#10B981','#3B82F6', '#F59E0B', '#EF4444'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            align: 'center',
            labels: {
              boxWidth: 16,
              padding: 10
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `${context.label}: ${context.raw}`;
              }
            }
          }
        },
        layout: {
          padding: {
            bottom: 20
          }
        }
      }
    });
  }

    let payrollChartInstance = null;

  function initPayrollChart() {
    const ctx = document.getElementById('payrollChart').getContext('2d');
    
    // Destroy previous chart instance if exists
    if (payrollChartInstance) {
      payrollChartInstance.destroy();
    }
    
    const payrollData = {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        label: 'Monthly Payroll (PHP)',
        data: [120000, 125000, 118000, 130000, 135000, 140000, 142000, 138000, 145000, 150000, 155000, 160000],
        borderColor: '#4f46e5',
        backgroundColor: 'rgba(79, 70, 229, 0.05)',
        borderWidth: 3,
        tension: 0.4,
        pointBackgroundColor: '#4f46e5',
        pointRadius: 5,
        pointHoverRadius: 7,
        fill: true
      }]
    };

    payrollChartInstance = new Chart(ctx, {
      type: 'line',
      data: payrollData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 1500, // Animation duration in milliseconds
          easing: 'easeOutQuart', // Smooth easing function
          animateScale: true, // Animate scaling
          animateRotate: true // Animate rotation
        },
        plugins: {
          legend: {
            position: 'top',
            labels: {
              font: {
                size: 14,
                family: 'Inter'
              },
              padding: 20,
              usePointStyle: true,
              pointStyle: 'circle'
            }
          },
          tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            padding: 12,
            usePointStyle: true,
            callbacks: {
              label: function(context) {
                return ' ₱' + context.parsed.y.toLocaleString('en-PH');
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: false,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              callback: function(value) {
                return '₱' + value.toLocaleString('en-PH');
              },
              font: {
                size: 12
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 12
              }
            }
          }
        }
      }
    });
  }

  // Function to show tab and initialize chart
  function showPayrollTab() {
    document.getElementById('content-payroll').classList.remove('hidden');
    initPayrollChart();
  }
  // Initialize the default tab on page load
  document.addEventListener('DOMContentLoaded', function() {
    showTab('dashboard');
  });
</script>

<?php require_once views_path("partials/footer"); ?>
