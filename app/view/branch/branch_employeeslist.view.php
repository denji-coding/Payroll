<?php

$title = "Employee's List";
require_once views_path("partials/header");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$managerId = $_SESSION['manager_id'] ?? null;
if (!$managerId) {
    die("Manager not logged in.");
}

require_once '../app/core/database.php';
$db = new Database();

$sql = "
SELECT 
    e.id, 
    e.employee_no, 
    e.rfid_number, 
    CONCAT(
        UPPER(LEFT(e.first_name, 1)), LOWER(SUBSTRING(e.first_name, 2)), ' ',
        UPPER(LEFT(e.middle_name, 1)), '. ',
        UPPER(LEFT(e.last_name, 1)), LOWER(SUBSTRING(e.last_name, 2))
    ) AS full_name, 
    e.position, 
    e.photo_path, 
    s.sched_morning_in, 
    s.sched_morning_out,
    s.sched_afternoon_in,
    s.sched_afternoon_out,
    e.approved_by_manager,
    CONCAT(m.first_name, ' ', m.last_name) AS manager_name
FROM employees e
LEFT JOIN employee_schedules es ON es.employee_id = e.id
LEFT JOIN schedules s ON s.id = es.schedule_id
LEFT JOIN employees m ON m.id = e.branch_manager
WHERE e.branch_manager = :manager_id
AND e.approved_by_manager = 1
ORDER BY e.last_name ASC
";

$list = $db->query($sql, ['manager_id' => $managerId]);

echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';
?>

<style>
@keyframes fadeInSlide {
  from {
    opacity: 0;
    transform: translateY(-2px);
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

<div class="flex min-h-screen overflow-hidden">
    <main id="mainContent" class="flex-1 p-6 bg-gray-100 transition-margin duration-300 ease-in-out" style="margin-left: 256px;">
        <?php require_once views_path("branch/branch_sidebar"); ?>

        <div>
            <span class="text-2xl font-bold tracking-tight">Employee's List</span>
            <p class="text-gray-600">A detailed list of employees managed by you.</p>
        </div>

        <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-gray-200 relative">
                <span class="text-lg font-semibold text-gray-800">Employees</span>
                <div class="relative max-w-sm w-full sm:w-auto">
                    <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="employeeSearch"
                        placeholder="Search employees..."
                        class="flex h-10 w-full placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
                    >
                    <button
                        id="clearSearch"
                         class="absolute right-2 top-[3px]  text-[#478547] text-xl hidden"

                        aria-label="Clear search"
                        type="button"
                    >
                        <span class="text-xs">&#x2715;</span>
                    </button>
                </div>
            </div>

            <div class="relative w-full overflow-auto custom-scrollbar">
                <div class="max-h-[calc(100vh-220px)]">
                    <table id="employeeTable" class="min-w-[1000px] w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-emerald-600 sticky top-0 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold tracking-wide">Photo</th>
                                <th class="px-6 py-3 text-left font-semibold tracking-wide">Employee Name</th>
                                <th class="px-6 py-3 text-left font-semibold tracking-wide">Employee ID</th>
                                <th class="px-6 py-3 text-left font-semibold tracking-wide">RFID Number</th>
                                <th class="px-6 py-3 text-left font-semibold tracking-wide">Position</th>
                                <th class="px-6 py-3 text-left font-semibold tracking-wide">Scheduled</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php if (!empty($list) && is_array($list)): ?>
                                <?php foreach ($list as $lists): ?>
                                    <tr class="fade-in-slide hover:bg-gray-50 transition-colors duration-200 even:bg-[#cde4cd]">
                                        <td class="px-6 py-3">
                                            <?php
                                                if (!empty($lists['photo_path'])) {
                                                    $imagePath = '/mvcPayroll/public/' . htmlspecialchars($lists['photo_path']);
                                                } else {
                                                    $sex = strtolower($lists['sex'] ?? '');
                                                    if (in_array($sex, ['f', 'female', 'woman'])) {
                                                        $imagePath = '/mvcPayroll/public/assets/image/default_women.png';
                                                    } else {
                                                        $imagePath = '/mvcPayroll/public/assets/image/default_men.png';
                                                    }
                                                }
                                            ?>

                                            <img src="<?= $imagePath ?>" class="h-10 w-10 rounded-full object-cover" alt="Employee Photo">

                                        </td>
                                        <td class="px-6 py-3"><?= htmlspecialchars(ucwords(strtolower($lists['full_name']))) ?></td>
                                        <td class="px-6 py-3"><?= htmlspecialchars($lists['employee_no']) ?></td>
                                        <td class="px-6 py-3"><?= htmlspecialchars($lists['rfid_number']) ?></td>
                                        <td class="px-6 py-3"><?= htmlspecialchars($lists['position']) ?></td>
                                        <td class="px-6 py-3">
                                            <?php if (!empty($lists['sched_morning_in']) && !empty($lists['sched_morning_out'])): ?>
                                                <?= date("h:i A", strtotime($lists['sched_morning_in'])) ?> - <?= date("h:i A", strtotime($lists['sched_morning_out'])) ?> |
                                                <?= date("h:i A", strtotime($lists['sched_afternoon_in'])) ?> - <?= date("h:i A", strtotime($lists['sched_afternoon_out'])) ?>
                                            <?php else: ?>
                                                No schedule assigned.   
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-4 py-3 text-center text-secondary fst-italic bg-light fade-in-slide">
                                        <i class="bi bi-person-x fs-4 me-2"></i> No employee found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
const searchInput = document.getElementById('employeeSearch');
const clearBtn = document.getElementById('clearSearch');
const table = document.getElementById('employeeTable');
const noResultRowId = 'no-result-row';

let debounceTimer;
const DEBOUNCE_DELAY = 300; // milliseconds

function handleSearch() {
    clearBtn.style.display = searchInput.value ? 'block' : 'none';

    const searchTerm = searchInput.value.trim().toLowerCase();
    const rows = Array.from(table.tBodies[0].rows);
    let matchFound = false;

    for (let row of rows) {
        if (row.id === noResultRowId) continue;

        const fullName = row.cells[1]?.textContent.trim().toLowerCase() || '';
        const employeeId = row.cells[2]?.textContent.trim().toLowerCase() || '';

        const nameParts = fullName.split(' ').filter(Boolean);
        let firstName = '', middleInitial = '', lastName = '';

        if (nameParts.length >= 2) {
            lastName = nameParts[nameParts.length - 1];
            const secondToLast = nameParts[nameParts.length - 2];
            if (secondToLast.endsWith('.')) {
                middleInitial = secondToLast.replace('.', '');
                firstName = nameParts.slice(0, nameParts.length - 2).join(' ');
            } else {
                firstName = nameParts.slice(0, nameParts.length - 1).join(' ');
            }
        } else {
            firstName = nameParts[0] || '';
        }

        const matches =
            fullName.includes(searchTerm) ||
            firstName.includes(searchTerm) ||
            middleInitial.includes(searchTerm) ||
            lastName.includes(searchTerm) ||
            employeeId.includes(searchTerm);

        if (matches) {
            row.style.display = '';
            row.classList.remove('fade-in-slide');
            void row.offsetWidth;
            row.classList.add('fade-in-slide');
            matchFound = true;
        } else {
            row.style.display = 'none';
        }
    }

    // Remove existing "no result" row
    const existingNoRow = document.getElementById(noResultRowId);
    if (existingNoRow) existingNoRow.remove();

    if (!matchFound) {
        const noRow = document.createElement('tr');
        noRow.id = noResultRowId;
        noRow.innerHTML = `
            <td colspan="8" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
                <i class="bi bi-person-x fs-4 me-2"></i> No employee found.
            </td>
        `;
        table.tBodies[0].appendChild(noRow);
    }
}

searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(handleSearch, DEBOUNCE_DELAY);
});

clearBtn.addEventListener('click', () => {
    searchInput.value = '';
    clearBtn.style.display = 'none';
    searchInput.dispatchEvent(new Event('input'));
});
</script>
