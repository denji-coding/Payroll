<?php
$title = "Update Benefit Rates";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");
require_once "../app/core/database.php";

// Fetch all rates ordered by latest
$db = new Database();
$pdo = $db->getConnection();
$stmt = $pdo->prepare("SELECT * FROM benefit_rates ORDER BY updated_at DESC");
$stmt->execute();
$rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Latest rate (prefill modal)
$latest = $rates[0] ?? ['sss_rate' => '', 'pagibig_rate' => '', 'philhealth_rate' => ''];
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
.flatpickr-calendar .flatpickr-prev-month,
.flatpickr-calendar .flatpickr-next-month {
    color: white;
    border: 1px solid transparent;
    border-radius: 4px;
    width: 28px;
    height: 28px;
    display: flex;
                align-items: center;
    justify-content: center;
    transition: border-color 0.2s ease;
    margin: 3px 10px;
    box-sizing: border-box;
    background: transparent;
    padding: 0;
}

/* Style the arrow icons */
.flatpickr-calendar .flatpickr-prev-month svg,
.flatpickr-calendar .flatpickr-next-month svg {
    width: 14px;
    height: 14px;
    fill: white; /* default arrow color (light gray or as needed) */
    transition: fill 0.2s ease;
}

/* On hover, border and arrow turn white */
.flatpickr-calendar .flatpickr-prev-month:hover,
.flatpickr-calendar .flatpickr-next-month:hover {
    border-color: white;
    cursor: pointer;
}

.flatpickr-calendar .flatpickr-prev-month:hover svg,
.flatpickr-calendar .flatpickr-next-month:hover svg {
    fill: white; /* hover arrow becomes white */
}

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

<main class="flex-1 h-[calc(100vh-3rem)] p-4 md:p-6 ml-[255px] mt-12 bg-[#f8fbf8]">
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <span class="text-2xl font-bold tracking-tight text-[#133913]">Benefit Rates</span>
        <p class="text-[#478547]">View and manage SSS, Pag-IBIG, and PhilHealth contribution rates.</p>
      </div>
    </div>

    <div class="rounded-lg border-2 border-green-200 bg-white text-[#133913] shadow-sm">
      <div class="space-y-1.5 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <span class="text-2xl font-semibold text-[#133913]">Government Contributions</span>
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-fit">

          <!-- Search -->
          <!-- <div class="relative w-full sm:w-64">
            <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="11" cy="11" r="8"></circle>
              <path d="m21 21-4.3-4.3"></path>
            </svg>
            <input type="text" id="searchInput"
              class="flex h-10 w-full rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base text-[#478547] placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 md:text-sm"
              placeholder="Search benefit..." oninput="toggleClearButton()" />
            <button id="clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden" onclick="clearInput()">×</button>
          </div> -->

          <!-- Month Filter -->
          <div class="relative w-52">
            <!-- <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg> -->
            <input
                type="text"
                id="monthFilter"
                class="flatpickr-month flex h-10 w-full rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base text-[#478547] placeholder:text-[#478547] text-center ring-offset-[#f8fbf8] focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:ring-offset-2 md:text-sm"
                
                readonly
                oninput="toggleMonthClearIcon()"
            />

            <!-- Calendar icon (shown by default) -->
            <svg id="calendarIcon" class="lucide-calendar absolute right-2 top-3 h-4 w-4 text-[#478547] pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect width="18" height="18" x="3" y="4" rx="2"/>
                <path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>

            <!-- Clear (×) icon, initially hidden -->
            <button
                id="clearMonthFilter"
                class="absolute right-2 top-1 text-[#478547] text-xl hidden z-10"
                onclick="clearMonthFilter()"
                type="button"
            >×</button>
            </div>


          <!-- Button -->
          <button type="button" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition" data-bs-toggle="modal" data-bs-target="#updateRatesModal">
            <i class="bi bi-pencil-square me-1"></i> Update Benefits Rate
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="p-6 pt-0">
        <div class="relative w-full overflow-auto">
          <div class="max-h-[calc(100vh-300px)] overflow-y-auto">
            <table class="w-full caption-bottom text-sm">
              <thead class="bg-[#f2f8f2] sticky top-0 z-10">
                <tr class="border-b">
                  <th class="h-12 px-3 text-center font-bold text-[#478547] bg-white">No.</th>
                  <th class="h-12 px-3 text-center font-bold text-[#478547] bg-white">SSS Rate</th>
                  <th class="h-12 px-3 text-center font-bold text-[#478547] bg-white">Pag-IBIG Rate</th>
                  <th class="h-12 px-3 text-center font-bold text-[#478547] bg-white">PhilHealth Rate</th>
                  <th class="h-12 px-3 text-center font-bold text-[#478547] bg-white">Updated At</th>
                  <th class="h-12 px-3 text-center font-bold text-[#478547] bg-white">Status</th>
                  <th class="h-12 px-3 text-center font-bold text-[#478547] bg-white">Action</th>
                </tr>
              </thead>
              <tbody id="benefitRateTable">
                <?php foreach ($rates as $index => $rate): ?>
                  <?php $isLatest = $index === 0; ?>
                  <tr class="fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                    <td class="p-3 font-semibold text-center text-[#133913]"><?= $index + 1 ?></td>
                    <td class="p-3 text-center"><?= htmlspecialchars($rate['sss_rate']) ?>%</td>
                    <td class="p-3 text-center"><?= htmlspecialchars($rate['pagibig_rate']) ?>%</td>
                    <td class="p-3 text-center"><?= htmlspecialchars($rate['philhealth_rate']) ?>%</td>
                    <td class="p-3 text-center text-sm text-gray-500"><?= date("M d, Y h:i A", strtotime($rate['updated_at'])) ?></td>
                    <td class="p-3 text-center">
                      <?php if ($isLatest): ?>
                        <span class="inline-block px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active Rate</span>
                      <?php else: ?>
                        <span class="inline-block px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">Inactive</span>
                      <?php endif; ?>
                    </td>
                    <td class="p-3 text-center">
                      <?php if (!$isLatest): ?>
                        <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $rate['id'] ?>" title="Delete Rate">
                          <i class="bi bi-trash text-center"></i>
                        </button>
                      <?php else: ?>
                        <span class="text-gray-400">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Modal -->
<div class="modal fade" id="updateRatesModal" tabindex="-1" aria-labelledby="updateBenefitsLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-success fs-5" id="updateBenefitsLabel">
          <i class="bi bi-gear me-2"></i> Update Benefits Rate
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal">
        <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="benefitsRateForm" method="POST" action="update_rates-api.php">
        <div class="modal-body space-y-4">
          <div>
            <label for="sss_rate" class="form-label">SSS Rate (%)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="sss_rate" name="sss_rate" required value="<?= htmlspecialchars($latest['sss_rate']) ?>">
          </div>
          <div>
            <label for="pagibig_rate" class="form-label">Pag-IBIG Rate (%)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="pagibig_rate" name="pagibig_rate" required value="<?= htmlspecialchars($latest['pagibig_rate']) ?>">
          </div>
          <div>
            <label for="philhealth_rate" class="form-label">PhilHealth Rate (%)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="philhealth_rate" name="philhealth_rate" required value="<?= htmlspecialchars($latest['philhealth_rate']) ?>">
          </div>

          <!-- Buttons aligned right -->
          <div class="d-flex justify-content-end gap-2 pt-3">
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-circle me-1"></i> Save Changes
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>




<script>
const monthFilterInput = document.getElementById("monthFilter");
const calendarIcon = document.getElementById("calendarIcon");
const clearButton = document.getElementById("clearMonthFilter");
const today = new Date();

const fp = flatpickr(monthFilterInput, {
  dateFormat: "Y-m",
  altInput: true,
  altFormat: "F Y",
  defaultDate: today,
  plugins: [
    new monthSelectPlugin({
      shorthand: true,
      dateFormat: "Y-m",
      altFormat: "F Y"
    })
  ],
  onChange: function (selectedDates, dateStr) {
    filterTableByMonth(dateStr);
    toggleMonthClearIcon();
  }
});

function toggleMonthClearIcon() {
  if (monthFilterInput.value.trim() !== "" && monthFilterInput.value !== formatDate(today)) {
    calendarIcon.classList.add("hidden");
    clearButton.classList.remove("hidden");
  } else {
    calendarIcon.classList.remove("hidden");
    clearButton.classList.add("hidden");
  }
}

clearButton.addEventListener("click", () => {
  fp.setDate(today, true, "Y-m");
  filterTableByMonth(formatDate(today));
  toggleMonthClearIcon();
});

function formatDate(date) {
  return `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, "0")}`;
}

function filterTableByMonth(monthStr) {
  const tbody = document.getElementById("benefitRateTable");
  const rows = Array.from(tbody.querySelectorAll("tr:not(#noDataRow)"));
  let hasVisibleRow = false;

  rows.forEach(row => {
    const dateCell = row.querySelector("td:nth-child(5)");
    const dateText = dateCell?.textContent || "";
    let rowMonth = "";

    if (dateText) {
      const parsedDate = new Date(dateText);
      if (!isNaN(parsedDate)) {
        rowMonth = parsedDate.toISOString().slice(0, 7);
      }
    }

    const show = !monthStr || rowMonth === monthStr;
    row.style.display = show ? "" : "none";
    if (show) hasVisibleRow = true;
  });

  const noDataRow = document.getElementById("noDataRow");

  if (!hasVisibleRow && !noDataRow) {
    const tr = document.createElement("tr");
    tr.id = "noDataRow";
    tr.innerHTML = `
      <td colspan="7" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
        <i class="bi bi-percent fs-5 me-2"></i>No benefit rate found for this month.
      </td>`;
    tbody.appendChild(tr);
  } else if (hasVisibleRow && noDataRow) {
    noDataRow.remove();
  }
}

function refreshBenefitRateTable(withFade = false) {
  fetch('../app/api/benefit_rates-api.php')
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success' && data.html) {
        const tbody = document.getElementById('benefitRateTable');
        const temp = document.createElement('tbody');
        temp.innerHTML = data.html;

        const newRows = Array.from(temp.children);
        tbody.innerHTML = '';
        newRows.forEach(row => {
          row.style.transition = 'opacity 0.5s ease';
          row.style.opacity = 0;
          tbody.appendChild(row);
        });

        requestAnimationFrame(() => {
          newRows.forEach(row => (row.style.opacity = 1));
        });

        if (withFade) {
          tbody.classList.add('fade-in-slide');
          tbody.addEventListener('animationend', () => {
            tbody.classList.remove('fade-in-slide');
          }, { once: true });
        }

        attachDeleteButtonHandlers();
        const selectedMonth = monthFilterInput.value || formatDate(today);
        filterTableByMonth(selectedMonth);
        toggleMonthClearIcon();
      } else {
        Swal.fire('Error!', data.message || 'Failed to refresh table.', 'error');
      }
    })
    .catch(() => Swal.fire('Error!', 'Network error.', 'error'));
}

function attachDeleteButtonHandlers() {
  document.querySelectorAll('.delete-btn').forEach(button => {
    button.onclick = () => {
      const id = button.getAttribute('data-id');
      Swal.fire({
        title: 'Are you sure?',
        text: "This benefit rate will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
      }).then(result => {
        if (result.isConfirmed) {
          fetch(`../app/api/benefit_rates-api.php?id=${id}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire({
                  icon: 'success',
                  title: 'Deleted!',
                  text: 'Benefit rate has been removed.',
                  timer: 1000,
                  showConfirmButton: false
                }).then(() => {
                  const row = button.closest('tr');
                  if (row) {
                    row.style.transition = 'opacity 0.5s ease';
                    row.style.opacity = 0;
                    setTimeout(() => {
                      row.remove();
                      refreshBenefitRateTable(false);
                    }, 500);
                  }
                });
              } else {
                Swal.fire('Error!', data.message || 'Failed to delete.', 'error');
              }
            })
            .catch(() => Swal.fire('Error!', 'Network error.', 'error'));
        }
      });
    };
  });
}

document.getElementById('benefitsRateForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const formData = new FormData(this);
  const payload = {
    sss_rate: parseFloat(formData.get('sss_rate')),
    pagibig_rate: parseFloat(formData.get('pagibig_rate')),
    philhealth_rate: parseFloat(formData.get('philhealth_rate'))
  };

  fetch('../app/api/benefit_rates-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('updateRatesModal')).hide();

        Swal.fire({
          icon: 'success',
          title: 'Updated!',
          text: 'Benefit rates have been saved.',
          timer: 1000,
          showConfirmButton: false
        }).then(() => {
          refreshBenefitRateTable(false);
        });
      } else {
        Swal.fire('Error!', data.message || 'Update failed.', 'error');
      }
    })
    .catch(() => Swal.fire('Error!', 'Network error.', 'error'));
});

attachDeleteButtonHandlers();
filterTableByMonth(formatDate(today));
toggleMonthClearIcon();
</script>





<?php require_once views_path("partials/footer"); ?>