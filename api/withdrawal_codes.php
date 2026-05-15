<?php
session_start();
header('Content-Type: application/json');

include_once __DIR__ . '/email_notifications.php';

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
$transactionsFile = __DIR__ . '/../transactions.json';

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
$transactions = file_exists($transactionsFile) ? json_decode(file_get_contents($transactionsFile), true) : [];
$transactions = is_array($transactions) ? $transactions : [];

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
    $transactionId = trim((string)($input['transaction_id'] ?? ''));

    if ($transactionId === '') {
        respond_error('Invalid request details', 'invalidRequestData');
    }

    $transaction = null;
    foreach ($transactions as $existingTransaction) {
        if (($existingTransaction['id'] ?? '') === $transactionId
            && ($existingTransaction['user_id'] ?? null) == $currentUser['id']) {
            $transaction = $existingTransaction;
            break;
        }
    }

    if (!$transaction || strtolower((string)($transaction['kind'] ?? '')) !== 'withdrawal') {
        respond_error('Pending withdrawal not found', 'pendingWithdrawalNotFound', 404);
    }

    $amount = abs((float)($transaction['amount'] ?? 0));
    $bankAccountId = trim((string)($transaction['bank_account_id'] ?? ''));

    $now = time();
    foreach ($requests as $request) {
        if (($request['user_id'] ?? null) == $currentUser['id']
            && (string)($request['transaction_id'] ?? '') === $transactionId
            && in_array((string)($request['status'] ?? 'pending'), ['pending', 'code_generated', 'validated'], true)
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
        'transaction_id' => $transactionId,
        'amount' => $amount,
        'bank_account_id' => $bankAccountId,
        'bank_name' => (string)($transaction['bank_name'] ?? ''),
        'account_number' => (string)($transaction['account_number'] ?? ''),
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
        'request_id' => $newRequest['id'],
        'transaction_id' => $transactionId
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
    $requestId = trim((string)($input['request_id'] ?? ''));
    $transactionId = trim((string)($input['transaction_id'] ?? ''));

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

    $matchedRequestIndex = null;
    if ($requestId !== '') {
        foreach ($requests as $index => $request) {
            if (($request['id'] ?? '') === $requestId) {
                $matchedRequestIndex = $index;
                break;
            }
        }
    } elseif ($transactionId !== '') {
        foreach ($requests as $index => $request) {
            if (($request['transaction_id'] ?? '') === $transactionId) {
                $matchedRequestIndex = $index;
                break;
            }
        }
    }

    if ($matchedRequestIndex !== null) {
        $amount = (float)($requests[$matchedRequestIndex]['amount'] ?? $amount);
        $transactionId = (string)($requests[$matchedRequestIndex]['transaction_id'] ?? $transactionId);
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
        'transaction_id' => $transactionId,
        'request_id' => $requestId !== '' ? $requestId : (($matchedRequestIndex !== null) ? ($requests[$matchedRequestIndex]['id'] ?? '') : ''),
        'created_at' => time(),
        'created_by' => $currentUser['id'],
        'used' => false,
        'used_at' => null
    ];

    $codes[] = $newCode;

    if ($matchedRequestIndex !== null) {
        $requests[$matchedRequestIndex]['status'] = 'code_generated';
        $requests[$matchedRequestIndex]['code_id'] = $newCode['id'];
        $requests[$matchedRequestIndex]['code_generated_at'] = time();
        $requestId = (string)($requests[$matchedRequestIndex]['id'] ?? $requestId);
    }

    save_json_file($codesFile, $codes);
    save_json_file($requestsFile, $requests);

    $to = $targetUser['email'] ?? '';
    if ($to !== '') {
        @sendWithdrawalCodeEmail($to, [
            'code' => $code,
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'bank_name' => $matchedRequestIndex !== null ? ($requests[$matchedRequestIndex]['bank_name'] ?? '') : '',
            'account_number' => $matchedRequestIndex !== null ? ($requests[$matchedRequestIndex]['account_number'] ?? '') : '',
            'expires_in_minutes' => 10,
        ]);
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
    $transactionId = trim((string)($input['transaction_id'] ?? ''));

    if ($code === '' || $transactionId === '') {
        respond_error('Invalid code or transaction', 'invalidCodeOrAmount');
    }

    $pendingRequestIndex = -1;
    foreach ($requests as $index => $request) {
        if (($request['user_id'] ?? null) == $currentUser['id']
            && (string)($request['transaction_id'] ?? '') === $transactionId
            && in_array(($request['status'] ?? 'pending'), ['pending', 'code_generated'], true)
        ) {
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
            && (($existingCode['transaction_id'] ?? '') === '' || ($existingCode['transaction_id'] ?? '') === $transactionId)
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
            'transaction_id' => $transactionId
        ]);
        respond_error('Invalid or expired code', 'invalidOrExpiredCode');
    }

    if ((time() - (int)($matchingCode['created_at'] ?? 0)) > 600) {
        respond_error('This code has expired. Request a new one.', 'codeExpired');
    }

    if (isset($matchingCode['amount']) && abs(((float)$matchingCode['amount']) - (float)($requests[$pendingRequestIndex]['amount'] ?? 0)) >= 0.01) {
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
