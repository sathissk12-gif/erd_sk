<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>WhatsApp Cloud Manager | SK LOGIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="theme_engine.js"></script>

    <style>
        :root {
            --primary: #25D366;
            --primary-dark: #1ebe5d;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #94a3b8;
            --danger: #f43f5e;
            --warning: #f59e0b;
        }
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #064e3b, #030712);
            color: var(--text); min-height: 100vh; padding-bottom: 80px;
        }
        header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(3, 7, 18, 0.7); backdrop-filter: blur(25px);
            padding: calc(12px + env(safe-area-inset-top,0px)) 20px 16px;
            border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .back-link { text-decoration: none; color: white; display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14px; }
        .container { max-width: 700px; margin: 20px auto; padding: 0 16px; }

        .status-bar {
            display: flex; align-items: center; gap: 12px;
            padding: 16px 20px; background: var(--surface); border: 1px solid var(--border);
            border-radius: 20px; margin-bottom: 20px; backdrop-filter: blur(20px);
        }
        .status-dot {
            width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0;
        }
        .status-dot.green { background: #25D366; box-shadow: 0 0 15px #25D366; }
        .status-dot.yellow { background: var(--warning); box-shadow: 0 0 15px var(--warning); }
        .status-dot.red { background: var(--danger); box-shadow: 0 0 15px var(--danger); }
        .status-dot.gray { background: #475569; }

        .glass-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 22px;
            backdrop-filter: blur(20px); margin-bottom: 16px;
        }
        .section-label {
            font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;
            letter-spacing: 1.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;
        }

        .input-group { margin-bottom: 14px; }
        .input-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; }
        .input-field {
            width: 100%; padding: 12px 16px; background: rgba(15,23,42,0.4); border: 1px solid var(--border);
            border-radius: 12px; color: white; font-size: 14px; outline: none; font-family: inherit;
        }
        .input-field:focus { border-color: var(--primary); }
        textarea.input-field { resize: vertical; min-height: 80px; }

        .btn-main {
            padding: 14px 24px; border: none; border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), #059669);
            color: white; font-weight: 800; cursor: pointer; font-size: 14px; transition: 0.2s; width: 100%;
        }
        .btn-main:hover { opacity: 0.9; }
        .btn-main:active { transform: scale(0.96); }
        .btn-outline {
            padding: 10px 18px; border: 1px solid var(--border); border-radius: 12px;
            background: transparent; color: var(--text); font-weight: 700; cursor: pointer; font-size: 12px;
        }
        .btn-sm {
            padding: 6px 14px; border: none; border-radius: 10px;
            font-weight: 700; font-size: 11px; cursor: pointer;
        }

        .tab-bar { display: flex; gap: 8px; margin-bottom: 18px; overflow-x: auto; }
        .tab {
            padding: 8px 16px; background: var(--surface); border: 1px solid var(--border); border-radius: 99px;
            font-size: 12px; font-weight: 700; color: var(--text-muted); cursor: pointer; white-space: nowrap;
        }
        .tab.active { background: var(--primary); color: white; border-color: var(--primary); }

        .log-item {
            padding: 10px; background: rgba(0,0,0,0.2); border-radius: 10px;
            font-size: 11px; font-family: monospace; color: var(--text-muted);
            margin-bottom: 6px; word-break: break-all;
        }
        .log-item.success { border-left: 3px solid #25D366; }
        .log-item.error { border-left: 3px solid var(--danger); }

        .hidden { display: none; }

        .dual-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        @media (max-width: 500px) {
            .dual-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-weight:800; font-size:14px;">💬 WA CLOUD</div>
    </header>

    <div class="container">
        <!-- Status Bar -->
        <div class="status-bar" id="statusBar">
            <div class="status-dot gray" id="statusDot"></div>
            <div style="flex:1;">
                <div id="statusTitle" style="font-weight:700; font-size:15px;">Checking connection...</div>
                <div id="statusSub" style="font-size:12px; color:var(--text-muted);">No PC needed - works from server directly!</div>
            </div>
            <button class="btn-outline" onclick="checkHealth()"><i class="fa-solid fa-rotate"></i></button>
        </div>

        <!-- Tabs -->
        <div class="tab-bar" id="tabs">
            <div class="tab active" onclick="switchTab('send')">📤 Send</div>
            <div class="tab" onclick="switchTab('bulk')">📨 Bulk Send</div>
            <div class="tab" onclick="switchTab('config')">⚙️ Setup</div>
            <div class="tab" onclick="switchTab('logs')">📋 Logs</div>
            <div class="tab" onclick="switchTab('scheduler')">⏰ Scheduler</div>
        </div>

        <!-- 📤 SEND TAB -->
        <div id="panel-send" class="glass-card">
            <div class="section-label"><i class="fa-brands fa-whatsapp"></i> Send WhatsApp Message</div>
            <div class="input-group">
                <label>Mobile Number <span style="color:var(--danger);">*</span></label>
                <input type="text" id="sendNumber" class="input-field" placeholder="9876543210 (10 digit)" oninput="formatNumber(this)">
            </div>
            <div class="input-group">
                <label>Message <span style="color:var(--danger);">*</span></label>
                <textarea id="sendMessage" class="input-field" placeholder="Type your message..."></textarea>
            </div>
            <button class="btn-main" onclick="sendMessage()"><i class="fa-brands fa-whatsapp"></i> Send via Cloud API</button>
            <div id="sendResult" style="margin-top:12px; font-size:13px;"></div>
        </div>

        <!-- 📨 BULK SEND TAB -->
        <div id="panel-bulk" class="glass-card hidden">
            <div class="section-label"><i class="fa-solid fa-users"></i> Bulk Send</div>
            <div class="input-group">
                <label>Select Customers</label>
                <select id="bulkFilter" class="input-field" onchange="loadBulkNumbers()">
                    <option value="">-- Choose Customer Group --</option>
                    <option value="all">All Customers (with WhatsApp numbers)</option>
                    <option value="renewal_due_7">Renewal Due in 7 Days</option>
                    <option value="renewal_due_1">Renewal Due Tomorrow</option>
                    <option value="renewal_overdue">Renewal Overdue</option>
                    <option value="payment_pending">Payment Pending Today</option>
                </select>
            </div>
            <div class="input-group">
                <label>Total Recipients: <span id="bulkCount" style="color:var(--primary);">0</span></label>
            </div>
            <div class="input-group">
                <label>Message <span style="color:var(--danger);">*</span></label>
                <textarea id="bulkMessage" class="input-field" placeholder="Type your broadcast message..." rows="4"></textarea>
            </div>
            <button class="btn-main" onclick="sendBulk()"><i class="fa-solid fa-paper-plane"></i> Send Bulk</button>
            <div id="bulkProgress" style="margin-top:12px;"></div>
        </div>

        <!-- ⚙️ CONFIG TAB -->
        <div id="panel-config" class="glass-card hidden">
            <div class="section-label"><i class="fa-solid fa-key"></i> Meta API Configuration</div>
            <div style="padding:12px; background:rgba(37,211,102,0.1); border-radius:12px; margin-bottom:14px; font-size:12px; color:#25D366;">
                <i class="fa-solid fa-circle-info"></i> 
                Get these from <a href="https://developers.facebook.com/apps" target="_blank" style="color:white;">Meta Developer Console</a> → WhatsApp → API Setup
            </div>
            <div class="input-group">
                <label>Phone Number ID</label>
                <input type="text" id="cfgPhoneId" class="input-field" placeholder="123456789012345">
            </div>
            <div class="input-group">
                <label>Permanent Access Token</label>
                <textarea id="cfgToken" class="input-field" placeholder="EAAx...ZCX" rows="3"></textarea>
            </div>
            <div class="input-group">
                <label>Business Phone (with country code)</label>
                <input type="text" id="cfgBusinessPhone" class="input-field" placeholder="919876543210">
            </div>
            <button class="btn-main" onclick="saveConfig()" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
                <i class="fa-solid fa-floppy-disk"></i> Save Configuration
            </button>
            <div id="cfgResult" style="margin-top:12px; font-size:13px;"></div>
        </div>

        <!-- 📋 LOGS TAB -->
        <div id="panel-logs" class="glass-card hidden">
            <div class="section-label" style="display:flex; justify-content:space-between;">
                <span><i class="fa-solid fa-list"></i> Recent Logs</span>
                <button class="btn-sm" style="background:rgba(244,63,94,0.2);color:var(--danger);" onclick="clearLogs()">Clear</button>
            </div>
            <div id="logList">
                <div style="text-align:center; padding:20px; color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading logs...</div>
            </div>
            <button class="btn-outline" style="width:100%; margin-top:10px;" onclick="loadLogs()"><i class="fa-solid fa-rotate"></i> Refresh Logs</button>
        </div>

        <!-- ⏰ SCHEDULER TAB -->
        <div id="panel-scheduler" class="glass-card hidden">
            <div class="section-label"><i class="fa-solid fa-clock"></i> Auto Scheduler</div>
            <div style="padding:12px; background:rgba(245,158,11,0.1); border-radius:12px; margin-bottom:14px; font-size:13px;">
                <i class="fa-solid fa-lightbulb" style="color:var(--warning);"></i> 
                Scheduler runs from your Hostinger server — no PC needed!
            </div>

            <div class="input-group">
                <label>Schedule Type</label>
                <select id="schedType" class="input-field" onchange="updateSchedPreview()">
                    <option value="renewal_due_7">Renewal Reminder (7 days before)</option>
                    <option value="renewal_due_3">Renewal Reminder (3 days before)</option>
                    <option value="renewal_due_1">Renewal Reminder (1 day before)</option>
                    <option value="payment_followup">Payment Follow-up (daily)</option>
                    <option value="custom_message">Custom Scheduled Message</option>
                </select>
            </div>

            <div class="input-group">
                <label>Message Template <span style="color:var(--danger);">*</span></label>
                <textarea id="schedTemplate" class="input-field" rows="3" placeholder="Hi {customer}, your renewal for {vehicle} is due on {date}. Please contact us!">Hi {customer}, your {vehicle} device renewal is expiring soon. Kindly renew to avoid service interruption. - SK LOGIC</textarea>
            </div>

            <div style="font-size:11px; color:var(--text-muted); margin-bottom:14px; padding:8px; background:rgba(0,0,0,0.2); border-radius:8px;">
                <b>Available Variables:</b> <code>{customer}</code> <code>{vehicle}</code> <code>{date}</code> <code>{imei}</code> <code>{amount}</code>
            </div>

            <div class="dual-grid">
                <div class="input-group">
                    <label>Send Time</label>
                    <input type="time" id="schedTime" class="input-field" value="10:00">
                </div>
                <div class="input-group">
                    <label>Status</label>
                    <div id="schedStatus" class="input-field" style="background:rgba(37,211,102,0.1); color:#25D366; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-circle"></i> Saving...
                    </div>
                </div>
            </div>

            <button class="btn-main" onclick="saveSchedule()" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <i class="fa-solid fa-floppy-disk"></i> Save Schedule Settings
            </button>
            <div id="schedResult" style="margin-top:12px; font-size:13px;"></div>
        </div>
    </div>

    <script>
        const API = 'api_wa_cloud.php';

        // 🔍 Page Load
        window.onload = function() {
            checkHealth();
            loadConfig();
            loadLogs();
            loadSchedule();
        };

        // 🔄 Check Health
        async function checkHealth() {
            const dot = document.getElementById('statusDot');
            const title = document.getElementById('statusTitle');
            const sub = document.getElementById('statusSub');

            try {
                const res = await fetch(API + '?action=health');
                const d = await res.json();

                if (d.status === 'connected') {
                    dot.className = 'status-dot green';
                    title.textContent = '✅ WhatsApp Cloud API Connected';
                    sub.textContent = 'No PC needed — send messages directly from server!';
                } else if (d.status === 'not_configured') {
                    dot.className = 'status-dot yellow';
                    title.textContent = '⚠️ Not Configured';
                    sub.textContent = 'Go to Setup tab and enter your Meta API credentials';
                } else {
                    dot.className = 'status-dot red';
                    title.textContent = '❌ Connection Error';
                    sub.textContent = d.response || d.message || 'Check configuration';
                }
            } catch (e) {
                dot.className = 'status-dot gray';
                title.textContent = '🔴 API Offline';
                sub.textContent = 'Cannot reach API endpoint';
            }
        }

        // 🔄 Switch Tabs
        function switchTab(tab) {
            document.querySelectorAll('#tabs .tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('#tabs .tab')[['send','bulk','config','logs','scheduler'].indexOf(tab)].classList.add('active');
            
            ['send','bulk','config','logs','scheduler'].forEach(p => {
                document.getElementById('panel-' + p).classList.toggle('hidden', p !== tab);
            });
        }

        // 📤 Send Single Message
        async function sendMessage() {
            const number = document.getElementById('sendNumber').value.trim();
            const message = document.getElementById('sendMessage').value.trim();
            const result = document.getElementById('sendResult');

            if (!number || !message) {
                result.innerHTML = '<span style="color:var(--danger);">❌ Please enter number and message</span>';
                return;
            }

            result.innerHTML = '<span style="color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Sending via Cloud API...</span>';

            try {
                const fd = new FormData();
                fd.append('action', 'send');
                fd.append('number', number);
                fd.append('message', message);

                const res = await fetch(API, { method: 'POST', body: fd });
                const d = await res.json();

                if (d.success) {
                    result.innerHTML = `<span style="color:#25D366;">✅ Sent via: ${d.via || 'Cloud API'} | ID: ${d.message_id || 'N/A'}</span>`;
                } else {
                    result.innerHTML = `<span style="color:var(--danger);">❌ ${d.error}</span>`;
                }
            } catch (e) {
                result.innerHTML = `<span style="color:var(--danger);">❌ Network Error: ${e.message}</span>`;
            }
        }

        // 📨 Load Bulk Numbers
        async function loadBulkNumbers() {
            const filter = document.getElementById('bulkFilter').value;
            const count = document.getElementById('bulkCount');

            if (!filter) { count.textContent = '0'; return; }

            try {
                const res = await fetch(`api_wa_cloud.php?action=health&filter=${filter}`);
                // Just estimate - exact count comes from scheduler
                count.textContent = 'Loading...';

                // Fetch actual numbers via scheduler
                const schedRes = await fetch(`wa_cloud_scheduler.php?action=preview&type=${filter}`);
                const data = await schedRes.json();
                if (data.count) count.textContent = data.count;
                else count.textContent = 'Custom';
            } catch(e) {
                count.textContent = '*';
            }
        }

        // 📨 Send Bulk
        async function sendBulk() {
            const filter = document.getElementById('bulkFilter').value;
            const message = document.getElementById('bulkMessage').value.trim();
            const progress = document.getElementById('bulkProgress');

            if (!filter || !message) {
                progress.innerHTML = '<span style="color:var(--danger);">❌ Select customer group and enter message</span>';
                return;
            }

            if (!confirm(`Send bulk WhatsApp to selected customers?`)) return;

            progress.innerHTML = '<span style="color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Preparing bulk send...</span>';

            try {
                const fd = new FormData();
                fd.append('action', 'send_bulk_scheduled');
                fd.append('type', filter);
                fd.append('message', message);

                const res = await fetch('wa_cloud_scheduler.php', { method: 'POST', body: fd });
                const d = await res.json();

                if (d.success) {
                    progress.innerHTML = `<span style="color:#25D366;">✅ ${d.sent || 0} messages queued. ${d.failed || 0} failed.</span>`;
                } else {
                    progress.innerHTML = `<span style="color:var(--danger);">❌ ${d.error}</span>`;
                }
            } catch(e) {
                progress.innerHTML = `<span style="color:var(--danger);">❌ ${e.message}</span>`;
            }
        }

        // ⚙️ Load Config
        async function loadConfig() {
            try {
                // We'll use the config file to determine if configured
                const res = await fetch(API + '?action=health');
                const d = await res.json();

                // If we can read config, show current values (simplified)
                // User needs to fill manually from Meta dashboard
            } catch(e) {}
        }

        // ⚙️ Save Config
        async function saveConfig() {
            const phoneId = document.getElementById('cfgPhoneId').value.trim();
            const token = document.getElementById('cfgToken').value.trim();
            const businessPhone = document.getElementById('cfgBusinessPhone').value.trim();
            const result = document.getElementById('cfgResult');

            if (!phoneId || !token) {
                result.innerHTML = '<span style="color:var(--danger);">❌ Phone Number ID and Access Token required</span>';
                return;
            }

            result.innerHTML = '<span style="color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Saving...</span>';

            try {
                const fd = new FormData();
                fd.append('action', 'save_config');
                fd.append('phone_number_id', phoneId);
                fd.append('access_token', token);
                fd.append('business_phone', businessPhone);

                const res = await fetch(API, { method: 'POST', body: fd });
                const d = await res.json();

                if (d.success) {
                    result.innerHTML = '<span style="color:#25D366;">✅ Configuration saved! <button class="btn-sm" style="background:var(--primary);color:white;" onclick="checkHealth()">Test Connection</button></span>';
                    checkHealth();
                } else {
                    result.innerHTML = `<span style="color:var(--danger);">❌ ${d.error}</span>`;
                }
            } catch(e) {
                result.innerHTML = `<span style="color:var(--danger);">❌ ${e.message}</span>`;
            }
        }

        // 📋 Load Logs
        async function loadLogs() {
            const list = document.getElementById('logList');
            try {
                const res = await fetch(API + '?action=get_logs');
                const d = await res.json();

                if (!d.success || !d.logs || !d.logs.length) {
                    list.innerHTML = '<div style="text-align:center; padding:20px; color:var(--text-muted);"><i class="fa-solid fa-inbox"></i><br>No logs yet</div>';
                    return;
                }

                list.innerHTML = d.logs.map(log => {
                    const isErr = log.includes('CURL_ERR') || log.includes('Error') || log.includes('error');
                    return `<div class="log-item ${isErr ? 'error' : 'success'}">${escHtml(log)}</div>`;
                }).join('');
            } catch(e) {
                list.innerHTML = '<div style="color:var(--danger);">Error loading logs</div>';
            }
        }

        // 🗑️ Clear Logs
        async function clearLogs() {
            if (!confirm('Clear all logs?')) return;
            try {
                await fetch(API + '?action=clear_logs');
                loadLogs();
            } catch(e) {}
        }

        // ⏰ Load Schedule Settings
        async function loadSchedule() {
            const status = document.getElementById('schedStatus');
            try {
                const res = await fetch('wa_cloud_scheduler.php?action=status');
                const d = await res.json();
                if (d.active) {
                    status.innerHTML = '<i class="fa-solid fa-circle" style="color:#25D366;"></i> Active — runs every hour';
                } else {
                    status.innerHTML = '<i class="fa-solid fa-circle" style="color:var(--warning);"></i> Not running';
                }
            } catch(e) {
                status.innerHTML = '<i class="fa-solid fa-circle" style="color:var(--danger);"></i> Error checking';
            }
        }

        // ⏰ Save Schedule
        async function saveSchedule() {
            const type = document.getElementById('schedType').value;
            const template = document.getElementById('schedTemplate').value.trim();
            const time = document.getElementById('schedTime').value;
            const result = document.getElementById('schedResult');

            if (!template) {
                result.innerHTML = '<span style="color:var(--danger);">❌ Please enter message template</span>';
                return;
            }

            result.innerHTML = '<span style="color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Saving schedule...</span>';

            try {
                const fd = new FormData();
                fd.append('action', 'save_schedule');
                fd.append('type', type);
                fd.append('template', template);
                fd.append('send_time', time);

                const res = await fetch('wa_cloud_scheduler.php', { method: 'POST', body: fd });
                const d = await res.json();

                if (d.success) {
                    result.innerHTML = '<span style="color:#25D366;">✅ Schedule saved! Auto-reminders will run daily.</span>';
                    loadSchedule();
                } else {
                    result.innerHTML = `<span style="color:var(--danger);">❌ ${d.error}</span>`;
                }
            } catch(e) {
                result.innerHTML = `<span style="color:var(--danger);">❌ ${e.message}</span>`;
            }
        }

        function formatNumber(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
        }

        function escHtml(s) {
            return (s || '').replace(/</g, '<').replace(/>/g, '>');
        }

        function updateSchedPreview() {
            // Just UI feedback
        }

        // Auto-refresh logs every 30 seconds
        setInterval(() => {
            const logsTab = document.getElementById('panel-logs');
            if (!logsTab.classList.contains('hidden')) loadLogs();
        }, 30000);
    </script>
</body>
</html>
