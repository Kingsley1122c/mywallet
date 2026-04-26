<?php
session_start();
header('Content-Type: application/json');

$usersFile = __DIR__ . '/../users.json';
$codesFile = __DIR__ . '/../withdrawal_codes.json';

if (!file_exists($usersFile)) {
    echo json_encode(['error' => 'System error']);
    exit();
}

$users = json_decode(file_get_contents($usersFile), true) ?: [];

// Find current user
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$currentUser = null;
foreach ($users as $u) {
    if ($u['id'] == $_SESSION['user_id']) {
        $currentUser = $u;
        break;
    }
}

if (!$currentUser) {
    echo json_encode(['error' => 'User not found']);
    exit();
}

// Ensure codes file exists
if (!file_exists($codesFile)) {
    file_put_contents($codesFile, json_encode([], JSON_PRETTY_PRINT));
}

$codes = json_decode(file_get_contents($codesFile), true) ?: [];

// Handle different actions
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

// ADMIN: Generate new withdrawal code
if ($action === 'generate' && $currentUser['role'] === 'admin') {
    $targetUserId = (int)($input['user_id'] ?? 0);
    $amount = floatval($input['amount'] ?? 0);
    
    if (!$targetUserId || $amount <= 0) {
        echo json_encode(['error' => 'Invalid user ID or amount']);
        exit();
    }
    
    // Find target user
    $targetUser = null;
    foreach ($users as $u) {
        $logFile = __DIR__ . '/../withdrawal_attempts.log';
        $maxAttempts = 5;
        $windowSeconds = 600; // 10 minutes
        $expireSeconds = 600; // Code expires after 10 minutes
        $now = time();
        // Rate limiting by user
        $attempts = [];
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $parts = explode('|', $line);
                if (count($parts) >= 3 && $parts[0] == $currentUser['id'] && ($now - (int)$parts[2]) < $windowSeconds) {
                    $attempts[] = $parts;
                }
            }
        }
        if (count($attempts) >= $maxAttempts) {
            echo json_encode(['error' => 'Too many attempts. Please wait and try again.']);
            exit();
        }
        // Log this attempt
        file_put_contents($logFile, $currentUser['id'] . '|' . $code . '|' . $now . "\n", FILE_APPEND);
        if (!$code || $amount <= 0) {
            echo json_encode(['error' => 'Invalid code or amount']);
            exit();
        }
        // Find matching code
        foreach ($codes as $i => $c) {
            if ($c['code'] === $code && 
                $c['user_id'] == $currentUser['id'] && 
                !$c['used']) {
                $matchingCode = $c;
                $codeIndex = $i;
                break;
            }
        }
        if (!$matchingCode) {
            echo json_encode(['error' => 'Invalid or expired code']);
            exit();
        }
        // Check expiration
        if (($now - $matchingCode['created_at']) > $expireSeconds) {
            echo json_encode(['error' => 'This code has expired. Request a new one.']);
            exit();
        }
    }
    
    // Generate unique 6-digit code
    do {
        $code = sprintf('%06d', mt_rand(100000, 999999));
        $exists = false;
        foreach ($codes as $c) {
            if ($c['code'] === $code && !$c['used']) {
                $exists = true;
                break;
            }
        }
    } while ($exists);
    
    // Create code entry
    $newCode = [
        'id' => uniqid(),
        'code' => $code,
        'user_id' => $targetUserId,
        'user_email' => $targetUser['email'],
        'amount' => $amount,
        'created_at' => time(),
        'created_by' => $currentUser['id'],
        'used' => false,
        'used_at' => null
    ];
    
    $codes[] = $newCode;
    file_put_contents($codesFile, json_encode($codes, JSON_PRETTY_PRINT), LOCK_EX);
    // Send notification to user (simple mail)
    $to = $targetUser['email'];
    $subject = 'Your Withdrawal Code';
    $message = "Dear user,\n\nA withdrawal code has been generated for your account.\n\nCode: $code\nAmount: $amount\n\nThis code will expire in 10 minutes.\n\nIf you did not request this, please contact support immediately.";
    $headers = 'From: noreply@yourdomain.com' . "\r\n" . 'Reply-To: support@yourdomain.com';
    @mail($to, $subject, $message, $headers);
    echo json_encode(['success' => true, 'code' => $code, 'codeData' => $newCode]);
    exit();
}

// ADMIN: Get all codes
if ($action === 'list' && $currentUser['role'] === 'admin') {
    echo json_encode(['codes' => $codes]);
    exit();
}

// USER: Validate withdrawal code
if ($action === 'validate') {
    $code = trim($input['code'] ?? '');
    $amount = floatval($input['amount'] ?? 0);
    
    if (!$code || $amount <= 0) {
        echo json_encode(['error' => 'Invalid code or amount']);
        exit();
    }
    
    // Find matching code
    $matchingCode = null;
    $codeIndex = -1;
    
    foreach ($codes as $i => $c) {
        if ($c['code'] === $code && 
            $c['user_id'] == $currentUser['id'] && 
            !$c['used']) {
            $matchingCode = $c;
            $codeIndex = $i;
            break;
        }
    }
    
    if (!$matchingCode) {
        echo json_encode(['error' => 'Invalid or expired code']);
        exit();
    }
    
                foreach ($codes as $i => $c) {
                    if ($c['code'] === $code && 
                        $c['user_id'] == $currentUser['id'] && 
                        !$c['used']) {
                        $matchingCode = $c;
                        $codeIndex = $i;
                        break;
                    }
                }
    
                // Audit log for invalid/expired code
                if (!$matchingCode) {
                    $audit = [
                        'event' => 'withdrawal_code_invalid',
                        'user_id' => $currentUser['id'],
                        'email' => $currentUser['email'],
                        'code' => $code,
                        'amount' => $amount,
                        'timestamp' => time(),
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
                    ];
                    file_put_contents(__DIR__ . '/../audit_log.jsonl', json_encode($audit) . "\n", FILE_APPEND);
                    echo json_encode(['error' => 'Invalid or expired code']);
                    exit();
                }
    file_put_contents($codesFile, json_encode($codes, JSON_PRETTY_PRINT), LOCK_EX);
    
    echo json_encode(['success' => true, 'valid' => true]);
    exit();
}

// USER: Check if they have any valid codes
if ($action === 'check_codes') {
    $validCodes = array_filter($codes, function($c) use ($currentUser) {
        return $c['user_id'] == $currentUser['id'] && !$c['used'];
    });
    
    echo json_encode(['has_codes' => count($validCodes) > 0, 'count' => count($validCodes)]);
    exit();
}

echo json_encode(['error' => 'Invalid action']);
