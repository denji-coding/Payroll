# Email Setup Guide for HR Account Creation

## Overview
This guide explains the email functionality that sends welcome emails when HR accounts are created.

## Files Modified
- `app/api/hr_account-api.php` - Added email sending functionality using existing SMTP settings

## Setup Instructions

### 1. Email Configuration

The HR account creation email functionality uses the same SMTP settings as the managers account system:

```php
// SMTP Settings (already configured)
$mail->Host = 'mail.smtp2go.com';
$mail->Username = 'nabesis.roy@dnsc.edu.ph';
$mail->Password = 'pGdu8SqFpeLnVp2Y';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
```

**No additional configuration needed** - the system uses the existing working email settings from the managers account system.

## Testing

### 1. Test Email Configuration
Create a test HR account through the system to verify email sending.

### 2. Check Logs
Monitor the error logs for email-related messages:
- Success: "Welcome email sent successfully to: email@example.com"
- Error: "Email sending failed: [error message]"

### 3. Test Script
Run the test script to verify email functionality:
```bash
php test_email.php
```

### 4. Troubleshooting

#### Common Issues:
1. **Authentication Failed**: Check SMTP credentials
2. **Connection Timeout**: Verify SMTP host and port
3. **SSL/TLS Error**: Check encryption setting

#### Debug Mode:
To enable detailed error logging, add this to the email function:
```php
$mail->SMTPDebug = 2; // Enable verbose debug output
```

## Email Template

The welcome email includes:
- Professional HTML design
- HR's login credentials (Employee ID and password)
- Security instructions
- Company branding
- Plain text fallback

## Security Notes

1. **Never commit email credentials to version control**
2. **Use environment variables for production**
3. **Regularly rotate app passwords**
4. **Monitor email sending logs**

## Production Deployment

For production, consider:
1. Using environment variables for email credentials
2. Setting up a dedicated email service (SendGrid, Mailgun, etc.)
3. Implementing email templates in a separate file
4. Adding email queue for high-volume sending

## Support

If you encounter issues:
1. Check the error logs
2. Verify SMTP settings
3. Test with a simple email first
4. Contact your system administrator
