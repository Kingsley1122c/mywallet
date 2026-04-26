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

$pendingTransfersFile = __DIR__ . '/pending_transfers.json';
$pendingTransfers = file_exists($pendingTransfersFile) ? json_decode(file_get_contents($pendingTransfersFile), true) : [];

// Filter only pending transfers
$pendingOnly = array_filter($pendingTransfers, fn($t) => $t['status'] === 'pending');
$pendingOnly = array_values($pendingOnly);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pending Transfers — Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .transfers-container { 
            max-width: 1000px; 
            margin: 30px auto; 
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .transfer-card { 
            background: #f9f9f9; 
            border-left: 4px solid #ffa500; 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 12px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .transfer-info h4 { 
            margin: 0 0 8px 0;
            font-weight: 700;
        }
        .transfer-details { 
            color: #666; 
            font-size: 14px; 
            margin: 4px 0;
        }
        .amount { 
            font-weight: 600; 
            color: #0066cc; 
            font-size: 16px;
        }
        .transfer-actions { 
            display: flex; 
            gap: 8px;
        }
        .btn-approve { 
            background: #0a8a00; 
            color: #fff; 
            border: none; 
            padding: 8px 16px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-approve:hover { 
            background: #077200;
            transform: translateY(-2px);
        }
        .btn-reject { 
            background: #b00020; 
            color: #fff; 
            border: none; 
            padding: 8px 16px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-reject:hover { 
            background: #8b0016;
            transform: translateY(-2px);
        }
        .empty-state { 
            text-align: center; 
            padding: 40px 20px; 
            color: #999;
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
        .status-badge { 
            display: inline-block; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 12px; 
            font-weight: 600;
        }
        .status-approved { 
            background: #d4edda; 
            color: #155724;
        }
        .status-rejected { 
            background: #f8d7da; 
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .transfers-container {
                margin: 20px 10px;
                padding: 16px;
            }
            
            .transfer-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .transfer-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .nav-links a {
                display: block;
                margin: 8px 0;
            }
        }
        
        @media (max-width: 480px) {
            .transfers-container {
                margin: 10px 5px;
                padding: 12px;
            }
            
            .transfers-container h3 {
                font-size: 20px;
                font-weight: 900;
            }
            
            .transfer-info h4 {
                font-size: 16px;
                font-weight: 800;
            }
            
            .transfer-details {
                font-size: 13px;
            }
            
            .transfer-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-approve,
            .btn-reject {
                width: 100%;
                padding: 10px 16px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div style="display:flex;justify-content:space-between;align-items:center;width:100%;max-width:1400px;margin:0 auto">
            <h2 style="margin:0">⏳ Pending Transfers</h2>
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
        <section class="transfers-container">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
                <a href="admin.php" style="background:#667eea;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600">← Back to Admin</a>
                <a href="dashboard.php" style="background:#f3f4f6;color:#374151;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600">🏠 Dashboard</a>
            </div>
            
            <!-- Quick Navigation -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px">
                <a href="add_money.php" style="background:linear-gradient(135deg,#0066cc 0%,#0052a3 100%);color:#fff;padding:16px;border-radius:10px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:transform 0.3s ease">
                    <span style="font-size:24px">💰</span>
                    <span style="font-weight:600">Add Money</span>
                </a>
                <a href="transactions.php" style="background:linear-gradient(135deg,#0a8a00 0%,#087000 100%);color:#fff;padding:16px;border-radius:10px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:transform 0.3s ease">
                    <span style="font-size:24px">📊</span>
                    <span style="font-weight:600">Transactions</span>
                </a>
            </div>

            <h3>Pending Money Transfers</h3>
            
            <?php if (empty($pendingOnly)): ?>
                <div class="empty-state">
                    <p>No pending transfers at the moment.</p>
                </div>
            <?php else: ?>
                <div id="transfers-list">
                    <?php foreach ($pendingOnly as $transfer): ?>
                        <div class="transfer-card" data-id="<?php echo htmlspecialchars($transfer['id']); ?>">
                            <div class="transfer-info">
                                <h4><?php echo htmlspecialchars($transfer['from_email']); ?> → <?php echo htmlspecialchars($transfer['to_email']); ?></h4>
                                <div class="amount">$<?php echo number_format($transfer['amount'], 2); ?></div>
                                <div class="transfer-details">
                                    Requested: <?php echo date('M j, Y g:i A', floor($transfer['created_at'] / 1000)); ?>
                                </div>
                            </div>
                            <div class="transfer-actions">
                                <button class="btn-approve" onclick="approveTransfer('<?php echo htmlspecialchars($transfer['id']); ?>')">Accept</button>
                                <button class="btn-reject" onclick="rejectTransfer('<?php echo htmlspecialchars($transfer['id']); ?>')">Reject</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <hr style="margin:30px 0">
            
            <h3>Recent Transfer History</h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:2px solid #eee;">
                        <th style="text-align:left; padding:10px;">From</th>
                        <th style="text-align:left; padding:10px;">To</th>
                        <th style="text-align:left; padding:10px;">Amount</th>
                        <th style="text-align:left; padding:10px;">Status</th>
                        <th style="text-align:left; padding:10px;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $allTransfers = array_slice($pendingTransfers, -20);
                    $allTransfers = array_reverse($allTransfers);
                    foreach ($allTransfers as $t): 
                    ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:10px;"><?php echo htmlspecialchars(substr($t['from_email'], 0, 30)); ?></td>
                            <td style="padding:10px;"><?php echo htmlspecialchars(substr($t['to_email'], 0, 30)); ?></td>
                            <td style="padding:10px;">$<?php echo number_format($t['amount'], 2); ?></td>
                            <td style="padding:10px;">
                                <?php if ($t['status'] === 'pending'): ?>
                                    <span style="color:#ffa500; font-weight:600;">⏳ Pending</span>
                                <?php elseif ($t['status'] === 'approved'): ?>
                                    <span class="status-badge status-approved">✓ Approved</span>
                                <?php else: ?>
                                    <span class="status-badge status-rejected">✗ Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:10px; font-size:13px; color:#666;"><?php echo date('M j, Y', floor($t['created_at'] / 1000)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        let csrf = null;
        
        // Get CSRF token
        fetch('get_csrf.php', {credentials: 'same-origin'})
            .then(r => r.json())
            .then(j => { csrf = j.csrf; });

        function approveTransfer(transferId) {
            if (!confirm('Are you sure you want to approve this transfer?')) return;
            
            fetch('api/pending_transfers.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'approve', transfer_id: transferId, csrf_token: csrf})
            })
            .then(r => r.json())
            .then(j => {
                if (j.error) return alert('Error: ' + j.error);
                alert('Transfer approved successfully!');
                location.reload();
            })
            .catch(() => alert('Network error'));
        }

        function rejectTransfer(transferId) {
            if (!confirm('Are you sure you want to reject this transfer?')) return;
            
            fetch('api/pending_transfers.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'reject', transfer_id: transferId, csrf_token: csrf})
            })
            .then(r => r.json())
            .then(j => {
                if (j.error) return alert('Error: ' + j.error);
                alert('Transfer rejected!');
                location.reload();
            })
            .catch(() => alert('Network error'));
        }
    </script>
</body>
</html>
