<?php
$title = "Delete History";
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

<main class="flex-1 h-[calc(100vh-3rem)] p-4 md:p-6 ml-[255px] mt-12 bg-[#f8fbf8]">
    <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <span class="text-2xl font-bold tracking-tight text-[#133913]">Delete History</span>
                    <p class="text-[#478547]">View and restore deleted employees and managers</p>
                </div>


                
                <!-- <div>
                    <button id="showAddEmployeeModal" 
                            class="btn btn-success d-inline-flex align-items-center h-10 px-4 py-2 " 
                            data-bs-toggle="modal" 
                            data-bs-target="#addProducts">
                    <i class="fas fa-plus me-2"></i>
                    <span class="font-semibold">Add Employee</span>
                    </button>
                </div> -->
            </div>


        <div class="rounded-lg border-2 border-green-200 bg-white text-[#133913] shadow-sm" 
                    >
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between">
                <span class="text-md font-semibold leading-none tracking-tight text-[#133913]">
                    <!-- All employees and managers in the delete history will be permanently deleted after 60 days if not restored. -->
                </span>

                <div class="relative w-64">
                    <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="delete_searchInput"
                        class="flex h-10 w-full text-sm placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8  placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
                        placeholder="Search employee or manager..."
                    >
                    <button id="delete_clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden">×</button>
                </div>                
            </div>

            <div class="p-6 pt-0">
                <div class="relative w-full overflow-auto">
                    <div class="max-h-[calc(100vh-300px)] overflow-y-auto">
                        <table class="w-full caption-bottom text-sm">
                            <thead class="[&_tr]:border-b bg-[#f2f8f2] sticky top-0 z-10">
                                <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">No.</th>
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Photos</th>
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Fullname</th>
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Employee ID</th>
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">RFID Number</th>
                                    <th class="h-12 px-3 text-center  font-bold text-[#478547] bg-white">Position</th>
                                    <th class="h-12 px-3 align-middle font-bold text-[#478547] text-center bg-white">Actions</th>
                                </tr>
                            </thead>
                                <tbody id="delete_employeeTable" class="[&_tr:last-child]:border-0">
                                    <?php if (count($allRecords) > 0): ?>
                                        <?php $count = 1; ?>
                                        <?php foreach ($allRecords as $record): ?>
                                            <tr class="fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]" data-record-type="<?= $record['record_type'] ?>">
                                                <td class="px-3 py-2 align-middle"><?= $count++ ?></td>
                                                <td class="px-3 py-2 align-middle">
                                                    <?php if ($record['record_type'] === 'employee'): ?>
                                                        <?php if (!empty($record['photo_path'])): ?>
                                                            <img src="<?= htmlspecialchars($record['photo_path']) ?>" alt="Photo" class="h-10 w-10 rounded-full object-cover">
                                                        <?php else: ?>
                                                            <?php
                                                                $defaultImage = ($record['sex'] === 'Female')
                                                                    ? '../public/assets/image/default_women.png'
                                                                    : '../public/assets/image/default_men.png';
                                                            ?>
                                                            <img src="<?= $defaultImage ?>" alt="Default Photo" class="h-10 w-10 rounded-full object-cover">
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <?php if (!empty($record['m_photo_path'])): ?>
                                                            <img src="../public/<?= htmlspecialchars($record['m_photo_path']) ?>" alt="Photo" class="h-10 w-10 rounded-full object-cover">
                                                    <?php else: ?>
                                                        <?php
                                                                $defaultImage = ($record['m_sex'] === 'Female')
                                                                ? '../public/assets/image/default_women.png'
                                                                : '../public/assets/image/default_men.png';
                                                        ?>
                                                        <img src="<?= $defaultImage ?>" alt="Default Photo" class="h-10 w-10 rounded-full object-cover">
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-2 align-middle">
                                                    <?php if ($record['record_type'] === 'employee'): ?>
                                                        <?= htmlspecialchars(
                                                            ucwords(strtolower($record['first_name'])) . ' ' .
                                                            (!empty($record['middle_name']) ? strtoupper(substr($record['middle_name'], 0, 1)) . '. ' : '') .
                                                            ucwords(strtolower($record['last_name']))
                                                        ) ?>
                                                    <?php else: ?>
                                                    <?= htmlspecialchars(
                                                            ucwords(strtolower($record['m_first_name'])) . ' ' .
                                                            (!empty($record['m_middle_name']) ? strtoupper(substr($record['m_middle_name'], 0, 1)) . '. ' : '') .
                                                            ucwords(strtolower($record['m_last_name']))
                                                        ) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-2 align-middle">
                                                    <?= $record['record_type'] === 'employee' ? htmlspecialchars($record['employee_no']) : htmlspecialchars($record['m_employee_id']) ?>
                                                </td>
                                                <td class="px-3 py-2 align-middle">
                                                    <?= $record['record_type'] === 'employee' ? htmlspecialchars($record['rfid_number']) : htmlspecialchars($record['m_rfid_number']) ?>
                                                </td>
                                                <td class="px-3 py-2 align-middle text-center">
                                                    <?php 
                                                        $position = $record['record_type'] === 'employee' ? $record['position'] : $record['m_position'];
                                                        $bgColor = '';
                                                        switch ($position) {
                                                            case 'Manager':
                                                                $bgColor = 'bg-green-600 text-white';
                                                                break;
                                                            case 'Human Resources':
                                                                $bgColor = 'bg-blue-600 text-white';
                                                                break;
                                                            case 'Staff':
                                                                $bgColor = 'bg-yellow-600 text-white';
                                                                break;
                                                            case 'Driver':
                                                                $bgColor = 'bg-red-600 text-white';
                                                                break;
                                                            default:
                                                                $bgColor = 'bg-gray-500 text-white';
                                                                break;
                                                        }
                                                    ?>
                                                    <div class="inline-flex items-center rounded-full border border-transparent <?= $bgColor ?> px-2.5 py-0.5 text-xs font-semibold">
                                                        <?= htmlspecialchars($position) ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2 align-middle text-center">
                                                    <div class="flex justify-center items-center gap-2">
                                                        <!-- View Details Button -->
                                                        <button 
                                                            type="button"
                                                            class="view-deleted-record inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-blue-500 hover:text-white" 
                                                            title="View Details"
                                                            data-id="<?= $record['id'] ?>"
                                                            data-type="<?= $record['record_type'] ?>"
                                                        >
                                                            <i class="bi bi-eye"></i>
                                                        </button>

                                                        <!-- Restore Button -->
                                                            <button 
                                                                type="button"
                                                                class="restore-button inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                                                                title="Restore"
                                                            data-id="<?= $record['id'] ?>"
                                                            data-type="<?= $record['record_type'] ?>"
                                                            >
                                                                <i class="fas fa-trash-restore"></i>
                                                            </button>

                                                        <!-- Delete Permanently Button -->
                                                            <button 
                                                                type="button"
                                                                class="delete-btn inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-red-600 hover:text-white"
                                                                title="Delete Permanently"
                                                            data-id="<?= $record['id'] ?>"
                                                            data-type="<?= $record['record_type'] ?>"
                                                            >
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="px-4 py-6 text-center text-muted fst-italic bg-light">
                                                <i class="bi bi-trash fs-4 me-2"></i> No deleted records found.
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
    </div>
</div>
</main>

<!-- View Deleted Employee Modal -->
<div class="modal fade" id="viewDeletedEmployeeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewDeletedEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-danger text-lg fw-semibold">
                    <i class="bi bi-info-circle me-2"></i>Deleted Employee Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="text-danger small mb-4">View complete deleted employee information</p>
                <input type="hidden" name="id" id="deletedEmployeeId">

                <div class="d-flex flex-column flex-md-row gap-4">
                    <div class="ml-4 mr-3">
                        <div class="rounded-circle border-2 border-danger mt-[25px]" style="width: 125px; height: 125px; overflow: hidden;">
                            <img id="view_deletedEmployeePhoto" src="assets/image/default_user_image.svg" alt="Deleted Employee Photo" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    </div>

                    <div class="flex-grow-1">
                        <h4 id="deletedEmployeeName" class="fw-bold mb-3 text-lg text-danger">Loading...</h4>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="hidden" id="view_deleted_employee_id" name="employee_id">

                                <p><strong class="text-danger text-sm">Employee ID:</strong> <span class="text-sm" id="deletedEmployeeIdView"></span></p>
                                <p><strong class="text-danger text-sm">Blood Type:</strong> <span class="text-sm" id="deletedEmployeeBloodType"></span></p>
                                <p><strong class="text-danger text-sm">Civil Status:</strong> <span class="text-sm" id="deletedEmployeeCivilStatus"></span></p>
                                <p><strong class="text-danger text-sm">Birthday:</strong> <span class="text-sm" id="deletedEmployeeBirthday"></span></p>
                                <p><strong class="text-danger text-sm">Sex:</strong> <span class="text-sm" id="deletedEmployeeSex"></span></p>
                                <p><strong class="text-danger text-sm">Citizenship:</strong> <span class="text-sm" id="deletedEmployeeCitizen"></span></p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <p><strong class="text-danger text-sm">RFID Number:</strong> <span class="text-sm" id="deletedEmployeeRFID"></span></p>
                                <p><strong class="text-danger text-sm">Position:</strong> <span class="text-sm" id="deletedEmployeePosition"></span></p>
                                <p><strong class="text-danger text-sm">Email:</strong> <span class="text-sm" id="deletedEmployeeEmail"></span></p>
                                <p><strong class="text-danger text-sm">Phone:</strong> <span class="text-sm" id="deletedEmployeePhone"></span></p>
                                <p><strong class="text-danger text-sm">Place of Birth:</strong> <span class="text-sm" id="deletedEmployeePlaceOfBirth"></span></p>
                                <p><strong class="text-danger text-sm">Branch Manager:</strong> <span class="text-sm" id="deletedEmployeeBranch"></span></p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="text-danger text-sm"><strong>Salary Information</strong></h6>
                                        <p><strong class="text-danger text-sm">Base Salary:</strong> &#8369;<span class="text-sm" id="deletedEmployeeSalary"></span></p>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="text-danger text-sm"><strong>Accounts Information</strong></h6>
                                        <p><strong class="text-danger text-sm">SSS:</strong> <span class="text-sm" id="deletedEmployeeSSS"></span></p>
                                        <p><strong class="text-danger text-sm">Pag-IBIG:</strong> <span class="text-sm" id="deletedEmployeePagibig"></span></p>
                                        <p><strong class="text-danger text-sm">Philhealth:</strong> <span class="text-sm" id="deletedEmployeePhilhealth"></span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="rowmt-3">
                                    <div class="col-12">
                                        <h6 class="text-danger mt-3 text-sm"><strong>Address</strong></h6>
                                        <p id="deletedEmployeeAddress" class="text-sm"></p>
                                    </div>
                                </div>
                            </div>

                        </div> <!-- end row -->
                    </div> <!-- end flex-grow-1 -->
                </div> <!-- end d-flex -->
            </div> <!-- end modal-body -->

            <!-- 
            <div class="d-flex justify-content-end mt-3 mb-2 mr-2">
                <form method="POST" action="index.php?payroll=delete_history" style="display:inline;">
                    <input type="hidden" name="id" id="restore_id" value="">
                    <button type="submit" class="btn btn-success me-2 w-[90px]" title="Restore Employee">
                        <i class="fas fa-trash-restore me-2"></i>
                    </button>
                </form>

                <form method="POST" action="delete_employee_permanent.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete this employee?');">
                    <input type="hidden" name="id" id="delete_id" value="">
                    <button type="submit" class="btn btn-danger w-[90px]" title="Delete Permanently">
                        <i class="bi bi-trash me-1"></i>
                    </button>
                </form>
            </div> 
            -->

        </div> <!-- end modal-content -->
    </div> <!-- end modal-dialog -->
</div> <!-- end modal -->


<script>
document.addEventListener('DOMContentLoaded', () => {
    bindRestoreAndDeleteButtons();

    const searchInput = document.getElementById("delete_searchInput");
    const clearBtn = document.getElementById("delete_clearButton");
    const tbody = document.getElementById("delete_employeeTable");

    let debounce;

    toggleClearButton();
    filterTable();

    searchInput.addEventListener("input", () => {
        toggleClearButton();
        clearTimeout(debounce);
        debounce = setTimeout(filterTable, 300);
    });

    clearBtn.addEventListener("click", () => {
        searchInput.value = "";
        toggleClearButton();
        filterTable();
    });

    function toggleClearButton() {
        clearBtn.classList.toggle("hidden", searchInput.value.trim() === "");
    }

    function filterTable() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const rows = tbody.querySelectorAll("tr");
        let visibleCount = 0;

        rows.forEach(row => {
            if (row.id === "noResultRow") return;

            const nameCell = row.querySelector("td:nth-child(3)");
            const empNoCell = row.querySelector("td:nth-child(4)");
            if (!nameCell || !empNoCell) return;

            const name = nameCell.textContent.trim().toLowerCase();
            const empNo = empNoCell.textContent.trim().toLowerCase();
            const nameParts = name.split(" ");

            const matches =
                name.includes(searchTerm) ||
                empNo.includes(searchTerm) ||
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
                    <td colspan="7" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
                        <i class="bi bi-trash fs-4 me-2"></i> No deleted records found.
                    </td>`;
                tbody.appendChild(noRow);
            }
        } else {
            if (noRow) noRow.remove();
        }
    }

    window.applyFilter = filterTable;
});

function bindRestoreAndDeleteButtons() {
    // Restore Button
    document.querySelectorAll('.restore-button').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const row = this.closest('tr');
            const recordType = this.dataset.type;

            Swal.fire({
                title: 'Restore Record?',
                text: "This will restore the record's data.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#478547',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Confirm'
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Record Restored!',
                        showConfirmButton: false,
                        timer: 1200
                    }).then(() => {
                        row.style.transition = 'opacity 0.5s ease';
                        row.style.opacity = 0;

                        setTimeout(() => {
                            fetch('../app/api/delete_history-api.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ restore_id: id, record_type: recordType })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'restored') {
                                    updateTable(data.html);
                                } else {
                                    Swal.fire('Error', data.error || 'Unexpected error', 'error');
                                }
                            })
                            .catch(err => {
                                console.error('Fetch failed:', err);
                                Swal.fire('Error', 'Request failed.', 'error');
                            });
                        }, 500);
                    });
                }
            });
        });
    });

    // Delete Button
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const row = this.closest('tr');
            const recordType = this.dataset.type;

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Confirm'
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Record Deleted!',
                        showConfirmButton: false,
                        timer: 1200
                    }).then(() => {
                        row.style.transition = 'opacity 0.5s ease';
                        row.style.opacity = 0;

                        setTimeout(() => {
                            fetch('../app/api/delete_history-api.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ delete_id: id, record_type: recordType })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'deleted') {
                                    updateTable(data.html);
                                } else {
                                    Swal.fire('Error', data.error || 'Unexpected error', 'error');
                                }
                            })
                            .catch(err => {
                                console.error('Delete request failed:', err);
                                Swal.fire('Error', 'Request failed.', 'error');
                            });
                        }, 500);
                    });
                }
            });
        });
    });

    // View Button
    document.querySelectorAll('.view-deleted-record').forEach(button => {
        button.addEventListener('click', function () {
            const recordId = this.dataset.id;
            const recordType = this.dataset.type;

            fetch(`../app/api/delete_history-api.php?id=${recordId}&type=${recordType}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    function toTitleCase(str) {
                    return str
                        .toLowerCase()
                        .split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');
                    }
                    
                    if (recordType === 'employee') {
                    const lastName = (data.last_name || '').toUpperCase();
                    const firstName = (data.first_name || '').toUpperCase();
                    const middleName = (data.middle_name || '').toUpperCase();
                    const middleInitial = middleName ? middleName.charAt(0) + '.' : '';
                    const formattedName = `${lastName}, ${firstName} ${middleInitial}`.trim();

                    const formattedAddress = (data.address || '')
                        .toLowerCase()
                        .replace(/\b\w/g, c => c.toUpperCase());

                    const formatSSS = sss => sss?.replace(/^(\d{4})(\d{7})(\d{1})$/, '$1-$2-$3') || 'N/A';
                    const formatPagibig = pagibig => pagibig?.replace(/^(\d{4})(\d{4})(\d{4})$/, '$1-$2-$3') || 'N/A';
                    const formatPhilhealth = philhealth => philhealth?.replace(/^(\d{2})(\d{9})(\d{1})$/, '$1-$2-$3') || 'N/A';

                    document.getElementById('deletedEmployeeId').value = data.id || '';
                    document.getElementById('view_deleted_employee_id').value = data.employee_no || '';
                    document.getElementById('deletedEmployeeName').textContent = formattedName || 'N/A';
                    document.getElementById('deletedEmployeeIdView').textContent = data.employee_no || 'N/A';
                    document.getElementById('deletedEmployeeBloodType').textContent = data.blood_type || 'N/A';
                    document.getElementById('deletedEmployeeCivilStatus').textContent = data.civil_status || 'N/A';
                    document.getElementById('deletedEmployeeBirthday').textContent = data.dob || 'N/A';
                    document.getElementById('deletedEmployeeSex').textContent = data.sex || 'N/A';
                    document.getElementById('deletedEmployeeCitizen').textContent = (data.citizenship || 'N/A').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                    document.getElementById('deletedEmployeeRFID').textContent = data.rfid_number || 'N/A';
                    document.getElementById('deletedEmployeePosition').textContent = data.position || 'N/A';
                    document.getElementById('deletedEmployeeEmail').textContent = data.email || 'N/A';
                    document.getElementById('deletedEmployeePhone').textContent = data.contact_number || 'N/A';
                    document.getElementById('deletedEmployeePlaceOfBirth').textContent = (data.place_of_birth || 'N/A').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                    document.getElementById('deletedEmployeeBranch').textContent = (data.manager_name && data.manager_address) ? `${toTitleCase(data.manager_name)} - ${toTitleCase(data.manager_address)}`: 'N/A';
                    document.getElementById('deletedEmployeeAddress').textContent = formattedAddress || 'N/A';
                    document.getElementById('deletedEmployeeSalary').textContent = data.base_salary || '0.00';
                    document.getElementById('deletedEmployeeSSS').textContent = formatSSS(data.sss_number);
                    document.getElementById('deletedEmployeePagibig').textContent = formatPagibig(data.pagibig_number);
                    document.getElementById('deletedEmployeePhilhealth').textContent = formatPhilhealth(data.philhealth_number);

                    const photoElem = document.getElementById('view_deletedEmployeePhoto');
                    if (photoElem) {
                        if (data.photo_path && data.photo_path.trim() !== '') {
                            photoElem.src = data.photo_path;
                        } else {
                            photoElem.src = (data.sex && data.sex.toLowerCase() === 'female')
                                ? '../public/assets/image/default_women.png'
                                : '../public/assets/image/default_men.png';
                        }
                    }
                    } else {
                        // Manager data
                        const lastName = (data.m_last_name || '').toUpperCase();
                        const firstName = (data.m_first_name || '').toUpperCase();
                        const middleName = (data.m_middle_name || '').toUpperCase();
                        const middleInitial = middleName ? middleName.charAt(0) + '.' : '';
                        const formattedName = `${lastName}, ${firstName} ${middleInitial}`.trim();

                        const formattedAddress = (data.m_address || '')
                            .toLowerCase()
                            .replace(/\b\w/g, c => c.toUpperCase());

                        const formatSSS = sss => sss?.replace(/^(\d{4})(\d{7})(\d{1})$/, '$1-$2-$3') || 'N/A';
                        const formatPagibig = pagibig => pagibig?.replace(/^(\d{4})(\d{4})(\d{4})$/, '$1-$2-$3') || 'N/A';
                        const formatPhilhealth = philhealth => philhealth?.replace(/^(\d{2})(\d{9})(\d{1})$/, '$1-$2-$3') || 'N/A';

                        document.getElementById('deletedEmployeeId').value = data.id || '';
                        document.getElementById('view_deleted_employee_id').value = data.m_employee_id || '';
                        document.getElementById('deletedEmployeeName').textContent = formattedName || 'N/A';
                        document.getElementById('deletedEmployeeIdView').textContent = data.m_employee_id || 'N/A';
                        document.getElementById('deletedEmployeeBloodType').textContent = data.m_blood_type || 'N/A';
                        document.getElementById('deletedEmployeeCivilStatus').textContent = data.m_civil_status || 'N/A';
                        document.getElementById('deletedEmployeeBirthday').textContent = data.m_dob || 'N/A';
                        document.getElementById('deletedEmployeeSex').textContent = data.m_sex || 'N/A';
                        document.getElementById('deletedEmployeeCitizen').textContent = (data.m_citizenship || 'N/A').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                        document.getElementById('deletedEmployeeRFID').textContent = data.m_rfid_number || 'N/A';
                        document.getElementById('deletedEmployeePosition').textContent = data.m_position || 'N/A';
                        document.getElementById('deletedEmployeeEmail').textContent = data.m_email || 'N/A';
                        document.getElementById('deletedEmployeePhone').textContent = data.m_contact_number || 'N/A';
                        document.getElementById('deletedEmployeePlaceOfBirth').textContent = (data.m_place_of_birth || 'N/A').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                        document.getElementById('deletedEmployeeBranch').textContent = data.m_branch || 'N/A';
                        document.getElementById('deletedEmployeeAddress').textContent = formattedAddress || 'N/A';
                        document.getElementById('deletedEmployeeSalary').textContent = data.m_base_salary || '0.00';
                        document.getElementById('deletedEmployeeSSS').textContent = formatSSS(data.m_sss_number);
                        document.getElementById('deletedEmployeePagibig').textContent = formatPagibig(data.m_pagibig_number);
                        document.getElementById('deletedEmployeePhilhealth').textContent = formatPhilhealth(data.m_philhealth_number);

                        const photoElem = document.getElementById('view_deletedEmployeePhoto');
                        if (photoElem) {
                            if (data.m_photo_path && data.m_photo_path.trim() !== '') {
                                photoElem.src = '../public/' + data.m_photo_path;
                            } else {
                                photoElem.src = (data.m_sex && data.m_sex.toLowerCase() === 'female')
                                    ? '../public/assets/image/default_women.png'
                                    : '../public/assets/image/default_men.png';
                            }
                        }
                    }

                    const restoreInput = document.getElementById('restore_id');
                    const deleteInput = document.getElementById('delete_id');
                    if (restoreInput) restoreInput.value = data.id || '';
                    if (deleteInput) deleteInput.value = data.id || '';

                    const deletedModal = new bootstrap.Modal(document.getElementById('viewDeletedEmployeeModal'));
                    deletedModal.show();
                })
                .catch(error => {
                    console.error('Failed to load deleted record data:', error);
                    Swal.fire('Error', 'Failed to load record details.', 'error');
                });
        });
    });
}

function updateTable(html) {
    const tbody = document.getElementById('delete_employeeTable');
    tbody.innerHTML = html;
    bindRestoreAndDeleteButtons();
    if (typeof applyFilter === 'function') applyFilter();
}
</script>








<?php require_once views_path("partials/footer"); ?>

<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

<?php if (isset($_GET['status']) && $_GET['status'] === 'restored'): ?>
<script>
Swal.fire({
    toast: true,
    icon: 'success',
    title: 'Employee restored successfully',
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});
if (window.history.replaceState) {
    const url = new URL(window.location.href);
    url.searchParams.delete('status');
    window.history.replaceState({}, document.title, url.toString());
}
</script>
<?php endif; ?>

<?php if (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
<script>
Swal.fire({
    toast: true,
    icon: 'success',
    title: 'Employee permanently deleted',
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});
if (window.history.replaceState) {
    const url = new URL(window.location.href);
    url.searchParams.delete('status');
    window.history.replaceState({}, document.title, url.toString());
}
</script>
<?php endif; ?>




