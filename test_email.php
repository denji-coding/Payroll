<?php
// Test Email Functionality for HR Account Creation
// Run this script to test if email configuration is working

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function testEmail() {
    try {
        $mail = new PHPMailer(true);
        
        // Gmail SMTP Configuration
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = 'migrantsventurecorporation@gmail.com';
        $mail->Password = 'tfop acec ukat dosw'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use STARTTLS for port 587
        $mail->Port = 587; // Use port 587 for STARTTLS
        
        // Enable debug output
        $mail->SMTPDebug = 2;
        
        // Recipients - Sender must match Gmail account
        $mail->setFrom('migrantsventurecorporation@gmail.com', 'Migrants Venture Corporation');
        $mail->addReplyTo('support@migrantsventurecorp.ip-ddns.com', 'Support Team');
        $mail->addAddress('test@example.com', 'Test User'); // Change this to your test email
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Test Email - HR Account Creation';
        $mail->Body = '<h1>Test Email</h1><p>This is a test email to verify HR account creation email functionality.</p>';
        $mail->AltBody = 'Test Email - This is a test email to verify HR account creation email functionality.';
        
        $mail->send();
        echo "✅ Test email sent successfully!\n";
        
    } catch (Exception $e) {
        echo "❌ Email sending failed: " . $e->getMessage() . "\n";
    }
}

echo "Testing HR Account Creation Email...\n";
echo "SMTP Host: smtp.gmail.com\n";
echo "SMTP Port: 587\n";
echo "Encryption: STARTTLS\n";
echo "Username: migrantsventurecorporation@gmail.com\n";
echo "\n";

// Uncomment the line below to send a test email
// testEmail();

echo "To send a test email, uncomment the last line in this script and update the test email address.\n";
?>
