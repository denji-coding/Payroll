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

.dropdown-header-btn {
    transition: background 0.2s, color 0.2s;
}
.dropdown-header-btn:hover, .dropdown-header-btn.active {
    background: #206037;
    color: #fff;
    border-left: 4px solid #fff;
}

.dropdown-arrow {
    transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
    transform: rotate(0deg);
}
.dropdown-arrow.open {
    transform: rotate(90deg);
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

    <div class="flex flex-col space-y-4">

    <!-- Section: Dashboard -->
    <div>
        <span class="text-xs uppercase tracking-wide text-gray-300 px-4 mb-1 block">Main</span>
        <a href="index.php?payroll=dashboard1"
            class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'dashboard1') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-house-door"></i> Dashboard
        </a>
    </div>

    <!-- Section: Employee Management -->
    <div>
        <button id="btn-employeeSectionDropdown" onclick="toggleSectionDropdown('employeeSectionDropdown', 'arrow-employeeSectionDropdown', 'btn-employeeSectionDropdown')" 
            class="dropdown-header-btn w-full flex justify-between items-center font-semibold text-white text-xs uppercase tracking-wide px-4 mb-1 block bg-transparent border-0 outline-none cursor-pointer">
           <span class="whitespace-nowrap">Employee Management</span>  
            <svg id="arrow-employeeSectionDropdown" class="dropdown-arrow w-4 h-4 flex-shrink-0 transition-all duration-300 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>
        <div id="employeeSectionDropdown" class="dropdown-container ml-0 mt-0 mb-2.5">
            <!-- Employees Dropdown (existing) -->
            <div class="w-full mb-2.5">
                <button id="btn-employeeDropdown" onclick="toggleEmployeeDropdown()" 
                    class="dropdown-button w-full flex justify-between items-center font-semibold text-white text-sm p-2 px-4 rounded <?= $isEmployeeDropdownOpen ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                    <span class="text-sm"><i class="bi bi-people"></i> Employees</span>
                    <svg id="arrow-employeeDropdown" class="dropdown-arrow ml-2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;">
                        <path d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <div id="employeeDropdown" class="dropdown-container <?= $isEmployeeDropdownOpen ? 'open' : '' ?> ml-5 mt-2 -mb-3">
                    <a href="index.php?payroll=employees" prefetch={false} 
                    class="block font-semibold py-2 px-3 text-xs text-white rounded <?= $currentPage == 'employees' ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                        <i class="bi bi-person-gear"></i> Manage Employees
                    </a>
                    <a href="index.php?payroll=approvals_request" 
                    class="block py-2 px-3 font-semibold text-xs text-white rounded <?= $currentPage == 'approvals_request' ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">                    
                        <i class="bi bi-check-circle"></i> Approvals by Manager
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
            <a href="index.php?payroll=managers_account"
                class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'managers_account') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                <i class="bi bi-person-badge"></i> Managers
            </a>
        </div>
    </div>

    <!-- Section: Payroll Management -->
    <div>
        <button id="btn-payrollSectionDropdown" onclick="toggleSectionDropdown('payrollSectionDropdown', 'arrow-payrollSectionDropdown', 'btn-payrollSectionDropdown')" 
            class="dropdown-header-btn w-full flex justify-between items-center font-semibold text-white text-xs uppercase tracking-wide px-4 mb-1 block bg-transparent border-0 outline-none cursor-pointer">            
            <span class="whitespace-nowrap">Payroll Management</span>  
            <svg id="arrow-payrollSectionDropdown" class="dropdown-arrow w-4 h-4 flex-shrink-0  transition-all duration-300 ml-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>
        <div id="payrollSectionDropdown" class="dropdown-container ml-0 mt-0 mb-2.5">
            <a href="index.php?payroll=timerecords"
                class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'timerecords') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                <i class="bi bi-calendar2-week"></i> Time Records
            </a>
            <a href="index.php?payroll=benefit_rates"
                class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'benefit_rates') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                <i class="bi bi-pencil-square"></i> Benefit Rates
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
</div>

</div>

<script>
function toggleEmployeeDropdown() {
    const dropdown = document.getElementById('employeeDropdown');
    const arrow = document.getElementById('arrow-employeeDropdown');
    const button = document.getElementById('btn-employeeDropdown');
    dropdown.classList.toggle('open');
    arrow.classList.toggle('open', dropdown.classList.contains('open'));
    if (dropdown.classList.contains('open')) {
        arrow.classList.add('open');
        button.classList.add('bg-[#206037]', 'border-l-4', 'border-white');
    } else {
        arrow.classList.remove('open');
        button.classList.remove('bg-[#206037]', 'border-l-4', 'border-white');
    }
}

function toggleSectionDropdown(sectionId, arrowId, buttonId) {
    const section = document.getElementById(sectionId);
    const arrow = document.getElementById(arrowId);
    const button = document.getElementById(buttonId);
    section.classList.toggle('open');
    arrow.classList.toggle('open', section.classList.contains('open'));
    if (section.classList.contains('open')) {
        button.classList.add('bg-[#206037]', 'border-l-4', 'border-white');
    } else {
        button.classList.remove('bg-[#206037]', 'border-l-4', 'border-white');
    }
    // Persist state
    localStorage.setItem(sectionId + '_open', section.classList.contains('open'));
}

document.addEventListener("DOMContentLoaded", () => {
    // Define which pages belong to which dropdown
    const employeeDropdownPages = ['employees', 'approvals_request', 'delete_history', 'schedules', 'leave_history', 'managers_account'];
    const payrollDropdownPages = ['timerecords', 'benefit_rates', 'payslips', 'reports'];
    // Get current page
    const currentPage = (typeof window !== 'undefined' && (new URLSearchParams(window.location.search)).get('payroll')) || (typeof window !== 'undefined' && window.location.pathname.split('/').pop());

    [
        {sectionId: 'employeeSectionDropdown', arrowId: 'arrow-employeeSectionDropdown', buttonId: 'btn-employeeSectionDropdown', pages: employeeDropdownPages},
        {sectionId: 'payrollSectionDropdown', arrowId: 'arrow-payrollSectionDropdown', buttonId: 'btn-payrollSectionDropdown', pages: payrollDropdownPages}
    ].forEach(({sectionId, arrowId, buttonId, pages}) => {
        const section = document.getElementById(sectionId);
        const arrow = document.getElementById(arrowId);
        const button = document.getElementById(buttonId);
        let forceOpen = pages.includes(currentPage);
        if (forceOpen) {
            section.classList.add('open');
            arrow.classList.add('open');
            button.classList.add('bg-[#206037]', 'border-l-4', 'border-white');
            localStorage.setItem(sectionId + '_open', 'true');
        } else {
            const isOpen = localStorage.getItem(sectionId + '_open') === 'true';
            if (isOpen) {
                section.classList.add('open');
                arrow.classList.add('open');
                button.classList.add('bg-[#206037]', 'border-l-4', 'border-white');
            } else {
                section.classList.remove('open');
                arrow.classList.remove('open');
                button.classList.remove('bg-[#206037]', 'border-l-4', 'border-white');
            }
        }
    });

    // Sync Employees dropdown arrow with its open state on page load
    const empDropdown = document.getElementById('employeeDropdown');
    const empArrow = document.getElementById('arrow-employeeDropdown');
    if (empDropdown && empArrow) {
        if (empDropdown.classList.contains('open')) {
            empArrow.classList.add('open');
        } else {
            empArrow.classList.remove('open');
        }
    }
});
</script>
