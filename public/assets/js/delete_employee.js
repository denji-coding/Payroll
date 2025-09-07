document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('click', async (event) => {
    if (event.target.classList.contains('deleteBtn')) {
      const button = event.target;
      const employeeId = button.getAttribute('data-id');
      const row = button.closest('tr');

      Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete the employee.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#b91c1c",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Confirm"
      }).then(async (result) => {
        if (!result.isConfirmed) return;

        const formData = new FormData();
        formData.append('employeeId', employeeId);
        formData.append('action', 'delete');
        formData.append('csrf_token', window.csrfToken || '');

        try {
          const res = await fetch('index.php?payroll=api/employees', {
            method: 'POST',
            body: formData
          });

          const contentType = res.headers.get('content-type') || '';
          if (!contentType.includes('application/json')) {
            const text = await res.text();
            console.error("Expected JSON, got:", text);
            throw new Error('Invalid JSON response');
          }

          const data = await res.json();

          const modalElement = document.getElementById('viewEmployeeModal');
          const modalInstance = modalElement ? bootstrap.Modal.getInstance(modalElement) : null;
          if (modalInstance) modalInstance.hide();

          if (data.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: data.message,
              timer: 1000,
              showConfirmButton: false,
              willClose: () => {
                if (row) {
                  // ✅ Apply styles before triggering animation
                  row.style.transition = 'opacity 0.5s ease, max-height 0.5s ease, padding 0.3s ease';
                  row.style.overflow = 'hidden'; // smooth collapse
                  row.style.maxHeight = '200px';

                  // ✅ Wait for next frame before triggering opacity
                  requestAnimationFrame(() => {
                    row.style.opacity = '0';
                    row.style.maxHeight = '0';
                    row.style.padding = '0';
                    row.style.border = '0';

                    setTimeout(() => {
                      row.remove();
                      setTimeout(() => {
                        if (typeof refreshEmployeeTableWithFade === 'function') {
                          refreshEmployeeTableWithFade();
                        }
                      }, 100); // allow refresh delay
                    }, 500); // wait for fade
                  });
                } else {
                  if (typeof refreshEmployeeTableWithFade === 'function') {
                    refreshEmployeeTableWithFade();
                  } else {
                    location.reload();
                  }
                }
              }
            });
          } else {
            Swal.fire({
              icon: data.icon || 'error',
              title: data.title || 'Error',
              text: data.message || 'Something went wrong.'
            });
          }
        } catch (err) {
          Swal.fire("Error", "Something went wrong while deleting the employee.", "error");
          console.error("Fetch or parse error:", err);
        }
      });
    }
  });
});




async function refreshEmployeeTableWithFade() {
  try {
    const res = await fetch('index.php?payroll=api/employees');
    const data = await res.json();

    const tableBody = document.getElementById('employeeTable');
    tableBody.innerHTML = '';

    if (data.status !== 'success') {
      tableBody.innerHTML = '<tr><td colspan="7" class="p-4 text-center text-gray-500">No Employee Found!</td></tr>';
      return;
    }

    let count = 1;
    for (const emp of data.data) {
      const row = document.createElement('tr');
      row.style.opacity = 0; // 🔘 Initially invisible
      row.style.transition = 'opacity 0.5s ease';

      row.className = 'transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]';

      const defaultImage = emp.sex === 'Female'
        ? '../public/assets/image/default_women.png'
        : '../public/assets/image/default_men.png';

      const position = emp.position || '';
      let bgColor = 'bg-gray-500 text-white';
      switch (position) {
        case 'Manager': bgColor = 'bg-green-600 text-white'; break;
        case 'Human Resources': bgColor = 'bg-blue-600 text-white'; break;
        case 'Staff': bgColor = 'bg-yellow-600 text-white'; break;
        case 'Driver': bgColor = 'bg-red-600 text-white'; break;
      }

      const capitalize = str => str ? str.charAt(0).toUpperCase() + str.slice(1).toLowerCase() : '';
      const fullName = `${capitalize(emp.first_name)} ${emp.middle_name ? emp.middle_name.charAt(0).toUpperCase() + '.' : ''} ${capitalize(emp.last_name)}`;

      row.innerHTML = `
      <tr class="fade-in-slide  transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
        <td class="p-3 align-middle font-medium">${count++}</td>
        <td class="p-3 align-middle font-medium">
          <div class="flex items-center space-x-2">
            <span class="relative flex shrink-0 overflow-hidden rounded-full h-12 w-12">
              <img class="aspect-square h-full w-full" src="${emp.photo_path ? emp.photo_path : defaultImage}" alt="Employee Photo">
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
        </tr>
      `;

      tableBody.appendChild(row);

      // 🔘 Fade in after short delay
      setTimeout(() => {
        row.style.opacity = 1;
      }, 10);
    }

    if (typeof bindEmployeeTableEvents === 'function') {
      bindEmployeeTableEvents();
    }

  } catch (err) {
    console.error('Error fetching employee table with fade:', err);
  }
}

