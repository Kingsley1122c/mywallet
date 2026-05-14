# Gmail SMTP Setup Guide 📧

## How to Enable Email Notifications

Your Mivonta application is now ready to send beautiful email notifications for:
- 🎉 Welcome emails when users register
- 💸 Transaction notifications when users send money
- 💰 Receive notifications when users receive money

## Setup Steps

### 1. Generate Gmail App Password

Since Gmail blocks less secure apps, you need to create an **App Password**:

1. Go to your Google Account: https://myaccount.google.com/
2. Click on **Security** in the left menu
3. Under "Signing in to Google", click on **2-Step Verification** (you must enable this first if not already enabled)
4. After 2-Step Verification is enabled, go back to Security
5. Click on **App passwords**: https://myaccount.google.com/apppasswords
6. Select **"Mail"** and **"Other (Custom name)"**
7. Type "Mivonta SMTP" as the name
8. Click **Generate**
9. Google will give you a 16-character password like: `abcd efgh ijkl mnop`
10. **Copy this password** (remove the spaces: `abcdefghijklmnop`)

### 2. Update email_config.php

Open `email_config.php` and update it:

```php
<?php
return [
    'enabled' => true,  // ⚠️ CHANGE THIS TO true
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your-email@gmail.com',  // ⚠️ YOUR GMAIL ADDRESS
    'smtp_password' => 'abcdefghijklmnop',      // ⚠️ YOUR APP PASSWORD (16 chars, no spaces)
    'from_email' => 'your-email@gmail.com',     // ⚠️ YOUR GMAIL ADDRESS
    'from_name' => 'Mivonta'
];
?>
```

**Example:**
```php
<?php
return [
    'enabled' => true,
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'john.doe@gmail.com',
    'smtp_password' => 'xyzw1234abcd5678',
    'from_email' => 'john.doe@gmail.com',
    'from_name' => 'Mivonta'
];
?>
```

### 3. Test the Email System

After updating the configuration:

1. **Test Registration Email:**
   - Go to: https://unfecundated-trinomially-elane.ngrok-free.dev/register.html
   - Register a new test account
   - Check your email inbox for a beautiful welcome message 🎉

2. **Test Transfer Email:**
   - Login to your dashboard
   - Send money to another user
   - Both sender and recipient should receive email notifications 💸

### 4. Troubleshooting

If emails are not being sent:

1. **Check email_log.txt:**
   - Location: `c:\Users\HP\OneDrive\Desktop\inside site\email_log.txt`
   - This file logs all failed email attempts

2. **Verify App Password:**
   - Make sure you copied the 16-character password correctly
   - Remove all spaces from the password
   - The password is case-sensitive

3. **Check 2-Step Verification:**
   - App Passwords only work with 2-Step Verification enabled
   - Go to https://myaccount.google.com/security and enable it

4. **Gmail Account Security:**
   - Some accounts may need to allow "less secure app access" (rarely needed with App Passwords)
   - Check: https://myaccount.google.com/lesssecureapps

5. **Firewall/Port Blocking:**
   - Make sure port 587 (SMTP) is not blocked by your firewall
   - Try disabling firewall temporarily to test

### 5. Current Status

✅ Beautiful success modals implemented  
✅ SMTP email system created  
✅ Welcome email function added  
✅ Transaction emails working  
⏳ **Waiting for Gmail configuration** (follow steps above)

### 6. What Emails Look Like

All emails include:
- 🎨 Beautiful HTML design with gradients and colors
- 📱 Mobile-responsive layout
- 🔒 Security tips and branding
- ✨ Animated icons and modern styling

**Welcome Email** includes:
- Personalized greeting with user's name
- Registration date and time
- Unique referral code
- Feature overview (Send, Receive, Add Money, Withdraw)
- Dashboard button link

**Transaction Emails** include:
- Transaction type (Sent/Received/Added/Withdrawn)
- Amount with currency symbol
- Recipient/Sender information
- Transaction date and time
- New balance display

## Quick Reference

| Setting | Value |
|---------|-------|
| SMTP Host | smtp.gmail.com |
| SMTP Port | 587 (TLS) |
| Authentication | Required (App Password) |
| Email Format | HTML |
| Fallback | PHP mail() function |

## Security Notes

- ⚠️ **Never commit email_config.php to public repositories**
- ⚠️ Keep your App Password secret
- ⚠️ Rotate your App Password periodically
- ✅ The application handles SMTP securely with TLS encryption
- ✅ Emails are sent asynchronously and won't block user actions

---

Need help? The email system will automatically fall back to PHP's mail() function if SMTP fails.
