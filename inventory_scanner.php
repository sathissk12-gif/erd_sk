<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Stock Scanner | SK LOGIC</title>
    
    <!-- Ultra Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://unpkg.com/html5-qrcode"></script>

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
            --accent: #f43f5e;
            --bg: #030712;
            --surface: rgba(15, 23, 42, 0.6);
            --card-base: rgba(30, 41, 59, 0.3);
            --border: rgba(255, 255, 255, 0.08);
            --border-focus: rgba(139, 92, 246, 0.3);
            --text: #ffffff;
            --text-muted: #94a3b8;
            --success: #10b981;
            --warn: #f59e0b;
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

        /* ─── HEADER ─── */
        header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(3, 7, 18, 0.7); backdrop-filter: blur(25px);
            padding: calc(12px + env(safe-area-inset-top, 0px)) 25px 18px;
            border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .back-link { text-decoration: none; color: white; display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14px; }
        
        .header-status {
            display: flex; align-items: center; gap: 12px;
        }
        .sl-badge {
            background: rgba(139, 92, 246, 0.12); border: 1px solid rgba(139, 92, 246, 0.2);
            padding: 6px 16px; border-radius: 99px; font-size: 10px; font-weight: 800;
            color: var(--primary); letter-spacing: 0.5px; white-space: nowrap;
        }
        .live-dot {
            width: 8px; height: 8px; border-radius: 50%; background: var(--success);
            animation: pulse 2s infinite; display: inline-block;
        }
        @keyframes pulse { 0% { opacity:1; box-shadow:0 0 0 0 rgba(16,185,129,0.5); } 50% { opacity:0.8; box-shadow:0 0 0 6px rgba(16,185,129,0); } 100% { opacity:1; box-shadow:0 0 0 0 rgba(16,185,129,0); } }

        /* ─── CONTAINER ─── */
        .container { max-width: 520px; margin: 20px auto; padding: 0 20px; animation: slideUp 0.6s ease-out; }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        /* ─── GLASS CARD ─── */
        .glass-card { 
            background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
            padding: 22px; backdrop-filter: blur(20px); margin-bottom: 18px;
            transition: border-color 0.3s;
        }
        .glass-card:focus-within { border-color: var(--border-focus); }

        .section-title {
            font-size: 9px; font-weight: 800; color: var(--primary); text-transform: uppercase;
            letter-spacing: 1.5px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
        }

        /* ─── INPUTS ─── */
        .input-group { margin-bottom: 16px; }
        .input-group:last-child { margin-bottom: 0; }
        .input-group label {
            display: flex; align-items: center; gap: 6px;
            font-size: 10px; font-weight: 700; color: var(--text-muted);
            margin-bottom: 7px; padding-left: 4px; text-transform: uppercase; letter-spacing: 0.8px;
        }
        .input-field, .input-select { 
            width: 100%; padding: 14px 18px; background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border); border-radius: var(--radius-sm);
            color: white; font-size: 14px; font-family: inherit;
            transition: 0.3s; outline: none;
        }
        .input-field:focus, .input-select:focus {
            border-color: var(--primary); box-shadow: 0 0 20px var(--primary-glow);
        }
        .input-field::placeholder { color: var(--text-muted); opacity: 0.5; }
        .input-select {
            appearance: none; cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px;
        }
        .input-select option { background: #0f172a; color: white; }

        /* ─── SUPPLIER SUGGESTIONS ─── */
        .suggest-wrap { position: relative; }
        .suggest-list {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 100;
            background: rgba(15, 23, 42, 0.95); border: 1px solid var(--border);
            border-radius: 12px; margin-top: 4px; max-height: 180px; overflow-y: auto;
            display: none; backdrop-filter: blur(20px);
        }
        .suggest-list.open { display: block; }
        .suggest-item {
            padding: 12px 16px; font-size: 13px; font-weight: 600; cursor: pointer;
            border-bottom: 1px solid var(--border); transition: 0.2s;
        }
        .suggest-item:last-child { border-bottom: none; }
        .suggest-item:hover { background: rgba(139, 92, 246, 0.1); color: var(--primary); }
        .suggest-item .sub { font-size: 10px; color: var(--text-muted); font-weight: 400; margin-top: 2px; }

        /* ─── CAMERA ─── */
        #reader {
            width: 100%; border-radius: var(--radius); overflow: hidden; background: #000;
            border: 2px solid var(--primary); display: none; margin-bottom: 18px;
            box-shadow: 0 0 30px var(--primary-glow); transition: 0.3s;
        }
        #reader.open { display: block; }

        .camera-btn { 
            width: 100%; padding: 16px; border: none; border-radius: var(--radius-sm);
            background: var(--border); color: white; font-weight: 800; font-size: 13px;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            gap: 10px; margin-bottom: 18px; transition: 0.3s;
        }
        .camera-btn:hover { background: rgba(255,255,255,0.05); }
        .camera-btn.active { 
            background: linear-gradient(135deg, var(--accent), #e11d48);
            box-shadow: 0 10px 20px rgba(244, 63, 94, 0.3);
        }
        .camera-btn i { font-size: 18px; }

        /* ─── SCAN VIEWFINDER ─── */
        .scan-area {
            background: rgba(0, 0, 0, 0.2); border: 2px dashed var(--border);
            border-radius: var(--radius-sm); padding: 16px; min-height: 140px;
            position: relative; transition: 0.3s;
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
            margin-bottom: 12px; position: relative; z-index: 2;
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

        /* ─── CHIPS (Scanned Items) ─── */
        .chips-wrap {
            display: flex; flex-wrap: wrap; gap: 8px; min-height: 40px;
            position: relative; z-index: 2; max-height: 200px; overflow-y: auto;
            padding: 4px 0;
        }
        .chips-wrap:empty::after {
            content: 'Scanned IMEIs appear here...'; display: block;
            color: var(--text-muted); font-size: 13px; opacity: 0.4;
            padding: 10px 4px; width: 100%; text-align: center;
        }

        .chip {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(139, 92, 246, 0.12); border: 1px solid rgba(139, 92, 246, 0.2);
            padding: 6px 10px 6px 14px; border-radius: 99px;
            font-family: 'Outfit'; font-size: 12px; font-weight: 700;
            letter-spacing: 0.5px; animation: chipIn 0.3s ease-out;
        }
        @keyframes chipIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .chip.duplicate { 
            background: rgba(239, 68, 68, 0.12); border-color: rgba(239, 68, 68, 0.3);
            color: var(--danger); text-decoration: line-through;
        }
        .chip .remove {
            width: 18px; height: 18px; border-radius: 50%; border: none;
            background: rgba(255,255,255,0.1); color: var(--text-muted); cursor: pointer;
            font-size: 8px; display: flex; align-items: center; justify-content: center;
            transition: 0.2s; flex-shrink: 0;
        }
        .chip .remove:hover { background: var(--danger); color: white; }
        .chip .dup-warn { color: var(--warn); font-size: 9px; }

        /* ─── SAVE BUTTON ─── */
        .btn-save {
            width: 100%; padding: 18px; border: none; border-radius: var(--radius);
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white; font-weight: 800; font-size: 15px; cursor: pointer;
            transition: 0.3s; box-shadow: 0 15px 30px rgba(139, 92, 246, 0.3);
            display: flex; align-items: center; justify-content: center; gap: 10px;
            position: relative; overflow: hidden;
        }
        .btn-save:active { transform: scale(0.97); }
        .btn-save:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .btn-save .btn-bg {
            position: absolute; inset: 0; background: linear-gradient(135deg, var(--success), #059669);
            opacity: 0; transition: 0.5s; border-radius: inherit;
        }
        .btn-save.success .btn-bg { opacity: 1; }
        .btn-save .btn-text { position: relative; z-index: 1; }

        /* ─── STATS ROW ─── */
        .stats-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px;
        }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm);
            padding: 14px 16px; text-align: center; backdrop-filter: blur(20px);
        }
        .stat-card .stat-label { font-size: 8px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1px; }
        .stat-card .stat-val { font-size: 20px; font-weight: 800; font-family: 'Outfit'; margin-top: 4px; }
        .stat-card .stat-sub { font-size: 9px; color: var(--text-muted); margin-top: 2px; }

        /* ─── TOAST ─── */
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
        .toast.warn .icon { color: var(--warn); }

        /* ─── LOADER OVERLAY ─── */
        .loader {
            position: fixed; inset: 0; background: rgba(2,6,23,0.85);
            backdrop-filter: blur(10px); display: none; align-items: center;
            justify-content: center; z-index: 5000; flex-direction: column; gap: 16px;
        }
        .loader.open { display: flex; }
        .spinner { width: 36px; height: 36px; border: 3px solid rgba(255,255,255,0.05); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loader .load-text { font-size: 12px; font-weight: 600; color: var(--text-muted); letter-spacing: 1px; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 400px) {
            .header-status .sl-badge { font-size: 8px; padding: 4px 12px; }
            .stats-row { gap: 8px; }
            .stat-card .stat-val { font-size: 17px; }
        }
    </style>
</head>
<body>

    <!-- ═══ HEADER ═══ -->
    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <div class="header-status">
            <span class="sl-badge" id="slLabel"><i class="fa-solid fa-hashtag"></i> SL: ---</span>
            <span class="live-dot" title="Live"></span>
        </div>
    </header>

    <div class="container">

        <!-- ═══ SUPPLIER & MODEL ═══ -->
        <div class="glass-card">
            <div class="section-title"><i class="fa-solid fa-boxes"></i> Stock Entry</div>
            
            <div class="input-group">
                <label><i class="fa-solid fa-truck"></i> Supplier / Vendor</label>
                <div class="suggest-wrap">
                    <input type="text" id="supplier" class="input-field" placeholder="Type supplier name..." autocomplete="off" oninput="onSupplierInput(this.value)">
                    <div class="suggest-list" id="suggestList"></div>
                </div>
            </div>

            <div class="input-group">
                <label><i class="fa-solid fa-mobile-screen"></i> Device Model</label>
                <select id="model" class="input-select"><option value="">Loading models...</option></select>
            </div>
        </div>

        <!-- ═══ CAMERA TOGGLE ═══ -->
        <button class="camera-btn" id="camBtn" onclick="toggleCam()">
            <i class="fa-solid fa-camera"></i> <span>Start QR / Barcode Scanner</span>
        </button>

        <!-- ═══ QR READER CONTAINER ═══ -->
        <div id="reader"></div>

        <!-- ═══ SCANNED ITEMS ═══ -->
        <div class="glass-card">
            <div class="section-title"><i class="fa-solid fa-list-check"></i> Scanned Serials</div>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-label">Scanned</div>
                    <div class="stat-val" id="statScanned">0</div>
                    <div class="stat-sub">IMEIs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Unique</div>
                    <div class="stat-val" id="statUnique">0</div>
                    <div class="stat-sub" id="statDupSub">— duplicates</div>
                </div>
            </div>

            <div class="scan-area" id="scanArea">
                <div class="scan-overlay"></div>
                <div class="scan-area-top">
                    <span class="count-badge" id="countBadge">0 Units</span>
                    <button class="clear-btn" onclick="clearAll()"><i class="fa-solid fa-trash-can"></i> Clear All</button>
                </div>
                <div class="chips-wrap" id="chipsWrap"></div>
                <textarea id="hiddenList" style="display:none"></textarea>
            </div>
        </div>

        <!-- ═══ MANUAL INPUT ═══ -->
        <div class="glass-card" style="padding: 16px 22px;">
            <div style="display:flex; gap:8px;">
                <input type="text" id="manualImei" class="input-field" placeholder="Or type IMEI manually..." 
                       style="padding:12px 16px; border-radius:12px; flex:1;" autocomplete="off">
                <button onclick="addManualImei()" style="
                    padding:12px 18px; border:none; border-radius:12px;
                    background: linear-gradient(135deg, var(--secondary), #0891b2);
                    color:white; font-weight:800; font-size:12px; cursor:pointer; white-space:nowrap;
                    box-shadow: 0 5px 15px rgba(6,182,212,0.3); transition:0.2s;
                "><i class="fa-solid fa-plus"></i> Add</button>
            </div>
        </div>

        <!-- ═══ SAVE BUTTON ═══ -->
        <button class="btn-save" id="saveBtn" onclick="submitStock()">
            <span class="btn-bg"></span>
            <span class="btn-text"><i class="fa-solid fa-cloud-arrow-up"></i> Save Current Batch</span>
        </button>

    </div>

    <!-- ═══ TOAST CONTAINER ═══ -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- ═══ LOADER OVERLAY ═══ -->
    <div class="loader" id="loader">
        <div class="spinner"></div>
        <div class="load-text">Saving to Stock...</div>
    </div>

    <script>
        // ─── STATE ───
        let models = [];
        let knownSuppliers = [];
        let scanner = null;
        let isCam = false;
        let scannedImeis = [];   // Array of {imei, duplicate}

        // ─── INIT ───
        async function init() {
            try {
                const res = await fetch('api_master_data.php?action=get_inventory_config');
                const data = await res.json();
                if (data.status === 'success') {
                    models = data.models || [];
                    const sel = document.getElementById('model');
                    sel.innerHTML = '<option value="">— Select Device Model —</option>';
                    models.forEach(m => {
                        const o = document.createElement('option');
                        o.value = m.device_model;
                        o.innerText = m.device_model + '  |  ₹' + (parseInt(m.rate) || 0).toLocaleString('en-IN');
                        sel.appendChild(o);
                    });
                    document.getElementById('slLabel').innerHTML = `<i class="fa-solid fa-hashtag"></i> SL: #${data.next_sl}`;
                } else {
                    toast('Failed to load configuration', 'error');
                }
            } catch(e) {
                toast('Connection error loading data', 'error');
            }

            // Load known suppliers from dealer list
            try {
                const r = await fetch('api_master_data.php?action=get_customer_names');
                const list = await r.json();
                if (Array.isArray(list)) {
                    knownSuppliers = [...new Set(list.map(c => (c.name || '').toUpperCase()).filter(Boolean))];
                }
            } catch(e) { /* optional */ }
        }

        // ─── SUPPLIER AUTO-SUGGEST ───
        function onSupplierInput(val) {
            const list = document.getElementById('suggestList');
            const upper = val.toUpperCase().trim();
            if (!upper || upper.length < 1) { list.classList.remove('open'); return; }

            const matches = knownSuppliers.filter(s => s.includes(upper));
            if (matches.length === 0 || matches.length > 50) { list.classList.remove('open'); return; }

            list.innerHTML = matches.slice(0, 8).map(s => `
                <div class="suggest-item" onclick="selectSupplier('${s.replace(/'/g, "\\'")}')">
                    <i class="fa-solid fa-building" style="margin-right:6px;color:var(--primary);"></i> ${s}
                </div>
            `).join('');
            list.classList.add('open');
        }

        function selectSupplier(name) {
            document.getElementById('supplier').value = name;
            document.getElementById('suggestList').classList.remove('open');
        }

        // Close suggestions on click outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.suggest-wrap')) {
                document.getElementById('suggestList').classList.remove('open');
            }
        });

        // ─── CAMERA ───
        async function toggleCam() {
            const btn = document.getElementById('camBtn');
            const div = document.getElementById('reader');
            const area = document.getElementById('scanArea');

            if (!isCam) {
                div.style.display = 'block';
                div.classList.add('open');
                area.classList.add('active');
                btn.classList.add('active');
                btn.innerHTML = `<i class="fa-solid fa-stop-circle"></i> <span>Stop Scanner</span>`;
                await startScanner();
            } else {
                await stopScanner();
                div.style.display = 'none';
                div.classList.remove('open');
                area.classList.remove('active');
                btn.classList.remove('active');
                btn.innerHTML = `<i class="fa-solid fa-camera"></i> <span>Start QR / Barcode Scanner</span>`;
            }
            isCam = !isCam;
        }

        async function startScanner() {
            try {
                scanner = new Html5Qrcode("reader");
                await scanner.start(
                    { facingMode: "environment" },
                    { fps: 15, qrbox: 240 },
                    onScanSuccess,
                    () => {} // ignore errors
                );
            } catch(e) {
                toast('Camera access denied or unavailable', 'error');
                isCam = true; toggleCam(); // Reset button state
            }
        }

        async function stopScanner() {
            if (scanner) {
                try { await scanner.stop(); } catch(e) {}
                try { await scanner.clear(); } catch(e) {}
                scanner = null;
            }
        }

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

        function playErrorBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                [300, 250].forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.value = freq;
                    const t = ctx.currentTime + i * 0.12;
                    gain.gain.setValueAtTime(0.2, t);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.1);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(t);
                    osc.stop(t + 0.1);
                });
                setTimeout(() => ctx.close(), 400);
            } catch(e) { /* audio not supported */ }
        }

        function onScanSuccess(id) {
            // Vibrate + sound feedback
            if (window.navigator.vibrate) window.navigator.vibrate(50);
            playBeep(2000, 100);
            
            // Only add if not already in the list (case-insensitive)
            const exists = scannedImeis.some(item => item.imei.toUpperCase() === id.toUpperCase());
            scannedImeis.push({ imei: id, duplicate: exists });
            
            renderChips();
            updateStats();
            toast('📱 Scanned: ' + id, exists ? 'warn' : 'success');
        }

        // ─── MANUAL IMEI ───
        function addManualImei() {
            const input = document.getElementById('manualImei');
            const val = input.value.trim().toUpperCase();
            if (!val) { input.style.borderColor = '#ef4444'; setTimeout(() => input.style.borderColor = '', 1000); return; }

            const exists = scannedImeis.some(item => item.imei.toUpperCase() === val);
            scannedImeis.push({ imei: val, duplicate: exists });
            input.value = '';
            input.focus();
            renderChips();
            updateStats();
            toast('📱 Added: ' + val, exists ? 'warn' : 'success');
        }

        // Enter key for manual input
        document.getElementById('manualImei').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') addManualImei();
        });

        // ─── CHIPS RENDER ───
        function renderChips() {
            const wrap = document.getElementById('chipsWrap');
            wrap.innerHTML = scannedImeis.map((item, idx) => `
                <span class="chip ${item.duplicate ? 'duplicate' : ''}">
                    ${item.duplicate ? '<i class="fa-solid fa-triangle-exclamation dup-warn"></i>' : '<i class="fa-solid fa-qrcode" style="font-size:10px;opacity:0.5;"></i>'}
                    ${item.imei}
                    <button class="remove" onclick="removeImei(${idx})" title="Remove"><i class="fa-solid fa-xmark"></i></button>
                </span>
            `).join('');
            document.getElementById('hiddenList').value = scannedImeis.map(i => i.imei).join('\n');
            document.getElementById('countBadge').innerText = scannedImeis.length + ' Units';
        }

        function removeImei(idx) {
            scannedImeis.splice(idx, 1);
            renderChips();
            updateStats();
        }

        function clearAll() {
            if (scannedImeis.length === 0) return;
            scannedImeis = [];
            renderChips();
            updateStats();
            toast('Cleared all scanned items', 'warn');
        }

        // ─── STATS ───
        function updateStats() {
            const total = scannedImeis.length;
            const unique = scannedImeis.filter(i => !i.duplicate).length;
            const dups = total - unique;
            document.getElementById('statScanned').innerText = total;
            document.getElementById('statUnique').innerText = unique;
            document.getElementById('statDupSub').innerText = dups > 0 ? dups + ' duplicate(s)' : '✓ all unique';
            document.getElementById('statDupSub').style.color = dups > 0 ? 'var(--warn)' : 'var(--success)';
        }

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
            }, 2500);
        }

        // ─── SUBMIT ───
        async function submitStock() {
            const supplier = document.getElementById('supplier').value.toUpperCase().trim();
            const model = document.getElementById('model').value;
            const imeis = scannedImeis.map(i => i.imei).filter(Boolean);

            if (!supplier) { toast('Please enter supplier name', 'error'); document.getElementById('supplier').focus(); return; }
            if (!model) { toast('Please select a device model', 'error'); document.getElementById('model').focus(); return; }
            if (imeis.length === 0) { toast('No IMEIs scanned or entered', 'error'); return; }

            // Check for duplicates - block save if duplicates exist
            const dups = scannedImeis.filter(i => i.duplicate);
            if (dups.length > 0) {
                if (!confirm(`⚠️ ${dups.length} duplicate IMEI(s) found!\n\nRemove them and continue saving only unique items?`)) {
                    // Remove duplicates
                    scannedImeis = scannedImeis.filter(i => !i.duplicate);
                    renderChips();
                    updateStats();
                    if (scannedImeis.length === 0) {
                        toast('No unique IMEIs to save', 'error');
                        return;
                    }
                }
            }

            const uniqueImeis = scannedImeis.filter(i => !i.duplicate).map(i => i.imei);
            if (uniqueImeis.length === 0) { toast('No unique IMEIs to save', 'error'); return; }

            const rate = models.find(m => m.device_model === model)?.rate || 0;

            // Show loader
            const loader = document.getElementById('loader');
            loader.querySelector('.load-text').innerText = `Saving ${uniqueImeis.length} device(s)...`;
            loader.classList.add('open');

            const fd = new FormData();
            fd.append('action', 'add_inventory_stock');
            fd.append('supplier', supplier);
            fd.append('model', model);
            fd.append('rate', rate);
            fd.append('imeis', uniqueImeis.join('\n'));

            try {
                const res = await fetch('api_master_data.php', { method: 'POST', body: fd });
                const r = await res.json();
                loader.classList.remove('open');

                if (r.status === 'success') {
                    toast('🎉 ' + r.message, 'success');
                    // Animate save button
                    const btn = document.getElementById('saveBtn');
                    btn.classList.add('success');
                    btn.querySelector('.btn-text').innerHTML = '<i class="fa-solid fa-check"></i> Saved!';
                    
                    // Reset after 2 seconds
                    setTimeout(() => {
                        btn.classList.remove('success');
                        btn.querySelector('.btn-text').innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Save Current Batch';
                        // Refresh SL number
                        init();
                    }, 2000);
                    
                    // Clear scanned items
                    scannedImeis = [];
                    renderChips();
                    updateStats();
                } else {
                    toast('Error: ' + (r.error || 'Unknown error'), 'error');
                }
            } catch(e) {
                loader.classList.remove('open');
                toast('Network error - check connection', 'error');
            }
        }

        // ─── START ───
        window.onload = init;
    </script>
</body>
</html>
