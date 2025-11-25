<?php
// Set page title
$title = "RFID Attendance";

// Include header partial
require_once views_path("partials/header");
?>

<style>
   @keyframes fadeInSlide {
  from {
    opacity: 0;
    transform: translateY(-1px);
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

<!-- SweetAlert2 CDN for alert popups -->
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->



<!-- Layout wrapper: Sidebar + Main -->
<div class="flex w-screen h-screen overflow-hidden" style="font-family: ${defaultFont};">

    <!-- SIDEBAR -->
<aside class="w-72 bg-green-800 shadow-md fixed top-0 left-0 bottom-0 z-30 border-r border-green-700 overflow-y-auto">
    <div class="p-6 text-white flex flex-col h-full">
        
        <!-- Logo & Company Info -->
        <div class="text-center">
            <img src="../public/assets/image/test_logo.png" alt="Company Logo" 
                class="mx-auto rounded-full ">
            <span class="font-extrabold text-xl md:text-2xl leading-tight block mt-2">
                Migrants Venture Corporation
            </span>
            <p class="text-green-100 font-semibold mt-1">
                Employee Attendance System
            </p>
        </div>

        <!-- Attendance Form -->
        <form id="manualAttendanceForm"
              method="POST"
              action="../app/api/attendance-api.php"
              class="mt-10 flex flex-col gap-6">

            <!-- Employee ID Input -->
            <div class="flex flex-col mt-12 gap-2">
                <label for="employeeIdInput" class="text-sm font-medium text-white">
                    EMPLOYEE ID
                </label>
                <input
                    type="text"
                    name="employee_id"
                    id="employeeIdInput"
                    placeholder="Enter Employee ID"
                    autocomplete="off"
                    required
                    oninput="this.value = this.value.toUpperCase()"
                    tabindex="1"
                    class="w-full px-3 py-2 rounded-md bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:border-white/40 focus:ring-1 focus:ring-white/20"
                />
            </div>

            <!-- Time In / Out Buttons -->
            <div class="flex gap-2">
                <button
                    type="button"
                    name="time_in"
                    id="manualTimeInBtn"
                    class="flex-1 py-2 rounded border font-semibold text-sm hover:bg-green-200 border-green-500 bg-green-50 text-green-700 transition"
                >
                    Time In
                </button>
                <button
                    type="button"
                    name="time_out"
                    id="manualTimeOutBtn"
                    class="flex-1 py-2 rounded border font-semibold text-sm hover:bg-red-200 border-red-500 bg-red-50 text-red-700 transition"
                >
                    Time Out
                </button>
            </div>
        </form>

        <!-- Hidden RFID Input -->
        <input type="text" name="rfid" id="rfidInput" autocomplete="off" class="sr-only mt-4" tabindex="-1">
    </div>
</aside>


    <!-- MAIN CONTENT -->
    <div class="flex-1 ml-72 flex flex-col overflow-hidden">
        <main class="p-6 overflow-y-auto flex-1">

            <!-- Current time and date display -->
            <div class="flex justify-center -mt-2">
                <div class="text-[#237339] w-full lg:w-1/2 h-32 flex flex-col items-center justify-center">
                    <div id="time" class="text-6xl font-extrabold"><?= $current_time ?></div>
                    <!-- <div class="text-xl mt-2"><?= $current_date ?></div> -->
                </div>
            </div>

            <!-- Instruction -->
            <div class="text-center text-gray-500 mb-2">
                Tap your RFID card or use Manual Attendance to record your attendance.
            </div>

            

            <!-- Optional: Tailwind-Compatible Custom Style for Flatpickr Header -->
            <style>
            /* Calendar Header Background and Text */
            /* .flatpickr-calendar .flatpickr-months {
              background-color: #237339; 
              color: white;
              border-top-left-radius: 0.375rem;
              border-top-right-radius: 0.375rem;
            } */

            .flatpickr-calendar .flatpickr-current-month input.cur-month {
                background: transparent;
                color: white;
                font-weight: 600;
            }

            .flatpickr-calendar .flatpickr-weekday {
              color: white;
              font-weight: 600;
            }

            .flatpickr-calendar .flatpickr-prev-month,
            .flatpickr-calendar .flatpickr-next-month {
                color: white;
                border: 1px solid transparent;
                border-radius: 4px;
                width: 28px;
                height: 28px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: border-color 0.2s ease;
                margin: 7px 10px;
                box-sizing: border-box;
                background: transparent;
                padding: 0;
            }

            /* Style the arrow icons */
            .flatpickr-calendar .flatpickr-prev-month svg,
            .flatpickr-calendar .flatpickr-next-month svg {
                width: 14px;
                height: 14px;
                fill: white; /* default arrow color (light gray or as needed) */
                transition: fill 0.2s ease;
            }

            /* On hover, border and arrow turn white */
            .flatpickr-calendar .flatpickr-prev-month:hover,
            .flatpickr-calendar .flatpickr-next-month:hover {
            border-color: white;
            cursor: pointer;
            }

            .flatpickr-calendar .flatpickr-prev-month:hover svg,
            .flatpickr-calendar .flatpickr-next-month:hover svg {
            fill: white; /* hover arrow becomes white */
            }

        </style>


           <!-- Date Filter with Flatpickr (No Page Reload) -->
<div class="bg-white p-2 rounded-md mb-2 text-sm max-w-xs w-full mx-auto">
  <div class="flex items-center gap-2 w-full">
    
    <!-- Flatpickr Date Input -->
    <div class="relative flex-1 min-w-0">
      <input 
        type="text" 
        id="date" 
        name="date" 
        value="<?= htmlspecialchars($filterDate ?? date('Y-m-d')) ?>"
        placeholder="Select a date"
        class="invisible w-full p-1 border border-emerald-300 rounded focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 text-emerald-700 text-center bg-white"
      >
    </div>

    <!-- Buttons -->
    <div class="flex-shrink-0 flex gap-1">
      <!-- Filter Button -->
      <button 
        type="button" 
        id="filterBtn"
        class="w-20 h-8 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs whitespace-nowrap flex items-center justify-center px-2"
      >
        <span class="text-sm">Filter</span>
        <i class="bi bi-filter text-sm ml-1"></i>
      </button>

      <!-- Clear Filter Button (shown via JS) -->
      <button 
        type="button" 
        id="clearFilterBtn"
        class="w-auto h-8 flex items-center justify-center gap-1 px-3 py-1 bg-red-700 hover:bg-red-800 text-white rounded text-xs whitespace-nowrap <?= (!isset($_GET['date']) || $_GET['date'] === date('Y-m-d')) ? 'hidden' : '' ?>"
      >
        <i class="bi bi-eraser text-sm"></i>
      </button>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
  const dateInput = document.getElementById("date");
  const clearBtn = document.getElementById("clearFilterBtn");
  const filterBtn = document.getElementById("filterBtn");
  const todayStr = new Date().toISOString().split('T')[0];

  window.fp = flatpickr(dateInput, {
  altInput: true,
  altFormat: "F j, Y",
  dateFormat: "Y-m-d",
  defaultDate: dateInput.value || todayStr,
  disableMobile: true,
  allowInput: true,

  onReady: function (selectedDates, dateStr, instance) {
    // Remove glitch: hide raw input until ready
    instance._input.classList.remove('invisible');

    // Prevent Flatpickr from closing when clicking month/year dropdowns
    const monthNav = instance.calendarContainer.querySelector('.flatpickr-monthDropdown-months');
    if (monthNav) {
      monthNav.addEventListener('click', function (e) {
        e.stopPropagation();
      });
    }

    const yearInput = instance.calendarContainer.querySelector('.numInputWrapper');
    if (yearInput) {
      yearInput.addEventListener('click', function (e) {
        // Optional: prevent close when clicking year input
        e.stopPropagation();
      });
    }

    // ✅ Show/hide clear button initially
    toggleClearBtn(dateInput.value);
  }
});

  function toggleClearBtn(selectedDate) {
    if (clearBtn) {
      if (selectedDate && selectedDate !== todayStr) {
        clearBtn.classList.remove("hidden");
      } else {
        clearBtn.classList.add("hidden");
      }
    }
  }

  if (clearBtn) {
    clearBtn.addEventListener("click", function () {
      fp.setDate(todayStr, true); // Reset to today
      toggleClearBtn(todayStr);
      refreshAttendanceTable(todayStr);
    });
  }

  if (filterBtn) {
    filterBtn.addEventListener("click", () => {
      const selectedDate = dateInput.value;
      if (selectedDate) {
        toggleClearBtn(selectedDate);
        refreshAttendanceTable(selectedDate);
      }
    });
  }
});

</script>



        <!-- Table of records -->
        <div class="overflow-x-auto max-h-[60vh] rounded-lg shadow-md bg-white">
            <table class="w-full text-base min-w-[700px]">
                <thead class="sticky top-0 bg-[#237339] text-white">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-bold" rowspan="2">NO.</th>
                        <th class="py-3 px-4 text-left text-xs font-bold" rowspan="2">PHOTO</th>
                        <!-- <th class="py-3 px-4 text-left text-xs font-bold" rowspan="2">EMP. ID</th> -->
                        <th class="py-3 px-4 text-left text-xs font-bold" rowspan="2">NAME</th>
                        <th class="py-3 px-4 text-left text-xs font-bold" rowspan="2">POSITION</th>
                        <th class="h-12 px-4 text-center text-xs align-bottom font-bold" colspan="2">MORNING</th>
                        <th class="h-12 px-4 text-center text-xs align-bottom font-bold" colspan="2">AFTERNOON</th>
                        <th class="py-3 px-4 text-center text-xs font-bold" rowspan="2">DATE</th>
                        </tr>
                        <tr>
                        <th class="py-2 px-4 text-center text-xs font-semibold">IN</th>
                        <th class="py-2 px-4 text-center text-xs font-semibold">OUT</th>
                        <th class="py-2 px-4 text-center text-xs font-semibold">IN</th>
                        <th class="py-2 px-4 text-center text-xs font-semibold">OUT</th>
                        </tr>
                    </thead>
                        <tbody id="attendance-table-body">
                                <?php if (count($attendanceRecords) > 0): ?>
                                    <?php foreach ($attendanceRecords as $index => $record): ?>
                                        <tr class="fade-in-slide ">
                                            <td class="py-3 px-4 text-center"><?= $index + 1?></td>
                                            <td class="py-3 px-4">
                                                <?php 
                                                $photoPath = $record['photo_path'] ?? null;
                                                $gender = strtolower($record['gender'] ?? '');
                                                
                                                // Determine default image based on gender
                                                if (empty($photoPath)) {
                                                    if ($gender === 'male' || $gender === 'm') {
                                                        $defaultImage = 'assets/image/default_men.png';
                                                    } elseif ($gender === 'female' || $gender === 'f') {
                                                        $defaultImage = 'assets/image/default_women.png';
                                                    } else {
                                                        $defaultImage = 'assets/image/default_user_image.svg';
                                                    }
                                                    $imageSrc = $defaultImage;
                                                } else {
                                                    $imageSrc = $photoPath;
                                                    // Set fallback default based on gender
                                                    if ($gender === 'male' || $gender === 'm') {
                                                        $defaultImage = 'assets/image/default_men.png';
                                                    } elseif ($gender === 'female' || $gender === 'f') {
                                                        $defaultImage = 'assets/image/default_women.png';
                                                    } else {
                                                        $defaultImage = 'assets/image/default_user_image.svg';
                                                    }
                                                }
                                                ?>
                                                <img src="<?= htmlspecialchars($imageSrc) ?>" alt="Photo" class="h-10 w-10 rounded-full object-cover" onerror="this.onerror=null;this.src='<?= htmlspecialchars($defaultImage) ?>';" />
                                            </td>
                                            <!-- <td class="py-3 text-sm px-4"><?= htmlspecialchars($record['employee_no']) ?></td> -->
                                            <td class="py-3 text-sm px-4"><?= htmlspecialchars(ucwords(strtolower($record['full_name']))) ?></td>

                                            <td class="py-3 text-sm px-4"><?= htmlspecialchars($record['position']) ?></td>
                                            <td class="truncate py-3 text-sm text-center px-4">
                                                <?= $record['morning_in'] ? date('h:i A', strtotime($record['morning_in'])) : '--:--' ?>
                                            </td>
                                            <td class="truncate py-3 text-sm text-center px-4">
                                                <?= $record['morning_out'] ? date('h:i A', strtotime($record['morning_out'])) : '--:--' ?>
                                            </td>
                                            <td class="truncate py-3 text-sm text-center px-4">
                                                <?= $record['afternoon_in'] ? date('h:i A', strtotime($record['afternoon_in'])) : '--:--' ?>
                                            </td>
                                            <td class="truncate py-3 text-sm text-center px-4">
                                                <?= $record['afternoon_out'] ? date('h:i A', strtotime($record['afternoon_out'])) : '--:--' ?>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-center"><?= htmlspecialchars(date('d-M-Y', strtotime($record['date']))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-sm">No attendance records found<?= isset($filterDate) ? ' for this date' : '' ?>.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </main>
            </div>
        </div>
    </div>


<script>
function updateClock() {
  const now = new Date();
  const timeElement = document.getElementById('time');
  const dateElement = document.getElementById('date');

  const timeString = now.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
  });

  const dateString = now.toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  });

  if (timeElement) timeElement.textContent = timeString;
  if (dateElement) dateElement.textContent = dateString;
}
updateClock();
setInterval(updateClock, 1000);

function formatName(str) {
  return str.toLowerCase().split(' ').map(word =>
    word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

// === Refresh attendance table
function refreshAttendanceTable(date = null) {
  // Get the selected date from the date picker, or use today if not provided
  let selectedDate;
  if (date) {
    selectedDate = date;
  } else {
    const dateInput = document.getElementById("date");
    if (dateInput && dateInput.value) {
      selectedDate = dateInput.value;
    } else {
      selectedDate = new Date().toISOString().split('T')[0];
    }
  }
  
  fetch(`../app/api/attendance-api.php?date=${selectedDate}`)
    .then(res => res.json())
    .then(res => {
  console.log("Fetch Log Response:", res);
  const tbody = document.getElementById('attendance-table-body');
  if (res.status === 'success' && res.html) {
    tbody.innerHTML = res.html;
  } else {
        console.warn('No HTML returned:', res.message || 'Unknown error');
        tbody.innerHTML = `
          <tr>
            <td colspan="10" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
              <i class="bi bi-calendar-x fs-5 me-2"></i>No attendance records found.
            </td>
          </tr>
        `;
  }
})
    .catch(err => {
      console.error('Table refresh error:', err);
      const tbody = document.getElementById('attendance-table-body');
      if (tbody) {
        tbody.innerHTML = `
          <tr>
            <td colspan="10" class="text-center py-4 text-sm text-red-500">Error loading attendance records. Please try again.</td>
          </tr>
        `;
      }
    });
}

document.addEventListener('DOMContentLoaded', () => {
  const rfidInput = document.getElementById('rfidInput');
  const employeeIdInput = document.getElementById('employeeIdInput');
  const timeInBtn = document.getElementById('manualTimeInBtn');
  const timeOutBtn = document.getElementById('manualTimeOutBtn');

  // Auto-refresh at midnight (12:00 AM)
  const scheduleMidnightRefresh = () => {
    const now = new Date();
    const midnight = new Date(now);
    midnight.setHours(24, 0, 0, 0); // Next midnight
    
    const timeUntilMidnight = midnight.getTime() - now.getTime();
    
    setTimeout(() => {
      // Refresh the table for the new day
      refreshAttendanceTable();
      
      // Update the date input to today
      const today = new Date().toISOString().split('T')[0];
      const dateInput = document.getElementById('date');
      if (dateInput) {
        dateInput.value = today;
        // Trigger flatpickr update if it exists
        if (window.fp) {
          window.fp.setDate(today, true);
        }
      }
      
      // Schedule the next midnight refresh
      scheduleMidnightRefresh();
    }, timeUntilMidnight);
  };

  // Start the midnight refresh scheduler
  scheduleMidnightRefresh();

  const showSimpleAlert = (type, title, text, timer = 2500) => {
    Swal.fire({ icon: type, title, text, timer: timer, showConfirmButton: false });
  };

  const showCustomToast = (message, bgColor) => {
    let container = document.getElementById('custom-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'custom-toast-container';
      Object.assign(container.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        zIndex: 9999,
        display: 'flex',
        flexDirection: 'column',
        gap: '10px'
      });
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    Object.assign(toast.style, {
      background: bgColor,
      color: '#133913',
      padding: '12px 20px',
      borderRadius: '10px',
      fontWeight: '600',
      fontSize: '14px',
      opacity: '1',
      transition: 'opacity 0.5s ease',
      display: 'flex',
      flexDirection: 'column',
      width: '300px'
    });

    const header = document.createElement('strong');
    header.textContent = 'Attendance time recorded';
    header.style.fontSize = '16px';
    header.style.marginBottom = '6px';

    const messageElem = document.createElement('span');
    messageElem.textContent = message;
    messageElem.style.fontWeight = 'normal';
    messageElem.style.fontSize = '14px';

    toast.appendChild(header);
    toast.appendChild(messageElem);
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => {
        container.removeChild(toast);
        if (!container.hasChildNodes()) container.remove();
      }, 500);
    }, 2000);
  };

  const fetchLogStatusAndSubmit = (empId) => {
    // Let the API handle schedule validation and determine the appropriate action
    // The API will check if the employee has a schedule and if current time is within schedule
    submitAttendance({ employee_id: empId });
  };

  const submitAttendance = (dataObj) => {
    fetch('../app/api/attendance-api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(dataObj).toString()
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          const type = data.type;
          const isIn = type.endsWith('in');
          const borderColor = isIn ? 'rgb(76, 180, 76)' : 'rgb(255, 119, 119)';
          const bgColor = isIn ? '#ecfdf5' : '#fef2f2';
          const name = formatName(data.name);
          const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
          const imageUrl = data.image_url || 'assets/image/default_user_image.svg';

          // Refresh table immediately with today's date (new attendance is always for today)
          const today = new Date().toISOString().split('T')[0];
          refreshAttendanceTable(today);
          
          // Update date picker to today if it's showing a different date
          const dateInput = document.getElementById("date");
          if (dateInput && window.fp) {
            window.fp.setDate(today, false); // false = don't trigger change event
          }

          Swal.fire({
  html: `
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
      <img src="${imageUrl}" alt="Employee Photo"
        style="height: 150px; width: 150px; border-radius: 50%; margin: 10px 0;
            border: 2px solid ${borderColor}; object-fit: cover;">
      <h2 style="margin: 5px 0 2px 0; font-weight: bold; font-size: 2rem;">${name}</h2>
      <p style="margin: 0;">${type.replace('-', ' ').toUpperCase()} successfully recorded</p>
    </div>
  `,
  showConfirmButton: false,
  timer: 2000,
  didOpen: () => {
    const popup = document.querySelector('.swal2-popup');
    if (popup) popup.style.border = '5px solid ' + borderColor;
    showCustomToast(`You have ${type.replace('-', ' ')} at ${currentTime}.`, bgColor);
  }
});

        } else {
          const alertTitle = data.status === 'warning' ? 'Restricted' : (data.status === 'info' ? 'Info' : 'Error');
          const timer = data.status === 'warning' ? 3000 : 2500; // 3 seconds for restricted warnings
          showSimpleAlert(data.status || 'error', alertTitle, data.message || 'Something went wrong.', timer);
        }

        if (rfidInput) {
          rfidInput.value = '';
          // Clear any pending timeout
          if (rfidInput.submitTimeout) {
            clearTimeout(rfidInput.submitTimeout);
          }
          // Refocus RFID input after a short delay to ensure it's ready
          setTimeout(() => {
            rfidInput.focus();
          }, 100);
        }
        if (employeeIdInput) employeeIdInput.value = '';
      })
      .catch(error => {
        console.error('Fetch Error:', error);
        showSimpleAlert('error', 'Error', 'Failed to submit attendance.');
      });
  };

  if (timeInBtn) {
    timeInBtn.addEventListener('click', () => {
      const empId = employeeIdInput?.value.trim();
      if (!empId) return showSimpleAlert('warning', 'Missing Input', 'Enter your Employee ID.');
      fetchLogStatusAndSubmit(empId);
    });
  }

  if (timeOutBtn) {
    timeOutBtn.addEventListener('click', () => {
      const empId = employeeIdInput?.value.trim();
      if (!empId) return showSimpleAlert('warning', 'Missing Input', 'Enter your Employee ID.');
      fetchLogStatusAndSubmit(empId);
    });
  }

  if (rfidInput) {
    // Focus RFID input immediately on page load
    setTimeout(() => {
      rfidInput.focus();
    }, 100);
    
    // Handle RFID card input (auto-submit when RFID is scanned)
    rfidInput.addEventListener('input', (e) => {
      const rfid = rfidInput.value.trim();
      // RFID cards typically send data quickly, so submit after a short delay
      if (rfid.length >= 8) { // Most RFID cards have at least 8 characters
        clearTimeout(rfidInput.submitTimeout);
        rfidInput.submitTimeout = setTimeout(() => {
          if (rfidInput.value.trim()) {
            submitAttendance({ rfid: rfidInput.value.trim() });
          }
        }, 300); // Wait 300ms for complete RFID scan
      }
    });
    
    rfidInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        const rfid = rfidInput.value.trim();
        if (!rfid) return showSimpleAlert('warning', 'Missing RFID', 'Scan your RFID first.');
        submitAttendance({ rfid });
      }
    });
  }

  document.body.addEventListener('click', (e) => {
    if (!rfidInput) return;
    const tag = e.target.tagName.toLowerCase();
    if (!['input', 'button', 'textarea'].includes(tag)) rfidInput.focus();
  });
});
</script>








<!-- Include footer -->
<?php require_once views_path("partials/footer"); ?>

<!-- Custom Style for SweetAlert Popup -->
<style>
.swal2-popup.custom-attendance-swal-popup {
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    padding: 20px;
    background-color: white;
    color: #333;
    border: 3px solid <?php echo isset($popup_border_color) ? $popup_border_color : 'white'; ?>;
    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif;
}

.swal2-popup.custom-attendance-swal-popup h2 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    color:rgb(10, 10, 10);
}

.swal2-popup.custom-attendance-swal-popup .swal2-html-container {
    margin: 10px 0;
    text-align: center;
}
</style>



