<?php
/**
 * Send email using Resend API
 */
function sendEmailResend($to, $subject, $htmlBody, $textBody = '') {
    $config = include __DIR__ . '/../email_config.php';
    
    if (!$config['enabled'] || $config['provider'] !== 'resend') {
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
    
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $config['resend_api_key'],
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return true;
    } else {
        error_log("Resend API error: " . $response);
        return false;
    }
}
