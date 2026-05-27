<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Dealer Manager | SK LOGIC</title>
    <script src="theme_engine.js"></script>
    
    <!-- Ultra Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- 🔥 Security -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>

    <style>
        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.4);
            --secondary: #06b6d4;
            --accent: #f43f5e;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius: 20px;
            --radius-sm: 14px;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
        
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

        .container { max-width: 600px; margin: 20px auto; padding: 0 20px; animation: slideUp 0.6s ease-out; }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        /* Menu Tabs */
        .tabs { display: flex; background: rgba(0,0,0,0.3); padding: 5px; border-radius: 20px; margin-bottom: 25px; border: 1px solid var(--border); }
        .tab { flex:1; padding: 12px; border-radius: 16px; text-align: center; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; color: var(--text-muted); }
        .tab.active { background: var(--primary); color: white; box-shadow: 0 5px 15px var(--primary-glow); }

        .glass-card { background: var(--surface); border: 1px solid var(--border); border-radius: 28px; padding: 25px; backdrop-filter: blur(20px); margin-bottom: 20px; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; }
        .input-field { 
            width: 100%; padding: 16px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 15px; transition: 0.3s;
        }
        .input-field:focus { border-color: var(--primary); background: rgba(15, 23, 42, 0.8); }

        .btn-main {
            width: 100%; padding: 18px; border: none; border-radius: 18px; font-weight: 800; font-size: 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 12px; transition: 0.3s; margin-top: 10px;
        }
        .btn-primary { background: linear-gradient(135deg, var(--primary), #6366f1); color: white; box-shadow: 0 10px 20px rgba(139,92,246,0.3); }

        /* 🎥 Modern Scanner */
        #scannerPanel { display: none; }
        #reader { border-radius: var(--radius); overflow: hidden; margin-bottom: 12px; display: none; border: 2px solid var(--primary); min-height: 280px; background: #000; box-shadow: 0 0 30px var(--primary-glow); }
        #scanHint { font-size: 12px; color: var(--text-muted); text-align: center; }

        .scan-btn {
            padding: 14px 20px; border: none; border-radius: var(--radius-sm);
            background: var(--border); color: white; font-weight: 800; font-size: 13px;
            cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; transition: 0.3s; white-space: nowrap;
        }
        .scan-btn:hover { background: rgba(255,255,255,0.05); }
        .scan-btn.active { background: linear-gradient(135deg, var(--accent), #e11d48); box-shadow: 0 8px 20px rgba(244,63,94,0.3); }
        .scan-btn i { font-size: 16px; }

        .scan-area {
            background: rgba(0, 0, 0, 0.2); border: 2px dashed var(--border);
            border-radius: var(--radius-sm); padding: 16px; position: relative;
            transition: 0.3s; margin-bottom: 15px;
        }
        .scan-area.active { border-color: var(--primary); border-style: solid; }

        .scan-overlay {
            display: none; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(139,92,246,0.05) 0%, transparent 50%, rgba(139,92,246,0.05) 100%);
            border-radius: var(--radius-sm); pointer-events: none;
            animation: scanLine 2s ease-in-out infinite;
        }
        .scan-area.active .scan-overlay { display: block; }
        @keyframes scanLine {
            0% { background-position: 0 0; }
            50% { background-position: 0 100%; }
            100% { background-position: 0 0; }
        }

        .scan-area-top {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 10px; position: relative; z-index: 2;
        }
        .count-badge {
            background: var(--primary); color: white; padding: 4px 14px;
            border-radius: 99px; font-size: 10px; font-weight: 800; letter-spacing: 0.5px;
        }
        .clear-btn {
            background: none; border: none; color: var(--text-muted); font-size: 11px;
            font-weight: 700; cursor: pointer; padding: 4px 10px; border-radius: 99px;
            transition: 0.2s; display: flex; align-items: center; gap: 5px;
        }
        .clear-btn:hover { background: rgba(239,68,68,0.1); color: var(--danger); }

        /* Chips */
        .chips-wrap {
            display: flex; flex-wrap: wrap; gap: 8px; min-height: 36px;
            position: relative; z-index: 2; max-height: 180px; overflow-y: auto;
            padding: 4px 0;
        }
        .chips-wrap:empty::after {
            content: 'Scanned IMEIs appear here...'; display: block;
            color: var(--text-muted); font-size: 13px; opacity: 0.4;
            padding: 8px 4px; width: 100%; text-align: center;
        }

        .chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(139, 92, 246, 0.12); border: 1px solid rgba(139, 92, 246, 0.2);
            padding: 5px 8px 5px 12px; border-radius: 99px;
            font-family: 'Outfit'; font-size: 11px; font-weight: 700;
            letter-spacing: 0.5px; animation: chipIn 0.3s ease-out;
        }
        @keyframes chipIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .chip .remove {
            width: 16px; height: 16px; border-radius: 50%; border: none;
            background: rgba(255,255,255,0.1); color: var(--text-muted); cursor: pointer;
            font-size: 7px; display: flex; align-items: center; justify-content: center;
            transition: 0.2s; flex-shrink: 0;
        }
        .chip .remove:hover { background: var(--danger); color: white; }

        /* IMEI status chip */
        #imeiStatus {
            font-size: 11px; font-weight: 700; padding: 8px 14px; border-radius: 10px;
            display: none; margin-top: 8px; line-height: 1.5;
        }

        /* Pending List */
        .dealer-box { background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 24px; padding: 20px; margin-bottom: 30px; transition: 0.3s; }
        .dealer-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        .dealer-name { font-size: 18px; font-weight: 800; color: var(--primary); letter-spacing: -0.5px; }
        .item-count { font-size: 10px; background: var(--primary); color: white; padding: 2px 8px; border-radius: 6px; }

        .bulk-payout-box { background: rgba(139, 92, 246, 0.05); border: 1px dashed var(--primary); border-radius: 18px; padding: 15px; margin-bottom: 20px; }
        .payout-title { font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 12px; display: block; }

        .pending-item { 
            background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 18px; padding: 15px; margin-bottom: 10px;
            position: relative; overflow: hidden;
        }
        .item-imei { font-weight: 800; font-family: 'Outfit'; font-size: 15px; display: block; margin-bottom: 4px; }
        .item-model { font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; }

        .pay-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 12px; }
        .mini-input { padding: 10px; font-size: 12px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white; outline: none; }
        .mini-input:focus { border-color: var(--primary); }

        .section { display: none; }
        .section.active { display: block; }

        .loader { text-align: center; padding: 40px; color: var(--text-muted); font-size: 14px; }

        /* Toast */
        .toast-container {
            position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
            z-index: 9999; display: flex; flex-direction: column; gap: 8px;
            width: calc(100% - 40px); max-width: 400px; pointer-events: none;
        }
        .toast {
            background: rgba(15, 23, 42, 0.95); border: 1px solid var(--border);
            backdrop-filter: blur(20px); border-radius: var(--radius-sm);
            padding: 14px 18px; display: flex; align-items: center; gap: 12px;
            animation: toastIn 0.4s ease-out; pointer-events: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .toast.out { animation: toastOut 0.3s ease-in forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { from { opacity:1; transform:translateY(0) scale(1); } to { opacity:0; transform:translateY(-10px) scale(0.95); } }
        .toast .icon { font-size: 20px; flex-shrink: 0; }
        .toast .msg { font-size: 12px; font-weight: 600; line-height: 1.4; }
        .toast.success { border-color: rgba(16,185,129,0.3); }
        .toast.success .icon { color: var(--success); }
        .toast.error { border-color: rgba(239,68,68,0.3); }
        .toast.error .icon { color: var(--danger); }
        .toast.warn { border-color: rgba(245,158,11,0.3); }
        .toast.warn .icon { color: var(--warning); }
    </style>
</head>
<body>

    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div style="font-size: 10px; font-weight: 800; color: var(--secondary); text-transform: uppercase;">Dealer Hub</div>
    </header>

    <div class="container">
        
        <div class="tabs">
            <div class="tab active" onclick="setTab('issue', this)"><i class="fa-solid fa-truck-ramp-box"></i> Issue</div>
            <div class="tab" onclick="setTab('bulk', this)"><i class="fa-solid fa-layer-group"></i> Bulk</div>
            <div class="tab" onclick="setTab('pending', this)"><i class="fa-solid fa-hourglass-half"></i> Pending</div>
        </div>

        <!-- 📤 ISSUE SECTION -->
        <div id="issue" class="section active">
            <div class="glass-card">
                <div class="input-group">
                    <label>Dealer Outlet</label>
                    <input type="text" id="dealerName" class="input-field" placeholder="Enter Dealer Name">
                </div>
                <div class="input-group">
                    <label>Individual IMEI</label>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="imei" class="input-field" placeholder="Scan or type IMEI..." oninput="checkImeiStatus(this.value)" autocomplete="off">
                        <button class="scan-btn" id="scanImeBtn" onclick="startScan('ime')"><i class="fa-solid fa-qrcode"></i> Scan</button>
                    </div>
                    <div id="imeiStatus"></div>
                </div>
                <button class="btn-main btn-primary" onclick="updateDevice()"><i class="fa-solid fa-paper-plane"></i> Assign to Dealer</button>
            </div>
        </div>

        <!-- 📦 BULK SECTION -->
        <div id="bulk" class="section">
            <div class="glass-card">
                <div class="input-group">
                    <label>Bulk Dealer</label>
                    <input type="text" id="bulkDealer" class="input-field" placeholder="Target Outlet">
                </div>
                <div class="input-group">
                    <label>Scan List (IMEIs)</label>

                    <!-- 🏷️ Chip-based scanned items -->
                    <div class="scan-area" id="bulkScanArea">
                        <div class="scan-overlay"></div>
                        <div class="scan-area-top">
                            <span class="count-badge" id="bulkCount">0 Units</span>
                            <button class="clear-btn" onclick="clearBulkImeis()"><i class="fa-solid fa-trash-can"></i> Clear</button>
                        </div>
                        <div class="chips-wrap" id="bulkChips"></div>
                        <textarea id="bulkList" style="display:none"></textarea>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:12px;">
                        <button class="scan-btn" id="bulkScanBtn" style="justify-content:center; width:100%;" onclick="startScan('bulk')">
                            <i class="fa-solid fa-camera"></i> Start Scanner
                        </button>
                        <button class="scan-btn" id="bulkStopBtn" style="justify-content:center; width:100%; background:var(--warning); color:white;" onclick="stopScan()">
                            <i class="fa-solid fa-stop"></i> Stop
                        </button>
                    </div>
                </div>
                <button class="btn-main btn-primary" onclick="bulkUpdate()"><i class="fa-solid fa-cloud-arrow-up"></i> Commit Bulk Upload</button>
            </div>
        </div>

        <!-- ⏳ PENDING SECTION -->
        <div id="pending" class="section">
            <button class="btn-main btn-primary" style="margin-bottom:25px;" onclick="loadPending()"><i class="fa-solid fa-magnifying-glass-chart"></i> Find Pending Payouts</button>
            <div id="pendingList"></div>
        </div>

        <!-- 🎥 Scanner Panel -->
        <div id="scannerPanel" class="glass-card">
            <div id="reader"></div>
            <div id="scanHint">Point camera at barcode / QR code</div>
            <button class="scan-btn" style="width:100%; justify-content:center; margin-top:10px; background:var(--accent); color:white;" onclick="stopScan()">
                <i class="fa-solid fa-xmark"></i> Close Scanner
            </button>
        </div>

    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        const API = "api_dealers.php";
        let scanner;
        let activeScanType = null;
        let bulkImeis = []; // Array of strings for bulk

        // ─── TOAST ───
        function toast(msg, type = 'success') {
            const container = document.getElementById('toastContainer');
            const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warn: 'fa-triangle-exclamation' };
            const el = document.createElement('div');
            el.className = `toast ${type}`;
            el.innerHTML = `<div class="icon"><i class="fa-regular ${icons[type] || icons.success}"></i></div><div class="msg">${msg}</div>`;
            container.appendChild(el);
            setTimeout(() => {
                el.classList.add('out');
                setTimeout(() => el.remove(), 300);
            }, 3000);
        }

        // ─── SCAN BEEP ───
        function playBeep(freq = 1800, duration = 120) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration / 1000);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + duration / 1000);
                setTimeout(() => ctx.close(), duration + 100);
            } catch(e) { /* audio not supported */ }
        }

        // ─── TAB ───
        function setTab(id, btn) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            stopScan();
        }

        // ─── SCANNER ───
        async function startScan(type) {
            const rd = document.getElementById('reader');
            const panel = document.getElementById('scannerPanel');
            const hint = document.getElementById('scanHint');

            activeScanType = type;
            panel.style.display = 'block';
            rd.style.display = 'block';
            hint.innerText = type === 'bulk' ? '📸 Scan multiple IMEIs — each will be added to the list' : '📸 Scan one IMEI code';

            // Update button state
            if (type === 'ime') {
                document.getElementById('scanImeBtn').classList.add('active');
                document.getElementById('scanImeBtn').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Scanning...';
            } else {
                document.getElementById('bulkScanBtn').classList.add('active');
                document.getElementById('bulkScanBtn').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Scanning...';
                document.getElementById('bulkScanArea').classList.add('active');
            }

            try {
                if (scanner) {
                    await stopScan(false);
                }

                scanner = new Html5Qrcode("reader");
                const cameras = await Html5Qrcode.getCameras();
                const rearCamera = cameras.find(cam => /back|rear|environment/i.test(cam.label || ""));
                const cameraConfig = rearCamera ? { deviceId: { exact: rearCamera.id } } : { facingMode: "environment" };

                await scanner.start(cameraConfig, { fps: 20, qrbox: 250 }, (text) => {
                    let code = text.replace(/\D/g,'');
                    if(!code) return;

                    // Beep + vibrate
                    playBeep(2000, 100);
                    if(navigator.vibrate) navigator.vibrate(50);

                    if(type === 'ime') {
                        document.getElementById('imei').value = code;
                        toast('📱 Scanned: ' + code, 'success');
                        checkImeiStatus(code);
                        stopScan();
                    } else {
                        // Add to bulk chips
                        const upper = code.toUpperCase();
                        if (!bulkImeis.includes(upper)) {
                            bulkImeis.push(upper);
                            renderBulkChips();
                            toast('📱 Added: ' + upper, 'success');
                        } else {
                            toast('⚠️ Duplicate: ' + upper, 'warn');
                        }
                    }
                });
            } catch (err) {
                console.error("Scanner start failed:", err);
                toast('Camera access denied. Allow permission and try again.', 'error');
                await stopScan(false);
            }
        }

        async function stopScan(hidePanel = true) {
            const rd = document.getElementById('reader');
            const panel = document.getElementById('scannerPanel');

            if (scanner) {
                try { await scanner.stop(); } catch (e) {}
                try { await scanner.clear(); } catch (e) {}
                scanner = null;
            }

            rd.style.display = 'none';
            if (hidePanel) panel.style.display = 'none';
            activeScanType = null;

            // Reset buttons
            const imeBtn = document.getElementById('scanImeBtn');
            imeBtn.classList.remove('active');
            imeBtn.innerHTML = '<i class="fa-solid fa-qrcode"></i> Scan';

            const bulkBtn = document.getElementById('bulkScanBtn');
            bulkBtn.classList.remove('active');
            bulkBtn.innerHTML = '<i class="fa-solid fa-camera"></i> Start Scanner';

            document.getElementById('bulkScanArea').classList.remove('active');
        }

        // ─── BULK CHIPS ───
        function renderBulkChips() {
            const wrap = document.getElementById('bulkChips');
            wrap.innerHTML = bulkImeis.map((imei, idx) => `
                <span class="chip">
                    <i class="fa-solid fa-qrcode" style="font-size:9px;opacity:0.5;"></i>
                    ${imei}
                    <button class="remove" onclick="removeBulkImei(${idx})" title="Remove"><i class="fa-solid fa-xmark"></i></button>
                </span>
            `).join('');
            document.getElementById('bulkList').value = bulkImeis.join('\n');
            document.getElementById('bulkCount').innerText = bulkImeis.length + ' Units';
        }

        function removeBulkImei(idx) {
            bulkImeis.splice(idx, 1);
            renderBulkChips();
        }

        function clearBulkImeis() {
            if (bulkImeis.length === 0) return;
            bulkImeis = [];
            renderBulkChips();
            toast('Cleared all scanned items', 'warn');
        }

        // ─── UPDATE DEVICE ───
        async function updateDevice() {
            let d = document.getElementById('dealerName').value;
            let i = document.getElementById('imei').value;
            if(!d || !i) {
                toast('Please enter both Dealer name and IMEI', 'error');
                return;
            }
            try {
                const res = await fetch(`${API}?action=update&dealer=${encodeURIComponent(d)}&imei=${encodeURIComponent(i)}`).then(r=>r.json());
                toast(res.message || 'Device assigned!', res.status === 'error' ? 'error' : 'success');
                if (res.status !== 'error') {
                    document.getElementById('imei').value = '';
                    document.getElementById('imeiStatus').style.display = 'none';
                }
            } catch(e) {
                toast('Network error', 'error');
            }
        }

        // ─── BULK UPDATE ───
        async function bulkUpdate() {
            let d = document.getElementById('bulkDealer').value;
            if (!d) { toast('Please enter dealer name', 'error'); return; }
            if (bulkImeis.length === 0) { toast('No IMEIs scanned', 'error'); return; }

            toast(`Processing ${bulkImeis.length} devices...`, 'warn');
            
            let success = 0, failed = 0;
            for(let imei of bulkImeis) {
                try {
                    const res = await fetch(`${API}?action=update&dealer=${encodeURIComponent(d)}&imei=${encodeURIComponent(imei)}`).then(r=>r.json());
                    if (res.status === 'error') failed++;
                    else success++;
                } catch(e) { failed++; }
            }
            toast(`✅ ${success} assigned, ❌ ${failed} failed`, failed > 0 ? 'warn' : 'success');
            
            if (success > 0) {
                bulkImeis = [];
                renderBulkChips();
            }
        }

        // ─── PENDING ───
        async function loadPending() {
            const listDiv = document.getElementById('pendingList');
            listDiv.innerHTML = '<div class="loader"><i class="fa-solid fa-circle-notch fa-spin"></i> Fetching records...</div>';
            
            await fetch(`${API}?action=sync_pending_ledger`).then(r=>r.json()).catch(() => null);
            const res = await fetch(`${API}?action=pending`).then(r=>r.json());
            if(res.length === 0) { listDiv.innerHTML = '<div style="text-align:center; padding:50px;">🎉 All Dealer Accounts Settled!</div>'; return; }

            let groups = {};
            res.forEach(item => {
                if(!groups[item.holder]) groups[item.holder] = [];
                groups[item.holder].push(item);
            });

            let html = "";
            for (let dealer in groups) {
                let items = groups[dealer];
                let safeId = dealer.replace(/[^a-zA-Z0-9]/g, '');
                let imeis = items.map(it => it.imei).join(',');

                html += `
                    <div class="dealer-box">
                        <div class="dealer-header">
                            <div>
                                <div class="dealer-name">${dealer}</div>
                                <span class="item-count">${items.length} Pending</span>
                            </div>
                        </div>

                        <div class="bulk-payout-box">
                            <span class="payout-title">Bulk Payout (Whole Group)</span>
                                <div class="pay-inputs">
                                    <input type="number" id="br_${safeId}" class="mini-input" placeholder="Selling Price Per Unit">
                                    <input type="text" id="bt_${safeId}" class="mini-input" placeholder="Bulk TXN ID">
                                </div>
                            <button class="btn-main btn-primary" style="font-size:12px; padding:12px; margin-top:10px;" onclick="bulkPay('${dealer}', '${imeis}')">
                                <i class="fa-solid fa-receipt"></i> Settle All ${items.length} Devices
                            </button>
                        </div>

                        ${items.map(it => `
                            <div class="pending-item">
                                <span class="item-imei">${it.imei}</span>
                                <span class="item-model">${it.model || 'Standard'}</span>
                                <div class="pay-inputs">
                                    <input type="number" id="r_${it.imei}" class="mini-input" placeholder="Selling Price">
                                    <input type="text" id="t_${it.imei}" class="mini-input" placeholder="TXN ID">
                                </div>
                                <button class="btn-main" style="background:var(--surface); border:1px solid var(--border); font-size:11px; padding:10px; margin-top:10px;" onclick="payDevice('${it.imei}')">
                                    Update Single
                                </button>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
            listDiv.innerHTML = html;
        }

        async function payDevice(imei) {
            let r = document.getElementById('r_'+imei).value;
            let t = document.getElementById('t_'+imei).value;
            if(!r || !t) { toast('Enter Selling Price and TXN ID', 'error'); return; }
            const form = new URLSearchParams({ action: 'payment', imei, txn: t, sale_rate: r });
            try {
                const res = await fetch(API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: form.toString(),
                    cache: 'no-store'
                }).then(r=>r.json());
                if(res.status==="success") {
                    toast(`✅ Payment updated | TXN: ${res.txn || t}`, 'success');
                    loadPending();
                } else {
                    toast(`Error: ${res.message}`, 'error');
                }
            } catch(e) {
                toast('Network error', 'error');
            }
        }

        async function bulkPay(dealer, imeiString) {
            let safeId = dealer.replace(/[^a-zA-Z0-9]/g, '');
            let r = document.getElementById('br_'+safeId).value;
            let t = document.getElementById('bt_'+safeId).value;
            if(!r || !t) { toast('Enter Bulk Selling Price and TXN ID', 'error'); return; }

            let imeis = imeiString.split(',');
            if(!confirm(`Confirm payment for ${imeis.length} devices at ₹${r} each?`)) return;

            let success = 0, failed = 0;
            for(let imei of imeis) {
                const form = new URLSearchParams({ action: 'payment', imei, txn: t, sale_rate: r });
                try {
                    const res = await fetch(API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: form.toString(),
                        cache: 'no-store'
                    }).then(r=>r.json());
                    if(res.status === "success") success++;
                    else failed++;
                } catch(e) { failed++; }
            }
            toast(`✅ ${success} settled, ❌ ${failed} failed`, failed > 0 ? 'warn' : 'success');
            if (success > 0) loadPending();
        }

        // ─── IMEI STATUS CHECK ───
        let statusT;
        async function checkImeiStatus(imei) {
            const statusDiv = document.getElementById('imeiStatus');
            if (!statusDiv) return;
            if (imei.length < 5) { statusDiv.style.display = 'none'; return; }

            clearTimeout(statusT);
            statusT = setTimeout(async () => {
                try {
                    const res = await fetch(`api_master_data.php?action=check_imei&imei=${encodeURIComponent(imei)}`).then(r => r.json());
                    statusDiv.style.display = 'block';
                    if (res.status === 'success') {
                        const d = res.data;
                        const statusStr = (d.status || "").toLowerCase();
                        const isStock = statusStr.includes('stock');
                        const branch = d.branch || 'ERD';
                        const otherBranch = (branch === 'ERD') ? 'SLM' : 'ERD';
                        
                        statusDiv.style.background = isStock ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)';
                        statusDiv.style.color = isStock ? 'var(--success)' : '#f87171';
                        
                        let displayStatus = d.status.replace('_', ' ').toUpperCase();
                        let html = `<i class="fa-solid ${isStock ? 'fa-check-circle' : 'fa-triangle-exclamation'}"></i> ` + 
                            (isStock ? `✅ DEVICE ${displayStatus} [${branch}]` : `⚠️ ALREADY SOLD [${branch}] / ISSUED TO: ${d.holder || 'UNKNOWN'}`);
                        
                        if (d.multi_branch) {
                            html += `<br><span style="font-size:10px; color:var(--warning); font-weight:800; display:block; margin-top:5px;">
                                <i class="fa-solid fa-circle-info"></i> DUPLICATE RECORD FOUND IN ${otherBranch} BRANCH
                            </span>`;
                        }
                        
                        html += `<br><span style="font-size:9px; opacity:0.8;">Model: ${d.device_model || 'Standard'}</span>`;
                        statusDiv.innerHTML = html;
                    } else {
                        statusDiv.style.background = 'rgba(255, 255, 255, 0.05)';
                        statusDiv.style.color = '#94a3b8';
                        statusDiv.innerHTML = `<i class="fa-solid fa-circle-question"></i> NOT FOUND IN ERD/SLM`;
                    }
                } catch (e) { statusDiv.style.display = 'none'; }
            }, 300);
        }
    </script>
</body>
</html>
