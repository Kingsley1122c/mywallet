<?php
session_start();

include_once __DIR__ . '/url_helpers.php';

$usersFile = __DIR__ . '/users.json';
$resetsFile = __DIR__ . '/password_resets.json';

if (!file_exists($resetsFile)) { file_put_contents($resetsFile, json_encode([], JSON_PRETTY_PRINT)); }

$users = file_exists($usersFile) ? (json_decode(file_get_contents($usersFile), true) ?: []) : [];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) { $error = 'Enter a valid email.'; }
    else {
        $found = null;
        foreach ($users as $u) { if (strtolower($u['email']) === strtolower($email)) { $found = $u; break; } }
        if (!$found) {
            // For security, do not reveal whether the account exists. Show message anyway.
            $message = 'If an account with that email exists, a reset link was created.';
        } else {
            // Create token
            $token = bin2hex(random_bytes(24));
            $expires = time() + 3600; // 1 hour
            $resets = json_decode(file_get_contents($resetsFile), true) ?: [];
            $resets[] = ['token' => $token, 'user_id' => $found['id'], 'expires' => $expires];
            file_put_contents($resetsFile, json_encode($resets, JSON_PRETTY_PRINT), LOCK_EX);

            $resetLink = app_url('reset.php?token=' . urlencode($token));

            // For local testing we will show the link on screen and also append it to a local file for convenience
            file_put_contents(__DIR__ . '/reset_links.txt', date('c') . " - " . $email . " - " . $resetLink . PHP_EOL, FILE_APPEND | LOCK_EX);

            $message = 'A reset link was generated. For local testing the link is shown below.';
            $message .= '\n\n' . $resetLink;

            // Audit: password reset requested
            include_once __DIR__ . '/audit.php';
            write_audit('password_reset_requested', $found['id'], $found['email'], $found['email'], ['token' => $token, 'expires' => $expires]);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Forgot Password | MyWallet</title>
    <link rel="stylesheet" href="style.css">
    <style>.auth{max-width:420px;margin:60px auto;padding:20px;background:#fff;border-radius:8px}</style>
</head>
<body>
    <main class="container">
        <section class="auth">
            <h2>Forgot password</h2>
            <?php if ($error): ?><p style="color:#b00020"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
            <?php if ($message): ?><pre style="white-space:pre-wrap;background:#f3f3f3;padding:8px;border-radius:6px"><?php echo htmlspecialchars($message); ?></pre><?php endif; ?>
            <form method="post" action="forgot.php">
                <input type="email" name="email" placeholder="Your email" required>
                <button type="submit">Send reset link</button>
            </form>
            <p style="margin-top:10px"><a href="login.php">Back to Sign in</a></p>
        </section>
    </main>
</body>
</html>
