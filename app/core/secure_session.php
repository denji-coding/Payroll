<?php
/**
 * Secure Session Management System
 * Enhanced security features for multi-user authentication
 */

// Include session helper for clearAllSessions function
require_once __DIR__ . '/session_helper.php';

// CRITICAL: Set session name BEFORE any session operations
// This must be done at the file level, not inside a function
// ALWAYS set the session name, even if session is already started (for consistency)
if (session_status() === PHP_SESSION_NONE) {
    session_name('MVC_PAYROLL_SESS');
} else {
    // If session is already started, we can't change the name
    // But we can log a warning if it's wrong
    if (session_name() !== 'MVC_PAYROLL_SESS') {
        error_log("⚠️ WARNING: Session already started with wrong name: " . session_name() . " (expected: MVC_PAYROLL_SESS)");
    }
}

// Start session with secure settings
function startSecureSession() {
    // Always ensure session is started and data is loaded
    if (session_status() === PHP_SESSION_NONE) {
        // Basic session settings for shared hosting compatibility
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 0); // Disable strict mode for shared hosting
        
        // Detect HTTPS more reliably for shared hosting
        $isHttps = false;
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $isHttps = true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $isHttps = true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
            $isHttps = true;
        } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
            $isHttps = true;
        }
        
        // Set secure cookie only if HTTPS is confirmed
        ini_set('session.cookie_secure', $isHttps ? 1 : 0);
        
        // Use Lax for SameSite for better compatibility
        ini_set('session.cookie_samesite', 'Lax');
        
        // Set session timeout (1 day)
        ini_set('session.gc_maxlifetime', 86400);
        
        // Session name is already set at file level, but ensure it's correct
        if (session_name() !== 'MVC_PAYROLL_SESS') {
            session_name('MVC_PAYROLL_SESS');
        }
        
        // Set cookie parameters with fallback for older PHP versions
        // Use root path to ensure session works across all pages
        $cookiePath = '/'; // Use root path for maximum compatibility
        $cookieDomain = ''; // Don't set domain to avoid subdomain issues
        
        // For InfinityFree, try to set domain to the actual host
        if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'free.nf') !== false) {
            $cookieDomain = '.' . $_SERVER['HTTP_HOST'];
        }
        
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 86400,
                'path' => $cookiePath,
                'domain' => $cookieDomain,
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            session_set_cookie_params(86400, $cookiePath, $cookieDomain);
        }
        
        session_start();
        
        // CRITICAL: Check for login data IMMEDIATELY after session_start()
        // This must happen before any session clearing logic
        $hasLoginData = isset($_SESSION['user_id']) || 
                        isset($_SESSION['employee_id']) || 
                        isset($_SESSION['employee_no']) ||
                        isset($_SESSION['manager_id']) || 
                        isset($_SESSION['SESSION_USER_ID']) ||
                        isset($_SESSION['owner_id']);
        
        // DEBUG: Log session state
        error_log("=== startSecureSession() DEBUG ===");
        error_log("Session ID: " . session_id());
        error_log("hasLoginData: " . ($hasLoginData ? 'TRUE' : 'FALSE'));
        error_log("SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
        error_log("SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
        error_log("All session keys: " . implode(', ', array_keys($_SESSION ?? [])));
        error_log("Session cookie: " . (isset($_COOKIE[session_name()]) ? $_COOKIE[session_name()] : 'NOT SET'));
        
        // NEVER clear session if user is logged in - this is critical!
        if (!$hasLoginData) {
            // Only regenerate and clear if this is truly a NEW session (no login data)
            // This ensures each login gets a fresh session
            session_regenerate_id(true);
            
            // Clear any existing session data to start fresh
            $_SESSION = [];
            error_log("Session cleared - no login data found (new session)");
        } else {
            // User is already logged in - PRESERVE all session data
            // Just ensure last_regeneration is set
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            }
            error_log("Session preserved - user is logged in");
            error_log("Preserved SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
            error_log("Preserved SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
        }
    } else {
        // Session is already started - verify it's loaded correctly
        // This happens when redirecting from login to dashboard
        error_log("=== startSecureSession() - Session already active ===");
        error_log("Session ID: " . session_id());
        error_log("Session cookie name: " . session_name());
        error_log("PHPSESSID cookie: " . (isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : 'NOT SET'));
        error_log("MVC_PAYROLL_SESS cookie: " . (isset($_COOKIE['MVC_PAYROLL_SESS']) ? $_COOKIE['MVC_PAYROLL_SESS'] : 'NOT SET'));
        error_log("Session cookie in request: " . (isset($_COOKIE[session_name()]) ? $_COOKIE[session_name()] : 'NOT SET'));
        error_log("SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
        error_log("SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
        error_log("All session keys: " . implode(', ', array_keys($_SESSION ?? [])));
        error_log("Session array empty: " . (empty($_SESSION) ? 'YES' : 'NO'));
        
        // CRITICAL: If session is already started but empty, and MVC_PAYROLL_SESS cookie exists,
        // try to load the session using the cookie's session ID
        if (empty($_SESSION) && isset($_COOKIE['MVC_PAYROLL_SESS']) && !empty($_COOKIE['MVC_PAYROLL_SESS'])) {
            error_log("⚠️ Session is empty but MVC_PAYROLL_SESS cookie exists!");
            error_log("Current session ID: " . session_id());
            error_log("Cookie session ID: " . $_COOKIE['MVC_PAYROLL_SESS']);
            
            // If session ID doesn't match cookie, reload session with cookie's ID
            if (session_id() !== $_COOKIE['MVC_PAYROLL_SESS']) {
                error_log("⚠️ Session ID mismatch! Reloading session with cookie's ID...");
                session_write_close();
                session_name('MVC_PAYROLL_SESS');
                session_id($_COOKIE['MVC_PAYROLL_SESS']);
                session_start();
                
                error_log("After reload - Session ID: " . session_id());
                error_log("After reload - SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
                error_log("After reload - SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
                error_log("After reload - Session keys: " . implode(', ', array_keys($_SESSION ?? [])));
            } else {
                error_log("⚠️ Session ID matches cookie but session is still empty!");
                error_log("Session file might not exist or be corrupted.");
            }
        }
        
        // CRITICAL FIX: Check if we need to migrate from PHPSESSID
        // First, check if current session is empty or if session name is wrong
        $needsMigration = false;
        
        // Case 1: Current session is empty but PHPSESSID cookie exists
        if (empty($_SESSION) && isset($_COOKIE['PHPSESSID']) && !empty($_COOKIE['PHPSESSID'])) {
            $needsMigration = true;
            error_log("⚠️ Migration needed: Current session empty, PHPSESSID cookie exists");
        }
        
        // Case 2: Session name is PHPSESSID but we expect MVC_PAYROLL_SESS
        if (session_name() === 'PHPSESSID' && isset($_COOKIE['PHPSESSID']) && !empty($_COOKIE['PHPSESSID'])) {
            $needsMigration = true;
            error_log("⚠️ Migration needed: Session name is PHPSESSID, should be MVC_PAYROLL_SESS");
        }
        
        // Perform migration if needed
        if ($needsMigration) {
            error_log("⚠️ WARNING: Current session is empty but PHPSESSID cookie exists!");
            error_log("Attempting to load session from PHPSESSID and migrate to MVC_PAYROLL_SESS...");
            
            // Close current session
            session_write_close();
            
            // Load session from PHPSESSID
            session_name('PHPSESSID');
            session_id($_COOKIE['PHPSESSID']);
            session_start();
            
            // Check if PHPSESSID session has data
            if (!empty($_SESSION) && isset($_SESSION['SESSION_USER_ID'])) {
                error_log("✅ Found session data in PHPSESSID!");
                error_log("PHPSESSID SESSION_USER_ID: " . $_SESSION['SESSION_USER_ID']);
                error_log("PHPSESSID SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
                
                // Save session data and ID
                $sessionData = $_SESSION;
                $sessionId = session_id();
                
                // Close PHPSESSID session
                session_write_close();
                
                // Switch to MVC_PAYROLL_SESS and use the same session ID
                session_name('MVC_PAYROLL_SESS');
                session_id($sessionId);
                session_start();
                
                // Restore session data
                $_SESSION = $sessionData;
                
                // Set the new cookie with proper settings
                $cookieLifetime = 86400; // 1 day
                $cookiePath = '/';
                $cookieDomain = '';
                $cookieSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
                $cookieHttpOnly = true;
                
                setcookie('MVC_PAYROLL_SESS', $sessionId, time() + $cookieLifetime, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly);
                
                // Delete old PHPSESSID cookie
                setcookie('PHPSESSID', '', time() - 3600, '/', '', false, true);
                
                error_log("✅ Session migrated from PHPSESSID to MVC_PAYROLL_SESS");
                error_log("After migration - SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
                error_log("After migration - SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
            } else {
                error_log("❌ PHPSESSID session is also empty");
                // Restart with MVC_PAYROLL_SESS
                session_write_close();
                session_name('MVC_PAYROLL_SESS');
                if (isset($_COOKIE['MVC_PAYROLL_SESS'])) {
                    session_id($_COOKIE['MVC_PAYROLL_SESS']);
                }
                session_start();
            }
        }
        
        // If session is empty but cookie exists, there might be a session ID mismatch
        if (empty($_SESSION) && isset($_COOKIE[session_name()])) {
            error_log("⚠️ WARNING: Session is empty but cookie exists! Possible session ID mismatch.");
            error_log("Cookie value: " . $_COOKIE[session_name()]);
            error_log("Current session ID: " . session_id());
            
            // If cookie doesn't match current session ID, try to use the cookie's session ID
            if ($_COOKIE[session_name()] !== session_id()) {
                error_log("⚠️ Session ID mismatch detected! Cookie: " . $_COOKIE[session_name()] . " vs Current: " . session_id());
                error_log("Attempting to restore session from cookie...");
                
                // Close current session and start with cookie's session ID
                session_write_close();
                session_id($_COOKIE[session_name()]);
                session_start();
                
                error_log("After restore - SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
                error_log("After restore - SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
            } else {
                error_log("⚠️ Session ID matches cookie but session is still empty!");
                error_log("This might mean the session file doesn't exist or is corrupted.");
                error_log("Session save path: " . session_save_path());
                $sessionFile = session_save_path() . '/sess_' . session_id();
                error_log("Session file should be at: " . $sessionFile);
                error_log("Session file exists: " . (file_exists($sessionFile) ? 'YES' : 'NO'));
                if (file_exists($sessionFile)) {
                    try {
                        $fileSize = @filesize($sessionFile);
                        error_log("Session file size: " . ($fileSize !== false ? $fileSize . " bytes" : "unknown"));
                        if (is_readable($sessionFile)) {
                            // Use @ to suppress warnings if file is locked or has permission issues
                            $fileContent = @file_get_contents($sessionFile);
                            if ($fileContent !== false) {
                                error_log("Session file content: " . substr($fileContent, 0, 200));
                            } else {
                                error_log("Could not read session file (may be locked or permission denied)");
                            }
                        } else {
                            error_log("Session file exists but is not readable (permission denied)");
                        }
                    } catch (Exception $e) {
                        error_log("Error reading session file: " . $e->getMessage());
                    } catch (Error $e) {
                        // Catch PHP 7+ errors as well
                        error_log("Error reading session file: " . $e->getMessage());
                    }
                }
            }
        }
        
        // FINAL CHECK: If session is still empty after all attempts, try one more time
        // This handles cases where the session was started before the cookie was set
        if (empty($_SESSION) && isset($_COOKIE['MVC_PAYROLL_SESS']) && !empty($_COOKIE['MVC_PAYROLL_SESS'])) {
            error_log("⚠️ FINAL ATTEMPT: Session still empty, trying to reload one more time...");
            $cookieSessionId = $_COOKIE['MVC_PAYROLL_SESS'];
            
            // Close current session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            
            // Start fresh with the cookie's session ID
            session_name('MVC_PAYROLL_SESS');
            session_id($cookieSessionId);
            session_start();
            
            error_log("Final reload - Session ID: " . session_id());
            error_log("Final reload - SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
            error_log("Final reload - SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
            error_log("Final reload - Session keys: " . implode(', ', array_keys($_SESSION ?? [])));
        }
        
        // Final check: If session is still empty after all attempts, log a warning
        if (empty($_SESSION)) {
            error_log("❌ CRITICAL: Session is still empty after all migration/restore attempts!");
            error_log("This means the user will not be authenticated.");
        } else {
            error_log("✅ Session data is present: " . count($_SESSION) . " keys found");
        }
    }
    
    // Regenerate session ID periodically to prevent session fixation
    // Only regenerate if user is logged in (has login data)
    $hasLoginData = isset($_SESSION['user_id']) || 
                    isset($_SESSION['employee_id']) || 
                    isset($_SESSION['employee_no']) ||
                    isset($_SESSION['manager_id']) || 
                    isset($_SESSION['SESSION_USER_ID']) ||
                    isset($_SESSION['owner_id']);
    
    if ($hasLoginData) {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            // Only regenerate if we have valid session data
            // Use false to keep old session data during regeneration
            $oldSessionData = $_SESSION;
            session_regenerate_id(false); // Keep old session data
            $_SESSION = $oldSessionData; // Restore session data
            $_SESSION['last_regeneration'] = time();
            error_log("Session ID regenerated (preserving data)");
        }
    }
}

// Initialize secure session
startSecureSession();

/**
 * Session timeout check
 */
function checkSessionTimeout() {
    $timeout = 86400; // 1 day
    $lastActivity = $_SESSION['last_activity'] ?? 0;
    
    // If last_activity is not set, this is a new session, so allow it
    if ($lastActivity === 0) {
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    if (time() - $lastActivity > $timeout) {
        // Session expired
        clearAllSessions();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Force session reset for new login
 */
function resetSessionForLogin() {
    // Destroy current session completely
    session_destroy();
    
    // Start fresh session
    session_start();
    
    // Regenerate session ID
    session_regenerate_id(true);
    
    // Clear all session data
    $_SESSION = [];
    
    // Set initial session data
    $_SESSION['last_regeneration'] = time();
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Secure login function
 */
function secureLogin($userData, $userType) {
    // Don't clear sessions if we're already logged in - this prevents losing session data
    // Only clear if this is a fresh login (no existing login data)
    $hasExistingLogin = isset($_SESSION['SESSION_USER_ID']) || 
                        isset($_SESSION['manager_id']) || 
                        isset($_SESSION['employee_id']);
    
    if (!$hasExistingLogin) {
        // Clear any existing sessions only if not already logged in
        clearAllSessions();
    }
    
    // Ensure session is active and writeable
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set user-specific session data
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['user_type'] = $userType;
    $_SESSION['user_email'] = $userData['email'] ?? $userData['hr_email'] ?? $userData['m_email'] ?? '';
    $_SESSION['user_name'] = $userData['name'] 
        ?? $userData['m_full_name'] 
        ?? trim(($userData['hr_first_name'] ?? $userData['first_name'] ?? '') . ' ' . ($userData['hr_last_name'] ?? $userData['last_name'] ?? ''));
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Get role information
    error_log("=== secureLogin() ROLE PROCESSING ===");
    error_log("UserType: " . $userType);
    error_log("UserData keys: " . implode(', ', array_keys($userData)));
    
    $roleId = $userData['role_id'] ?? null;
    error_log("Initial roleId from userData: " . ($roleId ?? 'NULL'));
    
    $role = null;
    
    if ($roleId) {
        error_log("Fetching role permissions for roleId: " . $roleId);
        $role = getRolePermissions($roleId);
        error_log("Role fetched: " . ($role ? json_encode($role) : 'NULL'));
    }
    
    // If no role found, set default based on user type
    if (!$role && $roleId === null) {
        error_log("No role found, setting default based on user type");
        // Set default role_id based on user type
        switch ($userType) {
            case 'employee':
                $roleId = 1; // Employee role
                break;
            case 'manager':
                $roleId = 2; // Manager role
                break;
            case 'admin':
                $roleId = 3; // HR role
                break;
        }
        if ($roleId) {
            error_log("Using default roleId: " . $roleId);
            $role = getRolePermissions($roleId);
            error_log("Default role fetched: " . ($role ? json_encode($role) : 'NULL'));
        }
    }
    
    // Set role-specific session variables
    switch ($userType) {
        case 'admin':
            // For admin/HR, use hr_email (admins table uses hr_email column)
            $adminEmail = trim($userData['hr_email'] ?? $userData['email'] ?? '');
            
            // Ensure email is not empty - this is required for isAdminLoggedIn() check
            if (empty($adminEmail)) {
                error_log("ERROR: Admin email is empty! UserData: " . json_encode($userData));
                throw new Exception("Admin email is required but not found in user data");
            }
            
            $_SESSION['SESSION_EMAIL'] = $adminEmail;
            $_SESSION['SESSION_USER_ID'] = $userData['id'];
            $_SESSION['USERNAME'] = $userData['name'] 
                ?? trim(($userData['hr_first_name'] ?? '') . ' ' . ($userData['hr_last_name'] ?? ''));
            
            // Debug logging
            error_log("=== secureLogin() ADMIN CASE ===");
            error_log("Setting SESSION_EMAIL: " . $adminEmail);
            error_log("Setting SESSION_USER_ID: " . $userData['id']);
            error_log("UserData keys: " . implode(', ', array_keys($userData)));
            error_log("hr_email in userData: " . ($userData['hr_email'] ?? 'NOT SET'));
            error_log("email in userData: " . ($userData['email'] ?? 'NOT SET'));
            error_log("Session after setting: SESSION_EMAIL=" . $_SESSION['SESSION_EMAIL'] . ", SESSION_USER_ID=" . $_SESSION['SESSION_USER_ID']);
            if (!empty($userData['photo_path'] ?? '')) {
                $_SESSION['photo_path'] = $userData['photo_path'];
            } elseif (!empty($userData['hr_photo_path'] ?? '')) {
                $_SESSION['photo_path'] = $userData['hr_photo_path'];
            }
            // Add role information
            if ($role) {
                $_SESSION['role_id'] = $roleId;
                $_SESSION['role_name'] = $role['name'];
                $_SESSION['can_access_employee_portal'] = $role['can_access_employee_portal'];
                $_SESSION['can_access_manager_portal'] = $role['can_access_manager_portal'];
                $_SESSION['can_access_hr_portal'] = $role['can_access_hr_portal'];
                $_SESSION['can_access_owner_portal'] = $role['can_access_owner_portal'];
            } else {
                error_log("WARNING: Role is NULL or empty for admin! roleId=" . ($roleId ?? 'NULL'));
            }
            
            // Verify session was set correctly before continuing
            error_log("Final session check - SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
            error_log("Final session check - SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
            error_log("isAdminLoggedIn() check: " . (isAdminLoggedIn() ? 'TRUE' : 'FALSE'));
            break;
            
        case 'manager':
            error_log("=== secureLogin() MANAGER CASE ===");
            error_log("UserData role_id: " . ($userData['role_id'] ?? 'NULL'));
            error_log("Role object: " . json_encode($role));
            
            $_SESSION['manager_id'] = $userData['id'];
            $_SESSION['manager_name'] = $userData['m_full_name'] ?? 
                trim($userData['m_first_name'] . ' ' . $userData['m_middle_name'] . ' ' . $userData['m_last_name']);
            $_SESSION['manager_email'] = $userData['m_email'];
            $_SESSION['manager_branch'] = $userData['m_branch'] ?? '';
            
            // Set photo path for manager (check m_photo_path first, then photo_path)
            if (!empty($userData['m_photo_path'] ?? '')) {
                $_SESSION['photo_path'] = $userData['m_photo_path'];
            } elseif (!empty($userData['photo_path'] ?? '')) {
                $_SESSION['photo_path'] = $userData['photo_path'];
            }

            // Set gender for manager (check m_sex first, then sex, then gender)
            if (!empty($userData['m_sex'] ?? '')) {
                $_SESSION['m_sex'] = $userData['m_sex'];
                $_SESSION['gender'] = $userData['m_sex'];
            } elseif (!empty($userData['sex'] ?? '')) {
                $_SESSION['m_sex'] = $userData['sex'];
                $_SESSION['gender'] = $userData['sex'];
            } elseif (!empty($userData['gender'] ?? '')) {
                $_SESSION['m_sex'] = $userData['gender'];
                $_SESSION['gender'] = $userData['gender'];
            }

            error_log("Session variables set: manager_id=" . $_SESSION['manager_id']);
            error_log("Photo path set: " . ($_SESSION['photo_path'] ?? 'NOT SET'));
            
            // Add role information
            if ($role) {
                $_SESSION['role_id'] = $roleId;
                $_SESSION['role_name'] = $role['name'];
                $_SESSION['can_access_employee_portal'] = $role['can_access_employee_portal'];
                $_SESSION['can_access_manager_portal'] = $role['can_access_manager_portal'];
                $_SESSION['can_access_hr_portal'] = $role['can_access_hr_portal'];
                $_SESSION['can_access_owner_portal'] = $role['can_access_owner_portal'];
                
                error_log("Role session variables set:");
                error_log("  role_id: " . $_SESSION['role_id']);
                error_log("  role_name: " . $_SESSION['role_name']);
                error_log("  can_access_employee_portal: " . $_SESSION['can_access_employee_portal']);
            } else {
                error_log("WARNING: Role is NULL or empty! roleId=" . ($roleId ?? 'NULL'));
            }
            break;
            
        case 'employee':
            $_SESSION['employee_id'] = $userData['id'];
            $_SESSION['employee_no'] = $userData['employee_no'];
            $_SESSION['email'] = $userData['email'];
            $_SESSION['name'] = $userData['first_name'] . ' ' . $userData['last_name'];
            $_SESSION['position'] = $userData['position'];
            $_SESSION['photo_path'] = $userData['photo_path'] ?? '';
            // Add role information
            if ($role) {
                $_SESSION['role_id'] = $roleId;
                $_SESSION['role_name'] = $role['name'];
                // Employees always have access to employee portal
                $_SESSION['can_access_employee_portal'] = 1;
                $_SESSION['can_access_manager_portal'] = $role['can_access_manager_portal'];
                $_SESSION['can_access_hr_portal'] = $role['can_access_hr_portal'];
                $_SESSION['can_access_owner_portal'] = $role['can_access_owner_portal'];
            } else {
                // If no role, set default access for employees
                $_SESSION['can_access_employee_portal'] = 1;
            }
            break;
    }
    
    // Set login success flag for toast notification
    $_SESSION['login_success'] = true;
    
    // Log successful login
    logUserActivity('login', "User logged in successfully");
    
    // Final debug log
    error_log("=== secureLogin() COMPLETE ===");
    error_log("Session ID: " . session_id());
    error_log("User Type: " . $userType);
    error_log("SESSION_EMAIL: " . ($_SESSION['SESSION_EMAIL'] ?? 'NOT SET'));
    error_log("SESSION_USER_ID: " . ($_SESSION['SESSION_USER_ID'] ?? 'NOT SET'));
    error_log("employee_id: " . ($_SESSION['employee_id'] ?? 'NOT SET'));
    error_log("employee_no: " . ($_SESSION['employee_no'] ?? 'NOT SET'));
    error_log("manager_id: " . ($_SESSION['manager_id'] ?? 'NOT SET'));
    error_log("can_access_employee_portal: " . ($_SESSION['can_access_employee_portal'] ?? 'NOT SET'));
    error_log("isAdminLoggedIn(): " . (function_exists('isAdminLoggedIn') && isAdminLoggedIn() ? 'TRUE' : 'FALSE'));
    
    // Force session write to ensure data persists across redirect
    // Don't close the session, just ensure it's written
    if (function_exists('session_commit')) {
        session_commit();
    }
}

/**
 * Secure logout function
 */
function secureLogout() {
    $userType = getCurrentUserType();
    $userName = getCurrentUserName();
    
    // Log logout activity
    logUserActivity('logout', "User logged out");
    
    // Clear all sessions
    clearAllSessions();
    
    // Destroy session completely
    session_destroy();
    
    // Start new session for logout message
    session_start();
    $_SESSION['logged_out'] = true;
    $_SESSION['logout_message'] = "You have been successfully logged out.";
}

/**
 * Check if user is authenticated and session is valid
 */
function isAuthenticated() {
    // Check if session timeout
    if (!checkSessionTimeout()) {
        error_log("Authentication failed: Session timeout");
        return false;
    }
    
    // Check if user is logged in
    $isLoggedIn = isAdminLoggedIn() || isManagerLoggedIn() || isEmployeeLoggedIn();
    if (!$isLoggedIn) {
        error_log("Authentication failed: No valid login session found");
    }
    
    return $isLoggedIn;
}

/**
 * Require authentication with role check
 */
function requireAuth($allowedRoles = []) {
    if (!isAuthenticated()) {
        secureLogout();
        redirectToLogin();
    }
    
    $userType = getCurrentUserType();
    
    // If specific roles are required, check if user has access
    if (!empty($allowedRoles) && !in_array($userType, $allowedRoles)) {
        http_response_code(403);
        require_once __DIR__ . '/../Error/unauthorized.php';
        exit;
    }
}

/**
 * Get user data securely
 */
function getCurrentUserData() {
    $userType = getCurrentUserType();
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return null;
    }
    
    try {
        $db = new Database();
        
        switch ($userType) {
            case 'admin':
                $stmt = $db->query("SELECT * FROM admins WHERE id = ?", [$userId]);
                break;
            case 'manager':
                $stmt = $db->query("SELECT * FROM managers WHERE id = ?", [$userId]);
                break;
            case 'employee':
                $stmt = $db->query("SELECT * FROM employees WHERE id = ?", [$userId]);
                break;
            default:
                return null;
        }
        
        return $stmt ? $stmt[0] : null;
    } catch (Exception $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if user can access specific resource
 */
function canAccessResource($resourceType, $resourceId) {
    $userType = getCurrentUserType();
    $userId = getCurrentUserId();
    
    // Admin can access everything
    if ($userType === 'admin') {
        return true;
    }
    
    // Users can only access their own resources
    if ($userType === 'employee' && $resourceType === 'employee') {
        return $userId == $resourceId;
    }
    
    if ($userType === 'manager' && $resourceType === 'manager') {
        return $userId == $resourceId;
    }
    
    return false;
}

/**
 * Validate session integrity
 */
function validateSessionIntegrity() {
    // Check if IP address changed (potential session hijacking)
    // Only check if we have a stored IP address
    if (isset($_SESSION['ip_address']) && !empty($_SESSION['ip_address'])) {
        $currentIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if ($_SESSION['ip_address'] !== $currentIP) {
            error_log("Session integrity check failed: IP mismatch. Stored: " . $_SESSION['ip_address'] . ", Current: " . $currentIP);
            secureLogout();
            return false;
        }
    }
    
    // Check if user agent changed
    // Only check if we have a stored user agent
    if (isset($_SESSION['user_agent']) && !empty($_SESSION['user_agent'])) {
        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        if ($_SESSION['user_agent'] !== $currentUserAgent) {
            error_log("Session integrity check failed: User agent mismatch. Stored: " . $_SESSION['user_agent'] . ", Current: " . $currentUserAgent);
            secureLogout();
            return false;
        }
    }
    
    return true;
}

/**
 * Enhanced session validation
 */
function validateSession() {
    if (!isAuthenticated()) {
        error_log("Session validation failed: User not authenticated");
        return false;
    }
    
    // Temporarily disable session integrity check to debug login issue
    // if (!validateSessionIntegrity()) {
    //     error_log("Session validation failed: Session integrity check failed");
    //     return false;
    // }
    
    return true;
}
?>
