<?php
session_start();

$usersFile = __DIR__ . '/users.json';

// Ensure users file exists
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([], JSON_PRETTY_PRINT));
}

$users = json_decode(file_get_contents($usersFile), true) ?: [];
$error = '';
$success = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Invalid request.';
    } else {
        $first_name = trim($_POST['first_name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $referral_code = strtoupper(trim($_POST['referral_code'] ?? ''));

        if (!$first_name) {
            $error = 'Please enter your first name.';
        } elseif (!$surname) {
            $error = 'Please enter your surname.';
        } elseif (!$phone) {
            $error = 'Please enter your phone number.';
        } elseif (!$email) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($password !== $password2) {
            $error = 'Passwords do not match.';
        } else {
            // Validate referral code if provided
            $referrer = null;
            if (!empty($referral_code)) {
                $found = false;
                foreach ($users as $u) {
                    if (isset($u['referral_code']) && $u['referral_code'] === $referral_code) {
                        $referrer = $u;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $error = 'Invalid referral code. Please check and try again.';
                }
            }

            if (!$error) {
                // Check uniqueness
                $exists = false;
                foreach ($users as $u) {
                    if (strtolower($u['email']) === strtolower($email)) { 
                        $exists = true; 
                        $error = 'An account with that email already exists.';
                        break; 
                    }
                    if (isset($u['phone']) && $u['phone'] === $phone) { 
                        $exists = true; 
                        $error = 'An account with that phone number already exists.';
                        break; 
                    }
                }

                if (!$exists) {
                    // Assign ID
                    $ids = array_column($users, 'id');
                    $nextId = $ids ? max($ids) + 1 : 1;

                    // Generate unique referral code for new user
                    $newReferralCode = strtoupper(substr($first_name, 0, 3) . substr($surname, 0, 3) . rand(100, 999));
                    
                    // Ensure uniqueness of the referral code
                    $codeExists = true;
                    while ($codeExists) {
                        $codeExists = false;
                        foreach ($users as $u) {
                            if (isset($u['referral_code']) && $u['referral_code'] === $newReferralCode) {
                                $codeExists = true;
                                $newReferralCode = strtoupper(substr($first_name, 0, 3) . substr($surname, 0, 3) . rand(100, 999));
                                break;
                            }
                        }
                    }

                    $new = [
                        'id' => $nextId,
                        'first_name' => $first_name,
                        'surname' => $surname,
                        'phone' => $phone,
                        'email' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => 'user',
                        'referral_code' => $newReferralCode
                    ];

                    $users[] = $new;
                    
                    // Only give welcome bonus if a valid referral code was used
                    if ($referrer) {
                        $bonus = 10.00; // welcome bonus (USD) for using referral code
                        $referrerBonus = 5.00; // bonus for the referrer
                        
                        // Save user first
                        if (false === file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX)) {
                            $error = 'Could not save user. Try again.';
                        } else {
                            // Award welcome bonus to new user
                            $txFile = __DIR__ . '/transactions.json';
                            $txs = file_exists($txFile) ? json_decode(file_get_contents($txFile), true) : [];
                            $txs[] = ['id' => uniqid(), 'user_id' => $new['id'], 'time' => time() * 1000, 'desc' => 'Referral welcome bonus', 'amount' => $bonus];
                            
                            // Award referral bonus to referrer
                            $txs[] = ['id' => uniqid(), 'user_id' => $referrer['id'], 'time' => time() * 1000, 'desc' => 'Referral bonus for inviting ' . $first_name . ' ' . $surname, 'amount' => $referrerBonus];
                            file_put_contents($txFile, json_encode($txs, JSON_PRETTY_PRINT), LOCK_EX);

                            // Update balances for both users
                            $users = json_decode(file_get_contents($usersFile), true);
                            foreach ($users as $i => $u) { 
                                if ($u['id'] == $new['id']) { 
                                    $users[$i]['balance'] = $bonus; 
                                } elseif ($u['id'] == $referrer['id']) {
                                    $users[$i]['balance'] = ($u['balance'] ?? 0) + $referrerBonus;
                                }
                            }
                            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
                            
                            include_once __DIR__ . '/audit.php';
                            write_audit('referral_welcome_bonus', $new['id'], $new['email'], 'system', ['amount' => $bonus, 'referrer_id' => $referrer['id'], 'referral_code' => $referral_code]);
                            write_audit('referrer_bonus', $referrer['id'], $referrer['email'], 'system', ['amount' => $referrerBonus, 'new_user_id' => $new['id']]);
                            
                            // Auto-login
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $new['id'];
                            $_SESSION['email'] = $new['email'];
                            // Refresh token to avoid reuse
                            unset($_SESSION['csrf_token']);
                            header('Location: dashboard.php');
                            exit();
                        }
                    } else {
                        // No referral code provided - create account without bonus
                        if (false === file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX)) {
                            $error = 'Could not save user. Try again.';
                        } else {
                            // Set initial balance to 0 (no bonus)
                            $users = json_decode(file_get_contents($usersFile), true);
                            foreach ($users as $i => $u) { 
                                if ($u['id'] == $new['id']) { 
                                    $users[$i]['balance'] = 0; 
                                    break;
                                }
                            }
                            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
                            
                            include_once __DIR__ . '/audit.php';
                            write_audit('user_registered', $new['id'], $new['email'], 'system', ['no_referral' => true]);
                            
                            // Send welcome email
                            if (file_exists(__DIR__ . '/api/email_notifications.php')) {
                                include_once __DIR__ . '/api/email_notifications.php';
                                try {
                                    $welcomeEmailData = [
                                        'type' => 'welcome',
                                        'name' => $first_name . ' ' . $surname,
                                        'email' => $email,
                                        'date' => date('F j, Y g:i A'),
                                        'referral_code' => $newReferralCode
                                    ];
                                    sendWelcomeEmail($email, $welcomeEmailData);
                                } catch (Exception $e) {
                                    // Silently fail - don't prevent registration
                                }
                            }
                            
                            // Auto-login
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $new['id'];
                            $_SESSION['email'] = $new['email'];
                            // Refresh token to avoid reuse
                            unset($_SESSION['csrf_token']);
                            header('Location: dashboard.php');
                            exit();
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Create account | mywallet</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Apply theme immediately to prevent flash
        (function() {
            const theme = localStorage.getItem('mw_theme') || 'light';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = theme === 'dark' || (theme === 'auto' && prefersDark);
            if (isDark) document.documentElement.classList.add('dark-mode');
        })();
    </script>
    <style>
        .dark-mode body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        
        .dark-mode .auth-topbar {
            background: rgba(30, 41, 59, 0.8);
        }
        
        .dark-mode .auth-card {
            background: #1e293b;
            color: #f1f5f9;
        }
        
        .dark-mode .auth-card h2 {
            color: #f1f5f9;
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .dark-mode .auth-card p {
            color: #cbd5e1;
        }
        
        .dark-mode .modern-input label {
            color: #cbd5e1;
        }
        
        .dark-mode .modern-input input {
            background: #334155;
            color: #f1f5f9;
            border-color: #475569;
        }
        
        .dark-mode .modern-input input:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }
        
        .dark-mode .auth-footer {
            border-top-color: #334155;
            color: #cbd5e1;
        }
        
        .dark-mode .terms-box {
            background: #334155;
            border-color: #475569;
        }
        
        .dark-mode .terms-text {
            color: #cbd5e1;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .auth-topbar {
            padding: 24px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .auth-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .auth-logo-icon {
            width: 44px;
            height: 44px;
            background: rgba(255,255,255,0.25);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 14px;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .auth-logo h2 {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }
        .auth-home-link {
            padding: 10px 24px;
            border-radius: 24px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .auth-home-link:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        .auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .auth-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.25);
            padding: 48px;
            max-width: 480px;
            width: 100%;
        }
        .auth-card h2 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .auth-card p {
            color: #6b7280;
            margin-bottom: 32px;
            font-size: 15px;
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
        .success {
            background: #efe;
            border-left: 4px solid #0f0;
            padding: 12px 16px;
            border-radius: 8px;
            color: #060;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .modern-input {
            margin-bottom: 20px;
        }
        .modern-input label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: #374151;
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper svg {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            z-index: 1;
        }
        .modern-input input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .modern-input input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        }
        .auth-actions {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 24px;
        }
        .btn-modern {
            width: 100%;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-modern.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 8px 24px rgba(102,126,234,0.4);
        }
        .btn-modern.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(102,126,234,0.5);
        }
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        .auth-footer p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .auth-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
        .terms-text {
            background: #f3f4f6;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 16px;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.6;
        }
        .terms-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .auth-card {
                padding: 32px 24px;
                width: 90%;
            }
            .auth-topbar {
                padding: 20px;
            }
            .auth-logo h2 {
                font-size: 16px;
            }
            .auth-logo-icon {
                width: 36px;
                height: 36px;
                font-size: 12px;
            }
            .auth-container {
                padding: 20px 10px;
            }
        }
        @media (max-width: 480px) {
            .auth-card {
                padding: 24px 20px;
                width: 95%;
                max-width: 95%;
            }
            .auth-card h2 {
                font-size: 28px;
                font-weight: 900;
            }
            .auth-card p {
                font-weight: 500;
                font-size: 14px;
            }
            .auth-topbar {
                padding: 16px;
                flex-wrap: wrap;
                gap: 12px;
            }
            .auth-logo {
                gap: 8px;
            }
            .modern-input label {
                font-weight: 700;
                font-size: 15px;
            }
            .modern-input input {
                font-size: 16px;
                font-weight: 500;
                padding: 16px 16px 16px 48px;
            }
            .btn-modern {
                font-weight: 700;
                font-size: 16px;
                padding: 16px 24px;
            }
            .auth-footer p,
            .auth-footer a {
                font-weight: 600;
            }
            .terms-text {
                font-weight: 600;
            }
            .auth-container {
                padding: 20px 5px;
            }
        }
        @media (max-width: 360px) {
            .auth-card {
                padding: 20px 16px;
                border-radius: 16px;
            }
            .auth-card h2 {
                font-size: 24px;
            }
            .auth-topbar {
                padding: 12px;
            }
            .modern-input input {
                padding: 14px 14px 14px 44px;
            }
            .btn-modern {
                padding: 14px 20px;
            }
        }
        
        /* Loading overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .loading-content {
            text-align: center;
            color: #fff;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .loading-subtext {
            font-size: 14px;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <header class="auth-topbar">
        <div class="auth-logo">
            <div class="auth-logo-icon">MPW</div>
            <h2 data-i18n="site-title">mywallet</h2>
        </div>
        <div style="display:flex;align-items:center;gap:16px">
            <select id="lang-select" style="padding:10px 16px;border-radius:24px;background:rgba(255,255,255,0.2);backdrop-filter:blur(10px);color:#fff;font-weight:600;font-size:14px;border:1px solid rgba(255,255,255,0.3);cursor:pointer;outline:none">
                <option value="en">🇬🇧 English</option>
                <option value="es">🇪🇸 Español</option>
                <option value="fr">🇫🇷 Français</option>
                <option value="de">🇩🇪 Deutsch</option>
                <option value="it">🇮🇹 Italiano</option>
                <option value="pt">🇵🇹 Português</option>
                <option value="ko">🇰🇷 한국어</option>
                <option value="ja">🇯🇵 日本語</option>
                <option value="zh-tw">🇹🇼 繁體中文</option>
                <option value="ar">🇸🇦 العربية</option>
                <option value="hi">🇮🇳 हिन्दी</option>
                <option value="ru">🇷🇺 Русский</option>
                <option value="nl">🇳🇱 Nederlands</option>
            </select>
            <a class="auth-home-link" href="index.php" data-i18n="nav-home">🏠 Home</a>
        </div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <h2 data-i18n="create-account-title">Create your account</h2>
            <p data-i18n="join-message">Join millions managing their money securely</p>

            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <form method="post" action="register.php" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="modern-input">
                    <label for="first_name" data-i18n="first-name">First Name</label>
                    <div class="input-wrapper">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.2"/><path d="M1.5 14c0-2.5 2.5-4 6.5-4s6.5 1.5 6.5 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                        <input type="text" id="first_name" name="first_name" placeholder="John" required data-i18n-placeholder="first-name">
                    </div>
                </div>

                <div class="modern-input">
                    <label for="surname" data-i18n="surname">Surname</label>
                    <div class="input-wrapper">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.2"/><path d="M1.5 14c0-2.5 2.5-4 6.5-4s6.5 1.5 6.5 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                        <input type="text" id="surname" name="surname" placeholder="Doe" required data-i18n-placeholder="surname">
                    </div>
                </div>

                <div class="modern-input">
                    <label for="phone" data-i18n="phone-number">Phone Number</label>
                    <div class="input-wrapper">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.5 11.3v1.9c0 .6-.5 1.1-1.1 1.1-6.1-.2-11.1-5.2-11.3-11.3 0-.6.5-1 1.1-1h1.9c.5 0 1 .4 1 1 0 .8.1 1.5.3 2.2.1.3 0 .7-.2.9l-1 1c1 1.9 2.6 3.5 4.5 4.5l1-1c.3-.3.6-.4.9-.2.7.2 1.4.3 2.2.3.6 0 1 .5.7.6z" stroke="currentColor" stroke-width="1.2" fill="none"/></svg>
                        <input type="tel" id="phone" name="phone" placeholder="+1234567890" required data-i18n-placeholder="phone-number">
                    </div>
                </div>

                <div class="modern-input">
                    <label for="email" data-i18n="email-address">Email address</label>
                    <div class="input-wrapper">
                        <svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 2.2C1 1.537 1.537 1 2.2 1h13.6c.663 0 1.2.537 1.2 1.2v9.6c0 .663-.537 1.2-1.2 1.2H2.2A1.2 1.2 0 011 11.8V2.2z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 3.4l6.1 4.2c.3.2.6.2.9 0L16 3.4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required data-i18n-placeholder="email-address">
                    </div>
                </div>

                <div class="modern-input">
                    <label for="password" data-i18n="password">Password</label>
                    <div class="input-wrapper">
                        <svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="7" width="14" height="9" rx="2" stroke="currentColor" stroke-width="1.2"/><path d="M4 7V5.2C4 2.92 5.92 1 8.2 1S12.4 2.92 12.4 5.2V7" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required data-i18n-placeholder="password-hint">
                    </div>
                </div>

                <div class="modern-input">
                    <label for="password2" data-i18n="confirm-password">Confirm password</label>
                    <div class="input-wrapper">
                        <svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="7" width="14" height="9" rx="2" stroke="currentColor" stroke-width="1.2"/><path d="M4 7V5.2C4 2.92 5.92 1 8.2 1S12.4 2.92 12.4 5.2V7" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input type="password" id="password2" name="password2" placeholder="Re-enter your password" required data-i18n-placeholder="confirm-password-hint">
                    </div>
                </div>

                <div class="modern-input">
                    <label for="referral_code" data-i18n="referral-code-optional">Referral Code (Optional)</label>
                    <div class="input-wrapper">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 1h5.5c.8 0 1.5.7 1.5 1.5V7m-4.5 6.5l4.5 4.5M1 8.5v-5C1 2.7 1.7 2 2.5 2H8m-3.5 9L1 14.5m5 1.5h5.5c.8 0 1.5-.7 1.5-1.5V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input type="text" id="referral_code" name="referral_code" placeholder="Enter referral code to get $10 bonus" style="text-transform: uppercase;" data-i18n-placeholder="referral-placeholder">
                    </div>
                    <small style="display: block; margin-top: 6px; color: #667eea; font-size: 12px; font-weight: 600;" data-i18n="referral-tip">💡 Use a referral code to receive $10 welcome bonus!</small>
                </div>

                <div class="auth-actions">
                    <button class="btn-modern primary" type="submit" data-i18n="create-account-btn">Create my account</button>
                </div>

                <div class="terms-text">
                    <span data-i18n="terms-agree-prefix">By creating an account, you agree to our</span>
                    <a href="terms.html" data-i18n="terms">Terms</a>
                    <span data-i18n="terms-agree-mid">and</span>
                    <a href="privacy.html" data-i18n="privacy">Privacy</a>.
                </div>
            </form>

            <div class="auth-footer">
                <p><span data-i18n="already-have-account">Already have an account?</span> <a href="login.php" data-i18n="sign-in-link">Sign in</a></p>
            </div>
        </div>
    </div>
    
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <div class="loading-text" data-i18n="creating-account">Creating your account...</div>
            <div class="loading-subtext" data-i18n="setting-up-wallet">Setting up your wallet</div>
        </div>
    </div>
    
<script>
    // Show loading on form submit
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function() {
            document.getElementById('loadingOverlay').classList.add('active');
        });
    }
</script>
<script src="i18n_enhanced.js"></script>
<script src="showhide.js"></script>
</body>
</html>
