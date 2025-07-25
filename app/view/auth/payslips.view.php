<?php
$title = "Payslips";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");
?>

<main class="font-sans flex-1 bg-[#f8fbf8] overflow-auto mt-12 p-4 md:p-6 ml-[255px]">
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <span class="text-2xl font-bold tracking-tight">Payslips</span>
      <p class="text-[#478547]">View and manage employee payslips</p>
    </div>

    <!-- Card -->
    <div class="rounded-lg border-2 border-green-200 bg-card text-card-foreground shadow-sm bg-white"
    data-aos="fade-in" 
                    data-aos-delay="<?= $index * 1 ?>"
                    data-aos-duration="500">
      <!-- Card Header -->
      <div class="space-y-1.5 p-6 flex flex-row items-center justify-between">
        <span class="text-xl md:text-2xl font-semibold text-[#133913]">All Employee Payslips</span>

        <!-- Search Bar -->
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
                oninput="toggleClearButton()"
            >
            <button id="clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden" onclick="clearInput()">×</button>
        </div>
      </div>

      <!-- Card Body -->
      <div class="p-6 pt-0 ">
        <div id="table-container" class="relative overflow-y-auto max-h-[350px] w-full max-w-full transition-all duration-300">
            <table class="min-w-full table-auto caption-bottom text-xs md:text-sm">
            <thead class="[&_tr]:border-b bg-white sticky top-0 z-10">
                <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                <th class="h-10 md:h-12 px-2 md:px-4 text-left align-middle font-bold text-[#478547]">#</th>
                <th class="h-10 md:h-12 px-2 md:px-4 text-left align-middle font-bold text-[#478547]">Employee Name</th>
                <th class="h-10 md:h-12 px-2 md:px-4 text-left align-middle font-bold text-[#478547]">Payroll Period</th>
                <th class="h-10 md:h-12 px-2 md:px-4 text-center align-middle font-bold text-[#478547]">Actions</th>
                </tr>
            </thead>
            <tbody id="payslipTableBody" class="[&_tr:last-child]:border-0">
              
    
    
</tbody>




          </table>
        </div>
      </div>
    </div>
  </div>
</main>


<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.getElementById('payslipTableBody');

  tbody.innerHTML = `<tr><td colspan="8" class="text-center text-gray-500">Loading payslips...</td></tr>`;

  fetch(`../app/api/payslips-api.php`)
    .then(response => response.json())
    .then(result => {
      if (result.status === 'success' && result.data.length > 0) {
        tbody.innerHTML = '';
        result.data.forEach((record, i) => {
          const payPeriod = record.pay_period_start && record.pay_period_end
            ? new Date(record.pay_period_start).toLocaleDateString('en-US', { month: 'short', day: '2-digit' }) + ' - ' +
              new Date(record.pay_period_end).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
            : '';

          const employeeName = record.full_name || 'N/A';

          const row = `
            <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
              <td class="p-2 md:p-4 align-middle">${i + 1}</td>
              <td class="p-2 md:p-4 align-middle">${employeeName}</td>
              <td class="p-2 md:p-4 align-middle">${payPeriod}</td>
              <td class="p-2 md:p-4 align-middle text-center">
                <button 
                  type="button"
                  class="btn btn-sm btn-outline-success view-payslip-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#payslipModal"
                  data-employee="${employeeName}"
                  data-id="${record.employee_id}"
                  data-position="${record.position}" 
                  data-salary="${record.base_salary}" 
                  data-period="${payPeriod}"
                  data-totalhours="${record.total_hours}"
                  data-absent="${record.absent_days}"
                  data-leave="${record.leave_days}"
                  data-gross="${record.gross_pay}"
                  data-net="${record.net_pay}"
                  data-sss="${record.sss_deduction}"
                  data-philhealth="${record.philhealth_deduction}"
                  data-pagibig="${record.pagibig_deduction}"
                  data-link="/mvcPayroll/public/${record.ps_pdf_file_path}"
                  title="View Payslip"
                >
                  <i class="bi bi-eye"></i>
                </button>
              </td>
            </tr>
          `;
          tbody.insertAdjacentHTML('beforeend', row);
        });
      } else {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-gray-500">No payslip records found.</td></tr>`;
      }
    })
    .catch(() => {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center text-red-500">Error loading payslips.</td></tr>`;
    });
});
</script>




<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->

<!-- Modal -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-labelledby="payslipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content p-6 border shadow-lg rounded-lg overflow-auto" style="max-height: 90vh;">

      <!-- Header -->
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title text-lg font-semibold" id="payslipModalLabel">Payslip Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body space-y-6" id="payslip-content">
        <!-- Header Info -->
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h2 class="text-2xl fw-bold" id="company-name">Migrants Venture Corporation</h2>
            <p class="text-[#478547]" id="company-address">Lapu-Lapu St. Tagum City, Davao Del Norte</p>
          </div>
          <!-- <div class="text-end">
            <h3 class="fw-bold">PAYSLIP</h3>
            <p class="text-sm" id="payslip-period">—</p>
          </div> -->
        </div>

        <hr class="my-3" />

        <!-- Employee & Payroll Details -->
        <div class="row">
          <div class="col-md-6 mb-3">
            <h5 class="fw-semibold mb-2">Employee Information</h5>
            <ul class="list-unstyled small">
              <li><strong>Name:</strong> <span id="emp-name">—</span></li>
              <li><strong>ID:</strong> <span id="emp-id">—</span></li>
              <li><strong>Position:</strong> <span id="emp-position">—</span></li>
              <li><strong>Basic Salary:</strong> ₱<span id="basic-pay">—</span> <span>/ day</span></li>
            </ul>
          </div>
          <div class="col-md-6 mb-3">
            <h5 class="fw-semibold mb-2">Payroll Details</h5>
            <ul class="list-unstyled small">
              <li><strong>Pay Period:</strong> <span id="emp-period">—</span></li>
              <li><strong>Total Hours:</strong> <span id="emp-hours">—</span></li>
              <li><strong>Absent:</strong> <span id="emp-absent">—</span></li>
              <li><strong>Leave:</strong> <span id="emp-leave">—</span></li>
            </ul>
          </div>
        </div>

        <hr class="my-3" />

        <!-- Earnings & Deductions -->
        <h5 class="fw-semibold">Earnings & Deductions</h5>
        <div class="row">
          <div class="col-md-6">
            <table class="table table-sm">
              <thead><tr><th>Earnings</th><th class="text-end"></th></tr></thead>
              <tbody>
                <tr><td>Basic Pay</td><td class="text-end">₱<span id="earn-basic-pay">0.00</span></td></tr>
                <tr class="fw-bold">
                  <td class="text-success">Total Earnings</td>
                  <td class="text-end text-success">₱<span id="total-earnings">0.00</span></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm">
              <thead><tr><th>Deductions</th><th class="text-end"></th></tr></thead>
              <tbody>
                <tr><td>SSS</td><td class="text-end">₱<span id="deduct-sss">0.00</span></td></tr>
                <tr><td>PhilHealth</td><td class="text-end">₱<span id="deduct-philhealth">0.00</span></td></tr>
                <tr><td>Pag-IBIG</td><td class="text-end">₱<span id="deduct-pagibig">0.00</span></td></tr>
                <tr class="fw-bold">
                  <td class="text-success">Total Deductions</td>
                  <td class="text-end text-success">₱<span id="total-deductions">0.00</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <hr class="my-3" />

        <!-- Net Pay Summary -->
        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
          <div>
            <h6 class="fw-semibold mb-1">Net Pay</h6>
            <p class="mb-0 text-muted small">Total earnings minus total deductions</p>
          </div>
          <div class="text-end">
            <h4 class="text-success fw-bold mb-0">₱<span id="net-pay">0.00</span></h4>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-success" id="download-btn" onclick="downloadPayslip()">
          <i class="bi bi-download me-1"></i> Download
        </button>
        <button type="button" class="btn btn-success" id="print-btn" onclick="printPayslip()">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>

    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
  const payslipModalEl = document.getElementById('payslipModal');

  payslipModalEl.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;

    // Get data attributes
    const name = button.getAttribute('data-employee') || '—';
    const empId = button.getAttribute('data-id') || '—';
    const position = button.getAttribute('data-position') || '—';
    const basicSalary = parseFloat(button.getAttribute('data-salary') || 0).toFixed(2);
    const payPeriod = button.getAttribute('data-period') || '—';
    const totalHours = button.getAttribute('data-totalhours') || '0';
    const absentDays = button.getAttribute('data-absent') || '0';
    const leaveDays = button.getAttribute('data-leave') || '0';
    const grossPay = parseFloat(button.getAttribute('data-gross') || 0).toFixed(2);
    const netPay = parseFloat(button.getAttribute('data-net') || 0).toFixed(2);
    const sss = parseFloat(button.getAttribute('data-sss') || 0).toFixed(2);
    const philhealth = parseFloat(button.getAttribute('data-philhealth') || 0).toFixed(2);
    const pagibig = parseFloat(button.getAttribute('data-pagibig') || 0).toFixed(2);
    const link = button.getAttribute('data-link') || '#';

    const totalDeductions = (parseFloat(sss) + parseFloat(philhealth) + parseFloat(pagibig)).toFixed(2);

    // Fill modal content
    document.getElementById('emp-name').textContent = name;
    document.getElementById('emp-id').textContent = empId;
    document.getElementById('emp-position').textContent = position;
    document.getElementById('basic-pay').textContent = basicSalary;
    document.getElementById('emp-period').textContent = payPeriod;
    // document.getElementById('payslip-period').textContent = payPeriod;
    document.getElementById('emp-hours').textContent = totalHours;
    document.getElementById('emp-absent').textContent = absentDays;
    document.getElementById('emp-leave').textContent = leaveDays;

    document.getElementById('earn-basic-pay').textContent = grossPay;
    document.getElementById('deduct-sss').textContent = sss;
    document.getElementById('deduct-philhealth').textContent = philhealth;
    document.getElementById('deduct-pagibig').textContent = pagibig;
    document.getElementById('total-deductions').textContent = totalDeductions;

    document.getElementById('total-earnings').textContent = grossPay;
    document.getElementById('net-pay').textContent = netPay;

    window.downloadPayslip = () => window.open(link, '_blank');
    window.printPayslip = () => {
      const printWindow = window.open('', '_blank');
      printWindow.document.write(`<iframe src="${link}" frameborder="0" style="width:100%;height:100vh;"></iframe>`);
    };
  });
});
</script>





<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
<script>
    // Function to adjust table height based on fullscreen mode
  function adjustTableHeight() {
    const tableContainer = document.getElementById('table-container');
    
    // If fullscreen mode is active, set max height to full height
    if (document.fullscreenElement || window.innerHeight === screen.height) {
      tableContainer.classList.remove('max-h-[350px]');
      tableContainer.classList.add('max-h-[60vh]');
    } else {
      // Otherwise, set the max height to 350px
      tableContainer.classList.remove('max-h-[60vh]');
      tableContainer.classList.add('max-h-[350px]');
    }
  }

  // Listen for fullscreen changes and window resize events
  document.addEventListener('fullscreenchange', adjustTableHeight);
  window.addEventListener('resize', adjustTableHeight);

  // Initial check when the page loads
  window.addEventListener('load', adjustTableHeight);

  // Function to clear the input field
  function clearInput() {
    const inputField = document.getElementById('searchInput');
    inputField.value = '';  // Clear the input field
    toggleClearButton();  // Hide the clear button
  }

  // Function to toggle the visibility of the clear button based on input content
  function toggleClearButton() {
    const inputField = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearButton');
    if (inputField.value.length > 0) {
      clearButton.classList.remove('hidden');  // Show the clear button
    } else {
      clearButton.classList.add('hidden');  // Hide the clear button
    }
  }

  // Print function
  function printPayslip() {
    var printContent = document.getElementById('payslip-content');
    var printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Payslip</title>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
  }

  function downloadPayslip(employeeId) {
  fetch(`../app/api/get_payslip-file.php?employee_id=${employeeId}`)
    .then(response => response.json())
    .then(data => {
      if (data.file) {
        const downloadUrl = `../app/api/download_payslip.php?file=${encodeURIComponent(data.file)}`;

        // Force download using a temporary <a> with download attribute
        const a = document.createElement('a');
        a.href = downloadUrl;
        a.setAttribute('download', '');
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      } else {
        alert('Payslip not found.');
      }
    })
    .catch(error => {
      console.error('Download error:', error);
      alert('Error downloading payslip.');
    });
  }

</script>



<?php require_once views_path("partials/footer"); ?>






<!-- Script to download in excel -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script> Include the xlsx library -->

<!-- /*<script>
    // Function to clear the input field
    function clearInput() {
        const inputField = document.getElementById('searchInput');
        inputField.value = '';  // Clear the input field
        toggleClearButton();  // Hide the clear button
    }

    // Function to toggle the visibility of the clear button based on input content
    function toggleClearButton() {
        const inputField = document.getElementById('searchInput');
        const clearButton = document.getElementById('clearButton');
        if (inputField.value.length > 0) {
            clearButton.classList.remove('hidden');  // Show the clear button
        } else {
            clearButton.classList.add('hidden');  // Hide the clear button
        }
    }

    // Open modal
    function openPayslip() {
        const overlay = document.getElementById("payslip-overlay");
        const dialog = document.getElementById("payslip-dialog");
        
        overlay.classList.remove("hidden");
        
        // Smooth transition for overlay
        overlay.classList.remove("opacity-0");
        overlay.classList.add("opacity-100");
        
        // Smooth transition for dialog (scale-up effect)
        dialog.classList.remove("scale-75");
        dialog.classList.add("scale-100");
    }

    // Close modal
    function closePayslip() {
        const overlay = document.getElementById("payslip-overlay");
        const dialog = document.getElementById("payslip-dialog");
        
        // Smooth transition for overlay
        overlay.classList.remove("opacity-100");
        overlay.classList.add("opacity-0");
        
        // Smooth transition for dialog (scale-down effect)
        dialog.classList.remove("scale-100");
        dialog.classList.add("scale-75");
        
        setTimeout(() => {
            overlay.classList.add("hidden");
        }, 200); // Delay to match animation duration
    }

    // Print function
    function printPayslip() {
        var printContent = document.getElementById('payslip-content');
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Payslip</title>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContent.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }

    // Download as Excel
    function downloadPayslip() {
        // Show loading toast
        Swal.fire({
            title: 'Downloading Payslip...',
            text: 'Please wait...',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Hide the buttons temporarily
        document.getElementById('payslip-buttons').style.display = 'none';

        // Grab the payslip content
        var element = document.getElementById('payslip-content');

        // Convert the content to a worksheet
        var ws = XLSX.utils.table_to_sheet(element);

        // Create a new workbook and append the worksheet
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Payslip");

        // Download the workbook as an Excel file
        XLSX.writeFile(wb, "payslip.xlsx").then(() => {
            // Close the loading toast after the file is downloaded
            Swal.close();

            // After download, show the buttons back and close the modal
            document.getElementById('payslip-buttons').style.display = 'flex';
            closePayslip();
            Swal.close();  // Close the loading toast
        });
    }
</script>*/ -->
