<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Appointment Manager | SK LOGIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="theme_engine.js"></script>
    <style>
        :root {
            --primary: #8b5cf6;
            --secondary: #06b6d4;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #94a3b8;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --now-glow: rgba(239, 68, 68, 0.6);
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text);
            min-height: 100vh;
            padding: 20px;
        }

        .container { max-width: 900px; margin: 0 auto; }

        .glass-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 25px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            margin-bottom: 25px;
        }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 10px; }
        .title { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; }

        .stats-row {
            display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .stat-card {
            flex: 1; min-width: 100px; background: rgba(30, 41, 59, 0.3);
            border: 1px solid var(--border); border-radius: 14px; padding: 14px 18px;
            text-align: center;
        }
        .stat-card .num { font-size: 28px; font-weight: 800; }
        .stat-card .lbl { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .stat-card.urgent { border-color: var(--danger); }
        .stat-card.urgent .num { color: var(--danger); animation: pulse-red 1.5s infinite; }
        .stat-card.warning { border-color: var(--warning); }
        .stat-card.warning .num { color: var(--warning); }
        .stat-card.success { border-color: var(--success); }
        .stat-card.success .num { color: var(--success); }

        @keyframes pulse-red {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .filter-bar {
            display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 16px; border-radius: 99px; border: 1px solid var(--border);
            background: transparent; color: var(--text-muted); font-size: 12px; font-weight: 600;
            cursor: pointer; transition: 0.3s;
        }
        .filter-btn:hover { border-color: var(--primary); color: white; }
        .filter-btn.active { background: var(--primary); border-color: var(--primary); color: white; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .input-group { margin-bottom: 15px; }
        .input-group label {
            display: block; font-size: 11px; font-weight: 700; color: var(--text-muted);
            text-transform: uppercase; margin-bottom: 8px;
        }
        .input-field {
            width: 100%; padding: 14px 18px; background: rgba(15, 23, 42, 0.4);
            border: 1px solid var(--border); border-radius: 12px; color: white; font-size: 14px;
            transition: 0.3s;
        }
        .input-field:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 20px rgba(139, 92, 246, 0.15); }
        .input-field::placeholder { color: rgba(148, 163, 184, 0.5); }
        select.input-field { appearance: auto; }

        .btn {
            padding: 14px 25px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer;
            transition: 0.3s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #6366f1); color: white;
            box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 15px 25px rgba(139, 92, 246, 0.4); }
        .btn-success { background: var(--success); color: white; }
        .btn-success:hover { transform: translateY(-2px); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-sm { padding: 8px 14px; font-size: 12px; border-radius: 8px; }

        .appt-list { margin-top: 20px; }
        .appt-item {
            background: rgba(30, 41, 59, 0.3); border: 1px solid var(--border); border-radius: 16px;
            padding: 18px; margin-bottom: 12px; transition: 0.3s; position: relative; overflow: hidden;
        }
        .appt-item:hover { background: rgba(30, 41, 59, 0.5); border-color: var(--primary); }
        .appt-item.urgent-now {
            border-color: var(--danger); box-shadow: 0 0 30px rgba(239, 68, 68, 0.2);
            animation: border-pulse 2s infinite;
        }
        .appt-item.urgent-now::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
            background: var(--danger); animation: pulse-red 1.5s infinite;
        }
        .appt-item.urgent-soon { border-color: var(--warning); }
        .appt-item.urgent-soon::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
            background: var(--warning);
        }

        @keyframes border-pulse {
            0%, 100% { border-color: var(--danger); box-shadow: 0 0 20px rgba(239,68,68,0.2); }
            50% { border-color: #ff6b6b; box-shadow: 0 0 40px rgba(239,68,68,0.4); }
        }

        .appt-info h4 { font-size: 16px; font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
        .appt-info p { font-size: 12px; color: var(--text-muted); }
        .appt-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        .appt-status {
            font-size: 10px; font-weight: 800; text-transform: uppercase;
            padding: 4px 10px; border-radius: 99px;
        }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
        .status-completed { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-cancelled { background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
        .status-missed { background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); }

        .reminder-badge {
            font-size: 9px; background: var(--primary); color: white; padding: 2px 8px;
            border-radius: 4px; margin-left: 6px; font-weight: 600;
        }
        .now-badge {
            font-size: 10px; background: var(--danger); color: white; padding: 3px 10px;
            border-radius: 99px; animation: pulse-red 1s infinite; font-weight: 800;
        }

        /* ── ⏰ FULL-SCREEN NOTIFICATION OVERLAY ── */
        #fullscreenAlert {
            display: none;
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: radial-gradient(ellipse at center, #1e1b4b 0%, #000000 100%);
            z-index: 99999;
            justify-content: center; align-items: center;
            flex-direction: column;
            padding: 40px;
            animation: fs-fadein 0.3s ease-out;
        }
        #fullscreenAlert.active {
            display: flex;
        }

        @keyframes fs-fadein {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .fs-icon {
            font-size: 80px; margin-bottom: 20px;
            animation: fs-bell 1.5s infinite;
        }
        @keyframes fs-bell {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-15deg); }
            75% { transform: rotate(15deg); }
        }

        .fs-title {
            font-size: 48px; font-weight: 800; margin-bottom: 8px;
            background: linear-gradient(135deg, #ef4444, #f97316);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            animation: fs-glow 2s infinite;
        }
        @keyframes fs-glow {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.3); }
        }

        .fs-subtitle { font-size: 20px; color: var(--text-muted); margin-bottom: 30px; }

        .fs-details {
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            border-radius: 20px; padding: 30px 40px; margin-bottom: 30px; max-width: 500px; width: 100%;
            backdrop-filter: blur(10px);
        }
        .fs-details .row {
            display: flex; justify-content: space-between; padding: 10px 0;
            border-bottom: 1px solid var(--border); font-size: 16px;
        }
        .fs-details .row:last-child { border-bottom: none; }
        .fs-details .label { color: var(--text-muted); }
        .fs-details .value { font-weight: 700; }

        .fs-actions { display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; }
        .fs-btn {
            padding: 18px 40px; border-radius: 16px; border: none; font-weight: 800;
            font-size: 18px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 12px;
        }
        .fs-btn-primary {
            background: linear-gradient(135deg, var(--success), #059669); color: white;
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.3);
        }
        .fs-btn-primary:hover { transform: scale(1.05); }
        .fs-btn-secondary {
            background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--border);
        }
        .fs-btn-secondary:hover { background: rgba(255,255,255,0.2); }
        .fs-btn-snooze {
            background: rgba(245, 158, 11, 0.2); color: var(--warning); border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .fs-btn-snooze:hover { background: rgba(245, 158, 11, 0.3); }

        .fs-time {
            font-size: 14px; color: var(--text-muted); margin-top: 20px;
            display: flex; align-items: center; gap: 8px;
        }

        /* ── Voice toggle ── */
        .voice-indicator {
            position: fixed; bottom: 20px; right: 20px; z-index: 9999;
            background: rgba(15,23,42,0.8); border: 1px solid var(--border);
            border-radius: 99px; padding: 8px 16px; font-size: 12px;
            display: flex; align-items: center; gap: 6px; backdrop-filter: blur(10px);
        }

        /* ── Toast notifications ── */
        .toast-container {
            position: fixed; top: 20px; right: 20px; z-index: 99998;
            display: flex; flex-direction: column; gap: 10px;
        }
        .toast {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 14px; padding: 14px 20px; backdrop-filter: blur(20px);
            min-width: 280px; animation: toast-in 0.3s ease-out;
            display: flex; align-items: center; gap: 12px;
        }
        .toast.alert { border-color: var(--danger); }
        .toast.alert .toast-icon { color: var(--danger); }
        .toast.info { border-color: var(--primary); }
        .toast.info .toast-icon { color: var(--primary); }
        @keyframes toast-in {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .suggestion-box {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 100;
            background: #1e293b; border: 1px solid var(--border); border-radius: 12px;
            max-height: 200px; overflow-y: auto; display: none;
        }
        .suggestion-item {
            padding: 12px 16px; cursor: pointer; border-bottom: 1px solid var(--border);
            transition: 0.2s;
        }
        .suggestion-item:hover { background: rgba(139, 92, 246, 0.15); }
        .suggestion-item small { color: var(--text-muted); display: block; font-size: 11px; }

        @media (max-width: 600px) {
            .form-grid, .form-grid-3 { grid-template-columns: 1fr; }
            .fs-title { font-size: 32px; }
            .fs-details { padding: 20px; }
            .fs-btn { width: 100%; justify-content: center; }
            .stats-row { flex-direction: column; }
        }
    </style>
</head>
<body>

<!-- ─── ⏰ FULL-SCREEN NOTIFICATION OVERLAY ─── -->
<div id="fullscreenAlert">
    <div class="fs-icon">🔔</div>
    <div class="fs-title">APPOINTMENT NOW!</div>
    <div class="fs-subtitle">Customer is waiting</div>
    <div class="fs-details" id="fsDetails">
        <div class="row"><span class="label">Customer</span><span class="value" id="fsCustomer">—</span></div>
        <div class="row"><span class="label">Mobile</span><span class="value" id="fsMobile">—</span></div>
        <div class="row"><span class="label">Vehicle</span><span class="value" id="fsVehicle">—</span></div>
        <div class="row"><span class="label">Time</span><span class="value" id="fsTime">—</span></div>
        <div class="row"><span class="label">Purpose</span><span class="value" id="fsPurpose">—</span></div>
    </div>
    <div class="fs-actions">
        <button class="fs-btn fs-btn-primary" onclick="acknowledgeAlert()">
            <i class="fa-solid fa-check-circle"></i> Acknowledge ✓
        </button>
        <button class="fs-btn fs-btn-snooze" onclick="snoozeAlert()">
            <i class="fa-solid fa-clock"></i> Snooze 5 min
        </button>
        <button class="fs-btn fs-btn-secondary" onclick="dismissAlert()">
            <i class="fa-solid fa-xmark"></i> Dismiss
        </button>
    </div>
    <div class="fs-time" id="fsServerTime">⏱️ Server: --:--:--</div>
</div>

<!-- ─── Toast Container ─── -->
<div class="toast-container" id="toastContainer"></div>

<div class="container">
    <!-- ─── HEADER ─── -->
    <div class="header">
        <h1 class="title"><i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i> Smart Appointments</h1>
        <div style="display: flex; gap: 8px; align-items: center;">
            <span id="liveIndicator" style="font-size: 11px; color: var(--success); display: flex; align-items: center; gap: 4px;">
                <i class="fa-solid fa-circle" style="font-size: 8px;"></i> LIVE
            </span>
            <a href="notification_settings.php" style="color: var(--text-muted); text-decoration: none; font-size: 13px;">
                <i class="fa-solid fa-bell"></i>
            </a>
            <a href="index.html" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- ─── STATS ─── -->
    <div class="stats-row" id="statsRow">
        <div class="stat-card urgent" id="statNow">
            <div class="num" id="statNowNum">0</div>
            <div class="lbl">Due Now</div>
        </div>
        <div class="stat-card warning">
            <div class="num" id="statTodayNum">0</div>
            <div class="lbl">Today</div>
        </div>
        <div class="stat-card success">
            <div class="num" id="statDoneNum">0</div>
            <div class="lbl">Completed</div>
        </div>
        <div class="stat-card">
            <div class="num" id="statUpcomingNum">0</div>
            <div class="lbl">Upcoming</div>
        </div>
    </div>

    <!-- ─── FILTERS ─── -->
    <div class="filter-bar">
        <button class="filter-btn active" data-filter="upcoming">📅 All Upcoming</button>
        <button class="filter-btn" data-filter="today">📋 Today</button>
        <button class="filter-btn" data-filter="pending">⏳ Pending</button>
        <button class="filter-btn" data-filter="overdue">🚨 Overdue</button>
        <button class="filter-btn" data-filter="completed">✅ Completed</button>
    </div>

    <!-- ─── CREATE FORM ─── -->
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="font-size: 12px; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1px;">
                Schedule New Appointment
            </div>
            <button class="btn btn-sm btn-success" onclick="clearForm()" type="button">
                <i class="fa-solid fa-rotate"></i> Reset
            </button>
        </div>
        <form id="apptForm" autocomplete="off">
            <div class="form-grid">
                <div class="input-group" style="position: relative;">
                    <label>Customer Name *</label>
                    <input list="customerList" id="customer_name" class="input-field"
                           placeholder="Search or Type Name" required oninput="handleNameInput(this.value)">
                    <datalist id="customerList"></datalist>
                    <div class="suggestion-box" id="suggestionBox"></div>
                </div>
                <div class="input-group">
                    <label>Mobile Number</label>
                    <input type="tel" id="mobile_number" class="input-field" placeholder="9876543210">
                </div>
            </div>
            <div class="form-grid">
                <div class="input-group">
                    <label>Vehicle No</label>
                    <input type="text" id="vehicle_no" class="input-field" placeholder="TN01AA1234">
                </div>
                <div class="input-group">
                    <label>Appointment Date *</label>
                    <input type="date" id="appointment_date" class="input-field" required>
                </div>
            </div>
            <div class="form-grid-3">
                <div class="input-group">
                    <label>Time *</label>
                    <input type="time" id="appointment_time" class="input-field" required>
                </div>
                <div class="input-group">
                    <label>Reminder Before</label>
                    <select id="reminder_minutes" class="input-field">
                        <option value="0">At time only</option>
                        <option value="5">5 min before</option>
                        <option value="10" selected>10 min before</option>
                        <option value="30">30 min before</option>
                        <option value="60">1 hour before</option>
                        <option value="1440">1 day before</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Notify Via</label>
                    <select id="notify_methods" class="input-field">
                        <option value="push" selected>Push Only</option>
                        <option value="push,whatsapp">Push + WhatsApp</option>
                        <option value="push,whatsapp,sms">All Channels</option>
                    </select>
                </div>
            </div>
            <div class="input-group">
                <label>Purpose</label>
                <input type="text" id="purpose" class="input-field" placeholder="e.g. GPS Installation, Service, Demo">
            </div>
            <div class="input-group">
                <label>Notes (Optional)</label>
                <textarea id="notes" class="input-field" rows="2" placeholder="Any special instructions..." style="resize: vertical;"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-plus-circle"></i> Create Smart Appointment
            </button>
        </form>
    </div>

    <!-- ─── APPOINTMENT LIST ─── -->
    <div class="appt-list" id="apptList">
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px;"></i>
            <p style="margin-top: 10px;">Loading appointments...</p>
        </div>
    </div>
</div>

<!-- ─── Voice indicator ─── -->
<div class="voice-indicator" id="voiceIndicator">
    <i class="fa-solid fa-volume-high" style="color: var(--primary);"></i>
    <span>Voice: <span id="voiceStatus">Active</span></span>
</div>

<script>
    // ──────────────────────────────────────────
    // 🚀 SMART APPOINTMENT ENGINE — CLIENT
    // ──────────────────────────────────────────
    const API = 'api_appointments.php';
    let allCustomers = [];
    let currentFilter = 'upcoming';
    let pollInterval = null;
    let fullScreenApptId = null;
    let snoozeTimeout = null;
    let audioCtx = null;
    let isFullScreenActive = false;
    let currentAlertData = null;

    // ─── INIT ───
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('appointment_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('appointment_time').value = new Date().toTimeString().slice(0, 5);
        
        loadCustomers();
        loadAppointments();
        loadStats();
        startPolling();
        requestNotificationPermission();
        registerServiceWorker();
        updateServerTime();
    });

    // ─── SERVICE WORKER (Push Notifications) ───
    async function registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('sw.js');
                console.log('✅ SW registered:', registration.scope);
                
                // Check for existing subscription
                const subscription = await registration.pushManager.getSubscription();
                if (!subscription) {
                    // We'll just use the SW for push event handling
                    console.log('📡 SW ready for push notifications');
                }
            } catch (err) {
                console.log('SW registration failed (non-critical):', err);
            }
        }
    }

    // ─── NOTIFICATION PERMISSION ───
    async function requestNotificationPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'default') {
            const result = await Notification.requestPermission();
            console.log('🔔 Notification permission:', result);
        }
    }

    // ─── SHOW BROWSER NOTIFICATION ───
    function showBrowserNotification(title, body, data = {}) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        try {
            const notif = new Notification(title, {
                body: body,
                icon: 'images/logo.png',
                badge: 'images/logo.png',
                tag: 'appointment-' + (data.id || Date.now()),
                requireInteraction: true,
                data: data
            });
            notif.onclick = () => {
                window.focus();
                notif.close();
            };
            // Auto-close after 10 seconds if not full-screen
            setTimeout(() => notif.close(), 10000);
        } catch (e) {
            console.log('Browser notification error:', e);
        }
    }

    // ─── 🎵 NOTIFICATION SOUND (Web Audio API) ───
    function playAlertSound(repeat = 3) {
        try {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            
            let count = 0;
            function beep() {
                if (count >= repeat) return;
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                
                osc.type = 'sine';
                osc.frequency.value = 880; // A5
                gain.gain.setValueAtTime(0.5, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
                
                osc.start();
                osc.stop(audioCtx.currentTime + 0.3);
                count++;
                setTimeout(beep, 400);
            }
            beep();
        } catch (e) {
            console.log('Audio play error:', e);
        }
    }

    // ─── 🗣️ VOICE ANNOUNCEMENT ───
    function voiceAnnounce(text) {
        if (!('speechSynthesis' in window)) return;
        try {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'en-US';
            utterance.rate = 1.1;
            utterance.pitch = 1;
            utterance.volume = 1;
            speechSynthesis.cancel();
            setTimeout(() => speechSynthesis.speak(utterance), 100);
        } catch (e) {
            console.log('Voice error:', e);
        }
    }

    // ─── TOAST NOTIFICATION ───
    function showToast(message, type = 'info', duration = 5000) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        const icon = type === 'alert' ? 'fa-bell' : 'fa-info-circle';
        toast.innerHTML = `
            <div class="toast-icon"><i class="fa-solid ${icon}"></i></div>
            <div style="flex:1; font-size: 13px;">${message}</div>
            <div style="cursor:pointer; color: var(--text-muted);" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark"></i>
            </div>
        `;
        container.appendChild(toast);
        setTimeout(() => { if (toast.parentElement) toast.remove(); }, duration);
    }

    // ─── SERVER TIME ───
    function updateServerTime() {
        setInterval(async () => {
            try {
                const res = await fetch(API + '?action=check_instant');
                const data = await res.json();
                if (data.server_time) {
                    const el = document.getElementById('fsServerTime');
                    const time = new Date(data.server_time + ' UTC+5:30');
                    el.textContent = '⏱️ Server: ' + time.toLocaleTimeString();
                }
            } catch(e) {}
        }, 30000);
    }

    // ─── ⏰ REAL-TIME POLLING ───
    function startPolling() {
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(checkInstantAlerts, 10000); // Every 10 seconds
        // Also check immediately
        setTimeout(checkInstantAlerts, 2000);
        // Also check every 60s for appointments
        setInterval(() => {
            loadAppointments();
            loadStats();
        }, 60000);
    }

    // ─── 🔍 CHECK INSTANT ALERTS ───
    async function checkInstantAlerts() {
        try {
            const res = await fetch(API + '?action=check_instant');
            const data = await res.json();
            
            if (!data.success) return;

            // Update live indicator
            const liveEl = document.getElementById('liveIndicator');
            liveEl.innerHTML = `<i class="fa-solid fa-circle" style="font-size: 8px; color: var(--success);"></i> LIVE`;

            // ⏰ FULL-SCREEN ALERT
            if (data.has_instant_alert && data.alert && !isFullScreenActive) {
                const a = data.alert;
                // If it's a "due_now" alert, trigger full-screen
                if (a.alert_type === 'due_now' || a.alert_type === 'overdue') {
                    triggerFullScreenAlert(a, data);
                }
            }

            // Show toast for upcoming alerts
            if (data.alerts && data.alerts.length > 0) {
                data.alerts.forEach(a => {
                    if (a.alert_type === 'upcoming_soon' && a.reminder_sent) {
                        showToast(`📅 ${a.customer_name} in ${a.vehicle_no || ''}`, 'info', 4000);
                    }
                });
            }

            // Update stat counters if present
            if (data.alerts) {
                const nowCount = data.alerts.filter(a => a.alert_type === 'due_now' || a.alert_type === 'overdue').length;
                document.getElementById('statNowNum').textContent = nowCount;
                document.getElementById('statNow').className = 'stat-card' + (nowCount > 0 ? ' urgent' : '');
            }

        } catch (e) {
            document.getElementById('liveIndicator').innerHTML = 
                `<i class="fa-solid fa-circle" style="font-size: 8px; color: var(--danger);"></i> OFFLINE`;
        }
    }

    // ─── ⏰ TRIGGER FULL-SCREEN ALERT ───
    function triggerFullScreenAlert(alert, data) {
        if (isFullScreenActive) return;
        
        currentAlertData = { alert, data };
        isFullScreenActive = true;
        fullScreenApptId = alert.id;

        // Populate details
        document.getElementById('fsCustomer').textContent = alert.customer_name || '—';
        document.getElementById('fsMobile').textContent = alert.mobile_number || '—';
        document.getElementById('fsVehicle').textContent = alert.vehicle_no || '—';
        document.getElementById('fsTime').textContent = alert.appointment_time 
            ? new Date('2000-01-01T' + alert.appointment_time).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})
            : '—';
        document.getElementById('fsPurpose').textContent = alert.purpose || '—';

        // Show fullscreen
        const fs = document.getElementById('fullscreenAlert');
        fs.classList.add('active');

        // 🔔 Play sound
        if (data.sound_enabled) {
            playAlertSound(5);
        }

        // 🗣️ Voice announcement
        if (data.voice_enabled) {
            const name = alert.customer_name || 'Customer';
            const vehicle = alert.vehicle_no ? ` with vehicle ${alert.vehicle_no}` : '';
            voiceAnnounce(`Attention! Appointment now for ${name}${vehicle}. Please attend immediately.`);
        }

        // Browser notification
        showBrowserNotification(
            '🚨 APPOINTMENT NOW!',
            `${alert.customer_name} - ${alert.vehicle_no || 'No vehicle'} - ${alert.purpose || ''}`,
            { id: alert.id, type: 'fullscreen' }
        );

        // Request fullscreen for the browser
        try {
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen().catch(() => {});
            }
        } catch(e) {}

        // Also try to play notification via the audio element fallback
        playNotificationSound();
    }

    // ─── Audio element fallback ───
    function playNotificationSound() {
        try {
            const audio = new Audio();
            // Use a data URI for a simple beep as fallback
            audio.src = 'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACAf39/f4B/f39/f3+AgH9/f3+AgH+AgH+AgIB/f39/gIB/f39/f4B/f39/gIB/f3+AgH9/f39/gH9/f39/gH9/f39/f4B/f3+AgH9/f3+AgH+AgH9/f3+Af39/f39/gH9/f3+AgH9/f3+AgH9/f39/gH9/f3+AgH9/f3+AgH9/f3+Af39/f39/gH9/f39/gH9/f3+AgH9/f3+AgH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f3+AgH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f3+AgH9/f3+AgH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f3+AgH9/f39/gH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f3+AgH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f3+AgH+AgH9/f3+Af4B/f3+Af4B/f39/gH9/f3+Af39/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH9/f39/gH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+AgH+A';
            audio.volume = 0.5;
            audio.play().catch(() => {});
        } catch(e) {}
    }

    // ─── ACKNOWLEDGE ───
    async function acknowledgeAlert() {
        if (!fullScreenApptId) return dismissAlert();
        try {
            const fd = new FormData();
            fd.append('action', 'acknowledge');
            fd.append('id', fullScreenApptId);
            await fetch(API, { method: 'POST', body: fd });
        } catch(e) {}
        dismissAlert();
        showToast('✅ Appointment acknowledged!', 'info');
        loadAppointments();
        loadStats();
    }

    // ─── SNOOZE ───
    function snoozeAlert() {
        dismissAlert();
        showToast('⏰ Snoozed for 5 minutes', 'alert', 3000);
        if (snoozeTimeout) clearTimeout(snoozeTimeout);
        snoozeTimeout = setTimeout(() => {
            if (currentAlertData) {
                triggerFullScreenAlert(currentAlertData.alert, currentAlertData.data);
            }
        }, 300000); // 5 min
    }

    // ─── DISMISS ───
    function dismissAlert() {
        const fs = document.getElementById('fullscreenAlert');
        fs.classList.remove('active');
        isFullScreenActive = false;
        fullScreenApptId = null;
        
        // Exit fullscreen
        try {
            if (document.fullscreenElement) {
                document.exitFullscreen().catch(() => {});
            }
        } catch(e) {}
    }

    // ─── CUSTOMER LOADING ───
    async function loadCustomers() {
        try {
            const res = await fetch('api_master_data.php?action=get_customer_names');
            allCustomers = await res.json();
            const list = document.getElementById('customerList');
            list.innerHTML = allCustomers.map(c => `<option value="${c.name}">`).join('');
        } catch(e) {}
    }

    // ─── SMART SUGGESTIONS ───
    let suggestionTimeout = null;
    function handleNameInput(value) {
        if (suggestionTimeout) clearTimeout(suggestionTimeout);
        
        // Auto-fill from existing customers
        const customer = allCustomers.find(c => c.name === value);
        if (customer) {
            document.getElementById('mobile_number').value = customer.mobile || '';
            document.getElementById('vehicle_no').value = customer.vehicle_no || '';
            document.getElementById('suggestionBox').style.display = 'none';
            return;
        }

        if (value.length < 2) {
            document.getElementById('suggestionBox').style.display = 'none';
            return;
        }

        suggestionTimeout = setTimeout(async () => {
            try {
                const res = await fetch(`${API}?action=get_suggestions&q=${encodeURIComponent(value)}`);
                const data = await res.json();
                const box = document.getElementById('suggestionBox');
                if (data.length === 0) { box.style.display = 'none'; return; }
                
                box.innerHTML = data.map(s => `
                    <div class="suggestion-item" onclick="fillSuggestion('${s.customer_name.replace(/'/g, "\\'")}', '${s.mobile_number || ''}', '${s.vehicle_no || ''}')">
                        <strong>${s.customer_name}</strong>
                        <small>${s.mobile_number || ''} ${s.vehicle_no ? '• ' + s.vehicle_no : ''}</small>
                    </div>
                `).join('');
                box.style.display = 'block';
            } catch(e) {}
        }, 300);
    }

    function fillSuggestion(name, mobile, vehicle) {
        document.getElementById('customer_name').value = name;
        document.getElementById('mobile_number').value = mobile;
        document.getElementById('vehicle_no').value = vehicle;
        document.getElementById('suggestionBox').style.display = 'none';
    }

    // ─── LOAD APPOINTMENTS ───
    async function loadAppointments() {
        try {
            const res = await fetch(`${API}?action=list&filter=${currentFilter}`);
            const data = await res.json();
            const list = document.getElementById('apptList');
            
            if (!Array.isArray(data) || data.length === 0) {
                list.innerHTML = `<div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fa-solid fa-calendar-xmark" style="font-size: 32px; margin-bottom: 10px;"></i>
                    <p>No appointments found</p>
                </div>`;
                return;
            }

            const now = new Date();
            list.innerHTML = `<div style="font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                ${data.length} Appointment${data.length > 1 ? 's' : ''}
            </div>`;

            data.forEach(it => {
                const item = document.createElement('div');
                const urgency = it.urgency || '';
                let extraClass = '';
                if (urgency === 'now') extraClass = 'urgent-now';
                else if (urgency === 'soon') extraClass = 'urgent-soon';
                
                item.className = `appt-item ${extraClass}`;
                
                const reminderBadge = it.reminder_sent == 1 ? '<span class="reminder-badge"><i class="fa-solid fa-bell"></i></span>' : '';
                const nowBadge = urgency === 'now' ? '<span class="now-badge">🔴 NOW</span>' : '';
                const ackBadge = it.acknowledged_at ? '<span style="font-size: 9px; color: var(--success); margin-left: 6px;">✓ Seen</span>' : '';

                item.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                        <div class="appt-info" style="flex: 1; min-width: 150px;">
                            <h4>
                                <i class="fa-solid fa-user" style="color: var(--primary); font-size: 12px;"></i>
                                ${it.customer_name}
                                ${reminderBadge} ${nowBadge} ${ackBadge}
                            </h4>
                            <p>
                                <i class="fa-solid fa-car"></i> ${it.vehicle_no || 'N/A'} 
                                | <i class="fa-solid fa-clock"></i> 
                                ${it.appointment_date} 
                                ${new Date('2000-01-01T' + it.appointment_time).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}
                                ${it.mobile_number ? '| <i class="fa-solid fa-phone"></i> ' + it.mobile_number : ''}
                            </p>
                            ${it.purpose ? `<p style="margin-top:5px; opacity:0.8;"><i class="fa-solid fa-tag"></i> ${it.purpose}</p>` : ''}
                            ${it.notes ? `<p style="margin-top:3px; font-size: 11px; color: #64748b;"><i class="fa-solid fa-note-sticky"></i> ${it.notes}</p>` : ''}
                        </div>
                        <div class="appt-actions">
                            <span class="appt-status status-${(it.status || 'Pending').toLowerCase()}">${it.status}</span>
                            ${it.status === 'Pending' ? `
                                <button class="btn btn-sm btn-success" onclick="updateStatus(${it.id}, 'Completed')">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="updateStatus(${it.id}, 'Cancelled')">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;
                list.appendChild(item);
            });
        } catch (e) {
            document.getElementById('apptList').innerHTML = `
                <div style="text-align: center; padding: 40px; color: var(--danger);">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Error loading appointments</p>
                </div>`;
        }
    }

    // ─── LOAD STATS ───
    async function loadStats() {
        try {
            const res = await fetch(`${API}?action=stats`);
            const data = await res.json();
            if (data.success && data.stats) {
                document.getElementById('statNowNum').textContent = data.stats.overdue || 0;
                document.getElementById('statTodayNum').textContent = data.stats.today_pending || 0;
                document.getElementById('statDoneNum').textContent = data.stats.today_completed || 0;
                document.getElementById('statUpcomingNum').textContent = data.stats.upcoming || 0;
                
                document.getElementById('statNow').className = 'stat-card' + 
                    ((data.stats.overdue || 0) > 0 ? ' urgent' : '');
            }
        } catch(e) {}
    }

    // ─── UPDATE STATUS ───
    async function updateStatus(id, status) {
        try {
            const fd = new FormData();
            fd.append('action', 'update_status');
            fd.append('id', id);
            fd.append('status', status);
            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showToast(status === 'Completed' ? '✅ Marked completed!' : '🗑️ Cancelled', 'info');
                loadAppointments();
                loadStats();
            }
        } catch(e) {
            showToast('❌ Failed to update', 'alert');
        }
    }

    // ─── FORM SUBMIT ───
    document.getElementById('apptForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        const fd = new FormData();
        fd.append('action', 'save');
        fd.append('customer_name', document.getElementById('customer_name').value);
        fd.append('mobile_number', document.getElementById('mobile_number').value);
        fd.append('vehicle_no', document.getElementById('vehicle_no').value);
        fd.append('appointment_date', document.getElementById('appointment_date').value);
        fd.append('appointment_time', document.getElementById('appointment_time').value);
        fd.append('purpose', document.getElementById('purpose').value);
        fd.append('notes', document.getElementById('notes').value);
        fd.append('reminder_minutes', document.getElementById('reminder_minutes').value);
        fd.append('notify_methods', document.getElementById('notify_methods').value);

        try {
            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showToast('✅ Appointment created! Smart reminders active.', 'info');
                document.getElementById('apptForm').reset();
                document.getElementById('appointment_date').value = new Date().toISOString().split('T')[0];
                document.getElementById('appointment_time').value = new Date().toTimeString().slice(0, 5);
                document.getElementById('reminder_minutes').value = '10';
                loadAppointments();
                loadStats();
                
                // Play confirmation sound
                playAlertSound(1);
            } else {
                showToast('❌ ' + data.message, 'alert');
            }
        } catch(e) {
            showToast('❌ Network error', 'alert');
        }
        
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-plus-circle"></i> Create Smart Appointment';
    });

    // ─── FILTER ───
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.dataset.filter;
            loadAppointments();
        });
    });

    // ─── CLEAR FORM ───
    function clearForm() {
        document.getElementById('apptForm').reset();
        document.getElementById('appointment_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('appointment_time').value = new Date().toTimeString().slice(0, 5);
        document.getElementById('reminder_minutes').value = '10';
    }

    // ─── Keyboard shortcut ───
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isFullScreenActive) {
            dismissAlert();
        }
        if (e.key === 'Enter' && isFullScreenActive) {
            acknowledgeAlert();
        }
    });

    // ─── Cleanup on page unload ───
    window.addEventListener('beforeunload', () => {
        if (pollInterval) clearInterval(pollInterval);
        if (snoozeTimeout) clearTimeout(snoozeTimeout);
        if (audioCtx) audioCtx.close();
    });

    console.log('🚀 Smart Appointment Engine loaded');
    console.log('🔔 Full-screen alerts active');
    console.log('🎵 Sound notification active');
    console.log('🗣️ Voice announcement ready');
    console.log('⏰ Polling every 10 seconds');
</script>

</body>
</html>
