<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Renewal Console | SK LOGIC</title>
    
    <!-- Ultra Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- 🔥 Security -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>

    <style>
        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.4);
            --secondary: #06b6d4;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --card-base: rgba(30, 41, 59, 0.3);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #f43f5e;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; outline: none; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #030712);
            color: var(--text);
            min-height: 100vh;
            padding-top: env(safe-area-inset-top, 0px);
            padding-bottom: 50px;
        }

        header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(3, 7, 18, 0.7); backdrop-filter: blur(25px);
            padding: calc(12px + env(safe-area-inset-top, 0px)) 25px 18px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .back-link { text-decoration: none; color: white; display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14px; }
        
        .container { max-width: 500px; margin: 20px auto; padding: 0 20px; animation: slideUp 0.6s ease-out; }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        .glass-card { 
            background: var(--surface); border: 1px solid var(--border); border-radius: 28px; padding: 25px; 
            backdrop-filter: blur(20px); box-shadow: 0 20px 40px rgba(0,0,0,0.4); margin-bottom: 20px;
        }

        .section-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; padding-left: 4px; }
        .input-field { 
            width: 100%; padding: 16px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 15px; font-family: inherit; transition: 0.3s;
        }

        .btn-main {
            width: 100%; padding: 20px; border: none; border-radius: 22px; 
            background: linear-gradient(135deg, var(--success), #059669);
            color: white; font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.3s;
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.3);
        }

        .search-area { display: flex; gap: 10px; margin-bottom: 15px; }
        .search-tools { display: grid; grid-template-columns: 1.2fr 1fr; gap: 12px; }
        .search-btn { background: var(--primary); color: white; border: none; padding: 0 20px; border-radius: 16px; cursor: pointer; }

        #postSave { display: none; margin-top: 20px; animation: slideUp 0.4s ease; }
        .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; }
        .action-btn { padding: 15px; border-radius: 14px; border: none; color: white; font-weight: 800; font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
        
        #msgBox { display: none; padding: 15px; border-radius: 16px; margin-bottom: 20px; font-size: 13px; font-weight: 700; text-align: center; }
        .msg-ok { background: rgba(16, 185, 129, 0.1); color: #4ade80; border: 1px solid rgba(16, 185, 129, 0.2); }
        .msg-err { background: rgba(244, 63, 94, 0.1); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.2); }

        .expiry-panel {
            display: none; margin-top: 14px; padding: 14px 16px;
            background: rgba(244, 63, 94, 0.08); border: 1px solid rgba(244, 63, 94, 0.25);
            border-radius: 16px;
        }
        .expiry-panel.show { display: block; }
        .expiry-panel-title { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .expiry-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .expiry-item { background: rgba(15, 23, 42, 0.5); border-radius: 12px; padding: 10px 12px; }
        .expiry-item span { display: block; font-size: 10px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; }
        .expiry-item strong { font-size: 14px; font-weight: 800; font-family: 'Outfit'; color: #fda4af; }
        .expiry-item strong.ok { color: #4ade80; }

        /* ⏳ Expiry Countdown */
        .countdown-bar {
            margin-top: 12px; padding: 10px 14px; border-radius: 12px;
            display: none; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 700;
        }
        .countdown-bar.show { display: flex; }
        .countdown-bar .days-num { font-size: 22px; font-family: 'Outfit'; font-weight: 800; }

        /* 💳 Payment Mode */
        #paymentMode { margin-top: 8px; }

        /* 📜 Renewal History */
        .history-panel { display: none; margin-top: 10px; }
        .history-panel.show { display: block; }
        .history-entry {
            background: rgba(15, 23, 42, 0.4); border-radius: 12px; padding: 12px 14px;
            margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;
            border-left: 3px solid var(--border); font-size: 12px;
        }
        .history-entry.paid { border-left-color: var(--success); }
        .history-entry.pending { border-left-color: var(--warn); }
        .history-entry .h-date { color: var(--text-muted); font-size: 10px; }
        .history-entry .h-amount { font-weight: 800; font-family: 'Outfit'; }
        .history-entry .h-status { font-size: 9px; font-weight: 800; padding: 3px 10px; border-radius: 99px; }
        .h-status.paid { background: rgba(16,185,129,0.15); color: var(--success); }
        .h-status.pending { background: rgba(245,158,11,0.15); color: var(--warn); }
        .h-more-link { text-align: center; font-size: 10px; color: var(--primary); cursor: pointer; font-weight: 700; padding: 8px; }
    </style>
</head>
<body>

    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-size: 10px; font-weight: 800; color: var(--primary); text-transform: uppercase;">Renewal Engine</div>
    </header>

    <div class="container">
        
        <div id="msgBox"></div>

        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-search"></i> Quick Search</div>
            <div class="search-area">
                <input type="text" id="q" class="input-field" placeholder="Enter Vehicle No..." oninput="this.value = this.value.toUpperCase()">
                <button class="search-btn" onclick="lookup()"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
            <div class="search-tools">
                <div class="input-group" style="margin-bottom:0;">
                    <label>New Entry Vehicle</label>
                    <input type="text" id="searchVehicleMirror" class="input-field" placeholder="Vehicle Number" oninput="syncVehicleFromSearch()">
                </div>
                <div class="input-group" style="margin-bottom:0;">
                    <label>Software</label>
                    <select id="searchSoft" class="input-field" style="appearance:none;" onchange="syncSoftwareFromSearch()">
                        <option value="">Select Software</option>
                    </select>
                </div>
            </div>
            <div id="expiryPanel" class="expiry-panel">
                <div class="expiry-panel-title"><i class="fa-solid fa-calendar-days"></i> Validity Period</div>
                <div class="expiry-grid">
                    <div class="expiry-item">
                        <span>From</span>
                        <strong id="expiryFrom">-</strong>
                    </div>
                    <div class="expiry-item">
                        <span>To (Expiry)</span>
                        <strong id="expiryTo">-</strong>
                    </div>
                </div>

                <!-- ⏳ Expiry Countdown -->
                <div id="countdownBar" class="countdown-bar"></div>
            </div>

            <!-- 📜 Renewal History (shown after search) -->
            <div id="historyPanel" class="history-panel">
                <div class="section-label" style="margin-top:15px; margin-bottom:10px;"><i class="fa-solid fa-clock-rotate-left"></i> Renewal History</div>
                <div id="historyList"></div>
            </div>
        </div>

        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-id-card"></i> Subscriber Data</div>
            <input type="hidden" id="rid">
            <div class="input-group"><label>Customer Name</label><input type="text" id="name" class="input-field"></div>
            <div class="input-group"><label>Mobile Number</label><input type="text" id="mobile" class="input-field"></div>
            <div class="input-group"><label>Vehicle Number</label><input type="text" id="vno" class="input-field"></div>
            <div class="input-group"><label>Software/Platform</label><input type="text" id="soft" class="input-field"></div>
        </div>

        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-file-invoice-dollar"></i> Billing Info</div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="input-group"><label>Renewal Cost</label><input type="number" id="amt" class="input-field"></div>
                <div class="input-group"><label>Amount Paid</label><input type="number" id="paid" class="input-field"></div>
            </div>
            <div class="input-group">
                <label>Payment Status</label>
                <select id="status" class="input-field" style="appearance: none;">
                    <option value="NO">PENDING / DUE</option>
                    <option value="YES">FULFILLED / PAID</option>
                </select>
            </div>
            <!-- 💳 Payment Mode -->
            <div class="input-group" id="paymentMode">
                <label>Payment Mode</label>
                <select id="payMode" class="input-field" style="appearance: none;">
                    <option value="">Select Mode</option>
                    <option value="CASH">💵 Cash</option>
                    <option value="UPI">📱 UPI (GPay/PayTM/PhonePe)</option>
                    <option value="CARD">💳 Card</option>
                    <option value="TRANSFER">🏦 Bank Transfer</option>
                    <option value="OTHER">🔄 Other</option>
                </select>
            </div>
            <div class="input-group" style="margin-bottom:0;"><label>Location</label><input type="text" id="loc" class="input-field"></div>
        </div>

        <button class="btn-main" onclick="store()">
            <i class="fa-solid fa-cloud-arrow-up"></i> Update Renewal
        </button>

        <!-- ➡️ Next Cycle Info (shown after update) -->
        <div id="nextCyclePanel" class="glass-card" style="display:none;">
            <div class="section-label"><i class="fa-solid fa-calendar-week"></i> Next Cycle (Auto-Created)</div>
            <div class="expiry-grid">
                <div class="expiry-item">
                    <span>Start Date</span>
                    <strong id="nextCycleFrom" style="color:#22d3ee;">-</strong>
                </div>
                <div class="expiry-item">
                    <span>Expiry Date</span>
                    <strong id="nextCycleTo" style="color:#4ade80;">-</strong>
                </div>
            </div>
            <div style="margin-top:10px; background:rgba(255,255,255,0.03); border-radius:12px; padding:10px 14px; text-align:center;">
                <span style="font-size:10px; font-weight:800; color:var(--text-muted); text-transform:uppercase;">⏰ Renewal Due in Next Cycle</span>
            </div>
        </div>

        <div id="postSave">
            <div class="glass-card" style="text-align: center; border-color: var(--success); background: rgba(16, 185, 129, 0.05);">
                <span style="font-size: 12px; font-weight: 800; color: var(--success);">SUCCESS: INVOICE READY</span>
                <div class="action-grid">
                    <button class="action-btn" onclick="share()" style="background: #25D366;"><i class="fa-brands fa-whatsapp"></i> WhatsApp</button>
                    <button class="action-btn" onclick="view()" style="background: var(--primary);"><i class="fa-solid fa-eye"></i> View Bill</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        const API = "api_renewal.php";
        let lastData = null;
        let systemSettings = {};

        async function fetchSoftwareList() {
            try {
                const res = await fetch(`${API}?action=softwareList`);
                const data = await res.json();
                const searchSoft = document.getElementById('searchSoft');

                data.forEach(it => {
                    const opt1 = document.createElement('option');
                    opt1.value = it.name;
                    opt1.innerText = it.name;
                    searchSoft.appendChild(opt1);
                });

                // Fetch System Settings
                const setRes = await fetch('api_master_data.php?action=get_settings');
                systemSettings = await setRes.json();
            } catch (e) {
                console.error('Initial data load failed', e);
            }
        }

        function syncVehicleFromSearch() {
            const value = document.getElementById('searchVehicleMirror').value.toUpperCase();
            document.getElementById('searchVehicleMirror').value = value;
            document.getElementById('vno').value = value;
        }

        function syncSoftwareFromSearch() {
            document.getElementById('soft').value = document.getElementById('searchSoft').value;
        }

        function notify(m, isErr){
            const b = document.getElementById('msgBox');
            b.innerText = m; b.style.display = 'block';
            b.className = isErr ? 'msg-err' : 'msg-ok';
            setTimeout(() => b.style.display='none', 3000);
        }

        function formatRenewalDate(d) {
            if (!d || d === '0000-00-00') return '-';
            const dt = new Date(d + 'T00:00:00');
            return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function showExpiryInfo(d) {
            const panel = document.getElementById('expiryPanel');
            const fromEl = document.getElementById('expiryFrom');
            const toEl = document.getElementById('expiryTo');
            const from = d?.valid_from || null;
            const to = d?.valid_to || d?.expiry_date || null;

            if (!from && !to) {
                panel.classList.remove('show');
                return;
            }

            fromEl.innerText = formatRenewalDate(from);
            toEl.innerText = formatRenewalDate(to);

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const toDate = to ? new Date(to + 'T00:00:00') : null;
            toEl.className = (toDate && toDate < today) ? '' : 'ok';

            panel.classList.add('show');
        }

        function hideExpiryInfo() {
            document.getElementById('expiryPanel').classList.remove('show');
            document.getElementById('expiryFrom').innerText = '-';
            document.getElementById('expiryTo').innerText = '-';
        }

        // ⏳ Expiry Countdown
        function showCountdown(expiryDate) {
            const bar = document.getElementById('countdownBar');
            if (!expiryDate || expiryDate === '0000-00-00' || expiryDate === '-') {
                bar.classList.remove('show');
                bar.innerHTML = '';
                return;
            }
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const expiry = new Date(expiryDate + 'T00:00:00');
            if (isNaN(expiry.getTime())) { bar.classList.remove('show'); bar.innerHTML = ''; return; }
            
            const diffMs = expiry - today;
            const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
            let emoji, color;
            if (diffDays < 0) { emoji = '🚨'; color = 'var(--danger)'; }
            else if (diffDays <= 7) { emoji = '🔥'; color = 'var(--warn)'; }
            else if (diffDays <= 30) { emoji = '⚠️'; color = '#f97316'; }
            else if (diffDays <= 90) { emoji = '⏳'; color = '#22d3ee'; }
            else { emoji = '✅'; color = 'var(--success)'; }
            
            const label = diffDays < 0 ? 'EXPIRED' : diffDays === 0 ? 'EXPIRES TODAY' : 'Days Remaining';
            bar.innerHTML = `
                <span style="font-size:24px;">${emoji}</span>
                <span><span class="days-num" style="color:${color}">${Math.abs(diffDays)}</span> ${label}</span>
            `;
            bar.style.background = diffDays < 0 ? 'rgba(239,68,68,0.1)' : diffDays <= 7 ? 'rgba(245,158,11,0.1)' : 'rgba(16,185,129,0.05)';
            bar.style.border = `1px solid ${diffDays < 0 ? 'rgba(239,68,68,0.3)' : diffDays <= 7 ? 'rgba(245,158,11,0.3)' : 'rgba(16,185,129,0.2)'}`;
            bar.style.color = color;
            bar.classList.add('show');
        }

        function hideCountdown() {
            document.getElementById('countdownBar').classList.remove('show');
            document.getElementById('countdownBar').innerHTML = '';
        }

        // 📜 Load Renewal History
        async function loadHistory(vehicle) {
            const panel = document.getElementById('historyPanel');
            const list = document.getElementById('historyList');
            if (!vehicle) { panel.classList.remove('show'); return; }
            try {
                const res = await fetch(`api_renewal.php?action=history&vehicle=${encodeURIComponent(vehicle)}`);
                const data = await res.json();
                if (data.success && data.history && data.history.length > 0) {
                    let html = '';
                    data.history.forEach(h => {
                        const isPaid = (h.status || '').toUpperCase() === 'YES' || (h.status || '').toUpperCase() === 'PAID';
                        html += `
                            <div class="history-entry ${isPaid ? 'paid' : 'pending'}">
                                <div>
                                    <div class="h-date">${h.date || '—'}</div>
                                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">${h.software || '—'}</div>
                                </div>
                                <div style="text-align:right;">
                                    <div class="h-amount">₹${parseInt(h.amount).toLocaleString('en-IN')}</div>
                                    <span class="h-status ${isPaid ? 'paid' : 'pending'}">${isPaid ? 'PAID' : 'PENDING'}</span>
                                </div>
                            </div>
                        `;
                    });
                    list.innerHTML = html;
                    panel.classList.add('show');
                } else {
                    panel.classList.remove('show');
                }
            } catch(e) {
                panel.classList.remove('show');
            }
        }

        async function lookup() {
            const v = document.getElementById('q').value;
            if(!v) return;
            console.log("Looking up vehicle:", v);
            try {
                const res = await fetch(`api_renewal.php?action=search&vehicle=${encodeURIComponent(v)}`);
                const r = await res.json();
                console.log("Lookup result:", r);
                if(r.success) {
                    const d = r.data;
                    document.getElementById('rid').value = d.id;
                    document.getElementById('name').value = d.customer_name || "";
                    document.getElementById('mobile').value = d.mobile_no || "";
                    document.getElementById('vno').value = d.vehicle_no || "";
                    document.getElementById('searchVehicleMirror').value = d.vehicle_no || "";
                    document.getElementById('soft').value = d.software_type || "";
                    document.getElementById('searchSoft').value = d.software_type || "";
                    document.getElementById('amt').value = d.amount || 0;
                    document.getElementById('paid').value = d.amount || 0;
                    document.getElementById('status').value = "NO";
                    document.getElementById('loc').value = d.location || "";
                    document.getElementById('payMode').value = "";
                    showExpiryInfo(d);
                    showCountdown(d.valid_to || d.expiry_date);
                    loadHistory(d.vehicle_no);
                    notify("Customer Found: Ready to Renew", false);
                } else { 
                    const d = r.data || {};
                    document.getElementById('rid').value = '';
                    document.getElementById('name').value = d.customer_name || "";
                    document.getElementById('mobile').value = d.mobile_no || "";
                    document.getElementById('vno').value = d.vehicle_no || v.toUpperCase();
                    document.getElementById('searchVehicleMirror').value = d.vehicle_no || v.toUpperCase();
                    document.getElementById('soft').value = d.software_type || document.getElementById('searchSoft').value || "";
                    document.getElementById('amt').value = d.amount || 0;
                    document.getElementById('paid').value = 0;
                    document.getElementById('status').value = "NO";
                    document.getElementById('loc').value = d.location || "";
                    document.getElementById('payMode').value = "";
                    hideExpiryInfo();
                    hideCountdown();
                    loadHistory(d.vehicle_no || v.toUpperCase());
                    notify(r.message || "No record found", true); 
                }
            } catch(e) { console.error(e); hideExpiryInfo(); notify("Search Error", true); }
        }

        async function store() {
            const id = document.getElementById('rid').value;

            const p = new URLSearchParams({
                action: 'update',
                id: id,
                customer_name: document.getElementById('name').value,
                mobile_no: document.getElementById('mobile').value,
                vehicle_no: document.getElementById('vno').value,
                software_type: document.getElementById('soft').value,
                amount: document.getElementById('amt').value,
                received_amount: document.getElementById('paid').value,
                status: document.getElementById('status').value,
                location: document.getElementById('loc').value,
                payment_mode: document.getElementById('payMode').value
            });

            try {
                const res = await fetch(`api_renewal.php?${p.toString()}`);
                const r = await res.json();
                if(r.success) {
                    lastData = r;
                    document.getElementById('rid').value = r.id || '';
                    notify("Renewal Updated! Invoice No: " + r.invoice_no, false);
                    if(r.invoice_no) document.getElementById('postSave').style.display='block';
                    
                    // Show next cycle info
                    if (r.next_valid_from || r.next_valid_to) {
                        document.getElementById('nextCycleFrom').innerText = formatRenewalDate(r.next_valid_from);
                        document.getElementById('nextCycleTo').innerText = formatRenewalDate(r.next_valid_to);
                        document.getElementById('nextCyclePanel').style.display = 'block';
                    }
                } else { 
                    notify("Update Failed: " + (r.message || ""), true); 
                }
            } catch(e) { console.error(e); notify("Network Error", true); }
        }

        function share() {
            const d = lastData;
            // Use UID for secure sharing
            const shareUrl = window.location.href.replace('renewal_entry.php', 'renewal_invoice.php') + "?uid=" + d.uid;
            let msg = `Dear ${document.getElementById('name').value},\n\nPayment Received for Vehicle ${document.getElementById('vno').value}. Your Renewal Invoice is ready.\n\nView Bill: ${shareUrl}`;
            
            if (systemSettings && systemSettings.google_review_url) {
                msg += `\n\n⭐ Please share your experience on Google: ${systemSettings.google_review_url}`;
            }

            const phone = document.getElementById('mobile').value.replace(/[^0-9]/g, '');
            window.open(`https://wa.me/91${phone}?text=${encodeURIComponent(msg)}`, "_blank");
        }
        function view() { 
            const d = lastData;
            window.location.href = `renewal_invoice.php?${d.uid ? 'uid='+d.uid : 'invoice_no='+d.invoice_no}`; 
        }

        window.onload = fetchSoftwareList;
    </script>
</body>
</html>
