<?php
/**
 * Send email using Resend API
 */

function logResendMessage($message) {
    $formatted = '[resend] ' . $message;
    error_log($formatted);

    if (function_exists('appendEmailLog')) {
        appendEmailLog($formatted);
    }
}

function sendResendRequest(array $config, array $data, ?string &$errorMessage = null, ?int &$httpCode = null) {
    $payload = json_encode($data);

    if ($payload === false) {
        $errorMessage = 'Failed to encode request payload';
        return false;
    }

    $headers = [
        'Authorization: Bearer ' . $config['resend_api_key'],
        'Content-Type: application/json',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $errorMessage = 'Request failed: ' . $curlError;
            return false;
        }

        return $response;
    }

    if (!ini_get('allow_url_fopen')) {
        $errorMessage = 'cURL extension is not available and allow_url_fopen is disabled';
        return false;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => $payload,
            'timeout' => 30,
            'ignore_errors' => true,
        ],
    ]);

    $response = @file_get_contents('https://api.resend.com/emails', false, $context);
    $statusLine = $http_response_header[0] ?? '';
    if (preg_match('/\s(\d{3})\s/', $statusLine, $matches)) {
        $httpCode = (int) $matches[1];
    }

    if ($response === false) {
        $errorMessage = 'Request failed using stream context';
        return false;
    }

    return $response;
}

function sendEmailResend($to, $subject, $htmlBody, $textBody = '') {
    $config = include __DIR__ . '/../email_config.php';
    
    if (!$config['enabled'] || $config['provider'] !== 'resend') {
        logResendMessage('Provider disabled or not set to resend');
        return false;
    }

    if (empty($config['resend_api_key'])) {
        logResendMessage('Missing RESEND_API_KEY');
        return false;
    }

    $data = [
        'from' => $config['from_name'] . ' <' . $config['from_email'] . '>',
        'to' => [$to],
        'subject' => $subject,
        'html' => $htmlBody
    ];
    
    if ($textBody) {
        $data['text'] = $textBody;
    }

    $errorMessage = null;
    $httpCode = null;
    $response = sendResendRequest($config, $data, $errorMessage, $httpCode);

    if ($response === false) {
        logResendMessage($errorMessage ?? 'Request failed');
        return false;
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        logResendMessage("Email accepted for {$to} (HTTP {$httpCode})");
        return true;
    }

    logResendMessage("API error HTTP {$httpCode}: {$response}");
    return false;
}
