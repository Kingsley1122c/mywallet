<?php
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$usersFile = __DIR__ . '/users.json';
$txFile = __DIR__ . '/transactions.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
include_once __DIR__ . '/withdrawal_status.php';
$txs = syncWithdrawalTransactions($txFile, $usersFile);

$meId = (int) $_SESSION['user_id'];
$me = null;
foreach ($users as $u) { if ($u['id'] == $meId) { $me = $u; break; } }

$isAdmin = ($me['role'] ?? '') === 'admin';
$viewUser = $isAdmin && !empty($_GET['user_id']) ? (int) $_GET['user_id'] : $meId;

$list = array_values(array_filter($txs, fn($t) => $t['user_id'] == $viewUser));
usort($list, fn($a,$b)=> $b['time'] <=> $a['time']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Transactions | Mivonta</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .transactions-table {
            max-width: 980px;
            margin: 28px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .transactions-table h3 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 20px;
        }
        
        .transactions-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .transactions-table th,
        .transactions-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .transactions-table th {
            background: #f9fafb;
            font-weight: 700;
            color: #374151;
        }
        
        .transactions-table tbody tr:hover {
            background: #f9fafb;
        }
        
        @media (max-width: 768px) {
            .transactions-table {
                margin: 20px 10px;
                padding: 16px;
            }
            
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .transactions-table table {
                min-width: 500px;
            }
        }
        
        @media (max-width: 480px) {
            .transactions-table {
                margin: 10px 5px;
                padding: 12px;
            }
            
            .transactions-table h3 {
                font-size: 20px;
                font-weight: 900;
            }
            
            .transactions-table th,
            .transactions-table td {
                padding: 8px 6px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div style="display:flex;justify-content:space-between;align-items:center;width:100%;max-width:1400px;margin:0 auto">
            <h2 style="margin:0">📊 Transactions</h2>
            <div style="display:flex;align-items:center;gap:16px">
                <div style="color:#fff;display:flex;align-items:center;gap:12px;background:rgba(255,255,255,0.15);padding:8px 16px;border-radius:8px">
                    <span style="font-size:20px">👤</span>
                    <div>
                        <div style="font-size:11px;opacity:0.8">Signed in as</div>
                        <div style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                    </div>
                </div>
                <a class="logout" href="logout.php" style="background:#fff;color:#667eea;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600;transition:all 0.3s ease">Log out</a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="transactions-table">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
                <a href="dashboard.php" style="background:#667eea;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600">← Back to Dashboard</a>
                <?php if ($isAdmin): ?>
                    <div style="display:flex;gap:12px">
                        <a href="admin.php" style="background:#f3f4f6;color:#374151;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600">⚙️ Admin</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($isAdmin): ?>
            <!-- Quick Navigation for Admin -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px">
                <a href="pending_transfers.php" style="background:linear-gradient(135deg,#ffa500 0%,#ff8c00 100%);color:#fff;padding:16px;border-radius:10px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:transform 0.3s ease">
                    <span style="font-size:24px">⏳</span>
                    <span style="font-weight:600">Pending Transfers</span>
                </a>
                <a href="add_money.php" style="background:linear-gradient(135deg,#0066cc 0%,#0052a3 100%);color:#fff;padding:16px;border-radius:10px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:transform 0.3s ease">
                    <span style="font-size:24px">💰</span>
                    <span style="font-weight:600">Add Money</span>
                </a>
            </div>
            <?php endif; ?>
            <h3>Transactions for <?php echo htmlspecialchars($viewUser === $meId ? ($_SESSION['email']) : "user #$viewUser"); ?></h3>
            <?php if ($isAdmin): ?>
                <form method="get" style="margin-bottom:12px">
                    <label class="muted-small">View user ID: <input name="user_id" style="width:80px;padding:6px;border-radius:6px;border:1px solid #eef" value="<?php echo htmlspecialchars($_GET['user_id'] ?? '')?>"></label>
                    <button class="btn" type="submit">View</button>
                </form>
            <?php endif; ?>

            <?php if (empty($list)): ?>
                <div class="empty">No transactions found.</div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Date</th><th>Description</th><th>Amount</th></tr></thead>
                        <tbody>
                    <?php foreach ($list as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', intval($t['time']/1000))); ?></td>
                            <td><?php echo htmlspecialchars($t['desc']); ?></td>
                            <td><?php echo ($t['amount']>0?'+':'').number_format($t['amount'],2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
