<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if user is admin
$usersFile = __DIR__ . '/../users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$currentUserId = (int)$_SESSION['user_id'];
$currentUser = null;
foreach ($users as $u) { if ($u['id'] == $currentUserId) { $currentUser = $u; break; } }

if (!$currentUser || ($currentUser['role'] ?? 'user') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit();
}

$pendingTransfersFile = __DIR__ . '/../pending_transfers.json';
$pendingTransfers = file_exists($pendingTransfersFile) ? json_decode(file_get_contents($pendingTransfersFile), true) : [];
$txFile = __DIR__ . '/../transactions.json';
$transactions = file_exists($txFile) ? json_decode(file_get_contents($txFile), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return all pending transfers
    $pending = array_filter($pendingTransfers, fn($t) => $t['status'] === 'pending');
    echo json_encode(['transfers' => array_values($pending)]);
    exit();
}

// POST actions
$body = json_decode(file_get_contents('php://input'), true) ?: [];
$token = $body['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid CSRF token']);
    exit();
}

$action = $body['action'] ?? '';
$transferId = $body['transfer_id'] ?? '';

if (!$transferId) {
    http_response_code(400);
    echo json_encode(['error'=>'Transfer ID required']);
    exit();
}

// Find transfer
$transferIdx = null;
foreach ($pendingTransfers as $i => $t) {
    if ($t['id'] === $transferId) {
        $transferIdx = $i;
        break;
    }
}

if ($transferIdx === null) {
    http_response_code(400);
    echo json_encode(['error'=>'Transfer not found']);
    exit();
}

$transfer = $pendingTransfers[$transferIdx];

if ($action === 'approve') {
    // Update user balances
    $fromIdx = null;
    $toIdx = null;
    foreach ($users as $i => $u) {
        if ($u['id'] == $transfer['from_user_id']) $fromIdx = $i;
        if ($u['id'] == $transfer['to_user_id']) $toIdx = $i;
    }
    
    if ($fromIdx === null || $toIdx === null) {
        http_response_code(400);
        echo json_encode(['error'=>'User not found']);
        exit();
    }
    
    // Perform the transfer
    $users[$fromIdx]['balance'] = ($users[$fromIdx]['balance'] ?? 0) - $transfer['amount'];
    $users[$toIdx]['balance'] = ($users[$toIdx]['balance'] ?? 0) + $transfer['amount'];
    
    // Create transaction records
    $tx1 = [
        'id'=>uniqid(),
        'user_id'=>$transfer['from_user_id'],
        'time'=>time()*1000,
        'desc'=>"Transfer to {$transfer['to_email']} (approved)",
        'amount'=>-$transfer['amount'],
        'status'=>'completed'
    ];
    $tx2 = [
        'id'=>uniqid(),
        'user_id'=>$transfer['to_user_id'],
        'time'=>time()*1000,
        'desc'=>"Transfer from {$transfer['from_email']} (approved)",
        'amount'=>$transfer['amount'],
        'status'=>'completed'
    ];
    $transactions[] = $tx1;
    $transactions[] = $tx2;
    
    // Update pending transfer status
    $pendingTransfers[$transferIdx]['status'] = 'approved';
    $pendingTransfers[$transferIdx]['approved_at'] = time() * 1000;
    $pendingTransfers[$transferIdx]['approved_by'] = $currentUser['email'];
    
    // Persist all changes
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
    file_put_contents($txFile, json_encode($transactions, JSON_PRETTY_PRINT), LOCK_EX);
    file_put_contents($pendingTransfersFile, json_encode($pendingTransfers, JSON_PRETTY_PRINT), LOCK_EX);
    
    // Audit log
    include_once __DIR__ . '/../audit.php';
    write_audit('transfer_approved', $transfer['from_user_id'], $transfer['from_email'], $currentUser['email'], [
        'from'=>$transfer['from_email'],
        'to'=>$transfer['to_email'],
        'amount'=>$transfer['amount'],
        'transfer_id'=>$transferId
    ]);
    
    // Send email notifications
    include_once __DIR__ . '/email_notifications.php';
    
    // Notify sender
    $senderEmailData = [
        'type' => 'send',
        'amount' => $transfer['amount'],
        'date' => date('F j, Y g:i A'),
        'balance' => $users[$fromIdx]['balance'],
        'recipient' => $transfer['to_email']
    ];
    sendTransactionEmail($transfer['from_email'], $senderEmailData);
    
    // Notify recipient
    $recipientEmailData = [
        'type' => 'receive',
        'amount' => $transfer['amount'],
        'date' => date('F j, Y g:i A'),
        'balance' => $users[$toIdx]['balance'],
        'sender' => $transfer['from_email']
    ];
    sendTransactionEmail($transfer['to_email'], $recipientEmailData);
    
    echo json_encode(['success'=>true, 'message'=>'Transfer approved']);
    exit();
}

if ($action === 'reject') {
    // Refund the amount to sender
    $fromIdx = null;
    foreach ($users as $i => $u) {
        if ($u['id'] == $transfer['from_user_id']) $fromIdx = $i;
    }
    
    if ($fromIdx === null) {
        http_response_code(400);
        echo json_encode(['error'=>'User not found']);
        exit();
    }
    
    // Refund balance (it wasn't deducted since transfer was pending)
    // No balance change needed, just mark as rejected
    
    // Update pending transfer status
    $pendingTransfers[$transferIdx]['status'] = 'rejected';
    $pendingTransfers[$transferIdx]['rejected_at'] = time() * 1000;
    $pendingTransfers[$transferIdx]['rejected_by'] = $currentUser['email'];
    
    // Persist
    file_put_contents($pendingTransfersFile, json_encode($pendingTransfers, JSON_PRETTY_PRINT), LOCK_EX);
    
    // Audit log
    include_once __DIR__ . '/../audit.php';
    write_audit('transfer_rejected', $transfer['from_user_id'], $transfer['from_email'], $currentUser['email'], [
        'from'=>$transfer['from_email'],
        'to'=>$transfer['to_email'],
        'amount'=>$transfer['amount'],
        'transfer_id'=>$transferId
    ]);
    
    echo json_encode(['success'=>true, 'message'=>'Transfer rejected']);
    exit();
}

http_response_code(400);
echo json_encode(['error'=>'Unknown action']);
exit();
?>
