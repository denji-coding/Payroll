<?php
$title = "Schedules";
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
</style>



<main class="h-[calc(100vh-3rem)] overflow-hidden p-4 md:p-6 sm:ml-64 mt-12 bg-[#f8fbf8]">
  <header class="mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <!-- Left Side -->
      <div>
        <span class="text-2xl font-bold tracking-tight text-[#133913]">Schedules</span>
        <p class="text-[#478547]">Manage employee schedules.</p>
      </div>

      <!-- Add Schedule Button -->
      <button type="button" 
              class="btn btn-success d-inline-flex align-items-center h-10 px-4 py-2"
              style="min-width: 106px;"
              data-bs-toggle="modal" 
              data-bs-target="#addScheduleModal">
        <i class="fas fa-plus me-2"></i>
        <span class="d-none d-sm-inline font-semibold">Add schedule</span>
      </button>
    </div>
  </header>

  <!-- Card Section -->
  <div class="bg-white border-2 border-green-200 rounded-lg p-4 md:p-6 mt-4">
    <!-- Title and Search -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
      <span class="text-xl md:text-2xl font-semibold text-[#133913]">Schedule Management</span>

      <!-- Search Input -->
      <div class="relative w-64">
        <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
        <input
          type="text"
          id="searchInput"
          class="sched-search flex h-10 w-full rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base placeholder:text-[#478547] focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:ring-offset-2 disabled:opacity-50 md:text-sm"
          placeholder="Search employee..."
        >
        <button id="clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden">×</button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-y-auto max-h-[357px] transition-all duration-300 ease-in-out">
      <table class="min-w-full table-auto md:table-fixed">
        <thead class="sticky top-0 z-10">
          <tr class="border-b hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
            <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white w-[8%]">No.</th>
            <th class="h-12 px-2 md:px-4 text-left text-sm font-bold text-[#478547] bg-white w-[25%]">Schedule Name</th>
            <th class="h-12 px-2 md:px-4 text-center text-sm font-bold  text-[#478547] bg-white w-[17%]">Morning In</th>
            <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white w-[17%]">Morning Out</th>
            <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white w-[17%]">Afternoon In</th>
            <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white w-[17%]">Afternoon Out</th>
            <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white whitespace-nowrap w-[18%]">Grace Period</th>
            <th class="h-12 px-2 md:px-4 text-sm font-bold text-[#478547] bg-white w-[15%] text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="scheduleTableBody" class="bg-white divide-y divide-gray-100 text-sm">
          <?php
          $db = new Database();
          $conn = $db->getConnection();

          $query = "SELECT * FROM schedules ORDER BY id DESC";
          $result = $conn->query($query);

          if ($result->rowCount() > 0):
              $i = 1;
              while ($row = $result->fetch(PDO::FETCH_ASSOC)):
          ?>
          <tr class="fade-in-slide border-b-0 hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
            <td class="px-3 md:px-6 text-center py-2"><?= $i++ ?></td>
            <td class="px-2 md:px-6 py-2"><?= htmlspecialchars($row['name']) ?></td>
            <td class="px-2 md:px-6 text-center py-2"><?= date("g:i A", strtotime($row['sched_morning_in'])) ?></td>
            <td class="px-2 md:px-6 text-center py-2"><?= date("g:i A", strtotime($row['sched_morning_out'])) ?></td>
            <td class="px-2 md:px-6 text-center py-2"><?= date("g:i A", strtotime($row['sched_afternoon_in'])) ?></td>
            <td class="px-2 md:px-6 text-center py-2"><?= date("g:i A", strtotime($row['sched_afternoon_out'])) ?></td>
            <td class="px-2 md:px-6 py-2 text-center whitespace-nowrap"><?= (int)$row['grace_period'] ?> mins</td>

            <td class="px-2 md:px-6 py-2 text-center whitespace-nowrap">
              <!-- Edit Button -->
              <button 
                type="button"
                class="edit-btn inline-flex h-8 w-8 items-center justify-center rounded-md transition duration-150 ease-in-out hover:bg-[#478547] hover:text-white transform hover:scale-105"
                data-id="<?= htmlspecialchars($row['id']) ?>"
                data-name="<?= htmlspecialchars($row['name']) ?>"
                data-morningin="<?= htmlspecialchars($row['sched_morning_in']) ?>"
                data-morningout="<?= htmlspecialchars($row['sched_morning_out']) ?>"
                data-afternoonin="<?= htmlspecialchars($row['sched_afternoon_in']) ?>"
                data-afternoonout="<?= htmlspecialchars($row['sched_afternoon_out']) ?>"
                data-grace="<?= htmlspecialchars($row['grace_period']) ?>"
                data-bs-toggle="modal"
                data-bs-target="#editScheduleModal"
                onclick="populateEditSchedule(<?= $row['id'] ?>)"
              >
                <i class="bi bi-pencil-square"></i>
              </button>

              
              <!-- DELETE BUTTON FORM -->
              <form id="deleteScheduleForm-<?= $row['id'] ?>" class="inline-block m-0 p-0">
                <input type="hidden" name="schedule_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="action" value="delete">

                <button type="button"
        data-action="delete-schedule"
        data-id="<?= $row['id'] ?>"
        class="inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors hover:bg-[#b91c1c] hover:text-white ml-1">
  <i class="bi bi-trash"></i>
</button>

              </form>

            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr>
            <td colspan="8" class="px-4 py-4 text-center text-gray-500">No Schedule Found</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>







<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="addScheduleForm">
        <div class="modal-header">
          <h5 class="modal-title text-success fs-5" id="addScheduleModalLabel">
            <i class="fa fa-plus me-2"></i>Add New Schedule
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body space-y-3">
          <!-- Employee Dropdown -->
          <div class="mb-3">
            <label class="form-label text-success ml-2">Employee Name</label>
            <select name="employee_id" class="form-select focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]" required>
              <option value="" disabled selected>--- Select an Employee ---</option>
              <?php if (!empty($data['employees'])): ?>
                <?php foreach ($data['employees'] as $employee): ?>
                  <?php if ($employee['approved_by_manager'] == 1): ?>
                    <option value="<?= htmlspecialchars($employee['id']) ?>">
                      <?= htmlspecialchars(
                        ucwords($employee['first_name']) . ' ' .
                        (isset($employee['middle_name'][0]) ? strtoupper($employee['middle_name'][0]) . '. ' : '') .
                        ucwords($employee['last_name'])
                      ) ?>
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
              <?php else: ?>
                <option disabled>No available employees</option>
              <?php endif; ?>
            </select>
          </div>

          <!-- Morning Time Row -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="form-label text-[#396A39] font-semibold ml-2">Morning In</label>
              <input type="time" name="sched_morning_in" class="form-control" required>
            </div>
            <div>
              <label class="form-label text-[#396A39] font-semibold ml-2">Morning Out</label>
              <input type="time" name="sched_morning_out" class="form-control" required>
            </div>
          </div>

          <!-- Afternoon Time Row -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="form-label text-[#396A39] font-semibold ml-2">Afternoon In</label>
              <input type="time" name="sched_afternoon_in" class="form-control" required>
            </div>
            <div>
              <label class="form-label text-[#396A39] font-semibold ml-2">Afternoon Out</label>
              <input type="time" name="sched_afternoon_out" class="form-control" required>
            </div>
          </div>

          <!-- Grace Period -->
          <div class="mb-3">
            <label class="form-label text-success ml-2">Grace Period (minutes)</label>
            <input type="number" name="grace_period" class="form-control" required>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-2 pt-4">
            <button type="submit" class="btn btn-success">Add Schedule</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- View Schedule Modal -->
<div class="modal fade" id="viewScheduleModal" tabindex="-1" aria-labelledby="viewScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-success fs-5" id="viewScheduleModalLabel">
          <i class="bi bi-info-circle me-2"></i>Schedule Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body space-y-2" id="viewScheduleBody">
        <!-- JS will populate this -->
      </div>
    </div>
  </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="editScheduleForm">
        <div class="modal-header">
          <h5 class="modal-title text-success fs-5" id="editScheduleModalLabel">
            <i class="fa fa-pen-to-square me-2"></i>Edit Schedule
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body space-y-3">
          <input type="hidden" name="id" id="editScheduleId">

          <!-- Schedule Name -->
          <div class="mb-3">
            <label class="form-label text-success ml-2">Schedule Name</label>
            <input type="text" name="schedule_name" id="editScheduleName" class="form-control bg-gray-100 pointer-events-none cursor-default" readonly>
          </div>

          <!-- Time Fields -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="form-label text-[#396A39] font-semibold ml-2">Morning In</label>
              <input type="time" name="sched_morning_in" id="editMorningIn" class="form-control" required>
            </div>
            <div>
              <label class="form-label text-[#396A39] font-semibold ml-2">Morning Out</label>
              <input type="time" name="sched_morning_out" id="editMorningOut" class="form-control" required>
            </div>
            <div>
              <label class="form-label text-[#396A39] font-semibold ml-2">Afternoon In</label>
              <input type="time" name="sched_afternoon_in" id="editAfternoonIn" class="form-control" required>
            </div>
            <div>
              <label class="form-label text-[#396A39] font-semibold ml-2">Afternoon Out</label>
              <input type="time" name="sched_afternoon_out" id="editAfternoonOut" class="form-control" required>
            </div>
          </div>

          <!-- Grace Period -->
          <div>
            <label class="form-label text-success ml-2">Grace Period (minutes)</label>
            <input type="number" name="grace_period" id="editGracePeriod" class="form-control" required>
          </div>

          <!-- Buttons -->
          <div class="flex justify-end gap-2 pt-4">
            <button type="submit" class="btn btn-success">Save Changes</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
  const addForm = document.getElementById('addScheduleForm');
  const editForm = document.getElementById('editScheduleForm');

// === ADD SCHEDULE ===
if (addForm) {
  addForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(addForm);

    try {
      const res = await fetch('../app/api/schedules-api.php', {
        method: 'POST',
        body: formData
      });

      const result = await res.json();

      if (result.status === 'success') {
        // ✅ Close modal first
        const modal = bootstrap.Modal.getInstance(document.getElementById('addScheduleModal'));
        if (modal) modal.hide();

        // ✅ Delay for modal close animation before Swal
        setTimeout(() => {
          Swal.fire({
            title: result.message,
            icon: result.icon,
            timer: 1000,
            timerProgressBar: true,
            showConfirmButton: false
          }).then(() => {
            addForm.reset();

            // ✅ If the API returns the new row's HTML, insert with animation
            if (result.new_row_html) {
              insertRowWithAnimation(result.new_row_html);
            } else {
              refreshScheduleTable(); // fallback if no HTML returned
            }

            refreshEmployeeSelect();
          });
        }, 400);
      } else {
        Swal.fire({
          title: 'Error',
          text: result.message,
          icon: result.icon || 'error'
        });
      }
    } catch (err) {
      Swal.fire({
        title: 'Request Failed',
        text: 'An unexpected error occurred. Please try again.',
        icon: 'error'
      });
      console.error('Add Schedule Error:', err);
    }
  });
}

// 🔁 Helper to insert a single row with animation
function insertRowWithAnimation(rowHTML) {
  const tbody = document.getElementById('scheduleTableBody');
  const temp = document.createElement('tbody');
  temp.innerHTML = rowHTML.trim();

  const newRow = temp.querySelector('tr');
  if (newRow) {
    newRow.style.opacity = '0';
    newRow.style.transition = 'opacity 0.4s ease';
    tbody.prepend(newRow);

    // Trigger fade-in
    setTimeout(() => {
      newRow.style.opacity = '1';
    }, 10); // slight delay to trigger transition
  }
}








  // === EDIT SCHEDULE ===
  if (editForm) {
    editForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(editForm);

      try {
        const res = await fetch('../app/api/schedules-api.php', {
          method: 'POST',
          body: formData
        });

        const result = await res.json();

        if (result.status === 'success') {
          const modalEl = document.getElementById('editScheduleModal');
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();

          setTimeout(() => {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            Swal.fire({
              title: result.message,
              icon: result.icon,
              timer: 1000,
              timerProgressBar: true,
              showConfirmButton: false
            }).then(() => {
              const row = document.querySelector(`tr[data-id="${formData.get('id')}"]`);

              if (row) {
                // 👇 Fade out the updated row
                row.style.transition = 'opacity 0.4s ease';
                row.style.opacity = '0';

                setTimeout(() => {
                  row.remove();
                  refreshScheduleTable();      // ✅ get updated version from server
                  refreshEmployeeSelect();     // ✅ refresh dropdowns
                }, 400);
              } else {
                refreshScheduleTable();
                refreshEmployeeSelect();
              }
            });
          }, 400);
        } else {
          Swal.fire(result.message, '', result.icon);
        }
      } catch (err) {
        console.error('Edit Schedule Error:', err);
      }
    });
  }

// === DELETE SCHEDULE ===
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-action="delete-schedule"]');
  if (btn) {
    const scheduleId = btn.getAttribute('data-id');

    const confirm = await Swal.fire({
      title: 'Are you sure?',
      text: 'This schedule will be permanently deleted.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!'
    });

    if (confirm.isConfirmed) {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('schedule_id', scheduleId);

      try {
        const res = await fetch('../app/api/schedules-api.php', {
          method: 'POST',
          body: formData
        });

        const result = await res.json();

        if (result.status === 'success') {
          const row = btn.closest('tr');

          // First show the Swal
          Swal.fire({
            title: result.message,
            icon: result.icon,
            timer: 1000,
            timerProgressBar: true,
            showConfirmButton: false
          }).then(() => {
            // After Swal closes, apply fade out animation
            if (row) {
              row.style.transition = 'opacity 0.4s ease';
              row.style.opacity = '0';

              setTimeout(() => {
                row.remove();
                updateScheduleRowNumbers();
                refreshEmployeeSelect(); // Update employee select after row removal
              }, 400); // match the transition duration
            } else {
              refreshScheduleTable(); // Fallback: if no row found
              refreshEmployeeSelect();
            }
          });

        } else {
          Swal.fire(result.message, '', result.icon);
        }

      } catch (err) {
        console.error('Delete Schedule Error:', err);
        Swal.fire('Error', 'Something went wrong during deletion.', 'error');
      }
    }
  }
});

function updateScheduleRowNumbers() {
  const rows = document.querySelectorAll('#scheduleTableBody tr');
  rows.forEach((row, index) => {
    const numberCell = row.querySelector('td');
    if (numberCell) {
      numberCell.textContent = index + 1;
    }
  });
}




  // === FILL EDIT MODAL ===
  window.populateEditSchedule = async (id) => {
    try {
      const res = await fetch(`../app/api/schedules-api.php?fetch_schedule=${id}`);
      const result = await res.json();

      if (result.status === 'success') {
        const s = result.data;

        const formatName = (name) => name.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());

        document.getElementById('editScheduleId').value = s.id;
        document.getElementById('editScheduleName').value = formatName(s.employee_name || s.name || '');
        document.getElementById('editMorningIn').value = s.sched_morning_in;
        document.getElementById('editMorningOut').value = s.sched_morning_out;
        document.getElementById('editAfternoonIn').value = s.sched_afternoon_in;
        document.getElementById('editAfternoonOut').value = s.sched_afternoon_out;
        document.getElementById('editGracePeriod').value = s.grace_period;

        // const modal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
        // modal.show();
      } else {
        Swal.fire('Error', result.message, 'error');
      }
    } catch (err) {
      console.error('Fetch Schedule Error:', err);
      Swal.fire('Error', 'Something went wrong while fetching the schedule.', 'error');
    }
  };

// === REFRESH SCHEDULE TABLE ===
window.refreshScheduleTable = async (highlightId = null) => {
  try {
    const res = await fetch('../app/api/schedules-api.php?fetch_table=1');
    const html = await res.text();

    // Create a temp container to parse new rows
    const temp = document.createElement('tbody');
    temp.innerHTML = html.trim();

    const tbody = document.getElementById('scheduleTableBody');
    tbody.innerHTML = ''; // Clear existing rows

    // Append each row with animation
    temp.querySelectorAll('tr').forEach((row, index) => {
      const rowId = row.getAttribute('data-id');

      // Only animate the newly inserted row
      if (highlightId && rowId === highlightId.toString()) {
        row.style.opacity = '0';
        row.style.transition = 'opacity 0.4s ease';
        tbody.appendChild(row);

        requestAnimationFrame(() => {
          row.style.opacity = '1';
        });
      } else {
        setTimeout(() => {
          row.classList.add('fade-in-slide');
          tbody.appendChild(row);
        }, index * 30);
      }
    });
  } catch (err) {
    console.error("Failed to refresh schedule table", err);
  }
};



// === REFRESH EMPLOYEE SELECT (only approved employees) ===
window.refreshEmployeeSelect = async () => {
  try {
    const res = await fetch('../app/api/schedules-api.php?available_employees=1');
    const employees = await res.json();

    const select = document.querySelector('select[name="employee_id"]');
    if (!select) return;

    select.innerHTML = `<option value="" disabled selected>--- Select an Employee ---</option>`;

    // Only display approved employees
    employees
      .filter(emp => parseInt(emp.approved_by_manager) === 1)
      .forEach(emp => {
        const fullName = `${capitalize(emp.first_name)} ${emp.middle_name ? emp.middle_name.charAt(0).toUpperCase() + '. ' : ''}${capitalize(emp.last_name)}`;
        const option = document.createElement('option');
        option.value = emp.id;
        option.textContent = fullName;
        select.appendChild(option);
      });
  } catch (err) {
    console.error('Failed to refresh employee list:', err);
  }
};



  // === CAPITALIZE UTILITY ===
  function capitalize(str) {
    return str.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
  }

  // Call once on page load
  refreshEmployeeSelect();
});
</script>

<?php require_once views_path("partials/footer"); ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const clearBtn = document.getElementById("clearButton");

  let debounceTimer;

  searchInput.addEventListener("input", function () {
    toggleClearButton();
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      filterTable();
    }, 300);
  });

  clearBtn.addEventListener("click", function () {
    searchInput.value = "";
    toggleClearButton();
    filterTable();
  });

  function toggleClearButton() {
    clearBtn.classList.toggle("hidden", searchInput.value.trim() === "");
  }

  function filterTable() {
    const searchTerm = searchInput.value.trim().toLowerCase();
    const rows = document.querySelectorAll("tbody tr");
    let visibleCount = 0;

    rows.forEach(row => {
      const nameCell = row.querySelector("td:nth-child(2)");
      if (!nameCell || row.id === "noResultRow") return;

      const nameText = nameCell.textContent.trim().toLowerCase();

      // Split by space and check each word or initial
      const words = nameText.split(/\s+/); // e.g., ["juan", "d.", "cruz"]
      const match = words.some(word => word.startsWith(searchTerm));

      row.style.display = match || searchTerm === "" ? "" : "none";

      if (match) visibleCount++;
    });

    // Handle no result
    let noResultRow = document.getElementById("noResultRow");
    if (visibleCount === 0) {
      if (!noResultRow) {
        noResultRow = document.createElement("tr");
        noResultRow.id = "noResultRow";
        noResultRow.innerHTML = `
          <td colspan="8" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
            <i class="bi bi-person-x fs-5 me-2"></i>No matching schedules found.
          </td>`;
        document.querySelector("tbody").appendChild(noResultRow);
      }
    } else {
      if (noResultRow) noResultRow.remove();
    }
  }
});
</script>




