<?php
$title = "Leave History";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");
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

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-approved {
  background-color: #d1fae5;
  color: #065f46;
}

.status-rejected {
  background-color: #fee2e2;
  color: #991b1b;
}
</style>

<main class="flex-1 h-[calc(100vh-3rem)] p-4 md:p-6 ml-[255px] mt-12 bg-[#f8fbf8]">
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <span class="text-2xl font-bold tracking-tight text-[#133913]">Leave History</span>
        <p class="text-[#478547]">View records of past employee leave requests and their statuses.</p>
      </div>
    </div>

    <div class="rounded-lg border-2 border-green-200 bg-white text-[#133913] shadow-sm">
      <div class="space-y-1.5 p-6 flex flex-row items-center justify-between">
        <span class="text-2xl font-semibold leading-none tracking-tight text-[#133913]">Leave Records</span>
        <div class="relative w-64">
          <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.3-4.3"></path>
          </svg>
          <input
            type="text"
            id="LeaveHistorySearch"
            class="flex h-10 w-full placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
            placeholder="Search employee..."
            oninput="LeaveHistorytoggleClearButton()"
          >
          <button id="LeaveHistoryClearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden" onclick="LeaveHistoryclearInput()">×</button>
        </div>
      </div>

      <div class="p-6 pt-0">
        <div class="relative w-full overflow-auto">
          <div class="max-h-[calc(100vh-300px)] overflow-y-auto">
            <table class="w-full caption-bottom text-sm">
              <thead class="[&_tr]:border-b bg-[#f2f8f2] sticky top-0 z-10">
                <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                  <th class="h-12 px-3 text-left font-bold text-[#478547] bg-white">Photos</th>
                  <th class="h-12 px-3 text-left font-bold text-[#478547] bg-white">Name</th>
                  <th class="h-12 px-3 text-left font-bold whitespace-nowrap text-[#478547] bg-white">ID</th>
                  <th class="h-12 px-3 text-left font-bold text-[#478547] bg-white">Type</th>
                  <th class="h-12 px-3 text-left font-bold text-[#478547] bg-white">Leave Types</th>
                  <th class="h-12 px-3 text-left font-bold text-[#478547] bg-white">Start Date</th>
                  <th class="h-12 px-3 text-left font-bold text-[#478547] bg-white">End Date</th>
                  <th class="h-12 px-3 text-left font-bold text-[#478547] bg-white">Status</th>
                  <th class="h-12 px-3 text-left font-bold text-[#478547] bg-white">Approved/Rejected By</th>
                  <th class="h-12 px-3 text-left font-bold text-[#478547] bg-white">Date Applied</th>
                </tr>
              </thead>
              <tbody id="LeaveemployeeTable" class="[&_tr:last-child]:border-0">
                <?php if (!empty($employees)): ?>
                  <?php foreach ($employees as $employee): ?>
                    <tr class="fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                      <td class="p-3">
                        <?php
                          $defaultImage = ($employee['sex'] === 'Female') 
                            ? '../public/assets/image/default_women.png' 
                            : '../public/assets/image/default_men.png';

                          $photoPath = !empty($employee['photo_path']) 
                            ? '../public/' . htmlspecialchars($employee['photo_path']) 
                            : $defaultImage;
                        ?>
                        <img src="<?= $photoPath ?>" alt="Photo" class="w-10 h-10 rounded-full object-cover border border-gray-300">
                      </td>
                      <td class="p-3 font-medium"><?= htmlspecialchars($employee['employee_name']) ?></td>
                      <td class="p-3"><?= htmlspecialchars($employee['employee_no']) ?></td>
                      <td class="p-3">
                        <?php
                          $applicantType = $employee['applicant_type'] ?? 'employee';
                          $typeBadgeClass = '';
                          $typeLabel = '';
                          switch ($applicantType) {
                            case 'manager':
                              $typeBadgeClass = 'bg-blue-100 text-blue-800';
                              $typeLabel = 'Manager';
                              break;
                            case 'hr':
                              $typeBadgeClass = 'bg-purple-100 text-purple-800';
                              $typeLabel = 'HR';
                              break;
                            default:
                              $typeBadgeClass = 'bg-green-100 text-green-800';
                              $typeLabel = 'Employee';
                              break;
                          }
                        ?>
                        <span class="px-2 py-1 rounded text-xs font-semibold <?= $typeBadgeClass ?>">
                          <?= htmlspecialchars($typeLabel) ?>
                        </span>
                      </td>
                      <td class="p-3"><?= htmlspecialchars($employee['leave_type']) ?></td>
                      <td class="p-3 whitespace-nowrap"><?= date('M d, Y', strtotime($employee['start_date'])) ?></td>
                      <td class="p-3 whitespace-nowrap"><?= date('M d, Y', strtotime($employee['end_date'])) ?></td>
                      <td class="p-3">
                        <?php
                          $statusClass = '';
                          switch ($employee['status']) {
                            case 'Pending':
                              $statusClass = 'status-pending';
                              break;
                            case 'Approved':
                              $statusClass = 'status-approved';
                              break;
                            case 'Rejected':
                              $statusClass = 'status-rejected';
                              break;
                          }
                        ?>
                        <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($employee['status']) ?></span>
                      </td>
                      <td class="p-3 text-center">
                        <?php if ($employee['status'] === 'Approved' || $employee['status'] === 'Rejected'): ?>
                          <?= !empty($employee['manager_name']) ? htmlspecialchars($employee['manager_name']) : '—' ?>
                        <?php else: ?>
                          <span class="text-gray-400">—</span>
                        <?php endif; ?>
                      </td>
                      <td class="p-3 text-sm text-gray-600">
                        <?= date('M d, Y', strtotime($employee['created_at'])) ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="10" class="p-4 text-center text-gray-500">
                      <i class="bi bi-calendar-x me-2"></i> No leave history found.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("LeaveHistorySearch");
  const clearBtn = document.getElementById("LeaveHistoryClearButton");
  const tbody = document.getElementById("LeaveemployeeTable");

  function LeaveHistorytoggleClearButton() {
    clearBtn.style.display = searchInput.value.trim() !== '' ? 'block' : 'none';
  }

  function LeaveHistoryclearInput() {
    searchInput.value = '';
    LeaveHistorytoggleClearButton();
    LeaveHistoryfilterTable();
  }

  function LeaveHistoryfilterTable() {
    const searchTerm = searchInput.value.trim().toLowerCase();
    const rows = tbody.querySelectorAll("tr");
    let visibleCount = 0;

    rows.forEach(row => {
      if (row.id === "noResultRow") return;

      const nameCell = row.querySelector("td:nth-child(2)");
      const empNoCell = row.querySelector("td:nth-child(3)");
      const leaveTypeCell = row.querySelector("td:nth-child(5)");
      if (!nameCell || !empNoCell || !leaveTypeCell) return;

      const name = nameCell.textContent.trim().toLowerCase();
      const empNo = empNoCell.textContent.trim().toLowerCase();
      const leaveType = leaveTypeCell.textContent.trim().toLowerCase();
      const nameParts = name.split(" ");

      const matches =
        name.includes(searchTerm) ||
        empNo.includes(searchTerm) ||
        leaveType.includes(searchTerm) ||
        nameParts.some(p => p.startsWith(searchTerm));

      if (matches) {
        row.style.display = "";
        row.classList.add("fade-in-slide");
        visibleCount++;
      } else {
        row.style.display = "none";
      }
    });

    let noRow = document.getElementById("noResultRow");
    if (visibleCount === 0) {
      if (!noRow) {
        noRow = document.createElement("tr");
        noRow.id = "noResultRow";
        noRow.innerHTML = `
          <td colspan="10" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
            <i class="bi bi-calendar-x fs-4 me-2"></i> No leave records found.
          </td>`;
        tbody.appendChild(noRow);
      }
    } else {
      if (noRow) noRow.remove();
    }
  }

  // Debounce function
  function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
  }

  const debouncedFilter = debounce(() => {
    LeaveHistorytoggleClearButton();
    LeaveHistoryfilterTable();
  }, 300); // 300ms delay

  searchInput.addEventListener("input", debouncedFilter);
  clearBtn.addEventListener("click", LeaveHistoryclearInput);

  // Expose filter function globally (if needed)
  window.applyFilter = LeaveHistoryfilterTable;
});
</script>

<?php require_once views_path("partials/footer"); ?>
