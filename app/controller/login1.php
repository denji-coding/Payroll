<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../app/core/SecureAuth.php";
require_once "../app/core/secure_session.php";

// Ensure session is started
startSecureSession();

$auth = new SecureAuth();
$msg = "";


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Don't reset session completely - just clear old login data
    // This preserves any debug data we might need
    if (isset($_SESSION['employee_auth_debug'])) {
        $preservedDebug = $_SESSION['employee_auth_debug'];
        error_log("Preserved debug data from previous attempt");
    }
    
    // Clear only login-related session data, not the entire session
    unset($_SESSION['SESSION_USER_ID'], $_SESSION['SESSION_EMAIL'], $_SESSION['USERNAME']);
    unset($_SESSION['employee_id'], $_SESSION['employee_no'], $_SESSION['manager_id']);
    unset($_SESSION['login_success'], $_SESSION['error']);
    
    // Restore preserved debug if it exists
    if (isset($preservedDebug)) {
        $_SESSION['employee_auth_debug'] = $preservedDebug;
    }
    
    $loginType = $_POST['login_type'] ?? 'manager'; // get login type
    error_log("Login type from POST: " . $loginType);

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Email and password are required.';
        header("Location: index.php?payroll=login1&type=$loginType"); // preserve login type
        exit;
    }

    try {
        if ($loginType === 'manager') {
            // Manager login logic
            $result = $auth->authenticateManager($email, $password);
            
            if ($result['success']) {
                header("Location: " . $result['redirect']);
                exit;
            } else {
                $_SESSION['error'] = $result['message'];
            }

        } elseif ($loginType === 'employee') {
            // Employee login logic
            $result = $auth->authenticateEmployee($email, $password);
            
            error_log("Session after employee auth: " . json_encode([
                'can_access_employee_portal' => $_SESSION['can_access_employee_portal'] ?? 'NOT SET',
                'manager_id' => $_SESSION['manager_id'] ?? 'NOT SET',
                'SESSION_USER_ID' => $_SESSION['SESSION_USER_ID'] ?? 'NOT SET',
                'employee_id' => $_SESSION['employee_id'] ?? 'NOT SET',
                'role_id' => $_SESSION['role_id'] ?? 'NOT SET'
            ]));
            
            if ($result['success']) {
                // Employees always have access to the employee portal
                $sessionDebug = [
                    'employee_id' => $_SESSION['employee_id'] ?? 'NOT SET',
                    'employee_no' => $_SESSION['employee_no'] ?? 'NOT SET',
                    'can_access_employee_portal' => $_SESSION['can_access_employee_portal'] ?? 'NOT SET',
                    'session_id' => session_id(),
                    'session_name' => session_name(),
                    'session_status' => session_status(),
                    'cookie_set' => isset($_COOKIE['MVC_PAYROLL_SESS']),
                    'cookie_value' => $_COOKIE['MVC_PAYROLL_SESS'] ?? 'NOT SET'
                ];
                error_log("Employee auth success. Session data before redirect: " . json_encode($sessionDebug));
                
                // Output debug info as HTML comment for console inspection
                echo "<!-- EMPLOYEE_LOGIN_DEBUG: " . json_encode($sessionDebug) . " -->\n";
                
                error_log("Redirecting to user_dashboard");
                header("Location: index.php?payroll=user_dashboard");
                exit;
            } else {
                error_log("Employee auth failed, trying manager...");
            // If employee login fails, try manager login
            $result = $auth->authenticateManager($email, $password);
                error_log("Manager auth result: " . json_encode($result));
                error_log("Session after manager auth: " . json_encode([
                    'can_access_employee_portal' => $_SESSION['can_access_employee_portal'] ?? 'NOT SET',
                    'manager_id' => $_SESSION['manager_id'] ?? 'NOT SET',
                    'role_id' => $_SESSION['role_id'] ?? 'NOT SET'
                ]));
                
            if ($result['success']) {
                    // Check if manager has employee portal access
                    $canAccess = isset($_SESSION['can_access_employee_portal']) && $_SESSION['can_access_employee_portal'] == 1;
                    error_log("Manager auth success. can_access_employee_portal: " . ($canAccess ? 'YES' : 'NO'));
                    if ($canAccess) {
                        error_log("Redirecting to user_dashboard");
                header("Location: index.php?payroll=user_dashboard");
                exit;
                    } else {
                        error_log("ERROR: Manager authenticated but no employee portal access");
                        $_SESSION['error'] = 'You do not have access to the employee portal.';
            }
                } else {
                    error_log("Manager auth failed, trying HR/Admin...");
            // If manager login fails, try HR/Admin login
            $result = $auth->authenticateAdmin($email, $password);
                    error_log("HR/Admin auth result: " . json_encode($result));
                    error_log("Session after HR/Admin auth: " . json_encode([
                        'can_access_employee_portal' => $_SESSION['can_access_employee_portal'] ?? 'NOT SET',
                        'SESSION_USER_ID' => $_SESSION['SESSION_USER_ID'] ?? 'NOT SET',
                        'role_id' => $_SESSION['role_id'] ?? 'NOT SET'
                    ]));
                    
            if ($result['success']) {
                        // Check if HR has employee portal access
                        $canAccess = isset($_SESSION['can_access_employee_portal']) && $_SESSION['can_access_employee_portal'] == 1;
                        error_log("HR/Admin auth success. can_access_employee_portal: " . ($canAccess ? 'YES' : 'NO'));
                        if ($canAccess) {
                            error_log("Redirecting to user_dashboard");
                header("Location: index.php?payroll=user_dashboard");
                exit;
                        } else {
                            error_log("ERROR: HR/Admin authenticated but no employee portal access");
                            $_SESSION['error'] = 'You do not have access to the employee portal.';
                        }
                    } else {
            // All login attempts failed
                        error_log("ERROR: All authentication attempts failed");
            $_SESSION['error'] = 'Invalid email or password.';
                    }
                }
            }
            error_log("=== EMPLOYEE LOGIN DEBUG END ===");

        } else {
            $_SESSION['error'] = 'Invalid login type.';
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = 'Authentication error. Please try again.';
    }

    // Redirect back with error and correct login type
    header("Location: index.php?payroll=login1&type=$loginType");
    exit;
}

// Handle debug clear request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_debug'])) {
    unset($_SESSION['employee_auth_debug']);
    exit('OK');
}

// Show logout message if set
$loggedOut = false;
if (!empty($_SESSION['logged_out'])) {
    $loggedOut = true;
    unset($_SESSION['logged_out']);
}

// Determine active form type for the view
$loginType = $_GET['type'] ?? 'manager'; // used in view to show correct form

// $_SESSION['login_success'] = true;
// $_SESSION['username'] = $employee['first_name'] . ' ' . $employee['last_name'];

// header('Location: index.php?payroll=user_dashboard');
// exit;


require views_path("auth/login1");
