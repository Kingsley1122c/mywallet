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
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Determine role for current user to show admin link
$role = '';
$usersFile = __DIR__ . '/users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
    foreach ($users as $u) { if ($u['id'] == $_SESSION['user_id']) { $role = $u['role'] ?? ''; break; } }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Dashboard | mywallet</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // For testing: always clear adminMessageShown so popup will show if unread message exists
        sessionStorage.removeItem('adminMessageShown');
        // Apply theme immediately to prevent flash
        (function() {
            const theme = localStorage.getItem('mw_theme') || 'light';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = theme === 'dark' || (theme === 'auto' && prefersDark);
            if (isDark) document.documentElement.classList.add('dark-mode');
        })();
    </script>
    <style>
        :root {
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --card-bg: #ffffff;
            --input-bg: #ffffff;
        }
        
        .dark-mode {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --border-color: #334155;
            --card-bg: #1e293b;
            --input-bg: #334155;
        }
        
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .dark-mode .bank-sidebar {
            background: #1e293b;
            border-right-color: #334155;
        }
        
        .dark-mode .bank-brand {
            color: #f1f5f9;
        }
        
        .dark-mode .muted-small {
            color: #94a3b8;
        }
        
        .dark-mode .nav a {
            color: #cbd5e1;
        }
        
        .dark-mode .nav a:hover,
        .dark-mode .nav a.active {
            background: #334155;
            color: #fff;
        }
        
        .dark-mode .account-card,
        .dark-mode .transactions-table,
        .dark-mode .card {
            background: #1e293b;
            border-color: #334155;
            color: #f1f5f9;
        }
        
        .dark-mode .account-balance {
            color: #f1f5f9;
        }
        
        .dark-mode input,
        .dark-mode select,
        .dark-mode textarea {
            background: #334155;
            color: #f1f5f9;
            border-color: #475569;
        }
        
        .dark-mode table {
            color: #f1f5f9;
        }
        
        .dark-mode table thead {
            background: #334155;
        }
        
        .dark-mode table tbody tr:hover {
            background: #334155;
        }
        
        .dark-mode h3 {
            color: #f1f5f9;
        }
        
        .dark-mode .btn-ghost {
            background: #334155;
            color: #cbd5e1;
        }
        
        .dark-mode .btn-ghost:hover {
            background: #475569;
        }
        
        .dark-mode .modal {
            background: rgba(0,0,0,0.8);
        }
        
        .dark-mode footer {
            background: #0f172a;
            border-top-color: #334155;
            color: #94a3b8;
        }
        
        /* Override sidebar layout - make single column */
        .bank-wrap {
            display: block !important;
            width: 100%;
            max-width: 1200px !important;
            min-width: 0;
            margin: 0 auto !important;
            padding: 32px 32px !important;
            box-sizing: border-box;
            grid-template-columns: none !important;
        }
        
        .bank-sidebar {
            display: none !important;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            min-width: 0;
            margin: 0 auto;
            padding: 0 32px 32px 32px;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        
        /* Modern Balance Card Styling */
        .account-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.35);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .account-card::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 250px;
            height: 250px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        
        .account-card::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            animation: float 10s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(3deg); }
        }
        
        .account-balance {
            font-size: 56px;
            font-weight: 900;
            margin: 12px 0;
            color: #fff;
            letter-spacing: -1px;
            position: relative;
            z-index: 1;
            transition: filter 0.3s ease;
        }
        
        .account-balance.hidden {
            filter: blur(12px);
            user-select: none;
        }
        
        .balance-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .balance-toggle {
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 20px;
            color: #fff;
            padding: 0;
        }
        
        .balance-toggle:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .balance-toggle:active {
            transform: scale(0.95);
        }
        
        .muted-small {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
        }
        
        .quick-actions {
            position: relative;
            z-index: 1;
        }
        
        .quick-actions .btn,
        .quick-actions .btn-primary,
        .quick-actions .btn-ghost {
            background: rgba(255,255,255,0.95);
            color: #667eea;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 14px 28px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .quick-actions .btn:hover,
        .quick-actions .btn-primary:hover,
        .quick-actions .btn-ghost:hover {
            background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            color: #764ba2;
        }
        
        .quick-actions .btn-primary {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #fff;
            border-color: rgba(251,191,36,0.3);
        }
        
        .quick-actions .btn-primary:hover {
            background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 100%);
            color: #fff;
        }
        
        /* Modern Financial Services Cards */
        .financial-services {
            margin-top: 24px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        
        .service-card {
            text-decoration: none;
            display: block;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 28px;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            color: #1f2937;
            position: relative;
            overflow: hidden;
        }
        
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 0;
        }
        
        .service-card:hover::before {
            opacity: 1;
        }
        
        .service-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 50px rgba(102,126,234,0.25), 0 0 0 1px rgba(102,126,234,0.1);
            background: rgba(255,255,255,1);
        }
        
        .service-card:active {
            transform: translateY(-4px) scale(1);
        }
        
        .service-icon {
            font-size: 48px;
            margin-bottom: 12px;
            display: inline-block;
            transition: all 0.4s ease;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
            position: relative;
            z-index: 1;
        }
        
        .service-card:hover .service-icon {
            transform: scale(1.2) rotate(5deg);
            filter: drop-shadow(0 8px 16px rgba(102,126,234,0.3));
        }
        
        .service-title {
            font-weight: 800;
            font-size: 18px;
            margin-bottom: 6px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }
        
        .service-desc {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
            position: relative;
            z-index: 1;
            line-height: 1.4;
        }
        
        .service-card.investments {
            border-top: 3px solid #3b82f6;
        }
        
        .service-card.investments:hover {
            box-shadow: 0 20px 50px rgba(59,130,246,0.25), 0 0 0 1px rgba(59,130,246,0.2);
        }
        
        .service-card.investments .service-title {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .service-card.loans {
            border-top: 3px solid #f093fb;
        }
        
        .service-card.loans:hover {
            box-shadow: 0 20px 50px rgba(240,147,251,0.25), 0 0 0 1px rgba(240,147,251,0.2);
        }
        
        .service-card.loans .service-title {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Pulse Animation */
        @keyframes pulse-ring {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }
            100% {
                transform: scale(1.3);
                opacity: 0;
            }
        }
        
        .service-card::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            border-radius: 20px;
            border: 2px solid currentColor;
            transform: translate(-50%, -50%) scale(1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .service-card:hover::after {
            animation: pulse-ring 1.5s ease-out infinite;
        }
        
        /* Modern Send Money Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal .card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
            position: relative;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal h3 {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }
        
        .close-btn {
            background: #f3f4f6;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 24px;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .close-btn:hover {
            background: #e5e7eb;
            color: #1f2937;
            transform: rotate(90deg);
        }
        
        .send-form {
            margin-top: 28px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            font-size: 16px;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .currency-input-group {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 12px;
        }
        
        .currency-select {
            background: white;
            cursor: pointer;
            font-weight: 600;
            color: #667eea;
        }
        
        .currency-select:hover {
            background: #f9fafb;
        }
        
        .amount-input {
            font-size: 18px;
            font-weight: 600;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }
        
        .form-actions .btn {
            flex: 1;
            padding: 16px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        .form-actions .btn:first-child {
            background: #f3f4f6;
            color: #6b7280;
        }
        
        .form-actions .btn:first-child:hover {
            background: #e5e7eb;
            color: #374151;
        }
        
        .form-actions .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .form-actions .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .form-actions .btn-primary:active {
            transform: translateY(0);
        }
        
        #send-loading {
            display: none;
            text-align: center;
            padding: 24px;
            background: #f9fafb;
            border-radius: 14px;
            margin: 20px 0;
        }
        
        #send-loading .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 12px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        #send-loading p {
            color: #6b7280;
            font-weight: 600;
            margin: 0;
        }
        
        /* Mobile Responsive for Modal */
        @media (max-width: 600px) {
            .modal .card {
                padding: 20px 8px 28px 8px;
                max-width: 100vw;
                min-width: 0;
                border-radius: 18px;
                box-shadow: 0 6px 32px rgba(2,132,199,0.13);
            }
            .modal h3 {
                font-size: 22px;
            }
            .form-group label, .form-group input, .form-group select {
                font-size: 17px !important;
            }
            .form-group input, .form-group select {
                padding: 14px 10px;
                border-radius: 10px;
            }
            .form-actions {
                flex-direction: column;
                gap: 12px;
            }
            .btn, button.btn, .btn-primary {
                min-height: 48px;
                font-size: 18px;
                border-radius: 10px;
                width: 100%;
            }
            #withdraw-modal {
                align-items: flex-end !important;
                justify-content: center;
                min-height: 100vh;
                width: 100vw;
                margin: 0;
            }
            #withdraw-content {
                padding-bottom: 8px;
            }
            #withdraw-success-message {
                font-size: 18px;
            }
            #withdrawal-code-error {
                font-size: 16px;
            }
            #withdraw-modal .close-btn {
                font-size: 28px;
                top: 8px;
                right: 8px;
            }
        }
        
        /* Modern Recent Activity Section */
        .transactions-table {
            background: #fff;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            margin-top: 32px;
        }
        
        .transactions-table h3 {
            font-size: 24px;
            font-weight: 800;
            color: #1f2937;
            margin: 0 0 24px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .transactions-table h3::before {
            content: '📊';
            font-size: 28px;
        }
        
        #tx-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .transaction-item:hover {
            transform: translateX(4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            border-color: #cbd5e1;
        }
        
        .transaction-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
        }
        
        .transaction-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        
        .transaction-icon.incoming {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }
        
        .transaction-icon.outgoing {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        }
        
        .transaction-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .transaction-description {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }
        
        .transaction-date {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .transaction-amount {
            font-size: 20px;
            font-weight: 800;
            white-space: nowrap;
        }
        
        .transaction-amount.positive {
            color: #059669;
        }
        
        .transaction-amount.negative {
            color: #dc2626;
        }
        
        .no-transactions {
            text-align: center;
            padding: 48px 24px;
            color: #9ca3af;
            font-size: 15px;
            font-style: italic;
        }
        
        /* Custom Logout Confirmation Modal */
        .logout-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }
        
        .logout-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logout-modal-content {
            background: #fff;
            border-radius: 24px;
            padding: 40px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 25px 70px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
            position: relative;
        }
        
        .dark-mode .logout-modal-content {
            background: #1e293b;
            color: #f1f5f9;
        }
        
        .logout-modal-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }
        
        .logout-modal-title {
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            margin: 0 0 12px 0;
            color: #1f2937;
        }
        
        .dark-mode .logout-modal-title {
            color: #f1f5f9;
        }
        
        .logout-modal-text {
            font-size: 16px;
            text-align: center;
            color: #6b7280;
            margin: 0 0 32px 0;
            line-height: 1.6;
        }
        
        .dark-mode .logout-modal-text {
            color: #94a3b8;
        }
        
        .logout-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        
        .logout-modal-btn {
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            flex: 1;
            max-width: 160px;
        }
        
        .logout-modal-btn.cancel {
            background: #f3f4f6;
            color: #374151;
            border: 2px solid #e5e7eb;
        }
        
        .logout-modal-btn.cancel:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        }
        
        .dark-mode .logout-modal-btn.cancel {
            background: #334155;
            color: #f1f5f9;
            border-color: #475569;
        }
        
        .dark-mode .logout-modal-btn.cancel:hover {
            background: #475569;
        }
        
        .logout-modal-btn.confirm {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: #fff;
            border: 2px solid transparent;
            box-shadow: 0 6px 18px rgba(220,38,38,0.3);
        }
        
        .logout-modal-btn.confirm:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(220,38,38,0.4);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 480px) {
            .logout-modal-content {
                padding: 32px 24px;
                border-radius: 20px;
            }
            
            .logout-modal-icon {
                width: 56px;
                height: 56px;
                font-size: 28px;
            }
            
            .logout-modal-title {
                font-size: 20px;
            }
            
            .logout-modal-text {
                font-size: 15px;
            }
            
            .logout-modal-actions {
                flex-direction: column;
            }
            
            .logout-modal-btn {
                max-width: 100%;
            }
        }
        
        .modern-topbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 18px 32px;
            box-shadow: 0 4px 24px rgba(102, 126, 234, 0.35);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        .topbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            min-width: 0;
        }
        .topbar-logo {
            width: 44px;
            height: 44px;
            background: rgba(255,255,255,0.3);
            backdrop-filter: blur(15px);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 15px;
            color: #fff;
            letter-spacing: -0.5px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
            flex-shrink: 0;
        }
        .topbar-brand {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .topbar-brand h2 {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            margin: 0;
            letter-spacing: -0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .topbar-brand small {
            font-size: 12px;
            color: rgba(255,255,255,0.88);
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.22);
            backdrop-filter: blur(15px);
            padding: 8px 16px;
            border-radius: 50px;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.25);
        }
        .user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 15px;
            color: #fff;
            box-shadow: 0 4px 12px rgba(240,147,251,0.4);
        }
        .user-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .user-details small {
            font-size: 10px;
            opacity: 0.85;
            font-weight: 500;
        }
        .user-details strong {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .topbar-btn {
            padding: 11px 22px;
            border-radius: 50px;
            background: rgba(255,255,255,0.25);
            backdrop-filter: blur(15px);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255,255,255,0.25);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .topbar-btn:hover {
            background: rgba(255,255,255,0.4);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
            border-color: rgba(255,255,255,0.35);
        }
        .topbar-btn.admin {
            background: linear-gradient(135deg, rgba(251,191,36,0.4) 0%, rgba(245,158,11,0.35) 100%);
            border-color: rgba(251,191,36,0.5);
            box-shadow: 0 4px 12px rgba(251,191,36,0.3);
        }
        .topbar-btn.admin:hover {
            background: linear-gradient(135deg, rgba(251,191,36,0.5) 0%, rgba(245,158,11,0.45) 100%);
            box-shadow: 0 6px 18px rgba(251,191,36,0.4);
        }
        .topbar-btn.settings {
            background: rgba(255,255,255,0.95);
            color: #667eea;
            border-color: rgba(255,255,255,0.4);
            box-shadow: 0 4px 12px rgba(102,126,234,0.2);
        }
        .topbar-btn.settings:hover {
            background: #fff;
            color: #764ba2;
            box-shadow: 0 6px 18px rgba(102,126,234,0.3);
        }
        .topbar-btn.logout {
            background: linear-gradient(135deg, rgba(220,38,38,0.3) 0%, rgba(239,68,68,0.25) 100%);
            border-color: rgba(220,38,38,0.4);
            box-shadow: 0 4px 12px rgba(220,38,38,0.25);
        }
        .topbar-btn.logout:hover {
            background: linear-gradient(135deg, rgba(220,38,38,0.4) 0%, rgba(239,68,68,0.35) 100%);
            box-shadow: 0 6px 18px rgba(220,38,38,0.35);
            transform: translateY(-2px);
        }
        @media (max-width: 1024px) {
            .modern-topbar {
                padding: 14px 24px;
            }
            .topbar-logo {
                width: 40px;
                height: 40px;
                font-size: 14px;
            }
            .topbar-brand h2 {
                font-size: 18px;
            }
            .topbar-brand small {
                font-size: 11px;
            }
            .topbar-btn {
                padding: 10px 18px;
                font-size: 13px;
            }
            .user-avatar {
                width: 34px;
                height: 34px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 1024px) {
            .bank-wrap {
                padding: 24px 4vw !important;
            }
        }
        
        @media (max-width: 768px) {
            .bank-wrap {
                padding: 20px 2vw !important;
            }
                        .container {
                            padding: 0 0 24px 0 !important;
                            width: 100vw !important;
                            max-width: 100vw !important;
                            min-width: 0 !important;
                            box-sizing: border-box;
                        }
                        .bank-wrap {
                            padding: 12px 0 !important;
                            width: 100vw !important;
                            max-width: 100vw !important;
                            min-width: 0 !important;
                            box-sizing: border-box;
                        }
                        .main-content, .dashboard-main, .dashboard-content, .dashboard-section, .transactions-table, .account-card, .service-card {
                            width: 100vw !important;
                            max-width: 100vw !important;
                            min-width: 0 !important;
                            box-sizing: border-box;
                        }
                        .dashboard-row, .dashboard-flex, .dashboard-grid, .transactions-table, .quick-actions, .financial-services {
                            flex-direction: column !important;
                            display: flex !important;
                            gap: 12px !important;
                        }
                        .dashboard-sidebar, .bank-sidebar {
                            display: none !important;
                        }
            
            .modern-topbar {
                padding: 12px 16px;
            }
            .topbar-content {
                gap: 12px;
            }
            .topbar-left {
                gap: 12px;
                min-width: 0;
                flex: 1;
            }
            .topbar-logo {
                width: 38px;
                height: 38px;
                font-size: 13px;
                border-radius: 12px;
            }
            .topbar-brand h2 {
                font-size: 16px;
            }
            .topbar-brand small {
                display: none;
            }
            .user-info {
                padding: 6px 12px;
                gap: 8px;
            }
            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 13px;
            }
            .user-details {
                display: none;
            }
            .topbar-actions {
                gap: 6px;
            }
            .topbar-btn {
                padding: 9px 14px;
                font-size: 13px;
            }
        }
        
        @media (max-width: 600px) {
            .modern-topbar {
                padding: 10px 12px;
            }
            .topbar-content {
                gap: 8px;
            }
            .topbar-left {
                gap: 10px;
            }
            .topbar-logo {
                width: 36px;
                height: 36px;
                font-size: 12px;
                border-radius: 10px;
            }
            .topbar-brand h2 {
                font-size: 15px;
            }
            .user-info {
                padding: 5px 10px;
            }
            .user-avatar {
                width: 30px;
                height: 30px;
                font-size: 12px;
            }
            .topbar-actions {
                flex-direction: column;
                gap: 4px;
            }
            .topbar-btn {
                padding: 8px 12px;
                font-size: 12px;
                border-radius: 20px;
                gap: 4px;
            }
            .topbar-btn span:last-child {
                display: none;
            }
            .topbar-btn span:first-child {
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .bank-wrap {
                padding: 16px 12px !important;
            }
            
            .topbar-brand h2 {
                font-size: 14px;
            }
            .topbar-right {
                gap: 8px;
            }
            .topbar-actions {
                gap: 4px;
            }
            .topbar-btn {
                padding: 7px 10px;
                font-size: 11px;
            }
            
            /* Mobile responsive for balance card */
            .account-card {
                padding: 28px 20px;
                border-radius: 20px;
            }
            
            .account-balance {
                font-size: 42px;
            }
            
            .muted-small {
                font-size: 12px;
            }
            
            .quick-actions {
                flex-direction: column;
            }
            
            .quick-actions .btn,
            .quick-actions .btn-primary,
            .quick-actions .btn-ghost {
                width: 100%;
                padding: 12px 24px;
                font-size: 14px;
            }
            
            /* Mobile responsive for financial services cards */
            .financial-services {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .service-card {
                padding: 24px;
            }
            
            .service-icon {
                font-size: 42px;
            }
            
            .service-title {
                font-size: 16px;
            }
            
            .service-desc {
                font-size: 12px;
            }
            
            /* Mobile responsive for transactions */
            .transactions-table {
                padding: 24px 16px;
                border-radius: 16px;
                margin-top: 24px;
            }
            
            .transactions-table h3 {
                font-size: 20px;
            }
            
            .transaction-item {
                padding: 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .transaction-left {
                width: 100%;
            }
            
            .transaction-icon {
                width: 42px;
                height: 42px;
                font-size: 20px;
            }
            
            .transaction-description {
                font-size: 15px;
            }
            
            .transaction-date {
                font-size: 12px;
            }
            
            .transaction-amount {
                font-size: 18px;
                align-self: flex-end;
            }
            
            .account-card::before,
            .account-card::after {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Diagnostic warning for missing API endpoints -->
    <div id="api-warning" style="display:none;background:#fee2e2;color:#dc2626;padding:18px 24px;border-radius:12px;margin:24px auto;max-width:600px;font-weight:700;font-size:16px;text-align:center;box-shadow:0 4px 18px rgba(220,38,38,0.08);">
        ⚠️ API endpoints not found. Please ensure the <b>/api/</b> folder is uploaded to your hosting and accessible at <b>https://clickmw.online/api/</b>.<br>
        If you see 404 errors in the browser console, your backend is not reachable.
    </div>
    <script>
    // Show warning if any API fetch fails with 404
    function showApiWarning() {
        var w = document.getElementById('api-warning');
        if (w) w.style.display = 'block';
    }
    // Patch fetch to detect 404s
    (function() {
        var origFetch = window.fetch;
        window.fetch = function(url, opts) {
            return origFetch(url, opts).then(function(r) {
                if (r.status === 404 && url.indexOf('/api/') !== -1) showApiWarning();
                return r;
            });
        };
    })();
    </script>
    <header class="modern-topbar">
        <div class="topbar-content">
            <div class="topbar-left">
                <div class="topbar-logo">MPW</div>
                <div class="topbar-brand">
                    <h2 data-i18n="mywallet">mywallet</h2>
                    <small>💳 Secure Financial Platform</small>
                </div>
            </div>
            <div class="topbar-right">
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['email'], 0, 1)); ?></div>
                    <div class="user-details">
                        <small data-i18n="signed-in-as">Signed in as</small>
                        <strong><?php echo htmlspecialchars(explode('@', $_SESSION['email'])[0]); ?></strong>
                    </div>
                </div>
                <div class="topbar-actions">
                    <?php if ($role === 'admin'): ?>
                        <a class="topbar-btn admin" href="admin.php">
                            <span>👑</span>
                            <span data-i18n="admin-btn">Admin</span>
                        </a>
                    <?php endif; ?>
                    <a class="topbar-btn settings" href="settings.php">
                        <span>⚙️</span>
                        <span data-i18n="nav-settings">Settings</span>
                    </a>
                    <a class="topbar-btn logout" href="#" data-logout-trigger>
                        <span>🚪</span>
                        <span data-i18n="logout">Log Out</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <?php $userId = (int)($_SESSION['user_id'] ?? 0); ?>
        <?php
        // Load user and transaction data
        $users = json_decode(file_get_contents('users.json'), true);
        $transactions = json_decode(file_get_contents('transactions.json'), true);
        $currentUser = null;
        foreach ($users as $u) {
            if ($u['id'] == $userId) {
                $currentUser = $u;
                break;
            }
        }
        // Calculate real balance from transactions (fallback if needed)
        $realBalance = 0;
        foreach ($transactions as $tx) {
            if ($tx['user_id'] == $userId && (!isset($tx['status']) || $tx['status'] === 'completed')) {
                $realBalance += $tx['amount'];
            }
        }
        // Prefer balance from users.json if available
        if ($currentUser && isset($currentUser['balance'])) {
            $realBalance = $currentUser['balance'];
        }
        // Get recent transactions (last 10, completed or pending)
        $userTxs = array_filter($transactions, function($tx) use ($userId) {
            return $tx['user_id'] == $userId;
        });
        usort($userTxs, function($a, $b) { return $b['time'] <=> $a['time']; });
        $recentTxs = array_slice($userTxs, 0, 10);
        ?>
        <div class="bank-wrap">
            <aside class="bank-sidebar" style="display:none">
                <div class="bank-brand">mywallet</div>
                <div class="muted-small" data-i18n="signed-in-as">Signed in as <?php echo htmlspecialchars($_SESSION['email']); ?></div>
                <nav class="nav">
                    <a href="#" id="nav-overview" class="active" data-i18n="nav-overview">Overview</a>
                    <a href="#" id="nav-accounts" data-i18n="nav-accounts">Accounts</a>
                    <a href="#" id="nav-payments" data-i18n="nav-payments">Payments</a>
                    <a href="settings.php" id="nav-settings" data-i18n="nav-settings">Settings</a>
                </nav>
                <div style="margin-top:auto;display:flex;gap:8px">
                    <a class="btn-ghost" id="change-password" href="change_password.php" data-i18n="change-password">Change password</a>
                    <a class="btn" id="sign-out" href="logout.php" data-i18n="sign-out">Sign out</a>
                </div>
            </aside>

            <section>
                <div class="account-card responsive-card">
                    <div style="display:flex;flex-direction:column;gap:20px">
                        <div style="text-align:center">
                            <div class="balance-header">
                                <div class="muted-small" id="balance-label" data-i18n="balance-label">Available Balance</div>
                                <button class="balance-toggle" id="toggle-balance" title="Hide/Show Balance" data-i18n-title="balance-toggle-title">
                                    <span id="eye-icon">👁️</span>
                                </button>
                            </div>
                            <div id="account-balance" class="account-balance">
                                <span id="account-balance-value">
                                    <?php
                                        // Show balance with currency formatting as fallback
                                        echo '<span id="account-balance-symbol">$</span>' . number_format($realBalance, 2) . ' <span id="account-balance-code">USD</span>';
                                    ?>
                                </span>
                                <div id="account-balance-currency-wrap" style="margin-top:8px;">
                                    <span id="account-balance-rate" style="font-size:13px;color:#64748b;"></span>
                                </div>
                            </div>
                        </div>
                        <div class="quick-actions responsive-actions" style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center">
                        <button id="send-btn" class="btn btn-primary" style="min-width:120px;" data-i18n="send-btn">Send Money</button>
                        <button id="add-money-btn" class="btn btn-primary" style="min-width:120px;" data-i18n="add-money-btn">Add Money</button>
                        <button id="withdraw-btn" class="btn btn-primary" style="min-width:120px;" data-i18n="withdraw-btn">Withdraw Money</button>
                        <a href="bank_accounts.php" class="btn btn-ghost" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center" data-i18n="nav-bank-accounts">Bank Accounts</a>
                                                </div>
                        
                        <!-- New Financial Services -->
                        <div class="financial-services">
                            <a href="investments.php" class="service-card investments responsive-service">
                                <div class="service-icon">📈</div>
                                <div class="service-title" data-i18n="investments-title">Investments</div>
                                <div class="service-desc" data-i18n="investments-desc">Stocks, Crypto & More</div>
                            </a>
                            <a href="loans.php" class="service-card loans responsive-service">
                                <div class="service-icon">💳</div>
                                <div class="service-title" data-i18n="loans-title">Loans & Credit</div>
                                <div class="service-desc" data-i18n="loans-desc">Personal & Business</div>
                            </a>
                        </div>
                    </div>
                </div>

                <div style="height:18px"></div>

                <!-- Settings section (hidden by default) -->
                <div id="settings-section" style="display:none">
                    <div class="account-card">
                        <h3 style="margin-bottom:24px;color:#333" data-i18n="account-settings-title">⚙️ Account Settings</h3>
                        <div style="display:grid;gap:24px">
                            <div>
                                <label for="settings-lang-select" style="display:block;font-weight:600;margin-bottom:8px;color:#555" data-i18n="language-label">Language</label>
                                <select id="settings-lang-select" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:15px">
                                    <option value="en" data-i18n="lang-en">🇬🇧 English</option>
                                    <option value="es" data-i18n="lang-es">🇪🇸 Español</option>
                                    <option value="fr" data-i18n="lang-fr">🇫🇷 Français</option>
                                    <option value="de" data-i18n="lang-de">🇩🇪 Deutsch</option>
                                    <option value="it" data-i18n="lang-it">🇮🇹 Italiano</option>
                                    <option value="pt" data-i18n="lang-pt">🇵🇹 Português</option>
                                    <option value="ko" data-i18n="lang-ko">🇰🇷 한국어</option>
                                    <option value="ja" data-i18n="lang-ja">🇯🇵 日本語</option>
                                    <option value="zh-tw" data-i18n="lang-zh-tw">🇹🇼 繁體中文</option>
                                    <option value="ar" data-i18n="lang-ar">🇸🇦 العربية</option>
                                    <option value="hi" data-i18n="lang-hi">🇮🇳 हिन्दी</option>
                                    <option value="ru" data-i18n="lang-ru">🇷🇺 Русский</option>
                                    <option value="nl" data-i18n="lang-nl">🇳🇱 Nederlands</option>
                                </select>
                                <label for="settings-currency-select" style="display:block;font-weight:600;margin:16px 0 8px 0;color:#555" data-i18n="currency-label">Currency</label>
                                <select id="settings-currency-select" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:15px">
                                    <option value="USD" data-i18n="currency-usd">$ US Dollar</option>
                                    <option value="EUR" data-i18n="currency-eur">€ Euro</option>
                                    <option value="GBP" data-i18n="currency-gbp">£ British Pound</option>
                                    <option value="CAD" data-i18n="currency-cad">C$ Canadian Dollar</option>
                                    <option value="AUD" data-i18n="currency-aud">A$ Australian Dollar</option>
                                    <option value="JPY" data-i18n="currency-jpy">¥ Japanese Yen</option>
                                    <option value="CHF" data-i18n="currency-chf">Fr. Swiss Franc</option>
                                    <option value="CNY" data-i18n="currency-cny">¥ Chinese Yuan</option>
                                    <option value="INR" data-i18n="currency-inr">₹ Indian Rupee</option>
                                    <option value="MXN" data-i18n="currency-mxn">$ Mexican Peso</option>
                                    <option value="BRL" data-i18n="currency-brl">R$ Brazilian Real</option>
                                    <option value="ZAR" data-i18n="currency-zar">R South African Rand</option>
                                    <option value="SGD" data-i18n="currency-sgd">S$ Singapore Dollar</option>
                                    <option value="HKD" data-i18n="currency-hkd">HK$ Hong Kong Dollar</option>
                                    <option value="KRW" data-i18n="currency-krw">₩ South Korean Won</option>
                                    <option value="TWD" data-i18n="currency-twd">NT$ Taiwan Dollar</option>
                                    <option value="THB" data-i18n="currency-thb">฿ Thai Baht</option>
                                    <option value="MYR" data-i18n="currency-myr">RM Malaysian Ringgit</option>
                                    <option value="IDR" data-i18n="currency-idr">Rp Indonesian Rupiah</option>
                                    <option value="PHP" data-i18n="currency-php">₱ Philippine Peso</option>
                                </select>
                            </div>
                            <div style="padding-top:16px;border-top:1px solid #e5e7eb">
                                <h4 style="margin-bottom:16px;color:#333" data-i18n="security">Security</h4>
                                <a class="btn btn-ghost" href="change_password.php" style="text-decoration:none;display:inline-block" data-i18n="change-password">Change Password</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="transactions-table" id="transactions-section">
                    <h3 id="recent-title" data-i18n="recent-title">Recent activity</h3>
                    <div id="tx-list">
                        <?php if (count($recentTxs) === 0): ?>
                            <div class="no-transactions" data-i18n="no-activity">No recent transactions found.</div>
                        <?php else: ?>
                            <?php foreach ($recentTxs as $tx): ?>
                                <?php
                                    $isPositive = $tx['amount'] > 0;
                                    $amountClass = $isPositive ? 'positive' : 'negative';
                                    $iconClass = $isPositive ? 'incoming' : 'outgoing';
                                    $icon = $isPositive ? '⬇️' : '⬆️';
                                    $desc = htmlspecialchars($tx['desc']);
                                    $date = date('M d, Y H:i', $tx['time']/1000);
                                    $status = isset($tx['status']) ? $tx['status'] : 'completed';
                                ?>
                                <div class="transaction-item" style="display:flex;align-items:center;justify-content:space-between;padding:18px 0;border-bottom:1px solid #f3f6fa;gap:12px;">
                                    <div class="transaction-left" style="display:flex;align-items:center;gap:16px;">
                                        <div class="transaction-icon <?php echo $iconClass; ?>" style="font-size:28px;width:44px;height:44px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:<?php echo $isPositive ? '#e0fbe0' : '#fee2e2'; ?>;color:<?php echo $isPositive ? '#22c55e' : '#ef4444'; ?>;box-shadow:0 2px 8px rgba(34,197,94,0.07);">
                                            <?php echo $icon; ?>
                                        </div>
                                        <div class="transaction-details" style="min-width:0;">
                                            <div class="transaction-description" style="font-weight:700;font-size:15px;color:#334155;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;"><?php echo $desc; ?></div>
                                            <div class="transaction-date" style="font-size:13px;color:#64748b;">
                                                <?php echo $date; ?><?php if ($status !== 'completed') echo ' <span style=\"color:#f59e0b;font-weight:600\">('.ucfirst($status).')</span>'; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="transaction-amount <?php echo $amountClass; ?>" data-amount="<?php echo $tx['amount']; ?>" style="font-size:18px;font-weight:900;min-width:90px;text-align:right;color:<?php echo $isPositive ? '#22c55e' : '#ef4444'; ?>;">
                                        <?php echo ($isPositive ? '+' : '-') . '$' . number_format(abs($tx['amount']), 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>

        <!-- Send money modal -->
        <div id="send-modal" class="modal" style="display:none">
            <div class="card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <h3 id="send-title" data-i18n="send-title">💸 Send Money</h3>
                    <button id="close-send" class="close-btn">✕</button>
                </div>
                <form id="send-form" class="send-form">
                    <div class="form-group">
                        <label for="send-to-placeholder" data-i18n="send-to-label">📧 Recipient</label>
                        <input name="to" id="send-to-placeholder" placeholder="Enter email address or account number" data-i18n-placeholder="send-to-placeholder" required>
                    </div>
                    <div class="form-group">
                        <label for="send-amount-placeholder" data-i18n="send-amount-label">💰 Amount (USD)</label>
                        <input name="amount" id="send-amount-placeholder" class="amount-input" placeholder="0.00" type="number" step="0.01" data-i18n-placeholder="send-amount-placeholder" required>
                    </div>
                    <div id="send-loading">
                        <div class="spinner"></div>
                        <p>Processing your transfer...</p>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn" id="cancel-btn" data-i18n="cancel-btn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="send-submit" data-i18n="send-submit">Send Money</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Money modal is hidden for users; only the blocked modal is shown via JS. -->

        <!-- Withdrawal modal -->
        <div id="withdraw-modal" class="modal" style="display:none" role="dialog" aria-modal="true" aria-labelledby="withdraw-title" tabindex="-1">
            <div class="card" style="max-width:600px;width:90%">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
                    <h3 style="margin:0" id="withdraw-title" data-i18n="withdraw-title">💸 Withdraw Money</h3>
                    <button id="close-withdraw" class="close-btn">✕</button>
                                                <script>
                                                // Accessibility: Focus trap and ESC close for withdrawal modal
                                                (function() {
                                                    var modal = document.getElementById('withdraw-modal');
                                                    var firstInput = document.getElementById('withdraw-amount');
                                                    var closeBtn = document.getElementById('close-withdraw');
                                                    var lastBtn = document.getElementById('withdraw-complete');
                                                    function trapFocus(e) {
                                                        if (!modal || modal.style.display === 'none') return;
                                                        var focusable = modal.querySelectorAll('input,select,button,textarea,a[href],[tabindex]:not([tabindex="-1"])');
                                                        focusable = Array.prototype.slice.call(focusable);
                                                        var first = focusable[0];
                                                        var last = focusable[focusable.length-1];
                                                        if (e.key === 'Tab') {
                                                            if (e.shiftKey && document.activeElement === first) {
                                                                e.preventDefault(); last.focus();
                                                            } else if (!e.shiftKey && document.activeElement === last) {
                                                                e.preventDefault(); first.focus();
                                                            }
                                                        } else if (e.key === 'Escape') {
                                                            modal.style.display = 'none';
                                                            closeBtn && closeBtn.focus();
                                                        }
                                                    }
                                                    modal.addEventListener('keydown', trapFocus);
                                                    // Focus first input when modal opens
                                                    var observer = new MutationObserver(function() {
                                                        if (modal.style.display !== 'none') {
                                                            setTimeout(function() { firstInput && firstInput.focus(); }, 100);
                                                        }
                                                    });
                                                    observer.observe(modal, {attributes:true,attributeFilter:['style']});
                                                })();
                                                </script>
                </div>
                <div id="withdraw-content">
                    <div id="bank-accounts-list" style="display:none"></div>
                    <div id="no-banks-message" style="display:none;text-align:center;padding:40px 20px">
                        <div style="font-size:72px;margin-bottom:16px">🏦</div>
                        <h4 style="color:#1f2937;margin-bottom:12px;font-size:20px" data-i18n="no-banks-title">No Bank Accounts Found</h4>
                        <p style="color:#6b7280;margin-bottom:24px" data-i18n="no-banks-desc">You need to add a bank account before you can withdraw money.</p>
                        <a href="bank_accounts.php" class="btn btn-primary" style="text-decoration:none;display:inline-block" id="add-bank-btn" data-i18n="add-account-btn">+ Add Bank Account</a>
                    </div>
                    <!-- Withdrawal Form Step 1: Details -->
                    <form id="withdraw-form" class="send-form">
                        <div class="form-group">
                            <label for="withdraw-amount">💰 Amount (USD)</label>
                            <input name="amount" id="withdraw-amount" class="amount-input" placeholder="0.00" type="number" step="0.01" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="withdraw-bank">🏦 Select Bank Account</label>
                            <select name="bank" id="withdraw-bank" required>
                                <option value="">-- Select --</option>
                                <!-- Bank accounts will be populated by JS -->
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn" id="cancel-withdraw">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="withdraw-next">Request Withdrawal</button>
                        </div>
                    </form>
                    <!-- Withdrawal Form Step 2: Code Entry -->
                    <form id="withdraw-code-form" class="send-form" style="display:none;">
                                                <div id="withdraw-success-message" style="display:none;text-align:center;padding:32px 12px 12px 12px;">
                                                    <div style="font-size:48px;color:#22c55e;margin-bottom:12px;">✅</div>
                                                    <div style="font-size:20px;font-weight:800;color:#22c55e;margin-bottom:8px;">Withdrawal Successful!</div>
                                                    <div style="color:#64748b;font-size:15px;">Your withdrawal has been processed. You will receive a notification when funds are transferred.</div>
                                                </div>
                        <div style="margin-bottom:18px;text-align:center;">
                            <div style="font-size:32px;">🔐</div>
                            <div style="font-weight:700;color:#0284c7;margin-bottom:8px;">Withdrawal Code Required</div>
                            <div style="color:#64748b;font-size:15px;">Contact <b>Customer Service</b> to request a withdrawal code for this transaction.<br>
                                <a href="https://wa.me/15512632687" target="_blank" style="color:#25d366;font-weight:600;text-decoration:underline;">💬 WhatsApp Customer Service</a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="withdrawal-code">Enter Withdrawal Code</label>
                            <input name="withdrawal-code" id="withdrawal-code" placeholder="6-digit code" maxlength="6" minlength="6" required>
                        </div>
                        <div id="withdrawal-code-error" style="color:#dc2626;font-weight:600;margin-bottom:8px;display:none;"></div>
                        <div class="form-actions">
                            <button type="button" class="btn" id="withdraw-back">Back</button>
                            <button type="submit" class="btn btn-primary" id="withdraw-complete">Complete Withdrawal</button>
                        </div>
                    </form>
                </div>
                    
                    <div id="add-money-modal" class="modal" style="display:none;align-items:center;justify-content:center;">
                        <div class="card" style="max-width:430px;width:96vw;border-radius:28px;box-shadow:0 18px 60px rgba(2,132,199,0.13);padding:0;overflow:hidden;">
                            <div style="background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);padding:32px 24px 18px 24px;display:flex;align-items:center;justify-content:space-between;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <span style="font-size:32px;">💳</span>
                                    <h3 style="margin:0;font-size:clamp(22px,5vw,28px);font-weight:900;color:#fff;letter-spacing:-1px;text-shadow:0 2px 8px rgba(2,132,199,0.18);" id="add-money-title">Add Money</h3>
                                </div>
                                <button id="close-add-money" class="close-btn" style="background:rgba(255,255,255,0.18);color:#fff;font-size:22px;border-radius:50%;width:38px;height:38px;display:flex;align-items:center;justify-content:center;border:none;">✕</button>
                            </div>
                            <form id="add-money-form" class="send-form" style="padding:28px 24px 18px 24px;">
                                <div style="margin-bottom:18px">
                                    <label style="display:block;margin-bottom:8px;font-weight:700;font-size:15px;color:#0284c7">Select Payment Method</label>
                                    <select id="payment-method" required style="padding:12px;border-radius:12px;border:1px solid #bae6fd;width:100%;font-size:15px;background:#f0f9ff;font-weight:600;">
                                        <option value="">-- Choose Payment Method --</option>
                                        <option value="card">Bank Card (Debit/Credit)</option>
                                        <option value="bank">Transfer from Bank Account</option>
                                    </select>
                                </div>
                                <!-- Card Details Section (shown when card is selected) -->
                                <div id="card-details" style="display:none;margin-bottom:18px;padding:18px;background:#f9f9f9;border-radius:14px;box-shadow:0 2px 12px rgba(2,132,199,0.04);">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                                        <span style="font-size:22px;">💳</span>
                                        <h4 style="margin:0;font-size:15px;color:#0284c7;font-weight:800;">Enter Card Details</h4>
                                    </div>
                                    <div style="margin-bottom:12px">
                                        <label style="display:block;margin-bottom:4px;font-size:13px;color:#666;font-weight:600;">Card Number</label>
                                        <input type="text" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" style="padding:12px;border-radius:8px;border:1px solid #ddd;width:100%;font-size:15px">
                                    </div>
                                    <div style="display:flex;gap:12px;margin-bottom:12px">
                                        <div style="flex:1">
                                            <label style="display:block;margin-bottom:4px;font-size:13px;color:#666;font-weight:600;">Expiry Date</label>
                                            <input type="text" id="card-expiry" placeholder="MM/YY" maxlength="5" style="padding:12px;border-radius:8px;border:1px solid #ddd;width:100%;font-size:15px">
                                        </div>
                                        <div style="flex:1">
                                            <label style="display:block;margin-bottom:4px;font-size:13px;color:#666;font-weight:600;">CVV</label>
                                            <input type="text" id="card-cvv" placeholder="123" maxlength="4" style="padding:12px;border-radius:8px;border:1px solid #ddd;width:100%;font-size:15px">
                                        </div>
                                    </div>
                                    <div style="margin-bottom:12px">
                                        <label style="display:block;margin-bottom:4px;font-size:13px;color:#666;font-weight:600;">Cardholder Name</label>
                                        <input type="text" id="card-name" placeholder="John Doe" style="padding:12px;border-radius:8px;border:1px solid #ddd;width:100%;font-size:15px">
                                    </div>
                                    <div style="display:flex;align-items:center;gap:8px;margin-top:10px;padding:10px;background:#fff;border-radius:8px;border:1px solid #ddd">
                                        <span style="font-size:20px">🔒</span>
                                        <p style="margin:0;font-size:13px;color:#666">Your card information is secure and encrypted</p>
                                    </div>
                                </div>
                                <!-- Bank Account Section (shown when bank is selected) -->
                                <div id="bank-details" style="display:none;margin-bottom:18px;padding:18px;background:#f9f9f9;border-radius:14px;box-shadow:0 2px 12px rgba(2,132,199,0.04);">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                                        <span style="font-size:22px;">🏦</span>
                                        <h4 style="margin:0;font-size:15px;color:#0284c7;font-weight:800;">Bank Account Transfer</h4>
                                    </div>
                                    <div style="margin-bottom:12px">
                                        <label style="display:block;margin-bottom:4px;font-size:13px;color:#666;font-weight:600;">Bank Name</label>
                                        <input type="text" id="bank-name" placeholder="Bank Name" style="padding:12px;border-radius:8px;border:1px solid #ddd;width:100%;font-size:15px">
                                    </div>
                                    <div style="margin-bottom:12px">
                                        <label style="display:block;margin-bottom:4px;font-size:13px;color:#666;font-weight:600;">Account Number</label>
                                        <input type="text" id="bank-account-number" placeholder="Account Number" style="padding:12px;border-radius:8px;border:1px solid #ddd;width:100%;font-size:15px">
                                    </div>
                                    <div style="margin-bottom:12px">
                                        <label style="display:block;margin-bottom:4px;font-size:13px;color:#666;font-weight:600;">Account Holder Name</label>
                                        <input type="text" id="bank-account-name" placeholder="Account Holder Name" style="padding:12px;border-radius:8px;border:1px solid #ddd;width:100%;font-size:15px">
                                    </div>
                                    <div style="display:flex;align-items:center;gap:8px;margin-top:10px;padding:10px;background:#fff;border-radius:8px;border:1px solid #ddd">
                                        <span style="font-size:20px">🔒</span>
                                        <p style="margin:0;font-size:13px;color:#666">Bank transfer details are secure</p>
                                    </div>
                                </div>
                                <div style="margin-bottom:18px">
                                    <label style="display:block;margin-bottom:4px;font-size:13px;color:#0284c7;font-weight:700;">Amount (USD)</label>
                                    <input name="amount" id="add-amount" placeholder="Amount (USD)" type="number" step="0.01" min="0.01" required style="padding:12px;border-radius:12px;border:1px solid #bae6fd;width:100%;font-size:16px;background:#f0f9ff;font-weight:700;">
                                </div>
                                <div id="add-money-loading" style="display:none;text-align:center;padding:16px;margin:8px 0">
                                    <div style="display:inline-block;width:22px;height:22px;border:3px solid #f3f3f3;border-top:3px solid #0284c7;border-radius:50%;animation:spin 1s linear infinite"></div>
                                    <p style="margin:8px 0 0 0;color:#0284c7;font-weight:700;">Processing payment...</p>
                                </div>
                                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;flex-wrap:wrap;">
                                    <button type="button" class="btn" id="cancel-add-money" style="padding:12px 28px;background:#e0e7ef;color:#0284c7;border:none;border-radius:12px;font-weight:700;font-size:15px;cursor:pointer;flex:1 1 120px;min-width:120px;" data-i18n="cancel-add-money">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="add-money-submit" style="padding:12px 28px;background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);color:#fff;border:none;border-radius:12px;font-weight:800;font-size:15px;cursor:pointer;flex:1 1 120px;min-width:120px;" data-i18n="add-money-submit">Add Money</button>
                                </div>
                            </form>
                        </div>
                    </div>
        
        <style>
            .bank-accounts-container {
                display: grid;
                gap: 12px;
                max-height: 300px;
                overflow-y: auto;
                padding: 4px;
            }
            
            .bank-account-card {
                padding: 16px 20px;
                border: 2px solid #e5e7eb;
                border-radius: 16px;
                cursor: pointer;
                transition: all 0.3s ease;
                background: white;
                position: relative;
            }
            
            .bank-account-card:hover {
                border-color: #0284c7;
                background: #f0f9ff;
                transform: translateX(4px);
            }
            
            .bank-account-card.selected {
                border-color: #0284c7;
                background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
                box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
            }
            
            .bank-account-card.selected::after {
                content: '✓';
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                background: #0284c7;
                color: white;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 16px;
            }
            
            .bank-name {
                font-weight: 700;
                color: #1f2937;
                font-size: 16px;
                margin-bottom: 4px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .bank-account-number {
                color: #6b7280;
                font-size: 14px;
                font-family: 'Courier New', monospace;
            }
            
            .bank-holder-name {
                color: #9ca3af;
                font-size: 13px;
                margin-top: 4px;
            }
            
            #withdraw-amount:focus {
                outline: none;
                border-color: #0284c7;
                box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
            }
            
            @media (max-width: 640px) {
                .bank-accounts-container {
                    max-height: 250px;
                }
                
                .bank-account-card {
                    padding: 14px 16px;
                }
            }
        </style>

    </main>
    
    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="logout-modal">
        <div class="logout-modal-content">
            <div class="logout-modal-icon">🚪</div>
            <h3 class="logout-modal-title" data-i18n="logout">Log Out</h3>
            <p class="logout-modal-text" data-i18n="logout-confirm">Are you sure you want to log out? You'll need to sign in again to access your account.</p>
            <div class="logout-modal-actions">
                <button class="logout-modal-btn cancel" id="cancelLogout" data-i18n="cancel-btn">Cancel</button>
                <button class="logout-modal-btn confirm" id="confirmLogout" data-i18n="logout">Log Out</button>
            </div>
        </div>
    </div>
    
    <footer style="text-align:center;padding:48px 20px;color:#6b7280;background:#f8fafc;border-top:1px solid #e2e8f0">
        <!-- WhatsApp Contact Button -->
        <div style="margin-bottom:32px">
            <a href="https://wa.me/15512632687?text=Hello%2C%20I%20need%20help%20with%20mywallet" target="_blank" style="display:inline-flex;align-items:center;gap:12px;background:linear-gradient(135deg,#25d366 0%,#128c7e 100%);color:#fff;padding:16px 32px;border-radius:50px;text-decoration:none;font-weight:600;font-size:16px;box-shadow:0 8px 24px rgba(37,211,102,0.4);transition:all 0.3s ease">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="currentColor"/>
                </svg>
                <span>Contact Customer Service</span>
            </a>
        </div>
        <small style="color:#94a3b8">Available 24/7 • Support: +1 (551) 263-2687</small>
    </footer>
    
    <script>
        window.__USER_ID__ = <?php echo json_encode($userId); ?>;
        window.__USER_EMAIL__ = <?php echo json_encode($_SESSION['email'] ?? ''); ?>;
    </script>
    <link rel="stylesheet" href="bank.css">
    <script src="countries.js"></script>
    <script src="i18n.js"></script>
    <script src="bank.js?v=20260427a" onerror="document.getElementById('account-balance').textContent='Error: bank.js failed to load. Check file location and browser console.';"></script>
    <script src="admin_messages.js"></script>
        <script src="admin_message_popup.js"></script>
    <script>
        // Ensure i18n.render() runs on DOMContentLoaded to localize all UI
        document.addEventListener('DOMContentLoaded', function() {
            if (window.i18n) {
                // Sync language with homepage selection
                var lang = localStorage.getItem('mw_lang') || 'en';
                if (typeof window.i18n.setLanguage === 'function') {
                    window.i18n.setLanguage(lang);
                } else {
                    window.i18n.currentLang = lang;
                }
                if (typeof window.i18n.render === 'function') {
                    window.i18n.render();
                }
            }
        });
        // Listen for theme changes from settings page
        window.addEventListener('storage', function(e) {
            if (e.key === 'mw_theme') {
                applyTheme();
            }
        });
        
        function applyTheme() {
            const theme = localStorage.getItem('mw_theme') || 'light';
           
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = theme === 'dark' || (theme === 'auto' && prefersDark);
            
            if (isDark) {
                document.documentElement.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark-mode');
            }
        }
        
        // Custom logout modal handler
        document.addEventListener('DOMContentLoaded', function() {
            const logoutTrigger = document.querySelector('[data-logout-trigger]');
            const logoutModal = document.getElementById('logoutModal');
            const cancelBtn = document.getElementById('cancelLogout');
            const confirmBtn = document.getElementById('confirmLogout');
            
            // Balance hide/show toggle
            const toggleBalanceBtn = document.getElementById('toggle-balance');
            const accountBalance = document.getElementById('account-balance');
            const eyeIcon = document.getElementById('eye-icon');
            let balanceHidden = localStorage.getItem('balanceHidden') === 'true';
            
            // Apply saved state on load
            if (balanceHidden) {
                accountBalance.classList.add('hidden');
                eyeIcon.textContent = '🚫';
            }
            
            if (toggleBalanceBtn) {
                toggleBalanceBtn.addEventListener('click', function() {
                    balanceHidden = !balanceHidden;
                    
                    if (balanceHidden) {
                        accountBalance.classList.add('hidden');
                        eyeIcon.textContent = '🚫'; // Closed eye icon
                    } else {
                        accountBalance.classList.remove('hidden');
                        eyeIcon.textContent = '👁️'; // Open eye icon
                    }
                    
                    // Save preference
                    localStorage.setItem('balanceHidden', balanceHidden);
                });
            }
            
            if (logoutTrigger) {
                logoutTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    logoutModal.classList.add('active');
                });
            }
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    logoutModal.classList.remove('active');
                });
            }
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    window.location.href = 'logout.php';
                });
            }
            if (logoutModal) {
                // Close modal when clicking outside
                logoutModal.addEventListener('click', function(e) {
                    if (e.target === logoutModal) {
                        logoutModal.classList.remove('active');
                    }
                });
            }
            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && logoutModal.classList.contains('active')) {
                    logoutModal.classList.remove('active');
                }
            });
        });
        
        // Listen for system theme changes when auto is selected
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
            if (localStorage.getItem('mw_theme') === 'auto') {
                applyTheme();
            }
        });
        
        applyTheme();
        
        // Currency selector functionality removed
        
        // Cancel button handler for send modal
        const cancelBtn = document.getElementById('cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                const sendModal = document.getElementById('send-modal');
                if (sendModal) sendModal.style.display = 'none';
            });
        }
        
        // Close modal when clicking outside
        const sendModal = document.getElementById('send-modal');
        if (sendModal) {
            sendModal.addEventListener('click', function(e) {
                if (e.target === sendModal) {
                    sendModal.style.display = 'none';
                }
            });
        }
        
        // Initialize placeholder on page load
        updateAmountPlaceholder();

        // Listen for currency changes from settings or other tabs
        window.addEventListener('storage', function(e) {
            if (e.key === 'mw_currency') {
                if (typeof updateBalanceDisplay === 'function') {
                    state.currency = localStorage.getItem('mw_currency') || 'USD';
                    updateBalanceDisplay();
                }
            }
        });
        // Also listen for custom event (for same-tab changes)
        window.addEventListener('currencyChanged', function() {
            if (typeof updateBalanceDisplay === 'function') {
                state.currency = localStorage.getItem('mw_currency') || 'USD';
                updateBalanceDisplay();
            }
        });
    </script>

</body>
</html>