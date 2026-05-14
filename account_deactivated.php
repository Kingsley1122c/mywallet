<?php
session_start();

$usersFile = __DIR__ . '/users.json';
$users = json_decode(file_get_contents($usersFile), true) ?: [];

if (empty($_SESSION['deactivated_user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = (int) $_SESSION['deactivated_user_id'];
$user = null;
foreach ($users as $candidate) {
    if ((int) ($candidate['id'] ?? 0) === $userId) {
        $user = $candidate;
        break;
    }
}

if (!$user || empty($user['deactivated'])) {
    unset($_SESSION['deactivated_user_id']);
    header('Location: login.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['activation_code'] ?? '');

    if (!preg_match('/^\d{12}$/', $code)) {
        $error = 'Enter a valid 12-digit activation code.';
    } elseif (empty($user['activation_code']) || !hash_equals((string) $user['activation_code'], $code)) {
        $error = 'Invalid activation code.';
    } else {
        foreach ($users as &$candidate) {
            if ((int) ($candidate['id'] ?? 0) === $userId) {
                $candidate['deactivated'] = false;
                $candidate['deactivate_message'] = null;
                $candidate['activation_code'] = null;
                $user = $candidate;
                break;
            }
        }
        unset($candidate);

        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

        unset($_SESSION['deactivated_user_id']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $user['email'] ?? '';

        header('Location: dashboard.php');
        exit();
    }
}

echo <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Unavailable | Mivonta</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(180deg,#eef4fb 0%,#e2eaf5 100%);color:#142033;font-family:inherit}
        .notice-card{width:min(460px,100%);background:#fff;border-radius:28px;padding:36px 32px;box-shadow:0 24px 60px rgba(20,32,51,.12);text-align:center}
        .notice-badge{width:70px;height:70px;margin:0 auto 18px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#eaf2ff;color:#175cd3;font-size:34px;font-weight:900}
        h1{margin:0 0 10px;font-size:28px}
        p{margin:0 0 16px;color:#5b6b80;line-height:1.7}
        .notice-message{margin-bottom:20px;padding:14px 16px;border-radius:16px;background:#f5f8fc;color:#425466}
        .notice-form{text-align:left;margin-top:12px}
        .notice-form label{display:block;margin:0 0 8px;font-size:14px;font-weight:700;color:#142033}
        .notice-form input{width:100%;box-sizing:border-box;padding:14px 16px;border-radius:16px;border:1px solid #c9d6e8;font-size:16px;letter-spacing:.08em}
        .notice-form input:focus{outline:none;border-color:#175cd3;box-shadow:0 0 0 4px rgba(23,92,211,.12)}
        .notice-error{margin:16px 0 0;padding:12px 14px;border-radius:14px;background:#fff1f2;color:#be123c;font-weight:700}
        .notice-actions{display:grid;gap:12px;margin-top:24px}
        .notice-link{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:999px;padding:13px 18px;font-weight:800;border:none;cursor:pointer;font-size:15px}
        .notice-link.primary{background:linear-gradient(135deg,#175cd3 0%,#4f8cff 100%);color:#fff}
        .notice-link.secondary{background:#eef4fb;color:#142033}
    </style>
</head>
<body>
    <div class="notice-card">
        <div class="notice-badge">!</div>
        <h1>Account unavailable</h1>
        <p>This account is currently unavailable for sign-in.</p>
        <p>Use your 12-digit activation code to restore access. If you need help, contact support for account-status assistance.</p>
HTML;

if (!empty($user['deactivate_message'])) {
    echo '<div class="notice-message">' . htmlspecialchars($user['deactivate_message']) . '</div>';
}

echo <<<'HTML'
        <form class="notice-form" method="post" action="">
            <label for="activation_code">Enter 12-digit Activation Code</label>
            <input type="text" id="activation_code" name="activation_code" inputmode="numeric" pattern="\d{12}" minlength="12" maxlength="12" autocomplete="one-time-code" required>
HTML;

if ($error !== '') {
    echo '<div class="notice-error">' . htmlspecialchars($error) . '</div>';
}

echo <<<'HTML'
            <div class="notice-actions">
                <button class="notice-link primary" type="submit">Activate account</button>
                <a class="notice-link secondary" href="mailto:support@mivonta.com">Email support</a>
                <a class="notice-link secondary" href="login.php">Back to sign in</a>
            </div>
        </form>
    </div>
</body>
</html>
HTML;

exit();
