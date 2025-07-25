// ========== Preview Employee Photo ==========
function previewEmployeePhoto(event) {
  const input = event.target;
  const preview = document.getElementById('employeePhotoPreview');
  const placeholder = document.getElementById('photoPlaceholder');

  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
      placeholder.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// ========== Display Uploaded Photo Name ==========
function displayFileName(input) {
  const fileNameSpan = document.getElementById('photoFileName');
  fileNameSpan.textContent = input.files?.[0]?.name || '';
}

// ========== Reset Add Employee Form ==========
function resetAddEmployeeForm() {
  const form = document.getElementById('addEmployeeForm');
  if (!form) return;

  form.reset();

  const preview = document.getElementById('employeePhotoPreview');
  const placeholder = document.getElementById('photoPlaceholder');
  const fileName = document.getElementById('photoFileName');

  if (preview) {
    preview.style.display = 'none';
    preview.src = '';
  }
  if (placeholder) {
    placeholder.style.display = 'flex';
  }
  if (fileName) {
    fileName.textContent = '';
  }

  form.querySelectorAll('.validation-message').forEach(el => el.textContent = '');
  form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
}

// ========== Refresh Employee Table ==========
async function refreshEmployeeTable() {
  try {
    const res = await fetch('../app/api/employees-api.php?fetch_table=1');
    const json = await res.json();

    const tbody = document.querySelector('#employeeTable');
    if (!tbody) return;
    tbody.innerHTML = ''; // Clear current rows

    if (!json.status || !Array.isArray(json.data) || json.data.length === 0) {
      tbody.innerHTML = `
        <tr id="EmployeenoResultRow">
          <td colspan="7" class="p-4 text-center italic text-gray-500 bg-[#f0fdf4]">
            <i class="bi bi-person-x me-2"></i> No employees found.
          </td>
        </tr>
      `;
      return;
    }

    json.data.forEach((employee, index) => {
      const defaultImage = employee.sex === 'Female'
        ? '../public/assets/image/default_women.png'
        : '../public/assets/image/default_men.png';

      const imageSrc = employee.photo_path || defaultImage;

      let positionColor = '';
      switch (employee.position) {
        case 'Manager': positionColor = 'bg-green-600 text-white'; break;
        case 'Human Resources': positionColor = 'bg-blue-600 text-white'; break;
        case 'Staff': positionColor = 'bg-yellow-600 text-white'; break;
        case 'Driver': positionColor = 'bg-red-600 text-white'; break;
        default: positionColor = 'bg-gray-500 text-white'; break;
      }

      const middleInitial = employee.middle_name
        ? employee.middle_name.charAt(0).toUpperCase() + '.'
        : '';

      const fullName = `${capitalize(employee.first_name)} ${middleInitial} ${capitalize(employee.last_name)}`;

      const tr = document.createElement('tr');
      tr.className = 'fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]';
      tr.innerHTML = `
        <td class="p-3 align-middle font-medium">${index + 1}</td>
        <td class="p-3 align-middle font-medium">
          <div class="flex items-center space-x-2">
            <span class="relative flex shrink-0 overflow-hidden rounded-full h-12 w-12">
              <img class="aspect-square h-full w-full" src="${imageSrc}" alt="Employee Photo">
            </span>
          </div>
        </td>
        <td class="p-3 align-middle font-medium">
          <div class="flex items-center space-x-2">
            <span>${fullName}</span>
          </div>
        </td>
        <td class="p-3 align-middle">${employee.employee_no}</td>
        <td class="p-3 align-middle">${employee.rfid_number}</td>
        <td class="p-3 align-middle text-center">
          <div class="inline-flex items-center rounded-full border border-transparent ${positionColor} px-2.5 py-0.5 text-xs font-semibold">
            ${employee.position}
          </div>
        </td>
        <td class="p-3 align-middle text-right">
          <div class="flex gap-2">
            <button type="button"
              class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
              data-bs-toggle="modal"
              data-bs-target="#viewEmployeeModal"
              onclick="viewEmployee('${employee.employee_no}')">
              <i class="bi bi-eye text-lg"></i>
            </button>
            <div class="dropdown relative inline-block">
              <button class="dropdown-toggle-btn inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                      type="button"
                      data-bs-toggle="dropdown"
                      aria-expanded="false">
                <i class="bi bi-person-gear text-lg"></i>
              </button>
              <ul class="dropdown-menu absolute right-0 mt-2 w-48 rounded-md shadow-md bg-white ring-1 ring-black ring-opacity-5 z-50">
                <li>
                  <a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal('viewAttendanceModal', ${employee.id})">
                    <i class="bi bi-calendar-check h-4 w-4"></i>
                    <span>View Attendance</span>
                  </a>
                </li>
                <li>
                  <a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal('viewSlipsModal', ${employee.id})">
                    <i class="bi bi-receipt h-4 w-4"></i>
                    <span>View Slips</span>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </td>
      `;

      tbody.appendChild(tr);
    });

  } catch (error) {
    console.error('Error refreshing employee table:', error);
  }
}

// Capitalize helper function
function capitalize(str) {
  return (str || '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
}



// ========== Document Ready ==========
document.addEventListener('DOMContentLoaded', function () {
  // --- Generate Unique Employee ID ---
  const generateIdBtn = document.getElementById('generateIdBtn');
  const employeeIdInput = document.getElementById('employeeId');

  if (generateIdBtn && employeeIdInput) {
    generateIdBtn.addEventListener('click', function () {
      const randomDigits = Math.floor(100000 + Math.random() * 900000);
      employeeIdInput.value = `EMP-${randomDigits}`;
    });
  }

  // --- Handle Add Employee Form Submission ---
  const form = document.getElementById('addEmployeeForm');
  if (form) {
    const modalEl = document.getElementById('addEmployeeModal');
    if (modalEl) {
      modalEl.addEventListener('hidden.bs.modal', resetAddEmployeeForm);
    }

    document.querySelectorAll('.cancelBtn').forEach(btn => {
      btn.addEventListener('click', resetAddEmployeeForm);
    });

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      const formData = new FormData(form);

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData
        });

        const text = await response.text();
        let data;

        try {
          data = JSON.parse(text);
        } catch (err) {
          console.error("Invalid JSON:", text);
          await Swal.fire({
            icon: 'error',
            title: 'Server Error',
            text: 'Server did not return valid JSON.'
          });
          return;
        }

        if (data.status === 'success') {
          await Swal.fire({
            icon: data.icon || 'success',
            title: data.title || 'Success!',
            text: data.message || 'Employee added successfully!',
            timer: 1000,
            showConfirmButton: false,
            timerProgressBar: true
          });
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();
          refreshEmployeeTable();
        } else {
          await Swal.fire({
            icon: data.icon || 'error',
            title: data.title || 'Error',
            text: data.message || 'An error occurred.',
            confirmButtonColor: '#d33'
          });
        }
      } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
          icon: 'error',
          title: 'Unexpected Error',
          text: error.message || 'Something went wrong.',
          confirmButtonColor: '#d33'
        });
      }
    });
  }

  // --- Search Filtering ---
  const searchInput = document.getElementById("employee_searchInput");
  const clearBtn = document.getElementById("employee_clearButton");
  const tableBody = document.getElementById("employeeTable");

  if (!searchInput || !clearBtn || !tableBody) return;

  toggleClearButton();
  filterEmployeeTable();

  const debouncedFilter = debounce(() => {
    toggleClearButton();
    filterEmployeeTable();
  }, 300);

  searchInput.addEventListener("input", debouncedFilter);

  clearBtn.addEventListener("click", () => {
    searchInput.value = "";
    toggleClearButton();
    filterEmployeeTable();
  });

  function toggleClearButton() {
    clearBtn.classList.toggle("hidden", searchInput.value.trim() === "");
  }

  function filterEmployeeTable() {
    const searchTerm = searchInput.value.trim().toLowerCase();
    const rows = tableBody.querySelectorAll("tr");
    let visibleCount = 0;

    const oldNoResult = document.getElementById("EmployeenoResultRow");
    if (oldNoResult) oldNoResult.remove();

    rows.forEach((row) => {
      if (row.id === "EmployeenoResultRow") return;

      const cells = row.querySelectorAll("td");
      if (cells.length < 4) {
        row.style.display = "none";
        return;
      }

      const nameText = cells[2].textContent.toLowerCase();
      const empIdText = cells[3].textContent.toLowerCase();
      const isMatch =
  nameText.split(' ').some(word => word.startsWith(searchTerm)) ||
  empIdText.startsWith(searchTerm);


      if (isMatch) {
        
        // row.classList.remove("fade-in-slide");
        void row.offsetWidth;
        row.style.display = "";
        row.classList.add("fade-in-slide");
        visibleCount++;
      } else {
        row.style.display = "none";
      }
    });

    if (visibleCount === 0) {
      const noResultRow = document.createElement("tr");
      noResultRow.id = "EmployeenoResultRow";
      noResultRow.innerHTML = `
        <td colspan="8" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
          <i class="bi bi-person-x fs-5 me-2"></i>No matching employees found.
        </td>
      `;
      tableBody.appendChild(noResultRow);
    }
  }

  function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
  }
});

// ========== Validation ==========
function validateRfidNumber(input) {
  input.value = input.value.replace(/\D/g, '');
  const messageDiv = input.closest('div.flex')?.querySelector('.validation-message');
  if (messageDiv) {
    messageDiv.textContent = '';
    input.classList.remove("border-red-500");
  }
}

function validateContactNumber(input) {
  const pattern = /^09\d{9}$/;
  const messageDiv = input.closest('div.flex')?.querySelector('.validation-message');
  if (!messageDiv) return;

  input.value = input.value.replace(/\D/g, '');

  input.addEventListener('blur', () => {
    if (!pattern.test(input.value)) {
      messageDiv.textContent = "Phone number must start with '09' and be exactly 11 digits.";
      input.classList.add("border-red-500");

      Swal.fire({
        icon: 'error',
        title: 'Invalid Contact Number',
        text: messageDiv.textContent,
        confirmButtonColor: '#d33'
      });
    } else {
      messageDiv.textContent = "";
      input.classList.remove("border-red-500");
    }
  });
}

function validateInput(input) {
  const field = input.name;
  const raw = input.value.replace(/\D/g, '');
  input.value = raw;

  const messageDiv = input.closest('div.flex')?.querySelector('.validation-message');
  if (!messageDiv) return;

  let valid = false;
  let errorMessage = '';

  const requirements = {
    sssNumber: { length: 12, message: "SSS Number must be exactly 12 digits." },
    pagibigNumber: { length: 12, message: "PAG-IBIG Number must be exactly 12 digits." },
    philhealthNumber: { length: 12, message: "PhilHealth Number must be exactly 12 digits." }
  };

  if (requirements[field]) {
    const { length, message } = requirements[field];
    valid = raw.length === length;
    errorMessage = message;

    if (raw === "") {
      messageDiv.textContent = "";
      input.classList.remove("border-red-500");
    } else if (!valid) {
      messageDiv.textContent = errorMessage;
      input.classList.add("border-red-500");

      Swal.fire({
        icon: 'error',
        title: 'Invalid Input',
        text: errorMessage,
        confirmButtonColor: '#d33'
      });
    } else {
      messageDiv.textContent = "";
      input.classList.remove("border-red-500");
    }
  } else if (input.type === "date" || input.tagName === "SELECT") {
    if (!input.value) {
      messageDiv.textContent = "This field is required.";
      input.classList.add("border-red-500");
    } else {
      messageDiv.textContent = "";
      input.classList.remove("border-red-500");
    }
  } else {
    messageDiv.textContent = "";
    input.classList.remove("border-red-500");
  }
}

// ========== Helper ==========
function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value ?? 'N/A';
}
