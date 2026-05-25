<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Sales Console | SK LOGIC</title>
    
    <!-- Ultra Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="theme_engine.js"></script>

    <!-- 🔥 Security -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>
    <script src="firebase_config.js"></script>
    <script>protectPage();</script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="theme_engine.js"></script>

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
        
        .mode-chip { 
            background: var(--primary); padding: 8px 16px; border-radius: 99px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
            box-shadow: 0 0 15px var(--primary-glow); border: none; color: white; cursor: pointer;
        }
        .mode-chip.edit { background: var(--secondary); box-shadow: 0 0 15px rgba(6, 182, 212, 0.4); }

        .container { max-width: 500px; margin: 20px auto; padding: 0 20px; animation: slideUp 0.6s ease-out; }
        
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        /* 🟦 Glass Cards */
        .glass-card { 
            background: var(--surface); border: 1px solid var(--border); border-radius: 28px; padding: 25px; 
            backdrop-filter: blur(20px); box-shadow: 0 20px 40px rgba(0,0,0,0.4); margin-bottom: 20px;
        }

        .section-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .section-label i { color: var(--primary); }

        /* 📋 Form Elements */
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; padding-left: 4px; }
        .input-field { 
            width: 100%; padding: 16px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 15px; font-family: inherit; transition: 0.3s;
        }
        .input-field:focus { border-color: var(--primary); background: rgba(15, 23, 42, 0.8); box-shadow: 0 0 20px rgba(139, 92, 246, 0.1); }

        .dual-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

        /* 🎭 Profit Panel */
        .profit-panel { 
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 24px; padding: 25px; text-align: center; margin-bottom: 20px;
            transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .profit-val { font-size: 32px; font-weight: 800; font-family: 'Outfit'; color: #4ade80; display: block; margin-top: 5px; }

        /* 🔘 Custom Buttons */
        .btn-main {
            width: 100%; padding: 20px; border: none; border-radius: 22px; 
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white; font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.3s;
            box-shadow: 0 15px 30px rgba(139, 92, 246, 0.3);
        }
        .btn-main:active { transform: scale(0.96); }

        .search-pane { display: none; margin-bottom: 20px; animation: slideDown 0.3s ease; }
        .search-pane.active { display: block; }
        @keyframes slideDown { from { opacity:0; height:0; } to { opacity:1; height:auto; } }

        .suggestion-box {
            position: absolute; top: 100%; left: 0; right: 0; background: rgba(15, 23, 42, 0.98); 
            border: 1px solid var(--border); border-radius: 16px; backdrop-filter: blur(30px); z-index: 2000;
            max-height: 250px; overflow-y: auto; margin-top: 5px; display: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }

        .select-field { 
            width: 100%; padding: 16px 20px; background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border); border-radius: 16px;
            color: white; font-size: 15px; appearance: none; cursor: pointer;
        }

        /* 📱 Mobile Specifics */
        .sim-toggle { display: flex; background: var(--border); padding: 5px; border-radius: 12px; gap: 5px; }
        .sim-btn { flex: 1; padding: 10px; border: none; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; background: transparent; color: var(--text-muted); transition: 0.3s; }
        .sim-btn.active { background: var(--primary); color: white; box-shadow: 0 5px 10px rgba(0,0,0,0.2); }
        .sim-btn:disabled { opacity: 0.4; cursor: not-allowed; }

        .toggle-row {
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border);
            border-radius: 16px; padding: 14px 18px; gap: 12px;
        }
        .toggle-label { font-size: 14px; font-weight: 800; color: white; }
        .toggle-label.off { color: var(--text-muted); }
        .switch { position: relative; display: inline-block; width: 52px; height: 30px; flex-shrink: 0; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .switch .slider {
            position: absolute; cursor: pointer; inset: 0;
            background: rgba(148, 163, 184, 0.35); border-radius: 30px; transition: 0.3s;
        }
        .switch .slider:before {
            position: absolute; content: ""; height: 22px; width: 22px; left: 4px; bottom: 4px;
            background: white; border-radius: 50%; transition: 0.3s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
        .switch input:checked + .slider { background: var(--primary); box-shadow: 0 0 12px var(--primary-glow); }
        .switch input:checked + .slider:before { transform: translateX(22px); }
        .switch input:disabled + .slider { opacity: 0.45; cursor: not-allowed; }
        .choice-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .choice-btn {
            border: 1px solid var(--border); border-radius: 14px; padding: 14px 10px; text-align: center;
            background: rgba(15, 23, 42, 0.65); color: white; font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.25s;
        }
        .choice-btn:hover, .choice-btn.active { border-color: var(--primary); background: rgba(139, 92, 246, 0.18); box-shadow: 0 0 20px rgba(139, 92, 246, 0.12); }
        .field-note { font-size: 11px; color: var(--text-muted); margin-top: 8px; padding-left: 4px; }
    </style>
</head>
<body>

    <header>
        <a href="index.html" class="back-link"><i class="fa-solid fa-chevron-left"></i> Console</a>
        <button class="mode-chip" id="modeBtn" onclick="toggleEdit()">New Entry</button>
    </header>

    <div class="container">
        
        <!-- ✨ Profit Monitor -->
        <div id="profitPanel" class="profit-panel" style="display:none;">
            <span style="font-size: 11px; font-weight: 800; color: #10b981; letter-spacing: 2px;">PROFIT PREVIEW</span>
            <span class="profit-val" id="profitDisplay">₹ 0</span>
            <div id="breakdown" style="font-size: 10px; opacity: 0.6; margin-top: 10px;"></div>
        </div>

        <div class="search-pane" id="editSearchBox">
            <div class="section-label" style="color:var(--secondary);">Search Record to Edit</div>
            <div style="display:flex; gap:10px;">
                <input type="text" id="editVehicle" class="input-field" placeholder="Enter Vehicle No..." oninput="this.value = this.value.toUpperCase()">
                <button onclick="loadRecord()" class="mode-chip edit" style="height:55px; width:60px; border-radius:16px;"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </div>

        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-user-circle"></i> Customer Identity</div>
            <div class="input-group">
                <label>Vehicle Number</label>
                <input type="text" id="vehicle" class="input-field" placeholder="TN01AA1234" oninput="this.value = this.value.toUpperCase()">
            </div>
            
            <div class="input-group" style="position:relative;">
                <label>Customer Name</label>
                <div style="display:flex; gap:10px;">
                    <input type="text" id="customer" class="input-field" placeholder="Type for Search..." oninput="searchClients(this.value)">
                    <button onclick="openModal()" class="mode-chip" style="height:55px; width:55px; border-radius:16px;">+</button>
                </div>
                <div id="suggestions" class="suggestion-box"></div>
            </div>

            <div class="dual-grid">
                <div class="input-group"><label>Mobile No</label><input type="tel" id="mobile" class="input-field" placeholder="987xxxxxxx"></div>
                <div class="input-group"><label>Location</label><input type="text" id="location" class="input-field" placeholder="City Area"></div>
            </div>
            <div class="input-group">
                <label>Sales Person Name</label>
                <input type="text" id="salesPerson" class="input-field" placeholder="Enter Sales Person Name">
            </div>
        </div>

        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-microchip"></i> Hardware & SIM</div>
            <div class="input-group">
                <label>Device IMEI</label>
                <div style="display:flex; gap:10px; margin-bottom: 5px;">
                    <input type="text" id="imei" class="input-field" placeholder="15 Digit Number" inputmode="numeric" pattern="[0-9]*" oninput="checkImeiStatus(this.value); calculateProfit();">
                    <button class="mode-chip" style="height:55px; width:55px; border-radius:16px; background:var(--secondary);" onclick="startScanner()"><i class="fa-solid fa-camera"></i></button>
                </div>
                <div id="imeiStatus" style="font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 8px; display: none;"></div>
                <!-- 📱 Device Info Card (shows when IMEI found in stock) -->
                <div id="deviceInfoCard" style="display:none; margin-top:10px; background:rgba(139,92,246,0.08); border:1px solid rgba(139,92,246,0.2); border-radius:16px; padding:14px 16px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:44px; height:44px; background:linear-gradient(135deg,#8b5cf6,#6366f1); border-radius:12px; display:flex; align-items:center; justify-content:center; color:white; font-size:20px; flex-shrink:0;">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div id="deviceModelDisplay" style="font-weight:800; font-size:16px; color:white;"></div>
                            <div style="display:flex; gap:10px; margin-top:4px; flex-wrap:wrap;">
                                <span id="deviceRateDisplay" style="font-size:11px; color:#4ade80; font-weight:700;">₹ 0</span>
                                <span id="deviceSupplierDisplay" style="font-size:11px; color:var(--text-muted);"></span>
                                <span id="deviceStockStatus" style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dual-grid">
                <div class="input-group">
                    <label>SIM Usage</label>
                    <div class="toggle-row">
                        <span class="toggle-label" id="simUsageLabel">SIM</span>
                        <label class="switch" title="SIM on / off">
                            <input type="checkbox" id="simUsageToggle" checked onchange="setSimUsage(this.checked)">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="field-note">Toggle ON = SIM · OFF = SIM illama save</div>
                </div>
                <div class="input-group">
                    <label>SIM Type</label>
                    <div class="sim-toggle">
                        <button type="button" class="sim-btn active" id="btnBASIC" onclick="setSim('BASIC')">BASIC</button>
                        <button type="button" class="sim-btn" id="btnVOICE" onclick="setSim('VOICE')">VOICE</button>
                    </div>
                    <input type="hidden" id="simType" value="BASIC">
                    <input type="hidden" id="hasSim" value="YES">
                </div>
            </div>
            <div class="input-group" id="simNumberGroup">
                <label>SIM Number</label>
                <input type="text" id="sim" class="input-field" placeholder="Last 4-6 Digits">
            </div>
            <div class="dual-grid">
                <div class="input-group">
                    <label>Platform/Software</label>
                    <select id="software" class="select-field" onchange="handleSoftwareChange()"><option value="">Select</option></select>
                    <div class="field-note" id="softwareDurationText">Default duration: 1 Year</div>
                    <input type="hidden" id="softwareDuration" value="1_year">
                </div>
                <div class="input-group">
                    <label>Relay Kit</label>
                    <select id="relay" class="select-field" onchange="calculateProfit()"><option value="NO">NO</option><option value="YES">YES</option></select>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="section-label"><i class="fa-solid fa-wallet"></i> Billing Details</div>
            <div class="dual-grid">
                <div class="input-group"><label>Sell Price</label><input type="number" id="price" class="input-field" value="0" oninput="calculateProfit()"></div>
                <div class="input-group"><label>Discount</label><input type="number" id="discount" class="input-field" value="0" oninput="calculateProfit()"></div>
            </div>
            <div class="dual-grid">
                <div class="input-group"><label>Paid Amount</label><input type="number" id="paid" class="input-field" value="0"></div>
                <div class="input-group"><label>Technician Payout</label><input type="number" id="payout" class="input-field" value="0" oninput="calculateProfit()"></div>
            </div>
            <div class="input-group">
                <label>Payment Method</label>
                <select id="payMode" class="select-field">
                    <option value="CASH">CASH</option>
                    <option value="UPI / GPAY">GPAY / UPI</option>
                    <option value="BANK">BANK / NEFT</option>
                    <option value="CREDIT">CREDIT / PENDING</option>
                </select>
            </div>
        </div>

        <button class="btn-main" id="saveBtn" onclick="saveSales()">
            <i class="fa-solid fa-cloud-arrow-up"></i> <span id="btnText">Save Sales Record</span>
        </button>

    </div>

    <!-- 🎭 Client Modal -->
    <div id="modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.9); backdrop-filter:blur(20px); z-index:9000; align-items:center; justify-content:center; padding:25px;">
        <div class="glass-card" style="width:100%; max-width:400px; margin:0;">
            <div class="section-label">Create New Client</div>
            <div class="input-group"><label>Full Name</label><input type="text" id="mName" class="input-field"></div>
            <div class="input-group"><label>Contact</label><input type="tel" id="mContact" class="input-field"></div>
            <div class="input-group"><label>Location</label><input type="text" id="mLoc" class="input-field"></div>
            <button class="btn-main" onclick="registerClient()">Add Record</button>
            <button class="mode-chip" style="width:100%; margin-top:15px; background:var(--card-base);" onclick="closeModal()">Dismiss</button>
        </div>
    </div>

    <!-- 📸 Scan Modal -->
    <div id="scanModal" style="display:none; position:fixed; inset:0; background:black; z-index:10000; flex-direction:column;">
        <div style="padding:20px; display:flex; justify-content:space-between; align-items:center; color:white;">
            <div style="font-weight:800;">SCAN DEVICE IMEI</div>
            <i class="fa-solid fa-circle-xmark" style="font-size:24px;" onclick="stopScanner()"></i>
        </div>
        <div id="reader" style="flex:1;"></div>
    </div>

    <div id="durationModal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(20px); z-index:9500; align-items:center; justify-content:center; padding:20px;">
        <div class="glass-card" style="width:100%; max-width:430px; margin:0;">
            <div class="section-label"><i class="fa-solid fa-clock"></i> Select Software Duration</div>
            <div class="choice-grid">
                <button type="button" class="choice-btn" onclick="setSoftwareDuration('13_month')">13 Months</button>
                <button type="button" class="choice-btn" onclick="setSoftwareDuration('14_month')">14 Months</button>
                <button type="button" class="choice-btn active" onclick="setSoftwareDuration('1_year')">1 Year</button>
                <button type="button" class="choice-btn" onclick="setSoftwareDuration('2_year')">2 Years</button>
                <button type="button" class="choice-btn" onclick="setSoftwareDuration('3_year')">3 Years</button>
                <button type="button" class="choice-btn" onclick="setSoftwareDuration('4_year')">4 Years</button>
            </div>
            
            <div style="margin-top: 15px; display: flex; gap: 10px; align-items: center;">
                <div style="flex: 1;">
                    <label style="font-size: 10px; color: var(--text-muted); display: block; margin-bottom: 5px;">Custom Months</label>
                    <input type="number" id="customMonths" class="input-field" style="padding: 12px 15px; height: 48px; font-size: 14px;" placeholder="Eg: 2, 5, 24">
                </div>
                <button type="button" class="mode-chip" style="height: 48px; margin-top: 20px; flex-shrink: 0; background: var(--secondary);" onclick="applyCustomDuration()">Apply</button>
            </div>
            <div class="field-note" style="margin-top:14px;">Software rate + SIM rate selected duration base-la calculate ஆகும்.</div>
            <button type="button" class="mode-chip" style="width:100%; margin-top:18px; background:var(--card-base);" onclick="closeDurationModal()">Close</button>
        </div>
    </div>

    <script>
        // Feature 4: Scanner Logic
        let html5QrCode;
        let suppressDurationPrompt = false;
        const durationLabels = {
            '13_month': '13 Months',
            '14_month': '14 Months',
            '1_year': '1 Year',
            '2_year': '2 Years',
            '3_year': '3 Years',
            '4_year': '4 Years'
        };

        function getDurationLabel(key) {
            if (durationLabels[key]) return durationLabels[key];
            const m = key.match(/^(\d+)_(month|year)$/);
            if (m) {
                const num = m[1];
                const unit = m[2];
                return `${num} ${unit === 'month' ? (num == 1 ? 'Month' : 'Months') : (num == 1 ? 'Year' : 'Years')}`;
            }
            return '1 Year';
        }
        function startScanner() {
            document.getElementById('scanModal').style.display = 'flex';
            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 150 } },
                (decodedText) => {
                    document.getElementById('imei').value = decodedText;
                    stopScanner();
                    calculateProfit();
                }
            ).catch(err => alert("Camera Error: " + err));
        }
        function stopScanner() {
            if(html5QrCode) html5QrCode.stop().then(() => {
                document.getElementById('scanModal').style.display = 'none';
            });
            else document.getElementById('scanModal').style.display = 'none';
        }

        const API = "api_sales.php";
        let isEdit = false;
        let clients = [];
        let systemSettings = {};

        // 📊 Smooth Number Counter
        function animateValue(obj, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const cur = Math.floor(progress * (end - start) + start);
                obj.innerText = '₹ ' + cur.toLocaleString();
                if (progress < 1) window.requestAnimationFrame(step);
            };
            window.requestAnimationFrame(step);
        }

        async function fetchInitial() {
            // Software List
            const sRes = await fetch(API + "?action=softwareList");
            const sData = await sRes.json();
            const soft = document.getElementById('software');
            sData.forEach(it => {
                let op = document.createElement('option');
                op.value = it.name; op.innerText = it.name;
                soft.appendChild(op);
            });

            // Master Client List
            const cRes = await fetch('api_master_data.php?action=get_customer_names');
            clients = await cRes.json();

            // System Settings (Google Review Link)
            const setRes = await fetch('api_master_data.php?action=get_settings');
            systemSettings = await setRes.json();
        }

        function toggleEdit() {
            isEdit = !isEdit;
            const pane = document.getElementById('editSearchBox');
            const btn = document.getElementById('modeBtn');
            const saveBtnText = document.getElementById('btnText');
            
            pane.classList.toggle('active', isEdit);
            btn.classList.toggle('edit', isEdit);
            btn.innerText = isEdit ? "Mode: Editing" : "New Entry";
            saveBtnText.innerText = isEdit ? "Update Changes" : "Save Sales Record";
        }

        function setSim(t) {
            document.getElementById('simType').value = t;
            document.getElementById('btnBASIC').classList.toggle('active', t === 'BASIC');
            document.getElementById('btnVOICE').classList.toggle('active', t === 'VOICE');
            calculateProfit();
        }

        function setSimUsage(enabled) {
            document.getElementById('hasSim').value = enabled ? 'YES' : 'NO';
            const toggle = document.getElementById('simUsageToggle');
            if (toggle) toggle.checked = enabled;
            const label = document.getElementById('simUsageLabel');
            if (label) {
                label.innerText = enabled ? 'SIM' : 'Skip SIM';
                label.classList.toggle('off', !enabled);
            }
            document.getElementById('simNumberGroup').style.display = enabled ? 'block' : 'none';
            document.getElementById('btnBASIC').disabled = !enabled;
            document.getElementById('btnVOICE').disabled = !enabled;
            if (!enabled) {
                document.getElementById('sim').value = '';
            }
            calculateProfit();
        }

        function handleSoftwareChange() {
            const software = document.getElementById('software').value;
            if (!software) {
                document.getElementById('softwareDurationText').innerText = 'Default duration: 1 Year';
                return calculateProfit();
            }
            if (!suppressDurationPrompt) {
                openDurationModal();
            }
            calculateProfit();
        }

        function openDurationModal() {
            document.getElementById('durationModal').style.display = 'flex';
            updateDurationButtons();
        }

        function closeDurationModal() {
            document.getElementById('durationModal').style.display = 'none';
        }

        function updateDurationButtons() {
            const active = document.getElementById('softwareDuration').value;
            document.querySelectorAll('#durationModal .choice-btn').forEach(btn => {
                const text = btn.textContent.trim();
                // Check if button text matches the formatted label of the active duration
                btn.classList.toggle('active', getDurationLabel(active) === text);
            });
        }

        function applyCustomDuration() {
            const months = document.getElementById('customMonths').value;
            if (!months || months <= 0) return alert("Valid month number enter pannunga");
            setSoftwareDuration(`${months}_month`);
            document.getElementById('customMonths').value = '';
        }

        function setSoftwareDuration(duration) {
            document.getElementById('softwareDuration').value = duration;
            document.getElementById('softwareDurationText').innerText = `Selected duration: ${getDurationLabel(duration)}`;
            updateDurationButtons();
            closeDurationModal();
            calculateProfit();
        }

        async function calculateProfit() {
            const imei = document.getElementById('imei').value;
            const software = document.getElementById('software').value;
            const simType = document.getElementById('hasSim').value === 'YES' ? document.getElementById('simType').value : 'NO_SIM';
            const softwareDuration = document.getElementById('softwareDuration').value;
            const relay = document.getElementById('relay').value;
            const sell = parseFloat(document.getElementById('price').value || 0);
            const disc = parseFloat(document.getElementById('discount').value || 0);
            const pay = parseFloat(document.getElementById('payout').value || 0);
            const panel = document.getElementById('profitPanel');
            const display = document.getElementById('profitDisplay');

            if (!software) {
                panel.style.display = 'none';
                return;
            }

            try {
                const res = await fetch(`${API}?action=get_preview&imei=${encodeURIComponent(imei)}&software=${encodeURIComponent(software)}&softwareDuration=${encodeURIComponent(softwareDuration)}&simType=${simType}&relay=${relay}`);
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                const netSell = sell - disc;
                const totalCost = Number(data.device_cost || 0) + Number(data.software_cost || 0) + Number(data.sim_cost || 0) + Number(data.relay_cost || 0) + pay;
                const profit = netSell - totalCost;

                panel.style.display = 'block';
                const start = parseInt(display.innerText.replace(/[^0-9-]/g, '')) || 0;
                animateValue(display, start, profit, 1000);
                document.getElementById('breakdown').innerHTML = `Cost: Rs.${totalCost.toLocaleString()} | Margin: Rs.${profit.toLocaleString()}`;
            } catch (err) {
                panel.style.display = 'block';
                display.innerText = 'Rs. 0';
                document.getElementById('breakdown').innerText = 'Preview load aagala. Software / IMEI data check pannunga.';
                console.error('Profit preview failed', err);
            }
        }
        function searchClients(q) {
            const box = document.getElementById('suggestions');
            if (q.length < 2) { box.style.display = 'none'; return; }
            const matches = clients.filter(c => c.name.toLowerCase().includes(q.toLowerCase()) || (c.mobile && c.mobile.includes(q))).slice(0, 5);
            if (matches.length > 0) {
                box.innerHTML = matches.map(c => `
                    <div style="padding:15px; border-bottom:1px solid rgba(255,255,255,0.05); cursor:pointer;" onclick="selectClient('${c.name}','${c.mobile}','${c.location}')">
                        <div style="font-weight:700;">${c.name}</div>
                        <div style="font-size:10px; opacity:0.6;">${c.mobile || 'No Phone'} • ${c.location || 'No Loc'}</div>
                    </div>
                `).join('');
                box.style.display = 'block';
            } else { box.style.display = 'none'; }
        }

        function selectClient(n, m, l) {
            document.getElementById('customer').value = n;
            document.getElementById('mobile').value = m;
            document.getElementById('location').value = l;
            document.getElementById('suggestions').style.display = 'none';
        }

        async function saveSales() {
            const v = document.getElementById('vehicle').value;
            if(!v) return alert("Vehicle No required");

            const fd = new FormData();
            fd.append('action', isEdit ? 'updateSale' : 'saveSale');
            fd.append('vehicle', v);
            fd.append('imei', document.getElementById('imei').value);
            fd.append('simNumber', document.getElementById('sim').value);
            fd.append('software', document.getElementById('software').value);
            fd.append('softwareDuration', document.getElementById('softwareDuration').value);
            fd.append('relay', document.getElementById('relay').value);
            fd.append('customer', document.getElementById('customer').value);
            fd.append('location', document.getElementById('location').value);
            fd.append('mobileNumber', document.getElementById('mobile').value);
            fd.append('simType', document.getElementById('hasSim').value === 'YES' ? document.getElementById('simType').value : 'NO_SIM');
            fd.append('sellingPrice', document.getElementById('price').value);
            fd.append('discountAmount', document.getElementById('discount').value);
            fd.append('receivedAmount', document.getElementById('paid').value);
            fd.append('installerPayout', document.getElementById('payout').value);
            fd.append('paymentMode', document.getElementById('payMode').value);
            fd.append('salesName', document.getElementById('salesPerson').value);

            const btn = document.getElementById('saveBtn');
            btn.disabled = true; btn.innerText = "Syncing...";

            try {
                const res = await fetch(API, { method: 'POST', body: fd });
                const data = await res.json();
                
                if (data.status === 'saved' || data.status === 'updated') {
                    const uid = data.uid;
                    const inv = data.invoice_no;
                    const vehicle = document.getElementById('vehicle').value;
                    const mobile = document.getElementById('mobile').value;
                    const name = document.getElementById('customer').value;
                    const amt = document.getElementById('price').value - document.getElementById('discount').value;

                    if (confirm("🎉 Sync Successful! Share Invoice on WhatsApp?")) {
                        const baseUrl = window.location.href.replace('sales_entry.php', 'sales_invoice.php');
                        const shareUrl = `${baseUrl}?uid=${uid}`;
                        let msg = `Dear ${name},\n\nThank you for choosing SK ENTERPRISES. Your Sales Invoice for Vehicle ${vehicle} is ready.\n\nInvoice: ${inv}\nTotal: ₹${amt}\n\nView Bill: ${shareUrl}`;
                        
                        if (systemSettings && systemSettings.google_review_url) {
                            msg += `\n\n⭐ Please share your experience on Google: ${systemSettings.google_review_url}`;
                        }
                        
                        window.open(`https://wa.me/91${mobile}?text=${encodeURIComponent(msg)}`, '_blank');
                    }
                    location.reload();
                } else {
                    alert("Sync Error: " + (data.error || data.message));
                }
            } catch (err) {
                alert("Critical System Error. Please check console or server logs.");
                console.error(err);
            }
            btn.disabled = false; btn.innerText = isEdit ? "Update Changes" : "Save Sales Record";
        }

        async function loadRecord() {
            const v = document.getElementById('editVehicle').value.trim().toUpperCase();
            if (!v) return alert("Vehicle No required");

            try {
                const res = await fetch(`${API}?action=getSale&vehicle=${encodeURIComponent(v)}`);
                const r = await res.json();
                if (r.status === 'found') {
                    const d = r.data;
                    document.getElementById('vehicle').value = d.vehicle || '';
                    document.getElementById('imei').value = d.imei || '';
                    document.getElementById('sim').value = d.simNumber || '';
                    document.getElementById('customer').value = d.customer || '';
                    document.getElementById('location').value = d.location || '';
                    document.getElementById('mobile').value = d.mobileNumber || '';
                    document.getElementById('salesPerson').value = d.salesPerson || '';
                    document.getElementById('price').value = d.sellingPrice || 0;
                    document.getElementById('discount').value = d.discountAmount || 0;
                    document.getElementById('paid').value = d.receivedAmount || 0;
                    document.getElementById('payout').value = d.installerPayout || 0;
                    document.getElementById('payMode').value = d.paymentMode || 'CASH';
                    suppressDurationPrompt = true;
                    document.getElementById('software').value = d.softwareName || d.software || '';
                    document.getElementById('softwareDuration').value = d.softwareDuration || '1_year';
                    document.getElementById('softwareDurationText').innerText = `Selected duration: ${durationLabels[d.softwareDuration || '1_year'] || '1 Year'}`;
                    document.getElementById('relay').value = d.relay || 'NO';
                    setSimUsage(d.hasSim !== false);
                    setSim((d.simType && d.simType !== 'NO_SIM') ? d.simType : 'BASIC');
                    suppressDurationPrompt = false;
                    calculateProfit();
                } else {
                    alert("Record not found");
                }
            } catch (e) {
                alert("Load failed: " + e.message);
            }
        }

        window.onload = () => {
            fetchInitial();
            setSoftwareDuration('1_year');
            setSimUsage(true);
        };
        function openModal() { document.getElementById('modal').style.display = 'flex'; }
        function closeModal() { document.getElementById('modal').style.display = 'none'; }

        async function registerClient() {
            const name = document.getElementById('mName').value;
            const mobile = document.getElementById('mContact').value;
            const location = document.getElementById('mLoc').value;

            if (!name) return alert("Full Name is required");

            const fd = new FormData();
            fd.append('action', 'add_customer');
            fd.append('name', name);
            fd.append('mobile', mobile);
            fd.append('location', location);

            try {
                const res = await fetch('api_master_data.php', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.status === 'success') {
                    // Update main screen fields
                    document.getElementById('customer').value = name;
                    document.getElementById('mobile').value = mobile;
                    document.getElementById('location').value = location;
                    
                    // Add to local list for future searches
                    clients.push({ name, mobile, location });
                    
                    alert("✅ Client Registered & Selected");
                    closeModal();
                    
                    // Clear modal fields
                    document.getElementById('mName').value = '';
                    document.getElementById('mContact').value = '';
                    document.getElementById('mLoc').value = '';
                } else {
                    alert("Error: " + data.message);
                }
            } catch (e) {
                alert("Network Error: " + e.message);
            }
        }

        let statusT;
        async function checkImeiStatus(imei) {
            console.log('checkImeiStatus called with:', imei);
            const statusDiv = document.getElementById('imeiStatus');
            const deviceCard = document.getElementById('deviceInfoCard');
            if (!statusDiv) { console.log('statusDiv not found!'); return; }
            if (imei.length < 5) { 
                statusDiv.style.display = 'none'; 
                if(deviceCard) deviceCard.style.display = 'none'; 
                return; 
            }

            clearTimeout(statusT);
            statusT = setTimeout(async () => {
                try {
                    const url = `api_master_data.php?action=check_imei&imei=${encodeURIComponent(imei)}`;
                    console.log('Fetching:', url);
                    const response = await fetch(url);
                    const res = await response.json();
                    console.log('API response:', res);
                    
                    statusDiv.style.display = 'block';
                    if (res.status === 'success') {
                        const d = res.data;
                        console.log('Device data:', d);
                        const statusLower = (d.status || '').toLowerCase();
                        const isStock = statusLower === 'in stock' || statusLower.includes('stock');
                        
                        // Update status badge
                        statusDiv.style.background = isStock ? 'rgba(16, 185, 129, 0.1)' : 'rgba(244, 63, 94, 0.1)';
                        statusDiv.style.color = isStock ? 'var(--success)' : 'var(--danger)';
                        statusDiv.innerHTML = `<i class="fa-solid ${isStock ? 'fa-check-circle' : 'fa-triangle-exclamation'}"></i> ` + 
                            (isStock ? 'DEVICE IN STOCK' : `ALREADY SOLD / ISSUED TO: ${d.holder || 'UNKNOWN'}`) + 
                            ` (${d.device_model || ''})`;

                        // ✅ Show Device Info Card with model, rate, supplier
                        const modelEl = document.getElementById('deviceModelDisplay');
                        const rateEl = document.getElementById('deviceRateDisplay');
                        const suppEl = document.getElementById('deviceSupplierDisplay');
                        const badgeEl = document.getElementById('deviceStockStatus');
                        
                        if (modelEl) modelEl.innerText = d.device_model || 'Unknown Model';
                        if (rateEl) rateEl.innerText = '₹ ' + (parseFloat(d.rate) || 0).toLocaleString();
                        if (suppEl) suppEl.innerText = d.supplier_name || d.holder || '';
                        
                        if (badgeEl) {
                            badgeEl.style.background = isStock ? 'rgba(16,185,129,0.2)' : 'rgba(244,63,94,0.2)';
                            badgeEl.style.color = isStock ? '#4ade80' : '#f87171';
                            badgeEl.innerText = isStock ? 'IN STOCK' : 'SOLD';
                        }
                        
                        if (deviceCard) {
                            deviceCard.style.display = 'block';
                            console.log('Device card shown!');
                        }
                    } else {
                        statusDiv.style.background = 'rgba(255, 255, 255, 0.05)';
                        statusDiv.style.color = 'var(--text-muted)';
                        statusDiv.innerHTML = `<i class="fa-solid fa-circle-question"></i> NOT FOUND (${res.error || ''})`;
                        if(deviceCard) deviceCard.style.display = 'none';
                    }
                } catch (e) { 
                    console.error('checkImeiStatus error:', e);
                    if(statusDiv) { statusDiv.style.display = 'none'; }
                    if(deviceCard) deviceCard.style.display = 'none'; 
                }
            }, 300);
        }
    </script>
</body>
</html>
