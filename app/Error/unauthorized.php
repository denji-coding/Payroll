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
    // Auto-detect user type and redirect if possible
    document.addEventListener('DOMContentLoaded', function() {
      // Check if there are any session remnants
      const sessionCheck = fetch('index.php?payroll=test_session')
        .then(response => response.json())
        .then(data => {
          if (data.session_status === 'active' && data.session_data) {
            // Try to determine user type from session data
            if (data.session_data.SESSION_EMAIL) {
              goToAdminLogin();
            } else if (data.session_data.manager_id) {
              goToManagerLogin();
            } else if (data.session_data.employee_id || data.session_data.employee_no) {
              goToEmployeeLogin();
            }
          }
        })
        .catch(error => {
          console.log('No active session detected');
        });
    });

    function goToAdminLogin() {
      window.location.href = 'index.php?payroll=login1&type=admin';
    }

    function goToManagerLogin() {
      window.location.href = 'index.php?payroll=login_manager';
    }

    function goToEmployeeLogin() {
      window.location.href = 'index.php?payroll=login1&type=employee';
    }

    // Show a helpful message
    Swal.fire({
      title: 'Session Expired',
      text: 'Your session has expired. Please log in again to continue.',
      icon: 'warning',
      confirmButtonText: 'OK',
      confirmButtonColor: '#3085d6'
    });
  </script>

</body>
</html>
