# Post-Upload Verification For Mivonta

Use this after uploading `shared-host-bundle` to your live host.

## Required Inputs

- Your live site URL, for example `https://mivonta.com`
- One working user login
- One working admin login
- Access to the hosting file manager or control panel

## 1. Confirm Basic Routing

Open these URLs in a private browser window.

- `/`
- `/login.php`
- `/privacy.html`
- `/terms.html`

Expected result:

- Pages load over HTTPS
- Branding shows `Mivonta`
- No PHP warnings or raw server errors appear
- Login page and public legal pages render without broken styles or missing assets

## 2. Confirm Sensitive Files Are Blocked

Open these URLs directly.

- `/users.json`
- `/transactions.json`
- `/admin_messages.json`
- `/password_resets.json`
- `/reset_links.txt`
- `/phpinfo.php`
- `/test_resend.php`

Expected result:

- Each request returns `403`, `404`, or another non-public response
- No JSON contents, logs, or debug output are exposed

If any of these files are downloadable, stop and fix `.htaccess` support before continuing.

## 3. Confirm Sign-In Works

1. Open `/login.php`
2. Sign in with a known user account
3. Open `/dashboard.php`

Expected result:

- Login succeeds without redirect loops
- Dashboard loads with balance, quick actions, and recent activity
- Footer support details show `support@mivonta.com`

## 4. Confirm Admin Access Works

1. Sign out
2. Sign in with an admin account
3. Open `/admin.php`

Expected result:

- Admin dashboard loads
- User list renders correctly
- Activation, deactivation, and message actions are available

## 5. Confirm Admin Messaging Works

1. From `/admin.php`, send a test message to a user account
2. Sign in as that user in a separate browser session
3. Open `/dashboard.php`

Expected result:

- The in-app admin message appears for the target user
- The message is marked unread before opening and read after viewing
- If email notifications are enabled, the recipient also receives the message email

## 6. Confirm Password Reset Works

1. Open `/forgot.php`
2. Request a password reset for a real account
3. Complete the reset flow using the host's actual email delivery path

Expected result:

- Reset request succeeds without PHP warnings
- The user receives a reset email if production email is configured
- The reset link opens `/reset.php` correctly
- The new password works on `/login.php`

## 7. Confirm Email Provider Works On Host

Test one action that sends email, such as:

- admin message notification
- welcome email on registration
- transaction email after a transfer or receive flow

Expected result:

- Email arrives with `Mivonta` branding
- Sender and reply details are correct for production
- No provider errors appear in the host error log

If you use Resend:

- Confirm the production domain is verified in Resend
- Confirm DNS records are live before relying on email delivery

If you use SMTP:

- Confirm the mailbox credentials are valid on the host
- Confirm TLS works from hosted PHP

## 8. Confirm Writable Data Files

Use the app normally after upload.

Expected result:

- New users can register
- Transactions update balances correctly
- Admin messages are saved and read states change
- Password resets can be created and consumed

If these actions fail silently, the host likely cannot write to the root JSON files.

## 9. Confirm HTTPS And Canonical Redirects

Test both of these if supported by your domain setup.

- `http://your-domain`
- `http://www.your-domain`

Expected result:

- Requests redirect to the HTTPS canonical host
- No mixed-content warnings appear in the browser console

## 10. Final Release Decision

Ready for public use only if all of these are true:

- public pages load cleanly
- sensitive files are blocked
- user login works
- admin login works
- admin messaging works
- password reset works
- production email works
- JSON-backed writes succeed on host

## Record The Result

Save these results after deployment:

- live URL
- host name
- date checked
- working user account tested
- working admin account tested
- email provider used
- any failing route or action
