<?php
session_start();
$usersFile = __DIR__ . '/users.json';
$users = json_decode(file_get_contents($usersFile), true) ?: [];

if (empty($_SESSION['deactivated_user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['deactivated_user_id'];
$user = null;
foreach ($users as $u) {
    if ($u['id'] == $userId) {
        $user = $u;
        break;
    }
}
if (!$user || empty($user['deactivated'])) {
    header('Location: login.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activation_code'])) {
    $code = trim($_POST['activation_code']);
    if (isset($user['activation_code']) && $code === $user['activation_code']) {
        // Reactivate user
        foreach ($users as &$u) {
            if ($u['id'] == $userId) {
                $u['deactivated'] = false;
                $u['deactivate_message'] = null;
                $u['activation_code'] = null;
            }
        }
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
        unset($_SESSION['deactivated_user_id']);
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $user['email'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid activation code.';
    }
}
$whatsapp = 'https://wa.me/18053371249'; // Customer service WhatsApp
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyWallet | <span data-i18n="deactivated-title"></span></title>
    <link rel="stylesheet" href="style.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            width: 100vw;
            overflow: hidden;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            min-width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .deact-card {
            width: 100vw;
            max-width: 420px;
            min-height: 100vh;
            margin: 0;
            background: #fff;
            border-radius: 0 0 32px 32px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.13);
            padding: 48px 32px 32px 32px;
            text-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .mw-title {
            font-size: 28px;
            font-weight: 900;
            color: #6366f1;
            margin-bottom: 18px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            text-shadow: 0 2px 8px #e0e7ff;
        }
        .modern-warning {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }
        .modern-warning-icon {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #b91c1c;
            border-radius: 50%;
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-right: 12px;
            box-shadow: 0 2px 8px #fee2e2;
        }
        .modern-warning-text {
            color: #b91c1c;
            font-size: 22px;
            font-weight: 800;
        }
        .deact-card p {
            color: #374151;
            margin-bottom: 24px;
            font-size: 17px;
            font-weight: 500;
        }
        .wa-btn {
            background: #25d366;
            color: #fff;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 16px;
            margin-bottom: 18px;
            display: inline-block;
            text-decoration: none;
            transition: background 0.2s;
            box-shadow: 0 2px 8px #25d36633;
        }
        .wa-btn:hover {
            background: #128c7e;
        }
        .modern-input {
            margin-bottom: 18px;
            text-align: left;
            width: 100%;
        }
        .modern-input label {
            font-weight: 600;
            font-size: 15px;
            color: #374151;
            margin-bottom: 8px;
            display: block;
        }
        .modern-input input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            font-size: 15px;
        }
        .modern-input input:focus {
            border-color: #667eea;
            outline: none;
        }
        .btn-modern {
            width: 100%;
            padding: 14px 0;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
            box-shadow: 0 2px 8px #667eea33;
        }
        .btn-modern:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .btn-exit {
            width: 100%;
            padding: 12px 0;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            border: none;
            background: linear-gradient(135deg, #e5e7eb 0%, #cbd5e1 100%);
            color: #374151;
            cursor: pointer;
            margin-top: 10px;
            margin-bottom: 0;
            transition: background 0.2s;
            box-shadow: 0 2px 8px #cbd5e133;
        }
        .btn-exit:hover {
            background: linear-gradient(135deg, #cbd5e1 0%, #e5e7eb 100%);
            color: #111827;
        }
        .error {
            background: #fee;
            border-left: 4px solid #f00;
            padding: 12px 16px;
            border-radius: 8px;
            color: #c00;
            font-weight: 600;
            margin-bottom: 20px;
        }
        @media (max-width: 600px) {
            .deact-card {
                border-radius: 0 0 18px 18px;
                padding: 32px 8vw 24px 8vw;
                min-height: 100vh;
            }
            .mw-title {
                font-size: 22px;
            }
        }
    </style>
    <script src="i18n.js"></script>
</head>
<body>
    <div class="deact-card">
        <div class="mw-title" data-i18n="mywallet">mywallet</div>
        <div class="modern-warning">
            <div class="modern-warning-icon">&#9888;</div>
            <div class="modern-warning-text" data-i18n="deactivated-title">Account Deactivated</div>
        </div>
        <p data-i18n="deactivated-desc">Your account is deactivated. Please contact customer service to activate your account.</p>
        <a class="wa-btn" href="<?php echo $whatsapp; ?>" target="_blank" data-i18n="deactivated-contact">Contact Customer Service on WhatsApp</a>
        <form method="post" action="" style="width:100%">
            <div class="modern-input">
                <label for="activation_code" data-i18n="deactivated-code-label">Enter 12-digit Activation Code</label>
                <input type="text" id="activation_code" name="activation_code" pattern="\d{12}" maxlength="12" minlength="12" required data-i18n-placeholder="deactivated-code-placeholder">
            </div>
            <button class="btn-modern" type="submit" data-i18n="deactivated-confirm">Confirm</button>
        </form>
        <button class="btn-exit" onclick="window.location.href='login.php'" data-i18n="deactivated-exit">Exit</button>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() { i18n.render(); });
    </script>
</body>
</html>
