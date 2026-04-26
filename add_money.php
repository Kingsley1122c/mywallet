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

$msgError = '';
$msgSuccess = '';

// Handle adding money to user account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) { 
        $msgError = 'Invalid request.'; 
    } else {
        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

        if (!$userId) { 
            $msgError = 'Missing user ID.'; 
        } elseif ($amount <= 0) { 
            $msgError = 'Amount must be greater than 0.'; 
        } else {
            // Find user
            $userIdx = null;
            foreach ($users as $i => $u) { if ($u['id'] == $userId) { $userIdx = $i; break; } }
            
            if ($userIdx === null) { 
                $msgError = 'User not found.'; 
            } else {
                // Add money to user balance
                $users[$userIdx]['balance'] = ($users[$userIdx]['balance'] ?? 0) + $amount;
                file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
                
                // Create transaction record
                $txFile = __DIR__ . '/transactions.json';
                $transactions = file_exists($txFile) ? json_decode(file_get_contents($txFile), true) : [];
                $tx = [
                    'id' => uniqid(),
                    'user_id' => $userId,
                    'time' => time() * 1000,
                    'desc' => 'Money added by admin',
                    'amount' => round($amount, 2)
                ];
                $transactions[] = $tx;
                file_put_contents($txFile, json_encode($transactions, JSON_PRETTY_PRINT), LOCK_EX);
                
                // Audit log
                include_once __DIR__ . '/audit.php';
                write_audit('admin_add_money', $userId, $users[$userIdx]['email'], $currentUser['email'], ['amount' => $amount, 'added_by_admin' => $currentUser['email']]);
                
                $msgSuccess = "Added $" . number_format($amount, 2) . " to " . htmlspecialchars($users[$userIdx]['email']);
                // Refresh users array
                $users = json_decode(file_get_contents($usersFile), true) ?: [];
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Add Money to User — Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { 
            max-width: 900px; 
            margin: 30px auto; 
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .admin-container h3 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 20px;
        }
        
        .form-group { 
            margin-bottom: 16px;
        }
        .form-group label { 
            display: block; 
            margin-bottom: 6px; 
            font-weight: 600;
            color: #374151;
        }
        .form-group input, .form-group select { 
            width: 100%; 
            padding: 10px; 
            border-radius: 6px; 
            border: 1px solid #ddd; 
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus { 
            outline: none; 
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
        }
        .btn-submit { 
            background: #0066cc; 
            color: #fff; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit:hover { 
            background: #0052a3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,102,204,0.3);
        }
        .danger { 
            background: #b00020; 
            color: #fff; 
            border: none; 
            padding: 6px 10px; 
            border-radius: 6px; 
            cursor: pointer;
        }
        .user-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        .user-table th, .user-table td { 
            padding: 10px; 
            border-bottom: 1px solid #eee; 
            text-align: left;
        }
        .user-table th { 
            background: #f5f5f5; 
            font-weight: 600;
        }
        .user-balance { 
            font-weight: 600; 
            color: #0066cc;
        }
        .nav-links { 
            margin-bottom: 20px;
        }
        .nav-links a { 
            margin-right: 12px; 
            color: #0066cc; 
            text-decoration: none;
            font-weight: 600;
        }
        .nav-links a:hover { 
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                margin: 20px 10px;
                padding: 16px;
            }
            
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .user-table {
                min-width: 500px;
            }
            
            .nav-links a {
                display: block;
                margin: 8px 0;
            }
        }
        
        @media (max-width: 480px) {
            .admin-container {
                margin: 10px 5px;
                padding: 12px;
            }
            
            .admin-container h3 {
                font-size: 20px;
                font-weight: 900;
            }
            
            .form-group label {
                font-size: 15px;
                font-weight: 700;
            }
            
            .btn-submit {
                width: 100%;
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div style="display:flex;justify-content:space-between;align-items:center;width:100%;max-width:1400px;margin:0 auto">
            <h2 style="margin:0">💰 Add Money to User</h2>
            <div style="display:flex;align-items:center;gap:16px">
                <div style="color:#fff;display:flex;align-items:center;gap:12px;background:rgba(255,255,255,0.15);padding:8px 16px;border-radius:8px">
                    <span style="font-size:20px">👤</span>
                    <div>
                        <div style="font-size:11px;opacity:0.8">Signed in as</div>
                        <div style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                    </div>
                </div>
                <a class="logout" href="logout.php" style="background:#fff;color:#667eea;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600;transition:all 0.3s ease">Log out</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="admin-container">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
                <a href="admin.php" style="background:#667eea;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600">← Back to Admin</a>
                <a href="dashboard.php" style="background:#f3f4f6;color:#374151;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600">🏠 Dashboard</a>
            </div>
            
            <!-- Quick Navigation -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px">
                <a href="pending_transfers.php" style="background:linear-gradient(135deg,#ffa500 0%,#ff8c00 100%);color:#fff;padding:16px;border-radius:10px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:transform 0.3s ease">
                    <span style="font-size:24px">⏳</span>
                    <span style="font-weight:600">Pending Transfers</span>
                </a>
                <a href="transactions.php" style="background:linear-gradient(135deg,#0a8a00 0%,#087000 100%);color:#fff;padding:16px;border-radius:10px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:transform 0.3s ease">
                    <span style="font-size:24px">📊</span>
                    <span style="font-weight:600">Transactions</span>
                </a>
            </div>

            <h3>Add Money to User Account</h3>
            
            <?php if ($msgError): ?><p style="color:#b00020"><?php echo htmlspecialchars($msgError); ?></p><?php endif; ?>
            <?php if ($msgSuccess): ?><p style="color:#0a8a00"><?php echo htmlspecialchars($msgSuccess); ?></p><?php endif; ?>

            <form method="post" action="add_money.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="form-group">
                    <label for="user_id">Select User</label>
                    <select name="user_id" id="user_id" required>
                        <option value="">-- Select a user --</option>
                        <?php foreach ($users as $u): ?>
                            <?php if (($u['role'] ?? '') !== 'admin'): ?>
                                <option value="<?php echo $u['id']; ?>">
                                    <?php echo htmlspecialchars($u['email']); ?> (<?php echo htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['surname'] ?? '')); ?>) - Balance: $<?php echo number_format($u['balance'] ?? 0, 2); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Amount (USD)</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01" required placeholder="Enter amount">
                </div>

                <button type="submit" class="btn-submit">Add Money</button>
            </form>

            <hr style="margin:30px 0">

            <h3>User Balances</h3>
            <div class="table-wrapper">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Current Balance</th>
                            <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['surname'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td class="user-balance">$<?php echo number_format($u['balance'] ?? 0, 2); ?></td>
                            <td><?php echo htmlspecialchars($u['role'] ?? 'user'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>
    </main>
</body>
</html>
