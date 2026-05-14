param(
    [string]$Destination = "shared-host-bundle"
)

$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$destinationRoot = Join-Path $projectRoot $Destination

$includeFiles = @(
    '.htaccess',
    'account_deactivated.php',
    'add_money.php',
    'admin.php',
    'admin_messages.js',
    'admin_messages.json',
    'admin_message_popup.js',
    'all_countries.js',
    'all_languages.js',
    'audit.php',
    'bank.css',
    'bank.js',
    'banks_list.php',
    'bank_accounts.json',
    'bank_accounts.php',
    'bank_accounts_new.php',
    'change_password.php',
    'countries.js',
    'dashboard.php',
    'email_config.php',
    'forgot.php',
    'get_csrf.php',
    'i18n.js',
    'i18n_enhanced.js',
    'index.html',
    'index.php',
    'investments.php',
    'lang_selector.html',
    'loans.php',
    'login.html',
    'login.php',
    'logout.php',
    'messages.json',
    'password_resets.json',
    'pending_transfers.json',
    'pending_transfers.php',
    'privacy.html',
    'register.html',
    'register.php',
    'reset.php',
    'settings.php',
    'showhide.js',
    'style.css',
    'terms.html',
    'transactions.json',
    'transactions.php',
    'url_helpers.php',
    'users.json',
    'users_bootstrap.php',
    'withdrawal_codes.json',
    'withdrawal_codes_admin.php',
    'api\bank_accounts.php',
    'api\check_messages.php',
    'api\email_notifications.php',
    'api\exchange_rates.php',
    'api\exchange_rates_cache.json',
    'api\mark_message_read.php',
    'api\pending_transfers.php',
    'api\send_email_resend.php',
    'api\send_email_smtp.php',
    'api\transactions.php',
    'api\user_settings.php',
    'api\verify_account.php',
    'api\withdrawal_codes.php'
)

if (Test-Path $destinationRoot) {
    Remove-Item -Path $destinationRoot -Recurse -Force
}

New-Item -Path $destinationRoot -ItemType Directory | Out-Null

foreach ($relativePath in $includeFiles) {
    $sourcePath = Join-Path $projectRoot $relativePath
    if (-not (Test-Path $sourcePath)) {
        Write-Warning "Skipping missing file: $relativePath"
        continue
    }

    $destinationPath = Join-Path $destinationRoot $relativePath
    $destinationDirectory = Split-Path -Parent $destinationPath
    if (-not (Test-Path $destinationDirectory)) {
        New-Item -Path $destinationDirectory -ItemType Directory -Force | Out-Null
    }

    Copy-Item -Path $sourcePath -Destination $destinationPath -Force
}

Write-Host "Shared host bundle created at: $destinationRoot"
