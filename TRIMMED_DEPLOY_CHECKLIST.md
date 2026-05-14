# Trimmed Deploy Checklist

Use this checklist when preparing a shared-host upload.

## Upload

- Upload the contents of `shared-host-bundle` after running `build_shared_host_bundle.ps1`.
- Confirm `.htaccess` is in the web root.
- Confirm the `api/` folder exists in the upload.
- Confirm the host can write to the app data files in the project root.

## Keep out of the shared-host bundle

- `.git/`
- `Dockerfile`
- `START_PUBLIC_SITE.bat`
- `phpinfo.php`
- `api/test.php`
- `test.html`
- `test_email.php`
- `test_resend.php`
- `test_session.php`
- `test_smtp_debug.php`
- `test_transfer.php`
- `dashboard_snapshot.html`
- `iphone_preview.html`
- `README.md`
- `FREE_PHP_HOSTING_GUIDE.md`
- `UPLOAD_MANIFEST.md`
- `TRIMMED_DEPLOY_CHECKLIST.md`
- `DEPLOY_TO_HOSTING.md`
- `HOSTING_SETUP_GUIDE.md`
- `EMAIL_SETUP_GUIDE.md`
- `TESTING_CHECKLIST.md`
- `GMAIL_SETUP.md`
- `SETUP_NGROK.txt`
- `FIND_CPANEL_URL.md`
- `YOUR_DOMAIN_SETUP.md`
- `users.seed.json`
- `local_only.php`
- `email_log.txt`
- `audit_log.jsonl`
- `reset_links.txt`

## Verify after upload

- Open `login.php`
- Sign in with a known working user
- Open `dashboard.php`
- Verify balances display correctly
- Verify transactions display correctly
- Verify admin login still works
- Verify admin messaging still works
- Verify password reset page opens
- Verify outbound email notifications work on the host
- Verify direct access to `users.json` returns forbidden or not found
