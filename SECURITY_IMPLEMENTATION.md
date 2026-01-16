# Secure Multi-User Login and Session Management System

## Overview

This implementation provides a comprehensive secure multi-user authentication and session management system for the MVC Payroll application. It ensures that each user can only access their own account and data, with proper role-based access control and security measures.

## Key Security Features

### 🔐 **Authentication & Authorization**
- **Multi-User Support**: Separate authentication for Admin, Manager, and Employee roles
- **Role-Based Access Control**: Each role has specific page and API access permissions
- **Session Isolation**: Users cannot access each other's sessions or data
- **Automatic Session Validation**: Continuous session integrity checks

### 🛡️ **Session Security**
- **Secure Session Management**: 30-minute timeout with automatic regeneration
- **Session Fixation Protection**: Periodic session ID regeneration
- **IP Address Validation**: Prevents session hijacking
- **User Agent Validation**: Additional session integrity checks
- **Automatic Logout**: Secure session termination

### 🔒 **Password Security**
- **Strong Password Hashing**: Uses Argon2id with high memory cost
- **Password Strength Validation**: Enforces strong password requirements
- **Automatic Rehashing**: Updates old password hashes automatically
- **Login Attempt Limiting**: 5 attempts with 15-minute lockout

### 🚫 **CSRF Protection**
- **CSRF Token Generation**: Secure random tokens for all forms
- **Token Validation**: Automatic validation for POST/PUT/DELETE requests
- **Token Expiry**: 1-hour token lifetime

### 📊 **Rate Limiting**
- **API Rate Limiting**: 100 requests per hour per IP
- **Login Rate Limiting**: Prevents brute force attacks
- **Configurable Limits**: Easy to adjust based on needs

### 🛡️ **Input Validation & Sanitization**
- **Prepared Statements**: All database queries use prepared statements
- **Input Sanitization**: Automatic HTML entity encoding
- **File Upload Security**: Validates file types and sizes
- **Email Validation**: Proper email format validation

## File Structure

```
app/core/
├── SecurityConfig.php          # Centralized security settings
├── secure_session.php          # Secure session management
├── SecureAuth.php             # Authentication class
├── AuthMiddleware.php         # Controller protection middleware
├── SecureAPIMiddleware.php    # API protection middleware
└── session_helper.php         # Enhanced session helper

app/controller/
├── login1.php                 # Updated admin/employee login
├── login_manager.php          # Updated manager login
├── admin_logout.php           # Secure logout
├── employee_logout.php        # Secure logout
└── manager_logout.php         # Secure logout
```

## Security Configuration

### Session Settings
```php
define('SESSION_TIMEOUT', 1800);           // 30 minutes
define('SESSION_REGENERATION_INTERVAL', 300); // 5 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_DURATION', 900);     // 15 minutes
```

### Password Requirements
```php
define('MIN_PASSWORD_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SPECIAL_CHARS', true);
```

### Security Headers
```php
'X-Frame-Options' => 'DENY',
'X-Content-Type-Options' => 'nosniff',
'X-XSS-Protection' => '1; mode=block',
'Referrer-Policy' => 'strict-origin-when-cross-origin',
'Content-Security-Policy' => "..."
```

## Role-Based Access Control

### Admin Access
- **Pages**: Dashboard, Employees, Managers, Attendance, Schedules, Payroll, Reports
- **APIs**: All CRUD operations for system management
- **Permissions**: Full system access

### Manager Access
- **Pages**: Manager Dashboard, Branch Profile, Employee List, Approvals, Leave Management
- **APIs**: Branch-specific operations and approvals
- **Permissions**: Branch-level management

### Employee Access
- **Pages**: User Dashboard, Profile, DTR, Payslip, Leave
- **APIs**: Personal data access and updates
- **Permissions**: Self-service only

## Usage Examples

### Protecting a Controller
```php
<?php
// Automatically applied via middleware
// No additional code needed in controller
requireAuth(['admin']); // Only admins can access
```

### Protecting an API Endpoint
```php
<?php
// Automatically applied via middleware
// API is protected based on endpoint name
// Returns JSON error responses for unauthorized access
```

### Manual Authentication Check
```php
<?php
require_once "../app/core/AuthMiddleware.php";

// Check if user is authenticated
if (!AuthMiddleware::requireAuth(['admin', 'manager'])) {
    // Redirect or show error
}

// Get current user data
$userData = AuthMiddleware::getCurrentUser();

// Validate CSRF token
if (!AuthMiddleware::validateCSRF($_POST['csrf_token'])) {
    // Handle invalid token
}
```

### Secure Login Process
```php
<?php
require_once "../app/core/SecureAuth.php";

$auth = new SecureAuth();

// Admin login
$result = $auth->authenticateAdmin($email, $password);
if ($result['success']) {
    // Redirect to admin dashboard
    header("Location: " . $result['redirect']);
}

// Manager login
$result = $auth->authenticateManager($email, $password);

// Employee login
$result = $auth->authenticateEmployee($email, $employeeNo);
```

## Security Best Practices Implemented

### ✅ **Session Security**
- Secure session configuration
- Automatic timeout and regeneration
- IP and user agent validation
- Complete session cleanup on logout

### ✅ **Password Security**
- Strong hashing with Argon2id
- Password strength requirements
- Automatic rehashing of old passwords
- Login attempt limiting

### ✅ **Input Validation**
- Prepared statements for all queries
- Input sanitization and validation
- File upload security
- Email format validation

### ✅ **Access Control**
- Role-based page access
- API endpoint protection
- Resource-level access control
- Automatic redirect on unauthorized access

### ✅ **CSRF Protection**
- Token generation and validation
- Automatic form protection
- Token expiry management

### ✅ **Rate Limiting**
- API rate limiting
- Login attempt limiting
- Configurable limits

### ✅ **Security Headers**
- XSS protection
- Clickjacking protection
- Content type sniffing protection
- Content Security Policy

## Error Handling

### Session Expired
- Automatic redirect to appropriate login page
- Clear error messages
- Secure session cleanup

### Unauthorized Access
- 403 Forbidden response
- Role-specific error messages
- Automatic logging of access attempts

### Invalid CSRF Token
- 403 Forbidden response
- Clear error message
- Automatic token regeneration

### Rate Limit Exceeded
- 429 Too Many Requests response
- Clear retry instructions
- Automatic limit reset

## Monitoring and Logging

### Security Events Logged
- Login attempts (successful and failed)
- Session hijacking attempts
- Unauthorized access attempts
- API rate limit violations
- CSRF token violations

### Log Format
```
[2025-01-15 10:30:45] [INFO] [192.168.1.100] [admin:123] login - User logged in successfully
[2025-01-15 10:35:12] [WARNING] [192.168.1.101] [guest:guest] SESSION_HIJACKING_ATTEMPT - IP address changed
```

## Migration Guide

### For Existing Controllers
1. **No changes needed** - Middleware is automatically applied
2. **Remove manual auth checks** - Middleware handles this
3. **Update logout calls** - Use `secureLogout()` function

### For Existing APIs
1. **No changes needed** - API middleware is automatically applied
2. **Add CSRF tokens** - Include in forms and AJAX requests
3. **Update error handling** - Use standardized JSON responses

### For Forms
1. **Add CSRF token**:
```html
<input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
```

2. **Include in AJAX requests**:
```javascript
headers: {
    'X-CSRF-Token': csrfToken
}
```

## Testing Security

### Session Security Tests
- Test session timeout
- Test session regeneration
- Test IP address validation
- Test user agent validation

### Authentication Tests
- Test login with valid credentials
- Test login with invalid credentials
- Test login attempt limiting
- Test role-based access

### CSRF Protection Tests
- Test form submission without token
- Test form submission with invalid token
- Test form submission with valid token

### Rate Limiting Tests
- Test API rate limiting
- Test login rate limiting
- Test rate limit reset

## Production Deployment

### Security Checklist
- [ ] Enable HTTPS (set `secure` => true in session config)
- [ ] Update Content Security Policy for production
- [ ] Configure proper error logging
- [ ] Set up monitoring for security events
- [ ] Regular security audits
- [ ] Keep dependencies updated

### Environment Variables
```php
// Production settings
define('SESSION_SECURE', true);
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Strict');
define('LOG_LEVEL', 'WARNING');
```

## Support and Maintenance

### Regular Tasks
- Monitor security logs
- Review failed login attempts
- Check for unusual access patterns
- Update security configurations as needed

### Security Updates
- Keep PHP version updated
- Update security libraries
- Review and update security policies
- Conduct regular security assessments

---

This implementation provides enterprise-level security for multi-user applications while maintaining ease of use and flexibility for future enhancements.
