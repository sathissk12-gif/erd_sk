// lead_notifier.js
// Real-time Lead Notification System v2.0 - With Configurable Sound

(function() {
    let lastLeadId = localStorage.getItem('lastNotifiedLeadId') || 0;
    let soundSettings = {
        enabled: true,
        soundUrl: 'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3',
        soundName: 'chime',
        vibration: true,
        vibrationPattern: 'standard'
    };

    // 🎵 Sound URL mapping based on configured sound names
    const SOUND_MAP = {
        'default': 'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3',
        'chime': 'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3',
        'bell': 'https://assets.mixkit.co/active_storage/sfx/2203/2203-preview.mp3',
        'notification': 'https://assets.mixkit.co/active_storage/sfx/2200/2200-preview.mp3',
        'alarm': 'https://assets.mixkit.co/active_storage/sfx/2204/2204-preview.mp3',
        'alert': 'https://assets.mixkit.co/active_storage/sfx/2201/2201-preview.mp3',
        'ringtone': 'https://assets.mixkit.co/active_storage/sfx/2202/2202-preview.mp3',
        'custom': ''
    };

    // 📳 Vibration patterns
    const VIBRATION_MAP = {
        'standard': [200, 100, 200],
        'double': [100, 100, 100],
        'long': [500],
        'rapid': [200, 100, 200, 100, 200, 100, 500],
        'heartbeat': [100, 200, 100, 500],
        'disabled': []
    };

    // 🔄 Fetch sound settings from server on startup
    const fetchSoundSettings = async () => {
        try {
            const res = await fetch('api_meta_leads.php?action=sound_settings');
            const settings = await res.json();
            if (settings) {
                soundSettings.enabled = settings.appt_sound_enabled !== '0';
                
                // Determine which sound to use
                const leadSound = settings.notification_sound_lead || settings.notification_sound || 'chime';
                soundSettings.soundName = leadSound;
                
                if (leadSound === 'custom' && settings.notification_custom_sound) {
                    soundSettings.soundUrl = settings.notification_custom_sound;
                } else if (SOUND_MAP[leadSound]) {
                    soundSettings.soundUrl = SOUND_MAP[leadSound];
                } else {
                    soundSettings.soundUrl = SOUND_MAP['chime'];
                }
                
                soundSettings.vibration = settings.notification_vibration !== '0';
                soundSettings.vibrationPattern = settings.notification_vibration_pattern || 'standard';
            }
        } catch (e) {
            console.warn("Could not fetch sound settings, using defaults:", e);
        }
    };

    // 🔊 Play notification sound based on settings
    const playNotificationSound = () => {
        if (!soundSettings.enabled) return;
        
        try {
            const audio = new Audio(soundSettings.soundUrl);
            audio.volume = 0.7;
            audio.play().catch(e => console.log("Sound blocked by browser"));
        } catch (e) {
            console.log("Sound play error:", e);
        }
    };

    // 📳 Vibrate based on settings
    const vibrateDevice = () => {
        if (!soundSettings.vibration || !window.navigator.vibrate) return;
        const pattern = VIBRATION_MAP[soundSettings.vibrationPattern] || VIBRATION_MAP['standard'];
        if (pattern.length > 0) {
            window.navigator.vibrate(pattern);
        }
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
        vibrateDevice();
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

    // ─── INIT ───
    // Fetch sound settings first, then start polling
    fetchSoundSettings().then(() => {
        console.log('🔊 Lead notifier sound:', soundSettings.soundName, soundSettings.enabled ? 'ENABLED' : 'DISABLED');
    });

    // Start Polling (every 30 seconds)
    setInterval(checkLeads, 30000);
    
    // Initial check after 5 seconds
    setTimeout(checkLeads, 5000);

})();
