# Deploy Mivonta To Shared Hosting

## Before You Upload

1. Run `build_shared_host_bundle.ps1` from the project root.
2. Upload the generated `shared-host-bundle` contents, not the entire workspace.
3. Confirm your host supports PHP sessions, writable JSON/text data files, and Apache-style `.htaccess` rules.

## What The Bundle Includes

- The main PHP and HTML runtime files
- The `api/` folder required by the dashboard and admin features
- Required JSON data stores such as `users.json`, `transactions.json`, `admin_messages.json`, `password_resets.json`, `pending_transfers.json`, `bank_accounts.json`, and `withdrawal_codes.json`
- The root `.htaccess` file that blocks direct access to sensitive files

## What The Bundle Excludes

- Local-only test files such as `phpinfo.php`, `test_resend.php`, `test_smtp_debug.php`, and similar test pages
- Local logs such as `email_log.txt`, `audit_log.jsonl`, and `reset_links.txt`
- Local-only helper files and development documentation

## Upload Steps

1. Open your hosting file manager or FTP client.
2. Upload the contents of `shared-host-bundle` to your web root such as `/public_html` or `/htdocs`.
3. Preserve the folder structure exactly, especially the `api/` directory.
4. Verify the uploaded root contains `.htaccess`.

## After Upload

1. Open the site homepage and `login.php`.
2. Sign in with a known working account.
3. Open `dashboard.php` and verify balances and recent activity render correctly.
4. Verify admin login and admin messaging still work.
5. Verify direct access to `users.json` returns forbidden or not found.
6. Verify password reset and outgoing email notifications work on the host.

## Email Setup

1. Keep `EMAIL_PROVIDER` aligned with your actual production provider.
2. If you use Resend, verify your sending domain and DNS records before go-live.
3. Do not upload local-only secrets that you do not intend to use in production.

## Host Requirements

- Apache or compatible `.htaccess` support
- PHP with HTTPS client support for outbound mail provider requests
- Write permission for the app data files stored in the project root
- TLS support for outbound mail if SMTP is used

## Recommendation

Use the generated bundle as the deployment source of truth. Do not upload the whole workspace directly.
