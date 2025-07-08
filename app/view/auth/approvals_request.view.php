<?php
$title = "Approvals by Manager";
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
</style>


<main class="flex-1 h-[calc(100vh-3rem)] p-4 md:p-6 ml-[255px] mt-12 bg-[#f8fbf8]">
    <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <span class="text-2xl font-bold tracking-tight text-[#133913]">Approval Request</span>
                    <p class="text-[#478547]">View and manage employees pending and reject approval</p>
                </div>



                
                <!-- <div>
                    <button id="showAddEmployeeModal" 
                            class="btn btn-success d-inline-flex align-items-center h-10 px-4 py-2 " 
                            data-bs-toggle="modal" 
                            data-bs-target="#addProducts">
                    <i class="fas fa-plus me-2"></i>
                    <span class="font-semibold">Add Employee</span>
                    </button>
                </div> -->
            </div>


        <div class="rounded-lg border-2 border-green-200 bg-white text-[#133913] shadow-sm" 
                    >
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between">
                <span class="text-md font-semibold leading-none tracking-tight text-[#133913]">
                    <!-- All employees in the delete history will be permanently deleted after 60 days if not restored. -->
                </span>

                <div class="relative w-64">
                    <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="approval_searchInput"
                        class="flex h-10 w-full text-sm placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8  placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
                        placeholder="Search employee..."
                        oninput="toggleClearButton()"
                    >
                    <button id="approval_clearButton" class="absolute right-2 top-1 text-[#478547] text-xl hidden" onclick="clearInput()">×</button>
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
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Position</th>
                                    <th class="h-12 px-3 text-left align-middle font-bold text-[#478547] bg-white">Status</th>
                                    <th class="h-12 px-3 align-middle font-bold text-[#478547] text-center bg-white">Actions</th>
                                </tr>
                            </thead>
                                <tbody id="approvalTable" class="[&_tr:last-child]:border-0">
                                    <?php if (!empty($employeesApproval)) : ?>
                                        <?php $index = 1; ?>
                                        <?php foreach ($employeesApproval as $emp) : ?>
                                            <tr class="fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                                                <td class="px-3 py-2 align-middle"><?= $index++ ?></td>
                                                <td class="px-3 py-2 align-middle">
                                                    <?php if (!empty($emp['photo_path'])): ?>
                                                        <img src="<?= htmlspecialchars($emp['photo_path']) ?>" alt="Photo" class="h-10 w-10 rounded-full object-cover">
                                                    <?php else: ?>
                                                        <?php
                                                            $defaultImage = ($emp['sex'] === 'Female')
                                                                ? '../public/assets/image/default_women.png'
                                                                : '../public/assets/image/default_men.png';
                                                        ?>
                                                        <img src="<?= $defaultImage ?>" alt="Default Photo" class="h-10 w-10 rounded-full object-cover">
                                                    <?php endif; ?>

                                                </td>
                                                <td class="px-3 py-2 align-middle">
                                                    <?= htmlspecialchars(
                                                        ucwords(strtolower($emp['first_name'])) . ' ' .
                                                        (!empty($emp['middle_name']) ? strtoupper(substr($emp['middle_name'], 0, 1)) . '. ' : '') .
                                                        ucwords(strtolower($emp['last_name']))
                                                    ) ?>
                                                </td>
                                                <td class="px-3 py-2 align-middle"><?= htmlspecialchars($emp['employee_no']) ?></td>
                                                <td class="px-3 py-2 align-middle"><?= htmlspecialchars($emp['rfid_number']) ?></td>
                                                <td class="px-3 py-2 align-middle"><?= htmlspecialchars($emp['position']) ?></td>
                                                <td class="px-3 py-2 align-middle text-center">
                                                    <?php
                                                        if ($emp['approved_by_manager'] == 1) {
                                                            $badgeClass = 'bg-green-100 text-green-800';
                                                            $badgeText = 'Approved';
                                                        } elseif ($emp['approved_by_manager'] == -1) {
                                                            $badgeClass = 'bg-red-100 text-red-800';
                                                            $badgeText = 'Rejected';
                                                        } else {
                                                            $badgeClass = 'bg-yellow-100 text-yellow-800';
                                                            $badgeText = 'Pending';
                                                        }
                                                    ?>
                                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?= $badgeClass ?>">
                                                        <?= $badgeText ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 align-middle text-center">
                                                    <?php if ($emp['approved_by_manager'] === -1): ?>
                                                        <button 
                                                            type="button" 
                                                            class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white resend-approval-btn" 
                                                            data-id="<?= htmlspecialchars($emp['id']) ?>" 
                                                            title="Resend Approval"
                                                        >
                                                            <i class="bi bi-send"></i>
                                                        </button>
                                                        <!-- View Button -->
                                                        <button 
                                                            type="button" 
                                                            class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-blue-500 hover:text-white approval-view-btn" 
                                                            data-id="<?= htmlspecialchars($emp['id']) ?>"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#approvalViewModal" 
                                                            title="View"
                                                        >
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <!-- Edit Button -->
                                                        <!-- <button 
                                                            type="button" 
                                                            class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-yellow-500 hover:text-white approval-edit-btn" 
                                                            data-id="<?= htmlspecialchars($emp['id']) ?>"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#approvaleditEmployeeModal"  
                                                            title="Edit"
                                                        >
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button> -->
                                                        <!-- Delete Button -->
                                                        <button 
                                                            type="button" 
                                                            class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-red-600 hover:text-white delete-btn" 
                                                            data-id="<?= htmlspecialchars($emp['id']) ?>" 
                                                            title="Delete"
                                                        >
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php elseif ($emp['approved_by_manager'] == 0): ?>
                                                        <!-- View Button -->
                                                        <button 
                                                            type="button" 
                                                            class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-blue-500 hover:text-white approval-view-btn" 
                                                            data-id="<?= htmlspecialchars($emp['id']) ?>"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#approvalViewModal"  
                                                            title="View"
                                                        >
                                                            <i class="bi bi-eye"></i>
                                                        </button>

                                                        <!-- Delete Button -->
                                                        <button 
                                                            type="button" 
                                                            class="inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-red-600 hover:text-white delete-btn" 
                                                            data-id="<?= htmlspecialchars($emp['id']) ?>" 
                                                            title="Delete"
                                                        >
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-gray-500 italic">No Action</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <!-- <td colspan="7" class="text-center py-4 text-gray-500">No employees found for approval tracking.</td> -->
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

<!-- View Approval Modal -->
<div class="modal fade" id="approvalViewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="approvalViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-primary fw-semibold">
                    <i class="bi bi-person-badge me-2"></i>Pending Employee Approval Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="text-muted small mb-4">Review full details of the employee pending and rejected approval</p>
                <input type="hidden" name="id" id="approvalEmployeeId">

                <div class="d-flex flex-column flex-md-row gap-4">
                    <div class="ml-4 mr-3">
                        <div class="rounded-circle border-2 border-primary mt-[25px]" style="width: 125px; height: 125px; overflow: hidden;">
                            <img id="view_approvalEmployeePhoto" src="assets/image/default_user_image.svg" alt="Employee Photo" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    </div>

                    <div class="flex-grow-1">
                        <h4 id="approvalEmployeeName" class="fw-bold mb-3 text-primary">Loading...</h4>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="hidden" id="view_deleted_employee_id" name="employee_id">

                                <p><strong class="text-sm">Employee ID:</strong> <span class="text-sm" id="approvalEmployeeIdView"></span></p>
                                <p><strong class="text-sm">Blood Type:</strong> <span class="text-sm" id="approvalEmployeeBloodType"></span></p>
                                <p><strong class="text-sm">Civil Status:</strong> <span class="text-sm" id="approvalEmployeeCivilStatus"></span></p>
                                <p><strong class="text-sm">Birthday:</strong> <span class="text-sm" id="approvalEmployeeBirthday"></span></p>
                                <p><strong class="text-sm">Sex:</strong> <span class="text-sm" id="approvalEmployeeSex"></span></p>
                                <p><strong class="text-sm">Citizenship:</strong> <span class="text-sm" id="approvalEmployeeCitizen"></span></p>
                            </div>

                            <div class="col-md-6 mb-2">
                                <p><strong class="text-sm">RFID Number:</strong> <span class="text-sm" id="approvalEmployeeRFID"></span></p>
                                <p><strong class="text-sm">Position:</strong> <span class="text-sm" id="approvalEmployeePosition"></span></p>
                                <p><strong class="text-sm">Email:</strong> <span class="text-sm" id="approvalEmployeeEmail"></span></p>
                                <p><strong class="text-sm">Phone:</strong> <span class="text-sm" id="approvalEmployeePhone"></span></p>
                                <p><strong class="text-sm">Place of Birth:</strong> <span class="text-sm" id="approvalEmployeePlaceOfBirth"></span></p>
                                <p><strong class="text-sm">Branch Manager:</strong> <span class="text-sm" id="approvalEmployeeBranch"></span></p>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="text-sm"><strong>Salary Information</strong></h6>
                                        <p><strong class="text-sm">Base Salary:</strong> &#8369;<span class="text-sm" id="approvalEmployeeSalary"></span></p>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="text-sm"><strong>Accounts Information</strong></h6>
                                        <p><strong class="text-sm">SSS:</strong> <span class="text-sm" id="approvalEmployeeSSS"></span></p>
                                        <p><strong class="text-sm">Pag-IBIG:</strong> <span class="text-sm" id="approvalEmployeePagibig"></span></p>
                                        <p><strong class="text-sm">Philhealth:</strong> <span class="text-sm" id="approvalEmployeePhilhealth"></span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="text-sm"><strong>Address</strong></h6>
                                        <p id="approvalEmployeeAddress" class="text-sm"></p>
                                    </div>
                                </div>
                            </div>

                        </div> <!-- end row -->
                    </div> <!-- end flex-grow-1 -->
                </div> <!-- end d-flex -->
            </div> <!-- end modal-body -->

            <!-- You can add action buttons here (Approve / Reject) if needed -->

        </div> <!-- end modal-content -->
    </div> <!-- end modal-dialog -->
</div> <!-- end modal -->

<!-- Edit employee approval -->
<div class="modal fade" id="approvaleditEmployeeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="approvaleditEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-file-pen text-[#16a249] fs-4 mr-2"></i>
                <h1 class="modal-title fs-5 text-[#16a249]" id="approvaleditEmployeeModalLabel">Edit Employees</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid  p-2 ">
                    <form method="POST" id="approvaleditEmployeeForm" action="index.php?payroll=approvals_request"
                        enctype="multipart/form-data">
                        <?php if (isset($employee)) : ?>
                            <input type="hidden" name="isUpdate" value="1">
                        <?php endif; ?>
                        <input type="hidden" name="approvaledit_id" id="approvaledit_id" value="" />


                        <div class="border rounded-lg mb-4">
                            <div class="bg-yellow-100 px-4 py-2 rounded-t-lg border-b border-b-gray-200">
                                <span class="font-semibold text-[#133913]">PERSONAL INFORMATION</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">

                                <div class="flex flex-col items-center md:col-span-1">
                                    <div
                                        class="relative w-32 h-32 mb-2 mt-1 flex items-center justify-center bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-[#16a249] transition-all duration-200">
                                        <input type="file" id="approvaleditEmployeePhoto" name="photo_path" accept="image/*"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            onchange="approvaleditpreviewEmployeePhoto(event); approvaleditdisplayFileName(this);">
                                        <img id="approvaledit_employeePhotoPreview" alt="Employee Photo"
                                            class="w-full h-full object-cover rounded-lg absolute top-0 left-0 z-0"
                                            style="display:none;">
                                        <span id="approvaledit_photoPlaceholder" class="flex flex-col items-center justify-center text-gray-400 z-0">
                                            <i class="bi bi-image text-3xl mb-1"></i>
                                            <span class="text-xs">Upload Photo</span>
                                        </span>
                                    </div>
                                    <span id="approvaledit_photoFileName" class="text-sm text-gray-600 mt-1 text-center break-all max-w-[128px] overflow-hidden whitespace-nowrap text-ellipsis"></span>
                                    <div class="validation-message text-red-500 text-xs mt-1"></div>
                                </div>
                                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 w-full">
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">EMPLOYEE ID</label>
                                        <div class="relative">
                                            <input type="text" name="approvaledit_employee_id" id="approvaledit_employee_id" placeholder="e.g., EMP-001" readonly 
                                                class="p-2 pl-8 border rounded bg-gray-100 text-sm pointer-events-none cursor-default focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 md:col-span-2 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">RFID NUMBER</label>
                                        <div class="relative">
                                            <input type="text" name="approvaledit_rfidNumber" id="approvaledit_rfidNumber" placeholder="e.g., 0083222913"
                                                class="p-2 pl-8 border rounded  text-sm w-full focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]"
                                                oninput="validateRfidNumber(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                                                                                        
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">MANAGER</label>
                                        <select id="approvaledit_branchManager" name="approvaledit_branchManager" 
                                            class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">

                                            <!-- Default option if no manager assigned -->
                                            <option value="" disabled <?= empty($currentBranchManager) ? 'selected' : '' ?>>
                                                Select a Manager
                                            </option>

                                            <?php if (!empty($managers)): ?>
                                                <?php foreach ($managers as $manager): ?>
                                                    <option 
                                                        value="<?= htmlspecialchars($manager['id']) ?>"
                                                        data-display="<?= htmlspecialchars($manager['name'] . ' - ' . $manager['branch']) ?>"
                                                        <?= (!empty($currentBranchManager) && $manager['id'] == $currentBranchManager) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars(ucwords($manager['name']) . ' - ' . ucwords($manager['branch'])) ?>
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
                                            <select name="approvaledit_position" id="approvaledit_position"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                                <option value="">Select</option>
                                                <option value="Manager">Manager</option>
                                                <option value="Human Resources">Human Resources</option>
                                                <option value="Staff">Staff</option>
                                                <option value="Driver">Driver</option>
                                            </select>
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">FIRST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="approvaledit_first_name" id="approvaledit_first_name" placeholder="e.g., Juan"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                >
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">MIDDLE NAME</label>
                                        <div class="relative">
                                            <input type="text" name="approvaledit_middle_name" id="approvaledit_middle_name" placeholder="e.g., Santos"
                                                class="p-2 pl-8 border rounded  text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">LAST NAME</label>
                                        <div class="relative">
                                            <input type="text" name="approvaledit_last_name" id="approvaledit_last_name" placeholder="e.g., Dela Cruz"
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
                                            <input type="date" name="approvaledit_dob" id="approvaledit_dob"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">PLACE OF BIRTH</label>
                                        <div class="relative">
                                            <input type="text" name="approvaledit_placeOfBirth" id="approvaledit_placeOfBirth" placeholder="e.g., Davao City"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">SEX</label>
                                        <div class="relative">
                                            <select name="approvaledit_sex" id="approvaledit_sex"
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
                                            <select name="approvaledit_civilStatus" id="approvaledit_civilStatus"
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
                                            <input type="text" name="approvaledit_contactNumber" id="approvaledit_contactNumber" placeholder="e.g., 09171234567"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                                maxlength="11" oninput="validateContactNumber(this)">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">EMAIL</label>
                                        <div class="relative">
                                            <input type="email" name="approvaledit_email" id="approvaledit_email" placeholder="e.g., john.doe@example.com"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">CITIZENSHIP</label>
                                        <div class="relative">
                                            <input type="text" name="approvaledit_citizenship" id="approvaledit_citizenship" placeholder="e.g., Filipino"
                                                class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full">
                                            <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                        </div>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1 relative">
                                        <label class="block text-xs font-medium mb-1 ml-2">BLOOD TYPE</label>
                                        <div class="relative">
                                            <select name="approvaledit_bloodType" id="approvaledit_bloodType"
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
                                            <input type="text" name="approvaledit_address" id="approvaledit_address" placeholder="e.g., 1234 Mabini St., Barangay Malinis, Quezon City"
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
                                        <input type="text" name="approvaledit_baseSalary" id="approvaledit_baseSalary" placeholder="e.g., 600"
                                            class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                            onchange="validateInput(this)">
                                        <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                    </div>
                                    <div class="validation-message text-red-500 text-xs mt-1"></div>
                                </div>
                                <div class="flex flex-col gap-1 relative">
                                    <label class="block text-xs font-medium mb-1 ml-2">SSS NUMBER</label>
                                    <div class="relative">
                                        <input type="text" name="approvaledit_sssNumber" id="approvaledit_sssNumber" maxlength="12" placeholder="e.g., 012345678912"
                                            class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                            onchange="validateInput(this)">
                                        <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                    </div>
                                    <div class="validation-message text-red-500 text-xs mt-1"></div>
                                </div>
                                <div class="flex flex-col gap-1 relative">
                                    <label class="block text-xs font-medium mb-1 ml-2">PAG-IBIG NUMBER</label>
                                    <div class="relative">
                                        <input type="text" name="approvaledit_pagibigNumber" id="approvaledit_pagibigNumber" maxlength="12" placeholder="e.g., 012345678901"
                                            class="p-2 pl-8 border rounded text-sm focus:outline-none focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249] w-full"
                                            onchange="validateInput(this)">
                                        <i class="validation-icon absolute right-2 top-1/2 transform -translate-y-1/2"></i>
                                    </div>
                                    <div class="validation-message text-red-500 text-xs mt-1"></div>
                                </div>
                                <div class="flex flex-col gap-1 relative">
                                    <label class="block text-xs font-medium mb-1 ml-2">PHILHEALTH NUMBER</label>
                                    <div class="relative">
                                        <input type="text" name="approvaledit_philhealthNumber" id="approvaledit_philhealthNumber" maxlength="12"
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

<script>
let originalApprovalEditData = null;

function approvaleditdisplayFileName(input) {
  const fileNameSpan = document.getElementById('approvaledit_photoFileName');
  if (!fileNameSpan) return;
  const file = input.files?.[0];
  fileNameSpan.textContent = file ? file.name : 'No file chosen';
}

function approvaleditpreviewEmployeePhoto(event) {
  const input = event.target;
  const preview = document.getElementById('approvaledit_employeePhotoPreview');
  const placeholder = document.getElementById('approvaledit_photoPlaceholder');
  if (!preview || !placeholder) return;

  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
      placeholder.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.src = '';
    preview.style.display = 'none';
    placeholder.style.display = 'flex';
  }
}

function approvaleditinitEmployeePhoto(photoPath) {
  const preview = document.getElementById('approvaledit_employeePhotoPreview');
  const placeholder = document.getElementById('approvaledit_photoPlaceholder');
  const fileNameSpan = document.getElementById('approvaledit_photoFileName');
  if (!preview || !placeholder || !fileNameSpan) return;

  if (photoPath) {
    preview.src = photoPath;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
    fileNameSpan.textContent = photoPath.split('/').pop();
  } else {
    preview.style.display = 'none';
    placeholder.style.display = 'flex';
    fileNameSpan.textContent = 'No file chosen';
  }
}

function formatSalary(salary) {
  if (!salary) return '';
  const str = salary.toString();
  return str.endsWith('.00') ? str.slice(0, -3) : str;
}

const approvaleditBtns = document.querySelectorAll('.approval-edit-btn');
approvaleditBtns.forEach(btn => {
  btn.addEventListener('click', function() {
    const employeeId = this.getAttribute('data-id');

    fetch(`index.php?payroll=approvals_request&id=${employeeId}`)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          const emp = data.data;

          // Populate all inputs including hidden internal ID
          const fields = {
            approvaledit_id: emp.id,                      // THIS IS THE KEY
            approvaledit_employee_id: emp.employee_no,
            approvaledit_rfidNumber: emp.rfid_number,
            approvaledit_first_name: emp.first_name,
            approvaledit_middle_name: emp.middle_name,
            approvaledit_last_name: emp.last_name,
            approvaledit_dob: emp.dob,
            approvaledit_placeOfBirth: emp.place_of_birth,
            approvaledit_sex: emp.sex,
            approvaledit_philhealthNumber: emp.philhealth_number,
            approvaledit_civilStatus: emp.civil_status,
            approvaledit_contactNumber: emp.contact_number,
            approvaledit_email: emp.email,
            approvaledit_citizenship: emp.citizenship,
            approvaledit_bloodType: emp.blood_type,
            approvaledit_position: emp.position,
            approvaledit_address: emp.address,
            approvaledit_baseSalary: formatSalary(emp.base_salary),
            approvaledit_sssNumber: emp.sss_number,
            approvaledit_pagibigNumber: emp.pagibig_number
          };

          Object.entries(fields).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) el.value = val || '';
          });

          // Update photo preview (your existing function)
          approvaleditinitEmployeePhoto(emp.photo_path);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Not Found',
            text: 'Employee data not found.',
          });
        }
      })
      .catch(err => {
        console.error('Error:', err);
        Swal.fire({
          icon: 'error',
          title: 'Fetch Failed',
          text: 'An error occurred while fetching the data.',
        });
      });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('approvaleditEmployeeForm');
  if (!form) return;

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(this);

    // Debug: log the ID sent
    console.log('Submitting approvaledit_id:', formData.get('approvaledit_id'));
    console.log('Submitting employee_no:', formData.get('approvaledit_employee_id'));
    console.log('Submitting rfid_number:', formData.get('approvaledit_rfidNumber'));
    console.log('Submitting email:', formData.get('approvaledit_email'));

    fetch('index.php?payroll=approvals_request', {
      method: 'POST',
      body: formData,
    })
      .then(async (response) => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          return response.json();
        } else {
          const text = await response.text();
          console.error('Non-JSON response:', text);
          throw new Error('Server did not return valid JSON.');
        }
      })
      .then(data => {
        console.log('Server response:', data);

        if (data.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: data.message || 'Approval updated successfully.',
            timer: 1000,
            showConfirmButton: false,
            timerProgressBar: true,
          }).then(() => {
            resetApprovalEditForm();
            const modal = bootstrap.Modal.getInstance(document.getElementById('approvaleditEmployeeModal'));
            if (modal) modal.hide();
            window.location.reload();
          });
        } else if (data.status === 'no_changes') {
          Swal.fire({
            icon: 'info',
            title: 'No Changes!',
            text: 'No changes were made to the approval data.',
          });
        } else if (data.status === 'error' && data.title === 'Duplicate Entry') {
          Swal.fire({
            icon: 'error',
            title: data.title,
            text: data.message,
          });
        } else {
          Swal.fire({
            icon: data.icon || 'error',
            title: data.title || 'Update Failed',
            text: data.message || 'An unknown error occurred during update.',
          });
        }
      })
      .catch(error => {
        console.error('Fetch error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Request Failed',
          text: 'Please try again later.',
        });
      });
  });
});


function approvaleditresetForm() {
  const form = document.getElementById('approvaleditEmployeeForm');
  if (form) form.reset();

  const preview = document.getElementById('approvaledit_employeePhotoPreview');
  if (preview) preview.style.display = 'none';

  const placeholder = document.getElementById('approvaledit_photoPlaceholder');
  if (placeholder) placeholder.style.display = 'flex';

  const photoFilename = document.getElementById('approvaledit_photoFileName');
  if (photoFilename) {
    photoFilename.textContent = 'No file chosen';
    photoFilename.style.display = 'block';
  }

  // Clear original data after reset
  originalApprovalEditData = null;
}
</script>




<script>
document.addEventListener('DOMContentLoaded', () => {
  // === View Details Modal ===
  document.querySelectorAll('.approval-view-btn').forEach(button => {
    button.addEventListener('click', () => {
      const employeeId = button.getAttribute('data-id');

      fetch(`../app/api/approvals_request-api.php?id=${employeeId}`)
        .then(response => {
          if (!response.ok) throw new Error('Network error');
          return response.json();
        })
        .then(data => {
          if (data.status !== 'success' || !data.data) throw new Error('Invalid employee data');

          const emp = data.data;
          const lastName = (emp.last_name || '').toUpperCase();
          const firstName = (emp.first_name || '').toUpperCase();
          const middleInitial = emp.middle_name ? emp.middle_name.charAt(0).toUpperCase() + '.' : '';
          const formattedName = `${lastName}, ${firstName} ${middleInitial}`.trim();
          const formattedAddress = (emp.address || '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());

          const formatSSS = sss => sss?.replace(/^(\d{2,4})(\d{6,7})(\d{1})$/, '$1-$2-$3') || 'N/A';
          const formatPagibig = pagibig => pagibig?.replace(/^(\d{4})(\d{4})(\d{4})$/, '$1-$2-$3') || 'N/A';
          const formatPhilhealth = ph => ph?.replace(/^(\d{2})(\d{9})(\d{1})$/, '$1-$2-$3') || 'N/A';

          setText('approvalEmployeeId', emp.id);
          setText('approvalEmployeeName', formattedName || 'N/A');
          setText('approvalEmployeeIdView', emp.employee_no || 'N/A');
          setText('approvalEmployeeBloodType', emp.blood_type || 'N/A');
          setText('approvalEmployeeCivilStatus', emp.civil_status || 'N/A');
          setText('approvalEmployeeBirthday', emp.dob || 'N/A');
          setText('approvalEmployeeSex', emp.sex || 'N/A');
          setText('approvalEmployeeCitizen', (emp.citizenship || 'N/A').toLowerCase().replace(/\b\w/g, c => c.toUpperCase()));
          setText('approvalEmployeeRFID', emp.rfid_number || 'N/A');
          setText('approvalEmployeePosition', emp.position || 'N/A');
          setText('approvalEmployeeEmail', emp.email || 'N/A');
          setText('approvalEmployeePhone', emp.contact_number || 'N/A');
          setText('approvalEmployeePlaceOfBirth', (emp.place_of_birth || 'N/A').toLowerCase().replace(/\b\w/g, c => c.toUpperCase()));
          setText('approvalEmployeeBranch', emp.branch_name && emp.branch_address ? `${emp.branch_name} - ${emp.branch_address}` : 'N/A');
          setText('approvalEmployeeSalary', emp.base_salary || 'N/A');
          setText('approvalEmployeeSSS', formatSSS(emp.sss_number));
          setText('approvalEmployeePagibig', formatPagibig(emp.pagibig_number));
          setText('approvalEmployeePhilhealth', formatPhilhealth(emp.philhealth_number));
          setText('approvalEmployeeAddress', formattedAddress || 'N/A');

          const photo = document.getElementById('view_approvalEmployeePhoto');
          if (photo) {
            photo.src = emp.photo_path?.trim() || (
              emp.sex?.toLowerCase() === 'female'
                ? '../public/assets/image/default_women.png'
                : '../public/assets/image/default_men.png'
            );
          }

          // Show modal
        //   new bootstrap.Modal(document.getElementById('approvalViewModal')).show();
        })
        .catch(error => {
          console.error('Failed to load employee data:', error);
          Swal.fire('Error', 'Failed to load employee details.', 'error');
        });
    });
  });

  // === Resend Approval ===
  document.querySelectorAll('.resend-approval-btn').forEach(button => {
    button.addEventListener('click', () => {
      const employeeId = button.getAttribute('data-id');

      Swal.fire({
        title: 'Resend Approval?',
        text: "This will reset the approval status to pending.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, resend',
        cancelButtonText: 'Cancel',
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('../app/api/approvals_request-api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: employeeId, action: 'resend' })
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'Sent!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
              }).then(() => {
                refreshApprovalTable();
              });
            } else {
              Swal.fire('Error', data.message || 'Failed to resend approval.', 'error');
            }
          })
          .catch(() => {
            Swal.fire('Error', 'Failed to send request.', 'error');
          });
        }
      });
    });
  });

  // === Delete Employee ===
  document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', () => {
      const employeeId = button.getAttribute('data-id');
      const row = button.closest('tr');

      Swal.fire({
        title: 'Are you sure?',
        text: "This employee will be marked as deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(`../app/api/approvals_request-api.php?id=${employeeId}`, {
            method: 'DELETE'
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message,
                timer: 1000,
                showConfirmButton: false
              }).then(() => {
                // Animate fade out
                row.style.transition = 'opacity 0.5s ease';
                row.style.opacity = 0;
                setTimeout(() => {
                  row.remove();
                  refreshApprovalTable();
                }, 400);
              });
            } else {
              Swal.fire('Error', data.message || 'Failed to delete employee.', 'error');
            }
          })
          .catch(() => {
            Swal.fire('Error', 'Failed to send delete request.', 'error');
          });
        }
      });
    });
  });
});

// === REFRESH APPROVAL TABLE ===
function refreshApprovalTable() {
  const tbody = document.querySelector('#approvalTableBody');
  if (!tbody) return;

  // Step 1: Animate all rows with fadeOutSlide
  const rows = tbody.querySelectorAll('tr');
  rows.forEach((row, index) => {
    row.classList.add('fade-in-slide');
    row.style.animationDelay = `${index * 30}ms`;
  });

  // Step 2: Fetch and update after the animation
  setTimeout(() => {
    fetch('../app/api/approvals_request-api.php?fetch_table=1')
      .then(res => res.text())
      .then(html => {
        tbody.innerHTML = html;
      })
      .catch(err => {
        console.error('Failed to refresh approval table:', err);
      });
  }, 300); // Match the duration of your animation
}


// === Helper ===
function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value ?? 'N/A';
}
</script>





<!-- <script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This employee will be marked as deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send DELETE via AJAX
                    fetch('index.php?payroll=approvals_request', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'delete',
                            id: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire(data.status === 'success' ? 'Deleted!' : 'Error', data.message, data.status);
                        if (data.status === 'success') {
                            // Optional: remove row or reload
                            location.reload();
                        }
                    });
                }
            });
        });
    });
});
</script> -->


<!-- <script>
document.addEventListener('DOMContentLoaded', () => {
  // Resend Approval Logic
  document.querySelectorAll('.resend-approval-btn').forEach(button => {
    button.addEventListener('click', () => {
      const employeeId = button.getAttribute('data-id');

      Swal.fire({
        title: 'Resend Approval?',
        text: "This will set the employee's approval status back to pending.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, resend',
        cancelButtonText: 'Cancel',
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('index.php?payroll=approvals_request', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
              id: employeeId,
              action: 'resend'
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'Sent!',
                text: 'Approval has been reset to pending.',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false
              }).then(() => location.reload());
            } else {
              Swal.fire('Error', data.message || 'Failed to resend approval.', 'error');
            }
          })
          .catch(() => {
            Swal.fire('Error', 'Failed to send request.', 'error');
          });
        }
      });
    });
  });

  // Delete Logic
  document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', () => {
      const employeeId = button.getAttribute('data-id');

      Swal.fire({
        title: 'Are you sure?',
        text: "This employee will be marked as deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('index.php?payroll=approvals_request', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
              id: employeeId,
              action: 'delete'
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Employee has been marked as deleted.',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false
              }).then(() => location.reload());
            } else {
              Swal.fire('Error', data.message || 'Failed to delete employee.', 'error');
            }
          })
          .catch(() => {
            Swal.fire('Error', 'Failed to send delete request.', 'error');
          });
        }
      });
    });
  });
});
</script> -->


<script>
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("approval_searchInput");
  const clearBtn = document.getElementById("approval_clearButton");

  let debounceTimer;

  // Initial setup
  toggleClearButton();
  filterTable(); // Run once on load

  searchInput.addEventListener("input", function () {
    toggleClearButton();

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      filterTable();
    }, 300);
  });

  clearBtn.addEventListener("click", function () {
    searchInput.value = "";
    toggleClearButton();
    filterTable();
  });

  function toggleClearButton() {
    clearBtn.classList.toggle("hidden", searchInput.value.trim() === "");
  }

  function filterTable() {
    const searchTerm = searchInput.value.trim().toLowerCase();
    const rows = document.querySelectorAll("#approvalTable tr");
    let visibleCount = 0;

    rows.forEach(row => {
      if (row.id === "noResultRow") return;

      const nameCell = row.querySelector("td:nth-child(3)");
      const empNoCell = row.querySelector("td:nth-child(4)");

      if (!nameCell || !empNoCell) return;

      const name = nameCell.textContent.trim().toLowerCase();
      const empNo = empNoCell.textContent.trim().toLowerCase();
      const matches = name.includes(searchTerm) || empNo.includes(searchTerm);

      row.style.display = matches || searchTerm === "" ? "" : "none";
      if (matches) visibleCount++;
    });

    // Handle "No Result" display
    let noResultRow = document.getElementById("noResultRow");
    if (visibleCount === 0) {
      if (!noResultRow) {
        noResultRow = document.createElement("tr");
        noResultRow.id = "noResultRow";
        document.querySelector("#approvalTable").appendChild(noResultRow);
      }

      noResultRow.innerHTML = searchTerm === ""
        ? `<td colspan="8" class="px-4 py-6 text-center text-muted fst-italic bg-light">
            <i class="bi bi-info-circle fs-5 me-2"></i>There are currently no employees pending approval.
           </td>`
        : `<td colspan="8" class="px-4 py-6 text-center text-secondary fst-italic bg-light">
            <i class="bi bi-person-x fs-5 me-2"></i>No matching employees found.
           </td>`;
    } else {
      if (noResultRow) noResultRow.remove();
    }
  }
});
</script>



<?php require_once views_path("partials/footer"); ?>
