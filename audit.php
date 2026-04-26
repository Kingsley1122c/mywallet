<?php
/**
 * Simple audit logger for security events.
 * Appends newline-delimited JSON records to `audit_log.jsonl`.
 *
 * Fields: time, type, user_id, email, actor, ip, details
 */
function write_audit(string $type, $user_id = null, $email = null, $actor = null, array $details = []) {
    $record = [
        'time' => date('c'),
        'type' => $type,
        'user_id' => $user_id,
        'email' => $email,
        'actor' => $actor,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'details' => $details,
    ];

    $file = __DIR__ . '/audit_log.jsonl';
    @file_put_contents($file, json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
}
