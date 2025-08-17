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
                    data-aos-delay="0"
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
// Global functions for payslip operations
// Global variable to store current payslip link
let currentPayslipLink = '';

window.downloadPayslip = () => {
  console.log('Download function called with payroll ID:', currentPayslipLink);
  console.log('Current location origin:', window.location.origin);
  
  if (!currentPayslipLink) {
    Swal.fire({
      icon: 'error',
      title: 'Download Failed',
      text: 'Could not find payslip file.',
    });
    return;
  }

  // Show loading message
  Swal.fire({
    title: 'Preparing Download...',
    text: 'Please wait while we prepare your download.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  // Use the new admin-specific API endpoint
  const downloadUrl = `${window.location.origin}/mvcPayroll/public/index.php?payroll=download_payslip_admin&payroll_id=${currentPayslipLink}&download=download&token=${Date.now()}`;
  
  // Open in new window/tab to avoid session issues
  const downloadWindow = window.open(downloadUrl, '_blank');
  
  if (downloadWindow) {
    // Close loading message
    Swal.close();
    console.log('Download window opened successfully');
  } else {
    // If popup blocked, show error
    Swal.fire({
      icon: 'error',
      title: 'Download Failed',
      text: 'Popup blocked. Please allow popups for this site and try again.',
    });
  }
};

function printPayslip(payrollId) {
    // Show loading message
    Swal.fire({
        title: 'Preparing for Print...',
        text: 'Please wait while we load the payslip PDF.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // First, check if the PDF exists by calling the payslips API
    fetch(`../app/api/payslips-api.php`)
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(result => {
        if (result.status !== 'success' || !result.data) {
            throw new Error('Failed to load payslips data');
        }

        // Find the specific payslip by payroll ID
        const payslip = result.data.find(item => item.payroll_id == payrollId);
        
        if (!payslip) {
            console.log('Available payslips:', result.data);
            console.log('Looking for payroll ID:', payrollId);
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Print Failed',
                text: `Payslip not found for ID: ${payrollId}`
            });
            return;
        }

        // Check if PDF file path exists
        if (!payslip.ps_pdf_file_path) {
            Swal.close();
            Swal.fire({
                icon: 'warning',
                title: 'PDF Not Available',
                text: 'This payslip PDF has not been generated yet. Please contact your administrator.'
            });
            return;
        }

        console.log('Found payslip:', payslip);
        console.log('PDF path:', payslip.ps_pdf_file_path);

        // Close loading dialog
        Swal.close();

        // Direct print using hidden iframe - no visible window
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = `../public/index.php?payroll=download_payslip_admin&payroll_id=${payrollId}&download=view`;
        document.body.appendChild(iframe);
        
        // Wait for iframe to load, then print immediately
        iframe.onload = function() {
            setTimeout(() => {
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    
                    // Keep iframe permanently - don't remove it
                    // This ensures the print dialog stays open
                } catch (error) {
                    console.error('Direct print error:', error);
                    // Fallback: try to open in new window but keep it open
                    fallbackDirectPrint();
                }
            }, 1000); // Reduced delay for faster print
        };
        
        // Fallback method for direct printing
        function fallbackDirectPrint() {
            if (document.body.contains(iframe)) {
                document.body.removeChild(iframe);
            }
            
            const printWindow = window.open(`../public/index.php?payroll=download_payslip_admin&payroll_id=${payrollId}&download=view`, '_blank');
            if (printWindow) {
                printWindow.onload = function() {
                    setTimeout(() => {
                        try {
                            printWindow.focus();
                            printWindow.print();
                            
                            // DON'T close the window - let user control it
                            // The window will stay open until user manually closes it
                        } catch (error) {
                            console.error('Fallback print error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Print Failed',
                                text: 'Unable to open print dialog directly.'
                            });
                        }
                    }, 2000);
                };
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Print Failed',
                    text: 'Unable to open PDF for printing. Please check your popup blocker settings.'
                });
            }
        }
        
        // Show success message
        Swal.fire({
            icon: 'info',
            title: 'Preparing Print...',
            text: 'Opening print dialog directly...',
            timer: 2000,
            showConfirmButton: false
        });

    })
    .catch(err => {
        console.error('Error loading payslip data for print:', err);
    Swal.close();
    Swal.fire({
      icon: 'error',
      title: 'Print Failed',
            text: 'Failed to load payslip data for printing. Please try again.'
    });
  });
}

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
                   data-late-deduction="0"
                   data-leave-deduction="0"
                   data-total-deductions="${record.total_deductions}"
                  data-payroll-id="${record.payroll_id}"
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
        <button type="button" class="btn btn-success" id="print-btn" onclick="printPayslip(currentPayslipLink)">
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

    // Get data attributes and store the payroll ID globally
    const name = button.getAttribute('data-employee') || '—';
    currentPayslipLink = button.getAttribute('data-payroll-id') || '';
    console.log('Modal opened with payroll ID:', currentPayslipLink);
    console.log('Button data attributes:', button.attributes);
    console.log('data-payroll-id value:', button.getAttribute('data-payroll-id'));
    const empId = button.getAttribute('data-id') || '—';
    const position = button.getAttribute('data-position') || '—';
    const basicSalary = parseFloat(button.getAttribute('data-salary') || 0).toFixed(2);
    const payPeriod = button.getAttribute('data-period') || '—';
    const totalHours = button.getAttribute('data-totalhours') || '0';
    const absentDays = parseInt(button.getAttribute('data-absent') || '0');
    const leaveDays = parseInt(button.getAttribute('data-leave') || '0');
    const grossPay = parseFloat(button.getAttribute('data-gross') || 0).toFixed(2);
    const netPay = parseFloat(button.getAttribute('data-net') || 0).toFixed(2);
    const sss = parseFloat(button.getAttribute('data-sss') || 0).toFixed(2);
    const philhealth = parseFloat(button.getAttribute('data-philhealth') || 0).toFixed(2);
    const pagibig = parseFloat(button.getAttribute('data-pagibig') || 0).toFixed(2);
    const lateDeduction = parseFloat(button.getAttribute('data-late-deduction') || 0).toFixed(2);
    const leaveDeduction = parseFloat(button.getAttribute('data-leave-deduction') || 0).toFixed(2);
    const link = button.getAttribute('data-link') || '#';
    const totalDeductions = parseFloat(button.getAttribute('data-total-deductions') || 0).toFixed(2);

    // Calculate effective absent days (absent days minus leave days)
    const effectiveAbsentDays = Math.max(0, Math.floor(absentDays - leaveDays));

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
              <p><strong>Absent Days:</strong> ${effectiveAbsentDays}</p>
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
                                     <tr class="border-bottom text-danger">
                     <td>Late Deduction</td>                        
                     <td class="text-end">₱${parseFloat(lateDeduction).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                   </tr>
                   <tr class="border-bottom text-danger">
                     <td>Leave Deduction</td>                        
                     <td class="text-end">₱${parseFloat(leaveDeduction).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
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
});
</script>





<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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


</script>



<?php require_once views_path("partials/footer"); ?>







