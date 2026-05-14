<?php
session_start();

if (empty($_SESSION['deactivated_user_id'])) {
    header('Location: login.php');
    exit();
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
        .notice-actions{display:grid;gap:12px;margin-top:24px}
        .notice-link{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:999px;padding:13px 18px;font-weight:800}
        .notice-link.primary{background:linear-gradient(135deg,#175cd3 0%,#4f8cff 100%);color:#fff}
        .notice-link.secondary{background:#eef4fb;color:#142033}
    </style>
</head>
<body>
    <div class="notice-card">
        <div class="notice-badge">!</div>
        <h1>Account unavailable</h1>
        <p>This account is currently unavailable for sign-in.</p>
        <p>If you need help, contact support for general assistance with your account status. Sensitive actions are not completed through codes issued by customer support.</p>
        <div class="notice-actions">
            <a class="notice-link primary" href="mailto:support@mivonta.com">Email support</a>
            <a class="notice-link secondary" href="login.php">Back to sign in</a>
        </div>
    </div>
</body>
</html>
HTML;

exit();
