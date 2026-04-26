<?php
session_start();

$usersFile = __DIR__ . '/users.json';
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([], JSON_PRETTY_PRINT));
}

$users = json_decode(file_get_contents($usersFile), true) ?: [];

// Find current user and role
if (empty($_SESSION['user_id'])) {
    header('Location: login.php'); exit();
}

$currentUser = null;
foreach ($users as $u) { if ($u['id'] == $_SESSION['user_id']) { $currentUser = $u; break; } }
if (!$currentUser) { header('Location: login.php'); exit(); }

if (($currentUser['role'] ?? 'user') !== 'admin') {
    http_response_code(403);
    echo "<h2>403 - Forbidden</h2><p>You must be an admin to access this page.</p><p><a href=\"index.php\">Back home</a></p>";
    exit();
}

// CSRF
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

// Get users for dropdown (excluding admins)
$regularUsers = array_filter($users, function($u) {
    return ($u['role'] ?? 'user') === 'user';
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Generate Withdrawal Codes — Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .codes-container {
            max-width: 1200px;
            margin: 30px auto;
            background: #fff;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .page-header h2 {
            font-size: 28px;
            font-weight: 800;
            color: #1f2937;
            margin: 0;
        }
        
        .nav-links {
            display: flex;
            gap: 12px;
        }
        
        .nav-links a {
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
        }
        
        .generate-form {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 28px;
            border-radius: 12px;
            margin-bottom: 32px;
            border-left: 4px solid #0284c7;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group select,
        .form-group input {
            padding: 12px 16px;
            border: 2px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
        }
        
        .btn-generate {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #fff;
            border: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }
        
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.4);
        }
        
        .btn-generate:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .codes-list {
            margin-top: 32px;
        }
        
        .codes-list h3 {
            font-size: 20px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 20px;
        }
        
        .code-card {
            background: #fff;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }
        
        .code-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .code-card.used {
            opacity: 0.6;
            background: #f9fafb;
        }
        
        .code-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .code-number {
            font-size: 32px;
            font-weight: 900;
            color: #0284c7;
            font-family: 'Courier New', monospace;
            letter-spacing: 4px;
        }
        
        .code-status {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .code-status.active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .code-status.used {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .code-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            color: #6b7280;
            font-size: 14px;
        }
        
        .code-detail {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .code-detail-label {
            font-weight: 600;
            color: #9ca3af;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .code-detail-value {
            font-weight: 700;
            color: #1f2937;
        }
        
        .success-message {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .success-message.show {
            display: block;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success-message-content {
            font-weight: 600;
            color: #065f46;
        }
        
        .generated-code {
            font-size: 28px;
            font-weight: 900;
            color: #10b981;
            font-family: 'Courier New', monospace;
            letter-spacing: 4px;
            margin-top: 8px;
        }
        
        @media (max-width: 768px) {
            .codes-container {
                padding: 20px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .code-details {
                grid-template-columns: 1fr;
            }
            
            .code-number {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="codes-container">
        <div class="page-header">
            <h2>🔐 Withdrawal Code Generator</h2>
            <div class="nav-links">
                <a href="admin.php">← Back to Admin</a>
                <a href="dashboard.php">Dashboard</a>
            </div>
        </div>
        
        <div id="success-message" class="success-message">
            <div class="success-message-content">
                <div>✅ Withdrawal code generated successfully!</div>
                <div class="generated-code" id="generated-code-display"></div>
                <div style="margin-top: 8px; font-size: 14px;">Send this code to the user's email.</div>
            </div>
        </div>
        
        <div class="generate-form">
            <h3 style="margin: 0 0 20px 0; color: #0c4a6e; font-size: 20px;">Generate New Withdrawal Code</h3>
            <form id="generate-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="user-select">Select User</label>
                        <select id="user-select" required>
                            <option value="">-- Choose User --</option>
                            <?php foreach ($regularUsers as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['surname'] . ' (' . $user['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount-input">Withdrawal Amount ($)</label>
                        <input type="number" id="amount-input" step="0.01" min="10" placeholder="Enter amount" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-generate" id="generate-btn">
                    🎯 Generate Code
                </button>
            </form>
        </div>
        
        <div class="codes-list">
            <h3>📋 Recent Withdrawal Codes</h3>
            <div id="codes-container">
                <div style="text-align: center; padding: 40px; color: #9ca3af;">
                    Loading codes...
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let csrf = null;
        
        // Get CSRF token
        fetch('get_csrf.php', {credentials: 'same-origin'})
            .then(r => r.json())
            .then(j => { csrf = j.csrf; loadCodes(); })
            .catch(() => { loadCodes(); });
        
        // Handle form submission
        document.getElementById('generate-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('user-select').value;
            const amount = parseFloat(document.getElementById('amount-input').value);
            const generateBtn = document.getElementById('generate-btn');
            
            if (!userId || !amount || amount < 10) {
                alert('Please select a user and enter a valid amount (minimum $10)');
                return;
            }
            
            generateBtn.disabled = true;
            generateBtn.textContent = '⏳ Generating...';
            
            fetch('api/withdrawal_codes.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'generate',
                    user_id: userId,
                    amount: amount,
                    csrf_token: csrf
                })
            })
            .then(r => r.json())
            .then(data => {
                generateBtn.disabled = false;
                generateBtn.textContent = '🎯 Generate Code';
                
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                
                if (data.success) {
                    // Show success message
                    const successMsg = document.getElementById('success-message');
                    const codeDisplay = document.getElementById('generated-code-display');
                    codeDisplay.textContent = data.code;
                    successMsg.classList.add('show');
                    
                    // Hide after 5 seconds
                    setTimeout(() => {
                        successMsg.classList.remove('show');
                    }, 5000);
                    
                    // Reset form
                    document.getElementById('generate-form').reset();
                    
                    // Reload codes
                    loadCodes();
                }
            })
            .catch(err => {
                console.error('Error:', err);
                generateBtn.disabled = false;
                generateBtn.textContent = '🎯 Generate Code';
                alert('Network error. Please try again.');
            });
        });
        
        // Load and display codes
        function loadCodes() {
            fetch('api/withdrawal_codes.php?action=list', {credentials: 'same-origin'})
                .then(r => r.json())
                .then(data => {
                    const container = document.getElementById('codes-container');
                    
                    if (!data.codes || data.codes.length === 0) {
                        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #9ca3af;">No withdrawal codes generated yet.</div>';
                        return;
                    }
                    
                    // Sort by date (newest first)
                    const sortedCodes = data.codes.sort((a, b) => b.created_at - a.created_at);
                    
                    container.innerHTML = sortedCodes.map(code => {
                        const createdDate = new Date(code.created_at * 1000).toLocaleString();
                        const usedDate = code.used_at ? new Date(code.used_at * 1000).toLocaleString() : 'N/A';
                        const statusClass = code.used ? 'used' : 'active';
                        const statusText = code.used ? 'Used' : 'Active';
                        
                        return `
                            <div class="code-card ${code.used ? 'used' : ''}">
                                <div class="code-header">
                                    <div class="code-number">${code.code}</div>
                                    <div class="code-status ${statusClass}">${statusText}</div>
                                </div>
                                <div class="code-details">
                                    <div class="code-detail">
                                        <span class="code-detail-label">User</span>
                                        <span class="code-detail-value">${code.user_email}</span>
                                    </div>
                                    <div class="code-detail">
                                        <span class="code-detail-label">Amount</span>
                                        <span class="code-detail-value">$${code.amount.toFixed(2)}</span>
                                    </div>
                                    <div class="code-detail">
                                        <span class="code-detail-label">Created</span>
                                        <span class="code-detail-value">${createdDate}</span>
                                    </div>
                                    ${code.used ? `
                                    <div class="code-detail">
                                        <span class="code-detail-label">Used</span>
                                        <span class="code-detail-value">${usedDate}</span>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        `;
                    }).join('');
                })
                .catch(err => {
                    console.error('Error loading codes:', err);
                    document.getElementById('codes-container').innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;">Error loading codes. Please refresh the page.</div>';
                });
        }
    </script>
</body>
</html>
