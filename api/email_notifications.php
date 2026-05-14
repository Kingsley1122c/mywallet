<?php
// Email notification system for transactions
// This file handles sending emails after each transaction

include_once __DIR__ . '/../url_helpers.php';

function loadEmailConfig() {
    $configFile = __DIR__ . '/../email_config.php';
    if (!file_exists($configFile)) {
        return [
            'enabled' => false,
            'provider' => 'none',
            'from_email' => 'noreply@mivonta.com',
            'from_name' => 'Mivonta',
        ];
    }

    $config = include $configFile;
    return is_array($config) ? $config : [
        'enabled' => false,
        'provider' => 'none',
        'from_email' => 'noreply@mivonta.com',
        'from_name' => 'Mivonta',
    ];
}

function appendEmailLog($message) {
    $logFile = __DIR__ . '/../email_log.txt';
    @file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND);
}

function sendConfiguredEmail($to, $subject, $body, array $config, $contextLabel) {
    $provider = strtolower((string) ($config['provider'] ?? 'none'));
    $enabled = !empty($config['enabled']);

    if (!$enabled || $provider === 'none') {
        appendEmailLog("Email disabled or unconfigured for {$contextLabel} to: {$to}");
        return false;
    }

    if ($provider === 'resend' && file_exists(__DIR__ . '/send_email_resend.php')) {
        include_once __DIR__ . '/send_email_resend.php';
        return sendEmailResend($to, $subject, $body);
    }

    if ($provider === 'smtp' && file_exists(__DIR__ . '/send_email_smtp.php')) {
        include_once __DIR__ . '/send_email_smtp.php';
        return sendEmailSMTP($to, $subject, $body, $config);
    }

    if ($provider === 'php_mail') {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: {$config['from_name']} <{$config['from_email']}>\r\n";
        $headers .= "Reply-To: {$config['from_email']}\r\n";
        return @mail($to, $subject, $body, $headers);
    }

    appendEmailLog("Unsupported email provider '{$provider}' for {$contextLabel} to: {$to}");
    return false;
}

function sendTransactionEmail($userEmail, $transactionData) {
    $config = loadEmailConfig();
    
    // Email configuration
    $to = $userEmail;
    $subject = "Transaction Notification - Mivonta";
    
    // Get transaction details
    $type = $transactionData['type'];
    $amount = number_format($transactionData['amount'], 2);
    $date = $transactionData['date'];
    $balance = isset($transactionData['balance']) ? number_format($transactionData['balance'], 2) : '0.00';
    
    // Build email body based on transaction type
    $body = buildEmailBody($type, $amount, $date, $balance, $transactionData);
    
    $result = sendConfiguredEmail($to, $subject, $body, $config, 'transaction email');
    
    // Log email attempts (optional - creates a log file)
    if (!$result) {
        appendEmailLog("Failed to send email to: {$to} (Type: {$type})");
    }
    
    return $result;
}

function buildEmailBody($type, $amount, $date, $balance, $data) {
    $dashboardUrl = app_url('dashboard.php');

    $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fb; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(90deg, #0066d6, #0052a3); color: #fff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 30px 20px; }
        .transaction-box { background: #f9fafb; border-left: 4px solid #0066d6; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .amount { font-size: 32px; font-weight: bold; color: #0066d6; margin: 10px 0; }
        .details { margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .label { color: #6b7280; font-weight: 600; }
        .value { color: #0f172a; font-weight: 500; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 13px; }
        .button { display: inline-block; background: #0066d6; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💳 Mivonta</h1>
            <p style="margin: 5px 0 0 0;">Transaction Notification</p>
        </div>
        <div class="content">';
    
    // Transaction type specific content
    if ($type === 'send') {
        $recipient = isset($data['recipient']) ? $data['recipient'] : 'Unknown';
        $recipientEmail = isset($data['recipient_email']) ? $data['recipient_email'] : '';
        $html .= '
            <h2 style="color: #0f172a;">Money Sent Successfully</h2>
            <p>You have sent money from your Mivonta account.</p>
            <div class="transaction-box">
                <div style="color: #6b7280; font-size: 14px;">Amount Sent</div>
                <div class="amount">-$' . $amount . '</div>
            </div>
            <div class="details">
                <div class="detail-row">
                    <span class="label">Recipient:</span>
                    <span class="value">' . htmlspecialchars($recipient) . ($recipientEmail ? ' (' . htmlspecialchars($recipientEmail) . ')' : '') . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Date & Time:</span>
                    <span class="value">' . $date . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Current Balance:</span>
                    <span class="value">$' . $balance . '</span>
                </div>
            </div>';
    } elseif ($type === 'receive') {
        $sender = isset($data['sender']) ? $data['sender'] : 'Unknown';
        $senderEmail = isset($data['sender_email']) ? $data['sender_email'] : '';
        $html .= '
            <h2 style="color: #0f172a;">💰 Money Received!</h2>
            <p><strong>' . htmlspecialchars($sender) . '</strong> sent you money!</p>
            <div class="transaction-box">
                <div style="color: #6b7280; font-size: 14px;">Amount Received</div>
                <div class="amount" style="color: #00a896;">+$' . $amount . '</div>
            </div>
            <div class="details">
                <div class="detail-row">
                    <span class="label">From:</span>
                    <span class="value">' . htmlspecialchars($sender) . ($senderEmail ? ' (' . htmlspecialchars($senderEmail) . ')' : '') . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Date & Time:</span>
                    <span class="value">' . $date . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Current Balance:</span>
                    <span class="value">$' . $balance . '</span>
                </div>
            </div>';
    } elseif ($type === 'add') {
        $method = isset($data['method']) ? $data['method'] : 'Bank Card';
        $html .= '
            <h2 style="color: #0f172a;">Money Added to Account</h2>
            <p>You have successfully added money to your Mivonta account.</p>
            <div class="transaction-box">
                <div style="color: #6b7280; font-size: 14px;">Amount Added</div>
                <div class="amount" style="color: #00a896;">+$' . $amount . '</div>
            </div>
            <div class="details">
                <div class="detail-row">
                    <span class="label">Payment Method:</span>
                    <span class="value">' . htmlspecialchars($method) . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Date & Time:</span>
                    <span class="value">' . $date . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Current Balance:</span>
                    <span class="value">$' . $balance . '</span>
                </div>
            </div>';
    } elseif ($type === 'withdraw') {
        $html .= '
            <h2 style="color: #0f172a;">Withdrawal Request Submitted</h2>
            <p>Your withdrawal request has been submitted and is being processed.</p>
            <div class="transaction-box">
                <div style="color: #6b7280; font-size: 14px;">Withdrawal Amount</div>
                <div class="amount">-$' . $amount . '</div>
            </div>
            <div class="details">
                <div class="detail-row">
                    <span class="label">Date & Time:</span>
                    <span class="value">' . $date . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Status:</span>
                    <span class="value">Pending Approval</span>
                </div>
            </div>
            <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
                Our customer service team will contact you via WhatsApp to complete the withdrawal process.
            </p>';
    } elseif ($type === 'withdraw_failed') {
        $bankName = isset($data['bank_name']) ? $data['bank_name'] : 'your selected bank';
        $accountNumber = isset($data['account_number']) ? $data['account_number'] : '';
        $bankLabel = htmlspecialchars($bankName . ($accountNumber ? ' (' . $accountNumber . ')' : ''), ENT_QUOTES, 'UTF-8');
        $html .= '
            <h2 style="color: #0f172a;">Withdrawal Failed</h2>
            <p>Your withdrawal could not be completed automatically after the 24-hour processing period.</p>
            <div class="transaction-box">
                <div style="color: #6b7280; font-size: 14px;">Failed Withdrawal</div>
                <div class="amount">-$' . $amount . '</div>
            </div>
            <div class="details">
                <div class="detail-row">
                    <span class="label">Bank Account:</span>
                    <span class="value">' . $bankLabel . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Date & Time:</span>
                    <span class="value">' . $date . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Status:</span>
                    <span class="value">Failed</span>
                </div>
            </div>
            <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
                Please contact customer service to finish this transaction.
            </p>';
    }
    
    $html .= '
            <a href="' . htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') . '" class="button">View Dashboard</a>
            <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
                If you did not make this transaction, please contact us immediately.
            </p>
        </div>
        <div class="footer">
            <p><strong>Mivonta</strong> account notifications</p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>© 2026 Mivonta</p>
            <p><a href="#" style="color: #0066d6;">Terms</a> • <a href="#" style="color: #0066d6;">Privacy</a> • <a href="#" style="color: #0066d6;">Support</a></p>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}

// Welcome email for new registrations
function sendWelcomeEmail($userEmail, $data) {
    $config = loadEmailConfig();
    
    $to = $userEmail;
    $subject = "Welcome to Mivonta! 🎉";
    $name = $data['name'] ?? 'there';
    $referralCode = $data['referral_code'] ?? '';
    $date = $data['date'] ?? date('F j, Y g:i A');
    $dashboardUrl = app_url('dashboard.php');
    
    $body = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fb; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(90deg, #667eea, #764ba2); color: #fff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 32px; }
        .content { padding: 40px 30px; }
        .welcome-box { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-left: 4px solid #10b981; padding: 24px; margin: 20px 0; border-radius: 8px; }
        .feature { display: flex; align-items: flex-start; margin: 16px 0; }
        .feature-icon { background: #667eea; color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 16px; flex-shrink: 0; }
        .referral-code { background: #f8fafc; border: 2px dashed #667eea; padding: 20px; text-align: center; border-radius: 12px; margin: 24px 0; }
        .code { font-size: 28px; font-weight: 900; color: #667eea; letter-spacing: 2px; margin: 10px 0; }
        .button { display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; padding: 14px 32px; border-radius: 8px; text-decoration: none; margin: 20px 0; font-weight: 600; }
        .footer { background: #f9fafb; padding: 24px; text-align: center; color: #6b7280; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💳 Welcome to Mivonta!</h1>
            <p style="margin: 10px 0 0 0; font-size: 18px;">Your digital wallet is ready</p>
        </div>
        <div class="content">
            <h2 style="color: #0f172a; margin-bottom: 16px;">Hi ' . htmlspecialchars($name) . '! 👋</h2>
            <p style="color: #475569; font-size: 16px; line-height: 1.6;">
                Thank you for joining Mivonta! We\'re excited to have you as part of our community. Your account was successfully created on ' . htmlspecialchars($date) . '.
            </p>
            
            <div class="welcome-box">
                <h3 style="color: #166534; margin: 0 0 12px 0;">🎉 Account Successfully Created!</h3>
                <p style="color: #166534; margin: 0;">You can now send money, receive payments, and manage your finances securely.</p>
            </div>
            
            <h3 style="color: #0f172a; margin-top: 32px;">What you can do now:</h3>
            
            <div class="feature">
                <div class="feature-icon">💸</div>
                <div>
                    <strong style="color: #0f172a; display: block; margin-bottom: 4px;">Send & Receive Money</strong>
                    <span style="color: #64748b; font-size: 14px;">Transfer funds instantly to anyone, anywhere</span>
                </div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">💳</div>
                <div>
                    <strong style="color: #0f172a; display: block; margin-bottom: 4px;">Add Money</strong>
                    <span style="color: #64748b; font-size: 14px;">Top up your wallet with your bank card</span>
                </div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">🏦</div>
                <div>
                    <strong style="color: #0f172a; display: block; margin-bottom: 4px;">Withdraw to Bank</strong>
                    <span style="color: #64748b; font-size: 14px;">Transfer funds to your bank account anytime</span>
                </div>
            </div>
            
            <div class="referral-code">
                <p style="margin: 0 0 8px 0; color: #475569; font-weight: 600;">🎁 Your Unique Referral Code</p>
                <div class="code">' . htmlspecialchars($referralCode) . '</div>
                <p style="margin: 8px 0 0 0; color: #64748b; font-size: 13px;">Share this code with friends and both of you get $10 bonus!</p>
            </div>
            
            <div style="text-align: center; margin: 32px 0;">
                <a href="' . htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') . '" class="button">Go to Dashboard</a>
            </div>
            
            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 8px; margin-top: 24px;">
                <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.6;">
                    <strong>🔒 Security Tip:</strong> Never share your password with anyone. We will never ask for your password via email.
                </p>
            </div>
        </div>
        <div class="footer">
            <p><strong>Mivonta</strong> account notifications</p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>© 2026 Mivonta</p>
            <p><a href="#" style="color: #667eea;">Terms</a> • <a href="#" style="color: #667eea;">Privacy</a> • <a href="#" style="color: #667eea;">Support</a></p>
        </div>
    </div>
</body>
</html>';

    $result = sendConfiguredEmail($to, $subject, $body, $config, 'welcome email');
    
    // Log email attempts
    if (!$result) {
        appendEmailLog("Failed to send welcome email to: {$to}");
    }
    
    return $result;
}

function sendAdminMessageEmail($userEmail, $messageData) {
    $config = loadEmailConfig();

    $to = $userEmail;
    $messageType = strtolower((string) ($messageData['message_type'] ?? 'info'));
    $subjectPrefix = [
        'alert' => 'Important Admin Alert',
        'warning' => 'Admin Warning',
        'promotion' => 'Special Admin Update',
        'info' => 'Admin Message',
    ][$messageType] ?? 'Admin Message';
    $subject = $subjectPrefix . ' - Mivonta';

    $adminEmail = (string) ($messageData['from_admin_email'] ?? 'support@Mivonta.com');
    $messageBody = nl2br(htmlspecialchars((string) ($messageData['message'] ?? ''), ENT_QUOTES, 'UTF-8'));
    $dashboardUrl = app_url('dashboard.php');

    $body = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fb; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(90deg, #0f172a, #1d4ed8); color: #fff; padding: 28px 20px; text-align: center; }
        .content { padding: 28px 22px; }
        .message-box { background: #f8fafc; border-left: 4px solid #1d4ed8; border-radius: 8px; padding: 18px; margin: 18px 0; color: #0f172a; line-height: 1.7; }
        .meta { color: #64748b; font-size: 14px; margin: 18px 0; }
        .button { display: inline-block; background: #1d4ed8; color: #fff; padding: 12px 22px; border-radius: 8px; text-decoration: none; margin-top: 12px; }
        .footer { background: #f8fafc; padding: 18px 20px; text-align: center; color: #64748b; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;font-size:28px;">Mivonta</h1>
            <p style="margin:8px 0 0 0;">You received a message from the admin team</p>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>An admin has sent you a new ' . htmlspecialchars(ucfirst($messageType), ENT_QUOTES, 'UTF-8') . ' message.</p>
            <div class="message-box">' . $messageBody . '</div>
            <div class="meta">
                Sent by: ' . htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') . '<br>
                Time: ' . htmlspecialchars(date('F j, Y g:i A', (int) ($messageData['timestamp'] ?? time())), ENT_QUOTES, 'UTF-8') . '
            </div>
            <a href="' . htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') . '" class="button">Open Dashboard</a>
        </div>
        <div class="footer">
            <p>This is an automated notification from Mivonta.</p>
        </div>
    </div>
</body>
</html>';

    $result = sendConfiguredEmail($to, $subject, $body, $config, 'admin message email');

    if (!$result) {
        appendEmailLog("Failed to send admin message email to: {$to}");
    }

    return $result;
}

// Helper function to get user email from users.json
function getUserEmail($userId) {
    $usersFile = __DIR__ . '/../users.json';
    if (!file_exists($usersFile)) return null;
    
    $users = json_decode(file_get_contents($usersFile), true);
    foreach ($users as $user) {
        if ($user['id'] == $userId) {
            return $user['email'];
        }
    }
    return null;
}
?>
