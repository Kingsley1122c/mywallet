<?php

if (!function_exists('getWithdrawalProcessingWindowMs')) {
    function getWithdrawalProcessingWindowMs(): int
    {
        return 24 * 60 * 60 * 1000;
    }
}

if (!function_exists('syncWithdrawalTransactions')) {
    function syncWithdrawalTransactions(string $transactionsFile, string $usersFile): array
    {
        $transactions = file_exists($transactionsFile)
            ? json_decode(file_get_contents($transactionsFile), true)
            : [];
        $transactions = is_array($transactions) ? $transactions : [];

        if (empty($transactions)) {
            return [];
        }

        $users = file_exists($usersFile)
            ? json_decode(file_get_contents($usersFile), true)
            : [];
        $users = is_array($users) ? $users : [];

        $usersById = [];
        foreach ($users as $user) {
            $usersById[(int) ($user['id'] ?? 0)] = $user;
        }

        $nowMs = (int) round(microtime(true) * 1000);
        $changed = false;

        foreach ($transactions as $index => $transaction) {
            $status = strtolower((string) ($transaction['status'] ?? 'completed'));
            $kind = strtolower((string) ($transaction['kind'] ?? ''));
            $expiresAt = (int) ($transaction['expires_at'] ?? 0);

            if ($status !== 'processing' || $kind !== 'withdrawal' || $expiresAt <= 0 || $expiresAt > $nowMs) {
                continue;
            }

            $transactions[$index]['status'] = 'failed';
            $transactions[$index]['failed_at'] = $nowMs;
            $transactions[$index]['failure_reason'] = 'contact_customer_service';
            $changed = true;

            if (!empty($transaction['failure_email_sent_at'])) {
                continue;
            }

            $userId = (int) ($transaction['user_id'] ?? 0);
            $user = $usersById[$userId] ?? null;
            $userEmail = (string) ($user['email'] ?? '');
            if ($userEmail === '') {
                continue;
            }

            include_once __DIR__ . '/api/email_notifications.php';
            sendTransactionEmail($userEmail, [
                'type' => 'withdraw_failed',
                'amount' => abs((float) ($transaction['amount'] ?? 0)),
                'date' => date('F j, Y g:i A'),
                'balance' => isset($user['balance']) ? (float) $user['balance'] : 0,
                'bank_name' => (string) ($transaction['bank_name'] ?? ''),
                'account_number' => (string) ($transaction['account_number'] ?? ''),
            ]);

            include_once __DIR__ . '/audit.php';
            write_audit('withdrawal_failed', $userId, $userEmail, $userEmail, [
                'transaction_id' => (string) ($transaction['id'] ?? ''),
                'bank' => (string) ($transaction['bank_name'] ?? ''),
                'amount' => abs((float) ($transaction['amount'] ?? 0)),
                'reason' => 'contact_customer_service',
            ]);

            $transactions[$index]['failure_email_sent_at'] = $nowMs;
            $changed = true;
        }

        if ($changed) {
            file_put_contents($transactionsFile, json_encode($transactions, JSON_PRETTY_PRINT), LOCK_EX);
        }

        return $transactions;
    }
}