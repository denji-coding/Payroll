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
        <span class="text-xl font-semibold">Migrants Venture Corporation</span>
        <p>Manager and Employee Login Page</p>
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

                name="email" id="managerEmail" placeholder="Enter your email" 
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

    <div class="forgot-password" data-aos="fade-up" data-aos-delay="90">
        <a href="#" onclick="openForgot('manager');return false;">Forgot Password?</a>
    </div>

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

                name="email" id="employeeEmail" placeholder="Enter your email" 
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

        <a href="#" onclick="openForgot('employee');return false;">Forgot Password?</a>
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
    // Store email for potential error handling
    const email = document.getElementById('employeeEmail').value;
    sessionStorage.setItem('employeeEmail', email);
    
    document.getElementById('employeeSpinner').classList.remove('hidden');
    document.getElementById('employeeLoginText').classList.add('hidden');
    document.getElementById('employeeLoggingInText').classList.remove('hidden');
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

// Forgot password modal flow
let fpUserType = 'employee';
function openForgot(type){
    fpUserType = type;
    const modal = document.getElementById('fpModal');
    const modalContent = modal.querySelector('div');
    
    // Show modal with animation
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    }, 10);
}
function closeForgot(){
    const modal = document.getElementById('fpModal');
    const modalContent = modal.querySelector('div');
    
    // Hide modal with animation
    modal.classList.add('opacity-0');
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        // Reset form state
        document.getElementById('fpStepEmail').classList.remove('hidden');
        document.getElementById('fpStepCode').classList.add('hidden');
        document.getElementById('fpEmailManager').value = '';
        document.getElementById('fpEmailEmployee').value = '';
        document.getElementById('fpCode').value = '';
        document.getElementById('fpToken').value = '';
    }, 300);
}

function setLoadingState(buttonId, spinnerId, textId, isLoading) {
    const button = document.getElementById(buttonId);
    const spinner = document.getElementById(spinnerId);
    const text = document.getElementById(textId);
    
    if (isLoading) {
        button.disabled = true;
        spinner.classList.remove('hidden');
        text.textContent = 'Processing...';
    } else {
        button.disabled = false;
        spinner.classList.add('hidden');
        if (buttonId === 'fpSendBtn') {
            text.textContent = 'Send Code';
        } else if (buttonId === 'fpVerifyBtn') {
            text.textContent = 'Verify';
        }
    }
}

async function requestReset(){
    const email = (fpUserType==='manager'?document.getElementById('fpEmailManager'):document.getElementById('fpEmailEmployee')).value.trim();
    if(!email) {
        alert('Please enter your email address');
        return;
    }
    
    // Set loading state
    setLoadingState('fpSendBtn', 'fpSendSpinner', 'fpSendText', true);
    
    try {
        const fd = new FormData();
        fd.append('csrf_token', window.csrfToken || '');
        fd.append('user_type', fpUserType);
        fd.append('email', email);
        
        
        const res = await fetch('../app/api/request-password-reset.php',{method:'POST',body:fd,credentials:'same-origin'});
        const json = await res.json();
        
        if(json.success){
            document.getElementById('fpToken').value = json.token;
            
            // Animate step transition
            const emailStep = document.getElementById('fpStepEmail');
            const codeStep = document.getElementById('fpStepCode');
            
            emailStep.style.opacity = '0';
            emailStep.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                emailStep.classList.add('hidden');
                codeStep.classList.remove('hidden');
                codeStep.style.opacity = '0';
                codeStep.style.transform = 'translateX(20px)';
                
                setTimeout(() => {
                    codeStep.style.opacity = '1';
                    codeStep.style.transform = 'translateX(0)';
                }, 50);
            }, 200);
        } else {
            alert(json.message||'Request failed');
        }
    } catch (error) {
        console.error('Request reset error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        // Reset loading state
        setLoadingState('fpSendBtn', 'fpSendSpinner', 'fpSendText', false);
    }
}

async function verifyCode(){
    const code = document.getElementById('fpCode').value.trim();
    const token = document.getElementById('fpToken').value;
    
    
    if(!code || code.length !== 6) {
        alert('Please enter a valid 6-digit code');
        return;
    }
    
    if(!token) {
        alert('No token found. Please start the process again.');
        return;
    }
    
    // Set loading state
    setLoadingState('fpVerifyBtn', 'fpVerifySpinner', 'fpVerifyText', true);
    
    try {
        const fd = new FormData();
        fd.append('csrf_token', window.csrfToken || '');
        fd.append('token', token);
        fd.append('code', code);
        
        const res = await fetch('../app/api/verify-password-reset.php',{method:'POST',body:fd,credentials:'same-origin'});
        
        console.log('Response status:', res.status);
        console.log('Response headers:', res.headers);
        
        const responseText = await res.text();
        console.log('Response text:', responseText);
        
        let json;
        try {
            json = JSON.parse(responseText);
            console.log('Parsed JSON:', json);
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Invalid response from server: ' + responseText);
            return;
        }
        
        if(json.success){
            // Show success message before redirect
            const verifyBtn = document.getElementById('fpVerifyBtn');
            const verifyText = document.getElementById('fpVerifyText');
            verifyText.textContent = 'Success! Redirecting...';
            
            setTimeout(() => {
                window.location.href = '/mvcPayroll/reset-password.php?token=' + encodeURIComponent(token);
            }, 1000);
        } else {
            alert(json.message||'Verification failed');
        }
    } catch (error) {
        console.error('Verify code error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        // Reset loading state
        setLoadingState('fpVerifyBtn', 'fpVerifySpinner', 'fpVerifyText', false);
    }
}
</script>

<!-- Forgot Password Modal -->
<div id="fpModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 transition-all duration-300 opacity-0">
  <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 transform transition-all duration-300 scale-95">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-800">Forgot Password</h3>
      <button onclick="closeForgot()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200 p-1 rounded-full hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <input type="hidden" id="fpToken" />
    <div id="fpStepEmail" class="space-y-4 transition-all duration-300">
      <p class="text-sm text-gray-600">Enter your email. We will send a verification code.</p>
      
      <!-- Manager Email Input -->
      <div class="position-relative" style="display:none;" id="fpEmailManagerDiv">
        <input type="email" 
            id="fpEmailManager" 
            placeholder="Manager email" 
            class="form-control form-control-lg ps-5 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43]
                focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 
                focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2" 
            required>
        <i class="bi bi-envelope position-absolute top-50 start-0 translate-middle-y ps-3" 
           style="color: #396A39; font-size: 1.4rem;"></i>
      </div>
      
      <!-- Employee Email Input -->
      <div class="position-relative" id="fpEmailEmployeeDiv">
        <input type="email" 
            id="fpEmailEmployee" 
            placeholder="Employee email" 
            class="form-control form-control-lg ps-5 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43]
                focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 
                focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2" 
            required>
        <i class="bi bi-envelope position-absolute top-50 start-0 translate-middle-y ps-3" 
           style="color: #396A39; font-size: 1.4rem;"></i>
      </div>
      <div class="flex gap-3 justify-end">
        <button class="px-6 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors duration-200" onclick="closeForgot()">Cancel</button>
        <button id="fpSendBtn" class="px-6 py-2 rounded-lg bg-[#16a249] text-white hover:bg-[#138a3e] transition-colors duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" onclick="requestReset()">
          <svg id="fpSendSpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"></path>
          </svg>
          <span id="fpSendText">Send Code</span>
        </button>
      </div>
      <script>
        // Toggle visible email input based on selected user type
        const toggleEmailField = () => {
          document.getElementById('fpEmailManagerDiv').style.display = fpUserType==='manager'?'block':'none';
          document.getElementById('fpEmailEmployeeDiv').style.display = fpUserType==='employee'?'block':'none';
        };
        const _openForgotRef = openForgot;
        openForgot = function(type){ fpUserType = type; _openForgotRef(type); toggleEmailField(); };
      </script>
    </div>
    <div id="fpStepCode" class="space-y-4 hidden transition-all duration-300">
      <p class="text-sm text-gray-600">Enter the 6-digit verification code sent to your email.</p>
      
      <!-- Verification Code Input -->
      <div class="position-relative">
        <input type="text" 
            id="fpCode" 
            maxlength="6" 
            placeholder="000000" 
            class="form-control form-control-lg ps-5 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43] tracking-widest text-center
                focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 
                focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2" 
            required>
        <i class="bi bi-shield-check position-absolute top-50 start-0 translate-middle-y ps-3" 
           style="color: #396A39; font-size: 1.4rem;"></i>
      </div>
      <div class="flex gap-3 justify-end">
        <button class="px-6 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors duration-200" onclick="closeForgot()">Cancel</button>
        <button id="fpVerifyBtn" class="px-6 py-2 rounded-lg bg-[#16a249] text-white hover:bg-[#138a3e] transition-colors duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" onclick="verifyCode()">
          <svg id="fpVerifySpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"></path>
          </svg>
          <span id="fpVerifyText">Verify</span>
        </button>
      </div>
    </div>
  </div>
</div>


<!-- Include the footer partial -->

            </span>

            <span class="position-absolute top-50 end-0 translate-middle-y pe-3" onclick="togglePassword('employeePassword')" style="cursor: pointer;">

                <i class="bi bi-eye-slash" style="font-size: 1.2rem; color: #396A39;"></i>

            </span>

        </div>

    </div>
</form>

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



// Duplicate handler removed - using the one above with debug logging

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
    console.log('=== EMPLOYEE LOGIN PAGE LOAD DEBUG ===');
    console.log('Current URL:', window.location.href);
    console.log('URL params:', new URLSearchParams(window.location.search).toString());
    
    // Check if this is a redirect back from failed login
    const loginAttempt = sessionStorage.getItem('employeeLoginAttempt');
    if (loginAttempt === 'true') {
        console.log('⚠️ Previous login attempt detected');
        console.log('Login attempt time:', sessionStorage.getItem('employeeLoginTime'));
        
        // Check cookies after redirect
        const cookies = document.cookie.split(';').reduce((acc, cookie) => {
            const [key, value] = cookie.trim().split('=');
            acc[key] = value;
            return acc;
        }, {});
        console.log('Cookies after redirect:', cookies);
        console.log('MVC_PAYROLL_SESS cookie:', cookies['MVC_PAYROLL_SESS'] || 'NOT SET');
        console.log('PHPSESSID cookie:', cookies['PHPSESSID'] || 'NOT SET');
        
        // Check for error message
        const errorMsg = document.querySelector('.alert-danger, [class*="error"]');
        if (errorMsg) {
            console.log('❌ Error message found:', errorMsg.textContent);
        }
        
        // Check if we're on login page (failed redirect)
        if (window.location.href.includes('login1')) {
            console.log('❌ Still on login page - login likely failed');
            console.log('Check PHP error logs for server-side errors');
        } else {
            console.log('✅ Redirected away from login page');
        }
        
        sessionStorage.removeItem('employeeLoginAttempt');
    } else {
        console.log('✅ Fresh page load (no previous login attempt)');
    }
    
    // Check session storage
    console.log('Session storage:', {
        employeeEmail: sessionStorage.getItem('employeeEmail'),
        managerEmail: sessionStorage.getItem('managerEmail')
    });
    
    restoreEmailOnError();
});

</script>






<!-- Include the footer partial -->


<?php require_once views_path("partials/footer"); ?>