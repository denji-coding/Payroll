// === Utility Functions ===
function formatFullName(first, middle, last, suffix) {
  const firstName = (first ?? '').toUpperCase();
  const middleInitial = middle ? `${middle.charAt(0).toUpperCase()}.` : '';
  const lastName = (last ?? '').toUpperCase();
  const nameSuffix = suffix ? suffix.toUpperCase() : '';
  return `${lastName}, ${firstName} ${middleInitial} ${nameSuffix}`.trim().replace(/\s+/g, ' ');
}

function formatSSS(sss) {
  return sss && sss.length >= 10
    ? `${sss.slice(0, 2)}-${sss.slice(2, 9)}-${sss.slice(9)}`
    : 'N/A';
}

function formatPagibig(pagibig) {
  return pagibig && pagibig.length >= 8
    ? `${pagibig.slice(0, 4)}-${pagibig.slice(4, 8)}-${pagibig.slice(8)}`
    : 'N/A';
}

function formatPhilhealth(philhealth) {
  return philhealth && philhealth.length >= 10
    ? `${philhealth.slice(0, 2)}-${philhealth.slice(2, 10)}-${philhealth.slice(10)}`
    : 'N/A';
}

function toTitleCase(str) {
  return str ? str.toLowerCase().replace(/\b\w/g, char => char.toUpperCase()) : 'N/A';
}

function formatManagerName(first, middle, last) {
  const capitalizeWords = str =>
    str
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ');

  const f = first ? capitalizeWords(first) : '';
  const m = middle
    ? middle
        .split(' ')
        .map(name => name.charAt(0).toUpperCase() + '.')
        .join(' ')
    : '';
  const l = last ? capitalizeWords(last) : '';

  return [f, m, l].filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();
}


// === Main View Function ===
function viewEmployee(employeeId) {
  fetch(`index.php?payroll=api/employees&id=${employeeId}`)
    .then(async (response) => {
      const text = await response.text();

      try {
        const data = JSON.parse(text);

        if (data.status === 'success' && data.data) {
          const emp = data.data;

          const setText = (id, text) => {
            const el = document.getElementById(id);
            if (el) el.textContent = text || 'N/A';
          };

          const nameParts = emp.manager_name.trim().split(' ');
          const first = nameParts.slice(0, -2).join(' '); // e.g., "Ken Jazver"
          const middle = nameParts.slice(-2, -1)[0];      // e.g., "Delos"
          const last = nameParts.slice(-1)[0];            // e.g., "Cruz"
          
          document.getElementById('view_employee_id').value = emp.employee_no || '';

          setText('employeeIdView', emp.employee_no);
          setText('employeeName', formatFullName(emp.first_name, emp.middle_name, emp.last_name, emp.suffix));
          setText('employeeId', emp.employee_no);
          setText('employeeBloodType', emp.blood_type || 'Not available');
          setText('employeeCivilStatus', emp.civil_status);
          setText('employeeSex', emp.sex);
          setText('employeeCitizen', toTitleCase(emp.citizenship));
          setText('employeePosition', emp.position);
          setText('employeeEmail', emp.email);
          setText('employeePhone', emp.contact_number);
          setText('employeePlaceOfBirth', toTitleCase(emp.place_of_birth));
          setText('employeeRFID', emp.rfid_number);
          setText('employeeAddress', toTitleCase(emp.address));
          setText('employeeSalary', emp.base_salary);
          setText('employeeSSS', formatSSS(emp.sss_number));
          setText('employeePagibig', formatPagibig(emp.pagibig_number));
          setText('employeePhilhealth', formatPhilhealth(emp.philhealth_number));
          

          setText('employeeBranch', 
            (emp.manager_name && emp.branch_name)
              ? `${formatManagerName(first, middle, last)} - ${emp.branch_name}`
              : 'Not assigned'
          );



          // Birthday formatting
          const dobEl = document.getElementById('employeeBirthday');
          if (dobEl) {
            if (emp.dob) {
              const date = new Date(emp.dob);
              const formatted = `${date.getMonth() + 1}-${date.getDate()}-${date.getFullYear()}`;
              dobEl.textContent = formatted;
            } else {
              dobEl.textContent = 'N/A';
            }
          }

          // Photo
          const photoElement = document.getElementById('view_employeePhoto');
          if (photoElement) {
            const hasPhoto = emp.photo_path && emp.photo_path.trim() !== '';
            const defaultImage = emp.sex?.toLowerCase() === 'female'
              ? 'assets/image/default_women.png'
              : 'assets/image/default_men.png';

            photoElement.src = hasPhoto ? `../public/${emp.photo_path}?v=${Date.now()}` : defaultImage;
            photoElement.onerror = () => {
              photoElement.onerror = null;
              photoElement.src = defaultImage;
            };
          }

          // Delete Button
          const deleteBtn = document.getElementById('modalDeleteBtn');
          if (deleteBtn) {
            deleteBtn.setAttribute('data-id', emp.id);
          }

        } else {
          console.warn("Employee not found or error:", data.message || data);
          alert('Employee not found.');
        }

      } catch (err) {
        console.error('❌ Invalid JSON from API:', err);
        console.warn('🧾 Raw API Response:', text);
        alert('⚠️ Invalid response from server. Check console.');
      }
    })
    .catch(error => {
      console.error('Fetch failed:', error);
      alert('⚠️ Network error while loading employee.');
    });
}
