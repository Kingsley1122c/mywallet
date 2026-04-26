<?php
// Setup script to create test users with proper password hashes
$usersFile = __DIR__ . '/users.json';

$users = [
    [
        'id' => 1,
        'first_name' => 'Admin',
        'surname' => 'User',
        'email' => 'admin@example.com',
        'password' => password_hash('AdminPass123', PASSWORD_DEFAULT),
        'role' => 'admin',
        'balance' => 500.00
    ],
    [
        'id' => 2,
        'first_name' => 'John',
        'surname' => 'Doe',
        'email' => 'user@example.com',
        'password' => password_hash('Password123', PASSWORD_DEFAULT),
        'role' => 'user',
        'balance' => 1000.00
    ]
];

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);

// Initialize transactions
$txFile = __DIR__ . '/transactions.json';
$txs = [];
foreach ($users as $u) {
    $txs[] = ['id' => uniqid(), 'user_id' => $u['id'], 'time' => time() * 1000, 'desc' => 'Opening balance', 'amount' => $u['balance']];
}
file_put_contents($txFile, json_encode($txs, JSON_PRETTY_PRINT), LOCK_EX);

echo "Users setup complete!\n";
echo "Test accounts created:\n";
echo "1. Admin User (admin@example.com) / AdminPass123\n";
echo "2. John Doe (user@example.com) / Password123\n";
?>
