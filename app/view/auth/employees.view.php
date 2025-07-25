<?php
$title = "Employees";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");
?>

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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid  p-2 ">
                    <form method="post" id="addEmployeeForm" action="../app/api/employees-api.php"
                        enctype="multipart/form-data">
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
                                        <label class="block text-xs font-medium mb-1 ml-2">MANAGER</label>
                                        <select name="branchManager"  class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <option value="" disabled selected>Select a Manager</option>
                                            <?php if (!empty($managers)): ?>
                                                <?php foreach ($managers as $manager): ?>
                                                    <option value="<?= htmlspecialchars($manager['id']) ?>">
                                                        <?= htmlspecialchars(ucwords($manager['m_first_name']) . ' ' . strtoupper(substr($manager['m_middle_name'], 0, 1)) . '. ' . ucwords($manager['m_last_name']) . ' - ' . ucwords($manager['m_branch'])) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option disabled>No available managers</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>   
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">POSITION</label>
                                        <div class="relative">
                                            <select name="position"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                                <option disabled selected value="">Select</option>
                                                <!-- <option value="Manager">Manager</option> -->
                                                <option value="Human Resources">Human Resources</option>
                                                <option value="Staff">Staff</option>
                                                <option value="Driver">Driver</option>
                                            </select>
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
                                            <input type="date" name="dob"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
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
                                        <div class="relative">
                                            <select name="sex"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                                <option value="">Select</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CIVIL STATUS</label>
                                        <div class="relative">
                                            <select name="civilStatus"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                                <option value="">Select</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Separated">Separated</option>
                                                <option value="Divorced">Divorced</option>
                                                <option value="Widowed">Widowed</option>
                                            </select>
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
                                        <div class="relative">
                                            <input type="text" name="citizenship" placeholder="e.g., Filipino"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BLOOD TYPE</label>
                                        <div class="relative">
                                            <select name="bloodType"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                                <option value="">Select</option>
                                                <option value="A+">A+</option>
                                                <option value="A-">A-</option>
                                                <option value="B+">B+</option>
                                                <option value="B-">B-</option>
                                                <option value="O+">O+</option>
                                                <option value="O-">O-</option>
                                                <option value="AB+">AB+</option>
                                                <option value="AB-">AB-</option>
                                            </select>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid  p-2 ">
                    <form method="post" id="editEmployeeForm" action="../app/api/employees-api.php"
                        enctype="multipart/form-data">
                        <?php if (isset($employee)) : ?>
                            <input type="hidden" name="isUpdate" value="1">
                        <?php endif; ?>
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
                                        <label class="block text-xs font-medium mb-1 ml-2">MANAGER</label>
                                        <select id="edit_branchManager" name="branchManager" 
                                            class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">

                                            <!-- Default option if no manager assigned -->
                                            <option value="" disabled <?= empty($currentBranchManager) ? 'selected' : '' ?>>
                                                Select a Manager
                                            </option>

                                            <?php if (!empty($managers)): ?>
                                                <?php foreach ($managers as $manager): ?>
                                                    <option 
                                                        value="<?= htmlspecialchars($manager['id']) ?>"
                                                        data-display="<?= htmlspecialchars(ucwords($manager['m_first_name']) . ' ' . strtoupper(substr($manager['m_middle_name'], 0, 1)) . '. ' . ucwords($manager['m_last_name']) . ' - ' . ucwords($manager['m_branch'])) ?>"
                                                        <?= (!empty($currentBranchManager) && $manager['id'] == $currentBranchManager) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars(ucwords($manager['m_first_name']) . ' ' . strtoupper(substr($manager['m_middle_name'], 0, 1)) . '. ' . ucwords($manager['m_last_name']) . ' - ' . ucwords($manager['m_branch'])) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option disabled>No available managers</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">POSITION</label>
                                        <div class="relative">
                                            <select name="position" id="edit_position"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                                <option disabled value="">Select</option>
                                                <option value="Manager">Manager</option>
                                                <option value="Human Resources">Human Resources</option>
                                                <option value="Staff">Staff</option>
                                                <option value="Driver">Driver</option>
                                            </select>
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
                                            <input type="date" name="dob" id="edit_dob"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
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
                                        <div class="relative">
                                            <select name="sex" id="edit_sex"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                                <option disabled value="">Select</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CIVIL STATUS</label>
                                        <div class="relative">
                                            <select name="civilStatus" id="edit_civilStatus"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                                <option disabled value="">Select</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Separated">Separated</option>
                                                <option value="Divorced">Divorced</option>
                                                <option value="Widowed">Widowed</option>
                                            </select>
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
                                        <div class="relative">
                                            <input type="text" name="citizenship" id="edit_citizenship" placeholder="e.g., Filipino"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BLOOD TYPE</label>
                                        <div class="relative">
                                            <select name="bloodType" id="edit_bloodType"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                                <option disabled value="">Select</option>
                                                <option value="A+">A+</option>
                                                <option value="A-">A-</option>
                                                <option value="B+">B+</option>
                                                <option value="B-">B-</option>
                                                <option value="O+">O+</option>
                                                <option value="O-">O-</option>
                                                <option value="AB+">AB+</option>
                                                <option value="AB-">AB-</option>
                                            </select>
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


</script>






<?php require_once views_path("partials/footer"); ?>