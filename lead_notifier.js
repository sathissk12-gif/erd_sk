// lead_notifier.js
// Real-time Lead Notification System

(function() {
    let lastLeadId = localStorage.getItem('lastNotifiedLeadId') || 0;

    // 🔊 Notification Sound (Optional - can use a base64 beep or external URL)
    const playNotificationSound = () => {
        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        audio.play().catch(e => console.log("Sound blocked by browser"));
    };

    const showFullNotification = (lead) => {
        // Create Overlay
        const overlay = document.createElement('div');
        overlay.id = 'leadNotificationOverlay';
        overlay.style.cssText = `
            position: fixed; inset: 0; z-index: 99999;
            background: rgba(3, 7, 18, 0.95);
            backdrop-filter: blur(25px);
            display: flex; align-items: center; justify-content: center;
            padding: 25px; animation: fadeIn 0.5s ease-out;
        `;

        overlay.innerHTML = `
            <style>
                @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
                @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
                @keyframes pulseBlue { 0% { box-shadow: 0 0 0 0 rgba(24, 119, 242, 0.4); } 70% { box-shadow: 0 0 0 30px rgba(24, 119, 242, 0); } 100% { box-shadow: 0 0 0 0 rgba(24, 119, 242, 0); } }
                
                .notif-card {
                    width: 100%; max-width: 400px;
                    background: linear-gradient(135deg, #1e293b, #0f172a);
                    border: 1px solid #1877F2; border-radius: 35px;
                    padding: 40px 30px; text-align: center;
                    animation: slideUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    box-shadow: 0 25px 50px rgba(0,0,0,0.5);
                }
                .notif-icon {
                    width: 80px; height: 80px; background: #1877F2;
                    border-radius: 25px; display: flex; align-items: center; justify-content: center;
                    margin: 0 auto 25px; font-size: 35px; color: white;
                    animation: pulseBlue 2s infinite;
                }
                .notif-title { font-size: 12px; font-weight: 800; color: #1877F2; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
                .notif-name { font-size: 28px; font-weight: 800; margin-bottom: 5px; color: white; }
                .notif-phone { font-size: 18px; color: #94a3b8; margin-bottom: 30px; }
                
                .notif-btns { display: grid; gap: 12px; }
                .nbtn { padding: 16px; border-radius: 18px; border: none; font-weight: 800; font-size: 15px; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.2s; }
                .nbtn-call { background: #10b981; color: white; }
                .nbtn-view { background: #1877F2; color: white; }
                .nbtn-close { background: rgba(255,255,255,0.05); color: #94a3b8; margin-top: 10px; font-size: 12px; }
                .nbtn:active { transform: scale(0.95); }
            </style>

            <div class="notif-card">
                <div class="notif-icon"><i class="fa-brands fa-facebook-f"></i></div>
                <div class="notif-title">New Lead Detected!</div>
                <div class="notif-name">${lead.full_name || 'New Customer'}</div>
                <div class="notif-phone">${lead.phone_number || 'No phone'}</div>
                
                <div class="notif-btns">
                    <a href="tel:${lead.phone_number}" class="nbtn nbtn-call"><i class="fa-solid fa-phone"></i> CALL NOW</a>
                    <a href="meta_leads.php" class="nbtn nbtn-view"><i class="fa-solid fa-gauge-high"></i> VIEW DASHBOARD</a>
                    <button onclick="this.closest('#leadNotificationOverlay').remove()" class="nbtn nbtn-close">DISMISS</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        playNotificationSound();

        // Auto vibrate if supported
        if(window.navigator.vibrate) window.navigator.vibrate([200, 100, 200, 100, 500]);
    };

    const checkLeads = async () => {
        try {
            const res = await fetch('api_meta_leads.php?action=latest_unprocessed');
            const lead = await res.json();

            if (lead && !lead.none && lead.id != lastLeadId) {
                lastLeadId = lead.id;
                localStorage.setItem('lastNotifiedLeadId', lead.id);
                showFullNotification(lead);
            }
        } catch (e) {
            console.error("Lead Check Error:", e);
        }
    };

    // Start Polling (every 30 seconds)
    setInterval(checkLeads, 30000);
    
    // Initial check after 5 seconds
    setTimeout(checkLeads, 5000);

})();
