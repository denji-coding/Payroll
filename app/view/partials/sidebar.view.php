<?php
$currentPage = $_GET['payroll'] ?? basename($_SERVER['PHP_SELF']);
require_once views_path("partials/header");

$employeePages = ['employees', 'delete_history', 'approvals_request', 'leave_credits'];
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
    transition: max-height 0.35s ease, opacity 0.25s ease, margin 0.25s ease;
    max-height: 0;
    opacity: 0;
    pointer-events: none;
    margin: 0;
    will-change: max-height, opacity, margin;
}

.dropdown-container.open {
    max-height: 600px; /* allow smooth expand; should exceed content height */
    opacity: 1;
    pointer-events: auto;
    margin: 0.25rem 0; /* tighter spacing when open */
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
    transition: transform 0.32s ease;
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

  /* Employee dropdown specific styling */
  #employeeDropdown {
    max-height: 350px;
    overflow-y: auto;
  }

  #employeeDropdown::-webkit-scrollbar {
    width: 3px;
  }

  #employeeDropdown::-webkit-scrollbar-track {
    background: #0b5125;
  }

  #employeeDropdown::-webkit-scrollbar-thumb {
    background-color: #1a7f3c;
    border-radius: 3px;
  }

  /* Ensure proper spacing between sections */
  .dropdown-container:not(.open) {
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Fix spacing for section buttons */
  .dropdown-header-btn {
    margin-bottom: 0.5rem;
  }

  /* Employees wrapper: no extra bottom gap */
  #employeeWrapper { 
    margin-bottom: 0 !important; 
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
        <span class="text-xs uppercase tracking-wide font-semibold px-4 mb-1 block">Main</span>
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
            <div id="employeeWrapper" class="w-full">
                <button id="btn-employeeDropdown" onclick="toggleEmployeeDropdown()" 
                    class="dropdown-button w-full flex justify-between items-center font-semibold text-white text-sm p-2 px-4 rounded <?= $isEmployeeDropdownOpen ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                    <span class="text-sm"><i class="bi bi-people"></i> Employees</span>
                    <svg id="arrow-employeeDropdown" class="dropdown-arrow ml-2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;">
                        <path d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <div id="employeeDropdown" class="dropdown-container <?= $isEmployeeDropdownOpen ? 'open' : '' ?> ml-5 mt-2">
                    <a href="index.php?payroll=employees" prefetch={false} 
                    class="block font-semibold ml-4 py-2 px-3 text-xs text-white rounded <?= $currentPage == 'employees' ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                        <i class="bi bi-person-gear"></i> Manage Employees
                    </a>
                    <a href="index.php?payroll=approvals_request" 
                    class="block ml-4 py-2 px-3 font-semibold text-xs text-white rounded <?= $currentPage == 'approvals_request' ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">                    
                        <i class="bi bi-check-circle"></i> Approvals by Manager
                    </a>
                    <a href="index.php?payroll=delete_history" 
                    class="block ml-4 py-2 px-3 font-semibold text-xs text-white rounded <?= $currentPage == 'delete_history' ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                        <i class="bi bi-trash"></i> Delete History
                    </a>
                </div>
                <a href="index.php?payroll=schedules"
            class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'schedules') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-clock"></i> Schedules
        </a>
        <a href="index.php?payroll=leave_credits"
            class="sidebar-item w-full flex items-center font-semibold text-white text-sm gap-1 p-2 px-4 rounded <?= ($currentPage == 'leave_credits') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-calendar-plus"></i> Leave Credits
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
        
        <!-- Individual buttons outside dropdown for better spacing control -->
        
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
        <div id="payrollSectionDropdown" class="dropdown-container ml-0 mt-0">
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
// Slide helpers: animate height/opacity and hide on close
function slideDown(element, duration = 320, easing = 'ease') {
    if (!element) return Promise.resolve();
    return new Promise(resolve => {
        element.style.removeProperty('display');
        const computed = window.getComputedStyle(element);
        if (computed.display === 'none') element.style.display = 'block';
        const targetHeight = element.scrollHeight;
        element.style.overflow = 'hidden';
        element.style.maxHeight = '0px';
        element.style.opacity = '0';
        element.offsetHeight; // reflow
        element.style.transition = `max-height ${duration}ms ${easing}, opacity ${duration}ms ${easing}`;
        element.style.maxHeight = targetHeight + 'px';
        element.style.opacity = '1';
        const onEnd = (e) => {
            if (e.propertyName === 'max-height') {
                element.style.transition = '';
                element.style.maxHeight = '';
                element.style.overflow = '';
                element.removeEventListener('transitionend', onEnd);
                resolve();
            }
        };
        element.addEventListener('transitionend', onEnd);
    });
}

function slideUp(element, duration = 320, easing = 'ease') {
    if (!element) return Promise.resolve();
    return new Promise(resolve => {
        const currentHeight = element.scrollHeight;
        element.style.overflow = 'hidden';
        element.style.maxHeight = currentHeight + 'px';
        element.style.opacity = '1';
        element.offsetHeight; // reflow
        element.style.transition = `max-height ${duration}ms ${easing}, opacity ${duration}ms ${easing}`;
        element.style.maxHeight = '0px';
        element.style.opacity = '0';
        const onEnd = (e) => {
            if (e.propertyName === 'max-height') {
                element.style.transition = '';
                element.style.maxHeight = '';
                element.style.overflow = '';
                element.style.display = 'none';
                element.removeEventListener('transitionend', onEnd);
                resolve();
            }
        };
        element.addEventListener('transitionend', onEnd);
    });
}

function toggleEmployeeDropdown() {
    const dropdown = document.getElementById('employeeDropdown');
    const arrow = document.getElementById('arrow-employeeDropdown');
    const button = document.getElementById('btn-employeeDropdown');
    const wrapper = document.getElementById('employeeWrapper');
    const willOpen = !dropdown.classList.contains('open');
    if (willOpen) {
        dropdown.classList.add('open');
        arrow.classList.add('open');
        if (wrapper) wrapper.classList.add('open');
        button.classList.add('bg-[#206037]', 'border-l-4', 'border-white');
        slideDown(dropdown, 320, 'ease').then(() => {
            setTimeout(() => { scrollDropdownIntoView('employeeDropdown'); }, 50);
        });
    } else {
        arrow.classList.remove('open');
        if (wrapper) wrapper.classList.remove('open');
        button.classList.remove('bg-[#206037]', 'border-l-4', 'border-white');
        slideUp(dropdown, 320, 'ease').then(() => dropdown.classList.remove('open'));
    }
}

function scrollDropdownIntoView(dropdownId) {
    const sidebar = document.getElementById('adminSidebar');
    const dropdown = document.getElementById(dropdownId);
    if (sidebar && dropdown && dropdown.classList.contains('open')) {
        const sidebarRect = sidebar.getBoundingClientRect();
        const dropdownRect = dropdown.getBoundingClientRect();
        if (dropdownRect.bottom > sidebarRect.bottom || dropdownRect.top < sidebarRect.top) {
            dropdown.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        // For employee dropdown, ensure all items are visible
        if (dropdownId === 'employeeDropdown') {
            setTimeout(() => {
                const lastItem = dropdown.querySelector('a:last-child');
                if (lastItem) {
                    lastItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }, 100);
        }
    }
}

function toggleSectionDropdown(sectionId, arrowId, buttonId) {
    const section = document.getElementById(sectionId);
    const arrow = document.getElementById(arrowId);
    const button = document.getElementById(buttonId);
    const willOpen = !section.classList.contains('open');
    if (willOpen) {
        section.classList.add('open');
        arrow.classList.add('open');
        button.classList.add('bg-[#206037]', 'border-l-4', 'border-white');
        slideDown(section, 320, 'ease').then(() => scrollDropdownIntoView(sectionId));
    } else {
        arrow.classList.remove('open');
        button.classList.remove('bg-[#206037]', 'border-l-4', 'border-white');
        slideUp(section, 320, 'ease').then(() => section.classList.remove('open'));
    }
    // Persist state
    localStorage.setItem(sectionId + '_open', willOpen);
}

document.addEventListener("DOMContentLoaded", () => {
    // Define which pages belong to which dropdown
    const employeeDropdownPages = ['employees', 'approvals_request', 'delete_history', 'schedules', 'leave_history', 'managers_account', 'leave_credits'];
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
            section.style.display = 'block';
        } else {
            const isOpen = localStorage.getItem(sectionId + '_open') === 'true';
            if (isOpen) {
                section.classList.add('open');
                arrow.classList.add('open');
                button.classList.add('bg-[#206037]', 'border-l-4', 'border-white');
                section.style.display = 'block';
            } else {
                section.classList.remove('open');
                arrow.classList.remove('open');
                button.classList.remove('bg-[#206037]', 'border-l-4', 'border-white');
                section.style.display = 'none';
            }
        }
    });

    // Sync Employees dropdown arrow with its open state on page load
    const empDropdown = document.getElementById('employeeDropdown');
    const empArrow = document.getElementById('arrow-employeeDropdown');
    const empWrapper = document.getElementById('employeeWrapper');
    if (empDropdown && empArrow) {
        if (empDropdown.classList.contains('open')) {
            empArrow.classList.add('open');
            if (empWrapper) empWrapper.classList.add('open');
            empDropdown.style.display = 'block';
        } else {
            empArrow.classList.remove('open');
            if (empWrapper) empWrapper.classList.remove('open');
            empDropdown.style.display = 'none';
        }
    }
    
    // Mobile close button functionality
    const mobileCloseBtn = document.getElementById('mobileCloseBtn');
    if (mobileCloseBtn) {
        mobileCloseBtn.addEventListener('click', function() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('mobileBackdrop');
            if (sidebar) {
                sidebar.classList.remove('show');
                if (backdrop) {
                    backdrop.classList.remove('show');
                }
            }
        });
    }
});
</script>
