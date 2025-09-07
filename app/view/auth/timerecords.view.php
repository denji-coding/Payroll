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
                <button class="btn btn-success d-flex align-items-center justify-content-center h-10 px-4 py-2 gap-3" onclick="openExportModal()">
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
                            oninput="handleSearchInput()"
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
                    <div>
                        <button title="Show All" id="showAllBtn" type="button" class="flex h-10 items-center justify-between rounded border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:ring-offset-2 transition-all duration-200" onclick="showAllAttendance()">
                            <i class="bi bi-list-ul "></i>
                            <!-- <span class="text-gray-900">Show All</span> -->
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
                                <?php if (!empty($attendanceRecords)): ?>
                                    <?php foreach ($attendanceRecords as $index => $rec): ?>
                                        <?php 
                                            $photo = !empty($rec['photo_path']) ? $rec['photo_path'] : '../public/assets/image/default_user_image.svg';
                                            $name  = ucwords(strtolower($rec['full_name']));
                                            $pos   = $rec['position'] ?? '';
                                            $date  = date('Y-m-d', strtotime($rec['date']));
                                            $min   = $rec['morning_in']   ? date('h:i A', strtotime($rec['morning_in']))   : '-';
                                            $mout  = $rec['morning_out']  ? date('h:i A', strtotime($rec['morning_out']))  : '-';
                                            $ain   = $rec['afternoon_in'] ? date('h:i A', strtotime($rec['afternoon_in'])) : '-';
                                            $aout  = $rec['afternoon_out']? date('h:i A', strtotime($rec['afternoon_out'])): '-';
                                            $status= $rec['status'] ?? 'Present';
                                            $statusClass = 'bg-green-100 text-green-800 border-green-200';
                                            if ($status === 'Late') $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                            if ($status === 'Absent') $statusClass = 'bg-red-100 text-red-800 border-red-200';
                                        ?>
                                <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                            <td class="p-2 align-middle font-medium"><?= $index + 1 ?></td>
                                    <td class="p-2 align-middle font-medium">
                                        <span class="relative flex shrink-0 overflow-hidden rounded-full h-8 w-8">
                                                    <img class="aspect-square h-full w-full" src="<?= htmlspecialchars($photo) ?>" alt="">
                                        </span>
                                    </td>
                                    <td class="p-2 align-middle font-medium">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex flex-col">
                                                        <span class="font-medium text-sm"><?= htmlspecialchars($name) ?></span>
                                                        <span class="text-[11px] text-gray-500"><?= htmlspecialchars($rec['employee_no']) ?></span>
                                        </div>
                                        </div>
                                    </td>
                                    <td class="p-2 align-middle">
                                                <span class="text-sm"><?= htmlspecialchars($pos) ?></span>
                                    </td>
                                            <td class="p-2 align-middle"><?= htmlspecialchars($date) ?></td>
                                            <td class="p-2 align-middle text-center"><?= $min ?></td>
                                            <td class="p-2 align-middle text-center"><?= $mout ?></td>
                                            <td class="p-2 align-middle text-center"><?= $ain ?></td>
                                            <td class="p-2 align-middle text-center"><?= $aout ?></td>
                                    <td class="p-2 align-middle">
                                                <div class="inline-flex items-center rounded-full border <?= $statusClass ?> px-2.5 py-0.5 text-xs font-semibold"><?= htmlspecialchars($status) ?></div>
                                    </td>
                                </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="px-4 py-6 text-center text-secondary fst-italic bg-light">
                                            <i class="bi bi-calendar-x me-2"></i>No attendance records found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
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
                        <div id="paginationBar" class="flex items-center justify-between mt-3 hidden">
                            <div class="text-sm">
                                Page <span id="pageNum">1</span> of <span id="pageTotal">1</span> — <span id="pageCount">0</span> records
                            </div>
                            <div class="flex items-center gap-2">
                                <button id="prevPageBtn" class="px-3 py-1 rounded border text-sm hover:bg-[#f2f8f2]" onclick="changePage(-1)">Previous</button>
                                <button id="nextPageBtn" class="px-3 py-1 rounded border text-sm hover:bg-[#f2f8f2]" onclick="changePage(1)">Next</button>
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

<!-- Export Modal -->
<div id="exportModalOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 opacity-0 transition-opacity duration-500" onclick="closeExportModal()"></div>
<div id="exportModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-md w-full max-w-md shadow-lg">
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-[#16a249] text-center">Export Attendance Report</h2>
                <button onclick="closeExportModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="bi bi-x-lg h-5 w-5"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700">Select Month and Year</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <select id="exportMonth" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:border-transparent">
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div>
                            <select id="exportYear" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:border-transparent">
                                <?php for ($year = date('Y'); $year >= 2020; $year--): ?>
                                    <option value="<?= $year ?>" <?= $year == date('Y') ? 'selected' : '' ?>><?= $year ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700">Export Format</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center p-3 border border-gray-300 rounded-md cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="exportFormat" value="csv" class="mr-2" checked>
                            <div>
                                <div class="font-medium text-sm">CSV Format</div>
                                <div class="text-xs text-gray-500">Simple data, opens in Excel</div>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border border-gray-300 rounded-md cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="exportFormat" value="html" class="mr-2">
                            <div>
                                <div class="font-medium text-sm">HTML Format</div>
                                <div class="text-xs text-gray-500">Web view with styling</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                    <div class="flex items-start">
                        <i class="bi bi-info-circle text-blue-500 mt-0.5 mr-2"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium">Export Information:</p>
                            <ul class="mt-1 space-y-1">
                                <li>• Exports all employee attendance for the selected month</li>
                                <li>• Includes time in/out, status, and total hours worked</li>
                                <li>• CSV: Simple data format for Excel analysis</li>
                                <li>• HTML: Professional styling with colored headers</li>
                                <li>• HTML: Web view with browser styling</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md border border-[#cde4cd] bg-[#f8fbf8] px-4 py-2 text-sm font-medium ring-offset-[#f8fbf8] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 hover:bg-[#16a249] hover:text-[#ffffff] disabled:pointer-events-none disabled:opacity-50" onclick="closeExportModal()">
                    <i class="bi bi-x h-4 w-4"></i>
                    Cancel
                </button>
                <button type="button" id="exportBtn" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-[#16a249] px-4 py-2 text-sm font-medium text-white hover:bg-[#16a249]/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2" onclick="exportAttendance()">
                    <i class="bi bi-download h-4 w-4"></i>
                    Export Report
                </button>

            </div>
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
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput.value.trim().toLowerCase();
    let hasVisibleRows = false;
    
    rows.forEach(row => {
        const statusCell = row.querySelector('td:nth-child(10)');
        if (!statusCell) return;
        const badge = statusCell.querySelector('.inline-flex');
        const statusText = (badge ? badge.textContent : statusCell.textContent).trim();
        
        // Check status filter
        const statusMatches = status === 'All Status' || statusText === status;
        
        // Check search filter
        let searchMatches = true;
        if (searchTerm.length > 0) {
            const nameCell = row.querySelector('td:nth-child(3)');
            if (nameCell) {
                const nameElement = nameCell.querySelector('.font-medium');
                const idElement = nameCell.querySelector('.text-gray-500');
                
                if (nameElement && idElement) {
                    const employeeName = nameElement.textContent.toLowerCase();
                    const employeeId = idElement.textContent.toLowerCase();
                    searchMatches = employeeName.includes(searchTerm) || employeeId.includes(searchTerm);
                }
            }
        }
        
        // Show row only if both status and search match
        if (statusMatches && searchMatches) {
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
        
        // Determine the appropriate message based on active filters
        let message = '';
        let icon = 'bi-calendar-x';
        
        if (searchTerm.length > 0 && status !== 'All Status') {
            message = `No ${status.toLowerCase()} records found for "${searchTerm}"`;
            icon = 'bi-search';
        } else if (searchTerm.length > 0) {
            message = `No employees found for "${searchTerm}"`;
            icon = 'bi-search';
        } else if (status !== 'All Status') {
            message = `No ${status.toLowerCase()} records found`;
            icon = 'bi-calendar-x';
        } else {
            message = 'No attendance records found for this date';
            icon = 'bi-calendar-x';
        }
        
        noRecordsMessage.innerHTML = `
            <div class="flex flex-col items-center justify-center gap-4">
                <i class="bi ${icon} text-4xl text-gray-400"></i>
                <p class="text-lg font-medium text-gray-500">${message}</p>
                <p class="text-sm text-gray-400">Try selecting a different status, date, or search term</p>
                <button onclick="resetStatusFilter()" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md border border-[#cde4cd] bg-[#f8fbf8] px-4 py-2 text-sm font-medium ring-offset-[#f8fbf8] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 hover:bg-[#16a249] hover:text-[#ffffff] disabled:pointer-events-none disabled:opacity-50">
                    <i class="bi bi-arrow-counterclockwise h-4 w-4"></i>
                    Reset Filter
                </button>
            </div>
        `;
        // Keep pagination visible but disabled at the bottom when empty
        // Don't call updatePagination(null) here as it hides the pagination
        // The pagination should remain visible from the previous state
    } else {
        tbody.style.display = '';
        noRecordsMessage.classList.add('hidden');
    }
}

async function resetStatusFilter() {
    // Reset status dropdown visual and state
    const statusButton = document.querySelector('[role="combobox"]');
    if (statusButton) {
    const statusSpan = statusButton.querySelector('span');
        if (statusSpan) statusSpan.textContent = 'All Status';
        statusButton.classList.remove('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
    }
    currentStatus = 'All Status';

    // Reset date to today and label
    const today = new Date();
    selectedDateGlobal = today;
    const formattedDate = today.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    const selectedSpan = document.getElementById('selectedDate');
    if (selectedSpan) selectedSpan.textContent = formattedDate;
    // Refresh calendar highlight
    if (typeof updateCalendar === 'function') updateCalendar();

    // Fetch today's records via API (no page reload)
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const dateParam = `${yyyy}-${mm}-${dd}`;
    
    try {
        window.__tr_state = { scope: dateParam, page: 1, per_page: 10 };
        const res = await fetch(`../app/api/timerecords-api.php?date=${dateParam}&page=${window.__tr_state.page}&per_page=${window.__tr_state.per_page}`, { headers: { 'Accept': 'application/json' } });
        const raw = await res.text();
        const data = JSON.parse(raw);
        const tbody = document.getElementById('attendanceTableBody');
        const noRecordsMessage = document.getElementById('noRecordsMessage');
        
        if (data && data.status === 'success') {
            // Always use the HTML from the API response
            if (tbody) tbody.innerHTML = data.html;
            if (tbody) tbody.style.display = '';
            if (noRecordsMessage) noRecordsMessage.classList.add('hidden');
            
            // Ensure filter is cleared visually
    filterTableByStatus('All Status');
            
            // Always update pagination with the meta data from API
            updatePagination(data.meta || null);
        } else {
            // Show the inline empty-state row in the table body
            if (tbody) tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="px-4 py-6 text-center text-secondary fst-italic bg-light">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i class="bi bi-calendar-x text-4xl text-gray-400"></i>
                            <p class="text-lg font-medium text-gray-500">No attendance records found for this date</p>
                            <p class="text-sm text-gray-400">Select another date or check back later</p>
                        </div>
                    </td>
                </tr>
            `;
            if (tbody) tbody.style.display = '';
            if (noRecordsMessage) noRecordsMessage.classList.add('hidden');
            updatePagination(null);
        }
    } catch (e) {
        console.error('Reset filter fetch failed:', e);
        updatePagination(null);
    }
}

async function showAllAttendance() {
    const showAllBtn = document.getElementById('showAllBtn');
    const isCurrentlyShowingAll = showAllBtn.classList.contains('bg-[#f2f8f2]');
    
    if (isCurrentlyShowingAll) {
        // Currently showing all, so undo and return to current date
        await undoShowAll();
        return;
    }
    
    // Currently showing date-specific, so show all
    try {
        window.__tr_state = { scope: 'all', page: 1, per_page: 10 };
        const res = await fetch(`../app/api/timerecords-api.php?all=1&page=${window.__tr_state.page}&per_page=${window.__tr_state.per_page}`, { headers: { 'Accept': 'application/json' } });
        const raw = await res.text();
        const data = JSON.parse(raw);
        const tbody = document.getElementById('attendanceTableBody');
        const noRecordsMessage = document.getElementById('noRecordsMessage');
        if (data && data.status === 'success') {
            if (tbody) tbody.innerHTML = data.html;
            if (tbody) tbody.style.display = '';
            if (noRecordsMessage) noRecordsMessage.classList.add('hidden');
            // Keep current status filter applied
            filterTableByStatus(currentStatus);
            // Update date label to "All Dates"
            const selectedSpan = document.getElementById('selectedDate');
            if (selectedSpan) selectedSpan.textContent = 'All Dates';
            selectedDateGlobal = null;
            if (typeof updateCalendar === 'function') updateCalendar();
            updatePagination(data.meta || null);
            // Activate Show All button style
            if (showAllBtn) {
                showAllBtn.classList.add('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
            }
        } else {
            if (tbody) tbody.innerHTML = '';
            if (tbody) tbody.style.display = 'none';
            if (noRecordsMessage) noRecordsMessage.classList.remove('hidden');
            updatePagination(null);
        }
    } catch (e) {
        console.error('Show all fetch failed:', e);
        updatePagination(null);
    }
}

async function undoShowAll() {
    try {
        // Return to current date view
        const today = new Date();
        selectedDateGlobal = today;
        const formattedDate = today.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const selectedSpan = document.getElementById('selectedDate');
        if (selectedSpan) selectedSpan.textContent = formattedDate;
        
        // Refresh calendar highlight
        if (typeof updateCalendar === 'function') updateCalendar();
        
        // Fetch today's records
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const res = await fetch(`../app/api/timerecords-api.php?date=${yyyy}-${mm}-${dd}`, { headers: { 'Accept': 'application/json' } });
        const raw = await res.text();
        const data = JSON.parse(raw);
        const tbody = document.getElementById('attendanceTableBody');
        const noRecordsMessage = document.getElementById('noRecordsMessage');
        
        if (data && data.status === 'success') {
            const html = (data.html || '').trim();
            if (tbody) tbody.innerHTML = html.length > 0 ? html : `
                <tr>
                    <td colspan="10" class="px-4 py-6 text-center text-secondary fst-italic bg-light">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i class="bi bi-calendar-x text-4xl text-gray-400"></i>
                            <p class="text-lg font-medium text-gray-500">No attendance records found for this date</p>
                            <p class="text-sm text-gray-400">Select another date or check back later</p>
                        </div>
                    </td>
                </tr>
            `;
            if (tbody) tbody.style.display = '';
            if (noRecordsMessage) noRecordsMessage.classList.add('hidden');
            updatePagination(html.length > 0 ? (data.meta || null) : null);
        } else {
            // Show empty state
            if (tbody) tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="px-4 py-6 text-center text-secondary fst-italic bg-light">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i class="bi bi-calendar-x text-4xl text-gray-400"></i>
                            <p class="text-lg font-medium text-gray-500">No attendance records found for this date</p>
                            <p class="text-sm text-gray-400">Select another date or check back later</p>
                        </div>
                    </td>
                </tr>
            `;
            if (tbody) tbody.style.display = '';
            if (noRecordsMessage) noRecordsMessage.classList.add('hidden');
            updatePagination(null);
        }
        
        // Reset Show All button style
        const showAllBtn = document.getElementById('showAllBtn');
        if (showAllBtn) {
            showAllBtn.classList.remove('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
        }
        
        // Don't affect date button styling - let it keep its current state
        
        // Reset pagination state
        window.__tr_state = { scope: `${yyyy}-${mm}-${dd}`, page: 1, per_page: 10 };
        
    } catch (e) {
        console.error('Undo show all failed:', e);
    }
}

function updatePagination(meta) {
    const bar = document.getElementById('paginationBar');
    const pageNum = document.getElementById('pageNum');
    const pageTotal = document.getElementById('pageTotal');
    const pageCount = document.getElementById('pageCount');
    const prev = document.getElementById('prevPageBtn');
    const next = document.getElementById('nextPageBtn');
    if (bar) bar.classList.remove('hidden');
    if (!meta) {
        if (pageNum) pageNum.textContent = '1';
        if (pageTotal) pageTotal.textContent = '1';
        if (pageCount) pageCount.textContent = '0';
        if (prev) prev.disabled = true;
        if (next) next.disabled = true;
        return;
    }
    if (pageNum) pageNum.textContent = meta.page;
    if (pageTotal) pageTotal.textContent = meta.total_pages;
    if (pageCount) pageCount.textContent = meta.total;
    if (prev) prev.disabled = meta.page <= 1 || meta.total === 0;
    if (next) next.disabled = meta.page >= meta.total_pages || meta.total === 0;
}

async function changePage(delta) {
    if (!window.__tr_state) return;
    const newPage = Math.max(1, window.__tr_state.page + delta);
    if (newPage === window.__tr_state.page) return;
    window.__tr_state.page = newPage;
    try {
        const scope = window.__tr_state.scope; // 'all' or 'date'
        let url;
        if (scope === 'all') {
            url = `../app/api/timerecords-api.php?all=1&page=${window.__tr_state.page}&per_page=${window.__tr_state.per_page}`;
        } else {
            const d = scope; // YYYY-MM-DD
            url = `../app/api/timerecords-api.php?date=${d}&page=${window.__tr_state.page}&per_page=${window.__tr_state.per_page}`;
        }
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        const tbody = document.getElementById('attendanceTableBody');
        if (data && data.status === 'success') {
            if (tbody) tbody.innerHTML = data.html;
            filterTableByStatus(currentStatus);
            updatePagination(data.meta || null);
            // Sync Show All button style with current scope
            const showAllBtn = document.getElementById('showAllBtn');
            if (showAllBtn) {
                if (scope === 'all') {
                    showAllBtn.classList.add('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
                } else {
                    showAllBtn.classList.remove('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
                }
            }
        } else {
            if (tbody) tbody.innerHTML = '';
            if (tbody) tbody.style.display = 'none';
            updatePagination(null);
        }
    } catch (e) {
        console.error('Pagination fetch failed:', e);
        updatePagination(null);
    }
}

let currentDate = new Date();
let selectedDateGlobal = null; // currently selected date (for active highlight)

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
        dayCell.className = 'p-2 cursor-pointer rounded text-center transition-colors transition-transform duration-150 ease-out hover:bg-[#f2f8f2] hover:-translate-y-0.5 hover:shadow-sm hover:ring-1 hover:ring-[#16a249]/40';
        dayCell.textContent = day;
        
        // Highlight logic for today vs selected date
        const today = new Date();
        const isToday = (day === today.getDate() && month === today.getMonth() && year === today.getFullYear());
        const isSelected = (selectedDateGlobal &&
            day === selectedDateGlobal.getDate() &&
            month === selectedDateGlobal.getMonth() &&
            year === selectedDateGlobal.getFullYear());

        if (isSelected) {
            // Active selected date: stronger ring, soft green bg, darker green text
            dayCell.classList.add('ring-2', 'ring-[#16a249]', 'bg-[#eaf7ea]', 'text-[#0f6b2f]', 'shadow-sm');
        } else if (isToday) {
            // Today (not selected): subtle ring and soft background to match hover tone
            dayCell.classList.add('ring-1', 'ring-[#16a249]/60', 'bg-[#f2f8f2]', 'text-[#16a249]', 'font-semibold');
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
    // Set active selected date and refresh calendar grid highlight
    selectedDateGlobal = selectedDate;
    updateCalendar();
    
    // Close modal
    closeCalendarModal();
    
    // Fetch rows via AJAX without reloading
    const yyyy = selectedDate.getFullYear();
    const mm = String(selectedDate.getMonth() + 1).padStart(2, '0');
    const dd = String(selectedDate.getDate()).padStart(2, '0');
    const dateParam = `${yyyy}-${mm}-${dd}`;
    try {
        window.__tr_state = { scope: dateParam, page: 1, per_page: 10 };
        const res = await fetch(`../app/api/timerecords-api.php?date=${dateParam}&page=${window.__tr_state.page}&per_page=${window.__tr_state.per_page}`, {
            headers: { 'Accept': 'application/json' }
        });
        const raw = await res.text();
        let data;
        try {
            data = JSON.parse(raw);
        } catch (e) {
            console.error('Failed to parse JSON. Raw response:', raw);
            return;
        }
        if (data && data.status === 'success') {
            const tbody = document.getElementById('attendanceTableBody');
            tbody.innerHTML = data.html;
            // Re-apply current status filter
        filterTableByStatus(currentStatus);
            updatePagination(data.meta);
            // Deactivate Show All button style
            const showAllBtn = document.getElementById('showAllBtn');
            if (showAllBtn) {
                showAllBtn.classList.remove('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
            }
        } else if (data && data.status === 'error') {
            console.error('Server error:', data.message || 'Unknown error');
        }
    } catch (err) {
        console.error('Failed to fetch time records:', err);
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


// Export Modal Functions
function openExportModal() {
    const modal = document.getElementById('exportModal');
    const overlay = document.getElementById('exportModalOverlay');
    
    if (modal && overlay) {
        modal.classList.remove('hidden');
        overlay.classList.remove('hidden');
        
        setTimeout(() => {
            modal.classList.remove('opacity-0', 'scale-75');
            modal.classList.add('opacity-100', 'scale-100');
            overlay.classList.add('opacity-100');
        }, 10);
        
        // Set current month as default
        const currentMonth = new Date().getMonth() + 1;
        const monthSelect = document.getElementById('exportMonth');
        if (monthSelect) {
            monthSelect.value = String(currentMonth).padStart(2, '0');
        }
    }
}

function closeExportModal() {
    const modal = document.getElementById('exportModal');
    const overlay = document.getElementById('exportModalOverlay');
    
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

function exportAttendance() {
    const month = document.getElementById('exportMonth').value;
    const year = document.getElementById('exportYear').value;
    const exportFormat = document.querySelector('input[name="exportFormat"]:checked').value;
    const exportBtn = document.getElementById('exportBtn');
    
    if (!month || !year) {
        Swal.fire({
            icon: 'error',
            title: 'Selection Required',
            text: 'Please select both month and year for export.',
            confirmButtonColor: '#16a249'
        });
        return;
    }
    
    // Disable button and show loading state
    exportBtn.disabled = true;
    exportBtn.innerHTML = '<i class="bi bi-hourglass-split h-4 w-4 animate-spin"></i> Exporting...';
    
    // Choose API endpoint based on format
    const apiEndpoint = exportFormat === 'html' 
        ? `../app/api/export-attendance-html.php?month=${month}&year=${year}`
        : `../app/api/export-attendance-api.php?month=${month}&year=${year}`;
    
    // Use fetch to get the file data
    fetch(apiEndpoint, {
        method: 'GET',
        credentials: 'same-origin' // Include cookies/session
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Export failed');
        }
        return response.blob();
    })
    .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        const fileExtension = exportFormat === 'html' ? 'html' : 'csv';
        link.download = `attendance_report_${year}_${month}.${fileExtension}`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        
        // Show success message
        const formatText = exportFormat === 'html' ? 'HTML file with styling' : 'CSV file';
        Swal.fire({
            icon: 'success',
            title: 'Export Successful!',
            text: `Attendance report for ${getMonthName(month)} ${year} has been downloaded as ${formatText}.`,
            confirmButtonColor: '#16a249',
            timer: 3000,
            timerProgressBar: true
        });
        
        // Reset button state
        exportBtn.disabled = false;
        exportBtn.innerHTML = '<i class="bi bi-download h-4 w-4"></i> Export';
        
        // Close modal
        closeExportModal();
    })
    .catch(error => {
        console.error('Export error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Export Failed',
            text: 'Failed to export attendance data. Please try again.',
            confirmButtonColor: '#16a249'
        });
        
        // Reset button state
        exportBtn.disabled = false;
        exportBtn.innerHTML = '<i class="bi bi-download h-4 w-4"></i> Export';
    });
}

function getMonthName(month) {
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return months[parseInt(month) - 1];
}



// Initialize with current date
document.addEventListener('DOMContentLoaded', () => {
    // Highlight date button and show server-selected date (or today)
    const dateButton = document.querySelector('button[onclick="openCalendarModal()"]');
    if (dateButton) {
    dateButton.classList.add('bg-[#f2f8f2]', 'border-[#16a249]', 'text-[#16a249]', 'ring-2', 'ring-[#16a249]', 'ring-offset-2');
    }
    const initialDate = '<?= htmlspecialchars($_GET['date'] ?? date('Y-m-d')) ?>';
    const dateObj = new Date(initialDate + 'T00:00:00');
    const formattedDate = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    const selectedSpan = document.getElementById('selectedDate');
    if (selectedSpan) selectedSpan.textContent = formattedDate;
    // Initialize active date highlight to the server-provided date
    selectedDateGlobal = dateObj;
    
    // Initialize calendar
    updateCalendar();

    // Initialize clear button state
    toggleClearButton();
    
    // Initialize pagination for current date (even if no records)
    const hasRecords = <?= !empty($attendanceRecords) ? 'true' : 'false' ?>;
    
    if (!hasRecords) {
        // If no records for current date, show pagination with 0 records
        updatePagination({
            total: 0,
            page: 1,
            per_page: 10,
            total_pages: 1
        });
        // Set the state for pagination
        window.__tr_state = { 
            scope: initialDate, 
            page: 1, 
            per_page: 10 
        };
    } else {
        // If there are records, set the state for pagination
        window.__tr_state = { 
            scope: initialDate, 
            page: 1, 
            per_page: 10 
        };
        // Show pagination with actual data
        updatePagination({
            total: <?= count($attendanceRecords) ?>,
            page: 1,
            per_page: 10,
            total_pages: 1
        });
    }
});

// Search functionality with debounce
let searchTimeout = null;

function handleSearchInput() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearButton');
    
    // Toggle clear button visibility
    if (searchInput.value.trim().length > 0) {
        clearButton.classList.remove('hidden');
    } else {
        clearButton.classList.add('hidden');
    }
    
    // Clear previous timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Set new timeout for debounce (300ms delay)
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 300);
}

function performSearch() {
    // Re-apply the current status filter which now includes search functionality
    filterTableByStatus(currentStatus);
}

function clearSearch() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearButton');
    
    searchInput.value = '';
    clearButton.classList.add('hidden');
    
    // Re-apply current status filter (which will now show all rows since search is cleared)
    filterTableByStatus(currentStatus);
}

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
    clearSearch();
}
</script>

<?php require_once views_path("partials/footer"); ?>

