<?php
require_once __DIR__ . '/local_only.php';

// Quick test for Resend API
include_once 'api/send_email_resend.php';

echo "Testing Resend API...\n\n";

$result = sendEmailResend(
    'harrisonjacksonj011@gmail.com',
    'Test Email from Mivonta',
    '<h1>Hello!</h1><p>This is a test email from your Mivonta site.</p><p>If you received this, email notifications are working! ✅</p>',
    'Hello! This is a test email from your Mivonta site.'
);

if ($result) {
    echo "✅ SUCCESS! Email sent successfully!\n";
    echo "Check harrisonjacksonj011@gmail.com inbox (and spam folder)\n";
} else {
    echo "❌ FAILED! Could not send email.\n";
}
