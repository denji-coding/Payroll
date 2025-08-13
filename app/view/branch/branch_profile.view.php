<?php
$title = "My Profile";
require_once views_path("partials/header");

echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

// Database connection and manager data fetching
require_once '../app/core/database.php';
$conn = new Database();
$pdo = $conn->getConnection();

// Function to format name with first letter of each word capitalized
function formatName($name) {
    return ucwords(strtolower(trim($name)));
}

// Fetch manager details
$managers = [];
if (isset($_SESSION['manager_id'])) {
    $stmt = $pdo->prepare("SELECT m_first_name, m_middle_name, m_last_name, m_email, m_branch, m_photo_path, m_sex, m_created_at, m_updated_at FROM managers WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['manager_id']]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($manager) {
        // Format the name with proper capitalization
        $firstName = formatName($manager['m_first_name'] ?? '');
        $middleName = $manager['m_middle_name'] ?? '';
        $lastName = formatName($manager['m_last_name'] ?? '');
        
        $managers = [
            'name' => trim($firstName . ' ' . ($middleName ? formatName($middleName)[0] . '. ' : '') . $lastName),
            'email' => $manager['m_email'] ?? '',
            'branch' => $manager['m_branch'] ?? '',
            'photo_path' => $manager['m_photo_path'] ?? '',
            'sex' => $manager['m_sex'] ?? '',
            'created_at' => $manager['m_created_at'] ?? '',
            'updated_at' => $manager['m_updated_at'] ?? ''
        ];
    }
}
?>

<div class="flex min-h-screen overflow-hidden">
    <!-- Main content -->
    <main id="mainContent" class="flex-1 p-6 bg-gray-100 transition-margin duration-300 ease-in-out" style="margin-left: 256px;">
        <?php require_once views_path("branch/branch_sidebar"); ?>

        <div class="mb-6">
            <span class="text-3xl font-bold tracking-tight text-gray-800">My Profile</span>
            <p class="text-gray-600 mt-2">View and manage your personal information and account settings.</p>
        </div>

        <!-- Profile Header Card -->
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
            <!-- Profile Header with Background -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-8 py-6">
                <div class="flex flex-col items-center text-center space-y-4">
                    <!-- Profile Image -->
                    <div class="relative">
                        <img src="<?= !empty($managers['photo_path']) ? '../public/upload/' . (strpos($managers['photo_path'], 'upload/') === 0 ? substr($managers['photo_path'], 7) : $managers['photo_path']) : ($managers['sex'] == 'F' ? '../public/assets/image/default_women.png' : '../public/assets/image/default_men.png') ?>"
                             alt="Profile Photo"
                             class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover"
                             onerror="this.onerror=null;this.src='<?= $managers['sex'] == 'F' ? '../public/assets/image/default_women.png' : '../public/assets/image/default_men.png' ?>';">
                        <div class="absolute -bottom-2 -right-2 bg-green-500 w-6 h-6 rounded-full border-2 border-white"></div>
                    </div>

                    <!-- Manager Name and Branch -->
                    <div class="text-white">
                        <h1 class="text-2xl font-bold profile-header-name"><?= htmlspecialchars($managers['name'] ?? 'N/A') ?></h1>
                        <p class="text-emerald-100 text-lg"><?= htmlspecialchars(ucwords($managers['branch'] ?? 'N/A')) ?> Branch</p>
                        <p class="text-emerald-100">Manager</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 mt-4">
                        <!-- <a href="index.php?payroll=edit_profile" class="inline-flex items-center bg-white hover:bg-gray-100 text-emerald-700 font-semibold px-6 py-2 rounded-lg shadow-sm transition-colors duration-200">
                            <i class="bi bi-pencil-fill mr-2"></i> Edit Profile
                        </a> -->
                        <button onclick="openChangePasswordModal()" class="inline-flex items-center bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-6 py-2 rounded-lg shadow-sm transition-colors duration-200">
                            <i class="bi bi-key-fill mr-2"></i> Change Password
                        </button>
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Personal Information -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-semibold text-gray-800 border-b border-gray-200 pb-2">Personal Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Full Name</label>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 flex items-center justify-between group">
                                    <p id="fullNameDisplay" class="text-gray-800 font-medium" data-original="<?= htmlspecialchars($managers['name'] ?? 'N/A') ?>"><?= htmlspecialchars($managers['name'] ?? 'N/A') ?></p>
                                    <input type="text" id="fullNameInput" value="<?= htmlspecialchars($managers['name'] ?? 'N/A') ?>" 
                                           class="hidden w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                           oninput="toggleSaveButton('fullName')">
                                    <div class="flex items-center space-x-2">
                                        <button id="fullNameEditBtn" onclick="toggleEdit('fullName')" 
                                                class="text-gray-400 hover:text-emerald-600 transition-colors duration-200">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button id="fullNameSaveBtn" onclick="saveEdit('fullName')" 
                                                class="hidden text-green-600 hover:text-green-700 transition-colors duration-200">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button id="fullNameCancelBtn" onclick="cancelEdit('fullName')" 
                                                class="hidden text-red-600 hover:text-red-700 transition-colors duration-200">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Email Address</label>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 flex items-center justify-between group">
                                    <p id="emailDisplay" class="text-gray-800" data-original="<?= htmlspecialchars($managers['email'] ?? 'N/A') ?>"><?= htmlspecialchars($managers['email'] ?? 'N/A') ?></p>
                                    <input type="email" id="emailInput" value="<?= htmlspecialchars($managers['email'] ?? 'N/A') ?>" 
                                           class="hidden w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                           oninput="toggleSaveButton('email')">
                                    <div class="flex items-center space-x-2">
                                        <button id="emailEditBtn" onclick="toggleEdit('email')" 
                                                class="text-gray-400 hover:text-emerald-600 transition-colors duration-200">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button id="emailSaveBtn" onclick="saveEdit('email')" 
                                                class="hidden text-green-600 hover:text-green-700 transition-colors duration-200">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button id="emailCancelBtn" onclick="cancelEdit('email')" 
                                                class="hidden text-red-600 hover:text-red-700 transition-colors duration-200">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Branch Assignment</label>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 font-medium"><?= htmlspecialchars(ucwords($managers['branch'] ?? 'N/A')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-semibold text-gray-800 border-b border-gray-200 pb-2">Account Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Account Status</label>
                                <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                                    <span class="inline-flex items-center text-green-800">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        Active
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Member Since</label>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800"><?= isset($managers['created_at']) ? htmlspecialchars(date("F d, Y", strtotime($managers['created_at']))) : 'N/A' ?></p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Last Updated</label>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <p class="text-gray-800"><?= isset($managers['updated_at']) ? htmlspecialchars(date("F d, Y", strtotime($managers['updated_at']))) : 'N/A' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Modal -->
        <div id="changePasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Change Password</h3>
                    </div>
                    
                    <form id="changePasswordForm" class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Current Password</label>
                                <div class="relative">
                                    <input type="password" id="currentPassword" name="currentPassword" required 
                                           class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    <button type="button" onclick="togglePassword('currentPassword')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="bi bi-eye" id="currentPasswordEye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">New Password</label>
                                <div class="relative">
                                    <input type="password" id="newPassword" name="newPassword" required 
                                           class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    <button type="button" onclick="togglePassword('newPassword')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="bi bi-eye" id="newPasswordEye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Confirm New Password</label>
                                <div class="relative">
                                    <input type="password" id="confirmPassword" name="confirmPassword" required 
                                           class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    <button type="button" onclick="togglePassword('confirmPassword')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="bi bi-eye" id="confirmPasswordEye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 mt-6">
                            <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                Update Password
                            </button>
                            <button type="button" onclick="closeChangePasswordModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

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
        const response = await fetch('../app/api/change_password-api.php', {
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

// New functions for editable fields
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
        // Store original value for potential cancellation
        inputElement.value = displayElement.textContent;
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
            const response = await fetch('../app/api/update_profile-api.php', {
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
                let formattedValue = originalValue;
                
                // Format the name if it's the fullName field
                if (fieldId === 'fullName') {
                    formattedValue = formatName(originalValue);
                }
                
                // Update display and hide edit mode
                displayElement.textContent = formattedValue;
                displayElement.setAttribute('data-original', formattedValue);
                inputElement.classList.add('hidden');
                displayElement.classList.remove('hidden');
                editBtn.classList.remove('hidden');
                saveBtn.classList.add('hidden');
                cancelBtn.classList.add('hidden');

                // Update sidebar display if it's the name field
                if (fieldId === 'fullName') {
                    updateSidebarName(formattedValue);
                }

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

    // Function to update sidebar name display
    function updateSidebarName(formattedName) {
        // Update the sidebar name display if it exists
        const sidebarNameElement = document.querySelector('.sidebar-manager-name');
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

        // Revert to original value (get from the original display text)
        const originalValue = displayElement.getAttribute('data-original') || displayElement.textContent;
        inputElement.value = originalValue;
        displayElement.textContent = originalValue;
        
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

    // Function to toggle save button visibility based on input changes
    function toggleSaveButton(fieldId) {
        const inputElement = document.getElementById(`${fieldId}Input`);
        const displayElement = document.getElementById(`${fieldId}Display`);
        const saveBtn = document.getElementById(`${fieldId}SaveBtn`);
        
        if (!inputElement || !displayElement || !saveBtn) return;
        
        const currentValue = inputElement.value.trim();
        const originalValue = displayElement.getAttribute('data-original') || '';
        
        // Show save button only if there are changes
        if (currentValue !== originalValue) {
            saveBtn.classList.remove('hidden');
        } else {
            saveBtn.classList.add('hidden');
        }
    }
</script>
