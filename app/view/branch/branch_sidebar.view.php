<?php
require_once '../app/core/database.php'; // adjust the path as needed
$currentPage = $_GET['payroll'] ?? basename($_SERVER['PHP_SELF']);
require_once views_path("partials/header");
// require_once views_path("partials/user_navbar");

// $username = $_SESSION['m_first_name'] . ' ' . $_SESSION['m_middle_name'][0] . '. ' . $_SESSION['m_last_name'] ?? 'Unknown Employee';
// $email = $_SESSION['m_email'] ?? 'no-email@example.com';

// $imagePath = (!empty($_SESSION['photo_path']))
//     ? '../public/upload/' . basename($_SESSION['photo_path'])
//     : '../public/assets/image/default_user_image.svg';

// $currentPage = $_GET['payroll'] ?? 'user_dashboard';



$username = "Manager"; // default fallback

if (isset($_SESSION['manager_id'])) {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT m_first_name, m_middle_name, m_last_name FROM managers WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['manager_id']]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($manager) {
        $first = $manager['m_first_name'] ?? '';
        $middle = $manager['m_middle_name'] ?? '';
        $last = $manager['m_last_name'] ?? '';
        $username = $first . ' ' . ($middle ? $middle[0] . '. ' : '') . $last;
    }
}
?>

<style>

a {
    text-decoration: none;
}
#sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh; /* full viewport height */
    width: 16rem; /* 256px */
    background-color: #0b5125;
    color: white;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow-y: auto; /* scroll inside sidebar if content overflows */
    z-index: 1000; /* make sure it's on top */
    transition: width 0.3s ease, padding 0.3s ease;
    flex-shrink: 0; /* prevent sidebar from shrinking */
}

/* Transition all relevant elements smoothly */
#sidebar a i,
#sidebar a span,
#sidebar .text-center img,
#sidebar .text-center span.text-lg,
#sidebar > div:last-child .bg-green-700,
#sidebar > div:last-child .font-bold {
    transition: all 0.3s ease;
}

/* Expanded state (default) */
#sidebar a span,
#sidebar .text-center span.text-lg,
#sidebar > div:last-child .font-bold,
#sidebar > div:last-child .bg-green-700 {
    opacity: 1;
    max-width: 200px;
    transform: scale(1);
    overflow: hidden;
    white-space: nowrap;
}

/* Logo image default size (expanded) */
#sidebar .text-center img {
    width: 64px;
    height: 64px;
    transition: width 0.3s ease, height 0.3s ease;
}

/* COLLAPSED STATE */
#sidebar.collapsed {
    width: 64px !important;
    padding: 1.5rem 0.5rem !important;
    /* justify-content: flex-start !important; */
}

/* Nav links when collapsed */
#sidebar.collapsed a {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    gap: 0 !important;
    margin-bottom: 5px;
     /* Adjust to align with icon */
    
}

#sidebar.collapsed nav {
    margin-bottom: 1.2rem !important;
    margin-top: -.1rem !important;
}

#sidebar.collapsed a i {
    font-size: 1rem;
}

/* Fade and hide text when collapsed */
#sidebar.collapsed a span,
#sidebar.collapsed .text-center span.text-lg,
#sidebar.collapsed > div:last-child .font-bold,
#sidebar.collapsed > div:last-child .bg-green-700 {
    opacity: 0;
    max-width: 0;
    transform: scale(0.8);
    overflow: hidden;
    white-space: nowrap;
}

/* Shrink logo image */
#sidebar.collapsed .text-center img {
    width: 32px !important;
    height: 32px !important;
}

/* ===== BOTTOM PROFILE AREA ===== */

/* Username container transition */
#sidebar > div:last-child .truncate {
    transition: max-width 0.3s ease, opacity 0.3s ease;
}

/* Hide username text when collapsed */
#sidebar.collapsed > div:last-child .truncate {
    max-width: 0 !important;
    opacity: 0 !important;
    overflow: hidden;
    pointer-events: none;
}

/* Show username when expanded */
#sidebar:not(.collapsed) > div:last-child .truncate {
    max-width: 150px;
    opacity: 1;
    overflow: visible;
    pointer-events: auto;
}

/* Layout for bottom profile section */
#sidebar > div:last-child {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
}

/* Adjust layout when collapsed */
#sidebar.collapsed > div:last-child {
    justify-content: center !important;
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
}

/* Profile image inside collapsed left block - centered */
#sidebar.collapsed > div:last-child > div.flex {
    flex-grow: 1;
    justify-content: center !important;
    gap: 0 !important;
}

/* Logout button remains aligned */
#sidebar > div:last-child button {
    flex-shrink: 0;
}

/* Sidebar bottom section layout when collapsed */
#sidebar.collapsed > div:last-child {
    flex-direction: column !important;
    align-items: center !important;
    padding: 0.75rem 0.5rem !important;
    gap: 0.5rem;
}

/* Center image when collapsed */
#sidebar.collapsed .sidebar-user {
    justify-content: center !important;
}

/* Remove text spacing when collapsed */
#sidebar.collapsed .sidebar-user .truncate {
    display: none !important;
}

/* ===== Employee Portal Label & Initials Handling ===== */

/* Initially hide the initials */
#sidebar .label-short {
    display: none;
}

/* For the full label (expanded) */
#sidebar:not(.collapsed) #portalLabel .label-full {
    opacity: 1 !important;
    max-width: 200px !important;
    transform: scale(1) !important;
    overflow: visible !important;
    white-space: nowrap;
    display: inline !important;
}

/* For the short label (expanded) hide it */
#sidebar:not(.collapsed) #portalLabel .label-short {
    opacity: 0 !important;
    max-width: 0 !important;
    transform: scale(0.8) !important;
    overflow: hidden !important;
    white-space: nowrap;
    display: none !important;
}

/* When collapsed, show short label fully */
#sidebar.collapsed #portalLabel .label-short {
    opacity: 1 !important;
    max-width: none !important; /* allow full width */
    transform: none !important; /* remove scale if not needed */
    overflow: visible !important;
    white-space: nowrap;
    display: inline-block !important;
    position: relative; /* make sure it positions correctly */
    visibility: visible !important;
}


/* When collapsed, hide full label */
#sidebar.collapsed #portalLabel .label-full {
    opacity: 0 !important;
    max-width: 0 !important;
    transform: scale(0.8) !important;
    overflow: hidden !important;
    white-space: nowrap;
    display: none !important;
}

/* Always show underline */
#sidebar #portalLabel .underline {
    display: inline-block !important;
    position: absolute;
    bottom: 0;
    left: 50%;
    height: 4px;
    background-color: #22c55e; /* green-500 */
    border-radius: 9999px;
    transition: all 0.3s ease;
}

/* Underline style for expanded (full label) */
#sidebar:not(.collapsed) #portalLabel .underline {
    width: 100%;
    margin-left: 89px; /* reset margin */
}

/* Ensure parent wrapper is relatively positioned */
#sidebar.collapsed #portalLabel .label-short-wrapper {
    position: relative;
    display: inline-block;
    text-align: center;
}

/* Underline inside short label wrapper */
#sidebar.collapsed #portalLabel .underline {
    width: 100%;
    position: absolute;
    bottom: -4px; /* or adjust as needed */
    left: 14px;
    transform: none; /* remove translateX */
    height: 4px;
    background-color: #22c55e; /* green-500 */
    border-radius: 9999px;
    transition: all 0.3s ease;
}


main#mainContent {
    flex: 1;
    transition: margin-left 0.3s ease; /* optional */
    /* no margin-left needed */
}

</style>

<aside id="sidebar" class="w-64 flex flex-col justify-between" style="background-color: #0b5125; color: white; height: 100vh; padding: 1.5rem;">
    
    <!-- Top: User Info & Navigation -->
    <div>
        <!-- Profile Section -->
        <div class="text-center mb-6">
            <!-- Sidebar Toggle Button -->
            <div style="display: flex; justify-content: center; align-items: center;">
                <button id="sidebarToggle"  title="Close sidebar"
                        class="mb-4 bg-green-600 hover:bg-green-700 text-white flex items-center justify-center rounded" 
                        style="width: 40px; height: 40px; padding: 0;">
                    <i class="bi bi-layout-sidebar-inset"></i>
                </button>
            </div>

            <!-- <img src="../public/assets/image/logo2.png" alt="Organization Logo"  class="mx-auto w-100 h-20 mb-3 rounded-md object-cover" /> -->
            <div id="portalLabel" class="text-white text-lg font-extrabold tracking-wide uppercase relative inline-block pb-2">
                <span class="label-full">Manager Portal</span>
                <span class="label-short-wrapper">
                <span class="label-short">MP</span>
                <span class="underline absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-1 bg-green-600 rounded-full"></span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex flex-col mt-12 space-y-1 gap-2">
            <a href="?payroll=manager_dashboard" title="Dashboard"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'manager_dashboard') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-house-door"></i> <span>Dashboard</span>
            </a>

            <a href="?payroll=branch_profile" title="My Profile"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'branch_profile') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-person"></i> <span>My Profile</span>
            </a>

            <a href="index.php?payroll=branch_employeeslist" title="Employee's List"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'branch_employeeslist') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                <i class="bi bi-people"></i> 
                <span>Employee's List</span>
            </a>

            <a href="index.php?payroll=branch_approvals" title="Employee's List"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'branch_approvals') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
                <i class="bi bi-check-circle"></i>
                <span>Approvals</span>
            </a>

            <a href="index.php?payroll=branch_leavemanagement" title="Leave Application"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'branch_leavemanagement') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-calendar-x"></i> <span>Leave Management</span>
            </a>

            <a href="index.php?payroll=branch_payroll" title="Payroll"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'branch_payroll') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="bi bi-credit-card"></i> <span>Payroll</span>
            </a>

            <a href="index.php?payroll=branch_reports" title="Reports"
            class="w-full flex items-center font-semibold text-white text-sm gap-2 p-2 px-4 rounded no-underline <?= ($currentPage == 'branch_reports') ? 'bg-[#206037] border-l-4 border-white' : 'hover:bg-[#206037] hover:border-l-4 hover:border-white' ?>">
            <i class="fas fa-chart-column"></i> <span>Reports</span>
            </a>
        </nav>
    </div>

    <!-- Bottom: Logout -->
    <div class="flex items-center justify-between text-white px-3 py-2 border-t">
        <div class="flex items-center gap-3 min-w-0 sidebar-user mt-2">
            <img 
                src="../public/assets/image/man (1).png"
                title="Profile Picture" 
                alt="Profile Picture" 
                class="w-10 h-10 rounded-full object-cover bg-white border border-gray-300 flex-shrink-0" 
                onerror="this.onerror=null;this.src='public/uploads/employees/default.png';"
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
function confirmLogout() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will be logged out of your account.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php?payroll=login_manager&logout=true';
        }
    });
}
</script>

<script>
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');
const toggleIcon = toggleBtn.querySelector('i');
const mainContent = document.getElementById('mainContent');
const portalLabel = document.getElementById('portalLabel');

function updateToggleIcon() {
    if (sidebar.classList.contains('collapsed')) {
        toggleIcon.classList.remove('bi-layout-sidebar-inset');
        toggleIcon.classList.add('bi-layout-sidebar-inset-reverse');
    } else {
        toggleIcon.classList.remove('bi-layout-sidebar-inset-reverse');
        toggleIcon.classList.add('bi-layout-sidebar-inset');
    }
}

function updateMainMargin() {
    if (sidebar.classList.contains('collapsed')) {
        mainContent.style.marginLeft = '64px';  // collapsed sidebar width
    } else {
        mainContent.style.marginLeft = '256px'; // expanded sidebar width
    }
}

function updatePortalLabel() {
    const isCollapsed = sidebar.classList.contains('collapsed');

    // Find the label elements inside portalLabel
    const labelFull = portalLabel.querySelector('.label-full');
    const labelShort = portalLabel.querySelector('.label-short');
    const underline = portalLabel.querySelector('.underline');

    if (isCollapsed) {
        if (labelFull) labelFull.style.display = 'none';
        if (underline) underline.style.display = 'none';
        if (labelShort) labelShort.style.display = 'inline-block';
    } else {
        if (labelFull) labelFull.style.display = 'inline';
        if (underline) underline.style.display = 'block';
        if (labelShort) labelShort.style.display = 'none';
    }
}

window.addEventListener('DOMContentLoaded', () => {
    const savedState = localStorage.getItem('sidebar-collapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
    } else {
        sidebar.classList.remove('collapsed');
    }
    updateToggleIcon();
    updateMainMargin();
    // updatePortalLabel();
});

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
    if (sidebar.classList.contains('collapsed')) {
        toggleBtn.title = 'Open sidebar';
    } else {
        toggleBtn.title = 'Close sidebar';
    }
    updateToggleIcon();
    updateMainMargin();
    // updatePortalLabel();
});
</script>

