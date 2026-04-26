# Deploy mywallet to 24/7 Hosting

## What You Need:
1. ✅ Domain name (moneywallet.xyz) - $1-2/year
2. ✅ Free hosting account (InfinityFree or 000webhost)

## Step-by-Step Deployment:

### STEP 1: Buy Domain
- Namecheap: https://www.namecheap.com → Search "moneywallet.xyz" → Buy ($1-2)
- Porkbun: https://porkbun.com → Search → Buy

### STEP 2: Sign Up for Free Hosting

**InfinityFree (Recommended):**
1. Go to: https://app.infinityfree.com/signup
2. Email: okoyechukwuebuka063@gmail.com
3. Create account
4. Click "Create Account" button
5. Choose: "Use your own domain"
6. Enter: moneywallet.xyz
7. Wait 5-10 minutes for activation

**OR 000webhost:**
1. Go to: https://www.000webhost.com
2. Sign up free
3. Create website with your domain

### STEP 3: Upload Files

**Using FileZilla (FTP):**
1. Download FileZilla: https://filezilla-project.org/download.php?type=client
2. Install it
3. Get FTP details from hosting panel:
   - Host: ftp.yourdomain.com (from hosting dashboard)
   - Username: (from hosting)
   - Password: (from hosting)
4. Connect FileZilla
5. Upload ALL files from:
   `C:\Users\HP\OneDrive\Desktop\inside site`
6. Upload to: `/htdocs` or `/public_html` folder

**Files to Upload:**
- All PHP files
- All HTML files
- style.css, bank.css
- bank.js, i18n_enhanced.js
- users.json, transactions.json
- api/ folder (all files)
- Everything except .md files

### STEP 4: Configure Domain DNS

**After hosting is set up:**
1. Go to your domain registrar (Namecheap/Porkbun)
2. Find "DNS Management" or "Nameservers"
3. Use nameservers provided by your hosting
4. Wait 1-24 hours for DNS propagation

**InfinityFree Nameservers (example):**
```
ns1.byet.org
ns2.byet.org
ns3.byet.org
ns4.byet.org
ns5.byet.org
```

### STEP 5: Test Your Site

After DNS propagates (1-24 hours):
- Visit: https://moneywallet.xyz
- Your site is now online 24/7!
- Accessible from any device
- No need to keep computer running

## Benefits:
✅ Custom domain (moneywallet.xyz)
✅ Online 24/7 (even when PC is off)
✅ Accessible worldwide
✅ Professional email possible
✅ Free forever (with ads) or $2-5/month (no ads)

## After Deployment:

### Enable Professional Emails:
1. Add domain to Resend: https://resend.com/domains
2. Configure DNS records (provided by Resend)
3. Update email_config.php with verified domain

### Remove PHP mail():
Once domain is verified with Resend, your emails will be professional and won't go to spam!

## Cost Breakdown:
- Domain: $1-2/year (moneywallet.xyz)
- Hosting: FREE (InfinityFree with ads) or $2-5/month (premium, no ads)
- Total: $1-2/year minimum

## Next Steps:
1. Buy domain NOW
2. Sign up for InfinityFree
3. Come back here and I'll help upload files
