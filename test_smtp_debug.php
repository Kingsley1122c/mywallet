<?php
require_once __DIR__ . '/local_only.php';

// Detailed SMTP test with full error output

$config = include 'email_config.php';

echo "=== SMTP Debug Test ===\n\n";
echo "Connecting to: {$config['smtp_host']}:{$config['smtp_port']}\n";
echo "Username: {$config['smtp_username']}\n";
echo "Password length: " . strlen($config['smtp_password']) . " characters\n\n";

$socket = @fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 30);

if (!$socket) {
    die("❌ Connection failed: $errstr ($errno)\n");
}

echo "✅ Connected to Gmail SMTP\n\n";

// Get initial response
$response = fgets($socket, 515);
echo "Server: $response";

// Send EHLO
fputs($socket, "EHLO localhost\r\n");
echo "Client: EHLO localhost\n";
while ($line = fgets($socket, 515)) {
    echo "Server: $line";
    if (substr($line, 3, 1) == ' ') break;
}

// Start TLS
fputs($socket, "STARTTLS\r\n");
echo "\nClient: STARTTLS\n";
$response = fgets($socket, 515);
echo "Server: $response";

stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
echo "✅ TLS encryption enabled\n\n";

// Send EHLO again
fputs($socket, "EHLO localhost\r\n");
echo "Client: EHLO localhost (after TLS)\n";
while ($line = fgets($socket, 515)) {
    echo "Server: $line";
    if (substr($line, 3, 1) == ' ') break;
}

// Authenticate
fputs($socket, "AUTH LOGIN\r\n");
echo "\nClient: AUTH LOGIN\n";
$response = fgets($socket, 515);
echo "Server: $response";

fputs($socket, base64_encode($config['smtp_username']) . "\r\n");
echo "Client: [base64 username]\n";
$response = fgets($socket, 515);
echo "Server: $response";

fputs($socket, base64_encode($config['smtp_password']) . "\r\n");
echo "Client: [base64 password]\n";
$response = fgets($socket, 515);
echo "Server: $response\n";

if (strpos($response, '235') !== false) {
    echo "✅ Authentication successful!\n";
} else {
    echo "❌ Authentication failed!\n";
    echo "\nPossible issues:\n";
    echo "1. App Password might be incorrect\n";
    echo "2. 2-Step Verification not enabled on Gmail account\n";
    echo "3. App Password not generated yet\n";
    echo "4. Gmail account might have restrictions\n";
}

// Send QUIT
fputs($socket, "QUIT\r\n");
fclose($socket);
