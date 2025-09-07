<?php
$title = "Employees";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");
?>
<link rel="stylesheet" href="../public/assets/css/flatpickr/material_green.css">

<style>
    
/* Hide the dropdown arrow */
.dropdown-toggle::after {
  display: none;
}

/* Remove border from the dropdown button */
.dropdown-toggle {
  border: none !important;
}

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


#noResultRow {
  display: table-row !important;
  background-color: #f0fdf4 !important;
  color: #6b7280 !important;
  font-style: italic;
}
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
                        <span class="text-2xl font-bold tracking-tight text-[#133913]">Employees</span>
                        <p class="text-[#478547]">Manage employee information and access</p>
                </div>
                
                <div>
                    <button id="showAddEmployeeModal" 
                            class="btn btn-success d-inline-flex align-items-center h-10 px-4 py-2 " 
                            data-bs-toggle="modal" 
                            data-bs-target="#addEmployeeModal">
                    <i class="fas fa-plus me-2"></i>
                    <span class="font-semibold">Add Employee</span>
                    </button>
                </div>
            </div>


        <div class="rounded-lg border-2 border-green-200 bg-white text-[#133913] shadow-sm" 
                    >
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between">
                <span class="text-2xl font-semibold leading-none tracking-tight text-[#133913]">Employee Directory</span>
                <div class="relative w-64">
                    <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="employee_searchInput"
                        class="flex h-10 w-full text-sm placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8  placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
                        placeholder="Search employee..."
                    >
                    <button id="employee_clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden" >×</button>
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
                                <tbody id="employeeTable" class="[&_tr:last-child]:border-0 min-h-[80px]">
                                    
                                        <?php if (!empty($employees) && is_array($employees) && count($employees) > 0): ?>

                                        <?php $count = 1; ?>
                                        <?php foreach($employees as $employee): ?>
                                            <tr class="fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                                <td class="p-3 align-middle font-medium"><?= $count++ ?></td>
                                                <td class="p-3 align-middle font-medium">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="relative flex shrink-0 overflow-hidden rounded-full h-12 w-12">
                                                            <?php if (!empty($employee['photo_path'])): ?>
                                                                <img class="aspect-square h-full w-full" src="<?= htmlspecialchars($employee['photo_path']) ?>" alt="Employee Photo">
                                                            <?php else: ?>
                                                                <?php
                                                                    $defaultImage = ($employee['sex'] === 'Female') 
                                                                        ? '../public/assets/image/default_women.png' 
                                                                        : '../public/assets/image/default_men.png';
                                                                ?>
                                                                <img class="aspect-square h-full w-full" src="<?= $defaultImage ?>" alt="Default Photo">
                                                            <?php endif; ?>
                                                        </span>

                                                    </div>
                                                </td>
                                                <td class="p-3 align-middle font-medium">
                                                    <div class="flex items-center space-x-2">
                                                        <span>
                                                            <?= ucwords(strtolower($employee['first_name'])) ?>
                                                            <?= !empty($employee['middle_name']) ? strtoupper(substr($employee['middle_name'], 0, 1)) . '.' : '' ?>
                                                            <?= ucwords(strtolower($employee['last_name'])) ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="p-3 align-middle"><?= htmlspecialchars($employee['employee_no']) ?></td>
                                                <td class="p-3 align-middle"><?= htmlspecialchars($employee['rfid_number']) ?></td>

                                                <?php
                                                    $position = $employee['position'] ?? '';
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
                                                        <?= htmlspecialchars($employee['position'] ?? '') ?>
                                                    </div>
                                                </td>
                                                <td class="p-3 align-middle text-right">
                                                    <div class="flex gap-2">
                                                        <!-- View Employee Button -->
                                                        <button type="button"
                                                                class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewEmployeeModal"
                                                                onclick="viewEmployee('<?= htmlspecialchars($employee['employee_no']) ?>')">
                                                            <i class="bi bi-eye text-lg"></i>
                                                        </button>

                                                        <!-- Dropdown Menu -->
                                                        <div class="dropdown relative inline-block">
                                                            <button class="dropdown-toggle-btn inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                                                                    type="button"
                                                                    data-bs-toggle="dropdown"
                                                                    aria-expanded="false">
                                                                <i class="bi bi-person-gear text-lg"></i>
                                                            </button>
                                                            <ul class="dropdown-menu absolute right-0 mt-2 w-48 rounded-md shadow-md bg-white ring-1 ring-black ring-opacity-5 z-50">
                                                                <li>
                                                                    <a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal('viewAttendanceModal', <?= $employee['id'] ?>)">
                                                                        <i class="bi bi-calendar-check h-4 w-4"></i>
                                                                        <span>View Attendance</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal('viewSlipsModal', <?= $employee['id'] ?>)">
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
                                        <tr id="EmployeenoResultRow">
                                            <td colspan="7" class="p-4 text-center italic text-gray-500 bg-[#f0fdf4]">
                                                <i class="bi bi-person-x me-2"></i> No employees found.
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
</div>
</main>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <i class="bi bi-person-plus text-[#16a249] fs-4 mr-2"></i>
                <h1 class="modal-title fs-5 text-[#16a249]" id="addModalLabel">Add Employees</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid  p-2 ">
                    <form method="post" id="addEmployeeForm" action="../app/api/employees-api.php"
                        enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="border rounded-lg mb-4">
                            <div class="bg-yellow-100 px-4 py-2 rounded-t-lg border-b border-b-gray-200">
                                <span class="font-semibold text-[#133913]">PERSONAL INFORMATION</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                <div class="flex flex-col items-center md:col-span-1">
                                    <div
                                        class="relative w-32 h-32 mb-2 mt-1 flex items-center justify-center bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-[#16a249] transition-all duration-200">
                                        <input type="file" id="employeePhoto" name="photo_path" accept="image/*"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            onchange="previewEmployeePhoto(event); displayFileName(this); validateInput(this);">
                                        <img id="employeePhotoPreview" alt="Employee Photo"
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
                                                        class="p-2 pl-8 border rounded text-sm w-full focus:outline-none"

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
                                        <label class="block text-xs font-medium mb-1 ml-2">MANAGER <span class="text-red-500">*</span></label>
                                        <div class="relative" id="manager-dropdown-container">
                                            <input type="hidden" name="branchManager" id="branchManager" required />
                                            <button type="button" id="managerDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="managerDropdownSelected" class="dropdown-button-text">Select a Manager</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="managerDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                            <?php if (!empty($managers)): ?>
                                                <?php foreach ($managers as $manager): ?>
                                                        <div class="custom-dropdown-option" data-value="<?= htmlspecialchars($manager['id']) ?>">
                                                        <?= htmlspecialchars(ucwords($manager['m_first_name']) . ' ' . strtoupper(substr($manager['m_middle_name'], 0, 1)) . '. ' . ucwords($manager['m_last_name']) . ' - ' . ucwords($manager['m_branch'])) ?>
                                                        </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                    <div class="custom-dropdown-option" data-value="">No available managers</div>
                                            <?php endif; ?>
                                    </div>   
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">POSITION <span class="text-red-500">*</span></label>
                                        <div class="relative" id="position-dropdown-container">
                                            <input type="hidden" name="position" id="position" required />
                                            <button type="button" id="positionDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="positionDropdownSelected" class="dropdown-button-text">Select</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="positionDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                                <!-- <div class="custom-dropdown-option" data-value="Manager">Manager</div> -->
                                                <div class="custom-dropdown-option" data-value="Human Resources">Human Resources</div>
                                                <div class="custom-dropdown-option" data-value="Staff">Staff</div>
                                                <div class="custom-dropdown-option" data-value="Driver">Driver</div>
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
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">MIDDLE NAME</label>
                                        <div class="relative">
                                            <input type="text" name="middleName" placeholder="e.g., Santos"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">LAST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="lastName" placeholder="e.g., Dela Cruz"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    
                                </div>
                                <div class="md:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BIRTHDAY</label>
                                        <div class="relative">
                                            <input type="text" name="dob" id="dob" class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full flatpickr-dob" placeholder="Select date">
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PLACE OF BIRTH</label>
                                        <div class="relative">
                                            <input type="text" name="placeOfBirth" placeholder="e.g., Davao City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SEX</label>
                                        <div class="relative" id="sex-dropdown-container">
                                            <input type="hidden" name="sex" id="sex" />
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
                                            <input type="hidden" name="civilStatus" id="civilStatus" />
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
                                                maxlength="11" oninput="validateContactNumber(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">EMAIL</label>
                                        <div class="relative">
                                            <input type="email" name="email" placeholder="e.g., john.doe@example.com"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
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
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
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
                                                onchange="validateInput(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SSS NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="sssNumber" maxlength="12" placeholder="e.g., 012345678912"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PAG-IBIG NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="pagibigNumber" maxlength="12" placeholder="e.g., 012345678901"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)">
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
                                                onchange="validateInput(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="modal-footer" style="border-top: none;">
                                <button type="submit"
                                    class="px-6 py-2 btn btn-success transition-colors duration-200 font-semibold">Add
                                    Employee</button>
                                <button type="button"
                                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-200 font-semibold"
                                    data-bs-dismiss="modal">Cancel</button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title text-success text-lg fw-semibold">
                    <i class="bi bi-info-circle me-2"></i>Employee Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p class="text-success small mb-4">View complete employee information</p>
                <input type="hidden" name="id" id="id">

                <div class="d-flex flex-column flex-md-row gap-4">
                    <!-- Employee Photo -->
                    <div class="ml-4 mr-3">
                        <div class="rounded-circle border-2 border-green-800 mt-[25px]" style="width: 125px; height: 125px; overflow: hidden;">
                            <img id="view_employeePhoto" src="assets/image/default_user_image.svg" alt="Employee Photo" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    </div>

                    <!-- Employee Information -->
                    <div class="flex-grow-1">
                        <h4 id="employeeName" class="fw-bold mb-3 text-lg text-[#133913]">Loading...</h4>
                        
                        <div class="row">
                            <!-- Column 1 -->
                            <div class="col-md-6 mb-2">
                                <input type="hidden" id="view_employee_id" name="employee_id">
                                <p><strong class="text-success text-sm">Employee ID:</strong> <span class="text-sm" id="employeeIdView"></span></p>
                                <p><strong class="text-success text-sm">Blood Type:</strong> <span class="text-sm" id="employeeBloodType"></span></p>
                                <p><strong class="text-success text-sm">Civil Status:</strong> <span class="text-sm" id="employeeCivilStatus"></span></p>
                                <p><strong class="text-success text-sm">Birthday:</strong> <span class="text-sm" id="employeeBirthday"></span></p>
                                <p><strong class="text-success text-sm">Sex:</strong> <span class="text-sm" id="employeeSex"></span></p>
                                <p><strong class="text-success text-sm">Citizenship:</strong> <span class="text-sm" id="employeeCitizen"></span></p>
                            </div>

                            <!-- Column 2 -->
                            <div class="col-md-6 mb-2">
                                <p><strong class="text-success text-sm">RFID Number:</strong> <span class="text-sm" id="employeeRFID"></span></p>
                                <p><strong class="text-success text-sm">Position:</strong> <span class="text-sm" id="employeePosition"></span></p>
                                <p><strong class="text-success text-sm">Email:</strong> <span class="text-sm" id="employeeEmail"></span></p>
                                <p><strong class="text-success text-sm">Phone:</strong> <span class="text-sm" id="employeePhone"></span></p>
                                <p><strong class="text-success text-sm">Place of Birth:</strong> <span class="text-sm" id="employeePlaceOfBirth"></span></p>
                                <p><strong class="text-success text-sm">Branch Manager:</strong> <span class="text-sm" id="employeeBranch"></span></p>
                            </div>

                            <!-- Column 3: Salary Info -->
                            <div class="col-md-6 mb-2">
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="text-success text-sm"><strong>Salary Information</strong></h6>
                                        <p><strong class="text-success text-sm">Base Salary:</strong> &#8369;<span class="text-sm" id="employeeSalary"></span></p>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="text-success text-sm"><strong>Accounts Information</strong></h6>
                                        <p><strong class="text-success text-sm">SSS:</strong> <span class="text-sm" id="employeeSSS"></span></p>
                                        <p><strong class="text-success text-sm">Pag-IBIG:</strong> <span class="text-sm" id="employeePagibig"></span></p>
                                        <p><strong class="text-success text-sm">Philhealth:</strong> <span class="text-sm" id="employeePhilhealth"></span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Column 4: Address -->
                            <div class="col-md-6 mb-2">
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="text-success  text-sm"><strong>Address</strong></h6>
                                        <p id="employeeAddress" class="text-sm"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end mt-3 mb-2 mr-2">
                    <button type="button" class="btn btn-outline-success me-2 w-[90px] editBtn" data-id="<?= $employee['employee_no'] ?>" data-bs-toggle="modal" data-bs-target="#editEmployeeModal">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </button>
                    <button type="button" id="modalDeleteBtn" class="btn btn-danger deleteBtn" data-id="<?= $employee['id'] ?>">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-file-pen text-[#16a249] fs-4 mr-2"></i>
                <h1 class="modal-title fs-5 text-[#16a249]" id="editEmployeeModalLabel">Edit Employees</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid  p-2 ">
                    <form method="post" id="editEmployeeForm" action="../app/api/employees-api.php"
                        enctype="multipart/form-data">
                        <?php if (isset($employee)) : ?>
                            <input type="hidden" name="isUpdate" value="1">
                        <?php endif; ?>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="border rounded-lg mb-4">
                            <div class="bg-yellow-100 px-4 py-2 rounded-t-lg border-b border-b-gray-200">
                                <span class="font-semibold text-[#133913]">PERSONAL INFORMATION</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                                <div class="flex flex-col items-center md:col-span-1">
                                    <div
                                        class="relative w-32 h-32 mb-2 mt-1 flex items-center justify-center bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-[#16a249] transition-all duration-200">
                                        <input type="file" id="employeePhoto" name="photo_path" accept="image/*"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            onchange="editpreviewEmployeePhoto(event); editdisplayFileName(this);">
                                        <img id="edit_employeePhotoPreview" alt="Employee Photo"
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
                                            <input type="text" name="employeeId" id="edit_employee_id" placeholder="e.g., EMP-001" readonly 
                                                class="p-2 pl-8 border rounded bg-gray-100 text-sm pointer-events-none cursor-default focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                            </div>                                                                            
                                        </div>
                                    </div>                                                                                                      
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">MANAGER <span class="text-red-500">*</span></label>
                                        <div class="relative" id="edit-manager-dropdown-container">
                                            <input type="hidden" name="branchManager" id="edit_branchManager" />
                                            <button type="button" id="editManagerDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editManagerDropdownSelected" class="dropdown-button-text">Select a Manager</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editManagerDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                            <?php if (!empty($managers)): ?>
                                                <?php foreach ($managers as $manager): ?>
                                                        <div class="custom-dropdown-option" data-value="<?= htmlspecialchars($manager['id']) ?>">
                                                        <?= htmlspecialchars(ucwords($manager['m_first_name']) . ' ' . strtoupper(substr($manager['m_middle_name'], 0, 1)) . '. ' . ucwords($manager['m_last_name']) . ' - ' . ucwords($manager['m_branch'])) ?>
                                                        </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                    <div class="custom-dropdown-option" data-value="">No available managers</div>
                                            <?php endif; ?>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">POSITION <span class="text-red-500">*</span></label>
                                        <div class="relative" id="edit-position-dropdown-container">
                                            <input type="hidden" name="position" id="edit_position" required />
                                            <button type="button" id="editPositionDropdownBtn" class="p-2 pl-8 border rounded text-sm w-full text-left bg-white focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] flex justify-between items-center">
                                                <span id="editPositionDropdownSelected" class="dropdown-button-text">Select</span>
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div id="editPositionDropdownList" class="absolute left-0 right-0 z-50 bg-white border rounded shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                                <div class="custom-dropdown-option" data-value="Manager">Manager</div>
                                                <div class="custom-dropdown-option" data-value="Human Resources">Human Resources</div>
                                                <div class="custom-dropdown-option" data-value="Staff">Staff</div>
                                                <div class="custom-dropdown-option" data-value="Driver">Driver</div>
                                            </div>
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 md:col-span-23 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">RFID NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="rfidNumber" id="edit_rfidNumber" placeholder="e.g., 0083222913"
                                                class="p-2 pl-8 border rounded  text-sm w-full focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]"
                                                oninput="validateRfidNumber(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">FIRST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="firstName" id="edit_first_name" placeholder="e.g., Juan"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">MIDDLE NAME</label>
                                        <div class="relative">
                                            <input type="text" name="middleName" id="edit_middle_name" placeholder="e.g., Santos"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">LAST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="lastName" id="edit_last_name" placeholder="e.g., Dela Cruz"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                </div>

                                
                                <div class="md:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BIRTHDAY</label>
                                        <div class="relative">
                                            <input type="text" name="dob" id="edit_dob" class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full flatpickr-dob" placeholder="Select date">
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PLACE OF BIRTH</label>
                                        <div class="relative">
                                            <input type="text" name="placeOfBirth" id="edit_placeOfBirth" placeholder="e.g., Davao City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SEX</label>
                                        <div class="relative" id="edit-sex-dropdown-container">
                                            <input type="hidden" name="sex" id="edit_sex" />
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
                                            <input type="hidden" name="civilStatus" id="edit_civilStatus" />
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
                                            <input type="text" name="contactNumber" id="edit_contactNumber" placeholder="e.g., 09171234567"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                maxlength="11" oninput="validateContactNumber(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">EMAIL</label>
                                        <div class="relative">
                                            <input type="email" name="email" id="edit_email" placeholder="e.g., john.doe@example.com"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CITIZENSHIP</label>
                                        <div class="relative" id="edit-citizenship-dropdown-container">
                                            <input type="hidden" name="citizenship" id="edit_citizenship" />
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
                                            <input type="hidden" name="bloodType" id="edit_bloodType" />
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
                                            <input type="text" name="address" id="edit_address" placeholder="e.g., 1234 Mabini St., Barangay Malinis, Quezon City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
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
                                            <input type="text" name="baseSalary" id="edit_baseSalary" placeholder="e.g., 600"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SSS NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="sssNumber" id="edit_sssNumber" maxlength="12" placeholder="e.g., 012345678912"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PAG-IBIG NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="pagibigNumber" id="edit_pagibigNumber" maxlength="12" placeholder="e.g., 012345678901"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PHILHEALTH NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="philhealthNumber" id="edit_philhealthNumber" maxlength="12"
                                                placeholder="e.g., 123456789012"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                onchange="validateInput(this)">
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
                                    Update Employee
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


<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

<script>
document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        const openModal = document.querySelector(".modal:not(.hidden)");
        if (openModal) {
            closeModal(openModal.id);
        }
    }
});

function toggleDropdown(button) {
    const dropdown = button.nextElementSibling;
    const tableCell = button.closest('td');
    const tableRect = tableCell.getBoundingClientRect();
    const viewportHeight = window.innerHeight;

    dropdown.classList.toggle('hidden');

    if (!dropdown.classList.contains('hidden')) {
        const dropdownRect = dropdown.getBoundingClientRect();

        // Check if dropdown would overflow the right side of the viewport
        if (dropdownRect.right > window.innerWidth) {
            dropdown.style.right = 'auto';
            dropdown.style.left = '0';
        } else {
            dropdown.style.right = '0';
            dropdown.style.left = 'auto';
        }

        // Check if dropdown would overflow the bottom of the viewport
        const dropdownHeight = dropdown.scrollHeight;
        const spaceBelow = viewportHeight - tableRect.bottom;
        const spaceAbove = tableRect.top;

        if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
            // Open upward if there's more space above
            dropdown.style.top = 'auto';
            dropdown.style.bottom = '100%';
            dropdown.style.marginTop = '0';
            dropdown.style.marginBottom = '0.5rem';
        } else {
            // Open downward
            dropdown.style.top = '100%';
            dropdown.style.bottom = 'auto';
            dropdown.style.marginTop = '0.5rem';
            dropdown.style.marginBottom = '0';
        }
    }

    // Remove existing click listener to prevent stacking
    document.removeEventListener('click', document._dropdownClickListener);

    // Add new click listener
    document._dropdownClickListener = function(e) {
        if (!button.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
            document.removeEventListener('click', document._dropdownClickListener);
            document._dropdownClickListener = null;
        }
    };

    document.addEventListener('click', document._dropdownClickListener);
}

// Update control numbers only if changed to prevent infinite loops
function updateControlNumbers() {
  const rows = document.querySelectorAll('#employeeTable tr');
  let visibleIndex = 1;

  rows.forEach(row => {
    const isNoResult = row.id === "noResultRow";
    const isHidden = row.style.display === "none";

    const numberCell = row.querySelector('td:first-child');

    if (isNoResult || isHidden) {
      if (numberCell) numberCell.textContent = '';
    } else {
      if (numberCell) numberCell.textContent = visibleIndex++;
    }
  });
}



// document.addEventListener('DOMContentLoaded', () => {
//     updateControlNumbers();

//     const tableBody = document.querySelector('tbody');

//     if (tableBody) {
//         const observer = new MutationObserver(() => {
//             observer.disconnect(); // prevent recursion
//             updateControlNumbers();
//             observer.observe(tableBody, { childList: true, subtree: true });
//         });

//         observer.observe(tableBody, { childList: true, subtree: true });
//     }
// });

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
                        warningDiv.textContent = 'The current citizenship is not valid. Please select a valid citizenship.';
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
// Add modal show listeners to initialize dropdowns
const addModal = document.getElementById('addEmployeeModal');
if (addModal) {
    addModal.addEventListener('show.bs.modal', function() {
        // Initialize all dropdowns with fresh state
        setupCustomCitizenshipDropdown(
            'citizenshipDropdownBtn',
            'citizenshipDropdownList',
            'citizenshipDropdownSelected',
            'citizenship',
            '../app/api/citizenship-api.php'
        );
        setupStaticDropdown(
            'bloodTypeDropdownBtn',
            'bloodTypeDropdownList',
            'bloodTypeDropdownSelected',
            'bloodType'
        );
        setupStaticDropdown(
            'managerDropdownBtn',
            'managerDropdownList',
            'managerDropdownSelected',
            'branchManager'
        );
        setupStaticDropdown(
            'positionDropdownBtn',
            'positionDropdownList',
            'positionDropdownSelected',
            'position'
        );
        setupStaticDropdown(
            'sexDropdownBtn',
            'sexDropdownList',
            'sexDropdownSelected',
            'sex'
        );
        setupStaticDropdown(
            'civilStatusDropdownBtn',
            'civilStatusDropdownList',
            'civilStatusDropdownSelected',
            'civilStatus'
        );
        
        // Add debugging for dropdown values
        console.log('Dropdowns initialized');
        console.log('Manager dropdown value:', document.getElementById('branchManager')?.value);
        console.log('Position dropdown value:', document.getElementById('position')?.value);
        console.log('Sex dropdown value:', document.getElementById('sex')?.value);
        console.log('Civil status dropdown value:', document.getElementById('civilStatus')?.value);
    });

    // Reset form when modal is hidden
    addModal.addEventListener('hidden.bs.modal', function() {
        resetAddEmployeeForm();
    });
}

const editModal = document.getElementById('editEmployeeModal');
if (editModal) {
    editModal.addEventListener('show.bs.modal', function() {
        // Get current value from the hidden input if it exists
        const currentHiddenInput = document.getElementById('edit_citizenship');
        const current = currentHiddenInput ? currentHiddenInput.value : "";
        setupCustomCitizenshipDropdown(
            'editCitizenshipDropdownBtn',
            'editCitizenshipDropdownList',
            'editCitizenshipDropdownSelected',
            'edit_citizenship',
            '../app/api/citizenship-api.php',
            current
        );
        
        // Get current blood type value
        const currentBloodTypeInput = document.getElementById('edit_bloodType');
        const currentBloodType = currentBloodTypeInput ? currentBloodTypeInput.value : "";
        setupStaticDropdown(
            'editBloodTypeDropdownBtn',
            'editBloodTypeDropdownList',
            'editBloodTypeDropdownSelected',
            'edit_bloodType',
            currentBloodType
        );
        
        // Get current manager value
        const currentManagerInput = document.getElementById('edit_branchManager');
        const currentManager = currentManagerInput ? currentManagerInput.value : "";
        setupStaticDropdown(
            'editManagerDropdownBtn',
            'editManagerDropdownList',
            'editManagerDropdownSelected',
            'edit_branchManager',
            currentManager
        );
        
        // Get current position value
        const currentPositionInput = document.getElementById('edit_position');
        const currentPosition = currentPositionInput ? currentPositionInput.value : "";
        setupStaticDropdown(
            'editPositionDropdownBtn',
            'editPositionDropdownList',
            'editPositionDropdownSelected',
            'edit_position',
            currentPosition
        );
        
        // Get current sex value
        const currentSexInput = document.getElementById('edit_sex');
        const currentSex = currentSexInput ? currentSexInput.value : "";
        setupStaticDropdown(
            'editSexDropdownBtn',
            'editSexDropdownList',
            'editSexDropdownSelected',
            'edit_sex',
            currentSex
        );
        
        // Get current civil status value
        const currentCivilStatusInput = document.getElementById('edit_civilStatus');
        const currentCivilStatus = currentCivilStatusInput ? currentCivilStatusInput.value : "";
        setupStaticDropdown(
            'editCivilStatusDropdownBtn',
            'editCivilStatusDropdownList',
            'editCivilStatusDropdownSelected',
            'edit_civilStatus',
            currentCivilStatus
        );
    });
}

// Generic function for static dropdowns (like Blood Type)
function setupStaticDropdown(dropdownBtnId, dropdownListId, selectedSpanId, hiddenInputId, initialValue = "") {
    const btn = document.getElementById(dropdownBtnId);
    const list = document.getElementById(dropdownListId);
    const selectedSpan = document.getElementById(selectedSpanId);
    const hiddenInput = document.getElementById(hiddenInputId);
    let selectedValue = initialValue;

    // Clear any existing selections first
    if (list) {
        list.querySelectorAll('.custom-dropdown-option').forEach(option => {
            option.classList.remove('selected');
        });
    }

    // Set default text based on dropdown type
    const defaultTexts = {
        'citizenshipDropdownSelected': 'Select citizenship',
        'bloodTypeDropdownSelected': 'Select blood type',
        'managerDropdownSelected': 'Select a Manager',
        'positionDropdownSelected': 'Select',
        'sexDropdownSelected': 'Select',
        'civilStatusDropdownSelected': 'Select'
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
            
            // Debug: Log the selection
            console.log(`Dropdown ${hiddenInputId} selected:`, {
                value: option.dataset.value,
                text: option.textContent,
                hiddenInputValue: hiddenInput.value
            });
            
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

// Function to reset Add Employee form
function resetAddEmployeeForm() {
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
    const fileInput = document.getElementById('employeePhoto');
    if (fileInput) {
        fileInput.value = '';
    }

    // Reset photo preview
    const photoPreview = document.getElementById('employeePhotoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const photoFileName = document.getElementById('photoFileName');
    if (photoPreview) photoPreview.style.display = 'none';
    if (photoPlaceholder) photoPlaceholder.style.display = 'flex';
    if (photoFileName) photoFileName.textContent = '';

    // Reset dropdown displays and clear selections
    const dropdownConfigs = [
        { spanId: 'citizenshipDropdownSelected', listId: 'citizenshipDropdownList', defaultText: 'Select citizenship' },
        { spanId: 'bloodTypeDropdownSelected', listId: 'bloodTypeDropdownList', defaultText: 'Select blood type' },
        { spanId: 'managerDropdownSelected', listId: 'managerDropdownList', defaultText: 'Select a Manager' },
        { spanId: 'positionDropdownSelected', listId: 'positionDropdownList', defaultText: 'Select' },
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

    // Reset flatpickr date picker
    const dobInput = document.getElementById('dob');
    if (dobInput && dobInput._flatpickr) {
        dobInput._flatpickr.clear();
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
        'citizenshipDropdownBtn',
        'bloodTypeDropdownBtn', 
        'managerDropdownBtn',
        'positionDropdownBtn',
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

// Add manual reset for close and cancel buttons
document.addEventListener('DOMContentLoaded', function() {
    // Reset on X button click
    const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal && modal.id === 'addEmployeeModal') {
                setTimeout(() => {
                    resetAddEmployeeForm();
                }, 100);
            }
        });
    });

    // Reset on Cancel button click
    const cancelButton = document.querySelector('#addEmployeeModal .btn-secondary, #addEmployeeModal .bg-gray-200');
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            setTimeout(() => {
                resetAddEmployeeForm();
            }, 100);
        });
    }
});
</script>
<script src="../public/assets/js/flatpickr/flatpickr.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Store flatpickr instances
    const flatpickrInstances = [];
    document.querySelectorAll('.flatpickr-dob').forEach(function(input) {
        const instance = flatpickr(input, {
            dateFormat: 'Y-m-d',
            allowInput: true,
            altInput: true,
            altFormat: 'F j, Y',
            theme: 'material_green',
        });
        flatpickrInstances.push(instance);
    });

    // Helper to close all flatpickr instances
    function closeAllFlatpickr() {
        flatpickrInstances.forEach(fp => fp.close());
    }

    // Add scroll event listeners to modal-content containers
    document.querySelectorAll('.modal-content').forEach(function(modalContent) {
        modalContent.addEventListener('scroll', closeAllFlatpickr);
    });
});
</script>


<?php require_once views_path("partials/footer"); ?>