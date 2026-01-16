<?php
$title = "Owner Login";
require_once views_path("partials/header");
echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';
$loginType = 'owner';

if (isset($_SESSION['error'])) {
    $msg = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

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

<div class="relative min-h-screen flex items-center justify-center bg-center bg-no-repeat bg-cover px-4" style="background-image: url('../public/assets/image/image_bg.jpg');">
  <!-- Soft light blur overlay -->
  <div class="absolute inset-0 bg-white/10 backdrop-blur-sm z-0"></div>

  <!-- Login Card -->
  <div class="w-full max-w-md bg-white bg-opacity-95 p-8 rounded-2xl shadow-xl relative z-10 transition-all duration-300">
    <div class="flex flex-col items-center mb-6">
      <!-- Owner Icon -->
      <i class="bi bi-person-circle" style="color: green; font-size: 2.9rem;"></i>
      <!-- Title -->
      <div class="text-center mt-3">
        <span class="text-xl font-semibold">Migrants Venture Corporation</span>
        <p>Owner Login Page</p>
      </div>
    </div>

    <!-- Login Form -->
    <form id="ownerLoginForm" action="" method="post" data-aos="fade-up" data-aos-delay="60" style="display: <?= $loginType === 'owner' ? 'block' : 'none' ?>;">
      <input type="hidden" name="login_type" value="owner">

      <!-- Email Input -->
      <div class="mb-4">
        <label class="ml-2 block mb-1 text-sm font-bold text-[#403E43]" data-aos="fade-up">Email</label>
        <div class="relative mb-3" data-aos="fade-up" data-aos-delay="50">
          <input
            type="email"
            class="form-control form-control-lg ps-5 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43] focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2 w-full rounded"
            name="o_email"
            placeholder="Enter your email address"
            value="<?php echo isset($_POST['o_email']) ? htmlspecialchars($_POST['o_email']) : ''; ?>"  
            required
          >
          <i class="bi bi-envelope absolute top-1/2 left-3 -translate-y-1/2" style="color: #396A39; font-size: 1.4rem;"></i>
        </div>
      </div>

      <!-- Password Input -->
      <div class="mb-4">
        <label class="ml-2 block mb-1 text-sm font-bold text-[#403E43]" data-aos="fade-up">Password</label>
        <div class="relative" data-aos="fade-up" data-aos-delay="50">
          <input
            type="password"
            class="form-control form-control-lg ps-5 text-sm bg-[#eaf5ea] placeholder:text-sm text-[#403E43] focus:bg-[#eaf5ea] focus:border-green-500 focus:ring-1 focus:ring-green-200 focus:outline-none focus:outline-2 focus:outline-green-500 focus:outline-offset-2 w-full rounded"
            name="o_password"
            id="ownerPassword"
            placeholder="Enter your password"
            required
          >
          <!-- Lock Icon -->
          <span class="absolute top-1/2 left-3 -translate-y-1/2">
            <i class="bi bi-lock" style="font-size: 1.4rem; color: #396A39;"></i>
          </span>
          <!-- Eye Toggle Icon -->
          <span class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer" onclick="togglePassword(event, 'ownerPassword')">
            <i class="bi bi-eye-slash" style="font-size: 1.2rem; color: #396A39;"></i>
          </span>
        </div>
      </div>

      <!-- Submit Button -->
      <button type="submit" name="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transform hover:scale-[1.01] transition-all duration-100 flex items-center justify-center gap-2 mt-4 focus:outline-none focus:ring-2 focus:ring-green-300 focus:ring-opacity-50">
        <svg id="ownerSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"></path>
        </svg>
        <span id="ownerLoginText" class="text-md font-medium">Log In</span>
        <span id="ownerLoggingInText" class="hidden">Logging in...</span>
      </button>
    </form>
  </div>
</div>

<!-- Password Toggle Script -->
<script>
  function togglePassword(event, fieldId) {
    const passwordField = document.getElementById(fieldId);
    const icon = event.currentTarget.querySelector('i');

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
  document.getElementById('ownerLoginForm').addEventListener('submit', function() {
    document.getElementById('ownerSpinner').classList.remove('hidden');
    document.getElementById('ownerLoginText').classList.add('hidden');
    document.getElementById('ownerLoggingInText').classList.remove('hidden');
    
    // Store email for potential error handling
    const email = document.querySelector('input[name="o_email"]').value;
    sessionStorage.setItem('ownerEmail', email);
  });

  // Function to restore email and clear password on error
  function restoreEmailOnError() {
    // Restore owner email if exists
    const ownerEmail = sessionStorage.getItem('ownerEmail');
    if (ownerEmail) {
      document.querySelector('input[name="o_email"]').value = ownerEmail;
      sessionStorage.removeItem('ownerEmail');
    }
    
    // Clear password field
    document.getElementById('ownerPassword').value = '';
  }

  // Call this function when page loads to restore email if there was an error
  document.addEventListener('DOMContentLoaded', function() {
    // Clear any existing session storage data
    sessionStorage.clear();
    localStorage.removeItem('sidebar-collapsed');
    
    restoreEmailOnError();
  });
</script>

<?php
require_once views_path("partials/footer");
?>

<script>
// Show logout toast if cookie flag is present, then clear it
document.addEventListener('DOMContentLoaded', function () {
  try {
    const cookies = document.cookie.split(';').map(c => c.trim());
    const flag = cookies.find(c => c.startsWith('owner_logged_out='));
    if (flag && flag.split('=')[1] === '1') {
      // Clear the cookie
      document.cookie = 'owner_logged_out=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Logged out',
        text: 'You have been logged out successfully.',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
      });
    }
  } catch (e) {}
});
</script>
