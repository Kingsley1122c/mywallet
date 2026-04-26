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
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$meId = (int) $_SESSION['user_id'];
$me = null; $meIdx = null;
foreach ($users as $i => $u) { if ($u['id'] == $meId) { $me = $u; $meIdx = $i; break; } }
if (!$me) { http_response_code(400); echo json_encode(['error'=>'User not found']); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    if (isset($body['currency'])) {
        $users[$meIdx]['currency'] = $body['currency'];
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
        // Return the updated currency value for immediate frontend update
        echo json_encode(['success'=>true, 'currency'=>$body['currency']]);
        exit();
    }
}
// GET: return user info (currency, first_name, surname, email)

// If action=get_balance, return only the balance (for dashboard.js compatibility)
if (isset($_GET['action']) && $_GET['action'] === 'get_balance') {
    $balance = isset($me['balance']) ? $me['balance'] : 0.00;
    echo json_encode(['balance' => $balance]);
    exit();
}

$currency = isset($me['currency']) ? $me['currency'] : 'USD';
$first_name = isset($me['first_name']) ? $me['first_name'] : '';
$surname = isset($me['surname']) ? $me['surname'] : '';
$email = isset($me['email']) ? $me['email'] : '';
echo json_encode([
    'currency' => $currency,
    'first_name' => $first_name,
    'surname' => $surname,
    'email' => $email,
    'balance' => isset($me['balance']) ? $me['balance'] : 0.00
]);
exit();
