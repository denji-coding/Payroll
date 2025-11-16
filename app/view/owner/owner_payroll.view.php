<?php
$title = "Payroll Manager";
require_once views_path("partials/header");
require_once views_path("owner/owner_sidebar");

// Add SweetAlert2 for better user experience
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';
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
        <span class="text-2xl font-bold tracking-tight text-[#133913]">Payroll HR</span>
        <p class="text-[#478547]">Select HR and calculate net pay</p>
      </div>
    </header>

<!-- Payroll Period & Summary as Two Columns -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

<!-- Payroll Period Container -->
<section class="bg-white shadow-sm rounded-lg border-2 border-green-200 p-6 space-y-6">
  <!-- Header -->
  <div class="pb-4 border-b border-gray-200 sm:text-left">
    <span class="text-lg font-semibold text-green-700 uppercase tracking-wide">Payroll Period</span>
    <p class="text-sm text-gray-500">Set the range, type, and notes for this payroll run</p>
  </div>

  <!-- Date Inputs + Decorations -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
      <label for="start_date" class="block text-sm font-medium text-green-700 mb-1 ml-2">Start Date <span class="text-red-500">*</span></label>
      <div class="relative">
        <input type="date" id="start_date" name="start_date"
          class="w-full p-2 pl-8 border rounded focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] text-gray-700 text-sm transition">
        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-green-600"></div>
      </div>
    </div>

    <div>
      <label for="end_date" class="block text-sm font-medium text-green-700 mb-1 ml-2">End Date <span class="text-red-500">*</span></label>
      <div class="relative">
        <input type="date" id="end_date" name="end_date"
          class="w-full p-2 pl-8 border rounded focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] text-gray-700 text-sm transition">
        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-green-600"></div>
      </div>
    </div>

    <div class="sm:col-span-2">
      <p class="text-sm font-medium text-green-700 mb-1 ml-2">Payroll Duration</p>
      <p id="durationDisplay" class="text-sm text-gray-800 px-3 py-2 rounded-lg border border-gray-200">Select a start and end date</p>
    </div>

    <div class="sm:col-span-2">
      <label for="payroll_type" class="block text-sm font-medium ml-2 text-green-700 mb-1">Payroll Frequency <span class="text-red-500">*</span></label>
      <select id="payroll_type" name="payroll_type"
        class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full text-gray-700 transition">
        <option value="">Select payroll frequency</option>
        <option value="monthly">Monthly</option>
      </select>
    </div>
  </div>
</section>

<!-- Payroll Summary Container -->
<section class="bg-white shadow-sm rounded-lg border-2 border-green-200 p-6 space-y-6">
  <div class="pb-4 border-b border-gray-200">
    <span class="text-lg font-semibold text-green-700 uppercase tracking-wide">Payroll Summary</span>
    <p class="text-sm text-gray-500">Overview of selected employees and estimated gross</p>
  </div>

  <!-- Grid: 2 on top, 1 below -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-center">
    <!-- Total Employees -->
    <div class="flex flex-col items-center justify-center p-3 border-1 border-green-200 rounded-md">
      <span class="text-sm font-medium text-green-700 mb-1">Total Employees</span>
      <p id="totalEmployees" class="text-2xl font-bold text-gray-800">0</p>
    </div>

    <!-- Selected for Processing -->
    <div class="flex flex-col items-center justify-center p-3 border-1 border-green-200 rounded-md">
      <span class="text-sm font-medium text-green-700 mb-1">Selected for Processing</span>
      <p id="selectedEmployees" class="text-2xl font-bold text-gray-800">0</p>
    </div>

    <!-- Estimated Gross Pay (spans full width) -->
    <div class="sm:col-span-2 flex flex-col items-center justify-center p-3 border-1 border-green-200 rounded-md">
      <span class="text-sm font-medium text-green-700 mb-1">Estimated Gross Payroll</span>
      <p id="estimatedGross" class="text-2xl font-bold text-gray-800">₱0.00</p>
    </div>
  </div>

  <div class="pt-2">
    <button
      id="processPayrollBtn"
      onclick="processPayroll()"
      class="w-full px-4 py-2 btn btn-success font-semibold rounded-lg shadow-sm transition"
      disabled>
      Process Payroll
    </button>
  </div>
</section>

</div>

<!-- Payroll Table Card -->
<section class="bg-white shadow-sm rounded-lg border-2 border-green-200 overflow-x-auto p-4">
  <!-- Search Bar -->
  <div class="flex justify-between items-center mb-4">
    <span class="text-lg font-semibold text-[#478547]">Select HR for Payroll</span>

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
        placeholder="Search HR..."
        onkeyup="filterTable(); toggleClearButton();"
      >
      <button id="clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden" onclick="clearInput()">×</button>
    </div>
  </div>

  <table class="w-full caption-bottom text-sm">
    <thead class="[&_tr]:border-b bg-[#f2f8f2] sticky top-0 z-10">
      <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">
          <input type="checkbox" id="selectAll" disabled class="employeeCheckbox h-5 w-5 rounded-md appearance-none border-2 border-gray-500 checked:bg-[#478547] checked:border-[#478547] checked:text-white focus:ring-2 focus:ring-[#478547] flex items-center justify-center">
        </th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Employee ID</th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Name</th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Total Hours</th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Present</th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Absent</th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Leave</th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Benefit Deduction</th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Leave Deduction</th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Gross Pay</th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Net Pay</th>
        <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Action</th>
      </tr>
    </thead>

    <tbody class="[&_tr:last-child]:border-0" id="payrollTable">
      <tr id="initialRow" class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd] fade-in-slide">
        <td colspan="12" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
          <i class="bi bi-calculator fs-5 me-2"></i>Fetch Employee first.
        </td>
      </tr>
    </tbody>
  </table>
</section>

  </div>
</main>

<script>
let payrollData = null; // Store payroll data globally

document.addEventListener('DOMContentLoaded', () => {
  const startDate = document.getElementById('start_date');
  const endDate = document.getElementById('end_date');
  const payrollType = document.getElementById('payroll_type');
  const table = document.getElementById('payrollTable');
  const processBtn = document.getElementById('processPayrollBtn');
  const selectAll = document.getElementById('selectAll');

  function updateDuration() {
    const durationDisplay = document.getElementById('durationDisplay');
    if (!durationDisplay) return;

    if (startDate.value && endDate.value) {
      const start = new Date(startDate.value);
      const end = new Date(endDate.value);
      const diff = (end - start) / (1000 * 60 * 60 * 24) + 1;
      durationDisplay.textContent = diff > 0 ? `${diff} day(s)` : 'Invalid date range';
    } else {
      durationDisplay.textContent = 'Select a start and end date';
    }
  }

  function updateSelectedSummary() {
    const checkboxes = document.querySelectorAll('.employeeCheckbox:not(#selectAll)');
    const checkedBoxes = document.querySelectorAll('.employeeCheckbox:checked:not(#selectAll)');
    const selectedEmpEl = document.getElementById("selectedEmployees");
    const estimatedGrossEl = document.getElementById("estimatedGross");

    let estimatedGross = 0;
    let hasNewlyCalculated = false;
    let selectedCount = 0;

    checkedBoxes.forEach(cb => {
      if (cb.getAttribute('data-processed') === '0') {
        const row = cb.closest('tr');
        const gross = parseFloat(row.querySelector('.gross')?.textContent.replace(/,/g, '') || 0);
        estimatedGross += gross;
        selectedCount++;
        hasNewlyCalculated = true;
      }
    });

    if (selectedEmpEl) selectedEmpEl.textContent = selectedCount;
    if (estimatedGrossEl) {
      estimatedGrossEl.textContent = "₱" + estimatedGross.toLocaleString(undefined, { minimumFractionDigits: 2 });
    }

    if (processBtn) {
      processBtn.disabled = !hasNewlyCalculated;
    }

    if (selectAll) {
      const eligible = [...checkboxes].filter(cb => cb.getAttribute('data-processed') === '0');
      const allChecked = eligible.length > 0 && eligible.every(cb => cb.checked);
      selectAll.checked = allChecked;
    }
  }

  function inputsAreReady() {
    return startDate.value && endDate.value && payrollType.value;
  }

  function fetchPayrollData() {
    if (!inputsAreReady()) return;

    const start = startDate.value;
    const end = endDate.value;

    fetch(`../app/api/hr_payroll-api.php?start_date=${start}&end_date=${end}`)
      .then(response => response.json())
      .then(data => {
        if (!table) return console.error('Error: payrollTable not found.');
        table.innerHTML = '';

        if (data.status === 'success') {
          let html = '';

          data.data.forEach((emp, index) => {
            const isProcessed = emp.processed;
            const sss = parseFloat(emp.sss_deduction ?? 0);
            const pagibig = parseFloat(emp.pagibig_deduction ?? 0);
            const philhealth = parseFloat(emp.philhealth_deduction ?? 0);
            const leaveDeduction = parseFloat(emp.leave_deduction ?? 0);
            const gross = parseFloat(emp.gross ?? 0);
            const net = parseFloat(emp.net ?? 0);
            const totalBenefits = sss + pagibig + philhealth;
            const showValue = isProcessed ? (v) => v.toFixed(2) : () => '--';

            html += `
              <tr class="border-b hover:bg-[#f2f8f2] even:bg-[#cde4cd] fade-in-slide" data-employee="${emp.employee_no}" data-hr-id="${emp.hr_id || ''}">
                <td class="p-3 align-middle font-medium">
                  <input 
                  type="checkbox" 
                  class="employeeCheckbox h-5 w-5 rounded-md appearance-none border-2 border-gray-500 checked:bg-[#478547] checked:border-[#478547] checked:text-white focus:ring-2 focus:ring-[#478547]"
                  ${isProcessed ? 'checked disabled' : ''}
                  data-processed="${isProcessed ? '1' : '0'}">
                </td>
                <td class="p-3 align-middle font-medium">${emp.employee_no}</td>
                <td class="p-3 align-middle font-medium">${emp.full_name}</td>
                <td class="p-3 align-middle text-left font-medium">${emp.total_hours ?? 0}</td>
                <td class="p-3 align-middle text-left font-medium">${emp.present_days ?? 0}</td>
                <td class="p-3 align-middle text-left font-medium">${emp.absent_days ?? 0}</td>
                <td class="p-3 align-middle text-left font-medium">${emp.leave_days ?? 0}</td>
                <td class="p-3 align-middle text-left font-medium benefit"
                    data-sss="${sss.toFixed(2)}"
                    data-pagibig="${pagibig.toFixed(2)}"
                    data-philhealth="${philhealth.toFixed(2)}"
                    data-leave="${leaveDeduction.toFixed(2)}">
                    ${isProcessed ? totalBenefits.toFixed(2) : '--'}
                </td>
                <td class="totalDeductions p-3 align-middle text-left font-medium">${showValue(leaveDeduction)}</td>
                <td class="gross p-3 align-middle text-left font-medium">${showValue(gross)}</td>
                <td class="net font-semibold text-green-700 p-3 align-middle text-left">${showValue(net)}</td>
                <td>
                  <div class="flex flex-col gap-1 items-center">
                    ${isProcessed ? `
                      <span 
                        class="text-green-700 bg-green-100 border border-green-400 text-xs px-2 py-1 rounded font-semibold"
                        data-processed="1">
                        Processed
                      </span>
                    ` : `
                      <button 
                        class="autoCalcBtn bg-emerald-600 text-white px-2 py-1 rounded text-xs"
                        title="Auto Calculate">
                        <i class="bi bi-robot"></i>
                      </button>
                      <button 
                        type="button" 
                        class="manualCalcBtn bg-amber-500 text-white px-2 py-1 rounded text-xs"
                        data-bs-toggle="modal" 
                        data-bs-target="#calculatePayModal"
                        title="Manual Calculate">
                        <i class="bi bi-pencil-square"></i>
                      </button>
                      <button 
                        type="button"
                        class="cancelCalcBtn d-none bg-red-500 text-white px-2 py-1 rounded text-xs"
                        title="Cancel Calculation">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    `}
                  </div>
                </td>
              </tr>`;
          });

          table.innerHTML = html;
          document.getElementById("totalEmployees").textContent = data.data.length;

          payrollData = data; // Store data globally
          setupActions(data);
        } else {
          Swal.fire('Error', data.message || 'Failed to fetch payroll data', 'error');
        }
      })
      .catch(error => {
        console.error(error);
        Swal.fire('Error', 'Request failed', 'error');
      });
  }

  function setupActions(data) {
    document.querySelectorAll('.employeeCheckbox:not(#selectAll)').forEach(cb => {
      cb.style.pointerEvents = 'none';
    });

    document.querySelectorAll('.autoCalcBtn').forEach(btn => {
      btn.addEventListener('click', () => {
        const row = btn.closest('tr');
        const employeeNo = row.dataset.employee;
        const emp = data.data.find(e => e.employee_no === employeeNo);

        if (!emp) {
          return Swal.fire('Error', 'Employee data not found.', 'error');
        }

        const gross = Number(emp.base_salary) * (Number(emp.total_hours) / 8);
        const sss = gross * (data.benefit_rates?.sss || 0) / 100;
        const pagibig = gross * (data.benefit_rates?.pagibig || 0) / 100;
        const philhealth = gross * (data.benefit_rates?.philhealth || 0) / 100;
        const benefit = sss + pagibig + philhealth;

        const leave = Number(emp.leave_days);
        const leaveDeduction = (Number(emp.base_salary) / (data.day_count || 22)) * leave;

        const totalDeductions = benefit + leaveDeduction;
        const net = gross - totalDeductions;

        Swal.fire({
          icon: 'success',
          title: 'Calculated!',
          text: 'Payroll has been automatically calculated.',
          showConfirmButton: false,
          timer: 1200,
          willClose: () => {
            row.querySelector('.gross').textContent = gross.toFixed(2);
            row.querySelector('.benefit').textContent = benefit.toFixed(2);
            row.querySelector('.benefit').dataset.sss = sss.toFixed(2);
            row.querySelector('.benefit').dataset.pagibig = pagibig.toFixed(2);
            row.querySelector('.benefit').dataset.philhealth = philhealth.toFixed(2);
            row.querySelector('.totalDeductions').textContent = leaveDeduction.toFixed(2);
            row.querySelector('.net').textContent = net.toFixed(2);

            const cb = row.querySelector('.employeeCheckbox');
            cb.checked = true;
            cb.disabled = true;
            cb.setAttribute('data-processed', '0');

            row.querySelector('.autoCalcBtn').classList.add('d-none');
            row.querySelector('.manualCalcBtn').classList.add('d-none');
            row.querySelector('.cancelCalcBtn').classList.remove('d-none');

            updateSelectedSummary();
          }
        });
      });
    });

    let calculatedManual = null;

    document.querySelectorAll('.manualCalcBtn').forEach((btn, index) => {
      btn.addEventListener('click', () => {
        const emp = data.data[index];

        document.getElementById('employeeIdHidden').value = emp.employee_no;
        document.getElementById('summaryFullname').textContent = emp.full_name || '';
        document.getElementById('summaryPresent').textContent = emp.present_days ?? 0;
        document.getElementById('summaryTotalHours').textContent = emp.total_hours ?? 0;
        document.getElementById('summaryAbsent').textContent = emp.absent_days ?? 0;
        document.getElementById('summaryLeave').textContent = emp.leave_days ?? 0;
        document.getElementById('baseSalaryHidden').textContent = emp.base_salary ?? 0;

        document.getElementById('sssDeduct').value = '';
        document.getElementById('pagibigDeduct').value = '';
        document.getElementById('philhealthDeduct').value = '';
        document.getElementById('grossPay').value = '';
        document.getElementById('netPay').value = '';
        document.getElementById('savePayBtn').disabled = true;

        calculatedManual = null;
        document.getElementById('calculatePayForm').dataset.rowIndex = index;
      });
    });

    document.querySelectorAll('.cancelCalcBtn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const row = btn.closest('tr');

        row.querySelector('.gross').textContent = '--';
        row.querySelector('.net').textContent = '--';
        row.querySelector('.benefit').textContent = '--';
        row.querySelector('.benefit').dataset.sss = '0.00';
        row.querySelector('.benefit').dataset.pagibig = '0.00';
        row.querySelector('.benefit').dataset.philhealth = '0.00';
        row.querySelector('.totalDeductions').textContent = '--';

        row.querySelector('.autoCalcBtn').classList.remove('d-none');
        row.querySelector('.manualCalcBtn').classList.remove('d-none');
        row.querySelector('.cancelCalcBtn').classList.add('d-none');

        const cb = row.querySelector('.employeeCheckbox');
        cb.checked = false;
        cb.disabled = true;
        cb.removeAttribute('data-processed');

        updateSelectedSummary();
      });
    });

    document.getElementById('calculatePayBtn').addEventListener('click', () => {
      const totalHours = parseFloat(document.getElementById('daysWorked').value);
      const leaveDays = parseFloat(document.getElementById('leaveDays').value) || 0;
      const index = document.getElementById('calculatePayForm').dataset.rowIndex;
      const emp = data.data[index];
      const baseSalary = parseFloat(emp.base_salary ?? 0);
      const dayCount = data.day_count || 22;

      if (isNaN(totalHours) || totalHours < 0 || isNaN(baseSalary) || baseSalary <= 0) {
        return Swal.fire('Invalid Input', 'Please enter a valid Total Hours and ensure Base Salary is available.', 'error');
      }

      const gross = baseSalary * (totalHours / 8);
      const sss = gross * (data.benefit_rates?.sss || 0) / 100;
      const pagibig = gross * (data.benefit_rates?.pagibig || 0) / 100;
      const philhealth = gross * (data.benefit_rates?.philhealth || 0) / 100;
      const benefit = sss + pagibig + philhealth;
      const leaveRate = baseSalary / dayCount;
      const leaveDeduct = leaveDays * leaveRate;
      const totalDeductions = benefit + leaveDeduct;
      const net = gross - totalDeductions;

      document.getElementById('grossPay').value = gross.toFixed(2);
      document.getElementById('netPay').value = net.toFixed(2);
      document.getElementById('sssDeduct').value = sss.toFixed(2);
      document.getElementById('pagibigDeduct').value = pagibig.toFixed(2);
      document.getElementById('philhealthDeduct').value = philhealth.toFixed(2);

      calculatedManual = { gross, net, sss, pagibig, philhealth, leaveDeduct, totalDeductions };
      document.getElementById('savePayBtn').disabled = false;

      Swal.fire({
        icon: 'success',
        title: 'Calculated',
        text: 'Manual payroll successfully calculated.',
        timer: 1000,
        showConfirmButton: false
      });
    });

    document.getElementById('savePayBtn').addEventListener('click', (e) => {
      e.preventDefault();

      if (!calculatedManual) {
        return Swal.fire('Error', 'Please calculate first before saving.', 'warning');
      }

      const index = document.getElementById('calculatePayForm').dataset.rowIndex;
      const row = document.querySelectorAll('#payrollTable tr')[index];
      const { gross, net, sss, pagibig, philhealth, leaveDeduct } = calculatedManual;

      row.querySelector('.gross').textContent = gross.toFixed(2);
      row.querySelector('.net').textContent = net.toFixed(2);
      row.querySelector('.benefit').textContent = (sss + pagibig + philhealth).toFixed(2);
      row.querySelector('.benefit').dataset.sss = sss.toFixed(2);
      row.querySelector('.benefit').dataset.pagibig = pagibig.toFixed(2);
      row.querySelector('.benefit').dataset.philhealth = philhealth.toFixed(2);
      row.querySelector('.totalDeductions').textContent = leaveDeduct.toFixed(2);

      const cb = row.querySelector('.employeeCheckbox');
      cb.checked = true;
      cb.disabled = true;
      cb.setAttribute('data-processed', '0');

      row.querySelector('.autoCalcBtn').classList.add('d-none');
      row.querySelector('.manualCalcBtn').classList.add('d-none');
      row.querySelector('.cancelCalcBtn').classList.remove('d-none');

      updateSelectedSummary();
      bootstrap.Modal.getInstance(document.getElementById('calculatePayModal')).hide();

      Swal.fire({
        icon: 'success',
        title: 'Saved!',
        text: 'Manual payroll saved successfully.',
        timer: 1200,
        showConfirmButton: false
      });
    });
  }

  function handleInputChange() {
    updateDuration();
    if (inputsAreReady()) fetchPayrollData();
  }

  startDate.addEventListener('input', handleInputChange);
  endDate.addEventListener('input', handleInputChange);
  payrollType.addEventListener('change', handleInputChange);
  updateDuration();
});

function processPayroll() {
  const rows = document.querySelectorAll('#payrollTable tr');
  const payload = [];

  rows.forEach(row => {
    const checkbox = row.querySelector('.employeeCheckbox');
    if (checkbox && checkbox.checked && checkbox.getAttribute('data-processed') !== '1') {
      const benefitCell = row.querySelector('.benefit');
      const employeeNo = row.children[1].textContent.trim();
      const rowData = payrollData?.data?.find(e => e.employee_no === employeeNo);
      
      // Get deduction values from table cells
      const sssDeduction = parseFloat(benefitCell.dataset.sss) || 0;
      const pagibigDeduction = parseFloat(benefitCell.dataset.pagibig) || 0;
      const philhealthDeduction = parseFloat(benefitCell.dataset.philhealth) || 0;
      const leaveDeduction = parseFloat(row.querySelector('.totalDeductions')?.textContent.trim().replace('--', '0')) || 0;
      
      // Calculate total deductions: SSS + Pag-IBIG + PhilHealth + Leave + Late
      // Late deduction defaults to 0 for now
      const lateDeduction = 0;
      const totalDeductions = sssDeduction + pagibigDeduction + philhealthDeduction + leaveDeduction + lateDeduction;
      
      payload.push({
        employee_no: employeeNo,
        hr_id: rowData?.hr_id || null, // Include hr_id if available
        total_hours: parseFloat(row.children[3].textContent.trim()) || 0,
        present_days: parseInt(row.children[4].textContent.trim()) || 0,
        absent_days: parseInt(row.children[5].textContent.trim()) || 0,
        leave_days: parseInt(row.children[6].textContent.trim()) || 0,
        sss_deduction: sssDeduction,
        pagibig_deduction: pagibigDeduction,
        philhealth_deduction: philhealthDeduction,
        late_deduction: lateDeduction,
        total_deductions: totalDeductions,
        gross: parseFloat(row.querySelector('.gross')?.textContent.trim()) || 0,
        net: parseFloat(row.querySelector('.net')?.textContent.trim()) || 0
      });
    }
  });

  if (payload.length === 0) {
    return Swal.fire(
      'No Payroll Selected',
      'Please calculate and select at least one payroll before submitting.',
      'warning'
    );
  }

  Swal.fire({
    title: 'Processing payroll...',
    html: 'Saving records...',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  fetch('../app/api/process_payroll-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      start_date: document.getElementById('start_date').value,
      end_date: document.getElementById('end_date').value,
      payroll_type: document.getElementById('payroll_type').value,
      payrolls: payload
    })
  })
    .then(res => res.json())
    .then(res => {
      Swal.close();

      if (res.status === 'success') {
        Swal.fire({
          title: 'Success',
          text: 'Payroll processed and emailed successfully!',
          icon: 'success',
          timer: 1000,
          showConfirmButton: false
        }).then(() => {
          const processedNos = res.processed_employees || [];

          processedNos.forEach(empNo => {
            const row = Array.from(document.querySelectorAll('#payrollTable tr'))
              .find(r => r.children[1]?.textContent.trim() === empNo);

            if (row) {
              const checkbox = row.querySelector('.employeeCheckbox');
              checkbox.disabled = true;
              checkbox.setAttribute('data-processed', '1');
              checkbox.checked = true;

              const actionTd = row.querySelector('td:last-child');
              if (actionTd) {
                actionTd.innerHTML = `
                  <div class="flex flex-col gap-1 items-center">
                    <span class="text-green-700 bg-green-100 border border-green-400 text-xs px-2 py-1 rounded font-semibold">
                      Processed
                    </span>
                  </div>
                `;
              }
            }
          });

          const allProcessed = Array.from(document.querySelectorAll('.employeeCheckbox'))
            .every(cb => cb.disabled || cb.getAttribute('data-processed') === '1');

          if (allProcessed) {
            document.getElementById('processPayrollBtn').disabled = true;
          }
        });
      } else {
        Swal.fire('Error', res.message || 'Something went wrong.', 'error');
      }
    })
    .catch(err => {
      console.error(err);
      Swal.close();
      Swal.fire('Error', 'Failed to submit payroll.', 'error');
    });
}

function filterTable() {
  const input = document.getElementById("searchInput");
  const filter = input.value.toLowerCase();
  const table = document.getElementById("payrollTable");
  const rows = table.getElementsByTagName("tr");

  for (let i = 0; i < rows.length; i++) {
    const nameCell = rows[i].getElementsByTagName("td")[2];
    const idCell = rows[i].getElementsByTagName("td")[1];
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

function calculatePayroll() {
  const totalHours = parseFloat(document.getElementById("daysWorked").value);
  const leaveDays = parseFloat(document.getElementById("leaveDays").value);
  const baseSalary = parseFloat(document.getElementById("baseSalaryHidden").textContent);
  const sssRate = parseFloat(document.getElementById("sssRateHidden").value || 0);
  const pagibigRate = parseFloat(document.getElementById("pagibigRateHidden").value || 0);
  const philhealthRate = parseFloat(document.getElementById("philhealthRateHidden").value || 0);
  const dayCount = parseFloat(document.getElementById("dayCountHidden").value || 22);

  if (isNaN(totalHours) || isNaN(baseSalary)) {
    return Swal.fire('Invalid Input', 'Please enter valid total hours and base salary.', 'warning');
  }

  const gross = baseSalary * (totalHours / 8);
  const sss = gross * (sssRate / 100);
  const pagibig = gross * (pagibigRate / 100);
  const philhealth = gross * (philhealthRate / 100);

  const leaveDeduction = (baseSalary / dayCount) * leaveDays;
  const totalDeduction = sss + pagibig + philhealth + leaveDeduction;
  const net = gross - totalDeduction;

  document.getElementById("sssDeduct").value = sss.toFixed(2);
  document.getElementById("pagibigDeduct").value = pagibig.toFixed(2);
  document.getElementById("philhealthDeduct").value = philhealth.toFixed(2);
  document.getElementById("grossPay").value = gross.toFixed(2);
  document.getElementById("netPay").value = net.toFixed(2);

  Swal.fire({
    icon: 'success',
    title: 'Calculated!',
    html: `
      <div class="text-sm leading-relaxed">
        Gross Pay: ₱${gross.toFixed(2)}<br>
        SSS: ₱${sss.toFixed(2)}<br>
        Pag-IBIG: ₱${pagibig.toFixed(2)}<br>
        PhilHealth: ₱${philhealth.toFixed(2)}<br>
        Leave Deduction: ₱${leaveDeduction.toFixed(2)}<br>
        <strong>Total Deductions: ₱${totalDeduction.toFixed(2)}</strong><br>
        <strong>Net Pay: ₱${net.toFixed(2)}</strong>
      </div>
    `,
    timer: 2500,
    showConfirmButton: false
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('calculatePayModal');

  if (modal) {
    modal.addEventListener('hidden.bs.modal', () => {
      const form = document.getElementById('calculatePayForm');
      if (form) form.reset();

      ['grossPay', 'netPay', 'sssDeduct', 'pagibigDeduct', 'philhealthDeduct'].forEach(id => {
        const input = document.getElementById(id);
        if (input) input.value = '';
      });

      const summaryFullname = document.getElementById('summaryFullname');
      if (summaryFullname) summaryFullname.textContent = '';
      const summaryPresent = document.getElementById('summaryPresent');
      if (summaryPresent) summaryPresent.textContent = '0';
      const summaryTotalHours = document.getElementById('summaryTotalHours');
      if (summaryTotalHours) summaryTotalHours.textContent = '0';
      const summaryAbsent = document.getElementById('summaryAbsent');
      if (summaryAbsent) summaryAbsent.textContent = '0';
      const summaryLeave = document.getElementById('summaryLeave');
      if (summaryLeave) summaryLeave.textContent = '0';

      const savePayBtn = document.getElementById('savePayBtn');
      if (savePayBtn) savePayBtn.disabled = true;
    });
  }
});
</script>

<!-- Manual Calculation Modal -->
<div class="modal fade" id="calculatePayModal" tabindex="-1" aria-labelledby="calculatePayModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content mt-5">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="calculatePayModalLabel">Manual Calculation</h5>
        <button type="button" class="btn-close" style="filter: invert(1);" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="calculatePayForm" method="post">
          <input type="hidden" id="employeeIdHidden" name="employeeId" />
          <input type="hidden" id="baseSalaryHidden">
          <input type="hidden" id="sssRateHidden">
          <input type="hidden" id="pagibigRateHidden">
          <input type="hidden" id="philhealthRateHidden">
          <input type="hidden" id="dayCountHidden">

          <div class="card mb-4 shadow-sm">
            <div class="card-body">
              <h6 class="card-title">Employee Summary</h6>
              <div class="row mb-2">
                <div class="col-md-12">
                  <strong>Full Name:</strong> <span id="summaryFullname">Juan Dela Cruz</span>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <strong>Present:</strong> <span id="summaryPresent">0</span> days
                </div>
                <div class="col-md-3">
                  <strong>Total Hours:</strong> <span id="summaryTotalHours">0</span> hrs
                </div>
                <div class="col-md-3">
                  <strong>Absent:</strong> <span id="summaryAbsent">0</span> days
                </div>
                <div class="col-md-3">
                  <strong>Leave:</strong> <span id="summaryLeave">0</span> day(s)
                </div>
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-4">
              <label for="daysWorked" class="form-label">Total Hours <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="daysWorked" name="daysWorked" required />
            </div>
            <div class="col-sm-4">
              <label for="hoursPerDay" class="form-label">Absent <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="hoursPerDay" name="hoursPerDay" required />
            </div>
            <div class="col-sm-4">
              <label for="leaveDays" class="form-label">Leave <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="leaveDays" name="leaveDays" value="" required />
            </div>
          </div>

          <div class="d-flex justify-content-center align-items-center mb-4">
            <button type="button" class="btn btn-success w-50" id="calculatePayBtn" onclick="calculatePayroll()">Calculate</button>
          </div>

          <div class="row mb-3">
            <div class="col-sm-4">
              <label for="sssDeduct" class="form-label">SSS Deduction</label>
              <input type="text" class="form-control" id="sssDeduct" name="sssDeduct" readonly />
            </div>
            <div class="col-sm-4">
              <label for="pagibigDeduct" class="form-label">Pag-IBIG Deduction</label>
              <input type="text" class="form-control" id="pagibigDeduct" name="pagibigDeduct" readonly />
            </div>
            <div class="col-sm-4">
              <label for="philhealthDeduct" class="form-label">PhilHealth Deduction</label>
              <input type="text" class="form-control" id="philhealthDeduct" name="philhealthDeduct" readonly />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-6">
              <label for="grossPay" class="form-label">Gross Pay</label>
              <input type="text" class="form-control" id="grossPay" name="grossPay" readonly />
            </div>
            <div class="col-sm-6">
              <label for="netPay" class="form-label">Net Pay</label>
              <input type="text" class="form-control" id="netPay" name="netPay" readonly />
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-success w-25" id="savePayBtn" form="calculatePayForm">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php require_once views_path("partials/footer"); ?>