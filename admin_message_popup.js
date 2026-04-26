// admin_message_popup.js
// Show admin message to user once after login (no welcome message)

(function() {
    // Only run after DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[AdminMsgPopup] DOMContentLoaded');
        // Only show if not already shown this session
        if (sessionStorage.getItem('adminMessageShown')) {
            console.log('[AdminMsgPopup] adminMessageShown flag set, skipping');
            return;
        }
        console.log('[AdminMsgPopup] Fetching admin message...');
        fetch('api/check_messages.php')
            .then(res => res.json())
            .then(data => {
                console.log('[AdminMsgPopup] API response:', data);
                if (data.success && data.message) {
                    console.log('[AdminMsgPopup] Showing admin message modal:', data.message);
                    showAdminMessageModal(data.message);
                    sessionStorage.setItem('adminMessageShown', '1');
                } else {
                    console.log('[AdminMsgPopup] No unread admin message for user.');
                }
            })
            .catch(err => {
                console.error('[AdminMsgPopup] Error fetching admin message:', err);
            });
    });

    function showAdminMessageModal(message) {
        console.log('[AdminMsgPopup] Rendering admin message modal:', message);
        // Create modal overlay
        const modal = document.createElement('div');
        modal.id = 'adminMessageModal';
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;z-index:9999;';

        // Only show the selected message type (icon and badge)
        const type = message.message_type || 'info';
        let icon = 'ℹ️', badgeColor = '#2563eb', badgeBg = 'rgba(37,99,235,0.12)';
        let typeLabel = 'Info';
        if (type === 'warning') { icon = '⚠️'; badgeColor = '#f59e42'; badgeBg = 'rgba(245,158,66,0.12)'; typeLabel = 'Warning'; }
        if (type === 'alert')   { icon = '🚨'; badgeColor = '#dc2626'; badgeBg = 'rgba(220,38,38,0.12)'; typeLabel = 'Alert'; }
        if (type === 'promotion') { icon = '🎁'; badgeColor = '#10b981'; badgeBg = 'rgba(16,185,129,0.12)'; typeLabel = 'Promotion'; }
        const escaped = escapeHtml(message.message);

        // Always show the selected message from admin, with type badge and icon
        const messageHtml = `<div style=\"background:rgba(255,255,255,0.97);padding:24px 18px;border-radius:16px;margin-top:18px;box-shadow:inset 0 2px 8px rgba(0,0,0,0.05);\"><p style=\"color:#1f2937;font-size:17px;line-height:1.7;margin:0;white-space:pre-wrap;\">${escaped}</p></div>`;

        modal.innerHTML = `
            <div style=\"background:linear-gradient(135deg,#fff 0%,#f0f4ff 100%);padding:32px 24px 24px 24px;border-radius:24px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.18);border:3px solid ${badgeColor};\">
                <div style=\"display:flex;align-items:center;justify-content:center;gap:12px;flex-direction:column;\">
                    <span style=\"font-size:54px;\">${icon}</span>
                    <span style=\"display:inline-block;padding:10px 28px;border-radius:20px;font-size:20px;font-weight:800;background:${badgeBg};color:${badgeColor};letter-spacing:0.5px;box-shadow:0 2px 8px rgba(0,0,0,0.04);text-transform:uppercase;\">${typeLabel}</span>
                </div>
                ${messageHtml}
                <button id=\"closeAdminMessageBtn\" style=\"margin-top:32px;width:100%;padding:16px;background:${badgeBg};border:2px solid ${badgeColor};border-radius:12px;color:${badgeColor};font-size:16px;font-weight:700;cursor:pointer;transition:all 0.3s ease;\">Dismiss</button>
            </div>
        `;
        document.body.appendChild(modal);
        document.getElementById('closeAdminMessageBtn').onclick = function() {
            modal.remove();
            // Mark as read
            fetch('api/mark_message_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message_id: message.id })
            });
        };
    }
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
