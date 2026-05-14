<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // set to your domain if needed
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
header('Content-Type: application/json');

function respond_error($message, $errorCode, $status = 400, $extra = []) {
    http_response_code($status);
    echo json_encode(array_merge(['error' => $message, 'error_code' => $errorCode], $extra));
    exit();
}

if (empty($_SESSION['user_id'])) {
    respond_error('Unauthorized', 'unauthorized', 401);
}

$bankAccountsFile = __DIR__ . '/../bank_accounts.json';
$usersFile = __DIR__ . '/../users.json';

// Load bank accounts
$bankAccounts = file_exists($bankAccountsFile) ? json_decode(file_get_contents($bankAccountsFile), true) ?: [] : [];
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) ?: [] : [];

$meId = (int) $_SESSION['user_id'];

// GET: Return user's bank accounts
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $myAccounts = array_values(array_filter($bankAccounts, fn($acc) => $acc['user_id'] == $meId));
    echo json_encode(['accounts' => $myAccounts]);
    exit();
}

// POST: Add or delete bank account
$body = json_decode(file_get_contents('php://input'), true) ?: [];
$token = $body['csrf_token'] ?? '';

if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    respond_error('Invalid CSRF token', 'invalidCsrfToken');
}

$action = $body['action'] ?? '';

if ($action === 'add') {
    $bankName = trim($body['bank_name'] ?? '');
    $accountNumber = trim($body['account_number'] ?? '');
    $accountHolder = trim($body['account_holder'] ?? '');

    if (!$bankName || !$accountNumber || !$accountHolder) {
        respond_error('All fields are required', 'allFieldsRequired');
    }

    if (strlen($accountNumber) < 8) {
        respond_error('Account number must be at least 8 characters', 'accountNumberTooShort');
    }

    $id = uniqid();
    $newAccount = [
        'id' => $id,
        'user_id' => $meId,
        'bank_name' => $bankName,
        'account_number' => $accountNumber,
        'account_holder' => $accountHolder,
        'created_at' => time()
    ];

    $bankAccounts[] = $newAccount;
    file_put_contents($bankAccountsFile, json_encode($bankAccounts, JSON_PRETTY_PRINT), LOCK_EX);

    // Audit log
    include_once __DIR__ . '/../audit.php';
    write_audit('bank_account_added', $meId, $_SESSION['email'] ?? '', $_SESSION['email'] ?? '', ['bank' => $bankName, 'last_4' => substr($accountNumber, -4)]);

    echo json_encode(['success' => true, 'account' => $newAccount]);
    exit();
}

if ($action === 'delete') {
    $accountId = $body['account_id'] ?? '';

    if (!$accountId) {
        respond_error('Account ID required', 'accountIdRequired');
    }

    $bankAccounts = array_values(array_filter($bankAccounts, function($acc) use ($accountId, $meId) {
        return !($acc['id'] === $accountId && $acc['user_id'] == $meId);
    }));

    file_put_contents($bankAccountsFile, json_encode($bankAccounts, JSON_PRETTY_PRINT), LOCK_EX);

    // Audit log
    include_once __DIR__ . '/../audit.php';
    write_audit('bank_account_deleted', $meId, $_SESSION['email'] ?? '', $_SESSION['email'] ?? '', ['account_id' => $accountId]);

    echo json_encode(['success' => true]);
    exit();
}

respond_error('Invalid action', 'invalidAction');
?>
