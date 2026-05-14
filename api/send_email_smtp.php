<?php
// SMTP Email Sender using PHPMailer-like functionality
// Sends emails via Gmail SMTP

function logSmtpMessage($message) {
    $formatted = '[smtp] ' . $message;
    error_log($formatted);

    if (function_exists('appendEmailLog')) {
        appendEmailLog($formatted);
    }
}

function readSmtpResponse($socket, &$timedOut = false) {
    $response = '';
    $timedOut = false;

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    $meta = stream_get_meta_data($socket);
    $timedOut = !empty($meta['timed_out']);

    return trim($response);
}

function smtpCommand($socket, $command, $expectedCodes, $label) {
    if ($command !== null) {
        fputs($socket, $command . "\r\n");
    }

    $timedOut = false;
    $response = readSmtpResponse($socket, $timedOut);

    if ($timedOut) {
        logSmtpMessage($label . ' timed out');
        return [false, $response];
    }

    if ($response === '') {
        logSmtpMessage($label . ' returned an empty response');
        return [false, $response];
    }

    foreach ((array) $expectedCodes as $code) {
        if (strpos($response, (string) $code) === 0) {
            return [true, $response];
        }
    }

    logSmtpMessage($label . ' failed with response: ' . $response);
    return [false, $response];
}

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
        logSmtpMessage("Connection failed to {$config['smtp_host']}:{$config['smtp_port']} - {$errstr} ({$errno})");
        return false;
    }
    
    stream_set_timeout($socket, 30);
    
    // Read server greeting
    list($ok, $response) = smtpCommand($socket, null, ['220'], 'Server greeting');
    if (!$ok) {
        fclose($socket);
        return false;
    }
    
    // Send EHLO
    $ehloHost = gethostname() ?: 'localhost';
    list($ok, $response) = smtpCommand($socket, 'EHLO ' . $ehloHost, ['250'], 'EHLO before STARTTLS');
    if (!$ok) {
        fclose($socket);
        return false;
    }
    
    // Start TLS
    list($ok, $response) = smtpCommand($socket, 'STARTTLS', ['220'], 'STARTTLS');
    if (!$ok) {
        fclose($socket);
        return false;
    }
    
    $cryptoEnabled = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    if ($cryptoEnabled !== true) {
        $cryptoError = error_get_last();
        logSmtpMessage('TLS negotiation failed' . ($cryptoError && !empty($cryptoError['message']) ? ': ' . $cryptoError['message'] : ''));
        fclose($socket);
        return false;
    }
    
    // Send EHLO again after TLS
    list($ok, $response) = smtpCommand($socket, 'EHLO ' . $ehloHost, ['250'], 'EHLO after STARTTLS');
    if (!$ok) {
        fclose($socket);
        return false;
    }
    
    // Authenticate with PLAIN (better for Brevo)
    $auth_string = base64_encode("\0" . $config['smtp_username'] . "\0" . $config['smtp_password']);
    list($ok, $auth_response) = smtpCommand($socket, 'AUTH PLAIN ' . $auth_string, ['235'], 'SMTP authentication');
    if (!$ok) {
        fclose($socket);
        return false;
    }
    
    // Send email
    list($ok, $response) = smtpCommand($socket, "MAIL FROM: <{$config['from_email']}>", ['250'], 'MAIL FROM');
    if (!$ok) {
        fclose($socket);
        return false;
    }
    
    list($ok, $response) = smtpCommand($socket, "RCPT TO: <{$to}>", ['250', '251'], 'RCPT TO');
    if (!$ok) {
        fclose($socket);
        return false;
    }
    
    list($ok, $response) = smtpCommand($socket, 'DATA', ['354'], 'DATA');
    if (!$ok) {
        fclose($socket);
        return false;
    }
    
    fputs($socket, "Subject: {$subject}\r\n");
    fputs($socket, $headers);
    fputs($socket, "\r\n");
    fputs($socket, $message);
    fputs($socket, "\r\n.\r\n");
    
    list($ok, $send_response) = smtpCommand($socket, null, ['250'], 'Message body');
    
    smtpCommand($socket, 'QUIT', ['221'], 'QUIT');
    fclose($socket);
    
    if (!$ok) {
        return false;
    }

    logSmtpMessage("Email accepted for {$to}");
    return true;
}
