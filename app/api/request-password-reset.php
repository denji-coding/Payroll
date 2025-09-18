
<?php
require_once __DIR__ . '/../core/secure_session.php';
require_once __DIR__ . '/../core/session_helper.php';
require_once __DIR__ . '/../core/password_reset.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Temporarily disable CSRF validation for forgot password (public endpoint)
// $csrf = $_POST['csrf_token'] ?? '';
// if (!verifyCSRFToken($csrf)) {
//     http_response_code(403);
//     echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
//     exit;
// }

$userType = $_POST['user_type'] ?? '';
$email = trim($_POST['email'] ?? '');

if (!in_array($userType, ['employee', 'manager'], true) || $email === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid inputs']);
    exit;
}

$user = pr_find_user_by_email($userType, $email);
if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$token = pr_create_reset_token($userType, (int)$user['id'], 30);
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
$resetUrl = $base . '/reset-password.php?token=' . urlencode($token);

$code = pr_get_verification_code($token);

// Attempt to send email (non-fatal if it fails)
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth   = true;
    $mail->Host       = 'mail.smtp2go.com';
    $mail->Username   = 'nabesis.roy@dnsc.edu.ph';
    $mail->Password   = 'pGdu8SqFpeLnVp2Y';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('noreply@migrantsventurecorp.ip-ddns.com', 'Migrants Venture Corporation');
    $mail->addReplyTo('support@migrantsventurecorp.ip-ddns.com', 'Support Team');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request - Verification Code';
    
    // Set account type for personalization
    $accountName = $userType === 'employee' ? 'Employee Account' : 'Manager Account';
    
    $mail->Body = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #16a34a; text-align: center;">Password Reset Request</h2>
        <p>Hello,</p>
        <p>We received a request to reset your password for your <strong>' . htmlspecialchars($accountName) . '</strong>.</p>
        <p>Please use the verification code below to proceed with resetting your password:</p>
        <div style="background-color: #f0f9ff; border: 2px solid #16a34a; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
            <h3 style="color: #16a34a; margin: 0; font-size: 24px;">Your Verification Code: <strong>' . htmlspecialchars($code) . '</strong></h3>
        </div>
        <p style="color: #dc2626; font-weight: bold;">This code will expire in 10 minutes.</p>
        <p>If you did not request a password reset, please ignore this email or contact our support team immediately.</p>
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;">
        <p style="color: #6b7280; font-size: 14px;">Best regards,<br><strong>Migrants Venture Corporation Support Team</strong></p>
    </div>';
    
    $mail->AltBody = "Hello,\n\n" .
                    "We received a request to reset your password for your " . $accountName . ".\n\n" .
                    "Please use the verification code below to proceed with resetting your password:\n\n" .
                    "Your Verification Code: " . $code . "\n\n" .
                    "This code will expire in 10 minutes. If you did not request a password reset, please ignore this email or contact our support team immediately.\n\n" .
                    "Best regards,\n" .
                    "Migrants Venture Corporation Support Team";
    $mail->send();
} catch (Exception $e) {
    // Log but do not fail the request
    error_log('Password reset email failed: ' . $e->getMessage());
}

echo json_encode(['success' => true, 'reset_url' => $resetUrl, 'token' => $token, 'verification_code' => $code, 'code_sent' => true]);
exit;
?>












