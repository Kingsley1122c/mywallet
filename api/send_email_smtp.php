<?php
// SMTP Email Sender using PHPMailer-like functionality
// Sends emails via Gmail SMTP

function sendEmailSMTP($to, $subject, $body, $config) {
    if (!$config['enabled']) {
        // Fallback to PHP mail() if SMTP not configured
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: {$config['from_name']} <{$config['from_email']}>" . "\r\n";
        return @mail($to, $subject, $body, $headers);
    }
    
    // Create email message
    $boundary = md5(uniqid(time()));
    
    $headers = "From: {$config['from_name']} <{$config['from_email']}>\r\n";
    $headers .= "Reply-To: {$config['from_email']}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    
    $message = "--{$boundary}\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $body . "\r\n";
    $message .= "--{$boundary}--";
    
    // Connect to SMTP server
    $socket = @fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 30);
    if (!$socket) {
        error_log("SMTP connection failed: $errstr ($errno)");
        return false;
    }
    
    stream_set_timeout($socket, 30);
    
    // Read server greeting
    $response = fgets($socket, 515);
    if (strpos($response, '220') === false) {
        fclose($socket);
        return false;
    }
    
    // Send EHLO
    fputs($socket, "EHLO " . gethostname() . "\r\n");
    $response = '';
    while ($line = fgets($socket, 515)) {
        $response .= $line;
        if (substr($line, 3, 1) == ' ') break;
    }
    
    // Start TLS
    fputs($socket, "STARTTLS\r\n");
    fgets($socket, 515);
    
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    
    // Send EHLO again after TLS
    fputs($socket, "EHLO " . gethostname() . "\r\n");
    while ($line = fgets($socket, 515)) {
        if (substr($line, 3, 1) == ' ') break;
    }
    
    // Authenticate with PLAIN (better for Brevo)
    $auth_string = base64_encode("\0" . $config['smtp_username'] . "\0" . $config['smtp_password']);
    fputs($socket, "AUTH PLAIN $auth_string\r\n");
    $auth_response = fgets($socket, 515);
    
    if (strpos($auth_response, '235') === false) {
        fclose($socket);
        error_log("SMTP authentication failed: " . $auth_response);
        return false;
    }
    
    // Send email
    fputs($socket, "MAIL FROM: <{$config['from_email']}>\r\n");
    fgets($socket, 515);
    
    fputs($socket, "RCPT TO: <{$to}>\r\n");
    fgets($socket, 515);
    
    fputs($socket, "DATA\r\n");
    fgets($socket, 515);
    
    fputs($socket, "Subject: {$subject}\r\n");
    fputs($socket, $headers);
    fputs($socket, "\r\n");
    fputs($socket, $message);
    fputs($socket, "\r\n.\r\n");
    
    $send_response = fgets($socket, 515);
    
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    return strpos($send_response, '250') !== false;
}
