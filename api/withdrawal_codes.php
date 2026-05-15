<?php
session_start();
header('Content-Type: application/json');

function respond_error($message, $errorCode, $status = 400, $extra = []) {
    http_response_code($status);
    echo json_encode(array_merge(['error' => $message, 'error_code' => $errorCode], $extra));
    exit();
}

function save_json_file($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function append_audit_event($event, $payload = []) {
    $entry = array_merge([
        'event' => $event,
        'timestamp' => time(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ], $payload);
    file_put_contents(__DIR__ . '/../audit_log.jsonl', json_encode($entry) . "\n", FILE_APPEND);
}

$usersFile = __DIR__ . '/../users.json';
$codesFile = __DIR__ . '/../withdrawal_codes.json';
$requestsFile = __DIR__ . '/../withdrawal_code_requests.json';

if (!file_exists($usersFile)) {
    respond_error('System error', 'systemError', 500);
}

if (!file_exists($codesFile)) {
    save_json_file($codesFile, []);
}

if (!file_exists($requestsFile)) {
    save_json_file($requestsFile, []);
}

$users = json_decode(file_get_contents($usersFile), true) ?: [];
$codes = json_decode(file_get_contents($codesFile), true) ?: [];
$requests = json_decode(file_get_contents($requestsFile), true) ?: [];

if (empty($_SESSION['user_id'])) {
    respond_error('Not authenticated', 'notAuthenticated', 401);
}

$currentUser = null;
foreach ($users as $user) {
    if (($user['id'] ?? null) == $_SESSION['user_id']) {
        $currentUser = $user;
        break;
    }
}

if (!$currentUser) {
    respond_error('User not found', 'userNotFound', 404);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? $_GET['action'] ?? '';

if ($action === 'request') {
    $amount = (float)($input['amount'] ?? 0);
    $bankAccountId = trim((string)($input['bank_account_id'] ?? ''));

    if ($amount <= 0 || $bankAccountId === '') {
        respond_error('Invalid request details', 'invalidRequestData');
    }

    $now = time();
    foreach ($requests as $request) {
        if (($request['user_id'] ?? null) == $currentUser['id']
            && ($request['status'] ?? 'pending') === 'pending'
            && abs(((float)($request['amount'] ?? 0)) - $amount) < 0.01
            && (string)($request['bank_account_id'] ?? '') === $bankAccountId
            && ($now - (int)($request['created_at'] ?? 0)) < 900) {
            echo json_encode([
                'success' => true,
                'requested' => true,
                'already_requested' => true,
                'requestData' => $request
            ]);
            exit();
        }
    }

    $newRequest = [
        'id' => uniqid('wcr_', true),
        'user_id' => $currentUser['id'],
        'user_email' => $currentUser['email'] ?? '',
        'amount' => $amount,
        'bank_account_id' => $bankAccountId,
        'status' => 'pending',
        'created_at' => $now
    ];

    $requests[] = $newRequest;
    save_json_file($requestsFile, $requests);
    append_audit_event('withdrawal_code_requested', [
        'user_id' => $currentUser['id'],
        'email' => $currentUser['email'] ?? '',
        'amount' => $amount,
        'bank_account_id' => $bankAccountId,
        'request_id' => $newRequest['id']
    ]);

    echo json_encode([
        'success' => true,
        'requested' => true,
        'requestData' => $newRequest
    ]);
    exit();
}

if ($action === 'generate' && (($currentUser['role'] ?? 'user') === 'admin')) {
    $targetUserId = (int)($input['user_id'] ?? 0);
    $amount = (float)($input['amount'] ?? 0);

    if ($targetUserId <= 0 || $amount <= 0) {
        respond_error('Invalid user ID or amount', 'invalidUserOrAmount');
    }

    $targetUser = null;
    foreach ($users as $user) {
        if (($user['id'] ?? null) == $targetUserId) {
            $targetUser = $user;
            break;
        }
    }

    if (!$targetUser) {
        respond_error('User not found', 'userNotFound', 404);
    }

    do {
        $code = sprintf('%06d', mt_rand(100000, 999999));
        $exists = false;
        foreach ($codes as $existingCode) {
            if (($existingCode['code'] ?? '') === $code && empty($existingCode['used'])) {
                $exists = true;
                break;
            }
        }
    } while ($exists);

    $newCode = [
        'id' => uniqid('wc_', true),
        'code' => $code,
        'user_id' => $targetUserId,
        'user_email' => $targetUser['email'] ?? '',
        'amount' => $amount,
        'created_at' => time(),
        'created_by' => $currentUser['id'],
        'used' => false,
        'used_at' => null
    ];

    $codes[] = $newCode;

    foreach ($requests as &$request) {
        if (($request['user_id'] ?? null) == $targetUserId
            && ($request['status'] ?? 'pending') === 'pending'
            && abs(((float)($request['amount'] ?? 0)) - $amount) < 0.01) {
            $request['status'] = 'code_generated';
            $request['code_id'] = $newCode['id'];
            $request['code_generated_at'] = time();
        }
    }
    unset($request);

    save_json_file($codesFile, $codes);
    save_json_file($requestsFile, $requests);

    $to = $targetUser['email'] ?? '';
    if ($to !== '') {
        $subject = 'Your Withdrawal Code';
        $message = "Dear user,\n\nA withdrawal code has been generated for your account.\n\nCode: $code\nAmount: $amount\n\nThis code will expire in 10 minutes.\n\nIf you did not request this, please contact support immediately.";
        $headers = 'From: noreply@yourdomain.com' . "\r\n" . 'Reply-To: support@yourdomain.com';
        @mail($to, $subject, $message, $headers);
    }

    echo json_encode(['success' => true, 'code' => $code, 'codeData' => $newCode]);
    exit();
}

if ($action === 'list' && (($currentUser['role'] ?? 'user') === 'admin')) {
    echo json_encode([
        'codes' => $codes,
        'requests' => $requests
    ]);
    exit();
}

if ($action === 'validate') {
    $code = trim((string)($input['code'] ?? ''));
    $amount = (float)($input['amount'] ?? 0);

    if ($code === '' || $amount <= 0) {
        respond_error('Invalid code or amount', 'invalidCodeOrAmount');
    }

    $pendingRequestIndex = -1;
    foreach ($requests as $index => $request) {
        if (($request['user_id'] ?? null) == $currentUser['id']
            && in_array(($request['status'] ?? 'pending'), ['pending', 'code_generated'], true)
            && abs(((float)($request['amount'] ?? 0)) - $amount) < 0.01) {
            $pendingRequestIndex = $index;
            break;
        }
    }

    if ($pendingRequestIndex === -1) {
        respond_error('Please request a withdrawal code first.', 'requestWithdrawalCodeFirst');
    }

    $matchingCode = null;
    foreach ($codes as $existingCode) {
        if (($existingCode['code'] ?? '') === $code
            && ($existingCode['user_id'] ?? null) == $currentUser['id']
            && empty($existingCode['used'])) {
            $matchingCode = $existingCode;
            break;
        }
    }

    if (!$matchingCode) {
        append_audit_event('withdrawal_code_invalid', [
            'user_id' => $currentUser['id'],
            'email' => $currentUser['email'] ?? '',
            'code' => $code,
            'amount' => $amount
        ]);
        respond_error('Invalid or expired code', 'invalidOrExpiredCode');
    }

    if ((time() - (int)($matchingCode['created_at'] ?? 0)) > 600) {
        respond_error('This code has expired. Request a new one.', 'codeExpired');
    }

    if (isset($matchingCode['amount']) && abs(((float)$matchingCode['amount']) - $amount) >= 0.01) {
        respond_error('This code does not match the withdrawal amount.', 'codeAmountMismatch');
    }

    $requests[$pendingRequestIndex]['status'] = 'validated';
    $requests[$pendingRequestIndex]['validated_at'] = time();
    $requests[$pendingRequestIndex]['code_id'] = $matchingCode['id'] ?? null;
    save_json_file($requestsFile, $requests);

    echo json_encode(['success' => true, 'valid' => true, 'codeData' => $matchingCode]);
    exit();
}

if ($action === 'check_codes') {
    $validCodes = array_values(array_filter($codes, function ($code) use ($currentUser) {
        return ($code['user_id'] ?? null) == $currentUser['id'] && empty($code['used']);
    }));

    echo json_encode(['has_codes' => count($validCodes) > 0, 'count' => count($validCodes)]);
    exit();
}

respond_error('Invalid action', 'invalidAction');
