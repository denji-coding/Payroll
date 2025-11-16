<?php
$title = "HR Management";
require_once views_path("partials/header");
require_once views_path("owner/owner_sidebar");

echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Fetch all HR records
    $sql = "SELECT 
        h.*,
        CONCAT(
            UPPER(LEFT(h.hr_first_name, 1)), LOWER(SUBSTRING(h.hr_first_name FROM 2)), ' ',
            IFNULL(CONCAT(UPPER(LEFT(h.hr_middle_name, 1)), '. '), ''),
            UPPER(LEFT(h.hr_last_name, 1)), LOWER(SUBSTRING(h.hr_last_name FROM 2))
        ) AS full_name
    FROM admins h 
    WHERE h.deleted_at IS NULL 
    ORDER BY h.hr_created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $hrList = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $hrList = [];
}
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

@keyframes fadeOut {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-8px);
  }
}
.fade-out {
  animation: fadeOut 0.3s ease-in;
}

/* Dropdown styles */
.custom-dropdown-option {
    padding: 8px 12px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.custom-dropdown-option:hover {
    background-color: #f3f4f6;
}

.custom-dropdown-option.selected {
    background-color: #16a249;
    color: white;
}

.custom-dropdown-option.selected:hover {
    background-color: #15803d;
}

/* Add ellipsis for long text in dropdown buttons */
.dropdown-button-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
    text-align: left;
}

/* Dropdown visibility */
.hidden {
    display: none !important;
}

/* Ensure dropdown containers are positioned relatively */
[id$="dropdown-container"] {
    position: relative;
}

/* Ensure dropdown lists are properly styled */
.custom-dropdown-list {
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    z-index: 50;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    margin-top: 0.25rem;
    max-height: 15rem;
    overflow-y: auto;
}

/* Show dropdown when not hidden */
.custom-dropdown-list:not(.hidden) {
    display: block !important;
}
</style>

<main class="flex-1 h-[calc(100vh-3rem)] p-4 md:p-6 ml-[255px] min-h-screen bg-[#f8fbf8]">
    <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                        <span class="text-2xl font-bold tracking-tight text-[#133913]">HR Management</span>
                        <p class="text-[#478547]">Manage HR personnel</p>
                </div>
                
                <div>
                    <button id="showAddEmployeeModal" 
                            class="btn btn-success d-inline-flex align-items-center h-10 px-4 py-2 " 
                            data-bs-toggle="modal" 
                            data-bs-target="#addHrAccountModal">
                    <i class="fas fa-plus me-2"></i>
                    <span class="font-semibold">Add new HR</span>
                    </button>
                </div>
            </div>

        <div class="rounded-lg border-2 border-green-200 bg-white text-[#133913] shadow-sm">
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between">
                <span class="text-2xl font-semibold leading-none tracking-tight text-[#133913]">HR Directory</span>
                <div class="relative w-64">
                    <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="hr_searchInput"
                        class="flex h-10 w-full text-sm placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8  placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
                        placeholder="Search HR..."
                    >
                    <button id="hr_clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden" >×</button>
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
                                    <th class="h-12 px-3 align-middle font-bold text-center text-[#478547] bg-white">Position</th>
                                    <th class="h-12 px-3 text-center align-middle font-bold text-[#478547] bg-white">Actions</th>
                                </tr>
                            </thead>
                                <tbody id="hrTable" class="[&_tr:last-child]:border-0 min-h-[80px]">
                                    <?php if (!empty($hrList) && is_array($hrList)): ?>
                                        <?php $count = 1; ?>
                                        <?php foreach ($hrList as $hr): ?>
                                            <tr class="fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                                <td class="p-3 align-middle font-medium"><?= $count++ ?></td>

                                                <!-- Photo -->
                                                <td class="p-3 align-middle font-medium">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="relative flex shrink-0 overflow-hidden rounded-full h-12 w-12">
                                                            <?php if (!empty($hr['hr_photo_path'])): ?>
                                                                <img class="aspect-square h-full w-full object-cover"
                                                                src="../public/<?= htmlspecialchars($hr['hr_photo_path']) ?>"
                                                                alt="HR Photo">
                                                            <?php else: ?>
                                                                <?php
                                                                    $defaultImage = ($hr['hr_sex'] === 'Female')    
                                                                        ? '../public/assets/image/default_women.png'
                                                                        : '../public/assets/image/default_men.png';
                                                                ?>
                                                                <img class="aspect-square h-full w-full" src="<?= $defaultImage ?>" alt="Default Photo">
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                </td>

                                                <!-- Fullname -->
                                                <td class="p-3 align-middle font-medium">
                                                    <?= htmlspecialchars($hr['full_name'] ?? '') ?>
                                                </td>

                                                <!-- Employee ID -->
                                                <td class="p-3 align-middle"><?= htmlspecialchars($hr['hr_employee_id'] ?? '') ?></td>

                                                <!-- RFID -->
                                                <td class="p-3 align-middle"><?= htmlspecialchars($hr['hr_rfid_number'] ?? '') ?></td>

                                                <!-- Position -->
                                                <?php
                                                    $position = $hr['hr_position'] ?? '';
                                                    $bgColor = 'bg-blue-600 text-white'; // HR position is always blue
                                                ?>
                                                <td class="p-3 align-middle text-center">
                                                    <div class="inline-flex items-center rounded-full border border-transparent <?= $bgColor ?> px-2.5 py-0.5 text-xs font-semibold">
                                                        <?= htmlspecialchars($position) ?>
                                                    </div>
                                                </td>

                                                <!-- Actions -->
                                                <td class="p-3 align-middle text-right">
                                                    <div class="flex gap-2">
                                                        <!-- View -->
                                                        <button type="button"
                                                        class="viewHrBtn inline-flex h-8 w-8 items-center justify-center rounded-md font-medium transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                                                        data-hr='<?= json_encode($hr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'
                                                        data-hr-id="<?= $hr['id'] ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewHrModal">
                                                        <i class="bi bi-eye"></i>
                                                        </button>

                                                        <!-- Edit -->
                                                        <button type="button"
                                                        class="editHrBtn inline-flex h-8 w-8 items-center justify-center rounded-md font-medium transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                                                        data-hr='<?= json_encode($hr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'
                                                        data-hr-id="<?= $hr['id'] ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#updateHrAccountModal">
                                                        <i class="bi bi-pencil"></i>
                                                        </button>

                                                        <!-- Delete -->
                                                        <button type="button"
                                                        class="deleteHrBtn inline-flex h-8 w-8 items-center justify-center rounded-md font-medium transition duration-100 transform hover:scale-105 hover:bg-red-600 hover:text-white"
                                                        data-hr-id="<?= $hr['id'] ?>">
                                                        <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="p-4 text-center italic text-gray-500 bg-[#f0fdf4]">
                                                <i class="bi bi-person-x me-2"></i> No HR found.
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
</main>

<!-- View HR Modal -->
<div class="modal fade" id="viewHrModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewHrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title text-success text-lg fw-semibold">
          <i class="bi bi-info-circle me-2"></i>HR Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal">
        <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <p class="text-success small mb-4">View complete HR information</p>

        <!-- 🔒 Hidden Inputs for future actions like Edit/Delete -->
        <input type="hidden" id="hr_id" name="id">

        <div class="d-flex flex-column flex-md-row gap-4">
          <!-- HR Photo -->
          <div class="ml-4 mr-3">
            <div class="rounded-circle border-2 border-green-800 mt-[25px]" style="width: 125px; height: 125px; overflow: hidden;">
              <img id="view_hrPhoto" src="assets/image/default_user_image.svg" alt="HR Photo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
          </div>

          <!-- HR Info -->
          <div class="flex-grow-1">
            <h4 id="hrName" class="fw-bold mb-3 text-lg text-[#133913]">Loading...</h4>

            <div class="row">
              <!-- Column 1 -->
              <div class="col-md-6 mb-2">
                <p><strong class="text-success text-sm">Employee ID:</strong> <span class="text-sm" id="hrIdView"></span></p>
                <p><strong class="text-success text-sm">Blood Type:</strong> <span class="text-sm" id="hrBloodType"></span></p>
                <p><strong class="text-success text-sm">Civil Status:</strong> <span class="text-sm" id="hrCivilStatus"></span></p>
                <p><strong class="text-success text-sm">Birthday:</strong> <span class="text-sm" id="hrBirthday"></span></p>
                <p><strong class="text-success text-sm">Sex:</strong> <span class="text-sm" id="hrSex"></span></p>
                <p><strong class="text-success text-sm">Citizenship:</strong> <span class="text-sm" id="hrCitizen"></span></p>
              </div>

              <!-- Column 2 -->
              <div class="col-md-6 mb-2">
                <p><strong class="text-success text-sm">RFID Number:</strong> <span class="text-sm" id="hrRFID"></span></p>
                <p><strong class="text-success text-sm">Position:</strong> <span class="text-sm" id="hrPosition"></span></p>
                <p><strong class="text-success text-sm">Email:</strong> <span class="text-sm" id="hrEmail"></span></p>
                <p><strong class="text-success text-sm">Phone:</strong> <span class="text-sm" id="hrPhone"></span></p>
                <p><strong class="text-success text-sm">Place of Birth:</strong> <span class="text-sm" id="hrPlaceOfBirth"></span></p>
              </div>

              <!-- Column 3 -->
              <div class="col-md-6 mb-2">
                <div class="row mt-3">
                  <div class="col-12">
                    <h6 class="text-success text-sm"><strong>Salary Information</strong></h6>
                    <p><strong class="text-success text-sm">Base Salary:</strong> &#8369;<span class="text-sm" id="hrSalary"></span></p>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-12">
                    <h6 class="text-success text-sm"><strong>Accounts Information</strong></h6>
                    <p><strong class="text-success text-sm">SSS:</strong> <span class="text-sm" id="hrSSS"></span></p>
                    <p><strong class="text-success text-sm">Pag-IBIG:</strong> <span class="text-sm" id="hrPagibig"></span></p>
                    <p><strong class="text-success text-sm">Philhealth:</strong> <span class="text-sm" id="hrPhilhealth"></span></p>
                  </div>
                </div>
              </div>

              <!-- Column 4 -->
              <div class="col-md-6 mb-2">
                <div class="row mt-3">
                  <div class="col-12">
                    <h6 class="text-success text-sm"><strong>Address</strong></h6>
                    <p id="hrAddress" class="text-sm"></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Buttons -->
        <!-- <div class="d-flex justify-content-end mt-3 mb-2 mr-2">
           <button
                type="button"
                class="btn btn-outline-success me-2 w-[90px] editHrBtn"
                data-bs-toggle="modal"
                data-bs-target="#updateHrAccountModal"
                onclick='editHrFromObject(window.lastViewedHr)'
                >
                <i class="bi bi-pencil me-2"></i>Edit
           </button>

            <button type="button" id="modalDeleteHrBtn" class="btn btn-danger deleteHrBtn" data-id="">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
        </div> -->
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // Utility functions
  const capitalize = str =>
    str ? str.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()) : '';

  const formatSSS = sss =>
    sss && sss.length === 12 ? `${sss.slice(0, 2)}-${sss.slice(2, 9)}-${sss.slice(9)}` : sss || 'N/A';

  const formatPagibig = pagibig =>
    pagibig && pagibig.length === 12 ? `${pagibig.slice(0, 4)}-${pagibig.slice(4, 8)}-${pagibig.slice(8)}` : pagibig || 'N/A';

  const formatPhilhealth = ph =>
    ph && ph.length === 12 ? `${ph.slice(0, 2)}-${ph.slice(2, 10)}-${ph.slice(10)}` : ph || 'N/A';

  // Generate HR Employee ID
  function generateHrId() {
    const prefix = 'EMP-';
    const randomNum = Math.floor(Math.random() * 900000) + 100000;
    return prefix + randomNum;
  }

  // Generate ID button functionality
  document.getElementById('generateHrIdBtn')?.addEventListener('click', function() {
    const hrEmployeeIdInput = document.getElementById('hrEmployeeId');
    if (hrEmployeeIdInput) {
      hrEmployeeIdInput.value = generateHrId();
    }
  });

  // Add HR form submission
  document.getElementById('hrsaveBtn')?.addEventListener('click', function() {
    const form = document.getElementById('addHrForm');
    if (!form) return;

    // Debug form data before submission
    console.log('Form submission debug:');
    const positionValue = document.getElementById('hrPosition').value;
    const positionSelected = document.getElementById('hrPositionDropdownSelected').textContent;
    console.log('Position hidden value:', positionValue);
    console.log('Position selected text:', positionSelected);
    
    // Check all dropdown values and ensure they're set for form submission
    const dropdowns = [
      { name: 'position', hidden: 'hrPosition', selected: 'hrPositionDropdownSelected' },
      { name: 'sex', hidden: 'hrSex', selected: 'hrSexDropdownSelected' },
      { name: 'civilStatus', hidden: 'hrCivilStatus', selected: 'hrCivilStatusDropdownSelected' },
      { name: 'citizenship', hidden: 'hrCitizenship', selected: 'hrCitizenshipDropdownSelected' },
      { name: 'bloodType', hidden: 'hrBloodType', selected: 'hrBloodTypeDropdownSelected' }
    ];
    
    dropdowns.forEach(dropdown => {
      const hiddenEl = document.getElementById(dropdown.hidden);
      const selectedEl = document.getElementById(dropdown.selected);
      console.log(`${dropdown.name}: hidden="${hiddenEl ? hiddenEl.value : 'NOT FOUND'}", selected="${selectedEl ? selectedEl.textContent : 'NOT FOUND'}"`);
      
      // Ensure the value is properly set
      if (hiddenEl && hiddenEl.value) {
        console.log(`Ensuring ${dropdown.name} value: ${hiddenEl.value}`);
        hiddenEl.setAttribute('value', hiddenEl.value);
      } else {
        console.log(`WARNING: ${dropdown.name} hidden input not found or empty`);
      }
    });

    const formData = new FormData(form);
    
    // Manually append dropdown values to FormData if they're missing
    dropdowns.forEach(dropdown => {
      const hiddenEl = document.getElementById(dropdown.hidden);
      if (hiddenEl && hiddenEl.value) {
        // Check if the value is already in FormData
        const existingValue = formData.get(dropdown.name);
        if (!existingValue || existingValue === '') {
          console.log(`Manually appending ${dropdown.name}: ${hiddenEl.value}`);
          formData.set(dropdown.name, hiddenEl.value);
        }
      }
    });
    
    // Debug FormData contents
    console.log('FormData contents after manual append:');
    for (let [key, value] of formData.entries()) {
      console.log(`${key}: ${value}`);
    }
    
    fetch('../app/api/hr_account-api.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: data.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.message
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Something went wrong!'
      });
    });
  });

  // View HR functionality
  document.querySelectorAll(".viewHrBtn").forEach(button => {
    button.addEventListener("click", () => {
      try {
        const data = JSON.parse(button.dataset.hr || '{}');
        
        const defaultImg = data.hr_sex?.toLowerCase() === 'female'
          ? '../public/assets/image/default_women.png'
          : '../public/assets/image/default_men.png';

        const img = document.getElementById("view_hrPhoto");
        if (img) img.src = data.hr_photo_path ? "../public/" + data.hr_photo_path : defaultImg;

        document.getElementById("hrName").textContent = data.full_name || 'N/A';
        document.getElementById("hrIdView").textContent = data.hr_employee_id || 'N/A';
        document.getElementById("hrBloodType").textContent = data.hr_blood_type || 'N/A';
        document.getElementById("hrCivilStatus").textContent = data.hr_civil_status || 'N/A';
        document.getElementById("hrBirthday").textContent = data.hr_dob || 'N/A';
        document.getElementById("hrSex").textContent = data.hr_sex || 'N/A';
        document.getElementById("hrCitizen").textContent = capitalize(data.hr_citizenship) || 'N/A';
        document.getElementById("hrRFID").textContent = data.hr_rfid_number || 'N/A';
        document.getElementById("hrPosition").textContent = data.hr_position || 'N/A';
        document.getElementById("hrEmail").textContent = data.hr_email || 'N/A';
        document.getElementById("hrPhone").textContent = data.hr_contact_number || 'N/A';
        document.getElementById("hrPlaceOfBirth").textContent = capitalize(data.hr_place_of_birth) || 'N/A';
        document.getElementById("hrSalary").textContent = parseFloat(data.hr_base_salary || 0).toFixed(2);
        document.getElementById("hrSSS").textContent = formatSSS(data.hr_sss_number);
        document.getElementById("hrPagibig").textContent = formatPagibig(data.hr_pagibig_number);
        document.getElementById("hrPhilhealth").textContent = formatPhilhealth(data.hr_philhealth_number);
        document.getElementById("hrAddress").textContent = capitalize(data.hr_address) || 'N/A';

        window.lastViewedHr = data;
        
        const hrIdInput = document.getElementById("hr_id");
        if (hrIdInput) hrIdInput.value = data.id || '';
        
        const deleteBtn = document.getElementById("modalDeleteHrBtn");
        if (deleteBtn) {
          deleteBtn.setAttribute("data-id", data.id || '');
        }
      } catch (e) {
        console.error("❌ Error displaying view modal:", e);
      }
    });
  });

  // Edit HR functionality
  document.querySelectorAll(".editHrBtn").forEach(button => {
    button.addEventListener("click", () => {
      try {
        const data = JSON.parse(button.dataset.hr || '{}');
        
        // Populate edit form
        document.getElementById('h_edit_hrId').value = data.id || '';
        document.getElementById('h_edit_hr_id').value = data.hr_employee_id || '';
        document.getElementById('h_edit_first_name').value = data.hr_first_name || '';
        document.getElementById('h_edit_middle_name').value = data.hr_middle_name || '';
        document.getElementById('h_edit_last_name').value = data.hr_last_name || '';
        document.getElementById('h_edit_email').value = data.hr_email || '';
        document.getElementById('h_edit_position').value = data.hr_position || '';
        document.getElementById('h_edit_rfidNumber').value = data.hr_rfid_number || '';
        document.getElementById('h_edit_dob').value = data.hr_dob || '';
        document.getElementById('h_edit_placeOfBirth').value = data.hr_place_of_birth || '';
        document.getElementById('h_edit_sex').value = data.hr_sex || '';
        document.getElementById('h_edit_civilStatus').value = data.hr_civil_status || '';
        document.getElementById('h_edit_contactNumber').value = data.hr_contact_number || '';
        document.getElementById('h_edit_citizenship').value = data.hr_citizenship || '';
        document.getElementById('h_edit_bloodType').value = data.hr_blood_type || '';
        document.getElementById('h_edit_address').value = data.hr_address || '';
        document.getElementById('h_edit_baseSalary').value = data.hr_base_salary || '';
        document.getElementById('h_edit_sssNumber').value = data.hr_sss_number || '';
        document.getElementById('h_edit_pagibigNumber').value = data.hr_pagibig_number || '';
        document.getElementById('h_edit_philhealthNumber').value = data.hr_philhealth_number || '';
        document.getElementById('h_existing_photo').value = data.hr_photo_path || '';

        // Set dropdown values
        setDropdownValue('editHrPositionDropdownSelected', 'h_edit_position', data.hr_position);
        setDropdownValue('editHrSexDropdownSelected', 'h_edit_sex', data.hr_sex);
        setDropdownValue('editHrCivilStatusDropdownSelected', 'h_edit_civilStatus', data.hr_civil_status);
        setDropdownValue('editHrCitizenshipDropdownSelected', 'h_edit_citizenship', data.hr_citizenship);
        setDropdownValue('editHrBloodTypeDropdownSelected', 'h_edit_bloodType', data.hr_blood_type);

        // Set photo preview
        if (data.hr_photo_path) {
          document.getElementById('edit_hrPhotoPreview').src = '../public/' + data.hr_photo_path;
          document.getElementById('edit_hrPhotoPreview').style.display = 'block';
          document.getElementById('edit_photoPlaceholder').style.display = 'none';
        }

      } catch (e) {
        console.error("❌ Error populating edit form:", e);
      }
    });
  });

  // Delete HR functionality
  document.querySelectorAll(".deleteHrBtn").forEach(button => {
    button.addEventListener("click", function() {
      const hrId = this.getAttribute("data-hr-id");
      
      if (!hrId) {
        Swal.fire({ 
          icon: "error", 
          title: "Error", 
          text: "HR ID not found." 
        });
        return;
      }

      Swal.fire({
        title: "Are you sure?",
        text: "This will move the HR to delete history. You can restore them later.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, move to history!"
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append("id", hrId);
          formData.append("action", "soft_delete");

          fetch("../app/api/hr_account-api.php", {
            method: "POST",
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === "success") {
              Swal.fire({
                icon: "success",
                title: "Moved to History!",
                text: data.message,
                timer: 1500,
                showConfirmButton: false
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({ icon: "error", title: "Delete failed", text: data.message });
            }
          })
          .catch(err => {
            console.error("❌ Error during delete:", err);
            Swal.fire({
              icon: "error",
              title: "Oops...",
              text: "Something went wrong while deleting the HR."
            });
          });
        }
      });
    });
  });

  // Search functionality
  const searchInput = document.getElementById("hr_searchInput");
  const clearBtn = document.getElementById("hr_clearButton");
  const tbody = document.getElementById("hrTable");

  let debounce;

  function toggleClearButton() {
    if (clearBtn) {
      clearBtn.classList.toggle("hidden", searchInput.value.trim() === "");
    }
  }

  function filterTable() {
    if (!searchInput || !tbody) return;
    
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
            <i class="bi bi-person-x fs-4 me-2"></i> No HR found.
          </td>`;
        tbody.appendChild(noRow);
      }
    } else {
      if (noRow) noRow.remove();
    }
  }

  if (searchInput && clearBtn) {
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
  }

  // Helper function to set dropdown values
  function setDropdownValue(selectedId, hiddenId, value) {
    const selectedElement = document.getElementById(selectedId);
    const hiddenElement = document.getElementById(hiddenId);
    
    if (selectedElement && hiddenElement && value) {
      selectedElement.textContent = value;
      hiddenElement.value = value;
      
      // Also update the visual state of the option in the dropdown list
      // Find the dropdown list by getting the parent container
      const container = selectedElement.closest('.relative');
      if (container) {
        const dropdownList = container.querySelector('.custom-dropdown-list');
        if (dropdownList) {
          // Remove active class from all options
          dropdownList.querySelectorAll('.custom-dropdown-option').forEach(option => {
            option.classList.remove('bg-[#16a249]', 'text-white');
          });
          
          // Add active class to the matching option
          const matchingOption = Array.from(dropdownList.querySelectorAll('.custom-dropdown-option')).find(
            option => option.getAttribute('data-value') === value
          );
          
          if (matchingOption) {
            matchingOption.classList.add('bg-[#16a249]', 'text-white');
          }
        }
      }
    }
  }

  // Photo preview functions
  window.previewHrPhoto = function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('hrsPhotoPreview');
    const placeholder = document.getElementById('photoPlaceholder');
    
    if (file && preview) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
      };
      reader.readAsDataURL(file);
    }
  };

  window.editpreviewHrPhoto = function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('edit_hrPhotoPreview');
    const placeholder = document.getElementById('edit_photoPlaceholder');
    
    if (file && preview) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
      };
      reader.readAsDataURL(file);
    }
  };

  window.displayFileName = function(input) {
    const fileName = document.getElementById('photoFileName');
    if (fileName && input.files[0]) {
      fileName.textContent = input.files[0].name;
    }
  };

  window.editdisplayFileName = function(input) {
    const fileName = document.getElementById('edit_photoFileName');
    if (fileName && input.files[0]) {
      fileName.textContent = input.files[0].name;
    }
  };

  // Validation functions
  window.validateRfidNumber = function(input) {
    const value = input.value.replace(/\D/g, '');
    input.value = value;
  };

  window.validateContactNumber = function(input) {
    const value = input.value.replace(/\D/g, '');
    input.value = value;
  };

  window.validateInput = function(input) {
    // Basic validation - can be expanded
    return true;
  };

  // Dropdown functionality for HR forms
  function initializeDropdowns() {
    // Position dropdowns
    initializeDropdown('hrPositionDropdownBtn', 'hrPositionDropdownList', 'hrPositionDropdownSelected', 'hrPosition');
    initializeDropdown('editHrPositionDropdownBtn', 'editHrPositionDropdownList', 'editHrPositionDropdownSelected', 'h_edit_position');
    
    // Sex dropdowns
    initializeDropdown('hrSexDropdownBtn', 'hrSexDropdownList', 'hrSexDropdownSelected', 'hrSex');
    initializeDropdown('editHrSexDropdownBtn', 'editHrSexDropdownList', 'editHrSexDropdownSelected', 'h_edit_sex');
    
    // Civil Status dropdowns
    initializeDropdown('hrCivilStatusDropdownBtn', 'hrCivilStatusDropdownList', 'hrCivilStatusDropdownSelected', 'hrCivilStatus');
    initializeDropdown('editHrCivilStatusDropdownBtn', 'editHrCivilStatusDropdownList', 'editHrCivilStatusDropdownSelected', 'h_edit_civilStatus');
    
    // Citizenship dropdowns
    initializeDropdown('hrCitizenshipDropdownBtn', 'hrCitizenshipDropdownList', 'hrCitizenshipDropdownSelected', 'hrCitizenship');
    initializeDropdown('editHrCitizenshipDropdownBtn', 'editHrCitizenshipDropdownList', 'editHrCitizenshipDropdownSelected', 'h_edit_citizenship');
    
    // Blood Type dropdowns
    initializeDropdown('hrBloodTypeDropdownBtn', 'hrBloodTypeDropdownList', 'hrBloodTypeDropdownSelected', 'hrBloodType');
    initializeDropdown('editHrBloodTypeDropdownBtn', 'editHrBloodTypeDropdownList', 'editHrBloodTypeDropdownSelected', 'h_edit_bloodType');
  }

  function initializeDropdown(btnId, listId, selectedId, hiddenId) {
    const btn = document.getElementById(btnId);
    const list = document.getElementById(listId);
    const selected = document.getElementById(selectedId);
    const hidden = document.getElementById(hiddenId);
    
    if (!btn || !list || !selected || !hidden) {
      console.log(`Missing elements for dropdown: ${btnId}, ${listId}, ${selectedId}, ${hiddenId}`);
      return;
    }

    // Check if already initialized to prevent duplicates
    if (btn.dataset.initialized === 'true') {
      console.log(`Dropdown already initialized: ${btnId}`);
      return;
    }

    console.log(`Initializing dropdown: ${btnId}`);
    console.log(`Button element:`, btn);
    console.log(`List element:`, list);
    console.log(`Selected element:`, selected);
    console.log(`Hidden element:`, hidden);

    // Mark as initialized
    btn.dataset.initialized = 'true';

    // Ensure dropdown is initially hidden with important style
    list.style.setProperty('display', 'none', 'important');

    // Remove any existing event listeners and add new one
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    newBtn.dataset.initialized = 'true';

    // Add debounce to prevent rapid clicking
    let clickTimeout;
    
    newBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      // Clear any existing timeout
      if (clickTimeout) {
        clearTimeout(clickTimeout);
        return;
      }
      
      console.log(`Clicked dropdown button: ${btnId}`);
      console.log(`Current dropdown display: ${list.style.display}`);
      
      // Close all other dropdowns first
      document.querySelectorAll('.custom-dropdown-list').forEach(dropdown => {
        if (dropdown !== list) {
          dropdown.style.setProperty('display', 'none', 'important');
        }
      });
      
      // Toggle current dropdown - simplified logic
      const isHidden = list.style.display === 'none' || 
                      list.style.getPropertyValue('display') === 'none' ||
                      getComputedStyle(list).display === 'none';
      
      if (isHidden) {
        // Before showing, mark the currently selected value
        const currentSelected = document.getElementById(selectedId);
        const currentHidden = document.getElementById(hiddenId);
        if (currentHidden && currentHidden.value) {
          // Find and mark the currently selected option
          list.querySelectorAll('.custom-dropdown-option').forEach(option => {
            option.classList.remove('bg-[#16a249]', 'text-white');
            if (option.getAttribute('data-value') === currentHidden.value) {
              option.classList.add('bg-[#16a249]', 'text-white');
              console.log(`Marked option as selected: ${option.textContent}`);
            }
          });
        }
        
        list.style.setProperty('display', 'block', 'important');
        console.log(`Showing dropdown: ${listId}`);
      } else {
        list.style.setProperty('display', 'none', 'important');
        console.log(`Hiding dropdown: ${listId}`);
      }
      
      // Set timeout to prevent rapid clicking
      clickTimeout = setTimeout(() => {
        clickTimeout = null;
      }, 100);
    });

    // Handle option selection
    const options = list.querySelectorAll('.custom-dropdown-option');
    options.forEach(option => {
      option.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const value = this.getAttribute('data-value');
        const text = this.textContent.trim();
        
        console.log(`Selected option: ${text} (${value})`);
        console.log(`Updating selected element: ${selectedId}`);
        console.log(`Updating hidden element: ${hiddenId}`);
        
        // Get fresh references to elements (in case they changed after button cloning)
        const currentSelected = document.getElementById(selectedId);
        const currentHidden = document.getElementById(hiddenId);
        
        console.log(`Selected element before update:`, currentSelected);
        console.log(`Hidden element before update:`, currentHidden);
        console.log(`Selected text before update: "${currentSelected ? currentSelected.textContent : 'NOT FOUND'}"`);
        console.log(`Hidden value before update: "${currentHidden ? currentHidden.value : 'NOT FOUND'}"`);
        
        if (currentSelected && currentHidden) {
          // Update the display text
          currentSelected.textContent = text;
          // Update the hidden input value
          currentHidden.value = value;
          
          console.log(`Updated - Selected text: "${currentSelected.textContent}", Hidden value: "${currentHidden.value}"`);
        } else {
          console.log('ERROR: Could not find selected or hidden elements!');
        }
        
        // Close the dropdown
        list.style.setProperty('display', 'none', 'important');
        
        // Update visual state - remove all selections first
        options.forEach(opt => {
          opt.classList.remove('bg-[#16a249]', 'text-white', 'selected');
        });
        // Mark the selected option
        this.classList.add('bg-[#16a249]', 'text-white', 'selected');
        
        // Trigger change event for validation
        if (currentHidden) {
          currentHidden.dispatchEvent(new Event('change'));
        }
      });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!newBtn.contains(e.target) && !list.contains(e.target)) {
        list.style.setProperty('display', 'none', 'important');
      }
    });
  }

  // Function to close all dropdowns
  function closeAllDropdowns() {
    document.querySelectorAll('.custom-dropdown-list').forEach(dropdown => {
      dropdown.style.setProperty('display', 'none', 'important');
    });
  }

  // Function to reset all dropdowns to closed state
  function resetAllDropdowns() {
    console.log('Resetting all dropdowns...');
    document.querySelectorAll('.custom-dropdown-list').forEach(dropdown => {
      dropdown.style.setProperty('display', 'none', 'important');
    });
    
    // Reset all dropdown selected text to default
    const dropdownButtons = [
      { selected: 'hrPositionDropdownSelected', default: 'Select a position' },
      { selected: 'hrSexDropdownSelected', default: 'Select' },
      { selected: 'hrCivilStatusDropdownSelected', default: 'Select' },
      { selected: 'hrCitizenshipDropdownSelected', default: 'Select citizenship' },
      { selected: 'hrBloodTypeDropdownSelected', default: 'Select blood type' }
    ];
    
    dropdownButtons.forEach(button => {
      const element = document.getElementById(button.selected);
      if (element) {
        element.textContent = button.default;
      }
    });
    
    console.log('All dropdowns reset');
  }

  // Manual test function for dropdowns
  window.testDropdown = function() {
    const dropdown = document.getElementById('hrPositionDropdownList');
    if (dropdown) {
      console.log('Testing dropdown manually...');
      dropdown.style.setProperty('display', 'block', 'important');
      console.log('Dropdown should now be visible');
    } else {
      console.log('Dropdown not found for testing');
    }
  };

  // Test function to manually set position value
  window.testPositionValue = function() {
    const positionHidden = document.getElementById('hrPosition');
    const positionSelected = document.getElementById('hrPositionDropdownSelected');
    
    console.log('Testing position value:');
    console.log('Position hidden element:', positionHidden);
    console.log('Position selected element:', positionSelected);
    console.log('Current hidden value:', positionHidden ? positionHidden.value : 'NOT FOUND');
    console.log('Current selected text:', positionSelected ? positionSelected.textContent : 'NOT FOUND');
    
    if (positionHidden && positionSelected) {
      positionHidden.value = 'Human Resources';
      positionSelected.textContent = 'Human Resources';
      console.log('Manually set position value:');
      console.log('Hidden value now:', positionHidden.value);
      console.log('Selected text now:', positionSelected.textContent);
    }
  };

  // Test function to reset all dropdowns
  window.resetDropdowns = function() {
    resetAllDropdowns();
  };

  // Track initialization to prevent duplicates
  let dropdownsInitialized = false;

  // Initialize dropdowns when DOM is loaded
  function initDropdownsOnce() {
    if (dropdownsInitialized) {
      console.log('Dropdowns already initialized, skipping...');
      return;
    }
    
    console.log('Initializing dropdowns...');
    initializeDropdowns();
    dropdownsInitialized = true;
    console.log('Dropdowns initialized');
    
    // Test dropdown elements
    const testDropdown = document.getElementById('hrPositionDropdownList');
    if (testDropdown) {
      console.log('Test dropdown found:', testDropdown);
      console.log('Test dropdown display:', testDropdown.style.display);
    } else {
      console.log('Test dropdown NOT found');
    }
  }

  // Initialize immediately
  initDropdownsOnce();

  // Handle modal events to reset dropdown states
  document.addEventListener('shown.bs.modal', function(e) {
    console.log('Modal opened:', e.target.id);
    // Reset all dropdowns when modal opens
    resetAllDropdowns();
  });

  document.addEventListener('hidden.bs.modal', function(e) {
    console.log('Modal closed:', e.target.id);
    // Reset all dropdowns when modal closes
    resetAllDropdowns();
  });

  // Function to attach event listeners to citizenship dropdowns after dynamic loading
  function attachCitizenshipEventListeners() {
    console.log('Attaching citizenship event listeners...');
    
    // Add HR citizenship dropdown
    attachDropdownEventListeners('hrCitizenshipDropdownBtn', 'hrCitizenshipDropdownList', 'hrCitizenshipDropdownSelected', 'hrCitizenship');
    
    // Edit HR citizenship dropdown
    attachDropdownEventListeners('editHrCitizenshipDropdownBtn', 'editHrCitizenshipDropdownList', 'editHrCitizenshipDropdownSelected', 'h_edit_citizenship');
    
    console.log('Citizenship event listeners attached');
  }

  // Function to attach event listeners to a specific dropdown
  function attachDropdownEventListeners(btnId, listId, selectedId, hiddenId) {
    const btn = document.getElementById(btnId);
    const list = document.getElementById(listId);
    const selected = document.getElementById(selectedId);
    const hidden = document.getElementById(hiddenId);
    
    if (!btn || !list || !selected || !hidden) {
      console.log(`Missing elements for citizenship dropdown: ${btnId}, ${listId}, ${selectedId}, ${hiddenId}`);
      return;
    }

    console.log(`Attaching event listeners to: ${btnId}`);

    // Handle option selection for citizenship dropdowns
    const options = list.querySelectorAll('.custom-dropdown-option');
    options.forEach(option => {
      // Remove any existing event listeners
      option.replaceWith(option.cloneNode(true));
    });

    // Re-query options after cloning
    const newOptions = list.querySelectorAll('.custom-dropdown-option');
    newOptions.forEach(option => {
      option.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const value = this.getAttribute('data-value');
        const text = this.textContent.trim();
        
        console.log(`Citizenship selected: ${text} (${value})`);
        
        // Get fresh references to elements
        const currentSelected = document.getElementById(selectedId);
        const currentHidden = document.getElementById(hiddenId);
        
        if (currentSelected && currentHidden) {
          // Update the display text
          currentSelected.textContent = text;
          // Update the hidden input value
          currentHidden.value = value;
          
          console.log(`Citizenship updated - Selected text: "${currentSelected.textContent}", Hidden value: "${currentHidden.value}"`);
        } else {
          console.log('ERROR: Could not find citizenship selected or hidden elements!');
        }
        
        // Close the dropdown
        list.style.setProperty('display', 'none', 'important');
        
        // Update visual state
        newOptions.forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        
        // Trigger change event for validation
        if (currentHidden) {
          currentHidden.dispatchEvent(new Event('change'));
        }
      });
    });
  }

  // Load citizenship options dynamically
  function loadCitizenshipOptions() {
    fetch('../app/api/citizenship-api.php')
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          const addList = document.getElementById('hrCitizenshipDropdownList');
          const editList = document.getElementById('editHrCitizenshipDropdownList');
          
          if (addList) {
            addList.innerHTML = data.data.map(citizen => 
              `<div class="custom-dropdown-option" data-value="${citizen.name}">${citizen.name}</div>`
            ).join('');
          }
          
          if (editList) {
            editList.innerHTML = data.data.map(citizen => 
              `<div class="custom-dropdown-option" data-value="${citizen.name}">${citizen.name}</div>`
            ).join('');
          }
          
          // Re-attach event listeners to citizenship dropdowns after loading options
          setTimeout(() => {
            attachCitizenshipEventListeners();
          }, 100);
        }
      })
      .catch(error => {
        console.error('Error loading citizenship options:', error);
      });
  }

  // Load citizenship options
  loadCitizenshipOptions();

  // Handle edit form submission
  window.updateHr = function(event) {
    event.preventDefault();
    
    const form = document.getElementById('updateHrForm');
    if (!form) return false;

    const formData = new FormData(form);
    formData.append('_method', 'PUT');
    
    fetch('../app/api/hr_account-api.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: data.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.message
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Something went wrong!'
      });
    });
    
    return false;
  };

  // Helper function to populate edit form from view modal
  window.editHrFromObject = function(hrData) {
    if (!hrData) return;
    
    try {
      // Populate edit form
      document.getElementById('h_edit_hrId').value = hrData.id || '';
      document.getElementById('h_edit_hr_id').value = hrData.hr_employee_id || '';
      document.getElementById('h_edit_first_name').value = hrData.hr_first_name || '';
      document.getElementById('h_edit_middle_name').value = hrData.hr_middle_name || '';
      document.getElementById('h_edit_last_name').value = hrData.hr_last_name || '';
      document.getElementById('h_edit_email').value = hrData.hr_email || '';
      document.getElementById('h_edit_position').value = hrData.hr_position || '';
      document.getElementById('h_edit_rfidNumber').value = hrData.hr_rfid_number || '';
      document.getElementById('h_edit_dob').value = hrData.hr_dob || '';
      document.getElementById('h_edit_placeOfBirth').value = hrData.hr_place_of_birth || '';
      document.getElementById('h_edit_sex').value = hrData.hr_sex || '';
      document.getElementById('h_edit_civilStatus').value = hrData.hr_civil_status || '';
      document.getElementById('h_edit_contactNumber').value = hrData.hr_contact_number || '';
      document.getElementById('h_edit_citizenship').value = hrData.hr_citizenship || '';
      document.getElementById('h_edit_bloodType').value = hrData.hr_blood_type || '';
      document.getElementById('h_edit_address').value = hrData.hr_address || '';
      document.getElementById('h_edit_baseSalary').value = hrData.hr_base_salary || '';
      document.getElementById('h_edit_sssNumber').value = hrData.hr_sss_number || '';
      document.getElementById('h_edit_pagibigNumber').value = hrData.hr_pagibig_number || '';
      document.getElementById('h_edit_philhealthNumber').value = hrData.hr_philhealth_number || '';
      document.getElementById('h_existing_photo').value = hrData.hr_photo_path || '';

      // Set dropdown values
      setDropdownValue('editHrPositionDropdownSelected', 'h_edit_position', hrData.hr_position);
      setDropdownValue('editHrSexDropdownSelected', 'h_edit_sex', hrData.hr_sex);
      setDropdownValue('editHrCivilStatusDropdownSelected', 'h_edit_civilStatus', hrData.hr_civil_status);
      setDropdownValue('editHrCitizenshipDropdownSelected', 'h_edit_citizenship', hrData.hr_citizenship);
      setDropdownValue('editHrBloodTypeDropdownSelected', 'h_edit_bloodType', hrData.hr_blood_type);

      // Set photo preview
      if (hrData.hr_photo_path) {
        document.getElementById('edit_hrPhotoPreview').src = '../public/' + hrData.hr_photo_path;
        document.getElementById('edit_hrPhotoPreview').style.display = 'block';
        document.getElementById('edit_photoPlaceholder').style.display = 'none';
      }

    } catch (e) {
      console.error("❌ Error populating edit form:", e);
    }
  };
});
</script>

<!-- Add HR Modal -->
<div class="modal fade" id="addHrAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addHrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <i class="bi bi-person-plus text-[#16a249] fs-4 mr-2"></i>
                <h1 class="modal-title fs-5 text-[#16a249]" id="addHrModalLabel">Add HR</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid p-2">
                    <form method="post" id="addHrForm" action="../app/api/hr_account-api.php"
                        enctype="multipart/form-data">
                        <div class="border rounded-lg mb-4">
                            <div class="bg-yellow-100 px-4 py-2 rounded-t-lg border-b border-b-gray-200">
                                <span class="font-semibold text-[#133913]">PERSONAL INFORMATION</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                <div class="flex flex-col items-center md:col-span-1">
                                    <div
                                        class="relative w-32 h-32 mb-2 mt-1 flex items-center justify-center bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-[#16a249] transition-all duration-200">
                                        <input type="file" id="hrPhoto" name="photo_path" accept="image/*"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            onchange="previewHrPhoto(event); displayFileName(this);">
                                        <img id="hrsPhotoPreview" alt="HR Photo"
                                            class="w-full h-full object-cover rounded-lg absolute top-0 left-0 z-0"
                                            style="display:none;">
                                        <span id="photoPlaceholder" class="flex flex-col items-center justify-center text-gray-400 z-0">
                                            <i class="bi bi-image text-3xl mb-1"></i>
                                            <span class="text-xs">Upload Photo</span>
                                        </span>
                                    </div>
                                    <span id="photoFileName" class="text-sm text-gray-600 mt-1 text-center break-all max-w-[128px] overflow-hidden whitespace-nowrap text-ellipsis"></span>
                                    <div class="validation-message text-red-500 text-xs mt-1"></div>
                                </div>
                                
                                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 w-full">
                                    <div class="md:col-span-3 w-full">
                                        <div class="grid grid-cols-1 md:grid-cols-1 gap-x-6 gap-y-4">                                        
                                            <div class="flex flex-col gap-1 relative w-full">
                                            <label class="block text-xs font-medium mb-1 ml-2">EMPLOYEE ID</label>
                                            <div class="relative flex items-center">
                                                <div class="relative flex items-center w-full">
                                                    <input
                                                        type="text"
                                                        id="hrEmployeeId"
                                                        name="employeeId"                                                        
                                                        placeholder="Click Generate ID"
                                                        class="p-2 pl-8 border rounded text-sm w-full focus:outline-none bg-gray-100"
                                                        required
                                                        readonly
                                                    >
                                                    <i class="validation-icon absolute right-24 top-1/2 transform -translate-y-1/2"></i>
                                                        <button
                                                            type="button"
                                                            id="generateHrIdBtn"
                                                            
                                                            class=" ml-2 h-[38px] min-w-[82.5px] btn btn-success flex items-center justify-center"
                                                        >
                                                            <span class="text-[13px] inline-block whitespace-nowrap font-semibold">Generate ID</span>
                                                        </button>
                                                </div>
                                                </div>
                                                <div class="validation-message text-red-500 text-xs mt-1"></div>
                                            </div>                                                                            
                                        </div>
                                    </div>
                                                                            
                                    
                                    <div class="flex flex-col gap-1 relative ">
                                        <label class="block text-xs font-medium mb-1 ml-2 ">POSITION <span class="text-red-500">*</span></label>
                                        <div class="relative" id="hr-position-dropdown-container">
                                            <input type="hidden" name="position" id="hrPosition" required />
                                            <button type="button" id="hrPositionDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="hrPositionDropdownSelected" class="dropdown-button-text">Select a position</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="hrPositionDropdownList" class="custom-dropdown-list">
                                                <div class="custom-dropdown-option" data-value="Human Resources">Human Resources</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative w-full">
                                            <label class="block text-xs font-medium mb-1 ml-2">RFID NUMBER</label>
                                            <div class="relative">
                                                <input
                                                type="text"
                                                name="rfidNumber"
                                                placeholder="e.g., 0983222913"
                                                class="p-2 pl-8 border rounded text-sm w-full focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]"
                                                oninput="validateRfidNumber(this)"
                                                required
                                                >
                                                <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                            <div class="validation-message text-red-500 text-xs mt-1"></div>
                                            </div>   
                                            
                                      <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                        <div class="flex flex-col gap-1 relative">
                                                <label class="block text-xs font-medium mb-1 ml-2">FIRST NAME</label>
                                                <div class="relative">
                                                    <input type="text" name="firstName" placeholder="e.g., Juan"
                                                        class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                        required
                                                        >
                                                    <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                                </div>
                                                <div class="validation-message text-red-500 text-xs mt-1"></div>
                                            </div>
                                            <div class="flex flex-col gap-1 relative">
                                                <label class="block text-xs font-medium mb-1 ml-2">MIDDLE NAME</label>
                                                <div class="relative">
                                                    <input type="text" name="middleName" placeholder="e.g., Santos"
                                                        class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                        required
                                                        >
                                                    <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                                </div>
                                                <div class="validation-message text-red-500 text-xs mt-1"></div>
                                            </div>
                                            <div class="flex flex-col gap-1 relative">
                                                <label class="block text-xs font-medium mb-1 ml-2">LAST NAME</label>
                                                <div class="relative">
                                                    <input type="text" name="lastName" placeholder="e.g., Dela Cruz"
                                                        class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                        required
                                                        >
                                                    <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                                </div>
                                                <div class="validation-message text-red-500 text-xs mt-1"></div>
                                            </div>
                                        </div>
                                </div>

                                

                                <div class="md:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BIRTHDAY</label>
                                        <div class="relative">
                                            <input type="date" name="dob" id="hrDob" class="p-2 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full" required>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PLACE OF BIRTH</label>
                                        <div class="relative">
                                            <input type="text" name="placeOfBirth" placeholder="e.g., Davao City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SEX</label>
                                        <div class="relative" id="hr-sex-dropdown-container">
                                            <input type="hidden" name="sex" id="hrSex" required />
                                            <button type="button" id="hrSexDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="hrSexDropdownSelected" class="dropdown-button-text">Select</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="hrSexDropdownList" class="custom-dropdown-list">
                                                <div class="custom-dropdown-option" data-value="Male">Male</div>
                                                <div class="custom-dropdown-option" data-value="Female">Female</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CIVIL STATUS</label>
                                        <div class="relative" id="hr-civilStatus-dropdown-container">
                                            <input type="hidden" name="civilStatus" id="hrCivilStatus" required />
                                            <button type="button" id="hrCivilStatusDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="hrCivilStatusDropdownSelected" class="dropdown-button-text">Select</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="hrCivilStatusDropdownList" class="custom-dropdown-list">
                                                <div class="custom-dropdown-option" data-value="Single">Single</div>
                                                <div class="custom-dropdown-option" data-value="Married">Married</div>
                                                <div class="custom-dropdown-option" data-value="Separated">Separated</div>
                                                <div class="custom-dropdown-option" data-value="Divorced">Divorced</div>
                                                <div class="custom-dropdown-option" data-value="Widowed">Widowed</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CONTACT NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="contactNumber" placeholder="e.g., 09171234567"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                maxlength="11" oninput="validateContactNumber(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">EMAIL</label>
                                        <div class="relative">
                                            <input type="email" name="email" placeholder="e.g., john.doe@example.com"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CITIZENSHIP</label>
                                        <div class="relative" id="hr-citizenship-dropdown-container">
                                            <input type="hidden" name="citizenship" id="hrCitizenship" />
                                            <button type="button" id="hrCitizenshipDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="hrCitizenshipDropdownSelected" class="dropdown-button-text">Select citizenship</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="hrCitizenshipDropdownList" class="custom-dropdown-list"></div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BLOOD TYPE</label>
                                        <div class="relative" id="hr-bloodType-dropdown-container">
                                            <input type="hidden" name="bloodType" id="hrBloodType" />
                                            <button type="button" id="hrBloodTypeDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="hrBloodTypeDropdownSelected" class="dropdown-button-text">Select blood type</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="hrBloodTypeDropdownList" class="custom-dropdown-list">
                                                <div class="custom-dropdown-option" data-value="A+">A+</div>
                                                <div class="custom-dropdown-option" data-value="A-">A-</div>
                                                <div class="custom-dropdown-option" data-value="B+">B+</div>
                                                <div class="custom-dropdown-option" data-value="B-">B-</div>
                                                <div class="custom-dropdown-option" data-value="O+">O+</div>
                                                <div class="custom-dropdown-option" data-value="O-">O-</div>
                                                <div class="custom-dropdown-option" data-value="AB+">AB+</div>
                                                <div class="custom-dropdown-option" data-value="AB-">AB-</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    
                                    <div class="col-span-3 flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">COMPLETE ADDRESS</label>
                                        <div class="relative">
                                            <input type="text" name="address" placeholder="e.g., 1234 Mabini St., Barangay Malinis, Quezon City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>
                            </div>
                    
                            <div class="border-t border-t-gray-200">
                                <div class="bg-yellow-100 px-4 py-2 border-b border-b-gray-200">
                                    <span class="font-semibold text-[#133913]">SALARY AND ACCOUNTS INFORMATION</span>
                                </div>
                                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BASE SALARY</label>
                                        <div class="relative">
                                            <input type="text" name="baseSalary" placeholder="e.g., 600"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SSS NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="sssNumber" maxlength="12" placeholder="e.g., 012345678912"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PAG-IBIG NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="pagibigNumber" maxlength="12" placeholder="e.g., 012345678901"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PHILHEALTH NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="philhealthNumber" maxlength="12"
                                                placeholder="e.g., 123456789012"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="modal-footer" style="border-top: none;">
                            <button id="hrsaveBtn" type="button"
                                class="px-6 py-2 btn btn-success transition-colors duration-200 font-semibold">
                                Add HR
                            </button>
                            <button type="button"
                                class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-200 font-semibold"
                                data-bs-dismiss="modal">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit HR Modal -->
<div class="modal fade" id="updateHrAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="updateHrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <i class="bi bi-pencil-square text-[#16a249] fs-4 mr-2"></i>
                <h1 class="modal-title fs-5 text-[#16a249]" id="updateHrModalLabel">Update HR</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid p-2">
                    <form method="post" id="updateHrForm" onsubmit="return updateHr(event)" action="../app/api/hr_account-api.php"
                        enctype="multipart/form-data">
                        <input type="hidden" name="id" id="h_edit_hrId" value="">
                        <input type="hidden" name="existingPhoto" id="h_existing_photo" value="">
                        
                        <div class="border rounded-lg mb-4">
                            <div class="bg-yellow-100 px-4 py-2 rounded-t-lg border-b border-b-gray-200">
                                <span class="font-semibold text-[#133913]">PERSONAL INFORMATION</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                <div class="flex flex-col items-center md:col-span-1">
                                    <div
                                        class="relative w-32 h-32 mb-2 mt-1 flex items-center justify-center bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-[#16a249] transition-all duration-200">
                                        <input type="file" id="hrEditPhoto" name="photo_path" accept="image/*"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            onchange="editpreviewHrPhoto(event); editdisplayFileName(this);">
                                        <img id="edit_hrPhotoPreview" alt="HR Photo"
                                            class="w-full h-full object-cover rounded-lg absolute top-0 left-0 z-0"
                                            style="display:none;">
                                        <span id="edit_photoPlaceholder" class="flex flex-col items-center justify-center text-gray-400 z-0">
                                            <i class="bi bi-image text-3xl mb-1"></i>
                                            <span class="text-xs">Upload Photo</span>
                                        </span>
                                    </div>
                                    <span id="edit_photoFileName" class="text-sm text-gray-600 mt-1 text-center break-all max-w-[128px] overflow-hidden whitespace-nowrap text-ellipsis"></span>
                                    <div class="validation-message text-red-500 text-xs mt-1"></div>
                                </div>
                                
                                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                    <div class="md:col-span-3 w-full">
                                        <div class="grid grid-cols-1 md:grid-cols-1 gap-x-6 gap-y-4">                                        
                                            <div class="flex flex-col gap-1 relative w-full">
                                            <label class="block text-xs font-medium mb-1 ml-2">EMPLOYEE ID</label>
                                        <div class="relative">
                                            <input type="text" name="employeeId" id="h_edit_hr_id" placeholder="e.g., HR-001" readonly 
                                                class="p-2 pl-8 border rounded bg-gray-100 text-sm pointer-events-none cursor-default focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                            </div>                                                                            
                                        </div>
                                    </div>                                                                                                      

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">POSITION <span class="text-red-500">*</span></label>
                                        <div class="relative" id="edit-hr-position-dropdown-container">
                                            <input type="hidden" name="position" id="h_edit_position" required />
                                            <button type="button" id="editHrPositionDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editHrPositionDropdownSelected" class="dropdown-button-text">Select a position</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editHrPositionDropdownList" class="custom-dropdown-list">
                                                <div class="custom-dropdown-option" data-value="Human Resources">Human Resources</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 md:col-span-23 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">RFID NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="rfidNumber" id="h_edit_rfidNumber" placeholder="e.g., 0083222913"
                                                class="p-2 pl-8 border rounded  text-sm w-full focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]"
                                                oninput="validateRfidNumber(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">FIRST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="firstName" id="h_edit_first_name" placeholder="e.g., Juan"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">MIDDLE NAME</label>
                                        <div class="relative">
                                            <input type="text" name="middleName" id="h_edit_middle_name" placeholder="e.g., Santos"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">LAST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="lastName" id="h_edit_last_name" placeholder="e.g., Dela Cruz"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>

                                
                                <div class="md:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BIRTHDAY</label>
                                        <div class="relative">
                                            <input type="date" name="dob" id="h_edit_dob" class="p-2 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full" required>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PLACE OF BIRTH</label>
                                        <div class="relative">
                                            <input type="text" name="placeOfBirth" id="h_edit_placeOfBirth" placeholder="e.g., Davao City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SEX</label>
                                        <div class="relative" id="edit-hr-sex-dropdown-container">
                                            <input type="hidden" name="sex" id="h_edit_sex" required />
                                            <button type="button" id="editHrSexDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editHrSexDropdownSelected" class="dropdown-button-text">Select</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editHrSexDropdownList" class="custom-dropdown-list">
                                                <div class="custom-dropdown-option" data-value="Male">Male</div>
                                                <div class="custom-dropdown-option" data-value="Female">Female</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CIVIL STATUS</label>
                                        <div class="relative" id="edit-hr-civilStatus-dropdown-container">
                                            <input type="hidden" name="civilStatus" id="h_edit_civilStatus" required />
                                            <button type="button" id="editHrCivilStatusDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editHrCivilStatusDropdownSelected" class="dropdown-button-text">Select</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editHrCivilStatusDropdownList" class="custom-dropdown-list">
                                                <div class="custom-dropdown-option" data-value="Single">Single</div>
                                                <div class="custom-dropdown-option" data-value="Married">Married</div>
                                                <div class="custom-dropdown-option" data-value="Separated">Separated</div>
                                                <div class="custom-dropdown-option" data-value="Divorced">Divorced</div>
                                                <div class="custom-dropdown-option" data-value="Widowed">Widowed</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CONTACT NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="contactNumber" id="h_edit_contactNumber" placeholder="e.g., 09171234567"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                maxlength="11" oninput="validateContactNumber(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">EMAIL</label>
                                        <div class="relative">
                                            <input type="email" name="email" id="h_edit_email" placeholder="e.g., john.doe@example.com"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CITIZENSHIP</label>
                                        <div class="relative" id="edit-hr-citizenship-dropdown-container">
                                            <input type="hidden" name="citizenship" id="h_edit_citizenship" />
                                            <button type="button" id="editHrCitizenshipDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editHrCitizenshipDropdownSelected" class="dropdown-button-text">Select citizenship</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editHrCitizenshipDropdownList" class="custom-dropdown-list"></div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BLOOD TYPE</label>
                                        <div class="relative" id="edit-hr-bloodType-dropdown-container">
                                            <input type="hidden" name="bloodType" id="h_edit_bloodType" />
                                            <button type="button" id="editHrBloodTypeDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editHrBloodTypeDropdownSelected" class="dropdown-button-text">Select blood type</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editHrBloodTypeDropdownList" class="custom-dropdown-list">
                                                <div class="custom-dropdown-option" data-value="A+">A+</div>
                                                <div class="custom-dropdown-option" data-value="A-">A-</div>
                                                <div class="custom-dropdown-option" data-value="B+">B+</div>
                                                <div class="custom-dropdown-option" data-value="B-">B-</div>
                                                <div class="custom-dropdown-option" data-value="O+">O+</div>
                                                <div class="custom-dropdown-option" data-value="O-">O-</div>
                                                <div class="custom-dropdown-option" data-value="AB+">AB+</div>
                                                <div class="custom-dropdown-option" data-value="AB-">AB-</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    
                                    <div class="col-span-3 flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">COMPLETE ADDRESS</label>
                                        <div class="relative">
                                            <input type="text" name="address" id="h_edit_address" placeholder="e.g., 1234 Mabini St., Barangay Malinis, Quezon City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>
                            </div>
                    
                            <div class="border-t border-t-gray-200">
                                <div class="bg-yellow-100 px-4 py-2 border-b border-b-gray-200">
                                    <span class="font-semibold text-[#133913]">SALARY AND ACCOUNTS INFORMATION</span>
                                </div>
                                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BASE SALARY</label>
                                        <div class="relative">
                                            <input type="text" name="baseSalary" id="h_edit_baseSalary" placeholder="e.g., 600"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SSS NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="sssNumber" id="h_edit_sssNumber" maxlength="12" placeholder="e.g., 012345678912"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PAG-IBIG NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="pagibigNumber" id="h_edit_pagibigNumber" maxlength="12" placeholder="e.g., 012345678901"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PHILHEALTH NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="philhealthNumber" id="h_edit_philhealthNumber" maxlength="12"
                                                placeholder="e.g., 123456789012"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="modal-footer" style="border-top: none;">
                                <button type="submit"  
                                    class="px-6 py-2 btn btn-success transition-colors duration-200 font-semibold">
                                    Update HR
                                </button>
                                <button type="button"
                                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-200 font-semibold"
                                    data-bs-dismiss="modal">Cancel</button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once views_path("partials/footer"); ?>
