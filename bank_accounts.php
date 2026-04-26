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
    <title>Bank Accounts | MyWallet</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { box-sizing: border-box; }
        
        .bank-accounts { 
            max-width: 980px; 
            margin: 36px auto; 
            padding: 20px; 
        }
        
        .account-card { 
            background: var(--card); 
            padding: 18px; 
            border-radius: 12px; 
            box-shadow: var(--shadow); 
            margin-bottom: 12px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            gap: 12px;
        }
        
        .account-info { 
            flex: 1; 
            min-width: 0;
        }
        
        .account-info h4 { 
            margin: 0; 
            color: var(--primary); 
            font-size: 16px; 
            word-break: break-word;
        }
        
        .account-info p { 
            margin: 4px 0; 
            color: var(--muted); 
            font-size: 13px; 
        }
        
        .account-number { 
            font-weight: 600; 
            letter-spacing: 2px; 
        }
        
        .account-actions { 
            display: flex; 
            gap: 8px; 
            flex-shrink: 0;
        }
        
        .btn-delete { 
            background: var(--danger); 
            color: #fff; 
            padding: 8px 12px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 12px; 
            white-space: nowrap;
        }
        
        .btn-delete:hover { 
            opacity: 0.9; 
        }
        
        .add-account-form { 
            background: var(--card); 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: var(--shadow); 
            margin-bottom: 24px; 
        }
        
        .add-account-form input, 
        .add-account-form select { 
            width: 100%; 
            padding: 10px; 
            margin-bottom: 10px; 
            border: 1px solid #eee; 
            border-radius: 8px; 
            font-size: 14px;
        }
        
        .add-account-form button { 
            padding: 10px 20px; 
            background: var(--primary); 
            color: #fff; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            width: 100%;
            font-size: 14px;
        }
        
        .add-account-form button:hover { 
            background: var(--primary-700); 
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .bank-accounts {
                margin: 12px auto;
                padding: 12px;
            }
            
            .bank-accounts h2 {
                font-size: 22px;
            }
            
            .bank-accounts h3 {
                font-size: 18px;
            }
            
            .account-card {
                flex-direction: column;
                align-items: stretch;
                padding: 14px;
            }
            
            .account-info {
                margin-bottom: 10px;
            }
            
            .account-info h4 {
                font-size: 15px;
            }
            
            .account-info p {
                font-size: 12px;
            }
            
            .account-actions {
                justify-content: stretch;
            }
            
            .btn-delete {
                width: 100%;
                padding: 10px 12px;
            }
            
            .add-account-form {
                padding: 16px;
            }
            
            .add-account-form h3 {
                font-size: 16px;
                margin-top: 0;
            }
            
            .add-account-form input,
            .add-account-form select {
                padding: 12px;
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .add-account-form button {
                padding: 12px 20px;
                font-size: 16px;
            }
        }

        /* Small Mobile Phones */
        @media (max-width: 480px) {
            .bank-accounts {
                margin: 8px auto;
                padding: 8px;
            }
            
            .bank-accounts h2 {
                font-size: 20px;
            }
            
            .account-card {
                padding: 12px;
            }
            
            .add-account-form {
                padding: 12px;
            }
        }

        /* Tablet and Medium Screens */
        @media (min-width: 769px) and (max-width: 1024px) {
            .bank-accounts {
                max-width: 750px;
                padding: 24px;
            }
        }

        /* Large Laptops and Desktops */
        @media (min-width: 1025px) {
            .bank-accounts {
                max-width: 980px;
            }
            
            .add-account-form button {
                width: auto;
                min-width: 150px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <h2>MyWallet</h2>
        <div style="display:flex;align-items:center;gap:12px;color:#fff">
            <span style="opacity:.9">Signed in as <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <a class="logout" href="logout.php">Log out</a>
        </div>
    </header>

    <main class="container">
        <div class="bank-accounts">
            <h2>Bank Accounts</h2>
            <p style="color:var(--muted); margin-bottom:24px;">Add your bank accounts to withdraw money from your wallet.</p>

            <div class="add-account-form">
                <h3>Add New Bank Account</h3>
                <form id="addAccountForm" onsubmit="return addBankAccount(event);">
                    <select id="countrySelect" required style="padding:10px;margin-bottom:10px;border:1px solid #eee;border-radius:8px;width:100%;cursor:pointer;">
                        <option value="">Select Country</option>
                        <option value="Australia">Australia</option>
                        <option value="Canada">Canada</option>
                        <option value="France">France</option>
                        <option value="Germany">Germany</option>
                        <option value="India">India</option>
                        <option value="Japan">Japan</option>
                        <option value="Korea">South Korea</option>
                        <option value="Mexico">Mexico</option>
                        <option value="Nigeria">Nigeria</option>
                        <option value="Taiwan">Taiwan</option>
                        <option value="UK">United Kingdom</option>
                        <option value="USA">United States</option>
                    </select>
                    <select id="bankSelect" required style="padding:10px;margin-bottom:10px;border:1px solid #eee;border-radius:8px;width:100%;cursor:pointer;">
                        <option value="">Select Bank</option>
                    </select>
                    <div style="position:relative">
                        <input type="text" id="accountNumber" placeholder="Account Number" required>
                        <span id="accountLoader" style="display:none;position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#0066d6;">
                            <svg width="16" height="16" viewBox="0 0 50 50" style="animation:spin 1s linear infinite">
                                <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="80" stroke-dashoffset="60"/>
                            </svg>
                        </span>
                    </div>
                    <div id="autoFillNotice" style="display:none;background:#e8f5e9;padding:10px;border-radius:8px;margin:8px 0;font-size:13px;color:#1b5e20;">
                        ✓ Account details auto-detected
                    </div>
                    <input type="text" id="accountHolder" placeholder="Account Holder Name" required>
                    <button type="submit">Add Account</button>
                    
                    <style>
                        @keyframes spin {
                            to { transform: rotate(360deg); }
                        }
                    </style>
                </form>
            </div>

            <h3>Your Bank Accounts</h3>
            <div id="accountsList" style="margin-top:12px;"></div>
        </div>
    </main>

    <script>
        let csrf = null;
        let banksList = {};
        let autoFillTimeout = null;

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
        
        // Mock database of account numbers for demo purposes
        const mockAccountDatabase = {
            '1234567890': { bank: 'Wells Fargo', holder: 'John Smith', country: 'USA' },
            '9876543210': { bank: 'Bank of America', holder: 'Sarah Johnson', country: 'USA' },
            '4567891234': { bank: 'Chase Bank', holder: 'Michael Brown', country: 'USA' },
            '7891234567': { bank: 'Citibank', holder: 'Emily Davis', country: 'USA' },
            '3216549870': { bank: 'US Bank', holder: 'David Wilson', country: 'USA' },
            '1112223334': { bank: 'PNC Bank', holder: 'Jennifer Martinez', country: 'USA' },
            '5556667778': { bank: 'Capital One', holder: 'Robert Anderson', country: 'USA' },
            '9998887776': { bank: 'TD Bank', holder: 'Lisa Thomas', country: 'USA' },
            '4326178398': { bank: 'Regions Bank', holder: 'Chi Chi', country: 'USA' },
            '8523697410': { bank: 'Shinhan Bank', holder: 'Kim Min-ji', country: 'Korea' },
            '7412589630': { bank: 'KB Kookmin Bank', holder: 'Lee Sung-ho', country: 'Korea' },
            '9517538460': { bank: 'E.SUN Bank', holder: 'Chen Wei', country: 'Taiwan' },
            '3692581470': { bank: 'BBVA México', holder: 'Carlos Garcia', country: 'Mexico' }
        };

        // Auto-detect account details when user types account number
        document.addEventListener('DOMContentLoaded', function() {
            const accountNumberInput = document.getElementById('accountNumber');
            const accountHolderInput = document.getElementById('accountHolder');
            const bankSelect = document.getElementById('bankSelect');
            const countrySelect = document.getElementById('countrySelect');
            const loader = document.getElementById('accountLoader');
            const notice = document.getElementById('autoFillNotice');

            // Function to verify account
            function verifyAccount() {
                const accountNumber = accountNumberInput.value.trim();
                const selectedCountry = countrySelect.value;
                const selectedBank = bankSelect.value;
                
                // Clear previous timeout
                if (autoFillTimeout) clearTimeout(autoFillTimeout);
                
                // Hide notice and reset if input is too short
                if (accountNumber.length < 8) {
                    loader.style.display = 'none';
                    notice.style.display = 'none';
                    return;
                }
                
                // Need both country and bank to verify
                if (!selectedCountry || !selectedBank) {
                    loader.style.display = 'none';
                    notice.style.display = 'none';
                    return;
                }
                
                // Show loading indicator
                loader.style.display = 'block';
                notice.style.display = 'none';
                
                // Call API to verify account
                autoFillTimeout = setTimeout(() => {
                    fetch(`api/verify_account.php?country=${encodeURIComponent(selectedCountry)}&bank=${encodeURIComponent(selectedBank)}&account_number=${encodeURIComponent(accountNumber)}`)
                        .then(response => response.json())
                        .then(data => {
                            loader.style.display = 'none';
                            
                            if (data.success && data.account_holder) {
                                // Auto-fill account holder
                                accountHolderInput.value = data.account_holder;
                                
                                // Show appropriate notice
                                if (data.verified) {
                                    notice.textContent = '✓ Account verified successfully';
                                    notice.style.color = '#2e7d32';
                                } else {
                                    notice.textContent = 'ℹ Account not verified. Please check the name.';
                                    notice.style.color = '#f57c00';
                                }
                                notice.style.display = 'block';
                                
                                // Add subtle animation
                                accountHolderInput.style.backgroundColor = data.verified ? '#e8f5e9' : '#fff3e0';
                                setTimeout(() => {
                                    accountHolderInput.style.transition = 'background-color 0.5s ease';
                                    accountHolderInput.style.backgroundColor = '';
                                }, 1000);
                            } else {
                                // Error handling
                                console.error('Account verification failed:', data.error || 'Unknown error');
                            }
                        })
                        .catch(error => {
                            loader.style.display = 'none';
                            console.error('Account verification error:', error);
                            // Don't show error to user, just fail silently
                        });
                }, 500); // Debounce delay
            }

            // Trigger verification on account number input
            accountNumberInput.addEventListener('input', verifyAccount);
            
            // Also trigger when country or bank changes (if account number already entered)
            countrySelect.addEventListener('change', verifyAccount);
            bankSelect.addEventListener('change', verifyAccount);
        });

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
                        list.innerHTML = '<p style="color:var(--muted); text-align:center;">No bank accounts added yet.</p>';
                        return;
                    }
                    list.innerHTML = data.accounts.map(acc => `
                        <div class="account-card">
                            <div class="account-info">
                                <h4>${escapeHtml(acc.bank_name)}</h4>
                                <p class="account-number">****${escapeHtml(acc.account_number.slice(-4))}</p>
                                <p>Holder: ${escapeHtml(acc.account_holder)}</p>
                            </div>
                            <div class="account-actions">
                                <button class="btn-delete" onclick="deleteAccount('${escapeHtml(acc.id)}');">Delete</button>
                            </div>
                        </div>
                    `).join('');
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
                document.getElementById('autoFillNotice').style.display = 'none';
                loadAccounts();
                alert('Bank account added successfully!');
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
                alert('Bank account deleted');
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
