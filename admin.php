<?php
session_start();

require_once __DIR__ . '/users_bootstrap.php';

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

// If no admin exists, create default admin (first-visit repair)
$hasAdmin = false;
foreach ($users as $u) { if (($u['role'] ?? '') === 'admin') { $hasAdmin = true; break; } }
if (!$hasAdmin) {
    repairUsersStore($usersFile);
    ensureAdminLogin($usersFile);
    // refresh local users
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
}

// CSRF
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

// Handle actions: delete, toggle_role
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    // Accept both 'id' and 'user_id' for compatibility with message form
    if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
    } elseif (isset($_POST['user_id'])) {
        $id = (int)$_POST['user_id'];
    } else {
        $id = 0;
    }
    // find index
    $idx = null; foreach ($users as $i => $u) { if ($u['id'] == $id) { $idx = $i; break; } }
    if ($idx === null) { $_SESSION['admin_error'] = 'User not found.'; header('Location: admin.php'); exit(); }
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) { $_SESSION['admin_error'] = 'Invalid request.'; header('Location: admin.php'); exit(); }

    // Handle user deactivation
    if ($action === 'deactivate_user') {
        $deactivateMsg = trim($_POST['deactivate_message'] ?? '');
        if (!$deactivateMsg) {
            $_SESSION['admin_error'] = 'Deactivation message required.';
            header('Location: admin.php'); exit();
        }
        $users[$idx]['deactivated'] = true;
        $users[$idx]['deactivate_message'] = $deactivateMsg;
        $users[$idx]['activation_code'] = null;
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
        $_SESSION['admin_success'] = 'User deactivated.';
        header('Location: admin.php'); exit();
    }

    // Handle user activation (code generation)
    if ($action === 'activate_user') {
        $activationCode = trim($_POST['activation_code'] ?? '');
        if (!preg_match('/^\d{12}$/', $activationCode)) {
            $_SESSION['admin_error'] = 'A valid 12-digit activation code is required.';
            header('Location: admin.php'); exit();
        }
        $users[$idx]['activation_code'] = $activationCode;
        $users[$idx]['deactivated'] = false;
        $users[$idx]['deactivate_message'] = null;
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
        $_SESSION['admin_success'] = 'Activation code set. User can now activate their account.';
        header('Location: admin.php'); exit();
    }

    // Handle admin sending a message to a user
    if (isset($_POST['message']) && $id) {
        require_once __DIR__ . '/api/email_notifications.php';
        $messageFile = __DIR__ . '/admin_messages.json';
        $messages = file_exists($messageFile) ? json_decode(file_get_contents($messageFile), true) : [];
        // Generate unique id
        $newId = 1;
        if (!empty($messages)) {
            $ids = array_column($messages, 'id');
            $newId = max($ids) + 1;
        }
        $messageRecord = [
            'id' => $newId,
            'user_id' => $id,
            'to_email' => $_POST['email'] ?? '',
            'from_admin_id' => $currentUser['id'],
            'from_admin_email' => $currentUser['email'],
            'message_type' => $_POST['message_type'] ?? 'info',
            'message' => trim($_POST['message']),
            'timestamp' => time(),
            'read' => false
        ];
        $messages[] = $messageRecord;
        file_put_contents($messageFile, json_encode($messages, JSON_PRETTY_PRINT), LOCK_EX);
        $emailSent = false;
        if (!empty($messageRecord['to_email'])) {
            $emailSent = sendAdminMessageEmail($messageRecord['to_email'], $messageRecord);
        }
        $_SESSION['admin_success'] = $emailSent
            ? 'Message sent successfully and email notification delivered.'
            : 'Message saved successfully. Email notification could not be sent.';
        header('Location: admin.php'); exit();
    }

    if (!$id) { $_SESSION['admin_error'] = 'Missing id.'; header('Location: admin.php'); exit(); }

    // prevent deleting self
    if ($action === 'delete') {
        if ($users[$idx]['id'] == $currentUser['id']) { $_SESSION['admin_error'] = 'You cannot delete your own account.'; header('Location: admin.php'); exit(); }
        // ensure at least one admin remains
        $adminCount = 0; foreach ($users as $u) { if (($u['role'] ?? '') === 'admin') $adminCount++; }
        if (($users[$idx]['role'] ?? '') === 'admin' && $adminCount <= 1) { $_SESSION['admin_error'] = 'Cannot delete the last admin.'; header('Location: admin.php'); exit(); }

        array_splice($users, $idx, 1);
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
        $_SESSION['admin_success'] = 'User deleted.';
        header('Location: admin.php'); exit();
    }

    if ($action === 'toggle_role') {
        $newRole = ($users[$idx]['role'] ?? 'user') === 'admin' ? 'user' : 'admin';
        // prevent demoting last admin
        if ($newRole === 'user') {
            $adminCount = 0; foreach ($users as $u) { if (($u['role'] ?? '') === 'admin') $adminCount++; }
            if ($users[$idx]['role'] === 'admin' && $adminCount <= 1) { $_SESSION['admin_error'] = 'Cannot demote the last admin.'; header('Location: admin.php'); exit(); }
        }

        $users[$idx]['role'] = $newRole;
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
        $_SESSION['admin_success'] = 'User role updated.';
        header('Location: admin.php'); exit();
    }

    if ($action === 'reset_password') {
        // set a temporary random password and show it to admin
        $temp = substr(bin2hex(random_bytes(5)), 0, 10);
        $users[$idx]['password'] = password_hash($temp, PASSWORD_DEFAULT);
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
        $_SESSION['admin_success'] = 'Password reset. Temporary password: ' . $temp;
        // Audit: admin reset password (do not store plaintext temp password in audit)
        include_once __DIR__ . '/audit.php';
        write_audit('password_reset_by_admin', $users[$idx]['id'], $users[$idx]['email'], $currentUser['email'], ['note' => 'temporary password generated']);
        header('Location: admin.php'); exit();
    }
}

$msgError = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_error']);
$msgSuccess = $_SESSION['admin_success'] ?? '';
unset($_SESSION['admin_success']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Dashboard | Mivonta</title>
    <link rel="stylesheet" href="style.css">
    <style>
    .modern-modal-card {
        border: 3px solid #6366f1;
        background: linear-gradient(135deg, #f0f4ff 0%, #fff 100%);
    }
    .modern-modal-title {
        color: #6366f1;
        text-shadow: 0 2px 8px #e0e7ff;
    }
    .modern-user-details {
        background: linear-gradient(90deg, #e0e7ff 0%, #f8fafc 100%);
        border-radius: 12px;
        padding: 10px 0 2px 0;
        box-shadow: 0 2px 12px rgba(99,102,241,0.07);
    }
    .modern-user-details div {
        border-left: 4px solid #6366f1;
        background: #fff;
        margin-bottom: 2px;
        transition: background 0.2s;
    }
    .modern-user-details div:hover {
        background: #f0f4ff;
    }
    .modern-user-details strong {
        color: #2563eb;
    }
        /* Modern Admin Topbar */
        .admin-topbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 18px 32px;
            box-shadow: 0 4px 24px rgba(102, 126, 234, 0.35);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .admin-topbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        
        .admin-topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            min-width: 0;
        }
        
        .admin-topbar-logo {
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
        
        .admin-topbar-brand {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        
        .admin-topbar-brand h2 {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            margin: 0;
            letter-spacing: -0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .admin-topbar-brand small {
            font-size: 12px;
            color: rgba(255,255,255,0.88);
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .admin-topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        
        .admin-user-info {
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
        
        .admin-user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            color: #fff;
            box-shadow: 0 4px 12px rgba(251,191,36,0.4);
        }
        
        .admin-user-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .admin-user-details small {
            font-size: 10px;
            opacity: 0.85;
            font-weight: 500;
        }
        
        .admin-user-details strong {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }
        
        .admin-topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-topbar-btn {
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
        
        .admin-topbar-btn:hover {
            background: rgba(255,255,255,0.4);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
            border-color: rgba(255,255,255,0.35);
        }
        
        .admin-topbar-btn.dashboard {
            background: rgba(255,255,255,0.95);
            color: #667eea;
            border-color: rgba(255,255,255,0.4);
            box-shadow: 0 4px 12px rgba(102,126,234,0.2);
        }
        
        .admin-topbar-btn.dashboard:hover {
            background: #fff;
            color: #764ba2;
            box-shadow: 0 6px 18px rgba(102,126,234,0.3);
        }
        
        .admin-topbar-btn.logout {
            background: rgba(239,68,68,0.25);
            border-color: rgba(239,68,68,0.3);
            box-shadow: 0 4px 12px rgba(239,68,68,0.2);
        }
        
        .admin-topbar-btn.logout:hover {
            background: rgba(239,68,68,0.35);
            box-shadow: 0 6px 18px rgba(239,68,68,0.3);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .admin-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        
        .stat-card.orange {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            box-shadow: 0 10px 30px rgba(245, 87, 108, 0.3);
        }
        
        .stat-card.green {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
        }
        
        .stat-icon {
            font-size: 36px;
            margin-bottom: 12px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            margin: 8px 0;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .users-section { 
            background: #fff;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .users-section h3 {
            font-size: 24px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse;
            font-size: 14px;
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        th { 
            padding: 16px 12px; 
            text-align: left;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 16px 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        tbody tr {
            transition: background 0.2s ease;
        }
        
        tbody tr:hover {
            background: #f9fafb;
        }
        
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .actions form { 
            display: inline-block;
            margin: 0;
        }
        
        .actions button {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        .actions button[type="submit"]:not(.danger) {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        
        .actions button[type="submit"]:not(.danger):hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .danger { 
            background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            color: #fff;
        }
        
        .danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(238, 9, 121, 0.4);
        }
        
        .user-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .user-badge.admin-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #fff;
        }
        
        .user-badge.user-badge-default {
            background: #e5e7eb;
            color: #6b7280;
        }
        
        .muted { 
            color: #9ca3af;
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
        
        .logout-modal-text {
            font-size: 16px;
            text-align: center;
            color: #6b7280;
            margin: 0 0 32px 0;
            line-height: 1.6;
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
        
        /* Mobile responsive styles */
        @media (max-width: 1024px) {
            .admin-topbar {
                padding: 14px 24px;
            }
            .admin-topbar-logo {
                width: 40px;
                height: 40px;
                font-size: 14px;
            }
            .admin-topbar-brand h2 {
                font-size: 18px;
            }
            .admin-topbar-brand small {
                font-size: 11px;
            }
            .admin-topbar-btn {
                padding: 10px 18px;
                font-size: 13px;
            }
            .admin-user-avatar {
                width: 34px;
                height: 34px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 768px) {
            .admin-topbar {
                padding: 12px 16px;
            }
            .admin-topbar-content {
                gap: 12px;
            }
            .admin-topbar-left {
                gap: 12px;
                min-width: 0;
                flex: 1;
            }
            .admin-topbar-logo {
                width: 38px;
                height: 38px;
                font-size: 13px;
                border-radius: 12px;
            }
            .admin-topbar-brand h2 {
                font-size: 16px;
            }
            .admin-topbar-brand small {
                display: none;
            }
            .admin-user-info {
                padding: 6px 12px;
                gap: 8px;
            }
            .admin-user-avatar {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }
            .admin-user-details {
                display: none;
            }
            .admin-topbar-actions {
                gap: 6px;
            }
            .admin-topbar-btn {
                padding: 9px 14px;
                font-size: 13px;
            }
            
            .admin-wrapper {
                padding: 10px;
            }
            
            .users-section {
                padding: 20px 16px;
                border-radius: 12px;
            }
            
            .users-section h3 {
                font-size: 20px;
                font-weight: 900;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            table {
                font-size: 13px;
            }
            
            th, td {
                padding: 12px 8px;
            }
            
            /* Make table scrollable on mobile */
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                min-width: 700px;
            }
            
            header.topbar > div {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start !important;
            }
            
            header.topbar h2 {
                font-size: 18px;
            }
            
            header.topbar > div > div {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .admin-topbar-brand h2 {
                font-size: 14px;
            }
            .admin-topbar-right {
                gap: 8px;
            }
            .admin-topbar-actions {
                gap: 4px;
            }
            .admin-topbar-btn {
                padding: 7px 10px;
                font-size: 11px;
            }
            
            .users-section {
                padding: 16px 12px;
            }
            
            .users-section h3 {
                font-size: 18px;
                font-weight: 900;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-value {
                font-size: 28px;
            }
            
            table {
                font-size: 12px;
                min-width: 650px;
            }
            
            th, td {
                padding: 10px 6px;
            }
            
            .actions {
                flex-direction: column;
                width: 100%;
            }
            
            .actions form {
                width: 100%;
            }
            
            .actions button {
                width: 100%;
                padding: 10px 14px;
            }
            
            header.topbar > div > div:first-of-type {
                font-size: 12px;
            }
            
            header.topbar a.logout {
                width: 100%;
                text-align: center;
            }
        }
        
        /* Quick action cards hover effect */
        a[href*="pending_transfers"],
        a[href*="add_money"],
        a[href*="transactions"] {
            display: block;
        }
        
        a[href*="pending_transfers"]:hover,
        a[href*="add_money"]:hover,
        a[href*="transactions"]:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            a[href*="pending_transfers"],
            a[href*="add_money"],
            a[href*="transactions"] {
                padding: 20px;
            }
            
            header.topbar > div {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start !important;
            }
            
            header.topbar h2 {
                font-size: 18px;
            }
            
            header.topbar > div > div {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            header.topbar > div > div:first-of-type {
                font-size: 12px;
            }
            
            header.topbar a.logout {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header class="admin-topbar">
        <div class="admin-topbar-content">
            <div class="admin-topbar-left">
                <div class="admin-topbar-logo">👑</div>
                <div class="admin-topbar-brand">
                    <h2>Admin Dashboard</h2>
                    <small>💼 Mivonta Administration</small>
                </div>
            </div>
            <div class="admin-topbar-right">
                <div class="admin-user-info">
                    <div class="admin-user-avatar">👤</div>
                    <div class="admin-user-details">
                        <small>Signed in as</small>
                        <strong><?php echo htmlspecialchars(explode('@', $currentUser['email'])[0]); ?></strong>
                    </div>
                </div>
                <div class="admin-topbar-actions">
                    <a class="admin-topbar-btn dashboard" href="dashboard.php">
                        <span>🏠</span>
                        <span>Dashboard</span>
                    </a>
                    <a class="admin-topbar-btn logout" href="#" data-logout-trigger>
                        <span>🚪</span>
                        <span>Log Out</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="admin-wrapper">
            <div style="margin-bottom:32px">
                <h1 style="margin:0;font-size:32px;font-weight:800;color:#1f2937">System Overview</h1>
                <p style="margin:8px 0 0 0;color:#6b7280">Manage users and monitor system activity</p>
            </div>
            
            <?php if ($msgError): ?><p style="color:#991b1b;background:#fee2e2;padding:16px 20px;border-radius:12px;border-left:4px solid #dc2626;margin-bottom:24px;font-weight:600"><?php echo htmlspecialchars($msgError); ?></p><?php endif; ?>
            <?php if ($msgSuccess): ?><p style="color:#065f46;background:#d1fae5;padding:16px 20px;border-radius:12px;border-left:4px solid #10b981;margin-bottom:24px;font-weight:600"><?php echo htmlspecialchars($msgSuccess); ?></p><?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?php echo count($users); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-icon">👑</div>
                    <div class="stat-value"><?php echo count(array_filter($users, fn($u) => ($u['role'] ?? 'user') === 'admin')); ?></div>
                    <div class="stat-label">Admin Users</div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">💼</div>
                    <div class="stat-value"><?php echo count(array_filter($users, fn($u) => ($u['role'] ?? 'user') === 'user')); ?></div>
                    <div class="stat-label">Regular Users</div>
                </div>
            </div>
            
            <!-- Quick Actions Cards -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;margin-bottom:32px">
                <a href="pending_transfers.php" style="background:linear-gradient(135deg,#ffa500 0%,#ff8c00 100%);color:#fff;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 4px 12px rgba(255,165,0,0.3);transition:transform 0.3s ease">
                    <div style="font-size:32px;margin-bottom:8px">⏳</div>
                    <h4 style="margin:0 0 8px 0;font-size:18px;font-weight:700">Pending Transfers</h4>
                    <p style="margin:0;opacity:0.9;font-size:14px">Review and approve transfers</p>
                </a>
                
                <a href="withdrawal_codes_admin.php" style="background:linear-gradient(135deg,#dc2626 0%,#991b1b 100%);color:#fff;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 4px 12px rgba(220,38,38,0.3);transition:transform 0.3s ease">
                    <div style="font-size:32px;margin-bottom:8px">🔐</div>
                    <h4 style="margin:0 0 8px 0;font-size:18px;font-weight:700">Withdrawal Codes</h4>
                    <p style="margin:0;opacity:0.9;font-size:14px">Generate withdrawal authorization codes</p>
                </a>
                
                <a href="add_money.php" style="background:linear-gradient(135deg,#0066cc 0%,#0052a3 100%);color:#fff;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 4px 12px rgba(0,102,204,0.3);transition:transform 0.3s ease">
                    <div style="font-size:32px;margin-bottom:8px">💰</div>
                    <h4 style="margin:0 0 8px 0;font-size:18px;font-weight:700">Add Money</h4>
                    <p style="margin:0;opacity:0.9;font-size:14px">Add funds to user accounts</p>
                </a>
                
                <a href="transactions.php" style="background:linear-gradient(135deg,#0a8a00 0%,#087000 100%);color:#fff;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 4px 12px rgba(10,138,0,0.3);transition:transform 0.3s ease">
                    <div style="font-size:32px;margin-bottom:8px">📊</div>
                    <h4 style="margin:0 0 8px 0;font-size:18px;font-weight:700">View Transactions</h4>
                    <p style="margin:0;opacity:0.9;font-size:14px">See all transaction history</p>
                </a>
            </div>
            
            <section class="users-section">
                <h3 style="margin:0 0 24px 0;font-size:24px;font-weight:800;color:#1f2937">Manage Users</h3>
                
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th class="muted">Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['surname'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="user-badge <?php echo ($u['role'] ?? 'user') === 'admin' ? 'admin-badge' : 'user-badge-default'; ?>">
                                        <?php echo htmlspecialchars($u['role'] ?? 'user'); ?>
                                    </span>
                                </td>
                            <td class="actions">
                                <button type="button" onclick='showUserDetails(<?php echo json_encode($u); ?>)' style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; margin-bottom: 4px;">👁️ View Details</button>
                                <button type="button" onclick='showMessageModal(<?php echo $u['id']; ?>, <?php echo json_encode($u['email']); ?>)' style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; margin-bottom: 4px;">💌 Send Message</button>
                                <?php if ($u['id'] != $currentUser['id']): ?>
                                    <form method="post" action="admin.php" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                        <input type="hidden" name="action" value="toggle_role">
                                        <button type="submit"><?php echo ($u['role'] ?? '') === 'admin' ? 'Demote' : 'Promote'; ?></button>
                                    </form>
                                    <form method="post" action="admin.php" onsubmit="return confirm('Delete this user?');" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="danger">Delete</button>
                                    </form>
                                    <form method="post" action="admin.php" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                        <input type="hidden" name="action" value="reset_password">
                                        <button type="submit">Reset password</button>
                                    </form>
                                    <?php if (empty($u['deactivated'])): ?>
                                    <button type="button" onclick='showDeactivateModal(<?php echo $u['id']; ?>, <?php echo json_encode($u['email']); ?>)' style="background: linear-gradient(135deg, #f59e42 0%, #eab308 100%); color: #fff; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; margin-bottom: 4px;">🚫 Deactivate</button>
                                    <?php else: ?>
                                    <button type="button" onclick='showActivateModal(<?php echo $u['id']; ?>, <?php echo json_encode($u['email']); ?>)' style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; margin-bottom: 4px;">✅ Activate</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="muted">(you)</span>
                                <?php endif; ?>
                                <!-- Deactivate User Modal -->
                                <div id="deactivateModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:3000;align-items:center;justify-content:center;">
                                    <div style="background:#fff;padding:32px 24px;border-radius:16px;max-width:400px;width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);position:relative;">
                                        <button onclick="closeDeactivateModal()" style="position:absolute;top:12px;right:12px;background:none;border:none;font-size:22px;cursor:pointer;">&times;</button>
                                        <h3 style="margin-top:0;font-size:22px;font-weight:700;">Deactivate User</h3>
                                        <form id="deactivateUserForm" method="post" action="admin.php">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="id" id="deactivateUserId">
                                            <input type="hidden" name="action" value="deactivate_user">
                                            <div style="margin-bottom:12px;">
                                                <label for="deactivateMessage">Deactivation Message:</label>
                                                <textarea id="deactivateMessage" name="deactivate_message" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #e2e8f0;min-height:80px;">Your account was deactivated due to some suspicious transaction you did in the past few days.</textarea>
                                            </div>
                                            <button type="submit" style="background:linear-gradient(135deg,#f59e42 0%,#eab308 100%);color:#fff;padding:10px 18px;border-radius:8px;font-size:15px;font-weight:600;border:none;cursor:pointer;width:100%;">Deactivate</button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Activate User Modal -->
                                <div id="activateModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:3000;align-items:center;justify-content:center;">
                                    <div style="background:#fff;padding:32px 24px;border-radius:16px;max-width:400px;width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);position:relative;">
                                        <button onclick="closeActivateModal()" style="position:absolute;top:12px;right:12px;background:none;border:none;font-size:22px;cursor:pointer;">&times;</button>
                                        <h3 style="margin-top:0;font-size:22px;font-weight:700;">Activate User</h3>
                                        <form id="activateUserForm" method="post" action="admin.php">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="id" id="activateUserId">
                                            <input type="hidden" name="action" value="activate_user">
                                            <div style="margin-bottom:12px;">
                                                <label for="activationCode">Generate 12-digit Activation Code:</label>
                                                <input type="text" id="activationCode" name="activation_code" readonly style="width:100%;padding:8px;border-radius:6px;border:1px solid #e2e8f0;font-size:18px;letter-spacing:2px;cursor:text;">
                                                <div style="display:flex;gap:8px;margin-top:8px;">
                                                    <button type="button" onclick="generateActivationCode()" style="background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);color:#fff;padding:8px 14px;border-radius:8px;font-size:14px;font-weight:600;border:none;cursor:pointer;flex:1;">Generate Code</button>
                                                    <button type="button" onclick="copyActivationCode()" style="background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);color:#fff;padding:8px 14px;border-radius:8px;font-size:14px;font-weight:600;border:none;cursor:pointer;flex:1;">Copy Code</button>
                                                </div>
                                            </div>
                                            <button type="submit" style="background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);color:#fff;padding:10px 18px;border-radius:8px;font-size:15px;font-weight:600;border:none;cursor:pointer;width:100%;">Activate</button>
                                        </form>
                                    </div>
                                </div>

                                <script>
                                function showDeactivateModal(userId, email) {
                                    document.getElementById('deactivateUserId').value = userId;
                                    document.getElementById('deactivateModal').style.display = 'flex';
                                }
                                function closeDeactivateModal() {
                                    document.getElementById('deactivateModal').style.display = 'none';
                                }
                                function showActivateModal(userId, email) {
                                    document.getElementById('activateUserId').value = userId;
                                    generateActivationCode();
                                    document.getElementById('activateModal').style.display = 'flex';
                                }
                                function closeActivateModal() {
                                    document.getElementById('activateModal').style.display = 'none';
                                }
                                function generateActivationCode() {
                                    var code = '';
                                    for (var i = 0; i < 12; i++) code += Math.floor(Math.random() * 10);
                                    document.getElementById('activationCode').value = code;
                                }
                                function copyActivationCode() {
                                    var input = document.getElementById('activationCode');
                                    if (!input || !input.value) {
                                        return;
                                    }
                                    input.focus();
                                    input.select();
                                    input.setSelectionRange(0, input.value.length);
                                    if (navigator.clipboard && window.isSecureContext) {
                                        navigator.clipboard.writeText(input.value).catch(function() {
                                            document.execCommand('copy');
                                        });
                                    } else {
                                        document.execCommand('copy');
                                    }
                                }
                                </script>
                                <!-- User Details Modal -->
                                <div id="userDetailsModal" class="modern-modal-bg">
                                    <div class="modern-modal-card">
                                        <button onclick="closeUserDetailsModal()" class="modern-modal-close">&times;</button>
                                        <h3 class="modern-modal-title">User Details</h3>
                                        <div id="userDetailsContent" class="modern-user-details"></div>
                                    </div>
                                </div>
    <style>
    .modern-modal-bg {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(30, 41, 59, 0.35);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }
    .modern-modal-bg[style*="display: flex"] {
        display: flex !important;
    }
    .modern-modal-card {
        background: #fff;
        padding: 36px 28px 28px 28px;
        border-radius: 18px;
        max-width: 420px;
        width: 92vw;
        box-shadow: 0 8px 32px rgba(16, 24, 40, 0.18);
        position: relative;
        animation: modalPop 0.25s cubic-bezier(.4,2,.6,1) 1;
    }
    @keyframes modalPop {
        0% { transform: scale(0.95) translateY(30px); opacity: 0; }
        100% { transform: scale(1) translateY(0); opacity: 1; }
    }
    .modern-modal-close {
        position: absolute;
        top: 14px;
        right: 16px;
        background: none;
        border: none;
        font-size: 26px;
        color: #64748b;
        cursor: pointer;
        transition: color 0.2s;
    }
    .modern-modal-close:hover {
        color: #dc2626;
    }
    .modern-modal-title {
        margin-top: 0;
        font-size: 24px;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.5px;
        margin-bottom: 18px;
        text-align: center;
    }
    .modern-user-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px 10px;
        font-size: 16px;
        color: #334155;
        margin-bottom: 8px;
    }
    .modern-user-details div {
        background: #f8fafc;
        border-radius: 8px;
        padding: 10px 12px;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(16,24,40,0.04);
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-break: break-word;
    }
    .modern-user-details strong {
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 2px;
        letter-spacing: 0.1px;
    }
    @media (max-width: 600px) {
        .modern-modal-card { padding: 22px 6vw 18px 6vw; }
        .modern-user-details { font-size: 15px; gap: 10px 6px; }
        .modern-user-details div { padding: 8px 7px; }
    }
    </style>

                                <!-- Send Message Modal -->
                                <div id="messageModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:2000;align-items:center;justify-content:center;">
                                    <div style="background:#fff;padding:32px 24px;border-radius:16px;max-width:400px;width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);position:relative;">
                                        <button onclick="closeMessageModal()" style="position:absolute;top:12px;right:12px;background:none;border:none;font-size:22px;cursor:pointer;">&times;</button>
                                        <h3 style="margin-top:0;font-size:22px;font-weight:700;">Send Message</h3>
                                        <form id="sendMessageForm" method="post" action="admin.php">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="user_id" id="messageUserId">
                                            <div style="margin-bottom:12px;">
                                                <label for="messageEmail">To:</label>
                                                <input type="email" id="messageEmail" name="email" readonly style="width:100%;padding:8px;border-radius:6px;border:1px solid #e2e8f0;">
                                            </div>
                                            <div style="margin-bottom:12px;">
                                                <label for="messageType">Type:</label>
                                                <select id="messageType" name="message_type" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #e2e8f0;">
                                                    <option value="">-- Select Type --</option>
                                                    <option value="info">Info</option>
                                                    <option value="warning">Warning</option>
                                                    <option value="alert">Alert</option>
                                                    <option value="promotion">Promotion</option>
                                                </select>
                                            </div>
                                            <div style="margin-bottom:12px;">
                                                <label for="messageText">Message:</label>
                                                <textarea id="messageText" name="message" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #e2e8f0;min-height:80px;"></textarea>
                                            </div>
                                            <button type="submit" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#fff;padding:10px 18px;border-radius:8px;font-size:15px;font-weight:600;border:none;cursor:pointer;width:100%;">Send</button>
                                        </form>
                                    </div>
                                </div>
                                <script>
                                function showUserDetails(user) {
                                    var modal = document.getElementById('userDetailsModal');
                                    var content = document.getElementById('userDetailsContent');
                                    content.innerHTML = `
                                        <div><strong>ID</strong>${user.id}</div>
                                        <div><strong>Name</strong>${user.first_name ?? ''} ${user.surname ?? ''}</div>
                                        <div><strong>Email</strong>${user.email}</div>
                                        <div><strong>Phone</strong>${user.phone ?? 'N/A'}</div>
                                        <div><strong>Role</strong>${user.role ?? 'user'}</div>
                                        <div><strong>Balance</strong>$${(user.balance ?? 0).toLocaleString()}</div>
                                        <div><strong>Referral Code</strong>${user.referral_code ?? 'N/A'}</div>
                                    `;
                                    modal.style.display = 'flex';
                                }
                                function closeUserDetailsModal() {
                                    document.getElementById('userDetailsModal').style.display = 'none';
                                }
                                function showMessageModal(userId, email) {
                                    document.getElementById('messageUserId').value = userId;
                                    document.getElementById('messageEmail').value = email;
                                    document.getElementById('messageModal').style.display = 'flex';
                                }
                                function closeMessageModal() {
                                    document.getElementById('messageModal').style.display = 'none';
                                }
                                // Close modals on background click
                                window.onclick = function(event) {
                                    if (event.target === document.getElementById('userDetailsModal')) closeUserDetailsModal();
                                    if (event.target === document.getElementById('messageModal')) closeMessageModal();
                                }
                                </script>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            </section>
        </div>
    </main>
    
    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="logout-modal">
        <div class="logout-modal-content">
            <div class="logout-modal-icon">🚪</div>
            <h3 class="logout-modal-title">Log Out</h3>
            <p class="logout-modal-text">Are you sure you want to log out of the admin panel? You'll need to sign in again to access the dashboard.</p>
            <div class="logout-modal-actions">
                <button class="logout-modal-btn cancel" id="cancelLogout">Cancel</button>
                <button class="logout-modal-btn confirm" id="confirmLogout">Log Out</button>
            </div>
        </div>
    </div>
    
    <script>
        // Custom logout modal handler
        document.addEventListener('DOMContentLoaded', function() {
            const logoutTrigger = document.querySelector('[data-logout-trigger]');
            const logoutModal = document.getElementById('logoutModal');
            const cancelBtn = document.getElementById('cancelLogout');
            const confirmBtn = document.getElementById('confirmLogout');
            
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
            
            // Close modal when clicking outside
            logoutModal.addEventListener('click', function(e) {
                if (e.target === logoutModal) {
                    logoutModal.classList.remove('active');
                }
            });
            
            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && logoutModal.classList.contains('active')) {
                    logoutModal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
