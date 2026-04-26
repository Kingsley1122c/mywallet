<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$messageId = $input['message_id'] ?? '';

if (!$messageId) {
    echo json_encode(['success' => false, 'error' => 'Missing message ID']);
    exit();
}

$messagesFile = __DIR__ . '/../admin_messages.json';

if (!file_exists($messagesFile)) {
    echo json_encode(['success' => false, 'error' => 'Messages file not found']);
    exit();
}

$messages = json_decode(file_get_contents($messagesFile), true);
if (!is_array($messages)) $messages = [];

// Mark message as read
$updated = false;
foreach ($messages as &$msg) {
    if ($msg['id'] == $messageId && $msg['user_id'] == $_SESSION['user_id']) {
        $msg['read'] = true;
        $updated = true;
        break;
    }
}

if ($updated) {
    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT), LOCK_EX);
}

echo json_encode(['success' => $updated]);
