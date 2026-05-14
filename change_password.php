<?php
session_start();

if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$usersFile = __DIR__ . '/users.json';
if (!file_exists($usersFile)) { die('Users database missing.'); }

$users = json_decode(file_get_contents($usersFile), true) ?: [];
$current = null; $currentIdx = null;
foreach ($users as $i => $u) { if ($u['id'] == $_SESSION['user_id']) { $current = $u; $currentIdx = $i; break; } }
if (!$current) { header('Location: login.php'); exit(); }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $new2 = $_POST['new_password2'] ?? '';

    if (!password_verify($old, $current['password'])) { $error = 'Current password is incorrect.'; }
    elseif (strlen($new) < 8) { $error = 'New password must be at least 8 characters.'; }
    elseif ($new !== $new2) { $error = 'New passwords do not match.'; }
    else {
        $users[$currentIdx]['password'] = password_hash($new, PASSWORD_DEFAULT);
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
        include_once __DIR__ . '/audit.php';
        write_audit('password_changed', $current['id'], $current['email'], $current['email'], ['method' => 'user_change']);
        $success = 'Password updated successfully.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Change Password | Mivonta</title>
    <link rel="stylesheet" href="style.css">
    <style>.auth{max-width:420px;margin:60px auto;padding:20px;background:#fff;border-radius:8px}</style>
</head>
<body>
    <main class="container">
        <section class="auth">
            <h2>Change password</h2>
            <?php if ($error): ?><p style="color:#b00020"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
            <?php if ($success): ?><p style="color:#0a8a00"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>
            <form method="post" action="change_password.php">
                <input type="password" name="old_password" placeholder="Current password" required>
                <input type="password" name="new_password" placeholder="New password (min 8 chars)" required>
                <input type="password" name="new_password2" placeholder="Confirm new password" required>
                <button type="submit">Update</button>
            </form>
            <p style="margin-top:10px"><a href="dashboard.php">Back to Dashboard</a></p>
        </section>
    </main>
    <script src="showhide.js?v=20260427c"></script>
</body>
</html>
