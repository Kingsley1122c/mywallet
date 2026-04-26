# 🌐 Paid Hosting Setup Guide

## 🏆 Best Options for Your PHP Banking App

| Hosting | Price | PHP | MySQL | Setup Time | Best For |
|---------|-------|-----|-------|-----------|----------|
| **Bluehost** | $2.95-8.95/mo | ✅ 8.0+ | ✅ | 10 min | Beginners, WordPress |
| **SiteGround** | $2.99-7.99/mo | ✅ 8.1+ | ✅ | 15 min | Best support, reliable |
| **HostGator** | $2.75-8.95/mo | ✅ 8.0+ | ✅ | 10 min | Affordable, easy |
| **Namecheap** | $1.44-5.88/mo | ✅ 8.1+ | ✅ | 20 min | Cheapest option |
| **DreamHost** | $2.59-3.95/mo | ✅ 8.0+ | ✅ | 15 min | Eco-friendly |
| **AWS Lightsail** | $3.50-5/mo | ✅ Custom | ✅ | 30 min | Scalable, powerful |

---

## 🚀 Recommended: SiteGround (Best Balance)

### Why SiteGround?
✅ Excellent 24/7 support
✅ Fast servers (HTTPS/SSL free)
✅ Easy cPanel control panel
✅ 30-day money-back guarantee
✅ Perfect for PHP apps

### Step 1: Buy Hosting
1. Go to: https://www.siteground.com/
2. Click "Get Started" 
3. Choose **Shared Hosting → StartUp Plan** (~$2.99/month first year)
4. Select a domain name OR use existing one
5. Complete checkout

### Step 2: Access Your Account
1. Check email for login credentials
2. Login to: https://client.siteground.com
3. Go to **cPanel** (usually in top right)
4. Look for **File Manager**

### Step 3: Upload Your Files
```
Folder structure should be:
/public_html/
  ├── index.html
  ├── index.php
  ├── login.html
  ├── login.php
  ├── dashboard.php
  ├── bank.js
  ├── bank.css
  ├── api/
  │   ├── transactions.php
  │   ├── bank_accounts.php
  │   ├── pending_transfers.php
  │   └── exchange_rates.php
  ├── admin.php
  ├── add_money.php
  ├── pending_transfers.php
  ├── countries.js
  ├── i18n.js
  └── (all other files)
```

**Upload Steps:**
1. In cPanel → File Manager
2. Open `/public_html/` folder
3. Click "Upload" button
4. Select ALL your files from: `c:\Users\HP\OneDrive\Desktop\inside site\`
5. Click "Upload Files"
6. Wait for completion ⏳

### Step 4: Create JSON Data Folders
Files need write permissions. In cPanel File Manager:
1. Right-click `/public_html/` → **Change Permissions** → Set to `755`
2. Make sure these files exist (or will be created):
   - `users.json`
   - `transactions.json`
   - `pending_transfers.json`
   - `bank_accounts.json`
   - `audit_log.jsonl`
   - `exchange_rates_cache.json`

### Step 5: Test Your Site
```
Your domain: https://yourdomain.com/
Home page: https://yourdomain.com/index.html
Login: https://yourdomain.com/login.html
Admin: https://yourdomain.com/admin.php (login as admin@example.com)
```

### Step 6: Update Configuration (If Needed)
Check if any files reference `localhost:8000`:
- Search in all files for "localhost"
- Replace with your actual domain: "yourdomain.com"

---

## 💳 Alternative: HostGator (Cheapest)

1. Go to: https://www.hostgator.com/
2. Select **Shared Hosting → Hatchling Plan** (~$2.75/month)
3. Follow same upload process as SiteGround
4. Use cPanel to manage files

---

## 🏗️ Alternative: AWS Lightsail (Most Powerful)

For serious, scalable deployment:

1. Go to: https://aws.amazon.com/lightsail/
2. Click "Create instance"
3. Choose **WordPress (comes with PHP 8.0+)**
4. Or select **Linux OS + Apache + MySQL**
5. Select $3.50/month plan
6. Download SSH key
7. Connect via SSH terminal

```powershell
# SSH into your server (on Windows)
ssh -i C:\path\to\key.pem bitnami@your-instance-ip

# Upload files via SCP
scp -i C:\path\to\key.pem -r "c:\Users\HP\OneDrive\Desktop\inside site\*" bitnami@your-instance-ip:/opt/bitnami/apache2/htdocs/
```

**Get public IP:** Shows in AWS Lightsail dashboard
**Access:** https://your-lightsail-ip/index.html

---

## 📋 Quick Decision: Which to Pick?

### Pick **SiteGround** if:
- You want best support
- You're new to hosting
- You want easiest setup
- You want reliability
✅ **RECOMMENDED FOR YOU**

### Pick **HostGator** if:
- You want cheapest option
- You have some technical experience
- You don't mind basic support

### Pick **AWS Lightsail** if:
- You want unlimited scalability
- You expect lots of traffic
- You want advanced control
- You're comfortable with Linux

---

## ✅ Pre-Upload Checklist

Before uploading, verify:
- [ ] All files are in: `c:\Users\HP\OneDrive\Desktop\inside site\`
- [ ] PHP server is NOT running (stop localhost:8000)
- [ ] All API endpoints use relative paths (not `/api/...`)
- [ ] No hardcoded `localhost` references
- [ ] Database files (JSON) are in root directory

---

## 🆘 Troubleshooting After Upload

### Site shows "404 Not Found"
- Make sure `index.html` or `index.php` is in `/public_html/`
- Check file upload completed

### Login not working
- Check `users.json` file is uploaded
- Verify folder permissions are `755`
- Check PHP version is 8.0+

### Card form not showing
- Make sure `bank.js` is uploaded
- Check browser console (F12) for JavaScript errors
- Verify `dashboard.php` is uploaded

### API endpoints returning 500 error
- Check file permissions (755)
- Verify all files in `/api/` folder uploaded
- Check `transactions.json` exists

### Exchange rates not updating
- Verify `exchange_rates_cache.json` exists
- Check `api/exchange_rates.php` is uploaded
- Cache updates every 24 hours

---

## 📞 Support After Setup

### If you have issues:
1. **SiteGround**: Live chat 24/7 (best)
2. **HostGator**: Phone + Email support
3. **AWS**: Documentation + Forums

---

## 💰 Cost Breakdown (First Year)

| Hosting | Year 1 | Renewal | Total Setup |
|---------|--------|---------|-------------|
| SiteGround | $35.88 (~$2.99/mo) | $95.88/yr | 15 min |
| HostGator | $32.88 (~$2.75/mo) | $95.88/yr | 10 min |
| Namecheap | $17.28 (~$1.44/mo) | $70.56/yr | 20 min |
| AWS Lightsail | $42/year ($3.50/mo) | $42/year | 30 min |

---

## 🎯 Next Steps

1. **Choose provider** (SiteGround recommended)
2. **Buy hosting plan**
3. **Get cPanel login**
4. **Upload files via File Manager**
5. **Test at your new domain**
6. **Invite users!**

---

## 📝 Domain Name Tips

- **.com** domains: More trusted, professional
- **.io** domains: Tech-friendly, modern
- **.co** domains: Short, contemporary

Register at:
- Namecheap (cheapest)
- GoDaddy (most popular)
- Google Domains (easiest)

Average: $10-15/year for domain

---

**Ready to go live? Start with SiteGround! 🚀**
