# 📧 Email Notification Setup Guide

## ✅ Email Notifications Enabled!

Your banking app now sends automatic email notifications for:
- ✉️ Money added to account
- ✉️ Money sent (pending approval)
- ✉️ Money received (after admin approval)
- ✉️ Withdrawal requests

---

## 🚀 How It Works

### For Local Testing (localhost:8000)
PHP's built-in `mail()` function is now integrated. However, **localhost cannot send real emails** without additional configuration.

### For Live Hosting (SiteGround/HostGator)
✅ **Emails will work automatically!** Most hosting providers have mail servers pre-configured.

---

## 🔧 Email Configuration Options

### Option 1: Use Hosting Provider's Mail (RECOMMENDED)
When you upload to **SiteGround** or **HostGator**, emails will send automatically using their SMTP servers. No setup needed!

### Option 2: Use Gmail SMTP (For Better Delivery)
For professional email delivery with Gmail:

1. **Install PHPMailer** (upload to your hosting):
```php
// Download PHPMailer from: https://github.com/PHPMailer/PHPMailer
```

2. **Update email_notifications.php** to use Gmail SMTP:
```php
// Replace the mail() function with PHPMailer
$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password'; // Use App Password, not regular password
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
```

### Option 3: Use SendGrid/Mailgun (For High Volume)
For professional transactional emails:
- **SendGrid**: Free 100 emails/day
- **Mailgun**: Free 5,000 emails/month

---

## 📝 Email Template Features

Your emails include:
- 🎨 Beautiful HTML design with MyWallet branding
- 💳 Transaction details (amount, date, balance)
- 📊 Current account balance
- 🔗 Direct link to dashboard
- 🔒 Security notice
- 📱 Mobile-responsive design

---

## 🧪 Testing Email Notifications

### On Localhost (Won't Send Real Emails)
Email functions will execute but won't deliver. To test:

1. **Check PHP error logs** for email attempts
2. **Use MailHog** (local email testing tool):
```powershell
# Install MailHog
winget install MailHog

# Run MailHog
mailhog

# Configure PHP to use MailHog
# In php.ini, set:
# sendmail_path = "C:\path\to\mailhog.exe sendmail"
```

3. View emails at: http://localhost:8025

### On Live Hosting (Real Emails)
1. Upload all files to your hosting
2. Perform a transaction (add money, send, etc.)
3. Check your email inbox
4. Check spam folder if not received

---

## 📧 Email Examples

### When User Adds Money:
**Subject:** Transaction Notification - MyWallet  
**Content:**
- Amount added: +$100.00
- Payment method: Bank Card
- Current balance: $500.00
- View Dashboard button

### When Money is Sent:
**Subject:** Transaction Notification - MyWallet  
**Content:**
- Amount sent: -$50.00
- Recipient: user@example.com
- Status: Pending approval
- Current balance: $450.00

### When Money is Received:
**Subject:** Transaction Notification - MyWallet  
**Content:**
- Amount received: +$50.00
- From: sender@example.com
- Status: Approved
- Current balance: $550.00

---

## 🎯 What You Need to Do

### For Development (Now):
✅ **Nothing!** Email notifications are already integrated in your code.

### For Production (When Going Live):
1. ✅ Upload all files to SiteGround/HostGator
2. ✅ Test by making a transaction
3. ✅ Check your email
4. ✅ If emails don't arrive, contact hosting support (they'll enable it)

---

## 🔍 Troubleshooting

### Emails not sending on live hosting?
1. Check spam/junk folder
2. Contact hosting support: "Please enable mail() function"
3. Verify hosting includes email support (most do)

### Want custom "From" email?
Edit `api/email_notifications.php` line 17:
```php
$headers .= "From: MyWallet <noreply@mypaypalwallet.com>" . "\r\n";
```
Change to:
```php
$headers .= "From: MyWallet <noreply@yourdomain.com>" . "\r\n";
```

### Want to customize email design?
Edit the HTML in `buildEmailBody()` function in `api/email_notifications.php`

---

## ✅ Files Modified

- ✅ `api/email_notifications.php` - Email sending system
- ✅ `api/transactions.php` - Added email calls for add/send/withdraw
- ✅ `api/pending_transfers.php` - Added email calls for approve/reject

---

## 🎉 You're All Set!

Email notifications will work automatically when you deploy to live hosting. Users will receive beautiful HTML emails for every transaction! 📧✨

**Test it:** Make a transaction after uploading to SiteGround and check your email!
