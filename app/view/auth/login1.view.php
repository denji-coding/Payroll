<?php
$title = "Login"; // Set the page title
require_once views_path("partials/header"); // Include the header partial

if (isset($_SESSION['error'])) {
    $msg = $_SESSION['error'];
    unset($_SESSION['error']);
}
// Determine which form to show initially (default to manager)
$loginType = $_GET['type'] ?? 'manager';
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
    #managerLoginForm,
    #employeeLoginForm {
        display: none;
    }
    
    /* Ensure proper form styling within the new card layout */
    .form-control {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
    }
    
    .form-control:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
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

<main class="relative min-h-screen flex items-center justify-center bg-center bg-no-repeat bg-cover px-4" style="background-image: url('../public/assets/image/image_bg.jpg');">
  <!-- Soft light blur overlay -->
  <div class="absolute inset-0 bg-white/10 backdrop-blur-sm z-0"></div>

  <!-- Login Card -->
  <div class="w-full max-w-md bg-white bg-opacity-95 p-8 rounded-2xl shadow-xl relative z-10 transition-all duration-300">
    <div class="flex flex-col items-center mb-6">
      <!-- User Icon -->
      <i class="bi bi-person-circle" style="color: green; font-size: 2.9rem;"></i>
      <!-- Title -->
      <div class="text-center mt-3">
        <span class="text-xl font-semibold">Migrants Venture</span>
        <p>HRM & Payroll Management System</p>
            </div>
        </div>
        <hr>

        <!-- Login type toggle -->
        <!-- Toggle Buttons -->
        <div class="text-center mt-4 mb-4">
             <div class="inline-flex rounded-xl overflow-hidden shadow-sm border border-green-200 bg-white">
                <button 
                    type="button" 
                    id="managerLoginBtn" 
                      class="px-4 py-2 font-semibold text-sm transition-all duration-300 bg-green-600 text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300 focus:ring-opacity-50 transform hover:scale-105"
                    onclick="toggleLoginForm('manager')"
                >
                    Manager
                </button>
                <button 
                    type="button" 
                    id="employeeLoginBtn" 
                      class="px-4 py-2 font-semibold text-sm transition-all duration-300 bg-white text-green-600 hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-300 focus:ring-opacity-50 transform hover:scale-105"
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

       <form id="managerLoginForm" action="" method="post" data-aos="fade-up" data-aos-delay="60" style="display: <?= $loginType === 'manager' ? 'block' : 'none' ?>;">
    <input type="hidden" name="login_type" value="manager">
    
    <!-- Email input -->
    <div class="mb-3">
        <label class="block mb-1 text-sm font-bold text-[#403E43]" data-aos="fade-up">Email</label>
        <div class="position-relative mb-3" data-aos="fade-up" data-aos-delay="50">
            <input type="email" 
                class="form-control form-control-lg ps-5 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43]
                    focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 
                    focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2" 
                name="email" id="managerEmail" placeholder="Enter your email address" 
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
                name="password" id="managerPassword" placeholder="Enter your password" required>
            <span class="position-absolute top-50 start-0 translate-middle-y ps-3" data-aos="fade-up" data-aos-delay="70">
                <i class="bi bi-lock" style="font-size: 1.4rem; color: #396A39;"></i>
            </span>
            <span class="position-absolute top-50 end-0 translate-middle-y pe-3" onclick="togglePassword('managerPassword')" style="cursor: pointer;" data-aos="fade-up" data-aos-delay="90">
                <i class="bi bi-eye-slash" style="font-size: 1.2rem; color: #396A39;"></i>
            </span>
        </div>
    </div>

    <!-- Forgot password link -->
    <!-- <div class="forgot-password" data-aos="fade-up" data-aos-delay="90">
        <a href="forgot-password.php">Forgot Password?</a>
    </div> -->

    <!-- Login button -->
       <button type="submit" name="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transform hover:scale-[1.01] transition-all duration-100 flex items-center justify-center gap-2 mt-4 focus:outline-none focus:ring-2 focus:ring-green-300 focus:ring-opacity-50">
        <svg id="managerSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"></path>
        </svg>
         <span id="managerLoginText" class="text-md font-medium">Manager Login</span>
        <span id="managerLoggingInText" class="hidden">Logging in...</span>
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
                name="email" id="employeeEmail" placeholder="Enter your email address" 
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
       <button type="submit" name="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transform hover:scale-[1.01] transition-all duration-100 flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-green-300 focus:ring-opacity-50">
        <svg id="employeeSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"></path>
        </svg>
         <span id="employeeLoginText" class="text-md font-medium">Employee Login</span>
        <span id="employeeLoggingInText" class="hidden">Logging in...</span>
    </button>
</form>


<!-- Link to registration page -->
<!-- <div class="register-text" data-aos="fade-up" data-aos-delay="110">
    Don't have an account? <a href="index.php?payroll=register" class="text-decoration-none">Register</a>
</div> -->

</div>
</main>

<script>
// Initialize form display based on URL parameter
document.addEventListener('DOMContentLoaded', function() {
    // Clear any existing session storage data
    sessionStorage.clear();
    localStorage.removeItem('sidebar-collapsed');
    
    const urlParams = new URLSearchParams(window.location.search);
    const loginType = urlParams.get('type') || 'manager';
    toggleLoginForm(loginType, false);
});

function toggleLoginForm(type, updateUrl = true) {
    const managerForm = document.getElementById('managerLoginForm');
    const employeeForm = document.getElementById('employeeLoginForm');
    const managerBtn = document.getElementById('managerLoginBtn');
    const employeeBtn = document.getElementById('employeeLoginBtn');

    // Toggle forms
    managerForm.style.display = type === 'manager' ? 'block' : 'none';
    employeeForm.style.display = type === 'employee' ? 'block' : 'none';

    // Update form actions with current type
    managerForm.action = "index.php?payroll=login1&type=manager";
    employeeForm.action = "index.php?payroll=login1&type=employee";

    // Update button styles
    if (type === 'manager') {
        // Set manager as active
        managerBtn.className = `px-4 py-1 font-semibold text-sm transition-all duration-300
                           bg-green-600 text-white hover:bg-green-700 focus:outline-none`;
        
        // Set employee as inactive
        employeeBtn.className = `px-4 py-1 font-semibold text-sm transition-all duration-300
                              bg-white text-green-600 hover:bg-green-100 focus:outline-none`;
    } else {
        // Set employee as active
        employeeBtn.className = `px-4 py-1 font-semibold text-sm transition-all duration-300
                              bg-green-600 text-white hover:bg-green-700 focus:outline-none`;
        
        // Set manager as inactive
        managerBtn.className = `px-4 py-1 font-semibold text-sm transition-all duration-300
                           bg-white text-green-600 hover:bg-green-100 focus:outline-none`;
    }

    // Reset AOS for the active form
    resetAOS(type === 'manager' ? managerForm : employeeForm);

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
document.getElementById('managerLoginForm').addEventListener('submit', function() {
    document.getElementById('managerSpinner').classList.remove('hidden');
    document.getElementById('managerLoginText').classList.add('hidden');
    document.getElementById('managerLoggingInText').classList.remove('hidden');
    
    // Store email for potential error handling
    const email = document.getElementById('managerEmail').value;
    sessionStorage.setItem('managerEmail', email);
});

document.getElementById('employeeLoginForm').addEventListener('submit', function() {
    document.getElementById('employeeSpinner').classList.remove('hidden');
    document.getElementById('employeeLoginText').classList.add('hidden');
    document.getElementById('employeeLoggingInText').classList.remove('hidden');
    
    // Store email for potential error handling
    const email = document.getElementById('employeeEmail').value;
    sessionStorage.setItem('employeeEmail', email);
});

// Function to restore email and clear password on error
function restoreEmailOnError() {
    // Restore manager email if exists
    const managerEmail = sessionStorage.getItem('managerEmail');
    if (managerEmail) {
        document.getElementById('managerEmail').value = managerEmail;
        sessionStorage.removeItem('managerEmail');
    }
    
    // Restore employee email if exists
    const employeeEmail = sessionStorage.getItem('employeeEmail');
    if (employeeEmail) {
        document.getElementById('employeeEmail').value = employeeEmail;
        sessionStorage.removeItem('employeeEmail');
    }
    
    // Clear all password fields
    document.getElementById('managerPassword').value = '';
    document.getElementById('employeePassword').value = '';
}

// Call this function when page loads to restore email if there was an error
document.addEventListener('DOMContentLoaded', function() {
    restoreEmailOnError();
});
</script>


<!-- Include the footer partial -->
<?php require_once views_path("partials/footer"); ?>