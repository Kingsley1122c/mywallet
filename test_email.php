<?php
require_once __DIR__ . '/local_only.php';

// Test Email Configuration
include_once __DIR__ . '/api/email_notifications.php';

echo "Testing email configuration...\n\n";

$testEmail = 'harrisonjacksonj011@gmail.com'; // Send test to yourself

$testData = [
    'type' => 'receive',
    'amount' => 100,
    'date' => date('F j, Y g:i A'),
    'balance' => 20500,
    'sender' => 'Test Sender',
    'sender_email' => 'test@example.com'
];

echo "Attempting to send test email to: $testEmail\n";
$result = sendTransactionEmail($testEmail, $testData);

if ($result) {
    echo "\n✅ SUCCESS! Email sent successfully!\n";
    echo "Check your inbox at: $testEmail\n";
} else {
    echo "\n❌ FAILED! Email could not be sent.\n";
    echo "Check email_log.txt for details.\n";
    if (file_exists(__DIR__ . '/email_log.txt')) {
        echo "\nLast errors:\n";
        echo file_get_contents(__DIR__ . '/email_log.txt');
    }
}
?>
