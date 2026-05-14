# 🎯 Complete Testing Checklist

## ✅ Server Status
- PHP Server: **RUNNING** on http://localhost:8000
- Test accounts: **CREATED**

---

## 🔐 Test Accounts

### Admin Account
- **Email:** admin@example.com
- **Password:** use the current admin password assigned in your local or deployed user data

### Regular User Account
- **Email:** user@example.com
- **Password:** use the current password stored for that test account

---

## 🧪 Testing Steps

### 1️⃣ **Home Page & Language Support**
- [ ] Open http://localhost:8000/index.html
- [ ] Test language selector (EN, ES, FR, KO, ZH-TW)
- [ ] Verify all text changes when language switches
- [ ] Test "Get Started" button redirects to login

### 2️⃣ **User Registration & Login**
- [ ] Click "Sign Up" and register a new test user
- [ ] Login with a current non-admin test account from your data store
- [ ] Verify dashboard loads with balance displayed

### 3️⃣ **Currency & Country Selection**
- [ ] Click country dropdown in dashboard
- [ ] Select different countries (USA, UK, Germany, Japan, etc.)
- [ ] Verify currency symbol and format changes
- [ ] Check balance converts with live exchange rates
- [ ] Refresh page - country selection should persist

### 4️⃣ **Add Money with Bank Card** ⭐ NEW FEATURE
- [ ] Click "Add Money" button
- [ ] Select payment method: "Bank Card (Debit/Credit)"
- [ ] **Card form should appear automatically**
- [ ] Enter test card details:
  - Card Number: `4532 1234 5678 9010` (auto-formats with spaces)
  - Expiry: `12/28` (auto-adds slash)
  - CVV: `123` (numeric only)
  - Name: `Test User`
- [ ] Click "Add Money"
- [ ] Verify balance increases
- [ ] Check transaction appears in history

### 5️⃣ **Send Money & Pending Transfers**
- [ ] Click "Send Money" button
- [ ] Enter recipient email: admin@example.com
- [ ] Enter amount: $50
- [ ] Submit - should show "Transfer submitted for approval"
- [ ] **Check loading spinner appears**
- [ ] Verify balance does NOT decrease yet

### 6️⃣ **Admin Approval Workflow**
- [ ] Logout from user account
- [ ] Login with the current admin credentials from your local seed or user store
- [ ] Click "Pending Transfers" in admin menu
- [ ] See pending $50 transfer from user@example.com
- [ ] Click "Accept" button
- [ ] Verify transfer moves to "Approved" section
- [ ] Check admin balance increased by $50

### 7️⃣ **Admin Add Money Feature**
- [ ] Stay logged in as admin
- [ ] Click "Add Money to User" in admin menu
- [ ] Select user: user@example.com
- [ ] Enter amount: $100
- [ ] Submit
- [ ] Logout and login as user@example.com
- [ ] Verify balance increased by $100

### 8️⃣ **Withdrawal via WhatsApp**
- [ ] Click "Withdraw" button
- [ ] Should see WhatsApp contact: +1 (551) 263-2687
- [ ] Click "Contact via WhatsApp" button
- [ ] Should open WhatsApp with pre-filled message

### 9️⃣ **Bank Account Management**
- [ ] Click "Bank Accounts" in menu
- [ ] Add a new bank account
- [ ] Verify it appears in list
- [ ] Delete bank account

### 🔟 **Language Persistence**
- [ ] Change language to Spanish
- [ ] Logout and login again
- [ ] Language should remain Spanish
- [ ] Dashboard text should be in Spanish

---

## 🎨 Card Form Features to Verify

### Input Formatting
- **Card Number:** Automatically adds spaces (1234 5678 9012 3456)
- **Expiry Date:** Automatically adds slash (MM/YY)
- **CVV:** Only accepts numbers (3-4 digits)

### Validation Messages
- Empty card number → "Please enter a valid card number"
- Card number < 13 digits → "Please enter a valid card number"
- Invalid expiry → "Please enter valid expiry date (MM/YY)"
- Invalid CVV → "Please enter valid CVV"
- Empty name → "Please enter cardholder name"

### Security Features
- 🔒 Security badge displays: "Your card information is secure and encrypted"
- Card details only show when "Bank Card" is selected

---

## 🌍 Currency Testing

Test these country conversions:
- **USA** → $ USD
- **United Kingdom** → £ GBP
- **Eurozone** → € EUR
- **Japan** → ¥ JPY (no decimals)
- **South Korea** → ₩ KRW (no decimals)
- **India** → ₹ INR
- **China** → ¥ CNY

Exchange rates update every 24 hours automatically.

---

## 🚨 Error Testing

### Security Tests
- [ ] Try accessing admin.php without login → Should redirect
- [ ] Try sending money with $0 amount → Should show error
- [ ] Try adding money without payment method → Should show error

### Edge Cases
- [ ] Add money with amount > $10,000 → Should show limit error
- [ ] Send money to non-existent email → Should show error
- [ ] Admin reject transfer → Should not update balances

---

## 📊 Data Files to Monitor

Check these JSON files for data integrity:
- `users.json` - User accounts
- `transactions.json` - Transaction history
- `pending_transfers.json` - Pending transfers
- `bank_accounts.json` - Bank account list
- `audit_log.jsonl` - Admin actions log
- `exchange_rates_cache.json` - Currency rates (24hr cache)

---

## ✨ All Features Implemented

✅ Authentication (Login, Register, Password Reset)
✅ Multi-language Support (5 languages)
✅ Multi-currency Support (20 countries)
✅ Live Exchange Rates (24-hour cache)
✅ Add Money with Bank Card Input
✅ Send Money (Pending Approval)
✅ Withdrawal via WhatsApp
✅ Admin Approval System
✅ Admin Add Money Feature
✅ Bank Account Management
✅ Loading Indicators
✅ CSRF Protection
✅ Audit Logging

---

## 🎉 You're All Set If:

1. ✅ Server is running on http://localhost:8000
2. ✅ You can login with test accounts
3. ✅ Card form appears when "Bank Card" is selected
4. ✅ Card inputs format automatically
5. ✅ Currency changes when country is selected
6. ✅ Pending transfers require admin approval
7. ✅ All language translations work

---

## 🔧 Quick Commands

**Start Server:**
```powershell
C:\xampp\php\php.exe -S localhost:8000
```

**Check Server Status:**
```powershell
Get-Process -Name php
```

**View Pending Transfers:**
```powershell
Get-Content .\pending_transfers.json | ConvertFrom-Json
```

**Reset Test Data:**
```powershell
# Reset test data manually from the local JSON files or restore from backup.
```

---

## 🆘 Need Help?

- Server not responding? Restart with command above
- Card form not showing? Check browser console (F12)
- Currency not converting? Check exchange_rates_cache.json exists
- Pending transfers not working? Login as admin and check pending_transfers.php

**Server is LIVE and ready for testing!** 🚀
