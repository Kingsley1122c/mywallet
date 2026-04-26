<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$userId = $_SESSION['user_id'];
$messagesFile = __DIR__ . '/../admin_messages.json';

if (!file_exists($messagesFile)) {
    echo json_encode(['success' => false, 'message' => null]);
    exit();
}

$messages = json_decode(file_get_contents($messagesFile), true);
if (!is_array($messages)) $messages = [];

// Find unread message for this user
$userMessage = null;
foreach ($messages as $msg) {
    if ($msg['user_id'] == $userId && !$msg['read']) {
        $userMessage = $msg;
        break;
    }
}

echo json_encode([
    'success' => true,
    'message' => $userMessage
]);
