<?php

if (!function_exists('app_base_url')) {
    function app_base_url(): string
    {
        $configured = getenv('APP_BASE_URL');
        if (is_string($configured) && $configured !== '') {
            return rtrim($configured, '/');
        }

        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
        $proto = strtolower(trim(explode(',', $forwardedProto)[0] ?? ''));
        $isHttps = $proto === 'https'
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;

        $scheme = $isHttps ? 'https' : 'http';
        $forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';
        $host = trim(explode(',', $forwardedHost ?: ($_SERVER['HTTP_HOST'] ?? 'localhost'))[0]);

        return $scheme . '://' . $host;
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        $baseUrl = app_base_url();
        if ($path === '') {
            return $baseUrl;
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }
}