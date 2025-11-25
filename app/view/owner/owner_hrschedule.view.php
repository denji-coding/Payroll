<?php
$title = "HR Schedule";
require_once views_path("partials/header");
require_once views_path("owner/owner_sidebar");
?>

<style>
@keyframes fadeInSlide {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
.fade-in-slide { animation: fadeInSlide 0.4s ease-out; }

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
.btn-close:hover { color: #000; text-decoration: none; opacity: 0.75; }
.btn-close:focus { outline: none; box-shadow: none; }

@media (max-width: 767px) {
    main#mainContent {
        padding-top: calc(var(--mobile-navbar-height, 3.5rem) + 0.5rem) !important;
    }
    
    header h1 {
        display: block !important;
        visibility: visible !important;
        padding-top: 0.5rem;
    }
}

@media (min-width: 768px) {
    main#mainContent {
        padding-top: 0 !important;
    }
}
</style>

<main id="mainContent" class="ml-0 md:ml-[256px] p-3 sm:p-4 md:p-6 bg-[#f8fbf8] min-h-screen">
  <header class="mb-4 md:mb-6 pt-2 sm:pt-0">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#133913] block" style="display: block !important; visibility: visible !important;">HR Schedule</h1>
        <p class="text-sm sm:text-base text-[#478547] mt-1">Manage HR schedules.</p>
      </div>

      <div class="flex gap-2 w-full sm:w-auto">
        <button type="button" 
                class="btn btn-success d-inline-flex align-items-center h-10 px-3 sm:px-4 py-2 text-sm sm:text-base w-full sm:w-auto"
                data-bs-toggle="modal" 
                data-bs-target="#addScheduleModal">
          <i class="fas fa-plus me-2"></i>
          <span class="font-semibold">Add schedule</span>
        </button>
      </div>
    </div>
  </header>

  <div class="bg-white border-2 border-green-200 rounded-lg p-4 md:p-6 mt-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
      <span class="text-xl md:text-2xl font-semibold text-[#133913]">Schedule Management</span>

      <div class="relative w-64">
        <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.3-4.3"></path>
        </svg>
        <input type="text" id="searchInput" class="sched-search flex h-10 w-full rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base placeholder:text-[#478547] focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:ring-offset-2 disabled:opacity-50 md:text-sm" placeholder="Search employee...">
        <button id="clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden">×</button>
      </div>
    </div>

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

          $query = "
            SELECT 
              s.*,
              CONCAT(
                a.hr_first_name, ' ',
                IFNULL(CONCAT(UPPER(LEFT(a.hr_middle_name, 1)), '. '), ''),
                a.hr_last_name
              ) AS display_name,
              'hr' AS record_type
            FROM schedules s
            INNER JOIN hr_schedules hs ON hs.schedule_id = s.id
            INNER JOIN admins a ON a.id = hs.hr_id
            ORDER BY s.id DESC
          ";
          $result = $conn->query($query);

          if ($result->rowCount() > 0):
              $i = 1;
              while ($row = $result->fetch(PDO::FETCH_ASSOC)):
          ?>
            <tr class="fade-in-slide border-b-0 hover:bg-[#f2f8f2] even:bg-[#cde4cd]" data-record-type="<?= htmlspecialchars($row['record_type']) ?>">
              <td class="px-3 md:px-6 text-center py-2"><?= $i++ ?></td>
              <td class="px-2 md:px-6 py-2"><?= htmlspecialchars(ucwords(strtolower($row['display_name']))) ?></td>
              <td class="px-2 md:px-6 text-center py-2"><?= date("g:i A", strtotime($row['sched_morning_in'])) ?></td>
              <td class="px-2 md:px-6 text-center py-2"><?= date("g:i A", strtotime($row['sched_morning_out'])) ?></td>
              <td class="px-2 md:px-6 text-center py-2"><?= date("g:i A", strtotime($row['sched_afternoon_in'])) ?></td>
              <td class="px-2 md:px-6 text-center py-2"><?= date("g:i A", strtotime($row['sched_afternoon_out'])) ?></td>
              <td class="px-2 md:px-6 py-2 text-center whitespace-nowrap"><?= (int)$row['grace_period'] ?> mins</td>
              <td class="px-2 md:px-6 py-2 text-center whitespace-nowrap">
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
                  onclick="populateEditSchedule(<?= $row['id'] ?>)">
                  <i class="bi bi-pencil-square"></i>
                </button>

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
          <button type="button" class="btn-close" data-bs-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body space-y-3">
          <div class="mb-3">
            <label class="form-label text-success ml-2">HR Name</label>
            <select name="employee_id" class="form-select focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]" required>
              <option value="" disabled selected>--- Select HR ---</option>
              <?php
                try {
                  $adminsStmt = $conn->prepare("SELECT id, hr_first_name, hr_middle_name, hr_last_name, hr_position FROM admins WHERE deleted_at IS NULL ORDER BY hr_first_name, hr_last_name");
                  $adminsStmt->execute();
                  while ($hr = $adminsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $mid = isset($hr['hr_middle_name']) && $hr['hr_middle_name'] !== '' ? strtoupper(substr($hr['hr_middle_name'],0,1)).'. ' : '';
                    $full = trim($hr['hr_first_name'].' '.$mid.$hr['hr_last_name']);
              ?>
                    <option value="<?= htmlspecialchars($hr['id']) ?>" data-type="hr">
                      <?= htmlspecialchars($full) ?> (<?= htmlspecialchars($hr['hr_position'] ?? 'HR') ?>)
                    </option>
              <?php
                  }
                } catch (Exception $e) {
                  // Fallback option
                  echo '<option disabled>Error loading HR list</option>';
                }
              ?>
            </select>
            <input type="hidden" name="record_type" id="recordType" value="hr">
          </div>

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

          <div class="mb-3">
            <label class="form-label text-success ml-2">Grace Period (minutes)</label>
            <input type="number" name="grace_period" class="form-control" required>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <button type="submit" class="btn btn-success"><i class="fa fa-plus me-2"></i>Add Schedule</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </form>
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
          <button type="button" class="btn-close" data-bs-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body space-y-3">
          <input type="hidden" name="id" id="editScheduleId">

          <div class="mb-3">
            <label class="form-label text-success ml-2">Schedule Name</label>
            <input type="text" name="schedule_name" id="editScheduleName" class="form-control bg-gray-100 pointer-events-none cursor-default" readonly>
          </div>

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

          <div>
            <label class="form-label text-[#396A39] font-semibold ml-2">Grace Period (minutes)</label>
            <input type="number" name="grace_period" id="editGracePeriod" class="form-control" required>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <button type="submit" class="btn btn-success">Save Changes</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once views_path("partials/footer"); ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const addForm = document.getElementById('addScheduleForm');
  const editForm = document.getElementById('editScheduleForm');

  if (addForm) {
    addForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(addForm);

      try {
        const res = await fetch('../app/api/schedules-api.php', { method: 'POST', body: formData });
        const result = await res.json();

        if (result.status === 'success') {
          const modal = bootstrap.Modal.getInstance(document.getElementById('addScheduleModal'));
          if (modal) modal.hide();
          setTimeout(() => {
            Swal.fire({ title: result.message, icon: result.icon, timer: 1000, timerProgressBar: true, showConfirmButton: false })
              .then(() => { addForm.reset(); if (result.new_row_html) { insertRowWithAnimation(result.new_row_html); } else { refreshScheduleTable(); } });
          }, 400);
        } else {
          Swal.fire({ title: 'Error', text: result.message, icon: result.icon || 'error' });
        }
      } catch (err) {
        Swal.fire({ title: 'Request Failed', text: 'An unexpected error occurred. Please try again.', icon: 'error' });
        console.error('Add Schedule Error:', err);
      }
    });
  }

  function insertRowWithAnimation(rowHTML) {
    const tbody = document.getElementById('scheduleTableBody');
    const temp = document.createElement('tbody');
    temp.innerHTML = rowHTML.trim();
    const newRow = temp.querySelector('tr');
    if (newRow) {
      newRow.style.opacity = '0';
      newRow.style.transition = 'opacity 0.4s ease';
      tbody.prepend(newRow);
      setTimeout(() => { newRow.style.opacity = '1'; }, 10);
    }
  }

  if (editForm) {
    editForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(editForm);
      try {
        const res = await fetch('../app/api/schedules-api.php', { method: 'POST', body: formData });
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
            Swal.fire({ title: result.message, icon: result.icon, timer: 1000, timerProgressBar: true, showConfirmButton: false })
              .then(() => { refreshScheduleTable(); });
          }, 400);
        } else {
          Swal.fire(result.message, '', result.icon);
        }
      } catch (err) {
        console.error('Edit Schedule Error:', err);
      }
    });
  }

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-action="delete-schedule"]');
    if (btn) {
      const scheduleId = btn.getAttribute('data-id');
      const confirm = await Swal.fire({ title: 'Are you sure?', text: 'This schedule will be permanently deleted.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Confirm' });
      if (confirm.isConfirmed) {
        const formData = new FormData(); formData.append('action', 'delete'); formData.append('schedule_id', scheduleId);
        try {
          const res = await fetch('../app/api/schedules-api.php', { method: 'POST', body: formData });
          const result = await res.json();
          if (result.status === 'success') {
            const row = btn.closest('tr');
            Swal.fire({ title: result.message, icon: result.icon, timer: 1000, timerProgressBar: true, showConfirmButton: false })
              .then(() => { if (row) { row.style.transition = 'opacity 0.4s ease'; row.style.opacity = '0'; setTimeout(() => { row.remove(); updateScheduleRowNumbers(); }, 400); } else { refreshScheduleTable(); } });
          } else { Swal.fire(result.message, '', result.icon); }
        } catch (err) { console.error('Delete Schedule Error:', err); Swal.fire('Error', 'Something went wrong during deletion.', 'error'); }
      }
    }
  });

  function updateScheduleRowNumbers() {
    const rows = document.querySelectorAll('#scheduleTableBody tr');
    rows.forEach((row, index) => { const numberCell = row.querySelector('td'); if (numberCell) { numberCell.textContent = index + 1; } });
  }

  window.populateEditSchedule = async (id) => {
    try {
      const res = await fetch(`../app/api/schedules-api.php?fetch_schedule=${id}`);
      const result = await res.json();
      if (result.status === 'success') {
        const s = result.data;
        const formatName = (name) => name.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
        document.getElementById('editScheduleId').value = s.id;
        document.getElementById('editScheduleName').value = formatName(s.record_name || s.name || '');
        document.getElementById('editMorningIn').value = s.sched_morning_in;
        document.getElementById('editMorningOut').value = s.sched_morning_out;
        document.getElementById('editAfternoonIn').value = s.sched_afternoon_in;
        document.getElementById('editAfternoonOut').value = s.sched_afternoon_out;
        document.getElementById('editGracePeriod').value = s.grace_period;
      } else { Swal.fire('Error', result.message, 'error'); }
    } catch (err) { console.error('Fetch Schedule Error:', err); Swal.fire('Error', 'Something went wrong while fetching the schedule.', 'error'); }
  };

  window.refreshScheduleTable = async (highlightId = null) => {
    try {
      const res = await fetch('../app/api/schedules-api.php?fetch_table=hr');
      const html = await res.text();
      const temp = document.createElement('tbody'); temp.innerHTML = html.trim();
      const tbody = document.getElementById('scheduleTableBody'); tbody.innerHTML = '';
      temp.querySelectorAll('tr').forEach((row, index) => {
        const rowId = row.getAttribute('data-id');
        if (highlightId && rowId === highlightId.toString()) { row.style.opacity = '0'; row.style.transition = 'opacity 0.4s ease'; tbody.appendChild(row); requestAnimationFrame(() => { row.style.opacity = '1'; }); }
        else { setTimeout(() => { row.classList.add('fade-in-slide'); tbody.appendChild(row); }, index * 30); }
      });
    } catch (err) { console.error("Failed to refresh schedule table", err); }
  };

  // No dynamic refresh needed; HR list is rendered server-side
  function refreshEmployeeSelect() {}

  function toggleClearButton() { clearBtn.classList.toggle("hidden", searchInput.value.trim() === ""); }

  const searchInput = document.getElementById("searchInput");
  const clearBtn = document.getElementById("clearButton");
  let debounceTimer;
  searchInput.addEventListener("input", function () { toggleClearButton(); clearTimeout(debounceTimer); debounceTimer = setTimeout(() => { filterTable(); }, 300); });
  clearBtn.addEventListener("click", function () { searchInput.value = ""; toggleClearButton(); filterTable(); });

  function filterTable() {
    const searchTerm = searchInput.value.trim().toLowerCase();
    const rows = document.querySelectorAll("tbody tr");
    let visibleCount = 0;
    rows.forEach(row => {
      const nameCell = row.querySelector("td:nth-child(2)");
      if (!nameCell || row.id === "noResultRow") return;
      const nameText = nameCell.textContent.trim().toLowerCase();
      const words = nameText.split(/\s+/);
      const match = words.some(word => word.startsWith(searchTerm));
      row.style.display = match || searchTerm === "" ? "" : "none";
      if (match) visibleCount++;
    });
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
    } else { if (noResultRow) noResultRow.remove(); }
  }

  // Initial loads (none needed for HR select)
});
</script>


