<?php
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
$allowedAddresses = ['127.0.0.1', '::1'];

if (PHP_SAPI !== 'cli' && !in_array($remoteAddr, $allowedAddresses, true)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Forbidden';
    exit;
}