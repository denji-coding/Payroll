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
                  data-id="${record.employee_no || record.employee_id || 'N/A'}"
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
                  data-total-deductions="${record.total_deductions}"
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
  <div class="modal-dialog modal-dialog-scrollable"> <!-- Removed modal-xl -->
    <div class="modal-content bg-[#f8fbf8] border">
      <div class="modal-header border-b">
        <h5 class="modal-title text-lg font-semibold" id="payslip-title">Payslip Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-6">
        <div id="payslip-content">
          <!-- Real data will be injected here -->
          <p>Loading payslip data...</p>
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
</div>

<style>
  /* Your modal custom size */
  #payslipModal .modal-dialog {
    max-width: 600px; /* narrow width */
    max-height: 90vh; /* tall but limited height */
  }
  #payslipModal .modal-content {
    max-height: 90vh;
    overflow-y: auto; /* scroll if content overflows */
  }
  
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const payslipModalEl = document.getElementById('payslipModal');

  payslipModalEl.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const payslipContent = document.getElementById('payslip-content');

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

    
    const totalDeductions = (parseFloat(button.getAttribute('data-total-deductions') || 0).toFixed(2));

    // Create the new modal content with the same design as user_mypayslip.view.php
    payslipContent.innerHTML = `
      <div class="space-y-6">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="text-2xl font-bold">Migrants Venture Corporation</span>
            <p class="text-[#478547]">Lapu-Lapu St. Tagum City, Davao Del Norte</p>
          </div>
          <div class="text-end">
            <span class="font-bold">PAYSLIP</span>
          </div>
        </div>

        <hr class="my-3 bg-border" style="height:1px;">

        <div class="row">
          <div class="col-md-6">
            <span class="font-semibold mb-2">Employee Information</span>
            <div class="mb-2 text-sm"> 
              <p><strong>Name:</strong> ${name}</p>
              <p><strong>ID:</strong> ${empId}</p>
              <p><strong>Position:</strong> ${position}</p>
            </div>
          </div>
          <div class="col-md-6">
            <span class="font-semibold mb-2">Payment Details</span>
            <div class="mb-2 text-sm">
              <p><strong>Basic Salary:</strong> ₱${basicSalary} / day</p>
              <p><strong>Pay Period:</strong> ${payPeriod}</p>
              <p><strong>Total Hours:</strong> ${totalHours}</p>
              <p><strong>Absent Days:</strong> ${absentDays}</p>
              <p><strong>Leave Days:</strong> ${leaveDays}</p>
            </div>
          </div>
        </div>

        <hr class="my-3 bg-border" style="height:1px;">

        <div class="mb-4">
          <span class="font-semibold">Earnings & Deductions</span>
          <div class="row">
            <div class="col-md-6">
              <span class="text-sm font-medium mb-2">Earnings</span>
              <table class="table table-sm text-sm mb-0">
                <tbody>
                  <tr class="border-bottom">
                    <td>Gross Pay</td>
                    <td class="text-end">₱${parseFloat(grossPay).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                  </tr>
                  <tr class="font-bold">
                    <td class="text-success">Total Earnings</td>
                    <td class="text-end text-success">₱${parseFloat(grossPay).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-6">
              <span class="text-sm font-medium mb-2">Deductions</span>
              <table class="table table-sm text-sm mb-0">
                <tbody class="bg-[#f8fbf8]">
                  <tr class="border-bottom text-danger">
                    <td>SSS</td>                        
                    <td class="text-end">₱${parseFloat(sss).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                  </tr>
                  <tr class="border-bottom text-danger">
                    <td>PhilHealth</td>                        
                    <td class="text-end">₱${parseFloat(philhealth).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                  </tr>
                  <tr class="border-bottom text-danger">
                    <td>Pag-Ibig</td>                        
                    <td class="text-end">₱${parseFloat(pagibig).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                  </tr>
                  <tr class="border-bottom font-bold ">
                    <td class="text-danger">Total Deductions</td>                        
                    <td class="text-end text-danger">₱${parseFloat(totalDeductions).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <hr class="bg-success my-3" style="height:1px;">

          <div class="bg-[#f2f8f2] rounded-lg p-4 d-flex justify-content-between align-items-center">
            <div>
              <span class="fw-semibold">Net Pay</span>
              <p class="text-success small mb-0">Total earnings minus total deductions</p>
            </div>
            <div class="text-end fw-bold fs-4">₱${parseFloat(netPay).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
          </div>
        </div>
      </div>
    `;

    window.downloadPayslip = () => window.open(link, '_blank');
    window.printPayslip = () => {
      const printWindow = window.open('', '_blank');
      printWindow.document.write(`<iframe src="${link}" frameborder="0" style="width:100%;height:100vh;"></iframe>`);
    };
  });

  // Helper functions for formatting
  function capitalizeFirstLetter(str) {
    if (!str) return '';
    // Handle multiple words - capitalize first letter of each word
    return str.toLowerCase().replace(/\b\w/g, (char) => char.toUpperCase());
  }

  function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>"']/g, function(m) {
      return ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      })[m];
    });
  }
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
    const content = document.getElementById("payslip-content").innerHTML;

    const printWindow = window.open('', '_blank');

    printWindow.document.open();
    printWindow.document.write(`
      <html>
        <head>
          <title>Payslip</title>
          <!-- Add Bootstrap CSS if you use it -->
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
          <style>
            body {
              font-family: Arial, sans-serif;
              padding: 40px;
              color: #333;
            }
            .text-success { color: #28a745; }
            .text-danger { color: #dc3545; }
            .border-bottom { border-bottom: 1px solid #ccc; }
            .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .table td { padding: 8px; border-bottom: 1px solid #ddd; }
            .text-end { text-align: right; }
            .fw-bold { font-weight: bold; }
            .fw-semibold { font-weight: 600; }
            .rounded-lg { border-radius: 0.5rem; }
            .p-4 { padding: 1.5rem; }
            .bg-light-green { background-color: #f2f8f2; }
            .text-sm { font-size: 0.875rem; }
            .text-lg { font-size: 1.125rem; }
            .fs-4 { font-size: 1.5rem; }
          </style>
        </head>
        <body>
          ${content}
        </body>
      </html>
    `);

    printWindow.document.close();

    // Wait until content is loaded, then print and close
    printWindow.onload = function() {
      printWindow.focus(); 
    printWindow.print();
      printWindow.close();
    };
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
