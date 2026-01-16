// edit_employee.js

function editdisplayFileName(input) {
  const fileNameSpan = document.getElementById('edit_photoFileName');
  if (!fileNameSpan) return;
  const file = input.files?.[0];
  fileNameSpan.textContent = file ? file.name : 'No file chosen';
}

function editpreviewEmployeePhoto(event) {
  const input = event.target;
  const preview = document.getElementById('edit_employeePhotoPreview');
  const placeholder = document.getElementById('edit_photoPlaceholder');
  if (!preview || !placeholder) return;

  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
      placeholder.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.src = '';
    preview.style.display = 'none';
    placeholder.style.display = 'flex';
  }
}

function formatSalary(salary) {
  if (!salary) return '';
  const str = salary.toString();
  return str.endsWith('.00') ? str.slice(0, -3) : str;
}

function resetEditForm() {
  const form = document.getElementById('editEmployeeForm');
  if (form) form.reset();

  const preview = document.getElementById('edit_employeePhotoPreview');
  if (preview) preview.style.display = 'none';

  const placeholder = document.getElementById('edit_photoPlaceholder');
  if (placeholder) placeholder.style.display = 'flex';

  const photoFilename = document.getElementById('edit_photoFileName');
  if (photoFilename) {
    photoFilename.textContent = 'No file chosen';
    photoFilename.style.display = 'block';
  }
}

function refreshEmployeeList() {
  fetch('index.php?payroll=api/employees')
    .then(res => res.json())
    .then(data => {
      const tableBody = document.getElementById('employeeTable');
      tableBody.innerHTML = '';

      if (data.status === 'success') {
        let count = 1;
        data.data.forEach((emp, index) => {
          const row = document.createElement('tr');
          row.className = 'transition-opacity duration-500 opacity-0 hover:bg-[#f2f8f2] even:bg-[#cde4cd]';

          const defaultImage = emp.sex === 'Female'
            ? '../public/assets/image/default_women.png'
            : '../public/assets/image/default_men.png';

          const position = emp.position || '';
          let bgColor = 'bg-gray-500 text-white';
          switch (position) {
            case 'Manager':
              bgColor = 'bg-green-600 text-white'; break;
            case 'Human Resources':
              bgColor = 'bg-blue-600 text-white'; break;
            case 'Staff':
              bgColor = 'bg-yellow-600 text-white'; break;
            case 'Driver':
              bgColor = 'bg-red-600 text-white'; break;
          }

          const capitalize = str => str ? str.charAt(0).toUpperCase() + str.slice(1).toLowerCase() : '';
          const fullName = `${capitalize(emp.first_name)} ${emp.middle_name ? emp.middle_name.charAt(0).toUpperCase() + '.' : ''} ${capitalize(emp.last_name)}`;

          row.innerHTML = `
            <td class="p-3 align-middle font-medium">${count++}</td>
            <td class="p-3 align-middle font-medium">
              <div class="flex items-center space-x-2">
                <span class="relative flex shrink-0 overflow-hidden rounded-full h-12 w-12">
                  <img class="aspect-square h-full w-full" src="${emp.photo_path || defaultImage}" alt="Employee Photo">
                </span>
              </div>
            </td>
            <td class="p-3 align-middle font-medium">
              <div class="flex items-center space-x-2">
                <span>${fullName}</span>
              </div>
            </td>
            <td class="p-3 align-middle">${emp.employee_no}</td>
            <td class="p-3 align-middle">${emp.rfid_number}</td>
            <td class="p-3 align-middle text-center">
              <div class="inline-flex items-center rounded-full border border-transparent ${bgColor} px-2.5 py-0.5 text-xs font-semibold">
                ${emp.position || ''}
              </div>
            </td>
            <td class="p-3 align-middle text-right">
              <div class="flex gap-2">
                <button type="button" class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white" data-bs-toggle="modal" data-bs-target="#viewEmployeeModal" onclick="viewEmployee('${emp.employee_no}')">
                  <i class="bi bi-eye text-lg"></i>
                </button>
                <div class="dropdown relative inline-block">
                  <button class="dropdown-toggle-btn inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-gear text-lg"></i>
                  </button>
                  <ul class="dropdown-menu absolute right-0 mt-2 w-48 rounded-md shadow-md bg-white ring-1 ring-black ring-opacity-5 z-50">
                    <li>
                      <a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal('viewAttendanceModal', ${emp.id})">
                        <i class="bi bi-calendar-check h-4 w-4"></i>
                        <span>View Attendance</span>
                      </a>
                    </li>
                    <li>
                      <a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal('viewSlipsModal', ${emp.id})">
                        <i class="bi bi-receipt h-4 w-4"></i>
                        <span>View Slips</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </td>
          `;

          tableBody.appendChild(row);

          // ✅ Delay fade-in to trigger animation
          setTimeout(() => {
            row.classList.remove('opacity-0');
            row.classList.add('opacity-100');
          }, index * 50); // stagger animation
        });

        if (typeof bindEmployeeTableEvents === 'function') {
          bindEmployeeTableEvents();
        }
      } else {
        tableBody.innerHTML = '<tr><td colspan="7" class="p-4 text-center text-gray-500">No Employee Found!</td></tr>';
      }
    })
    .catch(err => {
      console.error('Error loading employees:', err);
    });
}

// Helper to clear all custom dropdowns in the Edit modal
function clearEditDropdowns() {
  const dropdowns = [
    'editManagerDropdownSelected',
    'editPositionDropdownSelected',
    'editSexDropdownSelected',
    'editCivilStatusDropdownSelected',
    'editBloodTypeDropdownSelected',
    'editCitizenshipDropdownSelected',
  ];
  const hiddenInputs = [
    'edit_branchManager',
    'edit_position',
    'edit_sex',
    'edit_civilStatus',
    'edit_bloodType',
    'edit_citizenship',
  ];
  dropdowns.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = 'Select';
  });
  hiddenInputs.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
}

// EDIT BUTTON LOGIC
const editBtn = document.querySelector('.editBtn');
if (editBtn) {
  editBtn.addEventListener('click', () => {
    clearEditDropdowns(); // Clear dropdowns before loading new data
    const employeeIdInput = document.querySelector('#view_employee_id');
    const employeeId = employeeIdInput?.value;

    fetch(`index.php?payroll=api/employees&id=${employeeId}`)
      .then(async res => {
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
          const text = await res.text();
          console.error('Expected JSON, got:', text);
          throw new Error('Invalid JSON response');
        }
        return res.json();
      })
      .then(data => {
        if (data.status === 'success') {
          const emp = data.data;

          const fields = {
            edit_employee_id: emp.employee_no,
            edit_rfidNumber: emp.rfid_number,
            edit_first_name: emp.first_name,
            edit_middle_name: emp.middle_name,
            edit_last_name: emp.last_name,
            edit_dob: emp.dob,
            edit_placeOfBirth: emp.place_of_birth,
            edit_sex: emp.sex,
            edit_philhealthNumber: emp.philhealth_number,
            edit_civilStatus: emp.civil_status,
            edit_contactNumber: emp.contact_number,
            edit_email: emp.email,
            edit_citizenship: emp.citizenship,
            edit_bloodType: emp.blood_type,
            edit_position: emp.position,
            edit_address: emp.address,
            edit_baseSalary: formatSalary(emp.base_salary),
            edit_sssNumber: emp.sss_number,
            edit_pagibigNumber: emp.pagibig_number,
            edit_branchManager: emp.branch_manager,
          };

          Object.entries(fields).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) el.value = val || '';
            // If this is the birthday field, also set the date in flatpickr
            if (id === 'edit_dob' && window.flatpickr) {
              const fp = el._flatpickr;
              if (fp && val) {
                fp.setDate(val, true);
              }
            }
          });

          // After a short delay, re-initialize and update all custom dropdowns
          setTimeout(() => {
            // Manager
            const managerSpan = document.getElementById('editManagerDropdownSelected');
            const managerList = document.getElementById('editManagerDropdownList');
            const managerInput = document.getElementById('edit_branchManager');
            if (managerSpan && managerList && managerInput) {
              // Clear all previous selections
              managerList.querySelectorAll('.custom-dropdown-option').forEach(option => {
                option.classList.remove('selected');
              });
              // Find and mark the correct option as selected
              let found = false;
              managerList.querySelectorAll('.custom-dropdown-option').forEach(option => {
                if (option.dataset.value === managerInput.value) {
                  managerSpan.textContent = option.textContent;
                  option.classList.add('selected');
                  found = true;
                }
              });
              if (!found) managerSpan.textContent = 'Select a Manager';
            }
            
            // Position
            const positionSpan = document.getElementById('editPositionDropdownSelected');
            const positionList = document.getElementById('editPositionDropdownList');
            const positionInput = document.getElementById('edit_position');
            if (positionSpan && positionList && positionInput) {
              // Clear all previous selections
              positionList.querySelectorAll('.custom-dropdown-option').forEach(option => {
                option.classList.remove('selected');
              });
              // Find and mark the correct option as selected
              positionList.querySelectorAll('.custom-dropdown-option').forEach(option => {
                if (option.dataset.value === positionInput.value) {
                  positionSpan.textContent = option.textContent;
                  option.classList.add('selected');
                }
              });
              if (!positionInput.value) positionSpan.textContent = 'Select';
            }
            
            // Sex
            const sexSpan = document.getElementById('editSexDropdownSelected');
            const sexList = document.getElementById('editSexDropdownList');
            const sexInput = document.getElementById('edit_sex');
            if (sexSpan && sexList && sexInput) {
              // Clear all previous selections
              sexList.querySelectorAll('.custom-dropdown-option').forEach(option => {
                option.classList.remove('selected');
              });
              // Find and mark the correct option as selected
              sexList.querySelectorAll('.custom-dropdown-option').forEach(option => {
                if (option.dataset.value === sexInput.value) {
                  sexSpan.textContent = option.textContent;
                  option.classList.add('selected');
                }
              });
              if (!sexInput.value) sexSpan.textContent = 'Select';
            }
            
            // Civil Status
            const civilStatusSpan = document.getElementById('editCivilStatusDropdownSelected');
            const civilStatusList = document.getElementById('editCivilStatusDropdownList');
            const civilStatusInput = document.getElementById('edit_civilStatus');
            if (civilStatusSpan && civilStatusList && civilStatusInput) {
              // Clear all previous selections
              civilStatusList.querySelectorAll('.custom-dropdown-option').forEach(option => {
                option.classList.remove('selected');
              });
              // Find and mark the correct option as selected
              civilStatusList.querySelectorAll('.custom-dropdown-option').forEach(option => {
                if (option.dataset.value === civilStatusInput.value) {
                  civilStatusSpan.textContent = option.textContent;
                  option.classList.add('selected');
                }
              });
              if (!civilStatusInput.value) civilStatusSpan.textContent = 'Select';
            }
            
            // Blood Type
            const bloodTypeSpan = document.getElementById('editBloodTypeDropdownSelected');
            const bloodTypeList = document.getElementById('editBloodTypeDropdownList');
            const bloodTypeInput = document.getElementById('edit_bloodType');
            if (bloodTypeSpan && bloodTypeList && bloodTypeInput) {
              // Clear all previous selections
              bloodTypeList.querySelectorAll('.custom-dropdown-option').forEach(option => {
                option.classList.remove('selected');
              });
              // Find and mark the correct option as selected
              bloodTypeList.querySelectorAll('.custom-dropdown-option').forEach(option => {
                if (option.dataset.value === bloodTypeInput.value) {
                  bloodTypeSpan.textContent = option.textContent;
                  option.classList.add('selected');
                }
              });
              if (!bloodTypeInput.value) bloodTypeSpan.textContent = 'Select blood type';
            }
            
            // Citizenship (this one uses API, so we'll just update the span)
            const citizenshipSpan = document.getElementById('editCitizenshipDropdownSelected');
            const citizenshipInput = document.getElementById('edit_citizenship');
            if (citizenshipSpan && citizenshipInput) {
              citizenshipSpan.textContent = citizenshipInput.value || 'Select citizenship';
            }
          }, 100);

          const preview = document.getElementById('edit_employeePhotoPreview');
          const placeholder = document.getElementById('edit_photoPlaceholder');
          const photoFilename = document.getElementById('edit_photoFileName');

          if (emp.photo_path) {
            preview.src = `../public/${emp.photo_path}?v=${Date.now()}`;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            photoFilename.textContent = emp.photo_path.split('/').pop();
          } else {
            preview.style.display = 'none';
            placeholder.style.display = 'flex';
            photoFilename.textContent = 'No file chosen';
          }

        } else {
          Swal.fire({ icon: 'error', title: 'Not Found', text: 'Employee data not found.' });
        }
      })
      .catch(err => {
        console.error('Error:', err);
        Swal.fire({ icon: 'error', title: 'Fetch Failed', text: 'An error occurred while fetching the data.' });
      });
  });
}

// HANDLE FORM SUBMIT

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('editEmployeeForm');
  if (!form) return;

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    const formData = new FormData(this);

    fetch('index.php?payroll=api/employees', {
      method: 'POST',
      body: formData,
    })
      .then(async (response) => {
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
          const text = await response.text();
          console.error('Expected JSON, got:', text);
          throw new Error('Invalid JSON response');
        }
        return response.json();
      })
      .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal'));
            if (modal) modal.hide();
        if (data.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: data.message || 'Employee updated successfully.',
            timer: 1500,
            showConfirmButton: false,
          }).then(() => {
            refreshEmployeeList();
            resetEditForm();
            const modal = bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal'));
            if (modal) modal.hide();
            // reload data instead of full page
            if (typeof refreshEmployeeList === 'function') {
              refreshEmployeeList();
            }
          });
        } else {
          Swal.fire({ icon: data.icon || 'error', title: data.title || 'Error', text: data.message || 'Failed to update employee.' });
        }
      })
      .catch(error => {
        console.error('Submission error:', error);
        Swal.fire({ icon: 'error', title: 'Request Failed', text: 'Please try again later.' });
      });
  });
});
