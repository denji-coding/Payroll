<?php
$title = "Time Records";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");
?>

<main class="flex-1 overflow-auto p-4 md:p-6 ml-[255px] mt-12 bg-[#f8fbf8] min-h-[calc(100vh-3rem)] h-full">
    <div class="space-y-6 h-full">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="text-2xl font-bold tracking-tight text-[#133913]">Time Records</span>
                <p class="text-[#478547]">Track and manage employee attendance</p>
            </div>
            <div class="flex gap-3">
                <button class="btn btn-success d-flex align-items-center justify-content-center h-10 px-4 py-2 gap-3">
                    <i class="bi bi-download h-4 w-4 -mt-2"></i>
                    <span>Export</span>
                </button>
            </div>
        </div>

        <div class="rounded-lg border-2 border-green-200 bg-white text-card-foreground shadow-sm"
            data-aos="fade-in" 
            data-aos-delay="<?= $index * 1 ?>"
            data-aos-duration="200">
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between">
                <span class="text-2xl font-semibold leading-none tracking-tight text-[#133913]">Attendance Records</span>
                <div class="flex items-center gap-4">
                    <div class="relative w-64">
                        <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                        <input
                            type="text"
                            id="searchInput"
                            class="flex h-10 w-full placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
                            placeholder="Search employee..."
                            oninput="toggleClearButton()"
                        >
                        <button id="clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden hover:text-[#16a249]" onclick="clearInput()">×</button>
                    </div>
                    <div class="relative">
                        <button type="button" role="combobox" aria-controls="statusDropdown" aria-expanded="false" aria-autocomplete="none" dir="ltr" data-state="closed" class="flex h-10 items-center justify-between rounded border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 w-[150px] transition-all duration-200" onclick="toggleStatusDropdown(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-filter mr-2 h-4 w-4">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            <span class="text-gray-900">All Status</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-4 w-4 opacity-50" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </button>
                        <div id="statusDropdown" class="absolute right-0 mt-2 w-48 rounded-md bg-white ring-1 ring-black ring-opacity-5 hidden z-50 transform origin-top-right">
                            <div class="py-1" role="menu" aria-orientation="vertical">
                                <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" role="menuitem" onclick="selectStatus(this, 'All Status')">
                                    <i class="bi bi-people-fill mr-2"></i>
                                    <span>All Status</span>
                                </a>
                                <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" role="menuitem" onclick="selectStatus(this, 'Present')">
                                    <i class="bi bi-check-circle-fill text-green-500 mr-2"></i>
                                    <span>Present</span>
                                </a>
                                <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" role="menuitem" onclick="selectStatus(this, 'Late')">
                                    <i class="bi bi-clock-fill text-yellow-500 mr-2"></i>
                                    <span>Late</span>
                                </a>
                                <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" role="menuitem" onclick="selectStatus(this, 'Absent')">
                                    <i class="bi bi-x-circle-fill text-red-500 mr-2"></i>
                                    <span>Absent</span>
                                </a>
                                <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" role="menuitem" onclick="selectStatus(this, 'Half Day')">
                                    <i class="bi bi-hourglass-split text-blue-500 mr-2"></i>
                                    <span>Half Day</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <button type="button" class="flex h-10 items-center justify-between rounded  border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:ring-offset-2 transition-all duration-200" onclick="openCalendarModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar mr-2 h-4 w-4">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span class="text-gray-900" id="selectedDate">Select Date</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-4 w-4 opacity-50" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6 pt-0">
                <div class="relative w-full overflow-auto">
                    <div class="max-h-[calc(100vh-300px)] overflow-y-auto">
                        <table class="w-full caption-bottom text-sm">
                            <thead class="bg-[#f2f8f2] sticky top-0 z-10">
                                <!-- Header Row 1 -->
                                <tr class="transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                    <th rowspan="2" class="h-12 px-4 text-left align-middle font-bold text-[#478547] bg-white">No.</th>
                                    <th rowspan="2" class="h-12 px-4 text-left align-middle font-bold text-[#478547] bg-white">Photo</th>
                                    <th rowspan="2" class="h-12 px-4 text-left align-middle font-bold text-[#478547] bg-white">Employee</th>
                                    <th rowspan="2" class="h-12 px-4 text-left align-middle font-bold text-[#478547] bg-white">Position</th>
                                    <th rowspan="2" class="h-12 px-4 text-left align-middle font-bold text-[#478547] bg-white">Date</th>

                                    <!-- Grouped: Morning IN -->
                                    <th colspan="2" class="h-12 px-4 text-center align-bottom font-bold text-[#478547] bg-white border-b-0">Morning</th>

                                    <!-- Grouped: Afternoon OUT -->
                                    <th colspan="2" class="h-12 px-4 text-center align-bottom font-bold text-[#478547] bg-white border-b-0">Afternoon</th>

                                    <th rowspan="2" class="h-12 px-4 text-left align-middle font-bold text-[#478547] bg-white">Status</th>
                                </tr>

                                <!-- Header Row 2 (subheaders: IN / OUT for both groups) -->
                                <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                    <!-- Morning IN -->
                                <th class="h-7 px-4 w-[80px] text-center border-r align-middle font-bold text-[#478547] bg-white">IN</th>
                                <th class="h-7 px-4 w-[80px] text-center align-middle font-bold text-[#478547] bg-white">OUT</th>

                                <!-- Afternoon OUT -->
                                <th class="h-7 px-4 w-[80px] text-center border-r align-middle font-bold text-[#478547] bg-white">IN</th>
                                <th class="h-7 px-4 w-[80px] text-center align-middle font-bold text-[#478547] bg-white">OUT</th>

                                </tr>
                            </thead>


                            <tbody class="[&_tr:last-child]:border-0" id="attendanceTableBody">
                                <!-- Sample data - This will be replaced with dynamic data -->
                                
                                <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                    <td class="p-4 align-middle font-medium">1</td>
                                    <td class="p-4 align-middle font-medium">
                                        <span class="relative flex shrink-0 overflow-hidden rounded-full h-8 w-8">
                                            <img class="aspect-square h-full w-full" src="../public/assets/images/employee1.svg" alt="">
                                        </span>
                                    </td>
                                    <td class="p-4 align-middle font-medium">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex flex-col">
                                                <span class="font-medium">John Doe</span>
                                                <span class="text-xs text-gray-500">EMP-001</span>
                                        </div>
                                        </div>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <span class="text-sm">Software Engineer</span>
                                    </td>
                                    <td class="p-4 align-middle">2024-03-15</td>
                                    <!-- Morning IN/OUT -->
                                    <td class="p-4 align-middle text-center">08:00 AM</td>
                                    <td class="p-4 align-middle text-center">11:00 AM</td>
                                    <!-- Afternoon IN/OUT -->
                                    <td class="p-4 align-middle text-center">01:00 PM</td>
                                    <td class="p-4 align-middle text-center">04:00 PM</td>
                                    <!-- Status -->
                                    <td class="p-4 align-middle">
                                        <div class="inline-flex items-center rounded-full border border-transparent bg-green-100 text-green-800 px-2.5 py-0.5 text-xs font-semibold">Present</div>
                                    </td>
                                </tr>

                                    <!-- Actions column cell -->
                                    <!-- <td class="p-4 align-middle">
                                        <div class="flex gap-2">
                                            <button class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium hover:bg-[#478547] hover:text-white transition duration-100 transform hover:scale-105" onclick="openModal('editTimeRecordModal')" type="button">
                                                <i class="bi bi-pencil-square h-5 w-5 md:h-7 md:w-7 text-lg"></i>
                                            </button>
                                            <button class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium hover:bg-[#b91c1c] hover:text-white transition duration-100 transform hover:scale-105" onclick="openModal('deleteTimeRecordModal')" type="button">
                                                <i class="bi bi-trash  h-5 w-5 md:h-7 md:w-7 text-lg"></i>
                                            </button>
                                        </div>
                                    </td> -->
                                    </tr>
                            </tbody>
                        </table>
                        <!-- No Records Message -->
                        <div id="noRecordsMessage" class="hidden p-8 text-center">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="bi bi-calendar-x text-4xl text-gray-400"></i>
                                <p class="text-lg font-medium text-gray-500">No attendance records found for this date</p>
                                <p class="text-sm text-gray-400">Select another date or check back later</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Edit Time Record Modal -->
<div id="editTimeRecordModalOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 opacity-0 transition-opacity duration-500" onclick="closeModal('editTimeRecordModal')"></div>
<div id="editTimeRecordModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-md w-full max-w-md shadow-lg">
        <div class="space-y-4">
            <h2 class="text-xl font-semibold">Edit Time Record</h2>
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Employee</label>
                    <input type="text" class="w-full p-2 border rounded" value="John Doe" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Date</label>
                    <input type="date" class="w-full p-2 border rounded">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Time In</label>
                        <input type="time" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Time Out</label>
                        <input type="time" class="w-full p-2 border rounded">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md border border-[#cde4cd] bg-[#f8fbf8] px-4 py-2 text-sm font-medium ring-offset-[#f8fbf8] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 hover:bg-[#16a249] hover:text-[#ffffff] disabled:pointer-events-none disabled:opacity-50" onclick="closeModal('editTimeRecordModal')">
                        <i class="bi bi-x h-4 w-4"></i>
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-[#16a249] px-4 py-2 text-sm font-medium text-white hover:bg-[#16a249]/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2">
                        <i class="bi bi-check h-4 w-4"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Time Record Modal -->
<div id="deleteTimeRecordModalOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 opacity-0 transition-opacity duration-500" onclick="closeModal('deleteTimeRecordModal')"></div>
<div id="deleteTimeRecordModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-md w-full max-w-md shadow-lg">
        <div class="flex flex-col items-center gap-4">
            <div class="rounded-full bg-red-100 p-3">
                <i class="bi bi-exclamation-triangle-fill text-red-600 text-2xl"></i>
            </div>
            <div class="text-center">
                <h2 class="text-xl font-semibold text-gray-900">Delete Time Record</h2>
                <p class="mt-2 text-sm text-gray-500">Are you sure you want to delete this time record? This action cannot be undone.</p>
            </div>
            <div class="flex justify-center gap-3 mt-4">
                <button type="button" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md border border-[#cde4cd] bg-[#f8fbf8] px-4 py-2 text-sm font-medium ring-offset-[#f8fbf8] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 hover:bg-[#16a249] hover:text-[#ffffff] disabled:pointer-events-none disabled:opacity-50" onclick="closeModal('deleteTimeRecordModal')">
                    <i class="bi bi-x h-4 w-4"></i>
                    Cancel
                </button>
                <button type="button" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2" onclick="confirmDeleteTimeRecord()">
                    <i class="bi bi-trash h-4 w-4"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Modal -->
<div id="calendarModalOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 opacity-0 transition-opacity duration-500" onclick="closeCalendarModal()"></div>
<div id="calendarModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-md w-full max-w-md shadow-lg">
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-[#16a249] text-center">Select Date</h2>
                <button onclick="closeCalendarModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="bi bi-x-lg h-5 w-5"></i>
                </button>
            </div>
            <div class="flex justify-between items-center mb-4">
                <button onclick="prevMonth()" class="p-2 hover:bg-gray-100 rounded-full">
                    <i class="bi bi-chevron-left h-5 w-5"></i>
                </button>
                <span id="currentMonth" class="text-lg font-medium text-[#16a249]"></span>
                <button onclick="nextMonth()" class="p-2 hover:bg-gray-100 rounded-full">
                    <i class="bi bi-chevron-right h-5 w-5"></i>
                </button>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center text-sm font-medium text-gray-500 mb-2">
                <div>Su</div>
                <div>Mo</div>
                <div>Tu</div>
                <div>We</div>
                <div>Th</div>
                <div>Fr</div>
                <div>Sa</div>
            </div>
            <div id="calendarDays" class="grid grid-cols-7 gap-1"></div>
        </div>
    </div>
</div>

<script>
// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById(modalId + 'Overlay');
    
    if (modal && overlay) {
        modal.classList.remove('hidden');
        overlay.classList.remove('hidden');
        
        setTimeout(() => {
            modal.classList.remove('opacity-0', 'scale-75');
            modal.classList.add('opacity-100', 'scale-100');
            overlay.classList.add('opacity-100');
        }, 10);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById(modalId + 'Overlay');
    
    if (modal && overlay) {
        modal.classList.add('opacity-0', 'scale-75');
        modal.classList.remove('opacity-100', 'scale-100');
        overlay.classList.remove('opacity-100');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            overlay.classList.add('hidden');
        }, 300);
    }
}

function confirmDeleteTimeRecord() {
    Swal.fire({
        icon: 'success',
        title: 'Time Record deleted!',
        text: 'The time record has been removed from the system.',
        showConfirmButton: false,
        timer: 1000,
        timerProgressBar: true,
    }).then(() => {
        closeModal("deleteTimeRecordModal");
    });
}

// Close modals when clicking outside
document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        const openModal = document.querySelector(".modal:not(.hidden)");
        if (openModal) {
            closeModal(openModal.id);
        }
    }
});

let currentStatus = 'All Status';

function toggleStatusDropdown(button) {
    const dropdown = document.getElementById('statusDropdown');
    const isExpanded = button.getAttribute('aria-expanded') === 'true';
    
    // Close all other dropdowns
    document.querySelectorAll('[aria-expanded="true"]').forEach(el => {
        if (el !== button) {
            el.setAttribute('aria-expanded', 'false');
            el.nextElementSibling?.classList.add('hidden');
        }
    });
    
    // Toggle current dropdown
    button.setAttribute('aria-expanded', !isExpanded);
    dropdown.classList.toggle('hidden');
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!button.contains(e.target) && !dropdown.contains(e.target)) {
            button.setAttribute('aria-expanded', 'false');
            dropdown.classList.add('hidden');
            document.removeEventListener('click', closeDropdown);
        }
    });
}

function selectStatus(link, status) {
    const button = link.closest('.relative').querySelector('button');
    const span = button.querySelector('span');
    span.textContent = status;
    currentStatus = status;
    
    // Update active state
    const statusButton = document.querySelector('[role="combobox"]');
    if (status === 'All Status') {
        statusButton.classList.remove('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
    } else {
        statusButton.classList.add('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
    }
    
    // Close dropdown
    button.setAttribute('aria-expanded', 'false');
    document.getElementById('statusDropdown').classList.add('hidden');
    
    // Filter the table based on the selected status
    filterTableByStatus(status);
}

function filterTableByStatus(status) {
    const tbody = document.getElementById('attendanceTableBody');
    const noRecordsMessage = document.getElementById('noRecordsMessage');
    const rows = tbody.querySelectorAll('tr');
    let hasVisibleRows = false;
    
    rows.forEach(row => {
        const statusCell = row.querySelector('td:nth-child(7)');
        const statusText = statusCell.querySelector('.inline-flex').textContent.trim();
        
        if (status === 'All Status' || statusText === status) {
            row.style.display = '';
            hasVisibleRows = true;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show/hide no records message
    if (!hasVisibleRows) {
        tbody.style.display = 'none';
        noRecordsMessage.classList.remove('hidden');
        noRecordsMessage.innerHTML = `
            <div class="flex flex-col items-center justify-center gap-4">
                <i class="bi bi-calendar-x text-4xl text-gray-400"></i>
                <p class="text-lg font-medium text-gray-500">No ${status.toLowerCase()} records found</p>
                <p class="text-sm text-gray-400">Try selecting a different status or date</p>
                <button onclick="resetStatusFilter()" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md border border-[#cde4cd] bg-[#f8fbf8] px-4 py-2 text-sm font-medium ring-offset-[#f8fbf8] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 hover:bg-[#16a249] hover:text-[#ffffff] disabled:pointer-events-none disabled:opacity-50">
                    <i class="bi bi-arrow-counterclockwise h-4 w-4"></i>
                    Reset Filter
                </button>
            </div>
        `;
    } else {
        tbody.style.display = '';
        noRecordsMessage.classList.add('hidden');
    }
}

function resetStatusFilter() {
    const statusButton = document.querySelector('[role="combobox"]');
    const statusSpan = statusButton.querySelector('span');
    statusSpan.textContent = 'All Status';
    currentStatus = 'All Status';
    filterTableByStatus('All Status');
}

let currentDate = new Date();

function openCalendarModal() {
    const modal = document.getElementById('calendarModal');
    const overlay = document.getElementById('calendarModalOverlay');
    
    if (modal && overlay) {
        modal.classList.remove('hidden');
        overlay.classList.remove('hidden');
        
        setTimeout(() => {
            modal.classList.remove('opacity-0', 'scale-75');
            modal.classList.add('opacity-100', 'scale-100');
            overlay.classList.add('opacity-100');
        }, 10);
        
        updateCalendar();
    }
}

function closeCalendarModal() {
    const modal = document.getElementById('calendarModal');
    const overlay = document.getElementById('calendarModalOverlay');
    
    if (modal && overlay) {
        modal.classList.add('opacity-0', 'scale-75');
        modal.classList.remove('opacity-100', 'scale-100');
        overlay.classList.remove('opacity-100');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            overlay.classList.add('hidden');
        }, 300);
    }
}

function updateCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Update month display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
    
    // Get first day of month
    const firstDay = new Date(year, month, 1);
    const startingDay = firstDay.getDay();
    
    // Get last day of month
    const lastDay = new Date(year, month + 1, 0);
    const totalDays = lastDay.getDate();
    
    // Clear previous calendar
    const calendarDays = document.getElementById('calendarDays');
    calendarDays.innerHTML = '';
    
    // Add empty cells for days before the first day of the month
    for (let i = 0; i < startingDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'p-2';
        calendarDays.appendChild(emptyCell);
    }
    
    // Add days of the month
    for (let day = 1; day <= totalDays; day++) {
        const dayCell = document.createElement('div');
        dayCell.className = 'p-2 cursor-pointer hover:bg-[#f2f8f2] rounded text-center';
        dayCell.textContent = day;
        
        // Highlight current day
        const today = new Date();
        if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
            dayCell.classList.add('bg-[#16a249]', 'text-white');
        }
        
        dayCell.onclick = () => selectDate(day, month, year);
        calendarDays.appendChild(dayCell);
    }
}

function prevMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    updateCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    updateCalendar();
}

async function selectDate(day, month, year) {
    const selectedDate = new Date(year, month, day);
    const formattedDate = selectedDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Update button text and active state
    const dateButton = document.querySelector('button[onclick="openCalendarModal()"]');
    dateButton.classList.add('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
    document.getElementById('selectedDate').textContent = formattedDate;
    
    // Close modal
    closeCalendarModal();
    
    // Fetch and display attendance records
    const response = await fetchAttendanceRecords(selectedDate);
    if (response.success) {
        updateAttendanceTable(response.data, selectedDate);
        // Apply status filter after updating the table
        filterTableByStatus(currentStatus);
    } else {
        console.error(response.error);
    }
}

// Add status handling functions
function getStatusStyle(status) {
    const styles = {
        'Present': {
            bg: 'bg-green-100',
            text: 'text-green-800',
            border: 'border-green-200',
            icon: 'bi-check-circle'
        },
        'Late': {
            bg: 'bg-yellow-100',
            text: 'text-yellow-800',
            border: 'border-yellow-200',
            icon: 'bi-clock'
        },
        'Absent': {
            bg: 'bg-red-100',
            text: 'text-red-800',
            border: 'border-red-200',
            icon: 'bi-x-circle'
        },
        'Half Day': {
            bg: 'bg-blue-100',
            text: 'text-blue-800',
            border: 'border-blue-200',
            icon: 'bi-hourglass-split'
        }
    };
    
    return styles[status] || styles['Present'];
}

function getStatusDetails(status, timeIn = null) {
    const details = {
        'Present': {
            message: 'On time',
            time: timeIn ? `Arrived at ${timeIn}` : ''
        },
        'Late': {
            message: 'Late arrival',
            time: timeIn ? `Arrived at ${timeIn}` : ''
        },
        'Absent': {
            message: 'No attendance recorded',
            time: ''
        },
        'Half Day': {
            message: 'Worked half day',
            time: timeIn ? `Started at ${timeIn}` : ''
        }
    };
    
    return details[status] || details['Present'];
}

// Update the updateAttendanceTable function
function updateAttendanceTable(records, selectedDate) {
    const tbody = document.getElementById('attendanceTableBody');
    const noRecordsMessage = document.getElementById('noRecordsMessage');
    
    // Clear existing rows
    tbody.innerHTML = '';
    
    if (records.length === 0) {
        tbody.style.display = 'none';
        noRecordsMessage.classList.remove('hidden');
        return;
    }
    
    tbody.style.display = '';
    noRecordsMessage.classList.add('hidden');
    
    // Add new rows with proper numbering
    records.forEach((record, index) => {
        const statusStyle = getStatusStyle(record.status);
        const statusDetails = getStatusDetails(record.status, record.time_in);
        
        const row = document.createElement('tr');
        row.className = 'border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]';
        
        row.innerHTML = `
            <td class="p-4 align-middle font-medium">${index + 1}</td>
            <td class="p-4 align-middle font-medium">
                <div class="flex items-center space-x-2">
                    <span class="relative flex shrink-0 overflow-hidden rounded-full h-8 w-8">
                        <img class="aspect-square h-full w-full" src="${record.avatar}" alt="">
                    </span>
                    <div class="flex flex-col">
                        <span class="font-medium">${record.name}</span>
                        <span class="text-xs text-gray-500">${record.employee_id}</span>
                    </div>
                </div>
            </td>
            <td class="p-4 align-middle">
                <span class="text-sm">${record.position}</span>
            </td>
            <td class="p-4 align-middle">${record.date}</td>
            <td class="p-4 align-middle">${record.time_in}</td>
            <td class="p-4 align-middle">${record.time_out}</td>
            <td class="p-4 align-middle">
                <div class="group relative">
                    <div class="inline-flex items-center rounded-full border ${statusStyle.border} ${statusStyle.bg} ${statusStyle.text} px-2.5 py-0.5 text-xs font-semibold cursor-pointer">
                        <i class="bi ${statusStyle.icon} mr-1"></i>
                        ${record.status}
                    </div>
                    <div class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white p-2 hidden group-hover:block z-50">
                        <div class="text-sm">
                            <div class="font-medium ${statusStyle.text}">${statusDetails.message}</div>
                            ${statusDetails.time ? `<div class="text-gray-500 text-xs mt-1">${statusDetails.time}</div>` : ''}
                        </div>
                    </div>
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
    
    // Apply current status filter after updating the table
    filterTableByStatus(currentStatus);
}

// Update the sample data in fetchAttendanceRecords
// async function fetchAttendanceRecords(date) {
//     try {
//         // Here you would make an AJAX call to your backend
//         // Example:
//         // const response = await fetch(`/api/attendance?date=${date.toISOString()}`);
//         // const data = await response.json();
//         // return data;
        
//         // Sample data with various attendance statuses (removed Half Day records)
//         return {
//             success: true,
//             data: [
//                 {
//                     employee_id: "EMP-001",
//                     name: "John Doe",
//                     position: "Software Engineer",
//                     avatar: "../public/assets/images/employee1.svg",
//                     date: "2024-03-15",
//                     time_in: "08:00 AM",
//                     time_out: "05:00 OUT",
//                     status: "Present"
//                 },
//                 {
//                     employee_id: "EMP-002",
//                     name: "Jane Smith",
//                     position: "UI/UX Designer",
//                     avatar: "../public/assets/images/employee2.svg",
//                     date: "2024-03-15",
//                     time_in: "09:15 AM",
//                     time_out: "05:30 OUT",
//                     status: "Late"
//                 },
//                 {
//                     employee_id: "EMP-003",
//                     name: "Mike Johnson",
//                     position: "Project Manager",
//                     avatar: "../public/assets/images/employee3.svg",
//                     date: "2024-03-15",
//                     time_in: null,
//                     time_out: null,
//                     status: "Absent"
//                 },
//                 {
//                     employee_id: "EMP-005",
//                     name: "David Brown",
//                     position: "Senior Developer",
//                     avatar: "../public/assets/images/employee5.svg",
//                     date: "2024-03-15",
//                     time_in: "08:05 AM",
//                     time_out: "05:05 OUT",
//                     status: "Present"
//                 },
//                 {
//                     employee_id: "EMP-006",
//                     name: "Emily Davis",
//                     position: "Marketing Specialist",
//                     avatar: "../public/assets/images/employee6.svg",
//                     date: "2024-03-15",
//                     time_in: "10:30 AM",
//                     time_out: "06:30 OUT",
//                     status: "Late"
//                 },
//                 {
//                     employee_id: "EMP-007",
//                     name: "Robert Taylor",
//                     position: "System Administrator",
//                     avatar: "../public/assets/images/employee7.svg",
//                     date: "2024-03-15",
//                     time_in: null,
//                     time_out: null,
//                     status: "Absent"
//                 },
//                 {
//                     employee_id: "EMP-009",
//                     name: "James Wilson",
//                     position: "Database Administrator",
//                     avatar: "../public/assets/images/employee9.svg",
//                     date: "2024-03-15",
//                     time_in: "08:10 AM",
//                     time_out: "05:10 OUT",
//                     status: "Present"
//                 },
//                 {
//                     employee_id: "EMP-010",
//                     name: "Patricia Moore",
//                     position: "Business Analyst",
//                     avatar: "../public/assets/images/employee10.svg",
//                     date: "2024-03-15",
//                     time_in: "09:45 AM",
//                     time_out: "06:45 OUT",
//                     status: "Late"
//                 }
//             ]
//         };
//     } catch (error) {
//         console.error('Error fetching attendance records:', error);
//         return { success: false, error: 'Failed to fetch attendance records' };
//     }
// }

// Initialize with current date
document.addEventListener('DOMContentLoaded', () => {
    // Set current date in the button
    const today = new Date();
    const formattedDate = today.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    const dateButton = document.querySelector('button[onclick="openCalendarModal()"]');
    dateButton.classList.add('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
    document.getElementById('selectedDate').textContent = formattedDate;
    
    // Initialize calendar
    updateCalendar();
    
    // Load attendance records for current date
    fetchAttendanceRecords(today).then(response => {
        if (response.success) {
            updateAttendanceTable(response.data, today);
            filterTableByStatus(currentStatus);
        }
    });

    // Initialize clear button state
    toggleClearButton();
});

// Search bar clear button functions
function toggleClearButton() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearButton');
    
    if (searchInput.value.trim().length > 0) {
        clearButton.classList.remove('hidden');
    } else {
        clearButton.classList.add('hidden');
    }
}

function clearInput() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearButton');
    
    searchInput.value = '';
    clearButton.classList.add('hidden');
    // Trigger search if you have a search function
    // searchTable();
}
</script>

<?php require_once views_path("partials/footer"); ?>