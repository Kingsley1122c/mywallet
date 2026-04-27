<?php

function defaultBootstrapUsers(): array
{
    return [
        [
            'id' => 1,
            'email' => 'admin@example.com',
            'password' => password_hash('AdminPass123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'balance' => 500.00,
        ],
        [
            'id' => 2,
            'email' => 'user@example.com',
            'password' => password_hash('Password123', PASSWORD_DEFAULT),
            'role' => 'user',
            'balance' => 1000.00,
        ],
    ];
}

function loadBootstrapUsersSeed(): array
{
    $seedFile = __DIR__ . '/users.seed.json';
    if (file_exists($seedFile)) {
        $seedUsers = json_decode(file_get_contents($seedFile), true);
        if (is_array($seedUsers) && !empty($seedUsers)) {
            return $seedUsers;
        }
    }

    return defaultBootstrapUsers();
}

function loadUsersStore(string $usersFile): array
{
    if (!file_exists($usersFile)) {
        return [];
    }

    $users = json_decode(file_get_contents($usersFile), true);
    return is_array($users) ? $users : [];
}

function isExampleUsersStore(array $users): bool
{
    if (count($users) !== 2) {
        return false;
    }

    $emails = [];
    foreach ($users as $user) {
        $emails[] = strtolower(trim((string)($user['email'] ?? '')));
    }

    sort($emails);
    return $emails === ['admin@example.com', 'user@example.com'];
}

function mergeUsersFromSeed(array $currentUsers, array $seedUsers): array
{
    $emails = [];
    foreach ($currentUsers as $user) {
        $emails[strtolower(trim((string)($user['email'] ?? '')))] = true;
    }

    $mergedUsers = $currentUsers;
    foreach ($seedUsers as $seedUser) {
        $email = strtolower(trim((string)($seedUser['email'] ?? '')));
        if ($email === '' || isset($emails[$email])) {
            continue;
        }

        $mergedUsers[] = $seedUser;
        $emails[$email] = true;
    }

    return $mergedUsers;
}

function repairUsersStore(string $usersFile): array
{
    $currentUsers = loadUsersStore($usersFile);
    $seedUsers = loadBootstrapUsersSeed();

    if (empty($currentUsers)) {
        file_put_contents($usersFile, json_encode($seedUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        return $seedUsers;
    }

    if (isExampleUsersStore($currentUsers) && count($seedUsers) > count($currentUsers)) {
        $mergedUsers = mergeUsersFromSeed($currentUsers, $seedUsers);
        if (count($mergedUsers) > count($currentUsers)) {
            file_put_contents($usersFile, json_encode($mergedUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
            return $mergedUsers;
        }
    }

    return $currentUsers;
}