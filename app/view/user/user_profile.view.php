<?php
$title = "My Profile";
require_once views_path("partials/header");

echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

// Database connection and employee data fetching
require_once '../app/core/database.php';
$conn = new Database();
$pdo = $conn->getConnection();

// Function to format name with first letter of each word capitalized
function formatName($name) {
    return ucwords(strtolower(trim($name)));
}

// Fetch user details - support employees, managers, and HR
$employee = [];
$userType = 'employee';

// Check if user is HR/Admin
if (isset($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['SESSION_USER_ID'])) {
    $hrId = $_SESSION['SESSION_USER_ID'];
    $stmt = $pdo->prepare("SELECT hr_first_name, hr_middle_name, hr_last_name, hr_email, hr_contact_number, hr_position, hr_address, hr_dob, hr_place_of_birth, hr_sex, hr_civil_status, hr_citizenship, hr_blood_type, hr_photo_path, hr_created_at, hr_updated_at FROM admins WHERE id = :hr_id AND deleted_at IS NULL");
    $stmt->execute([':hr_id' => $hrId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userData) {
        $firstName = formatName($userData['hr_first_name'] ?? '');
        $middleName = $userData['hr_middle_name'] ?? '';
        $lastName = formatName($userData['hr_last_name'] ?? '');
        
        $employee = [
            'name' => trim($firstName . ' ' . ($middleName ? formatName($middleName)[0] . '. ' : '') . $lastName),
            'full_name' => trim($firstName . ' ' . ($middleName ? formatName($middleName) . ' ' : '') . $lastName),
            'email' => $userData['hr_email'] ?? '',
            'contact_number' => $userData['hr_contact_number'] ?? '',
            'position' => $userData['hr_position'] ?? '',
            'address' => $userData['hr_address'] ?? '',
            'dob' => $userData['hr_dob'] ?? '',
            'place_of_birth' => $userData['hr_place_of_birth'] ?? '',
            'sex' => $userData['hr_sex'] ?? '',
            'civil_status' => $userData['hr_civil_status'] ?? '',
            'citizenship' => $userData['hr_citizenship'] ?? '',
            'blood_type' => $userData['hr_blood_type'] ?? '',
            'photo_path' => $userData['hr_photo_path'] ?? '',
            'created_at' => $userData['hr_created_at'] ?? '',
            'updated_at' => $userData['hr_updated_at'] ?? ''
        ];
        $userType = 'hr';
    }
}
// Check if user is Manager
elseif (isset($_SESSION['manager_id']) && !empty($_SESSION['manager_id'])) {
    $managerId = $_SESSION['manager_id'];
    $stmt = $pdo->prepare("SELECT m_first_name, m_middle_name, m_last_name, m_email, m_contact_number, m_position, m_address, m_dob, m_place_of_birth, m_sex, m_civil_status, m_citizenship, m_blood_type, m_photo_path, m_created_at, m_updated_at FROM managers WHERE id = :manager_id AND deleted_at IS NULL");
    $stmt->execute([':manager_id' => $managerId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userData) {
        $firstName = formatName($userData['m_first_name'] ?? '');
        $middleName = $userData['m_middle_name'] ?? '';
        $lastName = formatName($userData['m_last_name'] ?? '');
        
        $employee = [
            'name' => trim($firstName . ' ' . ($middleName ? formatName($middleName)[0] . '. ' : '') . $lastName),
            'full_name' => trim($firstName . ' ' . ($middleName ? formatName($middleName) . ' ' : '') . $lastName),
            'email' => $userData['m_email'] ?? '',
            'contact_number' => $userData['m_contact_number'] ?? '',
            'position' => $userData['m_position'] ?? '',
            'address' => $userData['m_address'] ?? '',
            'dob' => $userData['m_dob'] ?? '',
            'place_of_birth' => $userData['m_place_of_birth'] ?? '',
            'sex' => $userData['m_sex'] ?? '',
            'civil_status' => $userData['m_civil_status'] ?? '',
            'citizenship' => $userData['m_citizenship'] ?? '',
            'blood_type' => $userData['m_blood_type'] ?? '',
            'photo_path' => $userData['m_photo_path'] ?? '',
            'created_at' => $userData['m_created_at'] ?? '',
            'updated_at' => $userData['m_updated_at'] ?? ''
        ];
        $userType = 'manager';
    }
}
// Regular Employee
elseif (isset($_SESSION['employee_no']) || isset($_SESSION['employee_id'])) {
    $employeeNo = $_SESSION['employee_no'] ?? $_SESSION['employee_id'];
    $stmt = $pdo->prepare("SELECT first_name, middle_name, last_name, email, contact_number, position, address, dob, place_of_birth, sex, civil_status, citizenship, blood_type, photo_path, created_at, updated_at FROM employees WHERE employee_no = :employee_no1 OR id = :employee_id");
    $stmt->execute([':employee_no1' => $employeeNo, ':employee_id' => $employeeNo]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userData) {
        $firstName = formatName($userData['first_name'] ?? '');
        $middleName = $userData['middle_name'] ?? '';
        $lastName = formatName($userData['last_name'] ?? '');
        
        $employee = [
            'name' => trim($firstName . ' ' . ($middleName ? formatName($middleName)[0] . '. ' : '') . $lastName),
            'full_name' => trim($firstName . ' ' . ($middleName ? formatName($middleName) . ' ' : '') . $lastName),
            'email' => $userData['email'] ?? '',
            'contact_number' => $userData['contact_number'] ?? '',
            'position' => $userData['position'] ?? '',
            'address' => $userData['address'] ?? '',
            'dob' => $userData['dob'] ?? '',
            'place_of_birth' => $userData['place_of_birth'] ?? '',
            'sex' => $userData['sex'] ?? '',
            'civil_status' => $userData['civil_status'] ?? '',
            'citizenship' => $userData['citizenship'] ?? '',
            'blood_type' => $userData['blood_type'] ?? '',
            'photo_path' => $userData['photo_path'] ?? '',
            'created_at' => $userData['created_at'] ?? '',
            'updated_at' => $userData['updated_at'] ?? ''
        ];
    }
}
?>

<div class="flex min-h-screen overflow-hidden">
    <!-- Main content -->
    <main id="mainContent" class="flex-1 p-3 sm:p-4 md:p-6 bg-gray-100 transition-margin duration-300 ease-in-out md:ml-64">
        <?php require_once views_path("partials/user_sidebar"); ?>

        <div class="mb-4 md:mb-6 pt-2 sm:pt-4">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold tracking-tight text-gray-800">My Profile</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1 md:mt-2">View and manage your personal information and account settings.</p>
        </div>

        <!-- Profile Header Card -->
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg md:rounded-xl overflow-hidden border border-gray-200">
            <!-- Profile Header with Background -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-4 sm:px-6 sm:py-5 md:px-8 md:py-6">
                <div class="flex flex-col items-center text-center space-y-3 sm:space-y-4">
                    <!-- Profile Image -->
                    <div class="relative">
                        <?php
                        // Format photo path based on user type
                        $photoSrc = '';
                        if (!empty($employee['photo_path'])) {
                            if ($userType === 'hr' || $userType === 'manager') {
                                // HR/Manager photos are in public/upload or public/assets/image
                                $photoSrc = '../public/' . ltrim($employee['photo_path'], '/');
                            } else {
                                // Employee photos are in public/upload
                                $photoSrc = '../public/upload/' . (strpos($employee['photo_path'], 'upload/') === 0 ? substr($employee['photo_path'], 7) : $employee['photo_path']);
                            }
                        } else {
                            $photoSrc = ($employee['sex'] == 'Female' ? '../public/assets/image/default_women.png' : '../public/assets/image/default_men.png');
                        }
                        $defaultPhoto = ($employee['sex'] == 'Female' ? '../public/assets/image/default_women.png' : '../public/assets/image/default_men.png');
                        ?>
                        <img src="<?= htmlspecialchars($photoSrc) ?>"
                     alt="Profile Photo"
                             class="w-24 h-24 sm:w-28 sm:h-28 md:w-32 md:h-32 rounded-full border-4 border-white shadow-lg object-cover"
                             onerror="this.onerror=null;this.src='<?= htmlspecialchars($defaultPhoto) ?>';">
                        <div class="absolute -bottom-1 -right-1 sm:-bottom-2 sm:-right-2 bg-green-500 w-5 h-5 sm:w-6 sm:h-6 rounded-full border-2 border-white"></div>
                    </div>

                    <!-- Employee Name and Position -->
                    <div class="text-white">
                        <h1 class="text-lg sm:text-xl md:text-2xl font-bold profile-header-name"><?= htmlspecialchars($employee['name'] ?? 'N/A') ?></h1>
                        <p class="text-emerald-100 text-sm sm:text-base md:text-lg"><?= htmlspecialchars(ucwords($employee['position'] ?? 'N/A')) ?></p>
                        <!-- <p class="text-emerald-100 text-xs sm:text-sm"><?= ucfirst($userType === 'hr' ? 'HR' : ($userType === 'manager' ? 'Manager' : 'Employee')) ?></p> -->
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 sm:gap-3 mt-2 sm:mt-4 w-full sm:w-auto">
                        <button onclick="openChangePasswordModal()" class="flex-1 sm:flex-initial inline-flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-4 sm:px-6 py-2 text-sm sm:text-base rounded-lg shadow-sm transition-colors duration-200">
                            <i class="bi bi-key-fill mr-2"></i> <span class="hidden sm:inline">Change Password</span><span class="sm:hidden">Password</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="p-4 sm:p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                    <!-- Personal Information -->
                    <div class="space-y-4 md:space-y-6">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-800 border-b border-gray-200 pb-2">Personal Information</h3>
                        
                        <div class="space-y-3 md:space-y-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Full Name</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200 flex items-center justify-between group gap-2">
                                    <p id="fullNameDisplay" class="text-gray-800 font-medium text-sm sm:text-base flex-1 truncate" data-original="<?= htmlspecialchars($employee['name'] ?? 'N/A') ?>"><?= htmlspecialchars($employee['name'] ?? 'N/A') ?></p>
                                    <input type="text" id="fullNameInput" value="<?= htmlspecialchars($employee['full_name'] ?? 'N/A') ?>" 
                                           class="hidden flex-1 px-2 py-1 sm:py-0.5 border border-gray-300 rounded text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                           oninput="toggleSaveButton('fullName')">
                                    <div class="flex items-center space-x-1 sm:space-x-2 flex-shrink-0">
                                        <button id="fullNameEditBtn" onclick="toggleEdit('fullName')" 
                                                class="text-gray-400 hover:text-emerald-600 transition-colors duration-200 p-1">
                                            <i class="bi bi-pencil-fill text-sm sm:text-base"></i>
                                        </button>
                                        <button id="fullNameSaveBtn" onclick="saveEdit('fullName')" 
                                                class="hidden text-green-600 hover:text-green-700 transition-colors duration-200 p-1">
                                            <i class="bi bi-check-lg text-sm sm:text-base"></i>
                                        </button>
                                        <button id="fullNameCancelBtn" onclick="cancelEdit('fullName')" 
                                                class="hidden text-red-600 hover:text-red-700 transition-colors duration-200 p-1">
                                            <i class="bi bi-x-lg text-sm sm:text-base"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Email Address</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200 flex items-center justify-between group gap-2">
                                    <p id="emailDisplay" class="text-gray-800 text-sm sm:text-base flex-1 truncate" data-original="<?= htmlspecialchars($employee['email'] ?? 'N/A') ?>"><?= htmlspecialchars($employee['email'] ?? 'N/A') ?></p>
                                    <input type="email" id="emailInput" value="<?= htmlspecialchars($employee['email'] ?? 'N/A') ?>" 
                                           class="hidden flex-1 px-2 py-1 sm:py-0.5 border border-gray-300 rounded text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                           oninput="toggleSaveButton('email')">
                                    <div class="flex items-center space-x-1 sm:space-x-2 flex-shrink-0">
                                        <button id="emailEditBtn" onclick="toggleEdit('email')" 
                                                class="text-gray-400 hover:text-emerald-600 transition-colors duration-200 p-1">
                                            <i class="bi bi-pencil-fill text-sm sm:text-base"></i>
                                        </button>
                                        <button id="emailSaveBtn" onclick="saveEdit('email')" 
                                                class="hidden text-green-600 hover:text-green-700 transition-colors duration-200 p-1">
                                            <i class="bi bi-check-lg text-sm sm:text-base"></i>
                                        </button>
                                        <button id="emailCancelBtn" onclick="cancelEdit('email')" 
                                                class="hidden text-red-600 hover:text-red-700 transition-colors duration-200 p-1">
                                            <i class="bi bi-x-lg text-sm sm:text-base"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Contact Number</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200 flex items-center justify-between group gap-2">
                                    <p id="contactNumberDisplay" class="text-gray-800 text-sm sm:text-base flex-1 truncate" data-original="<?= htmlspecialchars($employee['contact_number'] ?? 'N/A') ?>"><?= htmlspecialchars($employee['contact_number'] ?? 'N/A') ?></p>
                                    <input type="text" id="contactNumberInput" value="<?= htmlspecialchars($employee['contact_number'] ?? 'N/A') ?>" 
                                           class="hidden flex-1 px-2 py-1 sm:py-0.5 border border-gray-300 rounded text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                           oninput="toggleSaveButton('contactNumber')">
                                    <div class="flex items-center space-x-1 sm:space-x-2 flex-shrink-0">
                                        <button id="contactNumberEditBtn" onclick="toggleEdit('contactNumber')" 
                                                class="text-gray-400 hover:text-emerald-600 transition-colors duration-200 p-1">
                                            <i class="bi bi-pencil-fill text-sm sm:text-base"></i>
                                        </button>
                                        <button id="contactNumberSaveBtn" onclick="saveEdit('contactNumber')" 
                                                class="hidden text-green-600 hover:text-green-700 transition-colors duration-200 p-1">
                                            <i class="bi bi-check-lg text-sm sm:text-base"></i>
                                        </button>
                                        <button id="contactNumberCancelBtn" onclick="cancelEdit('contactNumber')" 
                                                class="hidden text-red-600 hover:text-red-700 transition-colors duration-200 p-1">
                                            <i class="bi bi-x-lg text-sm sm:text-base"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Position</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 font-medium text-sm sm:text-base"><?= htmlspecialchars(ucwords($employee['position'] ?? 'N/A')) ?></p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Address</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 text-sm sm:text-base"><?= htmlspecialchars(ucwords($employee['address'] ?? 'N/A')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="space-y-4 md:space-y-6">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-800 border-b border-gray-200 pb-2">Additional Information</h3>
                        
                        <div class="space-y-3 md:space-y-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Date of Birth</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 text-sm sm:text-base"><?= isset($employee['dob']) ? htmlspecialchars(date("F d, Y", strtotime($employee['dob']))) : 'N/A' ?></p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Place of Birth</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 text-sm sm:text-base"><?= htmlspecialchars(ucwords($employee['place_of_birth'] ?? 'N/A')) ?></p>
                                </div>
                            </div>

                <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Sex</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 text-sm sm:text-base"><?= htmlspecialchars(ucwords($employee['sex'] ?? 'N/A')) ?></p>
                                </div>
                </div>

                <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Civil Status</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 text-sm sm:text-base"><?= htmlspecialchars(ucwords($employee['civil_status'] ?? 'N/A')) ?></p>
                                </div>
                </div>

                <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Citizenship</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 text-sm sm:text-base"><?= htmlspecialchars(ucwords($employee['citizenship'] ?? 'N/A')) ?></p>
                                </div>
                </div>

                <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Blood Type</label>
                                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 text-sm sm:text-base"><?= htmlspecialchars(strtoupper($employee['blood_type'] ?? 'N/A')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="mt-6 md:mt-8 pt-6 md:pt-8 border-t border-gray-200">
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4 md:mb-6">Account Information</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                <div>
                            <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Account Status</label>
                            <div class="bg-green-50 p-2 sm:p-3 rounded-lg border border-green-200">
                                <span class="inline-flex items-center text-green-800 text-sm sm:text-base">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                    Active
                                </span>
                            </div>
                </div>

                <div>
                            <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Member Since</label>
                            <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200">
                                <p class="text-gray-800 text-sm sm:text-base"><?= isset($employee['created_at']) ? htmlspecialchars(date("F d, Y", strtotime($employee['created_at']))) : 'N/A' ?></p>
                            </div>
                </div>

                <div>
                            <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Last Updated</label>
                            <div class="bg-gray-50 p-2 sm:p-3 rounded-lg border border-gray-200">
                                <p class="text-gray-800 text-sm sm:text-base"><?= isset($employee['updated_at']) ? htmlspecialchars(date("F d, Y", strtotime($employee['updated_at']))) : 'N/A' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Modal -->
        <div id="changePasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full my-4">
                    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800">Change Password</h3>
                    </div>
                    
                    <form id="changePasswordForm" class="px-4 sm:px-6 py-4">
                        <div class="space-y-3 sm:space-y-4">
                <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Current Password</label>
                                <div class="relative">
                                    <input type="password" id="currentPassword" name="currentPassword" required 
                                           class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    <button type="button" onclick="togglePassword('currentPassword')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="bi bi-eye text-sm sm:text-base" id="currentPasswordEye"></i>
                                    </button>
                                </div>
                </div>
                            
                <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">New Password</label>
                                <div class="relative">
                                    <input type="password" id="newPassword" name="newPassword" required 
                                           class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    <button type="button" onclick="togglePassword('newPassword')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="bi bi-eye text-sm sm:text-base" id="newPasswordEye"></i>
                                    </button>
                                </div>
                </div>
                            
                <div>
                                <label class="block text-gray-700 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Confirm New Password</label>
                                <div class="relative">
                                    <input type="password" id="confirmPassword" name="confirmPassword" required 
                                           class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    <button type="button" onclick="togglePassword('confirmPassword')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="bi bi-eye text-sm sm:text-base" id="confirmPasswordEye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-4 sm:mt-6">
                            <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 text-sm sm:text-base">
                                Update Password
                            </button>
                            <button type="button" onclick="closeChangePasswordModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200 text-sm sm:text-base">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
/* Responsive adjustments for profile page */
@media (max-width: 767px) {
    main#mainContent {
        margin-left: 0 !important;
        padding-top: calc(var(--mobile-navbar-height, 3.5rem) + 0.5rem) !important;
    }
    
    /* Ensure heading is visible on mobile */
    main#mainContent > div:first-of-type {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }
}

@media (min-width: 768px) {
    main#mainContent:not(.ml-\[64px\]) {
        margin-left: 256px;
    }
}

/* Ensure text doesn't overflow on mobile */
#fullNameDisplay, #emailDisplay, #contactNumberDisplay {
    word-break: break-word;
    overflow-wrap: break-word;
}

/* Make sure heading is always visible */
h1.text-xl {
    display: block !important;
    visibility: visible !important;
}
</style>

<script>
function openChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.remove('hidden');
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.add('hidden');
    document.getElementById('changePasswordForm').reset();
}

// Close modal when clicking outside
document.getElementById('changePasswordModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeChangePasswordModal();
    }
});

// Handle password change form submission
document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validation
    if (newPassword !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Password Mismatch',
            text: 'New password and confirm password do not match!'
        });
        return;
    }
    
    if (newPassword.length < 6) {
        Swal.fire({
            icon: 'error',
            title: 'Password Too Short',
            text: 'New password must be at least 6 characters long!'
        });
        return;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i>Updating...';
    submitBtn.disabled = true;
    
    try {
        // Send request to API
        const response = await fetch('../app/api/user_change_password-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                currentPassword: currentPassword,
                newPassword: newPassword,
                confirmPassword: confirmPassword
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Password Updated',
                text: result.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                closeChangePasswordModal();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: result.message,
                confirmButtonColor: '#ef4444'
            });
        }
        
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to connect to server. Please try again.',
            confirmButtonColor: '#ef4444'
        });
    } finally {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eyeIcon = document.getElementById(`${inputId}Eye`);

    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.remove('bi-eye');
        eyeIcon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        eyeIcon.classList.remove('bi-eye-slash');
        eyeIcon.classList.add('bi-eye');
    }
}

// Functions for editable fields
function toggleEdit(fieldId) {
    const displayElement = document.getElementById(`${fieldId}Display`);
    const inputElement = document.getElementById(`${fieldId}Input`);
    const editBtn = document.getElementById(`${fieldId}EditBtn`);
    const saveBtn = document.getElementById(`${fieldId}SaveBtn`);
    const cancelBtn = document.getElementById(`${fieldId}CancelBtn`);

    if (displayElement.classList.contains('hidden')) {
        // Exiting edit mode - show display, hide input and action buttons
        displayElement.classList.remove('hidden');
        inputElement.classList.add('hidden');
        editBtn.classList.remove('hidden');
        saveBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
    } else {
        // Entering edit mode - hide display, show input and action buttons
        // For fullName, use the full name value, otherwise use display text
        if (fieldId === 'fullName') {
            // The input already has the full name value from PHP
            inputElement.value = inputElement.value; // Keep the full name value
        } else {
            inputElement.value = displayElement.textContent;
        }
        displayElement.classList.add('hidden');
        inputElement.classList.remove('hidden');
        editBtn.classList.add('hidden');
        saveBtn.classList.add('hidden'); // Initially hide save button since there are no changes
        cancelBtn.classList.remove('hidden');
        
        // Focus on input field
        inputElement.focus();
    }
}

async function saveEdit(fieldId) {
    const inputElement = document.getElementById(`${fieldId}Input`);
    const originalValue = inputElement.value.trim();
    const displayElement = document.getElementById(`${fieldId}Display`);
    const editBtn = document.getElementById(`${fieldId}EditBtn`);
    const saveBtn = document.getElementById(`${fieldId}SaveBtn`);
    const cancelBtn = document.getElementById(`${fieldId}CancelBtn`);

    // Client-side validation
    if (!originalValue) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Field cannot be empty'
        });
        return;
    }

    if (fieldId === 'email' && !isValidEmail(originalValue)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please enter a valid email address'
        });
        return;
    }

    if (fieldId === 'fullName' && originalValue.split(' ').length < 2) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Full name must contain at least first and last name'
        });
        return;
    }

    // Show loading state
    saveBtn.innerHTML = '<i class="bi bi-arrow-clockwise animate-spin"></i>';
    saveBtn.disabled = true;

    try {
        // Send data to API
        const response = await fetch('../app/api/user_update_profile-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                field: fieldId,
                value: originalValue
            })
        });

        const result = await response.json();

        if (result.success) {
            // Automatically refresh profile data after successful update
            await refreshProfileDataAfterUpdate();
            
            // Hide edit mode
            inputElement.classList.add('hidden');
            displayElement.classList.remove('hidden');
            editBtn.classList.remove('hidden');
            saveBtn.classList.add('hidden');
            cancelBtn.classList.add('hidden');

            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message,
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to update. Please try again.'
        });
    } finally {
        // Reset button state
        saveBtn.innerHTML = '<i class="bi bi-check-lg"></i>';
        saveBtn.disabled = false;
    }
}

// Function to format name with proper capitalization (matches PHP logic)
function formatName(name) {
    return name.toLowerCase().replace(/\b\w/g, function(char) {
        return char.toUpperCase();
    });
}

// Function to format name for display with middle name as initial
function formatDisplayName(name) {
    const nameParts = name.trim().split(' ');
    if (nameParts.length < 2) return name;
    
    const firstName = nameParts[0];
    const lastName = nameParts[nameParts.length - 1];
    let middleName = '';
    
    if (nameParts.length > 2) {
        // Take the first letter of the middle name(s)
        middleName = nameParts.slice(1, -1).map(part => part.charAt(0).toUpperCase() + '.').join(' ');
    }
    
    return `${firstName} ${middleName} ${lastName}`.replace(/\s+/g, ' ').trim();
}

// Function to update sidebar name display
function updateSidebarName(formattedName) {
    // Update the sidebar name display if it exists
    const sidebarNameElement = document.querySelector('.sidebar-employee-name');
    if (sidebarNameElement) {
        sidebarNameElement.textContent = formattedName;
    }
    
    // Also update the profile header name
    const profileHeaderName = document.querySelector('.profile-header-name');
    if (profileHeaderName) {
        profileHeaderName.textContent = formattedName;
    }
}

function cancelEdit(fieldId) {
    const inputElement = document.getElementById(`${fieldId}Input`);
    const displayElement = document.getElementById(`${fieldId}Display`);
    const editBtn = document.getElementById(`${fieldId}EditBtn`);
    const saveBtn = document.getElementById(`${fieldId}SaveBtn`);
    const cancelBtn = document.getElementById(`${fieldId}CancelBtn`);

    // Revert to current display value (which reflects the latest saved state)
    if (fieldId === 'fullName') {
        // For fullName, we need to get the actual full name from the database
        // Since we can't easily reconstruct the full middle name from initials,
        // we'll use the original full name value from PHP
        const fullNameValue = '<?= htmlspecialchars($employee['full_name'] ?? 'N/A') ?>';
        inputElement.value = fullNameValue;
    } else {
        // For other fields, use the current display text
        inputElement.value = displayElement.textContent;
    }
    
    inputElement.classList.add('hidden');
    displayElement.classList.remove('hidden');

    // Hide buttons
    editBtn.classList.remove('hidden');
    saveBtn.classList.add('hidden');
    cancelBtn.classList.add('hidden');
}

// Helper function to validate email format
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}



// Function to refresh profile data after update (without button loading state)
async function refreshProfileDataAfterUpdate() {
    try {
        const response = await fetch('../app/api/user_get_profile-api.php');
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            // Update all display fields
            updateProfileDisplay(data);
        } else {
            console.error('Failed to refresh profile data:', result.message);
        }
    } catch (error) {
        console.error('Error refreshing profile data:', error);
    }
}

// Function to update profile display with new data
function updateProfileDisplay(data) {
    // Update profile header name
    const profileHeaderName = document.querySelector('.profile-header-name');
    if (profileHeaderName) {
        profileHeaderName.textContent = data.name;
    }
    
    // Update editable fields
    updateFieldDisplay('fullName', data.name, data.full_name);
    updateFieldDisplay('email', data.email, data.email);
    updateFieldDisplay('contactNumber', data.contact_number, data.contact_number);
    
    // Update non-editable fields
    updateStaticField('position', data.position);
    updateStaticField('address', data.address);
    updateStaticField('dob', data.dob ? new Date(data.dob).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    }) : 'N/A');
    updateStaticField('place_of_birth', data.place_of_birth);
    updateStaticField('sex', data.sex);
    updateStaticField('civil_status', data.civil_status);
    updateStaticField('citizenship', data.citizenship);
    updateStaticField('blood_type', data.blood_type);
    updateStaticField('created_at', data.created_at ? new Date(data.created_at).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    }) : 'N/A');
    updateStaticField('updated_at', data.updated_at ? new Date(data.updated_at).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    }) : 'N/A');
    
    // Update sidebar name if it exists
    const sidebarNameElement = document.querySelector('.sidebar-employee-name');
    if (sidebarNameElement) {
        sidebarNameElement.textContent = data.name;
    }
}

// Function to update editable field display
function updateFieldDisplay(fieldId, displayValue, inputValue) {
    const displayElement = document.getElementById(`${fieldId}Display`);
    const inputElement = document.getElementById(`${fieldId}Input`);
    
    if (displayElement) {
        displayElement.textContent = displayValue;
        displayElement.setAttribute('data-original', displayValue);
    }
    
    if (inputElement) {
        inputElement.value = inputValue;
    }
}

// Function to update static field display
function updateStaticField(fieldName, value) {
    // Find the field by looking for the label text
    const labels = document.querySelectorAll('label');
    for (let label of labels) {
        if (label.textContent.toLowerCase().includes(fieldName.toLowerCase().replace('_', ' '))) {
            const fieldContainer = label.nextElementSibling;
            if (fieldContainer && fieldContainer.querySelector('p')) {
                const fieldText = fieldContainer.querySelector('p');
                fieldText.textContent = value ? value.charAt(0).toUpperCase() + value.slice(1) : 'N/A';
            }
            break;
        }
    }
}

// Function to toggle save button visibility based on input changes
function toggleSaveButton(fieldId) {
    const inputElement = document.getElementById(`${fieldId}Input`);
    const displayElement = document.getElementById(`${fieldId}Display`);
    const saveBtn = document.getElementById(`${fieldId}SaveBtn`);
    
    if (!inputElement || !displayElement || !saveBtn) return;
    
    const currentValue = inputElement.value.trim();
    
    if (fieldId === 'fullName') {
        // For fullName, compare with the original full name value from PHP
        const fullNameValue = '<?= htmlspecialchars($employee['full_name'] ?? 'N/A') ?>';
        if (currentValue !== fullNameValue) {
            saveBtn.classList.remove('hidden');
        } else {
            saveBtn.classList.add('hidden');
        }
    } else {
        // For other fields, compare with the current display text
        const currentDisplayText = displayElement.textContent;
        if (currentValue !== currentDisplayText) {
            saveBtn.classList.remove('hidden');
        } else {
            saveBtn.classList.add('hidden');
        }
    }
}
</script>
