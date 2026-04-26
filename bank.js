// Helper to get display sender name (first name or full name)
function getDisplaySenderName(senderName, senderEmail) {
  if (!senderName) {
    if (senderEmail) return senderEmail.split('@')[0];
    return '';
  }
  // If senderName contains a space, use first word (first name)
  if (typeof senderName === 'string' && senderName.trim().length > 0) {
    const parts = senderName.trim().split(' ');
    return parts[0];
  }
  return senderName;
}

// Show latest incoming transfer alert on login
document.addEventListener('DOMContentLoaded', function() {
    // --- Withdrawal Code Flow ---
    const withdrawForm = document.getElementById('withdraw-form');
    const withdrawCodeForm = document.getElementById('withdraw-code-form');
    const withdrawBackBtn = document.getElementById('withdraw-back');
    const withdrawNextBtn = document.getElementById('withdraw-next');
    const withdrawCompleteBtn = document.getElementById('withdraw-complete');
    const withdrawalCodeInput = document.getElementById('withdrawal-code');
    const withdrawalCodeError = document.getElementById('withdrawal-code-error');
    const withdrawBankSelect = document.getElementById('withdraw-bank');
    // Populate bank accounts (simulate, replace with real data if needed)
    if (withdrawBankSelect) {
      fetch('api/bank_accounts.php', {credentials: 'same-origin'})
        .then(r => r.json())
        .then(data => {
          const withdrawNextBtn = document.getElementById('withdraw-next');
          if (Array.isArray(data.accounts) && data.accounts.length > 0) {
            data.accounts.forEach(acc => {
              const opt = document.createElement('option');
              opt.value = acc.id;
              opt.textContent = acc.bank_name + ' - ' + acc.account_number;
              withdrawBankSelect.appendChild(opt);
            });
            if (withdrawNextBtn) withdrawNextBtn.disabled = false;
          } else {
            // No bank accounts
            if (withdrawNextBtn) withdrawNextBtn.disabled = true;
          }
        });
    }

    let withdrawDetails = {};

    if (withdrawForm && withdrawCodeForm) {
      withdrawForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Save details for next step
        withdrawDetails.amount = parseFloat(document.getElementById('withdraw-amount').value);
        withdrawDetails.bank = withdrawBankSelect.value;
        if (!withdrawDetails.amount || !withdrawDetails.bank) return;
        // Validate against user balance
        fetch('api/user_settings.php?action=get_balance', {credentials: 'same-origin'})
          .then(r => r.json())
          .then(data => {
            const balance = data && data.balance ? parseFloat(data.balance) : 0;
            if (withdrawDetails.amount > balance) {
              alert('Withdrawal amount exceeds your available balance.');
              return;
            }
            // Hide details form, show code form
            withdrawForm.style.display = 'none';
            withdrawCodeForm.style.display = 'block';
            withdrawalCodeInput.value = '';
            withdrawalCodeError.style.display = 'none';
          });
      });
      if (withdrawBackBtn) {
        withdrawBackBtn.addEventListener('click', function() {
          withdrawCodeForm.style.display = 'none';
          withdrawForm.style.display = 'block';
        });
      }
      withdrawCodeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const code = withdrawalCodeInput.value.trim();
        withdrawalCodeError.style.display = 'none';
        // Show loading indicator
        let loading = document.createElement('div');
        loading.id = 'withdrawal-loading';
        loading.style.cssText = 'text-align:center;margin-bottom:12px;';
        loading.innerHTML = '<div style="display:inline-block;width:28px;height:28px;border:4px solid #e5e7eb;border-top-color:#0284c7;border-radius:50%;animation:spin 0.8s linear infinite;margin-bottom:8px;"></div><div style="color:#0284c7;font-weight:700;">Processing...</div>';
        withdrawCodeForm.insertBefore(loading, withdrawCodeForm.firstChild);
        if (!code || code.length !== 6) {
          withdrawalCodeError.textContent = 'Please enter a valid 6-digit code.';
          withdrawalCodeError.style.display = 'block';
          loading.remove();
          return;
        }
        // Validate code via API
        fetch('api/withdrawal_codes.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({action: 'validate', code: code, amount: withdrawDetails.amount})
        })
        .then(r => r.json())
        .then(data => {
          if (data.success && data.valid) {
            // Proceed with withdrawal (simulate, replace with real API call)
            fetch('api/transactions.php', {
              method: 'POST',
              credentials: 'same-origin',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({action: 'withdraw', amount: withdrawDetails.amount, bank: withdrawDetails.bank, csrf_token: csrf})
            })
            .then(r => r.json())
            .then(txData => {
              if (txData.success) {
                withdrawCodeForm.style.display = 'none';
                document.getElementById('withdraw-success-message').style.display = 'block';
                setTimeout(() => {
                  withdrawModal.style.display = 'none';
                  document.getElementById('withdraw-success-message').style.display = 'none';
                  withdrawForm.style.display = 'block';
                  withdrawCodeForm.reset && withdrawCodeForm.reset();
                  withdrawForm.reset && withdrawForm.reset();
                  window.location.reload();
                }, 2500);
              } else {
                withdrawalCodeError.textContent = txData.error || 'Withdrawal failed.';
                withdrawalCodeError.style.display = 'block';
              }
              loading.remove();
            });
          } else {
            withdrawalCodeError.textContent = data.error || 'Invalid or expired code.';
            withdrawalCodeError.style.display = 'block';
            loading.remove();
          }
        })
        .catch(() => {
          withdrawalCodeError.textContent = 'Network error. Please try again.';
          withdrawalCodeError.style.display = 'block';
          loading.remove();
        });
      });
      // Cancel/close modal
      const cancelWithdrawBtn = document.getElementById('cancel-withdraw');
      if (cancelWithdrawBtn) {
        cancelWithdrawBtn.addEventListener('click', function() {
          withdrawModal.style.display = 'none';
          withdrawForm.style.display = 'block';
          withdrawCodeForm.style.display = 'none';
        });
      }
    }
  // Always update balance from backend on page load
  fetchAndUpdateBalance();

  // Load user info into state for sender name
  fetch('api/user_settings.php', {credentials: 'same-origin'})
    .then(r => r.json())
    .then(data => {
      if (data.first_name) state.first_name = data.first_name;
      if (data.name) state.name = data.name;
      if (data.email) state.email = data.email;
    });
  fetch('api/transactions.php?action=latest_incoming', {credentials: 'same-origin'})
    .then(r => r.json())
    .then(tx => {
      if (tx && tx.amount > 0 && tx.sender && tx.sender_email && tx.sender_name && tx.time) {
        // Only show if not already seen
        const lastSeenTxId = localStorage.getItem('lastSeenIncomingTxId');
        if (tx.txId && tx.txId === lastSeenTxId) return;
        // Mask sender email middle
        function maskEmail(email) {
          const [user, domain] = email.split('@');
          if (user.length <= 2) return email;
          const first = user[0];
          const last = user[user.length-1];
          return first + '*'.repeat(user.length-2) + last + '@' + domain;
        }
        const maskedEmail = maskEmail(tx.sender_email);
        const now = new Date(tx.time);
        const dateStr = now.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
        const timeStr = now.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true});
        const alertModal = document.createElement('div');
        alertModal.id = 'latest-incoming-alert-modal';
        alertModal.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(2,8,23,0.85);backdrop-filter:blur(6px);z-index:10001;display:flex;align-items:center;justify-content:center;';
        alertModal.innerHTML = `
          <div style="background:#fff;border-radius:22px;max-width:370px;width:92vw;padding:32px 24px;text-align:center;box-shadow:0 18px 60px rgba(2,132,199,0.13);position:relative;">
            <div style="font-size:44px;margin-bottom:12px;">💸</div>
            <h2 style="font-size:22px;font-weight:900;color:#0284c7;margin-bottom:8px;">You've received money!</h2>
            <div style="font-size:16px;color:#334155;font-weight:700;margin-bottom:8px;">From: <span style='color:#0ea5e9'>${getDisplaySenderName(tx.sender_name, tx.sender_email)}</span></div>
            <div style="font-size:15px;color:#334155;font-weight:700;margin-bottom:8px;">Sender Email: <span style='color:#0ea5e9'>${maskedEmail}</span></div>
            <div style="font-size:15px;color:#22c55e;font-weight:900;margin-bottom:8px;">Amount: +$${parseFloat(tx.amount).toFixed(2)}</div>
            <div style="font-size:13px;color:#64748b;margin-bottom:8px;">${dateStr} ${timeStr}</div>
            <div style="font-size:13px;color:#64748b;margin-bottom:18px;">Transaction ID: ${tx.txId || ''}</div>
            <button id="close-incoming-alert" style="padding:10px 28px;background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:15px;cursor:pointer;">Close</button>
          </div>
        `;
        document.body.appendChild(alertModal);
        document.getElementById('close-incoming-alert').onclick = function() {
          alertModal.remove();
          if (tx.txId) localStorage.setItem('lastSeenIncomingTxId', tx.txId);
        };
      }
    });
});
// Fetch and update available balance in the dashboard UI
function fetchAndUpdateBalance() {
  // Use the correct selector for the dashboard balance
  const balanceEl = document.getElementById('account-balance');
  fetch('api/user_settings.php?action=get_balance', {credentials: 'same-origin'})
    .then(r => r.json())
    .then(data => {
      if (data && data.balance !== undefined && balanceEl) {
        balanceEl.textContent = `$${parseFloat(data.balance).toFixed(2)}`;
      }
    })
    .catch(() => {});
}
console.log('[DEBUG] bank.js loaded');
// Provide a no-op render function if not defined elsewhere
if (typeof render === 'undefined') {
  function render() {}
}

// --- Variable declarations moved to top for hoisting and error prevention ---
let addMoneyForm = null;
let selectedBankAccount = null;
let bankAccountsList = null;
let noBanksMessage = null;
let bankSelectionForm = null;
let withdrawAmountInput = null;
let confirmWithdrawBtn = null;
let selectedBankInfo = null;
let selectedBankDetails = null;
let withdrawBtn = null;
let withdrawModal = null;
let closeWithdraw = null;
// Currency selector in settings and dashboard

// --- Minimal robust implementation for dashboard buttons and modals ---
document.addEventListener('DOMContentLoaded', function() {
    console.log('[DEBUG] DOMContentLoaded fired');
  // Currency selector
  const settingsSelect = document.getElementById('settings-currency-select');
  if (settingsSelect) {
    fetch('api/user_settings.php', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        if (data.currency) settingsSelect.value = data.currency;
      });
    settingsSelect.addEventListener('change', function(e) {
      fetch('api/user_settings.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'same-origin',
        body: JSON.stringify({currency: e.target.value})
      }).then(() => window.location.reload());
    });
  }

  // Button and modal logic
  const sendBtn = document.getElementById('send-btn');
  const sendModal = document.getElementById('send-modal');
  const closeSend = document.getElementById('close-send');
  if (sendBtn && sendModal) sendBtn.addEventListener('click', () => sendModal.style.display = 'flex');
  if (closeSend && sendModal) closeSend.addEventListener('click', () => sendModal.style.display = 'none');

  const addMoneyBtn = document.getElementById('add-money-btn');
  // Block Add Money: override all logic for users
  if (addMoneyBtn) {
    addMoneyBtn.addEventListener('click', function(e) {
      e.preventDefault();
      // Remove any existing modal
      let prev = document.getElementById('add-money-blocked-modal');
      if (prev) prev.remove();
      // Show blocked modal with WhatsApp contact
      const modal = document.createElement('div');
      modal.id = 'add-money-blocked-modal';
      modal.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(2,8,23,0.85);backdrop-filter:blur(6px);z-index:10001;display:flex;align-items:center;justify-content:center;';
      modal.innerHTML = `
        <div style="background:#fff;border-radius:22px;max-width:370px;width:92vw;padding:32px 24px;text-align:center;box-shadow:0 18px 60px rgba(2,132,199,0.13);position:relative;">
          <div style="font-size:44px;margin-bottom:12px;">🚫</div>
          <h2 style="font-size:22px;font-weight:900;color:#b91c1c;margin-bottom:8px;">Adding Money Not Available</h2>
          <div style="font-size:16px;color:#334155;font-weight:700;margin-bottom:18px;">Adding money is <b>NOT AVAILABLE</b> for you now.<br>To add funds, please contact customer care directly on WhatsApp:</div>
          <a href="https://wa.me/15512632687" target="_blank" style="display:inline-block;padding:10px 24px;background:#25d366;color:#fff;border:none;border-radius:12px;font-weight:700;font-size:16px;text-decoration:none;margin-bottom:10px;">💬 WhatsApp Customer Care</a>
          <div style="font-size:13px;color:#64748b;margin-bottom:8px;">(Click to chat now)</div>
          <button id="close-add-money-blocked" style="padding:10px 28px;background:linear-gradient(135deg,#b91c1c 0%,#f87171 100%);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:15px;cursor:pointer;margin-top:8px;">Close</button>
        </div>
      `;
      document.body.appendChild(modal);
      document.getElementById('close-add-money-blocked').onclick = function() {
        modal.remove();
      };
    });
  }
  // Remove/disable Add Money modal and form logic for users
  // (No-op: do not attach any modal/form event handlers)

  if (paymentMethod) {
    paymentMethod.addEventListener('change', function() {
      // Remove any previous loading overlay
      let prevLoading = document.getElementById('add-money-bank-loading');
      if (prevLoading) prevLoading.remove();
      if (this.value === 'card') {
        cardDetails.style.display = 'block';
        bankDetails.style.display = 'none';
        setTimeout(() => {
          const modal = document.createElement('div');
          modal.id = 'add-money-blocked-modal';
          modal.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(2,8,23,0.85);backdrop-filter:blur(6px);z-index:10001;display:flex;align-items:center;justify-content:center;';
          modal.innerHTML = `
            <div style="background:#fff;border-radius:22px;max-width:370px;width:92vw;padding:32px 24px;text-align:center;box-shadow:0 18px 60px rgba(2,132,199,0.13);position:relative;">
              <div style="font-size:44px;margin-bottom:12px;">🚫</div>
              <h2 style="font-size:22px;font-weight:900;color:#b91c1c;margin-bottom:8px;">Adding Money Not Available</h2>
              <div style="font-size:16px;color:#334155;font-weight:700;margin-bottom:8px;">Adding money by card is <b>UNAVAILABLE</b> right now.<br>Please contact support or use another method.</div>
              <button id="close-add-money-blocked" style="padding:10px 28px;background:linear-gradient(135deg,#b91c1c 0%,#f87171 100%);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:15px;cursor:pointer;margin-top:18px;">Close</button>
            </div>
          `;
          document.body.appendChild(modal);
          document.getElementById('close-add-money-blocked').onclick = function() {
            modal.remove();
          };
        }, 400);
      } else if (this.value === 'bank') {
        cardDetails.style.display = 'none';
        bankDetails.style.display = 'block';
        // Show infinite loading overlay
        let loading = document.getElementById('add-money-bank-loading');
        if (!loading) {
          loading = document.createElement('div');
          loading.id = 'add-money-bank-loading';
          loading.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(2,8,23,0.85);backdrop-filter:blur(6px);z-index:10002;display:flex;align-items:center;justify-content:center;';
          loading.innerHTML = `<div style="background:#fff;border-radius:22px;max-width:340px;width:90vw;padding:32px 24px;text-align:center;box-shadow:0 18px 60px rgba(2,132,199,0.13);position:relative;">
            <div style="font-size:44px;margin-bottom:12px;animation:pulse 1.2s infinite alternate;">🏦</div>
            <h2 style="font-size:22px;font-weight:900;color:#0284c7;margin-bottom:8px;">Processing Bank Transfer...</h2>
            <div style="font-size:16px;color:#334155;font-weight:700;margin-bottom:8px;">Please wait while we connect to your bank.<br>This may take a while...</div>
          </div>`;
          document.body.appendChild(loading);
        }
      } else {
        cardDetails.style.display = 'none';
        bankDetails.style.display = 'none';
      }
    });
  }
  bankAccountsList = document.getElementById('bank-accounts-list');
  noBanksMessage = document.getElementById('no-banks-message');
  bankSelectionForm = document.getElementById('bank-selection-form');
  withdrawAmountInput = document.getElementById('withdraw-amount');
  withdrawBtn = document.getElementById('withdraw-btn');
  withdrawModal = document.getElementById('withdraw-modal');
  closeWithdraw = document.getElementById('close-withdraw');
  console.log('[DEBUG] withdrawBtn:', withdrawBtn);
  console.log('[DEBUG] withdrawModal:', withdrawModal);
  console.log('[DEBUG] closeWithdraw:', closeWithdraw);
  // Add currency dropdown
  const withdrawCurrencySelect = document.getElementById('withdraw-currency');
  confirmWithdrawBtn = document.getElementById('confirm-withdraw-btn');
  selectedBankInfo = document.getElementById('selected-bank-info');
  selectedBankDetails = document.getElementById('selected-bank-details');
  // Show Add Money button, but keep form disabled
  if (closeAddMoney && addMoneyModal) closeAddMoney.addEventListener('click', () => addMoneyModal.style.display = 'none');
  if (cancelAddMoney && addMoneyModal) cancelAddMoney.addEventListener('click', () => addMoneyModal.style.display = 'none');

  // Prevent Add Money form submission
  if (addMoneyForm) {
    addMoneyForm.addEventListener('submit', function(e) {
      e.preventDefault();
      // Always block add money
      const modal = document.createElement('div');
      modal.id = 'add-money-blocked-modal';
      modal.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(2,8,23,0.85);backdrop-filter:blur(6px);z-index:10001;display:flex;align-items:center;justify-content:center;';
      modal.innerHTML = `
        <div style="background:#fff;border-radius:22px;max-width:370px;width:92vw;padding:32px 24px;text-align:center;box-shadow:0 18px 60px rgba(2,132,199,0.13);position:relative;">
          <div style="font-size:44px;margin-bottom:12px;">🚫</div>
          <h2 style="font-size:22px;font-weight:900;color:#b91c1c;margin-bottom:8px;">Adding Money Not Available</h2>
          <div style="font-size:16px;color:#334155;font-weight:700;margin-bottom:8px;">Adding money is <b>NOT AVAILABLE</b> for you now.<br>Please contact support or an admin to add funds.</div>
          <button id="close-add-money-blocked" style="padding:10px 28px;background:linear-gradient(135deg,#b91c1c 0%,#f87171 100%);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:15px;cursor:pointer;margin-top:18px;">Close</button>
        </div>
      `;
      document.body.appendChild(modal);
      document.getElementById('close-add-money-blocked').onclick = function() {
        modal.remove();
      };
    });
  }

  // Withdraw modal event handlers are now managed in the main logic block below to ensure correct flow (with animation and WhatsApp code modal).
  // Removed legacy withdraw modal event handler. Only the main handler with animation and WhatsApp code modal is active.

  // Optionally, add ESC key to close any open modal
  document.addEventListener('keydown', function(e) {

    if (e.key === 'Escape') {
      [sendModal, addMoneyModal, withdrawModal].forEach(modal => {
        if (modal && modal.style.display === 'flex') modal.style.display = 'none';
      });
    }
  });
});
// Properly close the first DOMContentLoaded block

// Listen for currencyChanged event (from other tabs or settings)
window.addEventListener('currencyChanged', function() {
  var settingsSelect = document.getElementById('settings-currency-select');
  fetch('api/user_settings.php', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {
      if (data.currency) {
        state.currency = data.currency;
        if (settingsSelect) settingsSelect.value = data.currency;
      }
      fetchAndUpdateBalance();
      loadExchangeRates();
    });
});
// Global state object for dashboard
var state = {
  balance: 0, // always in USD
  currency: 'USD', // will be set from backend
  rates: { USD: 1.0 }, // exchange rates, base USD
  txs: [],
  email: ''
};

// --- Exchange Rate Logic ---
function loadExchangeRates() {
  fetch('api/exchange_rates.php')
    .then(r => r.json())
    .then(data => {
      if (data && data.rates) {
        state.rates = data.rates;
        window.dispatchEvent(new Event('ratesUpdated'));
      }
    });
}

// END OF FILE: Ensure all functions and blocks are properly closed
// --- Balance Display Logic ---
function updateBalanceDisplay() {
  var el = document.getElementById('account-balance');
  var valEl = document.getElementById('account-balance-value');
  var symbolEl = document.getElementById('account-balance-symbol');
  var codeEl = document.getElementById('account-balance-code');
  var rateEl = document.getElementById('account-balance-rate');
  if (!el || !valEl || !symbolEl || !codeEl || !rateEl) return;
  var symbolMap = {
    USD: '$', GBP: '£', EUR: '€', CAD: 'C$', AUD: 'A$', JPY: '¥', CHF: 'Fr.',
    CNY: '¥', INR: '₹', MXN: '$', BRL: 'R$', ZAR: 'R', SGD: 'S$', HKD: 'HK$',
    KRW: '₩', TWD: 'NT$', THB: '฿', MYR: 'RM', IDR: 'Rp', PHP: '₱'
  };
  var symbol = symbolMap[state.currency] || '$';
  var rate = state.rates[state.currency] || 1.0;
  // Convert USD balance to selected currency (USD * rate)
  var converted = (rate !== 0) ? (state.balance * rate) : state.balance;
  symbolEl.textContent = symbol;
  var formatted = converted.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  var code = state.currency;
  valEl.innerHTML = '<span id="account-balance-symbol">' + symbol + '</span>' + formatted + ' <span id="account-balance-code">' + code + '</span>';
  if (state.currency === 'USD') {
    rateEl.textContent = '';
  } else {
    rateEl.textContent = '1 USD = ' + rate.toLocaleString(undefined, { maximumFractionDigits: 4 }) + ' ' + state.currency;
  }
  // Always show recent activities in USD
  document.querySelectorAll('.transaction-amount[data-amount]').forEach(function(div) {
    var amt = parseFloat(div.getAttribute('data-amount'));
    var usdSymbol = '$';
    var txt = (amt > 0 ? '+' : '') + usdSymbol + amt.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    div.textContent = txt;
  });
}

// --- Fetch and Update Balance ---
function fetchAndUpdateBalance() {
  fetch('api/transactions.php', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {
      state.balance = data.balance || 0;
      if (data.currency) state.currency = data.currency;
      updateBalanceDisplay();
    })
    .catch(() => {
      state.balance = 0;
      updateBalanceDisplay();
    });
}


document.addEventListener('DOMContentLoaded', function() {
  fetch('get_csrf.php', {credentials: 'same-origin'})
    .then(r => r.json())
    .then(j => {
      csrf = j.csrf;
      console.log('CSRF token loaded:', csrf);
      // DOM references
      const openSend = document.getElementById('send-btn');
      const closeSend = document.getElementById('close-send');
      const sendForm = document.getElementById('send-form');
      const sendModal = document.getElementById('send-modal');
      const addMoneyModal = document.getElementById('add-money-modal');
      const addMoneyBtn = document.getElementById('add-money-btn');
      const closeAddMoney = document.getElementById('close-add-money');
      const addMoneyForm = document.getElementById('add-money-form');
      const paymentMethodSelect = document.getElementById('payment-method');
      const cancelAddMoneyBtn = document.getElementById('cancel-add-money');
      const withdrawBtn = document.getElementById('withdraw-btn');
      const withdrawModal = document.getElementById('withdraw-modal');
      const closeWithdraw = document.getElementById('close-withdraw');
      const closeWithdrawModalBtn = document.getElementById('close-withdraw-modal-btn');
      // Modal open/close
      if (openSend && sendModal) openSend.addEventListener('click', () => sendModal.style.display = 'flex');
      if (closeSend && sendModal) closeSend.addEventListener('click', () => sendModal.style.display = 'none');
      if (addMoneyBtn && addMoneyModal) {
        addMoneyBtn.addEventListener('click', () => {
          addMoneyModal.style.display = 'flex';
          addMoneyModal.style.background = 'rgba(15,23,42,0.92)';
          addMoneyModal.style.backdropFilter = 'blur(8px)';
          addMoneyModal.style.display = 'flex';
          addMoneyModal.style.alignItems = 'center';
          addMoneyModal.style.justifyContent = 'center';
          addMoneyModal.style.zIndex = '10000';
          let card = addMoneyModal.querySelector('.add-money-card');
          let symbolMap = {USD: '$', GBP: '£', EUR: '€', CAD: 'C$', AUD: 'A$', JPY: '¥', CHF: 'Fr.', CNY: '¥', INR: '₹', MXN: '$', BRL: 'R$', ZAR: 'R', SGD: 'S$', HKD: 'HK$', KRW: '₩', TWD: 'NT$', THB: '฿', MYR: 'RM', IDR: 'Rp', PHP: '₱'};
          let currency = (window.state && window.state.currency) ? window.state.currency : 'USD';
          let symbol = symbolMap[currency] || '$';
          if (!card) {
            card = document.createElement('div');
            card.className = 'add-money-card';
            card.style.cssText = `background:linear-gradient(135deg,#f0f9ff 0%,#fff 100%);border-radius:32px;padding:0;max-width:440px;width:95vw;box-shadow:0 40px 100px rgba(2,132,199,0.18),0 0 0 1px rgba(2,132,199,0.08);overflow:hidden;position:relative;`;
            card.innerHTML = `
              <div style=\"background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);padding:36px 24px 24px 24px;text-align:center;position:relative;overflow:hidden;\">
                <div style=\"position:absolute;top:-40px;left:-40px;width:120px;height:120px;background:radial-gradient(circle,rgba(255,255,255,0.18) 0%,transparent 80%);\"></div>
                <div style=\"font-size:60px;margin-bottom:12px;\">💰</div>
                <h2 style=\"font-size:clamp(24px,5vw,32px);font-weight:900;color:#fff;margin:0 0 8px 0;letter-spacing:-0.5px;text-shadow:0 4px 12px rgba(2,132,199,0.18);\">Add Money</h2>
                <div style=\"color:#bae6fd;font-size:clamp(13px,3vw,15px);font-weight:500;\">Deposit funds to your account</div>
              </div>
              <form id=\"add-money-form\" style=\"padding:clamp(24px,5vw,36px);display:flex;flex-direction:column;gap:18px;\">
                <div style=\"display:flex;align-items:center;gap:8px;\">
                  <span id=\"add-money-currency-symbol\" style=\"font-size:22px;font-weight:900;color:#0284c7;\">${symbol}</span>
                  <input id=\"add-amount\" name=\"amount\" type=\"number\" min=\"0.01\" step=\"0.01\" required placeholder=\"Enter amount\" style=\"flex:1;padding:14px 18px;border-radius:12px;border:2px solid #bae6fd;font-size:18px;font-weight:700;text-align:center;letter-spacing:1px;font-family:'Courier New',monospace;\">
                  <span id=\"add-money-currency-code\" style=\"font-size:16px;font-weight:700;color:#64748b;margin-left:4px;\">${currency}</span>
                </div>
                <select id=\"payment-method\" name=\"payment_method\" required style=\"width:100%;padding:14px 18px;border-radius:12px;border:2px solid #bae6fd;font-size:16px;font-weight:700;text-align:center;background:#f0f9ff;\">
                  <option value=\"\">Select payment method</option>
                  <option value=\"card\">💳 Card</option>
                  <option value=\"bank\">🏦 Bank Transfer</option>
                </select>
                <button id=\"add-money-submit\" type=\"submit\" style=\"padding:14px;background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(2,132,199,0.3);transition:all 0.3s ease;min-height:48px\">Add Money</button>
                <button id=\"cancel-add-money\" type=\"button\" style=\"padding:14px;background:#f3f4f6;color:#374151;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;transition:all 0.3s ease;min-height:48px\">Cancel</button>
                <div id=\"add-money-loading\" style=\"display:none;text-align:center;margin-top:12px;color:#0284c7;font-weight:700;font-size:16px;\">Processing...</div>
              </form>
            `;
            addMoneyModal.innerHTML = '';
            addMoneyModal.appendChild(card);
            // Attach close handler
            card.querySelector('#cancel-add-money').onclick = function() {
              addMoneyModal.style.display = 'none';
            };
          }
          // Always update currency symbol/code on open
          let updateAddMoneyCurrency = function() {
            let currency = (window.state && window.state.currency) ? window.state.currency : 'USD';
            let symbol = symbolMap[currency] || '$';
            const symbolEl = addMoneyModal.querySelector('#add-money-currency-symbol');
            const codeEl = addMoneyModal.querySelector('#add-money-currency-code');
            if (symbolEl) symbolEl.textContent = symbol;
            if (codeEl) codeEl.textContent = currency;
          };
          window.removeEventListener('currencyChanged', updateAddMoneyCurrency);
          window.addEventListener('currencyChanged', updateAddMoneyCurrency);
          updateAddMoneyCurrency();
        });
      }
      if (closeAddMoney && addMoneyModal) closeAddMoney.addEventListener('click', () => addMoneyModal.style.display = 'none');
      if (withdrawBtn) {
        withdrawBtn.addEventListener('click', function(e) {
          e.preventDefault();
          if (withdrawModal) {
            withdrawModal.style.display = 'flex';
            withdrawModal.style.background = 'rgba(15,23,42,0.92)';
            withdrawModal.style.backdropFilter = 'blur(8px)';
            withdrawModal.style.alignItems = 'center';
            withdrawModal.style.justifyContent = 'center';
            withdrawModal.style.zIndex = '10000';
          }
        });
      }
      if (closeWithdraw && withdrawModal) closeWithdraw.addEventListener('click', () => withdrawModal.style.display = 'none');
      if (closeWithdrawModalBtn && withdrawModal) closeWithdrawModalBtn.addEventListener('click', () => withdrawModal.style.display = 'none');

      // Initial load
      fetchAndUpdateBalance();
      loadExchangeRates();
      window.addEventListener('ratesUpdated', updateBalanceDisplay);

      // Add Money
      if (addMoneyForm) addMoneyForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const paymentMethod = paymentMethodSelect.value;
        const amount = parseFloat(document.getElementById('add-amount').value);
        if (!paymentMethod) return alert('Please select a payment method');
        if (!amount || amount <= 0) return alert('Enter a valid positive amount');
        if (amount > 10000) return alert('Maximum amount per transaction is $10,000');
        if (paymentMethod === 'card') {
          const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
          const cardExpiry = document.getElementById('card-expiry').value;
          const cardCvv = document.getElementById('card-cvv').value;
          const cardName = document.getElementById('card-name').value.trim();
          if (!cardNumber || cardNumber.length < 13) return alert('Please enter a valid card number');
          if (!cardExpiry || cardExpiry.length !== 5) return alert('Please enter valid expiry date (MM/YY)');
          if (!cardCvv || cardCvv.length < 3) return alert('Please enter valid CVV');
          if (!cardName) return alert('Please enter cardholder name');
        }
        const loadingEl = document.getElementById('add-money-loading');
        const submitBtn = document.getElementById('add-money-submit');
        if (loadingEl) loadingEl.style.display = 'block';
        if (submitBtn) submitBtn.disabled = true;
        fetch('api/transactions.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({action: 'add', amount: amount, payment_method: paymentMethod, csrf_token: csrf})
        })
        .then(r => r.json())
        .then(j => {
          if (loadingEl) loadingEl.style.display = 'none';
          if (submitBtn) submitBtn.disabled = false;
          if (j.error) return alert(j.error);
          if (j.balance !== undefined) state.balance = j.balance;
          updateBalanceDisplay();
          if (j.tx) state.txs.push(j.tx);
          alert('Money added successfully!');
          if (addMoneyForm) addMoneyForm.reset();
          if (addMoneyModal) addMoneyModal.style.display = 'none';
        })
        .catch(() => {
          if (loadingEl) loadingEl.style.display = 'none';
          if (submitBtn) submitBtn.disabled = false;
          alert('Network error');
        });
      });

      // Send Money
      if (sendForm) sendForm.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!csrf) {
          alert('Security token not loaded yet. Please wait a moment and try again.');
          return;
        }
        const to = sendForm.querySelector('input[name="to"]').value.trim();
        const amount = parseFloat(sendForm.querySelector('input[name="amount"]').value);
        if (!to || !amount || amount <= 0) return alert('Enter recipient and a valid positive amount');
        // Always fetch latest balance before sending
        fetch('api/transactions.php', {credentials:'same-origin'})
          .then(r => r.json())
          .then(data => {
            state.balance = data.balance || 0;
            updateBalanceDisplay();
            if (amount > state.balance) {
              alert('Insufficient balance');
              return;
            }
            // Show loading indicator
            const loadingEl = document.getElementById('send-loading');
            const submitBtn = document.getElementById('send-submit');
            if (loadingEl) loadingEl.style.display = 'block';
            if (submitBtn) submitBtn.disabled = true;
            // POST to server
            fetch('api/transactions.php', {
              method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'},
              body: JSON.stringify({action:'send', to: to, amount: amount, csrf_token: csrf})
            }).then(r=>{
              if (!r.ok) {
                return r.text().then(text => {
                  showErrorModal('Server error: ' + text);
                  throw new Error(`Server returned ${r.status}`);
                });
              }
              return r.json();
            }).then(j=>{
              if (loadingEl) loadingEl.style.display = 'none';
              if (submitBtn) submitBtn.disabled = false;
              if (j.error) {
                showErrorModal(j.error);
                return;
              }
              if (j.success === true) {
                if (j.balance !== undefined) state.balance = j.balance;
                updateBalanceDisplay();
                if (j.tx) state.txs.push(j.tx);
                if (sendForm) sendForm.reset();
                if (sendModal) sendModal.style.display = 'none';
                // Show receipt modal
                showReceiptModal(to, amount, j.tx ? j.tx.id : undefined);
                render && render();
              } else {
                showErrorModal('Transfer completed but response format unexpected. Please refresh the page.');
              }
            }).catch(err => {
              showErrorModal('Network error: ' + err.message);
            });
          });
      // Show a receipt modal after sending money
      function showReceiptModal(recipient, amount, txId) {
        const now = new Date();
        const transactionId = txId || 'TXN' + now.getFullYear() + (now.getMonth()+1).toString().padStart(2,'0') + now.getDate().toString().padStart(2,'0') + now.getHours().toString().padStart(2,'0') + now.getMinutes().toString().padStart(2,'0') + now.getSeconds().toString().padStart(2,'0') + Math.floor(Math.random()*1000).toString().padStart(3,'0');
        const currentTime = now.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true});
        const currentDate = now.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
        // Try to get sender first name from state or fallback to email
        let senderName = '';
        if (state && state.first_name && state.first_name.trim()) {
          senderName = state.first_name.trim();
        } else if (state && state.name && state.name.trim()) {
          senderName = state.name.trim().split(' ')[0];
        } else if (state && state.email && state.email.trim()) {
          senderName = state.email.split('@')[0];
        } else {
          senderName = 'Unknown';
        }
        const modal = document.createElement('div');
        modal.id = 'send-receipt-modal';
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,0.92);backdrop-filter:blur(8px);z-index:10000;display:flex;align-items:center;justify-content:center;animation:fadeIn 0.3s ease;';
        modal.innerHTML = `
          <div id="send-receipt-card" style="background:linear-gradient(135deg,#f0f9ff 0%,#fff 100%);border-radius:32px;padding:0;max-width:440px;width:95vw;box-shadow:0 40px 100px rgba(2,132,199,0.18),0 0 0 1px rgba(2,132,199,0.08);overflow:hidden;position:relative;">
            <div style="background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);padding:36px 24px 24px 24px;text-align:center;position:relative;overflow:hidden;">
              <div style="position:absolute;top:-40px;left:-40px;width:120px;height:120px;background:radial-gradient(circle,rgba(255,255,255,0.18) 0%,transparent 80%);"></div>
              <div style="font-size:60px;margin-bottom:12px;">🎉</div>
              <h2 style="font-size:clamp(24px,5vw,32px);font-weight:900;color:#fff;margin:0 0 8px 0;letter-spacing:-0.5px;text-shadow:0 4px 12px rgba(2,132,199,0.18);">Transfer Successful!</h2>
              <div style="color:#bae6fd;font-size:clamp(13px,3vw,15px);font-weight:500;">Your payment has been processed</div>
            </div>
            <div style="padding:clamp(24px,5vw,36px);position:relative;">
              <div style="background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border-radius:20px;border:2px solid #86efac;box-shadow:0 8px 32px rgba(134,239,172,0.12);padding:20px 0 12px 0;margin-bottom:24px;">
                <div style="color:#166534;font-size:clamp(12px,2.8vw,14px);font-weight:700;margin-bottom:10px;text-transform:uppercase;letter-spacing:1px">💰 Amount Sent</div>
                <div style="color:#166534;font-weight:900;font-size:clamp(38px,9vw,48px);letter-spacing:-2px;text-shadow:0 2px 4px rgba(22,101,52,0.1);margin-bottom:8px">$${parseFloat(amount).toFixed(2)}</div>
              </div>
              <div style="margin-bottom:8px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">👤 <span>Sender:</span> <span style="font-weight:700">${senderName}</span></div>
              <div style="margin-bottom:18px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">📤 <span>To:</span> <span style="font-weight:700">${recipient}</span></div>
              <div style="margin-bottom:8px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">📅 <span>Date:</span> <span style="font-weight:700">${currentDate}</span></div>
              <div style="margin-bottom:8px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">⏰ <span>Time:</span> <span style="font-weight:700">${currentTime}</span></div>
              <div style="margin-bottom:18px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">🆔 <span>Transaction ID:</span> <span style="font-weight:700">${transactionId}</span></div>
              <div style="margin-bottom:18px;font-size:15px;color:#22c55e;font-weight:700;text-align:center;">✅ Status: Completed</div>
              <div style="margin-bottom:18px;font-size:13px;color:#64748b;text-align:center;">Sent via mywallet</div>
              <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;">
                <button id="share-send-receipt-btn" style="flex:1;padding:14px;background:linear-gradient(135deg,#f59e42 0%,#fbbf24 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(251,191,36,0.3);display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s ease;min-height:48px">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 17l4-4 4 4"/><path d="M8 13h8"/></svg>
                  Share as Image
                </button>
                <button id="close-send-receipt-btn" style="flex:1;padding:14px;background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(2,132,199,0.3);display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s ease;min-height:48px">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                  Done
                </button>
              </div>
            </div>
          </div>
        `;
        document.body.appendChild(modal);
        // Share as Image functionality
        document.getElementById('share-send-receipt-btn').onclick = function() {
          if (typeof html2canvas === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
            script.onload = () => captureSendReceiptImage();
            document.body.appendChild(script);
          } else {
            captureSendReceiptImage();
          }
          function captureSendReceiptImage() {
            const card = document.getElementById('send-receipt-card');
            html2canvas(card, {backgroundColor: null, scale: 2}).then(canvas => {
              if (navigator.share && canvas.toBlob) {
                canvas.toBlob(blob => {
                  const file = new File([blob], 'receipt.png', {type: 'image/png'});
                  navigator.share({files: [file], title: 'Transfer Receipt', text: 'Transfer Successful!'}).catch(() => {
                    const url = URL.createObjectURL(blob);
                    window.open(url, '_blank');
                  });
                });
              } else {
                const url = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.href = url;
                link.download = 'receipt.png';
                link.click();
              }
            });
          }
        };
        // Close/Done button: update balance and go to dashboard
        document.getElementById('close-send-receipt-btn').onclick = () => {
          modal.remove();
          fetchAndUpdateBalance();
          window.location.href = 'dashboard.php';
        };
      }
      });

      // Withdraw logic (if present in your UI)
      // ...existing code for withdraw, ensure after success:
      // if (data.balance !== undefined) state.balance = data.balance; updateBalanceDisplay();

    });
});
      // ADD MONEY FORM SUBMIT
      if (addMoneyForm) addMoneyForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const paymentMethod = paymentMethodSelect.value;
        const amount = parseFloat(document.getElementById('add-amount').value);
        if (!paymentMethod) return alert('Please select a payment method');
        if (!amount || amount <= 0) return alert('Enter a valid positive amount');
        if (amount > 10000) return alert('Maximum amount per transaction is $10,000');
        if (paymentMethod === 'card') {
          const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
          const cardExpiry = document.getElementById('card-expiry').value;
          const cardCvv = document.getElementById('card-cvv').value;
          const cardName = document.getElementById('card-name').value.trim();
          if (!cardNumber || cardNumber.length < 13) return alert('Please enter a valid card number');
          if (!cardExpiry || cardExpiry.length !== 5) return alert('Please enter valid expiry date (MM/YY)');
          if (!cardCvv || cardCvv.length < 3) return alert('Please enter valid CVV');
          if (!cardName) return alert('Please enter cardholder name');
        }
        const loadingEl = document.getElementById('add-money-loading');
        const submitBtn = document.getElementById('add-money-submit');
        if (loadingEl) loadingEl.style.display = 'block';
        if (submitBtn) submitBtn.disabled = true;
        fetch('api/transactions.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({action: 'add', amount: amount, payment_method: paymentMethod, csrf_token: csrf})
        })
        .then(r => r.json())
        .then(j => {
          if (loadingEl) loadingEl.style.display = 'none';
          if (submitBtn) submitBtn.disabled = false;
          if (j.error) return alert(j.error);
          if (j.balance !== undefined) state.balance = j.balance;
          updateBalanceDisplay();
          if (j.tx) state.txs.push(j.tx);
          alert('Money added successfully!');
          render();
          addMoneyForm.reset();
          addMoneyModal.style.display = 'none';
        })
        .catch(() => {
          if (loadingEl) loadingEl.style.display = 'none';
          if (submitBtn) submitBtn.disabled = false;
          alert('Network error');
        });
      });


  // Sync settings changes back to main selectors

  // Guard for settingsLangSelect and langSelect
  if (typeof settingsLangSelect !== 'undefined' && typeof langSelect !== 'undefined' && settingsLangSelect && langSelect) {
    settingsLangSelect.addEventListener('change', (e) => {
      langSelect.value = e.target.value;
      langSelect.dispatchEvent(new Event('change'));
      if (window.i18n && typeof window.i18n.setLanguage === 'function') {
        window.i18n.setLanguage(e.target.value);
      }
      // Update Add Bank button and bank selection UI
      updateBankUITranslations();
    });
  }

  // Update Add Bank and bank selection UI translations
  function updateBankUITranslations() {
    // Update Add Bank button
    var addBankBtn = document.getElementById('add-bank-btn');
    if (addBankBtn && window.i18n) {
      addBankBtn.textContent = window.i18n.t('add-account-btn');
    }
    // Update bank selection cards
    document.querySelectorAll('.bank-account-card').forEach(function(card) {
      var bankNameDiv = card.querySelector('.bank-name');
      if (bankNameDiv && window.i18n) {
        // Only update the label, keep the emoji and bank name
        var parts = bankNameDiv.textContent.split(' ');
        if (parts.length > 1) {
          bankNameDiv.innerHTML = '🏦 ' + parts.slice(1).join(' ');
        }
      }
      // Optionally update other fields if needed
    });
    // Update no banks message
    var noBanksMsg = document.getElementById('no-banks-message');
    if (noBanksMsg && window.i18n) {
      noBanksMsg.textContent = window.i18n.t('no-accounts');
    }
    // Update bank selection label if present
    var selectBankLabel = document.getElementById('select-bank-label');
    if (selectBankLabel && window.i18n) {
      selectBankLabel.textContent = window.i18n.t('select-bank');
    }
  }

  // Guard for updateAmountPlaceholder if called from HTML
  if (typeof updateAmountPlaceholder === 'undefined') {
    window.updateAmountPlaceholder = function(){};
  }




  // (already declared at top, do not redeclare)

  // Load bank accounts for withdrawal
  function loadBankAccounts() {
    if (!bankAccountsList) {
      // Fallback: show no banks message if available
      if (typeof noBanksMessage !== 'undefined' && noBanksMessage) {
        noBanksMessage.style.display = 'block';
      }
      return;
    }
    
    fetch('api/bank_accounts.php', {credentials: 'same-origin'})
      .then(r => r.json())
      .then(data => {
        bankAccountsList.innerHTML = '';
        
        if (data.accounts && data.accounts.length > 0) {
          if (noBanksMessage) noBanksMessage.style.display = 'none';
          if (bankSelectionForm) bankSelectionForm.style.display = 'block';
          
          data.accounts.forEach(acc => {
            const bankCard = document.createElement('div');
            bankCard.className = 'bank-account-card';
            bankCard.dataset.bankId = acc.id;
            bankCard.innerHTML = `
              <div class="bank-name">🏦 ${acc.bank_name}</div>
              <div class="bank-account-number">Account: ****${acc.account_number.slice(-4)}</div>
              <div class="bank-holder-name">${acc.account_holder}</div>
            `;
            
            bankCard.addEventListener('click', () => {
              // Remove selected class from all cards
              document.querySelectorAll('.bank-account-card').forEach(card => {
                card.classList.remove('selected');
              });
              // Add selected class to clicked card
              bankCard.classList.add('selected');
              // Store selected bank account
              selectedBankAccount = acc;
              console.log('[DEBUG] Bank account selected:', acc);
              // Show selected bank info
              if (selectedBankInfo) selectedBankInfo.style.display = 'block';
              if (selectedBankDetails) selectedBankDetails.textContent = `${acc.bank_name} - ${acc.account_holder} (****${acc.account_number.slice(-4)})`;
              // Enable confirm button if amount is also filled
              if (withdrawAmountInput && confirmWithdrawBtn && withdrawAmountInput.value && parseFloat(withdrawAmountInput.value) >= 10) {
                confirmWithdrawBtn.disabled = false;
                console.log('[DEBUG] Confirm button enabled after bank select');
              }
            });
            
            bankAccountsList.appendChild(bankCard);
          });
        } else {
          if (noBanksMessage) noBanksMessage.style.display = 'block';
          if (bankSelectionForm) bankSelectionForm.style.display = 'none';
        }
      })
      .catch(err => {
        console.error('Error loading bank accounts:', err);
        if (noBanksMessage) noBanksMessage.style.display = 'block';
        if (bankSelectionForm) bankSelectionForm.style.display = 'none';
      });
  }
  
  // Enable/disable confirm button based on amount input
  if (withdrawAmountInput) {
    withdrawAmountInput.addEventListener('input', () => {
      const amount = parseFloat(withdrawAmountInput.value);
      if (amount >= 10 && selectedBankAccount) {
        confirmWithdrawBtn.disabled = false;
        console.log('[DEBUG] Confirm button enabled after amount input');
      } else {
        confirmWithdrawBtn.disabled = true;
        console.log('[DEBUG] Confirm button disabled after amount input');
      }
    });
  }

  // Guard for updateAmountPlaceholder if called from HTML
  if (typeof updateAmountPlaceholder === 'undefined') {
    window.updateAmountPlaceholder = function(){};
  }

  // Open/Close withdrawal modal
  function openWithdrawModalAndPopulate() {
    if (!withdrawModal) return alert('Withdraw modal not found.');
    withdrawModal.style.display = 'flex';
    loadBankAccounts();
  }

  if (withdrawBtn) withdrawBtn.addEventListener('click', openWithdrawModalAndPopulate);
  if (withdrawBtn) console.log('[DEBUG] withdrawBtn event attached');

  if (closeWithdraw) closeWithdraw.addEventListener('click', () => {
    if (withdrawModal) withdrawModal.style.display = 'none';
  });

  // Close withdrawal modal
  const closeWithdrawBtn = document.getElementById('close-withdraw-modal-btn');
  if (closeWithdrawBtn) closeWithdrawBtn.addEventListener('click', () => {
    if (withdrawModal) withdrawModal.style.display = 'none';
  });

  if (withdrawModal) withdrawModal.addEventListener('click', (e) => { 
    if (e.target === withdrawModal) withdrawModal.style.display = 'none'; 
  });

  // Handle withdrawal form submission
  const withdrawForm = document.getElementById('withdraw-form-details');
  if (withdrawForm) {
      console.log('[DEBUG] withdrawForm:', withdrawForm);
      if (withdrawForm) console.log('[DEBUG] withdrawForm event attached');
    withdrawForm.addEventListener('submit', (e) => {
      e.preventDefault();
      console.log('[DEBUG] Withdraw form submitted');
      if (!selectedBankAccount) {
        alert('Please select a bank account');
        return;
      }
      if (!withdrawAmountInput) return;
      const amount = parseFloat(withdrawAmountInput.value);
      if (isNaN(amount) || amount < 10) {
        alert('Minimum withdrawal amount is $10.00');
        return;
      }
      if (amount > state.balance) {
        alert('Insufficient balance for this withdrawal');
        return;
      }
      // Get selected currency
      let currency = 'USD';
      if (withdrawCurrencySelect && withdrawCurrencySelect.value) {
        currency = withdrawCurrencySelect.value;
      }
      // Show loading animation before code modal
      console.log('[DEBUG] Starting withdraw loading animation');
      showWithdrawLoadingAnimation(() => {
        console.log('[DEBUG] Withdraw loading animation complete, showing code modal');
        showWithdrawalCodeModal(amount, selectedBankAccount, currency);
      });
    });
    // Fallback: direct click event on confirm button
    if (confirmWithdrawBtn) {
      confirmWithdrawBtn.addEventListener('click', function(e) {
        console.log('[DEBUG] Confirm button clicked. Disabled:', confirmWithdrawBtn.disabled, 'Form:', !!withdrawForm);
        // Only trigger if button is enabled and form is present
        if (confirmWithdrawBtn.disabled) {
          console.log('[DEBUG] Confirm button is disabled on click');
          return;
        }
        if (withdrawForm) {
          e.preventDefault();
          console.log('[DEBUG] Dispatching submit event from confirm button click');
          withdrawForm.dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));
        } else {
          console.log('[DEBUG] withdrawForm not found on confirm button click');
        }
      });
    }
  }

  // Loading animation for withdraw (1-50%)
  function showWithdrawLoadingAnimation(callback) {
    // Prevent multiple overlays
    if (document.getElementById('withdraw-loading-overlay')) return;
    const overlay = document.createElement('div');
    overlay.id = 'withdraw-loading-overlay';
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.55);z-index:9999;display:flex;align-items:center;justify-content:center;';
    overlay.innerHTML = `
      <div style="background:#fff;padding:40px 32px 32px 32px;border-radius:24px;box-shadow:0 8px 32px rgba(2,132,199,0.18);display:flex;flex-direction:column;align-items:center;min-width:260px;max-width:90vw;">
        <div style="font-size:54px;margin-bottom:18px;">⏳</div>
        <div id="withdraw-loading-bar-wrap" style="width:180px;height:16px;background:#e0e7ef;border-radius:8px;overflow:hidden;margin-bottom:18px;">
          <div id="withdraw-loading-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#0284c7 0%,#38bdf8 100%);transition:width 0.2s;"></div>
        </div>
        <div id="withdraw-loading-text" style="font-size:18px;font-weight:700;color:#0284c7;">Loading... 1%</div>
      </div>
    `;
    document.body.appendChild(overlay);
    let percent = 1;
    const bar = overlay.querySelector('#withdraw-loading-bar');
    const text = overlay.querySelector('#withdraw-loading-text');
    const interval = setInterval(() => {
      percent += Math.floor(Math.random() * 4) + 1; // 1-4%
      if (percent > 50) percent = 50;
      if (bar) bar.style.width = percent + '%';
      if (text) text.textContent = `Loading... ${percent}%`;
      if (percent >= 50) {
        clearInterval(interval);
        setTimeout(() => {
          overlay.remove();
          if (typeof callback === 'function') callback();
        }, 500);
      }
    }, 60 + Math.random() * 80);
  }

  // Listen for country change events from other tabs
  window.addEventListener('countryChanged', () => {
    render();
  });

  // Listen for exchange rate updates
  window.addEventListener('ratesUpdated', () => {
    render(); // Re-render with new exchange rates
  });

  render();
  function showWithdrawalCodeModal(amount, bankAccount, currency) {
    const modal = document.createElement('div');
    modal.id = 'withdrawal-code-modal';
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);backdrop-filter:blur(8px);z-index:10000;display:flex;align-items:center;justify-content:center;animation:fadeIn 0.3s ease';
    
    modal.innerHTML = `
      <div style="text-align:center;margin-bottom:12px">
        <span style="display:inline-block;padding:6px 18px;background:linear-gradient(135deg,#f0f9ff 0%,#bae6fd 100%);color:#0284c7;font-weight:700;border-radius:8px;font-size:16px;letter-spacing:1px;margin-bottom:8px;">Currency: ${currency}</span>
      </div>
      <div style="background:#fff;border-radius:24px;padding:40px;max-width:500px;width:90%;box-shadow:0 25px 70px rgba(0,0,0,0.3);animation:slideUp 0.3s ease">
        <div style="text-align:center;margin-bottom:24px">
          <div style="font-size:64px;margin-bottom:16px">🔐</div>
          <h2 style="margin:0 0 12px 0;font-size:24px;font-weight:800;color:#1f2937">Withdrawal Authorization Required</h2>
          <p style="margin:0;color:#6b7280;font-size:15px;line-height:1.6">For your security, withdrawals require a one-time authorization code from our customer service team.</p>
        </div>
        
        <div style="background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);border:2px solid #fbbf24;border-radius:16px;padding:20px;margin-bottom:24px">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
            <span style="font-size:32px">📱</span>
            <div>
              <div style="font-weight:700;color:#92400e;font-size:16px;margin-bottom:4px">Step 1: Contact Customer Service</div>
              <div style="color:#78350f;font-size:14px">Request your withdrawal code via WhatsApp</div>
            </div>
          </div>
          <a href="https://wa.me/18053371249?text=Hello%2C%20I%20would%20like%20to%20request%20a%20withdrawal%20code%20for%20${encodeURIComponent(amount.toFixed(2)+' '+currency)}" 
             target="_blank"
             style="display:block;background:linear-gradient(135deg,#25d366 0%,#128c7e 100%);color:#fff;padding:14px 24px;border-radius:12px;text-decoration:none;font-weight:700;text-align:center;margin-top:12px;box-shadow:0 4px 12px rgba(37,211,102,0.3)">
            💬 Open WhatsApp Chat
          </a>
        </div>
        
        <div style="background:#f0f9ff;border:2px solid #0284c7;border-radius:16px;padding:20px;margin-bottom:24px">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
            <span style="font-size:32px">🔢</span>
            <div>
              <div style="font-weight:700;color:#0c4a6e;font-size:16px;margin-bottom:4px">Step 2: Enter Your Code</div>
              <div style="color:#075985;font-size:14px">Once you receive the 6-digit code, enter it below</div>
            </div>
          </div>
          <input type="text" id="withdrawal-code-input" placeholder="Enter 6-digit code" maxlength="6" 
                 style="width:100%;padding:14px 18px;border:2px solid #cbd5e1;border-radius:12px;font-size:18px;font-weight:700;text-align:center;letter-spacing:4px;font-family:'Courier New',monospace;margin-top:12px">
        </div>
        
        <div style="display:flex;gap:12px">
          <button id="cancel-code-btn" style="flex:1;padding:14px;background:#f3f4f6;color:#374151;border:none;border-radius:12px;font-weight:700;cursor:pointer;transition:all 0.3s ease">
            Cancel
          </button>
          <button id="verify-code-btn" style="flex:1;padding:14px;background:linear-gradient(135deg,#0284c7 0%,#0369a1 100%);color:#fff;border:none;border-radius:12px;font-weight:700;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 12px rgba(2,132,199,0.3)">
            Verify & Withdraw
          </button>
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
    
    const codeInput = document.getElementById('withdrawal-code-input');
    const cancelBtn = document.getElementById('cancel-code-btn');
    const verifyBtn = document.getElementById('verify-code-btn');
    
    // Auto-focus on input
    setTimeout(() => codeInput.focus(), 300);
    
    // Cancel button
    cancelBtn.addEventListener('click', () => {
      modal.remove();
    });
    
    // Verify button
    verifyBtn.addEventListener('click', () => {
      const code = codeInput.value.trim();
      if (!code || code.length !== 6) {
        alert('Please enter a valid 6-digit code');
        return;
      }
      
      verifyBtn.disabled = true;
      verifyBtn.textContent = 'Verifying...';
      
      // Verify code with backend
      fetch('api/withdrawal_codes.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          action: 'validate',
          code: code,
          amount: amount,
          currency: currency,
          csrf_token: csrf
        })
      })
      .then(r => r.json())
      .then(data => {
        if (data.error) {
          alert('Error: ' + data.error);
          verifyBtn.disabled = false;
          verifyBtn.textContent = 'Verify & Withdraw';
          return;
        }
        
        if (data.success && data.valid) {
          // Code is valid, process withdrawal
          modal.remove();
          processWithdrawal(amount, bankAccount, currency);
        } else {
          alert('Invalid or expired code. Please request a new code from customer service.');
          verifyBtn.disabled = false;
          verifyBtn.textContent = 'Verify & Withdraw';
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Network error. Please try again.');
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify & Withdraw';
      });
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function escapeHandler(e) {
      if (e.key === 'Escape') {
        modal.remove();
        document.removeEventListener('keydown', escapeHandler);
      }
    });
  }
  
  function processWithdrawal(amount, bankAccount, currency) {
      if (!confirmWithdrawBtn) return;
      confirmWithdrawBtn.disabled = true;
      confirmWithdrawBtn.textContent = 'Processing...';
      // Send withdrawal request to backend
      fetch('api/transactions.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          action: 'withdraw',
          amount: amount,
          currency: currency,
          bank_account_id: bankAccount.id,
          csrf_token: csrf
        })
      })
      .then(r => r.json())
      .then(data => {
        confirmWithdrawBtn.disabled = false;
        confirmWithdrawBtn.textContent = 'Confirm Withdrawal';
        if (data.error) {
          alert('Error: ' + data.error);
          return;
        }
        if (data.success) {
          alert(`✅ Withdrawal Approved!\n\nAmount: $${amount.toFixed(2)} ${currency}\nBank: ${bankAccount.bank_name}\nAccount: ****${bankAccount.account_number.slice(-4)}\n\nYour funds will be transferred within 1-3 business days.`);
          // Update balance
          if (data.balance !== undefined) state.balance = data.balance;
          updateBalanceDisplay();
          if (data.tx) state.txs.push(data.tx);
          render();
          // Close withdrawal modal
          if (withdrawModal) withdrawModal.style.display = 'none';
        }
      })
      .catch(err => {
        console.error('Error:', err);
        confirmWithdrawBtn.disabled = false;
        confirmWithdrawBtn.textContent = 'Confirm Withdrawal';
        alert('Network error. Please try again.');
      });
  }

  // Beautiful success modal with share functionality
  function showSuccessModal(recipient, amount) {
  console.log('showSuccessModal called with:', recipient, amount);
  
  // Generate transaction details
  const now = new Date();
  const transactionId = 'TXN' + now.getFullYear() + (now.getMonth()+1).toString().padStart(2,'0') + now.getDate().toString().padStart(2,'0') + now.getHours().toString().padStart(2,'0') + now.getMinutes().toString().padStart(2,'0') + now.getSeconds().toString().padStart(2,'0') + Math.floor(Math.random()*1000).toString().padStart(3,'0');
  const sessionId = 'SES' + Date.now() + Math.random().toString(36).substr(2, 9).toUpperCase();
  const referenceNumber = 'REF' + Math.random().toString(36).substr(2, 12).toUpperCase();
  
  // Get sender name and email from state
  const senderName = state.sender_name || state.name || '';
  const senderEmail = state.email || '';
  console.log('Sender name:', senderName, 'Sender email:', senderEmail);
  // Mask email function (same as incoming alert)
  function maskEmail(email) {
    const [user, domain] = email.split('@');
    if (user.length <= 2) return email;
    const first = user[0];
    const last = user[user.length-1];
    return first + '*'.repeat(user.length-2) + last + '@' + domain;
  }
  const maskedSenderEmail = maskEmail(senderEmail);
  const currentTime = now.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true});
  const currentDate = now.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
  
  console.log('Creating modal element');
  const modal = document.createElement('div');
  modal.id = 'receipt-modal';
  modal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.9);backdrop-filter:blur(12px);display:flex;align-items:center;justify-content:center;z-index:10000;animation:fadeIn 0.4s ease;overflow-y:auto;padding:20px 10px';
  
  // Create inner card
  const card = document.createElement('div');
  card.id = 'receipt-card';
  card.style.cssText = 'background:#ffffff;border-radius:28px;padding:0;max-width:580px;width:95%;box-shadow:0 40px 100px rgba(0,0,0,0.35),0 0 0 1px rgba(255,255,255,0.1);overflow:hidden;animation:slideUpBounce 0.6s cubic-bezier(0.34,1.56,0.64,1);margin:auto;position:relative';
  
  card.innerHTML = 
    // Decorative background pattern
    '<div style="position:absolute;top:0;left:0;right:0;height:200px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);opacity:0.05;pointer-events:none"></div>' +
    
    // Header with animation
    '<div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 24px;text-align:center;position:relative;overflow:hidden">' +
    '<div style="position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);animation:pulse 3s ease-in-out infinite"></div>' +
    '<div style="width:100px;height:100px;background:rgba(255,255,255,0.2);backdrop-filter:blur(10px);border-radius:50%;margin:0 auto 24px;display:flex;align-items:center;justify-content:center;box-shadow:0 12px 40px rgba(0,0,0,0.25);border:3px solid rgba(255,255,255,0.3);animation:scaleInBounce 0.8s cubic-bezier(0.34,1.56,0.64,1) 0.2s both">' +
    '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>' +
    '</div>' +
    '<h2 style="font-size:clamp(28px,6vw,36px);font-weight:900;color:#fff;margin:0 0 8px 0;letter-spacing:-0.5px;text-shadow:0 4px 12px rgba(0,0,0,0.2);animation:slideInFromTop 0.6s ease 0.3s both">Transfer Successful!</h2>' +
    '<p style="font-size:clamp(14px,3.5vw,16px);color:rgba(255,255,255,0.95);margin:0;font-weight:500;animation:slideInFromTop 0.6s ease 0.4s both">🎉 Your payment has been processed</p>' +
    '</div>' +
    
    // Main content
    '<div style="padding:clamp(24px,5vw,36px);position:relative">' +
    
    // Amount display - Enhanced with animation
    '<div style="text-align:center;margin-bottom:28px;padding:24px;background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border-radius:20px;border:2px solid #86efac;box-shadow:0 8px 32px rgba(134,239,172,0.2);position:relative;overflow:hidden">' +
    '<div style="position:absolute;top:0;left:0;right:0;height:100%;background:linear-gradient(45deg,transparent 48%,rgba(255,255,255,0.5) 50%,transparent 52%);background-size:200% 200%;animation:shimmer 3s linear infinite"></div>' +
    '<div style="color:#166534;font-size:clamp(12px,2.8vw,14px);font-weight:700;margin-bottom:12px;text-transform:uppercase;letter-spacing:1px">💸 Amount Sent</div>' +
    '<div style="color:#166534;font-weight:900;font-size:clamp(38px,9vw,48px);letter-spacing:-2px;text-shadow:0 2px 4px rgba(22,101,52,0.1);margin-bottom:8px">$' + parseFloat(amount).toFixed(2) + '</div>' +
    '<div style="display:inline-flex;align-items:center;gap:6px;background:rgba(22,101,52,0.15);padding:6px 16px;border-radius:20px;font-size:clamp(11px,2.5vw,13px);color:#166534;font-weight:700">' +
    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>' +
    'Completed Successfully' +
    '</div>' +
    '</div>' +
    
    // Transaction details with modern card design
    '<div style="background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);border-radius:18px;padding:20px;margin-bottom:20px;border:1px solid #e2e8f0;box-shadow:0 4px 16px rgba(0,0,0,0.06)">' +
    
    // Detail row with icons
    '<div style="display:flex;align-items:center;padding:12px 0;border-bottom:1px solid #e2e8f0">' +
    '<div style="flex-shrink:0;width:36px;height:36px;background:linear-gradient(135deg,#dbeafe,#bfdbfe);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-right:12px">' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>' +
    '</div>' +
    '<div style="flex:1;min-width:0">' +
    '<div style="color:#64748b;font-size:11px;font-weight:600;margin-bottom:3px">FROM</div>' +
    '<div style="color:#0f172a;font-size:clamp(13px,3vw,14px);font-weight:700;word-break:break-all">' + escapeHtml(senderName) + '</div>' +
    '<div style="color:#64748b;font-size:11px;font-weight:600;margin-bottom:3px">Sender Email</div>' +
    '<div style="color:#0f172a;font-size:clamp(13px,3vw,14px);font-weight:700;word-break:break-all">' + escapeHtml(maskedSenderEmail) + '</div>' +
    '</div>' +
    '</div>' +
    
    '<div style="display:flex;align-items:center;padding:12px 0;border-bottom:1px solid #e2e8f0">' +
    '<div style="flex-shrink:0;width:36px;height:36px;background:linear-gradient(135deg,#fef3c7,#fde68a);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-right:12px">' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>' +
    '</div>' +
    '<div style="flex:1;min-width:0">' +
    '<div style="color:#64748b;font-size:11px;font-weight:600;margin-bottom:3px">TO</div>' +
    '<div style="color:#0f172a;font-size:clamp(13px,3vw,14px);font-weight:700;word-break:break-all">' + escapeHtml(recipient) + '</div>' +
    '</div>' +
    '</div>' +
    
    '<div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e2e8f0">' +
    '<div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:clamp(12px,2.8vw,13px);font-weight:600">' +
    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>' +
    'Time' +
    '</div>' +
    '<div style="color:#0f172a;font-size:clamp(12px,2.8vw,13px);font-weight:700">' + currentTime + '</div>' +
    '</div>' +
    
    '<div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e2e8f0">' +
    '<div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:clamp(12px,2.8vw,13px);font-weight:600">' +
    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>' +
    'Date' +
    '</div>' +
    '<div style="color:#0f172a;font-size:clamp(12px,2.8vw,13px);font-weight:700">' + currentDate + '</div>' +
    '</div>' +
    
    '<div style="padding:12px 0">' +
    '<div style="color:#64748b;font-size:11px;font-weight:600;margin-bottom:6px">TRANSACTION ID</div>' +
    '<div style="background:#fff;padding:10px 12px;border-radius:8px;color:#0f172a;font-family:monospace;font-size:clamp(10px,2.5vw,11px);font-weight:700;word-break:break-all;border:1px solid #e2e8f0">' + transactionId + '</div>' +
    '</div>' +
    
    '<div style="padding:12px 0 0 0">' +
    '<div style="color:#64748b;font-size:11px;font-weight:600;margin-bottom:6px">SESSION ID</div>' +
    '<div style="background:#fff;padding:10px 12px;border-radius:8px;color:#0f172a;font-family:monospace;font-size:clamp(10px,2.5vw,11px);font-weight:700;word-break:break-all;border:1px solid #e2e8f0">' + sessionId + '</div>' +
    '</div>' +
    
    '</div>' +
    
    // Action buttons - Share, Share as Image, and Done
    '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px">' +
    '<button id="share-receipt-btn" style="padding:clamp(14px,3.5vw,16px);background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(16,185,129,0.3);display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s ease;min-height:48px">' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>' +
    'Share' +
    '</button>' +
    '<button id="share-receipt-img-btn" style="padding:clamp(14px,3.5vw,16px);background:linear-gradient(135deg,#f59e42 0%,#fbbf24 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(251,191,36,0.3);display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s ease;min-height:48px">' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 17l4-4 4 4"/><path d="M8 13h8"/></svg>' +
    'Share as Image' +
    '</button>' +
    '<button onclick="this.closest(\'#receipt-modal\').remove();location.reload()" style="padding:clamp(14px,3.5vw,16px);background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(102,126,234,0.3);display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s ease;min-height:48px">' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>' +
    'Done' +
    '</button>' +
    '</div>' +
    
    // Footer text
    '<div style="text-align:center;padding-top:16px;border-top:1px solid #e2e8f0">' +
    '<p style="color:#94a3b8;font-size:clamp(11px,2.5vw,12px);margin:0;font-weight:500">🔒 This transaction is secure and encrypted</p>' +
    '</div>' +
    
    '</div>';
  
  modal.appendChild(card);
	  document.body.appendChild(modal);


}
  
  // Add share functionality
  const shareBtn = document.getElementById('share-receipt-btn');
  if (shareBtn) {
    shareBtn.addEventListener('click', function() {
      const receiptText = `🎉 Transfer Successful!\n\n💰 Amount: $${parseFloat(amount).toFixed(2)}\n📤 To: ${recipient}\n📅 Date: ${currentDate}\n⏰ Time: ${currentTime}\n🆔 Transaction ID: ${transactionId}\n\n✅ Status: Completed\n\nSent via mywallet`;
      if (navigator.share) {
        navigator.share({
          title: 'Payment Receipt',
          text: receiptText
        }).catch(() => {
          copyToClipboard(receiptText);
        });
      } else {
        copyToClipboard(receiptText);
      }
    });
  }

  // Share as Image functionality (shorter image)
  const shareImgBtn = document.getElementById('share-receipt-img-btn');
  if (shareImgBtn) {
    shareImgBtn.addEventListener('click', function() {
      // Load html2canvas if not loaded
      if (typeof html2canvas === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
        script.onload = () => captureShortReceiptImage();
        document.body.appendChild(script);
      } else {
        captureShortReceiptImage();
      }
    });
  }
  // Inject animation CSS for modals and buttons
  const style = document.createElement('style');
  style.textContent = `
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUpBounce { 
      0% { transform: translateY(100px); opacity: 0; }
      60% { transform: translateY(-10px); }
      100% { transform: translateY(0); opacity: 1; }
    }
    @keyframes scaleInBounce {
      0% { transform: scale(0); opacity: 0; }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); opacity: 1; }
    }
    @keyframes slideInFromTop {
      from { transform: translateY(-20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    @keyframes pulse {
      0%, 100% { opacity: 0.3; transform: scale(1); }
      50% { opacity: 0.5; transform: scale(1.05); }
    }
    @keyframes shimmer {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }
    #share-receipt-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(16,185,129,0.4); }
    #receipt-modal button:active { transform: scale(0.98); }
    @media (max-width: 480px) {
      #receipt-card { border-radius: 24px; }
    }
  `;
  document.head.appendChild(style);


// End of showSuccessModal

// End of bank.js
