<?php
$title = "My Payslips";
require_once views_path("partials/header");

// Add Bootstrap Icons CSS
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">';

echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';


$isMobile = '<script>document.write(window.innerWidth < 768 ? "true" : "false");</script>';
?>

<div class="flex min-h-screen overflow-hidden <?= $isMobile ? 'bg-gray-100' : '' ?>">    

    <main id="mainContent" class="flex-1 p-6 bg-gray-100 transition-all duration-300 ease-in-out ">

        <?php require_once views_path("partials/user_sidebar"); ?>

        <div class="mt-6">
            <span class="text-2xl font-bold tracking-tight">My Payslips</span>
            <p class="text-gray-600">A detailed summary of your salary, deductions, and net pay for the selected period.</p>
        </div>

        <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-b border-gray-200">
  <span class="text-lg font-semibold text-gray-800">Payslip Records</span>

  <div class="relative w-full sm:w-auto">
    <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"></circle>
      <path d="m21 21-4.3-4.3"></path>
    </svg>
    <input
      type="text"
      id="payslipSearch"
      placeholder="Search payslips..."
      class="flex h-10 w-full placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
    >
    <button
      id="clearSearch"
      class="absolute right-2 top-1/2 transform mt-[10px] -translate-y-1/2 text-sm text-gray-400 hover:text-gray-600 hidden"
      aria-label="Clear search"
      type="button"
    >
      &#x2715;
    </button>
  </div>
</div>

                

            <div class="w-full overflow-x-auto">
  <table id="payslipTable" class="w-full table-auto text-sm sm:text-base text-left border-collapse">
    <thead class="bg-emerald-600 text-white">
      <tr>
        <th class="px-4 sm:px-6 py-3 font-semibold tracking-wide">Pay Period</th>
        <!-- Table Header -->
        <th class="px-4 sm:px-6 py-2 text-center align-middle w-[120px]">Actions</th>


      </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-100">
      <?php foreach ($payslips as $payslip): ?>
        <tr class="hover:bg-emerald-50 transition-colors duration-200">
          <td class="px-4 sm:px-6 py-3 text-gray-800">
            <?= htmlspecialchars(date("M d, Y", strtotime($payslip['pay_period_start']))) ?> -
            <?= htmlspecialchars(date("M d, Y", strtotime($payslip['pay_period_end']))) ?>
          </td>
          <td class="px-4 sm:px-6 py-2 align-middle">
  <div class="flex justify-end items-center gap-2">
    <!-- View Button -->
    <!-- View Button -->
<a href="#"
  title="View"
  class="view-payslip-btn group flex items-center justify-center w-8 h-8 rounded
         hover:bg-emerald-600 active:bg-emerald-600 hover:text-white
         transition duration-100 transform hover:scale-105 active:scale-95"
  data-bs-toggle="modal"
  data-bs-target="#payslipModal"
  data-payroll-id="<?= $payslip['id'] ?>"
  data-pay-period-start="<?= htmlspecialchars(date("M d, Y", strtotime($payslip['pay_period_start']))) ?>"
  data-pay-period-end="<?= htmlspecialchars(date("M d, Y", strtotime($payslip['pay_period_end']))) ?>"
  data-gross-pay="<?= number_format($payslip['gross_pay'], 2) ?>"
            data-deductions="<?= number_format($payslip['total_deductions'], 2) ?>"
  data-net-pay="<?= number_format($payslip['net_pay'], 2) ?>"
>
  <i class="bi bi-eye text-lg"></i>
</a>

<!-- Download Button -->
<button
  title="Download"
  class="download-payslip-btn group flex items-center justify-center w-8 h-8 rounded 
         hover:bg-emerald-600 active:bg-emerald-600 hover:text-white
         transition duration-100 transform hover:scale-105 active:scale-95"
  data-payroll-id="<?= $payslip['id'] ?>"
>
  <i class="bi bi-download text-lg"></i>
</button>

<!-- Print Button -->
<button
  title="Print"
  class="print-payslip-btn group flex items-center justify-center w-8 h-8 rounded
         hover:bg-emerald-600 active:bg-emerald-600 hover:text-white
         transition duration-100 transform hover:scale-105 active:scale-95"
  onclick="printPayslip(<?= $payslip['id'] ?>)"
>
  <i class="bi bi-printer text-lg"></i>
</button>

  </div>
</td>


        </tr>
      <?php endforeach; ?>
      <?php if (empty($payslips)): ?>
        <tr>
          <td colspan="2" class="text-center px-4 py-4 text-gray-500">No payslips found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

        </div>
    </main>
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
</style>

<!-- Modal markup -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-labelledby="payslip-title" aria-hidden="true">
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
      
    </div>
  </div>
</div>




<script>
  const payslipModal = document.getElementById('payslipModal');
  const payslipContent = document.getElementById('payslip-content');

  payslipModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const payrollId = button.getAttribute('data-payroll-id');

    payslipContent.innerHTML = '<p>Loading payslip data...</p>';

    fetch(`index.php?payroll=user_mypayslip&id=${payrollId}`, {
      credentials: 'same-origin'
    })
      .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
      })
      .then(data => {
        if (data.error) {
          payslipContent.innerHTML = `<p class="text-danger">${escapeHtml(data.error)}</p>`;
          return;
        }

        const p = data.payroll;
        if (!p) {
          payslipContent.innerHTML = '<p class="text-danger">No payroll data found.</p>';
          return;
        }

        const formatDate = (dateStr) => {
          if (!dateStr) return '';
          const d = new Date(dateStr);
          if (isNaN(d)) return '';
          return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        };

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
                  <p><strong>Name:</strong> 
                    ${capitalizeFirstLetter(escapeHtml(p.first_name || ''))} 
                    ${p.middle_name ? capitalizeFirstLetter(escapeHtml(p.middle_name.charAt(0))) + '.' : ''} 
                    ${capitalizeFirstLetter(escapeHtml(p.last_name || ''))}
                  </p>

                  <p><strong>ID:</strong> ${escapeHtml(p.employee_no || '')}</p>
                  <p><strong>Position:</strong> ${escapeHtml(p.position || '')}</p>
                </div>
              </div>
              <div class="col-md-6">
                <span class="font-semibold mb-2">Payment Details</span>
                <div class="mb-2 text-sm">
                  <p><strong>Basic Salary:</strong> ₱${parseFloat(p.base_salary || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} / day</p>
                  <p><strong>Pay Period:</strong> ${escapeHtml(formatDate(p.pay_period_start || ''))} - ${escapeHtml(formatDate(p.pay_period_end || ''))}</p>
                  <p><strong>Total Hours:</strong> ${escapeHtml(p.total_hours || '0')} hours</p>
                  <p><strong>Absent Days:</strong> ${Math.max(0, Math.floor(parseInt(p.absent_days || '0') - parseInt(p.leave_days || '0')))} days</p>
                  <p><strong>Leave Days:</strong> ${escapeHtml(p.leave_days || '0')} days</p>
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
                        <td class="text-end">₱${parseFloat(p.gross_pay || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                      <tr class="fw-bold text-success">
                        <td class="text-success">Total Earnings</td>
                        <td class="text-end text-success">₱${parseFloat(p.gross_pay || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="col-md-6">
                  <span class="text-sm font-medium mb-2">Deductions</span>
                  <table class="table table-sm text-sm mb-0">
                    <tbody>
                      <tr class="border-bottom  text-danger">
                        <td>SSS</td>                        
                        <td class="text-end">₱${parseFloat(p.sss_deduction || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                      <tr class="border-bottom  text-danger">
                        <td>PhilHealth</td>                        
                        <td class="text-end">₱${parseFloat(p.philhealth_deduction || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                      <tr class="border-bottom  text-danger">
                        <td>Pag-Ibig</td>                        
                        <td class="text-end">₱${parseFloat(p.pagibig_deduction || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                      <tr class="border-bottom  text-danger">
                        <td>Late Deduction</td>                        
                        <td class="text-end">₱${parseFloat(p.late_deduction || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                      <tr class="border-bottom  text-danger">
                        <td>Leave Deduction</td>                        
                        <td class="text-end">₱${parseFloat(p.leave_deduction || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                      <tr class="border-bottom font-bold text-danger">
                        <td class="text-danger">Total Deductions</td>                        
                        <td class="text-end text-danger">₱${parseFloat(p.total_deductions || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
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
                <div class="text-end fw-bold fs-4">₱${parseFloat(p.net_pay || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
              </div>
            </div>
          </div>
        `;
      })
      .catch(err => {
        console.error('Error loading payslip data:', err);
        payslipContent.innerHTML = '<p class="text-danger">Failed to load payslip details.</p>';
      });
  });

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

  // First, check if the PDF exists
  fetch(`index.php?payroll=user_mypayslip&id=${payrollId}`, {
    credentials: 'same-origin'
  })
    .then(response => {
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      return response.json();
    })
    .then(data => {
      if (data.error) {
        Swal.close();
        Swal.fire({
          icon: 'error',
          title: 'Print Failed',
          text: 'Failed to load payslip data: ' + data.error
        });
        return;
      }

      const p = data.payroll;
      if (!p) {
        Swal.close();
        Swal.fire({
          icon: 'error',
          title: 'Print Failed',
          text: 'No payslip data found.'
        });
        return;
      }

      // Check if PDF file path exists
      if (!p.ps_pdf_file_path) {
        Swal.close();
        Swal.fire({
          icon: 'warning',
          title: 'PDF Not Available',
          text: 'This payslip PDF has not been generated yet. Please contact your administrator.'
        });
        return;
      }

      // Close loading dialog
      Swal.close();

      // Direct print using hidden iframe - no visible window
      const iframe = document.createElement('iframe');
      iframe.style.display = 'none';
              iframe.src = `../public/index.php?payroll=download_payslip_user&payroll_id=${payrollId}&download=view`;
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
        
        const printWindow = window.open(`../public/index.php?payroll=download_payslip_user&payroll_id=${payrollId}&download=view`, '_blank');
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
      
      // Show loading message
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
        text: 'Failed to load payslip data for printing.'
      });
    });
}



  function capitalizeFirstLetter(str) {
    if (!str) return '';
    // Convert to string and handle multiple words - capitalize first letter of each word
    const strStr = String(str);
    return strStr.toLowerCase().replace(/\b\w/g, (char) => char.toUpperCase());
  }

  function escapeHtml(text) {
    if (!text) return '';
    // Convert to string to ensure .replace() method is available
    const textStr = String(text);
    return textStr.replace(/[&<>"']/g, function(m) {
      return ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      })[m];
    });
  }


  document.querySelectorAll('.download-payslip-btn').forEach(button => {
    button.addEventListener('click', () => {
      const payrollId = button.getAttribute('data-payroll-id');

     fetch(`index.php?payroll=user_mypayslip&id=${payrollId}`, {
      credentials: 'same-origin'
    })
        .then(response => {
          if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
          return response.json();
        })
        .then(data => {
          if (data.error) {
            console.log('Fetched data:', data);
            Swal.fire({
              icon: 'error',
              title: 'Download Failed',
              text: 'Failed to load payslip for download: ' + data.error
            });
            return;
          }

          const p = data.payroll;
          if (!p) {
            Swal.fire({
              icon: 'error',
              title: 'Download Failed',
              text: 'No payslip data found for download.'
            });
            return;
          }





          Swal.fire({
              title: 'Generating Payslip...',
              text: 'Please wait while we prepare your PDF.',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

          // Check if PDF file path exists
          if (!p.ps_pdf_file_path) {
            Swal.close();
            Swal.fire({
              icon: 'warning',
              title: 'PDF Not Available',
              text: 'This payslip PDF has not been generated yet. Please contact your administrator.'
            });
            return;
          }

          // Close loading dialog
          Swal.close();

          // Create download link and trigger download
          const downloadLink = document.createElement('a');
          downloadLink.href = `../public/index.php?payroll=download_payslip_user&payroll_id=${payrollId}&download=download`;
          downloadLink.download = 'payslip.pdf';
          downloadLink.style.display = 'none';
          document.body.appendChild(downloadLink);
          

          
          // Add error handling for download
          downloadLink.onerror = function() {
            Swal.fire({
              icon: 'error',
              title: 'Download Failed',
              text: 'Failed to download the payslip PDF. Please try again.'
            });
          };
          
          // Add load event to check if download started successfully
          downloadLink.onload = function() {
            console.log('Download started successfully');
          };
          
          // Use a timeout to check if download actually started
          setTimeout(() => {
            try {
              downloadLink.click();
            } catch (error) {
              console.error('Download error:', error);
              Swal.fire({
                icon: 'error',
                title: 'Download Failed',
                text: 'Failed to initiate download. Please try again.'
              });
            }
          }, 100);
          
          document.body.removeChild(downloadLink);

          // Show success message
          Swal.fire({
            icon: 'success',
            title: 'Download Started',
            text: 'Your payslip PDF download has started.',
            timer: 2000,
            showConfirmButton: false
          });
          

        })
        .catch(err => {
          console.error('Error fetching payslip data:', err);
          Swal.fire({
            icon: 'error',
            title: 'Download Failed',
            text: 'Failed to load payslip data for download.'
          });
        });
    });
  });
</script>





<script>
const searchInput = document.getElementById('payslipSearch');
const clearBtn = document.getElementById('clearSearch');
const table = document.getElementById('payslipTable');
const tbody = table.tBodies[0];

// Debounce function
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Function to perform search
function performSearch() {
  const searchTerm = searchInput.value.toLowerCase().trim();
  
  // Show or hide the clear button based on input value
  clearBtn.style.display = searchTerm ? 'block' : 'none';

  const rows = Array.from(tbody.rows);
  let visibleCount = 0;

  // Remove existing "no results" row if it exists
  const existingNoResults = tbody.querySelector('.no-results-row');
  if (existingNoResults) {
    existingNoResults.remove();
  }

  // Filter rows
  rows.forEach(row => {
    if (row.classList.contains('no-results-row')) return; // Skip no results row
    
    const rowText = row.textContent.toLowerCase();
    const isVisible = rowText.indexOf(searchTerm) > -1;
    
    if (isVisible) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });

  // Add "no results" message if no matches found
  if (searchTerm && visibleCount === 0) {
    const noResultsRow = document.createElement('tr');
    noResultsRow.className = 'no-results-row';
    noResultsRow.innerHTML = `
      <td colspan="2" class="text-center px-4 py-8 text-gray-500">
        <div class="flex flex-col items-center space-y-2">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <span class="font-medium">No payslips found</span>
          <span class="text-sm">Try adjusting your search terms</span>
        </div>
      </td>
    `;
    tbody.appendChild(noResultsRow);
  }
}

// Debounced search function (300ms delay)
const debouncedSearch = debounce(performSearch, 300);

// Event listeners
searchInput.addEventListener('input', debouncedSearch);

clearBtn.addEventListener('click', () => {
  searchInput.value = '';
  clearBtn.style.display = 'none';
  performSearch(); // Perform search immediately when clearing
});

// Initial search to set up the table
performSearch();
</script>