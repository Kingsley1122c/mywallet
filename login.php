<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // set to your domain if needed
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/users_bootstrap.php';

// Path to users file
$usersFile = __DIR__ . '/users.json';

// CSRF token protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// If the users store is missing, empty, invalid, or reduced to the example accounts,
// rebuild it from the seeded users file without clobbering existing matching users.
$users = repairUsersStore($usersFile);

if (!empty($users) && !file_exists(__DIR__ . '/transactions.json')) {

    // Initialize transactions.json with opening balances when bootstrapping accounts.
    $txFile = __DIR__ . '/transactions.json';
    $txs = [];
    foreach ($users as $user) {
        $txs[] = ['id' => uniqid(), 'user_id' => $user['id'], 'time' => time() * 1000, 'desc' => 'Opening balance', 'amount' => $user['balance'] ?? 0];
    }
    file_put_contents($txFile, json_encode($txs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $identifier = trim($_POST['email'] ?? ''); // Can be email or phone
        $password = $_POST['password'] ?? '';

        if (!$identifier || !$password) {
            $error = 'Please enter your email/phone and password.';
        } else {
            $found = null;
            foreach ($users as $u) {
                // Check if identifier matches email or phone
                $matchesEmail = isset($u['email']) && strtolower($u['email']) === strtolower($identifier);
                $matchesPhone = isset($u['phone']) && $u['phone'] === $identifier;
                if ($matchesEmail || $matchesPhone) {
                    $found = $u;
                    break;
                }
            }

            if ($found && password_verify($password, $found['password'])) {
                // Check for deactivation
                if (!empty($found['deactivated'])) {
                    $_SESSION['deactivated_user_id'] = $found['id'];
                    unset($_SESSION['user_id']);
                    unset($_SESSION['email']);
                    header('Location: account_deactivated.php');
                    exit();
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $found['id'];
                    $_SESSION['email'] = $found['email'];
                    unset($_SESSION['csrf_token']); // Refresh token
                    header('Location: dashboard.php');
                    exit();
                }
            } else {
                $error = 'Invalid email/phone or password.';
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
    <title>Sign in | mywallet</title>
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
        
        .dark-mode .tip-box {
            background: linear-gradient(135deg, #451a03 0%, #78350f 100%);
            border-left-color: #f59e0b;
            color: #fde68a;
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
        .forgot-link {
            text-align: center;
            margin-top: 16px;
        }
        .forgot-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .forgot-link a:hover {
            text-decoration: underline;
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
        .tip-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 16px;
            font-size: 13px;
            color: #78350f;
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
            .tip-box {
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
        
        /* Modern Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102,126,234,0.95) 0%, rgba(118,75,162,0.95) 100%);
            backdrop-filter: blur(10px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .loading-content {
            text-align: center;
            color: #fff;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .spinner-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 32px;
        }
        
        .spinner {
            position: absolute;
            width: 120px;
            height: 120px;
            border: 6px solid rgba(255, 255, 255, 0.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }
        
        .spinner-inner {
            position: absolute;
            width: 90px;
            height: 90px;
            top: 15px;
            left: 15px;
            border: 6px solid rgba(255, 255, 255, 0.15);
            border-bottom-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            animation: spin 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite reverse;
        }
        
        .spinner-dot {
            position: absolute;
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
            50% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 0.8;
            }
        }
        
        .loading-text {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
            animation: fadeInOut 2s ease-in-out infinite;
        }
        
        .loading-subtext {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        @keyframes fadeInOut {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .progress-bar-container {
            width: 280px;
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            margin: 24px auto 0;
            overflow: hidden;
            position: relative;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #fff 0%, rgba(255,255,255,0.8) 100%);
            border-radius: 10px;
            animation: progress 5s linear;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        
        @keyframes progress {
            from { width: 0%; }
            to { width: 100%; }
        }
        
        .loading-dots {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .loading-dot {
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: bounce 1.4s ease-in-out infinite;
        }
        
        .loading-dot:nth-child(1) { animation-delay: 0s; }
        .loading-dot:nth-child(2) { animation-delay: 0.2s; }
        .loading-dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes bounce {
            0%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-15px);
            }
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
            <h2 data-i18n="welcome-back">Welcome back</h2>
            <p data-i18n="sign-in-message">Sign in to your account to continue</p>

            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>


            <?php if (!empty($showActivation)): ?>
                <!-- Deactivation handled by account_deactivated.php -->
            <?php else: ?>
                <form method="post" action="login.php" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="modern-input">
                        <label for="email" data-i18n="email-or-phone">Email or Phone Number</label>
                        <div class="input-wrapper">
                            <svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 2.2C1 1.537 1.537 1 2.2 1h13.6c.663 0 1.2.537 1.2 1.2v9.6c0 .663-.537 1.2-1.2 1.2H2.2A1.2 1.2 0 011 11.8V2.2z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 3.4l6.1 4.2c.3.2.6.2.9 0L16 3.4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <input type="text" id="email" name="email" placeholder="Email or phone number" required data-i18n-placeholder="email-or-phone">
                        </div>
                    </div>
                    <div class="modern-input">
                        <label for="password" data-i18n="password">Password</label>
                        <div class="input-wrapper">
                            <svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="7" width="14" height="9" rx="2" stroke="currentColor" stroke-width="1.2"/><path d="M4 7V5.2C4 2.92 5.92 1 8.2 1S12.4 2.92 12.4 5.2V7" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required data-i18n-placeholder="password">
                        </div>
                    </div>
                    <div class="auth-actions">
                        <button class="btn-modern primary" type="submit" data-i18n="sign-in-btn">Sign in to your account</button>
                    </div>
                    <div class="forgot-link">
                        <a href="forgot.php" data-i18n="forgot-password">Forgot your password?</a>
                    </div>
                </form>
            <?php endif; ?>
<?php
// Handle activation code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_account'])) {
    $activationUserId = (int)($_POST['activation_user_id'] ?? 0);
    $activationCode = trim($_POST['activation_code'] ?? '');
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
    $found = null;
    foreach ($users as $u) {
        if ($u['id'] == $activationUserId) {
            $found = $u;
            break;
        }
    }
    if ($found && !empty($found['deactivated']) && isset($found['activation_code']) && $activationCode === $found['activation_code']) {
        // Reactivate user
        foreach ($users as &$u) {
            if ($u['id'] == $activationUserId) {
                $u['deactivated'] = false;
                $u['deactivate_message'] = null;
                $u['activation_code'] = null;
            }
        }
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
        echo '<script>setTimeout(function(){alert("Your account activation is successfully done!");window.location.href="login.php";}, 500);</script>';
        exit();
    } else {
        $error = 'Invalid activation code.';
        $showActivation = true;
        $deactivateMsg = $found['deactivate_message'] ?? 'Your account is deactivated.';
        $activationUserId = $found['id'];
        $activationEmail = $found['email'];
    }
}
?>

            <div class="auth-footer">
                <p><span data-i18n="no-account">Don't have an account?</span> <a href="register.php" data-i18n="create-account">Create an account</a></p>
                <div class="tip-box">
                    💡 <strong data-i18n="demo-account">Demo Account</strong>: user@example.com / Password123
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner-container">
                <div class="spinner"></div>
                <div class="spinner-inner"></div>
                <div class="spinner-dot"></div>
            </div>
            <div class="loading-text" data-i18n="signing-in">Signing you in...</div>
            <div class="loading-subtext" data-i18n="verifying-credentials">Verifying your credentials</div>
            <div class="progress-bar-container">
                <div class="progress-bar"></div>
            </div>
            <div class="loading-dots">
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
            </div>
        </div>
    </div>
    
<script>
    // Show loading on form submit with 5-second delay
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const loadingOverlay = document.getElementById('loadingOverlay');

            loadingOverlay.classList.add('active');

            setTimeout(function() {
                form.submit();
            }, 5000);
        });
    }
</script>
<script src="i18n_enhanced.js"></script>
<script src="showhide.js"></script>
</body>
</html>
