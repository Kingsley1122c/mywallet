<?php
$usersFile = 'users.json';
$users = json_decode(file_get_contents($usersFile), true);

foreach ($users as &$user) {
    if ($user['role'] === 'admin') {
        $user['password'] = password_hash('EBUka.@1', PASSWORD_DEFAULT);
        break;
    }
}

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
echo "✅ Admin password successfully updated to: EBUka.@1\n";
echo "You can now login with:\n";
echo "Email: okoyechukwuebuka063@gmail.com\n";
echo "Password: EBUka.@1\n";
?>