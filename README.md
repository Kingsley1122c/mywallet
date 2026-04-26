# MyWallet (local)

Quick local setup to test the simple PHP login + dashboard.

1. Start the PHP built-in server (PowerShell):

   php -S localhost:8000 -t "c:\Users\HP\OneDrive\Desktop\inside site"

2. Open http://localhost:8000 in your browser.

3. Default credentials (created automatically on first run):

   - Email: user@example.com
   - Password: Password123

   - Admin account (created on first run):

     - Email: admin@example.com
     - Password: AdminPass123

4. Notes:

   - The first time you visit `login.php` the app will create `users.json` with the default account. You can remove or change it afterwards.
   - To add users securely, use PHP's `password_hash()` and store the resulting hash in `users.json` alongside an `id` and `email`.

5. Registering a new account

   - Open `register.php` (or click "Don't have an account? Register" from the login page) to create a new account.
   - Passwords must be at least 8 characters. After successful registration you are automatically signed in.

6. Admin panel

   - Admins can manage users at `admin.php`. On first run the project creates an admin account if none exists. Admin actions include promoting/demoting users and deleting users (the last admin cannot be removed).

7. Password reset

   - Forgot password: open `forgot.php`, enter your email, and a reset link will be generated. For local testing the reset link is displayed on the page and appended to `reset_links.txt` in the project folder.
   - Use the link to open `reset.php?token=...` and set a new password (token expires in 1 hour).
   - Logged-in users can change their password in `change_password.php`.
   - Admins can reset any user's password from `admin.php`; the admin interface will display a temporary password (change it after sign-in).

8. Audit log

   - Password-related events are recorded in `audit_log.jsonl` in the project folder. Each line is a JSON object with fields: `time`, `type` (e.g., `password_reset_requested`, `password_reset_completed`, `password_changed`, `password_reset_by_admin`), `user_id`, `email`, `actor`, `ip`, and `details`.
   - For local testing the audit file is local only — do not rely on it for production compliance. Consider shipping audit entries to a central logging system for production environments.

9. Static HTML pages

   - You can use the included static pages `login.html` and `register.html` if you prefer keeping form UI as pure HTML.
   - The static register page uses `get_csrf.php` to fetch a CSRF token before submitting to `register.php`.

10. Deploying on Render

   - This repository now includes `Dockerfile`, `start-render.sh`, and `render.yaml` for Render.
   - The app stores users, transactions, password resets, messages, and logs in local JSON/TXT files. On Render, attach a persistent disk at `/var/data` so those files survive restarts and deploys.
   - Push the repository to GitHub, create a new Render Blueprint or Web Service from the repo, and Render will build from `Dockerfile`.
   - If your Render plan does not support disks, the app will still boot, but file-based data will reset when the container is replaced.

