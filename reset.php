<?php
session_start();

$resetsFile = __DIR__ . '/password_resets.json';
$usersFile = __DIR__ . '/users.json';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$error = '';
$success = '';

if (!$token) { $error = 'Missing token.'; }

// Load tokens
$resets = file_exists($resetsFile) ? (json_decode(file_get_contents($resetsFile), true) ?: []) : [];

// Find matching token
$match = null; $matchIdx = null;
foreach ($resets as $i => $r) { if (hash_equals($r['token'], $token)) { $match = $r; $matchIdx = $i; break; } }

if (!$match) { $error = 'Invalid or expired token.'; }
elseif ($match['expires'] < time()) { $error = 'Token expired.'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if (strlen($password) < 8) { $error = 'Password must be at least 8 characters.'; }
    elseif ($password !== $password2) { $error = 'Passwords do not match.'; }
    else {
        // Update user's password
        if (!file_exists($usersFile)) { $error = 'Users database missing.'; }
        else {
            $users = json_decode(file_get_contents($usersFile), true) ?: [];
            foreach ($users as $i => $u) {
                if ($u['id'] == $match['user_id']) {
                    $users[$i]['password'] = password_hash($password, PASSWORD_DEFAULT);
                    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
                    // remove token
                    array_splice($resets, $matchIdx, 1);
                    file_put_contents($resetsFile, json_encode($resets, JSON_PRETTY_PRINT), LOCK_EX);

                    // Audit: password reset completed via token
                    include_once __DIR__ . '/audit.php';
                    write_audit('password_reset_completed', $users[$i]['id'], $users[$i]['email'], 'reset_via_token', []);

                    $success = 'Password updated. You can now sign in.';
                    break;
                }
            }
            if (!$success) $error = 'User not found.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reset Password | Mivonta</title>
    <link rel="stylesheet" href="style.css">
    <style>.auth{max-width:420px;margin:60px auto;padding:20px;background:#fff;border-radius:8px}</style>
</head>
<body>
    <main class="container">
        <section class="auth">
            <h2>Reset password</h2>
            <?php if ($error): ?><p style="color:#b00020"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
            <?php if ($success): ?><p style="color:#0a8a00"><?php echo htmlspecialchars($success); ?></p><p><a href="login.php">Sign in</a></p><?php else: ?>
                <?php if (!$error): ?>
                    <form method="post" action="reset.php">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="password" name="password" placeholder="New password (min 8 chars)" required>
                        <input type="password" name="password2" placeholder="Confirm new password" required>
                        <button type="submit">Update password</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
    <script src="showhide.js?v=20260427c"></script>
</body>
</html>
