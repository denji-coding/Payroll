<?php
// Set page title
$title = "RFID Attendance";

// Include header partial
require_once views_path("partials/header");
?>

<!-- SweetAlert2 CDN for alert popups -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<!-- Layout wrapper: Sidebar + Main -->
<div class="flex w-screen h-screen overflow-hidden" style="font-family: ${defaultFont};">

    <!-- SIDEBAR -->
<aside class="w-72 bg-green-800 shadow-md fixed top-0 left-0 bottom-0 z-30 border-r border-green-700 overflow-y-auto">
    <div class="p-6 text-white flex flex-col h-full">
        
        <!-- Logo & Company Info -->
        <div class="text-center">
            <img src="../public/assets/image/logo.png" alt="Company Logo" 
                class="mx-auto w-28 h-28 rounded-full border-4 border-white/10 bg-white shadow">
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
              action="index.php?payroll=attendance"
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
        <input type="text" name="rfid" id="rfidInput" autocomplete="off" class="sr-only mt-4">
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


            <!-- Date Filter Form with Flatpickr -->
          <form method="GET" action="index.php" class="bg-white p-2 rounded-md mb-2 text-sm max-w-xs w-full mx-auto">
            <input type="hidden" name="payroll" value="attendance">

            <div class="flex items-center gap-2 w-full">
              <!-- Flatpickr Date Input -->
              <div class="relative flex-1 min-w-0">
                <input 
                  type="text" 
                  id="date" 
                  name="date" 
                  value="<?= htmlspecialchars($filterDate ?? date('Y-m-d')) ?>"
                  placeholder="Select a date"
                  class="w-full p-1 border border-emerald-300 rounded focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 text-emerald-700 text-center bg-white"
                >
              </div>
              

              <!-- Buttons -->
              <div class="flex-shrink-0 flex gap-1">
                <!-- Filter Button -->
                <button 
                  type="submit" 
                  class="w-20 h-8 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs whitespace-nowrap flex items-center justify-center px-2"
                >
                  <span class="text-sm">Filter</span> <i class="bi bi-filter text-sm ml-1"></i>
                </button>

                <!-- Show Clear Button only if filtered -->
                <?php if (isset($_GET['date']) && $_GET['date'] !== date('Y-m-d')): ?>
                  <button 
                    type="button" 
                    id="clearFilterBtn"
                    class="w-auto h-8 flex items-center justify-center gap-1 px-3 py-1 bg-red-700 hover:bg-red-800 text-white rounded text-xs whitespace-nowrap"
                  >
                  <i class="bi bi-eraser text-sm"></i>
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </form>

          <!-- Scripts -->
          <script>
            
                flatpickr("#date", {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                disableMobile: true,
                allowInput: true, // optional but helpful

                onReady: function(selectedDates, dateStr, instance) {
                    // Ensure calendar stays open when selecting month/year
                    const monthNav = instance.calendarContainer.querySelector('.flatpickr-monthDropdown-months');
                    if (monthNav) {
                        monthNav.addEventListener('click', function(e) {
                            e.stopPropagation(); // prevent close
                        });
                    }

                    const yearInput = instance.calendarContainer.querySelector('.numInputWrapper');
                    if (yearInput) {
                        yearInput.addEventListener('click', function(e) {
                            // e.stopPropagation(); // prevent close
                        });
                    }
                }
            });
            // Clear Filter Button Logic
            const clearBtn = document.getElementById("clearFilterBtn");
            if (clearBtn) {
              clearBtn.addEventListener("click", function () {
                const dateInput = document.getElementById("date");
                if (dateInput) {
                  dateInput._flatpickr.clear(); // Clear using Flatpickr API
                }

                // Submit form without date
                const form = clearBtn.closest("form");
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "payroll";
                hiddenInput.value = "attendance";
                form.innerHTML = "";
                form.appendChild(hiddenInput);
                form.submit();
              });
            }
          </script>

        <!-- Table of records -->
        <div class="overflow-x-auto max-h-[60vh] rounded-lg shadow-md bg-white">
            <table class="w-full text-base min-w-[700px]">
                <thead class="sticky top-0 bg-[#237339] text-white">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-bold" rowspan="2">NO.</th>
                        <th class="py-3 px-4 text-left text-xs font-bold" rowspan="2">PHOTO</th>
                        <th class="py-3 px-4 text-left text-xs font-bold" rowspan="2">EMP. ID</th>
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
                                        <tr>
                                            <td class="py-2 px-4 text-center"><?= $index + 1 . '.)' ?></td>
                                            <td class="py-2 px-4">
                                                <img src="<?= htmlspecialchars($record['photo_path'] ?: 'assets/image/default_user_image.svg') ?>" alt="Photo" class="h-10 w-10 rounded-full object-cover" />
                                            </td>
                                            <td class="py-2 text-sm px-4"><?= htmlspecialchars($record['employee_no']) ?></td>
                                            <td class="py-2 text-sm px-4"><?= htmlspecialchars(ucwords(strtolower($record['full_name']))) ?></td>

                                            <td class="py-2 text-sm px-4"><?= htmlspecialchars($record['position']) ?></td>
                                            <td class="py-2 text-sm text-center px-4">
                                                <?= $record['morning_in'] ? date('h:i A', strtotime($record['morning_in'])) : '-' ?>
                                            </td>
                                            <td class="py-2 text-sm text-center px-4">
                                                <?= $record['morning_out'] ? date('h:i A', strtotime($record['morning_out'])) : '-' ?>
                                            </td>
                                            <td class="py-2 text-sm text-center px-4">
                                                <?= $record['afternoon_in'] ? date('h:i A', strtotime($record['afternoon_in'])) : '-' ?>
                                            </td>
                                            <td class="py-2 text-sm text-center px-4">
                                                <?= $record['afternoon_out'] ? date('h:i A', strtotime($record['afternoon_out'])) : '-' ?>
                                            </td>
                                            <td class="py-2 px-4 text-sm text-center"><?= htmlspecialchars(date('F j, Y', strtotime($record['date']))) ?></td>
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
    return str
        .toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

document.addEventListener('DOMContentLoaded', () => {
    const rfidInput = document.getElementById('rfidInput');
    const employeeIdInput = document.getElementById('employeeIdInput');
    const manualTimeInBtn = document.getElementById('manualTimeInBtn');
    const manualTimeOutBtn = document.getElementById('manualTimeOutBtn');

    const showSimpleAlert = (type, title, text) => {
        Swal.fire({
            icon: type,
            title: title,
            text: text,
            timer: 2500,
            showConfirmButton: false
        });
    };

    const showCustomToast = (message, bgColor) => {
        let container = document.getElementById('custom-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'custom-toast-container';
            container.style.position = 'fixed';
            container.style.bottom = '20px';
            container.style.right = '20px';
            container.style.zIndex = 9999;
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '10px';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.style.background = bgColor;
        toast.style.color = '#133913';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '10px';
        toast.style.fontWeight = '600';
        toast.style.fontSize = '14px';
        toast.style.opacity = '1';
        toast.style.transition = 'opacity 0.5s ease';
        toast.style.display = 'flex';
        toast.style.flexDirection = 'column';
        toast.style.width = '300px';

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
                if (container.childElementCount === 0) container.remove();
            }, 500);
        }, 2000);
    };

    const submitAttendance = (dataObj) => {
        fetch('index.php?payroll=attendance', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(dataObj).toString()
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response failed');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                const type = data.type;
                const isIn = type.endsWith('in');
                const borderColor = isIn ? 'rgb(76, 180, 76)' : 'rgb(255, 119, 119)';
                const bgColor = isIn ? '#ecfdf5' : '#fef2f2';

                const name = formatName(data.name);
                const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                const imageUrl = data.image_url || 'assets/image/default_user_image.svg';

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
                }).then(() => location.reload());

            } else {
                showSimpleAlert(data.status || 'info', data.status?.toUpperCase() || 'Info', data.message || '');
            }

            if (rfidInput) rfidInput.value = '';
            if (employeeIdInput) employeeIdInput.value = '';
            if (rfidInput) rfidInput.focus();
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showSimpleAlert('error', 'Error', 'Failed to submit attendance.');
        });
    };

    if (rfidInput) {
        rfidInput.focus();
        rfidInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const rfid = rfidInput.value.trim();
                if (!rfid) {
                    showSimpleAlert('warning', 'Missing Input', 'Please enter your RFID.');
                    return;
                }
                submitAttendance({ rfid });
            }
        });
    }

    if (employeeIdInput) {
        employeeIdInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault();
        });
    }

    if (manualTimeInBtn) {
        manualTimeInBtn.addEventListener('click', () => {
            const empId = employeeIdInput?.value.trim();
            if (!empId) {
                showSimpleAlert('warning', 'Missing Input', 'Please enter your Employee ID.');
                employeeIdInput.focus();
                return;
            }
            submitAttendance({ employee_id: empId, manual_type: 'morning-in' });
        });
    }

    if (manualTimeOutBtn) {
        manualTimeOutBtn.addEventListener('click', () => {
            const empId = employeeIdInput?.value.trim();
            if (!empId) {
                showSimpleAlert('warning', 'Missing Input', 'Please enter your Employee ID.');
                employeeIdInput.focus();
                return;
            }
            submitAttendance({ employee_id: empId, manual_type: 'afternoon-out' });
        });
    }

    document.body.addEventListener('click', (e) => {
        if (!rfidInput) return;
        const tag = e.target.tagName.toLowerCase();
        if (!['input', 'button', 'textarea'].includes(tag) && !e.target.closest('#manualAttendanceForm')) {
            rfidInput.focus();
        }
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



