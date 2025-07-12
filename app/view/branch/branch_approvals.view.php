<?php
$title = "Approvals";
require_once views_path("partials/header");

echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';
?>

<div class="flex min-h-screen overflow-hidden">
    <!-- Main content -->
    <main id="mainContent" class="flex-1 p-6 bg-gray-100 transition-margin duration-300 ease-in-out" style="margin-left: 256px;">
        <?php require_once views_path("branch/branch_sidebar"); ?>

        <div>
            <span class="text-2xl font-bold tracking-tight">Employee's List</span>
            <p class="text-gray-600">A detailed list of employees managed by you.</p>
        </div>

        <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-gray-200 relative">
                <span class="text-lg font-semibold text-gray-800">Employees</span>
                <div class="relative max-w-sm w-full sm:w-auto">
                  <svg class="lucide lucide-search absolute left-2.5 top-3 h-4 w-4 text-[#478547]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="employeeSearch"
                        placeholder="Search employees..."
                        class="flex h-10 w-full placeholder:ml-[10px] rounded-md border border-input bg-background px-[50px] py-2 pl-8 text-base placeholder:text-[#478547] ring-offset-[#f8fbf8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 disabled:opacity-50 md:text-sm"
                    >
                    <button
                        id="clearSearch"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-sm text-gray-400 hover:text-gray-600 hidden"
                        aria-label="Clear search"
                        type="button"
                    >
                        &#x2715;
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="employeeTable" class="min-w-full divide-y divide-gray-200 text-sm overflow-hidden">
                    <thead class="bg-emerald-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">Photo</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">Employee Name</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">Employee ID</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">RFID Number</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">Position</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">Status</th>
                            <th class="px-6 py-3 text-left font-semibold tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php if (!empty($list) && is_array($list)): ?>
                            <?php foreach ($list as $lists): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <?php if (!empty($lists['photo_path'])): ?>
                                            <img src="/mvcPayroll/public/<?= htmlspecialchars($lists['photo_path']) ?>" class="h-10 w-10 rounded-full object-cover" alt="Employee Photo">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-full border-1 border-gray-500 bg-gray-300 flex items-center justify-center text-sm text-white">
                                                <img src="../public/assets/image/man (1).png" class="h-10 w-10 rounded-full object-cover" alt="Employee Photo">
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4"><?= htmlspecialchars(ucwords(strtolower($lists['full_name']))) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($lists['employee_no']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($lists['rfid_number']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($lists['position']) ?></td>
                                    
                                    <td class="px-6 py-4">
                                        <?php if ($lists['approved_by_manager'] == 1): ?>
                                            <span class="text-green-600 font-medium">Approved</span>
                                        <?php elseif ($lists['approved_by_manager'] == -1): ?>
                                            <span class="text-red-600 font-medium">Rejected</span>
                                        <?php else: ?>
                                            <span class="text-yellow-500 text-xs font-medium">Waiting for approval..</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <?php if ($lists['approved_by_manager'] == 0): ?>
                                            <div class="flex space-x-2 gap-2">
                                                <button 
                                                    class="btn btn-success text-white rounded flex items-center justify-center p-1 w-8 h-8 approve-btn" 
                                                    data-id="<?= $lists['id'] ?>"
                                                    title="Approve"
                                                    type="button"
                                                >
                                                    <i class="bi bi-check-circle"></i>
                                                </button>

                                                <button 
                                                    class="btn btn-danger text-white rounded flex items-center justify-center p-1 w-8 h-8 reject-btn" 
                                                    data-id="<?= $lists['id'] ?>"
                                                    title="Reject"
                                                    type="button"
                                                >
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">No Action</span>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center px-6 py-4 text-gray-500">No employees found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Approve buttons
  document.querySelectorAll('.approve-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      Swal.fire({
        title: 'Approve Employee?',
        text: "Are you sure you want to approve this employee?",
        icon: 'question',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        showCancelButton: true,
        confirmButtonText: 'Yes, approved',
        cancelButtonText: 'Cancel',
      }).then((result) => {
        if (result.isConfirmed) {
          // Show loading popup
          Swal.fire({
            title: 'Processing...',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
          updateApproval(id, 'approve', btn);
        }
      });
    });
  });

  // Reject buttons
  document.querySelectorAll('.reject-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      Swal.fire({
        title: 'Reject Employee?',
        text: "Are you sure you want to reject this employee?",
        icon: 'warning',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        showCancelButton: true,
        confirmButtonText: 'Yes, reject',
        cancelButtonText: 'Cancel',
      }).then((result) => {
        if (result.isConfirmed) {
          // Show loading popup
          Swal.fire({
            title: 'Processing...',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
          updateApproval(id, 'reject', btn);
        }
      });
    });
  });

  function updateApproval(id, action, btn) {
    fetch(window.location.href, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({ id, action }),
    })
    .then(res => res.json())
    .then(data => {
      Swal.close(); // Close loading popup before showing the result
      if (data.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: data.message,
          timer: 1500,
          timerProgressBar: true,
          showConfirmButton: false,
        }).then(() => {
          location.reload(); // Reload the page after SweetAlert closes
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.message,
        });
      }
    })
    .catch(() => {
      Swal.close(); // Close loading popup on error
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Something went wrong. Please try again.',
      });
    });
  }

});
</script>
