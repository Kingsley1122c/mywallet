# InfinityFree Steps

## 1. Create the hosting account

1. Go to InfinityFree and create the main account.
2. Create a hosting account.
3. Choose a free subdomain first so you can test quickly.

## 2. Open the web root

1. Open the InfinityFree control panel.
2. Open File Manager or connect with FTP.
3. Find the site web root, usually `htdocs`.

## 3. Build the upload folder locally

From PowerShell in the project folder run:

```powershell
.\build_shared_host_bundle.ps1
```

This creates `shared-host-bundle` with the production app files and current local data files.

## 4. Upload the bundle

1. Open the local `shared-host-bundle` folder.
2. Upload every file and folder inside it to `htdocs`.
3. Make sure `.htaccess` is uploaded too.

## 5. Test the critical routes

1. Open `https://your-subdomain/login.php`
2. Sign in with a known account.
3. Open `dashboard.php`.
4. Open `transactions.php`.
5. Open `admin.php` if you have admin access.

## 6. Check file protection

Try visiting these paths directly in the browser:

- `/users.json`
- `/transactions.json`
- `/audit_log.jsonl`
- `/phpinfo.php`

They should not download or render publicly.

## 7. If data looks old

If the site works but recent users or transactions are missing, your local JSON files are older than the live InfinityFree files. In that case, back up the current live JSON and TXT files from `htdocs` before uploading replacements.
