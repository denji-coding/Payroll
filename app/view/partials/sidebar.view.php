<?php
$currentPage = $_GET['payroll'] ?? basename($_SERVER['PHP_SELF']);
require_once views_path("partials/header");

$employeePages = ['employees', 'delete_history', 'approvals_request'];
$isEmployeeDropdownOpen = in_array($currentPage, $employeePages);
?>

<style>
.-rotate-90 {
    transform: rotate(-90deg);
}
.rotate-180 {
    transform: rotate(180deg);
}
.transition-none {
    transition: none !important;
}

/* Smooth transition container */
.dropdown-container {
    overflow: hidden;
    transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
    max-height: 0;
    opacity: 0;
    pointer-events: none;
}

.dropdown-container.open {
    max-height: 300px; /* enough height for 3 items */
    opacity: 1;
    pointer-events: auto;
}

  #adminSidebar::-webkit-scrollbar {
    width: 4px; /* Adjust the scrollbar width */
  }

  #adminSidebar::-webkit-scrollbar-track {
    background: #0b5125; /* Track background (optional) */
  }

  #adminSidebar::-webkit-scrollbar-thumb {
    background-color: #1a7f3c; /* Scrollbar color */
    border-radius: 4px;        /* Rounded corners */
    border: 2px solid #0b5125; /* Space around thumb */
  }

  /* Optional: Firefox scrollbar styling */
  #adminSidebar {
    scrollbar-width: thin;              /* "auto" or "thin" */
    scrollbar-color: #1a7f3c #0b5125;   /* thumb and track */
  }
</style>

<div id="adminSidebar" class="w-40"
    style="background-color: #0b5125; color: white; max-height: 100vh; overflow-y: auto; padding: 1.5rem;">

    <img src="../public/assets/image/logo.png" alt="Company Logo" class="mx-auto w-24 h-24 mb-3 rounded-full border border-[#fff8] bg-white shadow">
    <span class="block text-center font-extrabold text-white text-xl md:text-lg mb-2">Migrants Venture Corporation</span>
    <div class="border-b border-white-500 mb-4"></div>

    <div class="flex flex-col">
        <a href="index.php?payroll=dashboard1"
            class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'dashboard1') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-house-door"></i> Dashboard
        </a>

        <!-- Employees Dropdown -->
        <div class="w-full mb-2.5">
            <button id="btn-employeeDropdown" onclick="toggleEmployeeDropdown()" 
                class="dropdown-button w-full flex justify-between items-center font-semibold text-white text-sm p-2 px-4 rounded <?= $isEmployeeDropdownOpen ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                <span class="text-sm"><i class="bi bi-people"></i> Employees</span>
                <svg id="arrow-employeeDropdown" class="w-4 h-4 transform transition-transform duration-300 <?= $isEmployeeDropdownOpen ? 'rotate-180' : '-rotate-90' ?>"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            

            <div id="employeeDropdown" class="dropdown-container <?= $isEmployeeDropdownOpen ? 'open' : '' ?> ml-5 mt-2 -mb-3">
                <a href="index.php?payroll=employees" prefetch={false} 
                class="block font-semibold py-2 px-3 text-xs text-white rounded <?= $currentPage == 'employees' ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                    <i class="bi bi-person-gear"></i> Manage Employees
                </a>
                <a href="index.php?payroll=approvals_request" 
                class="block py-2 px-3 font-semibold text-xs text-white rounded <?= $currentPage == 'approvals_request' ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">                    
                    <i class="bi bi-check-circle"></i> Approvals by manager
                </a>
                <a href="index.php?payroll=delete_history" 
                class="block py-2 px-3 font-semibold text-xs text-white rounded <?= $currentPage == 'delete_history' ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                    <i class="bi bi-trash"></i> Delete History
                </a>
            </div>
        </div>

        <a href="index.php?payroll=schedules"
            class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'schedules') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-clock"></i> Schedules
        </a>
        <a href="index.php?payroll=leave_history"
            class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'leave_history') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-calendar-check"></i> Leave History
        </a>
        <a href="index.php?payroll=timerecords"
            class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'timerecords') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-calendar2-week"></i> Time Records
        </a>
        <a href="index.php?payroll=payslips"
            class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'payslips') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-file-earmark-text"></i> Payslips
        </a>
        <a href="index.php?payroll=reports"
            class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'reports') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="fas fa-chart-column"></i> Reports
        </a>
    </div>
</div>

<script>
function toggleEmployeeDropdown() {
    const dropdown = document.getElementById('employeeDropdown');
    const arrow = document.getElementById('arrow-employeeDropdown');
    const button = document.getElementById('btn-employeeDropdown');

    dropdown.classList.toggle('open');

    // Rotate arrow
    if (dropdown.classList.contains('open')) {
        arrow.classList.remove('-rotate-90');
        arrow.classList.add('rotate-180');
        button.classList.add('bg-[#206037]', 'border-l-4', 'border-white');
    } else {
        arrow.classList.remove('rotate-180');
        arrow.classList.add('-rotate-90');
        button.classList.remove('bg-[#206037]', 'border-l-4', 'border-white');
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const dropdown = document.getElementById('employeeDropdown');
    const arrow = document.getElementById('arrow-employeeDropdown');
    const button = document.getElementById('btn-employeeDropdown');

    if (dropdown.classList.contains('open')) {
        arrow.classList.add('rotate-180');
        button.classList.add('bg-[#206037]', 'border-l-4', 'border-white');
    }

    // Optional: highlight other items
    document.querySelectorAll('.sidebar-item').forEach(item => {
        if (!item.classList.contains('dropdown-button')) {
            item.addEventListener('click', () => {
                dropdown.classList.remove('open');
                arrow.classList.remove('rotate-180');
                arrow.classList.add('-rotate-90');
                button.classList.remove('bg-[#206037]', 'border-l-4', 'border-white');
            });
        }
    });
});
</script>
