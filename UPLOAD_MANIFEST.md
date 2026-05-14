# Upload Manifest

Use this when moving the app to a free PHP host.

## Upload first

- `.htaccess`
- `index.php`
- `login.php`
- `register.php`
- `dashboard.php`
- all other application `.php`, `.html`, `.css`, and `.js` files
- the full `api/` folder except debug files you do not intend to keep exposed

## Upload next: current data files

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

## Keep but do not expose publicly

These can stay on the host, but the root `.htaccess` must block direct web access:

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
- `users.seed.json`
- `users_bootstrap.php`

## Do not rely on these in production

- `phpinfo.php`
- `api/test.php`
- `test_email.php`
- `test_resend.php`
- `test_session.php`
- `test_smtp_debug.php`
- `test_transfer.php`

## Local data inventory snapshot

Current local file timestamps from this workspace:

- `users.json` — 2026-04-27 06:44
- `transactions.json` — 2026-02-02 20:39
- `bank_accounts.json` — 2026-01-31 00:54
- `pending_transfers.json` — 2026-01-19 02:29
- `password_resets.json` — 2026-01-06 15:54
- `admin_messages.json` — 2026-01-29 08:52
- `messages.json` — 2026-01-21 09:25
- `withdrawal_codes.json` — 2026-01-08 04:47
- `audit_log.jsonl` — 2026-02-02 20:39
- `email_log.txt` — 2026-02-02 20:39
- `reset_links.txt` — 2026-01-06 15:54

Note:

- The recent `users.json` timestamp may reflect local maintenance, not necessarily the latest production activity.
- If your live InfinityFree files are newer than these local copies, back up the current host files before treating the local set as authoritative.