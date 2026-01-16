<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include session helper for isAdminLoggedIn function
require_once __DIR__ . '/../core/session_helper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Access Denied</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="text-center max-w-md bg-white p-8 rounded-xl shadow-lg">

    <!-- Lottie Animation -->
    <lottie-player
      src="https://assets2.lottiefiles.com/packages/lf20_touohxv0.json"
      background="transparent"
      speed="1"
      style="width: 150px; height: 150px; margin: 0 auto;"
      loop
      autoplay>
    </lottie-player>

    <h1 class="text-6xl font-bold text-red-600">403</h1>
    <h2 class="text-2xl mt-4 text-gray-800 font-semibold">Access Denied</h2>
    <p class="text-gray-600 mt-2 mb-6">
      You do not have permission to view this page or your session has expired.<br>
      Please log in again to continue.
    </p>

    <div class="space-y-3">
      <button onclick="goToAdminLogin()" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
        <i class="fas fa-user-shield mr-2"></i>Admin/HR Login
      </button>
      
      <button onclick="goToManagerLogin()" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
        <i class="fas fa-user-tie mr-2"></i>Manager Login
      </button>
      
      <button onclick="goToEmployeeLogin()" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200">
        <i class="fas fa-user mr-2"></i>Employee Login
      </button>
    </div>

    <div class="mt-6 text-sm text-gray-500">
      <p>If you believe this is an error, please contact your system administrator.</p>
    </div>
  </div>

  <script>
    // No automatic redirect - let user choose manually
    document.addEventListener('DOMContentLoaded', function() {
      console.log('🔴 403 Access Denied - Debug Information');
      console.log('==========================================');
      
      // Session Information
      console.log('📋 Session Information:');
      console.log('  Session ID:', '<?php echo session_id(); ?>');
      console.log('  SESSION_EMAIL:', '<?php echo $_SESSION['SESSION_EMAIL'] ?? 'NOT SET'; ?>');
      console.log('  SESSION_USER_ID:', '<?php echo $_SESSION['SESSION_USER_ID'] ?? 'NOT SET'; ?>');
      console.log('  USERNAME:', '<?php echo $_SESSION['USERNAME'] ?? 'NOT SET'; ?>');
      console.log('  user_type:', '<?php echo $_SESSION['user_type'] ?? 'NOT SET'; ?>');
      console.log('  user_id:', '<?php echo $_SESSION['user_id'] ?? 'NOT SET'; ?>');
      console.log('  manager_id:', '<?php echo $_SESSION['manager_id'] ?? 'NOT SET'; ?>');
      console.log('  employee_id:', '<?php echo $_SESSION['employee_id'] ?? 'NOT SET'; ?>');
      console.log('  owner_id:', '<?php echo $_SESSION['owner_id'] ?? 'NOT SET'; ?>');
      
      // Authentication Status
      console.log('🔐 Authentication Status:');
      console.log('  isAdminLoggedIn():', '<?php echo (function_exists("isAdminLoggedIn") && isAdminLoggedIn()) ? "TRUE" : "FALSE"; ?>');
      console.log('  isManagerLoggedIn():', '<?php echo (function_exists("isManagerLoggedIn") && isManagerLoggedIn()) ? "TRUE" : "FALSE"; ?>');
      console.log('  isEmployeeLoggedIn():', '<?php echo (function_exists("isEmployeeLoggedIn") && isEmployeeLoggedIn()) ? "TRUE" : "FALSE"; ?>');
      console.log('  isOwnerLoggedIn():', '<?php echo (function_exists("isOwnerLoggedIn") && isOwnerLoggedIn()) ? "TRUE" : "FALSE"; ?>');
      
      // Cookies
      console.log('🍪 Cookie Information:');
      console.log('  Session Cookie Name:', '<?php echo session_name(); ?>');
      const sessionCookie = document.cookie.split(';').find(c => c.trim().startsWith('<?php echo session_name(); ?>='));
      console.log('  Session Cookie Value:', sessionCookie || 'NOT FOUND');
      if (sessionCookie) {
        const cookieValue = sessionCookie.split('=')[1];
        console.log('  Extracted Cookie Value:', cookieValue);
        console.log('  Cookie matches Session ID:', cookieValue === '<?php echo session_id(); ?>' ? 'YES ✅' : 'NO ❌');
      }
      console.log('  All Cookies:', document.cookie);
      console.log('  Cookie Count:', document.cookie ? document.cookie.split(';').length : 0);
      
      // Session Data
      console.log('📦 All Session Keys:', <?php echo json_encode(array_keys($_SESSION ?? [])); ?>);
      console.log('📦 Full Session Data:', <?php echo json_encode($_SESSION ?? []); ?>);
      
      // Analysis
      console.log('📊 Analysis:');
      const sessionEmail = '<?php echo $_SESSION['SESSION_EMAIL'] ?? ''; ?>';
      const sessionUserId = '<?php echo $_SESSION['SESSION_USER_ID'] ?? ''; ?>';
      const isAdmin = '<?php echo (function_exists("isAdminLoggedIn") && isAdminLoggedIn()) ? "TRUE" : "FALSE"; ?>';
      
      if (sessionEmail && sessionEmail !== 'NOT SET' && sessionUserId && sessionUserId !== 'NOT SET') {
        console.log('  ✅ Session data is present (SESSION_EMAIL and SESSION_USER_ID are set)');
        if (isAdmin === 'FALSE') {
          console.log('  ❌ BUT isAdminLoggedIn() returns FALSE - This is the problem!');
          console.log('  💡 Possible causes:');
          console.log('     - Session cookie not being sent with request');
          console.log('     - Session data not being loaded from cookie');
          console.log('     - Session path/domain mismatch');
        } else {
          console.log('  ✅ isAdminLoggedIn() returns TRUE - Session should be valid');
        }
      } else {
        console.log('  ❌ Session data is missing (SESSION_EMAIL or SESSION_USER_ID not set)');
        console.log('  💡 Possible causes:');
        console.log('     - Session was cleared during redirect');
        console.log('     - Session cookie not being sent');
        console.log('     - Session expired or invalid');
      }
      
      console.log('==========================================');
    });

    function goToAdminLogin() {
      window.location.href = 'index.php?payroll=login_admin';
    }

    function goToManagerLogin() {
      window.location.href = 'index.php?payroll=login1&type=manager';
    }

    function goToEmployeeLogin() {
      window.location.href = 'index.php?payroll=login1&type=employee';
    }

    // Show a helpful message without auto-redirect
    Swal.fire({
      title: 'Session Expired',
      text: 'Your session has expired. Please select your login type below.',
      icon: 'warning',
      confirmButtonText: 'OK',
      confirmButtonColor: '#3085d6',
      allowOutsideClick: true,
      allowEscapeKey: true
    });
  </script>

</body>
</html>
