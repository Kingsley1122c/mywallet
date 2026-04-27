<?php
require_once __DIR__ . '/local_only.php';

// Change Admin Login Info
// Run this file once: http://localhost:8000/change_admin.php

$usersFile = 'users.json';
$users = json_decode(file_get_contents($usersFile), true);

// NEW ADMIN CREDENTIALS - CHANGE THESE:
$newAdminEmail = 'okoyechukwuebuka063@gmail.com';  // ← Change this
$newAdminPassword = 'EBUka.@1';          // ← Change this
$newAdminFirstName = 'Ebuka';                     // ← Change this
$newAdminSurname = 'Okoye';                     // ← Change this

// Find and update admin user
foreach ($users as &$user) {
    if ($user['role'] === 'admin') {
        $user['email'] = $newAdminEmail;
        $user['password'] = password_hash($newAdminPassword, PASSWORD_DEFAULT);
        $user['first_name'] = $newAdminFirstName;
        $user['surname'] = $newAdminSurname;
        break;
    }
}

// Save changes
file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

echo "✅ Admin credentials updated successfully!<br><br>";
echo "<strong>New Admin Login:</strong><br>";
echo "Email: <code>$newAdminEmail</code><br>";
echo "Password: <code>$newAdminPassword</code><br><br>";
echo "<a href='login.html'>Go to Login Page</a><br>";
echo "<a href='admin.php'>Go to Admin Dashboard</a><br><br>";
echo "<small style='color:#999'>You can delete this file (change_admin.php) after use.</small>";
?>
