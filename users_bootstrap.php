<?php

const ADMIN_LOGIN_EMAIL = 'admin@example.com';

function sanitizeUserRecord(array $user): array
{
    unset($user['plain_password']);
    return $user;
}

function sanitizeUsers(array $users): array
{
    return array_map('sanitizeUserRecord', $users);
}

function defaultBootstrapUsers(): array
{
    return [
        [
            'id' => 1,
            'email' => ADMIN_LOGIN_EMAIL,
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
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
            return sanitizeUsers($seedUsers);
        }
    }

    return sanitizeUsers(defaultBootstrapUsers());
}

function loadUsersStore(string $usersFile): array
{
    if (!file_exists($usersFile)) {
        return [];
    }

    $users = json_decode(file_get_contents($usersFile), true);
    return is_array($users) ? sanitizeUsers($users) : [];
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

    $sanitizedUsers = sanitizeUsers($currentUsers);
    if ($sanitizedUsers !== $currentUsers) {
        file_put_contents($usersFile, json_encode($sanitizedUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        return $sanitizedUsers;
    }

    return $currentUsers;
}

function ensureAdminLogin(string $usersFile): array
{
    $users = loadUsersStore($usersFile);
    $adminEmail = strtolower(ADMIN_LOGIN_EMAIL);
    $updated = false;

    foreach ($users as $index => $user) {
        $email = strtolower(trim((string)($user['email'] ?? '')));
        if ($email !== $adminEmail) {
            continue;
        }

        $users[$index]['email'] = ADMIN_LOGIN_EMAIL;
        $users[$index]['role'] = 'admin';
        $updated = true;
        break;
    }

    if (!$updated) {
        $seedUsers = loadBootstrapUsersSeed();
        $adminUser = null;
        foreach ($seedUsers as $seedUser) {
            $email = strtolower(trim((string)($seedUser['email'] ?? '')));
            if ($email === $adminEmail) {
                $adminUser = $seedUser;
                break;
            }
        }

        if ($adminUser === null) {
            $adminUser = defaultBootstrapUsers()[0];
        }

        $adminUser = sanitizeUserRecord($adminUser);
        $adminUser['email'] = ADMIN_LOGIN_EMAIL;
        $adminUser['role'] = 'admin';
        $users[] = $adminUser;
    }

    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    return $users;
}