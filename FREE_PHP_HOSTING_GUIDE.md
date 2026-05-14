# Free PHP Hosting Guide

This app is a plain PHP application with JSON and TXT files for storage. The simplest legitimate free hosting option is a PHP shared host instead of a Docker platform.

Recommended starting point:

- InfinityFree: PHP, MySQL, persistent filesystem, FTP upload, free subdomains.

See also: `UPLOAD_MANIFEST.md` for the exact file order and local data inventory snapshot.

## What you need before moving

Copy these files from your current working copy or your last good backup:

- `users.json`
- `transactions.json`
- `bank_accounts.json`
- `pending_transfers.json`
- `password_resets.json`
- `admin_messages.json`
- `messages.json`
- `withdrawal_codes.json`
- `audit_log.jsonl`
- `email_log.txt`
- `reset_links.txt`

Important:

- If your latest data only exists on the current InfinityFree host, back it up from the live `htdocs` files before replacing anything.
- The code changes in this repository preserve existing hashed passwords and transaction history, but they cannot recover data that you overwrite without a backup.

## InfinityFree deployment steps

1. Create an InfinityFree account.
2. Create a hosting account and choose a free subdomain or attach your own domain.
3. Open the File Manager or connect with FTP.
4. Upload the contents of this project folder to the web root, usually `htdocs`.
5. Make sure the root `.htaccess` file is uploaded. It blocks direct web access to JSON, TXT, and log files.
6. Upload your current data files listed above so they replace any placeholder JSON files in the upload.
7. If email sending is required, configure the host-supported mail settings or switch to an API-based provider that your host allows.
8. Visit `login.php` and verify login, dashboard loading, and transactions.

## Files you should not expose publicly

- `phpinfo.php`
- `api/test.php`
- `test_email.php`
- `test_resend.php`
- `test_session.php`
- `test_smtp_debug.php`
- `test_transfer.php`

These are already restricted to local-only usage in this repository where applicable. Do not advertise or link to them publicly.

The root `.htaccess` file also blocks direct access to sensitive JSON, TXT, and log files on Apache-based shared hosts.

## Why shared PHP hosting fits this app

- The app is served directly by PHP files.
- The app stores state in local JSON and TXT files.
- A normal persistent web root is enough for the current storage model.
- No Docker-specific feature is required for the code to run.

## Source of truth

Keep this repository on GitHub as the source of truth, and treat InfinityFree as the live deployment target for the current site.

For this repository as it exists today, a PHP shared host is the lowest-friction option.