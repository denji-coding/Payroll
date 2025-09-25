<?php
$username = $_SESSION['USERNAME'] ?? 'Unknown User';
$email = $_SESSION['SESSION_EMAIL'] ?? 'no-email@example.com';

// Get initials
$nameParts = explode(' ', $username);
$initials = '';
foreach ($nameParts as $part) {
    $initials .= strtoupper(substr($part, 0, 1));
}?>

<!-- Header -->
<header class="fixed top-0 ml-[31px] left-56 right-0 z-50 bg-white h-14 shadow-sm">
    <div class="flex items-center -mt-4 justify-between px-6 py-4">
        <span class="text-xl font-semibold text-[#403E43]">Migrants Venture HR & Payroll</span>
        <div class="flex items-center gap-6">
            <!-- Notification -->
            <div class="relative inline-block">
                <button id="notificationBtn" class="relative">
                    <i class="bi bi-bell text-xl text-[#396A39] p-1 rounded-md"></i>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full">1</span>
                </button>

                <div id="notificationDropdown" class="absolute right-0 mt-2 w-60 bg-white border border-gray-200 rounded shadow-md hidden z-50 transition-all duration-200 ease-in-out" style="left: -204.9px;">
                    <!-- <div class="absolute -top-2 right-3">
                        <div class="w-4 h-4 bg-white border-l border-t border-gray-200 transform rotate-45"></div>
                    </div> -->
                    <button id="closeNotificationDropdown" class="absolute right-3 text-gray-400 hover:text-gray-600 text-2xl">
                        &times;
                    </button>
                    <div class="p-4 pt-6 text-sm text-gray-700">
                        <p><strong>New Attendance Alert</strong></p>
                        <p class="text-xs text-gray-500">You have 1 new check-in today.</p>
                    </div>
                    <div class="border-t text-center text-sm text-blue-600 py-2 hover:bg-blue-100 cursor-pointer">
                        View All Notifications
                    </div>
                </div>
            </div>

            <!-- Profile Dropdown -->
            <div class="relative" style="margin-top: 2px;">
                <button id="profileDropdownBtn" class="w-9 h-9 rounded-circle bg-[#396A39] flex items-center justify-center text-white font-semibold focus:outline-none">
                    <?= htmlspecialchars($initials) ?>
                </button>
                <div id="profileDropdown" class="absolute right-0 mt-[12px] w-64 bg-white border rounded-lg shadow-md p-4 hidden z-50" style="left: -210px;">
                    <button id="closeProfileDropdown" class="absolute top-1 right-[21px] text-xl text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                    <!-- <div class="absolute -top-2 right-5">
                        <div class="w-4 h-4 bg-white border-t border-l border-gray-200 rotate-45"></div>
                    </div> -->
                    <div class="mb-2">
                        <h4 class="font-semibold text-[#403E43]"><?= htmlspecialchars($username) ?></h4>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($email) ?></p>
                    </div>
                    <hr class="my-2">
                    <ul class="space-y-2 text-sm text-[#403E43]">
                        <li><a href="index.php?payroll=admin_profile" class="bi bi-person-fill block hover:text-green-700"> Profile</a></li>
                        <!-- <li><a href="#" class="bi bi-gear block hover:text-green-700"> Settings</a></li> -->
                        <hr>
                        <li><a href="index.php?payroll=logout1" onclick="confirmLogout(event)" class="bi bi-box-arrow-right block hover:text-green-700"> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

