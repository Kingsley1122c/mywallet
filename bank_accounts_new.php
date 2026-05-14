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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Accounts | Mivonta</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .bank-accounts { 
            max-width: 1100px; 
            margin: 36px auto; 
            padding: 20px; 
        }
        
        .page-header {
            text-align: center;
            color: #fff;
            margin-bottom: 48px;
        }
        
        .page-header h2 {
            font-size: 42px;
            font-weight: 900;
            margin-bottom: 12px;
            text-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .page-header p {
            font-size: 18px;
            opacity: 0.95;
            font-weight: 500;
        }
        
        .add-account-form { 
            background: #fff; 
            padding: 32px; 
            border-radius: 24px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-bottom: 32px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .add-account-form h3 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 8px;
            color: #374151;
        }
        
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        }
        
        .form-group select {
            cursor: pointer;
            appearance: none;
            background: #fff url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="%23667eea" d="M4 6l4 4 4-4z"/></svg>') no-repeat right 12px center;
            padding-right: 40px;
        }
        
        .btn-add {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(102,126,234,0.4);
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(102,126,234,0.5);
        }
        
        .accounts-section h3 {
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 20px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .account-card { 
            background: #fff;
            padding: 24px; 
            border-radius: 20px; 
            box-shadow: 0 12px 32px rgba(0,0,0,0.15);
            margin-bottom: 16px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .account-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.2);
            border-color: rgba(102,126,234,0.3);
        }
        
        .account-info { 
            flex: 1; 
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .bank-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 900;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .account-details {
            flex: 1;
        }
        
        .account-info h4 { 
            margin: 0 0 6px 0; 
            color: #1e293b;
            font-size: 18px;
            font-weight: 800;
        }
        
        .account-info p { 
            margin: 4px 0; 
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
        }
        
        .account-number { 
            font-weight: 700;
            letter-spacing: 2px;
            color: #475569;
            font-family: 'Courier New', monospace;
        }
        
        .account-actions { 
            display: flex; 
            gap: 10px; 
        }
        
        .btn-delete { 
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 10px; 
            cursor: pointer; 
            font-size: 14px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239,68,68,0.3);
        }
        
        .btn-delete:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239,68,68,0.4);
        }
        
        .empty-state {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 48px;
            border-radius: 20px;
            text-align: center;
            color: #fff;
            border: 2px solid rgba(255,255,255,0.2);
        }
        
        .empty-state p {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .page-header h2 {
                font-size: 32px;
            }
            
            .account-card {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            
            .account-info {
                flex-direction: column;
                width: 100%;
                gap: 12px;
            }
            
            .account-actions {
                width: 100%;
            }
            
            .btn-delete {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <h2>Mivonta</h2>
        <div style="display:flex;align-items:center;gap:12px;color:#fff">
            <span style="opacity:.9">Signed in as <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <a class="logout" href="logout.php">Log out</a>
        </div>
    </header>

    <main class="container">
        <div class="bank-accounts">
            <div class="page-header">
                <h2>💳 Bank Accounts</h2>
                <p>Manage your bank accounts from around the world</p>
            </div>

            <div class="add-account-form">
                <h3>➕ Add New Bank Account</h3>
                <form id="addAccountForm" onsubmit="return addBankAccount(event);">
                    <div class="form-group">
                        <label for="countrySelect">🌍 Select Country</label>
                        <select id="countrySelect" required>
                            <option value="">Choose your country</option>
                            <option value="Australia">🇦🇺 Australia</option>
                            <option value="Canada">🇨🇦 Canada</option>
                            <option value="France">🇫🇷 France</option>
                            <option value="Germany">🇩🇪 Germany</option>
                            <option value="India">🇮🇳 India</option>
                            <option value="Japan">🇯🇵 Japan</option>
                            <option value="Korea">🇰🇷 South Korea</option>
                            <option value="Mexico">🇲🇽 Mexico</option>
                            <option value="Nigeria">🇳🇬 Nigeria</option>
                            <option value="Taiwan">🇹🇼 Taiwan</option>
                            <option value="UK">🇬🇧 United Kingdom</option>
                            <option value="USA">🇺🇸 United States</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bankSelect">🏦 Select Bank</label>
                        <select id="bankSelect" required>
                            <option value="">First select a country</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="accountNumber">🔢 Account Number</label>
                        <input type="text" id="accountNumber" placeholder="Enter your account number" required>
                    </div>
                    <div class="form-group">
                        <label for="accountHolder">👤 Account Holder Name</label>
                        <input type="text" id="accountHolder" placeholder="Enter account holder name" required>
                    </div>
                    <button type="submit" class="btn-add">Add Bank Account</button>
                </form>
            </div>

            <div class="accounts-section">
                <h3>Your Linked Accounts</h3>
                <div id="accountsList" style="margin-top:12px;"></div>
            </div>
        </div>
    </main>

    <script>
        let csrf = null;
        let banksList = {};

        // Load banks list from server
        fetch('banks_list.php?api=1')
            .then(r => r.json())
            .then(data => {
                banksList = data;
                document.getElementById('countrySelect').addEventListener('change', updateBankSelect);
            });

        // Get CSRF token
        fetch('get_csrf.php', {credentials: 'same-origin'})
            .then(r => r.json())
            .then(j => { csrf = j.csrf; loadAccounts(); });

        function updateBankSelect() {
            const country = document.getElementById('countrySelect').value;
            const bankSelect = document.getElementById('bankSelect');
            bankSelect.innerHTML = '<option value="">Select Bank</option>';
            if (country && banksList[country]) {
                // Sort banks alphabetically
                const sortedBanks = [...banksList[country]].sort((a, b) => a.localeCompare(b));
                sortedBanks.forEach(bank => {
                    const option = document.createElement('option');
                    option.value = bank;
                    option.textContent = bank;
                    bankSelect.appendChild(option);
                });
            }
        }

        function loadAccounts() {
            fetch('api/bank_accounts.php', {credentials: 'same-origin'})
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('accountsList');
                    if (!data.accounts || data.accounts.length === 0) {
                        list.innerHTML = '<div class="empty-state"><p>🏦 No bank accounts added yet. Add your first account above!</p></div>';
                        return;
                    }
                    list.innerHTML = data.accounts.map(acc => {
                        const initials = escapeHtml(acc.bank_name).substring(0, 2).toUpperCase();
                        return `
                        <div class="account-card">
                            <div class="account-info">
                                <div class="bank-icon">${initials}</div>
                                <div class="account-details">
                                    <h4>${escapeHtml(acc.bank_name)}</h4>
                                    <p class="account-number">****${escapeHtml(acc.account_number.slice(-4))}</p>
                                    <p>👤 ${escapeHtml(acc.account_holder)}</p>
                                </div>
                            </div>
                            <div class="account-actions">
                                <button class="btn-delete" onclick="deleteAccount('${escapeHtml(acc.id)}');">🗑️ Delete</button>
                            </div>
                        </div>
                    `}).join('');
                });
        }

        function addBankAccount(e) {
            e.preventDefault();
            const bankName = document.getElementById('bankSelect').value;
            const accountNumber = document.getElementById('accountNumber').value.trim();
            const accountHolder = document.getElementById('accountHolder').value.trim();

            if (!bankName || !accountNumber || !accountHolder) {
                alert('All fields are required');
                return false;
            }

            fetch('api/bank_accounts.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'add', bank_name: bankName, account_number: accountNumber, account_holder: accountHolder, csrf_token: csrf})
            })
            .then(r => r.json())
            .then(j => {
                if (j.error) return alert(j.error);
                document.getElementById('countrySelect').value = '';
                document.getElementById('bankSelect').innerHTML = '<option value="">Select Bank</option>';
                document.getElementById('accountNumber').value = '';
                document.getElementById('accountHolder').value = '';
                loadAccounts();
                alert('✅ Bank account added successfully!');
            })
            .catch(() => alert('Error adding account'));
            return false;
        }

        function deleteAccount(id) {
            if (!confirm('Delete this bank account?')) return;
            fetch('api/bank_accounts.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'delete', account_id: id, csrf_token: csrf})
            })
            .then(r => r.json())
            .then(j => {
                if (j.error) return alert(j.error);
                loadAccounts();
                alert('🗑️ Bank account deleted');
            })
            .catch(() => alert('Error deleting account'));
        }

        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, c => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            })[c]);
        }
    </script>
</body>
</html>
