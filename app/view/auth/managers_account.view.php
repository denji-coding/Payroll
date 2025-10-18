<?php
$title = "Managers";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");
?>
<style>
@keyframes fadeInSlide {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.fade-in-slide {
  animation: fadeInSlide 0.4s ease-out;
}

+/* Ensure close button is visible */
.btn-close {
    background: transparent;
    border: 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.5;
    cursor: pointer;
    padding: 0;
    width: auto;
    height: auto;
}

.btn-close:hover {
    color: #000;
    text-decoration: none;
    opacity: 0.75;
}

.btn-close:focus {
    outline: none;
    box-shadow: none;
}

@keyframes fadeOut {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-8px);
  }
}
.fade-out {
  animation: fadeOut 0.3s ease-in;
}

/* Dropdown styles */
.custom-dropdown-option {
    padding: 8px 12px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.custom-dropdown-option:hover {
    background-color: #f3f4f6;
}

.custom-dropdown-option.selected {
    background-color: #16a249;
    color: white;
}

.custom-dropdown-option.selected:hover {
    background-color: #15803d;
}

/* Add ellipsis for long text in dropdown buttons */
.dropdown-button-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
    text-align: left;
}

/* Default dropdown styling */

/* Ensure close button is visible */
.btn-close {
    background: transparent;
    border: 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.5;
    cursor: pointer;
    padding: 0;
    width: auto;
    height: auto;
}

.btn-close:hover {
    color: #000;
    text-decoration: none;
    opacity: 0.75;
}

.btn-close:focus {
    outline: none;
    box-shadow: none;
}
</style>

<main class="flex-1 h-[calc(100vh-3rem)] p-4 md:p-6 ml-[255px] mt-12 bg-[#f8fbf8]">
    <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                        <span class="text-2xl font-bold tracking-tight text-[#133913]">Managers</span>
                        <p class="text-[#478547]">Manage managers information and access</p>
                </div>
                
                <div>
                    <button id="showAddEmployeeModal" 
                            class="btn btn-success d-inline-flex align-items-center h-10 px-4 py-2 " 
                            data-bs-toggle="modal" 
                            data-bs-target="#addManagerAccountModal">
                    <i class="fas fa-plus me-2"></i>
                    <span class="font-semibold">Add Manager</span>
                    </button>
                </div>
            </div>


        <div class="rounded-lg border-2 border-green-200 bg-white text-[#133913] shadow-sm" 
                    >
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between">
                <span class="text-2xl font-semibold leading-none tracking-tight text-[#133913]">Managers Directory</span>
                <div class="relative w-64">
                    <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="manager_searchInput"
                        class="flex h-10 w-full text-sm placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8  placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
                        placeholder="Search manager..."
                    >
                    <button id="manager_clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden" >×</button>
                </div>                
            </div>

            <div class="p-6 pt-0">
                <div class="relative w-full overflow-auto">
                    <div class="max-h-[calc(100vh-300px)] overflow-y-auto">

                        <table class="w-full caption-bottom text-sm">
                            <thead class="[&_tr]:border-b bg-[#f2f8f2] sticky top-0 z-10">
                                <tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">No.</th>
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Photos</th>
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Fullname</th>
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Employee ID</th>
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">RFID Number</th>
                                    <th class="h-12 px-3 align-middle font-bold text-center text-[#478547] bg-white">Position</th>
                                    <th class="h-12 px-3 align-middle font-bold text-[#478547] bg-white">Actions</th>
                                </tr>
                            </thead>
                                <tbody id="managersTable" class="[&_tr:last-child]:border-0 min-h-[80px]">
                                    <?php if (!empty($managers) && is_array($managers)): ?>
                                        <?php $count = 1; ?>
                                        <?php foreach ($managers as $manager): ?>
                                            <tr class="fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                                <td class="p-3 align-middle font-medium"><?= $count++ ?></td>

                                                <!-- Photo -->
                                                <td class="p-3 align-middle font-medium">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="relative flex shrink-0 overflow-hidden rounded-full h-12 w-12">
                                                            <?php if (!empty($manager['m_photo_path'])): ?>
                                                                <img class="aspect-square h-full w-full object-cover"
                                                                src="../public/<?= htmlspecialchars($manager['m_photo_path']) ?>"
                                                                alt="Manager Photo">
                                                            <?php else: ?>
                                                                <?php
                                                                    $defaultImage = ($manager['m_sex'] === 'Female')    
                                                                        ? '../public/assets/image/default_women.png'
                                                                        : '../public/assets/image/default_men.png';
                                                                ?>
                                                                <img class="aspect-square h-full w-full" src="<?= $defaultImage ?>" alt="Default Photo">
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                </td>

                                                <!-- Fullname -->
                                                <td class="p-3 align-middle font-medium">
                                                    <?= ucwords(strtolower($manager['m_first_name'])) ?>
                                                    <?= !empty($manager['m_middle_name']) ? strtoupper(substr($manager['m_middle_name'], 0, 1)) . '.' : '' ?>
                                                    <?= ucwords(strtolower($manager['m_last_name'])) ?>
                                                </td>

                                                <!-- Employee ID -->
                                                <td class="p-3 align-middle"><?= htmlspecialchars($manager['m_employee_id']) ?></td>

                                                <!-- RFID -->
                                                <td class="p-3 align-middle"><?= htmlspecialchars($manager['m_rfid_number']) ?></td>

                                                <!-- Birthday -->
                                                <!-- <td class="p-3 align-middle">
                                                    <?php 
                                                        if (!empty($manager['m_dob'])) {
                                                            echo date('M d, Y', strtotime($manager['m_dob']));
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                    ?>
                                                </td> -->

                                                <!-- Position -->
                                                <?php
                                                    $position = $manager['m_position'] ?? '';
                                                    switch ($position) {
                                                        case 'Manager':
                                                            $bgColor = 'bg-green-600 text-white';
                                                            break;
                                                        case 'Human Resources':
                                                            $bgColor = 'bg-blue-600 text-white';
                                                            break;
                                                        case 'Staff':
                                                            $bgColor = 'bg-yellow-600 text-white';
                                                            break;
                                                        case 'Driver':
                                                            $bgColor = 'bg-red-600 text-white';
                                                            break;
                                                        default:
                                                            $bgColor = 'bg-gray-500 text-white';
                                                            break;
                                                    }
                                                ?>
                                                <td class="p-3 align-middle text-center">
                                                    <div class="inline-flex items-center rounded-full border border-transparent <?= $bgColor ?> px-2.5 py-0.5 text-xs font-semibold">
                                                        <?= htmlspecialchars($position) ?>
                                                    </div>
                                                </td>

                                                <!-- Actions -->
                                                <td class="p-3 align-middle text-right">
                                                    <div class="flex gap-2">
                                                        <!-- View -->
                                                        <button type="button"
                                                        class="viewManagerBtn inline-flex h-8 w-8 items-center justify-center rounded-md font-medium transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                                                        data-manager='<?= json_encode($manager, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'
                                                        data-manager-id="<?= $manager['id'] ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewManagerModal">
                                                        <i class="bi bi-eye"></i>
                                                        </button>

                                                        <!-- Dropdown -->
                                                        <div class="dropdown relative inline-block">
                                                            <button class="dropdown-toggle-btn inline-flex h-8 w-8 items-center justify-center rounded-md font-medium transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                                                                    type="button" data-bs-toggle="dropdown">
                                                                <i class="bi bi-person-gear text-lg"></i>
                                                            </button>
                                                            <ul class="dropdown-menu absolute right-0 mt-2 w-48 rounded-md shadow-md bg-white ring-1 ring-black ring-opacity-5 z-50">
                                                                <li>
                                                                    <a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal('viewAttendanceModal', <?= $manager['id'] ?>)">
                                                                        <i class="bi bi-calendar-check h-4 w-4"></i>
                                                                        <span>View Attendance</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal('viewSlipsModal', <?= $manager['id'] ?>)">
                                                                        <i class="bi bi-receipt h-4 w-4"></i>
                                                                        <span>View Slips</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="p-4 text-center italic text-gray-500 bg-[#f0fdf4]">
                                                <i class="bi bi-person-x me-2"></i> No managers found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add Manager Modal -->
<div class="modal fade" id="addManagerAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <i class="bi bi-person-plus text-[#16a249] fs-4 mr-2"></i>
                <h1 class="modal-title fs-5 text-[#16a249]" id="addManagerModalLabel">Add Manager</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid p-2">
                    <form method="post" id="addManagerForm" action="../app/api/managers_account-api.php"
                        enctype="multipart/form-data">
                        <div class="border rounded-lg mb-4">
                            <div class="bg-yellow-100 px-4 py-2 rounded-t-lg border-b border-b-gray-200">
                                <span class="font-semibold text-[#133913]">PERSONAL INFORMATION</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                <div class="flex flex-col items-center md:col-span-1">
                                    <div
                                        class="relative w-32 h-32 mb-2 mt-1 flex items-center justify-center bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-[#16a249] transition-all duration-200">
                                        <input type="file" id="managerPhoto" name="photo_path" accept="image/*"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            onchange="previewManagerPhoto(event); displayFileName(this);">
                                        <img id="managersPhotoPreview" alt="Manager Photo"
                                            class="w-full h-full object-cover rounded-lg absolute top-0 left-0 z-0"
                                            style="display:none;">
                                        <span id="photoPlaceholder" class="flex flex-col items-center justify-center text-gray-400 z-0">
                                            <i class="bi bi-image text-3xl mb-1"></i>
                                            <span class="text-xs">Upload Photo</span>
                                        </span>
                                    </div>
                                    <span id="photoFileName" class="text-sm text-gray-600 mt-1 text-center break-all max-w-[128px] overflow-hidden whitespace-nowrap text-ellipsis"></span>
                                    <div class="validation-message text-red-500 text-xs mt-1"></div>
                                </div>
                                
                                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                    <div class="md:col-span-3 w-full">
                                        <div class="grid grid-cols-1 md:grid-cols-1 gap-x-6 gap-y-4">                                        
                                            <div class="flex flex-col gap-1 relative w-full">
                                            <label class="block text-xs font-medium mb-1 ml-2">EMPLOYEE ID</label>
                                            <div class="relative flex items-center">
                                                <div class="relative flex items-center w-full">
                                                    <input
                                                        type="text"
                                                        id="employeeId"
                                                        name="employeeId"                                                        
                                                        placeholder="Click Generate ID"
                                                        class="p-2 pl-8 border rounded text-sm w-full focus:outline-none bg-gray-100"
                                                        required
                                                        readonly
                                                    >
                                                    <i class="validation-icon absolute right-24 top-1/2 transform -translate-y-1/2"></i>
                                                        <button
                                                            type="button"
                                                            id="generateIdBtn"
                                                            
                                                            class=" ml-2 h-[38px] min-w-[82.5px] btn btn-success flex items-center justify-center"
                                                        >
                                                            <span class="text-[13px] inline-block whitespace-nowrap font-semibold">Generate ID</span>
                                                        </button>
                                                </div>
                                                </div>
                                                <div class="validation-message text-red-500 text-xs mt-1"></div>
                                            </div>                                                                            
                                        </div>
                                    </div>
                                                                            
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BRANCH <span class="text-red-500">*</span></label>
                                        <div class="relative" id="branch-dropdown-container">
                                            <input type="hidden" name="branchManager" id="branchManager" required />
                                            <button type="button" id="branchDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="branchDropdownSelected" class="dropdown-button-text">Select a branch</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="branchDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-sm mt-1 max-h-60 overflow-y-auto hidden">
                                                <!-- Options will be loaded dynamically from branches API -->
                                            </div>
                                            <style>
                                                /* Styles for dynamic branch dropdown options */
                                                #branchDropdownList .custom-dropdown-option { padding: 0.5rem 0.75rem; cursor: pointer; transition: background-color 0.2s;}
                                                #branchDropdownList .custom-dropdown-option:hover { background-color: #f3f4f6; }
                                                #branchDropdownList .custom-dropdown-option.active { background-color: #16a249; color: white; font-weight: 600; }
                                                #branchDropdownList .custom-dropdown-option.selected { background-color: #16a249; color: white;}
                                                #branchDropdownList .custom-dropdown-option.selected:hover { background-color: #15803d;}

                                            </style>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>   
                                    
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">POSITION <span class="text-red-500">*</span></label>
                                        <div class="relative" id="position-dropdown-container">
                                            <input type="hidden" name="position" id="position" required />
                                            <button type="button" id="positionDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="positionDropdownSelected" class="dropdown-button-text">Select a position</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="positionDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                                <div class="custom-dropdown-option" data-value="Manager">Manager</div>
                                                <!-- <div class="custom-dropdown-option" data-value="Human Resources">Human Resources</div>
                                                <div class="custom-dropdown-option" data-value="Staff">Staff</div>
                                                <div class="custom-dropdown-option" data-value="Driver">Driver</div> -->
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative w-full">
                                            <label class="block text-xs font-medium mb-1 ml-2">RFID NUMBER</label>
                                            <div class="relative">
                                                <input
                                                type="text"
                                                name="rfidNumber"
                                                placeholder="e.g., 0983222913"
                                                class="p-2 pl-8 border rounded text-sm w-full focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]"
                                                oninput="validateRfidNumber(this)"
                                                required
                                                >
                                                <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                            <div class="validation-message text-red-500 text-xs mt-1"></div>
                                            </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">FIRST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="firstName" placeholder="e.g., Juan"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">MIDDLE NAME</label>
                                        <div class="relative">
                                            <input type="text" name="middleName" placeholder="e.g., Santos"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">LAST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="lastName" placeholder="e.g., Dela Cruz"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    
                                </div>
                                <div class="md:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BIRTHDAY</label>
                                        <div class="relative">
                                            <input type="date" name="dob" id="dob" class="p-2 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full" required>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PLACE OF BIRTH</label>
                                        <div class="relative">
                                            <input type="text" name="placeOfBirth" placeholder="e.g., Davao City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SEX</label>
                                        <div class="relative" id="sex-dropdown-container">
                                            <input type="hidden" name="sex" id="sex" required />
                                            <button type="button" id="sexDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="sexDropdownSelected" class="dropdown-button-text">Select</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="sexDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                                <div class="custom-dropdown-option" data-value="Male">Male</div>
                                                <div class="custom-dropdown-option" data-value="Female">Female</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CIVIL STATUS</label>
                                        <div class="relative" id="civilStatus-dropdown-container">
                                            <input type="hidden" name="civilStatus" id="civilStatus" required />
                                            <button type="button" id="civilStatusDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="civilStatusDropdownSelected" class="dropdown-button-text">Select</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="civilStatusDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                                <div class="custom-dropdown-option" data-value="Single">Single</div>
                                                <div class="custom-dropdown-option" data-value="Married">Married</div>
                                                <div class="custom-dropdown-option" data-value="Separated">Separated</div>
                                                <div class="custom-dropdown-option" data-value="Divorced">Divorced</div>
                                                <div class="custom-dropdown-option" data-value="Widowed">Widowed</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CONTACT NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="contactNumber" placeholder="e.g., 09171234567"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                maxlength="11" oninput="validateContactNumber(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">EMAIL</label>
                                        <div class="relative">
                                            <input type="email" name="email" placeholder="e.g., john.doe@example.com"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CITIZENSHIP</label>
                                        <div class="relative" id="citizenship-dropdown-container">
                                            <input type="hidden" name="citizenship" id="citizenship" />
                                            <button type="button" id="citizenshipDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="citizenshipDropdownSelected" class="dropdown-button-text">Select citizenship</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="citizenshipDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden"></div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BLOOD TYPE</label>
                                        <div class="relative" id="bloodType-dropdown-container">
                                            <input type="hidden" name="bloodType" id="bloodType" />
                                            <button type="button" id="bloodTypeDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="bloodTypeDropdownSelected" class="dropdown-button-text">Select blood type</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="bloodTypeDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                                <div class="custom-dropdown-option" data-value="A+">A+</div>
                                                <div class="custom-dropdown-option" data-value="A-">A-</div>
                                                <div class="custom-dropdown-option" data-value="B+">B+</div>
                                                <div class="custom-dropdown-option" data-value="B-">B-</div>
                                                <div class="custom-dropdown-option" data-value="O+">O+</div>
                                                <div class="custom-dropdown-option" data-value="O-">O-</div>
                                                <div class="custom-dropdown-option" data-value="AB+">AB+</div>
                                                <div class="custom-dropdown-option" data-value="AB-">AB-</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    
                                    <div class="col-span-3 flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">COMPLETE ADDRESS</label>
                                        <div class="relative">
                                            <input type="text" name="address" placeholder="e.g., 1234 Mabini St., Barangay Malinis, Quezon City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>
                            </div>
                    
                            <div class="border-t border-t-gray-200">
                                <div class="bg-yellow-100 px-4 py-2 border-b border-b-gray-200">
                                    <span class="font-semibold text-[#133913]">SALARY AND ACCOUNTS INFORMATION</span>
                                </div>
                                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BASE SALARY</label>
                                        <div class="relative">
                                            <input type="text" name="baseSalary" placeholder="e.g., 600"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SSS NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="sssNumber" maxlength="12" placeholder="e.g., 012345678912"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PAG-IBIG NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="pagibigNumber" maxlength="12" placeholder="e.g., 012345678901"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PHILHEALTH NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="philhealthNumber" maxlength="12"
                                                placeholder="e.g., 123456789012"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="modal-footer" style="border-top: none;">
                            <button id="managersaveBtn" type="button"
                                class="px-6 py-2 btn btn-success transition-colors duration-200 font-semibold">
                                Add Manager
                            </button>
                            <button type="button"
                                class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-200 font-semibold"
                                data-bs-dismiss="modal">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Manager Modal -->
<div class="modal fade" id="updateManagerAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="updateManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <i class="bi bi-pencil-square text-[#16a249] fs-4 mr-2"></i>
                <h1 class="modal-title fs-5 text-[#16a249]" id="updateManagerModalLabel">Update Manager</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid p-2">
                


                    <form method="post" id="updateManagerForm" onsubmit="return updateManager(event)" action="../app/api/managers_account-api.php"
                        enctype="multipart/form-data">
                        <input type="hidden" name="id" id="m_edit_managerId" value="<?= htmlspecialchars($manager['id']) ?>">
                        <input type="hidden" name="existingPhoto" id="m_existing_photo" value="">
                        <!-- <input type="hidden" name="id" value="<?= htmlspecialchars($manager['id']) ?>"> -->



                        
                        <div class="border rounded-lg mb-4">
                            <div class="bg-yellow-100 px-4 py-2 rounded-t-lg border-b border-b-gray-200">
                                <span class="font-semibold text-[#133913]">PERSONAL INFORMATION</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                <div class="flex flex-col items-center md:col-span-1">
                                    <div
                                        class="relative w-32 h-32 mb-2 mt-1 flex items-center justify-center bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-[#16a249] transition-all duration-200">
                                        <input type="file" id="managerEditPhoto" name="photo_path" accept="image/*"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            onchange="editpreviewManagerPhoto(event); editdisplayFileName(this);">
                                        <img id="edit_managerPhotoPreview" alt="Manager Photo"
                                            class="w-full h-full object-cover rounded-lg absolute top-0 left-0 z-0"
                                            style="display:none;">
                                        <span id="edit_photoPlaceholder" class="flex flex-col items-center justify-center text-gray-400 z-0">
                                            <i class="bi bi-image text-3xl mb-1"></i>
                                            <span class="text-xs">Upload Photo</span>
                                        </span>
                                    </div>
                                    <span id="edit_photoFileName" class="text-sm text-gray-600 mt-1 text-center break-all max-w-[128px] overflow-hidden whitespace-nowrap text-ellipsis"></span>
                                    <div class="validation-message text-red-500 text-xs mt-1"></div>
                                </div>
                                
                                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                    <div class="md:col-span-3 w-full">
                                        <div class="grid grid-cols-1 md:grid-cols-1 gap-x-6 gap-y-4">                                        
                                            <div class="flex flex-col gap-1 relative w-full">
                                            <label class="block text-xs font-medium mb-1 ml-2">EMPLOYEE ID</label>
                                        <div class="relative">
                                            <input type="text" name="employeeId" id="m_edit_manager_id" placeholder="e.g., EMP-001" readonly 
                                                class="p-2 pl-8 border rounded bg-gray-100 text-sm pointer-events-none cursor-default focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                            </div>                                                                            
                                        </div>
                                    </div>                                                                                                      
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BRANCH <span class="text-red-500">*</span></label>
                                        <div class="relative" id="edit-branch-dropdown-container">
                                            <input type="hidden" name="branchManager" id="m_edit_branchManager" required />
                                            <button type="button" id="editBranchDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editBranchDropdownSelected" class="dropdown-button-text">Select a branch</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editBranchDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-sm mt-1 max-h-60 overflow-y-auto hidden">
                                                <!-- Options will be loaded dynamically from branches API -->
                                            </div>
                                            <style>
                                                /* Match styles with add branch dropdown */
                                                #editBranchDropdownList .custom-dropdown-option { padding: 0.5rem 0.75rem; cursor: pointer; transition: background-color 0.2s; }
                                                #editBranchDropdownList .custom-dropdown-option:hover { background-color: #f3f4f6; }
                                                #editBranchDropdownList .custom-dropdown-option.active { background-color: #16a249; color: white; font-weight: 600; }
                                                #editBranchDropdownList .custom-dropdown-option.selected { background-color: #16a249; color: white; }
                                                #editBranchDropdownList .custom-dropdown-option.selected:hover { background-color: #15803d; }
                                            </style>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <script>
                                    (function(){
                                        var editListEl = document.getElementById('editBranchDropdownList');
                                        var editToggleBtn = document.getElementById('editBranchDropdownBtn');
                                        var editContainer = document.getElementById('edit-branch-dropdown-container');
                                        var editSelectedSpan = document.getElementById('editBranchDropdownSelected');
                                        var editHiddenInput = document.getElementById('m_edit_branchManager');

                                        function computeBranchesApiUrl(){
                                            try {
                                                var scriptName = '<?= addslashes($_SERVER['SCRIPT_NAME'] ?? '') ?>';
                                                var baseDir = scriptName.substring(0, scriptName.lastIndexOf('/'));
                                                if (/\/public$/.test(baseDir)) { baseDir = baseDir.replace(/\/public$/, ''); }
                                                return (window.location.origin || '') + baseDir + '/app/api/branches-api.php';
                                            } catch (_) {
                                                return (window.location.origin || '') + '/app/api/branches-api.php';
                                            }
                                        }
                                        var editApiUrl = computeBranchesApiUrl();

                                        function sanitize(text){ return (text || '').replace(/</g, '&lt;'); }

    function renderEditBranches(rows){
                                            if (!editListEl) return;
                                            editListEl.innerHTML = '';
                                            if (!rows || rows.length === 0) {
                                                var empty = document.createElement('div');
                                                empty.className = 'px-3 py-2 text-gray-500 text-sm';
                                                empty.textContent = 'No branches found';
                                                editListEl.appendChild(empty);
                                                return;
                                            }
        var currentValue = editHiddenInput ? (editHiddenInput.value || '') : '';
                                            rows.forEach(function(row){
                                                var label = sanitize(row.name) + (row.address ? ' - ' + sanitize(row.address) : '');
                                                var div = document.createElement('div');
                                                div.className = 'custom-dropdown-option';
            // Store branch id as value and show label as text
            div.setAttribute('data-value', String(row.id || ''));
            div.setAttribute('data-label', label);
            div.textContent = label;
            if (currentValue && currentValue === label) { div.classList.add('active'); }
                                                editListEl.appendChild(div);
                                            });
        // Ensure selected label reflects current hidden value
        try {
            var currId = editHiddenInput ? (editHiddenInput.value || '') : '';
            if (currId && editSelectedSpan) {
                var selectedOpt = Array.from(editListEl.querySelectorAll('.custom-dropdown-option')).find(function(el){
                  return (el.getAttribute('data-label') || el.textContent) === currId;
                });
                if (selectedOpt) { editSelectedSpan.textContent = selectedOpt.getAttribute('data-label') || selectedOpt.textContent || 'Select a branch'; }
            }
        } catch(_) {}
                                        }

    function fetchEditBranches(){
                                            try {
                                                fetch(editApiUrl, { credentials: 'same-origin' })
                                                  .then(function(r){ return r.json(); })
                                                  .then(function(res){
                                                      if (res && res.status === 'success' && Array.isArray(res.data)) {
                      renderEditBranches(res.data);
                      // After render, sync selection if we already have an ID
                      try {
                          var currId = editHiddenInput ? (editHiddenInput.value || '') : '';
                          if (currId && editSelectedSpan) {
                              var selectedOpt = editListEl.querySelector('[data-value="' + currId + '"]');
                              if (selectedOpt) {
                                  editSelectedSpan.textContent = selectedOpt.getAttribute('data-label') || selectedOpt.textContent || 'Select a branch';
                              }
                          }
                      } catch(_) {}
                                                      } else {
                                                          if (window.Swal) { Swal.fire({ icon: 'error', title: 'Failed to load branches', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' }); }
                                                      }
                                                  })
                                                  .catch(function(){
                                                      if (window.Swal) { Swal.fire({ icon: 'error', title: 'Failed to load branches', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' }); }
                                                  });
                                            } catch(_) {}
                                        }

                                        function openEditBranchDropdown(){
                                            if (!editListEl) return;
                                            editListEl.classList.remove('hidden');
                                            editListEl.style.display = 'block';
                                            try { editListEl.style.minWidth = editToggleBtn.getBoundingClientRect().width + 'px'; } catch(_) {}
                                            editListEl.dataset.open = '1';
                                            if (!editListEl.querySelector('.custom-dropdown-option')) {
                                                editListEl.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">Loading...</div>';
                                                fetchEditBranches();
                                            }
        try {
            var curr = editHiddenInput ? (editHiddenInput.value || '') : '';
            editListEl.querySelectorAll('.custom-dropdown-option').forEach(function(el){
                var lab = el.getAttribute('data-label') || el.textContent || '';
                el.classList.toggle('active', curr && curr === lab);
            });
        } catch(_) {}
                                        }

                                        function closeEditBranchDropdown(){
                                            if (!editListEl) return;
                                            editListEl.classList.add('hidden');
                                            editListEl.style.display = 'none';
                                            editListEl.dataset.open = '0';
                                        }

                                        // Option click: set value, mark active, close
                                        if (editContainer) {
                                            editContainer.addEventListener('click', function(ev){
                                                var opt = ev.target.closest('.custom-dropdown-option');
                                                if (!opt) return;
            var value = opt.getAttribute('data-value') || '';
            var label = opt.getAttribute('data-label') || opt.textContent || '';
            if (editHiddenInput) editHiddenInput.value = label; // store label (name - address)
            if (editSelectedSpan) editSelectedSpan.textContent = label || 'Select a branch';
                                                try {
                                                    editListEl.querySelectorAll('.custom-dropdown-option').forEach(function(el){ el.classList.remove('active'); });
                                                    opt.classList.add('active');
                                                } catch(_) {}
                                                closeEditBranchDropdown();
                                            });
                                        }

                                        // Toggle open/close on same button
                                        if (editToggleBtn && editListEl) {
                                            editToggleBtn.addEventListener('click', function(e){
                                                e.preventDefault();
                                                e.stopPropagation();
                                                var isOpen = editListEl.dataset.open === '1';
                                                if (isOpen) {
                                                    closeEditBranchDropdown();
                                                } else {
                                                    openEditBranchDropdown();
                                                }
                                            });
                                            document.addEventListener('click', function(ev){
                                                if (!editContainer.contains(ev.target)) {
                                                    closeEditBranchDropdown();
                                                }
                                            });
                                        }
                                        // Ensure branches load on first page load
                                        try {
                                            if (document.readyState === 'loading') {
                                                document.addEventListener('DOMContentLoaded', fetchEditBranches);
                                            } else {
                                                fetchEditBranches();
                                            }
                                        } catch(_) {}
                                    })();
                                    </script>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">POSITION <span class="text-red-500">*</span></label>
                                        <div class="relative" id="edit-position-dropdown-container">
                                            <input type="hidden" name="position" id="m_edit_position" required />
                                            <button type="button" id="editPositionDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editPositionDropdownSelected" class="dropdown-button-text">Select a position</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editPositionDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                                <div class="custom-dropdown-option" data-value="Manager">Manager</div>
                                                <!-- <div class="custom-dropdown-option" data-value="Human Resources">Human Resources</div>
                                                <div class="custom-dropdown-option" data-value="Staff">Staff</div>
                                                <div class="custom-dropdown-option" data-value="Driver">Driver</div> -->
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 md:col-span-23 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">RFID NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="rfidNumber" id="m_edit_rfidNumber" placeholder="e.g., 0083222913"
                                                class="p-2 pl-8 border rounded  text-sm w-full focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]"
                                                oninput="validateRfidNumber(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">FIRST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="firstName" id="m_edit_first_name" placeholder="e.g., Juan"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">MIDDLE NAME</label>
                                        <div class="relative">
                                            <input type="text" name="middleName" id="m_edit_middle_name" placeholder="e.g., Santos"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">LAST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="lastName" id="m_edit_last_name" placeholder="e.g., Dela Cruz"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>

                                
                                <div class="md:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BIRTHDAY</label>
                                        <div class="relative">
                                            <input type="date" name="dob" id="m_edit_dob" class="p-2 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full" required>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PLACE OF BIRTH</label>
                                        <div class="relative">
                                            <input type="text" name="placeOfBirth" id="m_edit_placeOfBirth" placeholder="e.g., Davao City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SEX</label>
                                        <div class="relative" id="edit-sex-dropdown-container">
                                            <input type="hidden" name="sex" id="m_edit_sex" required />
                                            <button type="button" id="editSexDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editSexDropdownSelected" class="dropdown-button-text">Select</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editSexDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                                <div class="custom-dropdown-option" data-value="Male">Male</div>
                                                <div class="custom-dropdown-option" data-value="Female">Female</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CIVIL STATUS</label>
                                        <div class="relative" id="edit-civilStatus-dropdown-container">
                                            <input type="hidden" name="civilStatus" id="m_edit_civilStatus" required />
                                            <button type="button" id="editCivilStatusDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editCivilStatusDropdownSelected" class="dropdown-button-text">Select</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editCivilStatusDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                                <div class="custom-dropdown-option" data-value="Single">Single</div>
                                                <div class="custom-dropdown-option" data-value="Married">Married</div>
                                                <div class="custom-dropdown-option" data-value="Separated">Separated</div>
                                                <div class="custom-dropdown-option" data-value="Divorced">Divorced</div>
                                                <div class="custom-dropdown-option" data-value="Widowed">Widowed</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CONTACT NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="contactNumber" id="m_edit_contactNumber" placeholder="e.g., 09171234567"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                maxlength="11" oninput="validateContactNumber(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">EMAIL</label>
                                        <div class="relative">
                                            <input type="email" name="email" id="m_edit_email" placeholder="e.g., john.doe@example.com"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CITIZENSHIP</label>
                                        <div class="relative" id="edit-citizenship-dropdown-container">
                                            <input type="hidden" name="citizenship" id="m_edit_citizenship" />
                                            <button type="button" id="editCitizenshipDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editCitizenshipDropdownSelected" class="dropdown-button-text">Select citizenship</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editCitizenshipDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden"></div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BLOOD TYPE</label>
                                        <div class="relative" id="edit-bloodType-dropdown-container">
                                            <input type="hidden" name="bloodType" id="m_edit_bloodType" />
                                            <button type="button" id="editBloodTypeDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editBloodTypeDropdownSelected" class="dropdown-button-text">Select blood type</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editBloodTypeDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                                <div class="custom-dropdown-option" data-value="A+">A+</div>
                                                <div class="custom-dropdown-option" data-value="A-">A-</div>
                                                <div class="custom-dropdown-option" data-value="B+">B+</div>
                                                <div class="custom-dropdown-option" data-value="B-">B-</div>
                                                <div class="custom-dropdown-option" data-value="O+">O+</div>
                                                <div class="custom-dropdown-option" data-value="O-">O-</div>
                                                <div class="custom-dropdown-option" data-value="AB+">AB+</div>
                                                <div class="custom-dropdown-option" data-value="AB-">AB-</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    
                                    <div class="col-span-3 flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">COMPLETE ADDRESS</label>
                                        <div class="relative">
                                            <input type="text" name="address" id="m_edit_address" placeholder="e.g., 1234 Mabini St., Barangay Malinis, Quezon City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>
                            </div>
                    
                            <div class="border-t border-t-gray-200">
                                <div class="bg-yellow-100 px-4 py-2 border-b border-b-gray-200">
                                    <span class="font-semibold text-[#133913]">SALARY AND ACCOUNTS INFORMATION</span>
                                </div>
                                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BASE SALARY</label>
                                        <div class="relative">
                                            <input type="text" name="baseSalary" id="m_edit_baseSalary" placeholder="e.g., 600"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SSS NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="sssNumber" id="m_edit_sssNumber" maxlength="12" placeholder="e.g., 012345678912"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PAG-IBIG NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="pagibigNumber" id="m_edit_pagibigNumber" maxlength="12" placeholder="e.g., 012345678901"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PHILHEALTH NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="philhealthNumber" id="m_edit_philhealthNumber" maxlength="12"
                                                placeholder="e.g., 123456789012"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)"
                                                required
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="modal-footer" style="border-top: none;">
                                <button type="submit"  
                                    class="px-6 py-2 btn btn-success transition-colors duration-200 font-semibold">
                                    Update Manager
                                </button>
                                <button type="button"
                                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-200 font-semibold"
                                    data-bs-toggle ="modal" data-bs-target="#viewEmployeeModal">Cancel</button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Manager Modal -->
<div class="modal fade" id="viewManagerModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewManagerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title text-success text-lg fw-semibold">
          <i class="bi bi-info-circle me-2"></i>Manager Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal">
        <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <p class="text-success small mb-4">View complete manager information</p>

        <!-- 🔒 Hidden Inputs for future actions like Edit/Delete -->
        <input type="hidden" id="manager_id" name="id">
        <input type="hidden" id="view_manager_employee_id" name="employee_id">
        <input type="hidden" id="view_manager_photo" name="photo">

        <div class="d-flex flex-column flex-md-row gap-4">
          <!-- Manager Photo -->
          <div class="ml-4 mr-3">
            <div class="rounded-circle border-2 border-green-800 mt-[25px]" style="width: 125px; height: 125px; overflow: hidden;">
              <img id="view_managerPhoto" src="assets/image/default_user_image.svg" alt="Manager Photo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
          </div>

          <!-- Manager Info -->
          <div class="flex-grow-1">
            <h4 id="managerName" class="fw-bold mb-3 text-lg text-[#133913]">Loading...</h4>

            <div class="row">
              <!-- Column 1 -->
              <div class="col-md-6 mb-2">
                <p><strong class="text-success text-sm">Manager ID:</strong> <span class="text-sm" id="managerIdView"></span></p>
                <p><strong class="text-success text-sm">Blood Type:</strong> <span class="text-sm" id="managerBloodType"></span></p>
                <p><strong class="text-success text-sm">Civil Status:</strong> <span class="text-sm" id="managerCivilStatus"></span></p>
                <p><strong class="text-success text-sm">Birthday:</strong> <span class="text-sm" id="managerBirthday"></span></p>
                <p><strong class="text-success text-sm">Sex:</strong> <span class="text-sm" id="managerSex"></span></p>
                <p><strong class="text-success text-sm">Citizenship:</strong> <span class="text-sm" id="managerCitizen"></span></p>
              </div>

              <!-- Column 2 -->
              <div class="col-md-6 mb-2">
                <p><strong class="text-success text-sm">RFID Number:</strong> <span class="text-sm" id="managerRFID"></span></p>
                <p><strong class="text-success text-sm">Position:</strong> <span class="text-sm" id="managerPosition"></span></p>
                <p><strong class="text-success text-sm">Email:</strong> <span class="text-sm" id="managerEmail"></span></p>
                <p><strong class="text-success text-sm">Phone:</strong> <span class="text-sm" id="managerPhone"></span></p>
                <p><strong class="text-success text-sm">Place of Birth:</strong> <span class="text-sm" id="managerPlaceOfBirth"></span></p>
                <p><strong class="text-success text-sm">Branch:</strong> <span class="text-sm" id="managerBranch"></span></p>
              </div>

              <!-- Column 3 -->
              <div class="col-md-6 mb-2">
                <div class="row mt-3">
                  <div class="col-12">
                    <h6 class="text-success text-sm"><strong>Salary Information</strong></h6>
                    <p><strong class="text-success text-sm">Base Salary:</strong> &#8369;<span class="text-sm" id="managerSalary"></span></p>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-12">
                    <h6 class="text-success text-sm"><strong>Accounts Information</strong></h6>
                    <p><strong class="text-success text-sm">SSS:</strong> <span class="text-sm" id="managerSSS"></span></p>
                    <p><strong class="text-success text-sm">Pag-IBIG:</strong> <span class="text-sm" id="managerPagibig"></span></p>
                    <p><strong class="text-success text-sm">Philhealth:</strong> <span class="text-sm" id="managerPhilhealth"></span></p>
                  </div>
                </div>
              </div>

              <!-- Column 4 -->
              <div class="col-md-6 mb-2">
                <div class="row mt-3">
                  <div class="col-12">
                    <h6 class="text-success text-sm"><strong>Address</strong></h6>
                    <p id="managerAddress" class="text-sm"></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-end mt-3 mb-2 mr-2">
           <button
                type="button"
                class="btn btn-outline-success me-2 w-[90px] editManagerBtn"
                data-bs-toggle="modal"
                data-bs-target="#updateManagerAccountModal"
                onclick='editManagerFromObject(window.lastViewedManager)'
                >
                <i class="bi bi-pencil me-2"></i>Edit
           </button>

            <button type="button" id="modalDeleteManagerBtn" class="btn btn-danger deleteManagerBtn" data-id="">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
        </div>
      </div>
    </div>
  </div>
</div>



<?php require_once views_path("partials/footer"); ?>




<script>
function updateManager(event) {
  event.preventDefault();
  const form = document.getElementById('updateManagerForm');
  const formData = new FormData(form);
  formData.append('_method', 'PUT');

  fetch('../app/api/managers_account-api.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        // Close the update modal first
        const updateModal = document.getElementById("updateManagerAccountModal");
        if (updateModal) {
          try {
            const modal = bootstrap.Modal.getInstance(updateModal);
            if (modal) {
              modal.hide();
            }
          } catch (error) {
            // Fallback: direct DOM manipulation
            updateModal.classList.remove('show');
            updateModal.style.display = 'none';
            document.body.classList.remove('modal-open');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
          }
        }

        Swal.fire({
          icon: 'success',
          title: 'Manager Updated!',
          text: data.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          refreshManagersTable();
        });
      } else {
        Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
      }
    })
    .catch(err => {
      console.error('❌ Error during fetch:', err);
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Something went wrong while updating the manager.'
      });
    });
  return false;
}

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("addManagerForm");
  const saveBtn = document.getElementById("managersaveBtn");

  const capitalize = str =>
    str ? str.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()) : '';

  const formatSSS = sss =>
    sss && sss.length === 12 ? `${sss.slice(0, 2)}-${sss.slice(2, 9)}-${sss.slice(9)}` : sss || 'N/A';

  const formatPagibig = pagibig =>
    pagibig && pagibig.length === 12 ? `${pagibig.slice(0, 4)}-${pagibig.slice(4, 8)}-${pagibig.slice(8)}` : pagibig || 'N/A';

  const formatPhilhealth = ph =>
    ph && ph.length === 12 ? `${ph.slice(0, 2)}-${ph.slice(2, 10)}-${ph.slice(10)}` : ph || 'N/A';

  function populateEditModal(manager) {
    
    if (!manager || Object.keys(manager).length === 0) return;

    // Reset the edit modal first
    resetEditManagerForm();

    const setValue = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.value = value ?? '';
    };

    const fields = {
      id: "m_edit_managerId",
      m_employee_id: "m_edit_manager_id",
      m_position: "m_edit_position",
      m_rfid_number: "m_edit_rfidNumber",
      m_first_name: "m_edit_first_name",
      m_middle_name: "m_edit_middle_name",
      m_last_name: "m_edit_last_name",
      m_dob: "m_edit_dob",
      m_place_of_birth: "m_edit_placeOfBirth",
      m_sex: "m_edit_sex",
      m_civil_status: "m_edit_civilStatus",
      m_contact_number: "m_edit_contactNumber",
      m_email: "m_edit_email",
      m_citizenship: "m_edit_citizenship",
      m_blood_type: "m_edit_bloodType",
      m_address: "m_edit_address",
      m_base_salary: "m_edit_baseSalary",
      m_sss_number: "m_edit_sssNumber",
      m_pagibig_number: "m_edit_pagibigNumber",
      m_philhealth_number: "m_edit_philhealthNumber"
    };

    for (const [key, fieldId] of Object.entries(fields)) {
      setValue(fieldId, manager[key]);
    }

    // Set the date of birth input value
    const editDobInput = document.getElementById("m_edit_dob");
    if (editDobInput) {
        editDobInput.value = manager.m_dob || '';
    }

    // Branch select - update dropdown display
    const branchSpan = document.getElementById("editBranchDropdownSelected");
    const hiddenBranchInput = document.getElementById("m_edit_branchManager");
    let branchId = manager.branch_id || manager.m_branch_id || '';
    if (hiddenBranchInput) hiddenBranchInput.value = branchId;
    if (branchSpan) {
      if (manager.branch_name) {
        branchSpan.textContent = manager.branch_name + (manager.branch_address ? (' - ' + manager.branch_address) : '');
      } else {
        // Try to resolve from current dropdown options
      const branchList = document.getElementById("editBranchDropdownList");
      if (branchList) {
          if (branchId) {
            const optionById = branchList.querySelector(`[data-value="${branchId}"]`);
            if (optionById) branchSpan.textContent = optionById.getAttribute('data-label') || optionById.textContent;
          } else if (manager.m_branch) {
            // Fallback: match by label text if only label is available
            const desiredLabel = manager.m_branch;
            const match = Array.from(branchList.querySelectorAll('.custom-dropdown-option')).find(function(el){
              return (el.getAttribute('data-label') || el.textContent) === desiredLabel;
            });
            if (match) {
              const id = match.getAttribute('data-value') || '';
              branchSpan.textContent = match.getAttribute('data-label') || match.textContent;
              if (hiddenBranchInput) hiddenBranchInput.value = id;
              branchId = id;
            } else {
              branchSpan.textContent = 'Select a branch';
            }
          }
        }
      }
    }

    // Position select - update dropdown display
    const positionSpan = document.getElementById("editPositionDropdownSelected");
    const positionValue = manager.m_position || '';
    if (positionSpan && positionValue) {
      // Find the option text for this value
      const positionList = document.getElementById("editPositionDropdownList");
      if (positionList) {
        const option = positionList.querySelector(`[data-value="${positionValue}"]`);
        if (option) {
          positionSpan.textContent = option.textContent;
          // Also update the hidden input
          const hiddenInput = document.getElementById("m_edit_position");
          if (hiddenInput) hiddenInput.value = positionValue;
        }
      }
    }

    // Citizenship select - update dropdown display
    const citizenshipSpan = document.getElementById("editCitizenshipDropdownSelected");
    const citizenshipValue = manager.m_citizenship || '';
    if (citizenshipSpan && citizenshipValue) {
      citizenshipSpan.textContent = citizenshipValue;
      // Also update the hidden input
      const hiddenInput = document.getElementById("m_edit_citizenship");
      if (hiddenInput) hiddenInput.value = citizenshipValue;
    }

    // Blood type select - update dropdown display
    const bloodTypeSpan = document.getElementById("editBloodTypeDropdownSelected");
    const bloodTypeValue = manager.m_blood_type || '';
    if (bloodTypeSpan && bloodTypeValue) {
      // Find the option text for this value
      const bloodTypeList = document.getElementById("editBloodTypeDropdownList");
      if (bloodTypeList) {
        const option = bloodTypeList.querySelector(`[data-value="${bloodTypeValue}"]`);
        if (option) {
          bloodTypeSpan.textContent = option.textContent;
          // Also update the hidden input
          const hiddenInput = document.getElementById("m_edit_bloodType");
          if (hiddenInput) hiddenInput.value = bloodTypeValue;
        }
      }
    }

    // Sex select - update dropdown display
    const sexSpan = document.getElementById("editSexDropdownSelected");
    const sexValue = manager.m_sex || '';
    if (sexSpan && sexValue) {
      const sexList = document.getElementById("editSexDropdownList");
      if (sexList) {
        const option = sexList.querySelector(`[data-value="${sexValue}"]`);
        if (option) {
          sexSpan.textContent = option.textContent;
          const hiddenInput = document.getElementById("m_edit_sex");
          if (hiddenInput) hiddenInput.value = sexValue;
        }
      }
    }

    // Civil status select - update dropdown display
    const csSpan = document.getElementById("editCivilStatusDropdownSelected");
    const csValue = manager.m_civil_status || '';
    if (csSpan && csValue) {
      const csList = document.getElementById("editCivilStatusDropdownList");
      if (csList) {
        const option = csList.querySelector(`[data-value="${csValue}"]`);
        if (option) {
          csSpan.textContent = option.textContent;
          const hiddenInput = document.getElementById("m_edit_civilStatus");
          if (hiddenInput) hiddenInput.value = csValue;
        }
      }
    }

    // Photo preview
    const photoPreview = document.getElementById("edit_managerPhotoPreview");
    const placeholder = document.getElementById("edit_photoPlaceholder");
    const fileNameLabel = document.getElementById("edit_photoFileName");
    const photoPath = manager.m_photo_path || "";

    if (photoPreview && photoPath) {
      photoPreview.src = "../public/" + photoPath;
      photoPreview.style.display = "block";
      if (placeholder) placeholder.style.display = "none";
      if (fileNameLabel) fileNameLabel.textContent = photoPath.split("/").pop();
    } else {
      if (photoPreview) photoPreview.style.display = "none";
      if (placeholder) placeholder.style.display = "flex";
      if (fileNameLabel) fileNameLabel.textContent = "";
    }

    const existingPhoto = document.getElementById("m_existing_photo");
    if (existingPhoto) existingPhoto.value = photoPath;
  }

  document.querySelectorAll(".editManagerBtn").forEach(button => {
    button.addEventListener("click", () => {
      try {
        const data = JSON.parse(button.dataset.manager || '{}');
        populateEditModal(data);
      } catch (e) {
        console.error("❌ Failed to parse manager data:", e);
      }
    });
  });

  document.querySelectorAll(".viewManagerBtn").forEach(button => {
    button.addEventListener("click", () => {
      try {
        const managerId = button.getAttribute('data-manager-id');
        
        if (!managerId) {
          console.error("❌ No manager ID found");
          return;
        }
        
                            // Always fetch fresh data from server to ensure we have the latest information
        fetch(`../app/api/managers_account-api.php?action=get_manager&id=${managerId}`)
          .then(response => response.json())
          .then(data => {
            
            if (data.status === 'success') {
              const manager = data.manager;
              
              const defaultImg = manager.m_sex?.toLowerCase() === 'female'
          ? '../public/assets/image/default_women.png'
          : '../public/assets/image/default_men.png';

        const img = document.getElementById("view_managerPhoto");
              if (img) img.src = manager.m_photo_path ? "../public/" + manager.m_photo_path : defaultImg;

              const fullName = `${capitalize(manager.m_last_name)}, ${capitalize(manager.m_first_name)} ${manager.m_middle_name ? manager.m_middle_name.charAt(0).toUpperCase() + '.' : ''}`;
        document.getElementById("managerName").textContent = fullName.trim();
              document.getElementById("managerIdView").textContent = manager.m_employee_id || 'N/A';
              document.getElementById("managerBloodType").textContent = manager.m_blood_type || 'N/A';
              document.getElementById("managerCivilStatus").textContent = manager.m_civil_status || 'N/A';
              document.getElementById("managerBirthday").textContent = manager.m_dob || 'N/A';
              document.getElementById("managerSex").textContent = manager.m_sex || 'N/A';
              document.getElementById("managerCitizen").textContent = capitalize(manager.m_citizenship) || 'N/A';
              document.getElementById("managerRFID").textContent = manager.m_rfid_number || 'N/A';
              document.getElementById("managerPosition").textContent = manager.m_position || 'N/A';
              document.getElementById("managerEmail").textContent = manager.m_email || 'N/A';
              document.getElementById("managerPhone").textContent = manager.m_contact_number || 'N/A';
              document.getElementById("managerPlaceOfBirth").textContent = capitalize(manager.m_place_of_birth) || 'N/A';
              // Resolve branch label
              (function(){
                function getBranchLabelById(id){
                  if (!id) return '';
                  try {
                    var lists = [document.getElementById('branchDropdownList'), document.getElementById('editBranchDropdownList')];
                    for (var i=0;i<lists.length;i++){
                      var list = lists[i];
                      if (!list) continue;
                      var opt = list.querySelector('[data-value="'+ String(id) +'"]');
                      if (opt) return opt.getAttribute('data-label') || opt.textContent || '';
                    }
                  } catch(_) {}
                  return '';
                }
                var branchLabel = '';
                if (manager.branch_name) {
                  branchLabel = manager.branch_name + (manager.branch_address ? (' - ' + manager.branch_address) : '');
                } else if (manager.branch_id) {
                  branchLabel = getBranchLabelById(manager.branch_id);
                }
                document.getElementById('managerBranch').textContent = branchLabel || manager.m_branch || 'N/A';
              })();
              document.getElementById("managerSalary").textContent = parseFloat(manager.m_base_salary || 0).toFixed(2);
              document.getElementById("managerSSS").textContent = formatSSS(manager.m_sss_number);
              document.getElementById("managerPagibig").textContent = formatPagibig(manager.m_pagibig_number);
              document.getElementById("managerPhilhealth").textContent = formatPhilhealth(manager.m_philhealth_number);
              document.getElementById("managerAddress").textContent = capitalize(manager.m_address) || 'N/A';

              window.lastViewedManager = manager;
        
        // Set hidden input values with error handling
        const managerIdInput = document.getElementById("manager_id");
        const employeeIdInput = document.getElementById("view_manager_employee_id");
        
              if (managerIdInput) managerIdInput.value = manager.id || '';
              if (employeeIdInput) employeeIdInput.value = manager.m_employee_id || '';
        
        // Set the delete button data-id with better error handling
        const deleteBtn = document.getElementById("modalDeleteManagerBtn");
        if (deleteBtn) {
                deleteBtn.setAttribute("data-id", manager.id);
              }
        } else {
              console.error("❌ Error fetching manager data:", data.message);
        }
          })
          .catch(err => {
            console.error("❌ Error during fetch:", err);
          });
      } catch (e) {
        console.error("❌ Error displaying view modal:", e);
      }
    });
  });

  // Add delete functionality for the view modal delete button
  document.getElementById("modalDeleteManagerBtn").addEventListener("click", function() {
    const managerId = this.getAttribute("data-id");
    
    if (!managerId || managerId === '') {
      Swal.fire({ 
        icon: "error", 
        title: "Error", 
        text: "Manager ID not found. Please try refreshing the page." 
      });
      return;
    }

    // Close the modal immediately when delete button is clicked
    const modalElement = document.getElementById("viewManagerModal");
    
    // Close modal using multiple methods to ensure it works
    if (modalElement) {
      try {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }
      } catch (error) {
        // Fallback method
      }
      
      // Fallback: direct DOM manipulation
      modalElement.classList.remove('show');
      modalElement.style.display = 'none';
      document.body.classList.remove('modal-open');
      const backdrop = document.querySelector('.modal-backdrop');
      if (backdrop) backdrop.remove();
    }

    // Show confirmation SweetAlert after modal is closed
    Swal.fire({
      title: "Are you sure?",
      text: "This will move the manager to delete history. You can restore them later.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Yes, move to history!"
    }).then((result) => {
      if (result.isConfirmed) {
        const formData = new FormData();
        formData.append("id", managerId);
        formData.append("action", "soft_delete");



        fetch("../app/api/managers_account-api.php", {
          method: "POST",
          body: formData
        })
        .then(response => {
          return response.json();
        })
        .then(data => {
          
          if (data.status === "success") {
            // Show success message
            Swal.fire({
              icon: "success",
              title: "Moved to History!",
              text: data.message,
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              // Refresh the table after a short delay
              setTimeout(() => {
                refreshManagersTable();
              }, 200);
            });
          } else {
            console.error("❌ Delete failed:", data.message);
            Swal.fire({ icon: "error", title: "Delete failed", text: data.message });
          }
        })
        .catch(err => {
          console.error("❌ Error during delete:", err);
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Something went wrong while deleting the manager."
          });
        });
      }
    });
  });

  // Add file input handling for add modal
  function previewManagerPhoto(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) {
      // No file selected; hide preview and show placeholder
      const preview = document.getElementById('managersPhotoPreview');
      const placeholder = document.getElementById('photoPlaceholder');
      if (preview) preview.style.display = 'none';
      if (placeholder) placeholder.style.display = 'flex';
      return;
    }
    try {
      const objectUrl = URL.createObjectURL(file);
      const preview = document.getElementById('managersPhotoPreview');
      const placeholder = document.getElementById('photoPlaceholder');
      try { console.debug('previewManagerPhoto: file', file && file.type, file && file.name); } catch(_) {}
      if (preview) {
        // If blob load fails (CSP or type), fallback to FileReader data URL
        preview.onerror = function(){
          try { URL.revokeObjectURL(objectUrl); } catch(_) {}
          const fr = new FileReader();
          fr.onload = function(ev){
            preview.src = ev.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
            try { console.debug('Preview (add) fallback via FileReader'); } catch(_) {}
          };
          try { fr.readAsDataURL(file); } catch(_) {}
        };
        preview.onload = function(){ try { URL.revokeObjectURL(objectUrl); } catch(_) {} };
        // Reset then assign to ensure refresh
        try { preview.removeAttribute('src'); } catch(_) {}
        preview.src = objectUrl;
        preview.style.display = 'block';
        try {
          preview.style.position = 'absolute';
          preview.style.top = '0';
          preview.style.left = '0';
          preview.style.width = '100%';
          preview.style.height = '100%';
          preview.style.objectFit = 'cover';
          preview.style.zIndex = '1';
        } catch (_) {}
      }
      if (placeholder) placeholder.style.display = 'none';
      try { console.debug('Preview (add) set via ObjectURL'); } catch(_) {}
    } catch (_) {
      // Fallback to FileReader if ObjectURL creation throws
      const reader = new FileReader();
      reader.onload = function(e) {
        const preview = document.getElementById('managersPhotoPreview');
        const placeholder = document.getElementById('photoPlaceholder');
        if (preview) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        }
        if (placeholder) placeholder.style.display = 'none';
      };
      reader.readAsDataURL(file);
    }
  }

  function displayFileName(input) {
    try {
      const fileNameLabel = document.getElementById('photoFileName');
      if (fileNameLabel) fileNameLabel.textContent = (input.files && input.files[0] && input.files[0].name) || '';
    } catch (_) {}
  }

  // Expose to inline handlers
  try { window.previewManagerPhoto = previewManagerPhoto; } catch(_) {}
  try { window.displayFileName = displayFileName; } catch(_) {}

  // Add file input handling for edit modal
  function editpreviewManagerPhoto(event) {
    const file = event.target.files[0];
    if (file) {
      try {
        const objectUrl = URL.createObjectURL(file);
        const preview = document.getElementById('edit_managerPhotoPreview');
        const placeholder = document.getElementById('edit_photoPlaceholder');
        try { console.debug('editpreviewManagerPhoto: file', file && file.type, file && file.name); } catch(_) {}
        if (preview) {
          preview.onerror = function(){
            try { URL.revokeObjectURL(objectUrl); } catch(_) {}
            const fr = new FileReader();
            fr.onload = function(ev){
              preview.src = ev.target.result;
              preview.style.display = 'block';
              if (placeholder) placeholder.style.display = 'none';
              try { console.debug('Preview (edit) fallback via FileReader'); } catch(_) {}
            };
            try { fr.readAsDataURL(file); } catch(_) {}
          };
          preview.onload = function(){ try { URL.revokeObjectURL(objectUrl); } catch(_) {} };
          try { preview.removeAttribute('src'); } catch(_) {}
          preview.src = objectUrl;
          preview.style.display = 'block';
          try {
            preview.style.position = 'absolute';
            preview.style.top = '0';
            preview.style.left = '0';
            preview.style.width = '100%';
            preview.style.height = '100%';
            preview.style.objectFit = 'cover';
            preview.style.zIndex = '1';
          } catch (_) {}
        }
        if (placeholder) {
          placeholder.style.display = 'none';
        }
        try { console.debug('Preview (edit) set via ObjectURL'); } catch(_) {}
      } catch(_) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const preview = document.getElementById('edit_managerPhotoPreview');
        const placeholder = document.getElementById('edit_photoPlaceholder');
        if (preview) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        }
        if (placeholder) {
          placeholder.style.display = 'none';
        }
      };
      reader.readAsDataURL(file);
      }
    }
  }

  function editdisplayFileName(input) {
    const fileName = input.files[0]?.name || '';
    const fileNameLabel = document.getElementById('edit_photoFileName');
    if (fileNameLabel) {
      fileNameLabel.textContent = fileName;
    }
  }

  // Make functions globally available
  window.editpreviewManagerPhoto = editpreviewManagerPhoto;
  window.editdisplayFileName = editdisplayFileName;

     window.editManagerFromObject = (managerData) => {
     
     // Always get fresh data from server to ensure we have the latest information
     const managerId = managerData?.id || window.lastViewedManager?.id;
     
     if (!managerId) {
       console.error("❌ No manager ID available for editing");
       Swal.fire({
         icon: 'error',
         title: 'Error',
         text: 'No manager data available. Please try viewing the manager again.'
       });
       return;
     }
     

     
     // Fetch fresh data from server
     fetch(`../app/api/managers_account-api.php?action=get_manager&id=${managerId}`)
       .then(response => response.json())
       .then(data => {
         if (data.status === 'success') {
           const freshManager = data.manager;
           
           // Update window.lastViewedManager with fresh data
           window.lastViewedManager = freshManager;
           
           // Populate the edit modal with fresh data
           setTimeout(() => {
             populateEditModal(freshManager);
           }, 100);
    } else {
           console.error("❌ Error fetching fresh manager data:", data.message);
           Swal.fire({
             icon: 'error',
             title: 'Error',
             text: 'Failed to fetch manager data. Please try again.'
           });
         }
       })
       .catch(err => {
         console.error("❌ Error during fetch:", err);
         Swal.fire({
           icon: 'error',
           title: 'Error',
           text: 'Failed to fetch manager data. Please try again.'
         });
       });
  };

  if (saveBtn && form) {
    saveBtn.addEventListener("click", e => {
      e.preventDefault();

      const formData = new FormData(form);
      const id = formData.get("id")?.trim();

      ["email", "rfidNumber", "employeeId"].forEach(key => {
        const input = form.querySelector(`[name="${key}"]`);
        if (input) formData.set(key, input.value.trim());
      });

      if (id && id !== "null" && id !== "") {
        formData.append("_method", "PUT");
      }

      fetch("../app/api/managers_account-api.php", {
        method: "POST",
        body: formData
      })
      .then(response => {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
          return response.json();
        } else {
          return response.text().then(text => {
            console.error("❌ Not JSON:", text);
            throw new Error("Response was not JSON");
          });
        }
      })
      .then(data => {
        if (data.status === "success") {
          // Close the add modal first
          const addModal = document.getElementById("addManagerAccountModal");
          if (addModal) {
            try {
              const modal = bootstrap.Modal.getInstance(addModal);
              if (modal) {
                modal.hide();
              }
            } catch (error) {
              // Fallback: direct DOM manipulation
              addModal.classList.remove('show');
              addModal.style.display = 'none';
              document.body.classList.remove('modal-open');
              const backdrop = document.querySelector('.modal-backdrop');
              if (backdrop) backdrop.remove();
            }
          }

          // Reset the form
          if (form) {
            resetAddManagerForm();
          }

          Swal.fire({
            icon: "success",
            title: id ? "Manager Updated!" : "Manager Added!",
            text: data.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            // Close the update modal if it's open
            const updateModal = document.getElementById("updateManagerAccountModal");
            if (updateModal) {
              try {
                const modal = bootstrap.Modal.getInstance(updateModal);
                if (modal) {
                  modal.hide();
                }
              } catch (error) {
                // Fallback: direct DOM manipulation
                updateModal.classList.remove('show');
                updateModal.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
              }
            }
            refreshManagersTable();
          });
        } else {
          Swal.fire({ icon: "error", title: "Failed", text: data.message });
        }
      })
      .catch(err => {
        console.error("❌ Error during fetch:", err);
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Something went wrong while saving the manager."
        });
      });
    });
  }

  document.querySelectorAll(".deleteManagerBtn").forEach(button => {
    button.addEventListener("click", () => {
      const id = button.dataset.id;
      if (!id) return;

      Swal.fire({
        title: "Are you sure?",
        text: "This will move the manager to delete history. You can restore them later.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, move to history!",
        cancelButtonText: "Cancel"
      }).then(result => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append("id", id);
          formData.append("action", "soft_delete");

          fetch("../app/api/managers_account-api.php", {
            method: "POST",
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === "success") {
              Swal.fire({
                icon: "success",
                title: "Moved to History!",
                text: data.message,
                timer: 1500,
                showConfirmButton: false
              }).then(() => refreshManagersTable());
            } else {
              Swal.fire({ icon: "error", title: "Delete failed", text: data.message });
            }
          });
        }
      });
    });
  });

  // Search functionality
  const searchInput = document.getElementById("manager_searchInput");
  const clearBtn = document.getElementById("manager_clearButton");
  const tbody = document.getElementById("managersTable");

  // Make searchInput globally available
  window.searchInput = searchInput;

  let debounce;

  toggleClearButton();
  filterTable();

  searchInput.addEventListener("input", () => {
    toggleClearButton();
    clearTimeout(debounce);
    debounce = setTimeout(filterTable, 300);
  });

  clearBtn.addEventListener("click", () => {
    searchInput.value = "";
    toggleClearButton();
    filterTable();
  });

  function toggleClearButton() {
    clearBtn.classList.toggle("hidden", searchInput.value.trim() === "");
  }

  function filterTable() {
    const searchTerm = searchInput.value.trim().toLowerCase();
    const rows = tbody.querySelectorAll("tr");
    let visibleCount = 0;

    rows.forEach(row => {
      if (row.id === "noResultRow") return;

      const nameCell = row.querySelector("td:nth-child(3)");
      const empNoCell = row.querySelector("td:nth-child(4)");
      if (!nameCell || !empNoCell) return;

      const name = nameCell.textContent.trim().toLowerCase();
      const empNo = empNoCell.textContent.trim().toLowerCase();
      const nameParts = name.split(" ");

      const matches =
        name.includes(searchTerm) ||
        empNo.includes(searchTerm) ||
        nameParts.some(p => p.startsWith(searchTerm));

      if (matches) {
        row.style.display = "";
        row.classList.add("fade-in-slide");
        visibleCount++;
      } else {
        row.style.display = "none";
      }
    });

    let noRow = document.getElementById("noResultRow");
    if (visibleCount === 0) {
      if (!noRow) {
        noRow = document.createElement("tr");
        noRow.id = "noResultRow";
        noRow.innerHTML = `
          <td colspan="7" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
            <i class="bi bi-person-x fs-4 me-2"></i> No managers found.
          </td>`;
        tbody.appendChild(noRow);
      }
    } else {
      if (noRow) noRow.remove();
    }
  }

  window.applyFilter = filterTable;
  window.filterTable = filterTable;
  
  // Function to refresh the managers table
  function refreshManagersTable() {
    const tbody = document.getElementById('managersTable');
    
    if (!tbody) {
      console.error("❌ managersTable tbody not found");
      return;
    }
    
    // Add fade out animation
    tbody.style.transition = 'opacity 0.3s ease';
    tbody.style.opacity = '0';
    
    setTimeout(() => {
      fetch('../app/api/managers_account-api.php?action=get_managers')
        .then(response => {
          return response.json();
        })
        .then(data => {
          
          if (data.status === 'success') {
            tbody.innerHTML = data.html;
            
            // Rebind event listeners for new elements
            bindManagerEventListeners();
            
            // Update lastViewedManager with fresh data if it exists
            if (window.lastViewedManager && window.lastViewedManager.id) {
              // Find the updated manager in the new table data
              const managerRow = tbody.querySelector(`[data-manager-id="${window.lastViewedManager.id}"]`);
              if (managerRow) {
                try {
                  const freshData = JSON.parse(managerRow.dataset.manager || '{}');
                  window.lastViewedManager = freshData;
                } catch (e) {
                  // If parsing fails, fetch fresh data from server
                  fetch(`../app/api/managers_account-api.php?action=get_manager&id=${window.lastViewedManager.id}`)
                    .then(response => response.json())
                    .then(data => {
                      if (data.status === 'success') {
                        window.lastViewedManager = data.manager;
                      }
                    })
                    .catch(err => {
                      // Silently handle error
                    });
                }
              }
            }
            
            // Also update any open edit modal with fresh data
            const editModal = document.getElementById('updateManagerAccountModal');
            if (editModal && editModal.classList.contains('show')) {
              // If edit modal is open, refresh its data
              const editDobInput = document.getElementById('m_edit_dob');
              if (editDobInput && window.lastViewedManager && window.lastViewedManager.m_dob) {
                // Update the DOB input with fresh data
                editDobInput.value = window.lastViewedManager.m_dob;
              }
            }
            
            // Reapply search filter if there's a search term
            if (searchInput.value.trim()) {
              filterTable();
            }
            
            // Add fade in animation
            tbody.style.opacity = '1';
          } else {
            tbody.style.opacity = '1'; // Restore opacity on error
          }
        })
        .catch(err => {
          tbody.style.opacity = '1'; // Restore opacity on error
        });
    }, 300);
  }

  // Make refreshManagersTable globally available
  window.refreshManagersTable = refreshManagersTable;

  // Function to bind event listeners for manager buttons
  function bindManagerEventListeners() {



    // Rebind view buttons
    document.querySelectorAll(".viewManagerBtn").forEach(button => {
      button.addEventListener("click", () => {
        try {
          const data = JSON.parse(button.dataset.manager || '{}');
          
          const defaultImg = data.m_sex?.toLowerCase() === 'female'
            ? '../public/assets/image/default_women.png'
            : '../public/assets/image/default_men.png';

          const img = document.getElementById("view_managerPhoto");
          if (img) img.src = data.m_photo_path ? "../public/" + data.m_photo_path : defaultImg;

          const fullName = `${capitalize(data.m_last_name)}, ${capitalize(data.m_first_name)} ${data.m_middle_name ? data.m_middle_name.charAt(0).toUpperCase() + '.' : ''}`;
          document.getElementById("managerName").textContent = fullName.trim();
          document.getElementById("managerIdView").textContent = data.m_employee_id || 'N/A';
          document.getElementById("managerBloodType").textContent = data.m_blood_type || 'N/A';
          document.getElementById("managerCivilStatus").textContent = data.m_civil_status || 'N/A';
          document.getElementById("managerBirthday").textContent = data.m_dob || 'N/A';
          document.getElementById("managerSex").textContent = data.m_sex || 'N/A';
          document.getElementById("managerCitizen").textContent = capitalize(data.m_citizenship) || 'N/A';
          document.getElementById("managerRFID").textContent = data.m_rfid_number || 'N/A';
          document.getElementById("managerPosition").textContent = data.m_position || 'N/A';
          document.getElementById("managerEmail").textContent = data.m_email || 'N/A';
          document.getElementById("managerPhone").textContent = data.m_contact_number || 'N/A';
          document.getElementById("managerPlaceOfBirth").textContent = capitalize(data.m_place_of_birth) || 'N/A';
          // Resolve branch label for cached row data too
          (function(){
            function getBranchLabelById(id){
              if (!id) return '';
              try {
                var lists = [document.getElementById('branchDropdownList'), document.getElementById('editBranchDropdownList')];
                for (var i=0;i<lists.length;i++){
                  var list = lists[i];
                  if (!list) continue;
                  var opt = list.querySelector('[data-value="'+ String(id) +'"]');
                  if (opt) return opt.getAttribute('data-label') || opt.textContent || '';
                }
              } catch(_) {}
              return '';
            }
            var branchLabel = '';
            if (data.branch_name) {
              branchLabel = data.branch_name + (data.branch_address ? (' - ' + data.branch_address) : '');
            } else if (data.branch_id) {
              branchLabel = getBranchLabelById(data.branch_id);
            }
            document.getElementById('managerBranch').textContent = branchLabel || data.m_branch || 'N/A';
          })();
          document.getElementById("managerSalary").textContent = parseFloat(data.m_base_salary || 0).toFixed(2);
          document.getElementById("managerSSS").textContent = formatSSS(data.m_sss_number);
          document.getElementById("managerPagibig").textContent = formatPagibig(data.m_pagibig_number);
          document.getElementById("managerPhilhealth").textContent = formatPhilhealth(data.m_philhealth_number);
          document.getElementById("managerAddress").textContent = capitalize(data.m_address) || 'N/A';

          window.lastViewedManager = data;
          
          const managerIdInput = document.getElementById("manager_id");
          const employeeIdInput = document.getElementById("view_manager_employee_id");
          
          if (managerIdInput) managerIdInput.value = data.id || '';
          if (employeeIdInput) employeeIdInput.value = data.m_employee_id || '';
          
          const deleteBtn = document.getElementById("modalDeleteManagerBtn");
          if (deleteBtn) {
            const managerId = data.id || data.m_id || button.getAttribute('data-manager-id') || '';
            deleteBtn.setAttribute("data-id", managerId);
          }
        } catch (e) {
          console.error("❌ Error displaying view modal:", e);
        }
      });
    });

    // Rebind edit buttons
    document.querySelectorAll(".editManagerBtn").forEach(button => {
      button.addEventListener("click", () => {
        try {
          const data = JSON.parse(button.dataset.manager || '{}');
           editManagerFromObject(data);
        } catch (e) {
          console.error("❌ Failed to parse manager data:", e);
        }
      });
    });

    // Rebind delete buttons
    document.querySelectorAll(".deleteManagerBtn").forEach(button => {
      button.addEventListener("click", () => {
        const id = button.dataset.id;
        if (!id) return;

        Swal.fire({
          title: "Are you sure?",
          text: "This will move the manager to delete history. You can restore them later.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, move to history!",
          cancelButtonText: "Cancel"
        }).then(result => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append("id", id);
            formData.append("action", "soft_delete");



            fetch("../app/api/managers_account-api.php", {
              method: "POST",
              body: formData
            })
            .then(res => {
              return res.json();
            })
            .then(data => {
              
              if (data.status === "success") {
                Swal.fire({
                  icon: "success",
                  title: "Moved to History!",
                  text: data.message,
                  timer: 1500,
                  showConfirmButton: false
                }).then(() => {
                  refreshManagersTable();
                });
              } else {
                console.error("❌ Delete failed:", data.message);
                Swal.fire({ icon: "error", title: "Delete failed", text: data.message });
              }
            })
            .catch(err => {
              console.error("❌ Error during delete:", err);
            });
          }
        });
      });
    });
  }
  
  // Initialize dropdowns
  try {
    // Static dropdowns
    setupStaticDropdown(
      'branchDropdownBtn',
      'branchDropdownList',
      'branchDropdownSelected',
      'branchManager'
    );
    
    setupStaticDropdown(
      'positionDropdownBtn',
      'positionDropdownList',
      'positionDropdownSelected',
      'position'
    );
    
    setupStaticDropdown(
      'editBranchDropdownBtn',
      'editBranchDropdownList',
      'editBranchDropdownSelected',
      'm_edit_branchManager'
    );
    
    setupStaticDropdown(
      'editPositionDropdownBtn',
      'editPositionDropdownList',
      'editPositionDropdownSelected',
      'm_edit_position'
    );

    // Blood type dropdowns (static options)
    setupStaticDropdown(
      'bloodTypeDropdownBtn',
      'bloodTypeDropdownList',
      'bloodTypeDropdownSelected',
      'bloodType'
    );
    
    setupStaticDropdown(
      'editBloodTypeDropdownBtn',
      'editBloodTypeDropdownList',
      'editBloodTypeDropdownSelected',
      'm_edit_bloodType'
    );

    // Sex dropdowns (static options)
    setupStaticDropdown(
      'sexDropdownBtn',
      'sexDropdownList',
      'sexDropdownSelected',
      'sex'
    );

    setupStaticDropdown(
      'editSexDropdownBtn',
      'editSexDropdownList',
      'editSexDropdownSelected',
      'm_edit_sex'
    );

    // Civil Status dropdowns (static options)
    setupStaticDropdown(
      'civilStatusDropdownBtn',
      'civilStatusDropdownList',
      'civilStatusDropdownSelected',
      'civilStatus'
    );

    setupStaticDropdown(
      'editCivilStatusDropdownBtn',
      'editCivilStatusDropdownList',
      'editCivilStatusDropdownSelected',
      'm_edit_civilStatus'
    );

    // Citizenship dropdowns (API-based)
    setupCustomCitizenshipDropdown(
      'citizenshipDropdownBtn',
      'citizenshipDropdownList',
      'citizenshipDropdownSelected',
      'citizenship',
      '../app/api/citizenship-api.php'
    );
    
    setupCustomCitizenshipDropdown(
      'editCitizenshipDropdownBtn',
      'editCitizenshipDropdownList',
      'editCitizenshipDropdownSelected',
      'm_edit_citizenship',
      '../app/api/citizenship-api.php'
    );
    
  } catch (error) {
    // Error initializing dropdowns
  }
  
  // Function to reset Add Manager form
  function resetAddManagerForm() {
    const addModal = document.getElementById('addManagerAccountModal');
    if (!addModal) {
      return;
    }

    // Reset all text inputs
    const textInputs = addModal.querySelectorAll('input[type="text"], input[type="email"]');
    textInputs.forEach(input => {
        input.value = '';
    });

    // Reset hidden inputs
    const hiddenInputs = addModal.querySelectorAll('input[type="hidden"]');
    hiddenInputs.forEach(input => {
        input.value = '';
    });

    // Reset file input
    const fileInput = document.getElementById('managerPhoto');
    if (fileInput) {
        fileInput.value = '';
    }

    // Reset photo preview
    const photoPreview = document.getElementById('managersPhotoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const photoFileName = document.getElementById('photoFileName');
    if (photoPreview) photoPreview.style.display = 'none';
    if (photoPlaceholder) photoPlaceholder.style.display = 'flex';
    if (photoFileName) photoFileName.textContent = '';

    // Reset dropdown displays and clear selections
    const dropdownConfigs = [
        { spanId: 'branchDropdownSelected', listId: 'branchDropdownList', defaultText: 'Select a branch' },
        { spanId: 'positionDropdownSelected', listId: 'positionDropdownList', defaultText: 'Select a position' },
        { spanId: 'citizenshipDropdownSelected', listId: 'citizenshipDropdownList', defaultText: 'Select citizenship' },
        { spanId: 'bloodTypeDropdownSelected', listId: 'bloodTypeDropdownList', defaultText: 'Select blood type' },
        { spanId: 'sexDropdownSelected', listId: 'sexDropdownList', defaultText: 'Select' },
        { spanId: 'civilStatusDropdownSelected', listId: 'civilStatusDropdownList', defaultText: 'Select' }
    ];

    dropdownConfigs.forEach(config => {
        // Reset the dropdown button text
        const span = document.getElementById(config.spanId);
        if (span) {
            span.textContent = config.defaultText;
        }

        // Clear all selected states in the dropdown list
        const list = document.getElementById(config.listId);
        if (list) {
            const options = list.querySelectorAll('.custom-dropdown-option');
            options.forEach(option => {
                option.classList.remove('selected');
            });
        }
    });

    // Reset date input
    const dobInput = document.getElementById('dob');
    if (dobInput) {
        dobInput.value = '';
    }

    // Clear validation messages
    const validationMessages = addModal.querySelectorAll('.validation-message');
    validationMessages.forEach(msg => {
        msg.textContent = '';
    });

    // Reset validation icons
    const validationIcons = addModal.querySelectorAll('.validation-icon');
    validationIcons.forEach(icon => {
        icon.className = 'validation-icon absolute right-2 top-1/2 transform -translate-y-1/2';
    });

    // Reset employee ID
    const employeeIdInput = document.getElementById('employeeId');
    if (employeeIdInput) {
        employeeIdInput.value = '';
    }

    // Force re-initialization of all dropdowns by clearing their state
    const dropdownButtons = [
        'branchDropdownBtn',
        'positionDropdownBtn',
        'citizenshipDropdownBtn',
        'bloodTypeDropdownBtn',
        'sexDropdownBtn',
        'civilStatusDropdownBtn'
    ];
    
    dropdownButtons.forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.classList.remove('active');
        }
    });
  }

  // Function to reset Edit Manager form
  function resetEditManagerForm() {
    const editModal = document.getElementById('updateManagerAccountModal');
    if (!editModal) {
      return;
    }

    // Reset all text inputs
    const textInputs = editModal.querySelectorAll('input[type="text"], input[type="email"]');
    textInputs.forEach(input => {
        input.value = '';
    });

    // Reset hidden inputs
    const hiddenInputs = editModal.querySelectorAll('input[type="hidden"]');
    hiddenInputs.forEach(input => {
        input.value = '';
    });

    // Reset file input
    const fileInput = document.getElementById('managerEditPhoto');
    if (fileInput) {
        fileInput.value = '';
    }

    // Reset photo preview
    const photoPreview = document.getElementById('edit_managerPhotoPreview');
    const photoPlaceholder = document.getElementById('edit_photoPlaceholder');
    const photoFileName = document.getElementById('edit_photoFileName');
    if (photoPreview) photoPreview.style.display = 'none';
    if (photoPlaceholder) photoPlaceholder.style.display = 'flex';
    if (photoFileName) photoFileName.textContent = '';

    // Reset dropdown displays and clear selections
    const dropdownConfigs = [
        { spanId: 'editBranchDropdownSelected', listId: 'editBranchDropdownList', defaultText: 'Select a branch' },
        { spanId: 'editPositionDropdownSelected', listId: 'editPositionDropdownList', defaultText: 'Select a position' },
        { spanId: 'editCitizenshipDropdownSelected', listId: 'editCitizenshipDropdownList', defaultText: 'Select citizenship' },
        { spanId: 'editBloodTypeDropdownSelected', listId: 'editBloodTypeDropdownList', defaultText: 'Select blood type' },
        { spanId: 'editSexDropdownSelected', listId: 'editSexDropdownList', defaultText: 'Select' },
        { spanId: 'editCivilStatusDropdownSelected', listId: 'editCivilStatusDropdownList', defaultText: 'Select' }
    ];

    dropdownConfigs.forEach(config => {
        // Reset the dropdown button text
        const span = document.getElementById(config.spanId);
        if (span) {
            span.textContent = config.defaultText;
        }

        // Clear all selected states in the dropdown list
        const list = document.getElementById(config.listId);
        if (list) {
            const options = list.querySelectorAll('.custom-dropdown-option');
            options.forEach(option => {
                option.classList.remove('selected');
            });
        }
    });

    // Reset date input
    const dobInput = document.getElementById('m_edit_dob');
    if (dobInput) {
        dobInput.value = '';
    }

    // Clear validation messages
    const validationMessages = editModal.querySelectorAll('.validation-message');
    validationMessages.forEach(msg => {
        msg.textContent = '';
    });

    // Reset validation icons
    const validationIcons = editModal.querySelectorAll('.validation-icon');
    validationIcons.forEach(icon => {
        icon.className = 'validation-icon absolute right-2 top-1/2 transform -translate-y-1/2';
    });

    // Force re-initialization of all dropdowns by clearing their state
    const dropdownButtons = [
        'editBranchDropdownBtn',
        'editPositionDropdownBtn',
        'editCitizenshipDropdownBtn',
        'editBloodTypeDropdownBtn',
        'editSexDropdownBtn',
        'editCivilStatusDropdownBtn'
    ];
    
    dropdownButtons.forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.classList.remove('active');
        }
    });
  }
  
  // Initial binding of event listeners
  bindManagerEventListeners();
  
  // Set up employee ID generation
  const generateIdBtn = document.getElementById('generateIdBtn');
  if (generateIdBtn) {
    generateIdBtn.addEventListener('click', function() {
      const employeeIdInput = document.getElementById('employeeId');
      if (employeeIdInput) {
        // Generate a unique employee ID with 6 random numbers
        const randomNum = Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
        const generatedId = `EMP-${randomNum}`;
        
        employeeIdInput.value = generatedId;
      }
    });
  }

  // Add modal close event listeners to reset forms
  
  // Reset on X button click
  const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
  closeButtons.forEach(button => {
      button.addEventListener('click', function() {
          const modal = this.closest('.modal');
          if (modal && modal.id === 'addManagerAccountModal') {
              setTimeout(() => {
                  resetAddManagerForm();
              }, 100);
          }
      });
  });

  // Reset on Cancel button click
  const cancelButton = document.querySelector('#addManagerAccountModal .btn-secondary, #addManagerAccountModal .bg-gray-200');
  if (cancelButton) {
      cancelButton.addEventListener('click', function() {
          setTimeout(() => {
              resetAddManagerForm();
          }, 100);
      });
  }

  // Reset edit modal on X button click
  const editCloseButtons = document.querySelectorAll('#updateManagerAccountModal [data-bs-dismiss="modal"]');
  editCloseButtons.forEach(button => {
      button.addEventListener('click', function() {
          setTimeout(() => {
              resetEditManagerForm();
          }, 100);
      });
  });

  // Reset edit modal on Cancel button click
  const editCancelButton = document.querySelector('#updateManagerAccountModal .btn-secondary, #updateManagerAccountModal .bg-gray-200');
  if (editCancelButton) {
      editCancelButton.addEventListener('click', function() {
          setTimeout(() => {
              resetEditManagerForm();
          }, 100);
      });
  }
  
  // Add modal event listener to ensure DOB is set correctly when edit modal is shown
  const editModal = document.getElementById('updateManagerAccountModal');
  if (editModal) {
    editModal.addEventListener('shown.bs.modal', function() {
      // If we have a lastViewedManager, ensure the DOB is set correctly
      const editDobInput = document.getElementById('m_edit_dob');
      if (window.lastViewedManager && window.lastViewedManager.m_dob) {
        if (editDobInput) {
          editDobInput.value = window.lastViewedManager.m_dob;
        }
      }
    });
  }
  
  // Add validation to Add Manager
  if (saveBtn && form) {
    saveBtn.addEventListener("click", e => {
      e.preventDefault();
      
      const formData = new FormData(form);
      
      if (!validateManagerForm(form)) {
        return;
      }
      
      // ... existing code ...
    });
  }

  // Add validation to Update Manager
  const updateForm = document.getElementById('updateManagerForm');
  if (updateForm) {
    updateForm.addEventListener('submit', function(e) {
      const formData = new FormData(updateForm);
      
      if (!validateManagerForm(updateForm)) {
        e.preventDefault();
        return false;
      }
    });
  }
});

// Function for citizenship dropdown (fetches from API)
function setupCustomCitizenshipDropdown(dropdownBtnId, dropdownListId, selectedSpanId, hiddenInputId, apiUrl, initialValue = "") {
    const btn = document.getElementById(dropdownBtnId);
    const list = document.getElementById(dropdownListId);
    const selectedSpan = document.getElementById(selectedSpanId);
    const hiddenInput = document.getElementById(hiddenInputId);
    let options = [];
    let selectedValue = initialValue;
    let invalidCitizenship = false;
    let warningDiv = null;

    function renderOptions() {
        list.innerHTML = '';
        options.forEach(opt => {
            const div = document.createElement('div');
            div.className = 'custom-dropdown-option' + (opt.citizenship === selectedValue ? ' selected' : '');
            div.textContent = opt.citizenship;
            div.onclick = () => {
                selectedValue = opt.citizenship;
                selectedSpan.textContent = opt.citizenship;
                hiddenInput.value = opt.citizenship;
                list.classList.add('hidden');
                btn.classList.remove('active');
                
                // Remove error message when valid citizenship is selected
                if (warningDiv) {
                    warningDiv.remove();
                    warningDiv = null;
                }
                invalidCitizenship = false;
                
                // Re-render to update selected state
                renderOptions();
            };
            list.appendChild(div);
        });
        
        // Scroll to selected option if exists
        if (selectedValue && options.some(opt => opt.citizenship === selectedValue)) {
            const selectedOption = list.querySelector('.selected');
            if (selectedOption) {
                selectedOption.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    // Fetch options from API
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                options = data.data;
                
                // Check if current value is valid
                const isValidValue = options.some(opt => opt.citizenship === selectedValue);
                if (selectedValue && !isValidValue) {
                    invalidCitizenship = true;
                    // Show warning message
                    if (!warningDiv) {
                        warningDiv = document.createElement('div');
                        warningDiv.className = 'text-red-500 text-xs mt-1 bg-red-50 p-2 rounded border border-red-200';
                        // warningDiv.textContent = 'The current citizenship is not valid. Please select a valid citizenship.';
                        btn.parentNode.appendChild(warningDiv);
                    }
                    selectedSpan.textContent = 'Select citizenship';
                    hiddenInput.value = '';
                } else if (selectedValue) {
                    selectedSpan.textContent = selectedValue;
                    hiddenInput.value = selectedValue;
                }
                
                renderOptions();
            }
        })
        .catch(error => {
            console.error('Error fetching citizenship data:', error);
            selectedSpan.textContent = 'Error loading options';
        });

    // Toggle dropdown
    btn.onclick = () => {
        list.classList.toggle('hidden');
        btn.classList.toggle('active');
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!btn.contains(e.target) && !list.contains(e.target)) {
            list.classList.add('hidden');
            btn.classList.remove('active');
        }
    });
}

// Generic function for static dropdowns (like Branch and Position)
function setupStaticDropdown(dropdownBtnId, dropdownListId, selectedSpanId, hiddenInputId, initialValue = "") {
    const btn = document.getElementById(dropdownBtnId);
    const list = document.getElementById(dropdownListId);
    const selectedSpan = document.getElementById(selectedSpanId);
    const hiddenInput = document.getElementById(hiddenInputId);
    
    if (!btn || !list || !selectedSpan || !hiddenInput) {
        return;
    }
    
    let selectedValue = initialValue;

    // Clear any existing selections first
    if (list) {
        list.querySelectorAll('.custom-dropdown-option').forEach(option => {
            option.classList.remove('selected');
        });
    }

    // Set default text based on dropdown type
    const defaultTexts = {
        'branchDropdownSelected': 'Select a branch',
        'positionDropdownSelected': 'Select a position',
        'bloodTypeDropdownSelected': 'Select blood type',
        'editBranchDropdownSelected': 'Select a branch',
        'editPositionDropdownSelected': 'Select a position',
        'editBloodTypeDropdownSelected': 'Select blood type'
    };

    // Always start with default text unless there's a valid initial value
    if (!initialValue) {
        selectedSpan.textContent = defaultTexts[selectedSpanId] || 'Select';
        hiddenInput.value = '';
    }

    function renderOptions() {
        const options = list.querySelectorAll('.custom-dropdown-option');
        options.forEach(option => {
            option.classList.remove('selected');
            if (option.dataset.value === selectedValue) {
                option.classList.add('selected');
            }
        });
        
        // Scroll to selected option if exists
        if (selectedValue) {
            const selectedOption = list.querySelector('.selected');
            if (selectedOption) {
                selectedOption.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    // Set initial value if provided
    if (initialValue) {
        // Use setTimeout to ensure DOM is ready
        setTimeout(() => {
            const options = list.querySelectorAll('.custom-dropdown-option');
            let displayText = initialValue;
            
            options.forEach(option => {
                if (option.dataset.value === initialValue) {
                    displayText = option.textContent;
                }
            });
            
            selectedSpan.textContent = displayText;
            hiddenInput.value = initialValue;
            renderOptions();
        }, 10);
    } else {
        // Ensure dropdown shows default text
        selectedSpan.textContent = defaultTexts[selectedSpanId] || 'Select';
        hiddenInput.value = '';
    }

    // Add click handlers to options
    list.querySelectorAll('.custom-dropdown-option').forEach(option => {
        option.onclick = () => {
            selectedValue = option.dataset.value;
            selectedSpan.textContent = option.textContent; // Use the display text, not the value
            hiddenInput.value = option.dataset.value;
            

            
            list.classList.add('hidden');
            btn.classList.remove('active');
            renderOptions();
        };
    });

    // Toggle dropdown
    btn.onclick = () => {
        list.classList.toggle('hidden');
        btn.classList.toggle('active');
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!btn.contains(e.target) && !list.contains(e.target)) {
            list.classList.add('hidden');
            btn.classList.remove('active');
        }
    });
}

// --- JS validation for required fields ---
function validateManagerForm(form) {
  let valid = true;
  let firstInvalid = null;
  // All fields except image
  const requiredFields = [
    'employeeId', 'branchManager', 'position', 'rfidNumber', 'firstName', 'middleName', 'lastName', 'dob',
    'placeOfBirth', 'sex', 'civilStatus', 'contactNumber', 'email', 'citizenship', 'bloodType',
    'address', 'baseSalary', 'sssNumber', 'pagibigNumber', 'philhealthNumber'
  ];
  requiredFields.forEach(name => {
    const input = form.querySelector(`[name="${name}"]`);
    if (input && input.value.trim() === '') {
      valid = false;
      input.classList.add('border-red-500');
      if (!firstInvalid) firstInvalid = input;
      // Show validation message if available
      const msg = input.closest('.flex')?.querySelector('.validation-message') || input.parentElement.querySelector('.validation-message');
      if (msg) msg.textContent = 'This field is required.';
    } else if (input) {
      input.classList.remove('border-red-500');
      const msg = input.closest('.flex')?.querySelector('.validation-message') || input.parentElement.querySelector('.validation-message');
      if (msg) msg.textContent = '';
    }
  });
  if (firstInvalid) firstInvalid.focus();
  return valid;
}

(function(){
                                        function computeBranchesApiUrl(){
                                            try {
                                                var scriptName = '<?= addslashes($_SERVER['SCRIPT_NAME'] ?? '') ?>';
                                                var baseDir = scriptName.substring(0, scriptName.lastIndexOf('/'));
                                                if (/\/public$/.test(baseDir)) { baseDir = baseDir.replace(/\/public$/, ''); }
                                                return (window.location.origin || '') + baseDir + '/app/api/branches-api.php';
                                            } catch (_) {
                                                return (window.location.origin || '') + '/app/api/branches-api.php';
                                            }
                                        }

                                        var apiUrl = computeBranchesApiUrl();
                                        var listEl = document.getElementById('branchDropdownList');
                                        var hiddenInput = document.getElementById('branchManager');
                                        var selectedSpan = document.getElementById('branchDropdownSelected');
                                        var container = document.getElementById('branch-dropdown-container');

                                        function sanitize(text){ return (text || '').replace(/</g, '&lt;'); }

                                        function renderBranches(rows){
                                            if (!listEl) return;
                                            listEl.innerHTML = '';
                                            if (!rows || rows.length === 0) {
                                                var empty = document.createElement('div');
                                                empty.className = 'px-3 py-2 text-gray-500 text-sm';
                                                empty.textContent = 'No branches found';
                                                listEl.appendChild(empty);
                                                return;
                                            }
                                            var currentValue = hiddenInput ? (hiddenInput.value || '') : '';
                                            rows.forEach(function(row){
                                                var label = sanitize(row.name) + (row.address ? ' - ' + sanitize(row.address) : '');
                                                var div = document.createElement('div');
                                                div.className = 'custom-dropdown-option';
                                                div.setAttribute('data-value', label);
                                                div.textContent = label;
                                                if (currentValue && currentValue === label) { div.classList.add('active'); }
                                                listEl.appendChild(div);
                                            });
                                        }

                                        function fetchBranches(){
                                            try {
                                                // Ensure session cookies are sent
                                                fetch(apiUrl, { credentials: 'same-origin' })
                                                  .then(function(r){ return r.json(); })
                                                  .then(function(res){
                                                      if (res && res.status === 'success' && Array.isArray(res.data)) {
                                                          renderBranches(res.data);
                                                      } else {
                                                          console.error('Branches API error', res);
                                                          if (window.Swal) { Swal.fire({ icon: 'error', title: 'Failed to load branches', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' }); }
                                                      }
                                                  })
                                                  .catch(function(err){
                                                      console.error('Branches API request failed', err);
                                                      if (window.Swal) { Swal.fire({ icon: 'error', title: 'Failed to load branches', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' }); }
                                                  });
                                            } catch(err) {
                                                console.error('Branches fetch error', err);
                                            }
                                        }

                                        function openBranchDropdown(){
                                            if (!listEl) return;
                                            listEl.classList.remove('hidden');
                                            listEl.style.display = 'block';
                                            try { listEl.style.minWidth = toggleBtn.getBoundingClientRect().width + 'px'; } catch(_) {}
                                            listEl.dataset.open = '1';
                                            if (!listEl.querySelector('.custom-dropdown-option')) {
                                                listEl.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">Loading...</div>';
                                                fetchBranches();
                                            }
                                            try {
                                                var current = hiddenInput ? (hiddenInput.value || '') : '';
                                                listEl.querySelectorAll('.custom-dropdown-option').forEach(function(el){
                                                    var val = el.getAttribute('data-value') || '';
                                                    el.classList.toggle('active', current && current === val);
                                                });
                                            } catch(_) {}
                                        }

                                        function closeBranchDropdown(){
                                            if (!listEl) return;
                                            listEl.classList.add('hidden');
                                            listEl.style.display = 'none';
                                            listEl.dataset.open = '0';
                                        }

                                        // Delegate option click
                                        if (container) {
                                            container.addEventListener('click', function(ev){
                                                var opt = ev.target.closest('.custom-dropdown-option');
                                                if (!opt) return;
                                                var value = opt.getAttribute('data-value') || '';
                                                if (hiddenInput) hiddenInput.value = value;
                                                if (selectedSpan) selectedSpan.textContent = value || 'Select a branch';
                                                // set active state
                                                try {
                                                    listEl.querySelectorAll('.custom-dropdown-option').forEach(function(el){ el.classList.remove('active'); });
                                                    opt.classList.add('active');
                                                } catch(_) {}
                                                // hide list
                                                closeBranchDropdown();
                                            });
                                        }

                                        // Toggle dropdown visibility
                                        var toggleBtn = document.getElementById('branchDropdownBtn');
                                        if (toggleBtn && listEl) {
                                            toggleBtn.addEventListener('click', function(e){
                                                e.preventDefault();
                                                e.stopPropagation();
                                                var isOpen = listEl.dataset.open === '1';
                                                if (isOpen) {
                                                    closeBranchDropdown();
                                                } else {
                                                    openBranchDropdown();
                                                }
                                            });
                                            // Close when clicking outside
                                            document.addEventListener('click', function(ev){
                                                if (!container.contains(ev.target)) {
                                                    closeBranchDropdown();
                                                }
                                            });
                                        }

                                        if (document.readyState === 'loading') {
                                            document.addEventListener('DOMContentLoaded', fetchBranches);
                                        } else {
                                            fetchBranches();
                                        }
                                    })();

// ... existing code ...
</script>



