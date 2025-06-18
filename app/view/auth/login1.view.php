<?php
$title = "Login"; // Set the page title
require_once views_path("partials/header"); // Include the header partial

if (isset($_SESSION['error'])) {
    $msg = $_SESSION['error'];
    unset($_SESSION['error']);
}
// Determine which form to show initially (default to admin)
$loginType = $_GET['type'] ?? 'admin';
?>
<!-- <script>
  // Reset the sidebar collapse state on fresh login
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('reset_sidebar') === 'true') {
    localStorage.removeItem('sidebar-collapsed');
  }

  // Apply collapse if saved
  if (localStorage.getItem('sidebar-collapsed') === 'true') {
    document.documentElement.classList.add('sidebar-collapsed');
  }
</script> -->

<!-- Show logout toast if logged out -->
<?php if ($loggedOut): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Logged Out!',
        text: 'You have been successfully logged out.',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    });
});
</script>
<?php endif; ?>

 <?php if (isset($msg) && $msg != ""): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: <?php echo json_encode($msg); ?>,
                    timer: 3000, // shows for 3 seconds
                    timerProgressBar: true,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true,
                });
            });
            </script>
        <?php endif; ?>

        <style>
            
    #adminLoginForm,
    #employeeLoginForm {
        display: none;
    }
</style>

      

        <script>
  // Reset the sidebar collapse state on fresh login
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('reset_sidebar') === 'true') {
    localStorage.removeItem('sidebar-collapsed');
  }

  // Apply collapse if saved
  if (localStorage.getItem('sidebar-collapsed') === 'true') {
    document.documentElement.classList.add('sidebar-collapsed');
  }
</script>

<main class="min-h-screen flex items-center justify-center p-4">
<div class="login-container mb-[10px] mt-[15px] d-md-flex ">
    <!-- Left side image container -->
    <div class="login-left col-md-6">
        <img src="../public/assets/image/image.svg" alt="Login Illustration">
    </div>
    
    <!-- Right side login form -->
    <div class="login-right col-md-6">
        <div class="text-center -mt-7">
            <!-- User icon -->
            <i class="bi bi-person-circle -mt-7" style="color: green; font-size: 2.9rem;"></i>
            <!-- System name and subtitle -->
            <div class="texxt-center">
                <span class="text-xl font-semibol">Migrants Venture</span>
                <p class="p">HRM & Payroll Management System</p>
            </div>
        </div>
        <hr>

        <!-- Login type toggle -->
        <!-- Toggle Buttons -->
        <div class="text-center mt-4 mb-4">
            <div class="inline-flex rounded-lg overflow-hidden border-1 border-green-600">
                <button 
                    type="button" 
                    id="adminLoginBtn" 
                    class="px-4 py-1 font-semibold text-sm transition-all duration-300 bg-green-600 text-white hover:bg-green-700 focus:outline-none"
                    onclick="toggleLoginForm('admin')"
                >
                    Admin Login
                </button>
                <button 
                    type="button" 
                    id="employeeLoginBtn" 
                    class="px-4 py-1 font-semibold text-sm transition-all duration-300 bg-white text-green-600 hover:bg-green-100 focus:outline-none"
                    onclick="toggleLoginForm('employee')"
                >
                    Employees
                </button>
            </div>
        </div>


        <!-- Login instructions -->
        <!-- <h5 class="text-center mt-3" data-aos="fade-up">Login to your account</h5>
        <p class="text-center" data-aos="fade-up" data-aos-delay="50">Enter your credentials to access the system</p> -->

        <!-- Display error message if available -->

       <form id="adminLoginForm" action="" method="post" data-aos="fade-up" data-aos-delay="60" style="display: <?= $loginType === 'admin' ? 'block' : 'none' ?>;">
    <input type="hidden" name="login_type" value="admin">
    
    <!-- Email input -->
    <div class="mb-3">
        <label class="block mb-1 text-sm font-bold text-[#403E43]" data-aos="fade-up">Admin Email</label>
        <div class="position-relative mb-3" data-aos="fade-up" data-aos-delay="50">
            <input type="email" 
                class="form-control form-control-lg ps-5 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43]
                    focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 
                    focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2" 
                name="email" placeholder="Enter admin email" 
                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            <i class="bi bi-envelope position-absolute top-50 start-0 translate-middle-y ps-3" 
            style="color: #396A39; font-size: 1.4rem;" data-aos="fade-up" data-aos-delay="70"></i>
        </div>
    </div>

    <!-- Password input -->
    <div class="mb-3">
        <label class="block mb-1 text-sm font-bold text-[#403E43]" data-aos="fade-up">Password</label>
        <div class="position-relative" data-aos="fade-up" data-aos-delay="50">
            <input type="password" 
                class="form-control form-control-lg ps-5 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43]
                    focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 
                    focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2" 
                name="password" id="adminPassword" placeholder="Enter your password" required>
            <span class="position-absolute top-50 start-0 translate-middle-y ps-3" data-aos="fade-up" data-aos-delay="70">
                <i class="bi bi-lock" style="font-size: 1.4rem; color: #396A39;"></i>
            </span>
            <span class="position-absolute top-50 end-0 translate-middle-y pe-3" onclick="togglePassword('adminPassword')" style="cursor: pointer;" data-aos="fade-up" data-aos-delay="90">
                <i class="bi bi-eye-slash" style="font-size: 1.2rem; color: #396A39;"></i>
            </span>
        </div>
    </div>

    <!-- Forgot password link -->
    <!-- <div class="forgot-password" data-aos="fade-up" data-aos-delay="90">
        <a href="forgot-password.php">Forgot Password?</a>
    </div> -->

    <!-- Login button -->
    <button type="submit" name="submit" class="btn btn-login btn-lg bg-[#12823A] text-white hover:bg-[#128d3d] p-2 flex items-center justify-center gap-2 mt-6" data-aos="fade-up" data-aos-delay="100">
        <svg id="adminSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"></path>
        </svg>
        <span id="adminLoginText" class="text-lg">Admin Login</span>
        <span id="adminLoggingInText" class="hidden">Logging in...</span>
    </button>
</form>

<!-- Employee Login Form -->
<form id="employeeLoginForm" action="index.php?payroll=login1" method="post" data-aos="fade-up" data-aos-delay="60" style="display: <?= $loginType === 'employee' ? 'block' : 'none' ?>;">
    <input type="hidden" name="login_type" value="employee">
    
    <!-- Email input -->
    <div class="mb-3">
        <label class="block mb-1 text-sm font-bold text-[#403E43]" data-aos="fade-up">Email</label>
        <div class="position-relative mb-3" data-aos="fade-up" data-aos-delay="50">
            <input type="email" 
                class="form-control form-control-lg ps-5 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43]
                    focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 
                    focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2" 
                name="email" placeholder="Enter your email address" 
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
            <i class="bi bi-envelope position-absolute top-50 start-0 translate-middle-y ps-3" 
               style="color: #396A39; font-size: 1.4rem;" data-aos="fade-up" data-aos-delay="70"></i>
        </div>
    </div>

    <!-- Password input (employee_no) -->
    <div class="mb-3">
        <label class="block mb-1 text-sm font-bold text-[#403E43]" data-aos="fade-up">Password</label>
        <div class="position-relative" data-aos="fade-up" data-aos-delay="50">
            <input type="password" 
                class="form-control form-control-lg ps-5 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43]
                    focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 
                    focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2" 
                name="password" id="employeePassword" placeholder="Enter your password" required>
            <span class="position-absolute top-50 start-0 translate-middle-y ps-3" data-aos="fade-up" data-aos-delay="70">
                <i class="bi bi-lock" style="font-size: 1.4rem; color: #396A39;"></i>
            </span>
            <span class="position-absolute top-50 end-0 translate-middle-y pe-3" onclick="togglePassword('employeePassword')" style="cursor: pointer;">
                <i class="bi bi-eye-slash" style="font-size: 1.2rem; color: #396A39;"></i>
            </span>
        </div>
    </div>

    <!-- Forgot password link -->
    <div class="forgot-password" data-aos="fade-up" data-aos-delay="90">
        <a href="forgot-password.php?type=employee">Forgot Password?</a>
    </div>

    <!-- Login button -->
    <button type="submit" name="submit" class="btn btn-login btn-lg bg-[#12823A] text-white hover:bg-[#128d3d] p-2 flex items-center justify-center gap-2" data-aos="fade-up" data-aos-delay="100">
        <svg id="employeeSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"></path>
        </svg>
        <span id="employeeLoginText">Employee Login</span>
        <span id="employeeLoggingInText" class="hidden">Logging in...</span>
    </button>
</form>


<!-- Link to registration page -->
<!-- <div class="register-text" data-aos="fade-up" data-aos-delay="110">
    Don't have an account? <a href="index.php?payroll=register" class="text-decoration-none">Register</a>
</div> -->

    </div>
</div>
</main>

<script>
// Initialize form display based on URL parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const loginType = urlParams.get('type') || 'admin';
    toggleLoginForm(loginType, false);
});

function toggleLoginForm(type, updateUrl = true) {
    const adminForm = document.getElementById('adminLoginForm');
    const employeeForm = document.getElementById('employeeLoginForm');
    const adminBtn = document.getElementById('adminLoginBtn');
    const employeeBtn = document.getElementById('employeeLoginBtn');

    // Toggle forms
    adminForm.style.display = type === 'admin' ? 'block' : 'none';
    employeeForm.style.display = type === 'employee' ? 'block' : 'none';

    // Update form actions with current type
    adminForm.action = "index.php?payroll=login1&type=admin";
    employeeForm.action = "index.php?payroll=login1&type=employee";

    // Update button styles
    if (type === 'admin') {
        // Set admin as active
        adminBtn.className = `px-4 py-1 font-semibold text-sm transition-all duration-300
                           bg-green-600 text-white hover:bg-green-700 focus:outline-none`;
        
        // Set employee as inactive
        employeeBtn.className = `px-4 py-1 font-semibold text-sm transition-all duration-300
                              bg-white text-green-600 hover:bg-green-100 focus:outline-none`;
    } else {
        // Set employee as active
        employeeBtn.className = `px-4 py-1 font-semibold text-sm transition-all duration-300
                              bg-green-600 text-white hover:bg-green-700 focus:outline-none`;
        
        // Set admin as inactive
        adminBtn.className = `px-4 py-1 font-semibold text-sm transition-all duration-300
                           bg-white text-green-600 hover:bg-green-100 focus:outline-none`;
    }

    // Reset AOS for the active form
    resetAOS(type === 'admin' ? adminForm : employeeForm);

    if (updateUrl) {
        const url = new URL(window.location);
        url.searchParams.set('type', type);
        window.history.pushState({}, '', url);
    }

    AOS.refreshHard();
}

// Remove previous animation class so it re-runs
function resetAOS(container) {
    const elements = container.querySelectorAll('[data-aos]');

    elements.forEach((el, index) => {
        el.setAttribute('data-aos', 'fade-up');
        el.setAttribute('data-aos-delay', `${60 + index * 20}`); // staggered delay
        el.classList.remove('aos-animate');
    });

    setTimeout(() => {
        AOS.refreshHard();
    }, 50);
}


// Toggle password visibility
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    // Get the parent div with class "position-relative"
    const parentDiv = passwordField.parentElement;
    // Find the eye icon within this parent div
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

// Handle form submission spinners and text
document.getElementById('adminLoginForm').addEventListener('submit', function() {
    document.getElementById('adminSpinner').classList.remove('hidden');
    document.getElementById('adminLoginText').classList.add('hidden');
    document.getElementById('adminLoggingInText').classList.remove('hidden');
});

document.getElementById('employeeLoginForm').addEventListener('submit', function() {
    document.getElementById('employeeSpinner').classList.remove('hidden');
    document.getElementById('employeeLoginText').classList.add('hidden');
    document.getElementById('employeeLoggingInText').classList.remove('hidden');
});
</script>


<!-- Include the footer partial -->
<?php require_once views_path("partials/footer"); ?>