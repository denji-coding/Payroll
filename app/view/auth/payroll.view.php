<?php
$title = "Payroll";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");

// Add SweetAlert2 for better user experience
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';
?>

<style>
  input[type="checkbox"].employeeCheckbox:checked::before {
    content: "✓";
    display: block;
    text-align: center;
    color: white;
    font-weight: bold;
    font-size: 0.8rem;
    line-height: 1.1rem;
  }
</style>


<main class="ml-64 mt-12 p-6 bg-gray-50 min-h-screen">
  <div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Title -->
    <header class="mb-6">
      <div>
        <span class="text-2xl font-bold tracking-tight text-[#133913]">Payroll</span>
          <p class="text-[#478547]">Select employees and calculate net pay</p>
      </div>
    </header>

<!-- Payroll Period & Summary as Two Columns -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  
<!-- Payroll Period Container -->
<section class="bg-white shadow-sm rounded-lg border-2 border-green-200 p-6 space-y-6"
  data-aos="fade-in" 
  data-aos-delay="<?= $index * 1 ?>"
  data-aos-duration="300">
  <!-- Header -->
  <div class="pb-4 border-b border-gray-200 sm:text-left">
    <span class="text-lg font-semibold text-green-700 uppercase tracking-wide">Payroll Period</span>
    <p class="text-sm text-gray-500">Set the range, type, and notes for this payroll run</p>
  </div>
  
  <!-- Date Inputs + Decorations -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <!-- Start Date -->
    <div>
      <label for="start_date" class="block text-sm font-medium text-green-700 mb-1 ml-2">Start Date</label>
      <div class="relative">
        <input type="date" id="start_date" name="start_date"
          class="w-full p-2 pl-8 border rounded focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] text-gray-700 text-sm transition">
        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-green-600">
        </div>
      </div>
    </div>

    <!-- End Date -->
    <div>
      <label for="end_date" class="block text-sm font-medium text-green-700 mb-1 ml-2">End Date</label>
      <div class="relative">
        <input type="date" id="end_date" name="end_date"
          class="w-full p-2 pl-8 border rounded focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] text-gray-700 text-sm transition">
        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-green-600">
        </div>
      </div>
    </div>

    <!-- Payroll Frequency -->
    <div class="sm:col-span-2">
      <label for="payroll_type" class="block text-sm font-medium ml-2 text-green-700 mb-1">Payroll Frequency</label>
      <select id="payroll_type" name="payroll_type"
        class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full text-gray-700 transition">
        <!-- <option value="bi-weekly">Bi-Weekly</option> -->
         <option value="">Select payroll frequency</option>
        <option value="monthly">Monthly</option>
        <!-- <option value="weekly">Weekly</option> -->
        <!-- <option value="custom">Custom</option> -->
      </select>
    </div>

    <!-- Duration Display -->
    <div class="sm:col-span-2">
      <p class="text-sm font-medium text-green-700 mb-1 ml-2">Payroll Duration</p>
      <p id="durationDisplay" class="text-md text-gray-800 px-4 py-2 rounded-lg border border-gray-200">Select a start and end date</p>
    </div>
  </div>
</section>



<!-- Payroll Summary Container -->
<section class="bg-white shadow-sm rounded-lg border-2 border-green-200 p-6 space-y-6"
    data-aos="fade-in" 
    data-aos-delay="<?= $index * 1 ?>"
    data-aos-duration="500">
  <div class="pb-4 border-b border-gray-200 ">
    <span class="text-lg font-semibold text-green-700 uppercase tracking-wide">Payroll Summary</span>
    <p class="text-sm text-gray-500">Overview of selected employees and estimated gross</p>
  </div>

  <!-- Grid: 2 on top, 1 below -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-center">
    <!-- Total Employees -->
    <div class="flex flex-col items-center justify-center p-3 border-1 border-green-200 rounded-md">
      <span class="text-sm font-medium text-green-700 mb-1">Total Employees</span>
      <p id="totalEmployees" class="text-2xl font-bold text-gray-800">0</p>
      <!-- <p class="text-xs text-gray-500 mt-1">Registered in system</p> -->
    </div>

    <!-- Selected for Processing -->
    <div class="flex flex-col items-center justify-center p-3 border-1 border-green-200 rounded-md">
      <span class="text-sm font-medium text-green-700 mb-1">Selected for Processing</span>
      <p id="selectedEmployees" class="text-2xl font-bold text-gray-800">0</p>
      <!-- <p class="text-xs text-gray-500 mt-1">Employees selected below</p> -->
    </div>

    <!-- Estimated Gross Pay (spans full width) -->
    <div class="sm:col-span-2 flex flex-col items-center justify-center p-3 border-1 border-green-200 rounded-md">
      <span class="text-sm font-medium text-green-700 mb-1">Estimated Gross Payroll</span>
      <p id="estimatedGross" class="text-2xl font-bold text-gray-800">₱0.00</p>
      <!-- <p class="text-xs text-gray-500 mt-1">Based on selected employees’ semi-monthly salaries</p> -->
    </div>
  </div>

  <div class="pt-2">
    <button
      id="processPayrollBtn"
      class="w-full px-4 py-2 btn btn-success font-semibold rounded-lg shadow-sm transition "
      disabled
      >
      Process Payroll
    </button>
  </div>
</section>

</div>


    <!-- Payroll Table Card -->
    <section class="bg-white shadow-sm rounded-lg border-2 border-green-200 overflow-x-auto p-4"
        data-aos="fade-in" 
        data-aos-delay="<?= $index * 1 ?>"
        data-aos-duration="600">
  <!-- Search Bar -->
  <div class="flex justify-between items-center mb-4">
  <span class="text-lg font-semibold text-[#478547]">Select Employees for Payroll</span>

  <!-- Search Bar (Right Side) -->
  <div class="relative w-64">
    <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"></circle>
      <path d="m21 21-4.3-4.3"></path>
    </svg>
    <input
      type="text"
      id="searchInput"
      class="flex h-10 w-full placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
      placeholder="Search employee..."
      onkeyup="filterTable(); toggleClearButton();"
    >
    <button id="clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden" onclick="clearInput()">×</button>
  </div>
</div>


  <table class="w-full caption-bottom text-sm">
  <thead class="[&_tr]:border-b bg-[#f2f8f2] sticky top-0 z-10">
    <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">
        <input type="checkbox" id="selectAll" class="employeeCheckbox h-5 w-5 rounded-md appearance-none border-2 border-gray-500 checked:bg-[#478547] checked:border-[#478547] checked:text-white focus:ring-2 focus:ring-[#478547] flex items-center justify-center">
      </th>
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Employee ID</th>
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Name</th>
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Basic Pay</th>
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Total Hours</th>
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Present</th>
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Late (Mins)</th>
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Absent</th>
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Total Deduction</th>
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Gross Pay</th>
      <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Net Pay</th>
    </tr>
  </thead>

  <tbody class="[&_tr:last-child]:border-0" id="payrollTable">
    <!-- Employee data will be loaded via JavaScript -->
  </tbody>
</table>


</section>



<script>
  function filterTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("payrollTable");
    const rows = table.getElementsByTagName("tr");

    for (let i = 0; i < rows.length; i++) {
      const nameCell = rows[i].getElementsByTagName("td")[2]; // Name column index
      const idCell = rows[i].getElementsByTagName("td")[1];   // Employee ID column index
      if (nameCell || idCell) {
        const nameText = nameCell.textContent || nameCell.innerText;
        const idText = idCell.textContent || idCell.innerText;
        if (nameText.toLowerCase().indexOf(filter) > -1 || idText.toLowerCase().indexOf(filter) > -1) {
          rows[i].style.display = "";
        } else {
          rows[i].style.display = "none";
        }
      }
    }
  }

  function toggleClearButton() {
    const input = document.getElementById("searchInput");
    const clearButton = document.getElementById("clearButton");
    if (input.value.length > 0) {
      clearButton.classList.remove("hidden");
    } else {
      clearButton.classList.add("hidden");
    }
  }

  function clearInput() {
    const input = document.getElementById("searchInput");
    input.value = "";
    filterTable();
    toggleClearButton();
    input.focus();
  }
</script>

  </div>
</main>

<script>
// Function to format employee names with proper Title Case
function formatEmployeeName(firstName, middleName, lastName) {
  const formatName = (name) => {
    if (!name) return '';
    return name.toLowerCase().replace(/\b\w/g, (char) => char.toUpperCase());
  };
  
  const formattedFirst = formatName(firstName);
  const middleInitial = middleName ? formatName(middleName.charAt(0)) + '.' : '';
  const formattedLast = formatName(lastName);
  
  return [formattedFirst, middleInitial, formattedLast].filter(Boolean).join(' ');
}

// Load employee data from API
function loadEmployeeData() {
  const tbody = document.getElementById('payrollTable');
  tbody.innerHTML = '<tr><td colspan="11" class="text-center p-4">Loading employees...</td></tr>';

  fetch('../app/api/employees-api.php')
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success' && data.data.length > 0) {
        tbody.innerHTML = '';
        data.data.forEach((emp, index) => {
          const row = `
            <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
              <td class="p-3 align-middle font-medium">
                <input type="checkbox" class="employeeCheckbox h-5 w-5 rounded-md appearance-none border-2 border-gray-500 checked:bg-[#478547] checked:border-[#478547] checked:text-white focus:ring-2 focus:ring-[#478547] flex items-center justify-center">
              </td>
              <td class="p-3 align-middle font-medium">${emp.employee_no || 'N/A'}</td>
              <td class="p-3 align-middle font-medium">${formatEmployeeName(emp.first_name, emp.middle_name, emp.last_name)}</td>
              <td class="p-3 align-middle text-left font-medium">${parseFloat(emp.base_salary || 0).toFixed(2)}</td>
              <td class="p-3 align-middle text-left font-medium">0</td>
              <td class="h-12 px-3 align-middle font-medium">0</td>
              <td class="h-12 px-3 align-middle font-medium">0</td>
              <td class="h-12 px-3 align-middle font-medium">0</td>
              <td class="p-3 align-middle text-left font-medium deductionCell">0.00</td>
              <td class="p-3 align-middle text-left font-medium grossCell">0.00</td>
              <td class="p-3 align-middle text-left font-semibold netPayCell">0.00</td>
            </tr>
          `;
          tbody.insertAdjacentHTML('beforeend', row);
        });

        // Re-attach event listeners after loading data
        document.querySelectorAll(".employeeCheckbox").forEach(checkbox => {
          checkbox.addEventListener("change", updatePayrollSummary);
        });

        updatePayrollSummary();
      } else {
        tbody.innerHTML = '<tr><td colspan="11" class="text-center p-4 text-gray-500">No employees found.</td></tr>';
      }
    })
    .catch(error => {
      console.error('Error loading employees:', error);
      tbody.innerHTML = '<tr><td colspan="11" class="text-center p-4 text-red-500">Error loading employees.</td></tr>';
    });
}

document.addEventListener('DOMContentLoaded', () => {
  // Load employee data first
  loadEmployeeData();
  const startDate = document.getElementById('start_date');
  const endDate = document.getElementById('end_date');
  const durationDisplay = document.getElementById('durationDisplay');
  const selectAll = document.getElementById("selectAll");
  const processPayrollBtn = document.getElementById("processPayrollBtn");

  // Update duration between start and end dates
  function updateDuration() {
    if (startDate.value && endDate.value) {
      const start = new Date(startDate.value);
      const end = new Date(endDate.value);
      const diff = (end - start) / (1000 * 60 * 60 * 24) + 1;
      if (diff > 0) {
        durationDisplay.textContent = `${diff} day(s)`;
      } else {
        durationDisplay.textContent = 'Invalid range';
      }
    } else {
      durationDisplay.textContent = 'Select a start and end date';
    }
  }

  // Update summary info and toggle process button
  function updatePayrollSummary() {
    const checkboxes = document.querySelectorAll(".employeeCheckbox");
    let selectedCount = 0;
    let estimatedGross = 0;
    let estimatedNet = 0;

    const tableRows = document.querySelectorAll("#payrollTable tr");
    let visibleEmployeesCount = 0;

    tableRows.forEach(row => {
      if (row.style.display !== "none") {
        visibleEmployeesCount++;

        const checkbox = row.querySelector(".employeeCheckbox");
        if (checkbox && checkbox.checked) {
          selectedCount++;

          const basic = parseFloat(row.children[3].innerText.replace(/,/g, '')) || 0;
          const totalHours = parseFloat(row.children[4].innerText) || 0;
          const lateMinutes = parseInt(row.children[6].innerText) || 0;
          const absentDays = parseInt(row.children[7].innerText) || 0;

          const totalDaysWorked = totalHours / 8;
          const hourlyRate = basic / 8;
          const grossPay = basic * totalDaysWorked;

          const lateDeduction = (lateMinutes / 60) * hourlyRate;
          const absenceDeduction = absentDays * basic;
          
          // Calculate individual benefit deductions (5% each for SSS, PhilHealth, Pag-IBIG)
          const sssDeduction = grossPay * 0.05;
          const philhealthDeduction = grossPay * 0.05;
          const pagibigDeduction = grossPay * 0.05;

          const totalDeduction = sssDeduction + philhealthDeduction + pagibigDeduction + absenceDeduction + lateDeduction;
          const netPay = grossPay - totalDeduction;

          estimatedGross += grossPay;
          estimatedNet += netPay;
        }
      }
    });

    const totalEmpEl = document.getElementById("totalEmployees");
    const selectedEmpEl = document.getElementById("selectedEmployees");
    const estimatedGrossEl = document.getElementById("estimatedGross");
    const estimatedNetEl = document.getElementById("estimatedNet");

    if (totalEmpEl) totalEmpEl.innerText = visibleEmployeesCount;
    if (selectedEmpEl) selectedEmpEl.innerText = selectedCount;
    if (estimatedGrossEl) estimatedGrossEl.innerText = "₱" + estimatedGross.toLocaleString(undefined, { minimumFractionDigits: 2 });
    if (estimatedNetEl) estimatedNetEl.innerText = "₱" + estimatedNet.toLocaleString(undefined, { minimumFractionDigits: 2 });

    processPayrollBtn.disabled = selectedCount === 0;
  }

  // Process payroll for selected employees
  function processPayroll() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const payrollType = document.getElementById('payroll_type').value;

    if (!startDate || !endDate || !payrollType) {
      Swal.fire({
        title: 'Missing Information',
        text: 'Please fill in start date, end date, and payroll frequency.',
        icon: 'warning'
      });
      return;
    }

    const checkboxes = document.querySelectorAll(".employeeCheckbox");
    const payrollData = [];

    checkboxes.forEach(cb => {
      if (cb !== selectAll) {
        const row = cb.closest("tr");
        const isSelected = cb.checked;

        const deductionCell = row.querySelector(".deductionCell");
        const grossCell = row.querySelector(".grossCell");
        const netPayCell = row.querySelector(".netPayCell");

        if (isSelected) {
          const empId = row.children[1].innerText.trim();
          const empName = row.children[2].innerText.trim();
          const basic = parseFloat(row.children[3].innerText.replace(/,/g, '')) || 0;
          const totalHours = parseFloat(row.children[4].innerText) || 0;
          const presentDays = parseInt(row.children[5].innerText) || 0;
          const lateMinutes = parseInt(row.children[6].innerText) || 0;
          const absentDays = parseInt(row.children[7].innerText) || 0;

          const totalDaysWorked = totalHours / 8;
          const hourlyRate = basic / 8;
          const grossPay = basic * totalDaysWorked;

          const lateDeduction = (lateMinutes / 60) * hourlyRate;
          const absenceDeduction = absentDays * basic;

          // Calculate individual benefit deductions (5% each for SSS, PhilHealth, Pag-IBIG)
          const sssDeduction = grossPay * 0.05;
          const philhealthDeduction = grossPay * 0.05;
          const pagibigDeduction = grossPay * 0.05;

          const totalDeduction = sssDeduction + philhealthDeduction + pagibigDeduction + absenceDeduction + lateDeduction;
          const netPay = grossPay - totalDeduction;



          // Update table display
          if (deductionCell) deductionCell.textContent = totalDeduction.toLocaleString(undefined, { minimumFractionDigits: 2 });
          if (grossCell) grossCell.textContent = grossPay.toLocaleString(undefined, { minimumFractionDigits: 2 });
          if (netPayCell) netPayCell.textContent = netPay.toLocaleString(undefined, { minimumFractionDigits: 2 });

          // Add to payroll data for API
          const payrollItem = {
            employee_no: empId,
            total_hours: totalHours,
            present_days: presentDays,
            absent_days: absentDays,
            leave_days: 0, // You may want to add leave calculation
            sss_deduction: sssDeduction,
            pagibig_deduction: pagibigDeduction,
            philhealth_deduction: philhealthDeduction,
            total_deductions: totalDeduction,
            gross: grossPay,
            net: netPay
          };

          payrollData.push(payrollItem);
        } else {
          if (deductionCell) deductionCell.textContent = "0.00";
          if (grossCell) grossCell.textContent = "0.00";
          if (netPayCell) netPayCell.textContent = "0.00";
        }
      }
    });

    if (payrollData.length === 0) {
      Swal.fire({
        title: 'No Employees Selected',
        text: 'Please select at least one employee to process payroll.',
        icon: 'warning'
      });
      return;
    }

    // Show processing indicator
    Swal.fire({
      title: 'Processing Payroll...',
      text: 'Please wait while we save the payroll data.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Send data to API
    const requestData = {
      start_date: startDate,
      end_date: endDate,
      payroll_type: payrollType,
      payrolls: payrollData
    };

    console.log('Sending to API:', requestData);

    fetch('../app/api/process_payroll-api.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
      Swal.close();
      
      if (data.status === 'success') {
        const processedEmployees = payrollData.map(p => 
          `${p.employee_no}: <b>₱${p.net.toLocaleString(undefined, { minimumFractionDigits: 2 })}</b>`
        );

        Swal.fire({
          title: 'Payroll Processed Successfully!',
          html: processedEmployees.join("<br>"),
          icon: 'success',
          confirmButtonText: 'OK'
        });

        // Disable processed checkboxes
        payrollData.forEach(p => {
          const rows = document.querySelectorAll("#payrollTable tr");
          rows.forEach(row => {
            if (row.children[1]?.innerText.trim() === p.employee_no) {
              const checkbox = row.querySelector(".employeeCheckbox");
              if (checkbox) {
                checkbox.disabled = true;
                checkbox.style.opacity = '0.5';
              }
            }
          });
        });

      } else {
        Swal.fire({
          title: 'Error Processing Payroll',
          text: data.message || 'An error occurred while processing payroll.',
          icon: 'error'
        });
      }
    })
    .catch(error => {
      Swal.close();
      console.error('Error:', error);
      Swal.fire({
        title: 'Network Error',
        text: 'Failed to connect to the server. Please try again.',
        icon: 'error'
      });
    });

    updatePayrollSummary();
  }

  // Event listeners
  if (startDate && endDate && durationDisplay) {
    startDate.addEventListener('change', updateDuration);
    endDate.addEventListener('change', updateDuration);
  }

  document.querySelectorAll(".employeeCheckbox").forEach(checkbox => {
    checkbox.addEventListener("change", updatePayrollSummary);
  });

  if (selectAll) {
    selectAll.addEventListener("change", function () {
      document.querySelectorAll(".employeeCheckbox").forEach(cb => {
        if (!cb.disabled) {
          cb.checked = this.checked;
        }
      });
      updatePayrollSummary();
    });
  }

  if (processPayrollBtn) {
    processPayrollBtn.addEventListener("click", processPayroll);
  }

  // Make processPayroll available globally
  window.processPayroll = processPayroll;

  // Initial setup
  updateDuration();
  updatePayrollSummary();
});
</script>




<?php require_once views_path("partials/footer"); ?>
