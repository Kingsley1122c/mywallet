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

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$usersFile = __DIR__ . '/../users.json';
$txFile = __DIR__ . '/../transactions.json';

$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$transactions = file_exists($txFile) ? json_decode(file_get_contents($txFile), true) : [];

$meId = (int) $_SESSION['user_id'];
$me = null; $meIdx = null;
foreach ($users as $i => $u) { if ($u['id'] == $meId) { $me = $u; $meIdx = $i; break; } }
if (!$me) { http_response_code(400); echo json_encode(['error'=>'User not found']); exit(); }

// ensure balance field exists
if (!isset($me['balance'])) {
    $me['balance'] = 20000450.75;
    $users[$meIdx]['balance'] = $me['balance'];
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Support for latest_incoming alert
    if (isset($_GET['action']) && $_GET['action'] === 'latest_incoming') {
        // Find the latest incoming transaction (amount > 0, not from self)
        $incoming = array_filter($transactions, function($t) use ($meId) {
            return $t['user_id'] == $meId && isset($t['amount']) && $t['amount'] > 0 && isset($t['desc']) && stripos($t['desc'], 'received from') !== false;
        });
        if (!empty($incoming)) {
            usort($incoming, function($a, $b) { return ($b['time'] ?? 0) <=> ($a['time'] ?? 0); });
            $tx = $incoming[0];
            // Try to extract sender name/email from desc
            $sender_name = '';
            $sender_email = '';
            if (preg_match('/Received from ([^\(]+) \(([^\)]+)\)/', $tx['desc'], $m)) {
                $sender_name = trim($m[1]);
                $sender_email = trim($m[2]);
            }
            echo json_encode([
                'amount' => $tx['amount'],
                'sender' => $sender_name,
                'sender_email' => $sender_email,
                'sender_name' => $sender_name,
                'time' => $tx['time'],
                'txId' => $tx['id']
            ]);
            exit();
        } else {
            echo json_encode([]); exit();
        }
    }
    // Return balance, user's latest 20 transactions, and currency
    $myTx = array_values(array_filter($transactions, fn($t) => $t['user_id'] == $meId));
    // Sort by time descending
    usort($myTx, function($a, $b) { return ($b['time'] ?? 0) <=> ($a['time'] ?? 0); });
    $myTx = array_slice($myTx, 0, 20);
    $currency = isset($me['currency']) ? $me['currency'] : 'USD';
    echo json_encode(['balance' => (float)$me['balance'], 'transactions' => $myTx, 'currency' => $currency]);
    exit();
}

// POST actions: expect JSON body
$body = json_decode(file_get_contents('php://input'), true) ?: [];
$token = $body['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(400); echo json_encode(['error'=>'Invalid CSRF token']); exit();
}

$action = $body['action'] ?? '';
if ($action === 'add') {
    $amount = floatval($body['amount'] ?? 0);
    if ($amount <= 0) { http_response_code(400); echo json_encode(['error'=>'Invalid amount']); exit(); }
    // update balance
    $users[$meIdx]['balance'] = ($users[$meIdx]['balance'] ?? 0) + $amount;
    // append transaction
    $tx = ['id'=>uniqid(), 'user_id'=>$meId, 'time'=>time()*1000, 'desc'=>'Money added', 'amount'=>round($amount,2)];
    $transactions[] = $tx;
    // persist
    file_put_contents($txFile, json_encode($transactions, JSON_PRETTY_PRINT), LOCK_EX);
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
    include_once __DIR__ . '/../audit.php';
    write_audit('money_added', $meId, $me['email'], $me['email'], ['amount'=>$amount]);
    
    // Send email notification
    include_once __DIR__ . '/email_notifications.php';
    $emailData = [
        'type' => 'add',
        'amount' => $amount,
        'date' => date('F j, Y g:i A'),
        'balance' => $users[$meIdx]['balance'],
        'method' => $body['payment_method'] ?? 'Bank Card'
    ];
    sendTransactionEmail($me['email'], $emailData);
    
    echo json_encode(['balance'=>$users[$meIdx]['balance'],'tx'=>$tx]); exit();
}

if ($action === 'send') {
    $to = trim($body['to'] ?? '');
    $amount = floatval($body['amount'] ?? 0);
    if ($amount <= 0 || $to === '') { http_response_code(400); echo json_encode(['error'=>'Invalid request']); exit(); }
    if ($amount > ($users[$meIdx]['balance'] ?? 0)) { http_response_code(400); echo json_encode(['error'=>'Insufficient balance']); exit(); }
    // find recipient by email
    $targetIdx = null; $target = null;
    foreach ($users as $i => $u) { if (strtolower($u['email']) === strtolower($to)) { $target = $u; $targetIdx = $i; break; } }
    if (!$target) { http_response_code(400); echo json_encode(['error'=>'Recipient not found']); exit(); }

    // Direct transfer - deduct from sender, add to recipient
    $users[$meIdx]['balance'] -= $amount;
    $users[$targetIdx]['balance'] = ($users[$targetIdx]['balance'] ?? 0) + $amount;
    
    // Get sender name
    $senderName = $me['email'];
    if (!empty($me['name'])) {
        $senderName = $me['name'];
    } elseif (!empty($me['first_name']) && !empty($me['surname'])) {
        $senderName = $me['first_name'] . ' ' . $me['surname'];
    } elseif (!empty($me['first_name'])) {
        $senderName = $me['first_name'];
    } elseif (!empty($me['surname'])) {
        $senderName = $me['surname'];
    } else {
        // Extract name from email (part before @)
        $senderName = explode('@', $me['email'])[0];
    }
    
    // Get recipient name
    $recipientName = $target['email'];
    if (!empty($target['name'])) {
        $recipientName = $target['name'];
    } else {
        $recipientName = explode('@', $target['email'])[0];
    }
    
    // Create transaction records
    $now = time() * 1000;
    $dateTime = date('F j, Y g:i A');
    
    $tx1 = [
        'id'=>uniqid(), 
        'user_id'=>$meId, 
        'time'=>$now, 
        'desc'=>"Sent to " . $recipientName . " (" . $target['email'] . ")", 
        'amount'=>-round($amount,2)
    ];
    $tx2 = [
        'id'=>uniqid(), 
        'user_id'=>$target['id'], 
        'time'=>$now, 
        'desc'=>"Received from " . $senderName . " (" . $me['email'] . ")", 
        'amount'=>round($amount,2)
    ];
    $transactions[] = $tx1;
    $transactions[] = $tx2;
    
    // Persist changes
    file_put_contents($txFile, json_encode($transactions, JSON_PRETTY_PRINT), LOCK_EX);
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
    
    include_once __DIR__ . '/../audit.php';
    write_audit('transfer_completed', $meId, $me['email'], $me['email'], ['to'=>$target['email'],'amount'=>$amount]);
    
    // Output success response FIRST before any email attempts
    $response = ['success'=>true, 'balance'=>$users[$meIdx]['balance'], 'tx'=>$tx1];
    echo json_encode($response);
    
    // Send email notifications AFTER response (suppressed to prevent JSON errors)
    if (file_exists(__DIR__ . '/email_notifications.php')) {
        ob_start(); // Capture any email output
        try {
            include_once __DIR__ . '/email_notifications.php';
            // Notify sender
            $senderEmailData = [
                'type' => 'send',
                'amount' => $amount,
                'date' => $dateTime,
                'balance' => $users[$meIdx]['balance'],
                'recipient' => $recipientName,
                'recipient_email' => $target['email']
            ];
            @sendTransactionEmail($me['email'], $senderEmailData);
            
            // Notify recipient
            $recipientEmailData = [
                'type' => 'receive',
                'amount' => $amount,
                'date' => $dateTime,
                'balance' => $users[$targetIdx]['balance'],
                'sender' => $senderName,
                'sender_email' => $me['email']
            ];
            @sendTransactionEmail($target['email'], $recipientEmailData);
        } catch (Exception $e) {
            // Silently fail on email errors
        }
        ob_end_clean(); // Discard any email output
    }
    
    exit();
}

if ($action === 'withdraw') {
    $amount = floatval($body['amount'] ?? 0);
    $bankAccountId = $body['bank_account_id'] ?? '';
    
    if ($amount <= 0) { http_response_code(400); echo json_encode(['error'=>'Invalid amount']); exit(); }
    if (!$bankAccountId) { http_response_code(400); echo json_encode(['error'=>'Bank account required']); exit(); }
    if ($amount > ($users[$meIdx]['balance'] ?? 0)) { http_response_code(400); echo json_encode(['error'=>'Insufficient balance']); exit(); }
    
    // Verify bank account belongs to user
    $bankAccountsFile = __DIR__ . '/../bank_accounts.json';
    $bankAccounts = file_exists($bankAccountsFile) ? json_decode(file_get_contents($bankAccountsFile), true) : [];
    $bankAccount = null;
    foreach ($bankAccounts as $ba) {
        if ($ba['id'] === $bankAccountId && $ba['user_id'] == $meId) {
            $bankAccount = $ba;
            break;
        }
    }
    if (!$bankAccount) { http_response_code(400); echo json_encode(['error'=>'Bank account not found']); exit(); }
    
    // Process withdrawal
    $users[$meIdx]['balance'] -= $amount;
    $tx = ['id'=>uniqid(), 'user_id'=>$meId, 'time'=>time()*1000, 'desc'=>"Withdrawal to {$bankAccount['bank_name']} ({$bankAccount['account_number']})", 'amount'=>-round($amount,2)];
    $transactions[] = $tx;
    
    // persist
    file_put_contents($txFile, json_encode($transactions, JSON_PRETTY_PRINT), LOCK_EX);
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
    
    include_once __DIR__ . '/../audit.php';
    write_audit('withdrawal', $meId, $me['email'], $me['email'], ['bank'=>$bankAccount['bank_name'], 'amount'=>$amount, 'last_4'=>substr($bankAccount['account_number'], -4)]);
    
    // Send email notification
    include_once __DIR__ . '/email_notifications.php';
    $emailData = [
        'type' => 'withdraw',
        'amount' => $amount,
        'date' => date('F j, Y g:i A'),
        'balance' => $users[$meIdx]['balance']
    ];
    sendTransactionEmail($me['email'], $emailData);
    
    echo json_encode(['success'=>true,'balance'=>$users[$meIdx]['balance'],'tx'=>$tx]); exit();
}

http_response_code(400); echo json_encode(['error'=>'Unknown action']); exit();
