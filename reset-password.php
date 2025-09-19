<?php
require_once __DIR__ . '/app/core/secure_session.php';
require_once __DIR__ . '/app/core/password_reset.php';

$token = $_GET['token'] ?? '';
$valid = $token ? pr_find_token($token) : null;
$userType = $valid ? $valid['user_type'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <script>
    // Fallback: if local SweetAlert2 failed to load, load from CDN
    (function ensureSwal(){
        if (typeof Swal === 'undefined') {
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            document.head.appendChild(s);
        }
    })();
    </script>
</head>
<body class="relative min-h-screen flex items-center justify-center bg-center bg-no-repeat bg-cover px-4" style="background-image: url('public/assets/image/image_bg.jpg');">
    <!-- Soft light blur overlay -->
    <div class="absolute inset-0 bg-white/10 backdrop-blur-sm z-0"></div>
    
    <!-- Back Button -->
    <div class="absolute top-4 left-4 z-20">
        <button onclick="goBack()" class="flex items-center gap-2 px-4 py-2 bg-white/90 hover:bg-white text-gray-700 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
            <i class="bi bi-arrow-left" style="font-size: 1.2rem;"></i>
            <!-- <span class="text-sm font-medium"></span> -->
        </button>
    </div>

    <!-- Reset Password Card -->
    <div class="w-full max-w-md bg-white bg-opacity-95 p-8 rounded-2xl shadow-xl relative z-10 transition-all duration-300">
        <div class="flex flex-col items-center mb-6">
            <!-- Lock Icon -->
            <i class="bi bi-shield-lock" style="color: green; font-size: 2.9rem;"></i>
            <!-- Title -->
            <div class="text-center mt-3">
                <span class="text-xl font-semibold">Reset Password</span>
                <p class="text-sm text-gray-600">Set a new password for your <?php echo $userType === 'manager' ? 'manager' : 'employee'; ?> account</p>
            </div>
        </div>
        <hr>

        <?php if (!$valid): ?>
            <div class="p-4 rounded bg-red-50 text-red-700 text-sm text-center">
                <i class="bi bi-exclamation-triangle mr-2"></i>
                Invalid or expired reset link.
            </div>
        <?php else: ?>
            <form id="resetForm" class="space-y-4" method="post" action="/mvcPayroll/app/api/perform-password-reset.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($userType, ENT_QUOTES, 'UTF-8'); ?>" />
                
                <!-- New Password Input -->
                <div class="mb-3">
                    <label class="block mb-1 text-sm font-bold text-[#403E43]">New Password</label>
                    <div class="relative mb-3">
                        <input type="password" 
                            name="password" 
                            id="newPassword"
                            required 
                            minlength="8" 
                            class="w-full pl-12 pr-12 py-3 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43] border border-gray-300 rounded-lg
                                focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 
                                focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2" 
                            placeholder="Enter new password">
                        <!-- Lock icon on the left -->
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                            <i class="bi bi-lock" style="font-size: 1.4rem; color: #396A39;"></i>
                        </div>
                        <!-- Eye icon on the right -->
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer" onclick="togglePassword('newPassword')">
                            <i class="bi bi-eye-slash" style="font-size: 1.2rem; color: #396A39;"></i>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password Input -->
                <div class="mb-3">
                    <label class="block mb-1 text-sm font-bold text-[#403E43]">Confirm Password</label>
                    <div class="relative">
                        <input type="password" 
                            name="confirm" 
                            id="confirmPassword"
                            required 
                            minlength="8" 
                            class="w-full pl-12 pr-12 py-3 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43] border border-gray-300 rounded-lg
                                focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 
                                focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2" 
                            placeholder="Confirm new password">
                        <!-- Lock icon on the left -->
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                            <i class="bi bi-lock-fill" style="font-size: 1.4rem; color: #396A39;"></i>
                        </div>
                        <!-- Eye icon on the right -->
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer" onclick="togglePassword('confirmPassword')">
                            <i class="bi bi-eye-slash" style="font-size: 1.2rem; color: #396A39;"></i>
                        </div>
                    </div>
                </div>

                <!-- Reset Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transform hover:scale-[1.01] transition-all duration-100 flex items-center justify-center gap-2 mt-4 focus:outline-none focus:ring-2 focus:ring-green-300 focus:ring-opacity-50">
                    <i class="bi bi-shield-check" style="font-size: 1.2rem;"></i>
                    <span class="text-md font-medium">Reset Password</span>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Back button functionality
        function goBack() {
            // Determine the correct login page based on user type
            const userType = '<?php echo $userType; ?>';
            if (userType === 'manager') {
                window.location.href = '/mvcPayroll/public/index.php?payroll=login1&type=manager';
            } else if (userType === 'employee') {
                window.location.href = '/mvcPayroll/public/index.php?payroll=login1&type=employee';
            } else {
                // Fallback to manager login
                window.location.href = '/mvcPayroll/public/index.php?payroll=login1&type=manager';
            }
        }

        // Toggle password visibility
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const parentDiv = passwordField.parentElement;
            const icon = parentDiv.querySelector('.bi-eye-slash, .bi-eye');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        }

        // Handle form submission with SweetAlert
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const formData = new FormData(this);
            const password = formData.get('password');
            const confirm = formData.get('confirm');
            
            // Validate passwords match
            if (password !== confirm) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Passwords do not match. Please try again.',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true,
                });
                return;
            }
            
            // Validate password length
            if (password.length < 8) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Too Short',
                    text: 'Password must be at least 8 characters long.',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true,
                });
                return;
            }
            
            // Show loading state and allow form to submit
            Swal.fire({
                title: 'Resetting Password...',
                text: 'Please wait while we update your password.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    </script>
</body>
</html>












