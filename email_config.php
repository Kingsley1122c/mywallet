<?php
// Email configuration for Mivonta.
// InfinityFree-style shared hosting may not expose normal environment variables,
// so this file also accepts a local PHP override file.

$overridesFile = __DIR__ . '/email_local.php';
$overrides = [];

if (file_exists($overridesFile)) {
    $loadedOverrides = include $overridesFile;
    if (is_array($loadedOverrides)) {
        $overrides = $loadedOverrides;
    }
}

$env = static function (string $key, $default = null) use ($overrides) {
    if (array_key_exists($key, $overrides) && $overrides[$key] !== '') {
        return $overrides[$key];
    }

    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
};

$provider = strtolower((string) $env('EMAIL_PROVIDER', ''));
$resendApiKey = (string) $env('RESEND_API_KEY', '');
$smtpHost = (string) $env('SMTP_HOST', '');
$smtpUsername = (string) $env('SMTP_USERNAME', '');
$smtpPassword = (string) $env('SMTP_PASSWORD', '');

if ($provider === '') {
    if ($resendApiKey !== '') {
        $provider = 'resend';
    } elseif ($smtpHost !== '' && $smtpUsername !== '' && $smtpPassword !== '') {
        $provider = 'smtp';
    } else {
        $provider = 'none';
    }
}

$enabledDefault = $provider !== 'none' ? 'true' : 'false';
$enabledRaw = strtolower((string) $env('EMAIL_ENABLED', $enabledDefault));
$enabled = !in_array($enabledRaw, ['0', 'false', 'off', 'no'], true);

return [
    'provider' => $provider,
    'from_email' => (string) $env('FROM_EMAIL', $smtpUsername ?: 'noreply@mivonta.com'),
    'from_name' => (string) $env('FROM_NAME', 'Mivonta'),
    'enabled' => $enabled,
    'resend_api_key' => $resendApiKey,
    'smtp_host' => $smtpHost,
    'smtp_port' => (int) $env('SMTP_PORT', 587),
    'smtp_username' => $smtpUsername,
    'smtp_password' => $smtpPassword,
    'smtp_encryption' => (string) $env('SMTP_ENCRYPTION', 'tls'),
];
