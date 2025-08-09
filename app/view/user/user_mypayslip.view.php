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
  onclick="printPayslip()"
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


<!-- jsPDF (para sa PDF generation) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- html2canvas (para sa DOM to image conversion) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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
                    ${capitalizeFirstLetter(escapeHtml(p.first_name))} 
                    ${p.middle_name ? capitalizeFirstLetter(escapeHtml(p.middle_name.charAt(0))) + '.' : ''} 
                    ${capitalizeFirstLetter(escapeHtml(p.last_name))}
                  </p>

                  <p><strong>ID:</strong> ${escapeHtml(p.employee_no)}</p>
                  <p><strong>Position:</strong> ${escapeHtml(p.position)}</p>
                </div>
              </div>
              <div class="col-md-6">
                <span class="font-semibold mb-2">Payment Details</span>
                <div class="mb-2 text-sm">
                  <p><strong>Basic Salary:</strong> ₱${parseFloat(p.base_salary).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} / day</p>
                  <p><strong>Pay Period:</strong> ${escapeHtml(formatDate(p.pay_period_start))} - ${escapeHtml(formatDate(p.pay_period_end))}</p>
                  
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
                        <td class="text-end">₱${parseFloat(p.gross_pay).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                      <tr class="fw-bold text-success">
                        <td>Total Earnings</td>
                        <td class="text-end">₱${parseFloat(p.gross_pay).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
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
                        <td class="text-end">₱${parseFloat(p.sss_deduction).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                      <tr class="border-bottom  text-danger">
                        <td>PhilHealth</td>                        
                        <td class="text-end">₱${parseFloat(p.philhealth_deduction).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                      <tr class="border-bottom  text-danger">
                        <td>Pag-Ibig</td>                        
                        <td class="text-end">₱${parseFloat(p.pagibig_deduction).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                      </tr>
                      <tr class="border-bottom font-bold text-danger">
                        <td>Total Deductions</td>                        
                        <td class="text-end">₱${parseFloat(p.total_deductions).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
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
                <div class="text-end fw-bold fs-4">₱${parseFloat(p.net_pay).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
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
            alert('Failed to load payslip for download.');
            return;
          }

          const p = data.payroll;
          if (!p) {
            alert('No payslip data found for download.');
            return;
          }

          const formatDate = (dateStr) => {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d)) return '';
            return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
          };

          // Build PDF content with escapeHtml & capitalizeFirstLetter for safety
          const pdfContent = document.createElement('div');
          pdfContent.style.width = '640px';
          pdfContent.style.padding = '0';
          pdfContent.style.backgroundColor = 'transparent';
          pdfContent.style.fontFamily = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
          pdfContent.style.position = 'absolute';
          pdfContent.style.left = '-9999px'; // hide off-screen
          document.body.appendChild(pdfContent);
          pdfContent.innerHTML = `
            <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px;">
              <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                  <h2 style="color:#478547; margin: 0; font-size: 18px;">Migrants Venture Corporation</h2>
                  <p style="margin: 2px 0 0 0;">123 Business Ave., Metro Manila</p>
                </div>
                <div style="text-align:right;">
                  <h3 style="margin: 0; font-size: 16px;">PAYSLIP</h3>
                  <p style="margin: 2px 0 0 0;">${escapeHtml(formatDate(p.pay_period_start))} - ${escapeHtml(formatDate(p.pay_period_end))}</p>
                </div>
              </div>

              <hr style="margin: 10px 0; border:1px solid #ccc;">

              <div style="display:flex; justify-content:space-between;">
                <div style="width: 48%;">
                  <h4 style="margin-bottom: 4px; font-size: 14px;">Employee Information</h4>
                  <p><strong>Name:</strong> ${capitalizeFirstLetter(escapeHtml(p.first_name))} ${p.middle_name ? capitalizeFirstLetter(escapeHtml(p.middle_name.charAt(0))) + '.' : ''} ${capitalizeFirstLetter(escapeHtml(p.last_name))}</p>
                  <p><strong>Employee No:</strong> ${escapeHtml(p.employee_no)}</p>
                  <p><strong>Position:</strong> ${escapeHtml(p.position)}</p>
                </div>
                <div style="width: 48%;">
                  <h4 style="margin-bottom: 4px; font-size: 14px;">Payment Details</h4>
                  <p><strong>Basic Salary:</strong> ₱${parseFloat(p.base_salary).toLocaleString(undefined, { minimumFractionDigits: 2 })} / day</p>
                  <p><strong>Pay Period:</strong> ${escapeHtml(formatDate(p.pay_period_start))} - ${escapeHtml(formatDate(p.pay_period_end))}</p>
                  <p><strong>Payment Date:</strong> ${escapeHtml(formatDate(p.generated_at))}</p>
                </div>
              </div>

              <hr style="margin: 10px 0; border:1px solid #ccc;">

              <div>
                <h4 style="font-size: 14px;">Earnings & Deductions</h4>
                <div style="display:flex; justify-content:space-between;">
                  <div style="width: 48%;">
                    <h5 style="font-size: 13px; margin-bottom: 4px;">Earnings</h5>
                    <table style="width:100%; font-size:12px;">
                      <tbody>
                        <tr>
                          <td>Gross Pay</td>
                          <td style="text-align:right;">₱${parseFloat(p.gross_pay).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                        </tr>
                        <tr style="font-weight:bold; color:green;">
                          <td>Total Earnings</td>
                          <td style="text-align:right;">₱${parseFloat(p.gross_pay).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div style="width: 48%;">
                    <h5 style="font-size: 13px; margin-bottom: 4px;">Deductions</h5>
                    <table style="width:100%; font-size:12px;">
                      <tbody>
                        <tr style="font-weight:bold; color:red;">
                          <td>Total Deductions</td>
                          <td style="text-align:right;">₱${parseFloat(p.total_deductions).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <hr style="border-top: 2px solid #478547; margin: 12px 0;">

                <div style="background-color:#f2f8f2; border-radius:8px; padding:10px 14px; display:flex; justify-content:space-between; align-items:center;">
                  <div>
                    <strong>Net Pay</strong><br>
                    <small style="color:#478547;">Total earnings minus total deductions</small>
                  </div>
                  <div style="font-weight:bold; font-size:16px;">₱${parseFloat(p.net_pay).toLocaleString(undefined, { minimumFractionDigits: 2 })}</div>
                </div>
              </div>
            </div>

          `;

          Swal.fire({
              title: 'Generating Payslip...',
              text: 'Please wait while we prepare your PDF.',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

          // Use html2canvas and jsPDF to generate PDF from DOM element
          html2canvas(pdfContent, { scale: 3 }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jspdf.jsPDF('p', 'pt', 'a4');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();

            // Fit the image into the PDF page while keeping aspect ratio
            const imgProps = {
              width: canvas.width,
              height: canvas.height,
            };
            const ratio = Math.min(pdfWidth / imgProps.width, pdfHeight / imgProps.height);
            const imgWidth = imgProps.width * ratio;
            const imgHeight = imgProps.height * ratio;

            pdf.addImage(imgData, 'PNG', (pdfWidth - imgWidth) / 2, 20, imgWidth, imgHeight);
            function formatDateForFilename(dateStr) {
              const d = new Date(dateStr);
              const mm = String(d.getMonth() + 1).padStart(2, '0'); // Months start at 0
              const dd = String(d.getDate()).padStart(2, '0');
              const yyyy = d.getFullYear();
              return `${mm}-${dd}-${yyyy}`;
            }

            const startDate = formatDateForFilename(p.pay_period_start);
            const endDate = formatDateForFilename(p.pay_period_end);
            pdf.save(`payslip-${startDate}-${endDate}.pdf`);
            Swal.close();
          }).catch(err => {
            console.error('Error generating PDF:', err);
            alert('Failed to generate payslip PDF.');
          });
        })
        .catch(err => {
          console.error('Error fetching payslip data:', err);
          alert('Failed to load payslip data for download.');
        });
    });
  });
</script>





<script>
const searchInput = document.getElementById('payslipSearch');
const clearBtn = document.getElementById('clearSearch');
const table = document.getElementById('payslipTable');

searchInput.addEventListener('input', () => {
  // Show or hide the clear button based on input value
  clearBtn.style.display = searchInput.value ? 'block' : 'none';

  const searchTerm = searchInput.value.toLowerCase();
  const rows = table.tBodies[0].rows;

  for (let row of rows) {
    const rowText = row.textContent.toLowerCase();
    row.style.display = rowText.indexOf(searchTerm) > -1 ? '' : 'none';
  }
});

clearBtn.addEventListener('click', () => {
  searchInput.value = '';
  clearBtn.style.display = 'none';

  // Trigger input event to reset filtering
  searchInput.dispatchEvent(new Event('input'));
});

</script>