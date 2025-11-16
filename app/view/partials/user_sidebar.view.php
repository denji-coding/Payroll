<?php
$currentPage = $_GET['payroll'] ?? basename($_SERVER['PHP_SELF']);
require_once views_path("partials/header");

// Get user information - support employees, managers, and HR
$username = 'Unknown User';
$email = 'no-email@example.com';
$gender = '';
$photoPath = '';

// Check if user is HR/Admin
if (isset($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['SESSION_USER_ID'])) {
    $username = $_SESSION['USERNAME'] ?? $_SESSION['user_name'] ?? 'HR User';
    $email = $_SESSION['SESSION_EMAIL'] ?? $_SESSION['user_email'] ?? 'no-email@example.com';
    $gender = strtolower($_SESSION['gender'] ?? $_SESSION['hr_sex'] ?? '');
    $photoPath = $_SESSION['photo_path'] ?? '';
    
    // Format photo path for HR
    if (!empty($photoPath)) {
        if (strpos($photoPath, 'public/') === 0 || strpos($photoPath, '../public/') === 0) {
            $imagePath = $photoPath;
        } else {
            $imagePath = '../public/' . ltrim($photoPath, '/');
        }
    } else {
        $defaultImage = in_array($gender, ['male', 'm', 'male'])
            ? '../public/assets/image/default_men.png'
            : '../public/assets/image/default_women.png';
        $imagePath = $defaultImage;
    }
}
// Check if user is Manager
elseif (isset($_SESSION['manager_id']) && !empty($_SESSION['manager_id'])) {
    $username = $_SESSION['manager_name'] ?? 'Manager';
    $email = $_SESSION['manager_email'] ?? 'no-email@example.com';
    $gender = strtolower($_SESSION['gender'] ?? $_SESSION['m_sex'] ?? '');
    $photoPath = $_SESSION['photo_path'] ?? '';
    
    // Format photo path for Manager
    if (!empty($photoPath)) {
        if (strpos($photoPath, 'public/') === 0 || strpos($photoPath, '../public/') === 0) {
            $imagePath = $photoPath;
        } else {
            $imagePath = '../public/' . ltrim($photoPath, '/');
        }
    } else {
        $defaultImage = in_array($gender, ['male', 'm', 'male'])
            ? '../public/assets/image/default_men.png'
            : '../public/assets/image/default_women.png';
        $imagePath = $defaultImage;
    }
}
// Regular Employee
else {
    $username = $_SESSION['name'] ?? $_SESSION['username'] ?? $_SESSION['first_name'] ?? 'Unknown Employee';
    $email = $_SESSION['email'] ?? 'no-email@example.com';
    $gender = strtolower($_SESSION['gender'] ?? $_SESSION['sex'] ?? '');
    $photoPath = $_SESSION['photo_path'] ?? '';
    
    $defaultImage = in_array($gender, ['male', 'm', 'male'])
        ? '../public/assets/image/default_men.png'
        : '../public/assets/image/default_women.png';
    $imagePath = (!empty($photoPath))
        ? '../public/upload/' . basename($photoPath)
        : $defaultImage;
}
?>

<style>
a {
    text-decoration: none;
}

/* Mobile Navbar Height */
:root {
    --mobile-navbar-height: 3.5rem;
}

#sidebar {
    position: fixed;
    top: var(--mobile-navbar-height);
    left: 0;
    height: calc(100vh - var(--mobile-navbar-height));
    width: 16rem;
    background-color: #0b5125;
    color: white;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow-y: auto;
    z-index: 40;
    transition: width 0.3s ease, padding 0.3s ease, transform 0.3s ease;
    flex-shrink: 0;
}

@media (min-width: 768px) {
    #sidebar {
        top: 0;
        height: 100vh;
        transform: none !important;
    }
}

@media (max-width: 767px) {
    #sidebar {
        transform: translateX(-100%) !important;
        transition: transform 0.3s ease;
    }

    #sidebar.open {
        transform: translateX(0) !important;
    }

    #sidebarToggle,
    #portalLabel {
        display: none !important;
    }
    
    main#mainContent {
        margin-left: 0 !important;
        padding-top: var(--mobile-navbar-height);
    }
}

#sidebar.collapsed {
    width: 64px !important;
    padding: 1.5rem 0.5rem !important;
    justify-content: flex-start !important;
}

#sidebar.collapsed a span,
#sidebar.collapsed .text-center span.text-lg,
#sidebar.collapsed > div:last-child .truncate,
#sidebar.collapsed .label-full,
#sidebar.collapsed .underline {
    display: none !important;
}

#sidebar.collapsed .label-short {
    display: inline-block !important;
}

#sidebar a i,
#sidebar a span {
    transition: all 0.3s ease;
}

#sidebar.collapsed a {
    justify-content: center !important;
    align-items: center !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    gap: 0 !important;
}

#sidebar > div:last-child {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
}

#sidebar.collapsed > div:last-child {
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0.75rem 0.5rem !important;
    gap: 0.5rem;
}

#sidebar.collapsed > div:last-child .truncate {
    display: none !important;
}

#sidebar .label-short {
    display: none;
}

#sidebar:not(.collapsed) #portalLabel .label-full {
    display: inline !important;
}

#sidebar.collapsed #portalLabel .label-short {
    display: inline-block !important;
}

#sidebar.collapsed #portalLabel .label-full {
    display: none !important;
}

#sidebar #portalLabel .underline {
    position: absolute;
    bottom: 0;
    left: 0;
    transform: translateX(-50%);
    width: 100%;
    height: 4px;
    background-color: #22c55e;
    border-radius: 9999px;
}

main#mainContent {
    flex: 1;
    transition: margin-left 0.3s ease;
    padding-top: var(--mobile-navbar-height);
}

@media (min-width: 768px) {
    main#mainContent {
        padding-top: 0;
    }

    header.md\\:hidden {
        display: none !important;
    }
}
</style>

<!-- Mobile Navbar -->
<header class="md:hidden fixed top-0 left-0 w-full border-b bg-[#0b5125] text-white flex justify-between items-center px-4 py-3 z-50 shadow">
    <button id="mobileMenuBtn" class="text-white text-2xl z-10">
        <i id="menuIcon" class="bi bi-list"></i>
    </button>
    
    <div class="absolute inset-0 flex justify-center items-center pointer-events-none">
        
        <div class="font-bold text-lg">Employee Portal</div>
    </div>
</header>

<!-- Sidebar -->
<aside id="sidebar" class="bg-[#0b5125] text-white">
    <div>
        <div class="text-center mb-6">
            <div class="hidden md:flex justify-center">
                <!-- <button id="sidebarToggle" title="Toggle sidebar" class="mb-4 bg-green-600 hover:bg-green-700 text-white flex items-center justify-center rounded w-10 h-10">
                    <i class="bi bi-layout-sidebar-inset"></i>
                </button> -->
            </div>
            <div id="portalLabel" class="hidden md:inline-block text-lg font-extrabold uppercase relative pb-2">
                <span class="label-full">Employee Portal</span>
                <span class="label-short">EP</span>
                <span class="underline absolute ml-[86px] bottom-0 left-1/2 -translate-x-1/2 w-full h-1 bg-green-600 rounded-full"></span>
            </div>
        </div>
        <nav class="flex flex-col space-y-2">
            <a href="?payroll=user_dashboard" title="Dashboard"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'user_dashboard') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-house-door"></i> <span>Dashboard</span>
            </a>
            <a href="index.php?payroll=user_profile" title="My Profile"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'user_profile') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-person"></i> <span>My Profile</span>
            </a>

            <a href="?payroll=user_dtr" title="Daily Time Record"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'user_dtr') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-clock-history"></i> <span>Daily Time Record</span>
            </a>

            <a href="?payroll=user_mypayslip" title="Payslips"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'user_mypayslip') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-file-earmark-text"></i> <span>My Payslips</span>
            </a>

            <a href="index.php?payroll=user_leave" title="Leave Application"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'user_leave') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-calendar-x"></i> <span>Leave Application</span>
            </a>
        </nav>
    </div>
    <!-- Bottom: Logout -->
    <div class="flex items-center justify-between text-white  py-2 border-t">
        <div class="flex items-center gap-3 min-w-0 sidebar-user mt-2">
            <img 
                src="<?= htmlspecialchars($imagePath) ?>"
                title="Profile Picture" 
                alt="Profile Picture" 
                class="w-10 h-10 rounded-full object-cover border border-gray-300 flex-shrink-0" 
                onerror="this.onerror=null;this.src='<?= htmlspecialchars($defaultImage) ?>';"
            />
            <div class="max-w-[150px] overflow-hidden truncate transition-all duration-300 sidebar-expanded:block sidebar-collapsed:hidden">
    <div class="text-sm font-medium text-gray-100 whitespace-normal">
        <?= htmlspecialchars(ucwords(strtolower($username))) ?>
    </div>
</div>

        </div>

        <button
            title="Logout" id="logoutBtn"
            class="inline-flex items-center justify-center gap-2 mt-2 rounded text-sm font-medium text-white h-10 w-10 transition-colors hover:bg-red-600 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 ring-offset-background"
            onclick="confirmLogout()">
            <svg xmlns="http://www.w3.org/2000/svg"
                width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-log-out h-5 w-5">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" x2="9" y1="12" y2="12"></line>
            </svg>
        </button>
    </div>
</aside>

<script>
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');
const toggleIcon = toggleBtn?.querySelector('i');
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const menuIcon = document.getElementById('menuIcon');
const mainContent = document.getElementById('mainContent');

// Mobile menu functionality
mobileMenuBtn?.addEventListener('click', () => {
    const isOpen = sidebar.classList.contains('open');
    sidebar.classList.toggle('open');
    sidebar.classList.remove('collapsed');
    menuIcon.classList.toggle('bi-list', isOpen);
    menuIcon.classList.toggle('bi-x', !isOpen);

    // Remove left margin on mobile
    if (mainContent) {
        mainContent.classList.remove('ml-[256px]');
        mainContent.classList.remove('ml-[64px]');
    }
});

// Desktop sidebar toggle
toggleBtn?.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    const collapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebar-collapsed', collapsed);

    if (toggleIcon) {
        toggleIcon.classList.toggle('bi-layout-sidebar-inset', !collapsed);
        toggleIcon.classList.toggle('bi-layout-sidebar-inset-reverse', collapsed);
    }

    // Adjust mainContent margin
    if (mainContent) {
        mainContent.classList.toggle('ml-[256px]', !collapsed);
        mainContent.classList.toggle('ml-[64px]', collapsed);
    }
});

// Initialize sidebar state
window.addEventListener('DOMContentLoaded', () => {
    const isDesktop = window.innerWidth >= 768;
    const collapsed = localStorage.getItem('sidebar-collapsed') === 'true';

    if (isDesktop && collapsed) {
        sidebar.classList.add('collapsed');
        mainContent?.classList.remove('ml-[256px]');
        mainContent?.classList.add('ml-[64px]');
    } else {
        sidebar.classList.remove('collapsed');
        if (isDesktop) {
            mainContent?.classList.remove('ml-[64px]');
            mainContent?.classList.add('ml-[256px]');
        }
    }
});

// Handle window resize
window.addEventListener('resize', () => {
    const isDesktop = window.innerWidth >= 768;
    if (!isDesktop) {
        sidebar.classList.remove('collapsed');
        localStorage.removeItem('sidebar-collapsed');
        mainContent?.classList.remove('ml-[64px]');
        mainContent?.classList.remove('ml-[256px]');
    } else {
        const collapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (collapsed) {
            sidebar.classList.add('collapsed');
            mainContent?.classList.remove('ml-[256px]');
            mainContent?.classList.add('ml-[64px]');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent?.classList.remove('ml-[64px]');
            mainContent?.classList.add('ml-[256px]');
        }
    }
});

function confirmLogout() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will be logged out.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, logout'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php?payroll=logout1';
        }
    });
}
</script>

