<?php
// Email configuration for mywallet.
// Prefer a real provider from environment variables on hosted deployments.

$env = static function (string $key, $default = null) {
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
};

$provider = strtolower((string) $env('EMAIL_PROVIDER', ''));

if ($provider === '') {
    if ($env('RESEND_API_KEY')) {
        $provider = 'resend';
    } elseif ($env('SMTP_HOST') && $env('SMTP_USERNAME') && $env('SMTP_PASSWORD')) {
        $provider = 'smtp';
    } else {
        $provider = 'php_mail';
    }
}

$enabledRaw = strtolower((string) $env('EMAIL_ENABLED', 'true'));
$enabled = !in_array($enabledRaw, ['0', 'false', 'off', 'no'], true);

$smtpUsername = (string) $env('SMTP_USERNAME', '');

return [
    'provider' => $provider,
    'from_email' => (string) $env('FROM_EMAIL', $smtpUsername ?: 'noreply@mywallet.com'),
    'from_name' => (string) $env('FROM_NAME', 'mywallet'),
    'enabled' => $enabled,
    'resend_api_key' => (string) $env('RESEND_API_KEY', ''),
    'smtp_host' => (string) $env('SMTP_HOST', ''),
    'smtp_port' => (int) $env('SMTP_PORT', 587),
    'smtp_username' => $smtpUsername,
    'smtp_password' => (string) $env('SMTP_PASSWORD', ''),
    'smtp_encryption' => (string) $env('SMTP_ENCRYPTION', 'tls'),
];
