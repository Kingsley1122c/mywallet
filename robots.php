<?php
require_once __DIR__ . '/url_helpers.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /api/\n";
echo "Disallow: /admin.php\n";
echo "Disallow: /dashboard.php\n";
echo "Disallow: /settings.php\n";
echo "Disallow: /pending_transfers.php\n";
echo "Disallow: /add_money.php\n";
echo "Disallow: /bank_accounts.php\n";
echo "Disallow: /bank_accounts_new.php\n";
echo "Disallow: /transactions.php\n";
echo "Disallow: /loans.php\n";
echo "Disallow: /investments.php\n";
echo "Disallow: /withdrawal_codes_admin.php\n";
echo "Disallow: /login.php\n";
echo "Disallow: /register.php\n";
echo "Disallow: /forgot.php\n";
echo "Disallow: /reset.php\n";
echo "Disallow: /change_password.php\n";
echo "Disallow: /test.html\n";
echo "Disallow: /dashboard_snapshot.html\n";
echo "Disallow: /iphone_preview.html\n\n";
echo 'Sitemap: ' . app_url('sitemap.xml') . "\n";