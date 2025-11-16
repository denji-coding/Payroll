<?php
// Start session to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Collect debug information
$debugInfo = [
    'session_id' => session_id(),
    'employee_id' => $_SESSION['employee_id'] ?? 'NOT SET',
    'employee_no' => $_SESSION['employee_no'] ?? 'NOT SET',
    'manager_id' => $_SESSION['manager_id'] ?? 'NOT SET',
    'SESSION_USER_ID' => $_SESSION['SESSION_USER_ID'] ?? 'NOT SET',
    'can_access_employee_portal' => $_SESSION['can_access_employee_portal'] ?? 'NOT SET',
    'role_id' => $_SESSION['role_id'] ?? 'NOT SET',
    'role_name' => $_SESSION['role_name'] ?? 'NOT SET',
    'manager_name' => $_SESSION['manager_name'] ?? 'NOT SET',
    'manager_email' => $_SESSION['manager_email'] ?? 'NOT SET',
    'SESSION_EMAIL' => $_SESSION['SESSION_EMAIL'] ?? 'NOT SET',
    'all_session_keys' => array_keys($_SESSION ?? [])
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Access Denied</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .debug-panel {
      max-height: 300px;
      overflow-y: auto;
      font-family: monospace;
      font-size: 12px;
    }
  </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="text-center max-w-2xl bg-white p-8 rounded-xl shadow-lg">

    <!-- Icon Animation (No external fetch) -->
    <div class="mx-auto w-24 h-24 mb-4 flex items-center justify-center rounded-full bg-red-100">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-red-600 animate-bounce">
        <path fill-rule="evenodd" d="M10.5 3.75a3.75 3.75 0 00-3.75 3.75V9H6A2.25 2.25 0 003.75 11.25v7.5A2.25 2.25 0 006 21h12a2.25 2.25 0 002.25-2.25v-7.5A2.25 2.25 0 0018 9h-.75V7.5a3.75 3.75 0 00-7.5 0V9H10.5V7.5a2.25 2.25 0 114.5 0V9H10.5z" clip-rule="evenodd" />
      </svg>
    </div>

    <h1 class="text-6xl font-bold text-red-600">403</h1>
    <h2 class="text-2xl mt-4 text-gray-800 font-semibold">Access Denied</h2>
    <p class="text-gray-600 mt-2">
      You do not have permission to view this page.<br>Please log in as a employee.
    </p>

    <!-- Debug Information Panel -->
    <div class="mt-6 text-left bg-gray-50 p-4 rounded-lg border border-gray-200">
      <h3 class="text-sm font-bold text-gray-700 mb-2">Debug Information (Check Browser Console for more details)</h3>
      <div class="debug-panel bg-white p-3 rounded border border-gray-300">
        <pre id="debugOutput" class="text-xs"><?php echo htmlspecialchars(json_encode($debugInfo, JSON_PRETTY_PRINT)); ?></pre>
      </div>
      <button onclick="copyDebugInfo()" class="mt-2 text-xs px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
        Copy Debug Info
      </button>
      <button onclick="toggleDebugPanel()" class="mt-2 ml-2 text-xs px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600">
        Toggle Panel
      </button>
    </div>

    <a href="index.php?payroll=login1&type=employee" class="mt-6 inline-block px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
      Return to Login
    </a>
  </div>

  <script>
    // Debug information
    const debugInfo = <?php echo json_encode($debugInfo, JSON_PRETTY_PRINT); ?>;
    
    console.group('🔴 403 Access Denied - Debug Information');
    console.log('Session ID:', debugInfo.session_id);
    console.log('Employee ID:', debugInfo.employee_id);
    console.log('Employee No:', debugInfo.employee_no);
    console.log('Manager ID:', debugInfo.manager_id);
    console.log('HR/Admin ID (SESSION_USER_ID):', debugInfo.SESSION_USER_ID);
    console.log('Can Access Employee Portal:', debugInfo.can_access_employee_portal);
    console.log('Role ID:', debugInfo.role_id);
    console.log('Role Name:', debugInfo.role_name);
    console.log('Manager Name:', debugInfo.manager_name);
    console.log('Manager Email:', debugInfo.manager_email);
    console.log('HR/Admin Email:', debugInfo.SESSION_EMAIL);
    console.log('All Session Keys:', debugInfo.all_session_keys);
    console.log('Full Session Data:', <?php echo json_encode($_SESSION ?? []); ?>);
    console.groupEnd();
    
    // Analysis
    console.group('📊 Analysis');
    if (debugInfo.manager_id !== 'NOT SET') {
      console.log('✅ Manager ID is set:', debugInfo.manager_id);
      if (debugInfo.can_access_employee_portal === 'NOT SET' || debugInfo.can_access_employee_portal == 0) {
        console.error('❌ Problem: Manager authenticated but can_access_employee_portal is NOT SET or is 0');
        console.log('   - This means the role permissions were not set in the session');
        console.log('   - Check if role_id is set:', debugInfo.role_id);
      } else {
        console.log('✅ can_access_employee_portal is set to:', debugInfo.can_access_employee_portal);
      }
    } else if (debugInfo.SESSION_USER_ID !== 'NOT SET') {
      console.log('✅ HR/Admin ID is set:', debugInfo.SESSION_USER_ID);
      if (debugInfo.can_access_employee_portal === 'NOT SET' || debugInfo.can_access_employee_portal == 0) {
        console.error('❌ Problem: HR/Admin authenticated but can_access_employee_portal is NOT SET or is 0');
        console.log('   - This means the role permissions were not set in the session');
        console.log('   - Check if role_id is set:', debugInfo.role_id);
      } else {
        console.log('✅ can_access_employee_portal is set to:', debugInfo.can_access_employee_portal);
      }
    } else if (debugInfo.employee_id !== 'NOT SET' || debugInfo.employee_no !== 'NOT SET') {
      console.log('✅ Employee ID/No is set');
    } else {
      console.error('❌ Problem: No user session variables are set');
      console.log('   - This means authentication did not complete successfully');
      console.log('   - Check the login process and session setup');
    }
    console.groupEnd();
    
    // Recommendations
    console.group('💡 Recommendations');
    if (debugInfo.manager_id !== 'NOT SET' && (debugInfo.can_access_employee_portal === 'NOT SET' || debugInfo.can_access_employee_portal == 0)) {
      console.log('1. Manager is authenticated but role permissions are missing');
      console.log('2. Check if the manager has a role_id assigned in the database');
      console.log('3. Check if the role has can_access_employee_portal = 1');
      console.log('4. Check the secureLogin() function to ensure role data is being set');
    } else if (debugInfo.SESSION_USER_ID !== 'NOT SET' && (debugInfo.can_access_employee_portal === 'NOT SET' || debugInfo.can_access_employee_portal == 0)) {
      console.log('1. HR/Admin is authenticated but role permissions are missing');
      console.log('2. Check if the HR/Admin has a role_id assigned in the database');
      console.log('3. Check if the role has can_access_employee_portal = 1');
      console.log('4. Check the secureLogin() function to ensure role data is being set');
    } else if (debugInfo.manager_id === 'NOT SET' && debugInfo.SESSION_USER_ID === 'NOT SET' && debugInfo.employee_id === 'NOT SET') {
      console.log('1. No authentication session found');
      console.log('2. Try logging in again');
      console.log('3. Check if the login process completed successfully');
      console.log('4. Check the error logs for authentication errors');
    }
    console.groupEnd();
    
    function copyDebugInfo() {
      const debugText = JSON.stringify(debugInfo, null, 2);
      navigator.clipboard.writeText(debugText).then(() => {
        alert('Debug information copied to clipboard!');
      }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy. Check console for details.');
      });
    }
    
    function toggleDebugPanel() {
      const panel = document.getElementById('debugOutput').parentElement;
      if (panel.style.display === 'none') {
        panel.style.display = 'block';
      } else {
        panel.style.display = 'none';
      }
    }
  </script>

</body>
</html>