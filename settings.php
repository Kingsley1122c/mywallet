<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Settings | Mivonta</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Apply theme immediately to prevent flash
        (function() {
            const theme = localStorage.getItem('mw_theme') || 'light';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = theme === 'dark' || (theme === 'auto' && prefersDark);
            if (isDark) document.documentElement.classList.add('dark-mode');
        })();
        // Unified currency selector logic
        document.addEventListener('DOMContentLoaded', function() {
            var currencySelect = document.getElementById('settings-currency-select');
            function fetchAndSetCurrency() {
                fetch('api/user_settings.php', { credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        if (data.currency) currencySelect.value = data.currency;
                    });
            }
            if (currencySelect) {
                fetchAndSetCurrency();
                currencySelect.addEventListener('change', function(e) {
                    fetch('api/user_settings.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        credentials: 'same-origin',
                        body: JSON.stringify({currency: e.target.value})
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.currency) currencySelect.value = data.currency;
                        window.dispatchEvent(new Event('currencyChanged'));
                    });
                });
            }
        });
    </script>
    <style>
        .dark-mode body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .settings-topbar {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 20px 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .topbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .topbar-logo {
            width: 44px;
            height: 44px;
            background: rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            color: #fff;
        }
        
        .topbar-brand h2 {
            color: #fff;
            margin: 0;
            font-size: 20px;
        }
        
        .topbar-back {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .topbar-back:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .settings-container {
            flex: 1;
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        
        .settings-card {
            background: #fff;
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            margin-bottom: 24px;
        }
        
        .settings-card h2 {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
        }
        
        .settings-card p {
            color: #6b7280;
            margin-bottom: 32px;
        }
        
        .settings-section {
            margin-bottom: 40px;
        }
        
        .settings-section:last-child {
            margin-bottom: 0;
        }
        
        .settings-section h3 {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .settings-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .setting-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .setting-item label {
            font-weight: 600;
            color: #555;
            font-size: 15px;
        }
        
        .setting-item select,
        .setting-item input {
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .setting-item select:focus,
        .setting-item input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .setting-item select:hover,
        .setting-item input:hover {
            border-color: #667eea;
        }
        
        .settings-actions {
            display: flex;
            gap: 16px;
            padding-top: 24px;
            border-top: 2px solid #f3f4f6;
        }
        
        .btn-modern {
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-modern.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .btn-modern.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(102, 126, 234, 0.5);
        }
        
        .btn-modern.secondary {
            background: #f3f4f6;
            color: #374151;
        }
        
        .btn-modern.secondary:hover {
            background: #e5e7eb;
        }
        
        .info-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            padding: 16px 20px;
            border-radius: 12px;
            margin-top: 24px;
        }
        
        .info-box p {
            margin: 0;
            color: #78350f;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .settings-card {
                padding: 32px 24px;
            }
            
            .settings-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .settings-topbar {
                padding: 16px 20px;
            }
            
            .topbar-brand h2 {
                font-size: 16px;
            }
            
            .topbar-logo {
                width: 36px;
                height: 36px;
                font-size: 12px;
            }
        }
        
        @media (max-width: 480px) {
            .settings-card {
                padding: 24px 20px;
                border-radius: 16px;
            }
            
            .settings-card h2 {
                font-size: 24px;
            }
            
            .settings-container {
                padding: 20px 10px;
            }
            
            .topbar-content {
                flex-wrap: wrap;
                gap: 12px;
            }
            
            .settings-actions {
                flex-direction: column;
            }
            
            .btn-modern {
                width: 100%;
            }
        }
        
        /* Dark mode styles */
        .dark-mode body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        
        .dark-mode .settings-topbar {
            background: rgba(30, 41, 59, 0.8);
        }
        
        .dark-mode .settings-card {
            background: #1e293b;
            color: #f1f5f9;
        }
        
        .dark-mode .settings-card h2 {
            color: #f1f5f9;
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .dark-mode .settings-card p {
            color: #cbd5e1;
        }
        
        .dark-mode .settings-section h3 {
            color: #f1f5f9;
        }
        
        .dark-mode .setting-item label {
            color: #cbd5e1;
        }
        
        .dark-mode .setting-item select,
        .dark-mode .setting-item input {
            background: #334155;
            color: #f1f5f9;
            border-color: #475569;
        }
        
        .dark-mode .setting-item select:hover,
        .dark-mode .setting-item input:hover {
            border-color: #8b5cf6;
        }
        
        .dark-mode .setting-item select:focus,
        .dark-mode .setting-item input:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }
        
        .dark-mode .settings-actions {
            border-top-color: #334155;
        }
        
        .dark-mode .btn-modern.secondary {
            background: #334155;
            color: #cbd5e1;
        }
        
        .dark-mode .btn-modern.secondary:hover {
            background: #475569;
        }
        
        .dark-mode .info-box {
            background: linear-gradient(135deg, #451a03 0%, #78350f 100%);
            border-left-color: #f59e0b;
        }
        
        .dark-mode .info-box p {
            color: #fde68a;
        }
    </style>
</head>
<body>

    <header class="settings-topbar">
        <div class="topbar-content">
            <div class="topbar-left">
                <div class="topbar-logo">MPW</div>
                <div class="topbar-brand">
                    <h2 data-i18n="site-title">Mivonta</h2>
                </div>
            </div>
            <a class="topbar-back" href="dashboard.php" data-i18n="back-to-dashboard">← Back to Dashboard</a>
        </div>
    </header>

    <main class="settings-container">
        <div class="settings-card">
            <h2 data-i18n="settings-title">⚙️ Account Settings</h2>
            <p data-i18n="settings-subtitle">Manage your account preferences and regional settings</p>
            
            <div class="settings-section">
                <h3 data-i18n="regional-settings">🌍 Regional Settings</h3>
                
                <div class="settings-row">
                    <div class="setting-item">
                        <p style="margin:0 0 16px 0;color:#4c566a;font-weight:500;" data-i18n="language-note-settings">Use the homepage language selector to change your language. Your choice applies across every page.</p>
                        <label for="settings-currency-select" data-i18n="currency-label" style="display:block;font-weight:600;margin:0 0 8px 0;color:#555">Country/Region & Currency</label>
                        <select id="settings-currency-select" style="width:100%;padding:12px;border:1px solid #667eea;border-radius:10px;font-size:16px;background:#f8fafc;color:#374151;box-shadow:0 2px 8px rgba(102,126,234,0.07);transition:border 0.2s;outline:none;appearance:none;">
                            <option value="USD">$ US Dollar</option>
                            <option value="EUR">€ Euro</option>
                            <option value="GBP">£ British Pound</option>
                            <option value="CAD">C$ Canadian Dollar</option>
                            <option value="AUD">A$ Australian Dollar</option>
                            <option value="JPY">¥ Japanese Yen</option>
                            <option value="CHF">Fr. Swiss Franc</option>
                            <option value="CNY">¥ Chinese Yuan</option>
                            <option value="INR">₹ Indian Rupee</option>
                            <option value="MXN">$ Mexican Peso</option>
                            <option value="BRL">R$ Brazilian Real</option>
                            <option value="ZAR">R South African Rand</option>
                            <option value="SGD">S$ Singapore Dollar</option>
                            <option value="HKD">HK$ Hong Kong Dollar</option>
                            <option value="KRW">₩ South Korean Won</option>
                            <option value="TWD">NT$ Taiwan Dollar</option>
                            <option value="THB">฿ Thai Baht</option>
                            <option value="MYR">RM Malaysian Ringgit</option>
                            <option value="IDR">Rp Indonesian Rupiah</option>
                            <option value="PHP">₱ Philippine Peso</option>
                        </select>
                    </div>
                    

                </div>
                
                <div class="info-box">
                    <p>💡 <strong data-i18n="tip-label">Tip:</strong> <span data-i18n="tip-regional">Your language and currency preferences will be saved automatically and applied across your account.</span></p>
                </div>
            </div>
            
            <div class="settings-section">
                <h3 data-i18n="appearance-label">🎨 Appearance</h3>
                
                <div class="settings-row">
                    <div class="setting-item">
                        <label for="theme-select" data-i18n="theme-label" data-i18n="theme-label">Theme</label>
                        <select id="theme-select">
                            <option value="light" data-i18n="theme-light">☀️ Light Mode</option>
                            <option value="dark" data-i18n="theme-dark">🌙 Dark Mode</option>
                            <option value="auto" data-i18n="theme-auto">🔄 Auto (System Preference)</option>
                        </select>
                    </div>
                </div>
                
                <div class="info-box">
                    <p>💡 <strong data-i18n="tip-label">Tip:</strong> <span data-i18n="tip-theme">Dark mode reduces eye strain in low-light environments and can help save battery on OLED screens.</span></p>
                </div>
            </div>
            
            <div class="settings-section">
                <h3 data-i18n="security-label">🔒 Security & Account</h3>
                
                <div class="settings-actions">
                    <a href="change_password.php" class="btn-modern secondary" data-i18n="change-password">Change Password</a>
                    <a href="dashboard.php" class="btn-modern primary" data-i18n="save-return">Save & Return</a>
                </div>
            </div>
        </div>
    </main>
    
    <script src="countries.js"></script>
    <script src="i18n_enhanced.js?v=20260427d"></script>
    <script>
        const themeSelect = document.getElementById('theme-select');
        
        // Theme handling
        function applyTheme(theme) {
            const isDark = theme === 'dark';
            
            if (isDark) {
                document.documentElement.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark-mode');
            }
        }
        
        if (themeSelect) {
            const savedTheme = localStorage.getItem('mw_theme') || 'light';
            themeSelect.value = savedTheme;
            
            if (savedTheme === 'dark') {
                applyTheme('dark');
            } else if (savedTheme === 'auto') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                applyTheme(prefersDark ? 'dark' : 'light');
            }
            
            themeSelect.addEventListener('change', (e) => {
                const theme = e.target.value;
                localStorage.setItem('mw_theme', theme);
                
                if (theme === 'auto') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    applyTheme(prefersDark ? 'dark' : 'light');
                } else {
                    applyTheme(theme);
                }
            });
            
            // Listen for system theme changes when auto is selected
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (themeSelect.value === 'auto') {
                    applyTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    </script>

</body>
</html>
